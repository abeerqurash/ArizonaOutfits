<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\InventoryService;
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

        $currency = strtoupper(
            config(
                'payments.currency',
                config('shipping.currency', 'USD')
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
                'selectedShippingMethod'
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
                    $shippingMethodDetails
                ): Order {
                    $order = Order::create([
                        'user_id' => auth()->id(),

                        'order_number' =>
                        $this->generateOrderNumber(),

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
                            config(
                                'payments.currency',
                                config(
                                    'shipping.currency',
                                    'USD'
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

                        'payment_reference' => null,
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

                    $this->inventoryService->deductForOrder(
                        $order
                    );

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
            config(
                'payments.bank_transfer.enabled',
                false
            )
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
        private readonly InventoryService $inventoryService,
        private readonly OrderEmailService $orderEmailService
    ) {}
}
