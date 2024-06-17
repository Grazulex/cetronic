<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ base_path('public/Css/pdf.css') }}">
    <link rel="stylesheet" href="{{ base_path('public/Css/simple-grid.css') }}">
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="container">
        <div class="row">
            <div class="col-4">
                <b>Marque:</b> {{$brandConditionNames ? implode(', ', $brandConditionNames) : 'any'}}
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
                    <div><img src="{{$product->getFirstMediaPath()}}"></div>
                    <div>{{$product->slug}}</div>
                    <div>{{$product->price}}€</div>
                    <div>Genre: {{$product->gender ?: "Non spécifié"}}</div>
                    <div>Taille de boîtier: </div>
                    <div>Matière bracelet: </div>
                    <div>Matière boîtier: </div>
                    <div>Etanchéité: </div>
                    <div>Mouvement: </div>
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
