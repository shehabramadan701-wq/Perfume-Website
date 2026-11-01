<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();               // e.g. AUR-20260101-AB12
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // Shipping details (copied at checkout time)
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('city');
            $table->string('address');
            $table->text('notes')->nullable();

            // Money
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('EGP');

            // Status: pending, processing, shipped, delivered, cancelled
            $table->string('status')->default('pending');

            // Payment: method = stripe | paymob | cod, status = unpaid | paid | failed | refunded
            $table->string('payment_method');
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_reference')->nullable();  // Stripe session / Paymob order id
            $table->string('transaction_id')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
