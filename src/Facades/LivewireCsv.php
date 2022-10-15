<?php

namespace Askdkc\LivewireCsv\Facades;

use Illuminate\Support\Facades\Facade;

class LivewireCsv extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'livewire-csv';
    }
}

