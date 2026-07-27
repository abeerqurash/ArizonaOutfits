<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function toggle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
        ]);

        $product = Product::query()
            ->where('status', 'active')
            ->findOrFail($validated['product_id']);

        $favorite = Favorite::query()
            ->where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with(
                'success',
                'Product removed from your favourites.'
            );
        }

        Favorite::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);

        return back()->with(
            'success',
            'Product added to your favourites.'
        );
    }

    public function index(): View
    {
        $favorites = Favorite::query()
            ->with([
                'product.categories',
                'product.images',
                'product.variants',
                'product.options',
                'product.optionValues',
            ])
            ->where('user_id', auth()->id())
            ->whereHas('product', function ($query) {
                $query->where('status', 'active');
            })
            ->latest()
            ->paginate(12);

        return view(
            'favorites.index',
            compact('favorites')
        );
    }
}