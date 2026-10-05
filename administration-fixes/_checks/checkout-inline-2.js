
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            'use strict';

            const paymentRadios =
                document.querySelectorAll(
                    'input[name="payment_method"]'
                );

            const stripeContainer =
                document.getElementById(
                    'stripe-payment-container'
                );

            const bankTransferContainer =
                document.getElementById(
                    'bank-transfer-details'
                );

            function updatePaymentMethodDisplay() {
                const selected =
                    document.querySelector(
                        'input[name="payment_method"]:checked'
                    )?.value || '';

                if (stripeContainer) {
                    stripeContainer.hidden =
                        selected !== 'stripe';
                }

                if (
                    bankTransferContainer
                ) {
                    bankTransferContainer
                        .hidden =
                        selected !==
                        'bank_transfer';
                }

                if (
                    selected === 'stripe'
                ) {
                    document.dispatchEvent(
                        new CustomEvent(
                            'checkout:stripe-selected'
                        )
                    );
                }
            }

            paymentRadios.forEach(
                function (radio) {
                    radio.addEventListener(
                        'change',
                        updatePaymentMethodDisplay
                    );
                }
            );

            updatePaymentMethodDisplay();
        }
    );
