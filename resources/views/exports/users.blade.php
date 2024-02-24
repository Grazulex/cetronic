<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Reference</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Franco</th>
        <th>Fixe shipping</th>
        <th>Agent</th>
        <th>Receive cart notifiction</th>
        <th>Language</th>
        <th>Is actif</th>
        <th>Is blocked</th>
        <th>Brand</th>
        <th>Category</th>
        <th>Category meta</th>
        <th>Category meta value</th>
        <th>Reduction</th>
        <th>Coefficient</th>
        <th>Addition price</th>
        <th>Price type</th>
        <th>Not show promo</th>
        <th>Is excluded</th>
        <th>Type</th>
        <th>Company</th>
        <th>Firstname</th>
        <th>Lastname</th>
        <th>Vat</th>
        <th>Street</th>
        <th>Street number</th>
        <th>Street other</th>
        <th>Zip</th>
        <th>City</th>
        <th>Country</th>
        <th>Phone</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($users as $user)
        <tr>
            <td>{{ $user['id'] }}</td>
            <td>{{ $user['external_reference'] }}</td>
            <td>{{ $user['name'] }}</td>
            <td>{{ $user['email'] }}</td>
            <td>{{ $user['role'] }}</td>
            <td>{{ $user['franco'] }}</td>
            <td>{{ $user['shipping_price'] }}</td>
            <td>@if ($user['agent']) {{ $user['agent']['name'] }}@endif</td>
            <td>{{ $user['receive_cart_notification'] }}</td>
            <td>{{ $user['language'] }}</td>
            <td>{{ $user['is_actif'] }}</td>
            <td>{{ $user['is_blocked'] }}</td>
            <td colspan="22"></td>
        </tr>
        @for ($j=0; $user['qtyBrands'] > $j; $j++)
            <tr>
                <td colspan="12"></td>
                <td>@if (($user['qtyBrands'] > 0) && $user['brands']) {{ $user['brands'][$j]['brand'] }}@endif</td>
                <td>@if (($user['qtyBrands'] > 0) && $user['brands']) {{ $user['brands'][$j]['category'] }}@endif</td>
                <td>@if (($user['qtyBrands'] > 0) && $user['brands']) {{ $user['brands'][$j]['category_meta'] }}@endif</td>
                <td>@if (($user['qtyBrands'] > 0) && $user['brands']) {{ $user['brands'][$j]['category_meta_value'] }}@endif</td>
                <td>@if (($user['qtyBrands'] > 0) && $user['brands']) {{ $user['brands'][$j]['reduction'] }}@endif</td>
                <td>@if (($user['qtyBrands'] > 0) && $user['brands']) {{ $user['brands'][$j]['coefficient'] }}@endif</td>
                <td>@if (($user['qtyBrands'] > 0) && $user['brands']) {{ $user['brands'][$j]['addition_price'] }}@endif</td>
                <td>@if (($user['qtyBrands'] > 0) && $user['brands']) {{ $user['brands'][$j]['price_type'] }}@endif</td>
                <td>@if (($user['qtyBrands'] > 0) && $user['brands']) {{ $user['brands'][$j]['not_show_promo'] }}@endif</td>
                <td>@if (($user['qtyBrands'] > 0) && $user['brands']) {{ $user['brands'][$j]['is_excluded'] }}@endif</td>
                <td colspan="12"></td>
            </tr>
        @endfor
        @for ($i=0; $user['qtyLocations'] > $i; $i++)
            <tr>
                <td colspan="22"></td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['type'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['company'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['firstname'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['lastname'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['vat'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['street'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['street_number'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['street_other'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['zip'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['city'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['country'] }}@endif</td>
                <td>@if (($user['qtyLocations'] > 0) && $user['locations']) {{ $user['locations'][$i]['phone'] }}@endif</td>
            </tr>
        @endfor
      @endforeach
    </tbody>
</table>
