<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(!empty($isPr) ? 'New Blog PR Enquiry' : 'New Blog Contact Enquiry'); ?></title>
</head>

<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f4f6f8;margin:0;padding:0;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:640px;background:#ffffff;border:1px solid #e5eaf1;border-radius:14px;overflow:hidden;">

                <tr>
                    <td style="padding:26px 30px;background:#172033;color:#ffffff;">
                        <div style="font-size:11px;line-height:1.4;letter-spacing:2.4px;text-transform:uppercase;color:#a7f3d0;font-weight:700;">
                            ArizonaOutfits
                        </div>

                        <h1 style="margin:8px 0 0;font-size:24px;line-height:1.3;color:#ffffff;font-weight:700;">
                            <?php echo e(!empty($isPr) ? 'New Blog PR Enquiry' : 'New Blog Contact Enquiry'); ?>

                        </h1>

                        <p style="margin:8px 0 0;font-size:13px;line-height:1.6;color:#cbd5e1;">
                            A visitor submitted an enquiry from a Blog article.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:26px 30px 8px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:0 0 16px;">
                                    <div style="padding:16px;background:#f8fafc;border:1px solid #e5eaf1;border-radius:10px;">
                                        <div style="font-size:10px;line-height:1.4;letter-spacing:1.8px;text-transform:uppercase;color:#0f766e;font-weight:700;margin-bottom:7px;">
                                            Blog Context
                                        </div>

                                        <div style="font-size:16px;line-height:1.5;font-weight:700;color:#172033;">
                                            <?php echo e($blog_title ?? 'N/A'); ?>

                                        </div>

                                        <?php if(!empty($blog_slug)): ?>
                                            <div style="margin-top:5px;font-size:12px;line-height:1.5;color:#64748b;word-break:break-word;">
                                                Slug: <?php echo e($blog_slug); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:4px 30px 0;">
                        <div style="font-size:11px;line-height:1.4;letter-spacing:1.8px;text-transform:uppercase;color:#0f766e;font-weight:700;margin-bottom:12px;">
                            Enquiry Details
                        </div>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                            <tr>
                                <td style="width:150px;padding:11px 12px;border-bottom:1px solid #e5eaf1;font-size:12px;color:#64748b;font-weight:700;vertical-align:top;">
                                    Enquiry Type
                                </td>
                                <td style="padding:11px 12px;border-bottom:1px solid #e5eaf1;font-size:14px;color:#172033;vertical-align:top;">
                                    <?php echo e($formType); ?>

                                </td>
                            </tr>

                            <tr>
                                <td style="width:150px;padding:11px 12px;border-bottom:1px solid #e5eaf1;font-size:12px;color:#64748b;font-weight:700;vertical-align:top;">
                                    Name
                                </td>
                                <td style="padding:11px 12px;border-bottom:1px solid #e5eaf1;font-size:14px;color:#172033;vertical-align:top;">
                                    <?php echo e($fullName); ?>

                                </td>
                            </tr>

                            <tr>
                                <td style="width:150px;padding:11px 12px;border-bottom:1px solid #e5eaf1;font-size:12px;color:#64748b;font-weight:700;vertical-align:top;">
                                    Subject
                                </td>
                                <td style="padding:11px 12px;border-bottom:1px solid #e5eaf1;font-size:14px;color:#172033;vertical-align:top;">
                                    <?php echo e($subject); ?>

                                </td>
                            </tr>

                            <tr>
                                <td style="width:150px;padding:11px 12px;border-bottom:1px solid #e5eaf1;font-size:12px;color:#64748b;font-weight:700;vertical-align:top;">
                                    Email
                                </td>
                                <td style="padding:11px 12px;border-bottom:1px solid #e5eaf1;font-size:14px;color:#172033;vertical-align:top;word-break:break-word;">
                                    <a href="mailto:<?php echo e($emailAddress); ?>" style="color:#0f766e;text-decoration:none;">
                                        <?php echo e($emailAddress); ?>

                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td style="width:150px;padding:11px 12px;border-bottom:1px solid #e5eaf1;font-size:12px;color:#64748b;font-weight:700;vertical-align:top;">
                                    Phone
                                </td>
                                <td style="padding:11px 12px;border-bottom:1px solid #e5eaf1;font-size:14px;color:#172033;vertical-align:top;">
                                    <?php echo e($phoneNumber); ?>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php if(!empty($isPr)): ?>
                    <tr>
                        <td style="padding:24px 30px 0;">
                            <div style="padding:18px;background:#f0fdfa;border:1px solid #ccfbf1;border-radius:10px;">
                                <div style="font-size:11px;line-height:1.4;letter-spacing:1.8px;text-transform:uppercase;color:#0f766e;font-weight:700;margin-bottom:12px;">
                                    PR Details
                                </div>

                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="width:150px;padding:9px 0;font-size:12px;color:#64748b;font-weight:700;vertical-align:top;">
                                            Publication / Company
                                        </td>
                                        <td style="padding:9px 0;font-size:14px;color:#172033;vertical-align:top;">
                                            <?php echo e($prOrganization ?? 'N/A'); ?>

                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="width:150px;padding:9px 0;font-size:12px;color:#64748b;font-weight:700;vertical-align:top;">
                                            PR Enquiry Type
                                        </td>
                                        <td style="padding:9px 0;font-size:14px;color:#172033;vertical-align:top;">
                                            <?php echo e($prEnquiryType ?? 'N/A'); ?>

                                        </td>
                                    </tr>

                                    <?php if(!empty($prWebsite)): ?>
                                        <tr>
                                            <td style="width:150px;padding:9px 0;font-size:12px;color:#64748b;font-weight:700;vertical-align:top;">
                                                Website / Social
                                            </td>
                                            <td style="padding:9px 0;font-size:14px;color:#172033;vertical-align:top;word-break:break-word;">
                                                <a href="<?php echo e($prWebsite); ?>" style="color:#0f766e;text-decoration:none;">
                                                    <?php echo e($prWebsite); ?>

                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <td style="padding:24px 30px 30px;">
                        <div style="font-size:11px;line-height:1.4;letter-spacing:1.8px;text-transform:uppercase;color:#0f766e;font-weight:700;margin-bottom:10px;">
                            Message
                        </div>

                        <div style="padding:16px;background:#f8fafc;border:1px solid #e5eaf1;border-radius:10px;font-size:14px;line-height:1.7;color:#334155;white-space:pre-wrap;word-break:break-word;"><?php echo e(!empty($messageBox) ? $messageBox : 'No message provided.'); ?></div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 30px;background:#f8fafc;border-top:1px solid #e5eaf1;">
                        <p style="margin:0;font-size:11px;line-height:1.6;color:#64748b;">
                            This enquiry was submitted through the ArizonaOutfits Blog Contact / PR form.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\emails\blog-form.blade.php ENDPATH**/ ?>