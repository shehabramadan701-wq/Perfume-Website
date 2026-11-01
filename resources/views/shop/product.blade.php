@extends('layouts.shop')
@section('title', $product->name.' — '.config('shop.name'))
@section('description', $product->short_description)

@section('content')
<section class="container product-page">
    <div class="gallery on-scroll"><img src="{{ $product->image_url }}" alt="{{ $product->name }}"></div>
    <div class="on-scroll">
        <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / <a href="{{ route('collection') }}">Collection</a> / {{ $product->name }}</div>
        @if ($product->category)<span class="badge">{{ $product->category }}</span>@endif
        <h1>{{ $product->name }}</h1>
        <div class="price">
            {{ money($product->price) }}
            @if ($product->old_price > $product->price)<del>{{ money($product->old_price) }}</del>@endif
        </div>
        <p class="desc">{{ $product->description ?: $product->short_description }}</p>

        @include('partials.alerts')

        @if ($product->stock > 0)
            <form method="POST" action="{{ route('cart.add', $product) }}" style="display:flex;gap:16px;flex-wrap:wrap">
                @csrf
                <div class="qty">
                    <button type="button" data-step="-1">−</button>
                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}">
                    <button type="button" data-step="1">+</button>
                </div>
                <button class="btn btn-primary">Add to Bag</button>
            </form>
            @if ($product->stock <= 5)<p style="color:var(--gold);font-size:13px;margin-top:14px">Only {{ $product->stock }} left in stock</p>@endif
        @else
            <p class="alert alert-error">Out of stock</p>
        @endif

        <div class="meta-list">
            <div><small>Notes</small>{{ $product->notes ?: '—' }}</div>
            <div><small>Size</small>{{ $product->size_ml }} ml</div>
            <div><small>Type</small>Eau de Parfum</div>
        </div>
    </div>
</section>

@if ($related->isNotEmpty())
<section class="block">
    <div class="container">
        <div class="section-head"><h2 class="section-title">You may also like</h2></div>
        <div class="grid">
            @foreach ($related as $product)
                @include('partials.product-card')
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
