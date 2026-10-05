<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Order status updated</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6fb;padding:32px 12px;"><tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;">
<tr><td style="padding:25px 30px;background:#111827;color:#fff;">
<div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#a5b4fc;">Arizona Outfits</div>
<div style="margin-top:7px;font-size:24px;font-weight:800;">Order status update</div>
<div style="margin-top:7px;font-size:13px;color:#cbd5e1;">There’s a new update on your Arizona Outfits order.</div>
</td></tr><tr><td style="padding:30px;">
<?php
$refundMeta = is_array($order->payment_metadata ?? null) ? ($order->payment_metadata['refund'] ?? []) : [];
$bankRefundReference = is_array($refundMeta) ? ($refundMeta['bank_refund_reference'] ?? null) : null;
$refundedAt = is_array($refundMeta) ? ($refundMeta['refunded_at'] ?? null) : null;
$currency = strtoupper((string) ($order->currency ?: 'USD'));
?>
<p style="margin:0 0 20px;line-height:1.7;">Hi <strong><?php echo e($customerName); ?></strong>, your order <strong><?php echo e($order->order_number); ?></strong> has moved to a new stage.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
<td width="48%" style="padding:16px;background:#f8fafc;border-radius:10px;"><div style="font-size:11px;color:#64748b;text-transform:uppercase;">Previous</div><div style="margin-top:5px;font-weight:700;"><?php echo e($previousStatus); ?></div></td>
<td width="4%"></td>
<td width="48%" style="padding:16px;background:#eef2ff;border:1px solid #dfe3ff;border-radius:10px;"><div style="font-size:11px;color:#6366f1;text-transform:uppercase;">Current</div><div style="margin-top:5px;font-weight:800;color:#4338ca;"><?php echo e($currentStatus); ?></div></td>
</tr></table>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:22px;border-collapse:collapse;">
<?php if(!empty($order->tracking_number)): ?><tr><td style="padding:10px 0;color:#64748b;">Tracking number</td><td align="right" style="padding:10px 0;font-weight:700;"><?php echo e($order->tracking_number); ?></td></tr><?php endif; ?>
<tr><td style="padding:10px 0;border-top:1px solid #eef0f5;color:#64748b;">Payment status</td><td align="right" style="padding:10px 0;border-top:1px solid #eef0f5;"><?php echo e(ucwords(str_replace(['_','-'],' ',(string)$order->payment_status))); ?></td></tr>
<?php if((string)$order->order_status === 'refunded'): ?>
<tr><td style="padding:10px 0;border-top:1px solid #eef0f5;color:#64748b;">Refund method</td><td align="right" style="padding:10px 0;border-top:1px solid #eef0f5;"><?php echo e(($order->payment_provider === 'bank_transfer' || $order->payment_method === 'bank_transfer') ? 'Bank Transfer' : 'Card / Stripe'); ?></td></tr>
<?php if($bankRefundReference): ?><tr><td style="padding:10px 0;border-top:1px solid #eef0f5;color:#64748b;">Refund reference</td><td align="right" style="padding:10px 0;border-top:1px solid #eef0f5;font-weight:700;"><?php echo e($bankRefundReference); ?></td></tr><?php endif; ?>
<?php if($refundedAt): ?><tr><td style="padding:10px 0;border-top:1px solid #eef0f5;color:#64748b;">Refunded on</td><td align="right" style="padding:10px 0;border-top:1px solid #eef0f5;"><?php echo e(\Carbon\Carbon::parse($refundedAt)->format('d M Y, h:i A')); ?></td></tr><?php endif; ?>
<?php endif; ?>
<tr><td style="padding:13px 0;border-top:1px solid #dfe3eb;font-weight:800;">Order total</td><td align="right" style="padding:13px 0;border-top:1px solid #dfe3eb;font-weight:800;color:#4f46e5;"><?php echo e($currency); ?> <?php echo e(number_format((float)$order->total,2)); ?></td></tr>
</table>
<?php if(in_array($currentStatus,['Shipped','Out For Delivery'])): ?><div style="margin-top:20px;padding:15px;background:#eff6ff;border-radius:9px;color:#1e40af;">Your order is on its way. Keep your tracking number available.</div>
<?php elseif(in_array($currentStatus,['Delivered','Completed'])): ?><div style="margin-top:20px;padding:15px;background:#ecfdf5;border-radius:9px;color:#047857;">Your order has been <?php echo e(strtolower($currentStatus)); ?>. Thank you for shopping with us.</div>
<?php elseif($currentStatus === 'Cancelled'): ?><div style="margin-top:20px;padding:15px;background:#fff7ed;border-radius:9px;color:#c2410c;">Your order has been cancelled. Contact support if you need assistance.</div><?php endif; ?>
</td></tr>
<tr><td style="padding:18px 30px;background:#f8fafc;border-top:1px solid #eef0f5;text-align:center;font-size:12px;line-height:1.6;color:#7c8597;">
&copy; <?php echo e(date('Y')); ?> Arizona Outfits. Transactional order communication.
</td></tr></table></td></tr></table></body></html><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\emails\orders\status-updated.blade.php ENDPATH**/ ?>