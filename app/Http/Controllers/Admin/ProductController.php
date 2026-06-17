<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('categories')->latest()->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = ProductCategory::orderBy('title')->get();
        $tags = ProductTag::orderBy('title')->get();

        return view('admin.products.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'sku' => 'nullable|string|max:255',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'additional_info' => 'nullable|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'status' => 'required|string',

            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',

            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',

            'categories' => 'required|array',
            'categories.*' => 'exists:product_categories,id',

            'tags' => 'nullable|array',
            'tags.*' => 'exists:product_tags,id',

            'variants' => 'nullable|array',
            'variants.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['stock'] = $data['stock'] ?? 0;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('products', 'public');
        }

        $product = Product::create($data);

        $product->categories()->sync($request->categories);
        $product->tags()->sync($request->tags ?? []);

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $image->store('products/gallery', 'public'),
                    'sort_order' => $index,
                ]);
            }
        }

        if ($request->variants) {
            foreach ($request->variants as $index => $variant) {
                if (!empty($variant['sku'])) {

                    $variantImage = null;

                    if ($request->hasFile("variants.$index.image")) {
                        $variantImage = $request->file("variants.$index.image")->store('products/variants', 'public');
                    }

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variant['sku'] ?? null,
                        'options' => json_encode([
                            'Color' => $variant['color'] ?? null,
                            'Size' => $variant['size'] ?? null,
                            'Design' => $variant['design'] ?? null,
                        ]),
                        'regular_price' => $variant['regular_price'] ?? null,
                        'sale_price' => $variant['sale_price'] ?? null,
                        'stock' => $variant['stock'] ?? 0,
                        'image' => $variantImage,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load('categories', 'tags', 'images', 'variants');

        $categories = ProductCategory::orderBy('title')->get();
        $tags = ProductTag::orderBy('title')->get();

        $selectedCategories = $product->categories->pluck('id')->toArray();
        $selectedTags = $product->tags->pluck('id')->toArray();

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'tags',
            'selectedCategories',
            'selectedTags'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $product->id,
            'sku' => 'nullable|string|max:255',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'additional_info' => 'nullable|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'status' => 'required|string',

            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',

            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',

            'categories' => 'required|array',
            'categories.*' => 'exists:product_categories,id',

            'tags' => 'nullable|array',
            'tags.*' => 'exists:product_tags,id',

            'variants' => 'nullable|array',
            'variants.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('products', 'public');
        } else {
            unset($data['featured_image']);
        }

        $product->update($data);

        $product->categories()->sync($request->categories);
        $product->tags()->sync($request->tags ?? []);

        if ($request->hasFile('gallery_images')) {
            ProductImage::where('product_id', $product->id)->delete();

            foreach ($request->file('gallery_images') as $index => $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $image->store('products/gallery', 'public'),
                    'sort_order' => $index,
                ]);
            }
        }

        ProductVariant::where('product_id', $product->id)->delete();

        if ($request->variants) {
            foreach ($request->variants as $index => $variant) {
                if (!empty($variant['sku'])) {

                    $variantImage = $variant['old_image'] ?? null;

                    if ($request->hasFile("variants.$index.image")) {
                        $variantImage = $request->file("variants.$index.image")->store('products/variants', 'public');
                    }

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variant['sku'] ?? null,
                        'options' => json_encode([
                            'Color' => $variant['color'] ?? null,
                            'Size' => $variant['size'] ?? null,
                            'Design' => $variant['design'] ?? null,
                        ]),
                        'regular_price' => $variant['regular_price'] ?? null,
                        'sale_price' => $variant['sale_price'] ?? null,
                        'stock' => $variant['stock'] ?? 0,
                        'image' => $variantImage,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->categories()->detach();
        $product->tags()->detach();
        $product->images()->delete();
        $product->variants()->delete();
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}