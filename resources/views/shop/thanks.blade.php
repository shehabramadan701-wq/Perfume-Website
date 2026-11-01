@extends('layouts.shop')
@section('title', 'Thank you — '.config('shop.name'))

@section('content')
<section class="page-hero">
    <div class="container" style="max-width:720px">
        <span class="eyebrow">Order {{ $order->number }}</span>
        <h1 class="section-title">Thank you, {{ strtok($order->name, ' ') }}!</h1>
        <p>
            @if ($order->payment_status === 'paid')
                Your payment was received successfully. We are preparing your order now.
            @else
                Your order has been placed. You will pay {{ money($order->total) }} on delivery.
            @endif
        </p>

        <div class="panel" style="text-align:left;margin-top:40px">
            @foreach ($order->items as $item)
                <div class="summary-row"><span>{{ $item->product_name }} × {{ $item->quantity }}</span><span>{{ money($item->total) }}</span></div>
            @endforeach
            <div class="summary-row"><span>Shipping</span><span>{{ $order->shipping > 0 ? money($order->shipping) : 'Free' }}</span></div>
            <div class="summary-row total"><span>Total</span><strong>{{ money($order->total) }}</strong></div>
            <div class="summary-row"><span>Payment</span><span>{{ $order->payment_method_label }} @if($order->card_last4) · **** {{ $order->card_last4 }} @endif</span></div>
            <div class="summary-row"><span>Deliver to</span><span>{{ $order->address }}, {{ $order->city }}</span></div>
        </div>
        <br>
        <a href="{{ route('collection') }}" class="btn btn-primary">Continue shopping</a>
    </div>
</section>
@endsection
