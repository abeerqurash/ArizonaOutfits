<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        name="robots"
        content="noindex, nofollow"
    >

    <title>
        @yield('title', 'My Account') | Arizona Outfits
    </title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    >

    <link
        rel="stylesheet"
        href="{{ asset('asset/css/admin-dashboard.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('asset/css/admin-hardening.css') }}"
    >

    @include('customer.partials.styles')

    @stack('page-styles')

    <style>
        .customer-security-popup-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            margin-top: 18px;
            padding: 10px 18px;
            border: 0;
            border-radius: 9px;
            background: #172033;
            color: #fff;
            font-size: 13px;
            font-weight: 850;
            text-decoration: none;
            cursor: pointer;
        }

        .customer-security-popup-action:focus-visible {
            outline: 3px solid rgba(15, 118, 110, .22);
            outline-offset: 3px;
        }

        .customer-feedback-popup.is-security .customer-feedback-popup-icon {
            background: #ecfdf5;
            color: #0f766e;
        }

        .customer-feedback-popup {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .customer-feedback-popup[hidden] {
            display: none;
        }

        .customer-feedback-popup-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, .58);
            backdrop-filter: blur(2px);
        }

        .customer-feedback-popup-dialog {
            position: relative;
            z-index: 1;
            width: min(100%, 430px);
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            padding: 30px 28px 26px;
            border: 1px solid #e5eaf1;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
            text-align: center;
        }

        .customer-feedback-popup-close {
            position: absolute;
            top: 12px;
            right: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            font-size: 15px;
            cursor: pointer;
        }

        .customer-feedback-popup-close:hover {
            background: #e2e8f0;
            color: #172033;
        }

        .customer-feedback-popup-close:focus-visible {
            outline: 3px solid rgba(15, 118, 110, .22);
            outline-offset: 2px;
        }

        .customer-feedback-popup-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            margin-bottom: 16px;
            border-radius: 50%;
            font-size: 24px;
        }

        .customer-feedback-popup.is-success .customer-feedback-popup-icon {
            background: #d1fae5;
            color: #047857;
        }

        .customer-feedback-popup.is-error .customer-feedback-popup-icon {
            background: #ffe4e6;
            color: #be123c;
        }

        .customer-feedback-popup-title {
            margin: 0 0 8px;
            color: #172033;
            font-size: 20px;
            line-height: 1.3;
        }

        .customer-feedback-popup-message {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.65;
        }

        .customer-feedback-popup-errors {
            margin: 14px 0 0;
            padding: 12px 14px;
            border: 1px solid #fecdd3;
            border-radius: 10px;
            background: #fff7f7;
            color: #9f1239;
            font-size: 12px;
            line-height: 1.55;
            list-style-position: inside;
            text-align: center;
        }

        .customer-feedback-popup-errors li + li {
            margin-top: 5px;
        }

        body.customer-feedback-popup-open {
            overflow: hidden;
        }

        @media (max-width: 480px) {
            .customer-feedback-popup {
                padding: 14px;
            }

            .customer-feedback-popup-dialog {
                max-height: calc(100vh - 28px);
                padding: 28px 20px 22px;
                border-radius: 14px;
            }

            .customer-feedback-popup-title {
                font-size: 18px;
            }
        }
    </style>

</head>

<body class="admin-body customer-dashboard-body">

    <div class="admin-layout">

        @include('customer.partials.sidebar')

        <div
            class="admin-sidebar-overlay"
            id="adminSidebarOverlay"
        ></div>

        <div class="admin-main">

            @include('customer.partials.topbar')

            <main
                class="admin-content customer-dashboard-content"
                id="mainContent"
            >

                @php
                    $customerFeedbackType = null;
                    $customerFeedbackTitle = null;
                    $customerFeedbackMessage = null;
                    $customerFeedbackErrors = [];

                    if (session('error')) {
                        $customerFeedbackType = 'error';
                        $customerFeedbackTitle = 'Something went wrong';
                        $customerFeedbackMessage = session('error');
                    } elseif ($errors->any()) {
                        $customerFeedbackType = 'error';
                        $customerFeedbackTitle = 'Please correct the following';
                        $customerFeedbackErrors = $errors->all();
                    } elseif (session('success')) {
                        $customerFeedbackType = 'success';
                        $customerFeedbackTitle = 'Success';
                        $customerFeedbackMessage = session('success');
                    }
                @endphp

                @if ($customerFeedbackType)

                    <div
                        class="customer-feedback-popup is-{{ $customerFeedbackType }}"
                        id="customerFeedbackPopup"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="customerFeedbackPopupTitle"
                        @if ($customerFeedbackMessage)
                            aria-describedby="customerFeedbackPopupMessage"
                        @endif
                    >

                        <div
                            class="customer-feedback-popup-backdrop"
                            data-customer-feedback-close
                            aria-hidden="true"
                        ></div>

                        <div
                            class="customer-feedback-popup-dialog"
                            role="document"
                        >

                            <button
                                type="button"
                                class="customer-feedback-popup-close"
                                data-customer-feedback-close
                                aria-label="Close message"
                            >
                                <i
                                    class="fa-solid fa-xmark"
                                    aria-hidden="true"
                                ></i>
                            </button>

                            <span
                                class="customer-feedback-popup-icon"
                                aria-hidden="true"
                            >
                                @if ($customerFeedbackType === 'success')
                                    <i class="fa-solid fa-circle-check"></i>
                                @else
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                @endif
                            </span>

                            <h2
                                class="customer-feedback-popup-title"
                                id="customerFeedbackPopupTitle"
                            >
                                {{ $customerFeedbackTitle }}
                            </h2>

                            @if ($customerFeedbackMessage)
                                <p
                                    class="customer-feedback-popup-message"
                                    id="customerFeedbackPopupMessage"
                                >
                                    {{ $customerFeedbackMessage }}
                                </p>
                            @endif

                            @if (!empty($customerFeedbackErrors))
                                <ul class="customer-feedback-popup-errors">
                                    @foreach ($customerFeedbackErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif

                        </div>

                    </div>

                @endif


                {{-- DAILY SECURITY REMINDER --}}

                @php
                    $securityReminder =
                        request()->attributes->get(
                            'customerSecurityReminder'
                        );
                @endphp

                @if ($securityReminder)

                    <div
                        class="customer-feedback-popup is-security"
                        id="customerSecurityReminderPopup"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="customerSecurityReminderTitle"
                        aria-describedby="customerSecurityReminderMessage"
                        @if ($customerFeedbackType)
                            hidden
                        @endif
                    >

                        <div
                            class="customer-feedback-popup-backdrop"
                            data-customer-security-close
                            aria-hidden="true"
                        ></div>

                        <div
                            class="customer-feedback-popup-dialog"
                            role="document"
                        >

                            <button
                                type="button"
                                class="customer-feedback-popup-close"
                                data-customer-security-close
                                aria-label="Close security reminder"
                            >
                                <i
                                    class="fa-solid fa-xmark"
                                    aria-hidden="true"
                                ></i>
                            </button>

                            <span
                                class="customer-feedback-popup-icon"
                                aria-hidden="true"
                            >
                                <i class="fa-solid fa-shield-halved"></i>
                            </span>

                            <h2
                                class="customer-feedback-popup-title"
                                id="customerSecurityReminderTitle"
                            >
                                {{ $securityReminder['title'] }}
                            </h2>

                            <p
                                class="customer-feedback-popup-message"
                                id="customerSecurityReminderMessage"
                            >
                                {{ $securityReminder['message'] }}
                            </p>

                            <a
                                href="{{ route('customer.security') }}"
                                class="customer-security-popup-action"
                            >
                                {{ $securityReminder['button'] }}

                                <i
                                    class="fa-solid fa-arrow-right"
                                    aria-hidden="true"
                                ></i>
                            </a>

                        </div>

                    </div>

                @endif

                @yield('content')

            </main>

            @include('customer.partials.footer')

        </div>

    </div>

    <script
        src="{{ asset('asset/js/admin-dashboard.js') }}"
    ></script>

    <script>
        (() => {
            'use strict';

            const feedbackPopup =
                document.getElementById('customerFeedbackPopup');

            const securityPopup =
                document.getElementById('customerSecurityReminderPopup');

            let activePopup = null;
            let previouslyFocusedElement = null;

            const getDialog = (popup) =>
                popup?.querySelector('.customer-feedback-popup-dialog');

            const getCloseButton = (popup) =>
                popup?.querySelector('.customer-feedback-popup-close');

            const showPopup = (popup) => {
                if (!popup) {
                    return;
                }

                if (!previouslyFocusedElement) {
                    previouslyFocusedElement = document.activeElement;
                }

                popup.hidden = false;
                activePopup = popup;
                document.body.classList.add('customer-feedback-popup-open');

                const closeButton = getCloseButton(popup);

                if (closeButton) {
                    closeButton.focus();
                }
            };

            const restorePageFocus = () => {
                document.body.classList.remove('customer-feedback-popup-open');

                if (
                    previouslyFocusedElement
                    && typeof previouslyFocusedElement.focus === 'function'
                ) {
                    previouslyFocusedElement.focus();
                }

                previouslyFocusedElement = null;
            };

            const closePopup = (popup) => {
                if (!popup) {
                    return;
                }

                popup.hidden = true;

                if (
                    popup === feedbackPopup
                    && securityPopup
                    && securityPopup.hidden
                ) {
                    activePopup = null;
                    showPopup(securityPopup);
                    return;
                }

                activePopup = null;
                restorePageFocus();
            };

            if (feedbackPopup) {
                feedbackPopup
                    .querySelectorAll('[data-customer-feedback-close]')
                    .forEach((trigger) => {
                        trigger.addEventListener(
                            'click',
                            () => closePopup(feedbackPopup)
                        );
                    });
            }

            if (securityPopup) {
                securityPopup
                    .querySelectorAll('[data-customer-security-close]')
                    .forEach((trigger) => {
                        trigger.addEventListener(
                            'click',
                            () => closePopup(securityPopup)
                        );
                    });
            }

            document.addEventListener('keydown', (event) => {
                if (!activePopup || activePopup.hidden) {
                    return;
                }

                if (event.key === 'Escape') {
                    closePopup(activePopup);
                    return;
                }

                if (event.key !== 'Tab') {
                    return;
                }

                const dialog = getDialog(activePopup);

                if (!dialog) {
                    return;
                }

                const focusableElements = dialog.querySelectorAll(
                    'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
                );

                if (!focusableElements.length) {
                    event.preventDefault();
                    return;
                }

                const firstFocusable = focusableElements[0];
                const lastFocusable =
                    focusableElements[focusableElements.length - 1];

                if (
                    event.shiftKey
                    && document.activeElement === firstFocusable
                ) {
                    event.preventDefault();
                    lastFocusable.focus();
                } else if (
                    !event.shiftKey
                    && document.activeElement === lastFocusable
                ) {
                    event.preventDefault();
                    firstFocusable.focus();
                }
            });

            if (feedbackPopup) {
                showPopup(feedbackPopup);
            } else if (securityPopup) {
                showPopup(securityPopup);
            }
        })();
    </script>

    @stack('page-scripts')

</body>

</html>
