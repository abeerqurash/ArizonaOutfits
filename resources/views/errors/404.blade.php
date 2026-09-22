@extends('layouts.app')

@section('title', '404 - Page Not Found | Arizona Outfits')
@section('meta_description', 'The page you are looking for could not be found. Explore Arizona Outfits and discover our latest products.')

@push('page-styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | Arizona Outfits — 404
    |--------------------------------------------------------------------------
    | - Real shared header/footer
    | - No inner left/right scrollbar
    | - Desktop 404 hero fits the available screen below the header
    | - Right image remains contained inside the visible hero
    | - Softer/wider fade between content and image
    | - Responsive desktop -> mobile
    */

    html,
    body {
        background: #ffffff;
    }

    .arizona-404 {
        --ao-dark: #080c1c;
        --ao-border: rgba(8, 12, 28, .14);
        --ao-muted: #687083;
        position: relative;
        overflow: hidden;
        background: #ffffff;
        color: var(--ao-dark);
    }

    .arizona-404__layout {
        display: grid;
        grid-template-columns: minmax(0, 56%) minmax(0, 44%);
        width: 100%;
        height: calc(100vh - var(--ao-header-height, 0px));
        min-height: 540px;
        max-height: 860px;
        height: 100vh;
        overflow: hidden;
    }

    .arizona-404__left {
        position: relative;
        z-index: 4;
        min-width: 0;
        height: 100%;
        overflow: hidden;
        background: #ffffff;
    }

    .arizona-404__content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
        height: 100%;
        min-height: 0;
        padding:
            clamp(24px, 4vh, 48px)
            clamp(30px, 5vw, 76px)
            clamp(22px, 3.5vh, 42px);
        box-sizing: border-box;
        background-color: #fbf9f5;
    }

    .arizona-404__eyebrow {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 0 0 clamp(10px, 1.8vh, 18px);
        color: var(--ao-dark);
        font-size: 9px;
        font-weight: 500;
        letter-spacing: 4px;
        text-transform: uppercase;
    }

    .arizona-404__eyebrow::before {
        width: 30px;
        height: 1px;
        flex: 0 0 auto;
        background: rgba(8, 12, 28, .3);
        content: "";
    }

    .arizona-404__mark {
        display: block;
        width: min(100%, 540px);
        height: auto;
        margin: 0;
        object-fit: contain;
        object-position: left center;
    }

    .arizona-404__title {
        max-width: 650px;
        margin: clamp(22px, 3.4vh, 34px) 0 clamp(10px, 1.8vh, 16px);
        color: var(--ao-dark);
        font-size: clamp(34px, 3.45vw, 56px);
        font-weight: 600;
        line-height: 1;
        letter-spacing: -2px;
    }

    .arizona-404__copy {
        max-width: 590px;
        margin: 0;
        color: var(--ao-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .arizona-404__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: clamp(18px, 2.7vh, 26px);
    }

    .arizona-404__actions .btn-style-2 {
        min-width: 162px;
    }

    .arizona-404__search {
        display: flex;
        width: 100%;
        max-width: 600px;
        margin-top: clamp(16px, 2.4vh, 22px);
        border: 1px solid var(--ao-border);
        background: #ffffff;
    }

    .arizona-404__search-field {
        position: relative;
        flex: 1;
        min-width: 0;
    }

    .arizona-404__search-field > i {
        position: absolute;
        top: 50%;
        left: 18px;
        z-index: 1;
        color: var(--ao-dark);
        font-size: 12px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .arizona-404__search input {
        display: block;
        width: 100%;
        height: 46px;
        padding: 0 16px 0 45px;
        border: 0;
        border-radius: 0;
        outline: 0;
        background: transparent;
        color: var(--ao-dark);
        font: inherit;
        font-size: 11px;
        box-shadow: none;
    }

    .arizona-404__search input::placeholder {
        color: #7c8391;
        opacity: 1;
    }

    .arizona-404__search-submit {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 108px;
        min-width: 108px;
        overflow: hidden;
        border: 0;
        border-left: 1px solid var(--ao-border);
        border-radius: 0;
        background: var(--ao-dark);
        color: #ffffff;
        font: inherit;
        cursor: pointer;
    }

    .arizona-404__search-submit .button-text {
        font-size: 9px;
        transition: transform .18s ease;
        will-change: transform;
    }

    .arizona-404__quick-links {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        width: 100%;
        max-width: 600px;
        margin-top: clamp(15px, 2.2vh, 22px);
        padding-top: clamp(13px, 2vh, 18px);
        border-top: 1px solid var(--ao-border);
    }

    .arizona-404__quick-link {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        padding-right: 12px;
        color: var(--ao-dark);
        text-decoration: none;
    }

    .arizona-404__quick-link + .arizona-404__quick-link {
        padding-left: 14px;
        border-left: 1px solid var(--ao-border);
    }

    .arizona-404__quick-link i {
        flex: 0 0 auto;
        width: 22px;
        text-align: center;
        font-size: 17px;
    }

    .arizona-404__quick-link strong,
    .arizona-404__quick-link small {
        display: block;
    }

    .arizona-404__quick-link strong {
        margin-bottom: 2px;
        font-size: 9px;
        font-weight: 600;
    }

    .arizona-404__quick-link small {
        color: #7c8391;
        font-size: 8px;
    }

    .arizona-404__right {
        position: relative;
        z-index: 1;
        min-width: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        background: #e9e1d6;
    }

    .arizona-404__right img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: 54% center;
    }

    /*
     * Wider multi-stage fade so the transition feels like the reference:
     * no hard vertical split and no obvious single gradient edge.
     */
    .arizona-404__fade {
        position: absolute;
        top: 0;
        bottom: 0;
        left: -155px;
        z-index: 2;
        width: 270px;
        pointer-events: none;
        background:
            linear-gradient(
                90deg,
                #ffffff 0%,
                rgba(255, 255, 255, .99) 12%,
                rgba(255, 255, 255, .93) 28%,
                rgba(255, 255, 255, .72) 48%,
                rgba(255, 255, 255, .42) 67%,
                rgba(255, 255, 255, .16) 84%,
                rgba(255, 255, 255, 0) 100%
            );
    }

    .arizona-404__fade::after {
        position: absolute;
        inset: 0;
        content: "";
        background: linear-gradient(
            90deg,
            rgba(255,255,255,.48) 0%,
            rgba(255,255,255,.16) 58%,
            transparent 100%
        );
        filter: blur(12px);
    }

    /*
     * Compact desktop heights such as 1366x768.
     * Shrink vertically rather than creating an inner scrollbar.
     */
    @media (min-width: 821px) and (max-height: 760px) {
        .arizona-404__layout {
            min-height: 650px;
        }

        .arizona-404__content {
            padding-top: 60px;
            padding-bottom: 20px;
        }

        .arizona-404__eyebrow {
            margin-bottom: 10px;
        }

        .arizona-404__title {
            margin-top: 20px;
            margin-bottom: 9px;
            font-size: clamp(31px, 3vw, 45px);
        }

        .arizona-404__copy {
            font-size: 10px;
            line-height: 1.55;
        }

        .arizona-404__actions {
            margin-top: 16px;
        }

        .arizona-404__search {
            margin-top: 14px;
        }

        .arizona-404__search input {
            height: 42px;
        }

        .arizona-404__quick-links {
            margin-top: 13px;
            padding-top: 11px;
        }
    }

    @media (min-width: 821px) and (max-width: 1100px) {
        .arizona-404__layout {
            grid-template-columns: minmax(0, 60%) minmax(0, 40%);
        }

        .arizona-404__content {
            padding-right: 28px;
            padding-left: 30px;
        }

        .arizona-404__title {
            font-size: clamp(32px, 4.4vw, 46px);
        }

        .arizona-404__fade {
            left: -120px;
            width: 220px;
        }
    }

    /*
     * Tablet/mobile: normal document flow is preferable to forcing a
     * viewport-locked split layout. No inner scrollbars are introduced.
     */
    @media (max-width: 820px) {
        .arizona-404 {
            overflow: visible;
        }

        .arizona-404__layout {
            display: flex;
            height: auto !important;
            min-height: 0;
            max-height: none;
            overflow: visible;
            flex-direction: column;
        }

        .arizona-404__left {
            order: 1;
            height: auto;
            overflow: visible;
        }

        .arizona-404__content {
            height: auto;
            min-height: 0;
            padding: 58px 24px 44px;
        }

        .arizona-404__title {
            max-width: 580px;
            margin-top: 28px;
            font-size: clamp(36px, 8vw, 52px);
        }

        .arizona-404__copy {
            font-size: 12px;
        }

        .arizona-404__search input {
            height: 52px;
        }

        .arizona-404__quick-links {
            max-width: 620px;
        }

        .arizona-404__right {
            order: 2;
            height: min(72vw, 570px);
            min-height: 430px;
        }

        .arizona-404__right img {
            object-position: 55% center;
        }

        .arizona-404__fade {
            top: -100px;
            right: 0;
            bottom: auto;
            left: 0;
            width: 100%;
            height: 175px;
            background: linear-gradient(
                180deg,
                #ffffff 0%,
                rgba(255,255,255,.98) 18%,
                rgba(255,255,255,.78) 43%,
                rgba(255,255,255,.38) 70%,
                rgba(255,255,255,0) 100%
            );
        }

        .arizona-404__fade::after {
            background: linear-gradient(
                180deg,
                rgba(255,255,255,.4),
                transparent
            );
        }
    }

    @media (max-width: 560px) {
        .arizona-404__content {
            padding: 48px 17px 36px;
        }

        .arizona-404__eyebrow {
            font-size: 8px;
            letter-spacing: 3px;
        }

        .arizona-404__title {
            margin-top: 24px;
            font-size: 34px;
            line-height: 1.02;
            letter-spacing: -1.3px;
        }

        .arizona-404__copy {
            font-size: 11px;
        }

        .arizona-404__actions {
            display: grid;
            grid-template-columns: 1fr;
            gap: 9px;
        }

        .arizona-404__actions .btn-style-2 {
            width: 100%;
            min-width: 0;
        }

        .arizona-404__search {
            flex-direction: column;
        }

        .arizona-404__search-submit {
            width: 100%;
            min-width: 0;
            height: 46px;
            border-top: 1px solid var(--ao-border);
            border-left: 0;
        }

        .arizona-404__quick-links {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .arizona-404__quick-link {
            padding: 0;
        }

        .arizona-404__quick-link + .arizona-404__quick-link {
            padding: 12px 0 0;
            border-top: 1px solid var(--ao-border);
            border-left: 0;
        }

        .arizona-404__right {
            height: 450px;
            min-height: 0;
        }

        .arizona-404__right img {
            object-position: 58% center;
        }
    }

    @media (max-width: 380px) {

        .arizona-404__title {
            font-size: 30px;
        }

        .arizona-404__right {
            height: 395px;
        }
    }
    @media (min-width: 821px) and (max-height: 760px) {
        .arizona-404__mark {
            width: min(100%, 455px);
        }
    }

    @media (max-width: 820px) {
        .arizona-404__mark {
            width: min(100%, 500px);
        }
    }

    @media (max-width: 560px) {
        .arizona-404__mark {
            width: min(100%, 390px);
        }
    }

    @media (max-width: 380px) {
        .arizona-404__mark {
            width: min(100%, 330px);
        }
    }

</style>
@endpush

@section('content')
<section class="arizona-404" aria-labelledby="arizona404Title">
    <div class="arizona-404__layout">

        <div class="arizona-404__left">
            <div class="arizona-404__content">

                <div class="arizona-404__eyebrow">
                    Page Not Found
                </div>

                <img
                    class="arizona-404__mark"
                    src="{{ asset('asset/media/arizona-404-mark.webp') }}"
                    alt="404"
                    width="1536"
                    height="796"
                >

                <h1
                    id="arizona404Title"
                    class="arizona-404__title"
                >
                    Looks like you've taken a wrong turn!
                </h1>

                <p class="arizona-404__copy">
                    The page you're looking for doesn't exist or may have been moved.
                    But don't worry, there are plenty of great outfits waiting for you.
                </p>

                <div class="arizona-404__actions">
                    <a
                        href="{{ url('/') }}"
                        class="btn-style-2 fs-12 text-color-white justify-self-start"
                    >
                        <div class="button-text text-uppercase letter-space-3px">
                            Go to Homepage
                        </div>
                    </a>

                    <a
                        href="{{ route('products.index') }}"
                        class="btn-style-2 fs-12 text-color-white justify-self-start"
                    >
                        <div class="button-text text-uppercase letter-space-3px">
                            Shop Now
                        </div>
                    </a>
                </div>

                <form
                    class="arizona-404__search"
                    action="{{ route('products.index') }}"
                    method="GET"
                    role="search"
                >
                    <div class="arizona-404__search-field">
                        <i
                            class="fa-solid fa-magnifying-glass"
                            aria-hidden="true"
                        ></i>

                        <input
                            type="search"
                            name="search"
                            placeholder="Search for jackets, collections or anything..."
                            aria-label="Search products"
                        >
                    </div>

                    <button
                        type="submit"
                        class="arizona-404__search-submit"
                    >
                        <span class="button-text text-uppercase letter-space-3px">
                            Search
                        </span>
                    </button>
                </form>

                <div class="arizona-404__quick-links">
                    <a
                        href="{{ route('products.index') }}"
                        class="arizona-404__quick-link"
                    >
                        <i
                            class="fa-solid fa-bag-shopping"
                            aria-hidden="true"
                        ></i>

                        <span>
                            <strong>Shop Products</strong>
                            <small>Find your style</small>
                        </span>
                    </a>

                    <a
                        href="{{ url('/contact') }}"
                        class="arizona-404__quick-link"
                    >
                        <i
                            class="fa-regular fa-comments"
                            aria-hidden="true"
                        ></i>

                        <span>
                            <strong>Need Help?</strong>
                            <small>Contact us</small>
                        </span>
                    </a>

                    <a
                        href="{{ url('/favorites') }}"
                        class="arizona-404__quick-link"
                    >
                        <i
                            class="fa-regular fa-heart"
                            aria-hidden="true"
                        ></i>

                        <span>
                            <strong>Favourites</strong>
                            <small>Saved outfits</small>
                        </span>
                    </a>
                </div>

            </div>
        </div>

        <div
            class="arizona-404__right"
            aria-hidden="true"
        >
            <img
                src="{{ asset('asset/media/arizona-404-couple-desert.webp') }}"
                alt=""
            >

            <div class="arizona-404__fade"></div>
        </div>

    </div>
</section>
@endsection

@push('page-scripts')
<script>
(function () {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Measure the REAL shared header
    |--------------------------------------------------------------------------
    | This prevents the 404 hero from beginning underneath the fixed/overlay
    | navbar. The hero then uses exactly the remaining visible viewport height.
    */

    const page404 =
        document.querySelector('.arizona-404');

    function sync404HeaderHeight() {
        if (!page404) {
            return;
        }

        const navbar =
            document.querySelector('.navbar');

        if (!navbar) {
            page404.style.setProperty(
                '--ao-header-height',
                '0px'
            );

            return;
        }

        const navbarRect =
            navbar.getBoundingClientRect();

        const headerBottom =
            Math.max(
                0,
                Math.min(
                    window.innerHeight,
                    navbarRect.bottom
                )
            );

        page404.style.setProperty(
            '--ao-header-height',
            headerBottom + 'px'
        );
    }

    sync404HeaderHeight();

    window.addEventListener(
        'resize',
        sync404HeaderHeight
    );

    window.addEventListener(
        'load',
        sync404HeaderHeight,
        { once: true }
    );

    /*
    |--------------------------------------------------------------------------
    | Canonical Arizona Outfits button movement
    |--------------------------------------------------------------------------
    */

    const movingButtons = document.querySelectorAll(
        '.arizona-404 .btn-style-1,' +
        '.arizona-404 .btn-style-2,' +
        '.arizona-404 .btn-style-3,' +
        '.arizona-404__search-submit'
    );

    movingButtons.forEach(function (button) {
        const buttonText =
            button.querySelector('.button-text');

        if (!buttonText) {
            return;
        }

        button.addEventListener(
            'mousemove',
            function (event) {
                const rect =
                    button.getBoundingClientRect();

                const x =
                    event.clientX -
                    rect.left -
                    rect.width / 2;

                const y =
                    event.clientY -
                    rect.top -
                    rect.height / 2;

                buttonText.style.transform =
                    'translate(' +
                    (x / 6) +
                    'px, ' +
                    (y / 6) +
                    'px) scale(1.12)';
            }
        );

        button.addEventListener(
            'mouseleave',
            function () {
                buttonText.style.transform =
                    'translate(0, 0) scale(1)';
            }
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Shared header fallback
    |--------------------------------------------------------------------------
    */

    if (typeof window.toggleMenu !== 'function') {
        window.toggleMenu = function () {
            document
                .querySelector('.menu-wrapper')
                ?.classList.toggle('active');

            document
                .querySelector('.menu-button')
                ?.classList.toggle('active');

            document
                .querySelector('.mega-menu')
                ?.classList.toggle('active');
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Error-page preloader fallback
    |--------------------------------------------------------------------------
    */

    function hide404Preloader() {
        const preloader =
            document.querySelector('.preloader');

        if (!preloader) {
            return;
        }

        preloader.style.opacity = '0';
        preloader.style.visibility = 'hidden';
        preloader.style.pointerEvents = 'none';

        window.setTimeout(function () {
            preloader.style.display = 'none';

            /*
             * Re-measure after the preloader disappears because the navbar
             * is now the only relevant top overlay.
             */
            sync404HeaderHeight();
        }, 450);
    }

    if (document.readyState === 'complete') {
        hide404Preloader();
    } else {
        window.addEventListener(
            'load',
            hide404Preloader,
            { once: true }
        );
    }
})();
</script>
@endpush
