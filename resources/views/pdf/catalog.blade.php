<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ base_path('public/Css/pdf.css') }}">
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
