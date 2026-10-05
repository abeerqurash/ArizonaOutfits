<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use App\Models\SupplierPurchaseOrderDelivery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SupplierPurchaseOrderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public PurchaseOrder $purchaseOrder,
        public SupplierPurchaseOrderDelivery $delivery
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->delivery->subject
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.suppliers.purchase-order'
        );
    }

    public function attachments(): array
    {
        if (!$this->delivery->attach_pdf) {
            return [];
        }

        $fileName = 'purchase-order-'
            . Str::slug(
                (string) $this->purchaseOrder->reference
            )
            . '.pdf';

        return [
            Attachment::fromData(
                function (): string {
                    return Pdf::loadView(
                        'admin.purchase-orders.pdf.supplier-delivery',
                        [
                            'purchaseOrder' =>
                                $this->purchaseOrder,
                        ]
                    )
                        ->setPaper('a4', 'portrait')
                        ->output();
                },
                $fileName
            )->withMime('application/pdf'),
        ];
    }
}
