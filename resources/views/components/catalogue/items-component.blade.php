<div>
    @foreach ($items as $item)
        item : {{ $item->slug }}
        <br>
        description : {{ $item->description }}
        <br>
        price : {{ $item->price }}
    @endforeach
</div>
