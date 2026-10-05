<?php
$root = dirname(__DIR__);
$stage = __DIR__.'/_staged';
require $root.'/vendor/autoload.php';
spl_autoload_register(function ($class) use ($stage) {
    if (str_starts_with($class, 'App\\')) {
        $path = $stage.'/app/'.str_replace('\\', '/', substr($class, 4)).'.php';
        if (is_file($path)) require $path;
    }
}, true, true);
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','cache.default'=>'array']);
Illuminate\Support\Facades\DB::purge('sqlite');
$checks = [];
function verify($name, $condition) { global $checks; $checks[$name]=(bool)$condition; if (!$condition) throw new RuntimeException($name); }
Illuminate\Support\Facades\Schema::create('products',function($t){$t->id();$t->string('title');$t->string('status');$t->timestamps();});
$migration = require $stage.'/database/migrations/2026_10_04_000001_create_catalog_order_state_table.php';
$migration->up();
for ($i=0;$i<18;$i++) App\Models\Product::create(['title'=>'Item '.$i,'status'=>'active']);
$service = app(App\Services\CatalogOrderService::class);
$order = fn()=> $service->apply(App\Models\Product::query())->pluck('id')->all();
$first = $order();
verify('Repeated catalog loads keep ordering', $first===$order());
$generation=Illuminate\Support\Facades\DB::table('catalog_order_state')->value('generation');
App\Models\Product::first()->update(['title'=>'Edited']);
verify('Product edits preserve generation',$generation===Illuminate\Support\Facades\DB::table('catalog_order_state')->value('generation'));
App\Models\Product::create(['title'=>'New product','status'=>'active']);
verify('Product creation advances generation',$generation+1===Illuminate\Support\Facades\DB::table('catalog_order_state')->value('generation'));
verify('Product addition reshuffles catalog',$first!==array_values(array_filter($order(),fn($id)=>$id<=18)));
$popular=$service->apply(App\Models\Product::query())->take(6)->pluck('id')->all();
$trending=$service->apply(App\Models\Product::query()->whereNotIn('products.id',$popular))->take(6)->pluck('id')->all();
verify('Home sections do not overlap',count($trending)===6&&!array_intersect($popular,$trending));
$measure = app(App\Services\CustomMeasurementsService::class);
$options=[['option_id'=>1,'option_name'=>'Size','value_id'=>4,'value_label'=>'Custom']];
$input=['chest'=>40,'waist'=>34,'shoulder'=>18,'sleeve_length'=>25,'body_length'=>29];
$normalized=$measure->validate($options,$input);
verify('All five measurements normalized',count($normalized)===5&&$normalized['chest']==='40.00');
$missing=$input;unset($missing['waist']);
try {$measure->validate($options,$missing);verify('Missing measurements rejected',false);}catch(Illuminate\Validation\ValidationException $e){verify('Missing measurements rejected',isset($e->errors()['custom_measurements.waist']));}
verify('Ordinary sizes discard measurement payload',$measure->validate([['option_name'=>'Size','value_label'=>'M']],$input)===[]);
verify('Color Custom does not trigger custom sizing',$measure->validate([['option_name'=>'Color','value_label'=>'Custom']],$input)===[]);
$snapshot=$measure->orderOptions($options,$input);
verify('Order snapshot contains measurements',count($snapshot)===6&&$snapshot[1]['option_name']==='Chest (inches)');
$compiler=app('blade.compiler');
foreach(json_decode(file_get_contents(__DIR__.'/changed.json'),true) as $path){
 if(!str_ends_with($path,'.blade.php'))continue;
 $compiled=$compiler->compileString(file_get_contents($stage.'/'.$path));
 $target=__DIR__.'/compiled-check.php';file_put_contents($target,$compiled);
 exec('"'.PHP_BINARY.'" -l '.escapeshellarg($target).' 2>&1',$out,$code);
 verify('Blade compiles: '.$path,$code===0);$out=[];
}
@unlink(__DIR__.'/compiled-check.php');
$router=app('router');$router->setRoutes(new Illuminate\Routing\RouteCollection());
require $stage.'/routes/web.php';$router->getRoutes()->refreshNameLookups();
verify('Articles use blog/slug',$router->getRoutes()->getByName('blog-show')->uri()==='blog/{slug}');
verify('CMS root resolver remains',$router->getRoutes()->getByName('content.resolve')->uri()==='{slug}');
Illuminate\Support\Facades\Schema::create('product_categories',function($t){$t->id();$t->string('title');$t->string('slug');$t->unsignedBigInteger('parent_id')->nullable();$t->timestamps();});
$parent=App\Models\ProductCategory::create(['title'=>'Men Outfits','slug'=>'men-outfits']);
App\Models\ProductCategory::create(['title'=>'Jackets','slug'=>'men-jackets','parent_id'=>$parent->id]);
$navigation=app(App\Services\NavigationMenuService::class);
$item=new App\Models\NavigationMenuItem(['link_type'=>'custom','url'=>'/product-category/men-outfits']);
verify('Custom category link resolves its children',$navigation->categoryFor($item)?->children->count()===1);
$external=new App\Models\NavigationMenuItem(['link_type'=>'custom','url'=>'https://example.com/product-category/men-outfits']);
verify('External category links remain external',$navigation->categoryFor($external)===null);
file_put_contents(__DIR__.'/checks.json',json_encode($checks,JSON_PRETTY_PRINT));
echo count($checks)." checks passed\n";
