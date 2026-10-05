from pathlib import Path
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'dynamic-commerce-menu-files';o.mkdir(exist_ok=True)
files=[]
def save(p,s):
 n=f'{len(files)+1:02d}_'+Path(p).name+'.txt';(o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8');files.append((n,p))
s=(r/'app/Http/Controllers/Admin/AdminNavigationMenuController.php').read_text(encoding='utf-8-sig');s=s.replace("'cart.index'=>'Cart'","'cart.index'=>'Cart page','cart.sidebar'=>'Cart sidebar','favorites.index'=>'Favorites','login'=>'Account (Login / My Account)'",1);s=s.replace("'cart.index','contact-page'","'cart.index','cart.sidebar','favorites.index','login','contact-page'",1);s=s.replace("'customer.dashboard'=>'My Account'","'customer.dashboard'=>'Account (Login / My Account)'",1);save('app/Http/Controllers/Admin/AdminNavigationMenuController.php',s)
s=(r/'app/Models/NavigationMenuItem.php').read_text(encoding='utf-8-sig');pos=s.index('    public function getResolvedUrlAttribute')
s=s[:pos]+'''    public function getCommerceBehaviorAttribute(): ?string
    {
        $route=$this->link_type==='route'?$this->route_name:null;
        if(in_array($route,['login','customer.dashboard'],true))return 'account';
        if($route==='favorites.index')return 'favorites';
        if($route==='cart.sidebar')return 'cart_sidebar';
        if($route==='cart.index')return 'cart_page';
        if($this->link_type==='custom'){
            $url=trim((string)$this->url);
            $host=parse_url($url,PHP_URL_HOST);
            if($host && $host!==parse_url(config('app.url'),PHP_URL_HOST))return null;
            $path='/'.trim((string)parse_url($url,PHP_URL_PATH),'/');
            if(in_array($path,['/login','/account','/customer/dashboard'],true))return 'account';
            if($path==='/favorites')return 'favorites';
            if($path==='/cart')return 'cart_page';
        }
        return null;
    }
    public function getDisplayLabelAttribute(): string
    {
        return $this->commerce_behavior==='account'?(auth('web')->check()?'My Account':'Login'):$this->label;
    }
'''+s[pos:]
s=s.replace("        if($this->link_type==='page')", "        if($this->commerce_behavior==='account')return route(auth('web')->check()?'customer.dashboard':'login');\n        if($this->commerce_behavior==='favorites')return route(auth('web')->check()?'favorites.index':'login');\n        if(in_array($this->commerce_behavior,['cart_page','cart_sidebar'],true))return route('cart.index');\n        if($this->link_type==='page')",1);save('app/Models/NavigationMenuItem.php',s)
s=(r/'resources/views/partials/header-menu-links.blade.php').read_text(encoding='utf-8-sig');a=s.index('@php\n');b=s.index('@endphp',a)+len('@endphp');counts=s[a:b]
end=s.index('{{-- ============================================================');drawer=s[end:]
head=counts+'''
@php
    $configuredMenuItems=$navigationMenuItems??collect();
    $menuBehaviors=$configuredMenuItems->map(fn($item)=>$item->commerce_behavior)->filter();
@endphp
@forelse($configuredMenuItems as $menuItem)
<a href="{{ $menuItem->resolved_url }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header header-commerce-nav-link" @if($menuItem->commerce_behavior==='cart_sidebar') data-header-cart-trigger @endif @if($menuItem->open_in_new_tab && !$menuItem->commerce_behavior) target="_blank" rel="noopener" @endif>
<div class="button-text text-uppercase letter-space-3px header-commerce-nav-text">
@if($menuItem->commerce_behavior==='favorites')<i class="fa-regular fa-heart" aria-hidden="true"></i>@endif
@if(in_array($menuItem->commerce_behavior,['cart_page','cart_sidebar'],true))<i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>@endif
<span>{{ $menuItem->display_label }}</span>
@if($menuItem->commerce_behavior==='favorites' && auth('web')->check())<span data-header-favorite-count class="header-commerce-nav-count {{ $headerFavoriteCount<1?'is-empty':'' }}">{{ $headerFavoriteCount }}</span>@endif
@if(in_array($menuItem->commerce_behavior,['cart_page','cart_sidebar'],true))<span data-header-cart-count class="header-commerce-nav-count {{ $headerCartCount<1?'is-empty':'' }}">{{ $headerCartCount }}</span>@endif
</div></a>
@empty
@foreach(['about-page'=>'About Us','blogs-page'=>'Blogs','products.index'=>'Shop','contact-page'=>'Contact'] as $name=>$label)
<a href="{{ route($name) }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header"><div class="button-text text-uppercase letter-space-3px">{{ $label }}</div></a>
@endforeach
@endforelse
@if(!$menuBehaviors->contains('favorites'))
<a href="{{ route(auth('web')->check()?'favorites.index':'login') }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header header-commerce-nav-link"><div class="button-text text-uppercase letter-space-3px header-commerce-nav-text"><i class="fa-regular fa-heart" aria-hidden="true"></i><span>Favorites</span>@auth('web')<span data-header-favorite-count class="header-commerce-nav-count {{ $headerFavoriteCount<1?'is-empty':'' }}">{{ $headerFavoriteCount }}</span>@endauth</div></a>
@endif
@if(!$menuBehaviors->contains('cart_page') && !$menuBehaviors->contains('cart_sidebar'))
<a href="{{ route('cart.index') }}" data-header-cart-trigger class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header header-commerce-nav-link"><div class="button-text text-uppercase letter-space-3px header-commerce-nav-text"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i><span>Cart</span><span data-header-cart-count class="header-commerce-nav-count {{ $headerCartCount<1?'is-empty':'' }}">{{ $headerCartCount }}</span></div></a>
@endif
@if(!$menuBehaviors->contains('account'))
<a href="{{ route(auth('web')->check()?'customer.dashboard':'login') }}" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header"><div class="button-text text-uppercase letter-space-3px">{{ auth('web')->check()?'My Account':'Login' }}</div></a>
@endif

'''
drawer=drawer.replace('const trigger = document.querySelector("[data-header-cart-trigger]");','const triggers = document.querySelectorAll("[data-header-cart-trigger]");').replace('if (!drawer || !trigger) {','if (!drawer || !triggers.length) {').replace('trigger.addEventListener("click", function (event) {','triggers.forEach(function(trigger) { trigger.addEventListener("click", function (event) {',1).replace('        openCartDrawer();\n    });','        openCartDrawer();\n    }); });',1)
save('resources/views/partials/header-menu-links.blade.php',head+drawer)
s=(r/'resources/views/partials/footer-menu-links.blade.php').read_text(encoding='utf-8-sig').replace('{{ $menuItem->label }}','{{ $menuItem->display_label }}').replace('class="footer-nav-link" @if','class="footer-nav-link" @if($menuItem->commerce_behavior===\'cart_sidebar\') data-header-cart-trigger @endif @if',1);save('resources/views/partials/footer-menu-links.blade.php',s)
(o/'INSTALL.txt').write_text('\n\n'.join(n+'\nC:\\xampp\\htdocs\\ArizonaOutfits\\'+p.replace('/','\\') for n,p in files)+'\n\nCopy all four files. Run php artisan optimize:clear. In Menu Builder choose Website route, then Account (Login / My Account), Favorites, Cart page or Cart sidebar. Add both cart entries if desired. A cart page entry navigates normally; sidebar entry opens the drawer. Existing account links resolve dynamically. No migration required.',encoding='utf-8')
