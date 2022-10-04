<?php

namespace Askdkc\LivewireCsv\Http\Livewire;

use Askdkc\LivewireCsv\Concerns;
use function Askdkc\LivewireCsv\csv_view_path;
use Askdkc\LivewireCsv\Facades\LaravelCsv;
use Askdkc\LivewireCsv\Jobs\ImportCsv;
use Askdkc\LivewireCsv\Utilities\ChunkIterator;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

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
    public array $fileHeaders = [];

    /** @var int */
    public int $fileRowCount = 0;

    /** @var array */
    protected $exceptions = [
        'mode', 'columnsToMap', 'open',
        'columnLabels', 'requiredColumns',
    ];

    /** @var array */
    protected $listeners = [
        'toggle',
    ];

    public function mount()
    {
        // map and coverts the columnsToMap property into an associative array
        $this->columnsToMap = $this->mapThroughColumns();

        // map and coverts the requiredColumns property int key => required value
        $this->requiredColumns = $this->mapThroughRequiredColumns();
    }

    public function updatedFile()
    {
        $this->validateOnly('file');

        $this->setCsvProperties();

        $this->resetValidation();
    }

    public function import()
    {
        $this->validate();

        $this->importCsv();

        $this->resetExcept($this->exceptions);

        $this->emitTo('csv-imports', 'imports.refresh');
    }

    public function toggle()
    {
        $this->open = ! $this->open;
    }

    public function render()
    {
        return view(csv_view_path('csv-importer'), [
            'fileSize' => LaravelCsv::formatFileSize(
                config('livewire_csv.file_upload_size', 20000)
            ),
        ]);
    }

    protected function validationAttributes()
    {
        return $this->columnLabels;
    }

    protected function rules()
    {
        return [
            'file' => 'required|file|mimes:csv,txt|max:'.config('livewire_csv.file_upload_size', '20000'),
        ] + $this->requiredColumns;
    }

    protected function setCsvProperties()
    {
        if (! $this->handleCsvProperties() instanceof MessageBag) {
            return [
                $this->fileHeaders,
                $this->fileRowCount
            ] = $this->handleCsvProperties();
        }

        $this->withValidator(function (Validator $validator) {
            $validator->after(function ($validator) {
                $validator->errors()->merge(
                    $this->handleCsvProperties()->getMessages()
                );
            });
        })->validate();
    }

    protected function importCsv()
    {
        $import = $this->createNewImport();
        $chunks = (new ChunkIterator($this->csvRecords->getIterator(), 10))->get();

        $jobs = collect($chunks)
                    ->map(
                        fn ($chunk) => new ImportCsv(
                            $import,
                            $this->model,
                            $chunk,
                            $this->columnsToMap
                        )
                    );

        Bus::batch($jobs)
                    ->name('import-csv')
                    ->finally(
                        fn () => $import->touch('completed_at')
                    )->dispatch();
    }

    protected function createNewImport()
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
