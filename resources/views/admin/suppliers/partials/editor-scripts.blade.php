@push('page-scripts')

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {
            'use strict';

            const form = document.getElementById(
                'supplierEditorForm'
            );

            const submitButton = document.getElementById(
                'supplierSubmitButton'
            );

            const companyNameInput = document.getElementById(
                'company_name'
            );

            const supplierCodeInput = document.getElementById(
                'supplier_code'
            );

            const supplierCodePreview =
                document.getElementById(
                    'supplierCodePreview'
                );

            const statusSelect = document.getElementById(
                'status'
            );

            const statusPreview = document.getElementById(
                'supplierStatusPreview'
            );

            const statusPreviewLabel =
                document.getElementById(
                    'supplierStatusPreviewLabel'
                );

            const statusPreviewDescription =
                document.getElementById(
                    'supplierStatusPreviewDescription'
                );

            const currencySelect = document.getElementById(
                'currency'
            );

            const currencySymbol =
                document.getElementById(
                    'supplierCurrencySymbol'
                );

            const notesInput = document.getElementById(
                'notes'
            );

            const internalNotesInput =
                document.getElementById(
                    'internal_notes'
                );

            const notesCount = document.getElementById(
                'supplierNotesCount'
            );

            const internalNotesCount =
                document.getElementById(
                    'supplierInternalNotesCount'
                );

            const statusInformation = {
                active: {
                    label: 'Active',

                    description:
                        'The supplier can be used for new purchase orders.'
                },

                inactive: {
                    label: 'Inactive',

                    description:
                        'The supplier remains saved but is not currently active.'
                },

                blocked: {
                    label: 'Blocked',

                    description:
                        'Purchase orders should not be created for this supplier.'
                }
            };

            const currencySymbols = {
                GBP: '£',
                USD: '$',
                EUR: '€',
                PKR: '₨',
                AED: 'د.إ',
                CNY: '¥'
            };

            function updateStatusPreview() {
                if (
                    !statusSelect
                    || !statusPreview
                ) {
                    return;
                }

                const status =
                    statusSelect.value || 'active';

                const information =
                    statusInformation[status]
                    || statusInformation.active;

                statusPreview.classList.remove(
                    'active',
                    'inactive',
                    'blocked'
                );

                statusPreview.classList.add(
                    status
                );

                if (statusPreviewLabel) {
                    statusPreviewLabel.textContent =
                        information.label;
                }

                if (statusPreviewDescription) {
                    statusPreviewDescription.textContent =
                        information.description;
                }
            }

            function updateCurrencySymbol() {
                if (
                    !currencySelect
                    || !currencySymbol
                ) {
                    return;
                }

                currencySymbol.textContent =
                    currencySymbols[
                        currencySelect.value
                    ] || currencySelect.value;
            }

            function updateCodePreview() {
                if (!supplierCodePreview) {
                    return;
                }

                const code =
                    supplierCodeInput
                        ? supplierCodeInput.value.trim()
                        : '';

                supplierCodePreview.textContent =
                    code !== ''
                        ? code
                        : 'Generated on save';
            }

            function updateCharacterCount(
                input,
                counter
            ) {
                if (!input || !counter) {
                    return;
                }

                counter.textContent =
                    input.value.length
                        .toLocaleString('en-GB');
            }

            if (statusSelect) {
                statusSelect.addEventListener(
                    'change',
                    updateStatusPreview
                );
            }

            if (currencySelect) {
                currencySelect.addEventListener(
                    'change',
                    updateCurrencySymbol
                );
            }

            if (supplierCodeInput) {
                supplierCodeInput.addEventListener(
                    'input',
                    updateCodePreview
                );
            }

            if (companyNameInput) {
                companyNameInput.addEventListener(
                    'input',
                    function() {
                        if (
                            supplierCodeInput
                            && supplierCodeInput
                                .value
                                .trim() !== ''
                        ) {
                            return;
                        }

                        updateCodePreview();
                    }
                );
            }

            if (notesInput) {
                notesInput.maxLength = 5000;

                notesInput.addEventListener(
                    'input',
                    function() {
                        updateCharacterCount(
                            notesInput,
                            notesCount
                        );
                    }
                );
            }

            if (internalNotesInput) {
                internalNotesInput.maxLength = 5000;

                internalNotesInput.addEventListener(
                    'input',
                    function() {
                        updateCharacterCount(
                            internalNotesInput,
                            internalNotesCount
                        );
                    }
                );
            }

            if (form) {
                form.addEventListener(
                    'submit',
                    function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();

                            form.reportValidity();

                            return;
                        }

                        if (submitButton) {
                            submitButton.disabled = true;

                            submitButton.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i>'
                                + ' Saving Supplier...';
                        }
                    }
                );
            }

            updateStatusPreview();
            updateCurrencySymbol();
            updateCodePreview();

            updateCharacterCount(
                notesInput,
                notesCount
            );

            updateCharacterCount(
                internalNotesInput,
                internalNotesCount
            );
        }
    );
</script>

@endpush