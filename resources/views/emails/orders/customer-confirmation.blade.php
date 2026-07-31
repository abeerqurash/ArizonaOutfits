<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Order confirmation
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background: #f5f5f5;
        font-family: Arial, Helvetica, sans-serif;
        color: #222222;
    ">
    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0">
        <tr>
            <td
                align="center"
                style="padding: 30px 15px;">
                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="
                    max-width: 680px;
                    background: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                ">
                    <tr>
                        <td
                            style="
                            padding: 30px;
                            background: #111111;
                            color: #ffffff;
                        ">
                            <h1
                                style="
                                margin: 0;
                                font-size: 26px;
                            ">
                                Thank you for your order
                            </h1>

                            <p
                                style="
                                margin: 10px 0 0;
                                line-height: 1.6;
                            ">
                                Your order has been received successfully.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px;">
                            <p
                                style="
                                margin-top: 0;
                                line-height: 1.7;
                            ">
                                Hello
                                {{ $order->customer_name
                                ?? $order->billing_name
                                ?? $order->name
                                ?? 'Customer' }},
                            </p>

                            <p style="line-height: 1.7;">
                                Your order number is
                                <strong>
                                    {{ $order->order_number }}
                                </strong>.
                            </p>

                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="8"
                                border="0"
                                style="
                                margin: 25px 0;
                                border-collapse: collapse;
                            ">
                                <thead>
                                    <tr
                                        style="
                                        background: #f1f1f1;
                                        text-align: left;
                                    ">
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th align="right">Total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($order->items as $item)
                                    <tr>
                                        <td
                                            style="
                                                border-bottom:
                                                    1px solid #eeeeee;
                                            ">
                                            <strong>
                                                {{ $item->productName() }}
                                            </strong>

                                            @if ($item->variantLabel())
                                            <br>

                                            <small>
                                                {{ $item->variantLabel() }}
                                            </small>
                                            @endif
                                        </td>

                                        <td
                                            style="
                                                border-bottom:
                                                    1px solid #eeeeee;
                                            ">
                                            {{ $item->quantity }}
                                        </td>

                                        <td
                                            align="right"
                                            style="
                                                border-bottom:
                                                    1px solid #eeeeee;
                                            ">
                                            {{ strtoupper(
                                                config(
                                                    'payments.currency',
                                                    'USD'
                                                )
                                            ) }}

                                            {{ number_format(
                                                (float) $item->subtotal(),
                                                2
                                            ) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="5"
                                border="0">
                                @if (
                                isset($order->subtotal)
                                && $order->subtotal !== null
                                )
                                <tr>
                                    <td>Subtotal</td>

                                    <td align="right">
                                        {{ strtoupper(
                                            config(
                                                'payments.currency',
                                                'USD'
                                            )
                                        ) }}

                                        {{ number_format(
                                            (float) $order->subtotal,
                                            2
                                        ) }}
                                    </td>
                                </tr>
                                @endif

                                @if (
                                isset($order->shipping_total)
                                && $order->shipping_total !== null
                                )
                                <tr>
                                    <td>Shipping</td>

                                    <td align="right">
                                        {{ strtoupper(
                                            config(
                                                'payments.currency',
                                                'USD'
                                            )
                                        ) }}

                                        {{ number_format(
                                            (float) $order->shipping_total,
                                            2
                                        ) }}
                                    </td>
                                </tr>
                                @endif

                                @if (
                                isset($order->discount_total)
                                && (float) $order->discount_total > 0
                                )
                                <tr>
                                    <td>Discount</td>

                                    <td align="right">
                                        -
                                        {{ strtoupper(
                                            config(
                                                'payments.currency',
                                                'USD'
                                            )
                                        ) }}

                                        {{ number_format(
                                            (float) $order->discount_total,
                                            2
                                        ) }}
                                    </td>
                                </tr>
                                @endif

                                <tr>
                                    <td
                                        style="
                                        padding-top: 12px;
                                        font-size: 18px;
                                        font-weight: bold;
                                    ">
                                        Order total
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                        padding-top: 12px;
                                        font-size: 18px;
                                        font-weight: bold;
                                    ">
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
                                    </td>
                                </tr>
                            </table>

                            @if (
                            ($order->payment_provider ?? null)
                            === 'bank_transfer'
                            || ($order->payment_method ?? null)
                            === 'bank_transfer'
                            )
                            <div
                                style="
                                    margin-top: 30px;
                                    padding: 20px;
                                    background: #f8f8f8;
                                    border-radius: 8px;
                                ">
                                <h2
                                    style="
                                        margin-top: 0;
                                        font-size: 20px;
                                    ">
                                    Bank transfer instructions
                                </h2>

                                <p style="line-height: 1.7;">
                                    Please use your order number
                                    <strong>
                                        {{ $order->order_number }}
                                    </strong>
                                    as the payment reference.
                                </p>

                                <p style="line-height: 1.8;">
                                    <strong>Bank:</strong>
                                    {{ config(
                                        'payments.bank_transfer.bank_name'
                                    ) ?: 'Not provided' }}
                                    <br>

                                    <strong>Account name:</strong>
                                    {{ config(
                                        'payments.bank_transfer.account_name'
                                    ) ?: 'Not provided' }}
                                    <br>

                                    <strong>Account number:</strong>
                                    {{ config(
                                        'payments.bank_transfer.account_number'
                                    ) ?: 'Not provided' }}
                                    <br>

                                    <strong>IBAN:</strong>
                                    {{ config(
                                        'payments.bank_transfer.iban'
                                    ) ?: 'Not provided' }}
                                    <br>

                                    <strong>SWIFT code:</strong>
                                    {{ config(
                                        'payments.bank_transfer.swift_code'
                                    ) ?: 'Not provided' }}
                                </p>
                            </div>
                            @endif

                            <p
                                style="
                                margin-bottom: 0;
                                margin-top: 30px;
                                line-height: 1.7;
                            ">
                                We will contact you when the order status
                                changes.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>