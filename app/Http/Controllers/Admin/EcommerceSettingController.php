<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EcommerceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EcommerceSettingController extends AdminController
{
    public function edit(): View
    {
        $settings = EcommerceSetting::current();

        return view(
            'admin.settings.edit',
            compact('settings')
        );
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => [
                'required',
                'string',
                'max:255',
            ],

            'store_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'store_phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'store_address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'currency' => [
                'required',
                'string',
                'max:10',
            ],

            'currency_symbol' => [
                'required',
                'string',
                'max:10',
            ],

            'tax_percentage' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'shipping_fee' => [
                'required',
                'numeric',
                'min:0',
            ],

            'free_shipping_threshold' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'order_prefix' => [
                'required',
                'string',
                'max:20',
            ],

            'low_stock_threshold' => [
                'required',
                'integer',
                'min:0',
            ],

            'checkout_notice' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'order_email_message' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $validated['guest_checkout_enabled'] =
            $request->boolean('guest_checkout_enabled');

        $validated['cash_on_delivery_enabled'] =
            $request->boolean('cash_on_delivery_enabled');

        $validated['stock_management_enabled'] =
            $request->boolean('stock_management_enabled');

        $validated['maintenance_mode'] =
            $request->boolean('maintenance_mode');

        EcommerceSetting::current()->update($validated);

        return back()->with(
            'success',
            'E-commerce settings updated successfully.'
        );
    }
}