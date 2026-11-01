<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::active()
            ->when($request->q, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")))
            ->when($request->category, fn ($q, $cat) => $q->where('category', $cat))
            ->when($request->sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($request->sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Product::active()->whereNotNull('category')->distinct()->pluck('category');

        return view('shop.collection', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $related = Product::active()->where('id', '!=', $product->id)->inRandomOrder()->take(3)->get();

        return view('shop.product', compact('product', 'related'));
    }
}
