<x-filament::page>
    <div class="space-y-6">
        <!-- Informations du client -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informations Client</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
        </div>

        <!-- Statistiques des commandes -->
        @php
            $orders = $record->orders()->whereIn('status', [\App\Enum\OrderStatusEnum::SHIPPED, \App\Enum\OrderStatusEnum::OPEN])->get();
            $totalOrders = $orders->count();
            $totalAmount = $orders->sum('total_price_with_tax') / 100;
            $avgOrderAmount = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;
            
            // Statistiques par mois (derniers 6 mois)
            $monthlyStats = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $monthOrders = $record->orders()
                    ->where('status', \App\Enum\OrderStatusEnum::SHIPPED)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->get();
                
                $monthlyStats[] = [
                    'month' => $month->format('M Y'),
                    'orders' => $monthOrders->count(),
                    'amount' => $monthOrders->sum('total_price_with_tax') / 100,
                ];
            }
        @endphp

        <!-- Statistiques générales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Commandes Validées</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $totalOrders }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Montant Total</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($totalAmount, 2, ',', ' ') }} €</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Panier Moyen</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($avgOrderAmount, 2, ',', ' ') }} €</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Dernière Commande</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                @if($orders->count() > 0)
                                    {{ $orders->sortByDesc('created_at')->first()->created_at->format('d/m/Y') }}
                                @else
                                    Aucune
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques par marques et catégories -->
        @php
            // Récupérer les données par marques pour ce client
            $brandStats = \Illuminate\Support\Facades\DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('items', 'order_items.item_id', '=', 'items.id')
                ->join('brands', 'items.brand_id', '=', 'brands.id')
                ->where('orders.user_id', $record->id)
                ->whereIn('orders.status', [\App\Enum\OrderStatusEnum::SHIPPED->value, \App\Enum\OrderStatusEnum::OPEN->value])
                ->whereNull('orders.deleted_at')
                ->select(
                    'brands.name as brand_name',
                    \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(order_items.id) as product_count'),
                    \Illuminate\Support\Facades\DB::raw('SUM(order_items.quantity) as total_quantity'),
                    \Illuminate\Support\Facades\DB::raw('SUM(order_items.price_paid * order_items.quantity) as total_amount')
                )
                ->groupBy('brands.id', 'brands.name')
                ->orderBy('total_amount', 'desc')
                ->get();

            // Récupérer les données par catégories pour ce client
            $categoryStats = \Illuminate\Support\Facades\DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('items', 'order_items.item_id', '=', 'items.id')
                ->join('categories', 'items.category_id', '=', 'categories.id')
                ->where('orders.user_id', $record->id)
                ->whereIn('orders.status', [\App\Enum\OrderStatusEnum::SHIPPED->value, \App\Enum\OrderStatusEnum::OPEN->value])
                ->whereNull('orders.deleted_at')
                ->select(
                    'categories.name as category_name',
                    \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(order_items.id) as product_count'),
                    \Illuminate\Support\Facades\DB::raw('SUM(order_items.quantity) as total_quantity'),
                    \Illuminate\Support\Facades\DB::raw('SUM(order_items.price_paid * order_items.quantity) as total_amount')
                )
                ->groupBy('categories.id', 'categories.name')
                ->orderBy('total_amount', 'desc')
                ->get();
        @endphp

        <!-- Statistiques par marques et catégories -->
        <div class="space-y-6">
            <!-- Statistiques par Marques -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Commandes par Marques</h3>
                @if($brandStats->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 text-sm font-medium text-gray-500">Marque</th>
                                    <th class="text-center py-2 text-sm font-medium text-gray-500">Nb Commandes</th>
                                    <th class="text-center py-2 text-sm font-medium text-gray-500">Nb Produits</th>
                                    <th class="text-center py-2 text-sm font-medium text-gray-500">Quantité</th>
                                    <th class="text-right py-2 text-sm font-medium text-gray-500">Montant Total (€)</th>
                                    <th class="text-right py-2 text-sm font-medium text-gray-500">Moy./Commande (€)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($brandStats as $brand)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $brand->brand_name }}</p>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-sm text-gray-900">{{ $brand->order_count }}</span>
                                            <span class="text-xs text-gray-500 ml-1">commandes</span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-sm text-gray-900">{{ $brand->product_count }}</span>
                                            <span class="text-xs text-gray-500 ml-1">produits</span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-sm text-gray-900">{{ $brand->total_quantity }}</span>
                                            <span class="text-xs text-gray-500 ml-1">articles</span>
                                        </td>
                                        <td class="py-3 text-right">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ number_format($brand->total_amount / 100, 2, ',', ' ') }} €
                                            </p>
                                        </td>
                                        <td class="py-3 text-right">
                                            <p class="text-sm text-gray-900">
                                                {{ number_format(($brand->total_amount / $brand->order_count) / 100, 2, ',', ' ') }} €
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">Aucune donnée par marque disponible.</p>
                @endif
            </div>

            <!-- Statistiques par Catégories -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Commandes par Catégories</h3>
                @if($categoryStats->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 text-sm font-medium text-gray-500">Catégorie</th>
                                    <th class="text-center py-2 text-sm font-medium text-gray-500">Nb Commandes</th>
                                    <th class="text-center py-2 text-sm font-medium text-gray-500">Nb Produits</th>
                                    <th class="text-center py-2 text-sm font-medium text-gray-500">Quantité</th>
                                    <th class="text-right py-2 text-sm font-medium text-gray-500">Montant Total (€)</th>
                                    <th class="text-right py-2 text-sm font-medium text-gray-500">Moy./Commande (€)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoryStats as $category)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $category->category_name }}</p>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-sm text-gray-900">{{ $category->order_count }}</span>
                                            <span class="text-xs text-gray-500 ml-1">commandes</span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-sm text-gray-900">{{ $category->product_count }}</span>
                                            <span class="text-xs text-gray-500 ml-1">produits</span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <span class="text-sm text-gray-900">{{ $category->total_quantity }}</span>
                                            <span class="text-xs text-gray-500 ml-1">articles</span>
                                        </td>
                                        <td class="py-3 text-right">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ number_format($category->total_amount / 100, 2, ',', ' ') }} €
                                            </p>
                                        </td>
                                        <td class="py-3 text-right">
                                            <p class="text-sm text-gray-900">
                                                {{ number_format(($category->total_amount / $category->order_count) / 100, 2, ',', ' ') }} €
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">Aucune donnée par catégorie disponible.</p>
                @endif
            </div>
        </div>

        <!-- Liste des commandes -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Toutes les Commandes</h3>
                <span class="text-sm text-gray-500">{{ $orders->count() }} commande(s) au total</span>
            </div>
            @if($orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nb Produits</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant HT (€)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant TTC (€)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($orders->sortByDesc('created_at') as $order)
                                @php
                                    $orderItems = $order->items;
                                    $totalQuantity = $orderItems->sum('quantity');
                                    $productCount = $orderItems->count();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $order->reference }}</div>
                                        @if($order->tracking_number)
                                            <div class="text-xs text-gray-500">Suivi: {{ $order->tracking_number }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order->created_at->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $order->status === \App\Enum\OrderStatusEnum::SHIPPED ? 'bg-green-100 text-green-800' : 
                                               ($order->status === \App\Enum\OrderStatusEnum::IN_PREPARATION ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ match($order->status) {
                                                \App\Enum\OrderStatusEnum::OPEN => 'Ouverte',
                                                \App\Enum\OrderStatusEnum::IN_PREPARATION => 'En préparation',
                                                \App\Enum\OrderStatusEnum::SHIPPED => 'Expédiée',
                                                default => $order->status->value,
                                            } }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-medium">{{ $productCount }}</span>
                                        <span class="text-xs text-gray-500">produit(s)</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-medium">{{ $totalQuantity }}</span>
                                        <span class="text-xs text-gray-500">article(s)</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($order->total_price / 100, 2, ',', ' ') }} €
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($order->total_price_with_tax / 100, 2, ',', ' ') }} €
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ \App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $order]) }}" 
                                               class="text-indigo-600 hover:text-indigo-900" target="_blank"
                                               title="Voir les détails de la commande">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('user_orders.pdf', ['order' => $order]) }}" 
                                               class="text-red-600 hover:text-red-900" target="_blank"
                                               title="Télécharger le PDF de la commande">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </a>
                                            @if($order->tracking_url)
                                                <a href="{{ $order->tracking_url }}" 
                                                   class="text-green-600 hover:text-green-900" target="_blank"
                                                   title="Suivre la commande">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($orders->count() > 10)
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Affichage de toutes les {{ $orders->count() }} commandes
                        </div>
                        <div class="text-xs text-gray-500">
                            Vous pouvez utiliser Ctrl+F pour rechercher dans la liste
                        </div>
                    </div>
                @endif
            @else
                <p class="text-gray-500">Aucune commande validée pour ce client.</p>
            @endif
        </div>
    </div>
</x-filament::page>
