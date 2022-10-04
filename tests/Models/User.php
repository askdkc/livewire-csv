<?php

namespace Askdkc\LivewireCsv\Tests\Models;

use Askdkc\LivewireCsv\Concerns\HasCsvImports;
use Askdkc\LivewireCsv\Tests\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasFactory;
    use HasCsvImports;

    protected $guarded = [];

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
