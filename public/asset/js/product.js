document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    initCustomSortDropdown();
    initProductCardGallery();
    initProductQuickView();
    initSingleProductGallery();
    initProductOptions();
    initProductQuantity();
    initVariantCartProtection();
    initAjaxAddToCart();
    initCartQuantity();
    initCartRemove();
    initCartCoupon();
    initProductTabs();
    reviewRatingField();
    shopFilter();
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function getCsrfToken() {
    const metaToken = document.querySelector('meta[name="csrf-token"]');

    if (metaToken) {
        return metaToken.getAttribute("content");
    }

    const csrfInput = document.querySelector('input[name="_token"]');

    return csrfInput ? csrfInput.value : "";
}

function formatPrice(price, currencySymbol = "$") {
    const numericPrice = Number(price || 0);

    return (
        currencySymbol +
        numericPrice.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
    );
}

function showElement(element) {
    if (!element) {
        return;
    }

    element.hidden = false;
    element.classList.remove("d-none");
}

function hideElement(element) {
    if (!element) {
        return;
    }

    element.hidden = true;
    element.classList.add("d-none");
}

/*
|--------------------------------------------------------------------------
| Custom sort dropdown
|--------------------------------------------------------------------------
*/

function initCustomSortDropdown() {
    const sortForms = document.querySelectorAll(".custom-sort-form, #sort-form");

    if (!sortForms.length) {
        return;
    }

    sortForms.forEach(function (form) {
        const dropdown = form.querySelector(".custom-sort-dropdown");
        const trigger = form.querySelector(".sort-trigger");
        const optionsWrapper = form.querySelector(".sort-options");
        const label = form.querySelector(".sort-label, #sort-label");
        const hiddenInput = form.querySelector(
            'input[name="sort"], #sort-value'
        );
        const options = form.querySelectorAll(".sort-option");

        if (
            !dropdown ||
            !trigger ||
            !optionsWrapper ||
            !hiddenInput ||
            !options.length
        ) {
            return;
        }

        trigger.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            const isOpen = optionsWrapper.classList.toggle("show");

            trigger.setAttribute(
                "aria-expanded",
                isOpen ? "true" : "false"
            );
        });

        options.forEach(function (option) {
            option.addEventListener("click", function () {
                const value = option.dataset.value || "";
                const text = option.textContent.trim();

                options.forEach(function (item) {
                    item.classList.remove("active");
                    item.setAttribute("aria-selected", "false");
                });

                option.classList.add("active");
                option.setAttribute("aria-selected", "true");

                hiddenInput.value = value;

                if (label) {
                    label.textContent = text;
                }

                optionsWrapper.classList.remove("show");
                trigger.setAttribute("aria-expanded", "false");

                form.submit();
            });
        });

        document.addEventListener("click", function (event) {
            if (!dropdown.contains(event.target)) {
                optionsWrapper.classList.remove("show");
                trigger.setAttribute("aria-expanded", "false");
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                optionsWrapper.classList.remove("show");
                trigger.setAttribute("aria-expanded", "false");
            }
        });
    });
}

/*
|--------------------------------------------------------------------------
| Product-card gallery
|--------------------------------------------------------------------------
*/

function initProductCardGallery() {
    document.addEventListener("click", function (event) {
        const galleryButton = event.target.closest(
            ".product-card-gallery-image"
        );

        if (!galleryButton) {
            return;
        }

        event.preventDefault();

        const card = galleryButton.closest(
            ".product-card, .card-parent"
        );

        if (!card) {
            return;
        }

        const backgroundImage = card.querySelector(".background-image");
        const imageElement = card.querySelector(
            ".card-main-image, .product-card-main-image"
        );
        const imageUrl = galleryButton.dataset.image;

        if (!imageUrl) {
            return;
        }

        if (backgroundImage) {
            backgroundImage.style.backgroundImage =
                'url("' + imageUrl + '")';
        }

        if (imageElement) {
            imageElement.src = imageUrl;
        }

        card.querySelectorAll(".product-card-gallery-image").forEach(
            function (button) {
                button.classList.remove("active");
            }
        );

        galleryButton.classList.add("active");
    });
}

/*
|--------------------------------------------------------------------------
| Product quick-view popup
|--------------------------------------------------------------------------
*/

function initProductQuickView() {
    const modal = document.getElementById("product-quick-view-modal");
    const modalContent = document.getElementById(
        "product-quick-view-content"
    );

    if (!modal || !modalContent) {
        return;
    }

    let requestController = null;
    let lastFocusedElement = null;

    function openModal() {
        modal.classList.add("active");
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("product-popup-open");

        const closeButton = modal.querySelector(".product-popup-close");

        if (closeButton) {
            closeButton.focus();
        }
    }

    function closeModal() {
        modal.classList.remove("active");
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("product-popup-open");

        if (requestController) {
            requestController.abort();
            requestController = null;
        }

        if (lastFocusedElement) {
            lastFocusedElement.focus();
        }
    }

    document.addEventListener("click", function (event) {
        const openButton = event.target.closest(".open-product-popup");

        if (!openButton) {
            return;
        }

        event.preventDefault();

        const popupUrl =
            openButton.dataset.popupUrl ||
            openButton.getAttribute("href");

        if (!popupUrl) {
            return;
        }

        lastFocusedElement = openButton;

        if (requestController) {
            requestController.abort();
        }

        requestController = new AbortController();

        modalContent.innerHTML =
            '<div class="product-popup-loader">Loading product...</div>';

        openModal();

        fetch(popupUrl, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "text/html",
            },
            signal: requestController.signal,
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Unable to load product.");
                }

                return response.text();
            })
            .then(function (html) {
                modalContent.innerHTML = html;

                initLoadedQuickViewContent(modalContent);
            })
            .catch(function (error) {
                if (error.name === "AbortError") {
                    return;
                }

                modalContent.innerHTML =
                    '<div class="product-popup-loader">' +
                    "Unable to load product." +
                    "</div>";
            });
    });

    document.addEventListener("click", function (event) {
        if (event.target.closest("[data-close-product-popup]")) {
            closeModal();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (
            event.key === "Escape" &&
            modal.classList.contains("active")
        ) {
            closeModal();
        }
    });

    modal.addEventListener("click", function (event) {
        const galleryItem = event.target.closest(
            ".quick-view-gallery-item"
        );

        if (galleryItem) {
            const mainImage = modal.querySelector(
                "#quick-view-main-image, .quick-view-main-image"
            );

            const imageUrl = galleryItem.dataset.popupImage;

            if (mainImage && imageUrl) {
                mainImage.src = imageUrl;
            }

            modal.querySelectorAll(".quick-view-gallery-item").forEach(
                function (item) {
                    item.classList.remove("active");
                }
            );

            galleryItem.classList.add("active");
        }
    });
}

function initLoadedQuickViewContent(container) {
    initOptionButtonsInside(container);
    initQuantityInside(container);
    initSingleGalleryInside(container);
}

/*
|--------------------------------------------------------------------------
| Single-product gallery
|--------------------------------------------------------------------------
*/

function initSingleProductGallery() {
    initSingleGalleryInside(document);
}

function initSingleGalleryInside(container) {
    const galleryContainers = container.querySelectorAll(
        ".single-product-gallery, .product-gallery, .quick-view-images"
    );

    if (!galleryContainers.length) {
        return;
    }

    galleryContainers.forEach(function (gallery) {
        const mainImage = gallery.querySelector(
            ".single-product-main-image, " +
            ".product-main-image, " +
            "#quick-view-main-image, " +
            ".quick-view-main-image"
        );

        const thumbnails = gallery.querySelectorAll(
            ".single-gallery-thumbnail, " +
            ".product-gallery-thumbnail, " +
            ".quick-view-gallery-item, " +
            "[data-gallery-image]"
        );

        if (!mainImage || !thumbnails.length) {
            return;
        }

        thumbnails.forEach(function (thumbnail) {
            if (thumbnail.dataset.galleryInitialized === "true") {
                return;
            }

            thumbnail.dataset.galleryInitialized = "true";

            thumbnail.addEventListener("click", function (event) {
                event.preventDefault();

                const newImage =
                    thumbnail.dataset.image ||
                    thumbnail.dataset.galleryImage ||
                    thumbnail.dataset.popupImage ||
                    thumbnail.querySelector("img")?.src;

                if (!newImage) {
                    return;
                }

                mainImage.src = newImage;

                if (thumbnail.dataset.largeImage) {
                    mainImage.dataset.zoomImage =
                        thumbnail.dataset.largeImage;
                }

                thumbnails.forEach(function (item) {
                    item.classList.remove("active");
                    item.setAttribute("aria-selected", "false");
                });

                thumbnail.classList.add("active");
                thumbnail.setAttribute("aria-selected", "true");
            });
        });
    });
}

/*
|--------------------------------------------------------------------------
| Product options and variants
|--------------------------------------------------------------------------
*/

function initProductOptions() {
    initOptionButtonsInside(document);
}

function initOptionButtonsInside(container) {
    const productForms = container.querySelectorAll(
        ".product-form, " +
        ".single-product-form, " +
        ".quick-view-cart-form, " +
        "[data-product-form]"
    );

    if (!productForms.length) {
        return;
    }

    productForms.forEach(function (form) {
        if (form.dataset.optionsInitialized === "true") {
            return;
        }

        form.dataset.optionsInitialized = "true";

        const optionButtons = form.querySelectorAll(
            ".option-value-button, .quick-view-option-value"
        );

        const optionSelects = form.querySelectorAll(
            ".product-option-select"
        );

        optionButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                if (
                    button.disabled ||
                    button.classList.contains("disabled")
                ) {
                    return;
                }

                const optionId =
                    button.dataset.optionId ||
                    button.dataset.option;

                const valueId =
                    button.dataset.valueId ||
                    button.dataset.value;

                if (!optionId || valueId === undefined) {
                    return;
                }

                const matchingSelect = form.querySelector(
                    '.product-option-select[data-option-id="' +
                    optionId +
                    '"], ' +
                    '.product-option-select[name="options[' +
                    optionId +
                    ']"]'
                );

                /*
                 * Clicking the currently selected value again deselects it.
                 * This restores the original customer-friendly toggle behavior
                 * while still allowing the availability engine to recalculate
                 * valid combinations immediately.
                 */
                const isAlreadySelected =
                    button.classList.contains("active") &&
                    matchingSelect &&
                    String(matchingSelect.value || "") ===
                        String(valueId);

                form.querySelectorAll(
                    '[data-option-id="' + optionId + '"]'
                ).forEach(function (item) {
                    item.classList.remove("active");
                    item.setAttribute("aria-pressed", "false");
                });

                const selectedLabel = form.querySelector(
                    '[data-selected-option="' +
                    optionId +
                    '"], ' +
                    '.selected-option-value[data-option-id="' +
                    optionId +
                    '"]'
                );

                if (isAlreadySelected) {
                    matchingSelect.value = "";

                    if (selectedLabel) {
                        selectedLabel.textContent = "";
                    }

                    matchingSelect.dispatchEvent(
                        new Event("change", {
                            bubbles: true,
                        })
                    );

                    updateSelectedVariant(form);
                    return;
                }

                button.classList.add("active");
                button.setAttribute("aria-pressed", "true");

                if (matchingSelect) {
                    matchingSelect.value = valueId;

                    matchingSelect.dispatchEvent(
                        new Event("change", {
                            bubbles: true,
                        })
                    );
                }

                if (selectedLabel) {
                    selectedLabel.textContent =
                        button.dataset.label ||
                        button.textContent.trim();
                }

                clearOptionError(form, optionId);
                updateSelectedVariant(form);
            });
        });

        optionSelects.forEach(function (select) {
            select.addEventListener("change", function () {
                const optionId = select.dataset.optionId;

                if (optionId) {
                    clearOptionError(form, optionId);
                }

                updateSelectedVariant(form);
            });
        });

        form.addEventListener("submit", function (event) {
            const valid = validateProductOptions(form);

            if (!valid) {
                event.preventDefault();
            }
        });

        updateSelectedVariant(form);
    });
}

function validateProductOptions(form) {
    const optionSelects = form.querySelectorAll(
        ".product-option-select[required]"
    );

    let isValid = true;
    let firstInvalidElement = null;

    optionSelects.forEach(function (select) {
        const optionId = select.dataset.optionId;

        if (!select.value) {
            isValid = false;

            const errorElement = form.querySelector(
                '[data-option-error="' +
                optionId +
                '"], ' +
                ".product-option-error"
            );

            if (errorElement) {
                errorElement.textContent =
                    "Please select this option.";

                showElement(errorElement);
            }

            if (!firstInvalidElement) {
                firstInvalidElement =
                    form.querySelector(
                        '[data-option-id="' +
                        optionId +
                        '"]'
                    ) || select;
            }
        }
    });

    if (firstInvalidElement) {
        firstInvalidElement.focus();
    }

    return isValid;
}

function clearOptionError(form, optionId) {
    const errorElement = form.querySelector(
        '[data-option-error="' +
        optionId +
        '"], ' +
        ".product-option-error"
    );

    if (errorElement) {
        errorElement.textContent = "";
        hideElement(errorElement);
    }
}

function getVariantsElement(form) {
    const localElement = form.querySelector(
        "[data-product-variants]"
    );

    if (localElement) {
        return localElement;
    }

    const productContainer = form.closest(
        ".quick-view-product, " +
        "#product-quick-view-content, " +
        ".single-product-page, " +
        ".single-product, " +
        "[data-product-container]"
    );

    return productContainer
        ? productContainer.querySelector("[data-product-variants]")
        : null;
}

function getProductVariants(form) {
    const variantsElement = getVariantsElement(form);

    if (!variantsElement) {
        return [];
    }

    const variantsJson =
        variantsElement.dataset.productVariants ||
        variantsElement.textContent ||
        "[]";

    try {
        const variants = JSON.parse(variantsJson);

        return Array.isArray(variants) ? variants : [];
    } catch (error) {
        console.error("Invalid product variant data.", error);

        return [];
    }
}

function normalizeVariantOptions(variant) {
    const rawOptions =
        variant.options ||
        variant.option_values ||
        variant.values ||
        {};

    const normalizedOptions = {};

    if (Array.isArray(rawOptions)) {
        rawOptions.forEach(function (option) {
            const optionId =
                option.option_id ||
                option.product_option_id ||
                option.option?.id ||
                "";

            const valueId =
                option.value_id ||
                option.option_value_id ||
                option.product_option_value_id ||
                option.value?.id ||
                option.value ||
                "";

            if (optionId && valueId) {
                normalizedOptions[String(optionId)] =
                    String(valueId);
            }
        });

        return normalizedOptions;
    }

    if (
        rawOptions &&
        typeof rawOptions === "object"
    ) {
        Object.entries(rawOptions).forEach(function (
            [optionId, value]
        ) {
            if (
                value &&
                typeof value === "object"
            ) {
                normalizedOptions[String(optionId)] = String(
                    value.value_id ||
                    value.option_value_id ||
                    value.id ||
                    value.value ||
                    ""
                );
            } else {
                normalizedOptions[String(optionId)] =
                    String(value);
            }
        });
    }

    return normalizedOptions;
}

function getVariantAvailability(variant) {
    if (typeof variant.available === "boolean") {
        return variant.available;
    }

    if (variant.available === 1 || variant.available === "1") {
        return true;
    }

    if (variant.available === 0 || variant.available === "0") {
        return false;
    }

    /*
     * Backward compatibility for cached/older markup.
     * New Blade files send only `available`, not the stock quantity.
     */
    const legacyStock = Number(
        variant.stock ??
        variant.quantity ??
        variant.stock_quantity ??
        0
    );

    return legacyStock > 0;
}

function getSelectedOptions(form) {
    const selectedOptions = {};

    form.querySelectorAll(".product-option-select").forEach(
        function (select) {
            const optionId =
                select.dataset.optionId ||
                select.name.match(/\[(.*?)\]/)?.[1];

            const valueId = String(select.value || "").trim();

            if (optionId && valueId) {
                selectedOptions[String(optionId)] = valueId;
            }
        }
    );

    return selectedOptions;
}

function variantMatchesSelections(
    variant,
    selectedOptions,
    ignoredOptionId = null
) {
    const variantOptions = normalizeVariantOptions(variant);

    return Object.entries(selectedOptions).every(
        function ([optionId, valueId]) {
            if (
                ignoredOptionId !== null &&
                String(optionId) === String(ignoredOptionId)
            ) {
                return true;
            }

            return (
                String(variantOptions[optionId] || "") ===
                String(valueId)
            );
        }
    );
}

function updateOptionAvailability(form) {
    const variants = getProductVariants(form);

    if (!variants.length) {
        return;
    }

    const selectedOptions = getSelectedOptions(form);

    form.querySelectorAll(".product-option-select").forEach(
        function (select) {
            const optionId =
                select.dataset.optionId ||
                select.name.match(/\[(.*?)\]/)?.[1];

            if (!optionId) {
                return;
            }

            Array.from(select.options).forEach(function (option) {
                const valueId = String(option.value || "").trim();

                if (!valueId) {
                    option.disabled = false;
                    return;
                }

                const candidateSelections = {
                    ...selectedOptions,
                    [String(optionId)]: valueId,
                };

                const canPurchase = variants.some(
                    function (variant) {
                        if (!getVariantAvailability(variant)) {
                            return false;
                        }

                        return variantMatchesSelections(
                            variant,
                            candidateSelections,
                            null
                        );
                    }
                );

                option.disabled = !canPurchase;
            });
        }
    );

    form.querySelectorAll(
        ".option-value-button, .quick-view-option-value"
    ).forEach(function (button) {
        const optionId =
            button.dataset.optionId ||
            button.dataset.option;

        const valueId =
            button.dataset.valueId ||
            button.dataset.value;

        if (!optionId || valueId === undefined) {
            return;
        }

        const candidateSelections = {
            ...selectedOptions,
            [String(optionId)]: String(valueId),
        };

        const canPurchase = variants.some(function (variant) {
            if (!getVariantAvailability(variant)) {
                return false;
            }

            return variantMatchesSelections(
                variant,
                candidateSelections,
                null
            );
        });

        button.disabled = !canPurchase;
        button.classList.toggle("disabled", !canPurchase);
        button.setAttribute(
            "aria-disabled",
            canPurchase ? "false" : "true"
        );

        if (!canPurchase) {
            button.setAttribute(
                "title",
                "This option is currently out of stock."
            );
        } else {
            button.removeAttribute("title");
        }
    });
}

function initializeSingleVariantState(form) {
    const variants = getProductVariants(form);

    if (variants.length !== 1) {
        return false;
    }

    const variant = variants[0];

    if (getVariantAvailability(variant)) {
        return false;
    }

    const variantInput = form.querySelector(
        'input[name="variant_id"], [data-selected-variant]'
    );

    if (variantInput) {
        variantInput.value = "";
    }

    showAvailabilityMessage(
        form,
        "This product is currently out of stock and cannot be purchased.",
        true
    );

    updateAddToCartButton(form, false);
    updateOptionAvailability(form);

    return true;
}

function updateSelectedVariant(form) {
    const variants = getProductVariants(form);

    if (!variants.length) {
        return;
    }

    if (initializeSingleVariantState(form)) {
        return;
    }

    updateOptionAvailability(form);

    const optionSelects = Array.from(
        form.querySelectorAll(".product-option-select")
    );

    const variantInput = form.querySelector(
        'input[name="variant_id"], [data-selected-variant]'
    );

    if (variantInput) {
        variantInput.value = "";
    }

    const selectedOptions = {};
    let allOptionsSelected = true;

    optionSelects.forEach(function (select) {
        const optionId =
            select.dataset.optionId ||
            select.name.match(/\[(.*?)\]/)?.[1];

        const valueId = String(select.value || "").trim();

        if (!optionId || !valueId) {
            allOptionsSelected = false;
            return;
        }

        selectedOptions[String(optionId)] = valueId;
    });

    if (
        !optionSelects.length ||
        !allOptionsSelected ||
        Object.keys(selectedOptions).length !== optionSelects.length
    ) {
        showAvailabilityMessage(
            form,
            "Please select one value from every option.",
            false
        );

        updateAddToCartButton(form, false);
        return;
    }

    const matchedVariant = variants.find(function (variant) {
        const variantOptions = normalizeVariantOptions(variant);
        const selectedEntries = Object.entries(selectedOptions);
        const variantEntries = Object.entries(variantOptions);

        if (variantEntries.length !== selectedEntries.length) {
            return false;
        }

        return selectedEntries.every(function (
            [optionId, valueId]
        ) {
            return (
                String(variantOptions[optionId] || "") ===
                String(valueId)
            );
        });
    });

    if (!matchedVariant) {
        updateVariantUnavailable(form);
        return;
    }

    updateVariantDisplay(form, matchedVariant);
    updateOptionAvailability(form);
}

function updateVariantDisplay(form, variant) {
    const variantInput = form.querySelector(
        'input[name="variant_id"], [data-selected-variant]'
    );

    if (variantInput) {
        variantInput.value = variant.id || "";
    }

    const currencySymbol =
        form.dataset.currencySymbol ||
        document.body.dataset.currencySymbol ||
        "$";

    const regularPrice = Number(
        variant.regular_price ??
        variant.price ??
        variant.original_price ??
        0
    );

    const salePrice = Number(
        variant.sale_price ??
        variant.discount_price ??
        regularPrice
    );

    const priceElement =
        form.querySelector(
            ".current-product-price, " +
            ".product-sale-price, " +
            "[data-product-price]"
        ) ||
        document.querySelector(
            ".current-product-price, [data-product-price]"
        );

    const regularPriceElement =
        form.querySelector(
            ".product-regular-price, [data-regular-price]"
        ) ||
        document.querySelector("[data-regular-price]");

    if (priceElement) {
        priceElement.textContent = formatPrice(
            salePrice,
            currencySymbol
        );
    }

    if (regularPriceElement) {
        if (salePrice < regularPrice) {
            regularPriceElement.textContent = formatPrice(
                regularPrice,
                currencySymbol
            );

            showElement(regularPriceElement);
        } else {
            hideElement(regularPriceElement);
        }
    }

    const isAvailable = getVariantAvailability(variant);

    const skuElement =
        form.querySelector("[data-product-sku], .product-sku-value") ||
        document.querySelector("[data-product-sku]");

    if (skuElement && variant.sku) {
        skuElement.textContent = variant.sku;
    }

    const productContainer = form.closest(
        ".quick-view-product, .single-product-page, [data-product-container]"
    );

    const mainImage =
        productContainer?.querySelector(
            ".single-product-main-image, " +
            ".product-main-image, " +
            ".quick-view-main-image"
        ) ||
        document.querySelector(
            ".single-product-main-image, .product-main-image"
        );

    const variantImage =
        variant.image_url ||
        variant.image ||
        variant.featured_image;

    if (mainImage && variantImage) {
        mainImage.src = variantImage;
    }

    if (isAvailable) {
        showAvailabilityMessage(form, "", false, true);
    } else {
        showAvailabilityMessage(
            form,
            "This selected option is currently out of stock. Please choose another available option.",
            true
        );
    }

    updateStockDisplay(form, isAvailable);
    updateAddToCartButton(form, isAvailable);
}

function updateVariantUnavailable(form) {
    const variantInput = form.querySelector(
        'input[name="variant_id"], [data-selected-variant]'
    );

    if (variantInput) {
        variantInput.value = "";
    }

    showAvailabilityMessage(
        form,
        "This option combination is currently unavailable.",
        true
    );

    updateAddToCartButton(form, false);
}

function showAvailabilityMessage(
    form,
    text,
    isError = false,
    hideWhenEmpty = false
) {
    const message = form.querySelector(
        ".variant-message, [data-variant-message]"
    );

    if (message) {
        message.textContent = text;
        message.classList.toggle("error", isError);
        message.classList.toggle("success", !isError && Boolean(text));

        if (hideWhenEmpty && !text) {
            hideElement(message);
        } else if (text) {
            showElement(message);
        }
    }

    const stockMessage =
        form.querySelector(
            "[data-stock-message], .quick-view-stock, .product-stock-message"
        ) ||
        form.closest(
            ".quick-view-product, .single-product-page, [data-product-container]"
        )?.querySelector(
            "[data-stock-message], .quick-view-stock, .product-stock-message"
        );

    if (stockMessage) {
        if (isError && text) {
            stockMessage.textContent = text;
            stockMessage.classList.remove("in-stock");
            stockMessage.classList.add("out-of-stock");
            showElement(stockMessage);
        } else if (!text) {
            hideElement(stockMessage);
        }
    }
}

function updateStockDisplay(form, isAvailable) {
    const productContainer = form.closest(
        ".quick-view-product, .single-product-page, [data-product-container]"
    );

    const stockElements = [
        form.querySelector("#product-stock"),
        form.querySelector("[data-product-stock]"),
        productContainer?.querySelector("#product-stock"),
        productContainer?.querySelector("[data-product-stock]"),
    ].filter(Boolean);

    stockElements.forEach(function (element) {
        element.textContent =
            isAvailable ? "Available" : "Out of Stock";

        element.classList.toggle("in-stock", isAvailable);
        element.classList.toggle("out-of-stock", !isAvailable);
    });

    const stockBadge =
        productContainer?.querySelector("#product-stock-badge");

    if (stockBadge) {
        stockBadge.textContent =
            isAvailable ? "In Stock" : "Out of Stock";

        stockBadge.classList.toggle(
            "in-stock-quick-view",
            isAvailable
        );

        stockBadge.classList.toggle(
            "out-of-stock-quick-view",
            !isAvailable
        );
    }
}

function updateAddToCartButton(form, isAvailable) {
    const buttons = form.querySelectorAll(
        "[data-add-to-cart], " +
        "[data-buy-now], " +
        ".add-to-cart-button, " +
        ".buy-now-button"
    );

    if (!buttons.length) {
        return;
    }

    const variants = getProductVariants(form);
    const hasVariants = variants.length > 0;

    const optionSelects = Array.from(
        form.querySelectorAll(".product-option-select")
    );

    const allOptionsSelected =
        optionSelects.length > 0 &&
        optionSelects.every(function (select) {
            return String(select.value || "").trim() !== "";
        });

    const variantInput = form.querySelector(
        'input[name="variant_id"], [data-selected-variant]'
    );

    const hasSelectedVariant = Boolean(
        String(variantInput?.value || "").trim()
    );

    buttons.forEach(function (button) {
        const readyText =
            button.dataset.readyText ||
            (
                button.hasAttribute("data-buy-now")
                    ? "Buy Now"
                    : "Add To Cart"
            );

        const buttonText =
            button.querySelector(".button-text") || button;

        if (!isAvailable) {
            button.disabled = true;
            buttonText.textContent = "Out of Stock";
            return;
        }

        if (
            hasVariants &&
            (
                !allOptionsSelected ||
                !hasSelectedVariant
            )
        ) {
            button.disabled = true;
            buttonText.textContent = "Select Options";
            return;
        }

        button.disabled = false;
        buttonText.textContent = readyText;
    });
}

/*
|--------------------------------------------------------------------------
| Product quantity
|--------------------------------------------------------------------------
*/

function initProductQuantity() {
    initQuantityInside(document);
}

function initQuantityInside(container) {
    const quantityWrappers = container.querySelectorAll(
        ".quantity-wrapper, .product-quantity, [data-quantity-wrapper]"
    );

    if (!quantityWrappers.length) {
        return;
    }

    quantityWrappers.forEach(function (wrapper) {
        if (wrapper.dataset.quantityInitialized === "true") {
            return;
        }

        wrapper.dataset.quantityInitialized = "true";

        const input = wrapper.querySelector(
            'input[type="number"], .quantity-input'
        );

        const decreaseButton = wrapper.querySelector(
            ".quantity-minus, [data-quantity-minus]"
        );

        const increaseButton = wrapper.querySelector(
            ".quantity-plus, [data-quantity-plus]"
        );

        if (!input) {
            return;
        }

        const minimum = Number(input.min || 1);
        const maximum = Number(
            input.max || wrapper.dataset.max || Infinity
        );

        if (decreaseButton) {
            decreaseButton.addEventListener("click", function () {
                const currentValue = Number(input.value || minimum);
                const newValue = Math.max(
                    minimum,
                    currentValue - 1
                );

                input.value = newValue;
                input.dispatchEvent(
                    new Event("change", {
                        bubbles: true,
                    })
                );
            });
        }

        if (increaseButton) {
            increaseButton.addEventListener("click", function () {
                const currentValue = Number(input.value || minimum);
                const newValue = Math.min(
                    maximum,
                    currentValue + 1
                );

                input.value = newValue;
                input.dispatchEvent(
                    new Event("change", {
                        bubbles: true,
                    })
                );
            });
        }

        input.addEventListener("change", function () {
            let value = Number(input.value || minimum);

            if (value < minimum) {
                value = minimum;
            }

            if (value > maximum) {
                value = maximum;
            }

            input.value = value;
        });
    });
}

/*
|--------------------------------------------------------------------------
| AJAX Add To Cart + product-page selected items + action popup
|--------------------------------------------------------------------------
*/

function initAjaxAddToCart() {
    document.addEventListener("submit", async function (event) {
        const form = event.target.closest(
            'form[action*="cart/add"], #add-to-cart-form'
        );

        if (!form) {
            return;
        }

        /*
         * Existing variant validation runs before this handler. If it has
         * already rejected the submission, do not send anything.
         */
        if (event.defaultPrevented) {
            return;
        }

        const submitter = event.submitter;

        /*
         * Buy Now keeps its normal Laravel submission so it can continue
         * directly to checkout. Only normal Add To Cart becomes AJAX.
         */
        if (
            submitter &&
            (
                submitter.hasAttribute("data-buy-now") ||
                submitter.classList.contains("buy-now-button") ||
                String(submitter.name || "") === "buy_now"
            )
        ) {
            return;
        }

        const addButton =
            submitter?.matches(
                "[data-add-to-cart], .add-to-cart-button, .quick-view-cart-button"
            )
                ? submitter
                : form.querySelector(
                    "[data-add-to-cart], .add-to-cart-button, .quick-view-cart-button"
                );

        if (!addButton) {
            return;
        }

        event.preventDefault();

        if (addButton.disabled) {
            return;
        }

        const originalButtonHtml = addButton.innerHTML;
        const buttonText =
            addButton.querySelector(".button-text") || addButton;

        addButton.disabled = true;
        addButton.setAttribute("aria-busy", "true");

        if (buttonText === addButton) {
            addButton.textContent = "Adding...";
        } else {
            buttonText.textContent = "Adding...";
        }

        try {
            const response = await fetch(form.action, {
                method: (form.method || "POST").toUpperCase(),
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: new FormData(form),
                credentials: "same-origin",
            });

            let data = {};

            try {
                data = await response.json();
            } catch (jsonError) {
                data = {};
            }

            if (!response.ok || data.success === false) {
                const validationMessage =
                    data.message ||
                    Object.values(data.errors || {})
                        .flat()
                        .filter(Boolean)[0] ||
                    "The product could not be added to your cart.";

                throw new Error(validationMessage);
            }

            updateHeaderCartCount(data.cart_count);

            const productId = String(
                form.querySelector('input[name="product_id"]')?.value || ""
            );

            renderProductAddedItems(
                form,
                data.cart || {},
                productId
            );

            showCartActionPopup({
                type: "success",
                title: "Added to your cart",
                message:
                    data.message ||
                    "Your selected item has been added successfully.",
                form: form,
            });
        } catch (error) {
            showCartActionPopup({
                type: "error",
                title: "Unable to add item",
                message:
                    error?.message ||
                    "Something went wrong while adding this item to your cart.",
                form: form,
            });
        } finally {
            addButton.removeAttribute("aria-busy");
            addButton.innerHTML = originalButtonHtml;

            /*
             * Recalculate the button after restoring its original markup so
             * existing variant/stock rules remain the authority.
             */
            const variantInput = form.querySelector(
                'input[name="variant_id"], [data-selected-variant]'
            );

            if (variantInput && String(variantInput.value || "").trim()) {
                const variants = getProductVariants(form);
                const selectedVariant = variants.find(function (variant) {
                    return String(variant.id) ===
                        String(variantInput.value);
                });

                updateAddToCartButton(
                    form,
                    selectedVariant
                        ? getVariantAvailability(selectedVariant)
                        : false
                );
            } else {
                const variants = getProductVariants(form);

                if (variants.length) {
                    const anyAvailable = variants.some(function (variant) {
                        return getVariantAvailability(variant);
                    });

                    updateAddToCartButton(form, anyAvailable);
                } else {
                    addButton.disabled = false;
                }
            }
        }
    });
}

function updateHeaderCartCount(count) {
    const numericCount = Math.max(
        0,
        Number(count || 0)
    );

    document
        .querySelectorAll(
            "[data-header-cart-count], .cart-count, [data-cart-count]"
        )
        .forEach(function (badge) {
            badge.textContent = String(numericCount);
            badge.classList.toggle(
                "is-empty",
                numericCount < 1
            );
            badge.setAttribute(
                "aria-label",
                numericCount + " items in cart"
            );
        });
}

function getCartItemsFromResponse(cart) {
    if (Array.isArray(cart)) {
        return cart;
    }

    if (cart && typeof cart === "object") {
        return Object.values(cart);
    }

    return [];
}

function getCartOptionText(options) {
    if (!options) {
        return "";
    }

    if (Array.isArray(options)) {
        return options
            .map(function (option) {
                if (typeof option === "string") {
                    return option;
                }

                if (!option || typeof option !== "object") {
                    return "";
                }

                return (
                    option.value_label ||
                    option.value ||
                    option.label ||
                    option.name ||
                    ""
                );
            })
            .filter(Boolean)
            .join(" · ");
    }

    if (typeof options === "object") {
        return Object.values(options)
            .map(function (option) {
                if (typeof option === "string") {
                    return option;
                }

                if (!option || typeof option !== "object") {
                    return "";
                }

                return (
                    option.value_label ||
                    option.value ||
                    option.label ||
                    option.name ||
                    ""
                );
            })
            .filter(Boolean)
            .join(" · ");
    }

    return "";
}

function renderProductAddedItems(form, cart, productId) {
    if (!form || !productId) {
        return;
    }

    const items = getCartItemsFromResponse(cart)
        .filter(function (item) {
            return String(item?.product_id || "") === productId;
        });

    let panel = form.parentElement?.querySelector(
        "[data-product-added-items]"
    );

    if (!panel) {
        panel = document.createElement("section");
        panel.className = "product-added-items";
        panel.setAttribute("data-product-added-items", "");
        panel.setAttribute("aria-live", "polite");

        panel.innerHTML =
            '<div class="product-added-items-heading">' +
                '<span class="product-added-items-kicker">ADDED TO CART</span>' +
                '<strong>Selected Variations</strong>' +
            "</div>" +
            '<div class="product-added-items-list" data-product-added-items-list></div>';

        form.insertAdjacentElement("afterend", panel);
    }

    const list = panel.querySelector(
        "[data-product-added-items-list]"
    );

    if (!list) {
        return;
    }

    list.innerHTML = "";

    items.forEach(function (item) {
        const row = document.createElement("div");
        row.className = "product-added-item";

        const optionText =
            getCartOptionText(item.options) ||
            "Standard option";

        const quantity = Math.max(
            1,
            Number(item.quantity || 1)
        );

        const check = document.createElement("span");
        check.className = "product-added-item-check";
        check.setAttribute("aria-hidden", "true");
        check.innerHTML = '<i class="fa-solid fa-check"></i>';

        const variation = document.createElement("span");
        variation.className = "product-added-item-variation";
        variation.textContent = optionText;

        const quantityBadge = document.createElement("span");
        quantityBadge.className = "product-added-item-quantity";
        quantityBadge.textContent = String(quantity);
        quantityBadge.setAttribute(
            "aria-label",
            "Quantity " + quantity
        );

        row.appendChild(check);
        row.appendChild(variation);
        row.appendChild(quantityBadge);

        list.appendChild(row);
    });

    panel.hidden = items.length === 0;

    ensureProductCartUiStyles();
}

function getCartActionUrls() {
    const cartLink = document.querySelector(
        '[data-header-cart-trigger], a[href$="/cart"], a[href*="/cart?"]'
    );

    const cartUrl =
        cartLink?.href ||
        new URL("cart", window.location.href).href;

    const checkoutLink = document.querySelector(
        'a[href$="/checkout"], a[href*="/checkout?"]'
    );

    let checkoutUrl = checkoutLink?.href || "";

    if (!checkoutUrl) {
        try {
            const url = new URL(cartUrl);
            url.pathname = url.pathname.replace(
                /\/cart\/?$/,
                "/checkout"
            );
            checkoutUrl = url.href;
        } catch (error) {
            checkoutUrl = new URL(
                "checkout",
                window.location.href
            ).href;
        }
    }

    return {
        cartUrl: cartUrl,
        checkoutUrl: checkoutUrl,
    };
}

function showCartActionPopup(options = {}) {
    ensureProductCartUiStyles();

    let popup = document.querySelector(
        "[data-cart-action-popup]"
    );

    if (!popup) {
        popup = document.createElement("div");
        popup.className = "cart-action-popup";
        popup.setAttribute("data-cart-action-popup", "");
        popup.setAttribute("aria-hidden", "true");

        popup.innerHTML =
            '<div class="cart-action-popup-backdrop" data-cart-popup-close></div>' +
            '<div class="cart-action-popup-dialog" role="dialog" aria-modal="true" aria-labelledby="cart-action-popup-title">' +
                '<button type="button" class="cart-action-popup-close" data-cart-popup-close aria-label="Close">' +
                    '<i class="fa-solid fa-xmark" aria-hidden="true"></i>' +
                "</button>" +
                '<div class="cart-action-popup-icon" data-cart-popup-icon></div>' +
                '<div class="cart-action-popup-copy">' +
                    '<span class="cart-action-popup-kicker">ARIZONA OUTFITS</span>' +
                    '<h3 id="cart-action-popup-title" data-cart-popup-title></h3>' +
                    '<p data-cart-popup-message></p>' +
                "</div>" +
                '<div class="cart-action-popup-actions" data-cart-popup-actions></div>' +
            "</div>";

        document.body.appendChild(popup);

        popup.addEventListener("click", function (event) {
            if (event.target.closest("[data-cart-popup-close]")) {
                closeCartActionPopup();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (
                event.key === "Escape" &&
                popup.classList.contains("is-open")
            ) {
                closeCartActionPopup();
            }
        });
    }

    const type =
        options.type === "error"
            ? "error"
            : "success";

    const title = popup.querySelector(
        "[data-cart-popup-title]"
    );

    const message = popup.querySelector(
        "[data-cart-popup-message]"
    );

    const icon = popup.querySelector(
        "[data-cart-popup-icon]"
    );

    const actions = popup.querySelector(
        "[data-cart-popup-actions]"
    );

    popup.dataset.type = type;

    if (title) {
        title.textContent =
            options.title ||
            (type === "success"
                ? "Added to your cart"
                : "Unable to add item");
    }

    if (message) {
        message.textContent = options.message || "";
    }

    if (icon) {
        icon.innerHTML =
            type === "success"
                ? '<i class="fa-solid fa-check" aria-hidden="true"></i>'
                : '<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>';
    }

    if (actions) {
        actions.innerHTML = "";

        if (type === "success") {
            const urls = getCartActionUrls();

            const addAnother = document.createElement("button");
            addAnother.type = "button";
            addAnother.className =
                "cart-action-popup-button cart-action-popup-button-secondary";
            addAnother.textContent = "Add Another Variation";

            addAnother.addEventListener("click", function () {
                closeCartActionPopup();

                const firstOption =
                    options.form?.querySelector(
                        ".product-option-select"
                    );

                const optionsArea =
                    firstOption?.closest(
                        ".product-options, .product-variations, [data-product-options]"
                    ) || firstOption;

                optionsArea?.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });

                firstOption?.focus();
            });

            const viewCart = document.createElement("a");
            viewCart.className =
                "cart-action-popup-button cart-action-popup-button-secondary";
            viewCart.href = urls.cartUrl;
            viewCart.textContent = "View Cart";

            const checkout = document.createElement("a");
            checkout.className =
                "cart-action-popup-button cart-action-popup-button-primary";
            checkout.href = urls.checkoutUrl;
            checkout.textContent = "Checkout";

            actions.appendChild(addAnother);
            actions.appendChild(viewCart);
            actions.appendChild(checkout);
        } else {
            const close = document.createElement("button");
            close.type = "button";
            close.className =
                "cart-action-popup-button cart-action-popup-button-primary";
            close.textContent = "Close";
            close.addEventListener(
                "click",
                closeCartActionPopup
            );

            actions.appendChild(close);
        }
    }

    popup.classList.add("is-open");
    popup.setAttribute("aria-hidden", "false");
    document.body.classList.add("cart-action-popup-open");

    window.setTimeout(function () {
        popup
            .querySelector(
                ".cart-action-popup-button, .cart-action-popup-close"
            )
            ?.focus();
    }, 30);
}

function closeCartActionPopup() {
    const popup = document.querySelector(
        "[data-cart-action-popup]"
    );

    if (!popup) {
        return;
    }

    const shouldReload =
        popup.dataset.reloadOnClose === "true";

    popup.classList.remove("is-open");
    popup.setAttribute("aria-hidden", "true");
    document.body.classList.remove("cart-action-popup-open");

    delete popup.dataset.reloadOnClose;

    if (shouldReload) {
        window.location.reload();
    }
}

function ensureProductCartUiStyles() {
    if (document.getElementById("product-cart-ajax-ui-styles")) {
        return;
    }

    const style = document.createElement("style");
    style.id = "product-cart-ajax-ui-styles";

    style.textContent = `
        .product-added-items {
            margin-top: 14px;
            padding: 13px 14px;
            border: 1px solid rgba(17, 17, 17, .14);
            border-radius: 9px;
            background: rgba(255, 255, 255, .96);
            color: #151515;
        }

        .product-added-items-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .product-added-items-heading strong {
            font-size: 14px;
            font-weight: 700;
        }

        .product-added-items-kicker,
        .cart-action-popup-kicker {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            opacity: .58;
        }

        .product-added-items-list {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .product-added-item {
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 7px 5px 9px;
            border: 1px solid rgba(17, 17, 17, .14);
            border-radius: 999px;
            background: #f7f7f7;
        }

        .product-added-item-variation {
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }

        .product-added-item-quantity {
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #171717;
            color: #ffffff;
            font-size: 8px;
            font-weight: 800;
            line-height: 1;
        }

        .product-added-item-check {
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 18px;
            border-radius: 50%;
            background: #e9f8ef;
            color: #18864b;
            font-size: 8px;
        }

        body.cart-action-popup-open {
            overflow: hidden;
        }

        .cart-action-popup {
            position: fixed;
            inset: 0;
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 22px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .2s ease, visibility .2s ease;
        }

        .cart-action-popup.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .cart-action-popup-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(10, 10, 12, .64);
            backdrop-filter: blur(5px);
        }

        .cart-action-popup-dialog {
            width: min(470px, 100%);
            position: relative;
            z-index: 1;
            padding: 28px;
            border-radius: 18px;
            background: #ffffff;
            color: #171717;
            box-shadow: 0 28px 80px rgba(0, 0, 0, .28);
            transform: translateY(12px) scale(.98);
            transition: transform .2s ease;
        }

        .cart-action-popup.is-open .cart-action-popup-dialog {
            transform: translateY(0) scale(1);
        }

        .cart-action-popup-close {
            width: 34px;
            height: 34px;
            position: absolute;
            top: 14px;
            right: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            background: #f1f1f1;
            color: #171717;
            cursor: pointer;
        }

        .cart-action-popup-icon {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            border-radius: 50%;
            background: #e9f8ef;
            color: #18864b;
            font-size: 18px;
        }

        .cart-action-popup[data-type="error"] .cart-action-popup-icon {
            background: #fff0f0;
            color: #c83232;
        }

        .cart-action-popup-copy h3 {
            margin: 7px 0 8px;
            font-size: 22px;
            line-height: 1.2;
        }

        .cart-action-popup-copy p {
            margin: 0;
            font-size: 13px;
            line-height: 1.65;
            color: #666;
        }

        .cart-action-popup-actions {
            display: grid;
            grid-template-columns: 1.35fr 1fr 1fr;
            gap: 8px;
            margin-top: 22px;
        }

        .cart-action-popup-button {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 12px;
            border: 1px solid #171717;
            border-radius: 9px;
            font: inherit;
            font-size: 10px;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: transform .16s ease, background .16s ease, color .16s ease;
        }

        .cart-action-popup-button:hover,
        .cart-action-popup-button:focus-visible {
            transform: translateY(-1px);
            outline: none;
        }

        .cart-action-popup-button-secondary {
            background: #ffffff;
            color: #171717;
        }

        .cart-action-popup-button-secondary:hover,
        .cart-action-popup-button-secondary:focus-visible {
            background: #f4f4f4;
            color: #171717;
        }

        .cart-action-popup-button-primary {
            background: #171717;
            color: #ffffff;
        }

        .cart-action-popup-button-primary:hover,
        .cart-action-popup-button-primary:focus-visible {
            background: #303030;
            color: #ffffff;
        }

        @media (max-width: 600px) {
            .cart-action-popup {
                align-items: flex-end;
                padding: 12px;
            }

            .cart-action-popup-dialog {
                width: 100%;
                padding: 24px 18px 18px;
                border-radius: 18px;
            }

            .cart-action-popup-actions {
                grid-template-columns: 1fr;
            }

            .cart-action-popup-button {
                width: 100%;
            }

            .product-added-items-list {
                gap: 6px;
            }

            .product-added-item {
                max-width: 100%;
            }

            .product-added-item-variation {
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }
    `;

    document.head.appendChild(style);
}

/*
|--------------------------------------------------------------------------
| Cart quantity update
|--------------------------------------------------------------------------
*/

function initCartQuantity() {
    const cartContainer = document.querySelector(
        ".cart-page, .cart-container, [data-cart-container]"
    );

    if (!cartContainer) {
        return;
    }

    let updateTimer = null;

    function normalizeCartQuantityInput(input, showFeedback = false) {
        const minimum = Math.max(1, Number(input.min || 1));
        const parsedMaximum = Number(input.max);
        const maximum =
            Number.isFinite(parsedMaximum) && parsedMaximum >= minimum
                ? parsedMaximum
                : Infinity;

        let rawValue = String(input.value || "")
            .replace(/[^\d]/g, "");

        let quantity = Math.floor(Number(rawValue || minimum));

        if (!Number.isFinite(quantity) || quantity < minimum) {
            quantity = minimum;
        }

        if (quantity > maximum) {
            quantity = maximum;

            if (showFeedback && Number.isFinite(maximum)) {
                showCartActionPopup({
                    type: "error",
                    title: "Stock limit reached",
                    message:
                        "Only " +
                        maximum +
                        " item" +
                        (maximum === 1 ? "" : "s") +
                        " currently available.",
                });
            }
        }

        input.value = quantity;

        updateCartQuantityButtons(input);

        return quantity;
    }

    cartContainer.addEventListener("input", function (event) {
        const quantityInput = event.target.closest(
            ".cart-quantity-input, " +
            'input[name^="quantities"], ' +
            "[data-cart-quantity]"
        );

        if (!quantityInput) {
            return;
        }

        /*
         * Quantity is genuinely numeric: strip letters, signs,
         * decimals, spaces and other irrelevant characters.
         */
        const digitsOnly = String(quantityInput.value || "")
            .replace(/[^\d]/g, "");

        if (quantityInput.value !== digitsOnly) {
            quantityInput.value = digitsOnly;
        }
    });

    cartContainer.addEventListener("change", function (event) {
        const quantityInput = event.target.closest(
            ".cart-quantity-input, " +
            'input[name^="quantities"], ' +
            "[data-cart-quantity]"
        );

        if (!quantityInput) {
            return;
        }

        normalizeCartQuantityInput(quantityInput, true);

        clearTimeout(updateTimer);

        updateTimer = setTimeout(function () {
            updateCartItemQuantity(quantityInput);
        }, 250);
    });

    cartContainer.addEventListener("click", function (event) {
        const decreaseButton = event.target.closest(
            ".cart-quantity-minus"
        );

        const increaseButton = event.target.closest(
            ".cart-quantity-plus"
        );

        const button = decreaseButton || increaseButton;

        if (!button) {
            return;
        }

        event.preventDefault();

        if (button.disabled) {
            return;
        }

        const item = button.closest(
            ".cart-item, [data-cart-item]"
        );

        if (!item) {
            return;
        }

        const input = item.querySelector(
            ".cart-quantity-input, [data-cart-quantity]"
        );

        if (!input || input.disabled) {
            return;
        }

        const minimum = Math.max(1, Number(input.min || 1));
        const parsedMaximum = Number(input.max);
        const maximum =
            Number.isFinite(parsedMaximum) && parsedMaximum >= minimum
                ? parsedMaximum
                : Infinity;

        const currentValue = normalizeCartQuantityInput(input);

        if (decreaseButton) {
            input.value = Math.max(
                minimum,
                currentValue - 1
            );
        }

        if (increaseButton) {
            if (currentValue >= maximum) {
                if (Number.isFinite(maximum)) {
                    showCartActionPopup({
                        type: "error",
                        title: "Stock limit reached",
                        message:
                            "Only " +
                            maximum +
                            " item" +
                            (maximum === 1 ? "" : "s") +
                            " currently available.",
                    });
                }

                updateCartQuantityButtons(input);
                return;
            }

            input.value = Math.min(
                maximum,
                currentValue + 1
            );
        }

        updateCartQuantityButtons(input);

        input.dispatchEvent(
            new Event("change", {
                bubbles: true,
            })
        );
    });

    cartContainer
        .querySelectorAll(
            ".cart-quantity-input, " +
            'input[name^="quantities"], ' +
            "[data-cart-quantity]"
        )
        .forEach(function (input) {
            normalizeCartQuantityInput(input);
        });
}

function updateCartQuantityButtons(input) {
    const cartItem = input.closest(
        ".cart-item, [data-cart-item]"
    );

    if (!cartItem) {
        return;
    }

    const quantity = Math.max(
        1,
        Math.floor(Number(input.value || 1))
    );

    const minimum = Math.max(1, Number(input.min || 1));
    const parsedMaximum = Number(input.max);
    const maximum =
        Number.isFinite(parsedMaximum) && parsedMaximum >= minimum
            ? parsedMaximum
            : Infinity;

    const minusButton = cartItem.querySelector(
        ".cart-quantity-minus"
    );

    const plusButton = cartItem.querySelector(
        ".cart-quantity-plus"
    );

    if (minusButton) {
        minusButton.disabled =
            input.disabled || quantity <= minimum;
    }

    if (plusButton) {
        plusButton.disabled =
            input.disabled || quantity >= maximum;
    }
}

function updateCartItemQuantity(input) {
    const cartItem = input.closest(
        ".cart-item, [data-cart-item]"
    );

    if (!cartItem) {
        return;
    }

    const updateUrl =
        input.dataset.updateUrl ||
        cartItem.dataset.updateUrl ||
        document.querySelector("[data-cart-update-url]")?.dataset
            .cartUpdateUrl;

    const cartKey =
        input.dataset.cartKey ||
        cartItem.dataset.cartKey ||
        input.name.match(/\[(.*?)\]/)?.[1];

    const minimum = Math.max(1, Number(input.min || 1));
    const parsedMaximum = Number(input.max);
    const maximum =
        Number.isFinite(parsedMaximum) && parsedMaximum >= minimum
            ? parsedMaximum
            : Infinity;

    let quantity = Math.floor(Number(input.value || minimum));

    if (!Number.isFinite(quantity) || quantity < minimum) {
        quantity = minimum;
    }

    if (quantity > maximum) {
        quantity = maximum;
    }

    input.value = quantity;

    if (!updateUrl || !cartKey) {
        const form = input.closest("form");

        if (form) {
            form.submit();
        }

        return;
    }

    input.disabled = true;
    cartItem.classList.add("updating");
    updateCartQuantityButtons(input);

    fetch(updateUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
        body: JSON.stringify({
            cart_key: cartKey,
            quantity: quantity,
        }),
    })
        .then(async function (response) {
            const data = await response.json().catch(function () {
                return {};
            });

            if (!response.ok || data.success === false) {
                const validationMessage =
                    data.errors?.quantity?.[0] ||
                    data.errors?.cart_key?.[0];

                throw new Error(
                    data.message ||
                    validationMessage ||
                    "Unable to update cart."
                );
            }

            return data;
        })
        .then(function (data) {
            const confirmedQuantity = Math.max(
                minimum,
                Math.floor(
                    Number(data.quantity || quantity)
                )
            );

            input.value = Math.min(
                maximum,
                confirmedQuantity
            );

            updateCartTotals(data, cartItem);
            updateHeaderCartCount(data.cart_count);
        })
        .catch(function (error) {
            console.error(error);

            showCartActionPopup({
                type: "error",
                title: "Unable to update quantity",
                message:
                    error?.message ||
                    "Please check the available stock and try again.",
            });

            /*
             * Return to the exact server cart state after a rejected
             * quantity. This prevents the browser from displaying a
             * quantity that the backend did not accept.
             */
            const popup = document.querySelector(
                "[data-cart-action-popup]"
            );

            if (popup) {
                popup.dataset.reloadOnClose = "true";
            }
        })
        .finally(function () {
            input.disabled = false;
            cartItem.classList.remove("updating");
            updateCartQuantityButtons(input);
        });
}

function updateCartTotals(data, cartItem) {
    if (!data || typeof data !== "object") {
        return;
    }

    const currencySymbol =
        data.currency_symbol ||
        document.body.dataset.currencySymbol ||
        "$";

    const itemSubtotalElement = cartItem.querySelector(
        ".cart-item-subtotal, [data-item-subtotal]"
    );

    if (
        itemSubtotalElement &&
        data.item_subtotal !== undefined
    ) {
        itemSubtotalElement.textContent = formatPrice(
            data.item_subtotal,
            currencySymbol
        );
    }

    const subtotalElement = document.querySelector(
        ".cart-subtotal, [data-cart-subtotal]"
    );

    if (
        subtotalElement &&
        data.subtotal !== undefined
    ) {
        subtotalElement.textContent = formatPrice(
            data.subtotal,
            currencySymbol
        );
    }

    const discountElement = document.querySelector(
        ".cart-discount, [data-cart-discount]"
    );

    if (
        discountElement &&
        data.discount !== undefined
    ) {
        discountElement.textContent = formatPrice(
            data.discount,
            currencySymbol
        );
    }

    const totalElement = document.querySelector(
        ".cart-total, [data-cart-total]"
    );

    if (totalElement && data.total !== undefined) {
        totalElement.textContent = formatPrice(
            data.total,
            currencySymbol
        );
    }

    const cartCountElements = document.querySelectorAll(
        ".cart-count, [data-cart-count]"
    );

    if (data.cart_count !== undefined) {
        cartCountElements.forEach(function (element) {
            element.textContent = data.cart_count;
        });
    }
}

/*
|--------------------------------------------------------------------------
| Cart coupon AJAX + ArizonaOutfits popup
|--------------------------------------------------------------------------
*/

function initCartCoupon() {
    document.addEventListener("submit", function (event) {
        const form = event.target.closest(
            ".coupon-form, form[data-cart-coupon-apply], form[data-cart-coupon-remove]"
        );

        if (!form) {
            return;
        }

        const isApplyForm =
            form.matches(
                ".coupon-form, form[data-cart-coupon-apply]"
            );

        const isRemoveForm =
            form.matches(
                "form[data-cart-coupon-remove]"
            ) ||
            !!form.querySelector(
                ".remove-coupon-button"
            );

        if (!isApplyForm && !isRemoveForm) {
            return;
        }

        event.preventDefault();

        const submitButton = form.querySelector(
            'button[type="submit"], input[type="submit"]'
        );

        const couponInput = form.querySelector(
            'input[name="coupon_code"]'
        );

        if (isApplyForm && couponInput) {
            const cleanedCode =
                String(couponInput.value || "")
                    .trim()
                    .toUpperCase();

            couponInput.value = cleanedCode;

            if (!cleanedCode) {
                showCartActionPopup({
                    type: "error",
                    title: "Enter a coupon code",
                    message:
                        "Please enter a coupon code before applying it.",
                });

                couponInput.focus();
                return;
            }

            if (cleanedCode.length > 100) {
                showCartActionPopup({
                    type: "error",
                    title: "Coupon code is too long",
                    message:
                        "Coupon codes can contain up to 100 characters.",
                });

                couponInput.focus();
                return;
            }
        }

        if (submitButton) {
            submitButton.disabled = true;
        }

        const formData = new FormData(form);

        fetch(form.action, {
            method: form.method.toUpperCase() || "POST",
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            body: formData,
        })
            .then(async function (response) {
                const data = await response.json().catch(
                    function () {
                        return {};
                    }
                );

                if (!response.ok || data.success === false) {
                    const firstValidationMessage =
                        data.errors?.coupon_code?.[0];

                    throw new Error(
                        data.message ||
                        firstValidationMessage ||
                        "Unable to update the coupon."
                    );
                }

                return data;
            })
            .then(function (data) {
                updateCartTotals(
                    data,
                    document.createElement("div")
                );

                updateHeaderCartCount(data.cart_count);

                if (isApplyForm) {
                    showCartActionPopup({
                        type: "success",
                        title: "Coupon applied",
                        message:
                            data.message ||
                            "Your coupon was applied successfully.",
                    });
                } else {
                    showCartActionPopup({
                        type: "success",
                        title: "Coupon removed",
                        message:
                            data.message ||
                            "Your coupon was removed successfully.",
                    });
                }

                /*
                 * The server remains the authority for coupon state.
                 * Reload after the user closes the popup so the coupon
                 * form/applied-coupon panel and conditional discount row
                 * are rebuilt from the current Laravel session.
                 */
                const popup = document.querySelector(
                    "[data-cart-action-popup]"
                );

                if (popup) {
                    popup.dataset.reloadOnClose = "true";
                }
            })
            .catch(function (error) {
                console.error(error);

                showCartActionPopup({
                    type: "error",
                    title: isApplyForm
                        ? "Coupon not applied"
                        : "Unable to remove coupon",
                    message:
                        error?.message ||
                        "Please check the coupon and try again.",
                });
            })
            .finally(function () {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            });
    });

    /*
     * Coupon codes are case-insensitive in the controller.
     * Keep the field visually consistent while typing.
     */
    document.addEventListener("input", function (event) {
        const input = event.target.closest(
            '.coupon-form input[name="coupon_code"]'
        );

        if (!input) {
            return;
        }

        input.value = input.value
            .replace(/[\r\n\t]/g, "")
            .toUpperCase();
    });
}

/*
|--------------------------------------------------------------------------
| Remove cart item
|--------------------------------------------------------------------------
*/

function initCartRemove() {
    document.addEventListener("click", function (event) {
        const removeButton = event.target.closest(
            ".cart-remove-button, [data-remove-cart-item]"
        );

        if (!removeButton) {
            return;
        }

        const form = removeButton.closest("form");
        const removeUrl =
            removeButton.dataset.removeUrl ||
            form?.getAttribute("action");

        const cartItem = removeButton.closest(
            ".cart-item, [data-cart-item]"
        );

        const cartKey =
            removeButton.dataset.cartKey ||
            cartItem?.dataset.cartKey ||
            form?.querySelector(
                'input[name="cart_key"], input[name="key"]'
            )?.value;

        if (!removeUrl || !cartKey) {
            return;
        }

        if (form && !removeButton.dataset.ajax) {
            return;
        }

        event.preventDefault();

        showCartRemoveConfirmation({
            onConfirm: function () {
                performCartItemRemoval({
                    removeButton,
                    form,
                    removeUrl,
                    cartItem,
                    cartKey,
                });
            },
        });
    });
}

function showCartRemoveConfirmation(options = {}) {
    ensureProductCartUiStyles();

    let popup = document.querySelector(
        "[data-cart-action-popup]"
    );

    if (!popup) {
        /*
         * Create the same popup shell used by Add To Cart.
         * showCartActionPopup() owns the shared ArizonaOutfits popup design.
         */
        showCartActionPopup({
            type: "error",
            title: "Remove item?",
            message: "Please confirm this cart action.",
        });

        popup = document.querySelector(
            "[data-cart-action-popup]"
        );
    }

    if (!popup) {
        return;
    }

    const title = popup.querySelector(
        "[data-cart-popup-title]"
    );
    const message = popup.querySelector(
        "[data-cart-popup-message]"
    );
    const icon = popup.querySelector(
        "[data-cart-popup-icon]"
    );
    const actions = popup.querySelector(
        "[data-cart-popup-actions]"
    );

    popup.dataset.type = "error";

    if (title) {
        title.textContent = "Remove from your cart?";
    }

    if (message) {
        message.textContent =
            "This item will be removed from your shopping cart.";
    }

    if (icon) {
        icon.innerHTML =
            '<i class="fa-solid fa-trash-can" aria-hidden="true"></i>';
    }

    if (actions) {
        actions.innerHTML = "";

        const cancelButton = document.createElement("button");
        cancelButton.type = "button";
        cancelButton.className =
            "cart-action-popup-button cart-action-popup-button-secondary";
        cancelButton.textContent = "Keep Item";
        cancelButton.addEventListener(
            "click",
            closeCartActionPopup
        );

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.className =
            "cart-action-popup-button cart-action-popup-button-primary";
        removeButton.textContent = "Remove Item";

        removeButton.addEventListener("click", function () {
            closeCartActionPopup();

            if (typeof options.onConfirm === "function") {
                options.onConfirm();
            }
        });

        actions.appendChild(cancelButton);
        actions.appendChild(removeButton);
    }

    popup.classList.add("is-open");
    popup.setAttribute("aria-hidden", "false");
    document.body.classList.add("cart-action-popup-open");

    window.setTimeout(function () {
        actions
            ?.querySelector(
                ".cart-action-popup-button-secondary"
            )
            ?.focus();
    }, 30);
}

function performCartItemRemoval({
    removeButton,
    form,
    removeUrl,
    cartItem,
    cartKey,
}) {
    removeButton.disabled = true;

    if (cartItem) {
        cartItem.classList.add("removing");
    }

    fetch(removeUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
        },
        body: JSON.stringify({
            cart_key: cartKey,
        }),
    })
        .then(async function (response) {
            const data = await response.json().catch(function () {
                return {};
            });

            if (!response.ok || data.success === false) {
                throw new Error(
                    data.message ||
                    "Unable to remove cart item."
                );
            }

            return data;
        })
        .then(function (data) {
            if (cartItem) {
                cartItem.remove();
            }

            updateCartTotals(
                data,
                document.createElement("div")
            );

            updateHeaderCartCount(data.cart_count);

            showCartActionPopup({
                type: "success",
                title: "Removed from your cart",
                message:
                    data.message ||
                    "The item was removed successfully.",
            });

            const remainingItems = document.querySelectorAll(
                ".cart-item, [data-cart-item]"
            );

            if (!remainingItems.length) {
                const cartContainer = document.querySelector(
                    ".cart-page, .cart-container, [data-cart-container]"
                );

                const emptyCart = document.querySelector(
                    ".empty-cart, [data-empty-cart]"
                );

                if (cartContainer) {
                    cartContainer.classList.add(
                        "cart-is-empty"
                    );
                }

                showElement(emptyCart);
            }
        })
        .catch(function (error) {
            console.error(error);

            showCartActionPopup({
                type: "error",
                title: "Unable to remove item",
                message:
                    error?.message ||
                    "Something went wrong while removing this item from your cart.",
            });
        })
        .finally(function () {
            removeButton.disabled = false;

            if (cartItem) {
                cartItem.classList.remove("removing");
            }
        });
}


const navCover = document.querySelector(".navigation-cover");
const navbar = document.querySelector(".navbar");

function updateNavbarOnScroll() {
    if (!navCover || !navbar) return;

    const scrollableHeight =
        document.documentElement.scrollHeight - window.innerHeight;

    const fivePercentScroll = scrollableHeight * 0.05;
    const scrollPosition = window.scrollY;

    if (scrollPosition >= fivePercentScroll) {
        navCover.classList.add("active");
        navbar.classList.add("scrolled");
    } else {
        navCover.classList.remove("active");
        navbar.classList.remove("scrolled");
    }
}

window.addEventListener("scroll", updateNavbarOnScroll, {
    passive: true
});

window.addEventListener("resize", updateNavbarOnScroll);

updateNavbarOnScroll();

const button = document.querySelectorAll('.moving-circle');

button.forEach(button => {

    button.addEventListener('mousemove', (e) => {
        const rect = button.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;

        // Adjust intensity here
        const moveX = x / 4;
        const moveY = y / 4;

        button.style.transform =
            `translate3d(${moveX}px, ${moveY}px, 0) scale(1.15)`;
    });

    button.addEventListener('mouseleave', () => {
        button.style.transform =
            'translate3d(0, 0, 0) scale(1)';
    });

});
const buttons = document.querySelectorAll(
    '.btn-style-1, .btn-style-2, .btn-style-3, .product-popup-close, .quick-view-cart-button'
);

buttons.forEach(button => {
    const text = button.querySelector('.button-text');
    if (!text) return;

    button.addEventListener('mousemove', (e) => {
        const rect = button.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;

        text.style.transform =
            `translate3d(${x / 6}px, ${y / 6}px, 0) scale(1.12)`;
    });

    button.addEventListener('mouseleave', () => {
        text.style.transform =
            'translate3d(0, 0, 0) scale(1)';
    });
});

const menuBtn = document.querySelector('.menu-button');
const megaMenu = document.querySelector('.mega-menu');

menuBtn.addEventListener('click', function () {
    megaMenu.classList.toggle('active');
});

//menubutton

function toggleMenu() {
    const menuButton = document.querySelector('.menu-button');
    menuButton.classList.toggle('open');
}

document.addEventListener("DOMContentLoaded", function () {

    const elements = document.querySelectorAll(
        ".blog-posts .about-info-parent, .recent-projects .list-item,.blog-posts .card-parent,.products-grid .card-parent "
    );

    const observer = new IntersectionObserver((entries, observer) => {

        entries.forEach((entry) => {

            if (entry.isIntersecting) {

                // get index of current element
                const index = Array.from(elements).indexOf(entry.target);

                setTimeout(() => {
                    entry.target.classList.add("in-view");
                }, index * 150); // 150ms delay per item

                observer.unobserve(entry.target); // run only once
            }

        });

    }, {
        threshold: 0.2
    });

    elements.forEach(el => observer.observe(el));

});

/*
|--------------------------------------------------------------------------
| Product tabs
|--------------------------------------------------------------------------
*/

function initProductTabs() {
    const productTabsContainers = document.querySelectorAll(".product-tabs");

    if (!productTabsContainers.length) {
        return;
    }

    productTabsContainers.forEach(function (tabsContainer) {
        if (tabsContainer.dataset.tabsInitialized === "true") {
            return;
        }

        tabsContainer.dataset.tabsInitialized = "true";

        const tabButtons = tabsContainer.querySelectorAll(".tab-btn");
        const tabContents = tabsContainer.querySelectorAll(".tab-content");

        if (!tabButtons.length || !tabContents.length) {
            return;
        }

        tabButtons.forEach(function (button) {
            button.setAttribute("role", "tab");

            const tabId = button.dataset.tab;
            const matchingContent = tabId
                ? tabsContainer.querySelector("#" + CSS.escape(tabId))
                : null;

            button.setAttribute(
                "aria-selected",
                button.classList.contains("active") ? "true" : "false"
            );

            if (tabId) {
                button.setAttribute("aria-controls", tabId);
            }

            if (matchingContent) {
                matchingContent.setAttribute("role", "tabpanel");
            }

            button.addEventListener("click", function () {
                const selectedTabId = button.dataset.tab;

                if (!selectedTabId) {
                    return;
                }

                const selectedContent = tabsContainer.querySelector(
                    "#" + CSS.escape(selectedTabId)
                );

                if (!selectedContent) {
                    return;
                }

                tabButtons.forEach(function (tabButton) {
                    tabButton.classList.remove("active");
                    tabButton.setAttribute("aria-selected", "false");
                });

                tabContents.forEach(function (tabContent) {
                    tabContent.classList.remove("active");
                    tabContent.setAttribute("aria-hidden", "true");
                });

                button.classList.add("active");
                button.setAttribute("aria-selected", "true");

                selectedContent.classList.add("active");
                selectedContent.setAttribute("aria-hidden", "false");
            });
        });

        tabContents.forEach(function (tabContent) {
            tabContent.setAttribute(
                "aria-hidden",
                tabContent.classList.contains("active")
                    ? "false"
                    : "true"
            );
        });
    });
}

function initVariantCartProtection() {
    document.addEventListener("submit", function (event) {
        const form = event.target.closest(
            ".product-form, " +
            ".single-product-form, " +
            ".quick-view-cart-form, " +
            "[data-product-form]"
        );

        if (!form) {
            return;
        }

        const variants = getProductVariants(form);

        /*
        |--------------------------------------------------------------------------
        | Simple products can submit normally
        |--------------------------------------------------------------------------
        */

        if (!variants.length) {
            return;
        }

        const optionSelects = Array.from(
            form.querySelectorAll(".product-option-select")
        );

        const missingOption = optionSelects.find(
            function (select) {
                return !String(select.value || "").trim();
            }
        );

        const variantInput = form.querySelector(
            'input[name="variant_id"], [data-selected-variant]'
        );

        const selectedVariantId = String(
            variantInput?.value || ""
        ).trim();

        const message = form.querySelector(
            ".variant-message, [data-variant-message]"
        );

        /*
        |--------------------------------------------------------------------------
        | Every option must be selected
        |--------------------------------------------------------------------------
        */

        if (
            !optionSelects.length ||
            missingOption ||
            !selectedVariantId
        ) {
            event.preventDefault();
            event.stopImmediatePropagation();

            if (variantInput) {
                variantInput.value = "";
            }

            if (message) {
                message.textContent =
                    "Please select one value from every option.";

                message.classList.remove("success");
                message.classList.add("error");

                showElement(message);
            }

            const optionGroup =
                missingOption?.closest(
                    ".product-option-group"
                ) ||
                form.querySelector(".product-option-group");

            optionGroup?.scrollIntoView({
                behavior: "smooth",
                block: "center",
            });

            missingOption?.focus();

            return;
        }

        const selectedVariant = variants.find(
            function (variant) {
                return (
                    String(variant.id) ===
                    selectedVariantId
                );
            }
        );

        if (!selectedVariant) {
            event.preventDefault();
            event.stopImmediatePropagation();

            variantInput.value = "";

            if (message) {
                message.textContent =
                    "The selected option combination is unavailable.";

                message.classList.remove("success");
                message.classList.add("error");

                showElement(message);
            }

            return;
        }

        const selectedOptions = {};

        optionSelects.forEach(function (select) {
            const optionId =
                select.dataset.optionId ||
                select.name.match(/\[(.*?)\]/)?.[1];

            if (optionId) {
                selectedOptions[String(optionId)] =
                    String(select.value);
            }
        });

        const variantOptions =
            normalizeVariantOptions(selectedVariant);

        const exactMatch =
            Object.keys(selectedOptions).length ===
            Object.keys(variantOptions).length &&
            Object.entries(selectedOptions).every(
                function ([optionId, valueId]) {
                    return (
                        String(
                            variantOptions[optionId] || ""
                        ) === String(valueId)
                    );
                }
            );

        if (!exactMatch) {
            event.preventDefault();
            event.stopImmediatePropagation();

            variantInput.value = "";

            if (message) {
                message.textContent =
                    "Please select a valid value from every option.";

                message.classList.remove("success");
                message.classList.add("error");

                showElement(message);
            }

            return;
        }

        const isAvailable =
            getVariantAvailability(selectedVariant);

        if (!isAvailable) {
            event.preventDefault();
            event.stopImmediatePropagation();

            if (message) {
                message.textContent =
                    "This selected option is currently out of stock and cannot be purchased.";

                message.classList.remove("success");
                message.classList.add("error");

                showElement(message);
            }
        }
    }, true);


}

document.addEventListener("DOMContentLoaded", function () {
    const ratingField = document.querySelector(
        ".review-rating-field"
    );

    if (!ratingField) {
        return;
    }

    const ratingInput = ratingField.querySelector(
        'input[name="rating"]'
    );

    const ratingOptions = ratingField.querySelectorAll(
        ".review-rating-option"
    );

    ratingOptions.forEach(function (option) {
        option.addEventListener("click", function () {
            const ratingValue =
                option.dataset.ratingValue || "";

            ratingInput.value = ratingValue;

            ratingOptions.forEach(function (item) {
                item.classList.remove("active");
                item.setAttribute(
                    "aria-pressed",
                    "false"
                );
            });

            option.classList.add("active");
            option.setAttribute(
                "aria-pressed",
                "true"
            );
        });
    });

    const reviewForm = ratingField.closest("form");

    if (reviewForm) {
        reviewForm.addEventListener("submit", function (event) {
            if (!ratingInput.value) {
                event.preventDefault();

                ratingField.classList.add("has-error");

                ratingOptions[0]?.focus();
            } else {
                ratingField.classList.remove("has-error");
            }
        });
    }
});


function reviewRatingField() {
    const reviewSelect = document.querySelector(".review-select");

    if (reviewSelect) {

        const trigger = reviewSelect.querySelector(".review-select-trigger");
        const options = reviewSelect.querySelectorAll(".review-option");
        const hiddenInput = document.getElementById("review-rating");
        const selectedText = document.getElementById("selected-rating-text");

        trigger.addEventListener("click", () => {
            reviewSelect.classList.toggle("active");
        });

        options.forEach(option => {

            option.addEventListener("click", () => {

                options.forEach(o => o.classList.remove("active"));

                option.classList.add("active");

                hiddenInput.value = option.dataset.value;

                selectedText.textContent = option.textContent;

                reviewSelect.classList.remove("active");

            });

        });

        document.addEventListener("click", e => {

            if (!reviewSelect.contains(e.target)) {
                reviewSelect.classList.remove("active");
            }

        });

    }
}

function shopFilter() {
    document.addEventListener("DOMContentLoaded", function () {
        const filterForm = document.getElementById("shop-filter-form");

        if (filterForm) {
            const autoSubmitFields = filterForm.querySelectorAll(
                ".auto-submit-filter"
            );

            let filterSubmitting = false;

            autoSubmitFields.forEach(function (field) {
                field.addEventListener("change", function () {
                    if (filterSubmitting) {
                        return;
                    }

                    filterSubmitting = true;
                    filterForm.submit();
                });
            });
        }

        const sortForm = document.getElementById("sort-form");
        const sortTrigger = document.getElementById("sort-trigger");
        const sortOptionsContainer =
            document.getElementById("sort-options");
        const sortValueInput = document.getElementById("sort-value");
        const sortLabel = document.getElementById("sort-label");

        if (
            !sortForm ||
            !sortTrigger ||
            !sortOptionsContainer ||
            !sortValueInput ||
            !sortLabel
        ) {
            return;
        }

        const sortOptions =
            sortOptionsContainer.querySelectorAll(".sort-option");

        function openSortDropdown() {
            sortOptionsContainer.classList.add("active");
            sortTrigger.classList.add("active");
            sortTrigger.setAttribute("aria-expanded", "true");
        }

        function closeSortDropdown() {
            sortOptionsContainer.classList.remove("active");
            sortTrigger.classList.remove("active");
            sortTrigger.setAttribute("aria-expanded", "false");
        }

        function toggleSortDropdown() {
            if (sortOptionsContainer.classList.contains("active")) {
                closeSortDropdown();
            } else {
                openSortDropdown();
            }
        }

        function selectSortOption(option) {
            const value = option.dataset.value || "";
            const label = option.textContent.trim();

            sortValueInput.value = value;
            sortLabel.textContent = label;

            sortOptions.forEach(function (item) {
                const isSelected = item === option;

                item.classList.toggle("active", isSelected);
                item.setAttribute(
                    "aria-selected",
                    isSelected ? "true" : "false"
                );
            });

            closeSortDropdown();
            sortForm.submit();
        }

        sortTrigger.addEventListener("click", function () {
            toggleSortDropdown();
        });

        sortOptions.forEach(function (option) {
            option.addEventListener("click", function () {
                selectSortOption(option);
            });

            option.addEventListener("keydown", function (event) {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    selectSortOption(option);
                }
            });
        });

        document.addEventListener("click", function (event) {
            if (!sortForm.contains(event.target)) {
                closeSortDropdown();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closeSortDropdown();
            }
        });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const dropdowns = document.querySelectorAll(
        "[data-filter-dropdown]"
    );

    dropdowns.forEach((dropdown) => {
        const trigger = dropdown.querySelector(
            "[data-filter-trigger]"
        );

        const optionsWrapper = dropdown.querySelector(
            "[data-filter-options]"
        );

        const options = dropdown.querySelectorAll(
            "[data-value]"
        );

        const input = dropdown.querySelector(
            "[data-filter-input]"
        );

        const label = dropdown.querySelector(
            "[data-filter-label]"
        );

        if (
            !trigger ||
            !optionsWrapper ||
            !input ||
            !label
        ) {
            return;
        }

        function openDropdown() {
            dropdown.classList.add("active");
            trigger.setAttribute(
                "aria-expanded",
                "true"
            );
        }

        function closeDropdown() {
            dropdown.classList.remove("active");
            trigger.setAttribute(
                "aria-expanded",
                "false"
            );
        }

        function toggleDropdown() {
            if (dropdown.classList.contains("active")) {
                closeDropdown();
            } else {
                closeDropdowns(dropdown);
                openDropdown();
            }
        }

        function selectOption(option) {
            const value = option.dataset.value;
            const optionLabel =
                option.dataset.label
                || option.textContent.trim();

            input.value = value;
            label.textContent = optionLabel;

            options.forEach((item) => {
                const isSelected = item === option;

                item.classList.toggle(
                    "active",
                    isSelected
                );

                item.setAttribute(
                    "aria-selected",
                    isSelected
                        ? "true"
                        : "false"
                );
            });

            closeDropdown();

            input.dispatchEvent(
                new Event("change", {
                    bubbles: true
                })
            );

            const form = dropdown.closest("form");

            if (form) {
                form.requestSubmit();
            }
        }

        trigger.addEventListener(
            "click",
            toggleDropdown
        );

        options.forEach((option) => {
            option.addEventListener("click", () => {
                selectOption(option);
            });

            option.addEventListener(
                "keydown",
                (event) => {
                    if (
                        event.key === "Enter"
                        || event.key === " "
                    ) {
                        event.preventDefault();
                        selectOption(option);
                    }
                }
            );
        });
    });

    function closeDropdowns(exception = null) {
        document
            .querySelectorAll(
                "[data-filter-dropdown].active"
            )
            .forEach((dropdown) => {
                if (dropdown === exception) {
                    return;
                }

                dropdown.classList.remove("active");

                const trigger = dropdown.querySelector(
                    "[data-filter-trigger]"
                );

                trigger?.setAttribute(
                    "aria-expanded",
                    "false"
                );
            });
    }

    document.addEventListener("click", (event) => {
        if (
            !event.target.closest(
                "[data-filter-dropdown]"
            )
        ) {
            closeDropdowns();
        }
    });

    document.addEventListener(
        "keydown",
        (event) => {
            if (event.key === "Escape") {
                closeDropdowns();
            }
        }
    );
});

document.addEventListener("DOMContentLoaded", () => {
    const multiSelects = document.querySelectorAll(
        "[data-multi-select]"
    );

    function closeAllMultiSelects(exception = null) {
        multiSelects.forEach((multiSelect) => {
            if (multiSelect === exception) {
                return;
            }

            multiSelect.classList.remove("active");

            const trigger = multiSelect.querySelector(
                "[data-multi-select-trigger]"
            );

            trigger?.setAttribute(
                "aria-expanded",
                "false"
            );
        });
    }

    multiSelects.forEach((multiSelect) => {
        const trigger = multiSelect.querySelector(
            "[data-multi-select-trigger]"
        );

        const label = multiSelect.querySelector(
            "[data-multi-select-label]"
        );

        const searchInput = multiSelect.querySelector(
            "[data-multi-select-search]"
        );

        const optionElements = Array.from(
            multiSelect.querySelectorAll(
                ".filter-select-option"
            )
        );

        const checkboxes = Array.from(
            multiSelect.querySelectorAll(
                "[data-multi-select-checkbox]"
            )
        );

        const selectAllButton = multiSelect.querySelector(
            "[data-select-all]"
        );

        const clearAllButton = multiSelect.querySelector(
            "[data-clear-all]"
        );

        const emptyMessage = multiSelect.querySelector(
            "[data-multi-select-empty]"
        );

        if (!trigger || !label) {
            return;
        }

        const defaultLabel =
            label.textContent.trim().includes("selected")
                ? null
                : label.textContent.trim();

        function updateLabel() {
            const checkedItems = checkboxes.filter(
                (checkbox) => checkbox.checked
            );

            if (!checkedItems.length) {
                label.textContent =
                    defaultLabel || "Select options";

                return;
            }

            if (checkedItems.length === 1) {
                const selectedOption =
                    checkedItems[0].closest(
                        ".filter-select-option"
                    );

                const selectedLabel =
                    selectedOption?.querySelector(
                        ".filter-option-label"
                    );

                label.textContent =
                    selectedLabel?.textContent.trim()
                    || "1 selected";

                return;
            }

            label.textContent =
                `${checkedItems.length} selected`;
        }

        function filterOptions() {
            if (!searchInput) {
                return;
            }

            const searchValue =
                searchInput.value
                    .trim()
                    .toLowerCase();

            let visibleCount = 0;

            optionElements.forEach((option) => {
                const optionText =
                    option.dataset.searchText
                    || option.textContent
                        .trim()
                        .toLowerCase();

                const matches =
                    optionText.includes(searchValue);

                option.hidden = !matches;

                if (matches) {
                    visibleCount++;
                }
            });

            if (emptyMessage) {
                emptyMessage.hidden =
                    visibleCount !== 0;
            }
        }

        function openDropdown() {
            closeAllMultiSelects(multiSelect);

            multiSelect.classList.add("active");

            trigger.setAttribute(
                "aria-expanded",
                "true"
            );

            window.setTimeout(() => {
                searchInput?.focus();
            }, 50);
        }

        function closeDropdown() {
            multiSelect.classList.remove("active");

            trigger.setAttribute(
                "aria-expanded",
                "false"
            );
        }

        trigger.addEventListener("click", () => {
            if (
                multiSelect.classList.contains("active")
            ) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener(
                "change",
                updateLabel
            );
        });

        searchInput?.addEventListener(
            "input",
            filterOptions
        );

        selectAllButton?.addEventListener(
            "click",
            () => {
                optionElements.forEach((option) => {
                    if (option.hidden) {
                        return;
                    }

                    const checkbox = option.querySelector(
                        "[data-multi-select-checkbox]"
                    );

                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                updateLabel();
            }
        );

        clearAllButton?.addEventListener(
            "click",
            () => {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });

                updateLabel();
            }
        );
    });

    document.addEventListener("click", (event) => {
        if (
            !event.target.closest(
                "[data-multi-select]"
            )
        ) {
            closeAllMultiSelects();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeAllMultiSelects();
        }
    });
});