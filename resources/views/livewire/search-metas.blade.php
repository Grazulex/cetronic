<div class="col-xl-3">
    <div class="accordion" id="accordionPanelsStayOpenExample">
        @foreach ($metas as $meta)
            <div class="accordion-item listing-accordion-item ">
                <h2 class="accordion-header" id="panelsStayOpen-heading{{ $meta['id'] }}">
                    <button class="accordion-button listing-accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse{{ $meta['id'] }}"
                        aria-expanded="false" aria-controls="panelsStayOpen-collapse{{ $meta['id'] }}">
                        <span class="listing-accordion-head">{{ ucfirst($meta['name']) }}</span>
                    </button>
                </h2>
                <div id="panelsStayOpen-collapse{{ $meta['id'] }}" class="accordion-collapse collapse show"
                    aria-labelledby="panelsStayOpen-heading{{ $meta['id'] }}">
                    <div class="accordion-body listing-accordion-body">
                        <ul>
                            @foreach ($meta['values'] as $value)
                                <li class="d-flex align-items-center">
                                    @if ($meta['is_color'])
                                        <input type="checkbox" class="listing-checkbox" wire:model="selected"
                                            id="{{ $meta['id'].$value['value'] }}" value="{{ $meta['id'] . '|' . $value['value'] }}" ><span
                                            class="select-bg"
                                            style="background-color: {{ $value['value'] }}; width: 20px; height: 20px; display: inline-block; border: 1px solid #000;"></span>
                                        ({{ $value['count'] }} items)
                                    @else
                                        <input type="checkbox" class="listing-checkbox" wire:model="selected"
                                            id="{{ $meta['id'].$value['value'] }}" value="{{ $meta['id'] . '|' . $value['value'] }}" >
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

    </div>
</div>
