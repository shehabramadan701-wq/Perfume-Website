@extends('layouts.admin')
@section('title', $customer->name)
@section('subtitle', 'Customer since '.$customer->created_at->format('d M Y'))

@section('content')
<div class="grid g-3 mb">
    <div class="card stat"><div class="top">Total paid</div><div class="value">{{ money($customer->total_spent) }}</div></div>
    <div class="card stat"><div class="top">Orders</div><div class="value">{{ $customer->orders->count() }}</div></div>
    <div class="card stat"><div class="top">Last order</div><div class="value" style="font-size:20px">{{ $customer->orders->first()?->created_at->format('d M Y') ?? '—' }}</div></div>
</div>

<div class="grid g-main">
    <div class="card">
        <div class="card-head"><h3>Order history</h3></div>
        <table>
            <thead><tr><th>Order</th><th>Date</th><th>Payment</th><th>Status</th><th class="right">Total</th></tr></thead>
            <tbody>
            @forelse ($customer->orders as $order)
                <tr onclick="location='{{ route('admin.orders.show', $order) }}'" style="cursor:pointer">
                    <td><strong>{{ $order->number }}</strong></td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td>@include('admin.status', ['status' => $order->payment_status])<br><small class="muted">{{ $order->payment_method_label }}</small></td>
                    <td>@include('admin.status', ['status' => $order->status])</td>
                    <td class="right">{{ money($order->total) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-state">No orders.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div>
        <div class="card mb">
            <div class="card-head"><h3>Contact info</h3></div>
            <dl class="kv" style="grid-template-columns:80px 1fr">
                <dt>Email</dt><dd><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></dd>
                <dt>Phone</dt><dd><a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a></dd>
                <dt>City</dt><dd>{{ $customer->city }}</dd>
                <dt>Address</dt><dd>{{ $customer->address }}</dd>
            </dl>
            @if ($customer->phone)
                <a href="https://wa.me/{{ preg_replace('/\D/', '', preg_replace('/^0/', '20', $customer->phone)) }}" target="_blank" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:18px">WhatsApp customer</a>
            @endif
        </div>
        <div class="card mb">
            <div class="card-head"><h3>Private notes</h3></div>
            <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                @csrf @method('PUT')
                <textarea name="notes" rows="4" placeholder="e.g. prefers woody scents, VIP customer…">{{ $customer->notes }}</textarea>
                <button class="btn btn-primary btn-sm" style="margin-top:10px">Save notes</button>
            </form>
        </div>
        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer and all their orders?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger" style="width:100%;justify-content:center">Delete customer</button>
        </form>
    </div>
</div>
@endsection
