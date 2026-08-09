@extends('admin.layouts.app')

@section('title', $supplier->company_name)

@section('content')

<div class="supplier-profile-page">

    @if (session('success'))

    <div class="supplier-profile-alert success">

        <i class="fa-solid fa-circle-check"></i>

        <span>
            {{ session('success') }}
        </span>

    </div>

    @endif

    @if (session('error'))

    <div class="supplier-profile-alert error">

        <i class="fa-solid fa-circle-exclamation"></i>

        <span>
            {{ session('error') }}
        </span>

    </div>

    @endif

    <section class="supplier-profile-header">

        <div class="supplier-profile-heading">

            <div class="supplier-profile-avatar">

                {{
                    strtoupper(
                        substr(
                            trim($supplier->company_name),
                            0,
                            1
                        )
                    )
                }}

            </div>

            <div>

                <span class="supplier-profile-eyebrow">
                    Supplier profile
                </span>

                <div class="supplier-profile-title-row">

                    <h1>
                        {{ $supplier->company_name }}
                    </h1>

                    <span
                        class="supplier-profile-status {{
                            $supplier->status
                        }}">

                        <span></span>

                        {{ $supplier->status_label }}

                    </span>

                    @if ($supplier->is_preferred)

                    <span class="supplier-profile-preferred">

                        <i class="fa-solid fa-star"></i>

                        Preferred

                    </span>

                    @endif

                </div>

                <p>

                    {{
                        $supplier->supplier_code
                        ?: 'No supplier code'
                    }}

                    @if ($supplier->country)

                    <span>•</span>

                    {{ $supplier->country }}

                    @endif

                </p>

            </div>

        </div>

        <div class="supplier-profile-actions">

            <a
                href="{{ route('admin.suppliers.index') }}"
                class="supplier-profile-button secondary">

                <i class="fa-solid fa-arrow-left"></i>

                All Suppliers

            </a>

            <a
                href="{{ route(
                    'admin.suppliers.edit',
                    $supplier
                ) }}"
                class="supplier-profile-button edit">

                <i class="fa-regular fa-pen-to-square"></i>

                Edit Supplier

            </a>

            <a
                href="{{ route(
                    'admin.suppliers.products.index',
                    $supplier
                ) }}"
                class="supplier-profile-button purchase">

                <i class="fa-solid fa-boxes-stacked"></i>

                Supplier Products

            </a>

            <a
                href="{{ route('admin.purchase-orders.index') }}"
                class="supplier-profile-button purchase">

                <i class="fa-solid fa-file-invoice-dollar"></i>

                Purchase Orders

            </a>

        </div>

    </section>

    <section class="supplier-profile-summary-grid">

        <article class="supplier-profile-summary-card">

            <span class="supplier-profile-summary-icon orders">

                <i class="fa-solid fa-file-invoice"></i>

            </span>

            <div>

                <span>
                    Total Purchase Orders
                </span>

                <strong>
                    {{ number_format($totalPurchaseOrders) }}
                </strong>

                <small>
                    All supplier orders
                </small>

            </div>

        </article>

        <article class="supplier-profile-summary-card">

            <span class="supplier-profile-summary-icon open">

                <i class="fa-solid fa-hourglass-half"></i>

            </span>

            <div>

                <span>
                    Open Purchase Orders
                </span>

                <strong>
                    {{ number_format($openPurchaseOrders) }}
                </strong>

                <small>
                    Draft, ordered or partial
                </small>

            </div>

        </article>

        <article class="supplier-profile-summary-card">

            <span class="supplier-profile-summary-icon received">

                <i class="fa-solid fa-box-circle-check"></i>

            </span>

            <div>

                <span>
                    Received Orders
                </span>

                <strong>
                    {{ number_format($receivedPurchaseOrders) }}
                </strong>

                <small>
                    Fully completed orders
                </small>

            </div>

        </article>

        <article class="supplier-profile-summary-card">

            <span class="supplier-profile-summary-icon spend">

                <i class="fa-solid fa-sterling-sign"></i>

            </span>

            <div>

                <span>
                    Total Spend
                </span>

                <strong>
                    £{{ number_format($totalSpend, 2) }}
                </strong>

                <small>
                    Excluding cancelled orders
                </small>

            </div>

        </article>

        <article class="supplier-profile-summary-card">

            <span class="supplier-profile-summary-icon average">

                <i class="fa-solid fa-chart-line"></i>

            </span>

            <div>

                <span>
                    Average Order Value
                </span>

                <strong>
                    £{{ number_format($averageOrderValue, 2) }}
                </strong>

                <small>
                    Average non-cancelled order
                </small>

            </div>

        </article>

        <article class="supplier-profile-summary-card">

            <span class="supplier-profile-summary-icon rating">

                <i class="fa-solid fa-star"></i>

            </span>

            <div>

                <span>
                    Average Rating
                </span>

                <strong>
                    {{ number_format($averageRating, 2) }}
                </strong>

                <small>
                    Supplier performance score
                </small>

            </div>

        </article>

    </section>

    <section
        class="supplier-analytics-panel"
        id="supplierAnalyticsPanel">

        <div class="supplier-analytics-header">

            <div>

                <span class="supplier-profile-eyebrow">
                    Procurement intelligence
                </span>

                <h2>
                    Supplier Analytics
                </h2>

                <p>
                    Purchasing activity, operational performance and compliance health.
                </p>

            </div>

            <span class="supplier-analytics-period">

                <i class="fa-regular fa-calendar"></i>

                Spend chart: last 12 months

            </span>

        </div>

        <div class="supplier-analytics-kpi-grid">

            <article>

                <span class="supplier-analytics-kpi-icon received">
                    <i class="fa-solid fa-box-circle-check"></i>
                </span>

                <div>

                    <span>Order completion</span>

                    <strong>
                        {{ number_format($supplierAnalytics['received_rate'], 1) }}%
                    </strong>

                    <small>
                        {{ number_format($receivedPurchaseOrders) }} received orders
                    </small>

                </div>

            </article>

            <article>

                <span class="supplier-analytics-kpi-icon cancelled">
                    <i class="fa-solid fa-ban"></i>
                </span>

                <div>

                    <span>Cancellation rate</span>

                    <strong>
                        {{ number_format($supplierAnalytics['cancellation_rate'], 1) }}%
                    </strong>

                    <small>
                        Lower is better
                    </small>

                </div>

            </article>

            <article>

                <span class="supplier-analytics-kpi-icon recommend">
                    <i class="fa-solid fa-thumbs-up"></i>
                </span>

                <div>

                    <span>Recommendation rate</span>

                    <strong>
                        {{ number_format($supplierAnalytics['recommendation_rate'], 1) }}%
                    </strong>

                    <small>
                        From {{ number_format($supplier->ratings->count()) }} reviews
                    </small>

                </div>

            </article>

            <article>

                <span class="supplier-analytics-kpi-icon compliance">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>

                <div>

                    <span>Document compliance</span>

                    <strong>
                        {{ number_format($supplierAnalytics['document_compliance_rate'], 1) }}%
                    </strong>

                    <small>
                        {{ number_format($supplierAnalytics['expired_documents']) }} expired,
                        {{ number_format($supplierAnalytics['expiring_documents']) }} expiring soon
                    </small>

                </div>

            </article>

        </div>

        <div class="supplier-analytics-chart-grid">

            <article class="supplier-analytics-chart-card spend-chart-card">

                <div class="supplier-analytics-card-header">

                    <div>

                        <span>Purchasing trend</span>

                        <h3>Monthly Spend</h3>

                    </div>

                    <strong>
                        £{{ number_format($monthlySupplierSpend->sum('spend'), 2) }}
                    </strong>

                </div>

                <div
                    class="supplier-monthly-spend-chart"
                    role="img"
                    aria-label="Supplier monthly spend for the last twelve months">

                    @foreach ($monthlySupplierSpend as $month)

                    @php
                        $barHeight = max(
                            3,
                            round(
                                $month['spend']
                                / $maxMonthlySupplierSpend
                                * 100,
                                2
                            )
                        );
                    @endphp

                    <div
                        class="supplier-spend-bar-column"
                        tabindex="0"
                        aria-label="{{ $month['full_label'] }}: £{{ number_format($month['spend'], 2) }} across {{ $month['orders'] }} orders"
                        title="{{ $month['full_label'] }}: £{{ number_format($month['spend'], 2) }} across {{ $month['orders'] }} orders">

                        <span class="supplier-spend-bar-value">
                            £{{ number_format($month['spend'], 0) }}
                        </span>

                        <span class="supplier-spend-bar-track">

                            <span style="height: {{ $barHeight }}%"></span>

                        </span>

                        <strong>{{ $month['label'] }}</strong>

                    </div>

                    @endforeach

                </div>

            </article>

            <article class="supplier-analytics-chart-card status-chart-card">

                <div class="supplier-analytics-card-header">

                    <div>

                        <span>Workflow distribution</span>

                        <h3>Order Status</h3>

                    </div>

                    <strong>
                        {{ number_format($totalPurchaseOrders) }}
                    </strong>

                </div>

                @php
                    $statusGradientParts = [];
                    $statusPosition = 0;

                    foreach ($purchaseOrderStatusAnalytics as $statusItem) {
                        $nextStatusPosition =
                            $statusPosition
                            + $statusItem['percentage'];

                        if ($statusItem['percentage'] > 0) {
                            $statusGradientParts[] =
                                $statusItem['color']
                                . ' '
                                . $statusPosition
                                . '% '
                                . $nextStatusPosition
                                . '%';
                        }

                        $statusPosition = $nextStatusPosition;
                    }

                    $statusGradient = $statusGradientParts
                        ? implode(', ', $statusGradientParts)
                        : '#e5e7eb 0% 100%';
                @endphp

                <div class="supplier-status-chart-layout">

                    <div
                        class="supplier-status-donut"
                        style="background: conic-gradient({{ $statusGradient }})"
                        role="img"
                        aria-label="Purchase order status distribution">

                        <div>

                            <strong>
                                {{ number_format($totalPurchaseOrders) }}
                            </strong>

                            <span>orders</span>

                        </div>

                    </div>

                    <div class="supplier-status-legend">

                        @foreach ($purchaseOrderStatusAnalytics as $statusItem)

                        <div>

                            <span
                                class="supplier-status-dot"
                                style="background: {{ $statusItem['color'] }}">
                            </span>

                            <span>{{ $statusItem['label'] }}</span>

                            <strong>
                                {{ $statusItem['count'] }}
                            </strong>

                            <small>
                                {{ number_format($statusItem['percentage'], 1) }}%
                            </small>

                        </div>

                        @endforeach

                    </div>

                </div>

            </article>

            <article class="supplier-analytics-chart-card rating-chart-card">

                <div class="supplier-analytics-card-header">

                    <div>

                        <span>Performance breakdown</span>

                        <h3>Rating Categories</h3>

                    </div>

                    <strong>
                        {{ number_format($averageRating, 2) }}/5
                    </strong>

                </div>

                <div class="supplier-rating-analytics-list">

                    @foreach ($ratingCategoryAnalytics as $label => $category)

                    <div>

                        <div>

                            <span>{{ $label }}</span>

                            <strong>
                                {{ number_format($category['score'], 2) }}/5
                            </strong>

                        </div>

                        <span class="supplier-rating-analytics-track">

                            <span style="width: {{ $category['percentage'] }}%"></span>

                        </span>

                    </div>

                    @endforeach

                </div>

            </article>

            <article class="supplier-analytics-chart-card insight-card">

                <div class="supplier-analytics-card-header">

                    <div>

                        <span>Operational summary</span>

                        <h3>Purchasing Insights</h3>

                    </div>

                    <i class="fa-solid fa-chart-line"></i>

                </div>

                <div class="supplier-insight-list">

                    <div>

                        <span>
                            <i class="fa-solid fa-boxes-stacked"></i>
                            Total items ordered
                        </span>

                        <strong>
                            {{ number_format($supplierAnalytics['total_items_ordered']) }}
                        </strong>

                    </div>

                    <div>

                        <span>
                            <i class="fa-solid fa-receipt"></i>
                            Average order value
                        </span>

                        <strong>
                            £{{ number_format($averageOrderValue, 2) }}
                        </strong>

                    </div>

                    <div>

                        <span>
                            <i class="fa-regular fa-clock"></i>
                            Days since last order
                        </span>

                        <strong>
                            {{
                                $supplierAnalytics['days_since_last_order']
                                ?? '—'
                            }}
                        </strong>

                    </div>

                    <div>

                        <span>
                            <i class="fa-solid fa-file-invoice"></i>
                            Latest order
                        </span>

                        <strong>
                            {{
                                $supplierAnalytics['latest_order_reference']
                                ?? 'No orders'
                            }}
                        </strong>

                    </div>

                    <div>

                        <span>
                            <i class="fa-solid fa-truck-fast"></i>
                            Expected lead time
                        </span>

                        <strong>
                            {{ number_format($supplier->lead_time_days ?? 0) }} days
                        </strong>

                    </div>

                </div>

            </article>

        </div>

    </section>

    <section class="supplier-contacts-panel">

        <div class="supplier-contacts-header">

            <div>

                <span class="supplier-profile-eyebrow">
                    Supplier team
                </span>

                <h2>
                    Supplier Contacts
                </h2>

                <p>
                    Manage sales, accounts, warehouse and purchase-order contacts.
                </p>

            </div>

            <button
                type="button"
                class="supplier-contact-add-button"
                id="openSupplierContactModal">

                <i class="fa-solid fa-user-plus"></i>

                Add Contact

            </button>

        </div>

        <div class="supplier-contact-grid">

            @forelse ($supplier->contacts as $contact)

            <article class="supplier-contact-card">

                <div class="supplier-contact-card-top">

                    <span class="supplier-contact-avatar">

                        {{
                            strtoupper(
                                substr(
                                    trim($contact->name),
                                    0,
                                    1
                                )
                            )
                        }}

                    </span>

                    <div>

                        <div class="supplier-contact-name-row">

                            <h3>
                                {{ $contact->name }}
                            </h3>

                            @if ($contact->is_primary)

                            <span class="supplier-contact-badge primary">

                                <i class="fa-solid fa-star"></i>

                                Primary

                            </span>

                            @endif

                            @if ($contact->receives_purchase_orders)

                            <span class="supplier-contact-badge purchase">

                                <i class="fa-solid fa-file-invoice"></i>

                                Receives POs

                            </span>

                            @endif

                        </div>

                        <p>
                            {{
                                collect([
                                    $contact->job_title,
                                    $contact->department,
                                ])
                                    ->filter()
                                    ->implode(' · ')
                                ?: 'Role not assigned'
                            }}
                        </p>

                    </div>

                </div>

                <div class="supplier-contact-details">

                    <div>

                        <span>
                            Email
                        </span>

                        @if ($contact->email)

                        <a href="mailto:{{ $contact->email }}">
                            {{ $contact->email }}
                        </a>

                        @else

                        <strong>
                            Not assigned
                        </strong>

                        @endif

                    </div>

                    <div>

                        <span>
                            Phone
                        </span>

                        <strong>
                            {{ $contact->phone ?: 'Not assigned' }}
                        </strong>

                    </div>

                    <div>

                        <span>
                            Mobile
                        </span>

                        <strong>
                            {{ $contact->mobile ?: 'Not assigned' }}
                        </strong>

                    </div>

                </div>

                @if ($contact->notes)

                <div class="supplier-contact-notes">
                    {{ $contact->notes }}
                </div>

                @endif

                <div class="supplier-contact-actions">

                    <button
                        type="button"
                        class="supplier-contact-edit-button"
                        data-edit-contact
                        data-contact-id="{{ $contact->id }}"
                        data-name="{{ $contact->name }}"
                        data-job-title="{{ $contact->job_title }}"
                        data-department="{{ $contact->department }}"
                        data-email="{{ $contact->email }}"
                        data-phone="{{ $contact->phone }}"
                        data-mobile="{{ $contact->mobile }}"
                        data-primary="{{ $contact->is_primary ? '1' : '0' }}"
                        data-receives-po="{{ $contact->receives_purchase_orders ? '1' : '0' }}"
                        data-notes="{{ $contact->notes }}"
                        data-update-url="{{ route(
                            'admin.suppliers.contacts.update',
                            [$supplier, $contact]
                        ) }}">

                        <i class="fa-regular fa-pen-to-square"></i>

                        Edit

                    </button>

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.suppliers.contacts.destroy',
                            [$supplier, $contact]
                        ) }}"
                        class="supplier-contact-delete-form">

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="supplier-contact-delete-button">

                            <i class="fa-regular fa-trash-can"></i>

                            Delete

                        </button>

                    </form>

                </div>

            </article>

            @empty

            <div class="supplier-contacts-empty">

                <span>

                    <i class="fa-solid fa-address-book"></i>

                </span>

                <h3>
                    No supplier contacts
                </h3>

                <p>
                    Add the supplier's sales, accounts or warehouse contacts.
                </p>

                <button
                    type="button"
                    id="openFirstSupplierContactModal">

                    <i class="fa-solid fa-plus"></i>

                    Add First Contact

                </button>

            </div>

            @endforelse

        </div>

    </section>

    <section
        class="supplier-documents-panel"
        id="supplierDocumentsPanel">

        <div class="supplier-documents-header">

            <div>

                <span class="supplier-profile-eyebrow">
                    Supplier records
                </span>

                <h2>
                    Documents
                </h2>

                <p>
                    Store contracts, certificates, price lists and compliance records.
                </p>

            </div>

            <span class="supplier-documents-count">

                <i class="fa-regular fa-folder-open"></i>

                {{ $supplier->documents->count() }}
                {{
                    \Illuminate\Support\Str::plural(
                        'document',
                        $supplier->documents->count()
                    )
                }}

            </span>

        </div>

        <div class="supplier-documents-layout">

            <article class="supplier-document-upload-card">

                <div class="supplier-document-upload-heading">

                    <span>
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </span>

                    <div>

                        <h3>
                            Upload Document
                        </h3>

                        <p>
                            PDF, Office, CSV or image up to 10 MB.
                        </p>

                    </div>

                </div>

                @if ($errors->supplierDocument->any())

                <div
                    class="supplier-document-errors"
                    role="alert">

                    <strong>
                        Please correct the document information.
                    </strong>

                    <ul>

                        @foreach ($errors->supplierDocument->all() as $error)

                        <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

                @endif

                <form
                    method="POST"
                    action="{{ route(
                        'admin.suppliers.documents.store',
                        $supplier
                    ) }}"
                    enctype="multipart/form-data"
                    id="supplierDocumentForm">

                    @csrf

                    <input
                        type="hidden"
                        name="form_context"
                        value="supplier_document">

                    <div class="supplier-document-form-field">

                        <label for="supplier_document_title">
                            Document Title *
                        </label>

                        <input
                            type="text"
                            id="supplier_document_title"
                            name="title"
                            value="{{ old('form_context') === 'supplier_document' ? old('title') : '' }}"
                            maxlength="255"
                            required>

                    </div>

                    <div class="supplier-document-form-row">

                        <div class="supplier-document-form-field">

                            <label for="supplier_document_type">
                                Document Type *
                            </label>

                            <select
                                id="supplier_document_type"
                                name="document_type"
                                required>

                                @php
                                    $oldDocumentType =
                                        old('form_context') === 'supplier_document'
                                            ? old('document_type')
                                            : '';
                                @endphp

                                <option value="">
                                    Select type
                                </option>

                                @foreach ([
                                    'contract' => 'Contract',
                                    'certificate' => 'Certificate',
                                    'price_list' => 'Price List',
                                    'bank_details' => 'Bank Details',
                                    'tax_document' => 'Tax Document',
                                    'insurance' => 'Insurance',
                                    'other' => 'Other',
                                ] as $value => $label)

                                <option
                                    value="{{ $value }}"
                                    @selected($oldDocumentType === $value)>
                                    {{ $label }}
                                </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="supplier-document-form-field">

                            <label for="supplier_document_expiry">
                                Expiry Date
                            </label>

                            <input
                                type="date"
                                id="supplier_document_expiry"
                                name="expires_at"
                                value="{{ old('form_context') === 'supplier_document' ? old('expires_at') : '' }}">

                        </div>

                    </div>

                    <div class="supplier-document-form-field">

                        <label for="supplier_document_file">
                            Choose File *
                        </label>

                        <label
                            class="supplier-document-file-picker"
                            for="supplier_document_file">

                            <i class="fa-solid fa-paperclip"></i>

                            <span id="supplierDocumentFileName">
                                Select a file
                            </span>

                            <strong>
                                Browse
                            </strong>

                        </label>

                        <input
                            class="supplier-document-file-input"
                            type="file"
                            id="supplier_document_file"
                            name="document"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.webp"
                            required>

                    </div>

                    <div class="supplier-document-form-field">

                        <label for="supplier_document_notes">
                            Notes
                        </label>

                        <textarea
                            id="supplier_document_notes"
                            name="notes"
                            rows="3"
                            maxlength="3000">{{ old('form_context') === 'supplier_document' ? old('notes') : '' }}</textarea>

                    </div>

                    <button
                        type="submit"
                        class="supplier-document-upload-button"
                        id="supplierDocumentUploadButton">

                        <i class="fa-solid fa-cloud-arrow-up"></i>

                        Upload Document

                    </button>

                </form>

            </article>

            <div class="supplier-document-list">

                @forelse ($supplier->documents->sortByDesc('created_at') as $document)

                <article class="supplier-document-card">

                    <span class="supplier-document-icon">

                        @if (str_contains((string) $document->mime_type, 'pdf'))

                        <i class="fa-solid fa-file-pdf"></i>

                        @elseif (str_contains((string) $document->mime_type, 'image'))

                        <i class="fa-solid fa-file-image"></i>

                        @elseif (str_contains((string) $document->mime_type, 'sheet') || str_contains((string) $document->mime_type, 'excel'))

                        <i class="fa-solid fa-file-excel"></i>

                        @else

                        <i class="fa-solid fa-file-lines"></i>

                        @endif

                    </span>

                    <div class="supplier-document-main">

                        <div class="supplier-document-title-row">

                            <div>

                                <span class="supplier-document-type">
                                    {{ $document->document_type_label }}
                                </span>

                                <h3>
                                    {{ $document->title }}
                                </h3>

                            </div>

                            @if ($document->expires_at)

                            <span class="supplier-document-expiry {{
                                $document->expires_at->isPast()
                                    ? 'expired'
                                    : ''
                            }}">

                                <i class="fa-regular fa-calendar"></i>

                                {{
                                    $document->expires_at->isPast()
                                        ? 'Expired '
                                        : 'Expires '
                                }}

                                {{ $document->expires_at->format('d M Y') }}

                            </span>

                            @endif

                        </div>

                        <div class="supplier-document-meta">

                            <span>
                                <i class="fa-regular fa-file"></i>
                                {{ $document->original_name }}
                            </span>

                            <span>
                                <i class="fa-solid fa-database"></i>
                                {{ $document->formatted_size }}
                            </span>

                            <span>
                                <i class="fa-regular fa-user"></i>
                                {{
                                    $document->uploader
                                        ? ($document->uploader->name ?? $document->uploader->email)
                                        : 'System'
                                }}
                            </span>

                            <span>
                                <i class="fa-regular fa-clock"></i>
                                {{ $document->created_at->format('d M Y') }}
                            </span>

                        </div>

                        @if ($document->notes)

                        <p class="supplier-document-notes">
                            {{ $document->notes }}
                        </p>

                        @endif

                    </div>

                    <div class="supplier-document-actions">

                        <a
                            href="{{ route(
                                'admin.suppliers.documents.download',
                                [$supplier, $document]
                            ) }}"
                            class="supplier-document-download-button">

                            <i class="fa-solid fa-download"></i>

                            Download

                        </a>

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.suppliers.documents.destroy',
                                [$supplier, $document]
                            ) }}"
                            class="supplier-document-delete-form">

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="supplier-document-delete-button">

                                <i class="fa-regular fa-trash-can"></i>

                                Delete

                            </button>

                        </form>

                    </div>

                </article>

                @empty

                <div class="supplier-documents-empty">

                    <span>
                        <i class="fa-regular fa-folder-open"></i>
                    </span>

                    <h3>
                        No supplier documents
                    </h3>

                    <p>
                        Upload the first contract, certificate or price list.
                    </p>

                </div>

                @endforelse

            </div>

        </div>

    </section>

    <section
        class="supplier-ratings-panel"
        id="supplierRatingsPanel">

        <div class="supplier-ratings-header">

            <div>

                <span class="supplier-profile-eyebrow">
                    Performance management
                </span>

                <h2>
                    Supplier Ratings
                </h2>

                <p>
                    Score product quality, delivery, communication and pricing.
                </p>

            </div>

            <div class="supplier-ratings-header-actions">

                <div class="supplier-ratings-average">

                    <span>
                        {{ number_format($averageRating, 2) }}
                    </span>

                    <div>

                        <div class="supplier-rating-stars">

                            @for ($star = 1; $star <= 5; $star++)

                            <i class="{{
                                $star <= round($averageRating)
                                    ? 'fa-solid'
                                    : 'fa-regular'
                            }} fa-star"></i>

                            @endfor

                        </div>

                        <small>
                            {{ $supplier->ratings->count() }}
                            {{
                                \Illuminate\Support\Str::plural(
                                    'review',
                                    $supplier->ratings->count()
                                )
                            }}
                        </small>

                    </div>

                </div>

                <button
                    type="button"
                    class="supplier-rating-add-button"
                    id="openSupplierRatingModal">

                    <i class="fa-solid fa-star"></i>

                    Add Rating

                </button>

            </div>

        </div>

        <div class="supplier-rating-list">

            @forelse ($supplier->ratings->sortByDesc('rated_at') as $rating)

            <article class="supplier-rating-card">

                <div class="supplier-rating-card-score">

                    <strong>
                        {{ number_format($rating->overall_rating, 2) }}
                    </strong>

                    <span>/ 5</span>

                    <div class="supplier-rating-stars">

                        @for ($star = 1; $star <= 5; $star++)

                        <i class="{{
                            $star <= round((float) $rating->overall_rating)
                                ? 'fa-solid'
                                : 'fa-regular'
                        }} fa-star"></i>

                        @endfor

                    </div>

                </div>

                <div class="supplier-rating-card-main">

                    <div class="supplier-rating-card-heading">

                        <div>

                            <span class="supplier-rating-reference">

                                @if ($rating->purchaseOrder)

                                <i class="fa-solid fa-file-invoice"></i>

                                PO {{ $rating->purchaseOrder->reference }}

                                @else

                                <i class="fa-solid fa-building"></i>

                                General supplier review

                                @endif

                            </span>

                            <h3>
                                {{ $rating->title ?: 'Supplier performance review' }}
                            </h3>

                        </div>

                        <span class="supplier-rating-recommendation {{
                            $rating->would_recommend
                                ? 'recommended'
                                : 'not-recommended'
                        }}">

                            <i class="fa-solid {{
                                $rating->would_recommend
                                    ? 'fa-thumbs-up'
                                    : 'fa-thumbs-down'
                            }}"></i>

                            {{
                                $rating->would_recommend
                                    ? 'Recommended'
                                    : 'Not recommended'
                            }}

                        </span>

                    </div>

                    <div class="supplier-rating-score-grid">

                        @foreach ([
                            'Quality' => $rating->quality_rating,
                            'Delivery' => $rating->delivery_rating,
                            'Communication' => $rating->communication_rating,
                            'Pricing' => $rating->pricing_rating,
                        ] as $label => $score)

                        <div>

                            <span>{{ $label }}</span>

                            <strong>{{ $score }}/5</strong>

                            <span class="supplier-rating-score-track">
                                <span style="width: {{ $score * 20 }}%"></span>
                            </span>

                        </div>

                        @endforeach

                    </div>

                    @if ($rating->review)

                    <p class="supplier-rating-review">
                        {{ $rating->review }}
                    </p>

                    @endif

                    <div class="supplier-rating-meta">

                        <span>
                            <i class="fa-regular fa-user"></i>
                            {{
                                $rating->ratedBy
                                    ? ($rating->ratedBy->name ?? $rating->ratedBy->email)
                                    : 'System'
                            }}
                        </span>

                        <span>
                            <i class="fa-regular fa-clock"></i>
                            {{
                                optional($rating->rated_at)->format('d M Y H:i')
                                    ?: $rating->updated_at->format('d M Y H:i')
                            }}
                        </span>

                    </div>

                </div>

                <div class="supplier-rating-actions">

                    <button
                        type="button"
                        class="supplier-rating-edit-button"
                        data-edit-rating
                        data-rating-id="{{ $rating->id }}"
                        data-purchase-order-id="{{ $rating->purchase_order_id }}"
                        data-quality="{{ $rating->quality_rating }}"
                        data-delivery="{{ $rating->delivery_rating }}"
                        data-communication="{{ $rating->communication_rating }}"
                        data-pricing="{{ $rating->pricing_rating }}"
                        data-title="{{ $rating->title }}"
                        data-review="{{ $rating->review }}"
                        data-recommend="{{ $rating->would_recommend ? '1' : '0' }}"
                        data-update-url="{{ route(
                            'admin.suppliers.ratings.update',
                            [$supplier, $rating]
                        ) }}">

                        <i class="fa-regular fa-pen-to-square"></i>

                        Edit

                    </button>

                    <form
                        method="POST"
                        action="{{ route(
                            'admin.suppliers.ratings.destroy',
                            [$supplier, $rating]
                        ) }}"
                        class="supplier-rating-delete-form">

                        @csrf
                        @method('DELETE')

                        <button type="submit">

                            <i class="fa-regular fa-trash-can"></i>

                            Delete

                        </button>

                    </form>

                </div>

            </article>

            @empty

            <div class="supplier-ratings-empty">

                <span>
                    <i class="fa-regular fa-star"></i>
                </span>

                <h3>
                    No performance ratings
                </h3>

                <p>
                    Add the first supplier performance review.
                </p>

                <button
                    type="button"
                    id="openFirstSupplierRatingModal">

                    <i class="fa-solid fa-plus"></i>

                    Add First Rating

                </button>

            </div>

            @endforelse

        </div>

    </section>

    <div
        class="supplier-contact-modal"
        id="supplierRatingModal"
        hidden>

        <div
            class="supplier-contact-modal-overlay"
            data-close-rating-modal>
        </div>

        <div
            class="supplier-contact-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="supplierRatingModalTitle">

            <div class="supplier-contact-modal-header">

                <div>

                    <span class="supplier-profile-eyebrow">
                        Performance review
                    </span>

                    <h2 id="supplierRatingModalTitle">
                        Add Supplier Rating
                    </h2>

                </div>

                <button
                    type="button"
                    class="supplier-contact-modal-close"
                    data-close-rating-modal
                    aria-label="Close rating form">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

            <form
                method="POST"
                action="{{ route(
                    'admin.suppliers.ratings.store',
                    $supplier
                ) }}"
                data-create-url="{{ route(
                    'admin.suppliers.ratings.store',
                    $supplier
                ) }}"
                data-has-errors="{{
                    $errors->supplierRating->any()
                        ? '1'
                        : '0'
                }}"
                data-editing-rating-id="{{ old('rating_id') }}"
                id="supplierRatingForm">

                @csrf

                <input
                    type="hidden"
                    name="form_context"
                    value="supplier_rating">

                <input
                    type="hidden"
                    name="_method"
                    id="supplierRatingMethod"
                    value="{{ old('_method', 'POST') }}">

                <input
                    type="hidden"
                    name="rating_id"
                    id="supplierRatingId"
                    value="{{ old('rating_id') }}">

                @if ($errors->supplierRating->any())

                <div class="supplier-rating-modal-errors" role="alert">

                    <strong>
                        Please correct the rating information.
                    </strong>

                    <ul>

                        @foreach ($errors->supplierRating->all() as $error)

                        <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

                @endif

                <div class="supplier-contact-form-grid">

                    <div class="supplier-contact-form-field supplier-contact-form-wide">

                        <label for="rating_purchase_order_id">
                            Purchase Order
                        </label>

                        <select
                            id="rating_purchase_order_id"
                            name="purchase_order_id">

                            <option value="">
                                General supplier review
                            </option>

                            @foreach ($supplier->purchaseOrders as $purchaseOrder)

                            <option
                                value="{{ $purchaseOrder->id }}"
                                @selected((string) old('purchase_order_id') === (string) $purchaseOrder->id)>

                                {{ $purchaseOrder->reference }}
                                — {{ $purchaseOrder->status_label }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                    @foreach ([
                        'quality_rating' => 'Product Quality',
                        'delivery_rating' => 'Delivery Performance',
                        'communication_rating' => 'Communication',
                        'pricing_rating' => 'Pricing & Value',
                    ] as $field => $label)

                    <div class="supplier-contact-form-field supplier-rating-score-field">

                        <label for="{{ $field }}">
                            {{ $label }} *
                        </label>

                        <select
                            id="{{ $field }}"
                            name="{{ $field }}"
                            required>

                            <option value="">
                                Select score
                            </option>

                            @for ($score = 5; $score >= 1; $score--)

                            <option
                                value="{{ $score }}"
                                @selected((string) old($field) === (string) $score)>

                                {{ $score }} — {{ match ($score) {
                                    5 => 'Excellent',
                                    4 => 'Very Good',
                                    3 => 'Good',
                                    2 => 'Needs Improvement',
                                    default => 'Poor',
                                } }}

                            </option>

                            @endfor

                        </select>

                    </div>

                    @endforeach

                    <div class="supplier-contact-form-field supplier-contact-form-wide">

                        <label for="supplier_rating_title">
                            Review Title
                        </label>

                        <input
                            type="text"
                            id="supplier_rating_title"
                            name="title"
                            value="{{ old('form_context') === 'supplier_rating' ? old('title') : '' }}"
                            maxlength="255">

                    </div>

                    <div class="supplier-contact-form-field supplier-contact-form-wide">

                        <label for="supplier_rating_review">
                            Review Notes
                        </label>

                        <textarea
                            id="supplier_rating_review"
                            name="review"
                            rows="4"
                            maxlength="5000">{{ old('form_context') === 'supplier_rating' ? old('review') : '' }}</textarea>

                    </div>

                    <label class="supplier-contact-checkbox supplier-contact-form-wide">

                        <input
                            type="checkbox"
                            id="supplier_rating_recommend"
                            name="would_recommend"
                            value="1"
                            @checked(old('form_context') === 'supplier_rating' && old('would_recommend'))>

                        <span>

                            <strong>
                                Recommend this supplier
                            </strong>

                            <small>
                                Mark when this supplier should be considered for future orders.
                            </small>

                        </span>

                    </label>

                </div>

                <div class="supplier-contact-modal-footer">

                    <button
                        type="button"
                        class="supplier-contact-cancel-button"
                        data-close-rating-modal>

                        Cancel

                    </button>

                    <button
                        type="submit"
                        class="supplier-contact-save-button"
                        id="supplierRatingSaveButton">

                        <i class="fa-solid fa-star"></i>

                        Save Rating

                    </button>

                </div>

            </form>

        </div>

    </div>

    <section class="supplier-profile-information-grid">

        <article class="supplier-profile-card">

            <div class="supplier-profile-card-header">

                <span class="supplier-profile-card-icon company">

                    <i class="fa-solid fa-building"></i>

                </span>

                <div>

                    <span class="supplier-profile-eyebrow">
                        Supplier identity
                    </span>

                    <h2>
                        Company Information
                    </h2>

                </div>

            </div>

            <div class="supplier-profile-information-list">

                <div>

                    <span>
                        Company Name
                    </span>

                    <strong>
                        {{ $supplier->company_name }}
                    </strong>

                </div>

                <div>

                    <span>
                        Supplier Code
                    </span>

                    <strong>
                        {{
                            $supplier->supplier_code
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Contact Person
                    </span>

                    <strong>
                        {{
                            $supplier->contact_person
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Registration Number
                    </span>

                    <strong>
                        {{
                            $supplier->registration_number
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Tax Number
                    </span>

                    <strong>
                        {{
                            $supplier->tax_number
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Created By
                    </span>

                    <strong>

                        {{
                            $supplier->creator
                                ? (
                                    $supplier->creator->name
                                    ?? $supplier->creator->email
                                )
                                : 'System'
                        }}

                    </strong>

                </div>

            </div>

        </article>

        <article class="supplier-profile-card">

            <div class="supplier-profile-card-header">

                <span class="supplier-profile-card-icon contact">

                    <i class="fa-solid fa-address-book"></i>

                </span>

                <div>

                    <span class="supplier-profile-eyebrow">
                        Communication
                    </span>

                    <h2>
                        Contact Information
                    </h2>

                </div>

            </div>

            <div class="supplier-profile-information-list">

                <div>

                    <span>
                        Email Address
                    </span>

                    @if ($supplier->email)

                    <a href="mailto:{{ $supplier->email }}">

                        {{ $supplier->email }}

                    </a>

                    @else

                    <strong>
                        Not assigned
                    </strong>

                    @endif

                </div>

                <div>

                    <span>
                        Primary Phone
                    </span>

                    @if ($supplier->phone)

                    <a href="tel:{{ $supplier->phone }}">

                        {{ $supplier->phone }}

                    </a>

                    @else

                    <strong>
                        Not assigned
                    </strong>

                    @endif

                </div>

                <div>

                    <span>
                        Alternate Phone
                    </span>

                    <strong>
                        {{
                            $supplier->alternate_phone
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Website
                    </span>

                    @if ($supplier->website)

                    <a
                        href="{{ $supplier->website }}"
                        target="_blank"
                        rel="noopener">

                        {{ $supplier->website }}

                    </a>

                    @else

                    <strong>
                        Not assigned
                    </strong>

                    @endif

                </div>

                <div class="supplier-profile-information-wide">

                    <span>
                        Full Address
                    </span>

                    <strong class="supplier-profile-address">

                        {{
                            $supplier->full_address
                            ?: 'Not assigned'
                        }}

                    </strong>

                </div>

            </div>

        </article>

        <article class="supplier-profile-card">

            <div class="supplier-profile-card-header">

                <span class="supplier-profile-card-icon commercial">

                    <i class="fa-solid fa-handshake"></i>

                </span>

                <div>

                    <span class="supplier-profile-eyebrow">
                        Purchasing terms
                    </span>

                    <h2>
                        Commercial Information
                    </h2>

                </div>

            </div>

            <div class="supplier-profile-information-list">

                <div>

                    <span>
                        Currency
                    </span>

                    <strong>
                        {{ $supplier->currency }}
                    </strong>

                </div>

                <div>

                    <span>
                        Payment Terms
                    </span>

                    <strong>
                        {{
                            $supplier->payment_terms
                            ?: 'Not configured'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Lead Time
                    </span>

                    <strong>

                        @if ($supplier->lead_time_days !== null)

                        {{
                                number_format(
                                    $supplier->lead_time_days
                                )
                            }}
                        days

                        @else

                        Not configured

                        @endif

                    </strong>

                </div>

                <div>

                    <span>
                        Credit Limit
                    </span>

                    <strong>

                        @if ($supplier->credit_limit !== null)

                        £{{ number_format(
                                $supplier->credit_limit,
                                2
                            ) }}

                        @else

                        Not configured

                        @endif

                    </strong>

                </div>

                <div>

                    <span>
                        Status
                    </span>

                    <strong>
                        {{ $supplier->status_label }}
                    </strong>

                </div>

                <div>

                    <span>
                        Supplier Type
                    </span>

                    <strong>

                        {{
                            $supplier->is_preferred
                                ? 'Preferred Supplier'
                                : 'Standard Supplier'
                        }}

                    </strong>

                </div>

            </div>

        </article>

        <article class="supplier-profile-card">

            <div class="supplier-profile-card-header">

                <span class="supplier-profile-card-icon banking">

                    <i class="fa-solid fa-building-columns"></i>

                </span>

                <div>

                    <span class="supplier-profile-eyebrow">
                        Payment account
                    </span>

                    <h2>
                        Banking Information
                    </h2>

                </div>

            </div>

            <div class="supplier-profile-information-list">

                <div>

                    <span>
                        Bank Name
                    </span>

                    <strong>
                        {{
                            $supplier->bank_name
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Account Name
                    </span>

                    <strong>
                        {{
                            $supplier->account_name
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Account Number
                    </span>

                    <strong>
                        {{
                            $supplier->account_number
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Sort Code
                    </span>

                    <strong>
                        {{
                            $supplier->sort_code
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        IBAN
                    </span>

                    <strong>
                        {{
                            $supplier->iban
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        SWIFT / BIC
                    </span>

                    <strong>
                        {{
                            $supplier->swift_code
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

            </div>

        </article>

    </section>

    <section class="supplier-profile-panel">

        <div class="supplier-profile-panel-header">

            <div>

                <span class="supplier-profile-eyebrow">
                    Purchasing history
                </span>

                <h2>
                    Purchase Orders
                </h2>

                <p>
                    Purchase orders associated with this supplier.
                </p>

            </div>

            <span class="supplier-profile-result-count">

                <strong>
                    {{ number_format(
                        $supplier->purchaseOrders->count()
                    ) }}
                </strong>

                orders

            </span>

        </div>

        <div class="supplier-profile-table-wrapper">

            <table class="supplier-profile-table">

                <thead>

                    <tr>

                        <th>Reference</th>
                        <th>Status</th>
                        <th class="number-column">Items</th>
                        <th class="number-column">Total</th>
                        <th>Order Date</th>
                        <th>Expected Date</th>
                        <th class="action-column"></th>

                    </tr>

                </thead>

                <tbody>

                    @forelse (
                    $supplier->purchaseOrders
                    as $purchaseOrder
                    )

                    <tr>

                        <td>

                            <a
                                href="{{ route(
                                        'admin.purchase-orders.show',
                                        $purchaseOrder
                                    ) }}"
                                class="supplier-profile-reference">

                                {{ $purchaseOrder->reference }}

                            </a>

                            <span>
                                Created
                                {{
                                        $purchaseOrder
                                            ->created_at
                                            ->format('d M Y H:i')
                                    }}
                            </span>

                        </td>

                        <td>

                            <span
                                class="supplier-po-status {{
                                        $purchaseOrder->status
                                    }}">

                                {{
                                        $purchaseOrder->status_label
                                    }}

                            </span>

                        </td>

                        <td class="number-column">

                            {{ number_format(
                                    $purchaseOrder->items_count
                                ) }}

                        </td>

                        <td class="number-column">

                            <strong class="supplier-profile-spend">

                                £{{ number_format(
                                        $purchaseOrder->total_amount,
                                        2
                                    ) }}

                            </strong>

                        </td>

                        <td>

                            {{
                                    optional(
                                        $purchaseOrder->order_date
                                    )->format('d M Y')
                                    ?: 'Not set'
                                }}

                        </td>

                        <td>

                            {{
                                    optional(
                                        $purchaseOrder->expected_date
                                    )->format('d M Y')
                                    ?: 'Not set'
                                }}

                        </td>

                        <td class="action-column">

                            <div class="supplier-po-row-actions">

                                <button
                                    type="button"
                                    class="supplier-po-email-button"
                                    data-email-purchase-order
                                    data-purchase-order-id="{{ $purchaseOrder->id }}"
                                    data-reference="{{ $purchaseOrder->reference }}"
                                    data-email-url="{{ route(
                                        'admin.suppliers.purchase-orders.email',
                                        [$supplier, $purchaseOrder]
                                    ) }}"
                                    aria-label="Email purchase order {{ $purchaseOrder->reference }}">

                                    <i class="fa-regular fa-envelope"></i>

                                </button>

                                <a
                                    href="{{ route(
                                            'admin.purchase-orders.show',
                                            $purchaseOrder
                                        ) }}"
                                    class="supplier-profile-row-action"
                                    aria-label="View purchase order {{ $purchaseOrder->reference }}">

                                    <i class="fa-solid fa-arrow-right"></i>

                                </a>

                            </div>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td
                            colspan="7"
                            class="supplier-profile-empty">

                            No purchase orders are connected to this
                            supplier yet.

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

    <section
        class="supplier-email-history-panel"
        id="supplierEmailHistoryPanel">

        <div class="supplier-email-history-header">

            <div>

                <span class="supplier-profile-eyebrow">
                    Purchase-order communication
                </span>

                <h2>
                    Email Delivery History
                </h2>

                <p>
                    Track queued, delivered and failed supplier purchase-order emails.
                </p>

            </div>

            <span>

                <i class="fa-regular fa-paper-plane"></i>

                {{ $supplier->purchaseOrderDeliveries->count() }} deliveries

            </span>

        </div>

        <div class="supplier-email-history-list">

            @forelse ($supplier->purchaseOrderDeliveries->sortByDesc('created_at')->take(10) as $delivery)

            <article class="supplier-email-history-card">

                <span class="supplier-email-status-icon {{ $delivery->status }}">

                    <i class="fa-solid {{ match ($delivery->status) {
                        'sent' => 'fa-check',
                        'failed' => 'fa-xmark',
                        default => 'fa-clock',
                    } }}"></i>

                </span>

                <div class="supplier-email-history-main">

                    <div>

                        <span>
                            {{
                                $delivery->purchaseOrder?->reference
                                ?? 'Deleted purchase order'
                            }}
                        </span>

                        <h3>
                            {{ $delivery->subject }}
                        </h3>

                    </div>

                    <div class="supplier-email-recipient-list">

                        <i class="fa-regular fa-envelope"></i>

                        {{ implode(', ', $delivery->recipient_emails ?? []) }}

                    </div>

                    @if ($delivery->status === 'failed' && $delivery->error_message)

                    <p class="supplier-email-failure-message">
                        {{ $delivery->error_message }}
                    </p>

                    @endif

                </div>

                <div class="supplier-email-history-meta">

                    <span class="supplier-email-status {{ $delivery->status }}">
                        {{ ucfirst($delivery->status) }}
                    </span>

                    <span>
                        <i class="fa-solid fa-paperclip"></i>
                        {{ $delivery->attach_pdf ? 'PDF attached' : 'No attachment' }}
                    </span>

                    <span>
                        <i class="fa-regular fa-user"></i>
                        {{
                            $delivery->sentBy
                                ? ($delivery->sentBy->name ?? $delivery->sentBy->email)
                                : 'System'
                        }}
                    </span>

                    <span>
                        <i class="fa-regular fa-clock"></i>
                        {{
                            optional(
                                $delivery->sent_at
                                ?? $delivery->queued_at
                                ?? $delivery->created_at
                            )->format('d M Y H:i')
                        }}
                    </span>

                </div>

            </article>

            @empty

            <div class="supplier-email-history-empty">

                <i class="fa-regular fa-paper-plane"></i>

                <div>

                    <h3>No purchase orders emailed</h3>

                    <p>
                        Use the envelope button beside a purchase order to send it.
                    </p>

                </div>

            </div>

            @endforelse

        </div>

    </section>

    <section class="supplier-profile-bottom-grid">

        <article class="supplier-profile-notes-card">

            <div class="supplier-profile-notes-section">

                <span class="supplier-profile-eyebrow">
                    General notes
                </span>

                <h2>
                    Supplier Notes
                </h2>

                <p>
                    {{
                        $supplier->notes
                        ?: 'No general supplier notes were added.'
                    }}
                </p>

            </div>

            <div class="supplier-profile-notes-section internal">

                <span class="supplier-profile-eyebrow">
                    Internal notes
                </span>

                <h2>
                    Administrator Notes
                </h2>

                <p>
                    {{
                        $supplier->internal_notes
                        ?: 'No internal notes were added.'
                    }}
                </p>

            </div>

        </article>

        <article class="supplier-profile-danger-card">

            <div>

                <span class="supplier-profile-eyebrow">
                    Record management
                </span>

                <h2>
                    Delete Supplier
                </h2>

                <p>
                    Suppliers with open purchase orders cannot be deleted.
                    Deletion uses Laravel soft deletes.
                </p>

            </div>

            <form
                method="POST"
                action="{{ route(
                    'admin.suppliers.destroy',
                    $supplier
                ) }}"
                id="deleteSupplierForm">

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="supplier-delete-button">

                    <i class="fa-regular fa-trash-can"></i>

                    Delete Supplier

                </button>

            </form>

        </article>

    </section>

    @php
        $firstSupplierPurchaseOrder =
            $supplier->purchaseOrders->first();

        $selectedEmailContactIds = collect(
            old('recipient_contact_ids', [])
        )->map(
            fn ($contactId): string =>
                (string) $contactId
        );
    @endphp

    <div
        class="supplier-contact-modal"
        id="supplierPurchaseOrderEmailModal"
        hidden>

        <div
            class="supplier-contact-modal-overlay"
            data-close-po-email-modal>
        </div>

        <div
            class="supplier-contact-modal-dialog supplier-po-email-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="supplierPurchaseOrderEmailModalTitle">

            <div class="supplier-contact-modal-header">

                <div>

                    <span class="supplier-profile-eyebrow">
                        Supplier communication
                    </span>

                    <h2 id="supplierPurchaseOrderEmailModalTitle">
                        Email Purchase Order
                    </h2>

                </div>

                <button
                    type="button"
                    class="supplier-contact-modal-close"
                    data-close-po-email-modal
                    aria-label="Close purchase-order email form">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

            <form
                method="POST"
                action="{{
                    $firstSupplierPurchaseOrder
                        ? route(
                            'admin.suppliers.purchase-orders.email',
                            [
                                $supplier,
                                $firstSupplierPurchaseOrder,
                            ]
                        )
                        : route('admin.suppliers.show', $supplier)
                }}"
                data-has-errors="{{
                    $errors->supplierPurchaseOrderEmail->any()
                        ? '1'
                        : '0'
                }}"
                data-purchase-order-id="{{ old('purchase_order_id') }}"
                data-app-name="{{ config('app.name') }}"
                id="supplierPurchaseOrderEmailForm">

                @csrf

                <input
                    type="hidden"
                    name="form_context"
                    value="supplier_po_email">

                <input
                    type="hidden"
                    name="purchase_order_id"
                    id="supplierEmailPurchaseOrderId"
                    value="{{ old('purchase_order_id') }}">

                @if ($errors->supplierPurchaseOrderEmail->any())

                <div class="supplier-rating-modal-errors" role="alert">

                    <strong>
                        Please correct the email delivery information.
                    </strong>

                    <ul>

                        @foreach ($errors->supplierPurchaseOrderEmail->all() as $error)

                        <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

                @endif

                <div class="supplier-po-email-form-content">

                    <div class="supplier-po-email-reference">

                        <span>
                            <i class="fa-solid fa-file-invoice"></i>
                        </span>

                        <div>

                            <small>Purchase order</small>

                            <strong id="supplierEmailPurchaseOrderReference">
                                Select purchase order
                            </strong>

                        </div>

                    </div>

                    <fieldset class="supplier-po-recipient-fieldset">

                        <legend>
                            Recipients *
                        </legend>

                        <div class="supplier-po-recipient-grid">

                            @foreach ($supplier->contacts->whereNotNull('email')->where('email', '!=', '') as $contact)

                            @php
                                $defaultContactSelected =
                                    $contact->receives_purchase_orders
                                    || $contact->is_primary;

                                $contactSelected =
                                    $errors->supplierPurchaseOrderEmail->any()
                                        ? $selectedEmailContactIds->contains(
                                            (string) $contact->id
                                        )
                                        : $defaultContactSelected;
                            @endphp

                            <label class="supplier-po-recipient-option">

                                <input
                                    type="checkbox"
                                    name="recipient_contact_ids[]"
                                    value="{{ $contact->id }}"
                                    data-default-checked="{{ $defaultContactSelected ? '1' : '0' }}"
                                    @checked($contactSelected)>

                                <span>

                                    <strong>{{ $contact->name }}</strong>

                                    <small>
                                        {{ $contact->email }}
                                    </small>

                                    @if ($contact->receives_purchase_orders)

                                    <em>PO recipient</em>

                                    @endif

                                </span>

                            </label>

                            @endforeach

                            @if ($supplier->email)

                            <label class="supplier-po-recipient-option supplier-email-option">

                                <input
                                    type="checkbox"
                                    name="include_supplier_email"
                                    value="1"
                                    data-default-checked="1"
                                    @checked(
                                        $errors->supplierPurchaseOrderEmail->any()
                                            ? old('include_supplier_email')
                                            : true
                                    )>

                                <span>

                                    <strong>Supplier main email</strong>

                                    <small>{{ $supplier->email }}</small>

                                </span>

                            </label>

                            @endif

                        </div>

                    </fieldset>

                    <div class="supplier-contact-form-field">

                        <label for="supplier_po_email_subject">
                            Email Subject
                        </label>

                        <input
                            type="text"
                            id="supplier_po_email_subject"
                            name="subject"
                            value="{{ old('form_context') === 'supplier_po_email' ? old('subject') : '' }}"
                            maxlength="255"
                            placeholder="Generated automatically when blank">

                    </div>

                    <div class="supplier-contact-form-field">

                        <label for="supplier_po_email_message">
                            Message
                        </label>

                        <textarea
                            id="supplier_po_email_message"
                            name="message"
                            rows="5"
                            maxlength="5000"
                            placeholder="Add delivery instructions or a note for the supplier...">{{ old('form_context') === 'supplier_po_email' ? old('message') : '' }}</textarea>

                    </div>

                    <label class="supplier-contact-checkbox">

                        <input
                            type="checkbox"
                            id="supplier_po_attach_pdf"
                            name="attach_pdf"
                            value="1"
                            data-default-checked="1"
                            @checked(
                                old('form_context') === 'supplier_po_email'
                                    ? old('attach_pdf')
                                    : true
                            )>

                        <span>

                            <strong>
                                Attach purchase-order PDF
                            </strong>

                            <small>
                                Generate and attach a supplier-friendly PDF copy.
                            </small>

                        </span>

                    </label>

                </div>

                <div class="supplier-contact-modal-footer">

                    <button
                        type="button"
                        class="supplier-contact-cancel-button"
                        data-close-po-email-modal>

                        Cancel

                    </button>

                    <button
                        type="submit"
                        class="supplier-contact-save-button"
                        id="supplierPurchaseOrderEmailButton">

                        <i class="fa-regular fa-paper-plane"></i>

                        Queue Email

                    </button>

                </div>

            </form>

        </div>

    </div>

    <div
        class="supplier-contact-modal"
        id="supplierContactModal"
        hidden>

        <div
            class="supplier-contact-modal-overlay"
            data-close-contact-modal>
        </div>

        <div
            class="supplier-contact-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="supplierContactModalTitle">

            <div class="supplier-contact-modal-header">

                <div>

                    <span class="supplier-profile-eyebrow">
                        Supplier contact
                    </span>

                    <h2 id="supplierContactModalTitle">
                        Add Contact
                    </h2>

                </div>

                <button
                    type="button"
                    class="supplier-contact-modal-close"
                    data-close-contact-modal
                    aria-label="Close contact form">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

            <form
                method="POST"
                action="{{ route(
                    'admin.suppliers.contacts.store',
                    $supplier
                ) }}"
                data-create-url="{{ route(
                    'admin.suppliers.contacts.store',
                    $supplier
                ) }}"
                data-has-errors="{{
                    $errors->supplierContact->any()
                        ? '1'
                        : '0'
                }}"
                data-editing-contact-id="{{
                    old('contact_id')
                }}"
                id="supplierContactForm">

                @csrf

                <input
                    type="hidden"
                    name="form_context"
                    value="supplier_contact">

                <input
                    type="hidden"
                    name="_method"
                    id="supplierContactMethod"
                    value="{{ old('_method', 'POST') }}">

                <input
                    type="hidden"
                    name="contact_id"
                    id="supplierContactId"
                    value="{{ old('contact_id') }}">

                @if ($errors->supplierContact->any())

                <div
                    class="supplier-profile-alert error"
                    role="alert">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <div>

                        <strong>
                            Please correct the contact information.
                        </strong>

                        <ul>

                            @foreach ($errors->supplierContact->all() as $error)

                            <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                </div>

                @endif

                <div class="supplier-contact-form-grid">

                    <div class="supplier-contact-form-field supplier-contact-form-wide">

                        <label for="contact_name">
                            Contact Name *
                        </label>

                        <input
                            type="text"
                            id="contact_name"
                            name="name"
                            value="{{ old('name') }}"
                            maxlength="255"
                            required>

                    </div>

                    <div class="supplier-contact-form-field">

                        <label for="contact_job_title">
                            Job Title
                        </label>

                        <input
                            type="text"
                            id="contact_job_title"
                            name="job_title"
                            value="{{ old('job_title') }}"
                            maxlength="255">

                    </div>

                    <div class="supplier-contact-form-field">

                        <label for="contact_department">
                            Department
                        </label>

                        <input
                            type="text"
                            id="contact_department"
                            name="department"
                            value="{{ old('department') }}"
                            maxlength="120"
                            placeholder="Sales, Accounts, Warehouse...">

                    </div>

                    <div class="supplier-contact-form-field supplier-contact-form-wide">

                        <label for="contact_email">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="contact_email"
                            name="email"
                            value="{{ old('email') }}"
                            maxlength="255">

                    </div>

                    <div class="supplier-contact-form-field">

                        <label for="contact_phone">
                            Phone
                        </label>

                        <input
                            type="text"
                            id="contact_phone"
                            name="phone"
                            value="{{ old('phone') }}"
                            maxlength="50">

                    </div>

                    <div class="supplier-contact-form-field">

                        <label for="contact_mobile">
                            Mobile
                        </label>

                        <input
                            type="text"
                            id="contact_mobile"
                            name="mobile"
                            value="{{ old('mobile') }}"
                            maxlength="50">

                    </div>

                    <div class="supplier-contact-form-field supplier-contact-form-wide">

                        <label for="contact_notes">
                            Notes
                        </label>

                        <textarea
                            id="contact_notes"
                            name="notes"
                            rows="4"
                            maxlength="3000">{{ old('notes') }}</textarea>

                    </div>

                    <label class="supplier-contact-checkbox">

                        <input
                            type="checkbox"
                            id="contact_is_primary"
                            name="is_primary"
                            value="1"
                            @checked(old('is_primary'))>

                        <span>

                            <strong>
                                Primary Contact
                            </strong>

                            <small>
                                This replaces the current primary contact.
                            </small>

                        </span>

                    </label>

                    <label class="supplier-contact-checkbox">

                        <input
                            type="checkbox"
                            id="contact_receives_po"
                            name="receives_purchase_orders"
                            value="1"
                            @checked(old('receives_purchase_orders'))>

                        <span>

                            <strong>
                                Receives Purchase Orders
                            </strong>

                            <small>
                                Use this contact for purchase-order delivery.
                            </small>

                        </span>

                    </label>

                </div>

                <div class="supplier-contact-modal-footer">

                    <button
                        type="button"
                        class="supplier-contact-cancel-button"
                        data-close-contact-modal>

                        Cancel

                    </button>

                    <button
                        type="submit"
                        class="supplier-contact-save-button"
                        id="supplierContactSaveButton">

                        <i class="fa-solid fa-floppy-disk"></i>

                        Save Contact

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection

@push('page-styles')

<style>
    .supplier-profile-page {
        --profile-text: #111827;
        --profile-muted: #64748b;
        --profile-border: #e5e7eb;
        --profile-soft-border: #eef2f7;
        --profile-indigo: #4f46e5;
        --profile-indigo-soft: #eef2ff;
        --profile-green: #15803d;
        --profile-green-soft: #ecfdf3;
        --profile-blue: #0369a1;
        --profile-blue-soft: #f0f9ff;
        --profile-orange: #c2410c;
        --profile-orange-soft: #fff7ed;
        --profile-red: #b91c1c;
        --profile-red-soft: #fef2f2;
        --profile-yellow: #a16207;
        --profile-yellow-soft: #fefce8;

        display: flex;
        flex-direction: column;
        gap: 22px;
        min-width: 0;
        color: var(--profile-text);
    }

    .supplier-profile-page *,
    .supplier-profile-page *::before,
    .supplier-profile-page *::after {
        box-sizing: border-box;
    }

    .supplier-profile-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border-radius: 11px;
        font-size: 11px;
        font-weight: 700;
    }

    .supplier-profile-alert.success {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: var(--profile-green);
    }

    .supplier-profile-alert.error {
        border: 1px solid #fecaca;
        background: var(--profile-red-soft);
        color: var(--profile-red);
    }

    .supplier-profile-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 27px 29px;
        border: 1px solid var(--profile-border);
        border-radius: 19px;
        background:
            radial-gradient(circle at top right,
                rgba(79, 70, 229, 0.14),
                transparent 38%),
            linear-gradient(135deg,
                #ffffff,
                #f8f9ff);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .supplier-profile-heading {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 17px;
        min-width: 0;
    }

    .supplier-profile-avatar {
        width: 60px;
        height: 60px;
        flex: 0 0 60px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 17px;
        background:
            linear-gradient(135deg,
                var(--profile-indigo-soft),
                #ddd6fe);
        color: var(--profile-indigo);
        font-size: 22px;
        font-weight: 850;
    }

    .supplier-profile-eyebrow {
        display: block;
        margin-bottom: 6px;
        color: var(--profile-indigo);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .supplier-profile-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 9px;
    }

    .supplier-profile-title-row h1 {
        margin: 0;
        font-size: 28px;
    }

    .supplier-profile-heading p {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 7px 0 0;
        color: var(--profile-muted);
        font-size: 10px;
    }

    .supplier-profile-status,
    .supplier-profile-preferred {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
    }

    .supplier-profile-status>span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .supplier-profile-status.active {
        background: var(--profile-green-soft);
        color: var(--profile-green);
    }

    .supplier-profile-status.inactive {
        background: #f1f5f9;
        color: #475569;
    }

    .supplier-profile-status.blocked {
        background: var(--profile-red-soft);
        color: var(--profile-red);
    }

    .supplier-profile-preferred {
        background: var(--profile-yellow-soft);
        color: var(--profile-yellow);
    }

    .supplier-profile-actions {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 9px;
        flex-shrink: 0;
    }

    .supplier-profile-button {
        min-height: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 14px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .supplier-profile-button.secondary {
        border-color: #d7dce5;
        background: #ffffff;
        color: #475569;
    }

    .supplier-profile-button.edit {
        border-color: #c7d2fe;
        background: var(--profile-indigo-soft);
        color: var(--profile-indigo);
    }

    .supplier-profile-button.purchase {
        border-color: #bae6fd;
        background: var(--profile-blue-soft);
        color: var(--profile-blue);
    }

    .supplier-profile-summary-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 14px;
    }

    .supplier-profile-summary-card {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
        padding: 17px;
        border: 1px solid var(--profile-border);
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 7px 20px rgba(15, 23, 42, 0.04);
    }

    .supplier-profile-summary-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
    }

    .supplier-profile-summary-icon.orders {
        background: var(--profile-indigo-soft);
        color: var(--profile-indigo);
    }

    .supplier-profile-summary-icon.open {
        background: var(--profile-orange-soft);
        color: var(--profile-orange);
    }

    .supplier-profile-summary-icon.received,
    .supplier-profile-summary-icon.spend {
        background: var(--profile-green-soft);
        color: var(--profile-green);
    }

    .supplier-profile-summary-icon.average {
        background: var(--profile-blue-soft);
        color: var(--profile-blue);
    }

    .supplier-profile-summary-icon.rating {
        background: var(--profile-yellow-soft);
        color: var(--profile-yellow);
    }

    .supplier-profile-summary-card span,
    .supplier-profile-summary-card small {
        display: block;
        color: var(--profile-muted);
    }

    .supplier-profile-summary-card span {
        margin-bottom: 3px;
        font-size: 8px;
        font-weight: 700;
    }

    .supplier-profile-summary-card strong {
        display: block;
        margin-bottom: 3px;
        font-size: 17px;
    }

    .supplier-profile-summary-card small {
        font-size: 7px;
    }

    .supplier-profile-information-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .supplier-profile-card,
    .supplier-profile-panel,
    .supplier-profile-notes-card,
    .supplier-profile-danger-card {
        border: 1px solid var(--profile-border);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 7px 22px rgba(15, 23, 42, 0.04);
    }

    .supplier-profile-card {
        padding: 21px;
    }

    .supplier-profile-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .supplier-profile-card-icon {
        width: 43px;
        height: 43px;
        flex: 0 0 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
    }

    .supplier-profile-card-icon.company {
        background: var(--profile-indigo-soft);
        color: var(--profile-indigo);
    }

    .supplier-profile-card-icon.contact {
        background: var(--profile-blue-soft);
        color: var(--profile-blue);
    }

    .supplier-profile-card-icon.commercial {
        background: var(--profile-green-soft);
        color: var(--profile-green);
    }

    .supplier-profile-card-icon.banking {
        background: var(--profile-orange-soft);
        color: var(--profile-orange);
    }

    .supplier-profile-card-header h2,
    .supplier-profile-panel-header h2,
    .supplier-profile-notes-section h2,
    .supplier-profile-danger-card h2 {
        margin: 0 0 4px;
        font-size: 17px;
    }

    .supplier-profile-information-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .supplier-profile-information-list>div {
        padding: 11px;
        border-radius: 9px;
        background: #f8fafc;
    }

    .supplier-profile-information-wide {
        grid-column: 1 / -1;
    }

    .supplier-profile-information-list span,
    .supplier-profile-information-list strong,
    .supplier-profile-information-list a {
        display: block;
    }

    .supplier-profile-information-list span {
        margin-bottom: 4px;
        color: var(--profile-muted);
        font-size: 8px;
        font-weight: 700;
    }

    .supplier-profile-information-list strong,
    .supplier-profile-information-list a {
        color: var(--profile-text);
        font-size: 10px;
        font-weight: 750;
        text-decoration: none;
        word-break: break-word;
    }

    .supplier-profile-information-list a:hover {
        color: var(--profile-indigo);
    }

    .supplier-profile-address {
        line-height: 1.6;
    }

    .supplier-profile-panel {
        overflow: hidden;
    }

    .supplier-profile-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 21px 23px;
        border-bottom: 1px solid var(--profile-border);
    }

    .supplier-profile-panel-header p {
        margin: 0;
        color: var(--profile-muted);
        font-size: 9px;
    }

    .supplier-profile-result-count {
        padding: 8px 11px;
        border-radius: 9px;
        background: var(--profile-indigo-soft);
        color: var(--profile-indigo);
        font-size: 8px;
        font-weight: 700;
    }

    .supplier-profile-result-count strong {
        margin-right: 3px;
        font-size: 15px;
    }

    .supplier-profile-table-wrapper {
        overflow-x: auto;
    }

    .supplier-profile-table {
        width: 100%;
        min-width: 930px;
        border-collapse: collapse;
    }

    .supplier-profile-table th,
    .supplier-profile-table td {
        padding: 13px 15px;
        border-bottom: 1px solid var(--profile-soft-border);
        text-align: left;
        vertical-align: middle;
    }

    .supplier-profile-table th {
        background: #f8fafc;
        color: var(--profile-muted);
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .supplier-profile-table td {
        color: #374151;
        font-size: 9px;
    }

    .supplier-profile-table .number-column {
        text-align: right;
        white-space: nowrap;
    }

    .supplier-profile-table .action-column {
        width: 52px;
        text-align: right;
    }

    .supplier-profile-reference {
        display: block;
        margin-bottom: 3px;
        color: var(--profile-indigo);
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
    }

    .supplier-profile-reference+span {
        color: var(--profile-muted);
        font-size: 7px;
    }

    .supplier-po-status {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
    }

    .supplier-po-status.draft {
        background: #f1f5f9;
        color: #475569;
    }

    .supplier-po-status.ordered {
        background: var(--profile-blue-soft);
        color: var(--profile-blue);
    }

    .supplier-po-status.partially_received {
        background: var(--profile-orange-soft);
        color: var(--profile-orange);
    }

    .supplier-po-status.received {
        background: var(--profile-green-soft);
        color: var(--profile-green);
    }

    .supplier-po-status.cancelled {
        background: var(--profile-red-soft);
        color: var(--profile-red);
    }

    .supplier-profile-spend {
        color: var(--profile-green);
    }

    .supplier-profile-row-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--profile-border);
        border-radius: 8px;
        background: #ffffff;
        color: #64748b;
        text-decoration: none;
    }

    .supplier-profile-row-action:hover {
        border-color: var(--profile-indigo);
        background: var(--profile-indigo);
        color: #ffffff;
    }

    .supplier-profile-empty {
        padding: 42px 20px !important;
        color: var(--profile-muted) !important;
        text-align: center !important;
    }

    .supplier-profile-bottom-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr) minmax(290px, 0.36fr);
        gap: 18px;
    }

    .supplier-profile-notes-card {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        overflow: hidden;
    }

    .supplier-profile-notes-section {
        padding: 21px;
    }

    .supplier-profile-notes-section+.supplier-profile-notes-section {
        border-left: 1px solid var(--profile-border);
    }

    .supplier-profile-notes-section.internal {
        background: #f8fafc;
    }

    .supplier-profile-notes-section p,
    .supplier-profile-danger-card p {
        margin: 0;
        color: var(--profile-muted);
        font-size: 9px;
        line-height: 1.7;
        white-space: pre-line;
    }

    .supplier-profile-danger-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 18px;
        padding: 21px;
        border-color: #fecaca;
        background:
            linear-gradient(135deg,
                #ffffff,
                #fff7f7);
    }

    .supplier-delete-button {
        width: 100%;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid var(--profile-red);
        border-radius: 9px;
        background: var(--profile-red-soft);
        color: var(--profile-red);
        font-family: inherit;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
    }

    .supplier-delete-button:hover {
        background: var(--profile-red);
        color: #ffffff;
    }

    .supplier-contacts-panel {
        border: 1px solid var(--profile-border);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 7px 22px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .supplier-contacts-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 21px 23px;
        border-bottom: 1px solid var(--profile-border);
    }

    .supplier-contacts-header h2 {
        margin: 0 0 5px;
        font-size: 18px;
    }

    .supplier-contacts-header p {
        margin: 0;
        color: var(--profile-muted);
        font-size: 9px;
    }

    .supplier-contact-add-button {
        min-height: 41px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 14px;
        border: 1px solid var(--profile-indigo);
        border-radius: 9px;
        background: var(--profile-indigo);
        color: #ffffff;
        font-family: inherit;
        font-size: 9px;
        font-weight: 800;
        cursor: pointer;
    }

    .supplier-contact-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        padding: 20px;
    }

    .supplier-contact-card {
        min-width: 0;
        padding: 16px;
        border: 1px solid var(--profile-border);
        border-radius: 13px;
        background: #ffffff;
    }

    .supplier-contact-card-top {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-bottom: 14px;
    }

    .supplier-contact-avatar {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: var(--profile-indigo-soft);
        color: var(--profile-indigo);
        font-weight: 850;
    }

    .supplier-contact-name-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
    }

    .supplier-contact-name-row h3 {
        margin: 0;
        font-size: 12px;
    }

    .supplier-contact-card-top p {
        margin: 4px 0 0;
        color: var(--profile-muted);
        font-size: 8px;
    }

    .supplier-contact-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        border-radius: 999px;
        font-size: 7px;
        font-weight: 800;
    }

    .supplier-contact-badge.primary {
        background: var(--profile-yellow-soft);
        color: var(--profile-yellow);
    }

    .supplier-contact-badge.purchase {
        background: var(--profile-blue-soft);
        color: var(--profile-blue);
    }

    .supplier-contact-details {
        display: grid;
        gap: 8px;
    }

    .supplier-contact-details>div {
        padding: 9px;
        border-radius: 8px;
        background: #f8fafc;
    }

    .supplier-contact-details span,
    .supplier-contact-details strong,
    .supplier-contact-details a {
        display: block;
    }

    .supplier-contact-details span {
        margin-bottom: 3px;
        color: var(--profile-muted);
        font-size: 7px;
    }

    .supplier-contact-details strong,
    .supplier-contact-details a {
        color: var(--profile-text);
        font-size: 8px;
        font-weight: 750;
        text-decoration: none;
        overflow-wrap: anywhere;
    }

    .supplier-contact-notes {
        margin-top: 10px;
        padding: 9px;
        border-radius: 8px;
        background: var(--profile-orange-soft);
        color: var(--profile-orange);
        font-size: 8px;
        line-height: 1.6;
    }

    .supplier-contact-actions {
        display: flex;
        gap: 7px;
        margin-top: 13px;
    }

    .supplier-contact-actions form {
        flex: 1;
    }

    .supplier-contact-edit-button,
    .supplier-contact-delete-button {
        width: 100%;
        min-height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 8px;
        font-weight: 800;
        cursor: pointer;
    }

    .supplier-contact-edit-button {
        flex: 1;
        border: 1px solid #c7d2fe;
        background: var(--profile-indigo-soft);
        color: var(--profile-indigo);
    }

    .supplier-contact-delete-button {
        border: 1px solid #fecaca;
        background: var(--profile-red-soft);
        color: var(--profile-red);
    }

    .supplier-contacts-empty {
        grid-column: 1 / -1;
        padding: 42px 20px;
        text-align: center;
    }

    .supplier-contacts-empty>span {
        width: 52px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: var(--profile-indigo-soft);
        color: var(--profile-indigo);
        font-size: 19px;
    }

    .supplier-contacts-empty h3 {
        margin: 12px 0 5px;
    }

    .supplier-contacts-empty p {
        margin: 0 0 13px;
        color: var(--profile-muted);
        font-size: 9px;
    }

    .supplier-contacts-empty button {
        min-height: 38px;
        padding: 0 13px;
        border: 0;
        border-radius: 8px;
        background: var(--profile-indigo);
        color: #ffffff;
        font-family: inherit;
        font-size: 9px;
        font-weight: 800;
        cursor: pointer;
    }

    .supplier-contact-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .supplier-contact-modal[hidden] {
        display: none;
    }

    .supplier-contact-modal-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.58);
        backdrop-filter: blur(3px);
    }

    .supplier-contact-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(100%, 720px);
        max-height: calc(100vh - 40px);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.25);
        overflow-y: auto;
    }

    .supplier-contact-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 19px 21px;
        border-bottom: 1px solid var(--profile-border);
    }

    .supplier-contact-modal-header h2 {
        margin: 0;
        font-size: 18px;
    }

    .supplier-contact-modal-close {
        width: 35px;
        height: 35px;
        border: 1px solid var(--profile-border);
        border-radius: 8px;
        background: #ffffff;
        color: #64748b;
        cursor: pointer;
    }

    .supplier-contact-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        padding: 20px 21px;
    }

    .supplier-contact-form-wide {
        grid-column: 1 / -1;
    }

    .supplier-contact-form-field label {
        display: block;
        margin-bottom: 6px;
        color: #374151;
        font-size: 9px;
        font-weight: 800;
    }

    .supplier-contact-form-field input,
    .supplier-contact-form-field textarea {
        width: 100%;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        color: var(--profile-text);
        font-family: inherit;
        font-size: 10px;
        outline: none;
    }

    .supplier-contact-form-field input {
        height: 41px;
        padding: 0 11px;
    }

    .supplier-contact-form-field textarea {
        padding: 10px 11px;
        resize: vertical;
    }

    .supplier-contact-form-field input:focus,
    .supplier-contact-form-field textarea:focus {
        border-color: var(--profile-indigo);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .supplier-contact-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 11px;
        border: 1px solid var(--profile-border);
        border-radius: 9px;
        background: #f8fafc;
        cursor: pointer;
    }

    .supplier-contact-checkbox input {
        margin-top: 2px;
    }

    .supplier-contact-checkbox strong,
    .supplier-contact-checkbox small {
        display: block;
    }

    .supplier-contact-checkbox strong {
        margin-bottom: 3px;
        font-size: 9px;
    }

    .supplier-contact-checkbox small {
        color: var(--profile-muted);
        font-size: 7px;
        line-height: 1.5;
    }

    .supplier-contact-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 15px 21px;
        border-top: 1px solid var(--profile-border);
        background: #f8fafc;
    }

    .supplier-contact-cancel-button,
    .supplier-contact-save-button {
        min-height: 40px;
        padding: 0 14px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 9px;
        font-weight: 800;
        cursor: pointer;
    }

    .supplier-contact-cancel-button {
        border: 1px solid #d7dce5;
        background: #ffffff;
        color: #475569;
    }

    .supplier-contact-save-button {
        border: 1px solid var(--profile-indigo);
        background: var(--profile-indigo);
        color: #ffffff;
    }




    @media (max-width: 1250px) {
        .supplier-profile-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .supplier-profile-summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 1050px) {
        .supplier-contact-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 850px) {

        .supplier-profile-information-grid,
        .supplier-profile-bottom-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .supplier-profile-header {
            padding: 22px 18px;
        }

        .supplier-profile-heading {
            align-items: flex-start;
        }

        .supplier-profile-actions,
        .supplier-profile-button {
            width: 100%;
        }

        .supplier-profile-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .supplier-profile-summary-grid {
            grid-template-columns: 1fr;
        }

        .supplier-profile-information-list,
        .supplier-profile-notes-card {
            grid-template-columns: 1fr;
        }

        .supplier-profile-information-wide {
            grid-column: auto;
        }

        .supplier-profile-notes-section+.supplier-profile-notes-section {
            border-top: 1px solid var(--profile-border);
            border-left: 0;
        }

        .supplier-contacts-header {
            align-items: stretch;
            flex-direction: column;
        }

        .supplier-contact-add-button {
            width: 100%;
        }

        .supplier-contact-grid,
        .supplier-contact-form-grid {
            grid-template-columns: 1fr;
        }

        .supplier-contact-form-wide {
            grid-column: auto;
        }
    }
.supplier-documents-panel {
    margin-bottom: 28px;
    padding: 28px;
    border: 1px solid #e5e7eb;
    border-radius: 22px;
    background: #ffffff;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
}

.supplier-documents-header,
.supplier-document-upload-heading,
.supplier-document-title-row,
.supplier-document-actions {
    display: flex;
    align-items: center;
}

.supplier-documents-header {
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}

.supplier-documents-header h2,
.supplier-document-upload-heading h3,
.supplier-document-card h3 {
    margin: 0;
    color: #111827;
}

.supplier-documents-header p,
.supplier-document-upload-heading p {
    margin: 6px 0 0;
    color: #6b7280;
}

.supplier-documents-count {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 999px;
    background: #f3f4f6;
    color: #374151;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.supplier-documents-layout {
    display: grid;
    grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}

.supplier-document-upload-card {
    padding: 22px;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    background: linear-gradient(145deg, #f8fbff, #ffffff);
}

.supplier-document-upload-heading {
    gap: 12px;
    margin-bottom: 20px;
}

.supplier-document-upload-heading > span {
    display: inline-grid;
    width: 44px;
    height: 44px;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 13px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 18px;
}

.supplier-document-form-field {
    margin-bottom: 15px;
}

.supplier-document-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.supplier-document-form-field label:not(.supplier-document-file-picker) {
    display: block;
    margin-bottom: 7px;
    color: #374151;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.supplier-document-form-field input,
.supplier-document-form-field select,
.supplier-document-form-field textarea {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 11px;
    background: #ffffff;
    color: #111827;
    font: inherit;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.supplier-document-form-field input,
.supplier-document-form-field select {
    min-height: 44px;
    padding: 0 12px;
}

.supplier-document-form-field textarea {
    min-height: 88px;
    padding: 11px 12px;
    resize: vertical;
}

.supplier-document-form-field input:focus,
.supplier-document-form-field select:focus,
.supplier-document-form-field textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.supplier-document-file-input {
    position: absolute;
    width: 1px !important;
    height: 1px;
    padding: 0 !important;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0 !important;
}

.supplier-document-file-picker {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 48px;
    padding: 10px 12px;
    border: 1px dashed #93c5fd;
    border-radius: 12px;
    background: #eff6ff;
    color: #1e40af;
    cursor: pointer;
}

.supplier-document-file-picker span {
    min-width: 0;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.supplier-document-file-picker strong {
    font-size: 12px;
    text-transform: uppercase;
}

.supplier-document-upload-button {
    display: inline-flex;
    width: 100%;
    min-height: 46px;
    align-items: center;
    justify-content: center;
    gap: 9px;
    border: 0;
    border-radius: 12px;
    background: #1d4ed8;
    color: #ffffff;
    font-weight: 800;
    cursor: pointer;
}

.supplier-document-upload-button:disabled {
    opacity: 0.65;
    cursor: wait;
}

.supplier-document-errors {
    margin-bottom: 18px;
    padding: 13px 15px;
    border: 1px solid #fecaca;
    border-radius: 12px;
    background: #fef2f2;
    color: #991b1b;
    font-size: 13px;
}

.supplier-document-errors ul {
    margin: 7px 0 0;
    padding-left: 18px;
}

.supplier-document-list {
    display: grid;
    gap: 14px;
}

.supplier-document-card {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    gap: 16px;
    align-items: start;
    padding: 18px;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    background: #ffffff;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.supplier-document-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}

.supplier-document-icon {
    display: inline-grid;
    width: 46px;
    height: 46px;
    place-items: center;
    border-radius: 13px;
    background: #fef2f2;
    color: #dc2626;
    font-size: 19px;
}

.supplier-document-main {
    min-width: 0;
}

.supplier-document-title-row {
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
}

.supplier-document-type {
    display: block;
    margin-bottom: 3px;
    color: #2563eb;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.supplier-document-card h3 {
    overflow-wrap: anywhere;
    font-size: 16px;
}

.supplier-document-expiry {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 9px;
    border-radius: 999px;
    background: #fff7ed;
    color: #c2410c;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.supplier-document-expiry.expired {
    background: #fef2f2;
    color: #b91c1c;
}

.supplier-document-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 14px;
    margin-top: 10px;
    color: #6b7280;
    font-size: 12px;
}

.supplier-document-meta span {
    display: inline-flex;
    min-width: 0;
    align-items: center;
    gap: 5px;
    overflow-wrap: anywhere;
}

.supplier-document-notes {
    margin: 11px 0 0;
    color: #4b5563;
    font-size: 13px;
    line-height: 1.6;
}

.supplier-document-actions {
    gap: 8px;
}

.supplier-document-download-button,
.supplier-document-delete-button {
    display: inline-flex;
    min-height: 38px;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0 11px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
    cursor: pointer;
}

.supplier-document-download-button {
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    color: #1d4ed8;
}

.supplier-document-delete-button {
    border: 1px solid #fecaca;
    background: #fff;
    color: #b91c1c;
}

.supplier-document-delete-button:disabled {
    opacity: 0.6;
    cursor: wait;
}

.supplier-documents-empty {
    display: grid;
    min-height: 290px;
    place-items: center;
    align-content: center;
    padding: 28px;
    border: 1px dashed #d1d5db;
    border-radius: 16px;
    background: #f9fafb;
    text-align: center;
}

.supplier-documents-empty > span {
    display: inline-grid;
    width: 54px;
    height: 54px;
    place-items: center;
    margin-bottom: 12px;
    border-radius: 16px;
    background: #e5e7eb;
    color: #4b5563;
    font-size: 21px;
}

.supplier-documents-empty h3,
.supplier-documents-empty p {
    margin: 0;
}

.supplier-documents-empty p {
    margin-top: 6px;
    color: #6b7280;
}

@media (max-width: 1100px) {
    .supplier-documents-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 760px) {
    .supplier-documents-panel {
        padding: 20px;
        border-radius: 17px;
    }

    .supplier-documents-header,
    .supplier-document-title-row,
    .supplier-document-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .supplier-documents-count {
        align-self: flex-start;
    }

    .supplier-document-form-row,
    .supplier-document-card {
        grid-template-columns: 1fr;
    }

    .supplier-document-icon {
        width: 42px;
        height: 42px;
    }

    .supplier-document-download-button,
    .supplier-document-delete-button {
        width: 100%;
    }
}

.supplier-ratings-panel {
    margin-bottom: 28px;
    padding: 28px;
    border: 1px solid #e5e7eb;
    border-radius: 22px;
    background: #ffffff;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
}

.supplier-ratings-header,
.supplier-ratings-header-actions,
.supplier-ratings-average,
.supplier-rating-card-heading,
.supplier-rating-actions {
    display: flex;
    align-items: center;
}

.supplier-ratings-header {
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}

.supplier-ratings-header h2,
.supplier-rating-card h3,
.supplier-ratings-empty h3 {
    margin: 0;
    color: #111827;
}

.supplier-ratings-header p {
    margin: 6px 0 0;
    color: #6b7280;
}

.supplier-ratings-header-actions {
    gap: 14px;
}

.supplier-ratings-average {
    gap: 10px;
    padding: 9px 13px;
    border: 1px solid #fde68a;
    border-radius: 13px;
    background: #fffbeb;
}

.supplier-ratings-average > span {
    color: #92400e;
    font-size: 25px;
    font-weight: 900;
}

.supplier-ratings-average small {
    display: block;
    margin-top: 3px;
    color: #78716c;
    font-size: 11px;
}

.supplier-rating-stars {
    display: flex;
    gap: 3px;
    color: #f59e0b;
    font-size: 12px;
}

.supplier-rating-add-button,
.supplier-ratings-empty button {
    display: inline-flex;
    min-height: 42px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 0 15px;
    border: 0;
    border-radius: 11px;
    background: #d97706;
    color: #ffffff;
    font-weight: 800;
    cursor: pointer;
}

.supplier-rating-list {
    display: grid;
    gap: 15px;
}

.supplier-rating-card {
    display: grid;
    grid-template-columns: 105px minmax(0, 1fr) auto;
    gap: 18px;
    align-items: start;
    padding: 20px;
    border: 1px solid #e5e7eb;
    border-radius: 17px;
    background: #ffffff;
}

.supplier-rating-card-score {
    display: grid;
    min-height: 104px;
    place-items: center;
    align-content: center;
    border-radius: 15px;
    background: linear-gradient(145deg, #fffbeb, #fef3c7);
    color: #92400e;
}

.supplier-rating-card-score strong {
    font-size: 27px;
    line-height: 1;
}

.supplier-rating-card-score > span {
    margin: 4px 0 8px;
    font-size: 11px;
    font-weight: 700;
}

.supplier-rating-card-main {
    min-width: 0;
}

.supplier-rating-card-heading {
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
}

.supplier-rating-reference {
    display: block;
    margin-bottom: 4px;
    color: #6b7280;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}

.supplier-rating-reference i {
    margin-right: 4px;
}

.supplier-rating-recommendation {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 9px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 900;
    white-space: nowrap;
}

.supplier-rating-recommendation.recommended {
    background: #ecfdf5;
    color: #047857;
}

.supplier-rating-recommendation.not-recommended {
    background: #fef2f2;
    color: #b91c1c;
}

.supplier-rating-score-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin-top: 15px;
}

.supplier-rating-score-grid > div {
    padding: 10px;
    border-radius: 11px;
    background: #f8fafc;
}

.supplier-rating-score-grid span,
.supplier-rating-score-grid strong {
    display: block;
}

.supplier-rating-score-grid > div > span:first-child {
    color: #64748b;
    font-size: 10px;
    font-weight: 800;
}

.supplier-rating-score-grid strong {
    margin: 3px 0 7px;
    color: #1f2937;
    font-size: 13px;
}

.supplier-rating-score-track {
    height: 4px;
    overflow: hidden;
    border-radius: 999px;
    background: #e5e7eb;
}

.supplier-rating-score-track > span {
    height: 100%;
    border-radius: inherit;
    background: #f59e0b;
}

.supplier-rating-review {
    margin: 14px 0 0;
    color: #4b5563;
    font-size: 13px;
    line-height: 1.65;
}

.supplier-rating-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 13px;
    color: #6b7280;
    font-size: 11px;
}

.supplier-rating-meta span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.supplier-rating-actions {
    gap: 7px;
}

.supplier-rating-edit-button,
.supplier-rating-delete-form button {
    display: inline-flex;
    min-height: 37px;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 10px;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
}

.supplier-rating-edit-button {
    border: 1px solid #fde68a;
    background: #fffbeb;
    color: #a16207;
}

.supplier-rating-delete-form button {
    border: 1px solid #fecaca;
    background: #ffffff;
    color: #b91c1c;
}

.supplier-rating-actions button:disabled {
    opacity: 0.6;
    cursor: wait;
}

.supplier-ratings-empty {
    display: grid;
    min-height: 245px;
    place-items: center;
    align-content: center;
    padding: 28px;
    border: 1px dashed #d1d5db;
    border-radius: 16px;
    background: #f9fafb;
    text-align: center;
}

.supplier-ratings-empty > span {
    display: inline-grid;
    width: 56px;
    height: 56px;
    place-items: center;
    margin-bottom: 12px;
    border-radius: 17px;
    background: #fef3c7;
    color: #d97706;
    font-size: 22px;
}

.supplier-ratings-empty p {
    margin: 7px 0 15px;
    color: #6b7280;
}

.supplier-contact-form-field select {
    width: 100%;
    height: 41px;
    padding: 0 11px;
    border: 1px solid #d7dce5;
    border-radius: 9px;
    background: #ffffff;
    color: var(--profile-text);
    font-family: inherit;
    font-size: 10px;
    outline: none;
}

.supplier-contact-form-field select:focus {
    border-color: var(--profile-indigo);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.supplier-rating-modal-errors {
    margin: 18px 21px 0;
    padding: 12px 14px;
    border: 1px solid #fecaca;
    border-radius: 10px;
    background: #fef2f2;
    color: #991b1b;
    font-size: 11px;
}

.supplier-rating-modal-errors ul {
    margin: 6px 0 0;
    padding-left: 17px;
}

@media (max-width: 1000px) {
    .supplier-rating-card {
        grid-template-columns: 90px minmax(0, 1fr);
    }

    .supplier-rating-actions {
        grid-column: 1 / -1;
        justify-content: flex-end;
    }

    .supplier-rating-score-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    .supplier-ratings-panel {
        padding: 20px;
        border-radius: 17px;
    }

    .supplier-ratings-header,
    .supplier-ratings-header-actions,
    .supplier-rating-card,
    .supplier-rating-card-heading,
    .supplier-rating-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .supplier-rating-card {
        display: flex;
    }

    .supplier-rating-card-score {
        min-height: 92px;
    }

    .supplier-rating-score-grid {
        grid-template-columns: 1fr 1fr;
    }

    .supplier-rating-edit-button,
    .supplier-rating-delete-form button,
    .supplier-rating-add-button {
        width: 100%;
    }
}

.supplier-analytics-panel {
    margin-bottom: 28px;
    padding: 28px;
    border: 1px solid #e5e7eb;
    border-radius: 22px;
    background: #ffffff;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
}

.supplier-analytics-header,
.supplier-analytics-card-header,
.supplier-status-chart-layout,
.supplier-rating-analytics-list > div > div,
.supplier-insight-list > div {
    display: flex;
    align-items: center;
}

.supplier-analytics-header {
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
}

.supplier-analytics-header h2,
.supplier-analytics-card-header h3 {
    margin: 0;
    color: #111827;
}

.supplier-analytics-header p {
    margin: 6px 0 0;
    color: #6b7280;
}

.supplier-analytics-period {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 13px;
    border-radius: 999px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.supplier-analytics-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}

.supplier-analytics-kpi-grid > article {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    padding: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    background: #ffffff;
}

.supplier-analytics-kpi-icon {
    display: inline-grid;
    width: 43px;
    height: 43px;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 13px;
    font-size: 17px;
}

.supplier-analytics-kpi-icon.received {
    background: #ecfdf5;
    color: #047857;
}

.supplier-analytics-kpi-icon.cancelled {
    background: #fef2f2;
    color: #b91c1c;
}

.supplier-analytics-kpi-icon.recommend {
    background: #fffbeb;
    color: #b45309;
}

.supplier-analytics-kpi-icon.compliance {
    background: #eff6ff;
    color: #1d4ed8;
}

.supplier-analytics-kpi-grid article span,
.supplier-analytics-kpi-grid article small {
    display: block;
    color: #6b7280;
}

.supplier-analytics-kpi-grid article span {
    font-size: 11px;
    font-weight: 700;
}

.supplier-analytics-kpi-grid article strong {
    display: block;
    margin: 2px 0;
    color: #111827;
    font-size: 22px;
}

.supplier-analytics-kpi-grid article small {
    overflow: hidden;
    font-size: 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.supplier-analytics-chart-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.45fr) minmax(300px, 0.8fr);
    gap: 16px;
}

.supplier-analytics-chart-card {
    min-width: 0;
    padding: 20px;
    border: 1px solid #e5e7eb;
    border-radius: 17px;
    background: #ffffff;
}

.supplier-analytics-card-header {
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.supplier-analytics-card-header > div > span {
    display: block;
    margin-bottom: 3px;
    color: #6b7280;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.supplier-analytics-card-header h3 {
    font-size: 16px;
}

.supplier-analytics-card-header > strong {
    color: #111827;
    font-size: 17px;
}

.supplier-analytics-card-header > i {
    color: #4f46e5;
    font-size: 20px;
}

.supplier-monthly-spend-chart {
    display: grid;
    height: 245px;
    grid-template-columns: repeat(12, minmax(24px, 1fr));
    gap: 8px;
    align-items: end;
    padding-top: 25px;
    border-bottom: 1px solid #d1d5db;
    background-image: linear-gradient(
        to bottom,
        rgba(229, 231, 235, 0.7) 1px,
        transparent 1px
    );
    background-size: 100% 25%;
}

.supplier-spend-bar-column {
    display: grid;
    height: 100%;
    grid-template-rows: 18px minmax(0, 1fr) 22px;
    gap: 5px;
    align-items: end;
    min-width: 0;
    text-align: center;
}

.supplier-spend-bar-value {
    overflow: hidden;
    color: #6b7280;
    font-size: 8px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.supplier-spend-bar-track {
    display: flex;
    width: min(28px, 74%);
    height: 100%;
    align-items: flex-end;
    justify-self: center;
    overflow: hidden;
    border-radius: 7px 7px 2px 2px;
    background: #eef2ff;
}

.supplier-spend-bar-track > span {
    width: 100%;
    min-height: 3px;
    border-radius: inherit;
    background: linear-gradient(180deg, #6366f1, #4338ca);
    transition: height 0.35s ease;
}

.supplier-spend-bar-column strong {
    color: #6b7280;
    font-size: 9px;
}

.supplier-status-chart-layout {
    justify-content: center;
    gap: 24px;
    min-height: 245px;
}

.supplier-status-donut {
    display: grid;
    width: 148px;
    height: 148px;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 50%;
}

.supplier-status-donut > div {
    display: grid;
    width: 92px;
    height: 92px;
    place-items: center;
    align-content: center;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 0 0 1px rgba(229, 231, 235, 0.8);
}

.supplier-status-donut strong,
.supplier-status-donut span {
    display: block;
}

.supplier-status-donut strong {
    color: #111827;
    font-size: 25px;
}

.supplier-status-donut span {
    color: #6b7280;
    font-size: 10px;
}

.supplier-status-legend {
    display: grid;
    width: 100%;
    gap: 9px;
}

.supplier-status-legend > div {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto auto;
    gap: 7px;
    align-items: center;
    color: #4b5563;
    font-size: 10px;
}

.supplier-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.supplier-status-legend strong {
    color: #111827;
}

.supplier-status-legend small {
    min-width: 38px;
    color: #6b7280;
    text-align: right;
}

.supplier-rating-analytics-list {
    display: grid;
    gap: 17px;
    padding: 4px 0;
}

.supplier-rating-analytics-list > div > div {
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 7px;
    color: #4b5563;
    font-size: 11px;
}

.supplier-rating-analytics-list strong {
    color: #111827;
}

.supplier-rating-analytics-track {
    display: block;
    height: 8px;
    overflow: hidden;
    border-radius: 999px;
    background: #f3f4f6;
}

.supplier-rating-analytics-track > span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #fbbf24, #d97706);
}

.supplier-insight-list {
    display: grid;
    gap: 2px;
}

.supplier-insight-list > div {
    justify-content: space-between;
    gap: 12px;
    padding: 11px 0;
    border-bottom: 1px solid #f1f5f9;
}

.supplier-insight-list > div:last-child {
    border-bottom: 0;
}

.supplier-insight-list span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #6b7280;
    font-size: 11px;
}

.supplier-insight-list span i {
    width: 15px;
    color: #6366f1;
    text-align: center;
}

.supplier-insight-list strong {
    color: #111827;
    font-size: 12px;
    text-align: right;
}

@media (max-width: 1180px) {
    .supplier-analytics-kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .supplier-analytics-chart-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 760px) {
    .supplier-analytics-panel {
        padding: 20px;
        border-radius: 17px;
    }

    .supplier-analytics-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .supplier-analytics-kpi-grid {
        grid-template-columns: 1fr;
    }

    .supplier-monthly-spend-chart {
        gap: 3px;
        overflow-x: auto;
    }

    .supplier-spend-bar-value {
        display: none;
    }

    .supplier-status-chart-layout {
        align-items: center;
        flex-direction: column;
    }
}

.supplier-po-row-actions {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.supplier-po-email-button {
    display: inline-grid;
    width: 32px;
    height: 32px;
    place-items: center;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    background: #eff6ff;
    color: #1d4ed8;
    cursor: pointer;
}

.supplier-po-email-button:hover {
    border-color: #2563eb;
    background: #2563eb;
    color: #ffffff;
}

.supplier-email-history-panel {
    margin-bottom: 28px;
    padding: 28px;
    border: 1px solid #e5e7eb;
    border-radius: 22px;
    background: #ffffff;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
}

.supplier-email-history-header,
.supplier-email-history-card,
.supplier-email-history-meta,
.supplier-email-history-empty,
.supplier-po-email-reference {
    display: flex;
    align-items: center;
}

.supplier-email-history-header {
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 20px;
}

.supplier-email-history-header h2,
.supplier-email-history-card h3,
.supplier-email-history-empty h3 {
    margin: 0;
    color: #111827;
}

.supplier-email-history-header p {
    margin: 6px 0 0;
    color: #6b7280;
}

.supplier-email-history-header > span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 13px;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
}

.supplier-email-history-list {
    display: grid;
    gap: 12px;
}

.supplier-email-history-card {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    gap: 14px;
    align-items: start;
    padding: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    background: #ffffff;
}

.supplier-email-status-icon {
    display: inline-grid;
    width: 40px;
    height: 40px;
    place-items: center;
    border-radius: 12px;
}

.supplier-email-status-icon.queued {
    background: #fff7ed;
    color: #c2410c;
}

.supplier-email-status-icon.sent {
    background: #ecfdf5;
    color: #047857;
}

.supplier-email-status-icon.failed {
    background: #fef2f2;
    color: #b91c1c;
}

.supplier-email-history-main {
    min-width: 0;
}

.supplier-email-history-main > div:first-child > span {
    display: block;
    margin-bottom: 3px;
    color: #2563eb;
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
}

.supplier-email-history-card h3 {
    overflow-wrap: anywhere;
    font-size: 14px;
}

.supplier-email-recipient-list {
    margin-top: 8px;
    color: #6b7280;
    font-size: 11px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.supplier-email-recipient-list i {
    margin-right: 5px;
}

.supplier-email-failure-message {
    margin: 9px 0 0;
    padding: 8px 10px;
    border-radius: 8px;
    background: #fef2f2;
    color: #991b1b;
    font-size: 10px;
    line-height: 1.5;
}

.supplier-email-history-meta {
    min-width: 150px;
    align-items: flex-end;
    flex-direction: column;
    gap: 6px;
    color: #6b7280;
    font-size: 10px;
}

.supplier-email-history-meta > span:not(.supplier-email-status) {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.supplier-email-status {
    padding: 5px 8px;
    border-radius: 999px;
    font-size: 9px;
    font-weight: 900;
    text-transform: uppercase;
}

.supplier-email-status.queued {
    background: #fff7ed;
    color: #c2410c;
}

.supplier-email-status.sent {
    background: #ecfdf5;
    color: #047857;
}

.supplier-email-status.failed {
    background: #fef2f2;
    color: #b91c1c;
}

.supplier-email-history-empty {
    justify-content: center;
    gap: 14px;
    min-height: 130px;
    padding: 24px;
    border: 1px dashed #d1d5db;
    border-radius: 14px;
    background: #f9fafb;
}

.supplier-email-history-empty > i {
    color: #9ca3af;
    font-size: 26px;
}

.supplier-email-history-empty p {
    margin: 5px 0 0;
    color: #6b7280;
    font-size: 12px;
}

.supplier-po-email-modal-dialog {
    width: min(100%, 760px);
}

.supplier-po-email-form-content {
    display: grid;
    gap: 16px;
    padding: 20px 21px;
}

.supplier-po-email-reference {
    gap: 11px;
    padding: 12px 14px;
    border: 1px solid #bfdbfe;
    border-radius: 11px;
    background: #eff6ff;
}

.supplier-po-email-reference > span {
    display: inline-grid;
    width: 38px;
    height: 38px;
    place-items: center;
    border-radius: 10px;
    background: #dbeafe;
    color: #1d4ed8;
}

.supplier-po-email-reference small,
.supplier-po-email-reference strong {
    display: block;
}

.supplier-po-email-reference small {
    margin-bottom: 2px;
    color: #6b7280;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
}

.supplier-po-email-reference strong {
    color: #111827;
    font-size: 14px;
}

.supplier-po-recipient-fieldset {
    margin: 0;
    padding: 0;
    border: 0;
}

.supplier-po-recipient-fieldset legend {
    margin-bottom: 8px;
    color: #374151;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
}

.supplier-po-recipient-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 9px;
}

.supplier-po-recipient-option {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    min-width: 0;
    padding: 11px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #ffffff;
    cursor: pointer;
}

.supplier-po-recipient-option:has(input:checked) {
    border-color: #93c5fd;
    background: #eff6ff;
}

.supplier-po-recipient-option input {
    margin-top: 3px;
}

.supplier-po-recipient-option span {
    min-width: 0;
}

.supplier-po-recipient-option strong,
.supplier-po-recipient-option small,
.supplier-po-recipient-option em {
    display: block;
}

.supplier-po-recipient-option strong {
    color: #111827;
    font-size: 10px;
}

.supplier-po-recipient-option small {
    margin-top: 2px;
    overflow: hidden;
    color: #6b7280;
    font-size: 9px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.supplier-po-recipient-option em {
    width: fit-content;
    margin-top: 5px;
    padding: 3px 6px;
    border-radius: 999px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 8px;
    font-style: normal;
    font-weight: 900;
    text-transform: uppercase;
}

.supplier-email-option {
    grid-column: 1 / -1;
}

@media (max-width: 760px) {
    .supplier-email-history-panel {
        padding: 20px;
        border-radius: 17px;
    }

    .supplier-email-history-header,
    .supplier-email-history-card {
        align-items: flex-start;
        grid-template-columns: 1fr;
        flex-direction: column;
    }

    .supplier-email-history-meta {
        min-width: 0;
        align-items: flex-start;
    }

    .supplier-po-recipient-grid {
        grid-template-columns: 1fr;
    }

    .supplier-email-option {
        grid-column: auto;
    }
}

</style>

@endpush

@push('page-scripts')

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {
            'use strict';

            /*
            |--------------------------------------------------------------------------
            | Delete Supplier
            |--------------------------------------------------------------------------
            */

            const deleteForm = document.getElementById(
                'deleteSupplierForm'
            );

            if (deleteForm) {
                deleteForm.addEventListener(
                    'submit',
                    function(event) {
                        const confirmed = window.confirm(
                            'Delete this supplier? The supplier will be soft-deleted and suppliers with open purchase orders cannot be removed.'
                        );

                        if (!confirmed) {
                            event.preventDefault();

                            return;
                        }

                        const button = deleteForm.querySelector(
                            'button[type="submit"]'
                        );

                        if (button) {
                            button.disabled = true;

                            button.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i>' +
                                ' Deleting...';
                        }
                    }
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Supplier Contact Modal Elements
            |--------------------------------------------------------------------------
            */

            const contactModal = document.getElementById(
                'supplierContactModal'
            );

            const contactForm = document.getElementById(
                'supplierContactForm'
            );

            const contactMethod = document.getElementById(
                'supplierContactMethod'
            );

            const contactId = document.getElementById(
                'supplierContactId'
            );

            const contactModalTitle = document.getElementById(
                'supplierContactModalTitle'
            );

            const contactSaveButton = document.getElementById(
                'supplierContactSaveButton'
            );

            const openContactButtons = [
                document.getElementById(
                    'openSupplierContactModal'
                ),

                document.getElementById(
                    'openFirstSupplierContactModal'
                )
            ].filter(Boolean);

            const closeContactButtons =
                document.querySelectorAll(
                    '[data-close-contact-modal]'
                );

            const editContactButtons =
                document.querySelectorAll(
                    '[data-edit-contact]'
                );

            const contactDeleteForms =
                document.querySelectorAll(
                    '.supplier-contact-delete-form'
                );

            /*
            |--------------------------------------------------------------------------
            | Supplier Contact Fields
            |--------------------------------------------------------------------------
            */

            const contactFields = {
                name: document.getElementById(
                    'contact_name'
                ),

                jobTitle: document.getElementById(
                    'contact_job_title'
                ),

                department: document.getElementById(
                    'contact_department'
                ),

                email: document.getElementById(
                    'contact_email'
                ),

                phone: document.getElementById(
                    'contact_phone'
                ),

                mobile: document.getElementById(
                    'contact_mobile'
                ),

                primary: document.getElementById(
                    'contact_is_primary'
                ),

                receivesPo: document.getElementById(
                    'contact_receives_po'
                ),

                notes: document.getElementById(
                    'contact_notes'
                )
            };

            const createContactUrl = contactForm ?
                contactForm.dataset.createUrl || contactForm.action :
                '';

            /*
            |--------------------------------------------------------------------------
            | Open Contact Modal
            |--------------------------------------------------------------------------
            */

            function openContactModal() {
                if (!contactModal) {
                    return;
                }

                contactModal.hidden = false;

                document.body.style.overflow = 'hidden';

                window.setTimeout(
                    function() {
                        if (contactFields.name) {
                            contactFields.name.focus();
                        }
                    },
                    50
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Close Contact Modal
            |--------------------------------------------------------------------------
            */

            function closeContactModal() {
                if (!contactModal) {
                    return;
                }

                contactModal.hidden = true;

                document.body.style.overflow = '';
            }

            /*
            |--------------------------------------------------------------------------
            | Set Contact Field Value Safely
            |--------------------------------------------------------------------------
            */

            function setContactFieldValue(
                field,
                value
            ) {
                if (!field) {
                    return;
                }

                field.value = value || '';
            }

            /*
            |--------------------------------------------------------------------------
            | Reset Contact Form
            |--------------------------------------------------------------------------
            */

            function resetContactForm() {
                if (!contactForm) {
                    return;
                }

                contactForm.reset();

                contactForm.action =
                    createContactUrl;

                if (contactMethod) {
                    contactMethod.value = 'POST';
                }

                if (contactId) {
                    contactId.value = '';
                }

                if (contactModalTitle) {
                    contactModalTitle.textContent =
                        'Add Contact';
                }

                if (contactSaveButton) {
                    contactSaveButton.disabled = false;

                    contactSaveButton.innerHTML =
                        '<i class="fa-solid fa-floppy-disk"></i>' +
                        ' Save Contact';
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Add Contact Buttons
            |--------------------------------------------------------------------------
            */

            openContactButtons.forEach(
                function(button) {
                    button.addEventListener(
                        'click',
                        function() {
                            resetContactForm();

                            openContactModal();
                        }
                    );
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Close Modal Buttons and Overlay
            |--------------------------------------------------------------------------
            */

            closeContactButtons.forEach(
                function(button) {
                    button.addEventListener(
                        'click',
                        closeContactModal
                    );
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Edit Contact Buttons
            |--------------------------------------------------------------------------
            */

            editContactButtons.forEach(
                function(button) {
                    button.addEventListener(
                        'click',
                        function() {
                            if (
                                !contactForm ||
                                !contactMethod
                            ) {
                                return;
                            }

                            resetContactForm();

                            const updateUrl =
                                button.dataset.updateUrl ||
                                '';

                            if (updateUrl === '') {
                                window.alert(
                                    'The contact update URL is missing.'
                                );

                                return;
                            }

                            contactForm.action =
                                updateUrl;

                            contactMethod.value =
                                'PUT';

                            if (contactId) {
                                contactId.value =
                                    button.dataset.contactId ||
                                    '';
                            }

                            if (contactModalTitle) {
                                contactModalTitle.textContent =
                                    'Edit Contact';
                            }

                            setContactFieldValue(
                                contactFields.name,
                                button.dataset.name
                            );

                            setContactFieldValue(
                                contactFields.jobTitle,
                                button.dataset.jobTitle
                            );

                            setContactFieldValue(
                                contactFields.department,
                                button.dataset.department
                            );

                            setContactFieldValue(
                                contactFields.email,
                                button.dataset.email
                            );

                            setContactFieldValue(
                                contactFields.phone,
                                button.dataset.phone
                            );

                            setContactFieldValue(
                                contactFields.mobile,
                                button.dataset.mobile
                            );

                            setContactFieldValue(
                                contactFields.notes,
                                button.dataset.notes
                            );

                            if (contactFields.primary) {
                                contactFields.primary.checked =
                                    button.dataset.primary ===
                                    '1';
                            }

                            if (contactFields.receivesPo) {
                                contactFields.receivesPo.checked =
                                    button.dataset.receivesPo ===
                                    '1';
                            }

                            openContactModal();
                        }
                    );
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Contact Form Submission
            |--------------------------------------------------------------------------
            */

            if (contactForm) {
                contactForm.addEventListener(
                    'submit',
                    function(event) {
                        if (
                            !contactForm.checkValidity()
                        ) {
                            event.preventDefault();

                            contactForm.reportValidity();

                            return;
                        }

                        if (contactSaveButton) {
                            contactSaveButton.disabled = true;

                            contactSaveButton.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i>' +
                                ' Saving...';
                        }
                    }
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Contact Confirmation
            |--------------------------------------------------------------------------
            */

            contactDeleteForms.forEach(
                function(form) {
                    form.addEventListener(
                        'submit',
                        function(event) {
                            const confirmed =
                                window.confirm(
                                    'Delete this supplier contact?'
                                );

                            if (!confirmed) {
                                event.preventDefault();

                                return;
                            }

                            const deleteButton =
                                form.querySelector(
                                    'button[type="submit"]'
                                );

                            if (deleteButton) {
                                deleteButton.disabled = true;

                                deleteButton.innerHTML =
                                    '<i class="fa-solid fa-spinner fa-spin"></i>' +
                                    ' Deleting...';
                            }
                        }
                    );
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Restore Contact Form After Validation Failure
            |--------------------------------------------------------------------------
            */

            if (
                contactForm &&
                contactForm.dataset.hasErrors === '1'
            ) {
                const editingContactId =
                    contactForm.dataset.editingContactId ||
                    '';

                if (editingContactId !== '') {
                    const editButton = Array.from(
                        editContactButtons
                    ).find(
                        function(button) {
                            return button.dataset.contactId ===
                                editingContactId;
                        }
                    );

                    if (editButton && contactMethod) {
                        contactForm.action =
                            editButton.dataset.updateUrl ||
                            createContactUrl;

                        contactMethod.value = 'PUT';

                        if (contactModalTitle) {
                            contactModalTitle.textContent =
                                'Edit Contact';
                        }
                    }
                } else {
                    contactForm.action =
                        createContactUrl;

                    if (contactMethod) {
                        contactMethod.value = 'POST';
                    }
                }

                openContactModal();
            }

            /*
            |--------------------------------------------------------------------------
            | Supplier Documents
            |--------------------------------------------------------------------------
            */

            const documentForm = document.getElementById(
                'supplierDocumentForm'
            );

            const documentFileInput = document.getElementById(
                'supplier_document_file'
            );

            const documentFileName = document.getElementById(
                'supplierDocumentFileName'
            );

            const documentUploadButton = document.getElementById(
                'supplierDocumentUploadButton'
            );

            const documentDeleteForms =
                document.querySelectorAll(
                    '.supplier-document-delete-form'
                );

            if (documentFileInput) {
                documentFileInput.addEventListener(
                    'change',
                    function() {
                        if (!documentFileName) {
                            return;
                        }

                        documentFileName.textContent =
                            documentFileInput.files.length > 0
                                ? documentFileInput.files[0].name
                                : 'Select a file';
                    }
                );
            }

            if (documentForm) {
                documentForm.addEventListener(
                    'submit',
                    function(event) {
                        if (!documentForm.checkValidity()) {
                            event.preventDefault();

                            documentForm.reportValidity();

                            return;
                        }

                        if (documentUploadButton) {
                            documentUploadButton.disabled = true;

                            documentUploadButton.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i>' +
                                ' Uploading...';
                        }
                    }
                );
            }

            documentDeleteForms.forEach(
                function(form) {
                    form.addEventListener(
                        'submit',
                        function(event) {
                            const confirmed = window.confirm(
                                'Delete this supplier document? The stored file will also be removed.'
                            );

                            if (!confirmed) {
                                event.preventDefault();

                                return;
                            }

                            const button = form.querySelector(
                                'button[type="submit"]'
                            );

                            if (button) {
                                button.disabled = true;

                                button.innerHTML =
                                    '<i class="fa-solid fa-spinner fa-spin"></i>' +
                                    ' Deleting...';
                            }
                        }
                    );
                }
            );

            const documentErrors = document.querySelector(
                '.supplier-document-errors'
            );

            if (documentErrors) {
                documentErrors.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Supplier Ratings
            |--------------------------------------------------------------------------
            */

            const ratingModal = document.getElementById(
                'supplierRatingModal'
            );

            const ratingForm = document.getElementById(
                'supplierRatingForm'
            );

            const ratingMethod = document.getElementById(
                'supplierRatingMethod'
            );

            const ratingId = document.getElementById(
                'supplierRatingId'
            );

            const ratingModalTitle = document.getElementById(
                'supplierRatingModalTitle'
            );

            const ratingSaveButton = document.getElementById(
                'supplierRatingSaveButton'
            );

            const ratingFields = {
                purchaseOrder: document.getElementById(
                    'rating_purchase_order_id'
                ),
                quality: document.getElementById(
                    'quality_rating'
                ),
                delivery: document.getElementById(
                    'delivery_rating'
                ),
                communication: document.getElementById(
                    'communication_rating'
                ),
                pricing: document.getElementById(
                    'pricing_rating'
                ),
                title: document.getElementById(
                    'supplier_rating_title'
                ),
                review: document.getElementById(
                    'supplier_rating_review'
                ),
                recommend: document.getElementById(
                    'supplier_rating_recommend'
                )
            };

            const ratingCreateUrl = ratingForm
                ? ratingForm.dataset.createUrl || ratingForm.action
                : '';

            const openRatingButtons = [
                document.getElementById(
                    'openSupplierRatingModal'
                ),
                document.getElementById(
                    'openFirstSupplierRatingModal'
                )
            ].filter(Boolean);

            const closeRatingButtons =
                document.querySelectorAll(
                    '[data-close-rating-modal]'
                );

            const editRatingButtons =
                document.querySelectorAll(
                    '[data-edit-rating]'
                );

            const ratingDeleteForms =
                document.querySelectorAll(
                    '.supplier-rating-delete-form'
                );

            function openRatingModal() {
                if (!ratingModal) {
                    return;
                }

                ratingModal.hidden = false;
                document.body.style.overflow = 'hidden';

                window.setTimeout(
                    function() {
                        ratingFields.quality?.focus();
                    },
                    50
                );
            }

            function closeRatingModal() {
                if (!ratingModal) {
                    return;
                }

                ratingModal.hidden = true;
                document.body.style.overflow = '';
            }

            function resetRatingForm() {
                if (!ratingForm) {
                    return;
                }

                ratingForm.reset();
                ratingForm.action = ratingCreateUrl;

                if (ratingMethod) {
                    ratingMethod.value = 'POST';
                }

                if (ratingId) {
                    ratingId.value = '';
                }

                if (ratingModalTitle) {
                    ratingModalTitle.textContent =
                        'Add Supplier Rating';
                }

                if (ratingSaveButton) {
                    ratingSaveButton.disabled = false;
                    ratingSaveButton.innerHTML =
                        '<i class="fa-solid fa-star"></i>' +
                        ' Save Rating';
                }
            }

            openRatingButtons.forEach(
                function(button) {
                    button.addEventListener(
                        'click',
                        function() {
                            resetRatingForm();
                            openRatingModal();
                        }
                    );
                }
            );

            closeRatingButtons.forEach(
                function(button) {
                    button.addEventListener(
                        'click',
                        closeRatingModal
                    );
                }
            );

            editRatingButtons.forEach(
                function(button) {
                    button.addEventListener(
                        'click',
                        function() {
                            if (!ratingForm || !ratingMethod) {
                                return;
                            }

                            resetRatingForm();

                            const updateUrl =
                                button.dataset.updateUrl || '';

                            if (updateUrl === '') {
                                window.alert(
                                    'The supplier rating update URL is missing.'
                                );

                                return;
                            }

                            ratingForm.action = updateUrl;
                            ratingMethod.value = 'PUT';

                            if (ratingId) {
                                ratingId.value =
                                    button.dataset.ratingId || '';
                            }

                            if (ratingModalTitle) {
                                ratingModalTitle.textContent =
                                    'Edit Supplier Rating';
                            }

                            setContactFieldValue(
                                ratingFields.purchaseOrder,
                                button.dataset.purchaseOrderId
                            );

                            setContactFieldValue(
                                ratingFields.quality,
                                button.dataset.quality
                            );

                            setContactFieldValue(
                                ratingFields.delivery,
                                button.dataset.delivery
                            );

                            setContactFieldValue(
                                ratingFields.communication,
                                button.dataset.communication
                            );

                            setContactFieldValue(
                                ratingFields.pricing,
                                button.dataset.pricing
                            );

                            setContactFieldValue(
                                ratingFields.title,
                                button.dataset.title
                            );

                            setContactFieldValue(
                                ratingFields.review,
                                button.dataset.review
                            );

                            if (ratingFields.recommend) {
                                ratingFields.recommend.checked =
                                    button.dataset.recommend === '1';
                            }

                            openRatingModal();
                        }
                    );
                }
            );

            if (ratingForm) {
                ratingForm.addEventListener(
                    'submit',
                    function(event) {
                        if (!ratingForm.checkValidity()) {
                            event.preventDefault();
                            ratingForm.reportValidity();

                            return;
                        }

                        if (ratingSaveButton) {
                            ratingSaveButton.disabled = true;
                            ratingSaveButton.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i>' +
                                ' Saving...';
                        }
                    }
                );
            }

            ratingDeleteForms.forEach(
                function(form) {
                    form.addEventListener(
                        'submit',
                        function(event) {
                            const confirmed = window.confirm(
                                'Delete this supplier performance rating?'
                            );

                            if (!confirmed) {
                                event.preventDefault();

                                return;
                            }

                            const button = form.querySelector(
                                'button[type="submit"]'
                            );

                            if (button) {
                                button.disabled = true;
                                button.innerHTML =
                                    '<i class="fa-solid fa-spinner fa-spin"></i>' +
                                    ' Deleting...';
                            }
                        }
                    );
                }
            );

            if (
                ratingForm &&
                ratingForm.dataset.hasErrors === '1'
            ) {
                const editingRatingId =
                    ratingForm.dataset.editingRatingId || '';

                if (editingRatingId !== '') {
                    const editButton = Array.from(
                        editRatingButtons
                    ).find(
                        function(button) {
                            return button.dataset.ratingId ===
                                editingRatingId;
                        }
                    );

                    if (editButton && ratingMethod) {
                        ratingForm.action =
                            editButton.dataset.updateUrl ||
                            ratingCreateUrl;

                        ratingMethod.value = 'PUT';

                        if (ratingModalTitle) {
                            ratingModalTitle.textContent =
                                'Edit Supplier Rating';
                        }
                    }
                } else {
                    ratingForm.action = ratingCreateUrl;

                    if (ratingMethod) {
                        ratingMethod.value = 'POST';
                    }
                }

                openRatingModal();
            }

            /*
            |--------------------------------------------------------------------------
            | Supplier Purchase-Order Email Delivery
            |--------------------------------------------------------------------------
            */

            const poEmailModal = document.getElementById(
                'supplierPurchaseOrderEmailModal'
            );

            const poEmailForm = document.getElementById(
                'supplierPurchaseOrderEmailForm'
            );

            const poEmailPurchaseOrderId = document.getElementById(
                'supplierEmailPurchaseOrderId'
            );

            const poEmailReference = document.getElementById(
                'supplierEmailPurchaseOrderReference'
            );

            const poEmailSubject = document.getElementById(
                'supplier_po_email_subject'
            );

            const poEmailMessage = document.getElementById(
                'supplier_po_email_message'
            );

            const poEmailSubmitButton = document.getElementById(
                'supplierPurchaseOrderEmailButton'
            );

            const poEmailOpenButtons =
                document.querySelectorAll(
                    '[data-email-purchase-order]'
                );

            const poEmailCloseButtons =
                document.querySelectorAll(
                    '[data-close-po-email-modal]'
                );

            const poEmailRecipientInputs = poEmailForm
                ? poEmailForm.querySelectorAll(
                    'input[name="recipient_contact_ids[]"], ' +
                    'input[name="include_supplier_email"]'
                )
                : [];

            const poEmailAttachPdf = document.getElementById(
                'supplier_po_attach_pdf'
            );

            function openPoEmailModal() {
                if (!poEmailModal) {
                    return;
                }

                poEmailModal.hidden = false;
                document.body.style.overflow = 'hidden';

                window.setTimeout(
                    function() {
                        poEmailSubject?.focus();
                    },
                    50
                );
            }

            function closePoEmailModal() {
                if (!poEmailModal) {
                    return;
                }

                poEmailModal.hidden = true;
                document.body.style.overflow = '';
            }

            function resetPoEmailForm() {
                if (!poEmailForm) {
                    return;
                }

                poEmailForm.reset();

                poEmailRecipientInputs.forEach(
                    function(input) {
                        input.checked =
                            input.dataset.defaultChecked === '1';
                    }
                );

                if (poEmailAttachPdf) {
                    poEmailAttachPdf.checked =
                        poEmailAttachPdf.dataset.defaultChecked === '1';
                }

                if (poEmailPurchaseOrderId) {
                    poEmailPurchaseOrderId.value = '';
                }

                if (poEmailReference) {
                    poEmailReference.textContent =
                        'Select purchase order';
                }

                if (poEmailSubject) {
                    poEmailSubject.value = '';
                }

                if (poEmailMessage) {
                    poEmailMessage.value = '';
                }

                if (poEmailSubmitButton) {
                    poEmailSubmitButton.disabled = false;
                    poEmailSubmitButton.innerHTML =
                        '<i class="fa-regular fa-paper-plane"></i>' +
                        ' Queue Email';
                }
            }

            poEmailOpenButtons.forEach(
                function(button) {
                    button.addEventListener(
                        'click',
                        function() {
                            if (!poEmailForm) {
                                return;
                            }

                            resetPoEmailForm();

                            const emailUrl =
                                button.dataset.emailUrl || '';

                            if (emailUrl === '') {
                                window.alert(
                                    'The purchase-order email URL is missing.'
                                );

                                return;
                            }

                            const reference =
                                button.dataset.reference || '';

                            poEmailForm.action = emailUrl;

                            if (poEmailPurchaseOrderId) {
                                poEmailPurchaseOrderId.value =
                                    button.dataset.purchaseOrderId || '';
                            }

                            if (poEmailReference) {
                                poEmailReference.textContent =
                                    reference || 'Purchase order';
                            }

                            if (poEmailSubject) {
                                const appName =
                                    poEmailForm.dataset.appName || '';

                                poEmailSubject.value =
                                    'Purchase Order ' +
                                    reference +
                                    (appName !== ''
                                        ? ' from ' + appName
                                        : '');
                            }

                            openPoEmailModal();
                        }
                    );
                }
            );

            poEmailCloseButtons.forEach(
                function(button) {
                    button.addEventListener(
                        'click',
                        closePoEmailModal
                    );
                }
            );

            if (poEmailForm) {
                poEmailForm.addEventListener(
                    'submit',
                    function(event) {
                        const hasRecipient = Array.from(
                            poEmailRecipientInputs
                        ).some(
                            function(input) {
                                return input.checked;
                            }
                        );

                        if (!hasRecipient) {
                            event.preventDefault();

                            window.alert(
                                'Select at least one email recipient.'
                            );

                            return;
                        }

                        if (!poEmailForm.checkValidity()) {
                            event.preventDefault();
                            poEmailForm.reportValidity();

                            return;
                        }

                        if (poEmailSubmitButton) {
                            poEmailSubmitButton.disabled = true;
                            poEmailSubmitButton.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i>' +
                                ' Queueing...';
                        }
                    }
                );
            }

            if (
                poEmailForm &&
                poEmailForm.dataset.hasErrors === '1'
            ) {
                const purchaseOrderId =
                    poEmailForm.dataset.purchaseOrderId || '';

                const emailButton = Array.from(
                    poEmailOpenButtons
                ).find(
                    function(button) {
                        return button.dataset.purchaseOrderId ===
                            purchaseOrderId;
                    }
                );

                if (emailButton) {
                    poEmailForm.action =
                        emailButton.dataset.emailUrl ||
                        poEmailForm.action;

                    if (poEmailReference) {
                        poEmailReference.textContent =
                            emailButton.dataset.reference ||
                            'Purchase order';
                    }
                }

                openPoEmailModal();
            }

            /*
            |--------------------------------------------------------------------------
            | Escape Key
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'keydown',
                function(event) {
                    if (
                        event.key === 'Escape' &&
                        contactModal &&
                        !contactModal.hidden
                    ) {
                        closeContactModal();
                    }

                    if (
                        event.key === 'Escape' &&
                        ratingModal &&
                        !ratingModal.hidden
                    ) {
                        closeRatingModal();
                    }

                    if (
                        event.key === 'Escape' &&
                        poEmailModal &&
                        !poEmailModal.hidden
                    ) {
                        closePoEmailModal();
                    }
                }
            );
        }
    );
</script>
@endpush
