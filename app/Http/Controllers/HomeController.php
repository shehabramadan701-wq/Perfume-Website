<?php

namespace App\Http\Controllers;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Product::active()->where('is_featured', true)->latest()->take(4)->get();
        if ($featured->isEmpty()) {
            $featured = Product::active()->latest()->take(4)->get();
        }

        $hero = $featured->first();

        return view('shop.home', compact('featured', 'hero'));
    }

    public function story()
    {
        return view('shop.story');
    }
}
