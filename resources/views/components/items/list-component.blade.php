@php
    $baseUrl = route('list', ['cat' => $catSlug, 'type' => $type, 'slug' => $slug]);
    $query = request()->query();
    $urlWith = function (array $params) use ($baseUrl): string {
        $params = array_filter($params, fn ($value) => null !== $value && '' !== $value && [] !== $value);

        return $baseUrl . ([] === $params ? '' : '?' . http_build_query($params));
    };
    $withoutMetaValue = function (int|string $metaId, string $value) use ($query, $urlWith): string {
        $params = $query;
        $params['metas'][$metaId] = array_values(array_filter($params['metas'][$metaId] ?? [], fn ($selected) => $selected !== $value));
        $params['metas'] = array_filter($params['metas'], fn ($values) => [] !== $values);

        return $urlWith($params);
    };
    $withoutParam = fn (string $key): string => $urlWith(array_diff_key($query, [$key => true]));
    $isNew = isset($new) && '1' == $new;
    $isBestSeller = isset($bestSeller) && '1' == $bestSeller;
    $activeCount = collect($selected)->flatten()->count() + ($isNew ? 1 : 0) + ($isBestSeller ? 1 : 0);
    $visibleValues = 6;
@endphp

<div class="listing">
    <div class="row mb-5">
        <div class="col-lg-3">
            <button type="button" class="filters-toggle" data-filters-toggle aria-controls="listing-filters-panel" aria-expanded="false">
                {{ __('listing.filters.title') }}@if ($activeCount > 0) ({{ $activeCount }})@endif
            </button>

            <aside class="filters" id="listing-filters-panel">
                <form id="listing-filters" action="{{ $baseUrl }}" method="GET" data-filters-form>
                    <div class="filters_quick">
                        <label @class(['filter-pill', 'is-active' => $isNew])>
                            <input type="checkbox" name="new" value="1" @checked($isNew)>
                            {{ __('listing.items.new.title') }}
                        </label>
                        <label @class(['filter-pill', 'is-active' => $isBestSeller])>
                            <input type="checkbox" name="best_seller" value="1" @checked($isBestSeller)>
                            {{ __('listing.items.best_seller.title') }}
                        </label>
                    </div>

                    @foreach ($metas as $meta)
                        @php
                            $chosen = $selected[$meta['id']] ?? [];
                            $values = $meta['values'];
                        @endphp
                        <details class="filter-group" data-meta="{{ $meta['id'] }}"{{ count($chosen) > 0 ? ' open' : '' }}>
                            <summary class="filter-group_title">
                                <span>{{ ucfirst($meta['name']) }}</span>
                                @if (count($chosen) > 0)
                                    <span class="filter-group_badge">{{ count($chosen) }}</span>
                                @endif
                            </summary>

                            @if (count($values) > 10)
                                <input type="search" class="filter-group_search" placeholder="{{ __('listing.filters.search') }}"
                                    aria-label="{{ __('listing.filters.search') }} {{ $meta['name'] }}" data-filter-search>
                            @endif

                            <ul class="filter-group_options">
                                @foreach ($values as $value)
                                    @php $isChecked = in_array($value['value'], $chosen); @endphp
                                    <li @class(['filter-option', 'is-extra' => $loop->index >= $visibleValues && ! $isChecked])>
                                        <label>
                                            <input type="checkbox" name="metas[{{ $meta['id'] }}][]" value="{{ $value['value'] }}" @checked($isChecked)>
                                            @if ($meta['is_color'])
                                                <span class="filter-option_swatch" style="background-color: {{ $value['value'] }}"></span>
                                            @endif
                                            <span class="filter-option_label">{{ ucfirst($value['value']) }}</span>
                                            <span class="filter-option_count">{{ $value['count'] }}</span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>

                            @if (count($values) > $visibleValues)
                                <button type="button" class="filter-group_more" data-filter-more
                                    data-less="{{ __('listing.filters.less') }}">{{ __('listing.filters.more', ['count' => count($values) - $visibleValues]) }}</button>
                            @endif
                        </details>
                    @endforeach

                    <noscript>
                        <button type="submit" class="filters_apply">{{ __('listing.filters.apply') }}</button>
                    </noscript>
                </form>
            </aside>
        </div>

        <div class="col-lg-9">
            <div class="listing-toolbar">
                <p class="listing-toolbar_count">{{ trans_choice('listing.results', $items->total(), ['count' => $items->total()]) }}</p>
                <div class="listing-toolbar_controls">
                    <label class="listing-toolbar_field">
                        <span>{{ __('item.orders') }}</span>
                        <select name="order" form="listing-filters" data-filters-auto>
                            <option value="reference_asc" @selected($order == 'reference_asc')>{{ __('item.reference_asc') }}</option>
                            <option value="reference_desc" @selected($order == 'reference_desc')>{{ __('item.reference_desc') }}</option>
                            <option value="price_asc" @selected($order == 'price_asc')>{{ __('item.price_asc') }}</option>
                            <option value="price_desc" @selected($order == 'price_desc')>{{ __('item.price_desc') }}</option>
                            <option value="catalogue" @selected($order == 'catalogue')>{{ __('item.catalogue') }}</option>
                        </select>
                    </label>
                    <label class="listing-toolbar_field">
                        <span>{{ __('item.paginate') }}</span>
                        <select name="paginate" form="listing-filters" data-filters-auto>
                            @foreach ([15, 21, 30, 42, 60, 84] as $size)
                                <option value="{{ $size }}" @selected($paginate == $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>

            @if ($activeCount > 0)
                <div class="listing-chips">
                    @if ($isNew)
                        <a class="filter-chip" href="{{ $withoutParam('new') }}" aria-label="{{ __('listing.filters.remove', ['name' => __('listing.items.new.title')]) }}">{{ __('listing.items.new.title') }} <span aria-hidden="true">×</span></a>
                    @endif
                    @if ($isBestSeller)
                        <a class="filter-chip" href="{{ $withoutParam('best_seller') }}" aria-label="{{ __('listing.filters.remove', ['name' => __('listing.items.best_seller.title')]) }}">{{ __('listing.items.best_seller.title') }} <span aria-hidden="true">×</span></a>
                    @endif
                    @foreach ($selected as $metaId => $values)
                        @foreach ((array) $values as $value)
                            <a class="filter-chip" href="{{ $withoutMetaValue($metaId, $value) }}" aria-label="{{ __('listing.filters.remove', ['name' => $value]) }}">{{ ucfirst($value) }} <span aria-hidden="true">×</span></a>
                        @endforeach
                    @endforeach
                    <a class="listing-chips_clear" href="{{ $baseUrl }}">{{ __('listing.filters.clear') }}</a>
                </div>
            @endif

            <div class="row">
                @foreach ($items as $item)
                    <div class="col-lg-4 col-xl-4">
                        <x-items.item-component :item="$item" :key="'item-' . $item->id" />
                    </div>
                @endforeach

                {{ $items->links() }}
            </div>
        </div>

    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-filters-form]');
            if (!form) return;

            // Apply filters as soon as they change: no "Show" button to click.
            const submit = () => (form.requestSubmit ? form.requestSubmit() : form.submit());
            form.addEventListener('change', (event) => {
                if (event.target.matches('input[type="checkbox"]')) submit();
            });
            document.querySelectorAll('[data-filters-auto]').forEach((select) => select.addEventListener('change', submit));

            // "Show N more" / "Show less" on long filter groups.
            form.querySelectorAll('[data-filter-more]').forEach((button) => {
                const more = button.textContent;
                button.addEventListener('click', () => {
                    const group = button.closest('.filter-group');
                    const expanded = group.classList.toggle('is-expanded');
                    button.textContent = expanded ? button.dataset.less : more;
                });
            });

            // Search inside long filter groups.
            form.querySelectorAll('[data-filter-search]').forEach((input) => {
                input.addEventListener('input', () => {
                    const group = input.closest('.filter-group');
                    const term = input.value.trim().toLowerCase();
                    group.classList.toggle('is-searching', term !== '');
                    group.querySelectorAll('.filter-option').forEach((option) => {
                        option.hidden = term !== '' && !option.textContent.toLowerCase().includes(term);
                    });
                });
            });

            // Mobile: the filters panel opens from a button.
            const toggle = document.querySelector('[data-filters-toggle]');
            const panel = document.getElementById('listing-filters-panel');
            toggle?.addEventListener('click', () => {
                const open = panel.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });
    </script>
@endonce
