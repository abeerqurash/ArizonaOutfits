<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>New order received</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6fb;padding:32px 12px;"><tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;">
<tr><td style="padding:25px 30px;background:#111827;color:#fff;">
<div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#a5b4fc;">Arizona Outfits</div>
<div style="margin-top:7px;font-size:24px;font-weight:800;">New order received</div>
<div style="margin-top:7px;font-size:13px;color:#cbd5e1;">A new checkout has been created and is ready for review.</div>
</td></tr><tr><td style="padding:30px;">
<?php
$currency = strtoupper((string) ($order->currency ?: config('payments.currency', 'USD')));
?>
<div style="padding:16px 18px;background:#eef2ff;border:1px solid #dfe3ff;border-radius:10px;margin-bottom:22px;">
<div style="font-size:12px;color:#6366f1;text-transform:uppercase;font-weight:700;">Order</div>
<div style="font-size:22px;font-weight:800;margin-top:4px;"><?php echo e($order->order_number); ?></div>
</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
<tr><td style="padding:11px 0;color:#64748b;">Customer</td><td align="right" style="padding:11px 0;font-weight:700;"><?php echo e($order->customer_name ?? $order->billing_name ?? $order->shipping_name ?? $order->user?->name ?? 'Not provided'); ?></td></tr>
<tr><td style="padding:11px 0;border-top:1px solid #eef0f5;color:#64748b;">Email</td><td align="right" style="padding:11px 0;border-top:1px solid #eef0f5;"><?php echo e($order->email ?? $order->customer_email ?? $order->billing_email ?? $order->user?->email ?? 'Not provided'); ?></td></tr>
<tr><td style="padding:11px 0;border-top:1px solid #eef0f5;color:#64748b;">Payment</td><td align="right" style="padding:11px 0;border-top:1px solid #eef0f5;"><?php echo e(ucwords(str_replace(['_','-'],' ',(string)($order->payment_provider ?? $order->payment_method ?? 'Not provided')))); ?></td></tr>
<tr><td style="padding:11px 0;border-top:1px solid #eef0f5;color:#64748b;">Status</td><td align="right" style="padding:11px 0;border-top:1px solid #eef0f5;font-weight:700;"><?php echo e(ucwords(str_replace(['_','-'],' ',(string)($order->payment_status ?? 'pending')))); ?></td></tr>
<tr><td style="padding:15px 0;border-top:1px solid #dfe3eb;font-size:18px;font-weight:800;">Total</td><td align="right" style="padding:15px 0;border-top:1px solid #dfe3eb;font-size:20px;font-weight:800;color:#4f46e5;"><?php echo e($currency); ?> <?php echo e(number_format((float)($order->total ?? 0),2)); ?></td></tr>
</table>
<p style="margin:25px 0 0;"><a href="<?php echo e(route('admin.orders.show',$order)); ?>" style="display:inline-block;background:#635bff;color:#fff;text-decoration:none;padding:13px 20px;border-radius:8px;font-weight:700;">Open order in Admin</a></p>
</td></tr>
<tr><td style="padding:18px 30px;background:#f8fafc;border-top:1px solid #eef0f5;text-align:center;font-size:12px;line-height:1.6;color:#7c8597;">
&copy; <?php echo e(date('Y')); ?> Arizona Outfits. Transactional order communication.
</td></tr></table></td></tr></table><?php if(filled(app(\App\Services\StoreSettingsService::class)->settings()->order_email_message)): ?><p style="text-align:center;padding:16px;font-family:Arial"><?php echo e(app(\App\Services\StoreSettingsService::class)->settings()->order_email_message); ?></p><?php endif; ?>
</body></html>