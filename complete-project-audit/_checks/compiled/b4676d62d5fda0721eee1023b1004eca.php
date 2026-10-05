<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Invoice <?php echo e($invoiceNumber); ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6fb;padding:32px 12px;"><tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;">
<tr><td style="padding:25px 30px;background:#111827;color:#fff;">
<div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#a5b4fc;">Arizona Outfits</div>
<div style="margin-top:7px;font-size:24px;font-weight:800;">Your invoice is ready</div>
<div style="margin-top:7px;font-size:13px;color:#cbd5e1;">A PDF copy of your Arizona Outfits invoice is attached.</div>
</td></tr><tr><td style="padding:30px;">
<p style="margin:0 0 18px;line-height:1.7;">Hello <strong><?php echo e($order->billing_name ?: $order->shipping_name ?: $order->user?->name ?: 'Customer'); ?></strong>,</p>
<p style="margin:0 0 22px;line-height:1.7;color:#596273;">Thank you for your order. Your invoice is attached as a PDF for your records.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;background:#f8fafc;border-radius:10px;">
<tr><td style="padding:13px 15px;color:#64748b;">Order number</td><td align="right" style="padding:13px 15px;font-weight:700;"><?php echo e($orderNumber); ?></td></tr>
<tr><td style="padding:13px 15px;border-top:1px solid #e5e7eb;color:#64748b;">Invoice number</td><td align="right" style="padding:13px 15px;border-top:1px solid #e5e7eb;font-weight:700;"><?php echo e($invoiceNumber); ?></td></tr>
<tr><td style="padding:15px;border-top:1px solid #dfe3eb;font-size:17px;font-weight:800;">Invoice total</td><td align="right" style="padding:15px;border-top:1px solid #dfe3eb;font-size:19px;font-weight:800;color:#4f46e5;"><?php echo e(strtoupper((string)($order->currency ?: 'USD'))); ?> <?php echo e(number_format((float)$order->total,2)); ?></td></tr>
</table>
<div style="margin-top:22px;padding:15px 17px;background:#eef2ff;border-radius:9px;color:#4338ca;font-size:13px;line-height:1.6;">Your detailed invoice is included with this email as a PDF attachment.</div>
</td></tr>
<tr><td style="padding:18px 30px;background:#f8fafc;border-top:1px solid #eef0f5;text-align:center;font-size:12px;line-height:1.6;color:#7c8597;">
&copy; <?php echo e(date('Y')); ?> Arizona Outfits. Transactional order communication.
</td></tr></table></td></tr></table></body></html><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\emails\orders\invoice.blade.php ENDPATH**/ ?>