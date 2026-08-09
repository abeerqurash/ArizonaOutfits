<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use App\Models\SupplierDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SupplierDocumentController extends AdminController
{
    public function store(
        Request $request,
        Supplier $supplier
    ): RedirectResponse {
        $validated = $request->validateWithBag(
            'supplierDocument',
            [
                'title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'document_type' => [
                    'required',
                    Rule::in([
                        SupplierDocument::TYPE_CONTRACT,
                        SupplierDocument::TYPE_CERTIFICATE,
                        SupplierDocument::TYPE_PRICE_LIST,
                        SupplierDocument::TYPE_BANK_DETAILS,
                        SupplierDocument::TYPE_TAX_DOCUMENT,
                        SupplierDocument::TYPE_INSURANCE,
                        SupplierDocument::TYPE_OTHER,
                    ]),
                ],

                'document' => [
                    'required',
                    'file',
                    'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp',
                    'max:10240',
                ],

                'expires_at' => [
                    'nullable',
                    'date',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:3000',
                ],
            ],
            [
                'document.required' =>
                    'Choose a supplier document to upload.',

                'document.mimes' =>
                    'The document must be a PDF, Office document, CSV, or supported image.',

                'document.max' =>
                    'The supplier document must not exceed 10 MB.',
            ]
        );

        $file = $validated['document'];

        $path = $file->store(
            'suppliers/'
                . $supplier->id
                . '/documents',
            'public'
        );

        try {
            $supplier->documents()->create([
                'title' => $validated['title'],
                'document_type' =>
                    $validated['document_type'],
                'file_path' => $path,
                'original_name' => Str::limit(
                    $file->getClientOriginalName(),
                    255,
                    ''
                ),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'expires_at' =>
                    $validated['expires_at'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'uploaded_by' => Auth::id(),
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(
                $path
            );

            throw $exception;
        }

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Supplier document was uploaded successfully.'
            );
    }

    public function download(
        Supplier $supplier,
        SupplierDocument $document
    ): StreamedResponse {
        $this->ensureDocumentBelongsToSupplier(
            $supplier,
            $document
        );

        abort_unless(
            Storage::disk('public')->exists(
                $document->file_path
            ),
            404,
            'The requested supplier document file was not found.'
        );

        return Storage::disk('public')->download(
            $document->file_path,
            $document->original_name
        );
    }

    public function destroy(
        Supplier $supplier,
        SupplierDocument $document
    ): RedirectResponse {
        $this->ensureDocumentBelongsToSupplier(
            $supplier,
            $document
        );

        $documentTitle = $document->title;
        $filePath = $document->file_path;

        $document->delete();

        Storage::disk('public')->delete(
            $filePath
        );

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Document '
                    . $documentTitle
                    . ' was deleted successfully.'
            );
    }

    private function ensureDocumentBelongsToSupplier(
        Supplier $supplier,
        SupplierDocument $document
    ): void {
        abort_unless(
            (int) $document->supplier_id
                === (int) $supplier->id,
            404
        );
    }
}
