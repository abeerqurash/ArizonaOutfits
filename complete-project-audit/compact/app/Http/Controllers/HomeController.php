<?php
namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\Product;
class HomeController extends Controller
{
    public function index()
    {
        $posts = Post::query()
            ->with(['categories', 'primaryCategory', 'author'])
            ->published()
            ->latestPosts()
            ->take(6)
            ->get();
        $productRelations = [
            'images',
            'variants',
            'options',
            'optionValues',
        ];
        $latestProducts = Product::query()
            ->with($productRelations)
            ->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating')
            ->withCount('approvedReviews as approved_reviews_count')
            ->where('status', 'active')
            ->latest()
            ->take(6)
            ->get();
        $popularProducts = Product::query()
            ->with($productRelations)
            ->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating')
            ->withCount('approvedReviews as approved_reviews_count')
            ->where('status', 'active')
            ->orderByDesc('purchase_count')
            ->orderByDesc('favorites_count')
            ->orderByDesc('cart_count')
            ->orderByDesc('average_rating')
            ->orderByDesc('views_count')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();
        return view('home', [
            'title' => 'Arizona Outfits',
            'meta_description' =>
                'Shop the latest collections and discover popular products at Arizona Outfits.',
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