@extends('layouts.admin')
@section('title', 'Customers')
@section('subtitle', $customers->total().' customers')

@section('content')
<div class="card">
    <div class="card-head">
        <form class="toolbar">
            <input class="input" name="q" value="{{ request('q') }}" placeholder="Name, email or phone…">
            <button class="btn btn-ghost">Search</button>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Customer</th><th>Phone</th><th>City</th><th>Orders</th><th>Total paid</th><th>Since</th></tr></thead>
            <tbody>
            @forelse ($customers as $customer)
                <tr onclick="location='{{ route('admin.customers.show', $customer) }}'" style="cursor:pointer">
                    <td><div class="cell-main"><div class="avatar" style="width:36px;height:36px;font-size:13px">{{ strtoupper(substr($customer->name, 0, 1)) }}</div><div><strong>{{ $customer->name }}</strong><small>{{ $customer->email }}</small></div></div></td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->city }}</td>
                    <td>{{ $customer->orders_count }}</td>
                    <td><strong>{{ money($customer->paid_total ?? 0) }}</strong></td>
                    <td class="muted">{{ $customer->created_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">No customers yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $customers->links() }}
</div>
@endsection
