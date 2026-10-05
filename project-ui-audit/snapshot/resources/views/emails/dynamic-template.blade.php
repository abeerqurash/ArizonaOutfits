<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $renderedSubject }}</title>
</head>

<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f3f4f6;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:680px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 12px 35px rgba(15,23,42,.08);">
                    <tr>
                        <td style="padding:25px 30px;background:#111827;color:#ffffff;">
                            <div style="font-size:12px;line-height:1.4;letter-spacing:.12em;text-transform:uppercase;color:#c7d2fe;">
                                {{ config('app.name', 'Arizona Outfits') }}
                            </div>

                            <h1 style="margin:7px 0 0;font-size:24px;line-height:1.35;color:#ffffff;">
                                {{ $renderedSubject }}
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px;font-size:15px;line-height:1.7;color:#374151;">
                            {!! $renderedBody !!}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 30px;background:#f9fafb;border-top:1px solid #eeeeee;text-align:center;font-size:12px;line-height:1.6;color:#6b7280;">
                            This automated email was sent by
                            {{ config('app.name', 'Arizona Outfits') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
