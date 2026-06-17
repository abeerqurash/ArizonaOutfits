<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['categories', 'images'])
            ->where('status', 'active');

        if ($request->search) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->category) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->min_price) {
            $query->where('regular_price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('regular_price', '<=', $request->max_price);
        }

        if ($request->sort === 'price_low') {
            $query->orderBy('regular_price', 'asc');
        } elseif ($request->sort === 'price_high') {
            $query->orderBy('regular_price', 'desc');
        } elseif ($request->sort === 'popular') {
            $query->orderByDesc('purchase_count');
        } elseif ($request->sort === 'rating') {
            $query->orderByDesc('average_rating');
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        $categories = ProductCategory::with('children')->whereNull('parent_id')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show($slug)
    {
        $product = Product::with(['categories', 'images', 'tags', 'reviews'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $product->increment('views_count');

        $categoryIds = $product->categories->pluck('id');

        $relatedProducts = Product::with(['categories', 'images'])
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('product_categories.id', $categoryIds);
            })
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function category($slug, Request $request)
    {
        $category = ProductCategory::where('slug', $slug)->firstOrFail();

        $products = $category->products()
            ->with(['categories', 'images'])
            ->where('status', 'active')
            ->paginate(12);

        $categories = ProductCategory::with('children')->whereNull('parent_id')->get();

        return view('products.category', compact('category', 'products', 'categories'));
    }

    public function sale()
    {
        $products = Product::with(['categories', 'images'])
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'regular_price')
            ->where('status', 'active')
            ->paginate(12);

        $categories = ProductCategory::with('children')->whereNull('parent_id')->get();

        return view('products.sale', compact('products', 'categories'));
    }
}