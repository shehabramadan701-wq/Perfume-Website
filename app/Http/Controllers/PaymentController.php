<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymobPayment;
use App\Services\StripePayment;
use App\Support\Cart;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /** Stripe sends the customer back here after paying. */
    public function stripeSuccess(Request $request, Order $order, StripePayment $stripe)
    {
        if ($request->session_id && $stripe->confirm($order, $request->session_id)) {
            return $this->success($order);
        }

        return redirect()->route('checkout')->with('error', 'We could not confirm your payment.');
    }

    /** Paymob redirects the customer here (GET) after paying. */
    public function paymobCallback(Request $request, PaymobPayment $paymob)
    {
        $data = self::rawQuery($request);
        $order = $paymob->handle($data, $data['hmac'] ?? null);

        if ($order && $order->payment_status === 'paid') {
            return $this->success($order);
        }

        return redirect()->route('checkout')->with('error', 'Payment was not completed. Please try again.');
    }

    public function cancel(Order $order)
    {
        if ($order->payment_status === 'unpaid') {
            $order->update(['payment_status' => 'failed']);
        }

        return redirect()->route('checkout')->with('error', 'Payment was cancelled.');
    }

    private function success(Order $order)
    {
        Cart::clear();

        return redirect()->route('order.thanks', $order);
    }

    /** PHP turns "source_data.pan" into "source_data_pan", so read the query string ourselves. */
    public static function rawQuery(Request $request): array
    {
        $data = [];
        foreach (explode('&', (string) $request->server('QUERY_STRING')) as $pair) {
            if ($pair === '') {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $data[urldecode($key)] = urldecode($value);
        }

        return $data;
    }
}
