@php($footerSettings=app(\App\Services\StoreSettingsService::class)->settings())
<div class="footer-section">
    <div class="wrapper">
        <div class="footer-wrapper">
            <div class="footer-logo-heading">
                <a href="/" class="text-decoration-none"><h2 class="text-color-white">ARIZONA OUTFITS</h2></a>
            </div>
            <div class="follow-social"><div class="subtitle">Follow Us</div><div class="social-link-list">@foreach(['facebook'=>['Facebook','facebook-f'],'instagram'=>['Instagram','instagram'],'linkedin'=>['LinkedIn','linkedin'],'youtube'=>['YouTube','youtube'],'x'=>['X','x-twitter']] as $network=>$details)@php($socialUrl=$footerSettings->{$network.'_url'})@if($socialUrl && \App\Services\SafeContentUrl::allowed($socialUrl))<a href="{{ $socialUrl }}" class="list-item" target="_blank" rel="noopener noreferrer"><div class="social-link-icon"><i class="fa-brands fa-{{ $details[1] }} text-color-dark"></i></div><div class="social-link-text">{{ $details[0] }}</div><i class="fa-solid fa-arrow-right-long social-right-arrow"></i></a>@endif
@endforeach
</div></div>
            <div class="navigation-link">
                <div class="subtitle">Navigation</div>
                <div class="navigation-wrapper">
                    @include('partials.footer-menu-links')
                </div>
            </div>
            <div class="about-description">
                <h3>About</h3>
                <p class="about-para">{{ $footerSettings->footer_about ?: 'Discover the latest collections at Arizona Outfits.' }}</p>
            </div>
            <div class="footer-copyright"><div>{{ $footerSettings->footer_copyright ?: '© '.date('Y').' '.($footerSettings->store_name ?: 'Arizona Outfits').'. All rights reserved.' }}</div></div>
        </div>
    </div>
    <div class="strip-wrapper">
        <div class="wrapper">
            <div class="strip-container">
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
            </div>
        </div>
    </div>
</div>
