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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ProductController extends AdminController
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
                $request->filled('featured'),
                function ($query) use ($request) {
                    $query->where(
                        'is_featured',
                        $request->input('featured') === '1'
                    );
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

                'cost_price' =>
                $validated['cost_price'],

                'stock' =>
                $validated['stock'] ?? 0,

                'reorder_point' =>
                $validated['reorder_point'] ?? null,

                'reorder_quantity' =>
                $validated['reorder_quantity'] ?? null,

                'status' =>
                $validated['status'],

                'is_featured' =>
                $request->boolean('is_featured'),

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

            $product->optionValues()->sync(
                $validated['product_option_values'] ?? []
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

                'cost_price' =>
                $validated['cost_price'],

                'stock' =>
                $validated['stock'] ?? 0,

                'reorder_point' =>
                $validated['reorder_point'] ?? null,

                'reorder_quantity' =>
                $validated['reorder_quantity'] ?? null,

                'status' =>
                $validated['status'],

                'is_featured' =>
                $request->boolean('is_featured'),

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
            $product->optionValues()->sync(
                $validated['product_option_values'] ?? []
            );
            $this->storeGalleryImages(
                request: $request,
                product: $product
            );

            /*
|--------------------------------------------------------------------------
| Safely synchronize product variants
|--------------------------------------------------------------------------
|
| Existing variants are updated instead of deleted/recreated.
| This preserves variant IDs used by inventory, suppliers,
| purchase orders and historical records.
|
*/

            $this->syncVariants(
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
            $product->optionValues()->detach();
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

            'cost_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'reorder_point' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'reorder_quantity' => [
                'nullable',
                'integer',
                'min:1',
                'max:1000000',
            ],

            'status' => [
                'required',

                Rule::in([
                    'active',
                    'draft',
                    'inactive',
                ]),
            ],

            'is_featured' => [
                'nullable',
                'boolean',
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

            'product_option_values' => [
                'nullable',
                'array',
            ],

            'product_option_values.*' => [
                'integer',
                'distinct',
                'exists:product_option_values,id',
            ],

            'variants' => [
                'nullable',
                'array',
            ],

            'variants.*.id' => [
                'nullable',
                'integer',

                Rule::exists(
                    'product_variants',
                    'id'
                )->where(
                    function ($query) use ($product) {
                        if ($product) {
                            $query->where(
                                'product_id',
                                $product->id
                            );
                        } else {
                            /*
                 * New products cannot submit an existing variant ID.
                 */
                            $query->whereRaw('1 = 0');
                        }
                    }
                ),
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

            'variants.*.reorder_point' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'variants.*.reorder_quantity' => [
                'nullable',
                'integer',
                'min:1',
                'max:1000000',
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

                'reorder_point' =>
                $variantData['reorder_point'] ?? null,

                'reorder_quantity' =>
                $variantData['reorder_quantity'] ?? null,

                'image' =>
                $variantImagePath,

                'options' => collect(
                    $variantData['options'] ?? []
                )->map(function (array $option): array {
                    return [
                        'option_id' => (int) $option['option_id'],
                        'option_name' => (string) $option['option_name'],
                        'value_id' => (int) $option['value_id'],
                        'value_label' => (string) $option['value_label'],
                    ];
                })->values()->all(),
            ]);
        }
    }

    /**
     * Safely synchronize variants when editing a product.
     *
     * Existing variants keep their database IDs.
     * New variants are created.
     * Removed variants are deleted only when they are not referenced
     * by important historical/business records.
     */
    private function syncVariants(
        Request $request,
        Product $product,
        array $variants
    ): void {
        /*
    |--------------------------------------------------------------------------
    | Load current variants
    |--------------------------------------------------------------------------
    */

        $existingVariants = $product
            ->variants()
            ->get()
            ->keyBy('id');

        $submittedVariantIds = [];


        /*
    |--------------------------------------------------------------------------
    | Create or update submitted variants
    |--------------------------------------------------------------------------
    */

        foreach ($variants as $index => $variantData) {

            $submittedId = isset($variantData['id'])
                ? (int) $variantData['id']
                : null;


            /*
        |--------------------------------------------------------------------------
        | Existing variant
        |--------------------------------------------------------------------------
        */

            if (
                $submittedId
                && $existingVariants->has($submittedId)
            ) {
                $variant = $existingVariants->get(
                    $submittedId
                );

                $submittedVariantIds[] =
                    $submittedId;
            }

            /*
        |--------------------------------------------------------------------------
        | New variant
        |--------------------------------------------------------------------------
        */ else {
                $variant = $product
                    ->variants()
                    ->make();
            }


            /*
        |--------------------------------------------------------------------------
        | Preserve existing image
        |--------------------------------------------------------------------------
        |
        | Do not trust a hidden old_image path from the browser when
        | an existing database record already exists.
        |
        */

            $variantImagePath =
                $variant->exists
                ? $variant->image
                : null;


            /*
        |--------------------------------------------------------------------------
        | New uploaded variant image
        |--------------------------------------------------------------------------
        */

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

                    /*
                 * Remove previous image only after the replacement
                 * has been successfully stored.
                 */

                    if ($variantImagePath) {
                        Storage::disk('public')
                            ->delete(
                                $variantImagePath
                            );
                    }

                    $variantImagePath =
                        $newVariantImagePath;
                }
            }


            /*
        |--------------------------------------------------------------------------
        | Update variant values
        |--------------------------------------------------------------------------
        */

            $variant->fill([
                'sku' =>
                $variantData['sku'] ?? null,

                'regular_price' =>
                $variantData['regular_price'] ?? null,

                'sale_price' =>
                $variantData['sale_price'] ?? null,

                'stock' =>
                $variantData['stock'] ?? 0,

                'reorder_point' =>
                $variantData['reorder_point'] ?? null,

                'reorder_quantity' =>
                $variantData['reorder_quantity'] ?? null,

                'image' =>
                $variantImagePath,

                'options' => collect(
                    $variantData['options'] ?? []
                )->map(
                    function (array $option): array {
                        return [
                            'option_id' =>
                            (int) $option['option_id'],

                            'option_name' =>
                            (string) $option['option_name'],

                            'value_id' =>
                            (int) $option['value_id'],

                            'value_label' =>
                            (string) $option['value_label'],
                        ];
                    }
                )->values()->all(),
            ]);


            /*
        |--------------------------------------------------------------------------
        | Save variant
        |--------------------------------------------------------------------------
        */

            $variant->save();


            /*
         * New variants receive an ID only after save().
         */
            if (! $submittedId) {
                $submittedVariantIds[] =
                    $variant->id;
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Find variants removed from the edit form
    |--------------------------------------------------------------------------
    */

        $removedVariants = $existingVariants
            ->reject(
                fn($variant) =>
                in_array(
                    $variant->id,
                    $submittedVariantIds,
                    true
                )
            );


        /*
    |--------------------------------------------------------------------------
    | Safely remove variants
    |--------------------------------------------------------------------------
    */

        foreach ($removedVariants as $variant) {

            /*
        |--------------------------------------------------------------------------
        | Check historical/business references
        |--------------------------------------------------------------------------
        */

            $isReferenced =
                DB::table('inventory_histories')
                ->where(
                    'product_variant_id',
                    $variant->id
                )
                ->exists()

                ||

                DB::table('purchase_order_items')
                ->where(
                    'product_variant_id',
                    $variant->id
                )
                ->exists()

                ||

                DB::table('supplier_products')
                ->where(
                    'product_variant_id',
                    $variant->id
                )
                ->exists()

                ||

                DB::table('purchase_order_receipt_items')
                ->where(
                    'product_variant_id',
                    $variant->id
                )
                ->exists()

                ||

                DB::table('supplier_return_items')
                ->where(
                    'product_variant_id',
                    $variant->id
                )
                ->exists()

                ||

                DB::table('inventory_alerts')
                ->where(
                    'product_variant_id',
                    $variant->id
                )
                ->exists();


            /*
        |--------------------------------------------------------------------------
        | Do not destroy referenced variant
        |--------------------------------------------------------------------------
        */

            if ($isReferenced) {
                throw ValidationException::withMessages([
                    'variants' =>
                    'Variant "' .
                        ($variant->sku ?: '#' . $variant->id) .
                        '" cannot be removed because it is already used in inventory, supplier or purchase-order history. Set its stock to 0 instead of deleting it.',
                ]);
            }


            /*
        |--------------------------------------------------------------------------
        | Remove unused variant image
        |--------------------------------------------------------------------------
        */

            if ($variant->image) {
                Storage::disk('public')
                    ->delete(
                        $variant->image
                    );
            }


            /*
        |--------------------------------------------------------------------------
        | Delete safe unused variant
        |--------------------------------------------------------------------------
        */

            $variant->delete();
        }
    }
}
