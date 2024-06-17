<div>
    @if($getState() && is_array($getState()))
        {{implode(', ', $getState())}}
    @else
        {{$getState() ?: 'any'}}
    @endif
</div>
