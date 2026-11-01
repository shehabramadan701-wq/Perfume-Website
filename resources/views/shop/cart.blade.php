@extends('layouts.shop')
@section('title', 'Your Bag — '.config('shop.name'))

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Bag</div>
        <h1 class="section-title">Your Bag</h1>
    </div>
</section>

<section class="container">
    @include('partials.alerts')

    @if ($items->isEmpty())
        <div class="panel empty">
            <p>Your bag is empty.</p><br>
            <a href="{{ route('collection') }}" class="btn btn-primary">Explore the collection</a>
        </div>
    @else
        <div class="two-col">
            <div class="panel">
                @foreach ($items as $line)
                    <div class="cart-line">
                        <img src="{{ $line->product->image_url }}" alt="">
                        <div>
                            <h4>{{ $line->product->name }}</h4>
                            <small style="color:var(--muted)">{{ money($line->product->price) }} · {{ $line->product->size_ml }} ml</small><br>
                            <form method="POST" action="{{ route('cart.remove', $line->product) }}" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="remove">Remove</button>
                            </form>
                        </div>
                        <form method="POST" action="{{ route('cart.update', $line->product) }}">
                            @csrf @method('PATCH')
                            <div class="qty" data-autosubmit>
                                <button type="button" data-step="-1">−</button>
                                <input type="number" name="quantity" value="{{ $line->quantity }}" min="1" max="{{ $line->product->stock }}" onchange="this.form.submit()">
                                <button type="button" data-step="1">+</button>
                            </div>
                        </form>
                        <strong>{{ money($line->total) }}</strong>
                    </div>
                @endforeach
            </div>

            <div class="panel">
                <h3 class="serif" style="font-size:28px;font-weight:500;margin-bottom:16px">Summary</h3>
                <div class="summary-row"><span>Subtotal</span><span>{{ money($subtotal) }}</span></div>
                <div class="summary-row"><span>Shipping</span><span>{{ $shipping ? money($shipping) : 'Free' }}</span></div>
                <div class="summary-row total"><span>Total</span><strong>{{ money($total) }}</strong></div>
                <a href="{{ route('checkout') }}" class="btn btn-primary btn-block" style="margin-top:24px">Checkout</a>
                <a href="{{ route('collection') }}" class="btn-link" style="margin-top:20px">Continue shopping <span class="arrow"></span></a>
            </div>
        </div>
    @endif
</section>
@endsection
