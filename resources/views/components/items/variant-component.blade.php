<div>
    @foreach ($variants as $variant)
        <div class="row">
            <div class="col-xl-2">
                {{ $variant->reference }}
            </div>
            <div class="col-xl-2">
                @if ($show_picture_variant)
                    <x-items.picture2-component :item="$variant->master" />
                @endif
            </div>
            <div class="col-xl-2">
                <ul>
                    @foreach ($variant->metas as $meta)
                        @if ($meta->meta->is_variant)
                            <li>{{ $meta->meta->name }}: {{ $meta->value }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <div class="col-xl-2">
                <x-items.price-component :item="$variant" />
            </div>
            <div class="col-xl-4">
                @livewire('form-item', ['item' => $variant])
            </div>

        </div>
        <hr>
    @endforeach


</div>
