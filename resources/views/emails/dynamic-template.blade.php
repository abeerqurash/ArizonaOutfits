<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $renderedSubject }}</title></head>
<body style="margin:0;background:#f3f4f6;color:#1f2937;font-family:Arial,sans-serif">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:28px 12px;background:#f3f4f6"><tr><td align="center">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;overflow:hidden;border-radius:16px;background:#fff;box-shadow:0 12px 35px rgba(15,23,42,.08)">
<tr><td style="padding:25px 30px;background:#111827;color:#fff"><small style="color:#c7d2fe;letter-spacing:.12em;text-transform:uppercase">{{ config('app.name') }}</small><h2 style="margin:7px 0 0">{{ $renderedSubject }}</h2></td></tr>
<tr><td style="padding:30px;font-size:15px;line-height:1.7">{!! $renderedBody !!}</td></tr>
<tr><td style="padding:18px 30px;background:#f9fafb;color:#6b7280;font-size:12px">This automated email was sent by {{ config('app.name') }}.</td></tr>
</table></td></tr></table></body></html>
