<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $subject ?? 'Message from Arizona Outfits' }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f6fb;padding:32px 12px;"><tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;">
<tr><td style="padding:25px 30px;background:#111827;color:#fff;">
<div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#a5b4fc;">Arizona Outfits</div>
<div style="margin-top:7px;font-size:24px;font-weight:800;">A message about your order</div>
<div style="margin-top:7px;font-size:13px;color:#cbd5e1;">Arizona Outfits Customer Care</div>
</td></tr><tr><td style="padding:30px;">
<p style="margin:0 0 8px;line-height:1.7;">Hi <strong>{{ $customerName ?? 'Customer' }}</strong>,</p>
<p style="margin:0 0 20px;font-size:13px;color:#7c8597;">Regarding order <strong style="color:#111827;">{{ $order->order_number ?: ('ORD-' . str_pad((string)$order->id,6,'0',STR_PAD_LEFT)) }}</strong></p>
<div style="margin:0 0 24px;padding:20px;background:#f8fafc;border-left:4px solid #635bff;border-radius:0 10px 10px 0;font-size:15px;line-height:1.8;">{!! nl2br(e($messageText)) !!}</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
<tr><td style="padding:11px 0;color:#64748b;">Order status</td><td align="right" style="padding:11px 0;font-weight:700;">{{ ucwords(str_replace(['_','-'],' ',(string)$order->order_status)) }}</td></tr>
@if(!empty($order->tracking_number))<tr><td style="padding:11px 0;border-top:1px solid #eef0f5;color:#64748b;">Tracking</td><td align="right" style="padding:11px 0;border-top:1px solid #eef0f5;font-weight:700;">{{ $order->tracking_number }}</td></tr>@endif
<tr><td style="padding:11px 0;border-top:1px solid #eef0f5;color:#64748b;">Payment</td><td align="right" style="padding:11px 0;border-top:1px solid #eef0f5;">{{ ucwords(str_replace(['_','-'],' ',(string)$order->payment_status)) }}</td></tr>
<tr><td style="padding:13px 0;border-top:1px solid #dfe3eb;font-weight:800;">Order total</td><td align="right" style="padding:13px 0;border-top:1px solid #dfe3eb;font-weight:800;color:#4f46e5;">{{ strtoupper((string)($order->currency ?: 'USD')) }} {{ number_format((float)$order->total,2) }}</td></tr>
</table>
</td></tr>
<tr><td style="padding:18px 30px;background:#f8fafc;border-top:1px solid #eef0f5;text-align:center;font-size:12px;line-height:1.6;color:#7c8597;">
&copy; {{ date('Y') }} Arizona Outfits. Transactional order communication.
</td></tr></table></td></tr></table></body></html>