<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CouponController extends AdminController
{
    /**
     * Display all coupons.
     */
    public function index(): View
    {
        $coupons = Coupon::query()
            ->latest()
            ->paginate(10);

        return view(
            'admin.coupons.index',
            compact('coupons')
        );
    }

    /**
     * Display coupon creation form.
     */
    public function create(): View
    {
        return view('admin.coupons.create');
    }

    /**
     * Store a newly created coupon.
     */
    public function store(
        Request $request
    ): RedirectResponse {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                'unique:coupons,code',
            ],

            'type' => [
                'required',
                'in:fixed,percentage',
            ],

            'value' => [
                'required',
                'numeric',
                'min:0',
            ],

            'minimum_order_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'usage_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        $data['code'] = strtoupper(
            Str::slug(
                $data['code'],
                ''
            )
        );

        $data['minimum_order_amount'] =
            $data['minimum_order_amount'] ?? 0;

        $data['usage_limit'] =
            $data['usage_limit'] ?? null;

        $data['start_date'] =
            $data['start_date'] ?? null;

        $data['end_date'] =
            $data['end_date'] ?? null;

        $data['status'] =
            $request->boolean('status');

        Coupon::create($data);

        return redirect()
            ->route('admin.coupons.index')
            ->with(
                'success',
                'Coupon created successfully.'
            );
    }

    /**
     * Display coupon editing form.
     */
    public function edit(
        Coupon $coupon
    ): View {
        return view(
            'admin.coupons.edit',
            compact('coupon')
        );
    }

    /**
     * Update an existing coupon.
     */
    public function update(
        Request $request,
        Coupon $coupon
    ): RedirectResponse {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                'unique:coupons,code,' . $coupon->id,
            ],

            'type' => [
                'required',
                'in:fixed,percentage',
            ],

            'value' => [
                'required',
                'numeric',
                'min:0',
            ],

            'minimum_order_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'usage_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ]);

        $data['code'] = strtoupper(
            Str::slug(
                $data['code'],
                ''
            )
        );

        $data['minimum_order_amount'] =
            $data['minimum_order_amount'] ?? 0;

        $data['usage_limit'] =
            $data['usage_limit'] ?? null;

        $data['start_date'] =
            $data['start_date'] ?? null;

        $data['end_date'] =
            $data['end_date'] ?? null;

        $data['status'] =
            $request->boolean('status');

        $coupon->update($data);

        /*
         * Remove the coupon from the current cart session when
         * an administrator disables or edits the active coupon.
         */
        $appliedCoupon = session('cart_coupon');

        if (
            is_array($appliedCoupon)
            && (int) ($appliedCoupon['id'] ?? 0)
                === (int) $coupon->id
        ) {
            session()->forget('cart_coupon');
        }

        return redirect()
            ->route('admin.coupons.index')
            ->with(
                'success',
                'Coupon updated successfully.'
            );
    }

    /**
     * Delete a coupon.
     */
    public function destroy(
        Coupon $coupon
    ): RedirectResponse {
        $appliedCoupon = session('cart_coupon');

        if (
            is_array($appliedCoupon)
            && (int) ($appliedCoupon['id'] ?? 0)
                === (int) $coupon->id
        ) {
            session()->forget('cart_coupon');
        }

        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with(
                'success',
                'Coupon deleted successfully.'
            );
    }
}