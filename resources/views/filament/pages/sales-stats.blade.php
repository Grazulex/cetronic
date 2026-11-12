<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    @include('filament.partials.sales-summary', ['summary' => $summary])

    @livewire(\App\Livewire\Admin\SalesChart::class, ['filters' => $chartFilters], key('sales-chart-' . md5(json_encode($chartFilters))))

    {{ $this->table }}
</x-filament-panels::page>
