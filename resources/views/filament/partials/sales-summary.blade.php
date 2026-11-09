@php
    /** @var array{revenue: float, quantity: int, orders: int, customers: int, average_order: float} $summary */
    $euros = fn (float $amount): string => number_format($amount, 2, ',', ' ') . ' €';
    $cards = [
        'CA HT' => $euros($summary['revenue']),
        'Quantité vendue' => number_format($summary['quantity'], 0, ',', ' '),
        'Commandes' => number_format($summary['orders'], 0, ',', ' '),
        'Clients' => number_format($summary['customers'], 0, ',', ' '),
        'Panier moyen HT' => $euros($summary['average_order']),
    ];
@endphp

<div class="grid grid-cols-2 gap-4 md:grid-cols-5">
    @foreach ($cards as $label => $value)
        <x-filament::card>
            <div class="text-sm font-medium text-gray-500">{{ $label }}</div>
            <div class="whitespace-nowrap text-xl font-semibold tracking-tight">{{ $value }}</div>
        </x-filament::card>
    @endforeach
</div>
