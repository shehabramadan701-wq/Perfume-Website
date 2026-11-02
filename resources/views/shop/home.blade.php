@extends('layouts.shop')

{{-- ============ INTRO: the perfume falls from the sky ============ --}}
@section('intro')
<div id="intro" aria-hidden="true">
    @if (file_exists(public_path(config('shop.intro_video'))))
        {{-- If you add public/videos/intro.mp4 it will play instead of the animation --}}
        <video src="{{ asset(config('shop.intro_video')) }}" muted playsinline preload="auto"></video>
    @else
        <canvas></canvas>
        <div class="intro-flash"></div>
        <div class="intro-ring"></div>
        <div class="intro-ring r2"></div>
        <div class="intro-bottle"><img src="{{ asset('images/hero-bottle.webp') }}" alt=""></div>
        <div class="intro-title">{{ config('shop.name') }}<small>ESSENCE COLLECTION</small></div>
    @endif
    <button class="intro-skip" type="button">SKIP</button>
</div>
@endsection

@section('content')
{{-- ============ HERO ============ --}}
<section class="hero">
    <div class="container hero-grid">
        <div>
            <span class="badge reveal d1">New Collection — {{ date('Y') }}</span>
            <h1 class="reveal d2">A Scent That<br>Leaves a <span class="accent">Mark.</span></h1>
            <p class="lead reveal d3">Discover a bold composition of warm woods, amber, and refined spices. Crafted for those who prefer to be remembered.</p>

            <div class="hero-actions reveal d4">
                <a href="{{ route('collection') }}" class="btn btn-primary">Explore Collection
                    <svg class="icon" viewBox="0 0 24 24" style="width:14px"><path d="M7 17 17 7M8 7h9v9"/></svg>
                </a>
                <a href="{{ route('story') }}" class="btn-link">Discover the Story <span class="arrow"></span></a>
            </div>

            <div class="hero-stats reveal d5">
                <div><strong>0{{ max(1, $featured->count()) }}</strong><span>Signature<br>Collection</span></div>
                <div><strong>{{ $hero->size_ml ?? 50 }} ML</strong><span>Eau de<br>Parfum</span></div>
                <div><span class="stars">★★★★★</span><span>Loved by fragrance enthusiasts</span></div>
            </div>
        </div>

        <div class="hero-visual reveal d3">
            <div class="hero-frame">
                <img src="{{ asset('images/hero-bottle.webp') }}" alt="{{ $hero->name ?? 'Signature perfume' }}">
                <div class="vertical-text">{{ config('shop.name') }} — Essence Collection</div>
            </div>
        </div>
    </div>

    <div class="container hero-bottom reveal d5">
        @include('partials.socials')
        <a href="#collection" class="scroll-hint">SCROLL TO DISCOVER</a>
        <button class="play-btn" type="button" data-replay-intro><span class="circle"></span><span>Play Atmosphere</span></button>
    </div>
</section>

<div class="marquee">
    <div class="marquee-track">
        @foreach (range(1, 2) as $i)
            <span>Oud</span><span>Amber</span><span>Saffron</span><span>Vanilla</span><span>Iris</span><span>Leather</span><span>Rose</span><span>Vetiver</span>
        @endforeach
    </div>
</div>

{{-- ============ FEATURED ============ --}}
<section class="block" id="collection">
    <div class="container">
        <div class="section-head on-scroll">
            <div>
                <span class="eyebrow">The Collection</span>
                <h2 class="section-title">Signature Scents</h2>
            </div>
            <a href="{{ route('collection') }}" class="btn-link">View all perfumes <span class="arrow"></span></a>
        </div>
        <div class="grid">
            @forelse ($featured as $product)
                @include('partials.product-card')
            @empty
                <p class="empty">Add products from the dashboard to show them here.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- ============ STORY ============ --}}
<section class="block">
    <div class="container story">
        <div class="story-img on-scroll"><img src="{{ asset('images/perfume-1.webp') }}" alt="Our craft"></div>
        <div class="on-scroll">
            <span class="eyebrow">Our Story</span>
            <h2 class="section-title">Crafted slowly,<br>worn <em>endlessly</em>.</h2>
            <p>Every {{ config('shop.name') }} fragrance begins with rare raw materials — aged oud, golden amber and hand-picked spices — blended in small batches and left to mature until every note sings in harmony.</p>
            <div class="notes-list">
                <div><small>Top</small><strong>Cardamom</strong></div>
                <div><small>Heart</small><strong>Amber</strong></div>
                <div><small>Base</small><strong>Aged Oud</strong></div>
            </div>
            <a href="{{ route('story') }}" class="btn btn-outline">Read our story</a>
        </div>
    </div>
</section>

{{-- ============ FEATURES ============ --}}
<section class="block" style="padding-top:0">
    <div class="container features">
        <div class="feature on-scroll"><div class="ico">✦</div><h4>Long Lasting</h4><p>High concentration Eau de Parfum that stays with you all day.</p></div>
        <div class="feature on-scroll"><div class="ico">❖</div><h4>Rare Ingredients</h4><p>Natural oils sourced from the finest growers worldwide.</p></div>
        <div class="feature on-scroll"><div class="ico">⛟</div><h4>Fast Delivery</h4><p>Delivered to your door in 2–4 days, free over {{ money(config('shop.free_shipping_over')) }}.</p></div>
        <div class="feature on-scroll"><div class="ico">⛨</div><h4>Secure Payment</h4><p>Pay by Visa, Mastercard, wallets or cash on delivery.</p></div>
    </div>
</section>

<section class="block">
    <div class="container quote on-scroll">
        <blockquote>“Perfume is the most intense form of memory.”</blockquote>
        <cite>— Jean-Paul Guerlain</cite>
    </div>
</section>

<section class="block" style="padding-top:0">
    <div class="container">
        <div class="cta on-scroll">
            <span class="eyebrow">Need help choosing?</span>
            <h2 class="section-title">Find your signature scent</h2>
            <p>Tell us what you love and our fragrance experts will recommend the perfect perfume for you.</p>
            <a href="{{ route('contact') }}" class="btn btn-primary">Contact us</a>
        </div>
    </div>
</section>
@endsection
