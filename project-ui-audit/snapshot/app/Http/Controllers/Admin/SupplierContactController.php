<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use App\Models\SupplierContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierContactController extends AdminController
{
    /**
     * Store a new supplier contact.
     */
    public function store(
        Request $request,
        Supplier $supplier
    ): RedirectResponse {
        $validated = $this->validateContact(
            $request
        );

        $validated['supplier_id'] =
            $supplier->id;

        $validated['is_primary'] =
            $request->boolean('is_primary');

        $validated['receives_purchase_orders'] =
            $request->boolean(
                'receives_purchase_orders'
            );

        DB::transaction(
            function () use (
                $supplier,
                $validated
            ): void {
                if ($validated['is_primary']) {
                    $supplier->contacts()
                        ->update([
                            'is_primary' => false,
                        ]);
                }

                $supplier->contacts()->create(
                    $validated
                );
            }
        );

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Supplier contact was added successfully.'
            );
    }

    /**
     * Update an existing supplier contact.
     */
    public function update(
        Request $request,
        Supplier $supplier,
        SupplierContact $contact
    ): RedirectResponse {
        $this->ensureContactBelongsToSupplier(
            $supplier,
            $contact
        );

        $validated = $this->validateContact(
            $request,
            $contact
        );

        $validated['is_primary'] =
            $request->boolean('is_primary');

        $validated['receives_purchase_orders'] =
            $request->boolean(
                'receives_purchase_orders'
            );

        DB::transaction(
            function () use (
                $supplier,
                $contact,
                $validated
            ): void {
                if ($validated['is_primary']) {
                    $supplier->contacts()
                        ->whereKeyNot($contact->id)
                        ->update([
                            'is_primary' => false,
                        ]);
                }

                $contact->update(
                    $validated
                );
            }
        );

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Supplier contact was updated successfully.'
            );
    }

    /**
     * Delete a supplier contact.
     */
    public function destroy(
        Supplier $supplier,
        SupplierContact $contact
    ): RedirectResponse {
        $this->ensureContactBelongsToSupplier(
            $supplier,
            $contact
        );

        $contactName = $contact->name;

        $contact->delete();

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Contact '
                    . $contactName
                    . ' was deleted successfully.'
            );
    }

    /**
     * Validate supplier contact input.
     */
    private function validateContact(
        Request $request,
        ?SupplierContact $contact = null
    ): array {
        return $request->validateWithBag(
            'supplierContact',
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'job_title' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'department' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',

                    Rule::unique(
                        'supplier_contacts',
                        'email'
                    )
                        ->where(
                            fn ($query) =>
                                $query->where(
                                    'supplier_id',
                                    $request->route(
                                        'supplier'
                                    )->id
                                )
                        )
                        ->ignore($contact?->id),
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'mobile' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'is_primary' => [
                    'nullable',
                    'boolean',
                ],

                'receives_purchase_orders' => [
                    'nullable',
                    'boolean',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:3000',
                ],
            ],
            [
                'name.required' =>
                    'The contact name is required.',

                'email.email' =>
                    'Enter a valid contact email address.',

                'email.unique' =>
                    'This email is already used by another contact for this supplier.',
            ]
        );
    }

    /**
     * Protect against editing another supplier's contact.
     */
    private function ensureContactBelongsToSupplier(
        Supplier $supplier,
        SupplierContact $contact
    ): void {
        abort_unless(
            (int) $contact->supplier_id
                === (int) $supplier->id,
            404
        );
    }
}
