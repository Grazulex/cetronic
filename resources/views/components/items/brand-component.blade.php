<div>
    @if ($brand->picture)
        <img src="{{ asset('storage/' . $brand->picture) }}" alt="{{ $brand->name }}">
    @else
        {{ $brand->name }}
    @endif
</div>
