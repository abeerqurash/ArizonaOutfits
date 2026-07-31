<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>New order</title>
</head>

<body
    style="
        margin: 0;
        padding: 30px;
        background: #f5f5f5;
        font-family: Arial, Helvetica, sans-serif;
        color: #222222;
    ">
    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="
        max-width: 680px;
        margin: auto;
        background: #ffffff;
        border-radius: 10px;
    ">
        <tr>
            <td style="padding: 30px;">
                <h1 style="margin-top: 0;">
                    New order received
                </h1>

                <p>
                    <strong>Order:</strong>
                    {{ $order->order_number }}
                </p>

                <p>
                    <strong>Customer:</strong>

                    {{ $order->customer_name
                    ?? $order->billing_name
                    ?? $order->name
                    ?? 'Not provided' }}
                </p>

                <p>
                    <strong>Email:</strong>

                    {{ $order->email
                    ?? $order->customer_email
                    ?? $order->billing_email
                    ?? $order->user?->email
                    ?? 'Not provided' }}
                </p>

                <p>
                    <strong>Payment method:</strong>

                    {{ str_replace(
                    '_',
                    ' ',
                    ucfirst(
                        $order->payment_provider
                            ?? $order->payment_method
                            ?? 'Not provided'
                    )
                ) }}
                </p>

                <p>
                    <strong>Payment status:</strong>

                    {{ ucfirst(
                    $order->payment_status
                        ?? $order->status
                        ?? 'Pending'
                ) }}
                </p>

                <p
                    style="
                    margin-top: 25px;
                    font-size: 20px;
                ">
                    <strong>Total:</strong>

                    {{ strtoupper(
                    $order->currency
                        ?? config(
                            'payments.currency',
                            'USD'
                        )
                ) }}

                    {{ number_format(
                    (float) (
                        $order->total
                        ?? $order->grand_total
                        ?? 0
                    ),
                    2
                ) }}
                </p>

                <p style="margin-top: 30px;">
                    <a
                        href="{{ route(
                        'admin.orders.show',
                        $order
                    ) }}"
                        style="
                        display: inline-block;
                        padding: 12px 20px;
                        background: #111111;
                        color: #ffffff;
                        text-decoration: none;
                        border-radius: 6px;
                    ">
                        View order
                    </a>
                </p>
            </td>
        </tr>
    </table>
</body>

</html>