<div>
    <h3 class="item-titre">{{ __('item.metas') }} </h3>
    <ul class="caracteristiques">
        @foreach ($metas as $meta)
            <li class="d-flex align-items-center">
                <svg width="13" height="10" viewBox="0 0 13 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 4.82609L5.30435 8.65217L12 1" stroke="#4D4D4D" stroke-miterlimit="10"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="caracteristique ms-2">
                @if (isset($meta->meta->translations[0]->name))
                    <b>{{ $meta->meta->translations[0]->name }}:</b>
                @else
                    <b>{{ $meta->meta->name }}:</b>
                @endif
                 {{ $meta->value }}</div>
            </li>
        @endforeach
    </ul>
</div>
