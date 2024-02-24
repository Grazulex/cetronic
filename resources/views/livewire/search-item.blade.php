<div style="position: relative;">
    <form method="GET" action="{{ route('search') }}">
        <input class="login-form form-control" style="min-width: 150px;"
               type="text" name="search-input" value="{{ Request::get('search-input') }}" placeholder="{{ __('listing.search.placeholder') }}" wire:model="term" >
    </form>

    <div wire:loading wire:loading.class="listing-search-preview">{{ __('listing.search.searching') }}</div>
    <div class="listing-search-preview {{ count($items) > 0 ? '' : 'empty' }}" wire:loading.remove wire:loading.attr="hidden">
    @foreach ($items as $item)
        <div>
            @if ($item->master)
                <a href="{{ route('item', $item->master->slug) }}" class="show_hover">
            @else
                <a href="{{ route('item', $item['slug']) }}" class="show_hover">
            @endif
                    <p class="text-gray-500 text-sm">{{ $item->brand->name }} - {{ $item->reference }}</p>
                </a>
        </div>
    @endforeach
    </div>
</div>
