document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const body = document.body;

    const sidebar = document.getElementById("adminSidebar");
    const sidebarOverlay = document.getElementById(
        "adminSidebarOverlay"
    );
    const mobileMenuButton = document.getElementById(
        "adminMobileMenuButton"
    );
    const sidebarClose = document.getElementById(
        "adminSidebarClose"
    );

    const notificationButton = document.getElementById(
        "adminNotificationButton"
    );
    const notificationDropdown = document.getElementById(
        "adminNotificationDropdown"
    );

    const profileButton = document.getElementById(
        "adminProfileButton"
    );
    const profileDropdown = document.getElementById(
        "adminProfileDropdown"
    );

    function openSidebar() {
        if (!sidebar || !sidebarOverlay) {
            return;
        }

        sidebar.classList.add("active");
        sidebarOverlay.classList.add("active");
        body.classList.add("admin-menu-open");
    }

    function closeSidebar() {
        if (!sidebar || !sidebarOverlay) {
            return;
        }

        sidebar.classList.remove("active");
        sidebarOverlay.classList.remove("active");
        body.classList.remove("admin-menu-open");
    }

    function closeDropdowns(exceptDropdown = null) {
        const dropdowns = document.querySelectorAll(
            ".admin-dropdown"
        );

        dropdowns.forEach(function (dropdown) {
            if (dropdown !== exceptDropdown) {
                dropdown.classList.remove("active");
            }
        });

        if (
            notificationButton &&
            notificationDropdown !== exceptDropdown
        ) {
            notificationButton.setAttribute(
                "aria-expanded",
                "false"
            );
        }

        if (
            profileButton &&
            profileDropdown !== exceptDropdown
        ) {
            profileButton.setAttribute(
                "aria-expanded",
                "false"
            );
        }
    }

    function toggleDropdown(button, dropdown) {
        if (!button || !dropdown) {
            return;
        }

        const isActive = dropdown.classList.contains("active");

        closeDropdowns(dropdown);

        dropdown.classList.toggle("active", !isActive);

        button.setAttribute(
            "aria-expanded",
            isActive ? "false" : "true"
        );
    }

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener(
            "click",
            openSidebar
        );
    }

    if (sidebarClose) {
        sidebarClose.addEventListener(
            "click",
            closeSidebar
        );
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener(
            "click",
            closeSidebar
        );
    }

    if (notificationButton && notificationDropdown) {
        notificationButton.addEventListener(
            "click",
            function (event) {
                event.stopPropagation();

                toggleDropdown(
                    notificationButton,
                    notificationDropdown
                );
            }
        );
    }

    if (profileButton && profileDropdown) {
        profileButton.addEventListener(
            "click",
            function (event) {
                event.stopPropagation();

                toggleDropdown(
                    profileButton,
                    profileDropdown
                );
            }
        );
    }

    document.addEventListener("click", function (event) {
        const clickedInsideDropdown = event.target.closest(
            ".admin-dropdown"
        );

        const clickedDropdownButton = event.target.closest(
            "#adminNotificationButton, #adminProfileButton"
        );

        if (!clickedInsideDropdown && !clickedDropdownButton) {
            closeDropdowns();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") {
            return;
        }

        closeSidebar();
        closeDropdowns();
    });

    const alertCloseButtons = document.querySelectorAll(
        ".admin-alert-close"
    );

    alertCloseButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const alert = button.closest(".admin-alert");

            if (!alert) {
                return;
            }

            alert.remove();
        });
    });

    const sidebarLinks = document.querySelectorAll(
        ".admin-sidebar .admin-menu-link"
    );

    sidebarLinks.forEach(function (link) {
        link.addEventListener("click", function () {
            if (window.innerWidth <= 991) {
                closeSidebar();
            }
        });
    });

    window.addEventListener("resize", function () {
        if (window.innerWidth > 991) {
            closeSidebar();
        }
    });
});

function showDashboardLoader() {
    document
        .getElementById("dashboardLoadingOverlay")
        ?.classList.remove("d-none");
}

function hideDashboardLoader() {
    document
        .getElementById("dashboardLoadingOverlay")
        ?.classList.add("d-none");
}