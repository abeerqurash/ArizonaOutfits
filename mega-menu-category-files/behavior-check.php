<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/_staged/app/Services/NavigationMenuService.php';
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
foreach(['navigation_menu_items','navigation_menus'] as $table)if(!Schema::hasTable($table))Schema::create($table,function(Blueprint $t)use($table){$t->id();if($table==='navigation_menus'){$t->string('name');$t->string('location');$t->boolean('is_active')->default(true);}else{$t->unsignedBigInteger('navigation_menu_id');$t->unsignedBigInteger('page_id')->nullable();$t->string('label');$t->string('link_type');$t->string('url')->nullable();$t->string('route_name')->nullable();$t->integer('position');$t->boolean('is_active')->default(true);$t->boolean('open_in_new_tab')->default(false);}$t->timestamps();});
$menu=App\Models\NavigationMenu::create(['name'=>'Categories','location'=>'mega_categories','is_active'=>true]);
for($i=1;$i<=16;$i++){$c=App\Models\ProductCategory::create(['title'=>'Collection '.$i,'slug'=>'collection-'.$i]);$ids[]=$c->id;App\Models\NavigationMenuItem::create(['navigation_menu_id'=>$menu->id,'label'=>$c->title,'link_type'=>'custom','url'=>'/product-category/'.$c->slug,'route_name'=>'product-category-id:'.$c->id,'position'=>$i,'is_active'=>true]);}
$service=new App\Services\NavigationMenuService;$service->forget();
function megaCheck($value,$msg){if(!$value)throw new RuntimeException($msg);echo 'PASS: '.$msg.PHP_EOL;}
megaCheck($service->megaCategories()->count()===15,'Storefront caps display at 15 categories.');
App\Models\ProductCategory::whereKey($ids[0])->update(['title'=>'Renamed','slug'=>'renamed']);
megaCheck($service->megaCategories()->first()->slug==='renamed','Slug edits resolve using category ID, despite cached menu items.');
App\Models\ProductCategory::whereKey($ids[0])->delete();
megaCheck(!$service->megaCategories()->contains('id',$ids[0]),'Deleted category is omitted.');
$menu->items()->where('position',2)->update(['is_active'=>false]);$service->forget();
megaCheck(!$service->megaCategories()->contains('id',$ids[1]),'Hidden menu entries are excluded.');

require __DIR__.'/_staged/app/Http/Controllers/Admin/AdminNavigationMenuController.php';
$controller=new App\Http\Controllers\Admin\AdminNavigationMenuController;
try{$controller->storeItem(Illuminate\Http\Request::create('/','POST',['category_id'=>$ids[2]]),$menu,$service);throw new RuntimeException('Limit failed');}catch(Illuminate\Validation\ValidationException $e){megaCheck(str_contains($e->getMessage(),'maximum of 15'),'Server rejects adding a sixteenth category.');}
