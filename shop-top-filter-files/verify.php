<?php
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
spl_autoload_register(function($class){if($class==='App\Http\Controllers\ShopController')require __DIR__.'/_staged/app/Http/Controllers/ShopController.php';},true,true);
app('view')->getFinder()->prependLocation(__DIR__.'/_staged/resources/views');
for($i=1;$i<=16;$i++)App\Models\Product::create(['title'=>'Top filter fixture '.$i,'slug'=>'top-filter-'.$i,'status'=>'active','regular_price'=>10,'stock'=>5]);
$r=Illuminate\Http\Request::create('/products','GET');$r->setLaravelSession(app('session')->driver());app()->instance('request',$r);
$controller=new App\Http\Controllers\ShopController;$view=$controller->index($r);$data=$view->getData();if($data['products']->perPage()!==15 || $data['products']->count()!==15 || $data['products']->total()!==16)throw new RuntimeException('Pagination mismatch');echo "PASS: 15 products on first page of 16.\n";
$html=$view->render();if(str_contains($html,'Shop by Category') || !str_contains($html,'az-shop-filters'))throw new RuntimeException('Layout mismatch');echo "PASS: top filter page renders with category cards removed.\n";
$r=Illuminate\Http\Request::create('/products','GET',['search'=>'Top filter fixture 16','min_price'=>5,'max_price'=>15]);$r->setLaravelSession(app('session')->driver());app()->instance('request',$r);$data=$controller->index($r)->getData();if($data['products']->total()!==1)throw new RuntimeException('Combined filters failed');echo "PASS: search and price filters combine correctly.\n";
$compiled=app('blade.compiler')->compileString(file_get_contents(__DIR__.'/_staged/resources/views/products/index.blade.php'));file_put_contents(__DIR__.'/compiled-check.php',$compiled);