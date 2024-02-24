<div>
    @if (count($item->getMedia()) > 0)
        <a href="{{ route('item.zip', $item) }}" class="button">{{ __('pictures.zip') }}</a>
    @endif
</div>
