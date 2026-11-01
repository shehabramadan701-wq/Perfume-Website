@extends('layouts.shop')
@section('title', 'Checkout — '.config('shop.name'))

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumbs"><a href="{{ route('cart') }}">Bag</a> / Checkout</div>
        <h1 class="section-title">Checkout</h1>
    </div>
</section>

<section class="container">
    @include('partials.alerts')

    <form method="POST" action="{{ route('checkout.store') }}" class="two-col">
        @csrf
        <div class="panel">
            <h3 class="serif" style="font-size:28px;font-weight:500;margin-bottom:20px">Delivery details</h3>
            <div class="form-grid">
                @foreach ([
                    'name' => ['Full name', 'text'], 'phone' => ['Phone', 'tel'],
                    'email' => ['Email', 'email'], 'city' => ['City', 'text'],
                ] as $field => [$label, $type])
                    <div class="field">
                        <label for="{{ $field }}">{{ $label }}</label>
                        <input class="input" id="{{ $field }}" type="{{ $type }}" name="{{ $field }}" value="{{ old($field) }}" required>
                        @error($field)<span class="error">{{ $message }}</span>@enderror
                    </div>
                @endforeach
                <div class="field full">
                    <label for="address">Address</label>
                    <input class="input" id="address" name="address" value="{{ old('address') }}" placeholder="Street, building, floor, apartment" required>
                    @error('address')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field full">
                    <label for="notes">Order notes (optional)</label>
                    <textarea class="input" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>

            <h3 class="serif" style="font-size:28px;font-weight:500;margin:14px 0 20px">Payment</h3>
            <div class="pay-options">
                <label class="pay-option {{ $methods['paymob'] ? '' : 'disabled' }}">
                    <input type="radio" name="payment_method" value="paymob" @checked(old('payment_method', $methods['paymob'] ? 'paymob' : 'cod') === 'paymob') @disabled(! $methods['paymob'])>
                    <div><strong>Credit / Debit card</strong><small>Secure payment by Paymob {{ $methods['paymob'] ? '' : '(not configured yet)' }}</small></div>
                    <div class="cards"><span>VISA</span><span class="mc">MC</span></div>
                </label>
                <label class="pay-option {{ $methods['stripe'] ? '' : 'disabled' }}">
                    <input type="radio" name="payment_method" value="stripe" @checked(old('payment_method') === 'stripe') @disabled(! $methods['stripe'])>
                    <div><strong>Card (International)</strong><small>Secure payment by Stripe {{ $methods['stripe'] ? '' : '(not configured yet)' }}</small></div>
                    <div class="cards"><span>VISA</span><span class="mc">MC</span></div>
                </label>
                <label class="pay-option">
                    <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', $methods['paymob'] ? 'paymob' : 'cod') === 'cod')>
                    <div><strong>Cash on delivery</strong><small>Pay when your order arrives</small></div>
                </label>
            </div>
            @error('payment_method')<span class="error">{{ $message }}</span>@enderror
        </div>

        <div class="panel">
            <h3 class="serif" style="font-size:28px;font-weight:500;margin-bottom:16px">Your order</h3>
            @foreach ($items as $line)
                <div class="summary-row"><span>{{ $line->product->name }} × {{ $line->quantity }}</span><span>{{ money($line->total) }}</span></div>
            @endforeach
            <div class="summary-row" style="border-top:1px solid var(--line);margin-top:10px;padding-top:16px"><span>Subtotal</span><span>{{ money($subtotal) }}</span></div>
            <div class="summary-row"><span>Shipping</span><span>{{ $shipping ? money($shipping) : 'Free' }}</span></div>
            <div class="summary-row total"><span>Total</span><strong>{{ money($total) }}</strong></div>
            <button class="btn btn-primary btn-block" style="margin-top:24px">Place order</button>
            <p style="color:var(--muted);font-size:12px;margin-top:14px;text-align:center">🔒 Your card details are entered on the secure payment page — we never store them.</p>
        </div>
    </form>
</section>
@endsection
