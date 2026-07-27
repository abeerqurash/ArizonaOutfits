<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends AdminController
{
    public function index(Request $request): View
    {
        $customers = User::query()
            ->where('is_admin', false)
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim($request->string('search')->toString());

                    $query->where(function ($innerQuery) use ($search) {
                        $innerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where(
                    'status',
                    $request->status
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.customers.index',
            compact('customers')
        );
    }

    public function show(User $customer): View
    {
        abort_if($customer->is_admin, 404);

        $customer->load([
            'orders' => fn ($query) => $query->latest(),
            'reviews.product',
        ]);

        $totalSpent = $customer->orders()
            ->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->sum('total');

        return view(
            'admin.customers.show',
            compact('customer', 'totalSpent')
        );
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        abort_if($customer->is_admin, 404);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($customer->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'status' => [
                'required',
                'in:active,inactive,blocked',
            ],
        ]);

        $customer->update($validated);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(User $customer): RedirectResponse
    {
        abort_if($customer->is_admin, 404);

        if ($customer->orders()->exists()) {
            return back()->with(
                'error',
                'This customer cannot be deleted because they have orders. You can block the customer instead.'
            );
        }

        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}