<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\Request;

/**
 * The bag is a slide-out panel (see layouts/shop.blade.php).
 * Every action goes back to the same page and opens the panel again.
 */
class CartController extends Controller
{
    /** Old /bag link: go back to the previous page with the bag open. */
    public function index()
    {
        $previous = url()->previous();
        $target = $previous && $previous !== url()->current() ? $previous : route('home');

        return redirect()->to($target)->with('open_cart', true);
    }

    public function add(Request $request, Product $product)
    {
        abort_unless($product->is_active, 404);

        if ($product->stock < 1) {
            return back()->with('error', 'Sorry, this perfume is out of stock.')->with('open_cart', true);
        }

        Cart::add($product, max(1, (int) $request->input('quantity', 1)));

        return back()->with('open_cart', true);
    }

    public function update(Request $request, Product $product)
    {
        Cart::update($product->id, min((int) $request->input('quantity'), $product->stock));

        return back()->with('open_cart', true);
    }

    public function remove(Product $product)
    {
        Cart::update($product->id, 0);

        return back()->with('open_cart', true);
    }
}
