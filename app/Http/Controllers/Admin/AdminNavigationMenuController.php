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
        $menus=NavigationMenu::with(['items.page'])->orderBy('id')->get();$pages=Page::published()->orderBy('title')->get(['id','title','slug']);
        $routes=['home-page'=>'Home','about-page'=>'About','blogs-page'=>'Blogs','products.index'=>'Products','cart.index'=>'Cart','contact-page'=>'Contact','customer.dashboard'=>'My Account'];
        return view('admin.navigation-menus.index',compact('menus','pages','routes'));
    }
    public function storeItem(Request $request,NavigationMenu $menu,NavigationMenuService $service): RedirectResponse
    {
        $data=$this->validated($request);$data['navigation_menu_id']=$menu->id;$data['position']=$menu->items()->max('position')+1;$this->normalize($data);NavigationMenuItem::create($data);$service->forget();return back()->with('success','Menu item added.');
    }
    public function updateItem(Request $request,NavigationMenuItem $item,NavigationMenuService $service): RedirectResponse
    {
        $data=$this->validated($request);$this->normalize($data);$item->update($data);$service->forget();return back()->with('success','Menu item updated.');
    }
    public function reorder(Request $request,NavigationMenu $menu,NavigationMenuService $service): RedirectResponse
    {
        $data=$request->validate(['items'=>['required','array','max:100'],'items.*'=>['integer']]);$allowed=$menu->items()->pluck('id')->map(fn($id)=>(int)$id)->all();
        foreach(array_values($data['items']) as $position=>$id)if(in_array((int)$id,$allowed,true))NavigationMenuItem::whereKey($id)->where('navigation_menu_id',$menu->id)->update(['position'=>$position]);
        $service->forget();return back()->with('success','Menu order saved.');
    }
    public function destroyItem(NavigationMenuItem $item,NavigationMenuService $service): RedirectResponse{$item->delete();$service->forget();return back()->with('success','Menu item removed.');}
    private function validated(Request $request): array{return $request->validate(['label'=>['required','string','max:100'],'link_type'=>['required','in:page,route,custom'],'page_id'=>['nullable','required_if:link_type,page','exists:pages,id'],'route_name'=>['nullable','required_if:link_type,route','string',Rule::in(['home-page','about-page','blogs-page','products.index','cart.index','contact-page','customer.dashboard'])],'url'=>['nullable','required_if:link_type,custom','string','max:1000',function($a,$v,$fail){if(!$v)return;if(str_starts_with($v,'/')&&!str_starts_with($v,'//'))return;$scheme=strtolower((string)parse_url($v,PHP_URL_SCHEME));if(!filter_var($v,FILTER_VALIDATE_URL)||!in_array($scheme,['http','https'],true))$fail('Enter an internal path beginning with / or a complete HTTP/HTTPS URL.');}],'is_active'=>['nullable','boolean'],'open_in_new_tab'=>['nullable','boolean']]);}
    private function normalize(array &$data):void{$data['is_active']=(bool)($data['is_active']??false);$data['open_in_new_tab']=(bool)($data['open_in_new_tab']??false);if($data['link_type']!=='page')$data['page_id']=null;if($data['link_type']!=='route')$data['route_name']=null;if($data['link_type']!=='custom')$data['url']=null;}
}
