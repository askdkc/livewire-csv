<?php

namespace Askdkc\LivewireCsv\Concerns;

trait InteractsWithColumns
{
    /**
     * Converts the columnsToMap property into an associative array.
     *
     * @return array
     */
    protected function mapThroughColumns(): array
    {
        if (! $this->columnsToMap) {
            return [];
        }

        return collect($this->columnsToMap)
                ->mapWithKeys(fn ($column): array => [$column => ''])
                ->toArray();
    }

    /**
     * Maps requiredColumns property into columnsToMap required state.
     *
     * @return array
     */
    protected function mapThroughRequiredColumns(): array
    {
        if (! $this->requiredColumns) {
            return [];
        }

        return collect($this->requiredColumns)
            ->mapWithKeys(function ($column): array {
                return ['columnsToMap.'.$column => 'required'];
            })->toArray();
    }

}
