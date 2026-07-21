@extends('layouts.app')

@section('title', 'Cart')

@section('content')

<div class="container">

    <h1>Cart</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('cart'))
        <div class="alert alert-danger">
            {{ $errors->first('cart') }}
        </div>
    @endif

    @php
        $subtotal = 0;
    @endphp

    <form
        action="{{ route('cart.update') }}"
        method="POST"
        id="cart-update-form"
    >
        @csrf

        @forelse ($cart as $cartKey => $item)

            @php
                $price = (float) ($item['price'] ?? 0);
                $quantity = (int) ($item['quantity'] ?? 1);
                $lineTotal = $price * $quantity;
                $subtotal += $lineTotal;

                $image = $item['image']
                    ?? 'asset/images/no-image.jpg';

                if (
                    !str_starts_with($image, 'http://')
                    && !str_starts_with($image, 'https://')
                ) {
                    $image = asset(
                        str_starts_with($image, 'storage/')
                            ? $image
                            : (
                                file_exists(public_path($image))
                                    ? $image
                                    : 'storage/' . ltrim($image, '/')
                            )
                    );
                }
            @endphp

            <div
                class="cart-item"
                data-cart-item
                data-cart-key="{{ $cartKey }}"
            >
                <a
                    href="{{ route('products.show', $item['slug']) }}"
                    class="cart-item-image"
                >
                    <img
                        src="{{ $image }}"
                        width="80"
                        alt="{{ $item['title'] }}"
                    >
                </a>

                <div class="cart-item-details">

                    <h3>
                        <a
                            href="{{ route('products.show', $item['slug']) }}"
                            class="text-decoration-none text-color-dark"
                        >
                            {{ $item['title'] }}
                        </a>
                    </h3>

                    @if (!empty($item['sku']))
                        <p>
                            SKU: {{ $item['sku'] }}
                        </p>
                    @endif

                    @if (!empty($item['options']))
                        <div class="cart-item-options">
                            @foreach ($item['options'] as $option)
                                <p>
                                    <strong>
                                        {{ $option['option_name'] }}:
                                    </strong>

                                    {{ $option['value_label'] }}
                                </p>
                            @endforeach
                        </div>
                    @endif

                    <p class="cart-item-price">
                        ${{ number_format($price, 2) }}
                    </p>

                    <div
                        class="quantity-box cart-quantity-box"
                        data-cart-quantity-wrapper
                    >
                        <button
                            type="button"
                            class="quantity-button cart-quantity-minus"
                            data-cart-quantity-minus
                            aria-label="Decrease quantity"
                        >
                            −
                        </button>

                        <input
                            type="number"
                            name="quantities[{{ $cartKey }}]"
                            class="cart-quantity-input"
                            value="{{ $quantity }}"
                            min="1"
                            max="{{ max(1, (int) ($item['stock'] ?? 1)) }}"
                            data-cart-key="{{ $cartKey }}"
                            data-update-url="{{ route('cart.update') }}"
                            aria-label="Product quantity"
                        >

                        <button
                            type="button"
                            class="quantity-button cart-quantity-plus"
                            data-cart-quantity-plus
                            aria-label="Increase quantity"
                        >
                            +
                        </button>
                    </div>

                    <p
                        class="cart-item-subtotal"
                        data-item-subtotal
                    >
                        Total:
                        <span data-item-subtotal-value>
                            ${{ number_format($lineTotal, 2) }}
                        </span>
                    </p>

                    <button
                        type="submit"
                        formaction="{{ route('cart.remove') }}"
                        name="cart_key"
                        value="{{ $cartKey }}"
                        class="cart-remove-button"
                        data-ajax="true"
                        data-cart-key="{{ $cartKey }}"
                        data-remove-url="{{ route('cart.remove') }}"
                    >
                        Remove
                    </button>

                </div>
            </div>

        @empty

            <div
                class="empty-cart"
                data-empty-cart
            >
                <p>Your cart is empty.</p>

                <a href="{{ route('products.index') }}">
                    Continue Shopping
                </a>
            </div>

        @endforelse

        @if (count($cart))
            <button
                type="submit"
                class="update-cart-button"
            >
                Update Cart
            </button>
        @endif

    </form>

    @if (count($cart))
        <div
            class="cart-summary"
            data-cart-summary
        >
            <h3>Cart Summary</h3>

            <p>
                Subtotal:

                <span data-cart-subtotal>
                    ${{ number_format($subtotal, 2) }}
                </span>
            </p>

            <p>
                Shipping:
                $0.00
            </p>

            <p>
                Total:

                <span data-cart-total>
                    ${{ number_format($subtotal, 2) }}
                </span>
            </p>

            <a href="{{ route('checkout.index') }}">
                Proceed To Checkout
            </a>
        </div>
    @endif

</div>

@endsection