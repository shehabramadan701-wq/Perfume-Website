<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('shop.name').' — A Scent That Leaves a Mark')</title>
    <meta name="description" content="@yield('description', 'Luxury perfumes crafted with warm woods, amber and refined spices.')">
    <link rel="icon" href="{{ asset('images/perfume-1.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}?v=1">
</head>
<body class="@yield('body-class')">
<div class="page-glow"></div>

@yield('intro')

<header class="header">
    <div class="container">
        <a href="{{ route('home') }}" class="logo">{{ config('shop.name') }}<span>.</span></a>

        <nav class="nav">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('collection') }}" class="{{ request()->routeIs('collection', 'product') ? 'active' : '' }}">Collection</a>
            <a href="{{ route('story') }}" class="{{ request()->routeIs('story') ? 'active' : '' }}">Our Story</a>
            <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
        </nav>

        <div class="header-actions">
            <button data-open=".search-box" aria-label="Search">
                <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <span class="label">Search</span>
            </button>
            <a href="{{ route('cart') }}">
                <svg class="icon" viewBox="0 0 24 24"><path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                <span class="label">Bag</span> ({{ \App\Support\Cart::count() }})
            </a>
            <button class="menu-btn" data-open=".drawer" aria-label="Menu">
                <span class="circle"><svg class="icon" viewBox="0 0 24 24"><path d="M6 9h12M6 15h12"/></svg></span>
                <span class="label">Menu</span>
            </button>
        </div>
    </div>
</header>

<div class="overlay"></div>
<aside class="drawer">
    <button class="close" data-close aria-label="Close">&times;</button>
    <nav>
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('collection') }}">Collection</a>
        <a href="{{ route('story') }}">Our Story</a>
        <a href="{{ route('contact') }}">Contact Us</a>
        <a href="{{ route('cart') }}">Bag ({{ \App\Support\Cart::count() }})</a>
    </nav>
    <div class="drawer-foot">
        <p>{{ config('shop.email') }}</p>
        <p>{{ config('shop.phone') }}</p>
    </div>
</aside>
<div class="search-box">
    <div class="container">
        <form action="{{ route('collection') }}">
            <input type="search" name="q" placeholder="Search for a scent…" value="{{ request('q') }}">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success toast">{{ session('success') }}</div>
@endif

<main>
    @yield('content')
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a href="{{ route('home') }}" class="logo">{{ config('shop.name') }}<span>.</span></a>
                <p>Luxury fragrances crafted for those who prefer to be remembered.</p>
                @include('partials.socials')
            </div>
            <div>
                <h5>Shop</h5>
                <ul>
                    <li><a href="{{ route('collection') }}">All perfumes</a></li>
                    <li><a href="{{ route('collection', ['category' => 'Woody']) }}">Woody</a></li>
                    <li><a href="{{ route('collection', ['category' => 'Floral']) }}">Floral</a></li>
                    <li><a href="{{ route('collection', ['category' => 'Oriental']) }}">Oriental</a></li>
                </ul>
            </div>
            <div>
                <h5>House</h5>
                <ul>
                    <li><a href="{{ route('story') }}">Our Story</a></li>
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                    <li><a href="{{ route('cart') }}">Your Bag</a></li>
                </ul>
            </div>
            <div>
                <h5>Contact</h5>
                <ul>
                    <li>{{ config('shop.address') }}</li>
                    <li><a href="mailto:{{ config('shop.email') }}">{{ config('shop.email') }}</a></li>
                    <li><a href="tel:{{ config('shop.phone') }}">{{ config('shop.phone') }}</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <span>&copy; {{ date('Y') }} {{ config('shop.name') }}. All rights reserved.</span>
            <span>Secure payments · Visa · Mastercard · Cash on delivery</span>
        </div>
    </div>
</footer>

@if (config('shop.whatsapp'))
    <a class="whatsapp" href="https://wa.me/{{ config('shop.whatsapp') }}" target="_blank" rel="noopener" aria-label="WhatsApp">
        <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.3 2.2 2.2 0 0 0 .1-1.3c0-.1-.2-.2-.5-.3Z"/></svg>
    </a>
@endif

<script src="{{ asset('js/shop.js') }}?v=2"></script>
</body>
</html>
