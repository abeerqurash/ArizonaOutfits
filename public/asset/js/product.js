document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    initCustomSortDropdown();
    initProductCardGallery();
    initProductQuickView();
    initSingleProductGallery();
    initProductOptions();
    initProductQuantity();
    initCartQuantity();
    initCartRemove();
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

                form.querySelectorAll(
                    '[data-option-id="' + optionId + '"]'
                ).forEach(function (item) {
                    item.classList.remove("active");
                    item.setAttribute("aria-pressed", "false");
                });

                button.classList.add("active");
                button.setAttribute("aria-pressed", "true");

                const matchingSelect = form.querySelector(
                    '.product-option-select[data-option-id="' +
                    optionId +
                    '"], ' +
                    '.product-option-select[name="options[' +
                    optionId +
                    ']"]'
                );

                if (matchingSelect) {
                    matchingSelect.value = valueId;

                    matchingSelect.dispatchEvent(
                        new Event("change", {
                            bubbles: true,
                        })
                    );
                }

                const selectedLabel = form.querySelector(
                    '[data-selected-option="' +
                    optionId +
                    '"], ' +
                    '.selected-option-value[data-option-id="' +
                    optionId +
                    '"]'
                );

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

function updateSelectedVariant(form) {
    const variantsElement =
        form.querySelector("[data-product-variants]") ||
        document.querySelector("[data-product-variants]");

    if (!variantsElement) {
        return;
    }

    let variants = [];

    try {
        variants = JSON.parse(
            variantsElement.dataset.productVariants ||
            variantsElement.textContent ||
            "[]"
        );
    } catch (error) {
        console.error("Invalid product variant data.", error);
        return;
    }

    if (!Array.isArray(variants) || !variants.length) {
        return;
    }

    const selectedOptions = {};

    form.querySelectorAll(".product-option-select").forEach(
        function (select) {
            const optionId =
                select.dataset.optionId ||
                select.name.match(/\[(.*?)\]/)?.[1];

            if (optionId && select.value) {
                selectedOptions[String(optionId)] = String(
                    select.value
                );
            }
        }
    );

    const selectedOptionCount = Object.keys(selectedOptions).length;

    if (!selectedOptionCount) {
        return;
    }

    const matchedVariant = variants.find(function (variant) {
        const variantOptions =
            variant.options ||
            variant.option_values ||
            variant.values ||
            {};

        if (Array.isArray(variantOptions)) {
            return Object.entries(selectedOptions).every(
                function ([optionId, valueId]) {
                    return variantOptions.some(function (option) {
                        const currentOptionId = String(
                            option.option_id ||
                            option.product_option_id ||
                            option.id ||
                            ""
                        );

                        const currentValueId = String(
                            option.value_id ||
                            option.option_value_id ||
                            option.product_option_value_id ||
                            option.value ||
                            ""
                        );

                        return (
                            currentOptionId === String(optionId) &&
                            currentValueId === String(valueId)
                        );
                    });
                }
            );
        }

        return Object.entries(selectedOptions).every(function (
            [optionId, valueId]
        ) {
            return String(variantOptions[optionId]) === String(valueId);
        });
    });

    if (!matchedVariant) {
        updateVariantUnavailable(form);
        return;
    }

    updateVariantDisplay(form, matchedVariant);
}

function updateVariantDisplay(form, variant) {
    const variantInput = form.querySelector(
        'input[name="variant_id"]'
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

    const stock = Number(
        variant.stock ??
        variant.quantity ??
        variant.stock_quantity ??
        0
    );

    updateStockDisplay(form, stock);

    const skuElement =
        form.querySelector("[data-product-sku], .product-sku-value") ||
        document.querySelector("[data-product-sku]");

    if (skuElement && variant.sku) {
        skuElement.textContent = variant.sku;
    }

    const mainImage =
        document.querySelector(
            ".single-product-main-image, .product-main-image"
        ) ||
        form.closest(".quick-view-product")?.querySelector(
            ".quick-view-main-image"
        );

    const variantImage =
        variant.image_url ||
        variant.image ||
        variant.featured_image;

    if (mainImage && variantImage) {
        mainImage.src = variantImage;
    }

    updateAddToCartButton(form, stock > 0);
}

function updateVariantUnavailable(form) {
    const stockElement =
        form.querySelector(
            "#product-stock, " +
            "#live-stock-count, " +
            "[data-product-stock]"
        ) ||
        document.querySelector("[data-product-stock]");

    if (stockElement) {
        stockElement.textContent = "Unavailable";
        stockElement.classList.remove("in-stock");
        stockElement.classList.add("out-of-stock");
    }

    updateAddToCartButton(form, false);
}

function updateStockDisplay(form, stock) {
    const stockElements = [
        form.querySelector("#product-stock"),
        form.querySelector("#live-stock-count"),
        form.querySelector("[data-product-stock]"),
        document.querySelector("#product-stock"),
        document.querySelector("#live-stock-count"),
    ].filter(Boolean);

    stockElements.forEach(function (element) {
        element.textContent =
            stock > 0 ? String(stock) : "0";

        element.classList.toggle("in-stock", stock > 0);
        element.classList.toggle("out-of-stock", stock <= 0);
    });

    const stockMessage =
        form.querySelector(".quick-view-stock, .product-stock-message") ||
        document.querySelector(".product-stock-message");

    if (stockMessage) {
        stockMessage.textContent =
            stock > 0
                ? stock + " available in stock"
                : "Out of stock";

        stockMessage.classList.toggle("in-stock", stock > 0);
        stockMessage.classList.toggle("out-of-stock", stock <= 0);
    }
}

function updateAddToCartButton(form, isAvailable) {
    const buttons = form.querySelectorAll(
        '[type="submit"].add-to-cart-button, ' +
        ".quick-view-cart-button, " +
        '[data-add-to-cart]'
    );

    buttons.forEach(function (button) {
        button.disabled = !isAvailable;

        if (button.dataset.originalText === undefined) {
            button.dataset.originalText =
                button.textContent.trim() || "Add To Cart";
        }

        button.textContent = isAvailable
            ? button.dataset.originalText
            : "Out of Stock";
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

    cartContainer.addEventListener("change", function (event) {
        const quantityInput = event.target.closest(
            ".cart-quantity-input, " +
            'input[name^="quantities"], ' +
            "[data-cart-quantity]"
        );

        if (!quantityInput) {
            return;
        }

        clearTimeout(updateTimer);

        updateTimer = setTimeout(function () {
            updateCartItemQuantity(quantityInput);
        }, 350);
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

        const item = button.closest(
            ".cart-item, [data-cart-item]"
        );

        if (!item) {
            return;
        }

        const input = item.querySelector(
            ".cart-quantity-input, [data-cart-quantity]"
        );

        if (!input) {
            return;
        }

        const minimum = Number(input.min || 1);
        const maximum = Number(input.max || Infinity);
        const currentValue = Number(input.value || minimum);

        if (decreaseButton) {
            input.value = Math.max(minimum, currentValue - 1);
        }

        if (increaseButton) {
            input.value = Math.min(maximum, currentValue + 1);
        }

        input.dispatchEvent(
            new Event("change", {
                bubbles: true,
            })
        );
    });
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

    const quantity = Number(input.value || 1);

    if (!updateUrl || !cartKey) {
        const form = input.closest("form");

        if (form) {
            form.submit();
        }

        return;
    }

    input.disabled = true;
    cartItem.classList.add("updating");

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
        .then(function (response) {
            if (!response.ok) {
                throw new Error("Unable to update cart.");
            }

            return response.json();
        })
        .then(function (data) {
            updateCartTotals(data, cartItem);
        })
        .catch(function (error) {
            console.error(error);

            window.location.reload();
        })
        .finally(function () {
            input.disabled = false;
            cartItem.classList.remove("updating");
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

        const shouldRemove = window.confirm(
            "Are you sure you want to remove this product from your cart?"
        );

        if (!shouldRemove) {
            return;
        }

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
            .then(function (response) {
                if (!response.ok) {
                    throw new Error(
                        "Unable to remove cart item."
                    );
                }

                return response.json();
            })
            .then(function (data) {
                if (cartItem) {
                    cartItem.remove();
                }

                updateCartTotals(
                    data,
                    document.createElement("div")
                );

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
                        cartContainer.classList.add("cart-is-empty");
                    }

                    showElement(emptyCart);
                }
            })
            .catch(function (error) {
                console.error(error);

                if (form) {
                    form.submit();
                } else {
                    window.location.reload();
                }
            })
            .finally(function () {
                removeButton.disabled = false;

                if (cartItem) {
                    cartItem.classList.remove("removing");
                }
            });
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
  '.btn-style-1, .btn-style-2, .btn-style-3'
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
