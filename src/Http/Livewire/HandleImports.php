<?php

namespace Askdkc\LivewireCsv\Http\Livewire;

use Askdkc\LivewireCsv\Models\Import;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User;
use Livewire\Component;

use function Askdkc\LivewireCsv\csv_view_path;

class HandleImports extends Component
{
    /** @var string */
    public $model;

    /** @var array */
    protected $listeners = [
        'imports.refresh' => '$refresh',
    ];

    public function mount(string $model): void
    {
        $this->model = $model;
    }

    public function getImportsProperty(): Collection
    {
        /** @var User */
        $user = auth()->user();

        return Import::query()
            ->forModel($this->model)
            ->forUser($user->id)
            ->oldest()
            ->unCompleted()
            ->get();
    }

    public function render(): View|Factory
    {
        return view(
            csv_view_path('handle-imports')
        );
    }
}
