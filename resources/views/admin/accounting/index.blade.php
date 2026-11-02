@extends('layouts.admin')
@section('title', 'Accounting')
@section('subtitle', 'Payments & expenses from '.$from->format('d M Y').' to '.$to->format('d M Y'))
@section('actions')
    <a href="{{ route('admin.accounting.export', request()->only('from', 'to')) }}" class="btn btn-ghost">⇩ Export CSV</a>
@endsection

@php $methodColors = ['paymob' => '#d4652a', 'stripe' => '#3f86c9', 'cod' => '#a8862a']; @endphp

@section('content')
<div class="card mb">
    <form class="toolbar">
        <label class="muted">From</label><input class="input" type="date" name="from" value="{{ $from->toDateString() }}">
        <label class="muted">To</label><input class="input" type="date" name="to" value="{{ $to->toDateString() }}">
        <button class="btn btn-primary">Apply</button>
        <a class="btn btn-ghost" href="{{ route('admin.accounting', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}">This month</a>
        <a class="btn btn-ghost" href="{{ route('admin.accounting', ['from' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(), 'to' => now()->subMonthNoOverflow()->endOfMonth()->toDateString()]) }}">Last month</a>
        <a class="btn btn-ghost" href="{{ route('admin.accounting', ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()]) }}">This year</a>
    </form>
</div>

<div class="grid g-4 mb">
    <div class="card stat hero-stat"><div class="top">Total income</div><div class="value">{{ number_format($summary['income'], 2) }} <small>{{ config('shop.currency') }}</small></div><div class="hint">All paid orders</div></div>
    <div class="card stat"><div class="top">Paid by card (Visa / MC)</div><div class="value">{{ number_format($summary['card'], 2) }}</div><div class="hint">Paymob + Stripe</div></div>
    <div class="card stat"><div class="top">Cash collected</div><div class="value">{{ number_format($summary['cash'], 2) }}</div><div class="hint">{{ number_format($summary['unpaid_cod'], 2) }} still to collect (COD)</div></div>
    <div class="card stat"><div class="top">Net profit</div><div class="value" style="color: {{ $summary['net'] >= 0 ? 'var(--good)' : 'var(--bad)' }}">{{ number_format($summary['net'], 2) }}</div><div class="hint">Income − expenses ({{ number_format($summary['expenses'], 2) }})</div></div>
</div>

<div class="grid g-acc mb">
    <div class="card">
        <div class="card-head">
            <h3>Transactions</h3>
            <div class="toolbar">
                @foreach (['' => 'All'] + \App\Models\Order::PAYMENT_METHODS as $k => $l)
                    <a href="{{ route('admin.accounting', ['method' => $k ?: null] + request()->only('from', 'to')) }}" class="btn btn-sm {{ request('method', '') === $k ? 'btn-primary' : 'btn-ghost' }}">{{ $l }}</a>
                @endforeach
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Order</th><th>Customer</th><th>Method</th><th>Card / Transaction</th><th class="right">Amount</th></tr></thead>
                <tbody>
                @forelse ($transactions as $tx)
                    <tr>
                        <td>{{ $tx->paid_at->format('d M Y') }}<br><small class="muted">{{ $tx->paid_at->format('H:i') }}</small></td>
                        <td><a href="{{ route('admin.orders.show', $tx) }}"><strong>{{ $tx->number }}</strong></a></td>
                        <td>{{ $tx->name }}<br><small class="muted">{{ $tx->phone }}</small></td>
                        <td><i class="legend-dot" style="background: {{ $methodColors[$tx->payment_method] ?? '#777' }}"></i>{{ $tx->payment_method_label }}</td>
                        <td>
                            @if ($tx->card_last4)<span class="card-badge">{{ $tx->card_brand }}</span>•••• {{ $tx->card_last4 }}@else<span class="muted">—</span>@endif
                            @if ($tx->transaction_id)<br><small class="muted">#{{ $tx->transaction_id }}</small>@endif
                        </td>
                        <td class="right"><strong>{{ money($tx->total) }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No payments in this period.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $transactions->links() }}
    </div>

    <div class="acc-side">
        <div class="card">
            <div class="card-head"><h3>Income by method</h3></div>
            @php $sum = max(1, $byMethod->sum('total')); @endphp
            <div class="seg-bar" role="img" aria-label="Share of income by payment method">
                @foreach ($methodColors as $k => $c)
                    @if (isset($byMethod[$k]))<span style="width: {{ $byMethod[$k]->total / $sum * 100 }}%; background: {{ $c }}"></span>@endif
                @endforeach
            </div>
            @foreach ($methodColors as $k => $c)
                <div class="list-row">
                    <span><i class="legend-dot" style="background: {{ $c }}"></i>{{ \App\Models\Order::PAYMENT_METHODS[$k] }}</span>
                    <span class="right"><strong>{{ money($byMethod[$k]->total ?? 0) }}</strong><br><small class="muted">{{ $byMethod[$k]->count ?? 0 }} payments</small></span>
                </div>
            @endforeach
            <div class="list-row"><span class="muted">Shipping fees collected</span><strong>{{ money($summary['shipping']) }}</strong></div>
            <div class="list-row"><span class="muted">Refunded</span><strong>{{ money($summary['refunded']) }}</strong></div>
        </div>

        <div class="card">
            <div class="card-head"><h3>Expenses</h3><strong>{{ money($summary['expenses']) }}</strong></div>
            <form method="POST" action="{{ route('admin.expenses.store') }}" class="mb">
                @csrf
                <div class="field"><input class="input" name="title" placeholder="e.g. Instagram ads" required></div>
                <div class="grid g-2" style="gap:10px;margin-bottom:10px">
                    <input class="input" type="number" step="0.01" name="amount" placeholder="Amount" required>
                    <select name="category">@foreach (\App\Models\Expense::CATEGORIES as $c)<option>{{ $c }}</option>@endforeach</select>
                </div>
                <div class="grid g-2" style="gap:10px">
                    <input class="input" type="date" name="spent_at" value="{{ now()->toDateString() }}" required>
                    <button class="btn btn-primary" style="justify-content:center">+ Add expense</button>
                </div>
            </form>
            @forelse ($expenseList as $e)
                <div class="list-row">
                    <span>{{ $e->title }}<br><small class="muted">{{ $e->category }} · {{ $e->spent_at->format('d M') }}</small></span>
                    <span style="display:flex;align-items:center;gap:10px">
                        <strong>{{ number_format($e->amount, 2) }}</strong>
                        <form method="POST" action="{{ route('admin.expenses.destroy', $e) }}" onsubmit="return confirm('Delete this expense?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" title="Delete">✕</button></form>
                    </span>
                </div>
            @empty
                <p class="muted" style="text-align:center;padding:16px">No expenses in this period.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
