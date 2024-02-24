@if (count($brandsFeatured) > 0)
    <h2>{{ __('home.brands.featured') }}</h2>
    @foreach ($brandsFeatured as $brandFeatured)
        <x-brands.picture-component :brand="$brandFeatured" />
    @endforeach
    </div>
@endif
