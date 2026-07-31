<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewOrderMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order
    ) {
        $this->order->loadMissing([
            'items.product',
            'items.variant',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New order received - '
                . $this->order->order_number
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.admin-new-order'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}