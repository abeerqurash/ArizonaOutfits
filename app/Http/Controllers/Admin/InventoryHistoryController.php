<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;

class InventoryHistoryController extends Controller
{
    /**
     * Display inventory history.
     */
    public function index(Request $request)
    {
        $query = InventoryHistory::query()
            ->with([
                'product',
                'variant',
                'order',
                'user',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

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

                    ->orWhereHas('user', function ($query) use ($search) {

                        $query->where('name', 'like', "%{$search}%");
                    });

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Movement Type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('movement')) {

            $query->where(
                'movement_type',
                $request->movement
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Product
        |--------------------------------------------------------------------------
        */

        if ($request->filled('product')) {

            $query->where(
                'product_id',
                $request->product
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

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