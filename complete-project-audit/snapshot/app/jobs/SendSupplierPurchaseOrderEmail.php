<?php

namespace App\Jobs;

use App\Mail\SupplierPurchaseOrderMail;
use App\Models\SupplierPurchaseOrderDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SendSupplierPurchaseOrderEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $deliveryId
    ) {
    }

    public function backoff(): array
    {
        return [
            60,
            300,
            900,
        ];
    }

    public function handle(): void
    {
        $delivery = SupplierPurchaseOrderDelivery::query()
            ->with([
                'supplier',
                'purchaseOrder.supplier',
                'purchaseOrder.items',
            ])
            ->findOrFail(
                $this->deliveryId
            );

        if (
            $delivery->status
            === SupplierPurchaseOrderDelivery::STATUS_SENT
        ) {
            return;
        }

        $recipientEmails = collect(
            $delivery->recipient_emails
        )->filter()->values();

        if ($recipientEmails->isEmpty()) {
            throw new RuntimeException(
                'No valid supplier email recipients were stored.'
            );
        }

        $primaryRecipient =
            $recipientEmails->shift();

        Mail::to($primaryRecipient)
            ->cc($recipientEmails->all())
            ->send(
                new SupplierPurchaseOrderMail(
                    $delivery->purchaseOrder,
                    $delivery
                )
            );

        $delivery->update([
            'status' =>
                SupplierPurchaseOrderDelivery::STATUS_SENT,
            'sent_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ]);
    }

    public function failed(
        ?Throwable $exception
    ): void {
        SupplierPurchaseOrderDelivery::query()
            ->whereKey($this->deliveryId)
            ->update([
                'status' =>
                    SupplierPurchaseOrderDelivery::STATUS_FAILED,
                'failed_at' => now(),
                'error_message' => Str::limit(
                    $exception?->getMessage()
                        ?? 'Unknown email delivery failure.',
                    5000
                ),
            ]);
    }
}
