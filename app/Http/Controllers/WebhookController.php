<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymobPayment;
use App\Services\StripePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Server-to-server notifications from the payment gateways.
 * They make sure orders get marked as paid even if the customer closes the browser.
 */
class WebhookController extends Controller
{
    public function stripe(Request $request, StripePayment $stripe)
    {
        if (! StripePayment::validSignature($request->getContent(), $request->header('Stripe-Signature'))) {
            return response('Invalid signature', 400);
        }

        if ($request->input('type') === 'checkout.session.completed') {
            $session = $request->input('data.object');
            $order = Order::where('number', $session['client_reference_id'] ?? '')->first();
            if ($order) {
                $stripe->confirm($order, $session['id']);
            }
        }

        return response('ok');
    }

    public function paymob(Request $request, PaymobPayment $paymob)
    {
        $obj = $request->input('obj', []);

        // Flatten nested data to the same "dotted" shape used by the redirect callback
        $data = Arr::dot($obj);
        $data['order'] = $obj['order']['id'] ?? '';
        $data['merchant_order_id'] = $obj['order']['merchant_order_id'] ?? $obj['special_reference'] ?? '';

        $paymob->handle($data, $request->query('hmac'));

        return response('ok');
    }
}
