<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderStatusUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $previousStatus
    ) {
        $this->order->loadMissing([
            'items.product',
            'items.variant',
            'user',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf(
                'Order %s status updated - %s',
                $this->order->order_number,
                $this->statusLabel(
                    $this->order->order_status
                )
            )
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.status-updated',
            with: [
                'order' => $this->order,
                'previousStatus' =>
                    $this->statusLabel(
                        $this->previousStatus
                    ),
                'currentStatus' =>
                    $this->statusLabel(
                        $this->order->order_status
                    ),
                'customerName' =>
                    $this->customerName(),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function customerName(): string
    {
        $possibleNames = [
            $this->order->billing_name ?? null,
            $this->order->shipping_name ?? null,
            $this->order->customer_name ?? null,
            $this->order->user?->name,
        ];

        foreach ($possibleNames as $name) {
            if (
                is_string($name)
                && trim($name) !== ''
            ) {
                return trim($name);
            }
        }

        return 'Customer';
    }

    private function statusLabel(
        ?string $status
    ): string {
        if (
            !is_string($status)
            || trim($status) === ''
        ) {
            return 'Not set';
        }

        return ucwords(
            str_replace(
                ['_', '-'],
                ' ',
                trim($status)
            )
        );
    }
}
