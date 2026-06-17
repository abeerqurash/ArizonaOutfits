<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);

        if (!count($cart)) {
            return redirect()->route('cart.index');
        }

        return view('checkout.index', compact('cart'));
    }

    public function placeOrder(Request $request)
    {
        $cart = session('cart', []);

        if (!count($cart)) {
            return redirect()->route('cart.index');
        }

        $data = $request->validate([
            'billing_name' => 'required|string|max:255',
            'billing_email' => 'required|email|max:255',
            'billing_phone' => 'required|string|max:50',
            'billing_address' => 'required|string',
            'billing_city' => 'required|string|max:255',
            'billing_country' => 'required|string|max:255',

            'shipping_name' => 'nullable|string|max:255',
            'shipping_email' => 'nullable|email|max:255',
            'shipping_phone' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|string',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:255',

            'payment_method' => 'nullable|string|max:255',
            'terms' => 'required',
        ]);

        $subtotal = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $shipping = 0;
        $discount = 0;
        $total = $subtotal + $shipping - $discount;

        $order = Order::create([
            'user_id' => auth()->id(),

            'order_number' => 'AO-' . strtoupper(Str::random(8)),
            'tracking_number' => 'TRK-' . strtoupper(Str::random(10)),

            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,

            'currency' => session('currency', 'USD'),

            'payment_method' => $data['payment_method'] ?? 'card',
            'payment_status' => 'pending',
            'order_status' => 'pending',

            'coupon_code' => session('coupon.code'),

            'billing_name' => $data['billing_name'],
            'billing_email' => $data['billing_email'],
            'billing_phone' => $data['billing_phone'],
            'billing_address' => $data['billing_address'],
            'billing_city' => $data['billing_city'],
            'billing_country' => $data['billing_country'],

            'shipping_name' => $data['shipping_name'] ?? $data['billing_name'],
            'shipping_email' => $data['shipping_email'] ?? $data['billing_email'],
            'shipping_phone' => $data['shipping_phone'] ?? $data['billing_phone'],
            'shipping_address' => $data['shipping_address'] ?? $data['billing_address'],
            'shipping_city' => $data['shipping_city'] ?? $data['billing_city'],
            'shipping_country' => $data['shipping_country'] ?? $data['billing_country'],
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'] ?? null,
                'product_title' => $item['title'],
                'sku' => $item['sku'] ?? null,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'options' => json_encode($item['options'] ?? []),
                'total' => $item['price'] * $item['quantity'],
            ]);

            if (!empty($item['product_id'])) {
                Product::where('id', $item['product_id'])->increment('purchase_count');
            }
        }

        session()->forget('cart');
        session()->forget('coupon');

        return redirect()->route('checkout.thankyou', $order->order_number);
    }

    public function thankYou($order_number)
    {
        $order = Order::with('items')
            ->where('order_number', $order_number)
            ->firstOrFail();

        return view('checkout.thank-you', compact('order'));
    }
}