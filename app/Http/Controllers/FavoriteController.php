<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $query = Favorite::with('product.images', 'product.categories');

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', session()->getId());
        }

        $favorites = $query->get();

        return view('favorites.index', compact('favorites'));
    }

    public function toggle(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        $query = Favorite::where('product_id', $product->id);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', session()->getId());
        }

        $favorite = $query->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            Favorite::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'session_id' => auth()->check() ? null : session()->getId(),
            ]);

            $product->increment('favorites_count');
        }

        return back();
    }
}