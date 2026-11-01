@extends('layouts.admin')
@section('title', 'Order '.$order->number)
@section('subtitle', 'Placed '.$order->created_at->format('d M Y, H:i'))
@section('actions')
    <button onclick="window.print()" class="btn btn-ghost">Print</button>
@endsection

@section('content')
<div class="grid g-main">
    <div>
        <div class="card mb">
            <div class="card-head"><h3>Items</h3>@include('admin.status', ['status' => $order->status])</div>
            <table>
                <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th class="right">Total</th></tr></thead>
                <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td><div class="cell-main">@if ($item->product)<img class="thumb" src="{{ $item->product->image_url }}" alt="">@endif<strong>{{ $item->product_name }}</strong></div></td>
                        <td>{{ money($item->price) }}</td>
                        <td>× {{ $item->quantity }}</td>
                        <td class="right">{{ money($item->total) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="max-width:320px;margin:16px 0 0 auto">
                <div class="list-row"><span class="muted">Subtotal</span><span>{{ money($order->subtotal) }}</span></div>
                <div class="list-row"><span class="muted">Shipping</span><span>{{ money($order->shipping) }}</span></div>
                <div class="list-row"><strong>Total</strong><strong style="font-size:18px">{{ money($order->total) }}</strong></div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h3>Payment</h3>@include('admin.status', ['status' => $order->payment_status])</div>
            <dl class="kv">
                <dt>Method</dt><dd>{{ $order->payment_method_label }}</dd>
                <dt>Card</dt><dd>@if ($order->card_last4)<span class="card-badge">{{ $order->card_brand }}</span>•••• {{ $order->card_last4 }}@else — @endif</dd>
                <dt>Transaction ID</dt><dd>{{ $order->transaction_id ?: '—' }}</dd>
                <dt>Gateway ref.</dt><dd style="word-break:break-all">{{ $order->payment_reference ?: '—' }}</dd>
                <dt>Paid at</dt><dd>{{ $order->paid_at?->format('d M Y, H:i') ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div>
        <div class="card mb">
            <div class="card-head"><h3>Update order</h3></div>
            <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                @csrf @method('PUT')
                <div class="field">
                    <label class="lbl">Order status</label>
                    <select name="status">@foreach (\App\Models\Order::STATUSES as $s)<option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
                </div>
                <div class="field">
                    <label class="lbl">Payment status</label>
                    <select name="payment_status">@foreach (\App\Models\Order::PAYMENT_STATUSES as $s)<option value="{{ $s }}" @selected($order->payment_status === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
                </div>
                <button class="btn btn-primary" style="width:100%;justify-content:center">Save</button>
            </form>
        </div>

        <div class="card mb">
            <div class="card-head"><h3>Customer</h3><a href="{{ route('admin.customers.show', $order->customer) }}" class="btn btn-ghost btn-sm">Profile</a></div>
            <dl class="kv" style="grid-template-columns:80px 1fr">
                <dt>Name</dt><dd>{{ $order->name }}</dd>
                <dt>Email</dt><dd><a href="mailto:{{ $order->email }}">{{ $order->email }}</a></dd>
                <dt>Phone</dt><dd><a href="tel:{{ $order->phone }}">{{ $order->phone }}</a></dd>
                <dt>City</dt><dd>{{ $order->city }}</dd>
                <dt>Address</dt><dd>{{ $order->address }}</dd>
                @if ($order->notes)<dt>Notes</dt><dd>{{ $order->notes }}</dd>@endif
            </dl>
        </div>

        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Delete this order permanently?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger" style="width:100%;justify-content:center">Delete order</button>
        </form>
    </div>
</div>
@endsection
