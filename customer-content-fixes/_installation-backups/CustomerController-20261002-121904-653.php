<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
            ->withSum([
                'orders as total_spent' => fn (Builder $query) => $query
                    ->whereNotIn('order_status', ['cancelled', 'refunded']),
            ], 'total')
            ->when(
                $request->filled('search'),
                function (Builder $query) use ($request): void {
                    $search = trim($request->string('search')->toString());

                    $query->where(function (Builder $innerQuery) use ($search): void {
                        $innerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->filled('status'),
                fn (Builder $query) => $query->where('status', $request->string('status')->toString())
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer): View
    {
        abort_if($customer->is_admin, 404);

        $customer->load([
            'orders' => fn ($query) => $query->latest(),
            'reviews.product',
            'emailIdentities',
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
            'status' => [
                'required',
                Rule::in(['active', 'inactive', 'blocked']),
            ],
        ]);

        $customer->update([
            'name' => trim($validated['name']),
            'status' => $validated['status'],
        ]);

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
