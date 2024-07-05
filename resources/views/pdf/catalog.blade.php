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
            margin: 20px;
        }

        .page {
            width: 100%;
            page-break-after: always;
        }

        .row {
            display: flex;
            width: 100%;
        }

        .column {
            flex: 1;
            border: 1px solid #000;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            height: 200px; /* Set a fixed height for each row */
        }

        footer {
            text-align: center;
            padding: 10px 0;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="section-container">
    @php
        $elements = range(1, 24); // Exemple d'éléments à afficher (24 éléments pour deux pages)
        $chunks = array_chunk($elements, 12); // Diviser les éléments en groupes de 12 pour chaque page
    @endphp

    @foreach ($chunks as $chunk)
        <div class="page">
            @foreach (array_chunk($chunk, 6) as $row)
                <div class="row">
                    @foreach ($row as $element)
                        <div class="column">
                            {{ $element }}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach
</div>
</body>
</html>
