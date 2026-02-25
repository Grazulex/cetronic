<div>
    <div class="row mb-5">
        <div class="col-lg-3">
            <div class="accordion" id="accordionPanelsStayOpenExample">
                <form action="{{ route('list', ['cat' => $catSlug, 'type' => $type, 'slug' => $slug]) }}" method="GET">
                    <div class="mb-3">
                        <label for="order" class="listing-accordion-head mb-1 d-block">{{ __('item.orders') }}</label>
                        <select id="order" name="order" class="card-select w-100">
                            <option value="reference_asc"
                                    @if ($order == 'reference_asc') selected @endif>{{ __('item.reference_asc') }}</option>
                            <option value="reference_desc"
                                    @if ($order == 'reference_desc') selected @endif>{{ __('item.reference_desc') }}</option>
                            <option value="price_asc"
                                    @if ($order == 'price_asc') selected @endif>{{ __('item.price_asc') }}</option>
                            <option value="price_desc"
                                    @if ($order == 'price_desc') selected @endif>{{ __('item.price_desc') }}</option>
                            <option value="catalogue"
                                    @if ($order == 'catalogue') selected @endif>{{ __('item.catalogue') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="paginate" class="listing-accordion-head mb-1 d-block">{{ __('item.paginate') }}</label>
                        <select id="paginate" name="paginate" class="card-select w-100">
                            <option value="15" @if ($paginate == '15') selected @endif>15</option>
                            <option value="21" @if ($paginate == '21') selected @endif>21</option>
                            <option value="30" @if ($paginate == '30') selected @endif>30</option>
                            <option value="42" @if ($paginate == '42') selected @endif>42</option>
                            <option value="60" @if ($paginate == '60') selected @endif>60</option>
                            <option value="84" @if ($paginate == '84') selected @endif>84</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3" style="border-bottom: 1px solid #4d4d4d; padding-bottom: 10px;">
                        <span class="listing-accordion-head">{{ __('item.filters') }}</span>
                        <div>
                            @if (count($selected) > 0 || (isset($new) && $new == '1') || (isset($bestSeller) && $bestSeller == '1'))
                                <a href="{{ route('list', ['cat' => $catSlug, 'type' => $type, 'slug' => $slug]) }}"
                                   class="btn btn-sm btn-outline-secondary me-1">{{ __('item.reset') }}</a>
                            @endif
                            <input type="submit" value="{{ __('item.show') }}" class="small_btn">
                        </div>
                    </div>
                    <div class="accordion-item listing-accordion-item ">
                        <h2 class="accordion-header" id="panelsStayOpen-heading99999">
                            <button class="accordion-button listing-accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse99999"
                                    aria-expanded="false" aria-controls="panelsStayOpen-collapse99999">
                                <span class="listing-accordion-head">{{ __('listing.items.new.title') }} </span>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapse99999" class="accordion-collapse collapse show"
                             aria-labelledby="panelsStayOpen-heading99999">
                            <div class="accordion-body listing-accordion-body">
                                <ul>
                                    <li class="d-flex align-items-center">
                                        <input type="checkbox" class="listing-checkbox" name="new" id="new" value="1"
                                               @if (isset($new) && $new == '1') checked @endif>
                                        <span class="select-bg">{{ __('listing.items.new.yes') }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item listing-accordion-item ">
                        <h2 class="accordion-header" id="panelsStayOpen-heading99998">
                            <button class="accordion-button listing-accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse99998"
                                    aria-expanded="false" aria-controls="panelsStayOpen-collapse99998">
                                <span class="listing-accordion-head">{{ __('listing.items.best_seller.title') }} </span>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapse99998" class="accordion-collapse collapse show"
                             aria-labelledby="panelsStayOpen-heading99998">
                            <div class="accordion-body listing-accordion-body">
                                <ul>
                                    <li class="d-flex align-items-center">
                                        <input type="checkbox" class="listing-checkbox" name="best_seller" id="best_seller" value="1"
                                               @if (isset($bestSeller) && $bestSeller == '1') checked @endif>
                                        <span class="select-bg">{{ __('listing.items.best_seller.yes') }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    @foreach ($metas as $meta)
                        <div class="accordion-item listing-accordion-item ">
                            <h2 class="accordion-header" id="panelsStayOpen-heading{{ $meta['id'] }}">
                                <button class="accordion-button listing-accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#panelsStayOpen-collapse{{ $meta['id'] }}"
                                        aria-expanded="false" aria-controls="panelsStayOpen-collapse{{ $meta['id'] }}">
                                    <span class="listing-accordion-head">{{ ucfirst($meta['name']) }} @if (isset($selected[$meta['id']]))
                                            ({{ __('item.filters') }}: {{ count($selected[$meta['id']]) }})
                                        @endif</span>
                                </button>
                            </h2>
                            <div id="panelsStayOpen-collapse{{ $meta['id'] }}" class="accordion-collapse collapse show"
                                 aria-labelledby="panelsStayOpen-heading{{ $meta['id'] }}">
                                <div class="accordion-body listing-accordion-body">
                                    <ul>
                                        @foreach ($meta['values'] as $value)
                                            <li class="d-flex align-items-center">
                                                @if ($meta['is_color'])
                                                    <input type="checkbox" class="listing-checkbox"
                                                           name="metas[{{ $meta['id'] }}][]"
                                                           id="metas[{{ $meta['id'] }}][{{ $value['value'] }}]"
                                                           value="{{ $value['value'] }}"
                                                           @if (isset($selected[$meta['id']]) && in_array($value['value'], $selected[$meta['id']])) checked @endif>
                                                    <span
                                                            class="select-bg"
                                                            style="background-color: {{ $value['value'] }}; width: 20px; height: 20px; display: inline-block; border: 1px solid #000;"></span>
                                                    ({{ $value['count'] }} items)
                                                @else
                                                    <input type="checkbox" class="listing-checkbox"
                                                           name="metas[{{ $meta['id'] }}][]"
                                                           id="metas[{{ $meta['id'] }}][{{ $value['value'] }}]"
                                                           value="{{ $value['value'] }}"
                                                           @if (isset($selected[$meta['id']]) && in_array($value['value'], $selected[$meta['id']])) checked @endif>
                                                    <span class="select-bg">{{ ucfirst($value['value']) }} ({{ $value['count'] }}
                                                   items)</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div>
                            @if (count($selected) > 0 || (isset($new) && $new == '1') || (isset($bestSeller) && $bestSeller == '1'))
                                <a href="{{ route('list', ['cat' => $catSlug, 'type' => $type, 'slug' => $slug]) }}"
                                   class="btn btn-sm btn-outline-secondary me-1">{{ __('item.reset') }}</a>
                            @endif
                        </div>
                        <input type="submit" value="{{ __('item.show') }}" class="small_btn">
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
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
