<!DOCTYPE html>
<html>
<head>
    <style>
        /* styles.css */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .section-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .page {
            height: 100vh;
            page-break-after: always;
        }

        .row {
            display: flex;
            flex: 1;
        }

        .column {
            flex: 1;
            border: 1px solid #ccc;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            background-color: #fff;
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
