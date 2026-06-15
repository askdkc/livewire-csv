<button
    {{ $attributes }}
    x-data
    x-on:click="Livewire.dispatchTo('csv-importer', 'toggle')">
    {{ $slot }}
</button>
