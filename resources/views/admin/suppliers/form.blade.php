@php
    $currentSupplier = $supplier ?? null;

    $selectedStatus = old(
        'status',
        $currentSupplier?->status ?? 'active'
    );

    $selectedCurrency = old(
        'currency',
        $currentSupplier?->currency ?? 'GBP'
    );

    $selectedPaymentTerms = old(
        'payment_terms',
        $currentSupplier?->payment_terms
    );

    $isPreferred = (bool) old(
        'is_preferred',
        $currentSupplier?->is_preferred ?? false
    );
@endphp

<div class="supplier-form-layout">

    {{-- Main Form Column --}}
    <div class="supplier-form-main">

        {{-- Company Information --}}
        <section class="supplier-form-card">

            <div class="supplier-form-card-header">

                <span class="supplier-form-card-icon company">

                    <i class="fa-solid fa-building"></i>

                </span>

                <div>

                    <span class="supplier-form-eyebrow">
                        Supplier identity
                    </span>

                    <h2>
                        Company Information
                    </h2>

                    <p>
                        Enter the supplier’s company and main contact details.
                    </p>

                </div>

            </div>

            <div class="supplier-form-grid">

                <div class="supplier-form-field supplier-form-field-wide">

                    <label for="company_name">

                        Company Name

                        <span class="required-mark">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        id="company_name"
                        name="company_name"
                        value="{{ old(
                            'company_name',
                            $currentSupplier?->company_name
                        ) }}"
                        placeholder="Enter supplier company name"
                        maxlength="255"
                        required
                        autofocus>

                    @error('company_name')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="supplier_code">
                        Supplier Code
                    </label>

                    <div class="supplier-input-with-icon">

                        <i class="fa-solid fa-barcode"></i>

                        <input
                            type="text"
                            id="supplier_code"
                            name="supplier_code"
                            value="{{ old(
                                'supplier_code',
                                $currentSupplier?->supplier_code
                            ) }}"
                            placeholder="Automatically generated"
                            maxlength="60">

                    </div>

                    <small class="supplier-field-help">
                        Leave empty to generate the code automatically.
                    </small>

                    @error('supplier_code')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="contact_person">
                        Main Contact Person
                    </label>

                    <div class="supplier-input-with-icon">

                        <i class="fa-regular fa-user"></i>

                        <input
                            type="text"
                            id="contact_person"
                            name="contact_person"
                            value="{{ old(
                                'contact_person',
                                $currentSupplier?->contact_person
                            ) }}"
                            placeholder="Contact person name"
                            maxlength="255">

                    </div>

                    @error('contact_person')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="email">
                        Email Address
                    </label>

                    <div class="supplier-input-with-icon">

                        <i class="fa-regular fa-envelope"></i>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old(
                                'email',
                                $currentSupplier?->email
                            ) }}"
                            placeholder="supplier@example.com"
                            maxlength="255">

                    </div>

                    @error('email')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="phone">
                        Phone Number
                    </label>

                    <div class="supplier-input-with-icon">

                        <i class="fa-solid fa-phone"></i>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old(
                                'phone',
                                $currentSupplier?->phone
                            ) }}"
                            placeholder="Primary phone number"
                            maxlength="50">

                    </div>

                    @error('phone')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="alternate_phone">
                        Alternate Phone
                    </label>

                    <div class="supplier-input-with-icon">

                        <i class="fa-solid fa-mobile-screen-button"></i>

                        <input
                            type="text"
                            id="alternate_phone"
                            name="alternate_phone"
                            value="{{ old(
                                'alternate_phone',
                                $currentSupplier?->alternate_phone
                            ) }}"
                            placeholder="Secondary phone number"
                            maxlength="50">

                    </div>

                    @error('alternate_phone')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field supplier-form-field-wide">

                    <label for="website">
                        Website
                    </label>

                    <div class="supplier-input-with-icon">

                        <i class="fa-solid fa-globe"></i>

                        <input
                            type="url"
                            id="website"
                            name="website"
                            value="{{ old(
                                'website',
                                $currentSupplier?->website
                            ) }}"
                            placeholder="https://example.com"
                            maxlength="255">

                    </div>

                    @error('website')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

            </div>

        </section>

        {{-- Address Information --}}
        <section class="supplier-form-card">

            <div class="supplier-form-card-header">

                <span class="supplier-form-card-icon address">

                    <i class="fa-solid fa-location-dot"></i>

                </span>

                <div>

                    <span class="supplier-form-eyebrow">
                        Business location
                    </span>

                    <h2>
                        Address Information
                    </h2>

                    <p>
                        Add the supplier’s registered or operational address.
                    </p>

                </div>

            </div>

            <div class="supplier-form-grid">

                <div class="supplier-form-field supplier-form-field-wide">

                    <label for="address_line_1">
                        Address Line 1
                    </label>

                    <input
                        type="text"
                        id="address_line_1"
                        name="address_line_1"
                        value="{{ old(
                            'address_line_1',
                            $currentSupplier?->address_line_1
                        ) }}"
                        placeholder="Building, street or business premises"
                        maxlength="255">

                    @error('address_line_1')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field supplier-form-field-wide">

                    <label for="address_line_2">
                        Address Line 2
                    </label>

                    <input
                        type="text"
                        id="address_line_2"
                        name="address_line_2"
                        value="{{ old(
                            'address_line_2',
                            $currentSupplier?->address_line_2
                        ) }}"
                        placeholder="Suite, unit, area or additional details"
                        maxlength="255">

                    @error('address_line_2')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="city">
                        City
                    </label>

                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="{{ old(
                            'city',
                            $currentSupplier?->city
                        ) }}"
                        placeholder="City"
                        maxlength="120">

                    @error('city')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="state">
                        State / Region
                    </label>

                    <input
                        type="text"
                        id="state"
                        name="state"
                        value="{{ old(
                            'state',
                            $currentSupplier?->state
                        ) }}"
                        placeholder="State, region or province"
                        maxlength="120">

                    @error('state')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="postal_code">
                        Postal Code
                    </label>

                    <input
                        type="text"
                        id="postal_code"
                        name="postal_code"
                        value="{{ old(
                            'postal_code',
                            $currentSupplier?->postal_code
                        ) }}"
                        placeholder="Postal or ZIP code"
                        maxlength="40">

                    @error('postal_code')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="country">
                        Country
                    </label>

                    <div class="supplier-input-with-icon">

                        <i class="fa-solid fa-earth-americas"></i>

                        <input
                            type="text"
                            id="country"
                            name="country"
                            value="{{ old(
                                'country',
                                $currentSupplier?->country
                            ) }}"
                            placeholder="Country"
                            maxlength="120">

                    </div>

                    @error('country')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

            </div>

        </section>

        {{-- Commercial Information --}}
        <section class="supplier-form-card">

            <div class="supplier-form-card-header">

                <span class="supplier-form-card-icon commercial">

                    <i class="fa-solid fa-handshake"></i>

                </span>

                <div>

                    <span class="supplier-form-eyebrow">
                        Purchasing terms
                    </span>

                    <h2>
                        Commercial Information
                    </h2>

                    <p>
                        Configure payment terms, lead time and account limits.
                    </p>

                </div>

            </div>

            <div class="supplier-form-grid">

                <div class="supplier-form-field">

                    <label for="currency">

                        Currency

                        <span class="required-mark">
                            *
                        </span>

                    </label>

                    <select
                        id="currency"
                        name="currency"
                        required>

                        @foreach ($currencies as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected(
                                    $selectedCurrency === $value
                                )>

                                {{ $label }}

                            </option>

                        @endforeach

                    </select>

                    @error('currency')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="payment_terms">
                        Payment Terms
                    </label>

                    <select
                        id="payment_terms"
                        name="payment_terms">

                        <option value="">
                            Select payment terms
                        </option>

                        @foreach ($paymentTerms as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected(
                                    $selectedPaymentTerms === $value
                                )>

                                {{ $label }}

                            </option>

                        @endforeach

                    </select>

                    @error('payment_terms')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="lead_time_days">
                        Lead Time
                    </label>

                    <div class="supplier-input-suffix">

                        <input
                            type="number"
                            id="lead_time_days"
                            name="lead_time_days"
                            value="{{ old(
                                'lead_time_days',
                                $currentSupplier?->lead_time_days
                            ) }}"
                            placeholder="0"
                            min="0"
                            max="3650"
                            step="1">

                        <span>
                            days
                        </span>

                    </div>

                    @error('lead_time_days')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="credit_limit">
                        Credit Limit
                    </label>

                    <div class="supplier-money-input">

                        <span id="supplierCurrencySymbol">
                            £
                        </span>

                        <input
                            type="number"
                            id="credit_limit"
                            name="credit_limit"
                            value="{{ old(
                                'credit_limit',
                                $currentSupplier?->credit_limit
                            ) }}"
                            placeholder="0.00"
                            min="0"
                            max="999999999999.99"
                            step="0.01">

                    </div>

                    @error('credit_limit')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="tax_number">
                        Tax Number
                    </label>

                    <input
                        type="text"
                        id="tax_number"
                        name="tax_number"
                        value="{{ old(
                            'tax_number',
                            $currentSupplier?->tax_number
                        ) }}"
                        placeholder="VAT, NTN or tax reference"
                        maxlength="120">

                    @error('tax_number')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="registration_number">
                        Registration Number
                    </label>

                    <input
                        type="text"
                        id="registration_number"
                        name="registration_number"
                        value="{{ old(
                            'registration_number',
                            $currentSupplier?->registration_number
                        ) }}"
                        placeholder="Company registration number"
                        maxlength="120">

                    @error('registration_number')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

            </div>

        </section>

        {{-- Banking Information --}}
        <section class="supplier-form-card">

            <div class="supplier-form-card-header">

                <span class="supplier-form-card-icon banking">

                    <i class="fa-solid fa-building-columns"></i>

                </span>

                <div>

                    <span class="supplier-form-eyebrow">
                        Payment account
                    </span>

                    <h2>
                        Banking Information
                    </h2>

                    <p>
                        Store supplier payment details for internal use.
                    </p>

                </div>

            </div>

            <div class="supplier-bank-warning">

                <i class="fa-solid fa-shield-halved"></i>

                <span>
                    Banking information should only be accessible to authorised
                    administrators.
                </span>

            </div>

            <div class="supplier-form-grid">

                <div class="supplier-form-field">

                    <label for="bank_name">
                        Bank Name
                    </label>

                    <input
                        type="text"
                        id="bank_name"
                        name="bank_name"
                        value="{{ old(
                            'bank_name',
                            $currentSupplier?->bank_name
                        ) }}"
                        placeholder="Bank name"
                        maxlength="255">

                    @error('bank_name')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="account_name">
                        Account Name
                    </label>

                    <input
                        type="text"
                        id="account_name"
                        name="account_name"
                        value="{{ old(
                            'account_name',
                            $currentSupplier?->account_name
                        ) }}"
                        placeholder="Account holder name"
                        maxlength="255">

                    @error('account_name')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="account_number">
                        Account Number
                    </label>

                    <input
                        type="text"
                        id="account_number"
                        name="account_number"
                        value="{{ old(
                            'account_number',
                            $currentSupplier?->account_number
                        ) }}"
                        placeholder="Account number"
                        maxlength="120">

                    @error('account_number')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="sort_code">
                        Sort Code
                    </label>

                    <input
                        type="text"
                        id="sort_code"
                        name="sort_code"
                        value="{{ old(
                            'sort_code',
                            $currentSupplier?->sort_code
                        ) }}"
                        placeholder="Sort or branch code"
                        maxlength="60">

                    @error('sort_code')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="iban">
                        IBAN
                    </label>

                    <input
                        type="text"
                        id="iban"
                        name="iban"
                        value="{{ old(
                            'iban',
                            $currentSupplier?->iban
                        ) }}"
                        placeholder="International bank account number"
                        maxlength="120">

                    @error('iban')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field">

                    <label for="swift_code">
                        SWIFT / BIC
                    </label>

                    <input
                        type="text"
                        id="swift_code"
                        name="swift_code"
                        value="{{ old(
                            'swift_code',
                            $currentSupplier?->swift_code
                        ) }}"
                        placeholder="SWIFT or BIC code"
                        maxlength="60">

                    @error('swift_code')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

            </div>

        </section>

        {{-- Notes --}}
        <section class="supplier-form-card">

            <div class="supplier-form-card-header">

                <span class="supplier-form-card-icon notes">

                    <i class="fa-regular fa-note-sticky"></i>

                </span>

                <div>

                    <span class="supplier-form-eyebrow">
                        Additional details
                    </span>

                    <h2>
                        Supplier Notes
                    </h2>

                    <p>
                        Add general notes and private administrator information.
                    </p>

                </div>

            </div>

            <div class="supplier-form-grid">

                <div class="supplier-form-field supplier-form-field-wide">

                    <label for="notes">
                        General Notes
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        rows="5"
                        placeholder="General supplier notes, delivery requirements or commercial information">{{ old(
                            'notes',
                            $currentSupplier?->notes
                        ) }}</textarea>

                    <div class="supplier-character-count">

                        <span id="supplierNotesCount">
                            0
                        </span>

                        / 5000 characters

                    </div>

                    @error('notes')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

                <div class="supplier-form-field supplier-form-field-wide">

                    <label for="internal_notes">

                        Internal Notes

                        <span class="private-label">

                            <i class="fa-solid fa-lock"></i>

                            Private

                        </span>

                    </label>

                    <textarea
                        id="internal_notes"
                        name="internal_notes"
                        rows="5"
                        placeholder="Private notes visible only to administrators">{{ old(
                            'internal_notes',
                            $currentSupplier?->internal_notes
                        ) }}</textarea>

                    <div class="supplier-character-count">

                        <span id="supplierInternalNotesCount">
                            0
                        </span>

                        / 5000 characters

                    </div>

                    @error('internal_notes')

                        <small class="supplier-field-error">
                            {{ $message }}
                        </small>

                    @enderror

                </div>

            </div>

        </section>

    </div>

    {{-- Right Sidebar --}}
    <aside class="supplier-form-sidebar">

        {{-- Status --}}
        <section class="supplier-sidebar-card">

            <div class="supplier-sidebar-card-header">

                <span class="supplier-sidebar-icon status">

                    <i class="fa-solid fa-toggle-on"></i>

                </span>

                <div>

                    <h3>
                        Supplier Status
                    </h3>

                    <p>
                        Control purchasing availability.
                    </p>

                </div>

            </div>

            <div class="supplier-form-field">

                <label for="status">

                    Status

                    <span class="required-mark">
                        *
                    </span>

                </label>

                <select
                    id="status"
                    name="status"
                    required>

                    @foreach ($statuses as $value => $label)

                        <option
                            value="{{ $value }}"
                            @selected(
                                $selectedStatus === $value
                            )>

                            {{ $label }}

                        </option>

                    @endforeach

                </select>

                @error('status')

                    <small class="supplier-field-error">
                        {{ $message }}
                    </small>

                @enderror

            </div>

            <div
                class="supplier-status-preview {{ $selectedStatus }}"
                id="supplierStatusPreview">

                <span class="supplier-status-preview-dot"></span>

                <div>

                    <strong id="supplierStatusPreviewLabel">

                        {{
                            $statuses[$selectedStatus]
                            ?? 'Active'
                        }}

                    </strong>

                    <small id="supplierStatusPreviewDescription">

                        @if ($selectedStatus === 'blocked')

                            Purchase orders should not be created for this supplier.

                        @elseif ($selectedStatus === 'inactive')

                            The supplier remains saved but is not currently active.

                        @else

                            The supplier can be used for new purchase orders.

                        @endif

                    </small>

                </div>

            </div>

        </section>

        {{-- Preferred --}}
        <section class="supplier-sidebar-card">

            <div class="supplier-sidebar-card-header">

                <span class="supplier-sidebar-icon preferred">

                    <i class="fa-solid fa-star"></i>

                </span>

                <div>

                    <h3>
                        Preferred Supplier
                    </h3>

                    <p>
                        Highlight trusted purchasing partners.
                    </p>

                </div>

            </div>

            <label class="supplier-toggle-card">

                <input
                    type="checkbox"
                    id="is_preferred"
                    name="is_preferred"
                    value="1"
                    @checked($isPreferred)>

                <span class="supplier-toggle-switch"></span>

                <span class="supplier-toggle-content">

                    <strong>
                        Mark as preferred
                    </strong>

                    <small>
                        Preferred suppliers are highlighted throughout the
                        purchasing system.
                    </small>

                </span>

            </label>

        </section>

        {{-- Form Information --}}
        <section class="supplier-sidebar-card">

            <div class="supplier-sidebar-card-header">

                <span class="supplier-sidebar-icon information">

                    <i class="fa-solid fa-circle-info"></i>

                </span>

                <div>

                    <h3>
                        Record Information
                    </h3>

                    <p>
                        Current supplier record details.
                    </p>

                </div>

            </div>

            <div class="supplier-record-information">

                <div>

                    <span>
                        Supplier Code
                    </span>

                    <strong id="supplierCodePreview">

                        {{
                            old(
                                'supplier_code',
                                $currentSupplier?->supplier_code
                            )
                            ?: 'Generated on save'
                        }}

                    </strong>

                </div>

                <div>

                    <span>
                        Record Type
                    </span>

                    <strong>

                        {{
                            $currentSupplier
                                ? 'Existing supplier'
                                : 'New supplier'
                        }}

                    </strong>

                </div>

                @if ($currentSupplier)

                    <div>

                        <span>
                            Created
                        </span>

                        <strong>
                            {{
                                optional(
                                    $currentSupplier->created_at
                                )->format('d M Y H:i')
                                ?: 'Unknown'
                            }}
                        </strong>

                    </div>

                    <div>

                        <span>
                            Last Updated
                        </span>

                        <strong>
                            {{
                                optional(
                                    $currentSupplier->updated_at
                                )->format('d M Y H:i')
                                ?: 'Unknown'
                            }}
                        </strong>

                    </div>

                @endif

            </div>

        </section>

        {{-- Submit --}}
        <section class="supplier-sidebar-card supplier-submit-card">

            <div class="supplier-submit-summary">

                <i class="fa-solid fa-floppy-disk"></i>

                <div>

                    <strong>
                        Save Supplier
                    </strong>

                    <span>
                        Review the information before submitting.
                    </span>

                </div>

            </div>

            <button
                type="submit"
                class="supplier-submit-button"
                id="supplierSubmitButton">

                <i class="fa-solid fa-floppy-disk"></i>

                {{ $submitLabel }}

            </button>

            <a
                href="{{
                    $currentSupplier
                        ? route(
                            'admin.suppliers.show',
                            $currentSupplier
                        )
                        : route(
                            'admin.suppliers.index'
                        )
                }}"
                class="supplier-cancel-button">

                <i class="fa-solid fa-xmark"></i>

                Cancel

            </a>

        </section>

    </aside>

</div>