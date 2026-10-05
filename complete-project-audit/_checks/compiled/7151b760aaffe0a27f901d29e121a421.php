<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($isPr ? 'New PR Enquiry' : 'New Contact Enquiry'); ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#172033;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f4f5f7;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:620px;background:#ffffff;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:26px 30px;background:#1f2230;color:#ffffff;">
                            <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;opacity:.75;">
                                ArizonaOutfits
                            </div>

                            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.3;font-weight:700;">
                                <?php echo e($isPr ? 'New PR Enquiry' : 'New Contact Enquiry'); ?>

                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 30px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding:0 0 14px;font-size:14px;color:#667085;width:165px;vertical-align:top;">
                                        Submitted From
                                    </td>
                                    <td style="padding:0 0 14px;font-size:14px;font-weight:700;vertical-align:top;">
                                        <?php echo e($formSource === 'contact' ? 'Contact Page' : 'Homepage'); ?>

                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 0 14px;font-size:14px;color:#667085;vertical-align:top;">
                                        Enquiry Type
                                    </td>
                                    <td style="padding:0 0 14px;font-size:14px;font-weight:700;vertical-align:top;">
                                        <?php echo e($formType); ?>

                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 0 14px;font-size:14px;color:#667085;vertical-align:top;">
                                        Name
                                    </td>
                                    <td style="padding:0 0 14px;font-size:14px;vertical-align:top;">
                                        <?php echo e($fullName); ?>

                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 0 14px;font-size:14px;color:#667085;vertical-align:top;">
                                        Subject
                                    </td>
                                    <td style="padding:0 0 14px;font-size:14px;vertical-align:top;">
                                        <?php echo e($subject); ?>

                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 0 14px;font-size:14px;color:#667085;vertical-align:top;">
                                        Email
                                    </td>
                                    <td style="padding:0 0 14px;font-size:14px;vertical-align:top;">
                                        <a href="mailto:<?php echo e($emailAddress); ?>" style="color:#172033;text-decoration:underline;">
                                            <?php echo e($emailAddress); ?>

                                        </a>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 0 14px;font-size:14px;color:#667085;vertical-align:top;">
                                        Phone
                                    </td>
                                    <td style="padding:0 0 14px;font-size:14px;vertical-align:top;">
                                        <?php echo e($phoneNumber); ?>

                                    </td>
                                </tr>

                                <?php if($isPr): ?>
                                    <tr>
                                        <td style="padding:0 0 14px;font-size:14px;color:#667085;vertical-align:top;">
                                            Publication / Company / Agency
                                        </td>
                                        <td style="padding:0 0 14px;font-size:14px;vertical-align:top;">
                                            <?php echo e($prOrganization); ?>

                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding:0 0 14px;font-size:14px;color:#667085;vertical-align:top;">
                                            PR Enquiry Type
                                        </td>
                                        <td style="padding:0 0 14px;font-size:14px;vertical-align:top;">
                                            <?php echo e($prEnquiryType); ?>

                                        </td>
                                    </tr>

                                    <?php if(!empty($prWebsite)): ?>
                                        <tr>
                                            <td style="padding:0 0 14px;font-size:14px;color:#667085;vertical-align:top;">
                                                Website / Social
                                            </td>
                                            <td style="padding:0 0 14px;font-size:14px;vertical-align:top;word-break:break-word;">
                                                <a href="<?php echo e($prWebsite); ?>" style="color:#172033;text-decoration:underline;">
                                                    <?php echo e($prWebsite); ?>

                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </table>

                            <div style="margin-top:12px;padding-top:22px;border-top:1px solid #e5e7eb;">
                                <div style="margin-bottom:8px;font-size:14px;color:#667085;">
                                    Message
                                </div>

                                <div style="font-size:14px;line-height:1.7;white-space:pre-wrap;word-break:break-word;">
                                    <?php echo e(!empty($messageBox) ? $messageBox : 'No message provided.'); ?>

                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 30px;background:#f8fafc;border-top:1px solid #e5e7eb;font-size:12px;line-height:1.6;color:#667085;">
                            This enquiry was submitted through the ArizonaOutfits
                            <?php echo e($formSource === 'contact' ? 'Contact page' : 'Homepage'); ?>.
                            Replying to this email will reply directly to <?php echo e($fullName); ?>.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\emails\home-form.blade.php ENDPATH**/ ?>