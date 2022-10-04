[![Latest Version on Packagist](https://img.shields.io/packagist/v/askdkc/livewire-csv.svg?style=flat-square)](https://packagist.org/packages/askdkc/livewire-csv)
[![Total Downloads](https://img.shields.io/packagist/dt/askdkc/livewire-csv.svg?style=flat-square)](https://packagist.org/packages/askdkc/livewire-csv)

# Livewire-CSVパッケージについて
- [このパッケージについて](#このパッケージについて)
- [インストール方法](#installation)
- [使用準備](#configuration)
- [使い方](#usage)
  - [コンポーネントを利用するbladeビューの準備](#createbladeview)
  - [CSV Importerコンポーネントについて](#csv-importer-component)
  - [Buttonコンポーネントについて](#aboutbladecomponent)
  - [TALLスタック利用のプロジェクトで使う場合](#in-tall-stack-project)
  - [TALLスタック以外のプロジェクトで使う場合](#in-none-tall-stack-project)
  - [キュー（Queues）の使用](#using-queues)
- [テスト](#test)
- [変更履歴](#changelog)
- [貢献方法](#contribution)
- [セキュリティ](#security)
- [このパッケージのアイデアについて](#ideas)
- [作成者一覧](#credits)
- [ライセンス](#license)

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

<a name=“createbladeview”></a>
### コンポーネントを利用するbladeビューの準備
CSVをインポートする`CSV Importer`コンポーネントはLivewireで作られているため、最初にLivewireが使えるビューファイルを準備します。また、CSVインポートに使用されるパッケージが認証されたユーザによる実行にのみ対応しているため、Laravelのログイン認証機能と併せて使える画面を用意するため、ここでは`laravel/breeze`を利用した例を記載します

(準備中)

<a name=“csv-importer-component”></a>
### CSV Importerコンポーネントについて
CSVファイルをインポートするための`CSV Importer`コンポーネントをbladeファイルに組み込むためには下記のようにします。ここでは`id`, `name`, `email`, `password`のフィールドを持つモデル（例として：YourModel::class）において、バリデーション対象フィールドとして`id`, `name`, `email`を、それぞれの読み込み時のラベルとして”ID”、”名前”、”メアド”、”パスワード”を指定する例を記載しております：

```blade
    <livewire:csv-importer :model="App\Models\YourModel::class"
                            :columns-to-map="['id', 'name', 'email', 'password']"
                            :required-columns="['id', 'name', 'email']"
                            :columns-label="[
                                'id' => 'ID',
                                'name' => '名前',
                                'email' => 'メアド',
                                'password' => 'パスワード',
                            ]"/>
```

| プロパティ  | 型  |  説明  |
|---|---|---|
|  :model |`string` | インポートしたいModelを指定します  |
|  :columns-to-map |`array` | DBテーブル上のカラムをここに書きます |
|  :required-columns |`array` | インポート時にバリデーションするカラムをここに書きます  |
| :columns-label  |`array` |  必須カラムのラベルを記載します  |

<a name=“aboutbladecomponent”></a>
### Buttonコンポーネントについて
Buttonコンポーネントは`CSV Importer`コンポーネントを表示させるのに使われます。このコンポーネントは `alpinejs` を使用しています。このボタンをビューの中で使うにはbladeファイルに `x-csv-button` コンポーネントを下記のように記載します

```blade
<x-csv-button>Import</x-csv-button>
```

 `class` にはTailwindのクラスをアトリビュートとして追加で指定出来ます

```blade
<x-csv-button 
        class="rounded py-2 px-3 bg-indigo-500 ..."
        type="button"
        ....>
    {{ __('Import') }}
</x-csv-button>
```

<a name=“in-tall-stack-project”></a>
### TALLスタック利用のプロジェクトで使う場合
このパッケージを [TALLスタック](https://tallstack.dev/) (Tailwindcss, Alpinejs, Laravel, Livewire) プロジェクトで利用する場合には、下記コマンドを使用することで vendor/views ファイルを出力させて表示をカスタマイズするこが出来ます：

```bash
php artisan vendor:publish --tag="csv-views"
```

利用時には下記コマンドを実行してアセットファイルを出力させましょう：
```bash
npm run dev
```

<a name=“in-none-tall-stack-project”></a>
### TALLスタック以外のプロジェクトで使う場合
TALLスタックを使用していない場合は `csvディレクティブ` を使用することで必要なスタイルシートとスクリプトを読み込めます：

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

<a name=“using-queues”></a>
### キュー（Queues）の使用
このパッケージは [キュー(Laravel queues)](https://laravel.com/docs/9.x/queues#main-content) を使っているので、CSVインポート前に下記のコマンドを実行してLaravelのキューワーカー(queue worker)を利用可能にしておいてください

```sh
php artisan queue:work
```

キューに関する細かな説明は[こちらのLaravel Queues Documentation](https://laravel.com/docs/9.x/queues#main-content) を参考にしてください

<a name=“test></a>
## テスト
```sh
composer test
```

<a name=“changelog”></a>
## 変更履歴
[CHANGELOG](CHANGELOG.md) を見てね

<a name=“contribution”></a>
## 貢献方法
[CONTRIBUTING](https://github.com/ousid/.github/blob/main/CONTRIBUTING.md) を見てね

<a name=“security”></a>
## セキュリティに関して
[our security policy](../../security/policy) を参考に報告してね

<a name=“ideas”></a>
## このパッケージのアイデアについて
このパッケージは[codecourse](https://codecourse.com) で提供されてるコースに登場するLaravelアプリを元にしています。もっと知りたい方はCodecourseの [こちらの動画](https://codecourse.com/subjects/laravel-livewire) を見てください

<a name=“credits”></a>
## 作成者一覧
- [askdkc](https://github.com/askdkc) このリポジトリの作者
- [ousid](https://github.com/ousid) このパッケージを最初に作った人
- [Alex Garrett-Smith](https://codecourse.com/courses/build-a-livewire-csv-importer) [Codecourse](https://codecourse.com/courses/build-a-livewire-csv-importer)でこのアプリの作成動画レッスンを提供した人<br>
このアプリが気に入った人は[Codecourse series](https://codecourse.com)を契約しましょう (どれも素晴らしいです).
- [All Contributors](../../contributors)

<a name=“license”></a>
## ライセンス
The MIT License (MIT)です。 [License File](LICENSE.md)を見てね
