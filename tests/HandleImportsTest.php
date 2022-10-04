<?php

use Askdkc\LivewireCsv\Http\Livewire\HandleImports;
use Askdkc\LivewireCsv\Tests\Models\User;
use function Pest\Livewire\livewire;

it('renders handle imports component with model', function () {
    $this->actingAs(User::factory()->create());

    $model = Customer::class;

    livewire(HandleImports::class, [
        'model' => $model,
    ])
        ->assertSet('model', $model)
        ->assertSuccessful();
});
