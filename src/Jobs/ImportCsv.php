<?php

namespace Askdkc\LivewireCsv\Jobs;

use Askdkc\LivewireCsv\Models\Import;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ImportCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Batchable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Import $import,
        public string $model,
        public array $chunk,
        public array $columns,
        public array $upsertColumns,
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $importData = [];

        // swap user specified csv data fields to actual database column names
        foreach($this->chunk as $data)
        {
            $temprow = new Collection();
            foreach ($this->columns as $key => $value)
            {
                $temprow->push([$key => $data[$value] ?? null]);
            }
            $importData[] = $temprow->collapse()->toArray();
        }

        $affectedRows = $this->model::upsert(
            $importData,
            $this->upsertColumns,
            collect($this->columns)->diff(['id'])->keys()->toArray(),
        );

        $this->import->increment('processed_rows', $affectedRows);
    }
}
