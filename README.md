[![Latest Version on Packagist](https://img.shields.io/packagist/v/askdkc/livewire-csv.svg?style=flat-square)](https://packagist.org/packages/askdkc/livewire-csv)
[![Total Downloads](https://img.shields.io/packagist/dt/askdkc/livewire-csv.svg?style=flat-square)](https://packagist.org/packages/askdkc/livewire-csv)

# [日本語ReadMeはこちら](/README-ja.md)

# About This Package
- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [CSV Importer Component](#csv-importer-component)
  - [Button Component](#button-component)
  - [In TALL stack project](#in-tall-stack-project)
  - [In none TALL Stack project](#in-none-tall-stack-project)
  - [Using Queues](#using-queues)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Inspiration](#inspiration)
- [Credits](#credits)
- [License](#license)

## Introduction
__Livewire CSV__ Package is a package created on top of Laravel [livewire](https://laravel-livewire.com) for easily handling imports with a simple API. And added some bug fixes to original Codecourse code and package.

## Installation

You can install the package via composer:

```bash
composer require askdkc/livewire-csv
```

## Setup Command

You can run `livecsv-setup` command to publish nessesary migration files for this packkage.

```bash
php artisan livecsv-setup
```

This command, after publishes files, ask you to run the migration for you. If you want to run the migration by youself then just answer no. Otherwise, type "yes" to run the migration.

This command also ask you to star this repo. If you don't mind helping me, please star the repo. (Thanks in advance)


## Add `use HasCsvImports` to your User Model

You need to implement HasCsvImports to your User model.

Open `app/Models/User.php` and edit like below:
```php
<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Askdkc\LivewireCsv\Concerns\HasCsvImports; // add
...

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasCsvImports; // add HasCsvImports here
    
```

## Usage

### CSV Importer Component
Using this package is easy. To implmenent the importer in your project, simply include the following component inside a Blade view.

```blade
    <livewire:csv-importer :model="App\Models\YourModel::class"
                            :columns-to-map="['id', 'name', 'email']"
                            :required-columns="['id', 'name', 'email']"
                            :column-labels="[
                                'id' => 'ID',
                                'name' => 'Name',
                                'email' => 'Email Address',
                            ]"/>
```

| Props  | Type  |  Description  |
|---|---|---|
|  model |`string` | Fully qualified name of the model you wish to import to  |
|  columns-to-map |`array` | Column names in the target database table |
|  required-columns |`array` | Columns that are required by validation for import  |
| columns-label  |`array` |  Display labels for the required columns  |

### Button Component
The Component uses `alpinejs` under the hood. To display an import button, include the `x-csv-button` component.

```blade
<x-csv-button>Import</x-csv-button>
```

To style the button, use the `class` attribute with Tailwind utility classes.

```blade
<x-csv-button 
        class="rounded py-2 px-3 bg-indigo-500 ..."
        type="button"
        ....>
    {{ __('Import') }}
</x-csv-button>
```

### Manual Configuration

If you are not using `livecsv-setup` command, follow these steps to manually configure package setup process.

Publish and run the migrations with:

```bash
php artisan vendor:publish --tag="livewire-csv-migrations"
php artisan migrate
```

Csv Import uses Queue Worker so you need to create these tables:

```bash
php artisan queue:table
php artisan queue:batches-table
php artisan migrate
```

Publish the config file with:

```bash
php artisan vendor:publish --tag="livewire-csv-config"
```

The following is the contents of the published config file:

```php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Layout
    |--------------------------------------------------------------------------
    |
    | This package plans on supporting multiple CSS frameworks. 
    | Currently, 'tailwindcss' is the default and only supported framework.
    |
    */
    'layout' => 'tailwindcss',

    /*
    |--------------------------------------------------------------------------
    | Max Upload File Size
    |--------------------------------------------------------------------------
    |
    | The default maximumum file size that can be imported by this
    | package is 20MB. If you wish to increase/decrease this value, 
    | change the value in KB below.
    |
    */
    'file_upload_size' => 20000,
];
```

The `layout` option is for choosing which CSS framework you are using and currently supports only `tailwindcss`. We are working on other CSS frameworks to implement in the future.

The `file_upload_size` is for validation rules, and it defines the maximum file size of uploaded files. You may also define this value from the [livewire config](https://github.com/livewire/livewire/blob/master/config/livewire.php#L100) file.

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="livewire-csv-views"
```

> Before Using this command, please take a look at this [section](#in-tall-stack-project) below.

### In TALL stack project
If you are using this package in a [TALL Stack](https://tallstack.dev/) project, (Tailwindcss, Alpinejs, Laravel, Livewire) publish the vendor views to include livewire-csv in your project.

```bash
php artisan vendor:publish --tag="csv-views"
```
Then compile your assets.
```bash
npm run dev
```

### In none TALL Stack project
If you are not using the TALL Stack, use the `csv directives` to add the necessary styles/scripts.

```blade
<html>
    ...
    <head>
        ...
        @csvStyles
    </head>
        ...
    <footer>
        ...
        @csvScripts
    </footer>
</html>

```
### Using Queues
This package uses [queues](https://laravel.com/docs/9.x/queues#main-content) under the hood with [PHP Generators](https://www.php.net/manual/en/language.generators.overview.php) to make it fast and efficient.

Create the `batches table` by running
```bash
php artisan queue:batches-table
```
Then, run the migration.
```
php artisan migrate
```

After that, set up the queues' configuration.
Head to [Laravel Queues Documentation](https://laravel.com/docs/9.x/queues#main-content) to learn more.


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/ousid/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Inspiration
This Package Was Inspired by [codecourse](https://codecourse.com) video series. If you want to learn how this package was created, make sure to take a look at this [video series](https://codecourse.com/subjects/laravel-livewire)

## Credits

- [askdkc](https://github.com/askdkc)
- [ousid](https://github.com/ousid) Original Package Creator
- [Alex Garrett-Smith](https://codecourse.com/courses/build-a-livewire-csv-importer) Originally introduced this app through his [Codecourse](https://codecourse.com/courses/build-a-livewire-csv-importer).<br>
If you want to learn deeper, subscribe his [Codecourse series](https://codecourse.com) (it's really good!).
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
