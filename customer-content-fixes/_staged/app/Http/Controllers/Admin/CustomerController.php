<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Services\CustomerSpendService;

class CustomerController extends AdminController
{
    public function index(Request $request): View
    {
        $customers = User::query()
            ->where('is_admin', false)
            ->withCount(['orders' => fn ($query) => $query->withTrashed()])
            ->with(['orders' => fn ($query) => $query->withTrashed()->where('payment_status', 'paid')->whereNotIn('order_status', ['cancelled', 'refunded'])->select(['id', 'user_id', 'currency', 'total', 'payment_status', 'order_status'])])
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

        foreach ($customers as $listedCustomer) {
            $listedCustomer->paid_spend_by_currency = CustomerSpendService::totals($listedCustomer->orders);
        }
        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer): View
    {
        abort_if($customer->is_admin, 404);

        $customer->load([
            'orders' => fn ($query) => $query->withTrashed()->latest(),
            'reviews.product',
            'emailIdentities',
        ]);

        $totalSpent = CustomerSpendService::totals($customer->orders);

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

        $deleted = DB::transaction(function () use ($customer): bool {
            $locked = User::query()->lockForUpdate()->findOrFail($customer->id);
            abort_if($locked->is_admin, 404);
            if ($locked->orders()->withTrashed()->exists()) return false;
            $locked->delete();
            return true;
        });
        if (!$deleted) {
            return back()->with('error', 'This customer has current or archived orders. Block the account instead of deleting its history.');
        }

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
