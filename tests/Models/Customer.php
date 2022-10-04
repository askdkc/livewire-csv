<?php

namespace Askdkc\LivewireCsv\Tests\Models;

use Askdkc\LivewireCsv\Tests\Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return CustomerFactory::new();
    }
}
