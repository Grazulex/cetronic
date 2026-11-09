@if ($record = $getRecord())
    @livewire(\App\Http\Livewire\Admin\ItemSales::class, ['itemId' => $record->getKey()], key('item-sales-' . $record->getKey()))
@endif
