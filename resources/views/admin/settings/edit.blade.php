@extends('layouts.app')

@section('title', 'E-commerce Settings')

@section('content')

<div class="page-wrapper">

    <div class="services">
        <div class="service-wrapper">

            <div class="container">

                <div class="admin-page-heading">
                    <div>
                        <h1>E-commerce Settings</h1>

                        <p>
                            Manage store, currency, tax, shipping and checkout settings.
                        </p>
                    </div>

                    <a href="{{ route('admin.dashboard') }}">
                        Back to Dashboard
                    </a>
                </div>

                @if (session('success'))
                    <div class="admin-alert admin-alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="admin-alert admin-alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{ route('admin.settings.update') }}"
                    method="POST"
                    class="admin-form"
                >
                    @csrf
                    @method('PUT')

                    <section class="admin-panel">

                        <h2>Store Information</h2>

                        <label for="store_name">
                            Store Name
                        </label>

                        <input
                            type="text"
                            name="store_name"
                            id="store_name"
                            value="{{ old(
                                'store_name',
                                $settings->store_name
                            ) }}"
                            required
                        >

                        <label for="store_email">
                            Store Email
                        </label>

                        <input
                            type="email"
                            name="store_email"
                            id="store_email"
                            value="{{ old(
                                'store_email',
                                $settings->store_email
                            ) }}"
                        >

                        <label for="store_phone">
                            Store Phone
                        </label>

                        <input
                            type="text"
                            name="store_phone"
                            id="store_phone"
                            value="{{ old(
                                'store_phone',
                                $settings->store_phone
                            ) }}"
                        >

                        <label for="store_address">
                            Store Address
                        </label>

                        <textarea
                            name="store_address"
                            id="store_address"
                            rows="4"
                        >{{ old(
                            'store_address',
                            $settings->store_address
                        ) }}</textarea>

                    </section>

                    <section class="admin-panel">

                        <h2>Currency and Orders</h2>

                        <label for="currency">
                            Currency Code
                        </label>

                        <input
                            type="text"
                            name="currency"
                            id="currency"
                            value="{{ old(
                                'currency',
                                $settings->currency
                            ) }}"
                            placeholder="USD"
                            required
                        >

                        <label for="currency_symbol">
                            Currency Symbol
                        </label>

                        <input
                            type="text"
                            name="currency_symbol"
                            id="currency_symbol"
                            value="{{ old(
                                'currency_symbol',
                                $settings->currency_symbol
                            ) }}"
                            placeholder="$"
                            required
                        >

                        <label for="order_prefix">
                            Order Prefix
                        </label>

                        <input
                            type="text"
                            name="order_prefix"
                            id="order_prefix"
                            value="{{ old(
                                'order_prefix',
                                $settings->order_prefix
                            ) }}"
                            placeholder="ORD"
                            required
                        >

                    </section>

                    <section class="admin-panel">

                        <h2>Tax and Shipping</h2>

                        <label for="tax_percentage">
                            Tax Percentage
                        </label>

                        <input
                            type="number"
                            name="tax_percentage"
                            id="tax_percentage"
                            value="{{ old(
                                'tax_percentage',
                                $settings->tax_percentage
                            ) }}"
                            min="0"
                            max="100"
                            step="0.01"
                            required
                        >

                        <label for="shipping_fee">
                            Standard Shipping Fee
                        </label>

                        <input
                            type="number"
                            name="shipping_fee"
                            id="shipping_fee"
                            value="{{ old(
                                'shipping_fee',
                                $settings->shipping_fee
                            ) }}"
                            min="0"
                            step="0.01"
                            required
                        >

                        <label for="free_shipping_threshold">
                            Free Shipping Threshold
                        </label>

                        <input
                            type="number"
                            name="free_shipping_threshold"
                            id="free_shipping_threshold"
                            value="{{ old(
                                'free_shipping_threshold',
                                $settings->free_shipping_threshold
                            ) }}"
                            min="0"
                            step="0.01"
                        >

                        <label for="low_stock_threshold">
                            Low Stock Threshold
                        </label>

                        <input
                            type="number"
                            name="low_stock_threshold"
                            id="low_stock_threshold"
                            value="{{ old(
                                'low_stock_threshold',
                                $settings->low_stock_threshold
                            ) }}"
                            min="0"
                            required
                        >

                    </section>

                    <section class="admin-panel">

                        <h2>Checkout Options</h2>

                        <label class="admin-checkbox">
                            <input
                                type="checkbox"
                                name="guest_checkout_enabled"
                                value="1"
                                @checked(
                                    old(
                                        'guest_checkout_enabled',
                                        $settings->guest_checkout_enabled
                                    )
                                )
                            >

                            Enable guest checkout
                        </label>

                        <label class="admin-checkbox">
                            <input
                                type="checkbox"
                                name="cash_on_delivery_enabled"
                                value="1"
                                @checked(
                                    old(
                                        'cash_on_delivery_enabled',
                                        $settings->cash_on_delivery_enabled
                                    )
                                )
                            >

                            Enable cash on delivery
                        </label>

                        <label class="admin-checkbox">
                            <input
                                type="checkbox"
                                name="stock_management_enabled"
                                value="1"
                                @checked(
                                    old(
                                        'stock_management_enabled',
                                        $settings->stock_management_enabled
                                    )
                                )
                            >

                            Enable stock management
                        </label>

                        <label class="admin-checkbox">
                            <input
                                type="checkbox"
                                name="maintenance_mode"
                                value="1"
                                @checked(
                                    old(
                                        'maintenance_mode',
                                        $settings->maintenance_mode
                                    )
                                )
                            >

                            Enable store maintenance mode
                        </label>

                    </section>

                    <section class="admin-panel">

                        <h2>Customer Messages</h2>

                        <label for="checkout_notice">
                            Checkout Notice
                        </label>

                        <textarea
                            name="checkout_notice"
                            id="checkout_notice"
                            rows="4"
                        >{{ old(
                            'checkout_notice',
                            $settings->checkout_notice
                        ) }}</textarea>

                        <label for="order_email_message">
                            Order Confirmation Message
                        </label>

                        <textarea
                            name="order_email_message"
                            id="order_email_message"
                            rows="5"
                        >{{ old(
                            'order_email_message',
                            $settings->order_email_message
                        ) }}</textarea>

                    </section>

                    <button type="submit">
                        Save E-commerce Settings
                    </button>

                </form>

            </div>

        </div>
    </div>

</div>

@endsection