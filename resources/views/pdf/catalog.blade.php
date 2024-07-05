<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ base_path('public/Css/pdf.css') }}">
</head>
<body>
<div class="section-container">
    @php
        $rowCount = 0;
        $nextPage = 0;
    @endphp
    <section class="top-section">
        @foreach($products as $product)
            <div class="column">
                <div class="image"><img src="{{$product->first_media_path}}"></div>
                <div class="product-data">
                    <div class="sku"><b>{{strtoupper($product->reference)}}</b></div>
                    <div><b>{{$product->price}}€</b></div>
                    @foreach($product->metas as $meta)
                        <div><b>{{ucfirst($meta->meta->name)}}: </b>{{$meta->value}}</div>
                    @endforeach

                    @php
                        $rowCount++;
                    @endphp
                    @if ($rowCount == 6)
                        @php
                            $nextPage++;
                            $rowCount = 0;
                        @endphp
                </div>
            </div>
            @if ($nextPage == 2)
                <div class="page-break"></div>
                @php
                    $nextPage = 0;
                @endphp
            @endif
            <section class="top-section">
                @endif
                @endforeach
            </section>
    </section>
</div>
</body>
</html>
