@extends('front.layout.default')
@section('content')

    <section class="login_page">
        <h2 class="login_page_title">{{ __('user.orders') }}</h2>


        <div class="container">

            <x-menu-dashboard-component />

            <div class="container">
                <div class="row mt-5">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('order.date') }}</th>
                                <th>{{ __('order.quantity') }}</th>
                                <th>{{ __('order.item') }}</th>
                                <th>{{ __('order.shipping') }}</th>
                                <th>{{ __('order.discount') }}</th>
                                <th>{{ __('order.total.title') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (Auth::user()->orders as $order)
                                <tr>
                                    <td>{{ strtoupper($order->reference) }}</td>
                                    <td>{{ $order->created_at }}</td>
                                    <td>{{ $order->items()->count() }}</td>
                                    <td style="text-align: right">
                                        @if ($order->invoice_country === 'BEL')
                                            @money($order->total_products_with_tax)
                                        @else
                                            @money($order->total_products)
                                        @endif
                                    </td>
                                    <td style="text-align: right">
                                        @if ($order->invoice_country === 'BEL')
                                            @if ($order->total_shipping_with_tax > 0)
                                                @money($order->total_shipping_with_tax)
                                            @endif
                                        @else
                                            @if ($order->total_shipping> 0)
                                                @money($order->total_shipping)
                                            @endif
                                        @endif
                                    </td>
                                    <td style="text-align: right">
                                        @if ($order->discount > 0)
                                            @money($order->discount)
                                        @endif
                                    </td>
                                    <td style="text-align: right">
                                        @if ($order->invoice_country === 'BEL')
                                            @money($order->total_price_with_tax)
                                        @else
                                            @money($order->total_price)
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('user_orders.zip', $order) }}"
                                            class="button">{{ __('order.zip') }}</a>
                                        <a href="{{ route('user_orders.pdf', $order) }}"
                                            class="button">{{ __('order.pdf') }}</a>
                                        <a href="{{ route('user_orders.detail', $order) }}"
                                            class="button">{{ __('order.view') }}</a>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
