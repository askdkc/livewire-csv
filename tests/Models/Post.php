<?php

namespace Askdkc\LivewireCsv\Tests\Models;

use Askdkc\LivewireCsv\Tests\Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return PostFactory::new();
    }
}
