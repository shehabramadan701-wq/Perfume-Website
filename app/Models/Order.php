<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    public const STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'failed', 'refunded'];

    public const PAYMENT_METHODS = [
        'stripe' => 'Card (Stripe)',
        'paymob' => 'Card (Paymob)',
        'cod' => 'Cash on Delivery',
    ];

    protected $fillable = [
        'number', 'customer_id', 'name', 'email', 'phone', 'city', 'address', 'notes',
        'subtotal', 'shipping', 'total', 'currency', 'status',
        'payment_method', 'payment_status', 'payment_reference', 'transaction_id',
        'card_brand', 'card_last4', 'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->number ??= 'AUR-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    public function isCard(): bool
    {
        return in_array($this->payment_method, ['stripe', 'paymob']);
    }

    /** Mark the order as paid (called from payment callbacks). */
    public function markAsPaid(?string $transactionId = null, ?string $brand = null, ?string $last4 = null): void
    {
        if ($this->payment_status === 'paid') {
            return;
        }

        $this->update([
            'payment_status' => 'paid',
            'status' => $this->status === 'pending' ? 'processing' : $this->status,
            'transaction_id' => $transactionId ?? $this->transaction_id,
            'card_brand' => $brand ?? $this->card_brand,
            'card_last4' => $last4 ? substr($last4, -4) : $this->card_last4,
            'paid_at' => now(),
        ]);

        // Reduce stock once payment is confirmed
        foreach ($this->items as $item) {
            $item->product?->decrement('stock', min($item->quantity, $item->product->stock));
        }
    }
}
