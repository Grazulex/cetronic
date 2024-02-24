@extends('front.layout.default')
@section('content')
    <section class="section_about-us">
        <h2 class="section_about-us_title">{{ __('order.detail') }} {{ strtoupper($order->reference) }}</h2>
        <div class="container">
            <section class="order_date">
                {{ __('order.date') }} {{ $order->created_at }}
            </section>
            <section class="order_locations">
                <section class="order_location_shipping">
                    <h3>{{ __('order.location.shipping') }}</h3>
                    {{ $order->shipping_company }} <br />
                    {{ $order->shipping_name }} <br />
                    {{ $order->shipping_street }} {{ $order->shipping_street_number }} <br />
                    {{ $order->shipping_zip }} {{ $order->shipping_city }} <br />
                    {{ $order->shipping_country }}
                </section>
                <section class="order_location_invoice">
                    <h3>{{ __('order.location.invoice') }}</h3>
                    {{ $order->invoice_vat }} <br />
                    {{ $order->invoice_company }} <br />
                    {{ $order->invoice_name }} <br />
                    {{ $order->invoice_street }} {{ $order->invoice_street_number }} <br />
                    {{ $order->invoice_zip }} {{ $order->invoice_city }} <br />
                    {{ $order->invoice_country }}
                </section>
            </section>

            <selection class="order_comment">
                <h3>{{ __('order.comment') }}</h3>
                {{ $order->comment }}
            </selection>

            <selection class="order_items">
                <h3>{{ __('order.item') }}</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('order.items.reference') }}</th>
                            <th>{{ __('order.items.unit') }}</th>
                            <th>{{ __('order.items.quantity') }}</th>
                            <th>{{ __('order.items.price') }}</th>
                    </thead>
                    <tbody>
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
                                    @money($item->price_paid)
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td style="text-align: right">@money($item->price_paid * $item->quantity)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </selection>
            <selection class="order_total">
                <h3>{{ __('order.total.title') }}</h3>
                <table class="table">
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
                        @if ($order->total_shipping > 0)
                            <tr>
                                <td>{{ __('order.total.shipping') }}</td>
                                <td style="text-align: right">@money($order->total_shipping)</td>
                                @if ($order->invoice_country === 'BEL')
                                    <td style="text-align: right">@money($order->total_shipping_tax)</td>
                                    <td style="text-align: right">@money($order->total_shipping_with_tax)</td>
                                @endif
                            </tr>
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
        </div>
    </section>
    <x-section-brands-component />
    <x-section-features-component />
@endsection
