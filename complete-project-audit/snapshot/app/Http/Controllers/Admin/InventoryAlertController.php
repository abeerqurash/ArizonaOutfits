<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\InventoryAlert;
use Illuminate\Http\Request;

class InventoryAlertController extends Controller
{
    /**
     * Display inventory alerts.
     */
    public function index(Request $request)
    {
        $alerts = InventoryAlert::query()
            ->with([
                'product',
                'variant.product',
            ])
            ->when(
                $request->filled('status'),
                fn($query) =>
                $query->where(
                    'status',
                    $request->status
                )
            )
            ->when(
                $request->filled('type'),
                fn($query) =>
                $query->where(
                    'alert_type',
                    $request->type
                )
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.inventory-alerts.index',
            [
                'alerts' => $alerts,

                'lowStockCount' =>
                InventoryAlert::where(
                    'status',
                    'active'
                )
                    ->where(
                        'alert_type',
                        'low_stock'
                    )
                    ->count(),

                'outOfStockCount' =>
                InventoryAlert::where(
                    'status',
                    'active'
                )
                    ->where(
                        'alert_type',
                        'out_of_stock'
                    )
                    ->count(),
            ]
        );
    }

    /**
     * Resolve an alert.
     */
    public function resolve(
        InventoryAlert $inventoryAlert
    ) {
        $inventoryAlert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return back()->with(
            'success',
            'Inventory alert resolved successfully.'
        );
    }

    public function boot(): void
    {
        View::composer('admin.*', function ($view) {
            $view->with(
                'activeInventoryAlertCount',
                InventoryAlert::where('status', 'active')->count()
            );
        });
    }
}
