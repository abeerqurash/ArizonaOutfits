<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/_staged/app/Models/Page.php';
require __DIR__.'/_staged/app/Http/Controllers/CmsPageController.php';
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
$page=App\Models\Page::create(['title'=>'Shipping Policy','slug'=>'shipping-policy','status'=>'published','published_at'=>now()->subMinute(),'render_mode'=>'editor','content'=>'Shipping details']);
if(!str_ends_with($page->public_url,'/shipping-policy'))throw new RuntimeException('Bad URL');echo 'PASS: CMS public URL has no page prefix.'.PHP_EOL;
$request=Illuminate\Http\Request::create('/shipping-policy');app()->instance('request',$request);
$view=(new App\Http\Controllers\CmsPageController)->resolve('shipping-policy');if($view->name()!=='pages.show')throw new RuntimeException('Wrong view');echo 'PASS: root resolver selects published CMS page.'.PHP_EOL;
$request=Illuminate\Http\Request::create('/page/shipping-policy');$route=new Illuminate\Routing\Route('GET','page/{slug}',fn()=>null);$route->name('pages.show');$request->setRouteResolver(fn()=>$route);app()->instance('request',$request);
$response=(new App\Http\Controllers\CmsPageController)->show('shipping-policy');if($response->getStatusCode()!==301||!str_ends_with($response->getTargetUrl(),'/shipping-policy'))throw new RuntimeException('Redirect failed');echo 'PASS: legacy URL redirects permanently to root URL.'.PHP_EOL;
$page->update(['status'=>'draft']);try{(new App\Http\Controllers\CmsPageController)->show('shipping-policy');throw new RuntimeException('Draft public');}catch(Illuminate\Database\Eloquent\ModelNotFoundException $e){echo 'PASS: draft CMS page unavailable.'.PHP_EOL;}
