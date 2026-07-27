<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\OrderNote;
use App\Services\OrderActivityService;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends AdminController
{
    /**
     * Allowed order statuses.
     */
    private const ORDER_STATUSES = [
        'pending',
        'processing',
        'shipped',
        'completed',
        'delivered',
        'cancelled',
        'refunded',
    ];

    /**
     * Allowed payment statuses.
     */
    private const PAYMENT_STATUSES = [
        'pending',
        'paid',
        'completed',
        'succeeded',
        'failed',
        'declined',
        'cancelled',
        'refunded',
    ];

    /**
     * Display the orders list.
     */
    public function index(Request $request): View|JsonResponse
    {
        $filters = $this->validateFilters($request);

        $orders = $this->buildOrdersQuery($filters)
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,

                'table_html' => view(
                    'admin.orders.partials.table',
                    compact('orders')
                )->render(),

                'pagination_html' => view(
                    'admin.orders.partials.pagination',
                    compact('orders')
                )->render(),

                'results_summary' => $this->buildResultsSummary(
                    $orders->firstItem(),
                    $orders->lastItem(),
                    $orders->total()
                ),

                'total' => $orders->total(),
            ]);
        }

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
        ]);
    }

    /**
     * Display one order.
     */
    public function show(Order $order): View
    {
        $order->load([
            'user',

            'items.product.images',

            'items.variant',

            'notes' => function ($query) {
                $query
                    ->with('user')
                    ->latestFirst();
            },

            'activities' => function ($query) {
                $query
                    ->with('user')
                    ->latestFirst();
            },
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Display a printable invoice.
     */
    public function invoice(Order $order): View
    {
        $order->load([
            'user',
            'items.product.images',
            'items.variant',
        ]);

        return view(
            'admin.orders.invoice',
            compact('order')
        );
    }

    /**
     * Download invoice as PDF.
     */
    /**
     * Download an order invoice as a PDF.
     */
    public function downloadInvoice(Order $order)
    {
        $order->load([
            'user',
            'items.product.images',
            'items.variant',
        ]);

        $orderNumber = $order->order_number
            ?: 'ORD-' . str_pad(
                (string) $order->id,
                6,
                '0',
                STR_PAD_LEFT
            );

        $safeOrderNumber = preg_replace(
            '/[^A-Za-z0-9\-_]/',
            '-',
            $orderNumber
        );

        $pdf = Pdf::loadView(
            'admin.orders.invoice-pdf',
            compact('order')
        );

        $pdf->setPaper('a4', 'portrait');

        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $pdf->download(
            'Invoice-' . $safeOrderNumber . '.pdf'
        );
    }

    /**
     * Update one order and record its changes.
     */
    public function update(
        Request $request,
        Order $order,
        OrderActivityService $activityService
    ): RedirectResponse {
        $validated = $request->validate([
            'order_status' => [
                'required',
                Rule::in(self::ORDER_STATUSES),
            ],

            'payment_status' => [
                'required',
                Rule::in(self::PAYMENT_STATUSES),
            ],

            'tracking_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'admin_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $validated['tracking_number'] = filled(
            $validated['tracking_number'] ?? null
        )
            ? trim($validated['tracking_number'])
            : null;

        $validated['admin_notes'] = filled(
            $validated['admin_notes'] ?? null
        )
            ? trim($validated['admin_notes'])
            : null;

        $oldOrderStatus = $order->order_status;
        $oldPaymentStatus = $order->payment_status;
        $oldTrackingNumber = $order->tracking_number;
        $oldAdminNotes = $order->admin_notes;

        DB::transaction(function () use (
            $order,
            $validated,
            $oldOrderStatus,
            $oldPaymentStatus,
            $oldTrackingNumber,
            $oldAdminNotes,
            $activityService
        ) {
            $order->update($validated);

            if ($oldOrderStatus !== $order->order_status) {
                $activityService->orderStatusChanged(
                    $order,
                    $oldOrderStatus,
                    $order->order_status
                );
            }

            if ($oldPaymentStatus !== $order->payment_status) {
                $activityService->paymentStatusChanged(
                    $order,
                    $oldPaymentStatus,
                    $order->payment_status
                );
            }

            if ($oldTrackingNumber !== $order->tracking_number) {
                $activityService->trackingUpdated(
                    $order,
                    $oldTrackingNumber,
                    $order->tracking_number
                );
            }

            if ($oldAdminNotes !== $order->admin_notes) {
                $activityService->record(
                    order: $order,
                    type: OrderActivity::TYPE_ORDER_UPDATED,
                    title: 'Legacy admin notes updated',
                    description: 'The order admin-notes field was updated.',
                    fieldName: 'admin_notes',
                    oldValue: $oldAdminNotes,
                    newValue: $order->admin_notes
                );
            }
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Store a new order note.
     */
    public function storeNote(
        Request $request,
        Order $order,
        OrderActivityService $activityService
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'note' => [
                'required',
                'string',
                'max:5000',
            ],

            'is_customer_visible' => [
                'nullable',
                'boolean',
            ],
        ]);

        $noteText = trim($validated['note']);

        $customerVisible = $request->boolean(
            'is_customer_visible'
        );

        DB::transaction(function () use (
            $order,
            $noteText,
            $customerVisible,
            $activityService
        ) {
            $order->notes()->create([
                'user_id' => auth()->id(),
                'note' => $noteText,
                'is_customer_visible' => $customerVisible,
            ]);

            $activityService->noteAdded(
                $order,
                $noteText,
                $customerVisible
            );
        });

        $order->load([
            'notes' => function ($query) {
                $query
                    ->with('user')
                    ->latestFirst();
            },

            'activities' => function ($query) {
                $query
                    ->with('user')
                    ->latestFirst();
            },
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,

                'message' => $customerVisible
                    ? 'Customer-visible note added successfully.'
                    : 'Internal note added successfully.',

                'notes_html' => view(
                    'admin.orders.partials.notes',
                    compact('order')
                )->render(),

                'activities_html' => view(
                    'admin.orders.partials.activities',
                    compact('order')
                )->render(),
            ]);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order note added successfully.');
    }

    /**
     * Delete an order note.
     */
    public function destroyNote(
        Request $request,
        Order $order,
        OrderNote $note,
        OrderActivityService $activityService
    ): JsonResponse|RedirectResponse {
        abort_unless(
            (int) $note->order_id === (int) $order->id,
            404
        );

        DB::transaction(function () use (
            $order,
            $note,
            $activityService
        ) {
            $deletedNoteText = $note->note;

            $note->delete();

            $activityService->record(
                order: $order,
                type: OrderActivity::TYPE_ORDER_UPDATED,
                title: 'Order note deleted',
                description: $deletedNoteText,
                metadata: [
                    'action' => 'note_deleted',
                ]
            );
        });

        $order->load([
            'notes' => function ($query) {
                $query
                    ->with('user')
                    ->latestFirst();
            },

            'activities' => function ($query) {
                $query
                    ->with('user')
                    ->latestFirst();
            },
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order note deleted successfully.',

                'notes_html' => view(
                    'admin.orders.partials.notes',
                    compact('order')
                )->render(),

                'activities_html' => view(
                    'admin.orders.partials.activities',
                    compact('order')
                )->render(),
            ]);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order note deleted successfully.');
    }

    /**
     * Delete one order.
     */
    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    /**
     * Update multiple selected orders.
     */
    public function bulkUpdate(
        Request $request,
        OrderActivityService $activityService
    ): JsonResponse {
        $validated = $request->validate([
            'order_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'order_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:orders,id',
            ],

            'action' => [
                'required',
                'string',
                Rule::in([
                    'order_status',
                    'payment_status',
                ]),
            ],

            'value' => [
                'required',
                'string',
            ],
        ]);

        $allowedValues = $validated['action'] === 'order_status'
            ? self::ORDER_STATUSES
            : self::PAYMENT_STATUSES;

        if (!in_array($validated['value'], $allowedValues, true)) {
            return response()->json([
                'success' => false,
                'message' => 'The selected status is invalid.',
            ], 422);
        }

        $updatedCount = DB::transaction(function () use (
            $validated,
            $activityService
        ) {
            $orders = Order::query()
                ->whereIn('id', $validated['order_ids'])
                ->get();

            $updated = 0;

            foreach ($orders as $order) {
                $field = $validated['action'];
                $oldValue = $order->{$field};
                $newValue = $validated['value'];

                if ($oldValue === $newValue) {
                    continue;
                }

                $order->update([
                    $field => $newValue,
                ]);

                if ($field === 'order_status') {
                    $activityService->orderStatusChanged(
                        $order,
                        $oldValue,
                        $newValue
                    );
                } else {
                    $activityService->paymentStatusChanged(
                        $order,
                        $oldValue,
                        $newValue
                    );
                }

                $updated++;
            }

            return $updated;
        });

        $label = $validated['action'] === 'order_status'
            ? 'order status'
            : 'payment status';

        return response()->json([
            'success' => true,

            'message' => sprintf(
                '%d order(s) had their %s updated to %s.',
                $updatedCount,
                $label,
                ucfirst($validated['value'])
            ),

            'updated_count' => $updatedCount,
        ]);
    }

    /**
     * Delete multiple selected orders.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'order_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:orders,id',
            ],
        ]);

        $deletedCount = DB::transaction(function () use ($validated) {
            $orders = Order::query()
                ->whereIn('id', $validated['order_ids'])
                ->get();

            $deleted = 0;

            foreach ($orders as $order) {
                $order->delete();
                $deleted++;
            }

            return $deleted;
        });

        return response()->json([
            'success' => true,

            'message' => sprintf(
                '%d order(s) deleted successfully.',
                $deletedCount
            ),

            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Export selected orders as CSV.
     */
    public function bulkExport(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'order_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'order_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:orders,id',
            ],
        ]);

        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->whereIn('id', $validated['order_ids'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'orders-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(
            function () use ($orders) {
                $handle = fopen('php://output', 'w');

                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv($handle, [
                    'Order ID',
                    'Order Number',
                    'Customer Name',
                    'Customer Email',
                    'Customer Phone',
                    'Items',
                    'Subtotal',
                    'Discount',
                    'Shipping',
                    'Tax',
                    'Total',
                    'Currency',
                    'Payment Method',
                    'Payment Status',
                    'Order Status',
                    'Tracking Number',
                    'Created At',
                ]);

                foreach ($orders as $order) {
                    $customerName = $order->billing_name
                        ?: $order->shipping_name
                        ?: $order->user?->name
                        ?: 'Guest';

                    $customerEmail = $order->billing_email
                        ?: $order->shipping_email
                        ?: $order->user?->email
                        ?: '';

                    $customerPhone = $order->billing_phone
                        ?: $order->shipping_phone
                        ?: '';

                    fputcsv($handle, [
                        $order->id,
                        $order->order_number ?: '#' . $order->id,
                        $customerName,
                        $customerEmail,
                        $customerPhone,
                        $order->items_count,
                        number_format((float) $order->subtotal, 2, '.', ''),
                        number_format((float) $order->discount, 2, '.', ''),
                        number_format((float) $order->shipping, 2, '.', ''),
                        number_format((float) $order->tax, 2, '.', ''),
                        number_format((float) $order->total, 2, '.', ''),
                        strtoupper($order->currency ?: 'USD'),
                        $order->payment_method,
                        $order->payment_status,
                        $order->order_status,
                        $order->tracking_number,
                        $order->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }

    /**
     * Validate list filters.
     */
    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'order_status' => [
                'nullable',
                Rule::in(self::ORDER_STATUSES),
            ],

            'payment_status' => [
                'nullable',
                Rule::in(self::PAYMENT_STATUSES),
            ],

            'date_range' => [
                'nullable',
                Rule::in([
                    'today',
                    '7days',
                    '30days',
                    'month',
                    'year',
                    'custom',
                ]),
            ],

            'date_from' => [
                'nullable',
                'date',
                'required_if:date_range,custom',
            ],

            'date_to' => [
                'nullable',
                'date',
                'required_if:date_range,custom',
                'after_or_equal:date_from',
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'newest',
                    'oldest',
                    'total_high',
                    'total_low',
                ]),
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);
    }

    /**
     * Build the filtered orders query.
     */
    private function buildOrdersQuery(array $filters): Builder
    {
        $query = Order::query()
            ->with('user')
            ->withCount('items');

        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $orderQuery) use ($search) {
                $orderQuery
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhere('tracking_number', 'like', "%{$search}%")
                    ->orWhere('billing_name', 'like', "%{$search}%")
                    ->orWhere('billing_email', 'like', "%{$search}%")
                    ->orWhere('billing_phone', 'like', "%{$search}%")
                    ->orWhere('shipping_name', 'like', "%{$search}%")
                    ->orWhere('shipping_email', 'like', "%{$search}%")
                    ->orWhere('shipping_phone', 'like', "%{$search}%")
                    ->orWhere('payment_reference', 'like', "%{$search}%")
                    ->orWhereHas(
                        'user',
                        function (Builder $userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");

                            if ($this->userTableHasPhoneColumn()) {
                                $userQuery->orWhere(
                                    'phone',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        }
                    );
            });
        }

        if (!empty($filters['order_status'])) {
            $query->where(
                'order_status',
                $filters['order_status']
            );
        }

        if (!empty($filters['payment_status'])) {
            $query->where(
                'payment_status',
                $filters['payment_status']
            );
        }

        $this->applyDateFilter(
            $query,
            $filters['date_range'] ?? null,
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null
        );

        $this->applySorting(
            $query,
            $filters['sort'] ?? 'newest'
        );

        return $query;
    }

    /**
     * Apply the selected date range.
     */
    private function applyDateFilter(
        Builder $query,
        ?string $dateRange,
        ?string $dateFrom,
        ?string $dateTo
    ): void {
        if (!$dateRange) {
            return;
        }

        [$startDate, $endDate] = match ($dateRange) {
            'today' => [
                now()->copy()->startOfDay(),
                now()->copy()->endOfDay(),
            ],

            '7days' => [
                now()->copy()->subDays(6)->startOfDay(),
                now()->copy()->endOfDay(),
            ],

            '30days' => [
                now()->copy()->subDays(29)->startOfDay(),
                now()->copy()->endOfDay(),
            ],

            'month' => [
                now()->copy()->startOfMonth(),
                now()->copy()->endOfDay(),
            ],

            'year' => [
                now()->copy()->startOfYear(),
                now()->copy()->endOfDay(),
            ],

            'custom' => [
                $dateFrom
                    ? now()->parse($dateFrom)->startOfDay()
                    : null,

                $dateTo
                    ? now()->parse($dateTo)->endOfDay()
                    : null,
            ],

            default => [null, null],
        };

        if (
            $startDate instanceof CarbonInterface &&
            $endDate instanceof CarbonInterface
        ) {
            $query->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        }
    }

    /**
     * Apply sorting.
     */
    private function applySorting(
        Builder $query,
        string $sort
    ): void {
        match ($sort) {
            'oldest' => $query
                ->orderBy('created_at')
                ->orderBy('id'),

            'total_high' => $query
                ->orderByDesc('total')
                ->orderByDesc('created_at'),

            'total_low' => $query
                ->orderBy('total')
                ->orderByDesc('created_at'),

            default => $query
                ->orderByDesc('created_at')
                ->orderByDesc('id'),
        };
    }

    /**
     * Generate the paginator summary.
     */
    private function buildResultsSummary(
        ?int $firstItem,
        ?int $lastItem,
        int $total
    ): string {
        if ($total === 0) {
            return 'No orders found';
        }

        return sprintf(
            'Showing %s–%s of %s orders',
            number_format((int) $firstItem),
            number_format((int) $lastItem),
            number_format($total)
        );
    }

    /**
     * Check if users have a phone column.
     */
    private function userTableHasPhoneColumn(): bool
    {
        static $hasPhoneColumn = null;

        if ($hasPhoneColumn !== null) {
            return $hasPhoneColumn;
        }

        $hasPhoneColumn = Schema::hasColumn('users', 'phone');

        return $hasPhoneColumn;
    }
    /**
     * Packing Slip Preview
     */
    public function packingSlip(Order $order)
    {
        $order->load([
            'user',
            'items.product.images',
            'items.variant',
        ]);

        return view(
            'admin.orders.packing-slip',
            compact('order')
        );
    }


    /**
     * Download Packing Slip PDF
     */
    public function downloadPackingSlip(Order $order)
    {
        $order->load([
            'user',
            'items.product.images',
            'items.variant',
        ]);

        $pdf = Pdf::loadView(
            'admin.orders.packing-slip-pdf',
            compact('order')
        );

        $pdf->setPaper('a4');

        return $pdf->download(
            'Packing-Slip-' .
                ($order->order_number ?: $order->id) .
                '.pdf'
        );
    }
}
