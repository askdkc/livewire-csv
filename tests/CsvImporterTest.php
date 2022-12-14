<?php

use Askdkc\LivewireCsv\Http\Livewire\CsvImporter;
use Askdkc\LivewireCsv\Models\Import;
use Askdkc\LivewireCsv\Tests\Models\Customer;
use Askdkc\LivewireCsv\Tests\Models\User;
use Illuminate\Bus\PendingBatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use function Pest\Livewire\livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('renders import CSV component with model', function () {
    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->assertSet('model', $model)
    ->assertSuccessful();
});

it('renders import CSV component with model and file', function () {
    $model = Customer::class;

    $file = UploadedFile::fake()
        ->createWithContent(
            'customers.csv',
            file_get_contents('stubs/customers.csv', true)
        );

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->assertSet('model', $model)
    ->assertSuccessful();
});

it('throws a validation error if the csv file empty', function () {
    $model = Customer::class;

    $file = UploadedFile::fake()
        ->createWithContent(
            'customers.csv',
            file_get_contents('stubs/empty.csv', true)
        );

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
        ->set('file', $file)
        ->assertSet('model', $model)
        ->assertHasErrors(['file']);
});

it('throws a validation error if the csv file has duplicate headers', function () {
    $model = Customer::class;

    $file = UploadedFile::fake()
        ->createWithContent(
            'customers.csv',
            file_get_contents('stubs/file_with_duplicate_headers.csv', true)
        );

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
        ->set('file', $file)
        ->assertSet('model', $model)
        ->assertHasErrors(['file']);
});

it('transfers columnsToMap into an associative array', function () {
    $columnsToMap = [
        'name',
        'email',
        'phone',
    ];
    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
        'columnsToMap' => $columnsToMap,
    ])
    ->assertSet('model', $model)
    ->assertSet('columnsToMap', [
        'name' => '',
        'email' => '',
        'phone' => '',
    ])
    ->assertSuccessful();
});

it('maps requiredColumns property into columnsToMap required state', function () {
    $columnsToMap = [
        'name',
        'email',
        'phone',
    ];

    $requiredColumns = [
        'name',
        'email',
        'phone',
    ];
    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
        'columnsToMap' => $columnsToMap,
        'requiredColumns' => $requiredColumns,
    ])
    ->assertSet('model', $model)
    ->assertSet('requiredColumns', [
        'columnsToMap.name' => 'required',
        'columnsToMap.email' => 'required',
        'columnsToMap.phone' => 'required',
    ]);
});

it('maps through columnsLabels for validate attributes', function () {
    $columnsToMap = [
        'name', 'email', 'phone',
    ];

    $requiredColumns = [
        'name', 'email',
    ];

    $columnLabels = [
        'name' => 'Name',
        'email' => 'Email',
    ];

    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
        'columnsToMap' => $columnsToMap,
        'requiredColumns' => $requiredColumns,
        'columnLabels' => $columnLabels,
    ])
    ->assertSet('model', $model)
    ->assertSet('columnLabels', [
        'name' => 'Name',
        'email' => 'Email',
    ]);
});

it('returns csv headers & row counts when upload a file', function () {

    $file = UploadedFile::fake()
                    ->createWithContent(
                        'customers.csv',
                        file_get_contents('stubs/customers.csv', true)
                    );

    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->assertSet('model', $model)
    ->assertSet('fileHeaders', [
        'id', 'first_name', 'last_name', 'email', 'company', 'vip', 'birthday', 'created_at', 'updated_at',
    ])
    ->assertSet('fileRowCount', 1000);
});

it('throws validation errors, if the file extension does not match', function () {
    $file = UploadedFile::fake()->create('image.png');
    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->assertHasErrors(['file']);
});

it('throws validation errors, if the columns does not match', function () {

    $columnsToMap = [
        'name', 'email', 'phone',
    ];

    $requiredColumns = [
        'name', 'email',
    ];

    $columnLabels = [
        'name' => 'Name',
        'email' => 'Email',
    ];

    $file = UploadedFile::fake()
        ->createWithContent(
            'customers.csv',
            file_get_contents('stubs/customers.csv', true)
        );

    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
        'columnsToMap' => $columnsToMap,
        'requiredColumns' => $requiredColumns,
        'columnLabels' => $columnLabels,
    ])
    ->set('file', $file)
    ->call('import')
    ->assertHasErrors(['columnsToMap.name', 'columnsToMap.email']);
});

it('ensures the imports is batched', function () {
    Bus::fake();

    $file = UploadedFile::fake()
        ->createWithContent(
            'customers.csv',
            file_get_contents('stubs/customers.csv', true)
        );

    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->set('columnsToMap', [
        'id' => 'id',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'email',
    ])
    ->call('import')
    ->assertEmitted('imports.refresh')
    ->assertHasNoErrors();

    Bus::assertBatched(function (PendingBatch $batch) {
        return $batch->name == 'import-csv' &&
               $batch->jobs->count() === 100;
    });

    $this->assertEquals(Import::count(), 1);
    $this->assertEquals(Import::forModel(Customer::class)->count(), 1);
});

it('creates customers records on top of csv file', function () {
    $file = UploadedFile::fake()
        ->createWithContent(
            'customers.csv',
            file_get_contents('stubs/customers.csv', true)
        );

    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->set('columnsToMap', [
        'id' => 'id',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'email',
    ])
    ->call('import')
    ->assertEmitted('imports.refresh')
    ->assertHasNoErrors();

    $import = Import::forModel(Customer::class);

    $this->assertEquals(Import::count(), 1);
    $this->assertEquals($import->count(), 1);
    $this->assertEquals(Customer::count(), 1000);
    $this->assertEquals($import->first()->processed_rows, 1000);
});

it('created customers records is following user specified data fields', function () {
    $file = UploadedFile::fake()
        ->createWithContent(
            'customers.csv',
            file_get_contents('stubs/customers.csv', true)
        );

    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->set('columnsToMap', [
        'id' => 'id',
        'first_name' => 'last_name', // This is intentional for this test
        'last_name' => 'first_name', // This is intentional for this test
        'email' => 'email',
    ])
    ->call('import')
    ->assertEmitted('imports.refresh')
    ->assertHasNoErrors();

    $import = Import::forModel(Customer::class);

    $this->assertEquals(Import::count(), 1);
    $this->assertEquals($import->count(), 1);
    $this->assertEquals(Customer::count(), 1000);
    $this->assertEquals($import->first()->processed_rows, 1000);

    $this->assertEquals(Customer::first()->first_name, "Ondricka");
});

it('can handled none required column even when column is empty', function () {
    $file = UploadedFile::fake()
        ->createWithContent(
            'customers.csv',
            file_get_contents('stubs/customers.csv', true)
        );

    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->set('columnsToMap', [
        'id' => '', // This is intentional for this test
        'first_name' => 'last_name',
        'last_name' => 'first_name',
        'email' => 'email',
    ])
    ->call('import')
    ->assertEmitted('imports.refresh')
    ->assertHasNoErrors();

    $import = Import::forModel(Customer::class);

    $this->assertEquals(Import::count(), 1);
    $this->assertEquals($import->count(), 1);
    $this->assertEquals(Customer::count(), 1000);
    $this->assertEquals($import->first()->processed_rows, 1000);

    $this->assertEquals(Customer::first()->first_name, "Ondricka");
});

it('toggles import button', function () {
    $model = Customer::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->emit('toggle')
    ->assertSet('open', true)
    ->assertHasNoErrors();
});
