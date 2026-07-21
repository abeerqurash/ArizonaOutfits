<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\ProductTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display all products.
     */
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with([
                'categories',
                'tags',
                'options',
                'variants',
                'images',
            ])
            ->withCount([
                'variants',
            ])
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim((string) $request->input('search'));

                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where('title', 'like', '%' . $search . '%')
                            ->orWhere('slug', 'like', '%' . $search . '%')
                            ->orWhere('sku', 'like', '%' . $search . '%');
                    });
                }
            )
            ->when(
                $request->filled('status'),
                function ($query) use ($request) {
                    $query->where(
                        'status',
                        $request->input('status')
                    );
                }
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.products.index',
            compact('products')
        );
    }

    /**
     * Show the create product form.
     */

    public function show(string $slug)
    {
        $product = Product::with([
            'categories',
            'tags',
            'options.values',
            'optionValues.option',
            'variants',
            'images',
            'reviews',
        ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $product->increment('views_count');

        $categoryIds = $product->categories
            ->pluck('id');

        $relatedProducts = Product::with([
            'categories',
            'images',
        ])
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->when(
                $categoryIds->isNotEmpty(),
                function ($query) use ($categoryIds) {
                    $query->whereHas(
                        'categories',
                        function ($categoryQuery) use (
                            $categoryIds
                        ) {
                            $categoryQuery->whereIn(
                                'product_categories.id',
                                $categoryIds
                            );
                        }
                    );
                }
            )
            ->latest()
            ->limit(4)
            ->get();

        return view(
            'products.show',
            compact(
                'product',
                'relatedProducts'
            )
        );
    }
    public function create(): View
    {
        $categories = ProductCategory::query()
            ->orderBy('title')
            ->get();

        $tags = ProductTag::query()
            ->orderBy('title')
            ->get();

        $productOptions = ProductOption::query()
            ->with([
                'values' => function ($query) {
                    $query->orderBy('label');
                },
            ])
            ->orderBy('name')
            ->get();

        return view(
            'admin.products.create',
            compact(
                'categories',
                'tags',
                'productOptions'
            )
        );
    }

    /**
     * Save a newly created product.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct(
            request: $request
        );

        DB::beginTransaction();

        try {
            $featuredImagePath = null;

            if ($request->hasFile('featured_image')) {
                $featuredImagePath = $request
                    ->file('featured_image')
                    ->store('products/featured', 'public');
            }

            $product = Product::create([
                'title' => $validated['title'],

                'slug' => $validated['slug'],

                'sku' => $validated['sku'] ?? null,

                'short_description' =>
                $validated['short_description'] ?? null,

                'long_description' =>
                $validated['long_description'] ?? null,

                'additional_info' =>
                $validated['additional_info'] ?? null,

                'regular_price' =>
                $validated['regular_price'],

                'sale_price' =>
                $validated['sale_price'] ?? null,

                'stock' =>
                $validated['stock'] ?? 0,

                'status' =>
                $validated['status'],

                'featured_image' =>
                $featuredImagePath,

                'meta_title' =>
                $validated['meta_title'] ?? null,

                'meta_description' =>
                $validated['meta_description'] ?? null,

                'meta_keywords' =>
                $validated['meta_keywords'] ?? null,
            ]);

            $product->categories()->sync(
                $validated['categories'] ?? []
            );

            $product->tags()->sync(
                $validated['tags'] ?? []
            );

            $product->options()->sync(
                $validated['product_options'] ?? []
            );

            $this->storeGalleryImages(
                request: $request,
                product: $product
            );

            $this->storeVariants(
                request: $request,
                product: $product,
                variants: $validated['variants'] ?? []
            );

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'Product created successfully.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            if (
                isset($featuredImagePath)
                && $featuredImagePath
            ) {
                Storage::disk('public')->delete(
                    $featuredImagePath
                );
            }

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The product could not be created: '
                        . $exception->getMessage()
                );
        }
    }

    /**
     * Show the edit product form.
     */
    public function edit(Product $product): View
    {
        $product->load([
            'categories',
            'tags',
            'options.values',
            'variants',
            'images',
        ]);

        $categories = ProductCategory::query()
            ->orderBy('title')
            ->get();

        $tags = ProductTag::query()
            ->orderBy('title')
            ->get();

        $productOptions = ProductOption::query()
            ->with([
                'values' => function ($query) {
                    $query->orderBy('label');
                },
            ])
            ->orderBy('name')
            ->get();

        return view(
            'admin.products.edit',
            compact(
                'product',
                'categories',
                'tags',
                'productOptions'
            )
        );
    }

    /**
     * Update an existing product.
     */
    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {
        $validated = $this->validateProduct(
            request: $request,
            product: $product
        );

        DB::beginTransaction();

        try {
            $featuredImagePath =
                $product->featured_image;

            if ($request->hasFile('featured_image')) {
                $newFeaturedImagePath = $request
                    ->file('featured_image')
                    ->store('products/featured', 'public');

                if ($featuredImagePath) {
                    Storage::disk('public')->delete(
                        $featuredImagePath
                    );
                }

                $featuredImagePath =
                    $newFeaturedImagePath;
            }

            $product->update([
                'title' => $validated['title'],

                'slug' => $validated['slug'],

                'sku' => $validated['sku'] ?? null,

                'short_description' =>
                $validated['short_description'] ?? null,

                'long_description' =>
                $validated['long_description'] ?? null,

                'additional_info' =>
                $validated['additional_info'] ?? null,

                'regular_price' =>
                $validated['regular_price'],

                'sale_price' =>
                $validated['sale_price'] ?? null,

                'stock' =>
                $validated['stock'] ?? 0,

                'status' =>
                $validated['status'],

                'featured_image' =>
                $featuredImagePath,

                'meta_title' =>
                $validated['meta_title'] ?? null,

                'meta_description' =>
                $validated['meta_description'] ?? null,

                'meta_keywords' =>
                $validated['meta_keywords'] ?? null,
            ]);

            $product->categories()->sync(
                $validated['categories'] ?? []
            );

            $product->tags()->sync(
                $validated['tags'] ?? []
            );

            $product->options()->sync(
                $validated['product_options'] ?? []
            );

            $this->storeGalleryImages(
                request: $request,
                product: $product
            );

            /*
             * The submitted variants replace the current variants.
             *
             * Existing images are preserved through old_image when
             * no replacement image is uploaded.
             */
            $product->variants()->delete();

            $this->storeVariants(
                request: $request,
                product: $product,
                variants: $validated['variants'] ?? []
            );

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'Product updated successfully.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The product could not be updated: '
                        . $exception->getMessage()
                );
        }
    }

    /**
     * Delete a product.
     */
    public function destroy(
        Product $product
    ): RedirectResponse {
        DB::beginTransaction();

        try {
            $product->load([
                'images',
                'variants',
            ]);

            if ($product->featured_image) {
                Storage::disk('public')->delete(
                    $product->featured_image
                );
            }

            foreach ($product->images as $image) {
                if (!empty($image->image)) {
                    Storage::disk('public')->delete(
                        $image->image
                    );
                }
            }

            foreach ($product->variants as $variant) {
                if (!empty($variant->image)) {
                    Storage::disk('public')->delete(
                        $variant->image
                    );
                }
            }

            $product->categories()->detach();
            $product->tags()->detach();
            $product->options()->detach();

            $product->images()->delete();
            $product->variants()->delete();

            $product->delete();

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'Product deleted successfully.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()->with(
                'error',
                'The product could not be deleted: '
                    . $exception->getMessage()
            );
        }
    }

    /**
     * Validate create and update requests.
     */
    private function validateProduct(
        Request $request,
        ?Product $product = null
    ): array {
        $productId = $product?->id;

        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'products',
                    'slug'
                )->ignore($productId),
            ],

            'sku' => [
                'nullable',
                'string',
                'max:100',

                Rule::unique(
                    'products',
                    'sku'
                )->ignore($productId),
            ],

            'short_description' => [
                'nullable',
                'string',
            ],

            'long_description' => [
                'nullable',
                'string',
            ],

            'additional_info' => [
                'nullable',
                'string',
            ],

            'regular_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:regular_price',
            ],

            'stock' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'status' => [
                'required',

                Rule::in([
                    'active',
                    'draft',
                    'inactive',
                ]),
            ],

            'featured_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'gallery_images' => [
                'nullable',
                'array',
            ],

            'gallery_images.*' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'categories' => [
                'nullable',
                'array',
            ],

            'categories.*' => [
                'integer',
                'exists:product_categories,id',
            ],

            'tags' => [
                'nullable',
                'array',
            ],

            'tags.*' => [
                'integer',
                'exists:product_tags,id',
            ],

            'product_options' => [
                'nullable',
                'array',
            ],

            'product_options.*' => [
                'integer',
                'exists:product_options,id',
            ],

            'variants' => [
                'nullable',
                'array',
            ],

            'variants.*.sku' => [
                'nullable',
                'string',
                'max:100',
            ],

            'variants.*.regular_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'variants.*.sale_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'variants.*.stock' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'variants.*.image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'variants.*.old_image' => [
                'nullable',
                'string',
                'max:500',
            ],

            'variants.*.options' => [
                'required_with:variants',
                'array',
            ],

            'variants.*.options.*.option_id' => [
                'required',
                'integer',
                'exists:product_options,id',
            ],

            'variants.*.options.*.option_name' => [
                'required',
                'string',
                'max:100',
            ],

            'variants.*.options.*.value_id' => [
                'required',
                'integer',
                'exists:product_option_values,id',
            ],

            'variants.*.options.*.value_label' => [
                'required',
                'string',
                'max:100',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
            ],

            'meta_keywords' => [
                'nullable',
                'string',
            ],
        ]);
    }

    /**
     * Store newly uploaded gallery images.
     */
    private function storeGalleryImages(
        Request $request,
        Product $product
    ): void {
        if (!$request->hasFile('gallery_images')) {
            return;
        }

        foreach (
            $request->file('gallery_images') as $galleryImage
        ) {
            if (!$galleryImage || !$galleryImage->isValid()) {
                continue;
            }

            $imagePath = $galleryImage->store(
                'products/gallery',
                'public'
            );

            $product->images()->create([
                'image' => $imagePath,
            ]);
        }
    }
    public function quickView(\App\Models\Product $product)
    {
        $product->load([
            'images',
            'variants',
            'options',
            'optionValues',
            'categories',
        ]);

        return view(
            'products.partials.quick-view',
            compact('product')
        );
    }

    /**
     * Store submitted product variants.
     */
    private function storeVariants(
        Request $request,
        Product $product,
        array $variants
    ): void {
        foreach ($variants as $index => $variantData) {
            $variantImagePath =
                $variantData['old_image'] ?? null;

            if (
                $request->hasFile(
                    'variants.' . $index . '.image'
                )
            ) {
                $variantImage = $request->file(
                    'variants.' . $index . '.image'
                );

                if (
                    $variantImage
                    && $variantImage->isValid()
                ) {
                    $newVariantImagePath =
                        $variantImage->store(
                            'products/variants',
                            'public'
                        );

                    if ($variantImagePath) {
                        Storage::disk('public')->delete(
                            $variantImagePath
                        );
                    }

                    $variantImagePath =
                        $newVariantImagePath;
                }
            }

            $product->variants()->create([
                'sku' =>
                $variantData['sku'] ?? null,

                'regular_price' =>
                $variantData['regular_price'] ?? null,

                'sale_price' =>
                $variantData['sale_price'] ?? null,

                'stock' =>
                $variantData['stock'] ?? 0,

                'image' =>
                $variantImagePath,

                'options' =>
                $variantData['options'] ?? [],
            ]);
        }
    }
}
