@extends('layouts.admin')
@section('title', 'Orders')
@section('subtitle', $orders->total().' orders')

@section('content')
<div class="card">
    <div class="card-head">
        <form class="toolbar">
            <input class="input" name="q" value="{{ request('q') }}" placeholder="Order #, name, email, phone…">
            <select name="status">
                <option value="">All statuses</option>
                @foreach (\App\Models\Order::STATUSES as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
            </select>
            <select name="payment_status">
                <option value="">All payments</option>
                @foreach (\App\Models\Order::PAYMENT_STATUSES as $s)<option value="{{ $s }}" @selected(request('payment_status') === $s)>{{ ucfirst($s) }}</option>@endforeach
            </select>
            <select name="payment_method">
                <option value="">All methods</option>
                @foreach (\App\Models\Order::PAYMENT_METHODS as $k => $l)<option value="{{ $k }}" @selected(request('payment_method') === $k)>{{ $l }}</option>@endforeach
            </select>
            <button class="btn btn-ghost">Filter</button>
            @if (request()->hasAny(['q', 'status', 'payment_status', 'payment_method']))<a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Reset</a>@endif
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Payment</th><th>Card</th><th>Status</th><th class="right">Total</th></tr></thead>
            <tbody>
            @forelse ($orders as $order)
                <tr onclick="location='{{ route('admin.orders.show', $order) }}'" style="cursor:pointer">
                    <td><strong>{{ $order->number }}</strong></td>
                    <td>{{ $order->name }}<br><small class="muted">{{ $order->email }}</small></td>
                    <td>{{ $order->created_at->format('d M Y') }}<br><small class="muted">{{ $order->created_at->format('H:i') }}</small></td>
                    <td>@include('admin.status', ['status' => $order->payment_status])<br><small class="muted">{{ $order->payment_method_label }}</small></td>
                    <td>
                        @if ($order->card_last4)<span class="card-badge">{{ $order->card_brand }}</span>•••• {{ $order->card_last4 }}@else<span class="muted">—</span>@endif
                    </td>
                    <td>@include('admin.status', ['status' => $order->status])</td>
                    <td class="right"><strong>{{ money($order->total) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-state">No orders found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div>
@endsection
