@extends('layouts.admin')
@section('title', 'Dashboard')
@section('subtitle', 'Welcome back, '.auth()->user()->name.' — here is what is happening in your store.')

@php
    // Validated colors for the three payment methods (used in every chart/legend)
    $methodColors = ['paymob' => '#d4652a', 'stripe' => '#3f86c9', 'cod' => '#a8862a'];
    $methodTotal = max(1, $methods->sum('total'));
@endphp

@section('content')
<div class="grid g-4 mb">
    <div class="card stat hero-stat">
        <div class="top">Total revenue <span class="ico"><svg class="icon" viewBox="0 0 24 24"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span></div>
        <div class="value">{{ number_format($stats['revenue']) }} <small>{{ config('shop.currency') }}</small></div>
        <div class="hint">{{ number_format($stats['revenue_month']) }} this month</div>
    </div>
    <div class="card stat">
        <div class="top">Paid by card <span class="ico"><svg class="icon" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></span></div>
        <div class="value">{{ number_format($stats['card']) }} <small>{{ config('shop.currency') }}</small></div>
        <div class="hint">Visa / Mastercard via Paymob & Stripe</div>
    </div>
    <div class="card stat">
        <div class="top">Orders <span class="ico"><svg class="icon" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18"/></svg></span></div>
        <div class="value">{{ $stats['orders'] }}</div>
        <div class="hint">{{ $stats['pending'] }} waiting to be shipped</div>
    </div>
    <div class="card stat">
        <div class="top">Customers <span class="ico"><svg class="icon" viewBox="0 0 24 24"><circle cx="9" cy="7" r="4"/><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/></svg></span></div>
        <div class="value">{{ $stats['customers'] }}</div>
        <div class="hint">{{ $stats['messages'] }} unread messages</div>
    </div>
</div>

<div class="grid g-main mb">
    <div class="card">
        <div class="card-head">
            <div><h3>Revenue — last 14 days</h3><span class="muted" style="font-size:12px">Paid orders, {{ config('shop.currency') }}</span></div>
            <a href="{{ route('admin.accounting') }}" class="btn btn-ghost btn-sm">Open accounting →</a>
        </div>
        <div class="chart-box"><canvas id="revenueChart" aria-label="Revenue per day for the last 14 days" role="img"></canvas></div>
    </div>

    <div class="card">
        <div class="card-head"><h3>Payment methods</h3></div>
        <div class="seg-bar" role="img" aria-label="Share of revenue by payment method">
            @foreach ($methodColors as $key => $color)
                @if (isset($methods[$key]))
                    <span style="width: {{ $methods[$key]->total / $methodTotal * 100 }}%; background: {{ $color }}" title="{{ \App\Models\Order::PAYMENT_METHODS[$key] }}"></span>
                @endif
            @endforeach
        </div>
        @foreach ($methodColors as $key => $color)
            <div class="list-row">
                <span><i class="legend-dot" style="background: {{ $color }}"></i>{{ \App\Models\Order::PAYMENT_METHODS[$key] }}</span>
                <span class="right">
                    <strong>{{ money($methods[$key]->total ?? 0) }}</strong><br>
                    <small class="muted">{{ $methods[$key]->count ?? 0 }} orders · {{ round(($methods[$key]->total ?? 0) / $methodTotal * 100) }}%</small>
                </span>
            </div>
        @endforeach
    </div>
</div>

<div class="grid g-main">
    <div class="card">
        <div class="card-head"><h3>Recent orders</h3><a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">View all</a></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Order</th><th>Customer</th><th>Payment</th><th>Status</th><th class="right">Total</th></tr></thead>
                <tbody>
                @forelse ($recentOrders as $order)
                    <tr onclick="location='{{ route('admin.orders.show', $order) }}'" style="cursor:pointer">
                        <td><strong>{{ $order->number }}</strong><br><small class="muted">{{ $order->created_at->diffForHumans() }}</small></td>
                        <td>{{ $order->name }}<br><small class="muted">{{ $order->phone }}</small></td>
                        <td>@include('admin.status', ['status' => $order->payment_status])<br><small class="muted">{{ $order->payment_method_label }}</small></td>
                        <td>@include('admin.status', ['status' => $order->status])</td>
                        <td class="right"><strong>{{ money($order->total) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No orders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h3>Best sellers</h3></div>
        @forelse ($topProducts as $i => $p)
            <div class="list-row">
                <span><span class="muted" style="margin-right:10px">0{{ $i + 1 }}</span>{{ $p->product_name }}</span>
                <span class="right"><strong>{{ $p->qty }} sold</strong><br><small class="muted">{{ money($p->revenue) }}</small></span>
            </div>
        @empty
            <p class="empty-state">No sales yet.</p>
        @endforelse

        <div class="list-row" style="margin-top:10px">
            <span class="muted">Products in store</span><strong>{{ $stats['products'] }}</strong>
        </div>
        <div class="list-row">
            <span class="muted">Low stock (≤ 5)</span>
            <a href="{{ route('admin.products.index') }}"><span class="pill {{ $stats['low_stock'] ? 'warn' : 'good' }}">{{ $stats['low_stock'] }} products</span></a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/vendor/chart.umd.min.js') }}"></script>
<script>
    const ctx = document.getElementById('revenueChart');
    const g = ctx.getContext('2d').createLinearGradient(0, 0, 0, 290);
    g.addColorStop(0, 'rgba(212, 101, 42, .28)');
    g.addColorStop(1, 'rgba(212, 101, 42, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chart['labels']),
            datasets: [{
                label: 'Revenue',
                data: @json($chart['values']),
                borderColor: '#d4652a', backgroundColor: g, fill: true, borderWidth: 2, cubicInterpolationMode: 'monotone',
                pointRadius: 0, pointHoverRadius: 5, pointHoverBackgroundColor: '#d4652a', pointHoverBorderColor: '#17100c', pointHoverBorderWidth: 2,
            }],
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#241914', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1, padding: 12,
                    titleColor: '#f1e9e3', bodyColor: '#c9bdb4', displayColors: false,
                    callbacks: { label: c => c.parsed.y.toLocaleString() + ' {{ config('shop.currency') }}' },
                },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#8a7f77', font: { size: 11 } }, border: { display: false } },
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8a7f77', font: { size: 11 } }, border: { display: false } },
            },
        },
    });
</script>
@endpush
