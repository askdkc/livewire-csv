<?php

namespace Askdkc\LivewireCsv;

use Askdkc\LivewireCsv\Commands\LiveSetupCommand;
use Askdkc\LivewireCsv\Http\Livewire\CsvImporter;
use Askdkc\LivewireCsv\Http\Livewire\HandleImports;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LivewireCsvServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('livewire-csv')
            ->hasCommand(LiveSetupCommand::class)
            ->hasConfigFile('livewire_csv')
            ->hasAssets()
            ->hasTranslations()
            ->hasViews('livewire-csv')
            ->hasMigration('create_csv_imports_table');
    }

    public function bootingPackage(): void
    {
        $this->registerLivewireComponents();

        $this->configureComponents();

        $this->registerBladeDirectives();
    }

    public function registeringPackage(): void
    {
        $this->app->bind('livewire-csv', fn () => new LivewireCsvManager);
    }

    /**
     * Configure Livewire CSV Blade components
     *
     * @return void
     */
    protected function configureComponents(): void
    {
        $this->callAfterResolving(BladeCompiler::class, function () {
            $this->registerComponent('button');
        });
    }

    /**
     * Register livewire components
     *
     * @return void
     */
    protected function registerLivewireComponents(): void
    {
        Livewire::component('csv-importer', CsvImporter::class);

        Livewire::component('handle-imports', HandleImports::class);
    }

    /**
     * Register given component.
     *
     * @param  string  $component
     * @return void
     */
    protected function registerComponent(string $component): void
    {
        Blade::component('livewire-csv::components.'.$component, 'csv-'.$component);
    }

    /**
     * Register Livewire CSV blade directives
     *
     * @return void
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('csvStyles', [LivewireCsvDirectives::class, 'csvStyles']);
        Blade::directive('csvScripts', [LivewireCsvDirectives::class, 'csvScripts']);
    }
}
