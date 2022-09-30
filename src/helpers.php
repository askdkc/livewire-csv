<?php

namespace Askdkc\LivewireCsv;

if (! function_exists('Askdkc\LivewireCsv\csv_view_path')) {
    /**
     * Get the evaluated view content from the livewire view
     *
     * @param  string|null  $view
     * @return string
     */
    function csv_view_path(string|null $view): string
    {
        return 'livewire-csv::livewire.'.config('livewire_csv.layout').'.'.$view;
    }
}
