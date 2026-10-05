<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;
class InventoryHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryHistory::query()
            ->with([
                'product',
                'variant',
                'order',
                'user',
                'admin',
            ]);
        if ($search = trim((string) $request->search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reason', 'like', "%{$search}%")
                    ->orWhere('movement_type', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    })
                    ->orWhereHas('order', function ($query) use ($search) {
                        $query->where('order_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('admin', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('variant', fn ($query) => $query->where('sku', 'like', "%{$search}%"))
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            });
        }
        if ($request->filled('movement')) {
            $query->where(
                'movement_type',
                $request->movement
            );
        }
        if ($request->filled('product')) {
            $query->where(
                'product_id',
                $request->product
            );
        }
        if ($request->filled('from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from
            );
        }
        if ($request->filled('to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to
            );
        }
        $history = $query
            ->latest()
            ->paginate(25)
            ->withQueryString();
        return view(
            'admin.inventory-history.index',
            [
                'history' => $history,
                'movementTypes'
                    => InventoryHistory::movementTypes(),
            ]
        );
    }
}