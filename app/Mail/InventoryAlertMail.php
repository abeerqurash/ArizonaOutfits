<?php

namespace App\Mail;

use App\Models\InventoryAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InventoryAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly InventoryAlert $alert
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->alert->isOutOfStock()
                ? 'Out of stock: '
                    . $this->alert->item_name
                : 'Low stock: '
                    . $this->alert->item_name
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inventory-alert'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}