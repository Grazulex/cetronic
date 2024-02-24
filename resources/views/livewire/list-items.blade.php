<div class="row">
    @if ($type == 'brand' && Auth::check())
        <div class="catalog">
            <a href="{{ route('brand.download.zip', $model) }}" class="btn btn-primary">{{ __('brand_download_zip') }}</a>
        </div>
        <div class="catalog">
            <a href="{{ route('brand.download.catalog', $model) }}" class="btn btn-primary">{{ __('brand_download_catalog') }}</a>
        </div>
    @endif
    @foreach ($items as $item)
        <div class="col-xl-4">
            <x-items.item-component :item="$item" :key="'item-' . $item->id" />
        </div>
    @endforeach

    {{ $items->links() }}
</div>
