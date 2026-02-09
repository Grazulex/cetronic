<div class="picture2-component">
    @if ($isNew)
        <div class="if-new">
            <p>{{ __('item.badge.new') }}</p>
        </div>
    @endif

    @if ($isBestSeller)
        @include('components.items.partials.best-seller-badge')
    @endif
    <div id="owl-example" class="overflow-hidden owl-carousel owl-pictures">
        @foreach ($pictures as $picture)
                @if ($slug != '')
                    <a href="{{ route('item', ['slug' => $slug]) }}">
                @endif
                <div class="cetronic-card-img" style="background-image: url('{{ $picture }}')"></div>
                @if ($slug != '')
                    </a>
                @endif
        @endforeach
    </div>
</div>
