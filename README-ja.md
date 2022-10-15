[![Latest Version on Packagist](https://img.shields.io/packagist/v/askdkc/livewire-csv.svg?style=flat-square)](https://packagist.org/packages/askdkc/livewire-csv)
[![Total Downloads](https://img.shields.io/packagist/dt/askdkc/livewire-csv.svg?style=flat-square)](https://packagist.org/packages/askdkc/livewire-csv)

# Livewire-CSVパッケージについて
- [このパッケージについて](#このパッケージについて)
- [インストール方法](#インストール方法)
- [使用準備](#使用準備)
- [Userモデルにuse HasCsvImportsを追加](#userモデルに-use-hascsvimports-を追加)
- [使い方](#使い方)
  - [コンポーネントを利用するbladeビューの準備](#コンポーネントを利用するbladeビューの準備)
  - [CSV Importerコンポーネントについて](#csv-importerコンポーネントについて)
  - [Buttonコンポーネントについて](#buttonコンポーネントについて)
  - [TALLスタック利用のプロジェクトで使う場合](#tallスタック利用のプロジェクトで使う場合)
  - [TALLスタック以外のプロジェクトで使う場合](#tallスタック以外のプロジェクトで使う場合)
  - [キュー（Queues）の使用](#キューqueuesの使用)
- [テスト](#テスト)
- [変更履歴](#変更履歴)
- [貢献方法](#貢献方法)
- [セキュリティ](#セキュリティに関して)
- [このパッケージのアイデアについて](#このパッケージのアイデアについて)
- [作成者一覧](#作成者一覧)
- [ライセンス](#ライセンス)


## このパッケージについて
__Livewire CSV__ はLaravel [Livewire](https://laravel-livewire.com)を使ってお手軽にCSVをインポート出来るように出来ています。


## インストール方法

下記のようにcomposerを使ってお手軽インストール出来ます:

```sh
composer require askdkc/livewire-csv
```

## 使用準備

`.env`ファイルの修正

ここではお手軽にパッケージを試す`.env`の設定例を記載します。適宜自分の環境に合わせて調整してください

```vim
.envファイル

---before---
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=filex
DB_USERNAME=root
DB_PASSWORD=
------------
↓
---after----
DB_CONNECTION=sqlite
------------

---before---
QUEUE_CONNECTION=sync
------------
↓
---after----
QUEUE_CONNECTION=database
------------
```

CSVインポート機能に必要なDBテーブル用のマイグレーションファイルを次のコマンドで自動生成し、マイグレーションを実行します:

```bash
php artisan vendor:publish --tag="livewire-csv-migrations"
php artisan migrate
```

CSVのインポート時にはLaravelのキュー(queue)機能を使うので、それ用のマイグレーションも以下の手順で実行しておきます:

```bash
php artisan queue:table
php artisan queue:batches-table
php artisan migrate
```
> **注意** <br>
> 既存のプロジェクトに追加時はqueue:tableが既に存在している場合もあるので重複に注意してください
<br>
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

<a name="addtraits"></a>
## Userモデルに `use HasCsvImports` を追加

このパッケージを動作させるためにはUserモデルにHasCsvImportsをインポートして使う必要があります

`app/Models/User.php`を開いて、次のように編集してください:

```php
<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Askdkc\LivewireCsv\Concerns\HasCsvImports; // 追加

（中略）

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasCsvImports; // ここにHasCsvImportsを追加
    
```


## 使い方


### コンポーネントを利用するbladeビューの準備

CSVをインポートする`CSV Importer`コンポーネントはLivewireで作られているため、最初にLivewireが使えるビューファイルを準備します。また、CSVインポートに使用されるパッケージが認証されたユーザによる実行にのみ対応しているため、Laravelのログイン認証機能と併せて使える画面を用意するため、ここでは`laravel/breeze`を利用した例を記載します

下記のコマンドでLaravel Breezeをインストール

```bash
composer require laravel/breeze --dev
php artisan breeze:install
```

今回のビューで使うモデル（Post）も作っておきます
```bash
php artisan make:model -m Post
```

エディターで次のファイルを編集します
```vim
resources/views/layouts/app.blade.php

(13行目付近)
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles //追加
  
（中略）
  
(33行目付近)
          </main>
      </div>
      @livewireScripts //追加
  </body>
```

もう一つ

```vim
resources/views/dashboard.blade.php

(12行目付近)
    You're logged in! //　これを消す
    <x-csv-button>Import</x-csv-button> // 代わりにこれを追加

（中略）
  
(16行目付近)
    </div>
    // この下を追加
    <livewire:csv-importer :model="App\Models\Post::class"
                            :columns-to-map="['title', 'body']"
                            :required-columns="['title', 'body']"
                            :column-labels="[
                                'title' => 'タイトル',
                                'body' => '本文',
                            ]" />
    // ここまで
</x-app-layout>
```

上記でCSV Importerコンポーネントで利用するPostモデルは次のように準備しておきます
```vim
app/Models/Post.php
---before---
class Post extends Model
{
    use HasFactory;
}
------------
↓
---after---
class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'body']; // 追加
}
-----------
```

マイグレーションファイルも準備

```vim
database/migrations/yyyy_mm_dd_hhmmss_create_posts_table.php

---before---
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });
}
------------
↓
---after---
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // 追加
        $table->text('body'); // 追加
        $table->timestamps();
    });
}
-----------
```

マイグレーションを実行します

```bash
php artisan migrate
```

Viteを起動

```bash
npm run dev
```

さらに別のTerminalでLaravel起動

```bash
php artisan serve
```

さらに別のTerminalでLaravelのキューを稼働

```
php artisan queue:work
```

ブラウザで下記にアクセスします
http://localhost:8000
<br><br>
右上のRegisterからユーザ登録します
<img width="1253" alt="image" src="https://user-images.githubusercontent.com/7894265/194009152-a6463e3a-9dd8-4505-b9a5-f7653f89011e.png">
<br><br>
この辺は適当に入力
<img width="1253" alt="image" src="https://user-images.githubusercontent.com/7894265/194009290-b41db021-469f-4024-bb02-7f774997c3a0.png">
<br><br>
インポートをクリックします
<img width="1288" alt="image" src="https://user-images.githubusercontent.com/7894265/194009470-e3a829a0-187e-48eb-a00f-b26382ae9ab6.png">
<br><br>
右側からニョッキりLivewireのインポート用CSV Importerコンポーネントが顔を出します👀
<img width="1288" alt="image" src="https://user-images.githubusercontent.com/7894265/194009938-c9aabfe6-616d-4551-8259-265328da98ea.png">
<br><br>
こんな感じでファイルをドラッグ＆ドロップして項目を指定し、Importボタンをクリックします
<img width="1288" alt="image" src="https://user-images.githubusercontent.com/7894265/194011805-d46db40e-e994-4de9-a98c-fa3880ab3a41.png">
<br><br>
データが読み込まれます。大量のデータでも捌いてくれます👍


### CSV Importerコンポーネントについて
CSVファイルをインポートするための`CSV Importer`コンポーネントをbladeファイルに組み込むためには下記のようにします。ここでは`id`, `name`, `email`のフィールドを持つモデル（例として：YourModel::class）において、バリデーション対象フィールドとして`id`, `name`, `email`を、それぞれの読み込み時のラベルとして”ID”、”名前”、”メアド”を指定する例を記載しております：

```blade
    <livewire:csv-importer :model="App\Models\YourModel::class"
                            :columns-to-map="['id', 'name', 'email']"
                            :required-columns="['id', 'name', 'email']"
                            :columns-label="[
                                'id' => 'ID',
                                'name' => '名前',
                                'email' => 'メアド',
                            ]"/>
```

| プロパティ  | 型  | 説明                                                    |
|---|---|-------------------------------------------------------|
|  :model |`string` | インポートしたいModelをフルパスで指定します                              |
|  :columns-to-map |`array` | DBテーブル上のカラムをここに書きます                                   |
|  :required-columns |`array` | インポート時にバリデーションするカラムをここに書きます（columns-to-mapと同様にしてください） |
| :columns-label  |`array` | 必須カラムのラベルを記載します                                       |

>**備考：** 既に登録があるデータIDのデータをアップロードすると、対象データは上書き更新され、IDの未登録なデータは新規追加されます(Upsert)

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


### TALLスタック利用のプロジェクトで使う場合
このパッケージを [TALLスタック](https://tallstack.dev/) (Tailwindcss, Alpinejs, Laravel, Livewire) プロジェクトで利用する場合には、下記コマンドを使用することで vendor/views ファイルを出力させて表示をカスタマイズするこが出来ます：

```bash
php artisan vendor:publish --tag="csv-views"
```

利用時には下記コマンドを実行してアセットファイルを出力させましょう：
```bash
npm run dev
```


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


### キュー（Queues）の使用
このパッケージは [キュー(Laravel queues)](https://laravel.com/docs/9.x/queues#main-content) を使っているので、CSVインポート前に下記のコマンドを実行してLaravelのキューワーカー(queue worker)を利用可能にしておいてください

```sh
php artisan queue:work
```

キューに関する細かな説明は[こちらのLaravel Queues Documentation](https://laravel.com/docs/9.x/queues#main-content) を参考にしてください


## テスト
```sh
composer test
```


## 変更履歴
[CHANGELOG](CHANGELOG.md) を見てね


## 貢献方法
[CONTRIBUTING](https://github.com/ousid/.github/blob/main/CONTRIBUTING.md) を見てね


## セキュリティに関して
[our security policy](../../security/policy) を参考に報告してね


## このパッケージのアイデアについて
このパッケージは[codecourse](https://codecourse.com) で提供されてるコースに登場するLaravelアプリを元にしています。もっと知りたい方はCodecourseの [こちらの動画](https://codecourse.com/subjects/laravel-livewire) を見てください


## 作成者一覧
- [askdkc](https://github.com/askdkc) このリポジトリの作者
- [ousid](https://github.com/ousid) このパッケージを最初に作った人
- [Alex Garrett-Smith](https://codecourse.com/courses/build-a-livewire-csv-importer) [Codecourse](https://codecourse.com/courses/build-a-livewire-csv-importer)でこのアプリの作成動画レッスンを提供した人<br>
このアプリが気に入った人は[Codecourse series](https://codecourse.com)を契約しましょう (どれも素晴らしいです).
- [All Contributors](../../contributors)


## ライセンス
The MIT License (MIT)です。 [License File](LICENSE.md)を見てね
