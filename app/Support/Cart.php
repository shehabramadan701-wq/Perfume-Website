<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * A very small session cart: [product_id => quantity]
 */
class Cart
{
    private const KEY = 'cart';

    public static function raw(): array
    {
        return session(self::KEY, []);
    }

    public static function add(Product $product, int $qty = 1): void
    {
        $cart = self::raw();
        $cart[$product->id] = min(($cart[$product->id] ?? 0) + $qty, max($product->stock, 1));
        session([self::KEY => $cart]);
    }

    public static function update(int $productId, int $qty): void
    {
        $cart = self::raw();
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $qty;
        }
        session([self::KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }

    public static function count(): int
    {
        return array_sum(self::raw());
    }

    /** Cart lines: each has product, quantity, total */
    public static function items(): Collection
    {
        $raw = self::raw();
        if (! $raw) {
            return collect();
        }

        return Product::active()->whereIn('id', array_keys($raw))->get()
            ->map(fn (Product $p) => (object) [
                'product' => $p,
                'quantity' => $raw[$p->id],
                'total' => $p->price * $raw[$p->id],
            ]);
    }

    public static function subtotal(): float
    {
        return (float) self::items()->sum('total');
    }

    public static function shipping(): float
    {
        $subtotal = self::subtotal();
        if ($subtotal <= 0 || $subtotal >= config('shop.free_shipping_over')) {
            return 0;
        }

        return config('shop.shipping_fee');
    }

    public static function total(): float
    {
        return self::subtotal() + self::shipping();
    }
}
