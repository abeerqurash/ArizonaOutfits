<?php
namespace App\Http\Controllers\Admin;
use App\Models\NavigationMenu;
use App\Models\NavigationMenuItem;
use App\Models\Page;
use App\Services\NavigationMenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class AdminNavigationMenuController extends AdminController
{
    public function index(): View
    {
        NavigationMenu::firstOrCreate(['location'=>'mega_categories'],['name'=>'Mega Menu Product Categories','is_active'=>true]);
        $productCategories=\App\Models\ProductCategory::orderBy('title')->get();
        $menus=NavigationMenu::with(['items.page'])->orderBy('id')->get();$pages=Page::published()->orderBy('title')->get(['id','title','slug']);
        $routes=['home-page'=>'Home','about-page'=>'About','blogs-page'=>'Blogs','products.index'=>'Products','cart.index'=>'Cart','contact-page'=>'Contact','customer.dashboard'=>'My Account'];
        return view('admin.navigation-menus.index',compact('menus','pages','routes','productCategories'));
    }
    public function storeItem(Request $request,NavigationMenu $menu,NavigationMenuService $service): RedirectResponse
    {
        $data=$this->validatedForMenu($request,$menu);$data['navigation_menu_id']=$menu->id;$data['position']=$menu->items()->max('position')+1;$this->normalize($data);\Illuminate\Support\Facades\DB::transaction(function()use($menu,$data){
            NavigationMenu::whereKey($menu->id)->lockForUpdate()->firstOrFail();
            if($menu->location==='mega_categories' && $menu->items()->count()>=15)throw \Illuminate\Validation\ValidationException::withMessages(['category_id'=>'The mega menu allows a maximum of 15 categories.']);
            if($menu->location==='mega_categories' && $menu->items()->where('url',$data['url'])->exists())throw \Illuminate\Validation\ValidationException::withMessages(['category_id'=>'This category is already in the mega menu.']);
            NavigationMenuItem::create($data);
        });$service->forget();return back()->with('success','Menu item added.');
    }
    public function updateItem(Request $request,NavigationMenuItem $item,NavigationMenuService $service): RedirectResponse
    {
        $data=$this->validatedForMenu($request,$item->menu);$this->normalize($data);$item->update($data);$service->forget();return back()->with('success','Menu item updated.');
    }
    public function reorder(Request $request,NavigationMenu $menu,NavigationMenuService $service): RedirectResponse
    {
        $data=$request->validate(['items'=>['required','array','max:100'],'items.*'=>['integer','distinct',\Illuminate\Validation\Rule::exists('navigation_menu_items','id')->where('navigation_menu_id',$menu->id)]]);
        \Illuminate\Support\Facades\DB::transaction(function()use($menu,$data){
            NavigationMenu::whereKey($menu->id)->lockForUpdate()->firstOrFail();
            $allowed=$menu->items()->lockForUpdate()->pluck('id')->map(fn($id)=>(int)$id)->sort()->values()->all();
            $requested=collect($data['items'])->map(fn($id)=>(int)$id)->sort()->values()->all();
            if($requested!==$allowed)throw \Illuminate\Validation\ValidationException::withMessages(['items'=>'Refresh the page and reorder all current menu items.']);
            foreach(array_values($data['items'])as $position=>$id)$menu->items()->whereKey($id)->update(['position'=>$position]);
        });$service->forget();return back()->with('success','Menu order saved.');
    }
    public function destroyItem(NavigationMenuItem $item,NavigationMenuService $service): RedirectResponse{$item->delete();$service->forget();return back()->with('success','Menu item removed.');}
    private function validatedForMenu(Request $request,NavigationMenu $menu): array
    {
        if($menu->location!=='mega_categories')return $this->validated($request);
        $data=$request->validate(['category_id'=>['required','integer','exists:product_categories,id']]);
        $category=\App\Models\ProductCategory::findOrFail($data['category_id']);
        return ['label'=>$category->title,'link_type'=>'custom','route_name'=>'product-category-id:'.$category->id,'url'=>'/product-category/'.$category->slug,'is_active'=>true,'open_in_new_tab'=>false];
    }
    private function validated(Request $request): array
    {
        return $request->validate(['label'=>['required','string','max:100'],'link_type'=>['required','in:page,route,custom'],'page_id'=>['nullable','required_if:link_type,page','exists:pages,id'],'route_name'=>['nullable','required_if:link_type,route','string',Rule::in(['home-page','about-page','blogs-page','products.index','cart.index','contact-page','customer.dashboard'])],'url'=>['nullable','required_if:link_type,custom','string','max:1000',function($a,$v,$fail){if($v&&!\App\Services\SafeContentUrl::allowed($v))$fail('Enter a safe internal path or complete HTTP/HTTPS URL.');}],'is_active'=>['nullable','boolean'],'open_in_new_tab'=>['nullable','boolean']]);
    }
    private function normalize(array &$data):void{$data['is_active']=(bool)($data['is_active']??false);$data['open_in_new_tab']=(bool)($data['open_in_new_tab']??false);if($data['link_type']!=='page')$data['page_id']=null;if($data['link_type']!=='route'&&!str_starts_with($data['route_name']??'','product-category-id:'))$data['route_name']=null;if($data['link_type']!=='custom')$data['url']=null;}
}
