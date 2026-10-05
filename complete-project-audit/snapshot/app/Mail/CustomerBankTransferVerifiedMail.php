<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerBankTransferVerifiedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Verified - Order '
                . $this->order->order_number
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.bank-transfer-verified'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
