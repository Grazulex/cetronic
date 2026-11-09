<x-filament::page>
    <x-filament::card>
        {{ $this->form }}
    </x-filament::card>

    @include('filament.partials.sales-summary', ['summary' => $summary])

    @livewire(\App\Http\Livewire\Admin\SalesChart::class, ['filters' => $chartFilters], key('sales-chart-' . md5(json_encode($chartFilters))))

    {{ $this->table }}
</x-filament::page>
