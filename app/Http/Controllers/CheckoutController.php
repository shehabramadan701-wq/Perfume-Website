<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Services\PaymobPayment;
use App\Services\StripePayment;
use App\Support\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckoutController extends Controller
{
    public function create()
    {
        $items = Cart::items();
        if ($items->isEmpty()) {
            return redirect()->route('collection')->with('error', 'Your bag is empty.');
        }

        return view('shop.checkout', [
            'items' => $items,
            'subtotal' => Cart::subtotal(),
            'shipping' => Cart::shipping(),
            'total' => Cart::total(),
            'methods' => [
                'paymob' => PaymobPayment::isConfigured(),
                'stripe' => StripePayment::isConfigured(),
                'cod' => true,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:30',
            'city' => 'required|string|max:80',
            'address' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:stripe,paymob,cod',
        ]);

        $items = Cart::items();
        if ($items->isEmpty()) {
            return redirect()->route('collection');
        }

        $order = DB::transaction(function () use ($data, $items) {
            $customer = Customer::updateOrCreate(
                ['email' => strtolower($data['email'])],
                collect($data)->only('name', 'phone', 'city', 'address')->all(),
            );

            $order = Order::create([
                ...collect($data)->except('payment_method')->all(),
                'customer_id' => $customer->id,
                'subtotal' => Cart::subtotal(),
                'shipping' => Cart::shipping(),
                'total' => Cart::total(),
                'currency' => config('shop.currency'),
                'payment_method' => $data['payment_method'],
            ]);

            foreach ($items as $line) {
                $order->items()->create([
                    'product_id' => $line->product->id,
                    'product_name' => $line->product->name,
                    'price' => $line->product->price,
                    'quantity' => $line->quantity,
                    'total' => $line->total,
                ]);
            }

            return $order->load('items');
        });

        // Remember the orders placed in this browser (to show the thank-you page)
        session()->push('my_orders', $order->number);

        // Cash on delivery: done
        if ($order->payment_method === 'cod') {
            Cart::clear();

            return redirect()->route('order.thanks', $order);
        }

        // Card payments: send the customer to the gateway
        try {
            $url = $order->payment_method === 'stripe'
                ? app(StripePayment::class)->createCheckout($order)
                : app(PaymobPayment::class)->createCheckout($order);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            $order->update(['payment_status' => 'failed']);

            return back()->withInput()->with('error', 'Online payment is not available right now. Please try another method.');
        }

        return redirect()->away($url);
    }

    public function thanks(Order $order)
    {
        // Only the person who placed the order (same session) can view it
        abort_unless(in_array($order->number, session('my_orders', [])), 404);

        return view('shop.thanks', ['order' => $order->load('items')]);
    }
}
