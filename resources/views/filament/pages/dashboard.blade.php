@php
    $euros = fn (float|int $amount): string => \Illuminate\Support\Number::currency((float) $amount, 'EUR', 'fr');
    $card = 'background:#faf6f0; border-radius:.6rem; padding:.85rem 1rem;';
    $box = 'border:1px solid #eee5da; border-radius:.6rem; padding:.9rem 1.1rem; background:#fff;';
    $boxTitle = 'font-size:.68rem; text-transform:uppercase; letter-spacing:.08em; color:#7d746a; font-weight:600; margin-bottom:.6rem;';
    $row = 'display:flex; justify-content:space-between; gap:.75rem; padding:.35rem 0; border-bottom:1px solid #f3ede5; font-size:.85rem;';
@endphp

<x-filament-panels::page>
    @if (! $isAdmin)
        <p class="text-sm" style="color:#8a8178;">Utilisez le menu pour accéder à vos clients et à vos catalogues.</p>
    @else
        {{-- Month navigation --}}
        <div style="display:flex; gap:.5rem; flex-wrap:wrap; margin-top:-.5rem;">
            <x-filament::button color="gray" size="sm" tag="a" :href="'?mois=' . $period->previousMonth()" icon="heroicon-m-chevron-left">
                {{ \Illuminate\Support\Str::ucfirst($period->previousFrom->copy()->locale('fr')->translatedFormat('F')) }}
            </x-filament::button>
            @if ($period->nextMonth())
                <x-filament::button color="gray" size="sm" tag="a" :href="'?mois=' . $period->nextMonth()" icon="heroicon-m-chevron-right" icon-position="after">
                    {{ \Illuminate\Support\Str::ucfirst($period->from->copy()->addMonthNoOverflow()->locale('fr')->translatedFormat('F')) }}
                </x-filament::button>
                <x-filament::button size="sm" tag="a" href="?">Ce mois</x-filament::button>
            @endif
        </div>

        {{-- À traiter --}}
        @if ($toActivate > 0)
            <div>
                <a href="{{ $toActivateUrl }}" style="display:inline-block; border:1px solid #111; border-radius:999px; padding:.3rem .9rem; font-size:.85rem;">
                    {{ $toActivate }} {{ $toActivate > 1 ? 'clients' : 'client' }} à activer →
                </a>
            </div>
        @endif

        @php
            $kpis = [
                ['label' => 'CA HT', 'value' => $euros($current['revenue']), 'change' => $changes['revenue'],
                    'extra' => $changes['revenue_last_year'] ? (($changes['revenue_last_year']['up'] ? '▲ ' : '▼ ') . $changes['revenue_last_year']['percent'] . ' % vs ' . $lastYearLabel) : null],
                ['label' => 'Commandes', 'value' => (string) $current['orders'], 'change' => $changes['orders'], 'extra' => null],
                ['label' => 'Panier moyen HT', 'value' => $euros($current['average_order']), 'change' => $changes['average_order'], 'extra' => null],
                ['label' => 'Clients qui ont commandé', 'value' => (string) $current['customers'], 'change' => $changes['customers'],
                    'extra' => 'dont ' . $newCustomers . ' ' . ($newCustomers > 1 ? 'nouveaux' : 'nouveau')],
            ];
        @endphp

        {{-- Key figures --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(12rem, 1fr)); gap:.75rem;">
            @foreach ($kpis as $kpi)
                <div style="{{ $card }}">
                    <div style="font-size:.68rem; text-transform:uppercase; letter-spacing:.07em; color:#7d746a;">{{ $kpi['label'] }}</div>
                    <div style="font-size:1.5rem; font-weight:500; margin:.25rem 0 .2rem;">{{ $kpi['value'] }}</div>
                    <div style="font-size:.75rem;">
                        @if ($kpi['change'])
                            <span style="color: {{ $kpi['change']['up'] ? '#2f6b45' : '#9b3b2f' }};">
                                {{ $kpi['change']['up'] ? '▲' : '▼' }} {{ $kpi['change']['percent'] }} %
                            </span>
                            <span style="color:#8a8178;">vs {{ $period->previousFrom->copy()->locale('fr')->translatedFormat('F') }}</span>
                        @else
                            <span style="color:#8a8178;">—</span>
                        @endif
                        @if ($kpi['extra'])
                            <div style="color:#8a8178;">{{ $kpi['extra'] }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Chart and top brands --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(20rem, 1fr)); gap:.75rem;">
            <div style="grid-column: span 2 / span 2; min-width:0;">
                @livewire(\App\Livewire\Admin\CumulativeRevenueChart::class, ['month' => $period->from->format('Y-m')], key('cumulative-' . $period->from->format('Y-m')))
            </div>
            <div style="{{ $box }}">
                <div style="{{ $boxTitle }}">Top marques du mois</div>
                @php($maxBrand = (int) ($topBrands->first()->revenue ?? 0))
                @forelse ($topBrands as $brand)
                    <div style="{{ $row }} border-bottom:none; padding-bottom:.1rem;">
                        <span>{{ $brand->label }}</span><span>{{ $euros(((int) $brand->revenue) / 100) }}</span>
                    </div>
                    <div style="height:4px; border-radius:2px; background:#111; width: {{ $maxBrand > 0 ? max(4, (int) round(((int) $brand->revenue) / $maxBrand * 100)) : 0 }}%; margin-bottom:.35rem;"></div>
                @empty
                    <p class="text-sm" style="color:#8a8178;">Aucune vente ce mois-ci.</p>
                @endforelse
            </div>
        </div>

        {{-- Latest orders and top products --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(20rem, 1fr)); gap:.75rem;">
            <div style="{{ $box }}">
                <div style="{{ $boxTitle }}">Dernières commandes</div>
                @forelse ($latestOrders as $order)
                    <a href="{{ $orderUrl($order) }}" style="{{ $row }}">
                        <span><strong>{{ $order->reference }}</strong> · {{ $order->user?->name ?? '—' }}</span>
                        <span style="color:#8a8178; white-space:nowrap;">{{ $order->created_at?->format('d/m H:i') }}</span>
                        <span style="white-space:nowrap;">{{ $euros((float) $order->total_price) }}</span>
                    </a>
                @empty
                    <p class="text-sm" style="color:#8a8178;">Aucune commande ce mois-ci.</p>
                @endforelse
            </div>
            <div style="{{ $box }}">
                <div style="{{ $boxTitle }}">Top produits du mois</div>
                @forelse ($topProducts as $product)
                    <div style="{{ $row }}">
                        <span><strong>{{ $product->label }}</strong> · {{ $product->brand_name }}</span>
                        <span style="white-space:nowrap;">{{ (int) $product->quantity }} pcs</span>
                    </div>
                @empty
                    <p class="text-sm" style="color:#8a8178;">Aucune vente ce mois-ci.</p>
                @endforelse
            </div>
        </div>
    @endif
</x-filament-panels::page>
