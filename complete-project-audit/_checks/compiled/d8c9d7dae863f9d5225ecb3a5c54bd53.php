<?php
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
?>

<div class="supplier-form-layout">

    
    <div class="supplier-form-main">

        
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
                        value="<?php echo e(old(
                            'company_name',
                            $currentSupplier?->company_name
                        )); ?>"
                        placeholder="Enter supplier company name"
                        maxlength="255"
                        required
                        autofocus>

                    <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                            value="<?php echo e(old(
                                'supplier_code',
                                $currentSupplier?->supplier_code
                            )); ?>"
                            placeholder="Automatically generated"
                            maxlength="60">

                    </div>

                    <small class="supplier-field-help">
                        Leave empty to generate the code automatically.
                    </small>

                    <?php $__errorArgs = ['supplier_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                            value="<?php echo e(old(
                                'contact_person',
                                $currentSupplier?->contact_person
                            )); ?>"
                            placeholder="Contact person name"
                            maxlength="255">

                    </div>

                    <?php $__errorArgs = ['contact_person'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                            value="<?php echo e(old(
                                'email',
                                $currentSupplier?->email
                            )); ?>"
                            placeholder="supplier@example.com"
                            maxlength="255">

                    </div>

                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                            value="<?php echo e(old(
                                'phone',
                                $currentSupplier?->phone
                            )); ?>"
                            placeholder="Primary phone number"
                            maxlength="50">

                    </div>

                    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                            value="<?php echo e(old(
                                'alternate_phone',
                                $currentSupplier?->alternate_phone
                            )); ?>"
                            placeholder="Secondary phone number"
                            maxlength="50">

                    </div>

                    <?php $__errorArgs = ['alternate_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                            value="<?php echo e(old(
                                'website',
                                $currentSupplier?->website
                            )); ?>"
                            placeholder="https://example.com"
                            maxlength="255">

                    </div>

                    <?php $__errorArgs = ['website'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

            </div>

        </section>

        
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
                        value="<?php echo e(old(
                            'address_line_1',
                            $currentSupplier?->address_line_1
                        )); ?>"
                        placeholder="Building, street or business premises"
                        maxlength="255">

                    <?php $__errorArgs = ['address_line_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field supplier-form-field-wide">

                    <label for="address_line_2">
                        Address Line 2
                    </label>

                    <input
                        type="text"
                        id="address_line_2"
                        name="address_line_2"
                        value="<?php echo e(old(
                            'address_line_2',
                            $currentSupplier?->address_line_2
                        )); ?>"
                        placeholder="Suite, unit, area or additional details"
                        maxlength="255">

                    <?php $__errorArgs = ['address_line_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field">

                    <label for="city">
                        City
                    </label>

                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="<?php echo e(old(
                            'city',
                            $currentSupplier?->city
                        )); ?>"
                        placeholder="City"
                        maxlength="120">

                    <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field">

                    <label for="state">
                        State / Region
                    </label>

                    <input
                        type="text"
                        id="state"
                        name="state"
                        value="<?php echo e(old(
                            'state',
                            $currentSupplier?->state
                        )); ?>"
                        placeholder="State, region or province"
                        maxlength="120">

                    <?php $__errorArgs = ['state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field">

                    <label for="postal_code">
                        Postal Code
                    </label>

                    <input
                        type="text"
                        id="postal_code"
                        name="postal_code"
                        value="<?php echo e(old(
                            'postal_code',
                            $currentSupplier?->postal_code
                        )); ?>"
                        placeholder="Postal or ZIP code"
                        maxlength="40">

                    <?php $__errorArgs = ['postal_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                            value="<?php echo e(old(
                                'country',
                                $currentSupplier?->country
                            )); ?>"
                            placeholder="Country"
                            maxlength="120">

                    </div>

                    <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

            </div>

        </section>

        
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

                        <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <option
                                value="<?php echo e($value); ?>"
                                <?php if(
                                    $selectedCurrency === $value
                                ): echo 'selected'; endif; ?>>

                                <?php echo e($label); ?>


                            </option>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </select>

                    <?php $__errorArgs = ['currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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

                        <?php $__currentLoopData = $paymentTerms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <option
                                value="<?php echo e($value); ?>"
                                <?php if(
                                    $selectedPaymentTerms === $value
                                ): echo 'selected'; endif; ?>>

                                <?php echo e($label); ?>


                            </option>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </select>

                    <?php $__errorArgs = ['payment_terms'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                            value="<?php echo e(old(
                                'lead_time_days',
                                $currentSupplier?->lead_time_days
                            )); ?>"
                            placeholder="0"
                            min="0"
                            max="3650"
                            step="1">

                        <span>
                            days
                        </span>

                    </div>

                    <?php $__errorArgs = ['lead_time_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                            value="<?php echo e(old(
                                'credit_limit',
                                $currentSupplier?->credit_limit
                            )); ?>"
                            placeholder="0.00"
                            min="0"
                            max="999999999999.99"
                            step="0.01">

                    </div>

                    <?php $__errorArgs = ['credit_limit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field">

                    <label for="tax_number">
                        Tax Number
                    </label>

                    <input
                        type="text"
                        id="tax_number"
                        name="tax_number"
                        value="<?php echo e(old(
                            'tax_number',
                            $currentSupplier?->tax_number
                        )); ?>"
                        placeholder="VAT, NTN or tax reference"
                        maxlength="120">

                    <?php $__errorArgs = ['tax_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field">

                    <label for="registration_number">
                        Registration Number
                    </label>

                    <input
                        type="text"
                        id="registration_number"
                        name="registration_number"
                        value="<?php echo e(old(
                            'registration_number',
                            $currentSupplier?->registration_number
                        )); ?>"
                        placeholder="Company registration number"
                        maxlength="120">

                    <?php $__errorArgs = ['registration_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

            </div>

        </section>

        
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
                        value="<?php echo e(old(
                            'bank_name',
                            $currentSupplier?->bank_name
                        )); ?>"
                        placeholder="Bank name"
                        maxlength="255">

                    <?php $__errorArgs = ['bank_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field">

                    <label for="account_name">
                        Account Name
                    </label>

                    <input
                        type="text"
                        id="account_name"
                        name="account_name"
                        value="<?php echo e(old(
                            'account_name',
                            $currentSupplier?->account_name
                        )); ?>"
                        placeholder="Account holder name"
                        maxlength="255">

                    <?php $__errorArgs = ['account_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field">

                    <label for="account_number">
                        Account Number
                    </label>

                    <input
                        type="text"
                        id="account_number"
                        name="account_number"
                        value="<?php echo e(old(
                            'account_number',
                            $currentSupplier?->account_number
                        )); ?>"
                        placeholder="Account number"
                        maxlength="120">

                    <?php $__errorArgs = ['account_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field">

                    <label for="sort_code">
                        Sort Code
                    </label>

                    <input
                        type="text"
                        id="sort_code"
                        name="sort_code"
                        value="<?php echo e(old(
                            'sort_code',
                            $currentSupplier?->sort_code
                        )); ?>"
                        placeholder="Sort or branch code"
                        maxlength="60">

                    <?php $__errorArgs = ['sort_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field">

                    <label for="iban">
                        IBAN
                    </label>

                    <input
                        type="text"
                        id="iban"
                        name="iban"
                        value="<?php echo e(old(
                            'iban',
                            $currentSupplier?->iban
                        )); ?>"
                        placeholder="International bank account number"
                        maxlength="120">

                    <?php $__errorArgs = ['iban'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

                <div class="supplier-form-field">

                    <label for="swift_code">
                        SWIFT / BIC
                    </label>

                    <input
                        type="text"
                        id="swift_code"
                        name="swift_code"
                        value="<?php echo e(old(
                            'swift_code',
                            $currentSupplier?->swift_code
                        )); ?>"
                        placeholder="SWIFT or BIC code"
                        maxlength="60">

                    <?php $__errorArgs = ['swift_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

            </div>

        </section>

        
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
                        placeholder="General supplier notes, delivery requirements or commercial information"><?php echo e(old(
                            'notes',
                            $currentSupplier?->notes
                        )); ?></textarea>

                    <div class="supplier-character-count">

                        <span id="supplierNotesCount">
                            0
                        </span>

                        / 5000 characters

                    </div>

                    <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
                        placeholder="Private notes visible only to administrators"><?php echo e(old(
                            'internal_notes',
                            $currentSupplier?->internal_notes
                        )); ?></textarea>

                    <div class="supplier-character-count">

                        <span id="supplierInternalNotesCount">
                            0
                        </span>

                        / 5000 characters

                    </div>

                    <?php $__errorArgs = ['internal_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                        <small class="supplier-field-error">
                            <?php echo e($message); ?>

                        </small>

                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                </div>

            </div>

        </section>

    </div>

    
    <aside class="supplier-form-sidebar">

        
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

                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <option
                            value="<?php echo e($value); ?>"
                            <?php if(
                                $selectedStatus === $value
                            ): echo 'selected'; endif; ?>>

                            <?php echo e($label); ?>


                        </option>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </select>

                <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                    <small class="supplier-field-error">
                        <?php echo e($message); ?>

                    </small>

                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            </div>

            <div
                class="supplier-status-preview <?php echo e($selectedStatus); ?>"
                id="supplierStatusPreview">

                <span class="supplier-status-preview-dot"></span>

                <div>

                    <strong id="supplierStatusPreviewLabel">

                        <?php echo e($statuses[$selectedStatus]
                            ?? 'Active'); ?>


                    </strong>

                    <small id="supplierStatusPreviewDescription">

                        <?php if($selectedStatus === 'blocked'): ?>

                            Purchase orders should not be created for this supplier.

                        <?php elseif($selectedStatus === 'inactive'): ?>

                            The supplier remains saved but is not currently active.

                        <?php else: ?>

                            The supplier can be used for new purchase orders.

                        <?php endif; ?>

                    </small>

                </div>

            </div>

        </section>

        
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
                    <?php if($isPreferred): echo 'checked'; endif; ?>>

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

                        <?php echo e(old(
                                'supplier_code',
                                $currentSupplier?->supplier_code
                            )
                            ?: 'Generated on save'); ?>


                    </strong>

                </div>

                <div>

                    <span>
                        Record Type
                    </span>

                    <strong>

                        <?php echo e($currentSupplier
                                ? 'Existing supplier'
                                : 'New supplier'); ?>


                    </strong>

                </div>

                <?php if($currentSupplier): ?>

                    <div>

                        <span>
                            Created
                        </span>

                        <strong>
                            <?php echo e(optional(
                                    $currentSupplier->created_at
                                )->format('d M Y H:i')
                                ?: 'Unknown'); ?>

                        </strong>

                    </div>

                    <div>

                        <span>
                            Last Updated
                        </span>

                        <strong>
                            <?php echo e(optional(
                                    $currentSupplier->updated_at
                                )->format('d M Y H:i')
                                ?: 'Unknown'); ?>

                        </strong>

                    </div>

                <?php endif; ?>

            </div>

        </section>

        
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

                <?php echo e($submitLabel); ?>


            </button>

            <a
                href="<?php echo e($currentSupplier
                        ? route(
                            'admin.suppliers.show',
                            $currentSupplier
                        )
                        : route(
                            'admin.suppliers.index'
                        )); ?>"
                class="supplier-cancel-button">

                <i class="fa-solid fa-xmark"></i>

                Cancel

            </a>

        </section>

    </aside>

</div><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/suppliers/form.blade.php ENDPATH**/ ?>