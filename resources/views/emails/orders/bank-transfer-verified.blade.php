<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verified - {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:#f5f6fa;font-family:Arial,Helvetica,sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f5f6fa;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#ffffff;border:1px solid #e6e8ef;border-radius:12px;overflow:hidden;">
                <tr>
                    <td style="padding:28px 32px;background:#111827;color:#ffffff;">
                        <div style="font-size:20px;font-weight:700;">Arizona Outfits</div>
                        <div style="margin-top:6px;font-size:13px;color:#cbd5e1;">Payment verification update</div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:32px;">
                        <h1 style="margin:0 0 14px;font-size:24px;line-height:1.3;color:#111827;">
                            Payment Verified
                        </h1>

                        <p style="margin:0 0 18px;font-size:15px;line-height:1.7;">
                            Hello {{ $order->customer_name ?? $order->billing_name ?? $order->shipping_name ?? 'Customer' }},
                        </p>

                        <p style="margin:0 0 24px;font-size:15px;line-height:1.7;">
                            We have successfully verified your bank transfer payment for order
                            <strong>{{ $order->order_number }}</strong>.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;">
                            <tr>
                                <td style="padding:20px;font-size:14px;line-height:1.8;">
                                    <strong>Order ID:</strong>
                                    {{ $order->order_number }}<br>

                                    <strong>Payment Reference:</strong>
                                    {{ $order->payment_reference ?: $order->order_number }}<br>

                                    <strong>Payment Status:</strong>
                                    Paid<br>

                                    <strong>Order Status:</strong>
                                    {{ ucwords(str_replace('_', ' ', (string) $order->order_status)) }}<br>

                                    @if($order->paid_at)
                                        <strong>Payment Verified At:</strong>
                                        {{ $order->paid_at->format('d M Y, h:i A') }}
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <p style="margin:24px 0 0;font-size:15px;line-height:1.7;">
                            Your order can now continue through our processing and fulfilment workflow.
                        </p>

                        <p style="margin:24px 0 0;font-size:15px;line-height:1.7;">
                            Thank you for shopping with Arizona Outfits.
                        </p>

                        <p style="margin:24px 0 0;font-size:15px;line-height:1.7;">
                            Regards,<br>
                            {{ config('app.name', 'Arizona Outfits') }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
