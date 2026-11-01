<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();        // e.g. Woody, Oriental, Floral
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('notes')->nullable();           // e.g. "Amber, Oud, Vanilla"
            $table->unsignedInteger('size_ml')->default(50);
            $table->decimal('price', 10, 2);
            $table->decimal('old_price', 10, 2)->nullable(); // shown crossed-out when set
            $table->unsignedInteger('stock')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
