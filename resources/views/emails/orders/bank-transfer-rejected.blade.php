<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification Update</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6f8; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px;">
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 18px; font-size:24px; line-height:1.3; color:#111827;">
                                Payment Verification Update
                            </h1>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
                                Hello,
                            </p>

                            <p style="margin:0 0 20px; font-size:15px; line-height:1.7;">
                                We were unable to verify the bank transfer payment for your order at this time.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 22px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;">
                                <tr>
                                    <td style="padding:18px;">
                                        <p style="margin:0 0 10px; font-size:14px; line-height:1.6;">
                                            <strong>Order ID:</strong>
                                            {{ $order->order_number }}
                                        </p>

                                        <p style="margin:0 0 10px; font-size:14px; line-height:1.6;">
                                            <strong>Payment Method:</strong>
                                            Bank Transfer
                                        </p>

                                        <p style="margin:0; font-size:14px; line-height:1.6;">
                                            <strong>Payment Status:</strong>
                                            Failed
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.7;">
                                Your order has not been marked as paid and will remain pending until the payment is successfully verified.
                            </p>

                            <p style="margin:0 0 24px; font-size:15px; line-height:1.7;">
                                If you believe the payment was sent correctly, please contact us and provide your Order ID and bank transfer details so the payment can be reviewed.
                            </p>

                            <p style="margin:0; font-size:15px; line-height:1.7;">
                                Thank you,<br>
                                <strong>{{ config('app.name', 'ArizonaOutfits') }}</strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>