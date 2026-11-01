<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Paymob "Unified Checkout" using the Intention API.
 * Docs: https://developers.paymob.com
 */
class PaymobPayment
{
    /** Fields Paymob uses (in this exact order) to build the HMAC of a transaction. */
    private const HMAC_FIELDS = [
        'amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction',
        'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded',
        'is_standalone_payment', 'is_voided', 'order', 'owner', 'pending',
        'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success',
    ];

    public static function isConfigured(): bool
    {
        return filled(config('services.paymob.secret_key'))
            && filled(config('services.paymob.public_key'))
            && filled(config('services.paymob.integration_id'));
    }

    /** Creates a payment intention and returns the checkout URL. */
    public function createCheckout(Order $order): string
    {
        $base = rtrim(config('services.paymob.base_url'), '/');
        $reference = $order->number.'-'.Str::lower(Str::random(5)); // must be unique per attempt
        [$first, $last] = array_pad(explode(' ', trim($order->name), 2), 2, '-');

        $items = $order->items->map(fn ($item) => [
            'name' => Str::limit($item->product_name, 50, ''),
            'amount' => (int) round($item->price * 100),
            'description' => 'Perfume',
            'quantity' => $item->quantity,
        ])->all();

        if ($order->shipping > 0) {
            $items[] = ['name' => 'Shipping', 'amount' => (int) round($order->shipping * 100), 'description' => 'Shipping', 'quantity' => 1];
        }

        $response = Http::withHeaders(['Authorization' => 'Token '.config('services.paymob.secret_key')])
            ->acceptJson()
            ->post($base.'/v1/intention/', [
                'amount' => (int) round($order->total * 100),
                'currency' => $order->currency,
                'payment_methods' => [(int) config('services.paymob.integration_id')],
                'items' => $items,
                'billing_data' => [
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $order->email,
                    'phone_number' => $order->phone,
                    'city' => $order->city,
                    'street' => $order->address,
                    'country' => 'EG',
                    'apartment' => 'NA', 'floor' => 'NA', 'building' => 'NA', 'state' => 'NA',
                ],
                'special_reference' => $reference,
                'notification_url' => route('webhooks.paymob'),
                'redirection_url' => route('payment.paymob.callback'),
            ]);

        if ($response->failed() || ! $response->json('client_secret')) {
            throw new RuntimeException('Paymob: '.($response->json('detail') ?? $response->body()));
        }

        $order->update(['payment_reference' => $reference]);

        return $base.'/unifiedcheckout/?'.http_build_query([
            'publicKey' => config('services.paymob.public_key'),
            'clientSecret' => $response->json('client_secret'),
        ]);
    }

    /**
     * Validates a transaction (from the redirect query string or the webhook "obj")
     * and updates the order. Returns the order or null.
     *
     * @param  array  $data  flat array with dotted keys, e.g. ['source_data.pan' => '...']
     */
    public function handle(array $data, ?string $hmac): ?Order
    {
        if (! $this->validHmac($data, $hmac)) {
            return null;
        }

        $order = Order::where('payment_reference', $data['merchant_order_id'] ?? '')->first();
        if (! $order) {
            return null;
        }

        if ($this->bool($data['success'] ?? false) && ! $this->bool($data['pending'] ?? false)) {
            $order->markAsPaid(
                (string) ($data['id'] ?? ''),
                $data['source_data.sub_type'] ?? $data['source_data.type'] ?? 'card',
                $data['source_data.pan'] ?? null,
            );
        } elseif ($order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'failed']);
        }

        return $order->fresh();
    }

    private function validHmac(array $data, ?string $hmac): bool
    {
        $secret = config('services.paymob.hmac');
        if (! $secret || ! $hmac) {
            return false;
        }

        $string = collect(self::HMAC_FIELDS)->map(fn ($f) => $this->stringify($data[$f] ?? ''))->implode('');

        return hash_equals(hash_hmac('sha512', $string, $secret), strtolower($hmac));
    }

    private function stringify($value): string
    {
        return is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
    }

    private function bool($value): bool
    {
        return $value === true || $value === 'true' || $value === 1 || $value === '1';
    }
}
