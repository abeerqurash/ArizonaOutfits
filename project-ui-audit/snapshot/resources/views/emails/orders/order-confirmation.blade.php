<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Order confirmation</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6fb;padding:32px 12px;"><tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;">
<tr><td style="padding:25px 30px;background:#111827;color:#fff;">
<div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#a5b4fc;">Arizona Outfits</div>
<div style="margin-top:7px;font-size:24px;font-weight:800;">Order confirmed</div>
<div style="margin-top:7px;font-size:13px;color:#cbd5e1;">We’ve received your order and saved its checkout totals.</div>
</td></tr><tr><td style="padding:30px;">
@php
$currency = strtoupper((string) ($order->currency ?: config('payments.currency', 'USD')));
$shipping = (float) ($order->shipping_price ?? $order->shipping ?? 0);
$discount = (float) ($order->discount ?? 0);
$tax = (float) ($order->tax ?? 0);
@endphp
<p style="margin:0 0 8px;font-size:16px;line-height:1.7;">Hello <strong>{{ $order->customer_name ?? $order->billing_name ?? $order->shipping_name ?? $order->user?->name ?? 'Customer' }}</strong>,</p>
<p style="margin:0 0 24px;color:#596273;line-height:1.7;">Thanks for shopping with Arizona Outfits. Your order <strong style="color:#111827;">{{ $order->order_number }}</strong> has been received.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;margin:0 0 24px;">
<tr style="background:#f8fafc;"><th align="left" style="padding:12px;border-bottom:1px solid #e5e7eb;font-size:12px;color:#64748b;">ITEM</th><th align="center" style="padding:12px;border-bottom:1px solid #e5e7eb;font-size:12px;color:#64748b;">QTY</th><th align="right" style="padding:12px;border-bottom:1px solid #e5e7eb;font-size:12px;color:#64748b;">TOTAL</th></tr>
@foreach ($order->items as $item)
<tr><td style="padding:14px 12px;border-bottom:1px solid #eef0f5;"><strong>{{ $item->productName() }}</strong>@if($item->variantLabel())<br><span style="font-size:12px;color:#7c8597;">{{ $item->variantLabel() }}</span>@endif</td><td align="center" style="padding:14px 12px;border-bottom:1px solid #eef0f5;">{{ $item->quantity }}</td><td align="right" style="padding:14px 12px;border-bottom:1px solid #eef0f5;font-weight:700;">{{ $currency }} {{ number_format((float) $item->subtotal(), 2) }}</td></tr>
@endforeach
</table>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;background:#f8fafc;border-radius:10px;">
<tr><td style="padding:10px 14px;">Subtotal</td><td align="right" style="padding:10px 14px;">{{ $currency }} {{ number_format((float) ($order->subtotal ?? 0),2) }}</td></tr>
@if($discount > 0)<tr><td style="padding:10px 14px;">Discount @if($order->coupon_code)<span style="font-size:11px;color:#635bff;">({{ $order->coupon_code }})</span>@endif</td><td align="right" style="padding:10px 14px;color:#047857;">-{{ $currency }} {{ number_format($discount,2) }}</td></tr>@endif
<tr><td style="padding:10px 14px;">Shipping</td><td align="right" style="padding:10px 14px;">{{ $currency }} {{ number_format($shipping,2) }}</td></tr>
<tr><td style="padding:10px 14px;">Tax</td><td align="right" style="padding:10px 14px;">{{ $currency }} {{ number_format($tax,2) }}</td></tr>
<tr><td style="padding:14px;border-top:1px solid #dfe3eb;font-size:18px;font-weight:800;">Order total</td><td align="right" style="padding:14px;border-top:1px solid #dfe3eb;font-size:18px;font-weight:800;color:#4f46e5;">{{ $currency }} {{ number_format((float) ($order->total ?? 0),2) }}</td></tr>
</table>
@if(($order->payment_provider ?? $order->payment_method) === 'bank_transfer')
<div style="margin-top:24px;padding:18px;border:1px solid #dbeafe;background:#eff6ff;border-radius:10px;">
<strong style="color:#1d4ed8;">Bank transfer required</strong>
<p style="margin:8px 0 0;line-height:1.7;color:#475569;">Use order <strong>{{ $order->order_number }}</strong> as your payment reference. Your order remains pending until payment is verified.</p>
</div>
@endif
</td></tr>
<tr><td style="padding:18px 30px;background:#f8fafc;border-top:1px solid #eef0f5;text-align:center;font-size:12px;line-height:1.6;color:#7c8597;">
&copy; {{ date('Y') }} Arizona Outfits. Transactional order communication.
</td></tr></table></td></tr></table></body></html>