<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ProductController extends AdminController
{
    /*
    |--------------------------------------------------------------------------
    | Product Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'draft',
                    'inactive',
                ]),
            ],

            'featured' => [
                'nullable',
                Rule::in([
                    '0',
                    '1',
                ]),
            ],

            'category' => [
                'nullable',
                'integer',
                'exists:product_categories,id',
            ],
        ]);

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

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            ->when(
                filled($validated['search'] ?? null),
                function ($query) use ($validated) {
                    $search = trim(
                        (string) $validated['search']
                    );

                    $query->where(
                        function ($subQuery) use ($search) {
                            $subQuery
                                ->where(
                                    'title',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'slug',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'sku',
                                    'like',
                                    '%' . $search . '%'
                                );
                        }
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            ->when(
                filled($validated['status'] ?? null),
                fn($query) => $query->where(
                    'status',
                    $validated['status']
                )
            )

            /*
            |--------------------------------------------------------------------------
            | Featured
            |--------------------------------------------------------------------------
            */

            ->when(
                array_key_exists(
                    'featured',
                    $validated
                ),
                fn($query) => $query->where(
                    'is_featured',
                    $validated['featured'] === '1'
                )
            )

            /*
            |--------------------------------------------------------------------------
            | Category
            |--------------------------------------------------------------------------
            */

            ->when(
                !empty($validated['category']),
                function ($query) use ($validated) {
                    $query->whereHas(
                        'categories',
                        fn($categoryQuery) =>
                        $categoryQuery->where(
                            'product_categories.id',
                            $validated['category']
                        )
                    );
                }
            )

            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = ProductCategory::query()
            ->orderBy('title')
            ->get([
                'id',
                'title',
            ]);

        return view(
            'admin.products.index',
            compact(
                'products',
                'categories'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Product
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view(
            'admin.products.create',
            $this->getProductFormData()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Product
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $this->validateProduct(
            request: $request
        );

        /*
        |--------------------------------------------------------------------------
        | Track newly-created files
        |--------------------------------------------------------------------------
        |
        | Database transactions cannot roll back files.
        | If anything fails, every newly-created physical file is removed.
        |
        */

        $newFiles = [];

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Featured Image
            |--------------------------------------------------------------------------
            */

            $featuredImagePath = null;

            if ($request->hasFile('featured_image')) {
                $featuredImagePath =
                    $this->storeSeoImage(
                        $request->file(
                            'featured_image'
                        ),
                        'products/featured'
                    );

                $newFiles[] =
                    $featuredImagePath;
            }

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $product = Product::create(
                $this->productPayload(
                    validated: $validated,
                    request: $request,
                    featuredImagePath: $featuredImagePath
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $this->syncProductRelationships(
                product: $product,
                validated: $validated
            );

            /*
            |--------------------------------------------------------------------------
            | Gallery
            |--------------------------------------------------------------------------
            */

            $this->storeGalleryImages(
                request: $request,
                product: $product,
                newFiles: $newFiles
            );

            /*
            |--------------------------------------------------------------------------
            | Variants
            |--------------------------------------------------------------------------
            */

            $this->storeVariants(
                request: $request,
                product: $product,
                variants: $validated['variants'] ?? [],
                newFiles: $newFiles
            );

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'Product created successfully.'
                );
        } catch (Throwable $exception) {
            $this->safeRollback();

            /*
            |--------------------------------------------------------------------------
            | Remove physical files created during failed request
            |--------------------------------------------------------------------------
            */

            $this->deleteFiles($newFiles);

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


    /*
    |--------------------------------------------------------------------------
    | Edit Product
    |--------------------------------------------------------------------------
    */

    public function edit(
        Product $product
    ): View {
        $product->load([
            'categories',
            'tags',
            'options.values',
            'optionValues',
            'variants',
            'images' => function ($query) {
                $query
                    ->orderBy('sort_order')
                    ->orderBy('id');
            },
        ]);

        return view(
            'admin.products.edit',
            array_merge(
                [
                    'product' => $product,
                ],
                $this->getProductFormData()
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Product
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {
        $validated = $this->validateProduct(
            request: $request,
            product: $product
        );

        /*
        |--------------------------------------------------------------------------
        | File transaction tracking
        |--------------------------------------------------------------------------
        */

        $newFiles = [];
        $oldFilesToDelete = [];

        DB::beginTransaction();

        try {
            /*
|--------------------------------------------------------------------------
| Featured Image
|--------------------------------------------------------------------------
|
| Supports:
| - Keep existing image
| - Replace existing image
| - Remove existing image
|
| Important:
| Old physical files are only deleted AFTER the database
| transaction commits successfully.
|
*/

            $oldFeaturedImage =
                $product->featured_image;

            $featuredImagePath =
                $oldFeaturedImage;


            /*
|--------------------------------------------------------------------------
| Replace / upload featured image
|--------------------------------------------------------------------------
|
| A newly uploaded image takes priority over the remove flag.
|
*/

            if (
                $request->hasFile(
                    'featured_image'
                )
            ) {

                $featuredImagePath =
                    $this->storeSeoImage(
                        $request->file(
                            'featured_image'
                        ),
                        'products/featured'
                    );


                /*
    |--------------------------------------------------------------------------
    | Track new file for rollback
    |--------------------------------------------------------------------------
    */

                $newFiles[] =
                    $featuredImagePath;


                /*
    |--------------------------------------------------------------------------
    | Delete previous image only after commit
    |--------------------------------------------------------------------------
    */

                if (
                    $oldFeaturedImage &&
                    $oldFeaturedImage !==
                    $featuredImagePath
                ) {

                    $oldFilesToDelete[] =
                        $oldFeaturedImage;
                }


                /*
|--------------------------------------------------------------------------
| Remove existing featured image
|--------------------------------------------------------------------------
*/
            } elseif (
                $request->boolean(
                    'remove_featured_image'
                )
            ) {

                $featuredImagePath = null;


                /*
    |--------------------------------------------------------------------------
    | Queue old file for deletion after successful DB commit
    |--------------------------------------------------------------------------
    */

                if ($oldFeaturedImage) {

                    $oldFilesToDelete[] =
                        $oldFeaturedImage;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $product->update(
                $this->productPayload(
                    validated: $validated,
                    request: $request,
                    featuredImagePath: $featuredImagePath
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $this->syncProductRelationships(
                product: $product,
                validated: $validated
            );

            /*
            |--------------------------------------------------------------------------
            | Remove selected gallery images
            |--------------------------------------------------------------------------
            */

            $this->removeGalleryImages(
                product: $product,
                imageIds: $validated['remove_gallery_images'] ?? [],
                oldFilesToDelete: $oldFilesToDelete
            );

            /*
            |--------------------------------------------------------------------------
            | Add gallery images
            |--------------------------------------------------------------------------
            */

            $this->storeGalleryImages(
                request: $request,
                product: $product,
                newFiles: $newFiles
            );

            /*
            |--------------------------------------------------------------------------
            | Variants
            |--------------------------------------------------------------------------
            |
            | Existing submitted IDs are updated in-place.
            | Their IDs are NOT recreated.
            |
            */

            $this->syncVariants(
                request: $request,
                product: $product,
                variants: $validated['variants'] ?? [],
                oldFilesToDelete: $oldFilesToDelete,
                newFiles: $newFiles
            );

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Database is now safe.
            | Delete obsolete physical files.
            |--------------------------------------------------------------------------
            */

            $this->deleteFiles(
                $oldFilesToDelete
            );

            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'Product updated successfully.'
                );
        } catch (Throwable $exception) {
            $this->safeRollback();

            /*
            |--------------------------------------------------------------------------
            | Request failed.
            | Remove ONLY files uploaded by this failed request.
            |--------------------------------------------------------------------------
            |
            | Existing files remain untouched.
            |
            */

            $this->deleteFiles($newFiles);

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


    /*
    |--------------------------------------------------------------------------
    | Delete Product
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Product $product
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Protect business/history records
        |--------------------------------------------------------------------------
        */

        if (
            $this->productHasReferences(
                $product
            )
        ) {
            return back()->with(
                'error',
                'This product cannot be permanently deleted because it is connected to order, inventory, supplier or purchase-order history. Set the product status to inactive instead.'
            );
        }

        $product->load([
            'images',
            'variants',
        ]);

        $filesToDelete = collect();

        if ($product->featured_image) {
            $filesToDelete->push(
                $product->featured_image
            );
        }

        foreach (
            $product->images
            as $image
        ) {
            if ($image->image) {
                $filesToDelete->push(
                    $image->image
                );
            }
        }

        foreach (
            $product->variants
            as $variant
        ) {
            if ($variant->image) {
                $filesToDelete->push(
                    $variant->image
                );
            }
        }

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Pivots
            |--------------------------------------------------------------------------
            */

            $product
                ->categories()
                ->detach();

            $product
                ->tags()
                ->detach();

            $product
                ->options()
                ->detach();

            $product
                ->optionValues()
                ->detach();

            /*
            |--------------------------------------------------------------------------
            | Child records
            |--------------------------------------------------------------------------
            */

            $product
                ->images()
                ->delete();

            $product
                ->variants()
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $product->delete();

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Physical files after DB commit
            |--------------------------------------------------------------------------
            */

            $this->deleteFiles(
                $filesToDelete->all()
            );

            return redirect()
                ->route('admin.products.index')
                ->with(
                    'success',
                    'Product deleted successfully.'
                );
        } catch (Throwable $exception) {
            $this->safeRollback();

            report($exception);

            return back()->with(
                'error',
                'The product could not be deleted: '
                    . $exception->getMessage()
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    private function getProductFormData(): array
    {
        $categories =
            ProductCategory::query()
            ->with('parent')
            ->orderBy('title')
            ->get();

        $tags =
            ProductTag::query()
            ->orderBy('title')
            ->get();

        $productOptions =
            ProductOption::query()
            ->with([
                'values' =>
                function ($query) {
                    $query
                        ->orderBy(
                            'label'
                        );
                },
            ])
            ->orderBy('name')
            ->get();

        return compact(
            'categories',
            'tags',
            'productOptions'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Product Validation
    |--------------------------------------------------------------------------
    */

    private function validateProduct(
        Request $request,
        ?Product $product = null
    ): array {
        $productId =
            $product?->id;

        // Slug is optional in the UI. Generate and normalize it on the server
        // so create/update remain safe even when JavaScript is unavailable.
        $requestedSlug = trim((string) $request->input('slug', ''));
        $slugSource = $requestedSlug !== ''
            ? $requestedSlug
            : (string) $request->input('title', '');

        $request->merge([
            'slug' => $this->makeUniqueProductSlug(
                $slugSource,
                $productId
            ),
        ]);

        $validated =
            $request->validate([
                /*
                |--------------------------------------------------------------------------
                | Basic
                |--------------------------------------------------------------------------
                */

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
                    )->ignore(
                        $productId
                    ),
                ],

                'sku' => [
                    'nullable',
                    'string',
                    'max:100',

                    Rule::unique(
                        'products',
                        'sku'
                    )->ignore(
                        $productId
                    ),
                ],

                /*
                |--------------------------------------------------------------------------
                | Description
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Pricing
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Inventory
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Featured Image
                |--------------------------------------------------------------------------
                */

                /*
|--------------------------------------------------------------------------
| Featured Image
|--------------------------------------------------------------------------
*/

                'featured_image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

                'remove_featured_image' => [
                    'nullable',
                    'boolean',
                ],

                /*
                |--------------------------------------------------------------------------
                | Gallery
                |--------------------------------------------------------------------------
                */

                'gallery_images' => [
                    'nullable',
                    'array',
                    'max:20',
                ],

                'gallery_images.*' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

                'remove_gallery_images' => [
                    'nullable',
                    'array',
                ],

                'remove_gallery_images.*' => [
                    'integer',
                    'distinct',
                ],

                /*
                |--------------------------------------------------------------------------
                | Categories
                |--------------------------------------------------------------------------
                */

                'categories' => [
                    'nullable',
                    'array',
                ],

                'categories.*' => [
                    'integer',
                    'distinct',
                    'exists:product_categories,id',
                ],

                /*
                |--------------------------------------------------------------------------
                | Tags
                |--------------------------------------------------------------------------
                */

                'tags' => [
                    'nullable',
                    'array',
                ],

                'tags.*' => [
                    'integer',
                    'distinct',
                    'exists:product_tags,id',
                ],

                /*
                |--------------------------------------------------------------------------
                | Product Options
                |--------------------------------------------------------------------------
                */

                'product_options' => [
                    'nullable',
                    'array',
                ],

                'product_options.*' => [
                    'integer',
                    'distinct',
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

                /*
                |--------------------------------------------------------------------------
                | Variants
                |--------------------------------------------------------------------------
                */

                'variants' => [
                    'nullable',
                    'array',
                    'max:250',
                ],

                'variants.*.id' => [
                    'nullable',
                    'integer',

                    Rule::exists(
                        'product_variants',
                        'id'
                    )->where(
                        function (
                            $query
                        ) use (
                            $product
                        ) {
                            if ($product) {
                                $query->where(
                                    'product_id',
                                    $product->id
                                );
                            } else {
                                /*
                                 * A new product must
                                 * never submit an
                                 * existing variant ID.
                                 */
                                $query->whereRaw(
                                    '1 = 0'
                                );
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

                'variants.*.remove_image' => [
                    'nullable',
                    'boolean',
                ],

                /*
|--------------------------------------------------------------------------
| Old Image
|--------------------------------------------------------------------------
|
| Kept only for current Blade compatibility.
| Existing database image remains the source of truth.
|
*/

                'variants.*.old_image' => [
                    'nullable',
                    'string',
                    'max:500',
                ],

                'variants.*.options' => [
                    'required_with:variants',
                    'array',
                    'min:1',
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

                /*
                |--------------------------------------------------------------------------
                | SEO
                |--------------------------------------------------------------------------
                */

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
            variants: $validated['variants'] ?? [],
            product: $product
        );

        return $validated;
    }


    /*
    |--------------------------------------------------------------------------
    | Product Payload
    |--------------------------------------------------------------------------
    */

    private function productPayload(
        array $validated,
        Request $request,
        ?string $featuredImagePath
    ): array {
        return [
            'title' =>
            trim($validated['title']),

            'slug' =>
            trim($validated['slug']),

            'sku' =>
            $this->nullableString(
                $validated['sku'] ?? null
            ),

            'short_description' =>
            $validated['short_description'] ?? null,

            'long_description' =>
            $validated['long_description'] ?? null,

            'additional_info' =>
            $validated['additional_info'] ?? null,

            'regular_price' =>
            $validated['regular_price'],

            'sale_price' =>
            $this->nullableNumber(
                $validated['sale_price'] ?? null
            ),

            'cost_price' =>
            $validated['cost_price'],

            'stock' =>
            (int) (
                $validated['stock'] ?? 0
            ),

            'reorder_point' =>
            $this->nullableInteger(
                $validated['reorder_point'] ?? null
            ),

            'reorder_quantity' =>
            $this->nullableInteger(
                $validated['reorder_quantity'] ?? null
            ),

            'status' =>
            $validated['status'],

            'is_featured' =>
            $request->boolean(
                'is_featured'
            ),

            'featured_image' =>
            $featuredImagePath,

            'meta_title' =>
            $this->nullableString(
                $validated['meta_title'] ?? null
            ),

            'meta_description' =>
            $this->nullableString(
                $validated['meta_description'] ?? null
            ),

            'meta_keywords' =>
            $this->nullableString(
                $validated['meta_keywords'] ?? null
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Synchronize Product Relationships
    |--------------------------------------------------------------------------
    */

    private function syncProductRelationships(
        Product $product,
        array $validated
    ): void {
        $product->categories()->sync(
            $validated['categories'] ?? []
        );

        $product->tags()->sync(
            $validated['tags'] ?? []
        );

        $product->options()->sync(
            $validated['product_options'] ?? []
        );

        /*
        |--------------------------------------------------------------------------
        | Product option values
        |--------------------------------------------------------------------------
        |
        | Includes explicitly selected values AND every value referenced
        | by submitted variants.
        |
        */

        $product->optionValues()->sync(
            $this->collectProductOptionValueIds(
                $validated
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Variant Integrity
    |--------------------------------------------------------------------------
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

        /*
        |--------------------------------------------------------------------------
        | Cache option/value relationships
        |--------------------------------------------------------------------------
        */

        $submittedValueIds =
            collect($variants)
            ->flatMap(
                fn(array $variant) =>
                collect(
                    $variant['options'] ?? []
                )->pluck(
                    'value_id'
                )
            )
            ->filter()
            ->map(
                fn($id) =>
                (int) $id
            )
            ->unique()
            ->values();

        $valueOptionMap =
            ProductOptionValue::query()
            ->whereIn(
                'id',
                $submittedValueIds
            )
            ->pluck(
                'product_option_id',
                'id'
            );

        foreach (
            $variants
            as $index => $variantData
        ) {
            $variantId =
                !empty($variantData['id'])
                ? (int) $variantData['id']
                : null;

            /*
            |--------------------------------------------------------------------------
            | SKU
            |--------------------------------------------------------------------------
            */

            $sku = trim(
                (string) (
                    $variantData['sku']
                    ?? ''
                )
            );

            if ($sku !== '') {
                $normalizedSku =
                    mb_strtolower($sku);

                if (
                    isset(
                        $usedSkus[$normalizedSku]
                    )
                ) {
                    $errors["variants.$index.sku"] =
                        'Each product variant must have a unique SKU.';
                }

                $usedSkus[$normalizedSku] = true;

                /*
                |--------------------------------------------------------------------------
                | Other variants
                |--------------------------------------------------------------------------
                */

                $variantSkuExists =
                    DB::table(
                        'product_variants'
                    )
                    ->where(
                        'sku',
                        $sku
                    )
                    ->when(
                        $variantId,
                        fn($query) =>
                        $query->where(
                            'id',
                            '!=',
                            $variantId
                        )
                    )
                    ->exists();

                if ($variantSkuExists) {
                    $errors["variants.$index.sku"] =
                        'This variant SKU is already being used by another variant.';
                }

                /*
                |--------------------------------------------------------------------------
                | Main product SKU
                |--------------------------------------------------------------------------
                */

                $productSkuExists =
                    Product::query()
                    ->where(
                        'sku',
                        $sku
                    )
                    ->when(
                        $product,
                        fn($query) =>
                        $query->where(
                            'id',
                            '!=',
                            $product->id
                        )
                    )
                    ->exists();

                if ($productSkuExists) {
                    $errors["variants.$index.sku"] =
                        'This SKU is already being used by another product.';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            $regularPrice =
                $variantData['regular_price'] ?? null;

            $salePrice =
                $variantData['sale_price'] ?? null;

            if (
                filled($regularPrice)
                && filled($salePrice)
                && (float) $salePrice
                > (float) $regularPrice
            ) {
                $errors["variants.$index.sale_price"] =
                    'Variant sale price cannot be greater than its regular price.';
            }

            /*
            |--------------------------------------------------------------------------
            | Options
            |--------------------------------------------------------------------------
            */

            $options =
                $variantData['options'] ?? [];

            if (empty($options)) {
                $errors["variants.$index.options"] =
                    'Each variant must contain at least one option.';

                continue;
            }

            $combination = [];
            $usedOptionIds = [];

            foreach (
                $options
                as $optionIndex =>
                $optionData
            ) {
                $optionId =
                    (int) (
                        $optionData['option_id'] ?? 0
                    );

                $valueId =
                    (int) (
                        $optionData['value_id'] ?? 0
                    );

                /*
                |--------------------------------------------------------------------------
                | Same option twice
                |--------------------------------------------------------------------------
                */

                if (
                    isset(
                        $usedOptionIds[$optionId]
                    )
                ) {
                    $errors["variants.$index.options.$optionIndex.option_id"] =
                        'The same option cannot be used more than once in a variant.';
                }

                $usedOptionIds[$optionId] = true;

                /*
                |--------------------------------------------------------------------------
                | Verify value belongs to option
                |--------------------------------------------------------------------------
                */

                if (
                    $optionId > 0
                    && $valueId > 0
                    && (int) (
                        $valueOptionMap[$valueId] ?? 0
                    ) !== $optionId
                ) {
                    $errors["variants.$index.options.$optionIndex.value_id"] =
                        'The selected option value does not belong to the selected option.';
                }

                $combination[] = [
                    'option_id' =>
                    $optionId,

                    'value_id' =>
                    $valueId,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Deterministic signature
            |--------------------------------------------------------------------------
            */

            usort(
                $combination,
                fn(
                    array $a,
                    array $b
                ): int =>
                $a['option_id']
                    <=>
                    $b['option_id']
            );

            $signature =
                collect($combination)
                ->map(
                    fn(
                        array $item
                    ): string =>
                    $item['option_id']
                        . ':'
                        . $item['value_id']
                )
                ->implode('|');

            if (
                $signature !== ''
                && isset(
                    $usedCombinations[$signature]
                )
            ) {
                $errors["variants.$index.options"] =
                    'This variant option combination already exists.';
            }

            if ($signature !== '') {
                $usedCombinations[$signature] = true;
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages(
                $errors
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Gallery Upload
    |--------------------------------------------------------------------------
    */

    private function storeGalleryImages(
        Request $request,
        Product $product,
        array &$newFiles
    ): void {
        if (
            !$request->hasFile(
                'gallery_images'
            )
        ) {
            return;
        }

        $currentSortOrder =
            (int) (
                $product
                ->images()
                ->max('sort_order')
                ?? 0
            );

        foreach (
            $request->file(
                'gallery_images'
            )
            as $galleryImage
        ) {
            if (
                !$galleryImage
                || !$galleryImage->isValid()
            ) {
                continue;
            }

            $imagePath =
                $this->storeSeoImage(
                    $galleryImage,
                    'products/gallery'
                );

            $newFiles[] =
                $imagePath;

            $currentSortOrder++;

            $product
                ->images()
                ->create([
                    'image' =>
                    $imagePath,

                    'sort_order' =>
                    $currentSortOrder,
                ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Gallery Images
    |--------------------------------------------------------------------------
    */

    private function removeGalleryImages(
        Product $product,
        array $imageIds,
        array &$oldFilesToDelete
    ): void {
        $imageIds =
            collect($imageIds)
            ->map(
                fn($id) =>
                (int) $id
            )
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($imageIds)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Query through relationship.
        | Prevents deleting another product's image.
        |--------------------------------------------------------------------------
        */

        $images =
            $product
            ->images()
            ->whereIn(
                'id',
                $imageIds
            )
            ->get();

        foreach ($images as $image) {
            if ($image->image) {
                $oldFilesToDelete[] =
                    $image->image;
            }

            $image->delete();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Store New Product Variants
    |--------------------------------------------------------------------------
    */

    private function storeVariants(
        Request $request,
        Product $product,
        array $variants,
        array &$newFiles
    ): void {
        foreach (
            $variants
            as $index => $variantData
        ) {
            /*
            |--------------------------------------------------------------------------
            | New product cannot legitimately have an old variant image.
            |--------------------------------------------------------------------------
            */

            $variantImagePath = null;

            $variantImage =
                $request->file(
                    'variants.'
                        . $index
                        . '.image'
                );

            if (
                $variantImage
                && $variantImage->isValid()
            ) {
                $variantImagePath =
                    $this->storeSeoImage(
                        $variantImage,
                        'products/variants'
                    );

                $newFiles[] =
                    $variantImagePath;
            }

            $product
                ->variants()
                ->create(
                    $this->variantPayload(
                        variantData: $variantData,
                        imagePath: $variantImagePath
                    )
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Synchronize Existing Product Variants
    |--------------------------------------------------------------------------
    */

    private function syncVariants(
        Request $request,
        Product $product,
        array $variants,
        array &$oldFilesToDelete,
        array &$newFiles
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Existing variants
        |--------------------------------------------------------------------------
        */

        $existingVariants =
            $product
            ->variants()
            ->get()
            ->keyBy('id');

        $submittedVariantIds = [];

        foreach (
            $variants
            as $index => $variantData
        ) {
            $submittedId =
                !empty($variantData['id'])
                ? (int) $variantData['id']
                : null;

            /*
            |--------------------------------------------------------------------------
            | Existing or new
            |--------------------------------------------------------------------------
            */

            if (
                $submittedId
                && $existingVariants->has(
                    $submittedId
                )
            ) {
                $variant =
                    $existingVariants->get(
                        $submittedId
                    );
            } else {
                $variant =
                    $product
                    ->variants()
                    ->make();

                $submittedId = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Existing image comes ONLY from database
            |--------------------------------------------------------------------------
            */

            $variantImagePath =
                $variant->exists
                ? $variant->image
                : null;

            /*
            |--------------------------------------------------------------------------
            | Replacement image
            |--------------------------------------------------------------------------
            */

            $variantImage =
                $request->file(
                    'variants.'
                        . $index
                        . '.image'
                );

            if (
                $variantImage
                && $variantImage->isValid()
            ) {
                $newVariantImagePath =
                    $this->storeSeoImage(
                        $variantImage,
                        'products/variants'
                    );

                $newFiles[] =
                    $newVariantImagePath;

                if (
                    $variantImagePath
                    && $variantImagePath
                    !== $newVariantImagePath
                ) {
                    $oldFilesToDelete[] =
                        $variantImagePath;
                }

                $variantImagePath =
                    $newVariantImagePath;
            } elseif (
                $request->boolean(
                    'variants.' . $index . '.remove_image'
                )
            ) {
                if ($variantImagePath) {
                    $oldFilesToDelete[] = $variantImagePath;
                }

                $variantImagePath = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Save values
            |--------------------------------------------------------------------------
            */

            $variant->fill(
                $this->variantPayload(
                    variantData: $variantData,
                    imagePath: $variantImagePath
                )
            );

            $variant->save();

            $submittedVariantIds[] =
                (int) $variant->id;
        }

        /*
        |--------------------------------------------------------------------------
        | Variants removed from form
        |--------------------------------------------------------------------------
        */

        $removedVariants =
            $existingVariants
            ->reject(
                fn($variant) =>
                in_array(
                    (int) $variant->id,
                    $submittedVariantIds,
                    true
                )
            );

        foreach (
            $removedVariants
            as $variant
        ) {
            /*
            |--------------------------------------------------------------------------
            | Never destroy a historically referenced variant
            |--------------------------------------------------------------------------
            */

            if (
                $this->variantHasReferences(
                    (int) $variant->id
                )
            ) {
                throw ValidationException::withMessages([
                    'variants' =>
                    'Variant "'
                        . (
                            $variant->sku
                            ?: '#'
                            . $variant->id
                        )
                        . '" cannot be removed because it is already used by an order, inventory, supplier or purchase-order record. Keep the variant and set its stock to 0 instead.',
                ]);
            }

            if ($variant->image) {
                $oldFilesToDelete[] =
                    $variant->image;
            }

            $variant->delete();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Variant Payload
    |--------------------------------------------------------------------------
    */

    private function variantPayload(
        array $variantData,
        ?string $imagePath
    ): array {
        return [
            'sku' =>
            $this->nullableString(
                $variantData['sku'] ?? null
            ),

            'regular_price' =>
            $this->nullableNumber(
                $variantData['regular_price'] ?? null
            ),

            'sale_price' =>
            $this->nullableNumber(
                $variantData['sale_price'] ?? null
            ),

            'stock' =>
            (int) (
                $variantData['stock'] ?? 0
            ),

            'reorder_point' =>
            $this->nullableInteger(
                $variantData['reorder_point'] ?? null
            ),

            'reorder_quantity' =>
            $this->nullableInteger(
                $variantData['reorder_quantity'] ?? null
            ),

            'image' =>
            $imagePath,

            'options' =>
            $this->normalizeVariantOptions(
                $variantData['options'] ?? []
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Variant Options
    |--------------------------------------------------------------------------
    */

    private function normalizeVariantOptions(
        array $options
    ): array {
        return collect($options)
            ->map(
                function (
                    array $option
                ): array {
                    return [
                        'option_id' =>
                        (int) (
                            $option['option_id'] ?? 0
                        ),

                        'option_name' =>
                        trim(
                            (string) (
                                $option['option_name'] ?? ''
                            )
                        ),

                        'value_id' =>
                        (int) (
                            $option['value_id'] ?? 0
                        ),

                        'value_label' =>
                        trim(
                            (string) (
                                $option['value_label'] ?? ''
                            )
                        ),
                    ];
                }
            )
            ->sortBy('option_id')
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Collect Product Option Values
    |--------------------------------------------------------------------------
    */

    private function collectProductOptionValueIds(
        array $validated
    ): array {
        $valueIds =
            collect(
                $validated['product_option_values'] ?? []
            )
            ->map(
                fn($id) =>
                (int) $id
            );

        foreach (
            $validated['variants'] ?? []
            as $variant
        ) {
            foreach (
                $variant['options'] ?? []
                as $option
            ) {
                if (
                    !empty($option['value_id'])
                ) {
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


    /*
    |--------------------------------------------------------------------------
    | SEO-Friendly Image Storage
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | Men's Black Leather Jacket.webp
    |
    | becomes:
    |
    | mens-black-leather-jacket.webp
    |
    | Duplicate:
    |
    | mens-black-leather-jacket-2.webp
    |
    |--------------------------------------------------------------------------
    */

    private function storeSeoImage(
        UploadedFile $file,
        string $directory
    ): string {
        if (!$file->isValid()) {
            throw ValidationException::withMessages([
                'image' =>
                'The uploaded image is invalid.',
            ]);
        }

        $originalName =
            pathinfo(
                $file->getClientOriginalName(),
                PATHINFO_FILENAME
            );

        $baseName =
            Str::slug(
                trim($originalName)
            );

        if ($baseName === '') {
            $baseName = 'image';
        }

        /*
        |--------------------------------------------------------------------------
        | Keep filenames manageable
        |--------------------------------------------------------------------------
        */

        $baseName =
            Str::limit(
                $baseName,
                150,
                ''
            );

        /*
        |--------------------------------------------------------------------------
        | Use validated MIME extension where possible
        |--------------------------------------------------------------------------
        */

        $extension =
            strtolower(
                $file->extension()
                    ?: $file
                    ->getClientOriginalExtension()
            );

        if (
            !in_array(
                $extension,
                [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'image' =>
                'Only JPG, JPEG, PNG and WebP images are allowed.',
            ]);
        }

        $directory =
            trim(
                str_replace(
                    '\\',
                    '/',
                    $directory
                ),
                '/'
            );

        $fileName =
            $baseName
            . '.'
            . $extension;

        $counter = 2;

        while (
            Storage::disk('public')
            ->exists(
                $directory
                    . '/'
                    . $fileName
            )
        ) {
            $fileName =
                $baseName
                . '-'
                . $counter
                . '.'
                . $extension;

            $counter++;
        }

        return $file->storeAs(
            $directory,
            $fileName,
            'public'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Product Reference Protection
    |--------------------------------------------------------------------------
    */

    private function productHasReferences(
        Product $product
    ): bool {
        $productId =
            (int) $product->id;

        /*
        |--------------------------------------------------------------------------
        | Direct product references
        |--------------------------------------------------------------------------
        */

        $directReferences = [
            [
                'table' =>
                'order_items',

                'column' =>
                'product_id',
            ],

            [
                'table' =>
                'inventory_histories',

                'column' =>
                'product_id',
            ],

            [
                'table' =>
                'supplier_products',

                'column' =>
                'product_id',
            ],

            [
                'table' =>
                'purchase_order_items',

                'column' =>
                'product_id',
            ],
        ];

        foreach (
            $directReferences
            as $reference
        ) {
            if (
                $this->tableHasReference(
                    table: $reference['table'],
                    column: $reference['column'],
                    value: $productId
                )
            ) {
                return true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Variant references
        |--------------------------------------------------------------------------
        */

        $variantIds =
            $product
            ->variants()
            ->pluck('id');

        foreach (
            $variantIds
            as $variantId
        ) {
            if (
                $this->variantHasReferences(
                    (int) $variantId
                )
            ) {
                return true;
            }
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Variant Reference Protection
    |--------------------------------------------------------------------------
    */

    private function variantHasReferences(
        int $variantId
    ): bool {
        $references = [
            [
                'table' =>
                'order_items',

                'column' =>
                'variant_id',
            ],

            [
                'table' =>
                'inventory_histories',

                'column' =>
                'product_variant_id',
            ],

            [
                'table' =>
                'purchase_order_items',

                'column' =>
                'product_variant_id',
            ],

            [
                'table' =>
                'supplier_products',

                'column' =>
                'product_variant_id',
            ],

            [
                'table' =>
                'purchase_order_receipt_items',

                'column' =>
                'product_variant_id',
            ],

            [
                'table' =>
                'supplier_return_items',

                'column' =>
                'product_variant_id',
            ],

            [
                'table' =>
                'inventory_alerts',

                'column' =>
                'product_variant_id',
            ],
        ];

        foreach (
            $references
            as $reference
        ) {
            if (
                $this->tableHasReference(
                    table: $reference['table'],
                    column: $reference['column'],
                    value: $variantId
                )
            ) {
                return true;
            }
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Safe Dynamic Reference Check
    |--------------------------------------------------------------------------
    |
    | Prevents controller errors when an optional module/table is not
    | installed yet.
    |
    */

    private function tableHasReference(
        string $table,
        string $column,
        int $value
    ): bool {
        if (
            !Schema::hasTable($table)
            || !Schema::hasColumn(
                $table,
                $column
            )
        ) {
            return false;
        }

        return DB::table($table)
            ->where(
                $column,
                $value
            )
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Physical Files
    |--------------------------------------------------------------------------
    */

    private function deleteFiles(
        array $files
    ): void {
        $files =
            collect($files)
            ->filter(
                fn($file) =>
                is_string($file)
                    && trim($file) !== ''
            )
            ->map(
                fn($file) =>
                ltrim(
                    str_replace(
                        '\\',
                        '/',
                        trim($file)
                    ),
                    '/'
                )
            )
            ->unique()
            ->values()
            ->all();

        if (empty($files)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Do not allow arbitrary external URLs to reach Storage::delete()
        |--------------------------------------------------------------------------
        */

        $files =
            collect($files)
            ->reject(
                fn(string $file) =>
                Str::startsWith(
                    $file,
                    [
                        'http://',
                        'https://',
                        '//',
                        'data:',
                    ]
                )
            )
            ->values()
            ->all();

        if (!empty($files)) {
            Storage::disk('public')
                ->delete($files);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Safe Rollback
    |--------------------------------------------------------------------------
    */

    private function safeRollback(): void
    {
        if (
            DB::transactionLevel() > 0
        ) {
            DB::rollBack();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Value Helpers
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Unique Product Slug
    |--------------------------------------------------------------------------
    */

    private function makeUniqueProductSlug(
        string $source,
        ?int $ignoreProductId = null
    ): string {
        $base = Str::slug($source);

        if ($base === '') {
            $base = 'product';
        }

        $slug = $base;
        $counter = 2;

        while (
            Product::query()
                ->when(
                    $ignoreProductId,
                    fn ($query) => $query->whereKeyNot($ignoreProductId)
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }


    private function nullableString(
        mixed $value
    ): ?string {
        if (
            $value === null
            || !is_scalar($value)
        ) {
            return null;
        }

        $value = trim(
            (string) $value
        );

        return $value === ''
            ? null
            : $value;
    }


    private function nullableInteger(
        mixed $value
    ): ?int {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        return (int) $value;
    }


    private function nullableNumber(
        mixed $value
    ): int|float|null {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        return (float) $value;
    }
}
