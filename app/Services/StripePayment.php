<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Stripe Checkout using the plain REST API (no SDK needed).
 * Docs: https://docs.stripe.com/api/checkout/sessions
 */
class StripePayment
{
    private const API = 'https://api.stripe.com/v1';

    public static function isConfigured(): bool
    {
        return filled(config('services.stripe.secret'));
    }

    /** Creates a Checkout Session and returns the URL to redirect the customer to. */
    public function createCheckout(Order $order): string
    {
        $params = [
            'mode' => 'payment',
            'customer_email' => $order->email,
            'client_reference_id' => $order->number,
            'metadata' => ['order_number' => $order->number],
            'success_url' => route('payment.stripe.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel', $order),
            'line_items' => [],
        ];

        foreach ($order->items as $item) {
            $params['line_items'][] = $this->line($item->product_name, $item->price, $item->quantity, $order->currency);
        }
        if ($order->shipping > 0) {
            $params['line_items'][] = $this->line('Shipping', $order->shipping, 1, $order->currency);
        }

        $response = Http::withToken(config('services.stripe.secret'))
            ->asForm()
            ->post(self::API.'/checkout/sessions', $params);

        if ($response->failed()) {
            throw new RuntimeException('Stripe: '.$response->json('error.message', 'request failed'));
        }

        $order->update(['payment_reference' => $response->json('id')]);

        return $response->json('url');
    }

    /** Checks a session with Stripe and marks the order as paid if it really is. */
    public function confirm(Order $order, string $sessionId): bool
    {
        $response = Http::withToken(config('services.stripe.secret'))
            ->get(self::API.'/checkout/sessions/'.$sessionId, [
                'expand' => ['payment_intent.latest_charge'],
            ]);

        if ($response->failed()
            || $response->json('client_reference_id') !== $order->number
            || $response->json('payment_status') !== 'paid') {
            return false;
        }

        $card = $response->json('payment_intent.latest_charge.payment_method_details.card', []);

        $order->markAsPaid(
            $response->json('payment_intent.id'),
            $card['brand'] ?? 'card',
            $card['last4'] ?? null,
        );

        return true;
    }

    /** Verifies the "Stripe-Signature" header of a webhook request. */
    public static function validSignature(string $payload, ?string $header): bool
    {
        $secret = config('services.stripe.webhook_secret');
        if (! $secret || ! $header) {
            return false;
        }

        parse_str(str_replace(',', '&', $header), $parts);
        $timestamp = $parts['t'] ?? '';
        $signature = $parts['v1'] ?? '';
        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return $signature && hash_equals($expected, $signature) && abs(time() - (int) $timestamp) < 600;
    }

    private function line(string $name, $amount, int $qty, string $currency): array
    {
        return [
            'quantity' => $qty,
            'price_data' => [
                'currency' => strtolower($currency),
                'unit_amount' => (int) round($amount * 100),
                'product_data' => ['name' => $name],
            ],
        ];
    }
}
