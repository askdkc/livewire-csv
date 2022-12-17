<?php

namespace Askdkc\LivewireCsv\Tests;

use Askdkc\LivewireCsv\Http\Livewire\CsvImporter;
use Askdkc\LivewireCsv\Http\Livewire\HandleImports;
use Askdkc\LivewireCsv\LivewireCsvServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected $fakeClient = null;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Askdkc\\LivewireCsv\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->registerLivewireComponents();

        // コマンドが出力したファイルがテスト前に残っていたら消す
        if (is_dir(__DIR__.'/../vendor/orchestra/testbench-core/laravel/lang/ja')) {
            unlink(__DIR__.'/../vendor/orchestra/testbench-core/laravel/lang/ja.json');
            unlink(__DIR__.'/../vendor/orchestra/testbench-core/laravel/lang/ja/validation.php');
            rmdir(__DIR__.'/../vendor/orchestra/testbench-core/laravel/lang/ja');
        }

        if(is_file(__DIR__.'/../vendor/orchestra/testbench-core/laravel/configlivewire_csv.php')) {
            unlink(__DIR__.'/../vendor/orchestra/testbench-core/laravel/configlivewire_csv.php');
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            LivewireCsvServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migrations = [
            include __DIR__.'/../database/migrations/create_csv_imports_table.php.stub',
            include __DIR__.'/Database/Migrations/create_customers_table.php',
            include __DIR__.'/Database/Migrations/create_posts_table.php',
            include __DIR__.'/Database/Migrations/create_tags_table.php',
            include __DIR__.'/Database/Migrations/create_users_table.php',
            include __DIR__.'/Database/Migrations/create_job_batches_table.php',
        ];

        collect($migrations)->each(fn ($path) => $path->up());
    }

    private function registerLivewireComponents(): self
    {
        Livewire::component('csv-importer', CsvImporter::class);
        Livewire::component('handle-imports', HandleImports::class);

        return $this;
    }

    public function migrationExists(string $filename): Bool
    {
        $path = database_path('migrations/');
        $files = scandir($path);
        $pos = false;
        foreach ($files as $value) {
            $pos = strpos($value, $filename);
            if($pos !== false) return true;
        }
        return false;
    }
}
