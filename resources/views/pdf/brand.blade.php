<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title></title>

    <style>
        @font-face {
            font-family: 'CHAMAN ELEGANT FONT';
            src: url({{ storage_path("fonts/CHAMAN.ttf") }}) format("truetype");
            font-display: swap;
        }
        @page {
            margin: 100px 25px;
        }

        header {
            position: fixed;
            top: -70px;
            left: 0px;
            right: 0px;
            height: 60px;

            /** Extra personal styles **/
            font-family: 'CHAMAN ELEGANT FONT';
            background-color: #f4f4f4;
            color: #4d4d4d;
            text-align: center;
            line-height: 25px;
            letter-spacing: -0.02em;
            text-transform: uppercase;
            font-weight: 400;
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 50px;

            /** Extra personal styles **/
            font-family: 'CHAMAN ELEGANT FONT';
            background-color: #f4f4f4;
            color: #4d4d4d;
            text-align: center;
            line-height: 25px;
            letter-spacing: -0.02em;
            text-transform: uppercase;
            font-weight: 100;
            font-size: 14px;
        }
        .listing-title {
            font-family: 'CHAMAN ELEGANT FONT';
            font-style: normal;
            font-weight: 400;
            font-size: 40px;
            line-height: 63px;
            letter-spacing: -0.02em;
            text-transform: uppercase;
            color: #4d4d4d;
            text-align: center;
            margin-bottom: 20px;
            background: #f4f4f4;
            padding-top: 5px;
            padding-bottom: 5px;
        }
        .listing-item {
            font-style: normal;
            font-weight: 400;
            font-size: 20px;
            line-height: 31px;
            letter-spacing: -0.02em;
            text-transform: uppercase;
            color: #4d4d4d;
            text-align: center;
            margin-bottom: 20px;
            background: #f4f4f4;
            padding-top: 5px;
            padding-bottom: 5px;
            height: 250px;
        }
        .strike {
            text-decoration: line-through !important;
        }
        .sale {
            font-size: 10px;
            font-style: italic;
            jutify-content: right;
        }

    </style>
</head>

<body>
    <header>
        <div style="width:100%">
            <div style="float:left; width: 50%">
                <img src="{{ storage_path('logo.png') }}" alt="{{ $brand->name }}">
            </div>
            <div style="float:right; width: 50%">
                @php
                    $brandService = new \App\Services\BrandService();
                    $picture = $brandService->getDefaultPicturePdf($brand);
                @endphp

                    {{ $brand->name }}

            </div>
        </div>
    </header>



    <table width="100%">
        <tr width="100%">
            @foreach ($items as $item)
                    <td width="25%">
                        <div class="listing-item">
                            <div>{{ $item->reference }}</div>
                            @php
                                $itemService = new \App\Services\ItemService($item);
                                $picture = $itemService->getDefaultPicturePdf();
                            @endphp
                            @if (str_contains($picture, 'default.jpg') == FALSE)
                                <img src="{{ $picture }}" alt="{{ $item->reference }}" height="150px">
                            @endif
                           <x-items.price-component :item="$item" />
                        </div>
                    </td>
                @if ($loop->iteration % 4 == 0)
                    </tr>
                    @if (!$loop->last)
                        <tr width="100%">
                    @endif
                @endif
            @endforeach
        </tr>
    </table>

    <footer>
        Copyright &copy; Cetronic Benelux @php echo date("Y"); @endphp <br/>Price list generated on @php echo date("d-m-Y"); @endphp only for customer {{ $user->name }}
    </footer>

</body>

</html>
