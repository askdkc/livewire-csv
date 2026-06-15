<?php

namespace Askdkc\LivewireCsv\Concerns;

use Askdkc\LivewireCsv\Models\Import;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasCsvImports
{
    /**
     * Has imports relationship
     *
     * @return HasMany<Import, $this>
     */
    public function imports(): HasMany
    {
        return $this->hasMany(Import::class, 'user_id');
    }
}
