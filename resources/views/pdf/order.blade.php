<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Cetronix Benelux</title>

    <style>
        .invoice-box {
            max-width: 100%;
            margin: auto;
            padding: 3px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 12px;
            line-height: 12px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 5px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 24px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }
        .strike {
            text-decoration: line-through !important;
        }

    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="{{ public_path('logo.png') }}" style="width: 100%; max-width: 200px" />
                            </td>

                            <td>
                                {{ __('order.reference') }} #: {{ strtoupper($order->reference) }}<br />
                                {{ __('order.date') }}: {{ $order->created_at }}<br />
                                @if ($order->user->external_reference)
                                    {{ __('order.user.reference') }}: {{ $order->user->external_reference }}<br />
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <h2>{{ __('order.location.shipping') }}</h2>
                                {{ $order->shipping_company }} <br />
                                {{ $order->shipping_name }} <br />
                                {{ $order->shipping_street_other }} <br />
                                {{ $order->shipping_street }} {{ $order->shipping_street_number }} <br />
                                {{ $order->shipping_zip }} {{ $order->shipping_city }} <br />
                                {{ $order->shipping_country }} <br />
                                {{ $order->invoice_email }}<br />
                                {{ $order->shipping_phone }}<br />

                            </td>

                            <td>
                                <h2>{{ __('order.location.invoice') }}</h2>
                                {{ $order->invoice_company }} <br />
                                {{ $order->invoice_name }} <br />
                                {{ $order->invoice_street_other }} <br />
                                {{ $order->invoice_street }} {{ $order->invoice_street_number }} <br />
                                {{ $order->invoice_zip }} {{ $order->invoice_city }} <br />
                                {{ $order->invoice_country }} <br />
                                {{ $order->invoice_vat }} <br />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information" style="width: 100%">
                <td colspan="2">
                    <selection class="order_comment">
                        <h3>{{ __('order.comment') }}</h3>
                        {{ $order->comment }}
                    </selection>
                </td>
            </tr>


            <tr class="heading" style="width: 100%">
                <td style="width: 100%">
                    <selection class="order_items">
                        <h3>{{ __('order.item') }}</h3>
                        <table style="width: 100%">
                            <thead>
                                <tr>
                                    <th>{{ __('order.items.reference') }}</th>
                                    <th>{{ __('order.items.old') }}</th>
                                    <th>{{ __('order.items.unit') }}</th>
                                    <th>{{ __('order.items.quantity') }}</th>
                                    <th>{{ __('order.items.price') }}</th>
                            </thead>
                            <tbody>
                                @php
                                    $page=1;
                                    $line=1;
                                @endphp
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td style="text-align: right">
                                            @if ($item->price_old > 0)
                                                <font class="strike">@money($item->price_old)</font>
                                            @endif
                                            @if ($item->price_show > 0)
                                                <font class="strike">@money($item->price_show)</font>
                                            @endif
                                        </td>
                                        <td style="text-align: right">
                                            @money($item->price_paid)
                                        </td>
                                        <td style="text-align: center">{{ $item->quantity }}</td>
                                        <td style="text-align: right">@money($item->price_paid*$item->quantity)</td>
                                    </tr>
                                    @if ((($page ==1) && (($line % 18) == 0)) || (($page > 1) && (($line % 35) == 0)))
                                        </tbody>
                                        </table>
                                        <div style="page-break-after: always;"></div>
                                        <table style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('order.items.reference') }}</th>
                                                    <th>{{ __('order.items.old') }}</th>
                                                    <th>{{ __('order.items.unit') }}</th>
                                                    <th>{{ __('order.items.quantity') }}</th>
                                                    <th>{{ __('order.items.price') }}</th>
                                            </thead>
                                            <tbody>
                                        @php
                                            $line=1;
                                            $page++
                                        @endphp
                                    @endif
                                    @php
                                        $line++
                                    @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" style="text-align: center"> {{ $order->items->sum('quantity') }}
                                        {{ __('order.resume.piece') }} / {{ $order->items->count() }}
                                        {{ __('order.resume.item') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </selection>
                    <selection class="order_total">
                        <h3>{{ __('order.total.title') }}</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th></th>
                                        <th>{{ __('order.total.without') }}</th>
                                        @if ($order->invoice_country === 'BEL')
                                            <th>{{ __('order.total.vat') }}</th>
                                            <th>{{ __('order.total.with') }}</th>
                                        @endif
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ __('order.total.items') }}</td>
                                    <td style="text-align: right">@money($order->total_products)</td>
                                    @if ($order->invoice_country === 'BEL')
                                        <td style="text-align: right">@money($order->total_products_tax)</td>
                                        <td style="text-align: right">@money($order->total_products_with_tax)</td>
                                    @endif
                                </tr>
                                @if (($order->total_shipping > 0) OR (($order->franco > 0) && ($order->franco <= $order->total_products)))
                                    <tr>
                                        <td>{{ __('order.total.shipping') }}</td>
                                        <td style="text-align: right">@money($order->total_shipping)</td>
                                        @if ($order->invoice_country === 'BEL')
                                            <td style="text-align: right">@money($order->total_shipping_tax)</td>
                                            <td style="text-align: right">@money($order->total_shipping_with_tax)</td>
                                        @endif
                                    </tr>
                                @else
                                    @if (($order->franco === 0) || (($order->franco > 0) && ($order->franco > $order->total_products)))
                                        <tr>
                                            <td>{{ __('order.total.shipping') }}</td>
                                            <td colspan="3" style="text-align: right">{{ __('cart.shipping.order') }}</td>
                                        </tr>
                                    @endif
                                @endif
                                @if ($order->discount > 0)
                                    <tr>
                                        <td>{{ __('order.total.discount') }}</td>
                                        <td colspan="
                                        @if ($order->invoice_country === 'BEL')
                                        3
                                        @else
                                        1
                                        @endif
                                        " style="text-align: right">-@money($order->discount)</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>{{ __('order.total.title') }}</td>
                                    <td style="text-align: right">@money($order->total_price)</td>
                                    @if ($order->invoice_country === 'BEL')
                                        <td style="text-align: right">@money($order->total_tax)</td>
                                        <td style="text-align: right"><b>@money($order->total_price_with_tax)</b></td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </selection>
                </td>

            </tr>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <h3>{{ __('order.disclaimer.title') }}</h3>
                        <p>{{ __('order.disclaimer.text') }}</p>
                    </td>
                </tr>
            </tfoot>

        </table>
    </div>
</body>

</html>
