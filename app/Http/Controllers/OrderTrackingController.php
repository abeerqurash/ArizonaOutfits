<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    /**
     * Display the public order-tracking form.
     */
    public function index(): View
    {
        return view('orders.track');
    }

    /**
     * Securely find an order using its order number and customer email.
     */
    public function lookup(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => [
                'required',
                'string',
                'max:100',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        $orderNumber = strtoupper(
            trim((string) $validated['order_number'])
        );

        $email = strtolower(
            trim((string) $validated['email'])
        );

        $order = Order::query()
            ->with([
                'items.product.images',
                'items.variant',
                'activities' => fn ($query) => $query
                    ->whereIn('type', [
                        'order_created',
                        'order_status_changed',
                        'tracking_updated',
                    ])
                    ->latestFirst(),
                'notes' => fn ($query) => $query
                    ->where('is_customer_visible', true)
                    ->latest(),
            ])
            ->whereRaw(
                'UPPER(order_number) = ?',
                [$orderNumber]
            )
            ->where(function ($query) use ($email): void {
                $query
                    ->whereRaw(
                        'LOWER(billing_email) = ?',
                        [$email]
                    )
                    ->orWhereRaw(
                        'LOWER(shipping_email) = ?',
                        [$email]
                    );
            })
            ->first();

        if (!$order) {
            return back()
                ->withInput(
                    $request->only(
                        'order_number',
                        'email'
                    )
                )
                ->withErrors([
                    'tracking' =>
                        'We could not find an order matching that order number and email address. Please check the details and try again.',
                ]);
        }

        return view(
            'orders.track',
            compact('order')
        );
    }
}
