<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CustomerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $ordersQuery = $request->user()->orders();

        $stats = [
            'orders' => (clone $ordersQuery)->count(),
            'processing' => (clone $ordersQuery)
                ->whereIn('order_status', [
                    'pending',
                    'confirmed',
                    'processing',
                    'packed',
                    'out_for_delivery',
                ])
                ->count(),
            'shipped' => (clone $ordersQuery)
                ->where('order_status', 'shipped')
                ->count(),
            'completed' => (clone $ordersQuery)
                ->whereIn('order_status', ['completed', 'delivered'])
                ->count(),
        ];

        $recentOrders = $request->user()
            ->orders()
            ->withCount('items')
            ->latest()
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact('stats', 'recentOrders'));
    }

    public function orders(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                'in:pending,confirmed,processing,packed,shipped,out_for_delivery,completed,delivered,cancelled,refunded',
            ],
        ]);

        $orders = $request->user()
            ->orders()
            ->withCount('items')
            ->when(
                filled($filters['search'] ?? null),
                function (Builder $query) use ($filters): void {
                    $search = trim((string) $filters['search']);

                    $query->where(function (Builder $query) use ($search): void {
                        $query
                            ->where('order_number', 'like', "%{$search}%")
                            ->orWhere('tracking_number', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                filled($filters['status'] ?? null),
                fn (Builder $query) => $query->where(
                    'order_status',
                    $filters['status']
                )
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Request $request, int $order): View
    {
        $order = $this->customerOrder($request, $order);

        $order->load([
            'items.product.images',
            'items.variant',

            /*
            |--------------------------------------------------------------------------
            | Customer-safe order activity
            |--------------------------------------------------------------------------
            |
            | Never expose internal/admin-only activity on the customer order page.
            | Customer-visible notes are loaded separately below.
            |
            */
            'activities' => fn ($query) => $query
                ->whereIn('type', [
                    OrderActivity::TYPE_ORDER_CREATED,
                    OrderActivity::TYPE_ORDER_STATUS_CHANGED,
                    OrderActivity::TYPE_PAYMENT_STATUS_CHANGED,
                    OrderActivity::TYPE_TRACKING_UPDATED,
                ])
                ->latestFirst(),

            'notes' => fn ($query) => $query
                ->where('is_customer_visible', true)
                ->latest(),
        ]);

        return view('customer.orders.show', compact('order'));
    }

    public function invoice(Request $request, int $order): View
    {
        $order = $this->customerOrder($request, $order);

        $order->load([
            'user',
            'items.product.images',
            'items.variant',
        ]);

        return view('admin.orders.invoice', compact('order'));
    }

    public function downloadInvoice(Request $request, int $order): Response
    {
        $order = $this->customerOrder($request, $order);

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
        )
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        return $pdf->download(
            'Invoice-' . $safeOrderNumber . '.pdf'
        );
    }

    private function customerOrder(
        Request $request,
        int $order
    ): Order {
        return $request->user()
            ->orders()
            ->whereKey($order)
            ->firstOrFail();
    }
}
