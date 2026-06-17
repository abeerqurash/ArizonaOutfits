<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        $cart = session('cart', []);

        $id = $product->id;

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $request->quantity ?? 1;
        } else {
            $cart[$id] = [
                'product_id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'image' => $product->featured_image,
                'price' => $product->final_price,
                'quantity' => $request->quantity ?? 1,
                'options' => $request->options ?? [],
            ];
        }

        session(['cart' => $cart]);

        $product->increment('cart_count');

        return redirect()->route('cart.index')->with('success', 'Product added to cart.');
    }

    public function update(Request $request)
    {
        $cart = session('cart', []);

        foreach ($request->quantities as $id => $quantity) {
            if (isset($cart[$id])) {
                $cart[$id]['quantity'] = max(1, $quantity);
            }
        }

        session(['cart' => $cart]);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request)
    {
        $cart = session('cart', []);

        unset($cart[$request->product_id]);

        session(['cart' => $cart]);

        return back()->with('success', 'Product removed.');
    }
}