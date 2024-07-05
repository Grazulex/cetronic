<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Document</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        img {
            max-width: 100%; /* Empêche l'image de dépasser la largeur de son conteneur */
            max-height: 100%; /* Empêche l'image de dépasser la hauteur de son conteneur */
            object-fit: contain; /* Redimensionne l'image en conservant ses proportions */
            width: auto; /* Permet à l'image de conserver ses proportions */
            height: auto;
        }

        .image {
            width: 100%;
            height: 280px;
            overflow: hidden;
        }

        .product-data {
            font-size: 8px;

        }


        /* container for the whole page width and height */
        .page {
            width: 100%;
            height: 100%;
            padding: 10px;
        }

        /* each row need to have 50vh height */
        .row {
            height: 50vh;
            display: block;
            clear: both;
        }

        .col-2 {
            width: 16%;
            display: block;
            float: left;
            height: 50vh;
        }
    </style>
</head>
<body>
<div class="section-container container">
    @php
        $elements = $products->toArray();
        $chunks = array_chunk($elements, 12);
    @endphp

    @foreach ($chunks as $chunk)
        <div class="page">
            @foreach (array_chunk($chunk, 6) as $row)
                <div class="row">
                    @foreach($products as $product)
                        <div class="col-2">
                            <div class="image"><img src="{{$product->first_media_path}}"></div>
                            <div class="product-data">
                                <div class="sku"><b>{{$product->reference}}</b></div>
                                <div><b>{{$product->price}}€</b></div>
                                @foreach($product->metas as $meta)
                                    <div><b>{{ucfirst($meta->meta->name)}}: </b>{{$meta->value}}</div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach
</div>
</body>
</html>
