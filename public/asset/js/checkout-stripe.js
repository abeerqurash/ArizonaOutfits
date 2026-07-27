/**
 * Arizona Outfits Checkout Stripe Integration
 *
 * Stripe Payment Element appears automatically after:
 * 1. Stripe is selected.
 * 2. Required billing/shipping fields are completed.
 * 3. Terms are accepted.
 *
 * The customer clicks Pay Securely only once.
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const checkoutPage = document.querySelector('.checkout-page');
    const checkoutForm = document.getElementById('checkout-form');
    const submitButton = document.getElementById(
        'checkout-place-order-button'
    );

    const stripeContainer = document.getElementById(
        'stripe-payment-container'
    );

    const stripeElementContainer = document.getElementById(
        'stripe-payment-element'
    );

    const stripeErrorElement = document.getElementById(
        'stripe-payment-error'
    );

    const bankTransferDetails = document.getElementById(
        'bank-transfer-details'
    );

    if (
        !checkoutPage ||
        !checkoutForm ||
        !submitButton
    ) {
        return;
    }

    const stripeKey =
        checkoutPage.dataset.stripeKey || '';

    const stripeIntentUrl =
        checkoutPage.dataset.stripeIntentUrl || '';

    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';

    let stripe = null;
    let elements = null;
    let paymentElement = null;

    let clientSecret = '';
    let orderNumber = '';
    let returnUrl = '';

    let stripeReady = false;
    let requestInProgress = false;
    let automaticPreparationTimer = null;

    /*
    |--------------------------------------------------------------------------
    | Payment method helpers
    |--------------------------------------------------------------------------
    */

    function getSelectedPaymentMethod() {
        return (
            checkoutForm.querySelector(
                'input[name="payment_method"]:checked'
            )?.value || ''
        );
    }

    function isStripeSelected() {
        return getSelectedPaymentMethod() === 'stripe';
    }

    function isBankTransferSelected() {
        return (
            getSelectedPaymentMethod() ===
            'bank_transfer'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Error handling
    |--------------------------------------------------------------------------
    */

    function showStripeError(message) {
        if (!stripeErrorElement) {
            return;
        }

        stripeErrorElement.textContent =
            message ||
            'An unexpected payment error occurred.';

        stripeErrorElement.hidden = false;
    }

    function clearStripeError() {
        if (!stripeErrorElement) {
            return;
        }

        stripeErrorElement.textContent = '';
        stripeErrorElement.hidden = true;
    }

    function extractErrorMessage(
        payload,
        fallbackMessage
    ) {
        if (!payload) {
            return fallbackMessage;
        }

        if (
            typeof payload.message === 'string' &&
            payload.message.trim() !== ''
        ) {
            return payload.message;
        }

        if (
            payload.errors &&
            typeof payload.errors === 'object'
        ) {
            const messages = [];

            Object.values(
                payload.errors
            ).forEach(function (errorGroup) {
                if (Array.isArray(errorGroup)) {
                    errorGroup.forEach(
                        function (message) {
                            messages.push(message);
                        }
                    );
                } else if (
                    typeof errorGroup === 'string'
                ) {
                    messages.push(errorGroup);
                }
            });

            if (messages.length > 0) {
                return messages.join(' ');
            }
        }

        return fallbackMessage;
    }

    /*
    |--------------------------------------------------------------------------
    | Button state
    |--------------------------------------------------------------------------
    */

    function setButtonLoading(
        isLoading,
        loadingText = ''
    ) {
        requestInProgress = isLoading;
        submitButton.disabled = isLoading;

        if (!submitButton.dataset.originalText) {
            submitButton.dataset.originalText =
                submitButton.textContent.trim() ||
                'Place Order';
        }

        if (isLoading) {
            submitButton.setAttribute(
                'aria-busy',
                'true'
            );

            submitButton.innerHTML =
                '<span class="checkout-button-spinner" ' +
                'aria-hidden="true"></span>' +
                '<span>' +
                (
                    loadingText ||
                    'Processing...'
                ) +
                '</span>';

            return;
        }

        submitButton.removeAttribute(
            'aria-busy'
        );

        updateButtonText();
    }

    function updateButtonText() {
        if (requestInProgress) {
            return;
        }

        if (isStripeSelected()) {
            submitButton.textContent =
                stripeReady
                    ? 'Pay Securely'
                    : 'Complete Details to Pay';

            return;
        }

        if (isBankTransferSelected()) {
            submitButton.textContent =
                'Place Bank Transfer Order';

            return;
        }

        submitButton.textContent =
            submitButton.dataset.originalText ||
            'Place Order';
    }

    /*
    |--------------------------------------------------------------------------
    | Form helpers
    |--------------------------------------------------------------------------
    */

    function getFieldByName(name) {
        return checkoutForm.querySelector(
            '[name="' + name + '"]'
        );
    }

    function getInputValue(fieldId) {
        return (
            document
                .getElementById(fieldId)
                ?.value
                ?.trim() || ''
        );
    }

    function shipToDifferentAddress() {
        const checkbox =
            document.getElementById(
                'different-shipping'
            ) ||
            getFieldByName(
                'ship_to_different_address'
            );

        return Boolean(checkbox?.checked);
    }

    function markFirstInvalidField() {
        const invalidField =
            checkoutForm.querySelector(
                ':invalid'
            );

        if (!invalidField) {
            return;
        }

        invalidField.focus({
            preventScroll: true,
        });

        invalidField.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
        });
    }

    function validateCheckoutForm() {
        clearStripeError();

        if (!checkoutForm.checkValidity()) {
            checkoutForm.reportValidity();
            markFirstInvalidField();

            return false;
        }

        if (!getSelectedPaymentMethod()) {
            showStripeError(
                'Please select a payment method.'
            );

            return false;
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Check whether Stripe can be prepared
    |--------------------------------------------------------------------------
    */

    function fieldIsValid(fieldName) {
        const field =
            getFieldByName(fieldName);

        if (!field || field.disabled) {
            return false;
        }

        if (
            typeof field.checkValidity ===
            'function'
        ) {
            return (
                field.value.trim() !== '' &&
                field.checkValidity()
            );
        }

        return field.value.trim() !== '';
    }

    function canPrepareStripePayment() {
        if (!isStripeSelected()) {
            return false;
        }

        const requiredFields = [
            'billing_name',
            'billing_email',
            'billing_phone',
            'billing_address',
            'billing_country',
            'billing_state',
            'billing_city',
            'billing_zip',
        ];

        if (shipToDifferentAddress()) {
            requiredFields.push(
                'shipping_name',
                'shipping_phone',
                'shipping_address',
                'shipping_country',
                'shipping_state',
                'shipping_city',
                'shipping_zip'
            );
        }

        const allFieldsValid =
            requiredFields.every(
                function (fieldName) {
                    return fieldIsValid(
                        fieldName
                    );
                }
            );

        const termsField =
            getFieldByName('terms');

        const termsAccepted =
            Boolean(termsField?.checked);

        return (
            allFieldsValid &&
            termsAccepted
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payment method display
    |--------------------------------------------------------------------------
    */

    function updatePaymentMethodDisplay() {
        const selectedMethod =
            getSelectedPaymentMethod();

        if (stripeContainer) {
            stripeContainer.hidden =
                selectedMethod !== 'stripe';
        }

        if (bankTransferDetails) {
            bankTransferDetails.hidden =
                selectedMethod !==
                'bank_transfer';
        }

        clearStripeError();
        updateButtonText();

        if (selectedMethod === 'stripe') {
            scheduleAutomaticStripePreparation();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Stripe library
    |--------------------------------------------------------------------------
    */

    function initialiseStripeLibrary() {
        if (
            typeof window.Stripe !==
            'function'
        ) {
            showStripeError(
                'Stripe could not be loaded. Please refresh the page.'
            );

            return false;
        }

        if (!stripeKey) {
            showStripeError(
                'Stripe publishable key is missing.'
            );

            return false;
        }

        if (!stripeIntentUrl) {
            showStripeError(
                'Stripe payment endpoint is missing.'
            );

            return false;
        }

        if (!stripe) {
            stripe =
                window.Stripe(stripeKey);
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Automatic preparation
    |--------------------------------------------------------------------------
    */

    function scheduleAutomaticStripePreparation() {
        window.clearTimeout(
            automaticPreparationTimer
        );

        automaticPreparationTimer =
            window.setTimeout(
                function () {
                    prepareStripeAutomatically();
                },
                400
            );
    }

    async function prepareStripeAutomatically() {
        if (!isStripeSelected()) {
            return;
        }

        if (
            stripeReady ||
            clientSecret ||
            paymentElement ||
            requestInProgress
        ) {
            return;
        }

        if (!canPrepareStripePayment()) {
            updateButtonText();
            return;
        }

        await createPaymentIntent();
    }

    /*
    |--------------------------------------------------------------------------
    | Create PaymentIntent
    |--------------------------------------------------------------------------
    */

    async function createPaymentIntent() {
        if (requestInProgress) {
            return false;
        }

        if (
            clientSecret &&
            paymentElement
        ) {
            return true;
        }

        if (!initialiseStripeLibrary()) {
            return false;
        }

        clearStripeError();

        setButtonLoading(
            true,
            'Preparing secure payment...'
        );

        try {
            const formData =
                new FormData(checkoutForm);

            formData.set(
                'payment_method',
                'stripe'
            );

            const response = await fetch(
                stripeIntentUrl,
                {
                    method: 'POST',

                    headers: {
                        Accept:
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken,

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    body: formData,

                    credentials:
                        'same-origin',
                }
            );

            let payload;

            try {
                payload =
                    await response.json();
            } catch (error) {
                throw new Error(
                    'The server returned an invalid Stripe response.'
                );
            }

            if (!response.ok) {
                throw new Error(
                    extractErrorMessage(
                        payload,
                        'Unable to prepare your payment.'
                    )
                );
            }

            if (!payload.client_secret) {
                throw new Error(
                    'Stripe did not return a valid payment session.'
                );
            }

            clientSecret =
                payload.client_secret;

            orderNumber =
                payload.order_number || '';

            returnUrl =
                payload.return_url || '';

            await mountStripePaymentElement();

            return true;
        } catch (error) {
            console.error(
                'Stripe PaymentIntent error:',
                error
            );

            resetStripeSession();

            showStripeError(
                error.message ||
                'Unable to prepare your payment.'
            );

            return false;
        } finally {
            setButtonLoading(false);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Mount Stripe Payment Element
    |--------------------------------------------------------------------------
    */

    async function mountStripePaymentElement() {
        if (
            !stripe ||
            !clientSecret ||
            !stripeElementContainer
        ) {
            throw new Error(
                'The Stripe payment form could not be initialized.'
            );
        }

        if (paymentElement) {
            try {
                paymentElement.destroy();
            } catch (error) {
                console.warn(
                    'Previous Stripe element could not be destroyed.',
                    error
                );
            }
        }

        paymentElement = null;
        elements = null;
        stripeReady = false;

        stripeElementContainer.innerHTML =
            '';

        const appearance = {
            theme: 'stripe',

            variables: {
                borderRadius: '4px',
                spacingUnit: '4px',
                fontSizeBase: '16px',
            },

            rules: {
                '.Input': {
                    padding: '13px 14px',
                },

                '.Label': {
                    marginBottom: '8px',
                },

                '.Tab': {
                    padding: '12px',
                },
            },
        };

        elements = stripe.elements({
            clientSecret: clientSecret,
            appearance: appearance,
            loader: 'auto',
        });

        paymentElement =
            elements.create(
                'payment',
                {
                    layout: {
                        type: 'tabs',
                        defaultCollapsed: false,
                    },

                    wallets: {
                        applePay: 'auto',
                        googlePay: 'auto',
                    },

                    fields: {
                        billingDetails: {
                            name: 'never',
                            email: 'never',
                            phone: 'never',
                            address: 'never',
                        },
                    },

                    terms: {
                        card: 'never',
                    },
                }
            );

        paymentElement.on(
            'ready',
            function () {
                stripeReady = true;

                clearStripeError();
                updateButtonText();

                if (stripeContainer) {
                    stripeContainer.hidden =
                        false;
                }
            }
        );

        paymentElement.on(
            'change',
            function (event) {
                if (event.error) {
                    showStripeError(
                        event.error.message
                    );

                    return;
                }

                clearStripeError();
            }
        );

        paymentElement.on(
            'loaderror',
            function (event) {
                console.error(
                    'Stripe Payment Element load error:',
                    event
                );

                stripeReady = false;

                showStripeError(
                    event?.error?.message ||
                    'The secure payment form could not be loaded.'
                );

                updateButtonText();
            }
        );

        paymentElement.mount(
            '#stripe-payment-element'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Stripe address details
    |--------------------------------------------------------------------------
    */

    function getBillingAddress() {
        return {
            line1:
                getInputValue(
                    'billing_address'
                ),

            city:
                getInputValue(
                    'billing_city'
                ),

            state:
                getInputValue(
                    'billing_state'
                ),

            postal_code:
                getInputValue(
                    'billing_zip'
                ),

            country:
                getInputValue(
                    'billing_country'
                ),
        };
    }

    function getShippingDetails() {
        if (shipToDifferentAddress()) {
            return {
                name:
                    getInputValue(
                        'shipping_name'
                    ),

                phone:
                    getInputValue(
                        'shipping_phone'
                    ),

                address: {
                    line1:
                        getInputValue(
                            'shipping_address'
                        ),

                    city:
                        getInputValue(
                            'shipping_city'
                        ),

                    state:
                        getInputValue(
                            'shipping_state'
                        ),

                    postal_code:
                        getInputValue(
                            'shipping_zip'
                        ),

                    country:
                        getInputValue(
                            'shipping_country'
                        ),
                },
            };
        }

        return {
            name:
                getInputValue(
                    'billing_name'
                ),

            phone:
                getInputValue(
                    'billing_phone'
                ),

            address:
                getBillingAddress(),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Confirm Stripe payment
    |--------------------------------------------------------------------------
    */

    async function confirmStripePayment() {
        if (
            !stripe ||
            !elements ||
            !paymentElement ||
            !clientSecret ||
            !stripeReady
        ) {
            showStripeError(
                'The payment form is not ready yet.'
            );

            return;
        }

        clearStripeError();

        setButtonLoading(
            true,
            'Processing payment...'
        );

        try {
            /*
             * Validate the Payment Element before
             * confirming the payment.
             */
            const submitResult =
                await elements.submit();

            if (submitResult.error) {
                throw new Error(
                    submitResult.error.message ||
                    'Please complete your card details.'
                );
            }

            const billingDetails = {
                name:
                    getInputValue(
                        'billing_name'
                    ),

                email:
                    getInputValue(
                        'billing_email'
                    ),

                phone:
                    getInputValue(
                        'billing_phone'
                    ),

                address:
                    getBillingAddress(),
            };

            const shippingDetails =
                getShippingDetails();

            const confirmationReturnUrl =
                returnUrl ||
                (
                    window.location.origin +
                    '/stripe/return'
                );

            const result =
                await stripe.confirmPayment({
                    elements: elements,

                    clientSecret:
                        clientSecret,

                    confirmParams: {
                        return_url:
                            confirmationReturnUrl,

                        payment_method_data: {
                            billing_details:
                                billingDetails,
                        },

                        shipping:
                            shippingDetails,
                    },

                    redirect:
                        'if_required',
                });

            if (result.error) {
                throw new Error(
                    result.error.message ||
                    'Your payment could not be completed.'
                );
            }

            if (!result.paymentIntent) {
                window.location.assign(
                    confirmationReturnUrl
                );

                return;
            }

            handlePaymentIntentResult(
                result.paymentIntent,
                confirmationReturnUrl
            );
        } catch (error) {
            console.error(
                'Stripe confirmation error:',
                error
            );

            showStripeError(
                error.message ||
                'Your payment could not be completed.'
            );

            setButtonLoading(false);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PaymentIntent result
    |--------------------------------------------------------------------------
    */

    function handlePaymentIntentResult(
        paymentIntent,
        confirmationReturnUrl
    ) {
        const paymentStatus =
            paymentIntent?.status || '';

        const paymentIntentId =
            paymentIntent?.id || '';

        const url = new URL(
            confirmationReturnUrl,
            window.location.origin
        );

        if (paymentIntentId) {
            url.searchParams.set(
                'payment_intent',
                paymentIntentId
            );
        }

        if (orderNumber) {
            url.searchParams.set(
                'order_number',
                orderNumber
            );
        }

        switch (paymentStatus) {
            case 'succeeded':
            case 'processing':
            case 'requires_capture':
                window.location.assign(
                    url.toString()
                );
                return;

            case 'requires_action':
                showStripeError(
                    'Additional authentication is required.'
                );
                break;

            case 'requires_payment_method':
                showStripeError(
                    'Your payment was unsuccessful. Please try another card.'
                );
                break;

            case 'canceled':
                showStripeError(
                    'The payment was cancelled.'
                );
                break;

            default:
                window.location.assign(
                    url.toString()
                );
                return;
        }

        setButtonLoading(false);
    }

    /*
    |--------------------------------------------------------------------------
    | Reset local Stripe session
    |--------------------------------------------------------------------------
    */

    function resetStripeSession() {
        stripeReady = false;
        clientSecret = '';
        orderNumber = '';
        returnUrl = '';

        if (paymentElement) {
            try {
                paymentElement.destroy();
            } catch (error) {
                console.warn(
                    'Stripe element reset failed.',
                    error
                );
            }
        }

        paymentElement = null;
        elements = null;

        if (stripeElementContainer) {
            stripeElementContainer.innerHTML =
                '';
        }

        updateButtonText();
    }

    /*
    |--------------------------------------------------------------------------
    | Bank transfer
    |--------------------------------------------------------------------------
    */

    function submitBankTransferOrder() {
        setButtonLoading(
            true,
            'Placing your order...'
        );

        /*
         * Native submit bypasses this JavaScript
         * submit listener.
         */
        checkoutForm.submit();
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout submission
    |--------------------------------------------------------------------------
    */

    checkoutForm.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            if (requestInProgress) {
                return;
            }

            if (!validateCheckoutForm()) {
                return;
            }

            const selectedMethod =
                getSelectedPaymentMethod();

            if (
                selectedMethod ===
                'bank_transfer'
            ) {
                submitBankTransferOrder();
                return;
            }

            if (selectedMethod !== 'stripe') {
                showStripeError(
                    'Please select a valid payment method.'
                );

                return;
            }

            /*
             * Normally Stripe has already loaded
             * automatically.
             *
             * This is only a fallback in case the
             * automatic preparation did not run.
             */
            if (
                !stripeReady ||
                !clientSecret ||
                !paymentElement
            ) {
                const created =
                    await createPaymentIntent();

                if (!created) {
                    return;
                }

                /*
                 * Wait until the Payment Element
                 * has fired its ready event.
                 */
                if (!stripeReady) {
                    showStripeError(
                        'The secure payment form is loading. Please wait a moment.'
                    );

                    return;
                }
            }

            await confirmStripePayment();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Payment method listeners
    |--------------------------------------------------------------------------
    */

    checkoutForm
        .querySelectorAll(
            'input[name="payment_method"]'
        )
        .forEach(
            function (paymentMethodInput) {
                paymentMethodInput.addEventListener(
                    'change',
                    updatePaymentMethodDisplay
                );
            }
        );

    /*
    |--------------------------------------------------------------------------
    | Automatically load Stripe after field completion
    |--------------------------------------------------------------------------
    */

    checkoutForm.addEventListener(
        'input',
        function (event) {
            if (
                event.target.closest(
                    '#stripe-payment-container'
                )
            ) {
                return;
            }

            scheduleAutomaticStripePreparation();
        }
    );

    checkoutForm.addEventListener(
        'change',
        function (event) {
            if (
                event.target.name ===
                'payment_method'
            ) {
                return;
            }

            scheduleAutomaticStripePreparation();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Warn if checkout information changes after Stripe was prepared
    |--------------------------------------------------------------------------
    */

    const checkoutDataFields = [
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_zip',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_country',
        'shipping_state',
        'shipping_city',
        'shipping_zip',
        'ship_to_different_address',
    ];

    checkoutForm.addEventListener(
        'change',
        function (event) {
            if (
                !clientSecret ||
                !isStripeSelected()
            ) {
                return;
            }

            const target = event.target;

            if (
                !target ||
                target.closest(
                    '#stripe-payment-container'
                ) ||
                target.name ===
                'payment_method'
            ) {
                return;
            }

            if (
                !checkoutDataFields.includes(
                    target.name
                )
            ) {
                return;
            }

            showStripeError(
                'Checkout information changed after the payment form was prepared. Refresh the page before paying so your order details remain correct.'
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Support the custom event dispatched by checkout Blade
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'checkout:stripe-selected',
        function () {
            updatePaymentMethodDisplay();
            scheduleAutomaticStripePreparation();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    updatePaymentMethodDisplay();
});