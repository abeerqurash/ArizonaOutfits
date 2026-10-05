<?php

namespace App\Http\Controllers\Admin;

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
            'facebook_url' => ['nullable','url:http,https','max:1000'],
            'instagram_url' => ['nullable','url:http,https','max:1000'],
            'linkedin_url' => ['nullable','url:http,https','max:1000'],
            'youtube_url' => ['nullable','url:http,https','max:1000'],
            'x_url' => ['nullable','url:http,https','max:1000'],
            'footer_copyright'=>['nullable','string','max:255'],
            'footer_about'=>['nullable','string','max:2000'],
            'store_name' => ['required', 'string', 'max:255'],
            'store_email' => ['nullable', 'email', 'max:255'],
            'store_phone' => ['nullable', 'string', 'max:50'],
            'store_address' => ['nullable', 'string', 'max:2000'],
            'currency' => ['required', 'string', 'regex:/^[A-Za-z]{3}$/D'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'shipping_fee' => ['required', 'numeric', 'min:0'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'order_prefix' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/D'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'checkout_notice' => ['nullable', 'string', 'max:5000'],
            'order_email_message' => ['nullable', 'string', 'max:5000'],

            /*
            |--------------------------------------------------------------------------
            | Bank Transfer Settings
            |--------------------------------------------------------------------------
            */
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:255'],
            'bank_swift_code' => ['nullable', 'string', 'max:255'],
            'bank_branch_name' => ['nullable', 'string', 'max:255'],
            'bank_transfer_instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        $validated['guest_checkout_enabled'] =
            $request->boolean('guest_checkout_enabled');

        $validated['cash_on_delivery_enabled'] =
            $request->boolean('cash_on_delivery_enabled');

        $validated['stock_management_enabled'] =
            $request->boolean('stock_management_enabled');

        $validated['maintenance_mode'] =
            $request->boolean('maintenance_mode');

        $validated['bank_transfer_enabled'] =
            $request->boolean('bank_transfer_enabled');

        $validated['currency']=strtoupper($validated['currency']);
        EcommerceSetting::current()->update($validated);

        return back()->with(
            'success',
            'E-commerce settings updated successfully.'
        );
    }
}
