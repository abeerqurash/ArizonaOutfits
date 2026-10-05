<?php
$root=dirname(__DIR__);$stage=__DIR__.'/_staged';require $root.'/vendor/autoload.php';
spl_autoload_register(function($class)use($stage){if(str_starts_with($class,'App\\')){$p=$stage.'/app/'.str_replace('\\','/',substr($class,4)).'.php';if(is_file($p))require $p;}},true,true);
$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','cache.default'=>'array','session.driver'=>'array']);
Illuminate\Support\Facades\DB::purge('sqlite');
$fixtureMigrations=__DIR__.'/fixture-migrations';if(!is_dir($fixtureMigrations))mkdir($fixtureMigrations);
foreach(glob($root.'/database/migrations/*.php') as $file){$source=file_get_contents($file);$source=preg_replace('/DB::statement\(.*?\);/s','/* MySQL-only legacy data conversion omitted in this empty SQLite fixture. */',$source);file_put_contents($fixtureMigrations.'/'.basename($file),$source);}
foreach(glob($fixtureMigrations.'/*.php') as $file){try{$migration=require $file;if(!is_object($migration)){$class=Illuminate\Support\Str::studly(implode('_',array_slice(explode('_',basename($file,'.php')),4)));$migration=new $class;}$migration->up();}catch(Illuminate\Database\QueryException $error){if(!str_contains($error->getMessage(),'admins.id'))throw $error;}}
foreach(glob($stage.'/database/migrations/*.php') as $file) if(!is_file($root.'/database/migrations/'.basename($file)))(require $file)->up();
app('view')->getFinder()->setPaths([$stage.'/resources/views',$root.'/resources/views']);
app('view')->share('errors',new Illuminate\Support\ViewErrorBag());
config(['size_charts'=>require $root.'/config/size_charts.php']);
$app->usePublicPath($root.'/public');
app('router')->setRoutes(new Illuminate\Routing\RouteCollection());require $root.'/routes/web.php';app('router')->getRoutes()->refreshNameLookups();
$session=app('session')->driver();$session->start();
$request=Illuminate\Http\Request::create('http://127.0.0.1:8000/');$request->setLaravelSession($session);$app->instance('request',$request);
for($i=1;$i<=14;$i++)App\Models\Product::create(['title'=>'Test Outfit '.$i,'slug'=>'test-outfit-'.$i,'sku'=>'OUT-'.$i,'status'=>'active','stock'=>10,'regular_price'=>120]);
App\Models\Review::withoutEvents(fn()=>App\Models\Review::create(['product_id'=>1,'name'=>'Fixture customer','email'=>'fixture@example.com','rating'=>5,'review'=>'A fixture review with no title field.','status'=>'approved']));
$category=App\Models\ProductCategory::create(['title'=>'Men Outfits','slug'=>'men-outfits']);$category->products()->attach(App\Models\Product::pluck('id'));
App\Models\ProductCategory::create(['title'=>'Men Jackets','slug'=>'men-jackets','parent_id'=>$category->id]);
$women=App\Models\ProductCategory::create(['title'=>'Women Outfits','slug'=>'women-outfits']);App\Models\ProductCategory::create(['title'=>'Women Jackets','slug'=>'women-jackets','parent_id'=>$women->id]);
$menu=App\Models\NavigationMenu::firstOrCreate(['location'=>'header'],['name'=>'Header','is_active'=>true]);
foreach([$category,$women] as $position=>$parent)App\Models\NavigationMenuItem::create(['navigation_menu_id'=>$menu->id,'label'=>$parent->title,'link_type'=>'custom','url'=>'/product-category/'.$parent->slug,'position'=>$position,'is_active'=>true]);
$admin=App\Models\Admin::first();
foreach(glob($root.'/resources/views/blogs/posts/*.blade.php') as $file){$slug=basename($file,'.blade.php');App\Models\Post::create(['title'=>ucwords(str_replace('-',' ',$slug)),'slug'=>$slug,'template'=>$slug,'expert'=>'Fixture excerpt','excerpt'=>'Fixture excerpt','content'=>'Fixture article','status'=>'published','published_at'=>now()->subDay(),'author_id'=>$admin?->id]);}
for($i=1;$i<=2;$i++)App\Models\Post::create(['title'=>'Extra article '.$i,'slug'=>'extra-article-'.$i,'template'=>'my-first-business-blog','expert'=>'Fixture excerpt','excerpt'=>'Fixture excerpt','content'=>'Fixture article','status'=>'published','published_at'=>now()->subDay(),'author_id'=>$admin?->id]);
$output=__DIR__.'/rendered';if(!is_dir($output))mkdir($output);
$results=[];
$save=function($name,$view)use($output,&$results,$session,$app){$publicPath=match($name){'home'=>'/','contact'=>'/contact','shop'=>'/products','category'=>'/product-category/men-outfits','product'=>'/product/test-outfit-1',default=>'/blog/'.$name};$request=Illuminate\Http\Request::create('http://127.0.0.1:8000'.$publicPath);$request->setLaravelSession($session);$route=app('router')->getRoutes()->match($request);$request->setRouteResolver(fn()=>$route);$app->instance('request',$request);$html=$view->render();file_put_contents($output.'/'.$name.'.html',$html);$results[$name]=['bytes'=>strlen($html),'title'=>preg_match('~<title>~',$html)===1];};
$save('home',app(App\Http\Controllers\HomeController::class)->index());
$save('contact',view('contact'));
$save('shop',app(App\Http\Controllers\ShopController::class)->index($request));
$save('category',app(App\Http\Controllers\ShopController::class)->category('men-outfits',$request));
foreach(App\Models\Post::where('slug','not like','extra-article-%')->get() as $post)$save($post->slug,app(App\Http\Controllers\BlogController::class)->show($post->slug));
$save('product',app(App\Http\Controllers\ShopController::class)->show('test-outfit-1'));
file_put_contents(__DIR__.'/render-checks.json',json_encode($results,JSON_PRETTY_PRINT));echo count($results)." full pages rendered using isolated database\n";
