<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomerResetPassword extends BaseResetPassword
{
    /**
     * Build the customer-facing Arizona Outfits password reset email.
     */
    public function toMail($notifiable): MailMessage
    {
        $name = trim((string) ($notifiable->name ?? ''));

        if ($name === '') {
            $name = 'Customer';
        }

        $resetUrl = url(
            route(
                'password.reset',
                [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ],
                false
            )
        );

        $expireMinutes = (int) config(
            'auth.passwords.users.expire',
            60
        );

        return (new MailMessage)
            ->subject('Reset Your Arizona Outfits Password')
            ->greeting('Hi '.$name.',')
            ->line(
                'We received a request to reset the password for your Arizona Outfits account.'
            )
            ->action('Reset Password', $resetUrl)
            ->line(
                'For your security, this password reset link will expire in '
                .$expireMinutes
                .' minutes.'
            )
            ->line(
                'If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.'
            )
            ->line(
                'Never share this password reset link with anyone.'
            )
            ->salutation(
                "Regards,\nArizona Outfits"
            );
    }
}
