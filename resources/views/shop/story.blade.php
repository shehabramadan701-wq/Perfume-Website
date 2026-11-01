@extends('layouts.shop')
@section('title', 'Our Story — '.config('shop.name'))

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Our Story</div>
        <h1 class="section-title">A house built on memory</h1>
        <p>From a small atelier to a signature collection loved by fragrance enthusiasts.</p>
    </div>
</section>

<section class="block" style="padding-top:20px">
    <div class="container story">
        <div class="story-img on-scroll"><img src="{{ asset('images/hero-bottle.webp') }}" alt=""></div>
        <div class="on-scroll">
            <span class="eyebrow">Since 2020</span>
            <h2 class="section-title">The art of the slow scent</h2>
            <p>{{ config('shop.name') }} was born from a simple belief: a perfume should be felt before it is noticed, and remembered long after it is gone.</p>
            <p>We work with master perfumers and source rare materials — Cambodian oud, Indian sandalwood, Turkish rose — blending them in small batches and letting each one mature for weeks before bottling.</p>
            <div class="notes-list">
                <div><small>Batches</small><strong>Small</strong></div>
                <div><small>Maturing</small><strong>6 weeks</strong></div>
                <div><small>Oils</small><strong>Natural</strong></div>
            </div>
            <a href="{{ route('collection') }}" class="btn btn-primary">Explore the collection</a>
        </div>
    </div>
</section>
@endsection
