<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Dashboard login:  admin@aura.test / password  (change it after the first login!)
        User::updateOrCreate(['email' => 'admin@aura.test'], [
            'name' => 'Admin',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $products = collect([
            ['Aubykhoa Essence', 'Woody', 'Warm woods, amber and refined spices.', 'Oud, Amber, Cardamom', 2450, 2900, true, 1],
            ['Midnight Iris', 'Floral', 'A cool, powdery iris under a moonlit sky.', 'Iris, Violet, Musk', 1950, null, true, 2],
            ['Noir Leather', 'Oriental', 'Smoky leather wrapped in black pepper.', 'Leather, Pepper, Tobacco', 2750, null, true, 3],
            ['Rose Velours', 'Floral', 'Velvety Damask rose with a hint of saffron.', 'Rose, Saffron, Patchouli', 2200, 2500, true, 4],
            ['Vetiver Garden', 'Fresh', 'Green vetiver and crushed fig leaves.', 'Vetiver, Fig, Bergamot', 1750, null, false, 5],
            ['Golden Amber', 'Oriental', 'Golden resins melting into soft vanilla.', 'Amber, Vanilla, Benzoin', 2350, null, false, 6],
        ])->map(fn ($p) => Product::updateOrCreate(['name' => $p[0]], [
            'category' => $p[1],
            'short_description' => $p[2],
            'description' => $p[2].' Crafted for those who prefer to be remembered — a long-lasting Eau de Parfum with a rich trail that evolves beautifully on the skin throughout the day.',
            'notes' => $p[3],
            'size_ml' => 50,
            'price' => $p[4],
            'old_price' => $p[5],
            'stock' => rand(8, 40),
            'image' => "images/perfume-{$p[7]}.webp",
            'is_featured' => $p[6],
            'is_active' => true,
        ]));

        // Demo customers & orders so the dashboard isn't empty (safe to delete)
        if (Order::count() > 0) {
            return;
        }

        $people = [
            ['Ahmed Hassan', 'ahmed@example.com', '01001234567', 'Cairo'],
            ['Mariam Adel', 'mariam@example.com', '01112345678', 'Giza'],
            ['Omar Khaled', 'omar@example.com', '01223456789', 'Alexandria'],
            ['Nour Mostafa', 'nour@example.com', '01534567890', 'Mansoura'],
            ['Youssef Samir', 'youssef@example.com', '01045678901', 'Cairo'],
            ['Salma Ibrahim', 'salma@example.com', '01156789012', 'Tanta'],
        ];

        foreach ($people as $i => [$name, $email, $phone, $city]) {
            $customer = Customer::create(compact('name', 'email', 'phone', 'city') + ['address' => (10 + $i).' Nile Street']);

            foreach (range(1, rand(1, 3)) as $n) {
                $method = ['paymob', 'stripe', 'cod'][array_rand([0, 1, 2])];
                $paid = $method !== 'cod' || rand(0, 1);
                $date = now()->subDays(rand(0, 20))->subHours(rand(0, 12));
                $items = $products->random(rand(1, 2));
                $subtotal = $items->sum('price');

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'name' => $name, 'email' => $email, 'phone' => $phone, 'city' => $city,
                    'address' => $customer->address,
                    'subtotal' => $subtotal,
                    'shipping' => 60,
                    'total' => $subtotal + 60,
                    'currency' => config('shop.currency'),
                    'status' => $paid ? ['processing', 'shipped', 'delivered'][rand(0, 2)] : 'pending',
                    'payment_method' => $method,
                    'payment_status' => $paid ? 'paid' : 'unpaid',
                    'transaction_id' => $paid && $method !== 'cod' ? (string) rand(100000000, 999999999) : null,
                    'card_brand' => $paid && $method !== 'cod' ? ['visa', 'mastercard'][rand(0, 1)] : null,
                    'card_last4' => $paid && $method !== 'cod' ? (string) rand(1000, 9999) : null,
                    'paid_at' => $paid ? $date : null,
                ]);
                $order->forceFill(['created_at' => $date])->save();

                foreach ($items as $product) {
                    $order->items()->create([
                        'product_id' => $product->id, 'product_name' => $product->name,
                        'price' => $product->price, 'quantity' => 1, 'total' => $product->price,
                    ]);
                }
            }
        }

        Expense::create(['title' => 'Facebook & Instagram ads', 'category' => 'Marketing', 'amount' => 3500, 'spent_at' => now()->subDays(5)]);
        Expense::create(['title' => 'Bottles & packaging', 'category' => 'Stock', 'amount' => 6200, 'spent_at' => now()->subDays(12)]);
        Expense::create(['title' => 'Shipping company invoice', 'category' => 'Shipping', 'amount' => 1450, 'spent_at' => now()->subDays(2)]);

        ContactMessage::create(['name' => 'Laila Fathy', 'email' => 'laila@example.com', 'phone' => '01099998888', 'subject' => 'Gift wrapping', 'message' => 'Hello, do you offer gift wrapping for Rose Velours? I want to order two bottles.']);
        ContactMessage::create(['name' => 'Karim Nabil', 'email' => 'karim@example.com', 'subject' => 'Wholesale', 'message' => 'Hi, I own a shop in Alexandria and I am interested in wholesale prices.', 'is_read' => true]);
    }
}
