<?php

namespace Askdkc\LivewireCsv;

class LivewireCsvManager
{
    /**
     * Get the given size and formated it.
     *
     * @param  int  $size
     * @param  int  $precision
     * @return string|int
     */
    public function formatFileSize(int $size, int $precision = 2): string|int
    {
        if ($size <= 0) {
            return $size;
        }

        $base = log((int) $size) / log(1024);
        $suffixes = ['KB', 'MB', 'GB', 'TB'];

        return round(
            pow(1024, $base - floor($base)), $precision
        ).$suffixes[floor($base)];
    }
}
