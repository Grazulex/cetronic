<div class="space-y-6">
    <div class="flex items-center justify-end gap-2">
        <label for="item-sales-period" class="text-sm font-medium text-gray-700">Période</label>
        <select id="item-sales-period" wire:model.live="period"
            class="block h-10 rounded-lg border-gray-300 text-gray-900 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500">
            @foreach ($periods as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    @include('filament.partials.sales-summary', ['summary' => $summary])

    @livewire(\App\Livewire\Admin\SalesChart::class, ['filters' => $chartFilters], key('item-sales-chart-' . $itemId . '-' . $period))

    <x-filament::section>
        <h2 class="text-base font-semibold leading-6 text-gray-950">Meilleurs clients</h2>

        @if ($topCustomers->isEmpty())
            <p class="text-sm text-gray-500">Aucune vente sur cette période.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-500">
                        <th class="py-2">Client</th>
                        <th class="py-2 text-right">Quantité</th>
                        <th class="py-2 text-right">Commandes</th>
                        <th class="py-2 text-right">CA HT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topCustomers as $customer)
                        <tr class="border-b">
                            <td class="py-2">
                                @if (\App\Enum\UserRoleEnum::CUSTOMER->value === $customer->role)
                                    <a href="{{ \App\Filament\Resources\UserResource::getUrl('customer-stats', ['record' => $customer->id]) }}"
                                        class="text-primary-600 hover:underline">{{ $customer->name }}</a>
                                @else
                                    {{ $customer->name }}
                                @endif
                            </td>
                            <td class="py-2 text-right">{{ number_format($customer->quantity, 0, ',', ' ') }}</td>
                            <td class="py-2 text-right">{{ $customer->orders_count }}</td>
                            <td class="py-2 text-right">{{ number_format($customer->revenue, 2, ',', ' ') }} €</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</div>
