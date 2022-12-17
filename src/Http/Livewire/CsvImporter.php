<?php

namespace Askdkc\LivewireCsv\Http\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;

use Livewire\WithFileUploads;
use Askdkc\LivewireCsv\Concerns;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\Validator;
use Askdkc\LivewireCsv\Jobs\ImportCsv;
use Askdkc\LivewireCsv\Facades\LivewireCsv;
use function Askdkc\LivewireCsv\csv_view_path;
use Askdkc\LivewireCsv\Utilities\ChunkIterator;

class CsvImporter extends Component
{
    use WithFileUploads;
    use Concerns\InteractsWithColumns;
    use Concerns\HasCsvProperties;

    /** @var string */
    public $model;

    /** @var bool */
    public bool $open = false;

    /** @var object */
    public $file;

    /** @var array */
    public array $columnsToMap = [];

    /** @var array */
    public array $requiredColumns = [];

    /** @var array */
    public array $columnLabels = [];

    /** @var array */
    public array $upsertColumns = [];

    /** @var array */
    public array $fileHeaders = [];

    /** @var int */
    public int $fileRowCount = 0;

    /** @var array */
    protected $exceptions = [
        'model', 'columnsToMap', 'open',
        'columnLabels', 'requiredColumns','upsertColumns',
    ];

    /** @var array */
    protected $listeners = [
        'toggle',
    ];

    public function mount(): void
    {
        // map and coverts the columnsToMap property into an associative array
        $this->columnsToMap = $this->mapThroughColumns();

        // map and coverts the requiredColumns property int key => required value
        $this->requiredColumns = $this->mapThroughRequiredColumns();

        // check if user specified upsert columns
        if(!$this->upsertColumns)
        {
            $this->upsertColumns = ['id'];
        }
    }

    public function updatedFile(): void
    {
        $this->validateOnly('file');

        $this->setCsvProperties();

        $this->resetValidation();
    }

    public function import(): void
    {
        $this->validate();

        $this->importCsv();

        $this->resetExcept($this->exceptions);

        $this->emitTo('handle-imports', 'imports.refresh');
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function render(): Object
    {
        return view(csv_view_path('csv-importer'), [
            'fileSize' => LivewireCsv::formatFileSize(
                config('livewire_csv.file_upload_size', 20000)
            ),
        ]);
    }

    protected function validationAttributes(): array
    {
        $columnMessage = new Collection();
        foreach ($this->requiredColumns as $key => $col)
        {
            $columnMessage->push([$key => $this->columnLabels[Str::after($key, 'columnsToMap.')] ?? Str::after($key, 'columnsToMap.')]);
        }
        return $columnMessage->collapse()->toArray();
    }

    protected function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,tsv,txt|max:'.config('livewire_csv.file_upload_size', '20000'),
        ] + $this->requiredColumns;
    }

    protected function setCsvProperties(): array
    {
        if (! $this->handleCsvProperties() instanceof MessageBag) {
            return [
                $this->fileHeaders,
                $this->fileRowCount
            ] = $this->handleCsvProperties();
        }

        return $this->withValidator(function (Validator $validator) {
            $validator->after(function ($validator) {
                $validator->errors()->merge(
                   $this->handleCsvProperties()->getMessages()
                );
            });
        })->validate();
    }

    protected function importCsv(): void
    {
        $import = $this->createNewImport();
        $chunks = (new ChunkIterator($this->csvRecords->getIterator(), 10))->get();

        /** @var array<array> $chunks */
        $jobs = collect($chunks)
                    ->map(
                        fn ($chunk) => new ImportCsv(
                            $import,
                            $this->model,
                            $chunk,
                            $this->columnsToMap,
                            $this->upsertColumns
                        )
                    );

        Bus::batch($jobs)
                    ->name('import-csv')
                    ->finally(
                        fn () => $import->touch('completed_at')
                    )->dispatch();
    }

    protected function createNewImport(): Object
    {
        /**
         * @var \Askdkc\LivewireCsv\Tests\Models\User */
        $user = auth()->user();

        return $user->imports()->create([
            'model' => $this->model,
            'file_path' => $this->file->getRealPath(),
            'file_name' => $this->file->getClientOriginalName(),
            'total_rows' => $this->fileRowCount,
        ]);
    }
}
