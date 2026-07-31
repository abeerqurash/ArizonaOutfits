<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoiceNumber }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#111827;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 15px;background:#f3f4f6;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;">

                <tr>
                    <td style="padding:24px;background:#111827;color:#ffffff;">
                        <div style="font-size:22px;font-weight:bold;">
                            Arizona Outfits
                        </div>

                        <div style="margin-top:6px;font-size:13px;color:#d1d5db;">
                            Invoice for your order
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px;">

                        <p style="margin:0 0 16px;font-size:16px;">
                            Hello
                            {{ $order->billing_name
                                ?: $order->shipping_name
                                ?: $order->user?->name
                                ?: 'Customer' }},
                        </p>

                        <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#4b5563;">
                            Thank you for your order. Your invoice is attached to this email as a PDF.
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin:22px 0;border:1px solid #e5e7eb;">
                            <tr>
                                <td style="padding:12px;color:#6b7280;font-size:13px;">
                                    Order number
                                </td>

                                <td align="right" style="padding:12px;font-size:13px;font-weight:bold;">
                                    {{ $orderNumber }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:12px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;">
                                    Invoice number
                                </td>

                                <td align="right" style="padding:12px;border-top:1px solid #e5e7eb;font-size:13px;font-weight:bold;">
                                    {{ $invoiceNumber }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:12px;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;">
                                    Total
                                </td>

                                <td align="right" style="padding:12px;border-top:1px solid #e5e7eb;font-size:13px;font-weight:bold;">
                                    {{ strtoupper($order->currency ?: 'PKR') }}
                                    {{ number_format((float) $order->total, 2) }}
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0;font-size:13px;line-height:1.7;color:#6b7280;">
                            Please keep the attached invoice for your records.
                        </p>

                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 28px;background:#f9fafb;color:#9ca3af;font-size:12px;text-align:center;">
                        Arizona Outfits
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>