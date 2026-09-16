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
        .customer-security-reminder {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
            padding: 18px 20px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
        }

        .customer-security-reminder-content {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .customer-security-reminder-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #f3f4f6;
            font-size: 18px;
        }

        .customer-security-reminder h3 {
            margin: 0 0 5px;
            font-size: 16px;
        }

        .customer-security-reminder p {
            margin: 0;
            line-height: 1.55;
        }

        .customer-security-reminder-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-shrink: 0;
            min-height: 42px;
            padding: 10px 16px;
            border-radius: 9px;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 767px) {
            .customer-security-reminder {
                flex-direction: column;
                align-items: stretch;
            }

            .customer-security-reminder-action {
                width: 100%;
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

                {{-- SUCCESS MESSAGE --}}

                @if (session('success'))

                    <div
                        class="admin-alert admin-alert-success"
                        role="status"
                    >

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            {{ session('success') }}
                        </span>

                        <button
                            type="button"
                            class="admin-alert-close"
                            aria-label="Close message"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                    </div>

                @endif


                {{-- ERROR MESSAGE --}}

                @if (session('error'))

                    <div
                        class="admin-alert admin-alert-danger"
                        role="alert"
                    >

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            {{ session('error') }}
                        </span>

                        <button
                            type="button"
                            class="admin-alert-close"
                            aria-label="Close message"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>

                    </div>

                @endif


                {{-- VALIDATION ERRORS --}}

                @if ($errors->any())

                    <div
                        class="admin-alert admin-alert-danger"
                        role="alert"
                    >

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <div>

                            <strong>
                                Please correct the following:
                            </strong>

                            <ul>

                                @foreach ($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                        <button
                            type="button"
                            class="admin-alert-close"
                            aria-label="Close validation messages"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>

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
                        class="customer-security-reminder"
                        role="status"
                    >

                        <div
                            class="customer-security-reminder-content"
                        >

                            <span
                                class="customer-security-reminder-icon"
                            >

                                <i
                                    class="fa-solid fa-shield-halved"
                                ></i>

                            </span>

                            <div>

                                <h3>
                                    {{ $securityReminder['title'] }}
                                </h3>

                                <p>
                                    {{ $securityReminder['message'] }}
                                </p>

                            </div>

                        </div>

                        <a
                            href="{{ route('customer.security') }}"
                            class="customer-security-reminder-action customer-primary-button"
                        >

                            {{ $securityReminder['button'] }}

                            <i
                                class="fa-solid fa-arrow-right"
                            ></i>

                        </a>

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

    @stack('page-scripts')

</body>

</html>