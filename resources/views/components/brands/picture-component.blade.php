<div class="brand-area">
    @if (str_contains($picture, 'default.jpg') == FALSE)
        <img src="{{ asset($picture) }}" alt=" {{ $brand->name }}">
    @else
        {{ $brand->name }}
    @endif
</div>
