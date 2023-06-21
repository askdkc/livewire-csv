<?php

use Askdkc\LivewireCsv\Http\Livewire\CsvImporter;
use Askdkc\LivewireCsv\Models\Import;
use Askdkc\LivewireCsv\Tests\Models\Post;
use Askdkc\LivewireCsv\Tests\Models\Tag;
use Askdkc\LivewireCsv\Tests\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use function Pest\Livewire\livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('maps requiredColumns property into columnsToMap required state', function () {
    $columnsToMap = [
        'title',
        'slug',
        'body',
        'extra',
    ];

    $requiredColumns = [
        'title',
        'slug',
        'body',
    ];

    $upsertColumns = [
        'slug',
    ];

    $model = Post::class;

    livewire(CsvImporter::class, [
        'model' => $model,
        'columnsToMap' => $columnsToMap,
        'requiredColumns' => $requiredColumns,
        'upsertColumns' => $upsertColumns,
    ])
    ->assertSet('model', $model)
    ->assertSet('requiredColumns', [
        'columnsToMap.title' => 'required',
        'columnsToMap.slug' => 'required',
        'columnsToMap.body' => 'required',
    ])
    ->assertSet('upsertColumns', $upsertColumns);
});

it('returns csv headers & row counts when upload a file', function () {

    $file = UploadedFile::fake()
                    ->createWithContent(
                        'posts.csv',
                        file_get_contents('stubs/posts.csv', true)
                    );

    $model = Post::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->assertSet('model', $model)
    ->assertSet('fileHeaders', [
        'title', 'slug', 'body', 'extra',
    ])
    ->assertSet('fileRowCount', 2);
});

it('creates posts records on top of csv file', function () {
    $file = UploadedFile::fake()
        ->createWithContent(
            'posts.csv',
            file_get_contents('stubs/posts.csv', true)
        );

    $model = Post::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->set('columnsToMap', [
        'title' => 'title',
        'slug' => 'slug',
        'body' => 'body',
        'extra' => 'extra',
    ])
    ->set('upsertColumns', [
        'slug',
    ])
    ->call('import')
    ->assertEmitted('imports.refresh')
    ->assertHasNoErrors();

    $import = Import::forModel(Post::class);

    $this->assertEquals(Import::count(), 1);
    $this->assertEquals($import->count(), 1);
    $this->assertEquals(Post::count(), 2);
    $this->assertEquals($import->first()->processed_rows, 2);
});

it('created posts records is following user specified data fields', function () {
    $file = UploadedFile::fake()
        ->createWithContent(
            'posts.csv',
            file_get_contents('stubs/posts.csv', true)
        );

    $model = Post::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->set('columnsToMap', [
        'slug' => 'slug',
        'title' => 'body', // This is intentional for this test
        'body' => 'title', // This is intentional for this test
        'extra' => 'extra',
    ])
    ->set('upsertColumns', [
        'slug',
    ])
    ->call('import')
    ->assertEmitted('imports.refresh')
    ->assertHasNoErrors();

    $import = Import::forModel(Post::class);

    $this->assertEquals(Import::count(), 1);
    $this->assertEquals($import->count(), 1);
    $this->assertEquals(Post::count(), 2);

    $this->assertEquals(Post::first()->title, "TestBody");
});


it('creates tag records on top of csv file', function () {
    $file = UploadedFile::fake()
        ->createWithContent(
            'tags.csv',
            file_get_contents('stubs/tags.csv', true)
        );

    $model = Tag::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->set('columnsToMap', [
        'tag_id' => 'tag_id',
        'post_id' => 'post_id',
        'memo' => 'memo',
    ])
    ->set('upsertColumns', [
        'tag_id','post_id',
    ])
    ->call('import')
    ->assertEmitted('imports.refresh')
    ->assertHasNoErrors();

    $import = Import::forModel(Tag::class);

    $this->assertEquals(Import::count(), 1);
    $this->assertEquals($import->count(), 1);
    $this->assertEquals(Tag::count(), 3);
    $this->assertEquals($import->first()->processed_rows, 3);
});


it('updated tag records with csv succesfully', function () {
    $file = UploadedFile::fake()
        ->createWithContent(
            'tags.csv',
            file_get_contents('stubs/tags.csv', true)
        );

    $model = Tag::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->set('columnsToMap', [
        'tag_id' => 'tag_id',
        'post_id' => 'post_id',
        'memo' => 'sample',
    ])
    ->set('upsertColumns', [
        'tag_id','post_id',
    ])
    ->call('import')
    ->assertEmitted('imports.refresh')
    ->assertHasNoErrors();

    $import = Import::forModel(Tag::class);

    $this->assertEquals(Import::count(), 1);
    $this->assertEquals($import->count(), 1);
    $this->assertEquals(Tag::count(), 3);
    $this->assertEquals(Tag::where('tag_id',1)->first()->memo, "sample1");
    $this->assertEquals(Tag::where('tag_id',2)->first()->memo, "sample2");
    $this->assertEquals(Tag::where('tag_id',3)->first()->memo, "sample3");
});


it('read csv file as tsv', function () {

    Config::set('livewire_csv.file_type', 'tsv');

    $file = UploadedFile::fake()
                    ->createWithContent(
                        'posts.csv',
                        file_get_contents('stubs/posts.csv', true)
                    );

    $model = Post::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->assertSet('model', $model)
    ->assertSet('fileHeaders', [
        'title,"slug","body","extra"',
    ])
    ->assertSet('fileRowCount', 2);
});

it('read csv file as csv', function () {

    Config::set('livewire_csv.file_type', 'csv');

    $file = UploadedFile::fake()
                    ->createWithContent(
                        'posts.csv',
                        file_get_contents('stubs/posts.csv', true)
                    );

    $model = Post::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->assertSet('model', $model)
    ->assertSet('fileHeaders', [
        'title', 'slug', 'body', 'extra',
    ])
    ->assertSet('fileRowCount', 2);
});

it('return error when read csv file with semicolon delimiter', function () {

    Config::set('livewire_csv.file_type', 'csv');

    $file = UploadedFile::fake()
                    ->createWithContent(
                        'posts_semi.csv',
                        file_get_contents('stubs/posts_semi.csv', true)
                    );

    $model = Post::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->assertSet('model', $model)
    ->assertNotSet('fileHeaders', [
        'title', 'slug', 'body', 'extra',
    ])
    ->assertSet('fileHeaders', [
        'title;"slug";"body";"extra"',
    ])
    ->assertSet('fileRowCount', 2);
});

it('read csv file with semicolon delimiter without error', function () {

    Config::set('livewire_csv.set_delimiter', ';');

    $file = UploadedFile::fake()
                    ->createWithContent(
                        'posts_semi.csv',
                        file_get_contents('stubs/posts_semi.csv', true)
                    );

    $model = Post::class;

    livewire(CsvImporter::class, [
        'model' => $model,
    ])
    ->set('file', $file)
    ->assertSet('model', $model)
    ->assertSet('fileHeaders', [
        'title', 'slug', 'body', 'extra',
    ])
    ->assertSet('fileRowCount', 2);
});
