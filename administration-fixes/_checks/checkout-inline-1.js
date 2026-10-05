
    document.addEventListener(
        'DOMContentLoaded',
        function () {
            'use strict';

            const checkoutPage =
                document.querySelector(
                    '.checkout-page'
                );

            if (!checkoutPage) {
                return;
            }

            const locationsApi =
                'https://countriesnow.space/api/v0.1/countries';

            const shippingQuoteUrl =
                checkoutPage.dataset
                    .shippingQuoteUrl || '';

            const csrfToken =
                document.querySelector(
                    'meta[name="csrf-token"]'
                )?.getAttribute('content') || '';

            const differentShippingCheckbox =
                document.getElementById(
                    'different-shipping'
                );

            const shippingFields =
                document.getElementById(
                    'shipping-fields'
                );

            const shippingAmount =
                document.getElementById(
                    'checkout-shipping-amount'
                );

            const totalAmount =
                document.getElementById(
                    'checkout-total-amount'
                );

            const selectedShippingText =
                document.getElementById(
                    'checkout-selected-shipping'
                );

            const shippingMethodStatus =
                document.getElementById(
                    'checkout-shipping-method-status'
                );

            const locationError =
                document.getElementById(
                    'checkout-location-error'
                );

            const placeOrderButton =
                document.getElementById(
                    'checkout-place-order-button'
                );

            const shippingRadios =
                document.querySelectorAll(
                    'input[name="shipping_method"]'
                );

            const locationGroups = {
                billing: {
                    country:
                        document.getElementById(
                            'billing_country'
                        ),

                    state:
                        document.getElementById(
                            'billing_state'
                        ),

                    city:
                        document.getElementById(
                            'billing_city'
                        ),

                    oldCountry:
                        checkoutPage.dataset
                            .oldBillingCountry || '',

                    oldState:
                        checkoutPage.dataset
                            .oldBillingState || '',

                    oldCity:
                        checkoutPage.dataset
                            .oldBillingCity || '',
                },

                shipping: {
                    country:
                        document.getElementById(
                            'shipping_country'
                        ),

                    state:
                        document.getElementById(
                            'shipping_state'
                        ),

                    city:
                        document.getElementById(
                            'shipping_city'
                        ),

                    oldCountry:
                        checkoutPage.dataset
                            .oldShippingCountry || '',

                    oldState:
                        checkoutPage.dataset
                            .oldShippingState || '',

                    oldCity:
                        checkoutPage.dataset
                            .oldShippingCity || '',
                },
            };

            let countries = [];
            let shippingRequestController = null;

            function showLocationError(
                message
            ) {
                if (!locationError) {
                    return;
                }

                locationError.textContent =
                    message;

                locationError.hidden =
                    false;
            }

            function clearLocationError() {
                if (!locationError) {
                    return;
                }

                locationError.textContent =
                    '';

                locationError.hidden =
                    true;
            }

            function flagFromCode(
                countryCode
            ) {
                if (!countryCode) {
                    return '';
                }

                return countryCode
                    .toUpperCase()
                    .split('')
                    .map(function (letter) {
                        return String.fromCodePoint(
                            127397 +
                            letter.charCodeAt()
                        );
                    })
                    .join('');
            }

            function updateFlag(select) {
                if (!select) {
                    return;
                }

                const flagElement =
                    document.querySelector(
                        '[data-flag-for="' +
                        select.id +
                        '"]'
                    );

                if (!flagElement) {
                    return;
                }

                flagElement.textContent =
                    flagFromCode(
                        select.value
                    );
            }

            function setSelectMessage(
                select,
                message,
                disabled = true
            ) {
                if (!select) {
                    return;
                }

                select.innerHTML = '';

                const option =
                    document.createElement(
                        'option'
                    );

                option.value = '';
                option.textContent =
                    message;

                select.appendChild(
                    option
                );

                select.disabled =
                    disabled;
            }

            function appendOption(
                select,
                value,
                label,
                selectedValue = ''
            ) {
                if (!select) {
                    return;
                }

                const option =
                    document.createElement(
                        'option'
                    );

                option.value = value;
                option.textContent =
                    label;

                if (
                    String(value) ===
                    String(selectedValue)
                ) {
                    option.selected =
                        true;
                }

                select.appendChild(
                    option
                );
            }

            function sortByName(items) {
                return [...items].sort(
                    function (
                        first,
                        second
                    ) {
                        return String(
                            first.name
                        ).localeCompare(
                            String(
                                second.name
                            )
                        );
                    }
                );
            }

            function getSelectedCountryName(
                select
            ) {
                return select
                    ?.selectedOptions?.[0]
                    ?.dataset?.countryName ||
                    '';
            }

            function populateCountries(
                group,
                selectedCountry = ''
            ) {
                const countrySelect =
                    group.country;

                if (!countrySelect) {
                    return;
                }

                countrySelect.innerHTML =
                    '';

                appendOption(
                    countrySelect,
                    '',
                    'Select country'
                );

                sortByName(
                    countries
                ).forEach(
                    function (country) {
                        const option =
                            document.createElement(
                                'option'
                            );

                        const countryCode =
                            String(
                                country.iso2
                                || ''
                            ).toUpperCase();

                        option.value =
                            countryCode;

                        option.dataset
                            .countryName =
                            country.name;

                        option.textContent =
                            flagFromCode(
                                countryCode
                            )
                            + ' '
                            + country.name;

                        if (
                            countryCode ===
                            String(
                                selectedCountry
                            ).toUpperCase()
                        ) {
                            option.selected =
                                true;
                        }

                        countrySelect
                            .appendChild(
                                option
                            );
                    }
                );

                countrySelect.disabled =
                    false;

                updateFlag(
                    countrySelect
                );
            }

            function findCountry(
                countryCode
            ) {
                return countries.find(
                    function (country) {
                        return String(
                            country.iso2
                            || ''
                        ).toUpperCase() ===
                            String(
                                countryCode
                                || ''
                            ).toUpperCase();
                    }
                );
            }

            function populateStates(
                group,
                selectedState = ''
            ) {
                if (
                    !group.country ||
                    !group.state ||
                    !group.city
                ) {
                    return;
                }

                const selectedCountry =
                    findCountry(
                        group.country
                            .value
                    );

                if (
                    !selectedCountry ||
                    !Array.isArray(
                        selectedCountry
                            .states
                    )
                ) {
                    setSelectMessage(
                        group.state,
                        'No states available',
                        false
                    );

                    setSelectMessage(
                        group.city,
                        'Select state first'
                    );

                    return;
                }

                group.state.innerHTML =
                    '';

                appendOption(
                    group.state,
                    '',
                    'Select state / province'
                );

                sortByName(
                    selectedCountry
                        .states
                ).forEach(
                    function (state) {
                        appendOption(
                            group.state,
                            state.name,
                            state.name,
                            selectedState
                        );
                    }
                );

                group.state.disabled =
                    false;

                setSelectMessage(
                    group.city,
                    'Select state first'
                );
            }

            async function populateCities(
                group,
                selectedCity = ''
            ) {
                if (
                    !group.country ||
                    !group.state ||
                    !group.city
                ) {
                    return;
                }

                const countryName =
                    getSelectedCountryName(
                        group.country
                    );

                const stateName =
                    group.state.value;

                if (
                    !countryName ||
                    !stateName
                ) {
                    setSelectMessage(
                        group.city,
                        'Select state first'
                    );

                    return;
                }

                setSelectMessage(
                    group.city,
                    'Loading cities...'
                );

                try {
                    const response =
                        await fetch(
                            locationsApi +
                            '/state/cities',
                            {
                                method:
                                    'POST',

                                headers: {
                                    'Content-Type':
                                        'application/json',

                                    Accept:
                                        'application/json',
                                },

                                body:
                                    JSON.stringify(
                                        {
                                            country:
                                                countryName,

                                            state:
                                                stateName,
                                        }
                                    ),
                            }
                        );

                    if (!response.ok) {
                        throw new Error(
                            'Unable to load cities.'
                        );
                    }

                    const payload =
                        await response
                            .json();

                    const cities =
                        Array.isArray(
                            payload.data
                        )
                            ? payload.data
                            : [];

                    group.city.innerHTML =
                        '';

                    appendOption(
                        group.city,
                        '',
                        cities.length
                            ? 'Select city'
                            : 'No cities available'
                    );

                    [...cities]
                        .sort(
                            function (
                                first,
                                second
                            ) {
                                return String(
                                    first
                                ).localeCompare(
                                    String(
                                        second
                                    )
                                );
                            }
                        )
                        .forEach(
                            function (city) {
                                appendOption(
                                    group.city,
                                    city,
                                    city,
                                    selectedCity
                                );
                            }
                        );

                    group.city.disabled =
                        false;
                } catch (error) {
                    console.error(
                        error
                    );

                    setSelectMessage(
                        group.city,
                        'Unable to load cities',
                        false
                    );

                    showLocationError(
                        'Cities could not be loaded. Please refresh the page and try again.'
                    );
                }
            }

            async function initialiseGroup(
                group
            ) {
                populateCountries(
                    group,
                    group.oldCountry
                );

                if (!group.oldCountry) {
                    return;
                }

                populateStates(
                    group,
                    group.oldState
                );

                if (
                    group.oldState &&
                    group.state?.value
                ) {
                    await populateCities(
                        group,
                        group.oldCity
                    );
                }
            }

            function updateShippingFields() {
                const isDifferent =
                    Boolean(
                        differentShippingCheckbox
                            ?.checked
                    );

                if (shippingFields) {
                    shippingFields.hidden =
                        !isDifferent;

                    shippingFields
                        .querySelectorAll(
                            '[data-shipping-required]'
                        )
                        .forEach(
                            function (field) {
                                field.required =
                                    isDifferent;
                            }
                        );
                }
            }

            async function loadCountries() {
                clearLocationError();

                Object.values(
                    locationGroups
                ).forEach(
                    function (group) {
                        setSelectMessage(
                            group.country,
                            'Loading countries...'
                        );
                    }
                );

                try {
                    const response =
                        await fetch(
                            locationsApi +
                            '/states',
                            {
                                method:
                                    'GET',

                                headers: {
                                    Accept:
                                        'application/json',
                                },
                            }
                        );

                    if (!response.ok) {
                        throw new Error(
                            'Unable to load countries.'
                        );
                    }

                    const payload =
                        await response
                            .json();

                    countries =
                        Array.isArray(
                            payload.data
                        )
                            ? payload.data
                            : [];

                    if (!countries.length) {
                        throw new Error(
                            'No country data was returned.'
                        );
                    }

                    await initialiseGroup(
                        locationGroups.billing
                    );

                    await initialiseGroup(
                        locationGroups.shipping
                    );
                } catch (error) {
                    console.error(
                        error
                    );

                    Object.values(
                        locationGroups
                    ).forEach(
                        function (group) {
                            setSelectMessage(
                                group.country,
                                'Unable to load countries',
                                false
                            );
                        }
                    );

                    showLocationError(
                        'Countries could not be loaded. Check your internet connection and refresh the page.'
                    );
                }
            }

            async function updateShippingMethod() {
                const selectedRadio =
                    document.querySelector(
                        'input[name="shipping_method"]:checked'
                    );

                if (
                    !selectedRadio ||
                    !shippingQuoteUrl
                ) {
                    return;
                }

                if (
                    shippingRequestController
                ) {
                    shippingRequestController
                        .abort();
                }

                shippingRequestController =
                    new AbortController();

                if (
                    shippingMethodStatus
                ) {
                    shippingMethodStatus
                        .textContent =
                        'Updating shipping method...';
                }

                if (placeOrderButton) {
                    placeOrderButton
                        .disabled = true;
                }

                shippingRadios.forEach(
                    function (radio) {
                        radio.disabled =
                            true;
                    }
                );

                try {
                    const response =
                        await fetch(
                            shippingQuoteUrl,
                            {
                                method:
                                    'POST',

                                headers: {
                                    'Content-Type':
                                        'application/json',

                                    Accept:
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        csrfToken,

                                    'X-Requested-With':
                                        'XMLHttpRequest',
                                },

                                body:
                                    JSON.stringify(
                                        {
                                            shipping_method:
                                                selectedRadio
                                                    .value,
                                        }
                                    ),

                                signal:
                                    shippingRequestController
                                        .signal,
                            }
                        );

                    const payload =
                        await response
                            .json();

                    if (
                        !response.ok ||
                        !payload.success
                    ) {
                        throw new Error(
                            payload.message
                            || 'Shipping could not be updated.'
                        );
                    }

                    if (shippingAmount) {
                        shippingAmount
                            .textContent =
                            payload
                                .formatted_shipping;
                    }

                    if (totalAmount) {
                        totalAmount
                            .textContent =
                            payload
                                .formatted_total;
                        const taxAmount=document.getElementById("checkout-tax-amount");if(taxAmount)taxAmount.textContent=(payload.currency || "")+" "+Number(payload.tax || 0).toFixed(2);
                    }

                    if (
                        selectedShippingText
                    ) {
                        selectedShippingText
                            .textContent =
                            payload.shipping_name
                            + ' • '
                            + payload.delivery_time;
                    }

                    if (
                        shippingMethodStatus
                    ) {
                        shippingMethodStatus
                            .textContent =
                            payload.message
                            || 'Shipping method updated.';
                    }

                    /*
                     * Let the Stripe integration know
                     * that the order total has changed.
                     */
                    document.dispatchEvent(
                        new CustomEvent(
                            'checkout:shipping-updated',
                            {
                                detail: {
                                    shippingMethod:
                                        payload
                                            .shipping_method,

                                    shipping:
                                        payload.shipping,

                                    total:
                                        payload.total,
                                },
                            }
                        )
                    );
                } catch (error) {
                    if (
                        error.name ===
                        'AbortError'
                    ) {
                        return;
                    }

                    console.error(
                        error
                    );

                    if (
                        shippingMethodStatus
                    ) {
                        shippingMethodStatus
                            .textContent =
                            error.message
                            || 'Shipping could not be updated. Please try again.';
                    }
                } finally {
                    shippingRadios
                        .forEach(
                            function (radio) {
                                radio.disabled =
                                    false;
                            }
                        );

                    if (
                        placeOrderButton
                    ) {
                        placeOrderButton
                            .disabled =
                            false;
                    }
                }
            }

            Object.values(
                locationGroups
            ).forEach(
                function (group) {
                    group.country
                        ?.addEventListener(
                            'change',
                            function () {
                                updateFlag(
                                    group.country
                                );

                                populateStates(
                                    group
                                );
                            }
                        );

                    group.state
                        ?.addEventListener(
                            'change',
                            function () {
                                populateCities(
                                    group
                                );
                            }
                        );
                }
            );

            differentShippingCheckbox
                ?.addEventListener(
                    'change',
                    updateShippingFields
                );

            shippingRadios.forEach(
                function (radio) {
                    radio.addEventListener(
                        'change',
                        updateShippingMethod
                    );
                }
            );

            updateShippingFields();
            loadCountries();

            /*
             * The initial values are already calculated
             * by Laravel. A new server request is not
             * required until the customer changes the
             * selected shipping method.
             */
        }
    );
