@php
    $unread = \App\Models\ContactMessage::where('is_read', false)->count();
    $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
    $nav = [
        ['admin.dashboard', 'Dashboard', '<path d="M3 13h8V3H3zM13 21h8V11h-8zM3 21h8v-6H3zM13 3v6h8V3z"/>', null],
        ['admin.orders.*', 'Orders', '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0"/>', $pendingOrders],
        ['admin.products.*', 'Products', '<path d="M9 3h6v3H9zM8 6h8l2 4v9a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-9z"/><path d="M9 14h6"/>', null],
        ['admin.customers.*', 'Customers', '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>', null],
        ['admin.accounting', 'Accounting', '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>', null],
        ['admin.messages.*', 'Messages', '<path d="M4 4h16v12H5.2L4 17.2z"/><path d="m4 4 8 7 8-7"/>', $unread],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('shop.name') }} Admin</title>
    <link rel="icon" href="{{ asset('images/perfume-1.webp') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=1">
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="brand">{{ config('shop.name') }}<span>.</span><small>DASHBOARD</small></a>

        <div class="side-label">Menu</div>
        <nav class="side-nav">
            @foreach ($nav as [$route, $label, $icon, $count])
                <a href="{{ route(str_replace('.*', '.index', $route)) }}" class="{{ request()->routeIs($route) ? 'active' : '' }}">
                    <svg class="icon" viewBox="0 0 24 24">{!! $icon !!}</svg> {{ $label }}
                    @if ($count)<span class="count">{{ $count }}</span>@endif
                </a>
            @endforeach
        </nav>

        <div class="side-label">Store</div>
        <nav class="side-nav">
            <a href="{{ route('home') }}" target="_blank"><svg class="icon" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg> View website</a>
        </nav>

        <div class="side-foot">
            <strong>Add a new perfume</strong>
            <p>Upload a photo and publish it to your store in seconds.</p>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">+ New product</a>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:14px">
                <button class="btn btn-ghost btn-sm menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                <div>
                    <h1>@yield('title', 'Dashboard')</h1>
                    <div class="sub">@yield('subtitle', now()->format('l, d F Y'))</div>
                </div>
            </div>
            <div class="toolbar">
                @yield('actions')
                <div class="user-chip">
                    <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <span>{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="btn btn-ghost btn-sm" title="Log out">Log out</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="content">
            @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
            @yield('content')
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>
