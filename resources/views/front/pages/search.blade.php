@extends('front.layout.default')
@section('title'){{ trim($search) }}@stop
@section('description')
    {{ trim($search) }}
@stop
@section('content')
    <section class="section-listing">
        <h2 class="listing-title">
                {{ $search }}
        </h2>
        <div class="container">
            <div class="row">
                <div style="float:left;width:100%;">
                </div>

            </div>

            <div class="row">
               @foreach ($items as $item)
                  <div class="col-xl-4">
                        <x-items.item-component :item="$item" :key="'item-' . $item->id" />
                  </div>
               @endforeach

               {{ $items->appends(request()->input())->links() }}
            </div>

    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
