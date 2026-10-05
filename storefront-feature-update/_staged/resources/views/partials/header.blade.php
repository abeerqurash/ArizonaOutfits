<div class="preloader">
    <div class="preloader-middle">
        <div class="left-preloader"></div>
        <div class="middle-preloader">
            <div class="stripe-preloader left"></div>
            <div class="stripe-preloader middle"></div>
            <div class="stripe-preloader right"></div>
        </div>
        <div class="right-preloader"></div>
    </div>
</div>
<div class="navbar">
    <div class="navbar-wrapper">
        <div class="left-navbar">
            <a href="/" class="logo">Arizona Outfits</a>
            <button type="button" class="menu-button" aria-label="Open navigation menu" aria-expanded="false" aria-controls="arizonaMegaMenu">
                <span class="line line-1"></span>
                <span class="line line-2"></span>
                <span class="line line-3"></span>
            </button>
        </div>
        <div class="menu-wrapper">
            <div class="navigaiton">
                <div class="navigation-links">
                    @include('partials.header-menu-links')
                </div>
            </div>
            <div class="navigation-cover">

            </div>
        </div>
        <div class="mega-menu" id="arizonaMegaMenu" aria-hidden="true" inert>
            <div class="mega-menu-wrapper">
                <div class="project-socials">
                    <div class="social-wrapper">
                        <div class="social-media">
                            @php
$headerSocial=app(\App\Services\StoreSettingsService::class)->settings()->facebook_url;
@endphp
@if($headerSocial)<a aria-label="Facebook" href="{{ $headerSocial }}" class="icon-and-title text-decoration-none" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f text-color-dark"></i></a>@endif
                            @php
$headerSocial=app(\App\Services\StoreSettingsService::class)->settings()->instagram_url;
@endphp
@if($headerSocial)<a aria-label="Instagram" href="{{ $headerSocial }}" class="icon-and-title text-decoration-none" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-instagram text-color-dark"></i></a>@endif
                            @php
$headerSocial=app(\App\Services\StoreSettingsService::class)->settings()->x_url;
@endphp
@if($headerSocial)<a aria-label="X" href="{{ $headerSocial }}" class="icon-and-title text-decoration-none" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-x-twitter text-color-dark"></i></a>@endif
                            @php
$headerSocial=app(\App\Services\StoreSettingsService::class)->settings()->linkedin_url;
@endphp
@if($headerSocial)<a aria-label="LinkedIn" href="{{ $headerSocial }}" class="icon-and-title text-decoration-none" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-linkedin text-color-dark"></i></a>@endif
                            @php
$headerSocial=app(\App\Services\StoreSettingsService::class)->settings()->youtube_url;
@endphp
@if($headerSocial)<a aria-label="YouTube" href="{{ $headerSocial }}" class="icon-and-title text-decoration-none" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-youtube text-color-dark"></i></a>@endif
                        </div>
                    </div>
                </div>
                <nav class="az-mobile-mega-nav" aria-label="Main navigation">
@forelse(($navigationMenuItems ?? collect()) as $mobileMenuItem)
@php
$mobileCategory = app(\App\Services\NavigationMenuService::class)->categoryFor($mobileMenuItem);
@endphp
<div class="az-mobile-menu-group">
<a href="{{ $mobileCategory ? route('products.category', $mobileCategory->slug) : $mobileMenuItem->resolved_url }}" @if($mobileMenuItem->commerce_behavior==='cart_sidebar') data-header-cart-trigger @endif @if($mobileMenuItem->open_in_new_tab && !$mobileMenuItem->commerce_behavior) target="_blank" rel="noopener" @endif>{{ $mobileMenuItem->display_label }}</a>
@if($mobileCategory && $mobileCategory->children->isNotEmpty())
<details><summary aria-label="Show {{ $mobileCategory->title }} subcategories">Subcategories</summary><div>
@foreach($mobileCategory->children as $child)<a href="{{ route('products.category',$child->slug) }}">{{ $child->title }}</a>@endforeach
</div></details>
@endif
</div>
@empty
@foreach(['about-page'=>'About Us','blogs-page'=>'Blogs','products.index'=>'Shop','contact-page'=>'Contact'] as $mobileRoute=>$mobileLabel)
<a href="{{ route($mobileRoute) }}">{{ $mobileLabel }}</a>
@endforeach
@endforelse
</nav>
<div class="categories-wrapper">
                    <div class="categories-description"><div class="title">Product Categories</div>
                        <a href="{{ route('products.index') }}" class="btn-style-2 fs-12 text-color-white justify-self-end"><div class="button-text text-uppercase letter-space-3px">View All Products</div></a>
                    </div>
                    <div class="category-list"><div class="list-collection az-mega-category-grid">
                        @forelse(app(\App\Services\NavigationMenuService::class)->megaCategories() as $megaCategory)
                        <div class="list-item"><a href="{{ route('products.category',$megaCategory->slug) }}" class="list"><div class="list-description"><div class="list-item-text">{{ $megaCategory->title }}</div></div><i class="fa-solid fa-arrow-right-long right-arrow-icon"></i></a></div>
                        @empty<p>No product categories selected yet.</p>@endforelse
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* White navigation cover remains visible before and after scrolling. */
.navbar .navigation-cover,
.navbar .navigation-cover.active {
    background-color: #fff !important;
    transform: none !important;
    transition: none !important;
    will-change: auto;
    border: 1px solid var(--dark-outline, #dbe2ed);
}
.navbar .navigation-links .nav-links-header,
.navbar.scrolled .navigation-links .nav-links-header,
.navbar .navigation-links .nav-links-header .button-text {
    color: #172033 !important;
}
.navbar .navigation-links .nav-links-header:hover,
.navbar .navigation-links .nav-links-header:hover .button-text {
    color: #0f766e !important;
}
.navbar .navigation-links .header-commerce-nav-count {
    background: #eef2f7;
    color: #172033;
    border-color: #dbe2ed;
}
.navbar .navigation-links .nav-links-header:focus-visible {
    outline: 2px solid #0f766e;
    outline-offset: -3px;
}
</style>

<style>
.navbar button.menu-button { background: #fff; color: #172033; font: inherit; }
.navbar button.menu-button:focus-visible { outline: 2px solid #0f766e; outline-offset: -3px; }
</style>
<script>
(function () {
    const navbarElement = document.querySelector('.navbar');
    if (!navbarElement) return;
    const trigger = navbarElement.querySelector('.menu-button');
    const panel = navbarElement.querySelector('.mega-menu');
    if (!trigger || !panel || trigger.dataset.arizonaMenuBound) return;
    trigger.dataset.arizonaMenuBound = 'true';
    function setOpen(open) {
        trigger.classList.toggle('open', open);
        panel.classList.toggle('active', open);
        trigger.setAttribute('aria-expanded', String(open));
        trigger.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
        panel.setAttribute('aria-hidden', String(!open));
        panel.inert = !open;
    }
    setOpen(false);
    // Capture handles the click before the older page-specific bubble listeners.
    trigger.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        setOpen(!panel.classList.contains('active'));
    }, true);
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && panel.classList.contains('active')) {
            setOpen(false);
            trigger.focus();
        }
    });
    document.addEventListener('click', function (event) {
        if (panel.classList.contains('active') && !navbarElement.contains(event.target)) setOpen(false);
    });
    panel.addEventListener('click', function (event) {
        if (event.target.closest('a[href]')) setOpen(false);
    });
})();
</script>

<style>
.navbar .az-mega-category-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:0 24px;width:100%}
.navbar .az-mega-category-grid .list-item{width:100%;min-width:0}
.navbar .az-mega-category-grid .list-item-text{overflow-wrap:anywhere}
@media(max-width:600px){.navbar .az-mega-category-grid{grid-template-columns:1fr}}
</style>

<style>.az-mobile-mega-nav{display:none}@media(max-width:880px){.az-mobile-mega-nav{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;padding:0 20px}.az-mobile-mega-nav a{display:flex;align-items:center;justify-content:center;min-height:30px;border-radius:30px;padding:8px 3px;color:#090b19;text-decoration:none;font-size:12px;text-align:center;overflow-wrap:anywhere;border:1px solid #d5dbea}.navbar .mega-menu{max-height:100dvh;overflow-y:auto}.az-mobile-mega-nav a:focus-visible{outline:2px solid #2563eb;outline-offset:2px}}</style>
<style>.az-mobile-menu-group{min-width:0}.az-mobile-menu-group summary{cursor:pointer;padding:10px 4px;font-size:11px;text-align:center;color:#172033}.az-mobile-menu-group details a{margin:4px 0;border-radius:8px}.az-mobile-menu-group details[open]{background:#f4f6fa}</style>
