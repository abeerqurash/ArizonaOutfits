<?php
namespace App\Services;
use App\Models\NavigationMenu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
class NavigationMenuService
{
    public function items(string $location): Collection
    {
        if(!Schema::hasTable('navigation_menus')||!Schema::hasTable('navigation_menu_items'))return collect();
        return Cache::remember('navigation-menu:'.$location,3600,function()use($location){$menu=NavigationMenu::query()->where('location',$location)->where('is_active',true)->first();return $menu?$menu->activeItems()->with('page')->get():collect();});
    }
    public function forget(): void{Cache::forget('navigation-menu:header');Cache::forget('navigation-menu:footer');}
}
