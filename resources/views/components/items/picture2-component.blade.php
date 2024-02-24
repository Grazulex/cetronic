<div class="picture2-component">
    @if ($isNew)
        <div class="if-new">
            <p>{{ __('item.badge.new') }}</p>
        </div>
    @endif
    <div class="overflow-hidden">
        <div class="cetronic-card-img" style="background-image: url('{{ $picture }}')"></div>
    </div>
</div>
