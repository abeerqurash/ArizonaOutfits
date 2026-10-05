<?php
require __DIR__.'/bootstrap.php';
$rows=[];
foreach(app('router')->getRoutes() as $route){
 $action=$route->getActionName();
 if(!preg_match('/Customer\\\\|Auth\\\\|CheckoutController|StripePaymentController|FavoriteController|ReviewController|OrderTrackingController|ProfileController/',$action))continue;
 if(!str_contains($action,'@'))continue;
 [$class,$method]=explode('@',$action,2);
 $rows[]=['name'=>$route->getName(),'uri'=>$route->uri(),'action'=>$action,'exists'=>class_exists($class)&&method_exists($class,$method),'middleware'=>$route->gatherMiddleware()];
}
file_put_contents(__DIR__.'/../routes.json',json_encode($rows,JSON_PRETTY_PRINT));
echo count($rows).' customer/storefront/auth routes checked; '.count(array_filter($rows,fn($r)=>!$r['exists']))." missing actions.\n";