<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Document</title>
    <style>
        /* styles.css */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }

        header nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        header nav ul li {
            display: inline;
            margin-right: 20px;
        }

        header nav ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        .section-container {
            width: 100%;
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
            border: 1px solid #ccc;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            background-color: #fff;
            min-height: 50vh; /* Ensure each row is half the viewport height */
        }

        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
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
