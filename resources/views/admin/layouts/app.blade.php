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
        @yield('title', 'Admin Dashboard') | Arizona Outfits
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

    @stack('page-styles')
</head>

<body class="admin-body">

    <div class="admin-layout">

        @include('admin.partials.sidebar')

        <div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

        <div class="admin-main">

            @include('admin.partials.topbar')

            <main class="admin-content">

                @if (session('success'))
                    <div class="admin-alert admin-alert-success">
                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            {{ session('success') }}
                        </span>

                        <button
                            type="button"
                            class="admin-alert-close"
                            aria-label="Close success message"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="admin-alert admin-alert-danger">
                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            {{ session('error') }}
                        </span>

                        <button
                            type="button"
                            class="admin-alert-close"
                            aria-label="Close error message"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="admin-alert admin-alert-danger">
                        <i class="fa-solid fa-circle-exclamation"></i>

                        <div>
                            <strong>Please correct the following:</strong>

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

                @yield('content')

            </main>

            @include('admin.partials.footer')

        </div>

    </div>

    <script src="{{ asset('asset/js/admin-dashboard.js') }}"></script>

    @stack('page-scripts')

</body>

</html>