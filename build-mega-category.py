from pathlib import Path
root=Path(r'C:\xampp\htdocs\ArizonaOutfits'); out=root/'mega-menu-category-files'; out.mkdir(exist_ok=True)
c=(root/'app/Http/Controllers/Admin/AdminNavigationMenuController.php').read_text()
c=c.replace('$menus=NavigationMenu::', "NavigationMenu::firstOrCreate(['location'=>'mega_categories'],['name'=>'Mega Menu Product Categories','is_active'=>true]);\n        $productCategories=\\App\\Models\\ProductCategory::orderBy('title')->get();\n        $menus=NavigationMenu::",1).replace("compact('menus','pages','routes')","compact('menus','pages','routes','productCategories')")
c=c.replace("$data=$this->validated($request);$data['navigation_menu_id']", "$data=$this->validatedForMenu($request,$menu);$data['navigation_menu_id']",1)
old="$this->normalize($data);NavigationMenuItem::create($data);"
new="""$this->normalize($data);\\Illuminate\\Support\\Facades\\DB::transaction(function()use($menu,$data){
            NavigationMenu::whereKey($menu->id)->lockForUpdate()->firstOrFail();
            if($menu->location==='mega_categories' && $menu->items()->count()>=15)throw \\Illuminate\\Validation\\ValidationException::withMessages(['category_id'=>'The mega menu allows a maximum of 15 categories.']);
            if($menu->location==='mega_categories' && $menu->items()->where('url',$data['url'])->exists())throw \\Illuminate\\Validation\\ValidationException::withMessages(['category_id'=>'This category is already in the mega menu.']);
            NavigationMenuItem::create($data);
        });"""
c=c.replace(old,new)
c=c.replace('$data=$this->validated($request);$this->normalize($data);$item->update($data);', '$data=$this->validatedForMenu($request,$item->menu);$this->normalize($data);$item->update($data);')
pos=c.index('    private function validated(Request')
c=c[:pos]+'''    private function validatedForMenu(Request $request,NavigationMenu $menu): array
    {
        if($menu->location!=='mega_categories')return $this->validated($request);
        $data=$request->validate(['category_id'=>['required','integer','exists:product_categories,id']]);
        $category=\\App\\Models\\ProductCategory::findOrFail($data['category_id']);
        return ['label'=>$category->title,'link_type'=>'custom','url'=>'/product-category/'.$category->slug,'is_active'=>true,'open_in_new_tab'=>false];
    }
'''+c[pos:]
s=(root/'app/Services/NavigationMenuService.php').read_text().replace("Cache::forget('navigation-menu:footer');", "Cache::forget('navigation-menu:footer');Cache::forget('navigation-menu:mega_categories');")
pos=s.index('    public function forget')
s=s[:pos]+'''    public function megaCategories(): Collection
    {
        // Resolve live category records so deleted categories disappear and edited slugs stay valid.
        return $this->items('mega_categories')->take(15)->map(function($item){
            $slug=basename((string)parse_url($item->url,PHP_URL_PATH));
            return \\App\\Models\\ProductCategory::where('slug',$slug)->first();
        })->filter()->values();
    }
'''+s[pos:]
# Store category ID in existing page-independent route_name to survive slug edits.
c=c.replace("'url'=>'/product-category/'.$category->slug", "'route_name'=>'product-category-id:'.$category->id,'url'=>'/product-category/'.$category->slug")
c=c.replace("if($data['link_type']!=='route')$data['route_name']=null;", "if($data['link_type']!=='route'&&!str_starts_with($data['route_name']??'','product-category-id:'))$data['route_name']=null;")
s=s.replace("$slug=basename((string)parse_url($item->url,PHP_URL_PATH));\n            return \\App\\Models\\ProductCategory::where('slug',$slug)->first();", "$id=str_starts_with((string)$item->route_name,'product-category-id:')?(int)substr($item->route_name,20):0;\n            return $id?\\App\\Models\\ProductCategory::find($id):null;")
h=(root/'resources/views/partials/header.blade.php').read_text()
a=h.index('                <div class="categories-wrapper">'); b=h.index('\n            </div>\n        </div>',a)
h=h[:a]+'''                <div class="categories-wrapper">
                    <div class="categories-description"><div class="title">Product Categories</div>
                        <a href="{{ route('products.index') }}" class="btn-style-2 fs-12 text-color-white justify-self-end"><div class="button-text text-uppercase letter-space-3px">View All Products</div></a>
                    </div>
                    <div class="category-list"><div class="list-collection az-mega-category-grid">
                        @forelse(app(\\App\\Services\\NavigationMenuService::class)->megaCategories() as $megaCategory)
                        <div class="list-item"><a href="{{ route('products.category',$megaCategory->slug) }}" class="list"><div class="list-description"><div class="list-item-text">{{ $megaCategory->title }}</div></div><i class="fa-solid fa-arrow-right-long right-arrow-icon"></i></a></div>
                        @empty<p>No product categories selected yet.</p>@endforelse
                    </div></div>
                </div>'''+h[b:]
h+='''\n<style>
.navbar .az-mega-category-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:0 24px;width:100%}
.navbar .az-mega-category-grid .list-item{width:100%;min-width:0}
.navbar .az-mega-category-grid .list-item-text{overflow-wrap:anywhere}
@media(max-width:600px){.navbar .az-mega-category-grid{grid-template-columns:1fr}}
</style>\n'''
v=(root/'resources/views/admin/navigation-menus/index.blade.php').read_text()
a=v.index('<div class="builder-grid">'); b=v.index('\n@endforeach</div>',a)
normal=v[a:b]
special='''@if($menu->location==='mega_categories')
<div class="builder-grid"><div class="items-list" data-sort-list>
@forelse($menu->items as $item)
@php($selectedCategory=$productCategories->firstWhere('id',(int)substr((string)$item->route_name,20)))
<article class="menu-item-card" data-item-id="{{ $item->id }}"><div class="move-buttons"><button type="button" data-move="up" aria-label="Move up">↑</button><button type="button" data-move="down" aria-label="Move down">↓</button></div><div>{{ $selectedCategory?->title ?? 'Deleted category — remove this link' }}</div><form method="POST" action="{{ route('admin.navigation-menus.items.destroy',$item) }}">@csrf @method('DELETE')<button class="delete-item" aria-label="Remove category">×</button></form></article>
@empty<p>Select categories to display in the mega menu.</p>@endforelse
</div><aside class="add-item"><h3>Add product category</h3><p>Maximum 15 categories. Three per row on desktop. Use the arrows and Save order to arrange them.</p><form method="POST" action="{{ route('admin.navigation-menus.items.store',$menu) }}">@csrf<label>Product category<select name="category_id" required><option value="">Choose category</option>@foreach($productCategories as $category)<option value="{{ $category->id }}">{{ $category->title }}</option>@endforeach</select></label><button @disabled($menu->items->count()>=15)>Add category</button></form></aside></div></section>
@else
'''
v=v[:a]+special+normal+'\n@endif'+v[b:]
files=[('01_AdminNavigationMenuController.php.txt','app/Http/Controllers/Admin/AdminNavigationMenuController.php',c),('02_NavigationMenuService.php.txt','app/Services/NavigationMenuService.php',s),('03_header.blade.php.txt','resources/views/partials/header.blade.php',h),('04_index.blade.php.txt','resources/views/admin/navigation-menus/index.blade.php',v)]
for name,dest,content in files:
 (out/name).write_text(content,encoding='utf-8'); p=out/'_staged'/dest;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(content,encoding='utf-8')
(out/'INSTALL.txt').write_text('\n'.join(name+' -> C:\\xampp\\htdocs\\ArizonaOutfits\\'+dest.replace('/','\\') for name,dest,_ in files)+'\n\nCopy full contents into each destination. Run C:\\xampp\\php\\php.exe artisan optimize:clear from the project folder. Open Admin > Menu Builder > Mega Menu Product Categories. Select a category and click Add category. Repeat up to 15. Arrange with arrows and Save order. Remove with ×. Refresh storefront. No migration needed; opening Menu Builder creates the panel. Existing header/footer links remain intact.',encoding='utf-8')
print('Created four replacements and installation instructions.')
