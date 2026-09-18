<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ShopController extends Controller
{
    /**
     * Display the main shop page.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'categories' => [
                'nullable',
                'array',
            ],

            'categories.*' => [
                'nullable',
                'string',
                'max:150',
            ],

            'min_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'ratings' => [
                'nullable',
                'array',
            ],

            'ratings.*' => [
                'nullable',
                'integer',
                'between:1,5',
            ],

            'availability' => [
                'nullable',
                'array',
            ],

            'availability.*' => [
                'nullable',
                'in:in_stock,out_of_stock',
            ],

            'offers' => [
                'nullable',
                'array',
            ],

            'offers.*' => [
                'nullable',
                'in:on_sale',
            ],
            'featured' => [
                'nullable',
                'in:1',
            ],
            /*
            |--------------------------------------------------------------------------
            | Dynamic variant option filters
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | options[1][]=3
            | options[1][]=4
            | options[2][]=7
            |
            */

            'options' => [
                'nullable',
                'array',
            ],

            'options.*' => [
                'nullable',
                'array',
            ],

            'options.*.*' => [
                'nullable',
                'integer',
            ],

            'sort' => [
                'nullable',
                'in:newest,price_low,price_high,popular,rating,best_selling,discount',
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalize multi-select fields
        |--------------------------------------------------------------------------
        */

        $validated['categories'] = array_values(
            array_filter(
                (array) ($validated['categories'] ?? [])
            )
        );

        $validated['ratings'] = array_values(
            array_filter(
                (array) ($validated['ratings'] ?? [])
            )
        );

        $validated['availability'] = array_values(
            array_filter(
                (array) ($validated['availability'] ?? [])
            )
        );

        $validated['offers'] = array_values(
            array_filter(
                (array) ($validated['offers'] ?? [])
            )
        );

        $validated['options'] = $this->normalizeOptionFilters(
            $validated['options'] ?? []
        );

        /*
        |--------------------------------------------------------------------------
        | Product query
        |--------------------------------------------------------------------------
        */

        $query = Product::query()
            ->with($this->productCardRelations())
            ->withCount([
                'approvedReviews as approved_reviews_count',
            ])
            ->withAvg(
                'approvedReviews as approved_reviews_avg_rating',
                'rating'
            )
            ->where('status', 'active');

        $this->applyFilters(
            $query,
            $validated
        );

        $this->applySorting(
            $query,
            $validated['sort'] ?? 'newest'
        );

        $products = $query
            ->paginate(12)
            ->withQueryString();

        $categories = $this->getActiveCategories();

        /*
        |--------------------------------------------------------------------------
        | Variant filters
        |--------------------------------------------------------------------------
        |
        | Loads options such as:
        |
        | Color
        | Size
        | Material
        |
        | The previous whereHas('products') condition could hide all options.
        | We only need options that contain at least one value.
        |
        */

        $filterOptions = ProductOption::query()
            ->with([
                'values' => function ($query) {
                    $query
                        ->orderBy('value');
                },
            ])
            ->whereHas('values')
            ->orderBy('name')
            ->get();

        $priceRange = $this->getAvailablePriceRange();

        return view(
            'products.index',
            compact(
                'products',
                'categories',
                'filterOptions',
                'priceRange'
            )
        );
    }

    /**
     * Display the product quick-view modal.
     */
    public function quickView(Product $product): View
    {
        abort_unless(
            $product->status === 'active',
            404
        );

        $product->load([
            'images',
            'categories',
            'tags',
            'options.values',
            'optionValues',
            'variants',
            'approvedReviews.user',
        ]);

        $product->loadCount([
            'approvedReviews as approved_reviews_count',
        ]);

        $product->loadAvg(
            'approvedReviews as approved_reviews_avg_rating',
            'rating'
        );

        return view(
            'products.partials.quick-view',
            compact('product')
        );
    }

    /**
     * Display a single product.
     */
    public function show(string $slug): View
    {
        $product = Product::query()
            ->with([
                'categories',
                'images',
                'tags',
                'options.values',
                'optionValues',
                'variants',

                'approvedReviews' => function ($query) {
                    $query
                        ->with('user')
                        ->latest();
                },
            ])
            ->withCount([
                'approvedReviews as approved_reviews_count',
            ])
            ->withAvg(
                'approvedReviews as approved_reviews_avg_rating',
                'rating'
            )
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $product->increment('views_count');

        $categoryIds = $product
            ->categories
            ->pluck('id');

        $relatedProducts = Product::query()
            ->with($this->productCardRelations())
            ->withCount([
                'approvedReviews as approved_reviews_count',
            ])
            ->withAvg(
                'approvedReviews as approved_reviews_avg_rating',
                'rating'
            )
            ->where('status', 'active')
            ->whereKeyNot($product->getKey())
            ->when(
                $categoryIds->isNotEmpty(),
                function (
                    Builder $query
                ) use (
                    $categoryIds
                ) {
                    $query->whereHas(
                        'categories',
                        function (
                            Builder $categoryQuery
                        ) use (
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

    /**
     * Redirect category pages to the filtered shop.
     */
    public function category(
        string $slug,
        Request $request
    ): RedirectResponse {
        $category = ProductCategory::query()
            ->where('slug', $slug)
            ->firstOrFail();

        return redirect()->route(
            'products.index',
            array_merge(
                $request->query(),
                [
                    'categories' => [
                        $category->slug,
                    ],
                ]
            )
        );
    }

    /**
     * Redirect sale page to the filtered shop.
     */
    public function sale(
        Request $request
    ): RedirectResponse {
        return redirect()->route(
            'products.index',
            array_merge(
                $request->query(),
                [
                    'offers' => [
                        'on_sale',
                    ],
                ]
            )
        );
    }

    /**
     * Apply all shop filters.
     */
    private function applyFilters(
        Builder $query,
        array $filters
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['search'])) {
            $search = trim(
                $filters['search']
            );

            $query->where(
                function (
                    Builder $searchQuery
                ) use (
                    $search
                ) {
                    $searchQuery
                        ->where(
                            'title',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'sku',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'short_description',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'long_description',
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'variants',
                            function (
                                Builder $variantQuery
                            ) use (
                                $search
                            ) {
                                $variantQuery->where(
                                    'sku',
                                    'LIKE',
                                    "%{$search}%"
                                );
                            }
                        )
                        ->orWhereHas(
                            'categories',
                            function (
                                Builder $categoryQuery
                            ) use (
                                $search
                            ) {
                                $categoryQuery->where(
                                    'product_categories.title',
                                    'LIKE',
                                    "%{$search}%"
                                );
                            }
                        )
                        ->orWhereHas(
                            'tags',
                            function (
                                Builder $tagQuery
                            ) use (
                                $search
                            ) {
                                $tagQuery->where(
                                    'product_tags.title',
                                    'LIKE',
                                    "%{$search}%"
                                );
                            }
                        );
                    /*
|--------------------------------------------------------------------------
| Featured products
|--------------------------------------------------------------------------
*/

                    if (
                        isset($filters['featured'])
                        && (string) $filters['featured'] === '1'
                    ) {
                        $query->where(
                            'is_featured',
                            true
                        );
                    }
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $selectedCategories = array_filter(
            (array) ($filters['categories'] ?? [])
        );

        if (!empty($selectedCategories)) {
            $query->whereHas(
                'categories',
                function (
                    Builder $categoryQuery
                ) use (
                    $selectedCategories
                ) {
                    $categoryQuery->whereIn(
                        'product_categories.slug',
                        $selectedCategories
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum price
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists('min_price', $filters)
            && $filters['min_price'] !== null
            && $filters['min_price'] !== ''
        ) {
            $minimumPrice = (float) $filters['min_price'];

            $query->where(
                function (
                    Builder $priceQuery
                ) use (
                    $minimumPrice
                ) {
                    $priceQuery
                        ->whereRaw(
                            $this->productEffectivePriceSql() . ' >= ?',
                            [$minimumPrice]
                        )
                        ->orWhereHas(
                            'variants',
                            function (
                                Builder $variantQuery
                            ) use (
                                $minimumPrice
                            ) {
                                $variantQuery->whereRaw(
                                    $this->variantEffectivePriceSql() . ' >= ?',
                                    [$minimumPrice]
                                );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum price
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists('max_price', $filters)
            && $filters['max_price'] !== null
            && $filters['max_price'] !== ''
        ) {
            $maximumPrice = (float) $filters['max_price'];

            $query->where(
                function (
                    Builder $priceQuery
                ) use (
                    $maximumPrice
                ) {
                    $priceQuery
                        ->whereRaw(
                            $this->productEffectivePriceSql() . ' <= ?',
                            [$maximumPrice]
                        )
                        ->orWhereHas(
                            'variants',
                            function (
                                Builder $variantQuery
                            ) use (
                                $maximumPrice
                            ) {
                                $variantQuery->whereRaw(
                                    $this->variantEffectivePriceSql() . ' <= ?',
                                    [$maximumPrice]
                                );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Customer rating
        |--------------------------------------------------------------------------
        */

        $selectedRatings = array_map(
            'intval',
            array_filter(
                (array) ($filters['ratings'] ?? [])
            )
        );

        if (!empty($selectedRatings)) {
            $minimumRating = min(
                $selectedRatings
            );

            $query->whereHas(
                'approvedReviews',
                function (
                    Builder $reviewQuery
                ) use (
                    $minimumRating
                ) {
                    $reviewQuery
                        ->selectRaw('product_id')
                        ->groupBy('product_id')
                        ->havingRaw(
                            'AVG(rating) >= ?',
                            [$minimumRating]
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Availability
        |--------------------------------------------------------------------------
        */

        $selectedAvailability = array_filter(
            (array) ($filters['availability'] ?? [])
        );

        $hasInStock = in_array(
            'in_stock',
            $selectedAvailability,
            true
        );

        $hasOutOfStock = in_array(
            'out_of_stock',
            $selectedAvailability,
            true
        );

        if ($hasInStock && !$hasOutOfStock) {
            $query->where(
                function (Builder $stockQuery) {
                    $stockQuery
                        ->where(
                            'stock',
                            '>',
                            0
                        )
                        ->orWhereHas(
                            'variants',
                            function (
                                Builder $variantQuery
                            ) {
                                $variantQuery->where(
                                    'stock',
                                    '>',
                                    0
                                );
                            }
                        );
                }
            );
        }

        if ($hasOutOfStock && !$hasInStock) {
            $query
                ->where(
                    function (Builder $stockQuery) {
                        $stockQuery
                            ->whereNull('stock')
                            ->orWhere(
                                'stock',
                                '<=',
                                0
                            );
                    }
                )
                ->whereDoesntHave(
                    'variants',
                    function (
                        Builder $variantQuery
                    ) {
                        $variantQuery->where(
                            'stock',
                            '>',
                            0
                        );
                    }
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Offers: on sale
        |--------------------------------------------------------------------------
        */

        $selectedOffers = array_filter(
            (array) ($filters['offers'] ?? [])
        );

        if (
            in_array(
                'on_sale',
                $selectedOffers,
                true
            )
        ) {
            $query->where(
                function (
                    Builder $discountQuery
                ) {
                    $discountQuery
                        ->where(
                            function (
                                Builder $productDiscountQuery
                            ) {
                                $productDiscountQuery
                                    ->whereNotNull('sale_price')
                                    ->where(
                                        'sale_price',
                                        '>',
                                        0
                                    )
                                    ->whereColumn(
                                        'sale_price',
                                        '<',
                                        'regular_price'
                                    );
                            }
                        )
                        ->orWhereHas(
                            'variants',
                            function (
                                Builder $variantQuery
                            ) {
                                $variantQuery
                                    ->whereNotNull('sale_price')
                                    ->where(
                                        'sale_price',
                                        '>',
                                        0
                                    )
                                    ->whereColumn(
                                        'sale_price',
                                        '<',
                                        'regular_price'
                                    );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Dynamic variant option filters
        |--------------------------------------------------------------------------
        |
        | Values from the same option use OR logic.
        | Separate options use AND logic.
        |
        | Example:
        |
        | Color = Black OR Blue
        | AND
        | Size = Medium OR Large
        |
        */

        foreach (
            ($filters['options'] ?? [])
            as $optionId => $valueIds
        ) {
            $optionId = (int) $optionId;

            $valueIds = collect($valueIds)
                ->filter()
                ->map(
                    fn($valueId) => (int) $valueId
                )
                ->unique()
                ->values();

            if (
                $optionId < 1
                || $valueIds->isEmpty()
            ) {
                continue;
            }

            $query->whereHas(
                'variants',
                function (
                    Builder $variantQuery
                ) use (
                    $optionId,
                    $valueIds
                ) {
                    $variantQuery->where(
                        function (
                            Builder $valueQuery
                        ) use (
                            $optionId,
                            $valueIds
                        ) {
                            foreach ($valueIds as $valueId) {
                                /*
                                 * Supports JSON stored as:
                                 *
                                 * [
                                 *     {
                                 *         "option_id": 1,
                                 *         "value_id": 2
                                 *     }
                                 * ]
                                 */

                                $valueQuery->orWhere(function (
                                    Builder $jsonQuery
                                ) use (
                                    $optionId,
                                    $valueId
                                ) {
                                    /*
     * Correct format:
     * option_id and value_id stored as JSON numbers.
     */
                                    $jsonQuery->whereJsonContains(
                                        'options',
                                        [
                                            'option_id' => $optionId,
                                            'value_id' => $valueId,
                                        ]
                                    );

                                    /*
     * Backward compatibility:
     * older variants may contain IDs as JSON strings.
     */
                                    $jsonQuery->orWhereJsonContains(
                                        'options',
                                        [
                                            'option_id' => (string) $optionId,
                                            'value_id' => (string) $valueId,
                                        ]
                                    );
                                });
                            }
                        }
                    );
                }
            );
        }
    }

    /**
     * Normalize selected option IDs and value IDs.
     */
    private function normalizeOptionFilters(
        array $options
    ): array {
        $normalizedOptions = [];

        foreach ($options as $optionId => $valueIds) {
            $optionId = (int) $optionId;

            if ($optionId < 1) {
                continue;
            }

            $normalizedValueIds = collect(
                (array) $valueIds
            )
                ->filter(
                    fn($valueId) => is_numeric($valueId)
                )
                ->map(
                    fn($valueId) => (int) $valueId
                )
                ->filter(
                    fn($valueId) => $valueId > 0
                )
                ->unique()
                ->values()
                ->all();

            if (empty($normalizedValueIds)) {
                continue;
            }

            $normalizedOptions[$optionId] =
                $normalizedValueIds;
        }

        return $normalizedOptions;
    }

    /**
     * Apply product sorting.
     */
    private function applySorting(
        Builder $query,
        ?string $sort
    ): void {
        match ($sort) {
            'price_low' => $query
                ->orderByRaw(
                    $this->productEffectivePriceSql() . ' ASC'
                )
                ->orderBy('id'),

            'price_high' => $query
                ->orderByRaw(
                    $this->productEffectivePriceSql() . ' DESC'
                )
                ->orderByDesc('id'),

            'popular' => $query
                ->orderByDesc('views_count')
                ->orderByDesc('purchase_count')
                ->orderByDesc('id'),

            'rating' => $query
                ->orderByDesc(
                    'approved_reviews_avg_rating'
                )
                ->orderByDesc(
                    'approved_reviews_count'
                )
                ->orderByDesc('id'),

            'best_selling' => $query
                ->orderByDesc('purchase_count')
                ->orderByDesc('views_count')
                ->orderByDesc('id'),

            'discount' => $query
                ->orderByRaw(
                    '
                    CASE
                        WHEN regular_price > 0
                            AND sale_price IS NOT NULL
                            AND sale_price > 0
                            AND sale_price < regular_price
                        THEN (
                            (regular_price - sale_price)
                            / regular_price
                        ) * 100
                        ELSE 0
                    END DESC
                    '
                )
                ->orderByDesc('id'),

            default => $query
                ->latest('id'),
        };
    }

    /**
     * Product relations used by product cards.
     */
    private function productCardRelations(): array
    {
        return [
            'categories',
            'images',
            'tags',
            'options.values',
            'optionValues',
            'variants',
        ];
    }

    /**
     * Get active parent and child categories.
     */
    private function getActiveCategories(): Collection
    {
        return ProductCategory::query()

            ->withCount([
                'products as active_products_count' => function (
                    $query
                ) {
                    $query->where(
                        'products.status',
                        'active'
                    );
                },
            ])

            ->with([
                'children' => function ($query) {

                    $query

                        ->withCount([
                            'products as active_products_count'
                            => function ($productQuery) {

                                $productQuery->where(
                                    'products.status',
                                    'active'
                                );
                            },
                        ])

                        ->whereHas(
                            'products',
                            function ($productQuery) {

                                $productQuery->where(
                                    'products.status',
                                    'active'
                                );
                            }
                        )

                        ->orderBy('title');
                },
            ])

            ->whereNull('parent_id')

            ->where(
                function (Builder $query) {

                    $query

                        ->whereHas(
                            'products',
                            function ($productQuery) {

                                $productQuery->where(
                                    'products.status',
                                    'active'
                                );
                            }
                        )

                        ->orWhereHas(
                            'children.products',
                            function ($productQuery) {

                                $productQuery->where(
                                    'products.status',
                                    'active'
                                );
                            }
                        );
                }
            )

            ->orderBy('title')

            ->get();
    }

    /**
     * Get minimum and maximum product prices.
     */
    private function getAvailablePriceRange(): array
    {
        $productPrices = Product::query()
            ->where(
                'status',
                'active'
            )
            ->selectRaw(
                'MIN('
                    . $this->productEffectivePriceSql()
                    . ') as minimum'
            )
            ->selectRaw(
                'MAX('
                    . $this->productEffectivePriceSql()
                    . ') as maximum'
            )
            ->first();

        $variantPrices = ProductVariant::query()
            ->whereHas(
                'product',
                function (
                    Builder $query
                ) {
                    $query->where(
                        'status',
                        'active'
                    );
                }
            )
            ->selectRaw(
                'MIN('
                    . $this->variantEffectivePriceSql()
                    . ') as minimum'
            )
            ->selectRaw(
                'MAX('
                    . $this->variantEffectivePriceSql()
                    . ') as maximum'
            )
            ->first();

        $minimumValues = collect([
            $productPrices?->minimum,
            $variantPrices?->minimum,
        ])->filter(
            fn($value) => $value !== null
        );

        $maximumValues = collect([
            $productPrices?->maximum,
            $variantPrices?->maximum,
        ])->filter(
            fn($value) => $value !== null
        );

        return [
            'min' => $minimumValues->isNotEmpty()
                ? floor(
                    (float) $minimumValues->min()
                )
                : 0,

            'max' => $maximumValues->isNotEmpty()
                ? ceil(
                    (float) $maximumValues->max()
                )
                : 0,
        ];
    }

    /**
     * Effective product price SQL.
     */
    private function productEffectivePriceSql(): string
    {
        return '
            CASE
                WHEN sale_price IS NOT NULL
                    AND sale_price > 0
                    AND sale_price < regular_price
                THEN sale_price
                ELSE regular_price
            END
        ';
    }

    /**
     * Effective variant price SQL.
     */
    private function variantEffectivePriceSql(): string
    {
        return '
            CASE
                WHEN sale_price IS NOT NULL
                    AND sale_price > 0
                    AND sale_price < regular_price
                THEN sale_price
                ELSE regular_price
            END
        ';
    }
}
