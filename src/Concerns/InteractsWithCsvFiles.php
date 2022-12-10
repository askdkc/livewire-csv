<?php

namespace Askdkc\LivewireCsv\Concerns;

use League\Csv\Reader;

trait InteractsWithCsvFiles
{
    /**
     * Read CSV File.
     *
     * @param  string  $path
     * @return Reader
     */
    protected function readCSV(string $path): Reader
    {
        $stream = fopen($path, 'r');
        $csv = Reader::createFromStream($stream);

        $csv->setHeaderOffset(0)
            ->skipEmptyRecords();

        // Check File Type
        if(config('livewire_csv.file_type') === 'tsv') {
            $csv->setDelimiter("\t");
        }

        return $csv;
    }
}
