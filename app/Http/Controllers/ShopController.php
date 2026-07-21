<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:150'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:price_low,price_high,popular,rating'],
        ]);

        $query = Product::query()
            ->with(['categories', 'images', 'variants', 'options', 'optionValues'])
            ->where('status', 'active');

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($productQuery) use ($search) {
                $productQuery
                    ->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('short_description', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($validated['category'])) {
            $categorySlug = $validated['category'];

            $query->whereHas('categories', function ($categoryQuery) use ($categorySlug) {
                $categoryQuery->where('product_categories.slug', $categorySlug);
            });
        }

        if (isset($validated['min_price'])) {
            $query->whereRaw(
                'COALESCE(sale_price, regular_price) >= ?',
                [(float) $validated['min_price']]
            );
        }

        if (isset($validated['max_price'])) {
            $query->whereRaw(
                'COALESCE(sale_price, regular_price) <= ?',
                [(float) $validated['max_price']]
            );
        }

        match ($validated['sort'] ?? null) {
            'price_low' => $query
                ->orderByRaw('COALESCE(sale_price, regular_price) ASC')
                ->orderBy('id'),
            'price_high' => $query
                ->orderByRaw('COALESCE(sale_price, regular_price) DESC')
                ->orderByDesc('id'),
            'popular' => $query
                ->orderByDesc('purchase_count')
                ->orderByDesc('views_count'),
            'rating' => $query
                ->orderByDesc('average_rating')
                ->orderByDesc('id'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = ProductCategory::query()
            ->with([
                'children' => function ($childrenQuery) {
                    $childrenQuery
                        ->whereHas('products', function ($productQuery) {
                            $productQuery->where('status', 'active');
                        })
                        ->orderBy('title');
                },
            ])
            ->whereNull('parent_id')
            ->whereHas('products', function ($productQuery) {
                $productQuery->where('status', 'active');
            })
            ->orderBy('title')
            ->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function quickView(Product $product)
    {
        abort_unless($product->status === 'active', 404);

        $product->load([
            'images',
            'variants',
            'options',
            'optionValues',
            'categories',
            'tags',
        ]);

        return view('products.partials.quick-view', compact('product'));
    }

    public function show(string $slug)
    {
        $product = Product::query()
            ->with([
                'categories',
                'images',
                'tags',
                'reviews',
                'variants',
                'options',
                'optionValues',
            ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $product->increment('views_count');

        $categoryIds = $product->categories->pluck('id');

        $relatedProducts = Product::query()
            ->with(['categories', 'images', 'variants', 'options', 'optionValues'])
            ->where('status', 'active')
            ->whereKeyNot($product->getKey())
            ->when($categoryIds->isNotEmpty(), function ($query) use ($categoryIds) {
                $query->whereHas('categories', function ($categoryQuery) use ($categoryIds) {
                    $categoryQuery->whereIn('product_categories.id', $categoryIds);
                });
            })
            ->latest()
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function category(string $slug, Request $request)
    {
        $validated = $request->validate([
            'sort' => ['nullable', 'in:price_low,price_high,popular,rating'],
        ]);

        $category = ProductCategory::where('slug', $slug)->firstOrFail();

        $productsQuery = $category->products()
            ->with(['categories', 'images', 'variants', 'options', 'optionValues'])
            ->where('status', 'active');

        match ($validated['sort'] ?? null) {
            'price_low' => $productsQuery->orderByRaw('COALESCE(sale_price, regular_price) ASC'),
            'price_high' => $productsQuery->orderByRaw('COALESCE(sale_price, regular_price) DESC'),
            'popular' => $productsQuery->orderByDesc('purchase_count'),
            'rating' => $productsQuery->orderByDesc('average_rating'),
            default => $productsQuery->latest(),
        };

        $products = $productsQuery->paginate(12)->withQueryString();

        $categories = ProductCategory::with('children')
            ->whereNull('parent_id')
            ->orderBy('title')
            ->get();

        return view('products.category', compact('category', 'products', 'categories'));
    }

    public function sale(Request $request)
    {
        $validated = $request->validate([
            'sort' => ['nullable', 'in:price_low,price_high,popular,rating'],
        ]);

        $query = Product::query()
            ->with(['categories', 'images', 'variants', 'options', 'optionValues'])
            ->where('status', 'active')
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'regular_price');

        match ($validated['sort'] ?? null) {
            'price_low' => $query->orderBy('sale_price'),
            'price_high' => $query->orderByDesc('sale_price'),
            'popular' => $query->orderByDesc('purchase_count'),
            'rating' => $query->orderByDesc('average_rating'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = ProductCategory::with('children')
            ->whereNull('parent_id')
            ->orderBy('title')
            ->get();

        return view('products.sale', compact('products', 'categories'));
    }
}