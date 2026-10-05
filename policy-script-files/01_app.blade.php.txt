<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        /*
        |--------------------------------------------------------------------------
        | Central public SEO values
        |--------------------------------------------------------------------------
        |
        | Normal public pages continue using their Blade title/description
        | sections. Individual blog articles use the Post database SEO fields.
        |
        */

        $isBlogPost = request()->routeIs('blog-show') && isset($post);

        $seoTitle = $isBlogPost
            ? ($post->meta_title ?: $post->title)
            : ($title ?? 'Arizona Outfits');

        $seoDescription = $isBlogPost
            ? ($post->meta_description ?: ($post->excerpt ?: ''))
            : ($meta_description ?? '');

        $seoRobots = $isBlogPost
            ? (($post->robots_index ? 'index' : 'noindex') . ', ' . ($post->robots_follow ? 'follow' : 'nofollow'))
            : ($robots ?? 'index, follow');

        $seoCanonical = $isBlogPost
            ? ($post->canonical_url ?: url()->current())
            : null;

        $seoOgTitle = $isBlogPost
            ? ($post->og_title ?: $seoTitle)
            : null;

        $seoOgDescription = $isBlogPost
            ? ($post->og_description ?: $seoDescription)
            : null;

        $seoOgImage = $isBlogPost
            ? ($post->og_image_url ?: $post->feature_image_url)
            : null;
    @endphp

    @if ($isBlogPost)

        <title>{{ $seoTitle }}</title>

        <meta
            name="description"
            content="{{ $seoDescription }}"
        >

    @else

        <title>
            @yield(
                'title',
                $seoTitle
            )
        </title>

        @hasSection('meta_description')
            <meta
                name="description"
                content="@yield('meta_description')"
            >
        @elseif (!empty($seoDescription))
            <meta
                name="description"
                content="{{ $seoDescription }}"
            >
        @endif

    @endif

    <meta
        name="robots"
        content="{{ $seoRobots }}"
    >

    @if ($isBlogPost)

        <link
            rel="canonical"
            href="{{ $seoCanonical }}"
        >

        <meta
            property="og:type"
            content="article"
        >

        <meta
            property="og:title"
            content="{{ $seoOgTitle }}"
        >

        <meta
            property="og:description"
            content="{{ $seoOgDescription }}"
        >

        <meta
            property="og:url"
            content="{{ $seoCanonical }}"
        >

        @if ($seoOgImage)
            <meta
                property="og:image"
                content="{{ $seoOgImage }}"
            >

            <meta
                name="twitter:card"
                content="summary_large_image"
            >
        @else
            <meta
                name="twitter:card"
                content="summary"
            >
        @endif

        <meta
            name="twitter:title"
            content="{{ $seoOgTitle }}"
        >

        <meta
            name="twitter:description"
            content="{{ $seoOgDescription }}"
        >

        @if ($seoOgImage)
            <meta
                name="twitter:image"
                content="{{ $seoOgImage }}"
            >
        @endif

    @endif

    @stack('head-seo')

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
    data-currency-symbol="{{ app(\App\Services\StoreSettingsService::class)->symbol() }}"
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

    {{-- Home page --}}
    @if (request()->routeIs('home-page'))
        <script src="{{ asset('asset/js/main.js') }}"></script>
    @endif

    {{-- Admin dashboard --}}
    @if (
        request()->routeIs(
            'admin.*',
            'dashboard'
        )
    )
        <script src="{{ asset('asset/js/dashboard.js') }}"></script>
    @endif

    {{-- About page --}}
    @if (request()->routeIs('about-page'))
        <script src="{{ asset('asset/js/about.js') }}"></script>
    @endif

    {{-- Blog pages --}}
    @if (
        request()->routeIs(
            'blogs-page',
            'blog-show',
            'categories-page',
            'category-show'
        )
    )
        <script src="{{ asset('asset/js/blogs.js') }}"></script>
    @endif

    {{-- Contact page --}}
    @if (request()->routeIs('contact-page'))
        <script src="{{ asset('asset/js/contact.js') }}"></script>
    @endif

    {{-- Services pages --}}
    @if (
        request()->routeIs(
            'services-page.index',
            'services-show.show'
        )
    )
        <script src="{{ asset('asset/js/services.js') }}"></script>
    @endif

    {{-- Projects pages --}}
    @if (
        request()->routeIs(
            'projects-page.index',
            'projects-show.show'
        )
    )
        <script src="{{ asset('asset/js/project-main.js') }}"></script>
    @endif

    {{-- Privacy policy and terms pages --}}
    @if (
        request()->routeIs(
            'privacy-policy-page',
            'terms-and-conditions-page'
        )
        || (isset($page) && in_array($page->slug, ['privacy-policy', 'terms-and-conditions'], true))
    )
        <script src="{{ asset('asset/js/policy-condtions.js') }}"></script>
    @endif

    {{-- General thank-you page --}}
    @if (request()->routeIs('thank-you'))
        <script src="{{ asset('asset/js/thankyou.js') }}"></script>
    @endif

    {{-- Complete shop system --}}
    @if (
        request()->routeIs(
            'products.*',
            'cart.*',
            'favorites.*',
            'favorite.*',
            'checkout.*'
        )
    )
        <script src="{{ asset('asset/js/product.js') }}"></script>
    @endif

    @stack('page-scripts')

</body>

</html>
