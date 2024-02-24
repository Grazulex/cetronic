@if (count($brandsFeatured)>0)
    {{ __('nav.brands.featured') }}
    <ul>
        @foreach ($brandsFeatured as $brand)
            <li>
                {{ $brand['name'] }}
            </li>
        @endforeach
    </ul>
@endif
