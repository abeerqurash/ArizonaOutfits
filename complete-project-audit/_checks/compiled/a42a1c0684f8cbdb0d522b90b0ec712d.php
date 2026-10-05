<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Payment verification update</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6fb;padding:32px 12px;"><tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;">
<tr><td style="padding:25px 30px;background:#111827;color:#fff;">
<div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#a5b4fc;">Arizona Outfits</div>
<div style="margin-top:7px;font-size:24px;font-weight:800;">Payment needs attention</div>
<div style="margin-top:7px;font-size:13px;color:#cbd5e1;">We could not verify the submitted bank transfer.</div>
</td></tr><tr><td style="padding:30px;">
<p style="margin:0 0 18px;line-height:1.7;">Hello <strong><?php echo e($order->customer_name ?? $order->billing_name ?? $order->shipping_name ?? 'Customer'); ?></strong>,</p>
<div style="padding:18px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;margin-bottom:22px;"><strong style="color:#c2410c;font-size:17px;">Payment not verified</strong><p style="margin:7px 0 0;color:#475569;line-height:1.7;">We were unable to verify the bank transfer for order <strong><?php echo e($order->order_number); ?></strong>. The order has not been marked as paid.</p></div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
<tr><td style="padding:11px 0;color:#64748b;">Order</td><td align="right" style="padding:11px 0;font-weight:700;"><?php echo e($order->order_number); ?></td></tr>
<tr><td style="padding:11px 0;border-top:1px solid #eef0f5;color:#64748b;">Payment method</td><td align="right" style="padding:11px 0;border-top:1px solid #eef0f5;">Bank Transfer</td></tr>
<tr><td style="padding:11px 0;border-top:1px solid #eef0f5;color:#64748b;">Payment status</td><td align="right" style="padding:11px 0;border-top:1px solid #eef0f5;color:#c2410c;font-weight:700;">Failed verification</td></tr>
</table>
<p style="margin:22px 0 0;line-height:1.7;color:#596273;">If you believe your payment was sent correctly, contact Arizona Outfits support with your order number and bank-transfer details.</p>
</td></tr>
<tr><td style="padding:18px 30px;background:#f8fafc;border-top:1px solid #eef0f5;text-align:center;font-size:12px;line-height:1.6;color:#7c8597;">
&copy; <?php echo e(date('Y')); ?> Arizona Outfits. Transactional order communication.
</td></tr></table></td></tr></table></body></html><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\emails\orders\bank-transfer-rejected.blade.php ENDPATH**/ ?>