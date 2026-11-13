@php
    $euros = fn (float $amount): string => number_format($amount, 2, ',', ' ') . ' €';
    $statusLabels = [
        \App\Enum\OrderStatusEnum::OPEN->value => 'Ouverte',
        \App\Enum\OrderStatusEnum::IN_PREPARATION->value => 'En préparation',
        \App\Enum\OrderStatusEnum::SHIPPED->value => 'Expédiée',
    ];
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Informations du client --}}
        <x-filament::section>
            <h2 class="text-base font-semibold leading-6 text-gray-950">Informations client</h2>
            <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nom</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $record->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $record->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Dernière connexion</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $record->logged_at ? $record->logged_at->format('d/m/Y H:i') : 'Jamais connecté' }}
                    </dd>
                </div>
            </div>
        </x-filament::section>

        {{-- Chiffres clés (toutes les commandes non supprimées, montants HT) --}}
        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));">
            @foreach ([
                'Commandes' => number_format($summary['orders'], 0, ',', ' '),
                'CA HT total' => $euros($summary['revenue']),
                'Panier moyen HT' => $euros($summary['average_order']),
                'Dernière commande' => $lastOrder?->created_at->format('d/m/Y') ?? 'Aucune',
            ] as $label => $value)
                <x-filament::section>
                    <div class="text-sm font-medium text-gray-500">{{ $label }}</div>
                    <div class="whitespace-nowrap text-xl font-semibold tracking-tight">{{ $value }}</div>
                </x-filament::section>
            @endforeach
        </div>

        {{-- Évolution mensuelle depuis la première commande --}}
        @livewire(\App\Livewire\Admin\SalesChart::class, ['filters' => $chartFilters], key('customer-sales-chart-' . $record->id))

        {{-- Répartition par marque puis par catégorie --}}
        @foreach (['Marque' => $brandStats, 'Catégorie' => $categoryStats] as $dimension => $rows)
            <x-filament::section>
                <h2 class="text-base font-semibold leading-6 text-gray-950">Commandes par {{ mb_strtolower($dimension) }}</h2>

                @if ($rows->isEmpty())
                    <p class="text-sm text-gray-500">Aucune donnée disponible.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-gray-500">
                                    <th class="py-2 text-left">{{ $dimension }}</th>
                                    <th class="py-2 text-center">Commandes</th>
                                    <th class="py-2 text-center">Produits</th>
                                    <th class="py-2 text-center">Quantité</th>
                                    <th class="py-2 text-right">CA HT</th>
                                    <th class="py-2 text-right">Moy. / commande</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    <tr class="border-b">
                                        <td class="py-2 font-medium text-gray-900">{{ $row->label }}</td>
                                        <td class="py-2 text-center">{{ $row->orders_count }}</td>
                                        <td class="py-2 text-center">{{ $row->products_count }}</td>
                                        <td class="py-2 text-center">{{ number_format((int) $row->quantity, 0, ',', ' ') }}</td>
                                        <td class="py-2 text-right font-medium">{{ $euros($row->revenue / 100) }}</td>
                                        <td class="py-2 text-right">{{ $euros($row->revenue / 100 / max(1, $row->orders_count)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-filament::section>
        @endforeach

        {{-- Liste des commandes --}}
        <x-filament::section>
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold leading-6 text-gray-950">Toutes les commandes</h2>
                <span class="text-sm text-gray-500">{{ $orders->count() }} commande(s)</span>
            </div>

            @if ($orders->isEmpty())
                <p class="text-sm text-gray-500">Aucune commande pour ce client.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Référence</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Statut</th>
                                <th class="px-4 py-3 text-right">Produits</th>
                                <th class="px-4 py-3 text-right">Quantité</th>
                                <th class="px-4 py-3 text-right">Total HT</th>
                                <th class="px-4 py-3 text-right">Total TVAC</th>
                                <th class="px-4 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($orders as $order)
                                @php($lines = $orderLines->get($order->id))
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $order->reference }}</div>
                                        @if ($order->tracking_number)
                                            <div class="text-xs text-gray-500">Suivi : {{ $order->tracking_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $statusLabels[$order->status->value] ?? $order->status->value }}</td>
                                    <td class="px-4 py-3 text-right">{{ $lines->products ?? 0 }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format((int) ($lines->quantity ?? 0), 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right">{{ $euros($order->total_price) }}</td>
                                    <td class="px-4 py-3 text-right font-medium">{{ $euros($order->total_price_with_tax) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <a href="{{ \App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $order]) }}"
                                                class="text-primary-600 hover:underline" target="_blank">Détail</a>
                                            <a href="{{ route('user_orders.pdf', ['order' => $order]) }}"
                                                class="text-danger-600 hover:underline" target="_blank">PDF</a>
                                            @if ($order->tracking_url)
                                                <a href="{{ $order->tracking_url }}" class="text-success-600 hover:underline" target="_blank">Suivi</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
