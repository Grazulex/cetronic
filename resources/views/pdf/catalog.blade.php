<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ base_path('public/Css/pdf.css') }}">
    <link rel="stylesheet" href="{{ base_path('public/Css/simple-grid.css') }}">
</head>
<body>
    <div class="container">
        <div class="row conditions">
            <div class="col-4">
                <b>Marque:</b> {{$brandConditionNames ? implode(', ', $brandConditionNames) : 'any'}}
            </div>
            <div class="col-4">
                <b>Catégories:</b> {{$categoryConditionNames ? implode(', ', $categoryConditionNames) : 'any'}}
            </div>
            <div class="col-4">
                <b>Type:</b> {{$typeConditionNames ? implode(', ', $typeConditionNames) : 'any'}}
            </div>
            <div class="col-4">
                <b>Genre:</b> {{$genderConditionNames ? implode(', ', $genderConditionNames) : 'any'}}
            </div>
        </div>
    </div>
    <div class="container">
        @php
            $rowCount = 0;
            $nextPage = 0;
        @endphp
        <div class="row">
        @foreach($products as $product)
            <div class="col-2 item">
                <div>
                    <div class="image"><img src="{{$product->first_media_path}}"></div>
                    <div class="product-data">
                        <div class="sku"><b>{{$product->slug}}</b></div>
                        <div><b>{{$product->price}}€</b></div>
                        @foreach($product->metas as $meta)
                            <div><b>{{ucfirst($meta->meta->name)}}: </b>{{$meta->value}}</div>
                        @endforeach
                    </div>
                </div>
            </div>
            @php
                $rowCount++;
            @endphp
            @if ($rowCount == 6)
                @php
                    $nextPage++;
                    $rowCount = 0;
                @endphp
                </div>
                @if ($nextPage == 2)
                    <div class="page-break"></div>
                    <div class="page-header"></div>
                    @php
                        $nextPage = 0;
                    @endphp
                @endif
                <div class="row">
            @endif
       @endforeach
        </div>
    </div>
</body>
</html>
