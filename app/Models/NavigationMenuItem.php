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
    public function getResolvedUrlAttribute(): string
    {
        if($this->link_type==='page') return $this->page?->status==='published'?$this->page->public_url:'#';
        if($this->link_type==='route'&&$this->route_name&&Route::has($this->route_name)) return route($this->route_name);
        $url=trim((string)$this->url);
        if(str_starts_with($url,'/')&&!str_starts_with($url,'//'))return url($url);
        $scheme=strtolower((string)parse_url($url,PHP_URL_SCHEME));
        return filter_var($url,FILTER_VALIDATE_URL)&&in_array($scheme,['http','https'],true)?$url:'#';
    }
}
