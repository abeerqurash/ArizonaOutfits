<?php
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
app('view')->getFinder()->prependLocation(__DIR__.'/_staged/resources/views');
$r=Illuminate\Http\Request::create('/products','GET',['search'=>'test','min_price'=>10]);$r->setLaravelSession(app('session')->driver());app()->instance('request',$r);
$html=(new App\Http\Controllers\ShopController)->index($r)->render();$doc=new DOMDocument;@$doc->loadHTML($html);$details=$doc->getElementById('shop-filter-box');if(!$details || $details->hasAttribute('open'))throw new RuntimeException('Filter not closed');if(!str_contains($html,'value="test"') || !str_contains($html,'value="10"'))throw new RuntimeException('Selected values lost');echo "PASS: filter box renders closed and preserves search/price values.\n";
$compiled=app('blade.compiler')->compileString(file_get_contents(__DIR__.'/_staged/resources/views/products/index.blade.php'));file_put_contents(__DIR__.'/compiled-check.php',$compiled);