<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title></title>


</head>

<body>

{{ $user->name }}
<br>
{{ $user->email }}
<br><br>


@foreach ($user->locations as $location)
    type: {{ $location->type }}<br>
    company :{{ $location->company }}<br>
    vat: {{ $location->vat }}<br>
    firstname: {{ $location->firstname }}<br>
    lastname: {{ $location->lastname }}<br>
    street: {{ $location->street }}<br>
    street number: {{ $location->street_number }}<br>
    street other: {{ $location->street_other }}<br>
    city: {{ $location->city }}<br>
    zip: {{ $location->zip }}<br>
    country: {{ $location->country }}<br>
    phone: {{ $location->phone }}<br>
    <hr>
@endforeach

</body>

</html>
