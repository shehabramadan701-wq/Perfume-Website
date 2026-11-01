<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('shop.cart', [
            'items' => Cart::items(),
            'subtotal' => Cart::subtotal(),
            'shipping' => Cart::shipping(),
            'total' => Cart::total(),
        ]);
    }

    public function add(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        if ($product->stock < 1) {
            return back()->with('error', 'Sorry, this perfume is out of stock.');
        }

        Cart::add($product, max(1, (int) $request->input('quantity', 1)));

        return back()->with('success', "{$product->name} was added to your bag.");
    }

    public function update(Request $request, Product $product)
    {
        Cart::update($product->id, min((int) $request->input('quantity'), $product->stock));

        return back();
    }

    public function remove(Product $product)
    {
        Cart::update($product->id, 0);

        return back()->with('success', 'Item removed.');
    }
}
