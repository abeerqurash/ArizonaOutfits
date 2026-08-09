<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends AdminController
{
    /**
     * Display the supplier listing.
     */
    public function index(
        Request $request
    ): View {
        $search = trim(
            $request->string('search')->value()
        );

        $status = trim(
            $request->string('status')->value()
        );

        $country = trim(
            $request->string('country')->value()
        );

        $preferred = trim(
            $request->string('preferred')->value()
        );

        $sort = trim(
            $request->string('sort')->value()
        );

        $query = Supplier::query()
            ->withCount([
                'contacts',
                'documents',
                'ratings',
                'purchaseOrders',
            ])
            ->withAvg(
                'ratings as average_rating',
                'overall_rating'
            )
            ->withSum(
                [
                    'purchaseOrders as total_spend' =>
                        function ($purchaseOrderQuery): void {
                            $purchaseOrderQuery->where(
                                'status',
                                '!=',
                                'cancelled'
                            );
                        },
                ],
                'total_amount'
            );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(
                function ($searchQuery) use (
                    $search
                ): void {
                    $searchQuery
                        ->where(
                            'company_name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'supplier_code',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'contact_person',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'email',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'phone',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'country',
                            'like',
                            '%' . $search . '%'
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [
            Supplier::STATUS_ACTIVE,
            Supplier::STATUS_INACTIVE,
            Supplier::STATUS_BLOCKED,
        ];

        if (
            $status !== ''
            && in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            $query->where(
                'status',
                $status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Country
        |--------------------------------------------------------------------------
        */

        if ($country !== '') {
            $query->where(
                'country',
                $country
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Preferred supplier
        |--------------------------------------------------------------------------
        */

        if ($preferred === 'yes') {
            $query->where(
                'is_preferred',
                true
            );
        }

        if ($preferred === 'no') {
            $query->where(
                'is_preferred',
                false
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        match ($sort) {
            'oldest' =>
                $query->oldest(),

            'company-asc' =>
                $query->orderBy(
                    'company_name'
                ),

            'company-desc' =>
                $query->orderByDesc(
                    'company_name'
                ),

            'highest-spend' =>
                $query->orderByDesc(
                    'total_spend'
                ),

            'highest-rating' =>
                $query->orderByDesc(
                    'average_rating'
                ),

            default =>
                $query->latest(),
        };

        $suppliers = $query
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Filter countries
        |--------------------------------------------------------------------------
        */

        $countries = Supplier::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        /*
        |--------------------------------------------------------------------------
        | Dashboard statistics
        |--------------------------------------------------------------------------
        */

        $totalSuppliers = Supplier::query()
            ->count();

        $activeSuppliers = Supplier::query()
            ->where(
                'status',
                Supplier::STATUS_ACTIVE
            )
            ->count();

        $inactiveSuppliers = Supplier::query()
            ->where(
                'status',
                Supplier::STATUS_INACTIVE
            )
            ->count();

        $blockedSuppliers = Supplier::query()
            ->where(
                'status',
                Supplier::STATUS_BLOCKED
            )
            ->count();

        $preferredSuppliers = Supplier::query()
            ->where(
                'is_preferred',
                true
            )
            ->count();

        $totalSupplierSpend = (float) Supplier::query()
            ->join(
                'purchase_orders',
                'suppliers.id',
                '=',
                'purchase_orders.supplier_id'
            )
            ->where(
                'purchase_orders.status',
                '!=',
                'cancelled'
            )
            ->sum(
                'purchase_orders.total_amount'
            );

        return view(
            'admin.suppliers.index',
            compact(
                'suppliers',
                'countries',
                'totalSuppliers',
                'activeSuppliers',
                'inactiveSuppliers',
                'blockedSuppliers',
                'preferredSuppliers',
                'totalSupplierSpend'
            )
        );
    }

    /**
     * Show the create supplier form.
     */
    public function create(): View
    {
        return view(
            'admin.suppliers.create',
            [
                'paymentTerms' =>
                    $this->paymentTerms(),

                'currencies' =>
                    $this->currencies(),

                'statuses' =>
                    $this->statuses(),
            ]
        );
    }

    /**
     * Store a new supplier.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $this->validateSupplier(
            $request
        );

        $validated['slug'] =
            Supplier::generateUniqueSlug(
                $validated['company_name']
            );

        $validated['created_by'] =
            Auth::id();

        $validated['is_preferred'] =
            $request->boolean(
                'is_preferred'
            );

        $supplier = Supplier::create(
            $validated
        );

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Supplier '
                    . $supplier->company_name
                    . ' was created successfully.'
            );
    }

    /**
     * Display one supplier.
     */
    public function show(
        Supplier $supplier
    ): View {
        $supplier->load([
            'primaryContact',
            'contacts',
            'documents.uploader',
            'ratings.ratedBy',
            'ratings.purchaseOrder',
            'purchaseOrderDeliveries.sentBy',
            'purchaseOrderDeliveries.purchaseOrder',
            'purchaseOrders' =>
                function ($query): void {
                    $query
                        ->withCount('items')
                        ->latest();
                },
            'creator',
        ]);

        $totalPurchaseOrders =
            $supplier->purchaseOrders->count();

        $openPurchaseOrders =
            $supplier->purchaseOrders
                ->whereIn(
                    'status',
                    [
                        'draft',
                        'ordered',
                        'partially_received',
                    ]
                )
                ->count();

        $receivedPurchaseOrders =
            $supplier->purchaseOrders
                ->where(
                    'status',
                    'received'
                )
                ->count();

        $totalSpend = (float) $supplier
            ->purchaseOrders
            ->where(
                'status',
                '!=',
                'cancelled'
            )
            ->sum('total_amount');

        $averageOrderValue =
            $totalPurchaseOrders > 0
                ? round(
                    $totalSpend
                    / max(
                        1,
                        $supplier
                            ->purchaseOrders
                            ->where(
                                'status',
                                '!=',
                                'cancelled'
                            )
                            ->count()
                    ),
                    2
                )
                : 0;

        $averageRating = round(
            (float) $supplier
                ->ratings
                ->avg('overall_rating'),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Supplier analytics
        |--------------------------------------------------------------------------
        */

        $nonCancelledPurchaseOrders =
            $supplier->purchaseOrders
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                );

        $cancelledPurchaseOrders =
            $supplier->purchaseOrders
                ->where(
                    'status',
                    'cancelled'
                )
                ->count();

        $receivedRate =
            $totalPurchaseOrders > 0
                ? round(
                    $receivedPurchaseOrders
                    / $totalPurchaseOrders
                    * 100,
                    1
                )
                : 0;

        $cancellationRate =
            $totalPurchaseOrders > 0
                ? round(
                    $cancelledPurchaseOrders
                    / $totalPurchaseOrders
                    * 100,
                    1
                )
                : 0;

        $recommendationRate =
            $supplier->ratings->isNotEmpty()
                ? round(
                    $supplier->ratings
                        ->where(
                            'would_recommend',
                            true
                        )
                        ->count()
                    / $supplier->ratings->count()
                    * 100,
                    1
                )
                : 0;

        $expiredDocuments =
            $supplier->documents
                ->filter(
                    function ($document): bool {
                        if (!$document->expires_at) {
                            return false;
                        }

                        return Carbon::parse(
                            $document->expires_at
                        )->isPast();
                    }
                )
                ->count();

        $expiringDocuments =
            $supplier->documents
                ->filter(
                    function ($document): bool {
                        if (!$document->expires_at) {
                            return false;
                        }

                        $expiryDate = Carbon::parse(
                            $document->expires_at
                        );

                        return $expiryDate->isFuture()
                            && $expiryDate->lte(
                                now()->addDays(30)
                            );
                    }
                )
                ->count();

        $documentComplianceRate =
            $supplier->documents->isNotEmpty()
                ? round(
                    (
                        $supplier->documents->count()
                        - $expiredDocuments
                    )
                    / $supplier->documents->count()
                    * 100,
                    1
                )
                : 0;

        $monthlySupplierSpend = collect(
            range(11, 0)
        )->map(
            function (int $monthOffset) use (
                $nonCancelledPurchaseOrders
            ): array {
                $month = now()
                    ->startOfMonth()
                    ->subMonths($monthOffset);

                $monthKey = $month->format('Y-m');

                $ordersForMonth =
                    $nonCancelledPurchaseOrders
                        ->filter(
                            function ($purchaseOrder) use (
                                $monthKey
                            ): bool {
                                $orderDate =
                                    $purchaseOrder->order_date
                                    ?? $purchaseOrder->created_at;

                                if (!$orderDate) {
                                    return false;
                                }

                                return Carbon::parse(
                                    $orderDate
                                )->format('Y-m')
                                    === $monthKey;
                            }
                        );

                return [
                    'label' => $month->format('M'),
                    'full_label' =>
                        $month->format('F Y'),
                    'spend' => round(
                        (float) $ordersForMonth
                            ->sum('total_amount'),
                        2
                    ),
                    'orders' =>
                        $ordersForMonth->count(),
                ];
            }
        );

        $maxMonthlySupplierSpend = max(
            1,
            (float) $monthlySupplierSpend
                ->max('spend')
        );

        $purchaseOrderStatusAnalytics = collect([
            'draft' => [
                'label' => 'Draft',
                'color' => '#64748b',
            ],
            'ordered' => [
                'label' => 'Ordered',
                'color' => '#2563eb',
            ],
            'partially_received' => [
                'label' => 'Partially Received',
                'color' => '#d97706',
            ],
            'received' => [
                'label' => 'Received',
                'color' => '#059669',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'color' => '#dc2626',
            ],
        ])->map(
            function (
                array $statusData,
                string $status
            ) use (
                $supplier,
                $totalPurchaseOrders
            ): array {
                $count = $supplier
                    ->purchaseOrders
                    ->where(
                        'status',
                        $status
                    )
                    ->count();

                return [
                    ...$statusData,
                    'status' => $status,
                    'count' => $count,
                    'percentage' =>
                        $totalPurchaseOrders > 0
                            ? round(
                                $count
                                / $totalPurchaseOrders
                                * 100,
                                1
                            )
                            : 0,
                ];
            }
        )->values();

        $ratingCategoryAnalytics = collect([
            'Quality' => 'quality_rating',
            'Delivery' => 'delivery_rating',
            'Communication' =>
                'communication_rating',
            'Pricing & Value' => 'pricing_rating',
        ])->map(
            function (string $column) use (
                $supplier
            ): array {
                $score = round(
                    (float) $supplier
                        ->ratings
                        ->avg($column),
                    2
                );

                return [
                    'score' => $score,
                    'percentage' =>
                        round($score * 20, 1),
                ];
            }
        );

        $latestPurchaseOrder =
            $supplier->purchaseOrders
                ->sortByDesc(
                    fn ($purchaseOrder): string =>
                        (string) (
                            $purchaseOrder->order_date
                            ?? $purchaseOrder->created_at
                        )
                )
                ->first();

        $latestOrderDate = $latestPurchaseOrder
            ? Carbon::parse(
                $latestPurchaseOrder->order_date
                ?? $latestPurchaseOrder->created_at
            )
            : null;

        $supplierAnalytics = [
            'received_rate' => $receivedRate,
            'cancellation_rate' =>
                $cancellationRate,
            'recommendation_rate' =>
                $recommendationRate,
            'document_compliance_rate' =>
                $documentComplianceRate,
            'expired_documents' =>
                $expiredDocuments,
            'expiring_documents' =>
                $expiringDocuments,
            'total_items_ordered' =>
                (int) $supplier
                    ->purchaseOrders
                    ->sum('items_count'),
            'days_since_last_order' =>
                $latestOrderDate
                    ? (int) $latestOrderDate
                        ->diffInDays(now())
                    : null,
            'latest_order_reference' =>
                $latestPurchaseOrder?->reference,
        ];

        return view(
            'admin.suppliers.show',
            compact(
                'supplier',
                'totalPurchaseOrders',
                'openPurchaseOrders',
                'receivedPurchaseOrders',
                'totalSpend',
                'averageOrderValue',
                'averageRating',
                'monthlySupplierSpend',
                'maxMonthlySupplierSpend',
                'purchaseOrderStatusAnalytics',
                'ratingCategoryAnalytics',
                'supplierAnalytics'
            )
        );
    }

    /**
     * Show the edit supplier form.
     */
    public function edit(
        Supplier $supplier
    ): View {
        return view(
            'admin.suppliers.edit',
            [
                'supplier' => $supplier,

                'paymentTerms' =>
                    $this->paymentTerms(),

                'currencies' =>
                    $this->currencies(),

                'statuses' =>
                    $this->statuses(),
            ]
        );
    }

    /**
     * Update a supplier.
     */
    public function update(
        Request $request,
        Supplier $supplier
    ): RedirectResponse {
        $validated = $this->validateSupplier(
            $request,
            $supplier
        );

        $validated['is_preferred'] =
            $request->boolean(
                'is_preferred'
            );

        if (
            $supplier->company_name
            !== $validated['company_name']
        ) {
            $validated['slug'] =
                Supplier::generateUniqueSlug(
                    $validated['company_name'],
                    $supplier->id
                );
        }

        $supplier->update(
            $validated
        );

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Supplier '
                    . $supplier->company_name
                    . ' was updated successfully.'
            );
    }

    /**
     * Soft-delete a supplier.
     */
    public function destroy(
        Supplier $supplier
    ): RedirectResponse {
        $openOrders = $supplier
            ->purchaseOrders()
            ->whereIn(
                'status',
                [
                    'draft',
                    'ordered',
                    'partially_received',
                ]
            )
            ->count();

        if ($openOrders > 0) {
            return redirect()
                ->route(
                    'admin.suppliers.show',
                    $supplier
                )
                ->with(
                    'error',
                    'This supplier cannot be deleted while it has open purchase orders.'
                );
        }

        $supplierName =
            $supplier->company_name;

        $supplier->delete();

        return redirect()
            ->route(
                'admin.suppliers.index'
            )
            ->with(
                'success',
                'Supplier '
                    . $supplierName
                    . ' was deleted successfully.'
            );
    }

    /**
     * Validate supplier input.
     */
    private function validateSupplier(
        Request $request,
        ?Supplier $supplier = null
    ): array {
        return $request->validate(
            [
                'company_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'supplier_code' => [
                    'nullable',
                    'string',
                    'max:60',

                    Rule::unique(
                        'suppliers',
                        'supplier_code'
                    )->ignore(
                        $supplier?->id
                    ),
                ],

                'contact_person' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'alternate_phone' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'website' => [
                    'nullable',
                    'url',
                    'max:255',
                ],

                'address_line_1' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'address_line_2' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'city' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'state' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'postal_code' => [
                    'nullable',
                    'string',
                    'max:40',
                ],

                'country' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'currency' => [
                    'required',
                    'string',
                    'size:3',
                ],

                'payment_terms' => [
                    'nullable',
                    'string',
                    'max:60',
                ],

                'lead_time_days' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:3650',
                ],

                'credit_limit' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999999999.99',
                ],

                'tax_number' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'registration_number' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'bank_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'account_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'account_number' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'sort_code' => [
                    'nullable',
                    'string',
                    'max:60',
                ],

                'iban' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'swift_code' => [
                    'nullable',
                    'string',
                    'max:60',
                ],

                'status' => [
                    'required',

                    Rule::in(
                        array_keys(
                            $this->statuses()
                        )
                    ),
                ],

                'is_preferred' => [
                    'nullable',
                    'boolean',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'internal_notes' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ]
        );
    }

    /**
     * Available supplier statuses.
     */
    private function statuses(): array
    {
        return [
            Supplier::STATUS_ACTIVE =>
                'Active',

            Supplier::STATUS_INACTIVE =>
                'Inactive',

            Supplier::STATUS_BLOCKED =>
                'Blocked',
        ];
    }

    /**
     * Available payment terms.
     */
    private function paymentTerms(): array
    {
        return [
            'COD' => 'Cash on Delivery',
            'Advance' => 'Advance Payment',
            'Net 7' => 'Net 7 Days',
            'Net 15' => 'Net 15 Days',
            'Net 30' => 'Net 30 Days',
            'Net 45' => 'Net 45 Days',
            'Net 60' => 'Net 60 Days',
            'Custom' => 'Custom Terms',
        ];
    }

    /**
     * Available currencies.
     */
    private function currencies(): array
    {
        return [
            'GBP' => 'GBP — British Pound',
            'USD' => 'USD — US Dollar',
            'EUR' => 'EUR — Euro',
            'PKR' => 'PKR — Pakistani Rupee',
            'AED' => 'AED — UAE Dirham',
            'CNY' => 'CNY — Chinese Yuan',
        ];
    }
}
