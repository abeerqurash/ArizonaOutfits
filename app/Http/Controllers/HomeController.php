<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Latest Blog Posts
        |--------------------------------------------------------------------------
        */

        $posts = Post::with('categories')
            ->latest()
            ->take(6)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Product Card Relationships
        |--------------------------------------------------------------------------
        |
        | These are required by:
        | resources/views/partials/product-card.blade.php
        |
        */

        $productRelations = [
            'images',
            'variants',
            'options',
            'optionValues',
        ];


        /*
        |--------------------------------------------------------------------------
        | Latest Products
        |--------------------------------------------------------------------------
        |
        | Show newest ACTIVE products first.
        |
        */

        $latestProducts = Product::query()
            ->with($productRelations)
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->where('status', 'active')
            ->latest()
            ->take(6)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Popular Products
        |--------------------------------------------------------------------------
        |
        | Popularity priority:
        |
        | 1. Purchases
        | 2. Favorites
        | 3. Added to cart
        | 4. Average rating
        | 5. Views
        | 6. Newest product
        |
        | This means the section can still contain products even when
        | there are not yet 6 products with actual purchases.
        |
        */

        $popularProducts = Product::query()
            ->with($productRelations)
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->where('status', 'active')

            ->orderByDesc('purchase_count')
            ->orderByDesc('favorites_count')
            ->orderByDesc('cart_count')
            ->orderByDesc('average_rating')
            ->orderByDesc('views_count')
            ->orderByDesc('created_at')

            ->take(6)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Homepage
        |--------------------------------------------------------------------------
        */

        return view('home', [
            'title' => 'Novahub',

            'meta_description' =>
                'Learn more about our company.',

            'robots' =>
                'index, follow',

            'posts' =>
                $posts,

            'latestProducts' =>
                $latestProducts,

            'popularProducts' =>
                $popularProducts,
        ]);
    }
}