<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\SendSupplierPurchaseOrderEmail;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPurchaseOrderDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupplierPurchaseOrderDeliveryController extends AdminController
{
    public function store(
        Request $request,
        Supplier $supplier,
        PurchaseOrder $purchaseOrder
    ): RedirectResponse {
        $this->ensurePurchaseOrderBelongsToSupplier(
            $supplier,
            $purchaseOrder
        );

        $validated = $request->validateWithBag(
            'supplierPurchaseOrderEmail',
            [
                'recipient_contact_ids' => [
                    'nullable',
                    'array',
                ],

                'recipient_contact_ids.*' => [
                    'integer',

                    Rule::exists(
                        'supplier_contacts',
                        'id'
                    )->where(
                        fn ($query) =>
                            $query->where(
                                'supplier_id',
                                $supplier->id
                            )
                    ),
                ],

                'include_supplier_email' => [
                    'nullable',
                    'boolean',
                ],

                'subject' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'message' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'attach_pdf' => [
                    'nullable',
                    'boolean',
                ],
            ]
        );

        $contactIds = collect(
            $validated['recipient_contact_ids'] ?? []
        )->map(
            fn ($contactId): int =>
                (int) $contactId
        )->unique();

        $recipientEmails = $supplier
            ->contacts()
            ->whereIn('id', $contactIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email');

        if (
            $request->boolean('include_supplier_email')
            && filled($supplier->email)
        ) {
            $recipientEmails->push(
                $supplier->email
            );
        }

        $recipientEmails = $recipientEmails
            ->filter(
                fn ($email): bool =>
                    filter_var(
                        $email,
                        FILTER_VALIDATE_EMAIL
                    ) !== false
            )
            ->unique(
                fn ($email): string =>
                    strtolower($email)
            )
            ->values();

        if ($recipientEmails->isEmpty()) {
            return redirect()
                ->route(
                    'admin.suppliers.show',
                    $supplier
                )
                ->withErrors(
                    [
                        'recipient_contact_ids' =>
                            'Select at least one contact with an email address or include the supplier email.',
                    ],
                    'supplierPurchaseOrderEmail'
                )
                ->withInput();
        }

        $subject = trim(
            (string) (
                $validated['subject'] ?? ''
            )
        );

        if ($subject === '') {
            $subject = 'Purchase Order '
                . $purchaseOrder->reference
                . ' from '
                . config('app.name');
        }

        $delivery = SupplierPurchaseOrderDelivery::create([
            'supplier_id' => $supplier->id,
            'purchase_order_id' =>
                $purchaseOrder->id,
            'recipient_emails' =>
                $recipientEmails->all(),
            'subject' => $subject,
            'message' =>
                $validated['message'] ?? null,
            'attach_pdf' =>
                $request->boolean('attach_pdf'),
            'status' =>
                SupplierPurchaseOrderDelivery::STATUS_QUEUED,
            'sent_by' => Auth::id(),
            'queued_at' => now(),
        ]);

        SendSupplierPurchaseOrderEmail::dispatch(
            $delivery->id
        );

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Purchase order '
                    . $purchaseOrder->reference
                    . ' was queued for email delivery.'
            );
    }

    private function ensurePurchaseOrderBelongsToSupplier(
        Supplier $supplier,
        PurchaseOrder $purchaseOrder
    ): void {
        abort_unless(
            (int) $purchaseOrder->supplier_id
                === (int) $supplier->id,
            404
        );
    }
}
