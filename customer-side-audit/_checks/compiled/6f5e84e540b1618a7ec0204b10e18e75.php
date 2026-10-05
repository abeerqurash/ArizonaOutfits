<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($renderedSubject); ?></title>
</head>

<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#172033;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f4f6fb;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:680px;background:#ffffff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;box-shadow:0 14px 38px rgba(15,23,42,.08);">
                    <tr>
                        <td style="padding:26px 30px;background:#111827;color:#ffffff;">
                            <div style="font-size:12px;line-height:1.4;letter-spacing:.14em;text-transform:uppercase;color:#a5b4fc;">
                                Arizona Outfits
                            </div>

                            <h1 style="margin:7px 0 0;font-size:24px;line-height:1.35;font-weight:800;color:#ffffff;">
                                <?php echo e($renderedSubject); ?>

                            </h1>

                            <div style="margin-top:8px;font-size:13px;line-height:1.6;color:#cbd5e1;">
                                Order &amp; payment notification
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px;font-size:15px;line-height:1.75;color:#334155;">
                            <?php echo $renderedBody; ?>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 30px;background:#f8fafc;border-top:1px solid #eef0f5;text-align:center;font-size:12px;line-height:1.65;color:#7c8597;">
                            This transactional email was sent by
                            <strong style="color:#475569;"><?php echo e(config('app.name', 'Arizona Outfits')); ?></strong>
                            regarding an Arizona Outfits order.
                            <br>
                            &copy; <?php echo e(date('Y')); ?> Arizona Outfits. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
<?php if(filled(app(\App\Services\StoreSettingsService::class)->settings()->order_email_message)): ?><p style="text-align:center;padding:16px;font-family:Arial"><?php echo e(app(\App\Services\StoreSettingsService::class)->settings()->order_email_message); ?></p><?php endif; ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\customer-admin-sync-files\_checks/../_staged/resources/views/emails/orders/dynamic-template.blade.php ENDPATH**/ ?>