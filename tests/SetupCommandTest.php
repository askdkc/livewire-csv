<?php

it('runs setup command without error', function () {

    $this->artisan('livecsv-setup')
        ->expectsOutput('Preparing Livewire-CSV necessary migrations / 必要なマイグレーションを準備します')
        ->expectsOutput('Publishing migration... / マイグレーションファイル準備中')
        ->expectsConfirmation('Would you like to set your locale to Japanese? / 言語を日本語にしますか?', false)
        ->expectsConfirmation('Would you like to run the migrations now? / マイグレーションを実行しますか?', 'no')
        ->expectsOutput('Done! / 完了!')
        ->expectsConfirmation("Would you like to star our repo on GitHub? \n GitHubリポジトリにスターの御協力をお願いします🙏", 'no')
        ->assertExitCode(0);
});

it('sees migration files but not Japanese Translation files after running setup command', function () {

    $this->artisan('livecsv-setup')
        ->expectsOutput('Preparing Livewire-CSV necessary migrations / 必要なマイグレーションを準備します')
        ->expectsOutput('Publishing migration... / マイグレーションファイル準備中')
        ->expectsConfirmation('Would you like to set your locale to Japanese? / 言語を日本語にしますか?', false)
        ->expectsConfirmation('Would you like to run the migrations now? / マイグレーションを実行しますか?', 'no')
        ->expectsOutput('Done! / 完了!')
        ->expectsConfirmation("Would you like to star our repo on GitHub? \n GitHubリポジトリにスターの御協力をお願いします🙏", 'no')
        ->assertExitCode(0);

    $this->assertTrue($this->migrationExists('csv_imports_table'));
    $this->assertTrue($this->migrationExists('create_jobs_table'));
    $this->assertTrue($this->migrationExists('create_csv_imports_table'));
    $this->assertFileExists(config_path('livewire_csv.php'));
});

it('sees migration files and Japanese Translation files after running setup command', function () {

    $this->artisan('livecsv-setup')
        ->expectsOutput('Preparing Livewire-CSV necessary migrations / 必要なマイグレーションを準備します')
        ->expectsOutput('Publishing migration... / マイグレーションファイル準備中')
        ->expectsConfirmation('Would you like to set your locale to Japanese? / 言語を日本語にしますか?', 'yes')
        ->expectsConfirmation('Would you like to run the migrations now? / マイグレーションを実行しますか?', 'no')
        ->expectsOutput('Done! / 完了!')
        ->expectsConfirmation("Would you like to star our repo on GitHub? \n GitHubリポジトリにスターの御協力をお願いします🙏", 'no')
        ->assertExitCode(0);

    $this->assertTrue($this->migrationExists('csv_imports_table'));
    $this->assertTrue($this->migrationExists('create_jobs_table'));
    $this->assertTrue($this->migrationExists('create_csv_imports_table'));
    $this->assertFileExists(config_path('livewire_csv.php'));
});
