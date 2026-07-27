<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function index(): View|RedirectResponse
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'error',
                    'Your cart is empty.'
                );
        }

        $subtotal = $this->calculateSubtotal(
            $cart
        );

        $coupon = session()->get(
            'cart_coupon',
            []
        );

        $discount = $this->calculateCouponDiscount(
            $subtotal,
            $coupon
        );

        /*
         * Initial international shipping estimate.
         *
         * This amount is updated when the customer
         * selects their delivery country.
         */
        $shipping = $this->calculateShipping(
            $subtotal
        );

        $tax = 0;

        $total = $this->calculateTotal(
            $subtotal,
            $discount,
            $shipping,
            $tax
        );

        $currency = strtoupper(
            config(
                'payments.currency',
                'USD'
            )
        );

        return view(
            'checkout.index',
            compact(
                'cart',
                'subtotal',
                'discount',
                'shipping',
                'tax',
                'total',
                'currency'
            )
        );
    }

    /**
     * Return a live shipping quotation.
     */
    public function shippingQuote(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'country_code' => [
                'required',
                'string',
                'size:2',
            ],
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.',
            ], 422);
        }

        $subtotal = $this->calculateSubtotal(
            $cart
        );

        $coupon = session()->get(
            'cart_coupon',
            []
        );

        $discount = $this->calculateCouponDiscount(
            $subtotal,
            $coupon
        );

        $shipping = $this->calculateShipping(
            $subtotal,
            $validated['country_code']
        );

        $tax = 0;

        $total = $this->calculateTotal(
            $subtotal,
            $discount,
            $shipping,
            $tax
        );

        return response()->json([
            'success' => true,

            'subtotal' => round(
                $subtotal,
                2
            ),

            'discount' => round(
                $discount,
                2
            ),

            'shipping' => round(
                $shipping,
                2
            ),

            'tax' => round(
                $tax,
                2
            ),

            'total' => round(
                $total,
                2
            ),

            'currency' => strtoupper(
                config(
                    'payments.currency',
                    'USD'
                )
            ),

            'formatted_shipping' =>
            $shipping > 0
                ? '$' . number_format(
                    $shipping,
                    2
                )
                : 'Free',

            'formatted_total' =>
            '$' . number_format(
                $total,
                2
            ),
        ]);
    }

    /**
     * Place a non-Stripe checkout order.
     *
     * Stripe orders are created through
     * StripePaymentController.
     */
    public function placeOrder(
        Request $request
    ): RedirectResponse {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'error',
                    'Your cart is empty.'
                );
        }

        $validated = $this->validateCheckout(
            $request
        );

        /*
         * Stripe checkout is handled through JavaScript
         * and StripePaymentController.
         *
         * This fallback prevents a Stripe order from being
         * created without an actual payment attempt.
         */
        if (
            $validated['payment_method']
            === 'stripe'
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The card payment form was not initialized. Please refresh the page and try again.'
                );
        }

        /*
         * Only direct bank transfer can reach this point.
         */
        if (
            $validated['payment_method']
            !== 'bank_transfer'
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'The selected payment method is unavailable.'
                );
        }

        $shipToDifferentAddress =
            $request->boolean(
                'ship_to_different_address'
            );

        $deliveryCountry =
            $shipToDifferentAddress
            ? $validated['shipping_country']
            : $validated['billing_country'];

        /*
         * Never trust totals submitted by the browser.
         * Calculate everything again from the session cart.
         */
        $subtotal = $this->calculateSubtotal(
            $cart
        );

        $coupon = session()->get(
            'cart_coupon',
            []
        );

        $discount = $this->calculateCouponDiscount(
            $subtotal,
            $coupon
        );

        $shipping = $this->calculateShipping(
            $subtotal,
            $deliveryCountry
        );

        $tax = 0;

        $total = $this->calculateTotal(
            $subtotal,
            $discount,
            $shipping,
            $tax
        );

        $shippingDetails =
            $this->resolveShippingDetails(
                $validated,
                $shipToDifferentAddress
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
                    $coupon,
                    $shippingDetails
                ): Order {
                    $order = Order::create([
                        'user_id' => auth()->id(),

                        'order_number' =>
                        $this->generateOrderNumber(),

                        'tracking_number' =>
                        $this->generateTrackingNumber(),

                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'shipping' => $shipping,
                        'tax' => $tax,
                        'total' => $total,

                        'currency' => strtoupper(
                            config(
                                'payments.currency',
                                'USD'
                            )
                        ),

                        'coupon_code' =>
                        $coupon['code']
                            ?? null,

                        'payment_method' =>
                        'bank_transfer',

                        'payment_provider' =>
                        'bank_transfer',

                        'payment_reference' =>
                        null,

                        'payment_intent_id' =>
                        null,

                        /*
                         * Bank-transfer orders remain pending
                         * until payment is manually verified.
                         */
                        'payment_status' =>
                        'pending',

                        'order_status' =>
                        'pending',

                        'paid_at' => null,

                        'payment_failed_at' =>
                        null,

                        'payment_failure_message' =>
                        null,

                        'payment_metadata' => [
                            'method' =>
                            'bank_transfer',

                            'bank_name' =>
                            config(
                                'payments.bank_transfer.bank_name'
                            ),

                            'account_name' =>
                            config(
                                'payments.bank_transfer.account_name'
                            ),
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

            return back()
                ->withInput()
                ->with(
                    'error',
                    'The order could not be placed. Please try again.'
                );
        }

        /*
         * Bank-transfer order creation is complete.
         * The cart can now safely be cleared.
         */
        session()->forget([
            'cart',
            'cart_coupon',
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

    /**
     * Display the order thank-you page.
     */
    public function thankYou(
        string $orderNumber
    ): View {
        $order = Order::query()
            ->with([
                'items',
            ])
            ->where(
                'order_number',
                $orderNumber
            )
            ->firstOrFail();

        /*
         * Prevent completely unrelated guest orders
         * from being freely opened by changing the URL.
         *
         * Authenticated users may view their own orders.
         * Guests may view the order just completed in
         * their current session.
         */
        $canViewOrder = false;

        if (
            auth()->check()
            && (int) $order->user_id
            === (int) auth()->id()
        ) {
            $canViewOrder = true;
        }

        if (
            session('recent_order_number')
            === $order->order_number
        ) {
            $canViewOrder = true;
        }

        if (!$canViewOrder) {
            abort(403);
        }

        return view(
            'checkout.thank-you',
            compact('order')
        );
    }

    /**
     * Validate all checkout fields.
     */
    private function validateCheckout(
        Request $request
    ): array {
        $availablePaymentMethods = [];

        if (
            config(
                'payments.stripe.enabled',
                false
            )
        ) {
            $availablePaymentMethods[] =
                'stripe';
        }

        if (
            config(
                'payments.bank_transfer.enabled',
                false
            )
        ) {
            $availablePaymentMethods[] =
                'bank_transfer';
        }

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
                'required_if:ship_to_different_address,1',
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
                Rule::in(
                    $availablePaymentMethods
                ),
            ],

            'terms' => [
                'accepted',
            ],
        ]);
    }

    /**
     * Resolve the final shipping address.
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
     * Create all order-item records.
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

            if (
                !empty($item['product_id'])
            ) {
                Product::query()
                    ->whereKey(
                        $item['product_id']
                    )
                    ->increment(
                        'purchase_count',
                        $quantity
                    );
            }
        }
    }

    /**
     * Calculate the cart subtotal.
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

                    return $price * $quantity;
                }
            ),
            2
        );
    }

    /**
     * Calculate the coupon discount.
     */
    private function calculateCouponDiscount(
        float $subtotal,
        array $coupon
    ): float {
        if (empty($coupon)) {
            return 0;
        }

        /*
         * Support coupons that already contain
         * a calculated discount.
         */
        if (
            isset($coupon['discount'])
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

        $type = $coupon['type'] ?? null;

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
     * Calculate shipping by delivery country.
     */
    private function calculateShipping(
        float $subtotal,
        ?string $countryCode = null
    ): float {
        /*
         * Free shipping for orders of $400 or above.
         */
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
     * Calculate the final checkout total.
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
     * Generate a unique public order number.
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
     * Generate a tracking reference.
     */
    private function generateTrackingNumber(): string
    {
        return 'TRK-'
            . strtoupper(
                Str::random(12)
            );
    }
}
