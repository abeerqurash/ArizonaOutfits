@forelse (($navigationMenuItems ?? collect()) as $menuItem)
    <a href="{{ $menuItem->resolved_url }}" class="footer-nav-link" @if ($menuItem->open_in_new_tab) target="_blank" rel="noopener" @endif>
        <div class="link-item-text">{{ $menuItem->label }}</div>
        <i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i>
    </a>
@empty
    <a href="/about" class="footer-nav-link"><div class="link-item-text">About</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/projects" class="footer-nav-link"><div class="link-item-text">Projects</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/services" class="footer-nav-link"><div class="link-item-text">Services</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/blogs" class="footer-nav-link"><div class="link-item-text">Blogs</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/contact" class="footer-nav-link"><div class="link-item-text">Contact</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/category" class="footer-nav-link"><div class="link-item-text">Categories</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/privacy-policy" class="footer-nav-link"><div class="link-item-text">Privacy Policy</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
    <a href="/terms-and-conditions" class="footer-nav-link"><div class="link-item-text">Terms &amp; Conditions</div><i class="fa-solid fa-arrow-right-long footer-nav-right-arrow"></i></a>
@endforelse
