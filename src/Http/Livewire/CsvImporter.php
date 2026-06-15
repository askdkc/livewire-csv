<?php

namespace Askdkc\LivewireCsv\Http\Livewire;

use Askdkc\LivewireCsv\Concerns;
use Askdkc\LivewireCsv\Facades\LivewireCsv;
use Askdkc\LivewireCsv\Jobs\ImportCsv;
use Askdkc\LivewireCsv\Tests\Models\User;
use Askdkc\LivewireCsv\Utilities\ChunkIterator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

use function Askdkc\LivewireCsv\csv_view_path;

class CsvImporter extends Component
{
    use Concerns\HasCsvProperties;
    use Concerns\InteractsWithColumns;
    use WithFileUploads;

    /** @var string */
    public $model;

    public bool $open = false;

    /** @var object */
    public $file;

    public array $columnsToMap = [];

    public array $requiredColumns = [];

    public array $columnLabels = [];

    public array $upsertColumns = [];

    public array $fileHeaders = [];

    public int $fileRowCount = 0;

    /** @var array */
    protected $exceptions = [
        'model', 'columnsToMap', 'open',
        'columnLabels', 'requiredColumns', 'upsertColumns',
    ];

    /** @var array */
    protected $listeners = [
        'toggle',
    ];

    // This makes validation message translatable using the package's lang files
    protected function messages(): array
    {
        return [
            'required' => trans('livewire-csv::validation.required'),
        ];
    }

    public function mount(): void
    {
        // map and coverts the columnsToMap property into an associative array
        $this->columnsToMap = $this->mapThroughColumns();

        // map and coverts the requiredColumns property int key => required value
        $this->requiredColumns = $this->mapThroughRequiredColumns();

        // check if user specified upsert columns
        if (! $this->upsertColumns) {
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

        $this->dispatch('imports.refresh')->to(HandleImports::class);
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function render(): object
    {
        return view(csv_view_path('csv-importer'), [
            'fileSize' => LivewireCsv::formatFileSize(
                config('livewire_csv.file_upload_size', 20000)
            ),
        ]);
    }

    protected function validationAttributes(): array
    {
        $columnMessage = new Collection;
        foreach ($this->requiredColumns as $key => $col) {
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
        $this->dispatch('imports');
    }

    protected function createNewImport(): object
    {
        /**
         * @var User */
        $user = auth()->user();

        return $user->imports()->create([
            'model' => $this->model,
            'file_path' => $this->file->getRealPath(),
            'file_name' => $this->file->getClientOriginalName(),
            'total_rows' => $this->fileRowCount,
        ]);
    }
}
