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
    public function megaCategories(): Collection
    {
        // Resolve live category records so deleted categories disappear and edited slugs stay valid.
        return $this->items('mega_categories')->take(15)->map(function($item){
            $id=str_starts_with((string)$item->route_name,'product-category-id:')?(int)substr($item->route_name,20):0;
            return $id?\App\Models\ProductCategory::find($id):null;
        })->filter()->values();
    }
    public function categoryFor($item): ?\App\Models\ProductCategory
    {
        $url = $item->resolved_url;
        $host = parse_url($url, PHP_URL_HOST);
        if ($host && strtolower($host) !== strtolower((string) parse_url(url('/'), PHP_URL_HOST))) return null;
        $path = trim(rawurldecode((string) parse_url($url, PHP_URL_PATH)), '/');
        if (!preg_match('~^(?:product-category/)?([^/]+)$~', $path, $match)) return null;
        static $categories = null;
        $categories ??= \App\Models\ProductCategory::with(['children' => fn($query) => $query->orderBy('title')])->get()->keyBy('slug');
        return $categories->get($match[1]) ?? $categories->get(strtolower($match[1]));
    }
    public function forget(): void{Cache::forget('navigation-menu:header');Cache::forget('navigation-menu:footer');Cache::forget('navigation-menu:mega_categories');}
}
