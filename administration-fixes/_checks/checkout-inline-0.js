
(() => {
    "use strict";

    const drawer = document.querySelector("[data-cart-drawer]");
    const trigger = document.querySelector("[data-header-cart-trigger]");

    if (!drawer || !trigger) {
        return;
    }

    const panel = drawer.querySelector(".header-cart-drawer-panel");
    const closeButtons = drawer.querySelectorAll("[data-cart-drawer-close]");
    let lastFocusedElement = null;

    function openCartDrawer() {
        lastFocusedElement = document.activeElement;

        drawer.classList.add("is-open");
        drawer.setAttribute("aria-hidden", "false");

        document.documentElement.classList.add("header-cart-drawer-open");
        document.body.classList.add("header-cart-drawer-open");

        window.requestAnimationFrame(function () {
            drawer.querySelector(".header-cart-drawer-close")?.focus();
        });
    }

    function closeCartDrawer() {
        drawer.classList.remove("is-open");
        drawer.setAttribute("aria-hidden", "true");

        document.documentElement.classList.remove("header-cart-drawer-open");
        document.body.classList.remove("header-cart-drawer-open");

        if (
            lastFocusedElement
            && typeof lastFocusedElement.focus === "function"
        ) {
            lastFocusedElement.focus();
        }
    }

    function bindDrawerMovingButtons() {
        drawer.querySelectorAll(
            ".header-cart-drawer-quantity-button, " +
            ".header-cart-drawer-remove, " +
            ".header-cart-drawer-shop, " +
            ".header-cart-drawer-button"
        ).forEach(function (button) {
            if (button.dataset.drawerMotionBound === "true") {
                return;
            }

            const buttonText = button.querySelector(".button-text");

            if (!buttonText) {
                return;
            }

            button.dataset.drawerMotionBound = "true";

            button.addEventListener("mousemove", function (event) {
                if (button.disabled) {
                    return;
                }

                const rect = button.getBoundingClientRect();
                const x = event.clientX - rect.left - rect.width / 2;
                const y = event.clientY - rect.top - rect.height / 2;

                buttonText.style.transform =
                    "translate3d(" +
                    (x / 5) +
                    "px, " +
                    (y / 5) +
                    "px, 0) scale(1)";
            });

            button.addEventListener("mouseleave", function () {
                buttonText.style.transform =
                    "translate3d(0px, 0px, 0px) scale(1)";
            });
        });
    }

    bindDrawerMovingButtons();

    trigger.addEventListener("click", function (event) {
        if (
            event.button !== 0
            || event.ctrlKey
            || event.metaKey
            || event.shiftKey
            || event.altKey
        ) {
            return;
        }

        event.preventDefault();
        openCartDrawer();
    });

    closeButtons.forEach(function (button) {
        button.addEventListener("click", closeCartDrawer);
    });

    document.addEventListener("keydown", function (event) {
        if (
            event.key === "Escape"
            && drawer.classList.contains("is-open")
        ) {
            closeCartDrawer();
        }
    });

    drawer.addEventListener("keydown", function (event) {
        if (
            event.key !== "Tab"
            || !drawer.classList.contains("is-open")
            || !panel
        ) {
            return;
        }

        const focusable = Array.from(
            panel.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), ' +
                'select:not([disabled]), textarea:not([disabled]), ' +
                '[tabindex]:not([tabindex="-1"])'
            )
        ).filter(function (element) {
            return element.offsetParent !== null;
        });

        if (!focusable.length) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (
            !event.shiftKey
            && document.activeElement === last
        ) {
            event.preventDefault();
            first.focus();
        }
    });
})();
