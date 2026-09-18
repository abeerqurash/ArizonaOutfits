<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Updated</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#222222;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f5f5f5;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#ffffff;border:1px solid #e5e5e5;border-radius:10px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 30px;background:#111111;color:#ffffff;text-align:center;">
                            <div style="font-size:25px;font-weight:700;letter-spacing:.5px;">
                                Arizona Outfits
                            </div>
                            <div style="margin-top:8px;font-size:14px;color:#dddddd;">
                                Order Status Update
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px;">
                            <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">
                                Hi {{ $customerName }},
                            </p>

                            <p style="margin:0 0 22px;font-size:15px;line-height:1.7;color:#444444;">
                                The status of your order has been updated.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:12px 14px;border:1px solid #e5e5e5;background:#fafafa;font-size:14px;font-weight:700;width:42%;">
                                        Order Number
                                    </td>
                                    <td style="padding:12px 14px;border:1px solid #e5e5e5;font-size:14px;">
                                        {{ $order->order_number ?: ('ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:12px 14px;border:1px solid #e5e5e5;background:#fafafa;font-size:14px;font-weight:700;">
                                        Previous Status
                                    </td>
                                    <td style="padding:12px 14px;border:1px solid #e5e5e5;font-size:14px;">
                                        {{ $previousStatus }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:12px 14px;border:1px solid #e5e5e5;background:#fafafa;font-size:14px;font-weight:700;">
                                        Current Status
                                    </td>
                                    <td style="padding:12px 14px;border:1px solid #e5e5e5;font-size:14px;font-weight:700;">
                                        {{ $currentStatus }}
                                    </td>
                                </tr>

                                @if (!empty($order->tracking_number))
                                    <tr>
                                        <td style="padding:12px 14px;border:1px solid #e5e5e5;background:#fafafa;font-size:14px;font-weight:700;">
                                            Tracking Number
                                        </td>
                                        <td style="padding:12px 14px;border:1px solid #e5e5e5;font-size:14px;">
                                            {{ $order->tracking_number }}
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                    <td style="padding:12px 14px;border:1px solid #e5e5e5;background:#fafafa;font-size:14px;font-weight:700;">
                                        Payment Status
                                    </td>
                                    <td style="padding:12px 14px;border:1px solid #e5e5e5;font-size:14px;">
                                        {{ ucwords(str_replace(['_', '-'], ' ', (string) $order->payment_status)) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:12px 14px;border:1px solid #e5e5e5;background:#fafafa;font-size:14px;font-weight:700;">
                                        Order Total
                                    </td>
                                    <td style="padding:12px 14px;border:1px solid #e5e5e5;font-size:14px;font-weight:700;">
                                        {{ strtoupper((string) ($order->currency ?: 'USD')) }}
                                        {{ number_format((float) $order->total, 2) }}
                                    </td>
                                </tr>
                            </table>

                            @if ($currentStatus === 'Shipped' || $currentStatus === 'Out For Delivery')
                                <div style="margin:0 0 22px;padding:14px 16px;background:#f7f7f7;border-left:4px solid #111111;font-size:14px;line-height:1.6;color:#444444;">
                                    Your order is on its way. Keep your tracking number available for delivery updates.
                                </div>
                            @elseif ($currentStatus === 'Delivered' || $currentStatus === 'Completed')
                                <div style="margin:0 0 22px;padding:14px 16px;background:#f7f7f7;border-left:4px solid #111111;font-size:14px;line-height:1.6;color:#444444;">
                                    Your order has been marked as {{ strtolower($currentStatus) }}. Thank you for shopping with Arizona Outfits.
                                </div>
                            @elseif ($currentStatus === 'Cancelled')
                                <div style="margin:0 0 22px;padding:14px 16px;background:#f7f7f7;border-left:4px solid #111111;font-size:14px;line-height:1.6;color:#444444;">
                                    Your order has been cancelled. If you need assistance, please contact Arizona Outfits support.
                                </div>
                            @endif

                            <p style="margin:0;font-size:14px;line-height:1.7;color:#555555;">
                                This is an automated transactional email regarding your Arizona Outfits order.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 30px;background:#fafafa;border-top:1px solid #eeeeee;text-align:center;font-size:12px;line-height:1.6;color:#777777;">
                            &copy; {{ date('Y') }} Arizona Outfits. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
