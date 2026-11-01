<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        return Product::create($attrs + ['name' => 'Test Oud', 'price' => 1000, 'stock' => 10, 'size_ml' => 50]);
    }

    private function checkoutData(string $method): array
    {
        return ['name' => 'Sara Ali', 'email' => 'sara@example.com', 'phone' => '0100', 'city' => 'Cairo', 'address' => 'Street 1', 'payment_method' => $method];
    }

    public function test_public_pages_load(): void
    {
        $product = $this->product();
        foreach (['/', '/collection', '/our-story', '/contact', '/bag', '/perfume/'.$product->slug] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_cash_on_delivery_checkout_creates_customer_and_order(): void
    {
        $product = $this->product();
        $this->post(route('cart.add', $product), ['quantity' => 2]);

        $response = $this->post(route('checkout.store'), $this->checkoutData('cod'));

        $order = Order::first();
        $response->assertRedirect(route('order.thanks', $order));
        $this->assertSame('2060.00', $order->total); // 2 × 1000 + 60 shipping
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('Sara Ali', $order->customer->name);
        $this->get(route('order.thanks', $order))->assertOk()->assertSee($order->number);
    }

    public function test_stripe_checkout_redirects_and_success_marks_paid(): void
    {
        config(['services.stripe.secret' => 'sk_test_x']);
        $product = $this->product();
        $this->post(route('cart.add', $product));

        Http::fake(['api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_1', 'url' => 'https://checkout.stripe.com/pay/cs_1'])]);
        $this->post(route('checkout.store'), $this->checkoutData('stripe'))->assertRedirect('https://checkout.stripe.com/pay/cs_1');

        $order = Order::first();
        Http::fake(['api.stripe.com/v1/checkout/sessions/cs_1*' => Http::response([
            'client_reference_id' => $order->number,
            'payment_status' => 'paid',
            'payment_intent' => ['id' => 'pi_1', 'latest_charge' => ['payment_method_details' => ['card' => ['brand' => 'visa', 'last4' => '4242']]]],
        ])]);

        $this->get(route('payment.stripe.success', $order).'?session_id=cs_1')->assertRedirect(route('order.thanks', $order));
        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('4242', $order->card_last4);
        $this->assertSame(9, $product->fresh()->stock);
    }

    public function test_stripe_webhook_rejects_bad_signature(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_x']);
        $this->postJson(route('webhooks.stripe'), [], ['Stripe-Signature' => 't=1,v1=bad'])->assertStatus(400);
    }

    public function test_paymob_callback_validates_hmac(): void
    {
        config(['services.paymob.hmac' => 'secret']);
        $product = $this->product();
        $order = Order::create([
            'customer_id' => \App\Models\Customer::create(['name' => 'A', 'email' => 'a@a.com'])->id,
            'name' => 'A', 'email' => 'a@a.com', 'phone' => '1', 'city' => 'C', 'address' => 'X',
            'subtotal' => 1000, 'shipping' => 0, 'total' => 1000, 'payment_method' => 'paymob', 'payment_reference' => 'REF-1',
        ]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => 'x', 'price' => 1000, 'quantity' => 1, 'total' => 1000]);
        session()->push('my_orders', $order->number);

        $tx = [
            'amount_cents' => '100000', 'created_at' => '2026-01-01T10:00:00', 'currency' => 'EGP', 'error_occured' => 'false',
            'has_parent_transaction' => 'false', 'id' => '555', 'integration_id' => '1', 'is_3d_secure' => 'true',
            'is_auth' => 'false', 'is_capture' => 'false', 'is_refunded' => 'false', 'is_standalone_payment' => 'true',
            'is_voided' => 'false', 'order' => '99', 'owner' => '7', 'pending' => 'false',
            'source_data.pan' => '2346', 'source_data.sub_type' => 'MasterCard', 'source_data.type' => 'card', 'success' => 'true',
        ];
        $hmac = hash_hmac('sha512', implode('', $tx), 'secret');
        $query = http_build_query($tx + ['merchant_order_id' => 'REF-1']);

        // Tampered request is rejected
        $this->get('/payment/paymob/callback?'.$query.'&hmac=bad')->assertRedirect(route('checkout'));
        $this->assertSame('unpaid', $order->fresh()->payment_status);

        // Valid one marks the order paid
        $this->get('/payment/paymob/callback?'.$query.'&hmac='.$hmac)->assertRedirect(route('order.thanks', $order));
        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('2346', $order->card_last4);
        $this->assertSame('MasterCard', $order->card_brand);
    }

    public function test_contact_form_saves_message(): void
    {
        $this->post(route('contact.store'), ['name' => 'N', 'email' => 'n@n.com', 'message' => 'Hi'])->assertSessionHas('success');
        $this->assertDatabaseHas('contact_messages', ['email' => 'n@n.com']);
    }

    public function test_dashboard_requires_admin(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));

        $user = User::create(['name' => 'U', 'email' => 'u@u.com', 'password' => 'x', 'is_admin' => false]);
        $this->actingAs($user)->get('/admin')->assertRedirect(route('admin.login'));

        $admin = User::create(['name' => 'Admin', 'email' => 'ad@ad.com', 'password' => 'x', 'is_admin' => true]);
        foreach (['/admin', '/admin/orders', '/admin/products', '/admin/products/create', '/admin/customers', '/admin/accounting', '/admin/accounting/export', '/admin/messages'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_can_create_product(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'ad@ad.com', 'password' => 'x', 'is_admin' => true]);
        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Velvet Musk', 'price' => 1500, 'stock' => 4, 'size_ml' => 100, 'is_active' => 1,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Velvet Musk', 'slug' => 'velvet-musk', 'is_active' => true]);
    }
}
