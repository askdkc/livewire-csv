<?php

namespace Askdkc\LivewireCsv\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class LiveSetupCommand extends Command
{
    protected $signature = 'livecsv-setup';

    protected $description = 'Run Livewire-Csv initial set up';

    public function handle(): void
    {
        $this->info('Preparing Livewire-CSV necessary migrations / 必要なマイグレーションを準備します');
        $this->comment('Publishing migration... / マイグレーションファイル準備中');

        if (! $this->migrationExists('create_jobs_table')) {
            Artisan::call('queue:table');
            $this->comment('Migration Jobs created successfully / Jobsテーブル作成');
        }

        if (! $this->migrationExists('create_job_batches_table')) {
            Artisan::call('queue:batches-table');
            $this->comment('Migration Job Batches created successfully / Job Batches テーブル作成');
        }

        if (! $this->migrationExists('create_csv_imports_table')) {
            $this->callSilently('vendor:publish', [
                '--tag' => 'livewire-csv-migrations',
            ]);
            $this->comment('Migration Csv Imports created successfully / Csv Importsテーブル作成');
        }

        if ($this->confirm('Would you like to set your locale to Japanese? / 言語を日本語にしますか?')) {
            $this->info('config/app.phpのlocaleをjaにします');
            // Read the contents of the file into a string
            $configfile = file_get_contents(base_path('config/app.php'));

            // Modify the contents of the string
            $configfile = str_replace("'locale' => 'en'", "'locale' => 'ja'", $configfile);
            $configfile = str_replace("'faker_locale' => 'en_US'", "'faker_locale' => 'ja_JP'", $configfile);

            // Save the modified contents back to the file
            file_put_contents(base_path('config/app.php'), $configfile);
        }

        $this->comment('Publishing Config file... / 設定ファイルを出力します');
        $this->call('vendor:publish', [
            '--tag' => 'livewire-csv-config',
        ]);

        $this->info('Done! / 完了!');

        if ($this->confirm('Would you like to run the migrations now? / マイグレーションを実行しますか?')) {
            $this->comment('Running migrations... / 実行中...');

            $this->call('migrate');
        }

        if ($this->confirm("Would you like to star our repo on GitHub? \n GitHubリポジトリにスターの御協力をお願いします🙏", true)) {
            $repoUrl = 'https://github.com/askdkc/livewire-csv';

            if (PHP_OS_FAMILY == 'Darwin') {
                exec("open {$repoUrl}");
            }
            if (PHP_OS_FAMILY == 'Windows') {
                exec("start {$repoUrl}");
            }
            if (PHP_OS_FAMILY == 'Linux') {
                exec("xdg-open {$repoUrl}");
            }

            $this->line('Thank you! / ありがとう💓');
        }
    }

    private function migrationExists(string $filename): bool
    {
        $path = database_path('migrations/');
        $files = scandir($path);
        $pos = false;
        foreach ($files as $value) {
            $pos = strpos($value, $filename);
            if ($pos !== false) {
                return true;
            }
        }

        return false;
    }
}
