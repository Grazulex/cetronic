<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
</head>
<body>
<h1>{{ $title }}</h1>
    <div class="row">
        <b>BRAND:</b> {{$brandConditionNames ? implode(', ', $brandConditionNames) : 'any'}}
    </div>
    <div class="row">
        <b>TYPE:</b> {{$typeConditionNames ? implode(', ', $typeConditionNames) : 'any'}}
    </div>
    <div class="row">
        <b>GENDER:</b> {{$genderConditionNames ? implode(', ', $genderConditionNames) : 'any'}}
    </div>

    @foreach($products as $product)
        @dd($product->getFirstMediaUrl())
        <img src="{{$product->getFirstMediaUrl()}}" alt="{{$product->slug}}">
        <h4>{{$product->slug}}</h4>
        @break
    @endforeach
</body>
</html>
