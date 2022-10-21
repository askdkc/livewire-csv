<?php

namespace Askdkc\LivewireCsv\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class LiveSetupCommand extends Command
{
    protected $signature = 'livecsv-setup';

    protected $description = 'Run Livewire-Csv initial set up';

    public function handle(): void
    {
        $this->info('Preparing Livewire-CSV necessary migrations / 必要なマイグレーションを準備します');
        $this->comment('Publishing migration... / マイグレーションファイル準備中');

        if(!$this->migrationExists('create_jobs_table')) {
            Artisan::call("queue:table");
            $this->comment('Migration Jobs created successfully / Jobsテーブル作成');
        }

        if(!$this->migrationExists('create_job_batches_table')) {
            Artisan::call("queue:batches-table");
            $this->comment('Migration Job Batches created successfully / Job Batches テーブル作成');
        }

        if(!$this->migrationExists('create_csv_imports_table')) {
            $this->callSilently("vendor:publish", [
                '--tag' => "livewire-csv-migrations",
            ]);
            $this->comment('Migration Csv Imports created successfully / Csv Importsテーブル作成');
        }

        $this->comment('Adding Japanese Validation file... / 日本語のバリデーションファイルを作成します');
        (new Filesystem)->ensureDirectoryExists(lang_path());
        (new Filesystem)->copyDirectory(__DIR__.'/../../resources/lang/stub', lang_path());

        $this->info("Done! / 完了!");

        if ($this->confirm('Would you like to run the migrations now? / マイグレーションを実行しますか?')) {
            $this->comment('Running migrations... / 実行中...');

            $this->call('migrate');
        }

        if ($this->confirm("Would you like to star our repo on GitHub? \n GitHubリポジトリにスターの御協力をお願いします🙏")) {
            $repoUrl = "https://github.com/askdkc/livewire-csv";

            if (PHP_OS_FAMILY == 'Darwin') {
                exec("open {$repoUrl}");
            }
            if (PHP_OS_FAMILY == 'Windows') {
                exec("start {$repoUrl}");
            }
            if (PHP_OS_FAMILY == 'Linux') {
                exec("xdg-open {$repoUrl}");
            }
        }
    }

    private function migrationExists(string $filename): Bool
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
