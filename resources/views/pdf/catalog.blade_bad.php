<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ base_path('public/Css/pdf.css') }}">
    <link rel="stylesheet" href="{{ base_path('public/Css/simple-grid.css') }}">
    <style>
        .page-number {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 8px;
            color: #ccc;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
<div class="container">
    @php
        $oldGroup = '';
        $isFirstProduct = true;
        $currentPage = 1;

        // Calculer le nombre total de pages
        $totalPages = 0;
        $tempGroup = '';
        $tempCount = 0;
        foreach($products as $tempProduct) {
            if ($tempGroup != $tempProduct->catalog_group) {
                if ($tempCount > 0) {
                    $totalPages += ceil($tempCount / 6);
                }
                $tempGroup = $tempProduct->catalog_group;
                $tempCount = 1;
            } else {
                $tempCount++;
            }
        }
        if ($tempCount > 0) {
            $totalPages += ceil($tempCount / 6);
        }
    @endphp

    {{-- Numéro de page pour la première page --}}
    @if ($isFirstProduct)
        <div class="page-number">{{ $currentPage }}/{{ $totalPages }}</div>
    @endif

    @foreach($products as $index => $product)
        {{-- Nouveau groupe = nouvelle page --}}
        @if (!$isFirstProduct && $oldGroup != $product->catalog_group)
            </div>
            <div class="page-break"></div>
            <div class="page-header"></div>
            @php $currentPage++; @endphp
            <div class="page-number">{{ $currentPage }}/{{ $totalPages }}</div>
            <div class="row">
        @endif

        {{-- Nouvelle page après 6 produits (dans le même groupe) --}}
        @if (!$isFirstProduct && $oldGroup == $product->catalog_group && $index % 6 == 0)
            </div>
            <div class="page-break"></div>
            <div class="page-header"></div>
            @php $currentPage++; @endphp
            <div class="page-number">{{ $currentPage }}/{{ $totalPages }}</div>
            <div class="row">
        @endif

        {{-- Commencer une nouvelle rangée --}}
        @if ($isFirstProduct || $oldGroup != $product->catalog_group)
            <div class="row">
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
            $oldGroup = $product->catalog_group;
            $isFirstProduct = false;
        @endphp
    @endforeach
    </div>
</div>
</body>
</html>