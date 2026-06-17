<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductTagController extends Controller
{
    public function index()
    {
        $tags = ProductTag::latest()->paginate(10);

        return view('admin.product-tags.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.product-tags.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_tags,slug',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        ProductTag::create($data);

        return redirect()->route('admin.product-tags.index')
            ->with('success', 'Product tag created successfully.');
    }

    public function edit(ProductTag $productTag)
    {
        return view('admin.product-tags.edit', compact('productTag'));
    }

    public function update(Request $request, ProductTag $productTag)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_tags,slug,' . $productTag->id,
        ]);

        $productTag->update($data);

        return redirect()->route('admin.product-tags.index')
            ->with('success', 'Product tag updated successfully.');
    }

    public function destroy(ProductTag $productTag)
    {
        $productTag->products()->detach();
        $productTag->delete();

        return redirect()->route('admin.product-tags.index')
            ->with('success', 'Product tag deleted successfully.');
    }
}
