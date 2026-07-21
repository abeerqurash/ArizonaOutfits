<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
<meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @yield(
            'title',
            $title ?? 'IdeoStream'
        )
    </title>

    <meta
        name="description"
        content="@yield(
            'meta_description',
            $meta_description ?? ''
        )"
    >

    <meta
        name="robots"
        content="{{ $robots ?? 'index, follow' }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('asset/css/style.css') }}"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css"
    >

    @stack('page-styles')

</head>

<body
    id="{{
        Route::currentRouteName()
            ? str_replace(
                '.',
                '-',
                Route::currentRouteName()
            )
            : 'page'
    }}"
    class="{{
        Route::currentRouteName()
            ? str_replace(
                '.',
                ' ',
                Route::currentRouteName()
            )
            : ''
    }}"
>

    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    <script
        src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"
    ></script>

    <script
        src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"
    ></script>

    {{--
    |--------------------------------------------------------------------------
    | Home page
    |--------------------------------------------------------------------------
    --}}

    @if (request()->routeIs('home-page'))

        <script
            src="{{ asset('asset/js/main.js') }}"
        ></script>

    @endif

    {{--
    |--------------------------------------------------------------------------
    | Admin dashboard
    |--------------------------------------------------------------------------
    --}}

    @if (
        request()->routeIs(
            'admin.*',
            'dashboard'
        )
    )

        <script
            src="{{ asset('asset/js/dashboard.js') }}"
        ></script>

    @endif

    {{--
    |--------------------------------------------------------------------------
    | About page
    |--------------------------------------------------------------------------
    --}}

    @if (request()->routeIs('about-page'))

        <script
            src="{{ asset('asset/js/about.js') }}"
        ></script>

    @endif

    {{--
    |--------------------------------------------------------------------------
    | Blog pages
    |--------------------------------------------------------------------------
    --}}

    @if (
        request()->routeIs(
            'blogs-page',
            'blog-show',
            'categories-page',
            'category-show'
        )
    )

        <script
            src="{{ asset('asset/js/blogs.js') }}"
        ></script>

    @endif

    {{--
    |--------------------------------------------------------------------------
    | Contact page
    |--------------------------------------------------------------------------
    --}}

    @if (request()->routeIs('contact-page'))

        <script
            src="{{ asset('asset/js/contact.js') }}"
        ></script>

    @endif

    {{--
    |--------------------------------------------------------------------------
    | Services pages
    |--------------------------------------------------------------------------
    --}}

    @if (
        request()->routeIs(
            'services-page.index',
            'services-show.show'
        )
    )

        <script
            src="{{ asset('asset/js/services.js') }}"
        ></script>

    @endif

    {{--
    |--------------------------------------------------------------------------
    | Projects pages
    |--------------------------------------------------------------------------
    --}}

    @if (
        request()->routeIs(
            'projects-page.index',
            'projects-show.show'
        )
    )

        <script
            src="{{ asset('asset/js/project-main.js') }}"
        ></script>

    @endif

    {{--
    |--------------------------------------------------------------------------
    | Privacy policy and terms pages
    |--------------------------------------------------------------------------
    --}}

    @if (
        request()->routeIs(
            'privacy-policy-page',
            'terms-and-conditions-page'
        )
    )

        <script
            src="{{ asset('asset/js/policy-condtions.js') }}"
        ></script>

    @endif

    {{--
    |--------------------------------------------------------------------------
    | General thank-you page
    |--------------------------------------------------------------------------
    --}}

    @if (request()->routeIs('thank-you'))

        <script
            src="{{ asset('asset/js/thankyou.js') }}"
        ></script>

    @endif

    {{--
    |--------------------------------------------------------------------------
    | Complete shop system
    |--------------------------------------------------------------------------
    |
    | product.js loads only on:
    |
    | - All products
    | - Single product
    | - Product categories
    | - Sale page
    | - Quick-view request/page
    | - Cart
    | - Favorites
    | - Checkout
    | - Order thank-you page
    |
    --}}

    @if (
        request()->routeIs(
            'products.*',
            'cart.*',
            'favorites.*',
            'favorite.*',
            'checkout.*'
        )
    )

        <script
            src="{{ asset('asset/js/product.js') }}"
        ></script>

    @endif

    @stack('page-scripts')

</body>

</html>