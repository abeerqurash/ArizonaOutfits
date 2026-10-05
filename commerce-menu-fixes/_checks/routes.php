<?php
require __DIR__.'/../../vendor/autoload.php';
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\')) {
        $path = __DIR__.'/../_staged/app/'.str_replace('\\','/',substr($class,4)).'.php';
        if (is_file($path)) require $path;
    }
}, true, true);
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app['router']->setRoutes(new Illuminate\Routing\RouteCollection);
require __DIR__.'/../_staged/routes/web.php';
$app['router']->getRoutes()->refreshNameLookups();
$app['router']->getRoutes()->refreshActionLookups();
$app['view']->getFinder()->prependLocation(__DIR__.'/../_staged/resources/views');
$routes=$app['router']->getRoutes();$prefixes=['admin.orders.','admin.payment-verifications.','admin.products.','admin.product-categories.','admin.product-tags.','admin.coupons.','customer.orders.'];$checks=[];
foreach($routes as $route){$name=$route->getName();if(!$name || !collect($prefixes)->contains(fn($p)=>str_starts_with($name,$p)))continue;$errors=[];$action=$route->getActionName();$views=[];
if($action!=='Closure' && str_contains($action,'@')){[$class,$method]=explode('@',$action,2);if(!class_exists($class)||!method_exists($class,$method))$errors[]='Missing controller method';else{$ref=new ReflectionMethod($class,$method);if(!$ref->isPublic())$errors[]='Non-public controller method';$body=implode('',array_slice(file($ref->getFileName()),$ref->getStartLine()-1,$ref->getEndLine()-$ref->getStartLine()+1));preg_match_all("~(?:view|loadView)\(\s*['\"]([^'\"]+)['\"]~",$body,$m);foreach(array_unique($m[1]) as $view){$views[]=$view;if(!Illuminate\Support\Facades\View::exists($view))$errors[]='Missing view '.$view;}}}
$params=[];foreach($route->parameterNames() as $parameter)$params[$parameter]=str_contains(strtolower($parameter),'slug')?'fixture':1;
try{$url=route($name,$params,false);$matched=$routes->match(Illuminate\Http\Request::create($url,$route->methods()[0]));if($matched->getName()!==$name)$errors[]='Matched other route '.$matched->getName();}catch(Throwable $e){$errors[]=$e->getMessage();}
$checks[]=['route'=>$name,'methods'=>$route->methods(),'uri'=>$route->uri(),'action'=>$action,'views'=>$views,'middleware'=>$route->gatherMiddleware(),'errors'=>$errors];}
$directories=['admin/orders','admin/payment-verifications','admin/products','admin/product-categories','admin/product-tags','admin/coupons','customer/orders'];$viewCalls=[];
foreach($directories as $directory){$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../../resources/views/'.$directory));foreach($iterator as $file){if(!$file->isFile()||!str_ends_with($file->getFilename(),'.blade.php'))continue;$content=file_get_contents($file->getPathname());preg_match_all("~route\(\s*['\"]([^'\"]+)['\"]~",$content,$m);foreach(array_unique($m[1]) as $name)$viewCalls[]=['view'=>str_replace('\\','/',$file->getPathname()),'route'=>$name,'registered'=>$routes->getByName($name)!==null];}}
file_put_contents(__DIR__.'/../route-checks.json',json_encode(['endpoints'=>$checks,'literal_view_route_references'=>$viewCalls],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo count($checks).' endpoints audited; '.count(array_filter($checks,fn($r)=>$r['errors']))." with route/action/view errors.\n";
echo count($viewCalls).' literal named-route references across requested views; '.count(array_filter($viewCalls,fn($r)=>!$r['registered']))." unregistered.\n";
foreach($checks as $r)if($r['errors'])echo $r['route'].': '.implode('; ',$r['errors'])."\n";
foreach($viewCalls as $r)if(!$r['registered'])echo $r['route'].' in '.$r['view']."\n";
