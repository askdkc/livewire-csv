[![Latest Version on Packagist](https://img.shields.io/packagist/v/askdkc/livewire-csv.svg?style=flat-square)](https://packagist.org/packages/askdkc/livewire-csv)
[![Total Downloads](https://img.shields.io/packagist/dt/askdkc/livewire-csv.svg?style=flat-square)](https://packagist.org/packages/askdkc/livewire-csv)


- [このパッケージについて](#このパッケージについて)
- [インストール方法](#installation)
- [使用準備](#configuration)
- [使い方](#usage)
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

## このパッケージについて
__Livewire CSV__ はLaravel [Livewire](https://laravel-livewire.com)を使ってお手軽にCSVをインポート出来るように出来ています。

<a name="installation"></a>
## インストール方法

下記のようにcomposerを使ってお手軽インストール出来ます:

```sh
composer require askdkc/livewire-csv
```
<a name="configuration"></a>
## 使用準備

CSVインポート機能に必要なDBテーブル用のマイグレーションファイルを次のコマンドで自動生成し、マイグレーションを実行します:

```sh
php artisan vendor:publish --tag="livewire-csv-migrations"
php artisan migrate
```

CSVのインポート時にはLaravelのキュー(queue)機能を使うので、それ用のマイグレーションも以下の手順で実行しておきます:

```bash
php artisan queue:table
php artisan queue:batches-table
php artisan migrate
```

このパッケージ用の設定ファイルは下記のコマンドで出力してカスタマイズ可能です:

```bash
php artisan vendor:publish --tag="livewire-csv-config"
```

出力された設定ファイルは下記のような内容です:

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

 `layout` オプションはCSSの選択肢となりますが、今のところ`tailwindcss`しか使えないので弄らないでください。将来別のCSSでこのパッケージ用のblade.phpを作った時にはここを変更するだけで切り替え可能に😏

`file_upload_size` はアップロードされるCSVファイルの最大サイズのバリデーションに使われます(初期値は約20MB)。Livewireを使っているので[livewire config](https://github.com/livewire/livewire/blob/master/config/livewire.php#L100) ファイルを変更して対応させることも可能です

オプションとして、CSVインポート用の画面のデザインを下記コマンドを実行して出力されるファイルから行うことができます

```bash
php artisan vendor:publish --tag="livewire-csv-views"
```

> このコマンドを実行する前に[こちらの説明](#in-tall-stack-project) もお読みください

## Userモデルに `use HasCsvImports` を追加

このパッケージを動作させるためにはUserモデルにHasCsvImportsをインポートして使う必要があります

 `app/Models/User.php` を開いて、次のように編集してください:
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

<a name="usage"></a>
## 使い方

準備中
