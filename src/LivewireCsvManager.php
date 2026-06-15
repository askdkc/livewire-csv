<?php

namespace Askdkc\LivewireCsv;

class LivewireCsvManager
{
    /**
     * Get the given size and formated it.
     */
    public function formatFileSize(int $size, int $precision = 2): string|int
    {
        if ($size <= 0) {
            return $size;
        }

        $base = (int) floor(log($size) / log(1024));
        $suffixes = ['KB', 'MB', 'GB', 'TB'];

        return round(
            pow(1024, (log($size) / log(1024)) - $base), $precision
        ).$suffixes[$base];
    }
}
