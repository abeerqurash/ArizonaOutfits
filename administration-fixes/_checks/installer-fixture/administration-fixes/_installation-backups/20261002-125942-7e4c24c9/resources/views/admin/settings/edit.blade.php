@extends('admin.layouts.app')

@section('title', 'Store Settings')
@section('page-heading', 'Store Settings')

@section('content')

<div class="admin-page-header settings-page-header">
    <div>
        <span class="admin-page-eyebrow">Store configuration</span>
        <h2>Store Settings</h2>
        <p>
            Manage store details, checkout, payments, shipping and customer-facing messages.
        </p>
    </div>

    <div class="admin-page-actions">
        <a href="{{ route('admin.dashboard') }}" class="admin-button admin-button-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Dashboard
        </a>

        <button type="submit" form="ecommerceSettingsForm" class="admin-button admin-button-primary">
            <i class="fa-solid fa-floppy-disk"></i>
            Save Settings
        </button>
    </div>
</div>

@if (session('success'))
    <div class="settings-alert settings-alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <div>
            <strong>Settings saved</strong>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="settings-alert settings-alert-error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
            <strong>Please check the highlighted information.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form
    id="ecommerceSettingsForm"
    action="{{ route('admin.settings.update') }}"
    method="POST"
    class="settings-form"
>
    @csrf
    @method('PUT')

    <div class="settings-summary-grid">
        <article class="admin-stat-card">
            <div class="admin-stat-card-top">
                <div>
                    <span class="admin-stat-label">Store</span>
                    <strong class="admin-stat-value settings-stat-text">
                        {{ $settings->store_name ?: 'Not configured' }}
                    </strong>
                </div>
                <span class="admin-stat-icon settings-icon-purple">
                    <i class="fa-solid fa-store"></i>
                </span>
            </div>
            <span class="settings-stat-note">Identity & contact information</span>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card-top">
                <div>
                    <span class="admin-stat-label">Currency</span>
                    <strong class="admin-stat-value">
                        {{ strtoupper($settings->currency ?: 'USD') }}
                    </strong>
                </div>
                <span class="admin-stat-icon settings-icon-blue">
                    <i class="fa-solid fa-coins"></i>
                </span>
            </div>
            <span class="settings-stat-note">Store pricing currency</span>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card-top">
                <div>
                    <span class="admin-stat-label">Bank Transfer</span>
                    <strong class="admin-stat-value settings-stat-text">
                        {{ $settings->bank_transfer_enabled ? 'Enabled' : 'Disabled' }}
                    </strong>
                </div>
                <span class="admin-stat-icon settings-icon-green">
                    <i class="fa-solid fa-building-columns"></i>
                </span>
            </div>
            <span class="settings-stat-note">Manual payment verification</span>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card-top">
                <div>
                    <span class="admin-stat-label">Maintenance</span>
                    <strong class="admin-stat-value settings-stat-text">
                        {{ $settings->maintenance_mode ? 'Enabled' : 'Disabled' }}
                    </strong>
                </div>
                <span class="admin-stat-icon settings-icon-orange">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </span>
            </div>
            <span class="settings-stat-note">Store availability mode</span>
        </article>
    </div>

    <div class="settings-layout">

        <div class="settings-main">

            <section class="admin-panel settings-panel">
                <div class="admin-panel-header settings-panel-header">
                    <div>
                        <span class="admin-panel-eyebrow">Store profile</span>
                        <h3>Store Information</h3>
                        <p>Public business and customer contact details.</p>
                    </div>
                    <span class="settings-panel-icon settings-icon-purple">
                        <i class="fa-solid fa-store"></i>
                    </span>
                </div>

                <div class="settings-panel-body">
                    <div class="settings-grid settings-grid-two">
                        <div class="settings-field">
                            <label for="store_name">Store Name <span>*</span></label>
                            <input
                                type="text"
                                name="store_name"
                                id="store_name"
                                value="{{ old('store_name', $settings->store_name) }}"
                                required
                            >
                            @error('store_name')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="store_email">Store Email</label>
                            <input
                                type="email"
                                name="store_email"
                                id="store_email"
                                value="{{ old('store_email', $settings->store_email) }}"
                                placeholder="support@example.com"
                            >
                            @error('store_email')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="store_phone">Store Phone</label>
                            <input
                                type="text"
                                name="store_phone"
                                id="store_phone"
                                value="{{ old('store_phone', $settings->store_phone) }}"
                                placeholder="+1 000 000 0000"
                            >
                            @error('store_phone')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field settings-field-wide">
                            <label for="store_address">Store Address</label>
                            <textarea
                                name="store_address"
                                id="store_address"
                                rows="4"
                                placeholder="Business or correspondence address"
                            >{{ old('store_address', $settings->store_address) }}</textarea>
                            @error('store_address')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="admin-panel settings-panel">
                <div class="admin-panel-header settings-panel-header">
                    <div>
                        <span class="admin-panel-eyebrow">Payments</span>
                        <h3>Bank Transfer</h3>
                        <p>Manage the bank information customers need to complete a transfer.</p>
                    </div>
                    <span class="settings-panel-icon settings-icon-green">
                        <i class="fa-solid fa-building-columns"></i>
                    </span>
                </div>

                <div class="settings-panel-body">
                    <label class="settings-toggle-card" for="bank_transfer_enabled">
                        <span class="settings-toggle-copy">
                            <strong>Enable Bank Transfer</strong>
                            <small>
                                Allow authenticated customers to place orders for manual bank-transfer payment.
                            </small>
                        </span>

                        <span class="settings-switch">
                            <input
                                type="checkbox"
                                name="bank_transfer_enabled"
                                id="bank_transfer_enabled"
                                value="1"
                                @checked(old('bank_transfer_enabled', $settings->bank_transfer_enabled))
                            >
                            <span class="settings-switch-track"></span>
                        </span>
                    </label>

                    <div class="settings-grid settings-grid-two settings-bank-fields">
                        <div class="settings-field">
                            <label for="bank_name">Bank Name</label>
                            <input
                                type="text"
                                name="bank_name"
                                id="bank_name"
                                value="{{ old('bank_name', $settings->bank_name) }}"
                                placeholder="Bank name"
                            >
                            @error('bank_name')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="bank_account_name">Account Name / Title</label>
                            <input
                                type="text"
                                name="bank_account_name"
                                id="bank_account_name"
                                value="{{ old('bank_account_name', $settings->bank_account_name) }}"
                                placeholder="Arizona Outfits"
                            >
                            @error('bank_account_name')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="bank_account_number">Account Number</label>
                            <input
                                type="text"
                                name="bank_account_number"
                                id="bank_account_number"
                                value="{{ old('bank_account_number', $settings->bank_account_number) }}"
                                autocomplete="off"
                                placeholder="Account number"
                            >
                            @error('bank_account_number')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="bank_iban">IBAN</label>
                            <input
                                type="text"
                                name="bank_iban"
                                id="bank_iban"
                                value="{{ old('bank_iban', $settings->bank_iban) }}"
                                autocomplete="off"
                                placeholder="International bank account number"
                            >
                            @error('bank_iban')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="bank_swift_code">SWIFT / BIC Code</label>
                            <input
                                type="text"
                                name="bank_swift_code"
                                id="bank_swift_code"
                                value="{{ old('bank_swift_code', $settings->bank_swift_code) }}"
                                placeholder="SWIFT / BIC"
                            >
                            @error('bank_swift_code')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="bank_branch_name">Branch Name</label>
                            <input
                                type="text"
                                name="bank_branch_name"
                                id="bank_branch_name"
                                value="{{ old('bank_branch_name', $settings->bank_branch_name) }}"
                                placeholder="Branch name"
                            >
                            @error('bank_branch_name')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field settings-field-wide">
                            <label for="bank_transfer_instructions">Customer Payment Instructions</label>
                            <textarea
                                name="bank_transfer_instructions"
                                id="bank_transfer_instructions"
                                rows="5"
                                placeholder="Example: Transfer the exact order total and use your Order ID as the payment reference."
                            >{{ old('bank_transfer_instructions', $settings->bank_transfer_instructions) }}</textarea>
                            <small class="settings-help">
                                Each bank-transfer order uses its generated Order ID as the payment reference.
                            </small>
                            @error('bank_transfer_instructions')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="admin-panel settings-panel">
                <div class="admin-panel-header settings-panel-header">
                    <div>
                        <span class="admin-panel-eyebrow">Customer communication</span>
                        <h3>Customer Messages</h3>
                        <p>Control the messages displayed during checkout and order communication.</p>
                    </div>
                    <span class="settings-panel-icon settings-icon-blue">
                        <i class="fa-solid fa-message"></i>
                    </span>
                </div>

                <div class="settings-panel-body">
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="checkout_notice">Checkout Notice</label>
                            <textarea
                                name="checkout_notice"
                                id="checkout_notice"
                                rows="4"
                                placeholder="Optional notice shown during checkout"
                            >{{ old('checkout_notice', $settings->checkout_notice) }}</textarea>
                            @error('checkout_notice')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="order_email_message">Order Confirmation Message</label>
                            <textarea
                                name="order_email_message"
                                id="order_email_message"
                                rows="5"
                                placeholder="Optional message included in order communication"
                            >{{ old('order_email_message', $settings->order_email_message) }}</textarea>
                            @error('order_email_message')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <aside class="settings-sidebar">

            <section class="admin-panel settings-panel">
                <div class="admin-panel-header settings-panel-header">
                    <div>
                        <span class="admin-panel-eyebrow">Commerce</span>
                        <h3>Currency & Orders</h3>
                    </div>
                    <span class="settings-panel-icon settings-icon-blue">
                        <i class="fa-solid fa-receipt"></i>
                    </span>
                </div>

                <div class="settings-panel-body">
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="currency">Currency Code <span>*</span></label>
                            <input
                                type="text"
                                name="currency"
                                id="currency"
                                value="{{ old('currency', $settings->currency) }}"
                                placeholder="USD"
                                required
                            >
                            @error('currency')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="currency_symbol">Currency Symbol <span>*</span></label>
                            <input
                                type="text"
                                name="currency_symbol"
                                id="currency_symbol"
                                value="{{ old('currency_symbol', $settings->currency_symbol) }}"
                                placeholder="$"
                                required
                            >
                            @error('currency_symbol')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="order_prefix">Order Prefix <span>*</span></label>
                            <input
                                type="text"
                                name="order_prefix"
                                id="order_prefix"
                                value="{{ old('order_prefix', $settings->order_prefix) }}"
                                placeholder="ORD"
                                required
                            >
                            @error('order_prefix')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="admin-panel settings-panel">
                <div class="admin-panel-header settings-panel-header">
                    <div>
                        <span class="admin-panel-eyebrow">Fulfilment</span>
                        <h3>Tax & Shipping</h3>
                    </div>
                    <span class="settings-panel-icon settings-icon-orange">
                        <i class="fa-solid fa-truck-fast"></i>
                    </span>
                </div>

                <div class="settings-panel-body">
                    <div class="settings-grid">
                        <div class="settings-field">
                            <label for="tax_percentage">Tax Percentage <span>*</span></label>
                            <div class="settings-input-suffix">
                                <input
                                    type="number"
                                    name="tax_percentage"
                                    id="tax_percentage"
                                    value="{{ old('tax_percentage', $settings->tax_percentage) }}"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    required
                                >
                                <span>%</span>
                            </div>
                            @error('tax_percentage')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="shipping_fee">Standard Shipping Fee <span>*</span></label>
                            <input
                                type="number"
                                name="shipping_fee"
                                id="shipping_fee"
                                value="{{ old('shipping_fee', $settings->shipping_fee) }}"
                                min="0"
                                step="0.01"
                                required
                            >
                            @error('shipping_fee')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="free_shipping_threshold">Free Shipping Threshold</label>
                            <input
                                type="number"
                                name="free_shipping_threshold"
                                id="free_shipping_threshold"
                                value="{{ old('free_shipping_threshold', $settings->free_shipping_threshold) }}"
                                min="0"
                                step="0.01"
                            >
                            @error('free_shipping_threshold')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="settings-field">
                            <label for="low_stock_threshold">Low Stock Threshold <span>*</span></label>
                            <input
                                type="number"
                                name="low_stock_threshold"
                                id="low_stock_threshold"
                                value="{{ old('low_stock_threshold', $settings->low_stock_threshold) }}"
                                min="0"
                                required
                            >
                            @error('low_stock_threshold')
                                <small class="settings-field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="admin-panel settings-panel">
                <div class="admin-panel-header settings-panel-header">
                    <div>
                        <span class="admin-panel-eyebrow">Checkout</span>
                        <h3>Store Options</h3>
                    </div>
                    <span class="settings-panel-icon settings-icon-purple">
                        <i class="fa-solid fa-sliders"></i>
                    </span>
                </div>

                <div class="settings-panel-body settings-options-list">
                    <label class="settings-option" for="guest_checkout_enabled">
                        <span>
                            <strong>Guest Checkout</strong>
                            <small>Allow customers to checkout without an account.</small>
                        </span>
                        <span class="settings-switch">
                            <input
                                type="checkbox"
                                name="guest_checkout_enabled"
                                id="guest_checkout_enabled"
                                value="1"
                                @checked(old('guest_checkout_enabled', $settings->guest_checkout_enabled))
                            >
                            <span class="settings-switch-track"></span>
                        </span>
                    </label>

                    <label class="settings-option" for="cash_on_delivery_enabled">
                        <span>
                            <strong>Cash on Delivery</strong>
                            <small>Allow eligible orders to use COD.</small>
                        </span>
                        <span class="settings-switch">
                            <input
                                type="checkbox"
                                name="cash_on_delivery_enabled"
                                id="cash_on_delivery_enabled"
                                value="1"
                                @checked(old('cash_on_delivery_enabled', $settings->cash_on_delivery_enabled))
                            >
                            <span class="settings-switch-track"></span>
                        </span>
                    </label>

                    <label class="settings-option" for="stock_management_enabled">
                        <span>
                            <strong>Stock Management</strong>
                            <small>Track and manage product inventory.</small>
                        </span>
                        <span class="settings-switch">
                            <input
                                type="checkbox"
                                name="stock_management_enabled"
                                id="stock_management_enabled"
                                value="1"
                                @checked(old('stock_management_enabled', $settings->stock_management_enabled))
                            >
                            <span class="settings-switch-track"></span>
                        </span>
                    </label>

                    <label class="settings-option" for="maintenance_mode">
                        <span>
                            <strong>Maintenance Mode</strong>
                            <small>Mark the storefront as under maintenance.</small>
                        </span>
                        <span class="settings-switch">
                            <input
                                type="checkbox"
                                name="maintenance_mode"
                                id="maintenance_mode"
                                value="1"
                                @checked(old('maintenance_mode', $settings->maintenance_mode))
                            >
                            <span class="settings-switch-track"></span>
                        </span>
                    </label>
                </div>
            </section>

            <section class="admin-panel settings-save-panel">
                <div class="settings-save-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <strong>Ready to save?</strong>
                    <p>Review payment and checkout details before applying changes.</p>
                </div>
                <button type="submit" class="admin-button admin-button-primary settings-save-button">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Settings
                </button>
            </section>

        </aside>

    </div>
</form>

<style>
.settings-page-header{margin-bottom:18px}
.settings-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
.settings-summary-grid .admin-stat-card{min-height:116px;padding:17px;border:1px solid #e6eaf1;border-radius:11px;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.02)}
.settings-summary-grid .admin-stat-label{color:#687386;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em}
.settings-summary-grid .admin-stat-value{display:block;margin-top:12px;color:#111827;font-size:19px;font-weight:800}
.settings-summary-grid .settings-stat-text{font-size:15px;line-height:1.3}
.settings-summary-grid .admin-stat-icon,.settings-panel-icon{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:9px;font-size:13px}
.settings-stat-note{display:block;margin-top:9px;color:#8a93a4;font-size:9px}
.settings-icon-purple{background:#eef0ff!important;color:#635bff!important}
.settings-icon-blue{background:#edf4ff!important;color:#3974dc!important}
.settings-icon-green{background:#eaf8f1!important;color:#13875b!important}
.settings-icon-orange{background:#fff4df!important;color:#d88716!important}

.settings-alert{display:flex;align-items:flex-start;gap:10px;margin-bottom:18px;padding:13px 15px;border:1px solid;border-radius:10px;font-size:11px}
.settings-alert>i{margin-top:2px}
.settings-alert strong,.settings-alert span{display:block}
.settings-alert span{margin-top:2px}
.settings-alert ul{margin:6px 0 0;padding-left:18px}
.settings-alert-success{border-color:#b9e6cc;background:#f1fbf5;color:#167149}
.settings-alert-error{border-color:#f1c7c7;background:#fff5f5;color:#ad3030}

.settings-layout{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(285px,.75fr);gap:18px;align-items:start}
.settings-main,.settings-sidebar{display:grid;gap:18px}
.settings-panel{padding:0!important;overflow:hidden;border:1px solid #e6eaf1!important;border-radius:11px!important;background:#fff!important;box-shadow:0 1px 2px rgba(15,23,42,.02)!important}
.settings-panel-header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:0!important;padding:16px 18px;border-bottom:1px solid #edf0f5}
.settings-panel-header>div:first-child{min-width:0}
.settings-panel-header .admin-panel-eyebrow{display:block;margin-bottom:4px;color:#635bff;font-size:9px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.settings-panel-header h3{margin:0;color:#111827;font-size:14px;font-weight:800}
.settings-panel-header p{margin:4px 0 0;color:#8a93a4;font-size:10px;line-height:1.5}
.settings-panel-body{padding:18px}

.settings-grid{display:grid;grid-template-columns:minmax(0,1fr);gap:15px}
.settings-grid-two{grid-template-columns:repeat(2,minmax(0,1fr))}
.settings-field{min-width:0}
.settings-field-wide{grid-column:1/-1}
.settings-field label{display:block;margin-bottom:6px;color:#465064;font-size:10px;font-weight:800}
.settings-field label span{color:#d44b4b}
.settings-field input,.settings-field textarea{width:100%;box-sizing:border-box;border:1px solid #dfe4ec;border-radius:8px;background:#fff;color:#172033;font:inherit;font-size:11px;outline:none;transition:border-color .18s ease,box-shadow .18s ease,background .18s ease}
.settings-field input{height:38px;padding:0 11px}
.settings-field textarea{min-height:92px;padding:10px 11px;line-height:1.55;resize:vertical}
.settings-field input::placeholder,.settings-field textarea::placeholder{color:#a0a8b7}
.settings-field input:focus,.settings-field textarea:focus{border-color:#8c86ff;box-shadow:0 0 0 3px rgba(99,91,255,.09)}
.settings-help{display:block;margin-top:6px;color:#8a93a4;font-size:9px;line-height:1.5}
.settings-field-error{display:block;margin-top:5px;color:#c63f3f;font-size:9px;font-weight:700}

.settings-input-suffix{position:relative}
.settings-input-suffix input{padding-right:34px}
.settings-input-suffix>span{position:absolute;top:50%;right:11px;transform:translateY(-50%);color:#8a93a4;font-size:10px;font-weight:800}

.settings-toggle-card{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:18px;padding:13px 14px;border:1px solid #dfe4ec;border-radius:9px;background:#f9fafc;cursor:pointer}
.settings-toggle-copy{min-width:0}
.settings-toggle-copy strong{display:block;color:#172033;font-size:11px}
.settings-toggle-copy small{display:block;margin-top:3px;color:#7d8798;font-size:9px;line-height:1.5}
.settings-bank-fields{padding-top:2px}

.settings-options-list{padding:5px 18px!important}
.settings-option{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px 0;border-bottom:1px solid #edf0f5;cursor:pointer}
.settings-option:last-child{border-bottom:0}
.settings-option>span:first-child{min-width:0}
.settings-option strong{display:block;color:#172033;font-size:10px}
.settings-option small{display:block;margin-top:3px;color:#8a93a4;font-size:9px;line-height:1.4}

.settings-switch{position:relative;display:inline-flex;flex:0 0 auto}
.settings-switch input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
.settings-switch-track{position:relative;display:block;width:34px;height:19px;border-radius:999px;background:#cbd2dd;transition:background .18s ease;box-shadow:inset 0 0 0 1px rgba(15,23,42,.04)}
.settings-switch-track::after{content:"";position:absolute;top:3px;left:3px;width:13px;height:13px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(15,23,42,.2);transition:transform .18s ease}
.settings-switch input:checked+.settings-switch-track{background:#635bff}
.settings-switch input:checked+.settings-switch-track::after{transform:translateX(15px)}
.settings-switch input:focus-visible+.settings-switch-track{outline:3px solid rgba(99,91,255,.18);outline-offset:2px}

.settings-save-panel{display:grid;grid-template-columns:auto 1fr;gap:11px;padding:16px!important}
.settings-save-icon{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:9px;background:#eef0ff;color:#635bff;font-size:13px}
.settings-save-panel strong{display:block;color:#172033;font-size:11px}
.settings-save-panel p{margin:3px 0 0;color:#8a93a4;font-size:9px;line-height:1.5}
.settings-save-button{grid-column:1/-1;width:100%;justify-content:center;margin-top:3px}

@media(max-width:1100px){
    .settings-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .settings-layout{grid-template-columns:1fr}
    .settings-sidebar{grid-template-columns:repeat(2,minmax(0,1fr))}
    .settings-sidebar .settings-save-panel{grid-column:1/-1}
}
@media(max-width:700px){
    .settings-summary-grid,.settings-grid-two,.settings-sidebar{grid-template-columns:1fr}
    .settings-page-header{align-items:flex-start}
    .settings-page-header .admin-page-actions{width:100%}
    .settings-page-header .admin-page-actions .admin-button{flex:1;justify-content:center}
    .settings-panel-header{align-items:flex-start}
}
@media(max-width:480px){
    .settings-summary-grid{gap:10px}
    .settings-summary-grid .admin-stat-card{min-height:105px;padding:14px}
    .settings-panel-header,.settings-panel-body{padding:14px}
    .settings-toggle-card{align-items:flex-start}
}
</style>

@endsection
