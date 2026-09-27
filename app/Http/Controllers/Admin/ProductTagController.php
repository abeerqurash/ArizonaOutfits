<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProductTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductTagController extends AdminController
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $tags = ProductTag::query()
            ->withCount('products')
            ->when(
                filled($validated['search'] ?? null),
                function ($query) use ($validated) {
                    $search = trim((string) $validated['search']);

                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where('title', 'like', '%' . $search . '%')
                            ->orWhere('slug', 'like', '%' . $search . '%');
                    });
                }
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalTags = ProductTag::query()->count();
        $usedTags = ProductTag::query()->has('products')->count();
        $unusedTags = max(0, $totalTags - $usedTags);
        $productAssignments = ProductTag::query()->withCount('products')->get()->sum('products_count');

        return view('admin.product-tags.index', compact(
            'tags',
            'totalTags',
            'usedTags',
            'unusedTags',
            'productAssignments'
        ));
    }

    public function create(): View
    {
        return view('admin.product-tags.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        $data['title'] = trim($data['title']);
        $data['slug'] = $this->uniqueSlug(
            filled($data['slug'] ?? null)
                ? Str::slug((string) $data['slug'])
                : Str::slug($data['title'])
        );

        ProductTag::create($data);

        return redirect()
            ->route('admin.product-tags.index')
            ->with('success', 'Product tag created successfully.');
    }

    public function edit(ProductTag $productTag): View
    {
        $productTag->loadCount('products');

        return view('admin.product-tags.edit', compact('productTag'));
    }

    public function update(Request $request, ProductTag $productTag): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        $data['title'] = trim($data['title']);
        $data['slug'] = $this->uniqueSlug(
            filled($data['slug'] ?? null)
                ? Str::slug((string) $data['slug'])
                : Str::slug($data['title']),
            $productTag->id
        );

        $productTag->update($data);

        return redirect()
            ->route('admin.product-tags.index')
            ->with('success', 'Product tag updated successfully.');
    }

    public function destroy(ProductTag $productTag): RedirectResponse
    {
        $productCount = $productTag->products()->count();

        if ($productCount > 0) {
            return redirect()
                ->route('admin.product-tags.index')
                ->with(
                    'warning',
                    'This tag is assigned to ' . $productCount . ' product' .
                    ($productCount === 1 ? '' : 's') .
                    '. Remove the tag from those products before deleting it.'
                );
        }

        $productTag->delete();

        return redirect()
            ->route('admin.product-tags.index')
            ->with('success', 'Product tag deleted successfully.');
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug !== '' ? $slug : 'tag';
        $candidate = $base;
        $suffix = 2;

        while (
            ProductTag::query()
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->whereKeyNot($ignoreId)
                )
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}
