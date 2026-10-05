<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyPendingEmail extends Notification
{
    use Queueable;

    public function __construct(
        private readonly int $userId,
        private readonly string $pendingEmail,
        private readonly string $customerName
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(
        object $notifiable
    ): MailMessage {
        $verificationUrl =
            URL::temporarySignedRoute(
                'customer.security.email.change.verify',
                now()->addMinutes(60),
                [
                    'user' => $this->userId,
                    'email' => $this->pendingEmail,
                ]
            );

        return (new MailMessage)
            ->subject(
                'Verify your new Arizona Outfits email address'
            )
            ->greeting(
                'Hello ' . $this->customerName . ','
            )
            ->line(
                'You requested to use this email address with your Arizona Outfits account.'
            )
            ->line(
                'Your current email remains active until this new address is verified.'
            )
            ->action(
                'Verify New Email',
                $verificationUrl
            )
            ->line(
                'This verification link expires in 60 minutes.'
            )
            ->line(
                'If you did not request this change, you can ignore this email.'
            );
    }
}