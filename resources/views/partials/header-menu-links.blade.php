@forelse(($navigationMenuItems??collect()) as $menuItem)
<a href="{{ $menuItem->resolved_url }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header" @if($menuItem->open_in_new_tab) target="_blank" rel="noopener" @endif><div class="button-text text-uppercase letter-space-3px">{{ $menuItem->label }}</div></a>
@empty
<a href="{{ route('home-page') }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header"><div class="button-text text-uppercase letter-space-3px">Home</div></a>
<a href="{{ route('about-page') }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header"><div class="button-text text-uppercase letter-space-3px">About Us</div></a>
<a href="{{ route('blogs-page') }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header"><div class="button-text text-uppercase letter-space-3px">Blogs</div></a>
<a href="{{ route('products.index') }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header"><div class="button-text text-uppercase letter-space-3px">Shop</div></a>
<a href="{{ route('contact-page') }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header"><div class="button-text text-uppercase letter-space-3px">Contact</div></a>
@endforelse

@auth
    <a href="{{ route('customer.dashboard') }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header">
        <div class="button-text text-uppercase letter-space-3px">My Account</div>
    </a>
@else
    <a href="{{ route('login') }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header">
        <div class="button-text text-uppercase letter-space-3px">Login</div>
    </a>
@endauth
