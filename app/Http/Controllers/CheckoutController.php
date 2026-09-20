<?php

namespace App\Http\Controllers;

use App\Models\EcommerceSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\OrderEmailService;
use RuntimeException;
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

        $cartGate = $this->validateCartForCheckout($cart);

        if (!$cartGate['valid']) {
            session()->put('cart', $cartGate['cart']);

            return redirect()
                ->route('cart.index')
                ->with('cart_warning', $cartGate['message']);
        }

        $cart = $cartGate['cart'];
        session()->put('cart', $cart);

        $subtotal = $this->calculateSubtotal($cart);

        $coupon = session()->get(
            'cart_coupon',
            []
        );

        $discount = $this->calculateCouponDiscount(
            $subtotal,
            $coupon
        );

        $shippingMethods = $this->getShippingMethods();

        $selectedShippingMethod = old(
            'shipping_method',
            config('shipping.default', 'standard')
        );

        if (
            !array_key_exists(
                $selectedShippingMethod,
                $shippingMethods
            )
        ) {
            $selectedShippingMethod = array_key_first(
                $shippingMethods
            );
        }

        $shippingDetails = $this->resolveShippingMethod(
            $selectedShippingMethod
        );

        $shipping = $shippingDetails['price'];

        $tax = 0;

        $total = $this->calculateTotal(
            $subtotal,
            $discount,
            $shipping,
            $tax
        );

        $ecommerceSettings =
            EcommerceSetting::current();

        $currency = strtoupper(
            (string) (
                $ecommerceSettings->currency
                ?: config(
                    'payments.currency',
                    config('shipping.currency', 'USD')
                )
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
                'currency',
                'shippingMethods',
                'selectedShippingMethod',
                'ecommerceSettings'
            )
        );
    }

    /**
     * Return a live shipping-method quotation.
     */
    public function shippingQuote(
        Request $request
    ): JsonResponse {
        $shippingMethods = $this->getShippingMethods();

        $validated = $request->validate([
            'shipping_method' => [
                'required',
                'string',
                Rule::in(
                    array_keys($shippingMethods)
                ),
            ],
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.',
            ], 422);
        }

        $cartGate = $this->validateCartForCheckout($cart);

        if (!$cartGate['valid']) {
            session()->put('cart', $cartGate['cart']);

            return response()->json([
                'success' => false,
                'message' => $cartGate['message'],
                'redirect_url' => route('cart.index'),
            ], 422);
        }

        $cart = $cartGate['cart'];
        session()->put('cart', $cart);

        $subtotal = $this->calculateSubtotal($cart);

        $coupon = session()->get(
            'cart_coupon',
            []
        );

        $discount = $this->calculateCouponDiscount(
            $subtotal,
            $coupon
        );

        $shippingDetails = $this->resolveShippingMethod(
            $validated['shipping_method']
        );

        $shipping = $shippingDetails['price'];

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
                config('shipping.currency', 'USD')
            )
        );

        return response()->json([
            'success' => true,

            'shipping_method' =>
            $validated['shipping_method'],

            'shipping_name' =>
            $shippingDetails['name'],

            'delivery_time' =>
            $shippingDetails['delivery_time'],

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

            'currency' => $currency,

            'formatted_shipping' =>
            '$' . number_format(
                $shipping,
                2
            ),

            'formatted_total' =>
            '$' . number_format(
                $total,
                2
            ),

            'message' =>
            $shippingDetails['name']
                . ' selected. Estimated delivery: '
                . $shippingDetails['delivery_time']
                . '.',
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

        /*
         * Revalidate inventory and prices even when the checkout page
         * is bypassed or has been left open while inventory changed.
         */
        $cartGate = $this->validateCartForCheckout($cart);

        if (!$cartGate['valid']) {
            session()->put('cart', $cartGate['cart']);

            return redirect()
                ->route('cart.index')
                ->with('cart_warning', $cartGate['message']);
        }

        $cart = $cartGate['cart'];
        session()->put('cart', $cart);

        $validated = $this->validateCheckout(
            $request
        );

        /*
         * Stripe checkout is handled through JavaScript
         * and StripePaymentController.
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
         * Only direct bank transfer can reach this method.
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

        /*
         * Direct bank transfer is available only to
         * authenticated customers. Enforce this on the
         * server even if the checkout UI is bypassed.
         */
        if (!auth()->check()) {
            /*
             * Return the customer to the checkout page after login.
             *
             * Do not use the current POST URL as the intended destination,
             * because the customer must return to the GET checkout page and
             * review/submit the bank-transfer order again after signing in.
             */
            $request->session()->put(
                'url.intended',
                route('checkout.index', absolute: false)
            );

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Please sign in or create an account before placing a bank transfer order.'
                );
        }

        $shipToDifferentAddress =
            $request->boolean(
                'ship_to_different_address'
            );

        /*
         * Never trust totals or shipping prices
         * submitted by the browser.
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

        $shippingMethodDetails =
            $this->resolveShippingMethod(
                $validated['shipping_method']
            );

        $shipping =
            $shippingMethodDetails['price'];

        $tax = 0;

        $total = $this->calculateTotal(
            $subtotal,
            $discount,
            $shipping,
            $tax
        );

        $shippingAddressDetails =
            $this->resolveShippingAddress(
                $validated,
                $shipToDifferentAddress
            );

        $ecommerceSettings =
            EcommerceSetting::current();

        if (!$ecommerceSettings->bank_transfer_enabled) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Bank transfer is currently unavailable.'
                );
        }

        /*
         * Snapshot the current bank-transfer instructions into
         * the order. This keeps an existing pending order tied
         * to the exact payment details that were active when the
         * customer placed it, even if an administrator changes
         * Store Settings later.
         */
        $bankTransferSnapshot = [
            'bank_name' =>
                $ecommerceSettings->bank_name,

            'account_name' =>
                $ecommerceSettings->bank_account_name,

            'account_number' =>
                $ecommerceSettings->bank_account_number,

            'iban' =>
                $ecommerceSettings->bank_iban,

            'swift_code' =>
                $ecommerceSettings->bank_swift_code,

            'branch_name' =>
                $ecommerceSettings->bank_branch_name,

            'instructions' =>
                $ecommerceSettings->bank_transfer_instructions,
        ];

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
                    $shippingAddressDetails,
                    $shippingMethodDetails,
                    $ecommerceSettings,
                    $bankTransferSnapshot
                ): Order {
                    /*
                     * Generate the bank-transfer order reference once.
                     *
                     * The customer must use this exact Order ID as the
                     * bank-transfer payment reference.
                     */
                    $orderNumber =
                        $this->generateOrderNumber();

                    $order = Order::create([
                        'user_id' => auth()->id(),

                        'order_number' =>
                        $orderNumber,

                        'tracking_number' =>
                        $this->generateTrackingNumber(),

                        'subtotal' => $subtotal,
                        'discount' => $discount,

                        /*
                         * Keep the existing shipping column
                         * synchronized with shipping_price.
                         */
                        'shipping' => $shipping,

                        'shipping_method' =>
                        $shippingMethodDetails['name'],

                        'shipping_price' =>
                        $shipping,

                        'estimated_delivery' =>
                        $shippingMethodDetails['delivery_time'],

                        'tax' => $tax,
                        'total' => $total,

                        'currency' => strtoupper(
                            (string) (
                                $ecommerceSettings->currency
                                ?: config(
                                    'payments.currency',
                                    config(
                                        'shipping.currency',
                                        'USD'
                                    )
                                )
                            )
                        ),

                        'coupon_code' =>
                        $coupon['code']
                            ?? null,

                        'payment_method' =>
                        'bank_transfer',

                        'payment_provider' =>
                        'bank_transfer',

                        /*
                         * For bank transfer, the Order ID is also the
                         * required customer payment reference.
                         */
                        'payment_reference' =>
                        $orderNumber,

                        'payment_intent_id' => null,

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

                            'shipping_method_key' =>
                            $validated['shipping_method'],

                            'shipping_method_name' =>
                            $shippingMethodDetails['name'],

                            'shipping_price' =>
                            $shipping,

                            'estimated_delivery' =>
                            $shippingMethodDetails['delivery_time'],

                            'bank_name' =>
                            $bankTransferSnapshot['bank_name'],

                            'account_name' =>
                            $bankTransferSnapshot['account_name'],

                            'account_number' =>
                            $bankTransferSnapshot['account_number'],

                            'iban' =>
                            $bankTransferSnapshot['iban'],

                            'swift_code' =>
                            $bankTransferSnapshot['swift_code'],

                            'branch_name' =>
                            $bankTransferSnapshot['branch_name'],

                            'instructions' =>
                            $bankTransferSnapshot['instructions'],
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
                        $shippingAddressDetails['name'],

                        'shipping_email' =>
                        $shippingAddressDetails['email'],

                        'shipping_phone' =>
                        $shippingAddressDetails['phone'],

                        'shipping_address' =>
                        $shippingAddressDetails['address'],

                        'shipping_country' =>
                        strtoupper(
                            $shippingAddressDetails['country']
                        ),

                        'shipping_state' =>
                        $shippingAddressDetails['state'],

                        'shipping_city' =>
                        $shippingAddressDetails['city'],

                        'shipping_zip' =>
                        $shippingAddressDetails['zip'],

                        'order_notes' =>
                        $validated['order_notes']
                            ?? null,
                    ]);

                    $this->createOrderItems(
                        $order,
                        $cart
                    );

                    /*
                     * Do not deduct inventory yet.
                     *
                     * A bank-transfer order is still unpaid at this
                     * stage. Inventory will be deducted only after
                     * an administrator verifies the payment.
                     */
                    return $order;
                }
            );
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'We could not complete your order. Please try again.'
                );
        }


        $this->orderEmailService->sendOrderEmails($order);

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
            EcommerceSetting::current()
                ->bank_transfer_enabled
        ) {
            $availablePaymentMethods[] =
                'bank_transfer';
        }

        $shippingMethods =
            $this->getShippingMethods();

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

            /*
             * The customer selects one of the
             * configured shipping methods.
             */
            'shipping_method' => [
                'required',
                'string',
                Rule::in(
                    array_keys($shippingMethods)
                ),
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
    private function resolveShippingAddress(
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
     * Return configured shipping methods.
     */
    private function getShippingMethods(): array
    {
        $shippingMethods = config(
            'shipping.methods',
            []
        );

        return is_array($shippingMethods)
            ? $shippingMethods
            : [];
    }

    /**
     * Resolve and normalize one shipping method.
     */
    private function resolveShippingMethod(
        string $shippingMethod
    ): array {
        $shippingMethods =
            $this->getShippingMethods();

        $method =
            $shippingMethods[$shippingMethod]
            ?? null;

        if (!is_array($method)) {
            abort(
                422,
                'The selected shipping method is unavailable.'
            );
        }

        return [
            'key' => $shippingMethod,

            'name' =>
            (string) (
                $method['name']
                ?? ucfirst($shippingMethod)
            ),

            'price' => round(
                max(
                    0,
                    (float) (
                        $method['price']
                        ?? 0
                    )
                ),
                2
            ),

            'delivery_time' =>
            (string) (
                $method['delivery_time']
                ?? 'Delivery time unavailable'
            ),

            'description' =>
            (string) (
                $method['description']
                ?? ''
            ),
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
        }
    }

    /**
     * Revalidate the complete cart before checkout.
     *
     * This gate is independent from the Cart page UI. A customer can type
     * /checkout directly, keep an old checkout tab open, or submit a stale
     * order request after inventory or pricing has changed.
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

    public function __construct(
        private readonly OrderEmailService $orderEmailService
    ) {}
}
