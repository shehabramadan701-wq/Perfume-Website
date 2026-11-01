<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'category', 'short_description', 'description', 'notes',
        'size_ml', 'price', 'old_price', 'stock', 'image', 'is_featured', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Auto-generate a unique slug from the name
        static::saving(function (Product $product) {
            if (blank($product->slug)) {
                $base = Str::slug($product->name) ?: 'perfume';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $product->id ?? 0)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $product->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Full URL of the product image (falls back to the default bottle). */
    public function getImageUrlAttribute(): string
    {
        if ($this->image && str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return asset($this->image ?: 'images/perfume-1.webp');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
