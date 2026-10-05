<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;
class NavigationMenuItem extends Model
{
    protected $fillable=['navigation_menu_id','page_id','label','link_type','url','route_name','position','is_active','open_in_new_tab'];
    protected $casts=['position'=>'integer','is_active'=>'boolean','open_in_new_tab'=>'boolean'];
    public function menu(): BelongsTo{return $this->belongsTo(NavigationMenu::class,'navigation_menu_id');}
    public function page(): BelongsTo{return $this->belongsTo(Page::class);}
    public function getCommerceBehaviorAttribute(): ?string
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
    public function getResolvedUrlAttribute(): string
    {
        if($this->commerce_behavior==='account')return route(auth('web')->check()?'customer.dashboard':'login');
        if($this->commerce_behavior==='favorites')return route(auth('web')->check()?'favorites.index':'login');
        if(in_array($this->commerce_behavior,['cart_page','cart_sidebar'],true))return route('cart.index');
        if($this->link_type==='page') return $this->page?->status==='published'?$this->page->public_url:'#';
        if($this->link_type==='route'&&$this->route_name&&Route::has($this->route_name)) return route($this->route_name);
        $url=trim((string)$this->url);
        if(str_starts_with($url,'/')&&!str_starts_with($url,'//'))return url($url);
        $scheme=strtolower((string)parse_url($url,PHP_URL_SCHEME));
        return filter_var($url,FILTER_VALIDATE_URL)&&in_array($scheme,['http','https'],true)?$url:'#';
    }
}