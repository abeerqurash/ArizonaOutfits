<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends AdminController
{
    public function index()
    {
        $categories = ProductCategory::with('parent')->latest()->paginate(10);

        return view('admin.product-categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = ProductCategory::whereNull('parent_id')->orderBy('title')->get();

        return view('admin.product-categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug',
            'parent_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
            'featured_image' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        ProductCategory::create($data);

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Product category created successfully.');
    }

    public function edit(ProductCategory $productCategory)
    {
        $parents = ProductCategory::whereNull('parent_id')
            ->where('id', '!=', $productCategory->id)
            ->orderBy('title')
            ->get();

        return view('admin.product-categories.edit', compact('productCategory', 'parents'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_categories,slug,' . $productCategory->id,
            'parent_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
            'featured_image' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        $productCategory->update($data);

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Product category updated successfully.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $productCategory->products()->detach();
        $productCategory->delete();

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Product category deleted successfully.');
    }
}