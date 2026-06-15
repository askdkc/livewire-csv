<?php

namespace Askdkc\LivewireCsv;

class LivewireCsvDirectives
{
    /**
     * Get CSV Styles
     */
    public static function csvStyles(): ?string
    {
        if (config('livewire_csv.layout') == 'tailwindcss') {
            return self::getTailwindStyle();
        }

        return self::getTailwindStyle();
    }

    /**
     * Get CSV Scripts
     */
    public static function csvScripts(): string
    {
        return <<<'HTML'
            <script src="{{ asset('vendor/csv/js/app.js') }}"></script>
        HTML;
    }

    /**
     * Get Tailwind Style Path
     */
    protected static function getTailwindStyle(): string
    {
        return <<<'HTML'
                <link href="{{ asset('vendor/csv/css/tailwind.css') }}" rel="stylesheet"></link>
        HTML;
    }
}
