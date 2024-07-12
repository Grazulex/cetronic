<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ base_path('public/Css/pdf.css') }}">
    <link rel="stylesheet" href="{{ base_path('public/Css/simple-grid.css') }}">
</head>
<body>
<div class="container">
    @php
        $rowCount = 0;
        $nextPage = 0;
        $oldGroup = '';
    @endphp
    <div class="row">
        @foreach($products as $product)
            @if ($rowCount > 0 && $oldGroup != $product->catalog_group)
                @php
                    $rowCount = 0;
                    $nextPage++;
                @endphp
                <div class="page-break"></div>
                <div class="page-header"></div>
            @endif
            <div class="col-2 item">
                <div class="image"><img src="{{$product->first_media_path}}"></div>
                <div class="product-data">
                    <div class="sku"><b>{{$product->reference}} - {{$product->reseller_price}}€</b></div>
                    <div class="meta">
                        @foreach($product->metas as $meta)
                            <div><b>{{ucfirst($meta->meta->name)}}: </b>{{$meta->value}}</div>
                        @endforeach
                        <div><b>Group: {{ $product->catalog_group }}</b></div>
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
        @php
            $oldGroup = $product->catalog_group;
        @endphp
        @endforeach
    </div>
</div>
</body>
</html>