<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Throwable;

class StripePaymentController extends Controller
{
    /**
     * Create the pending order and Stripe PaymentIntent.
     */
    public function createIntent(
        Request $request
    ): JsonResponse {
        if (
            !config(
                'payments.stripe.enabled',
                false
            )
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                'Stripe payments are currently unavailable.',
            ], 503);
        }

        $stripeSecret = trim(
            (string) config(
                'payments.stripe.secret'
            )
        );

        if (
            $stripeSecret === ''
            || str_contains(
                $stripeSecret,
                'your_secret'
            )
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                'Stripe has not been configured correctly.',
            ], 503);
        }

        $cart = session()->get(
            'cart',
            []
        );

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' =>
                'Your cart is empty.',
            ], 422);
        }

        /*
         * Revalidate inventory and prices immediately before
         * creating the Stripe order / PaymentIntent.
         *
         * The checkout page may have been left open while
         * inventory or pricing changed, and this endpoint can
         * also be called directly. Never trust the old session
         * cart without checking the current database state.
         */
        $cartGate =
            $this->validateCartForCheckout(
                $cart
            );

        if (!$cartGate['valid']) {
            session()->put(
                'cart',
                $cartGate['cart']
            );

            return response()->json([
                'success' => false,
                'message' =>
                $cartGate['message'],
                'redirect_url' =>
                route('cart.index'),
            ], 422);
        }

        $cart = $cartGate['cart'];

        session()->put(
            'cart',
            $cart
        );

        try {
            $validated =
                $this->validateCheckout(
                    $request
                );
        } catch (ValidationException $exception) {
            throw $exception;
        }

        $shipToDifferentAddress =
            $request->boolean(
                'ship_to_different_address'
            );

        $shippingDetails =
            $this->resolveShippingDetails(
                $validated,
                $shipToDifferentAddress
            );

        $deliveryCountry =
            $shippingDetails['country'];

        /*
         * Never trust prices or totals submitted
         * by the customer's browser.
         */
        $subtotal =
            $this->calculateSubtotal(
                $cart
            );

        $coupon = session()->get(
            'cart_coupon',
            []
        );

        $discount =
            $this->calculateCouponDiscount(
                $subtotal,
                $coupon
            );

        $shipping =
            $this->calculateShipping(
                $subtotal,
                $deliveryCountry
            );

        $tax = 0;

        $total =
            $this->calculateTotal(
                $subtotal,
                $discount,
                $shipping,
                $tax
            );

        if ($total <= 0) {
            return response()->json([
                'success' => false,
                'message' =>
                'The order total must be greater than zero.',
            ], 422);
        }

        $currency = strtolower(
            trim(
                (string) config(
                    'payments.currency',
                    'USD'
                )
            )
        );

        $amount =
            $this->convertToMinorUnit(
                $total,
                $currency
            );

        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' =>
                'The payment amount is invalid.',
            ], 422);
        }

        /*
         * Reuse the current unfinished Stripe attempt when the
         * checkout snapshot has not changed. This prevents a page
         * refresh from creating another local order / PaymentIntent.
         *
         * If the checkout details, cart, amount or currency changed,
         * the old unfinished attempt is cancelled and removed before
         * a fresh one is created.
         */
        $reusedAttempt = $this->reusePreviousPendingAttempt(
            cart: $cart,
            validated: $validated,
            shippingDetails: $shippingDetails,
            total: $total,
            currency: $currency,
            amount: $amount,
            stripeSecret: $stripeSecret
        );

        if ($reusedAttempt !== null) {
            return $reusedAttempt;
        }

        $this->discardPreviousPendingAttempt(
            $stripeSecret
        );

        try {
            $order = DB::transaction(
                function () use (
                    $cart,
                    $validated,
                    $subtotal,
                    $discount,
                    $shipping,
                    $tax,
                    $total,
                    $currency,
                    $coupon,
                    $shippingDetails
                ): Order {
                    $order = Order::create([
                        'user_id' =>
                        auth()->id(),

                        'order_number' =>
                        $this->generateOrderNumber(),

                        'tracking_number' =>
                        $this->generateTrackingNumber(),

                        'subtotal' =>
                        $subtotal,

                        'discount' =>
                        $discount,

                        'shipping' =>
                        $shipping,

                        'tax' =>
                        $tax,

                        'total' =>
                        $total,

                        'currency' =>
                        strtoupper(
                            $currency
                        ),

                        'coupon_code' =>
                        $coupon['code']
                            ?? null,

                        'payment_method' =>
                        'stripe',

                        'payment_provider' =>
                        'stripe',

                        'payment_reference' =>
                        null,

                        'payment_intent_id' =>
                        null,

                        'payment_status' =>
                        'pending',

                        'order_status' =>
                        'pending',

                        'paid_at' =>
                        null,

                        'payment_failed_at' =>
                        null,

                        'payment_failure_message' =>
                        null,

                        'payment_metadata' => [
                            'provider' =>
                            'stripe',

                            'created_from' =>
                            'checkout',
                        ],

                        'billing_name' =>
                        $validated['billing_name'],

                        'billing_email' =>
                        $validated['billing_email'],

                        'billing_phone' =>
                        $validated['billing_phone'],

                        'billing_address' =>
                        $validated['billing_address'],

                        'billing_country' =>
                        strtoupper(
                            $validated['billing_country']
                        ),

                        'billing_state' =>
                        $validated['billing_state'],

                        'billing_city' =>
                        $validated['billing_city'],

                        'billing_zip' =>
                        $validated['billing_zip'],

                        'shipping_name' =>
                        $shippingDetails['name'],

                        'shipping_email' =>
                        $shippingDetails['email'],

                        'shipping_phone' =>
                        $shippingDetails['phone'],

                        'shipping_address' =>
                        $shippingDetails['address'],

                        'shipping_country' =>
                        strtoupper(
                            $shippingDetails['country']
                        ),

                        'shipping_state' =>
                        $shippingDetails['state'],

                        'shipping_city' =>
                        $shippingDetails['city'],

                        'shipping_zip' =>
                        $shippingDetails['zip'],

                        'order_notes' =>
                        $validated['order_notes']
                            ?? null,
                    ]);

                    $this->createOrderItems(
                        $order,
                        $cart
                    );

                    return $order;
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' =>
                'The order could not be created. Please try again.',
            ], 500);
        }

        try {
            $stripe =
                new StripeClient(
                    $stripeSecret
                );

            $paymentIntent =
                $stripe
                ->paymentIntents
                ->create([
                    'amount' =>
                    $amount,

                    'currency' =>
                    $currency,

                    /*
                         * Card payments also allow supported
                         * Apple Pay and Google Pay wallets.
                         */
                    'payment_method_types' => [
                        'card',
                    ],

                    'receipt_email' =>
                    $validated['billing_email'],

                    'description' =>
                    'Arizona Outfits order '
                        . $order->order_number,

                    'metadata' => [
                        'order_id' =>
                        (string) $order->id,

                        'order_number' =>
                        $order->order_number,

                        'customer_email' =>
                        $validated['billing_email'],

                        'payment_method' =>
                        'stripe',
                    ],

                    'shipping' => [
                        'name' =>
                        $shippingDetails['name'],

                        'phone' =>
                        $shippingDetails['phone'],

                        'address' => [
                            'line1' =>
                            $shippingDetails['address'],

                            'city' =>
                            $shippingDetails['city'],

                            'state' =>
                            $shippingDetails['state'],

                            'postal_code' =>
                            $shippingDetails['zip'],

                            'country' =>
                            strtoupper(
                                $shippingDetails['country']
                            ),
                        ],
                    ],
                ], [
                    /*
                         * Prevent accidental duplicate intents
                         * if the same request is retried.
                         */
                    'idempotency_key' =>
                    'order_'
                        . $order->id
                        . '_'
                        . $order->order_number,
                ]);

            /*
             * The verified Stripe webhook is the only code path allowed to
             * finalize a Stripe order as paid. A PaymentIntent can already be
             * "succeeded" by the time this response is returned, but that must
             * not manufacture a local paid state without paid_at, inventory
             * deduction and order-notification processing.
             *
             * Refresh first because the webhook may also have completed while
             * this request was waiting for Stripe. Never downgrade that
             * authoritative paid state.
             */
            $order->refresh();

            $localPaymentStatus =
                $order->payment_status === 'paid'
                    ? 'paid'
                    : $this->mapStripeStatus(
                        $paymentIntent->status
                    );

            $order->update([
                'payment_reference' =>
                $paymentIntent->id,

                'payment_intent_id' =>
                $paymentIntent->id,

                'payment_status' =>
                $localPaymentStatus,

                'payment_metadata' => [
                    'provider' =>
                    'stripe',

                    'created_from' =>
                    'checkout',

                    'stripe_status' =>
                    $paymentIntent->status,

                    'amount' =>
                    $paymentIntent->amount,

                    'currency' =>
                    $paymentIntent->currency,
                ],
            ]);

            session()->put(
                'stripe_pending_order_id',
                $order->id
            );

            session()->put(
                'stripe_pending_order_number',
                $order->order_number
            );

            return response()->json([
                'success' => true,

                'client_secret' =>
                $paymentIntent
                    ->client_secret,

                'payment_intent_id' =>
                $paymentIntent->id,

                'order_number' =>
                $order->order_number,

                'return_url' =>
                route(
                    'checkout.stripe.return'
                ),
            ]);
        } catch (ApiErrorException $exception) {
            report($exception);

            $order->update([
                'payment_status' =>
                'failed',

                'payment_failed_at' =>
                now(),

                'payment_failure_message' =>
                $exception->getMessage(),

                'payment_metadata' => [
                    'provider' =>
                    'stripe',

                    'created_from' =>
                    'checkout',

                    'error_type' =>
                    get_class(
                        $exception
                    ),
                ],
            ]);

            return response()->json([
                'success' => false,
                'message' =>
                $this->safeStripeMessage(
                    $exception
                ),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            $order->update([
                'payment_status' =>
                'failed',

                'payment_failed_at' =>
                now(),

                'payment_failure_message' =>
                $exception->getMessage(),

                'payment_metadata' => [
                    'provider' =>
                    'stripe',

                    'created_from' =>
                    'checkout',

                    'error_type' =>
                    get_class(
                        $exception
                    ),
                ],
            ]);

            return response()->json([
                'success' => false,
                'message' =>
                'Stripe could not initialize the payment. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle the browser return from Stripe.
     *
     * The webhook remains the authoritative payment
     * confirmation, but this method gives the customer
     * an immediate redirect after checkout.
     */
    public function paymentReturn(
        Request $request
    ): RedirectResponse {
        $paymentIntentId = trim(
            (string) $request->query(
                'payment_intent',
                ''
            )
        );

        if ($paymentIntentId === '') {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Stripe did not return a valid payment reference.'
                );
        }

        $order = Order::query()
            ->where(
                'payment_intent_id',
                $paymentIntentId
            )
            ->where(
                'payment_provider',
                'stripe'
            )
            ->first();

        if (!$order) {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'The Stripe order could not be found.'
                );
        }

        $stripeSecret = trim(
            (string) config(
                'payments.stripe.secret'
            )
        );

        try {
            $stripe =
                new StripeClient(
                    $stripeSecret
                );

            $paymentIntent =
                $stripe
                ->paymentIntents
                ->retrieve(
                    $paymentIntentId,
                    []
                );

            $metadataOrderId =
                (string) (
                    $paymentIntent
                    ->metadata
                    ->order_id
                    ?? ''
                );

            if (
                $metadataOrderId !== ''
                && $metadataOrderId
                !== (string) $order->id
            ) {
                abort(403);
            }

            if (
                $paymentIntent->status === 'succeeded'
            ) {
                /*
     * The Stripe webhook is responsible for:
     * - verifying the payment
     * - deducting inventory
     * - marking the order as paid
     *
     * This return endpoint only redirects the customer.
     */

                session()->forget([
                    'cart',
                    'cart_coupon',
                    'stripe_pending_order_id',
                    'stripe_pending_order_number',
                ]);

                session()->put(
                    'recent_order_number',
                    $order->order_number
                );

                return redirect()->route(
                    'checkout.thankyou',
                    [
                        'order_number' => $order->order_number,
                    ]
                );
            }

            if (
                in_array(
                    $paymentIntent->status,
                    [
                        'processing',
                        'requires_capture',
                    ],
                    true
                )
            ) {
                /*
                 * The webhook may already have finalized this order
                 * before the browser reaches the return URL.
                 * Never downgrade an already-paid local order.
                 */
                $order->refresh();

                if ($order->payment_status !== 'paid') {
                    $order->update([
                        'payment_status' =>
                        'processing',

                        'payment_metadata' => array_merge(
                            is_array($order->payment_metadata)
                                ? $order->payment_metadata
                                : [],
                            [
                                'provider' =>
                                'stripe',

                                'stripe_status' =>
                                $paymentIntent->status,
                            ]
                        ),
                    ]);
                }

                session()->put(
                    'recent_order_number',
                    $order->order_number
                );

                return redirect()
                    ->route(
                        'checkout.thankyou',
                        [
                            'order_number' =>
                            $order
                                ->order_number,
                        ]
                    )
                    ->with(
                        'success',
                        $order->payment_status === 'paid'
                            ? 'Your payment has been confirmed.'
                            : 'Your payment is being processed.'
                    );
            }

            $failureMessage =
                $paymentIntent
                ->last_payment_error
                ?->message
                ?? 'The card payment was not completed.';

            /*
             * A Stripe webhook can arrive before this browser return.
             * If it has already confirmed the order as paid, this
             * endpoint must never overwrite that authoritative state
             * with a stale/failed browser-return status.
             */
            $order->refresh();

            if ($order->payment_status === 'paid') {
                session()->forget([
                    'cart',
                    'cart_coupon',
                    'stripe_pending_order_id',
                    'stripe_pending_order_number',
                ]);

                session()->put(
                    'recent_order_number',
                    $order->order_number
                );

                return redirect()->route(
                    'checkout.thankyou',
                    [
                        'order_number' =>
                        $order->order_number,
                    ]
                );
            }

            $order->update([
                'payment_status' =>
                'failed',

                'payment_failed_at' =>
                now(),

                'payment_failure_message' =>
                $failureMessage,

                'payment_metadata' => array_merge(
                    is_array($order->payment_metadata)
                        ? $order->payment_metadata
                        : [],
                    [
                        'provider' =>
                        'stripe',

                        'stripe_status' =>
                        $paymentIntent->status,
                    ]
                ),
            ]);

            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    $failureMessage
                );
        } catch (ApiErrorException $exception) {
            report($exception);

            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'Stripe could not verify the payment. Please try again.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('checkout.index')
                ->with(
                    'error',
                    'The payment status could not be verified.'
                );
        }
    }

    /**
     * Validate Stripe checkout fields.
     */
    private function validateCheckout(
        Request $request
    ): array {
        return $request->validate([
            'billing_name' => [
                'required',
                'string',
                'max:255',
            ],

            'billing_email' => [
                'required',
                'email',
                'max:255',
            ],

            'billing_phone' => [
                'required',
                'string',
                'max:50',
            ],

            'billing_address' => [
                'required',
                'string',
                'max:1000',
            ],

            'billing_country' => [
                'required',
                'string',
                'size:2',
            ],

            'billing_state' => [
                'required',
                'string',
                'max:255',
            ],

            'billing_city' => [
                'required',
                'string',
                'max:255',
            ],

            'billing_zip' => [
                'required',
                'string',
                'max:30',
            ],

            'ship_to_different_address' => [
                'nullable',
                'boolean',
            ],

            'shipping_name' => [
                'required_if:ship_to_different_address,1',
                'nullable',
                'string',
                'max:255',
            ],

            'shipping_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'shipping_phone' => [
                'required_if:ship_to_different_address,1',
                'nullable',
                'string',
                'max:50',
            ],

            'shipping_address' => [
                'required_if:ship_to_different_address,1',
                'nullable',
                'string',
                'max:1000',
            ],

            'shipping_country' => [
                'required_if:ship_to_different_address,1',
                'nullable',
                'string',
                'size:2',
            ],

            'shipping_state' => [
                'required_if:ship_to_different_address,1',
                'nullable',
                'string',
                'max:255',
            ],

            'shipping_city' => [
                'required_if:ship_to_different_address,1',
                'nullable',
                'string',
                'max:255',
            ],

            'shipping_zip' => [
                'required_if:ship_to_different_address,1',
                'nullable',
                'string',
                'max:30',
            ],

            'order_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'payment_method' => [
                'required',
                'string',
                'in:stripe',
            ],

            'terms' => [
                'accepted',
            ],
        ]);
    }

    /**
     * Resolve the actual shipping address.
     */
    private function resolveShippingDetails(
        array $validated,
        bool $shipToDifferentAddress
    ): array {
        if ($shipToDifferentAddress) {
            return [
                'name' =>
                $validated['shipping_name'],

                'email' =>
                $validated['shipping_email']
                    ?? $validated['billing_email'],

                'phone' =>
                $validated['shipping_phone'],

                'address' =>
                $validated['shipping_address'],

                'country' =>
                $validated['shipping_country'],

                'state' =>
                $validated['shipping_state'],

                'city' =>
                $validated['shipping_city'],

                'zip' =>
                $validated['shipping_zip'],
            ];
        }

        return [
            'name' =>
            $validated['billing_name'],

            'email' =>
            $validated['billing_email'],

            'phone' =>
            $validated['billing_phone'],

            'address' =>
            $validated['billing_address'],

            'country' =>
            $validated['billing_country'],

            'state' =>
            $validated['billing_state'],

            'city' =>
            $validated['billing_city'],

            'zip' =>
            $validated['billing_zip'],
        ];
    }

    /**
     * Create order items from the current cart.
     */
    private function createOrderItems(
        Order $order,
        array $cart
    ): void {
        foreach ($cart as $item) {
            $quantity = max(
                1,
                (int) (
                    $item['quantity']
                    ?? 1
                )
            );

            $price = max(
                0,
                (float) (
                    $item['price']
                    ?? 0
                )
            );

            $lineTotal = round(
                $price * $quantity,
                2
            );

            OrderItem::create([
                'order_id' =>
                $order->id,

                'product_id' =>
                $item['product_id']
                    ?? null,

                'variant_id' =>
                $item['variant_id']
                    ?? null,

                'product_title' =>
                $item['title']
                    ?? 'Product',

                'sku' =>
                $item['sku']
                    ?? null,

                'price' =>
                $price,

                'quantity' =>
                $quantity,

                'options' =>
                is_array(
                    $item['options']
                        ?? null
                )
                    ? $item['options']
                    : [],

                'total' =>
                $lineTotal,
            ]);
        }
    }


    /**
     * Revalidate the complete cart immediately before Stripe.
     *
     * This independently protects /stripe/create-intent from stale
     * session data, direct requests, changed stock, removed variants,
     * inactive products, and changed prices.
     */
    private function validateCartForCheckout(array $cart): array
    {
        $refreshedCart = $cart;
        $problems = [];

        foreach ($refreshedCart as $cartKey => &$item) {
            $productId = (int) ($item['product_id'] ?? 0);

            $product = Product::query()
                ->whereKey($productId)
                ->where('status', 'active')
                ->first();

            if (!$product) {
                $item['stock'] = 0;
                $item['unavailable'] = true;
                $item['unavailable_reason'] =
                    'This product is no longer available.';

                $problems[] =
                    ($item['title'] ?? 'A product')
                    . ' is no longer available.';

                continue;
            }

            $variant = null;
            $variantId = (int) ($item['variant_id'] ?? 0);

            if ($variantId > 0) {
                $variant = ProductVariant::query()
                    ->whereKey($variantId)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$variant) {
                    $item['stock'] = 0;
                    $item['unavailable'] = true;
                    $item['unavailable_reason'] =
                        'The selected product variation is no longer available.';

                    $problems[] =
                        $product->title
                        . ': the selected variation is no longer available.';

                    continue;
                }
            }

            $availableStock = $variant
                ? (int) $variant->stock
                : (int) $product->stock;

            $item['stock'] = max(0, $availableStock);

            if ($availableStock < 1) {
                $item['unavailable'] = true;
                $item['unavailable_reason'] =
                    'This item is currently out of stock.';

                $problems[] =
                    $product->title
                    . ' is currently out of stock.';

                continue;
            }

            $quantity = max(
                1,
                (int) ($item['quantity'] ?? 1)
            );

            if ($quantity > $availableStock) {
                $item['quantity'] = $availableStock;

                $problems[] =
                    $product->title
                    . ' now has only '
                    . $availableStock
                    . ' available. Its cart quantity was adjusted.';
            } else {
                $item['quantity'] = $quantity;
            }

            unset(
                $item['unavailable'],
                $item['unavailable_reason']
            );

            $regularPrice = $variant
                ? (
                    $variant->regular_price !== null
                        ? (float) $variant->regular_price
                        : (float) $product->regular_price
                )
                : (float) $product->regular_price;

            $salePrice = $variant
                ? (
                    $variant->sale_price !== null
                        ? (float) $variant->sale_price
                        : null
                )
                : (
                    $product->sale_price !== null
                        ? (float) $product->sale_price
                        : null
                );

            $price = (
                $salePrice !== null
                && $salePrice < $regularPrice
            )
                ? $salePrice
                : $regularPrice;

            if (
                round((float) ($item['price'] ?? 0), 2)
                !== round($price, 2)
            ) {
                $problems[] =
                    $product->title
                    . ' has a new price. Please review your cart before checkout.';
            }

            $item['title'] = $product->title;
            $item['slug'] = $product->slug;
            $item['sku'] = $variant?->sku ?: $product->sku;
            $item['image'] = (
                $variant
                && !empty($variant->image)
            )
                ? $variant->image
                : $product->featured_image;
            $item['regular_price'] = $regularPrice;
            $item['sale_price'] = $salePrice;
            $item['price'] = $price;
        }

        unset($item);

        $problems = array_values(
            array_unique(
                array_filter($problems)
            )
        );

        return [
            'valid' => empty($problems),
            'cart' => $refreshedCart,
            'message' => empty($problems)
                ? ''
                : implode(' ', $problems),
        ];
    }

    /**
     * Reuse the current pending Stripe attempt when the complete
     * checkout snapshot still matches.
     *
     * A browser refresh can initialize the Payment Element again.
     * Returning the existing client secret keeps one local order and
     * one PaymentIntent instead of creating duplicates.
     */
    private function reusePreviousPendingAttempt(
        array $cart,
        array $validated,
        array $shippingDetails,
        float $total,
        string $currency,
        int $amount,
        string $stripeSecret
    ): ?JsonResponse {
        $previousOrderId = session(
            'stripe_pending_order_id'
        );

        if (!$previousOrderId) {
            return null;
        }

        $previousOrder = Order::query()
            ->with('items')
            ->whereKey($previousOrderId)
            ->where('payment_provider', 'stripe')
            ->where('payment_status', 'pending')
            ->first();

        if (
            !$previousOrder
            || empty($previousOrder->payment_intent_id)
        ) {
            return null;
        }

        if (
            !$this->pendingOrderMatchesCheckout(
                order: $previousOrder,
                cart: $cart,
                validated: $validated,
                shippingDetails: $shippingDetails,
                total: $total,
                currency: $currency
            )
        ) {
            return null;
        }

        try {
            $stripe = new StripeClient(
                $stripeSecret
            );

            $paymentIntent = $stripe
                ->paymentIntents
                ->retrieve(
                    $previousOrder->payment_intent_id,
                    []
                );

            if (
                !in_array(
                    $paymentIntent->status,
                    [
                        'requires_payment_method',
                        'requires_confirmation',
                        'requires_action',
                    ],
                    true
                )
            ) {
                return null;
            }

            if (
                (int) $paymentIntent->amount !== $amount
                || strtolower(
                    (string) $paymentIntent->currency
                ) !== strtolower($currency)
            ) {
                return null;
            }

            session()->put(
                'stripe_pending_order_id',
                $previousOrder->id
            );

            session()->put(
                'stripe_pending_order_number',
                $previousOrder->order_number
            );

            return response()->json([
                'success' => true,
                'client_secret' =>
                    $paymentIntent->client_secret,
                'payment_intent_id' =>
                    $paymentIntent->id,
                'order_number' =>
                    $previousOrder->order_number,
                'return_url' =>
                    route(
                        'checkout.stripe.return'
                    ),
                'reused' => true,
            ]);
        } catch (Throwable $exception) {
            /*
             * Do not let a stale/unavailable previous intent block
             * checkout. The normal cleanup path below will remove it
             * before a fresh attempt is created.
             */
            report($exception);

            return null;
        }
    }

    /**
     * Compare the pending order against the current authoritative
     * checkout snapshot.
     */
    private function pendingOrderMatchesCheckout(
        Order $order,
        array $cart,
        array $validated,
        array $shippingDetails,
        float $total,
        string $currency
    ): bool {
        if (
            round((float) $order->total, 2)
                !== round($total, 2)
            || strtoupper((string) $order->currency)
                !== strtoupper($currency)
        ) {
            return false;
        }

        $fields = [
            'billing_name' =>
                $validated['billing_name'],
            'billing_email' =>
                $validated['billing_email'],
            'billing_phone' =>
                $validated['billing_phone'],
            'billing_address' =>
                $validated['billing_address'],
            'billing_country' =>
                strtoupper(
                    $validated['billing_country']
                ),
            'billing_state' =>
                $validated['billing_state'],
            'billing_city' =>
                $validated['billing_city'],
            'billing_zip' =>
                $validated['billing_zip'],
            'shipping_name' =>
                $shippingDetails['name'],
            'shipping_email' =>
                $shippingDetails['email'],
            'shipping_phone' =>
                $shippingDetails['phone'],
            'shipping_address' =>
                $shippingDetails['address'],
            'shipping_country' =>
                strtoupper(
                    $shippingDetails['country']
                ),
            'shipping_state' =>
                $shippingDetails['state'],
            'shipping_city' =>
                $shippingDetails['city'],
            'shipping_zip' =>
                $shippingDetails['zip'],
            'order_notes' =>
                $validated['order_notes']
                    ?? null,
        ];

        foreach ($fields as $field => $value) {
            if (
                trim((string) $order->{$field})
                !== trim((string) $value)
            ) {
                return false;
            }
        }

        $orderItems = $order->items
            ->map(
                fn ($item): array => [
                    'product_id' =>
                        (int) ($item->product_id ?? 0),
                    'variant_id' =>
                        (int) ($item->variant_id ?? 0),
                    'quantity' =>
                        (int) $item->quantity,
                    'price' =>
                        round(
                            (float) $item->price,
                            2
                        ),
                ]
            )
            ->sortBy(
                fn (array $item): string =>
                    $item['product_id']
                    . ':'
                    . $item['variant_id']
                    . ':'
                    . $item['price']
            )
            ->values()
            ->all();

        $cartItems = collect($cart)
            ->map(
                fn (array $item): array => [
                    'product_id' =>
                        (int) (
                            $item['product_id']
                            ?? 0
                        ),
                    'variant_id' =>
                        (int) (
                            $item['variant_id']
                            ?? 0
                        ),
                    'quantity' =>
                        max(
                            1,
                            (int) (
                                $item['quantity']
                                ?? 1
                            )
                        ),
                    'price' =>
                        round(
                            (float) (
                                $item['price']
                                ?? 0
                            ),
                            2
                        ),
                ]
            )
            ->sortBy(
                fn (array $item): string =>
                    $item['product_id']
                    . ':'
                    . $item['variant_id']
                    . ':'
                    . $item['price']
            )
            ->values()
            ->all();

        return $orderItems === $cartItems;
    }

    /**
     * Remove an earlier unfinished Stripe attempt.
     *
     * If Stripe already has a PaymentIntent for it, cancel that
     * intent first so an old browser tab cannot later pay an order
     * that Laravel has removed.
     */
    private function discardPreviousPendingAttempt(
        string $stripeSecret
    ): void {
        $previousOrderId = session(
            'stripe_pending_order_id'
        );

        if (!$previousOrderId) {
            return;
        }

        $previousOrder = Order::query()
            ->whereKey($previousOrderId)
            ->where('payment_provider', 'stripe')
            ->where('payment_status', 'pending')
            ->first();

        if ($previousOrder) {
            $canDelete = true;

            if (
                !empty(
                    $previousOrder
                        ->payment_intent_id
                )
            ) {
                try {
                    $stripe = new StripeClient(
                        $stripeSecret
                    );

                    $paymentIntent = $stripe
                        ->paymentIntents
                        ->retrieve(
                            $previousOrder
                                ->payment_intent_id,
                            []
                        );

                    if (
                        in_array(
                            $paymentIntent->status,
                            [
                                'requires_payment_method',
                                'requires_confirmation',
                                'requires_action',
                            ],
                            true
                        )
                    ) {
                        $stripe
                            ->paymentIntents
                            ->cancel(
                                $paymentIntent->id,
                                []
                            );
                    } elseif (
                        in_array(
                            $paymentIntent->status,
                            [
                                'succeeded',
                                'processing',
                                'requires_capture',
                            ],
                            true
                        )
                    ) {
                        /*
                         * Never delete an order whose Stripe payment
                         * has already succeeded or is processing.
                         */
                        $canDelete = false;
                    }
                } catch (Throwable $exception) {
                    report($exception);

                    /*
                     * Keep the local order if Stripe could not confirm
                     * that the previous intent is safe to discard.
                     */
                    $canDelete = false;
                }
            }

            if ($canDelete) {
                $previousOrder->delete();
            }
        }

        session()->forget([
            'stripe_pending_order_id',
            'stripe_pending_order_number',
        ]);
    }

    /**
     * Calculate cart subtotal.
     */
    private function calculateSubtotal(
        array $cart
    ): float {
        return round(
            collect($cart)->sum(
                function ($item): float {
                    $price = max(
                        0,
                        (float) (
                            $item['price']
                            ?? 0
                        )
                    );

                    $quantity = max(
                        1,
                        (int) (
                            $item['quantity']
                            ?? 1
                        )
                    );

                    return $price
                        * $quantity;
                }
            ),
            2
        );
    }

    /**
     * Calculate coupon discount.
     */
    private function calculateCouponDiscount(
        float $subtotal,
        array $coupon
    ): float {
        if (empty($coupon)) {
            return 0;
        }

        if (
            isset(
                $coupon['discount']
            )
            && (float) $coupon['discount'] > 0
        ) {
            return round(
                min(
                    $subtotal,
                    (float) $coupon['discount']
                ),
                2
            );
        }

        $type =
            $coupon['type']
            ?? null;

        $value = max(
            0,
            (float) (
                $coupon['value']
                ?? 0
            )
        );

        $discount = match ($type) {
            'percentage' =>
            $subtotal
                * (
                    min(
                        100,
                        $value
                    ) / 100
                ),

            'fixed' =>
            $value,

            default =>
            0,
        };

        return round(
            min(
                $subtotal,
                $discount
            ),
            2
        );
    }

    /**
     * Calculate shipping from country code.
     */
    private function calculateShipping(
        float $subtotal,
        ?string $countryCode = null
    ): float {
        if ($subtotal >= 400) {
            return 0;
        }

        $countryCode = strtoupper(
            trim(
                $countryCode
                    ?? ''
            )
        );

        return match ($countryCode) {
            'PK' => 5,
            'US' => 10,
            'CA' => 15,
            'GB' => 18,
            default => 25,
        };
    }

    /**
     * Calculate final order total.
     */
    private function calculateTotal(
        float $subtotal,
        float $discount,
        float $shipping,
        float $tax
    ): float {
        return round(
            max(
                0,
                $subtotal
                    - $discount
                    + $shipping
                    + $tax
            ),
            2
        );
    }

    /**
     * Convert the total into Stripe's minor unit.
     */
    private function convertToMinorUnit(
        float $amount,
        string $currency
    ): int {
        $zeroDecimalCurrencies = [
            'bif',
            'clp',
            'djf',
            'gnf',
            'jpy',
            'kmf',
            'krw',
            'mga',
            'pyg',
            'rwf',
            'ugx',
            'vnd',
            'vuv',
            'xaf',
            'xof',
            'xpf',
        ];

        if (
            in_array(
                strtolower($currency),
                $zeroDecimalCurrencies,
                true
            )
        ) {
            return (int) round(
                $amount
            );
        }

        return (int) round(
            $amount * 100
        );
    }

    /**
     * Convert Stripe status into local status.
     */
    private function mapStripeStatus(
        string $status
    ): string {
        return match ($status) {
            /*
             * "succeeded" is intentionally kept as processing here.
             * Only StripeWebhookController may promote the local order
             * to paid after signature, amount and currency verification.
             */
            'succeeded' =>
            'processing',

            'processing',
            'requires_capture' =>
            'processing',

            'canceled' =>
            'cancelled',

            default =>
            'pending',
        };
    }

    /**
     * Return a customer-safe Stripe error.
     */
    private function safeStripeMessage(
        ApiErrorException $exception
    ): string {
        $stripeError =
            $exception->getError();

        $message =
            $stripeError?->message;

        if (
            is_string($message)
            && trim($message) !== ''
        ) {
            return $message;
        }

        return 'Stripe could not initialize the payment. Please check your payment details and try again.';
    }

    /**
     * Generate unique order number.
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber =
                'AO-'
                . strtoupper(
                    Str::random(10)
                );
        } while (
            Order::query()
            ->where(
                'order_number',
                $orderNumber
            )
            ->exists()
        );

        return $orderNumber;
    }

    /**
     * Generate tracking reference.
     */
    private function generateTrackingNumber(): string
    {
        return 'TRK-'
            . strtoupper(
                Str::random(12)
            );
    }
}
