<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php($needsPhoneInput = (str_contains($__env->yieldContent('content'), 'phone-field') || str_contains($__env->yieldContent('content'), 'phone-number')))
    @php($publicSeo = app(\App\Services\PublicSeoService::class)->values(get_defined_vars(), trim($__env->yieldContent('title')), trim($__env->yieldContent('meta_description'))))
    <title>{{ $publicSeo['title'] }}</title>
    <meta name="description" content="{{ $publicSeo['description'] }}">
    <meta name="robots" content="{{ $publicSeo['robots'] }}">
    <link rel="canonical" href="{{ $publicSeo['canonical'] }}">
    <meta property="og:type" content="{{ $publicSeo['type'] }}">
    <meta property="og:site_name" content="{{ $publicSeo['store'] }}">
    <meta property="og:title" content="{{ $publicSeo['ogTitle'] }}">
    <meta property="og:description" content="{{ $publicSeo['ogDescription'] }}">
    <meta property="og:url" content="{{ $publicSeo['canonical'] }}">
    <meta name="twitter:card" content="{{ $publicSeo['image'] ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $publicSeo['ogTitle'] }}">
    <meta name="twitter:description" content="{{ $publicSeo['ogDescription'] }}">
    @if($publicSeo['image'])
        <meta property="og:image" content="{{ $publicSeo['image'] }}">
        <meta name="twitter:image" content="{{ $publicSeo['image'] }}">
    @endif
    @if($publicSeo['graph'])
        <script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@graph'=>$publicSeo['graph']], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    @if(request()->routeIs('home-page'))
        <link rel="preload" as="image" media="(max-width:880px)" href="{{ app(\App\Services\ResponsiveMediaService::class)->url(asset('asset/media/hero.webp'),640) }}" fetchpriority="high">
        <link rel="preload" as="image" media="(min-width:881px)" href="{{ app(\App\Services\ResponsiveMediaService::class)->url(asset('asset/media/hero.webp'),1280) }}" fetchpriority="high">
    @endif

    @stack('head-seo')

    @include('partials.storefront-styles')
    @include('partials.optimized-backgrounds')

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" media="print" onload="this.media='all'"
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    >

    @if($needsPhoneInput)<link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css" media="print" onload="this.media='all'"
    >@endif

    @include('partials.carousel-styles')
    <style>:root{--body-display:#606779}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}</style>

    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">@if($needsPhoneInput)<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">@endif</noscript>
    @include('partials.icon-font-display')
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

    @if($needsPhoneInput)<script
        src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js" defer></script>@endif

    {{-- Home page --}}
    @if (request()->routeIs('home-page'))
        <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/main.js') }}" defer></script>
    @endif

    {{-- Admin dashboard --}}
    @if (
        request()->routeIs(
            'admin.*',
            'dashboard'
        )
    )
        <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/dashboard.js') }}" defer></script>
    @endif

    {{-- About page --}}
    @if (request()->routeIs('about-page'))
        <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/about.js') }}" defer></script>
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
        <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/blogs.js') }}" defer></script>
    @endif

    {{-- Contact page --}}
    @if (request()->routeIs('contact-page'))
        <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/contact.js') }}" defer></script>
    @endif

    {{-- Services pages --}}
    @if (
        request()->routeIs(
            'services-page.index',
            'services-show.show'
        )
    )
        <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/services.js') }}" defer></script>
    @endif

    {{-- Projects pages --}}
    @if (
        request()->routeIs(
            'projects-page.index',
            'projects-show.show'
        )
    )
        <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/project-main.js') }}" defer></script>
    @endif

    {{-- Privacy policy and terms pages --}}
    @if (
        request()->routeIs(
            'privacy-policy-page',
            'terms-and-conditions-page'
        )
        || (isset($page) && in_array($page->slug, ['privacy-policy', 'terms-and-conditions'], true))
    )
        <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/policy-condtions.js') }}" defer></script>
    @endif

    {{-- General thank-you page --}}
    @if (request()->routeIs('thank-you'))
        <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/thankyou.js') }}" defer></script>
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
        <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/product.js') }}" defer></script>
    @endif

    <script src="{{ app(\App\Services\PublicAssetService::class)->url('asset/js/card-carousel.js') }}" defer></script>
    @stack('page-scripts')

</body>

</html>
