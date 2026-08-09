<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\InventoryHistory;
use App\Models\PurchaseOrderItem;
use App\Exports\PurchaseOrderExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PurchaseOrderController extends AdminController
{
    /**
     * Display the purchase-order form using selected reorder items.
     */
    /**
     * Redirect administrators to the Reorder Dashboard because a purchase
     * order must be created from selected inventory items.
     */
    public function create(): RedirectResponse
    {
        return redirect()
            ->route('admin.reorder-dashboard.index')
            ->with(
                'error',
                'Select one or more reorder items before creating a purchase order.'
            );
    }
    public function createFromReorder(
        Request $request
    ): View|RedirectResponse {
        $validated = $request->validate(
            [
                'selected_items' => [
                    'required',
                    'array',
                    'min:1',
                    'max:500',
                ],

                'selected_items.*' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^(product|variant):[1-9][0-9]*$/',
                ],
            ],
            [
                'selected_items.required' =>
                'Please select at least one inventory item.',

                'selected_items.min' =>
                'Please select at least one inventory item.',

                'selected_items.*.regex' =>
                'One of the selected inventory items is invalid.',
                'supplier_id.required' =>
                'Please select a supplier.',

                'supplier_id.exists' =>
                'The selected supplier could not be found.',
            ]
        );

        $selectedItems = $this->buildSelectedItems(
            $validated['selected_items']
        );

        if ($selectedItems->isEmpty()) {
            return redirect()
                ->route('admin.reorder-dashboard.index')
                ->with(
                    'error',
                    'The selected products or variants could not be found.'
                );
        }

        $subtotal = round(
            (float) $selectedItems->sum('line_total'),
            2
        );

        $totalQuantity = (int) $selectedItems
            ->sum('quantity');

        $suppliers = Supplier::query()
            ->where(
                'status',
                Supplier::STATUS_ACTIVE
            )
            ->orderByDesc('is_preferred')
            ->orderBy('company_name')
            ->get([
                'id',
                'company_name',
                'supplier_code',
                'contact_person',
                'email',
                'phone',
                'alternate_phone',
                'address_line_1',
                'address_line_2',
                'city',
                'state',
                'postal_code',
                'country',
                'currency',
                'payment_terms',
                'lead_time_days',
                'is_preferred',
            ]);

        return view(
            'admin.purchase-orders.create',
            compact(
                'selectedItems',
                'subtotal',
                'totalQuantity',
                'suppliers'
            )
        );
    }



    /**
     * Display the purchase-orders dashboard.
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

        $sort = trim(
            $request->string('sort')->value()
        );

        $query = PurchaseOrder::query()
            ->withCount('items')
            ->withSum(
                'items as ordered_quantity',
                'quantity_ordered'
            )
            ->withSum(
                'items as received_quantity',
                'quantity_received'
            )
            ->with('creator');

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
                            'reference',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'supplier_name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'supplier_email',
                            'like',
                            '%' . $search . '%'
                        );
                }
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Status filter
    |--------------------------------------------------------------------------
    */

        $allowedStatuses = [
            PurchaseOrder::STATUS_DRAFT,
            PurchaseOrder::STATUS_ORDERED,
            PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
            PurchaseOrder::STATUS_RECEIVED,
            PurchaseOrder::STATUS_CANCELLED,
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
    | Sorting
    |--------------------------------------------------------------------------
    */

        match ($sort) {
            'oldest' =>
            $query->oldest(),

            'highest-total' =>
            $query->orderByDesc(
                'total_amount'
            ),

            'lowest-total' =>
            $query->orderBy(
                'total_amount'
            ),

            'expected-date' =>
            $query
                ->orderByRaw(
                    'expected_date IS NULL'
                )
                ->orderBy(
                    'expected_date'
                ),

            default =>
            $query->latest(),
        };

        $purchaseOrders = $query
            ->paginate(15)
            ->withQueryString();

        /*
    |--------------------------------------------------------------------------
    | Dashboard statistics
    |--------------------------------------------------------------------------
    */

        $totalPurchaseOrders =
            PurchaseOrder::query()->count();

        $draftCount = PurchaseOrder::query()
            ->where(
                'status',
                PurchaseOrder::STATUS_DRAFT
            )
            ->count();

        $orderedCount = PurchaseOrder::query()
            ->where(
                'status',
                PurchaseOrder::STATUS_ORDERED
            )
            ->count();

        $partiallyReceivedCount =
            PurchaseOrder::query()
            ->where(
                'status',
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED
            )
            ->count();

        $receivedCount = PurchaseOrder::query()
            ->where(
                'status',
                PurchaseOrder::STATUS_RECEIVED
            )
            ->count();

        $cancelledCount = PurchaseOrder::query()
            ->where(
                'status',
                PurchaseOrder::STATUS_CANCELLED
            )
            ->count();

        $openOrderValue = (float) PurchaseOrder::query()
            ->whereIn(
                'status',
                [
                    PurchaseOrder::STATUS_DRAFT,
                    PurchaseOrder::STATUS_ORDERED,
                    PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                ]
            )
            ->sum('total_amount');

        $totalPurchaseValue = (float) PurchaseOrder::query()
            ->where(
                'status',
                '!=',
                PurchaseOrder::STATUS_CANCELLED
            )
            ->sum('total_amount');

        return view(
            'admin.purchase-orders.index',
            compact(
                'purchaseOrders',
                'totalPurchaseOrders',
                'draftCount',
                'orderedCount',
                'partiallyReceivedCount',
                'receivedCount',
                'cancelledCount',
                'openOrderValue',
                'totalPurchaseValue'
            )
        );
    }
    /**
     * Store a new purchase order and its items.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'supplier_id' => [
                    'required',
                    'integer',
                    Rule::exists(
                        'suppliers',
                        'id'
                    )->where(
                        fn($query) =>
                        $query->whereNull('deleted_at')
                    ),
                ],

                'supplier_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'supplier_email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'supplier_phone' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'supplier_address' => [
                    'nullable',
                    'string',
                    'max:3000',
                ],

                'order_date' => [
                    'required',
                    'date',
                ],

                'expected_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:order_date',
                ],

                'tax_amount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999999999.99',
                ],

                'shipping_amount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999999999.99',
                ],

                'discount_amount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999999999.99',
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

                'items' => [
                    'required',
                    'array',
                    'min:1',
                    'max:500',
                ],

                'items.*.identifier' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^(product|variant):[1-9][0-9]*$/',
                ],

                'items.*.quantity' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:1000000',
                ],

                'items.*.unit_cost' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999999999.99',
                ],
            ],
            [
                'items.required' =>
                'At least one purchase-order item is required.',

                'items.min' =>
                'At least one purchase-order item is required.',

                'items.*.identifier.regex' =>
                'One of the selected purchase-order items is invalid.',

                'items.*.quantity.min' =>
                'Every purchase-order quantity must be at least 1.',
            ]
        );

        $supplier = Supplier::query()
            ->whereKey(
                (int) $validated['supplier_id']
            )
            ->first();

        if (!$supplier) {
            throw ValidationException::withMessages([
                'supplier_id' =>
                'The selected supplier could not be found.',
            ]);
        }

        if ($supplier->isBlocked()) {
            throw ValidationException::withMessages([
                'supplier_id' =>
                'Purchase orders cannot be created for a blocked supplier.',
            ]);
        }

        if ($supplier->isInactive()) {
            throw ValidationException::withMessages([
                'supplier_id' =>
                'Purchase orders cannot be created for an inactive supplier.',
            ]);
        }

        $selectedIdentifiers = collect(
            $validated['items']
        )
            ->pluck('identifier')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $verifiedItems = $this->buildSelectedItems(
            $selectedIdentifiers
        )->keyBy('identifier');

        if ($verifiedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' =>
                'The selected products or variants could not be found.',
            ]);
        }

        $purchaseOrder = DB::transaction(
            function () use (
                $validated,
                $verifiedItems,
                $supplier
            ): PurchaseOrder {
                $purchaseOrder = PurchaseOrder::create([
                    'supplier_id' =>
                    $supplier->id,

                    'supplier_name' =>
                    $supplier->company_name,

                    'supplier_email' =>
                    $supplier->email,

                    'supplier_phone' =>
                    $supplier->phone
                        ?: $supplier->alternate_phone,

                    'supplier_address' =>
                    $supplier->full_address !== ''
                        ? $supplier->full_address
                        : null,

                    'status' =>
                    PurchaseOrder::STATUS_DRAFT,

                    'order_date' =>
                    $validated['order_date'],

                    'expected_date' =>
                    $validated['expected_date'] ?? null,

                    'tax_amount' =>
                    round(
                        (float) (
                            $validated['tax_amount']
                            ?? 0
                        ),
                        2
                    ),

                    'shipping_amount' =>
                    round(
                        (float) (
                            $validated['shipping_amount']
                            ?? 0
                        ),
                        2
                    ),

                    'discount_amount' =>
                    round(
                        (float) (
                            $validated['discount_amount']
                            ?? 0
                        ),
                        2
                    ),

                    'currency' =>
                    $supplier->currency
                        ?: 'GBP',

                    'notes' =>
                    $validated['notes'] ?? null,

                    'internal_notes' =>
                    $validated['internal_notes'] ?? null,

                    'created_by' =>
                    Auth::id(),
                ]);

                foreach (
                    $validated['items']
                    as $submittedItem
                ) {
                    $identifier =
                        $submittedItem['identifier'];

                    $verifiedItem =
                        $verifiedItems->get(
                            $identifier
                        );

                    if (!$verifiedItem) {
                        continue;
                    }

                    $quantity = max(
                        1,
                        (int) $submittedItem['quantity']
                    );

                    $unitCost = max(
                        0,
                        round(
                            (float) $submittedItem['unit_cost'],
                            2
                        )
                    );

                    $purchaseOrder->items()->create([
                        'product_id' =>
                        $verifiedItem['product_id'],

                        'product_variant_id' =>
                        $verifiedItem['product_variant_id'],

                        'item_name' =>
                        $verifiedItem['item_name'],

                        'sku' =>
                        $verifiedItem['sku'],

                        'variant_name' =>
                        $verifiedItem['variant_name'],

                        'quantity_ordered' =>
                        $quantity,

                        'quantity_received' =>
                        0,

                        'unit_cost' =>
                        $unitCost,

                        'line_total' =>
                        round(
                            $quantity * $unitCost,
                            2
                        ),

                        'stock_before' =>
                        $verifiedItem['stock'],

                        'reorder_point' =>
                        $verifiedItem['reorder_point'],

                        'suggested_quantity' =>
                        $verifiedItem['quantity'],
                    ]);
                }

                if (
                    !$purchaseOrder->items()
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'items' =>
                        'No valid purchase-order items were submitted.',
                    ]);
                }

                $purchaseOrder->recalculateTotals();

                return $purchaseOrder;
            }
        );

        return redirect()
            ->route(
                'admin.purchase-orders.show',
                $purchaseOrder
            )
            ->with(
                'success',
                'Purchase order '
                    . $purchaseOrder->reference
                    . ' was created successfully.'
            );
    }

    /**
     * Display one purchase order.
     */
    public function show(
        PurchaseOrder $purchaseOrder
    ): View {
        $purchaseOrder->load([
            'items.product',
            'items.variant',
            'creator',
        ]);

        $totalOrderedQuantity = (int) $purchaseOrder
            ->items
            ->sum('quantity_ordered');

        $totalReceivedQuantity = (int) $purchaseOrder
            ->items
            ->sum('quantity_received');

        $remainingQuantity = max(
            0,
            $totalOrderedQuantity
                - $totalReceivedQuantity
        );

        $receivingPercentage =
            $totalOrderedQuantity > 0
            ? min(
                100,
                (int) round(
                    (
                        $totalReceivedQuantity
                        / $totalOrderedQuantity
                    ) * 100
                )
            )
            : 0;

        return view(
            'admin.purchase-orders.show',
            compact(
                'purchaseOrder',
                'totalOrderedQuantity',
                'totalReceivedQuantity',
                'remainingQuantity',
                'receivingPercentage'
            )
        );
    }

    /**
     * Download a purchase order as a PDF document.
     */
    public function downloadPdf(
        PurchaseOrder $purchaseOrder
    ): Response {
        $purchaseOrder->load([
            'items.product',
            'items.variant',
            'creator',
        ]);

        $totalOrderedQuantity = (int) $purchaseOrder
            ->items
            ->sum('quantity_ordered');

        $totalReceivedQuantity = (int) $purchaseOrder
            ->items
            ->sum('quantity_received');

        $remainingQuantity = max(
            0,
            $totalOrderedQuantity
                - $totalReceivedQuantity
        );

        $receivingPercentage =
            $totalOrderedQuantity > 0
            ? min(
                100,
                (int) round(
                    (
                        $totalReceivedQuantity
                        / $totalOrderedQuantity
                    ) * 100
                )
            )
            : 0;

        $pdf = Pdf::loadView(
            'admin.purchase-orders.pdf',
            compact(
                'purchaseOrder',
                'totalOrderedQuantity',
                'totalReceivedQuantity',
                'remainingQuantity',
                'receivingPercentage'
            )
        );

        $pdf->setPaper(
            'a4',
            'landscape'
        );

        $fileName =
            strtolower(
                preg_replace(
                    '/[^A-Za-z0-9\-]+/',
                    '-',
                    $purchaseOrder->reference
                )
            )
            . '.pdf';

        return $pdf->download(
            $fileName
        );
    }

    /**
     * Download one purchase order as an Excel document.
     */
    public function downloadExcel(
        PurchaseOrder $purchaseOrder
    ): BinaryFileResponse {
        $purchaseOrder->load([
            'items.product',
            'items.variant',
            'creator',
        ]);

        $safeReference = strtolower(
            preg_replace(
                '/[^A-Za-z0-9\-]+/',
                '-',
                $purchaseOrder->reference
            )
        );

        $fileName =
            $safeReference
            . '.xlsx';

        return Excel::download(
            new PurchaseOrderExport(
                $purchaseOrder
            ),
            $fileName
        );
    }
    /**
     * Mark a draft purchase order as ordered.
     */
    public function markOrdered(
        PurchaseOrder $purchaseOrder
    ): RedirectResponse {
        if ($purchaseOrder->isCancelled()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'error',
                    'A cancelled purchase order cannot be marked as ordered.'
                );
        }

        if ($purchaseOrder->isReceived()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'error',
                    'A fully received purchase order cannot be changed back to ordered.'
                );
        }

        if ($purchaseOrder->isPartiallyReceived()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'error',
                    'This purchase order has already received some inventory.'
                );
        }

        if ($purchaseOrder->isOrdered()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'success',
                    'This purchase order is already marked as ordered.'
                );
        }

        if (!$purchaseOrder->items()->exists()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'error',
                    'This purchase order cannot be ordered because it has no items.'
                );
        }

        DB::transaction(
            function () use (
                $purchaseOrder
            ): void {
                $purchaseOrder->forceFill([
                    'status' =>
                    PurchaseOrder::STATUS_ORDERED,

                    'ordered_at' =>
                    $purchaseOrder->ordered_at
                        ?? now(),

                    'cancelled_at' =>
                    null,
                ])->save();
            }
        );

        return redirect()
            ->route(
                'admin.purchase-orders.show',
                $purchaseOrder
            )
            ->with(
                'success',
                'Purchase order '
                    . $purchaseOrder->reference
                    . ' was marked as ordered.'
            );
    }

    /**
     * Cancel a purchase order that has not received inventory.
     */
    public function cancel(
        PurchaseOrder $purchaseOrder
    ): RedirectResponse {
        if ($purchaseOrder->isCancelled()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'success',
                    'This purchase order is already cancelled.'
                );
        }

        if ($purchaseOrder->isReceived()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'error',
                    'A fully received purchase order cannot be cancelled.'
                );
        }

        if ($purchaseOrder->isPartiallyReceived()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'error',
                    'This purchase order cannot be cancelled because inventory has already been received.'
                );
        }

        $receivedQuantity = (int) $purchaseOrder
            ->items()
            ->sum('quantity_received');

        if ($receivedQuantity > 0) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'error',
                    'This purchase order cannot be cancelled because it contains received inventory.'
                );
        }

        DB::transaction(
            function () use (
                $purchaseOrder
            ): void {
                $purchaseOrder->forceFill([
                    'status' =>
                    PurchaseOrder::STATUS_CANCELLED,

                    'cancelled_at' =>
                    now(),

                    'received_at' =>
                    null,
                ])->save();
            }
        );

        return redirect()
            ->route(
                'admin.purchase-orders.show',
                $purchaseOrder
            )
            ->with(
                'success',
                'Purchase order '
                    . $purchaseOrder->reference
                    . ' was cancelled successfully.'
            );
    }

    /**
     * Receive inventory against a purchase order.
     */
    public function receive(
        Request $request,
        PurchaseOrder $purchaseOrder
    ): RedirectResponse {
        /*
    |--------------------------------------------------------------------------
    | Purchase-order status protection
    |--------------------------------------------------------------------------
    */

        if ($purchaseOrder->isCancelled()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'error',
                    'Inventory cannot be received against a cancelled purchase order.'
                );
        }

        if ($purchaseOrder->isDraft()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'error',
                    'Mark this purchase order as ordered before receiving inventory.'
                );
        }

        if ($purchaseOrder->isReceived()) {
            return redirect()
                ->route(
                    'admin.purchase-orders.show',
                    $purchaseOrder
                )
                ->with(
                    'error',
                    'This purchase order has already been fully received.'
                );
        }

        /*
    |--------------------------------------------------------------------------
    | Request validation
    |--------------------------------------------------------------------------
    */

        $validated = $request->validate(
            [
                'items' => [
                    'required',
                    'array',
                    'min:1',
                    'max:500',
                ],

                'items.*.purchase_order_item_id' => [
                    'required',
                    'integer',
                    'distinct',
                    'exists:purchase_order_items,id',
                ],

                'items.*.quantity_received' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:1000000',
                ],

                'receiving_notes' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ],
            [
                'items.required' =>
                'The purchase order does not contain any receiving items.',

                'items.*.quantity_received.integer' =>
                'Every received quantity must be a whole number.',

                'items.*.quantity_received.min' =>
                'A received quantity cannot be negative.',
            ]
        );

        /*
    |--------------------------------------------------------------------------
    | Verify submitted items belong to this purchase order
    |--------------------------------------------------------------------------
    */

        $submittedItemIds = collect(
            $validated['items']
        )
            ->pluck('purchase_order_item_id')
            ->map(
                fn(mixed $value): int =>
                (int) $value
            )
            ->unique()
            ->values();

        $purchaseOrderItems = $purchaseOrder
            ->items()
            ->whereIn(
                'id',
                $submittedItemIds
            )
            ->get()
            ->keyBy('id');

        if (
            $purchaseOrderItems->count()
            !== $submittedItemIds->count()
        ) {
            throw ValidationException::withMessages([
                'items' =>
                'One or more receiving items do not belong to this purchase order.',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Validate quantities against remaining amounts
    |--------------------------------------------------------------------------
    */

        $totalQuantityToReceive = 0;

        foreach (
            $validated['items']
            as $index => $submittedItem
        ) {
            $purchaseOrderItem = $purchaseOrderItems->get(
                (int) $submittedItem['purchase_order_item_id']
            );

            if (!$purchaseOrderItem) {
                continue;
            }

            $quantityToReceive = max(
                0,
                (int) (
                    $submittedItem['quantity_received']
                    ?? 0
                )
            );

            $remainingQuantity = max(
                0,
                (int) $purchaseOrderItem->quantity_ordered
                    - (int) $purchaseOrderItem->quantity_received
            );

            if (
                $quantityToReceive
                > $remainingQuantity
            ) {
                throw ValidationException::withMessages([
                    'items.' . $index . '.quantity_received' =>
                    'The received quantity for '
                        . $purchaseOrderItem->item_name
                        . ' cannot exceed the remaining quantity of '
                        . number_format($remainingQuantity)
                        . '.',
                ]);
            }

            $totalQuantityToReceive +=
                $quantityToReceive;
        }

        if ($totalQuantityToReceive <= 0) {
            throw ValidationException::withMessages([
                'items' =>
                'Enter a received quantity for at least one purchase-order item.',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Receive stock inside one database transaction
    |--------------------------------------------------------------------------
    */

        DB::transaction(
            function () use (
                $validated,
                $purchaseOrder,
                $purchaseOrderItems
            ): void {
                foreach (
                    $validated['items']
                    as $submittedItem
                ) {
                    $quantityToReceive = max(
                        0,
                        (int) (
                            $submittedItem['quantity_received']
                            ?? 0
                        )
                    );

                    if ($quantityToReceive <= 0) {
                        continue;
                    }

                    /*
                 * Lock the purchase-order item to protect against
                 * simultaneous receiving requests.
                 */
                    $purchaseOrderItem =
                        PurchaseOrderItem::query()
                        ->whereKey(
                            (int) $submittedItem['purchase_order_item_id']
                        )
                        ->where(
                            'purchase_order_id',
                            $purchaseOrder->id
                        )
                        ->lockForUpdate()
                        ->first();

                    if (!$purchaseOrderItem) {
                        throw ValidationException::withMessages([
                            'items' =>
                            'A purchase-order item could not be found.',
                        ]);
                    }

                    $remainingQuantity = max(
                        0,
                        (int) $purchaseOrderItem
                            ->quantity_ordered
                            - (int) $purchaseOrderItem
                                ->quantity_received
                    );

                    if (
                        $quantityToReceive
                        > $remainingQuantity
                    ) {
                        throw ValidationException::withMessages([
                            'items' =>
                            'The received quantity for '
                                . $purchaseOrderItem->item_name
                                . ' is greater than the remaining quantity.',
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Variant inventory
                |--------------------------------------------------------------------------
                */

                    if (
                        $purchaseOrderItem
                        ->product_variant_id
                    ) {
                        $variant = ProductVariant::query()
                            ->whereKey(
                                $purchaseOrderItem
                                    ->product_variant_id
                            )
                            ->lockForUpdate()
                            ->first();

                        if (!$variant) {
                            throw ValidationException::withMessages([
                                'items' =>
                                'The variant for '
                                    . $purchaseOrderItem->item_name
                                    . ' no longer exists.',
                            ]);
                        }

                        $product = Product::query()
                            ->find(
                                $purchaseOrderItem
                                    ->product_id
                            );

                        $stockBefore = max(
                            0,
                            (int) ($variant->stock ?? 0)
                        );

                        $stockAfter =
                            $stockBefore
                            + $quantityToReceive;

                        $variant->forceFill([
                            'stock' => $stockAfter,
                        ])->save();

                        InventoryHistory::create([
                            'product_id' =>
                            $purchaseOrderItem->product_id,

                            'product_variant_id' =>
                            $variant->id,

                            'order_id' =>
                            null,

                            'user_id' =>
                            Auth::id(),

                            'quantity_change' =>
                            $quantityToReceive,

                            'stock_before' =>
                            $stockBefore,

                            'stock_after' =>
                            $stockAfter,

                            'movement_type' =>
                            InventoryHistory::TYPE_RESTOCK,

                            'reason' =>
                            'Purchase order inventory received',

                            'notes' =>
                            $validated['receiving_notes']
                                ?? null,

                            'reference_type' =>
                            PurchaseOrder::class,

                            'reference_id' =>
                            (string) $purchaseOrder->id,

                            'metadata' => [
                                'purchase_order_id' =>
                                $purchaseOrder->id,

                                'purchase_order_reference' =>
                                $purchaseOrder->reference,

                                'purchase_order_item_id' =>
                                $purchaseOrderItem->id,

                                'item_name' =>
                                $purchaseOrderItem->item_name,

                                'sku' =>
                                $purchaseOrderItem->sku,

                                'quantity_received' =>
                                $quantityToReceive,

                                'product_id' =>
                                $purchaseOrderItem->product_id,

                                'product_variant_id' =>
                                $variant->id,

                                'product_title' =>
                                $product?->title,

                                'supplier_name' =>
                                $purchaseOrder->supplier_name,
                            ],
                        ]);
                    } else {
                        /*
                    |--------------------------------------------------------------------------
                    | Simple-product inventory
                    |--------------------------------------------------------------------------
                    */

                        $product = Product::query()
                            ->whereKey(
                                $purchaseOrderItem
                                    ->product_id
                            )
                            ->lockForUpdate()
                            ->first();

                        if (!$product) {
                            throw ValidationException::withMessages([
                                'items' =>
                                'The product for '
                                    . $purchaseOrderItem->item_name
                                    . ' no longer exists.',
                            ]);
                        }

                        $stockBefore = max(
                            0,
                            (int) ($product->stock ?? 0)
                        );

                        $stockAfter =
                            $stockBefore
                            + $quantityToReceive;

                        $product->forceFill([
                            'stock' => $stockAfter,
                        ])->save();

                        InventoryHistory::create([
                            'product_id' =>
                            $product->id,

                            'product_variant_id' =>
                            null,

                            'order_id' =>
                            null,

                            'user_id' =>
                            Auth::id(),

                            'quantity_change' =>
                            $quantityToReceive,

                            'stock_before' =>
                            $stockBefore,

                            'stock_after' =>
                            $stockAfter,

                            'movement_type' =>
                            InventoryHistory::TYPE_RESTOCK,

                            'reason' =>
                            'Purchase order inventory received',

                            'notes' =>
                            $validated['receiving_notes']
                                ?? null,

                            'reference_type' =>
                            PurchaseOrder::class,

                            'reference_id' =>
                            (string) $purchaseOrder->id,

                            'metadata' => [
                                'purchase_order_id' =>
                                $purchaseOrder->id,

                                'purchase_order_reference' =>
                                $purchaseOrder->reference,

                                'purchase_order_item_id' =>
                                $purchaseOrderItem->id,

                                'item_name' =>
                                $purchaseOrderItem->item_name,

                                'sku' =>
                                $purchaseOrderItem->sku,

                                'quantity_received' =>
                                $quantityToReceive,

                                'product_id' =>
                                $product->id,

                                'product_variant_id' =>
                                null,

                                'product_title' =>
                                $product->title,

                                'supplier_name' =>
                                $purchaseOrder->supplier_name,
                            ],
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Update purchase-order item received quantity
                |--------------------------------------------------------------------------
                */

                    $purchaseOrderItem->forceFill([
                        'quantity_received' =>
                        (int) $purchaseOrderItem
                            ->quantity_received
                            + $quantityToReceive,
                    ])->save();
                }

                /*
            |--------------------------------------------------------------------------
            | Refresh purchase-order status
            |--------------------------------------------------------------------------
            */

                $purchaseOrder->refresh();
                $purchaseOrder->refreshReceivingStatus();
            }
        );

        $purchaseOrder->refresh();

        return redirect()
            ->route(
                'admin.purchase-orders.show',
                $purchaseOrder
            )
            ->with(
                'success',
                number_format($totalQuantityToReceive)
                    . ' inventory units were received against '
                    . $purchaseOrder->reference
                    . '.'
            );
    }
    /**
     * Convert selected dashboard identifiers into purchase-order rows.
     */
    private function buildSelectedItems(
        array $selectedIdentifiers
    ): Collection {
        $selectedItems = collect();

        $identifiers = collect($selectedIdentifiers)
            ->filter(
                fn(mixed $value): bool =>
                is_string($value)
            )
            ->unique()
            ->values();

        $productIds = $identifiers
            ->filter(
                fn(string $value): bool =>
                str_starts_with(
                    $value,
                    'product:'
                )
            )
            ->map(
                fn(string $value): int =>
                (int) str_replace(
                    'product:',
                    '',
                    $value
                )
            )
            ->filter()
            ->values();

        $variantIds = $identifiers
            ->filter(
                fn(string $value): bool =>
                str_starts_with(
                    $value,
                    'variant:'
                )
            )
            ->map(
                fn(string $value): int =>
                (int) str_replace(
                    'variant:',
                    '',
                    $value
                )
            )
            ->filter()
            ->values();

        $products = Product::query()
            ->with('categories')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $variants = ProductVariant::query()
            ->with([
                'product.categories',
            ])
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        foreach ($identifiers as $identifier) {
            [$type, $id] = explode(
                ':',
                $identifier,
                2
            );

            $id = (int) $id;

            if ($type === 'product') {
                $product = $products->get($id);

                if (!$product) {
                    continue;
                }

                /*
                 * A parent product with variants should not be used as
                 * an independent purchase-order line.
                 */
                if ($product->variants()->exists()) {
                    continue;
                }

                $selectedItems->push(
                    $this->buildProductItem(
                        $product
                    )
                );

                continue;
            }

            if ($type === 'variant') {
                $variant = $variants->get($id);

                if (
                    !$variant
                    || !$variant->product
                ) {
                    continue;
                }

                $selectedItems->push(
                    $this->buildVariantItem(
                        $variant
                    )
                );
            }
        }

        return $selectedItems->values();
    }

    /**
     * Build one simple-product purchase row.
     */
    private function buildProductItem(
        Product $product
    ): array {
        $stock = max(
            0,
            (int) ($product->stock ?? 0)
        );

        $reorderPoint = max(
            0,
            (int) ($product->reorder_point ?? 5)
        );

        $configuredQuantity = max(
            0,
            (int) ($product->reorder_quantity ?? 0)
        );

        $quantity = $configuredQuantity > 0
            ? $configuredQuantity
            : max(
                1,
                $reorderPoint - $stock
            );

        $unitCost = max(
            0,
            (float) ($product->cost_price ?? 0)
        );

        return [
            'identifier' =>
            'product:' . $product->id,

            'product_id' =>
            (int) $product->id,

            'product_variant_id' =>
            null,

            'item_name' =>
            (string) $product->title,

            'variant_name' =>
            null,

            'sku' =>
            $product->sku,

            'categories' =>
            $product->categories
                ->pluck('title')
                ->filter()
                ->implode(', '),

            'stock' =>
            $stock,

            'reorder_point' =>
            $reorderPoint,

            'quantity' =>
            $quantity,

            'unit_cost' =>
            round($unitCost, 2),

            'line_total' =>
            round(
                $quantity * $unitCost,
                2
            ),
        ];
    }

    /**
     * Build one variant purchase row.
     */
    private function buildVariantItem(
        ProductVariant $variant
    ): array {
        $product = $variant->product;

        $stock = max(
            0,
            (int) ($variant->stock ?? 0)
        );

        $reorderPoint = max(
            0,
            (int) (
                $variant->reorder_point
                ?? 5
            )
        );

        $configuredQuantity = max(
            0,
            (int) (
                $variant->reorder_quantity
                ?? 0
            )
        );

        $quantity = $configuredQuantity > 0
            ? $configuredQuantity
            : max(
                1,
                $reorderPoint - $stock
            );

        /*
         * Variant cost is not currently stored separately,
         * so the parent product cost is used.
         */
        $unitCost = max(
            0,
            (float) ($product->cost_price ?? 0)
        );

        $variantName = $this->variantLabel(
            $variant->options ?? []
        );

        $itemName = (string) $product->title;

        if ($variantName !== null) {
            $itemName .= ' — ' . $variantName;
        }

        return [
            'identifier' =>
            'variant:' . $variant->id,

            'product_id' =>
            (int) $product->id,

            'product_variant_id' =>
            (int) $variant->id,

            'item_name' =>
            $itemName,

            'variant_name' =>
            $variantName,

            'sku' =>
            $variant->sku
                ?: $product->sku,

            'categories' =>
            $product->categories
                ->pluck('title')
                ->filter()
                ->implode(', '),

            'stock' =>
            $stock,

            'reorder_point' =>
            $reorderPoint,

            'quantity' =>
            $quantity,

            'unit_cost' =>
            round($unitCost, 2),

            'line_total' =>
            round(
                $quantity * $unitCost,
                2
            ),
        ];
    }

    /**
     * Convert variant options into readable text.
     */
    private function variantLabel(
        mixed $options
    ): ?string {
        if (!is_array($options)) {
            return null;
        }

        $labels = collect($options)
            ->map(
                function (mixed $option): ?string {
                    if (!is_array($option)) {
                        return null;
                    }

                    $optionName = trim(
                        (string) (
                            $option['option_name']
                            ?? ''
                        )
                    );

                    $valueLabel = trim(
                        (string) (
                            $option['value_label']
                            ?? ''
                        )
                    );

                    if (
                        $optionName === ''
                        && $valueLabel === ''
                    ) {
                        return null;
                    }

                    if ($optionName === '') {
                        return $valueLabel;
                    }

                    if ($valueLabel === '') {
                        return $optionName;
                    }

                    return $optionName
                        . ': '
                        . $valueLabel;
                }
            )
            ->filter()
            ->values();

        return $labels->isEmpty()
            ? null
            : $labels->implode(' / ');
    }
}
