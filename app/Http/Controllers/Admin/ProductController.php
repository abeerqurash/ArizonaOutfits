<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
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
                $this->collectProductOptionValueIds(
                    $validated
                )
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
        $oldVariantImagesToDelete = [];
        $newVariantImagesUploaded = [];
        try {
            $oldFeaturedImagePath =
                $product->featured_image;

            $featuredImagePath =
                $oldFeaturedImagePath;

            $newFeaturedImagePath = null;

            if ($request->hasFile('featured_image')) {

                $newFeaturedImagePath = $request
                    ->file('featured_image')
                    ->store(
                        'products/featured',
                        'public'
                    );

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
                $this->collectProductOptionValueIds(
                    $validated
                )
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
                variants: $validated['variants'] ?? [],
                oldImagesToDelete: $oldVariantImagesToDelete,
                newImagesUploaded: $newVariantImagesUploaded
            );

            DB::commit();

            /*
|--------------------------------------------------------------------------
| Remove replaced featured image after successful database commit
|--------------------------------------------------------------------------
*/

            if (
                $newFeaturedImagePath
                && $oldFeaturedImagePath
                && $oldFeaturedImagePath !== $newFeaturedImagePath
            ) {
                Storage::disk('public')->delete(
                    $oldFeaturedImagePath
                );
            }
            /*
|--------------------------------------------------------------------------
| Delete obsolete variant images after successful commit
|--------------------------------------------------------------------------
*/

            foreach (
                array_unique($oldVariantImagesToDelete)
                as $oldVariantImage
            ) {
                if ($oldVariantImage) {
                    Storage::disk('public')->delete(
                        $oldVariantImage
                    );
                }
            }
            /*
|--------------------------------------------------------------------------
| Remove newly uploaded variant images after rollback
|--------------------------------------------------------------------------
*/

            foreach (
                array_unique($newVariantImagesUploaded)
                as $newVariantImage
            ) {
                if ($newVariantImage) {
                    Storage::disk('public')->delete(
                        $newVariantImage
                    );
                }
            }
            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'Product updated successfully.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();
            /*
|--------------------------------------------------------------------------
| Remove newly uploaded image after failed database update
|--------------------------------------------------------------------------
*/

            if (
                isset($newFeaturedImagePath)
                && $newFeaturedImagePath
            ) {
                Storage::disk('public')->delete(
                    $newFeaturedImagePath
                );
            }
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
    /**
     * Safely delete a product.
     */
    public function destroy(
        Product $product
    ): RedirectResponse {

        /*
    |--------------------------------------------------------------------------
    | Protect historical customer orders
    |--------------------------------------------------------------------------
    */

        $usedInOrders = DB::table('order_items')
            ->where('product_id', $product->id)
            ->exists();

        if ($usedInOrders) {
            return back()->with(
                'error',
                'This product cannot be deleted because it is already used in customer order history. Set the product status to inactive instead.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Protect inventory history
    |--------------------------------------------------------------------------
    */

        $usedInInventory = DB::table('inventory_histories')
            ->where('product_id', $product->id)
            ->exists();

        if ($usedInInventory) {
            return back()->with(
                'error',
                'This product cannot be deleted because it has inventory history. Set the product status to inactive instead.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Protect supplier records
    |--------------------------------------------------------------------------
    */

        $usedBySupplier = DB::table('supplier_products')
            ->where('product_id', $product->id)
            ->exists();

        if ($usedBySupplier) {
            return back()->with(
                'error',
                'This product cannot be deleted because it is connected to supplier records. Set the product status to inactive instead.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Protect purchase-order records
    |--------------------------------------------------------------------------
    */

        $usedInPurchaseOrders = DB::table(
            'purchase_order_items'
        )
            ->where('product_id', $product->id)
            ->exists();

        if ($usedInPurchaseOrders) {
            return back()->with(
                'error',
                'This product cannot be deleted because it is used in purchase-order history. Set the product status to inactive instead.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Load files before deletion
    |--------------------------------------------------------------------------
    */

        $product->load([
            'images',
            'variants',
        ]);

        $filesToDelete = [];

        if ($product->featured_image) {
            $filesToDelete[] =
                $product->featured_image;
        }

        foreach ($product->images as $image) {
            if (!empty($image->image)) {
                $filesToDelete[] =
                    $image->image;
            }
        }

        foreach ($product->variants as $variant) {
            if (!empty($variant->image)) {
                $filesToDelete[] =
                    $variant->image;
            }
        }


        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | Remove relationships
        |--------------------------------------------------------------------------
        */

            $product->categories()->detach();

            $product->tags()->detach();

            $product->options()->detach();

            $product->optionValues()->detach();


            /*
        |--------------------------------------------------------------------------
        | Remove child records
        |--------------------------------------------------------------------------
        */

            $product->images()->delete();

            $product->variants()->delete();


            /*
        |--------------------------------------------------------------------------
        | Delete product
        |--------------------------------------------------------------------------
        */

            $product->delete();


            /*
        |--------------------------------------------------------------------------
        | Commit database FIRST
        |--------------------------------------------------------------------------
        */

            DB::commit();


            /*
        |--------------------------------------------------------------------------
        | Delete physical files only after successful commit
        |--------------------------------------------------------------------------
        */

            foreach (
                array_unique($filesToDelete)
                as $file
            ) {
                if ($file) {
                    Storage::disk('public')
                        ->delete($file);
                }
            }


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

        $validated = $request->validate([
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
        $this->validateVariantIntegrity(
            $validated['variants'] ?? [],
            $product
        );

        return $validated;
    }

    /**
     * Perform advanced safety validation for product variants.
     */
    private function validateVariantIntegrity(
        array $variants,
        ?Product $product = null
    ): void {
        if (empty($variants)) {
            return;
        }

        $errors = [];
        $usedSkus = [];
        $usedCombinations = [];

        foreach ($variants as $index => $variantData) {

            /*
        |--------------------------------------------------------------------------
        | Variant ID
        |--------------------------------------------------------------------------
        */

            $variantId = !empty($variantData['id'])
                ? (int) $variantData['id']
                : null;


            /*
        |--------------------------------------------------------------------------
        | SKU uniqueness inside submitted form
        |--------------------------------------------------------------------------
        */

            $sku = trim((string) ($variantData['sku'] ?? ''));

            if ($sku !== '') {
                $normalizedSku = mb_strtolower($sku);

                if (isset($usedSkus[$normalizedSku])) {
                    $errors["variants.$index.sku"] = 'Each product variant must have a unique SKU.';
                }

                $usedSkus[$normalizedSku] = true;


                /*
            |--------------------------------------------------------------------------
            | SKU uniqueness in product_variants table
            |--------------------------------------------------------------------------
            */

                $skuExists = DB::table('product_variants')
                    ->where('sku', $sku)
                    ->when(
                        $variantId,
                        fn($query) =>
                        $query->where('id', '!=', $variantId)
                    )
                    ->exists();

                if ($skuExists) {
                    $errors["variants.$index.sku"] = 'This variant SKU is already being used by another variant.';
                }


                /*
            |--------------------------------------------------------------------------
            | Do not allow variant SKU to duplicate a main product SKU
            |--------------------------------------------------------------------------
            */

                $productSkuExists = DB::table('products')
                    ->where('sku', $sku)
                    ->when(
                        $product,
                        fn($query) =>
                        $query->where('id', '!=', $product->id)
                    )
                    ->exists();

                if ($productSkuExists) {
                    $errors["variants.$index.sku"] = 'This SKU is already being used by another product.';
                }
            }


            /*
        |--------------------------------------------------------------------------
        | Variant pricing
        |--------------------------------------------------------------------------
        */

            $regularPrice =
                $variantData['regular_price'] ?? null;

            $salePrice =
                $variantData['sale_price'] ?? null;

            if (
                $regularPrice !== null
                && $regularPrice !== ''
                && $salePrice !== null
                && $salePrice !== ''
                && (float) $salePrice > (float) $regularPrice
            ) {
                $errors["variants.$index.sale_price"] = 'Variant sale price cannot be greater than its regular price.';
            }


            /*
        |--------------------------------------------------------------------------
        | Validate options
        |--------------------------------------------------------------------------
        */

            $options = $variantData['options'] ?? [];

            if (empty($options)) {
                $errors["variants.$index.options"] = 'Each variant must contain at least one option.';

                continue;
            }

            $combination = [];
            $usedOptionIds = [];

            foreach ($options as $optionIndex => $optionData) {

                $optionId = isset($optionData['option_id'])
                    ? (int) $optionData['option_id']
                    : 0;

                $valueId = isset($optionData['value_id'])
                    ? (int) $optionData['value_id']
                    : 0;


                /*
            |--------------------------------------------------------------------------
            | Same option cannot appear twice in one variant
            |--------------------------------------------------------------------------
            */

                if (isset($usedOptionIds[$optionId])) {
                    $errors["variants.$index.options.$optionIndex.option_id"] = 'The same option cannot be used more than once in a variant.';
                }

                $usedOptionIds[$optionId] = true;


                /*
            |--------------------------------------------------------------------------
            | Verify value belongs to submitted option
            |--------------------------------------------------------------------------
            */

                if ($optionId > 0 && $valueId > 0) {
                    $valueBelongsToOption =
                        ProductOptionValue::query()
                        ->whereKey($valueId)
                        ->where(
                            'product_option_id',
                            $optionId
                        )
                        ->exists();

                    if (!$valueBelongsToOption) {
                        $errors["variants.$index.options.$optionIndex.value_id"] = 'The selected option value does not belong to the selected option.';
                    }
                }

                $combination[] = [
                    'option_id' => $optionId,
                    'value_id' => $valueId,
                ];
            }


            /*
        |--------------------------------------------------------------------------
        | Create deterministic combination signature
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | Color = Black + Size = Medium
        |
        | must be considered identical regardless of the order in which
        | those options were submitted.
        |
        */

            usort(
                $combination,
                fn(array $a, array $b): int =>
                $a['option_id'] <=> $b['option_id']
            );

            $signature = collect($combination)
                ->map(
                    fn(array $item): string =>
                    $item['option_id']
                        . ':'
                        . $item['value_id']
                )
                ->implode('|');


            /*
        |--------------------------------------------------------------------------
        | Prevent duplicate variant combinations
        |--------------------------------------------------------------------------
        */

            if (
                $signature !== ''
                && isset($usedCombinations[$signature])
            ) {
                $errors["variants.$index.options"] = 'This variant option combination already exists.';
            }

            if ($signature !== '') {
                $usedCombinations[$signature] = true;
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Throw validation errors
    |--------------------------------------------------------------------------
    */

        if (!empty($errors)) {
            throw ValidationException::withMessages(
                $errors
            );
        }
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
        array $variants,
        array &$oldImagesToDelete,
        array &$newImagesUploaded
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
|--------------------------------------------------------------------------
| Remember newly uploaded file
|--------------------------------------------------------------------------
*/

                    $newImagesUploaded[] =
                        $newVariantImagePath;


                    /*
|--------------------------------------------------------------------------
| Schedule old file for deletion after commit
|--------------------------------------------------------------------------
*/

                    if (
                        $variantImagePath
                        && $variantImagePath !== $newVariantImagePath
                    ) {
                        $oldImagesToDelete[] =
                            $variantImagePath;
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
                DB::table('order_items')
                ->where(
                    'variant_id',
                    $variant->id
                )
                ->exists()

                ||

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
                        '" cannot be removed because it is already used by an order, inventory, supplier or purchase-order record. Set its stock to 0 instead of deleting it.',
                ]);
            }


            /*
        |--------------------------------------------------------------------------
        | Remove unused variant image
        |--------------------------------------------------------------------------
        */
            if ($variant->image) {
                $oldImagesToDelete[] =
                    $variant->image;
            }

            $variant->delete();
        }
    }
    /**
     * Collect all option value IDs used by the product and its variants.
     */
    private function collectProductOptionValueIds(
        array $validated
    ): array {
        $valueIds = collect(
            $validated['product_option_values'] ?? []
        )
            ->map(fn($id) => (int) $id);

        foreach (
            $validated['variants'] ?? []
            as $variant
        ) {
            foreach (
                $variant['options'] ?? []
                as $option
            ) {
                if (!empty($option['value_id'])) {
                    $valueIds->push(
                        (int) $option['value_id']
                    );
                }
            }
        }

        return $valueIds
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
