@php
    $map = [
        'pending' => 'warn', 'processing' => 'info', 'shipped' => 'info', 'delivered' => 'good', 'cancelled' => 'bad',
        'unpaid' => 'warn', 'paid' => 'good', 'failed' => 'bad', 'refunded' => 'neutral',
    ];
@endphp
<span class="pill {{ $map[$status] ?? 'neutral' }}">{{ ucfirst($status) }}</span>
