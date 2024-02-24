@extends('front.layout.default')
@section('title')
    {{ $item->brand->name }} - {{ $item->reference }}
@stop
@section('description')
    {{ $item->description }}
@stop
@section('keywords')
    @foreach ($item->metas as $meta)
        {{ $meta->meta->name }}: {{ $meta->value }},
    @endforeach
@stop
@section('content')
    <section class="item-section">
        <x-items.micro-component :item="$item" />
        <h2 class="listing-title">{{ $item->brand->name }} - {{ $item->reference }}</h2>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <x-items.picture3-component :item="$item" :itemCarouselId="'carousel-main-'.$item->id" />
                </div>
                <div class="col-md-8">
                    <div class="info-produit">
                        <h3>
                            {{ $item->reference }}
                            @php
                                $itemService = new \App\Services\ItemService($item);
                                echo $itemService->getVariantIfChoice();
                            @endphp
                        </h3>
                        <x-items.price-component :item="$item" />
                        <x-items.discount-component :item="$item" />
                        <x-items.description-component>
                            <div class="description">
                                {{ $item->description }}
                            </div>
                        </x-items.description-component>
                        <x-items.download-component :item="$item" />
                        <div class="d-flex align-items-center mt-3 mb-2">
                            <div>
                                <svg width="14" height="16" viewBox="0 0 14 16" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M3.66667 7.08696C5.1 8.28609 5.74 9.64348 6.33333 10.7391C6.9 9.64956 9.02 7.28783 11 5.26087M7 1C9.02 2.01652 10.9533 3.11826 13 2.82609L12.3333 10.1304C11.22 13.0583 8.64 13.2409 7 15C5.36 13.2409 2.78 13.0583 1.66667 10.1304L1 2.82609C3.05333 3.11826 4.98 2.01652 7 1Z"
                                        stroke="#4D4D4D" stroke-width="0.5" stroke-miterlimit="10"
                                        stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span class="caracteristique">{{ __('item.stock') }}</span>
                            </div>
                            <div class="mx-3">
                                <svg width="20" height="16" viewBox="0 0 20 16" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M1 13H0.75C0.75 13.1381 0.861929 13.25 1 13.25V13ZM19 13V13.25C19.1381 13.25 19.25 13.1381 19.25 13H19ZM18.7026 10.024L18.8599 9.82966C18.8458 9.81827 18.8305 9.80843 18.8143 9.80034L18.7026 10.024ZM16.6522 9L16.4046 9.03504C16.4162 9.11642 16.4669 9.18694 16.5405 9.22366L16.6522 9ZM16.3 6.512L16.0509 6.53589L16.0525 6.54704L16.3 6.512ZM11.9565 5.8V5.55C11.8184 5.55 11.7065 5.66193 11.7065 5.8H11.9565ZM6.86957 13.25H11.9565V12.75H6.86957V13.25ZM12.2065 13V1.8H11.7065V13H12.2065ZM12.2065 1.8C12.2065 1.22714 11.7476 0.75 11.1739 0.75V1.25C11.4611 1.25 11.7065 1.49286 11.7065 1.8H12.2065ZM11.1739 0.75H1.78261V1.25H11.1739V0.75ZM1.78261 0.75C1.20895 0.75 0.75 1.22714 0.75 1.8H1.25C1.25 1.49286 1.4954 1.25 1.78261 1.25V0.75ZM0.75 1.8V13H1.25V1.8H0.75ZM1 13.25H2.95652V12.75H1V13.25ZM17.8261 13.25H19V12.75H17.8261V13.25ZM19.25 13V10.648H18.75V13H19.25ZM19.25 10.648C19.25 10.3331 19.1068 10.0295 18.8599 9.82966L18.5453 10.2183C18.6741 10.3225 18.75 10.4829 18.75 10.648H19.25ZM18.8143 9.80034L16.7639 8.77634L16.5405 9.22366L18.5909 10.2477L18.8143 9.80034ZM16.8997 8.96496L16.5475 6.47696L16.0525 6.54704L16.4046 9.03504L16.8997 8.96496ZM16.5489 6.48813C16.4976 5.95326 16.0545 5.55 15.5252 5.55V6.05C15.7942 6.05 16.0242 6.25474 16.0511 6.53587L16.5489 6.48813ZM15.5252 5.55H11.9565V6.05H15.5252V5.55ZM11.7065 5.8V13H12.2065V5.8H11.7065ZM11.9565 13.25H13.913V12.75H11.9565V13.25ZM17.5761 13C17.5761 13.9717 16.8069 14.75 15.8696 14.75V15.25C17.0933 15.25 18.0761 14.2374 18.0761 13H17.5761ZM15.8696 14.75C14.9322 14.75 14.163 13.9717 14.163 13H13.663C13.663 14.2374 14.6458 15.25 15.8696 15.25V14.75ZM14.163 13C14.163 12.0283 14.9322 11.25 15.8696 11.25V10.75C14.6458 10.75 13.663 11.7626 13.663 13H14.163ZM15.8696 11.25C16.8069 11.25 17.5761 12.0283 17.5761 13H18.0761C18.0761 11.7626 17.0933 10.75 15.8696 10.75V11.25ZM6.61957 13C6.61957 13.9717 5.85038 14.75 4.91304 14.75V15.25C6.13682 15.25 7.11957 14.2374 7.11957 13H6.61957ZM4.91304 14.75C3.97571 14.75 3.20652 13.9717 3.20652 13H2.70652C2.70652 14.2374 3.68926 15.25 4.91304 15.25V14.75ZM3.20652 13C3.20652 12.0283 3.97571 11.25 4.91304 11.25V10.75C3.68926 10.75 2.70652 11.7626 2.70652 13H3.20652ZM4.91304 11.25C5.85038 11.25 6.61957 12.0283 6.61957 13H7.11957C7.11957 11.7626 6.13682 10.75 4.91304 10.75V11.25Z"
                                        fill="#4D4D4D" />
                                </svg>
                                <span class="caracteristique">{{ __('item.shipping.days') }}</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-12">
                                @if ($item->variants->count() > 0)
                                    <x-items.variant-component :item="$item" />
                                @else
                                    <h2 class="Quatité-title">{{ __('item.quantity') }}</h2>
                                    @livewire('form-item', ['item' => $item])
                                @endif
                            </div>
                        </div>
                        <x-items.meta-component :item="$item" />
                    </div>
                </div>
            </div>
        </div>
        {{-- <div>
            <article>
                <header>
                    <h3>
                        {{ $item->reference }}
                    </h3>
                    <x-items.brand-component :brand="$item->brand" />
                </header>

                <body>
                    <x-items.picture3-component :item="$item" :itemCarouselId="'carousel-'.$item->id" />
                    <x-items.description-component>
                        {{ $item->description }}
                    </x-items.description-component>
                    <x-items.meta-component :item="$item" />
                </body>
                <footer>
                    <x-items.price-component :item="$item" />
                    @if ($item->variants->count() > 0)
                        <x-items.variant-component :item="$item" />
                    @else
                        @livewire('form-item', ['item' => $item])
                    @endif
                </footer>
            </article>
        </div> --}}
    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
