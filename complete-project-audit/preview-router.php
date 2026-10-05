<?php
$project=dirname(__DIR__);$audit=__DIR__;$staged=$audit.'/replacement-files/_staged';
$uri=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)?:'/');
if(str_contains($uri,'..')){http_response_code(404);exit;}
if(str_starts_with($uri,'/asset/')||str_starts_with($uri,'/storage/')||$uri==='/robots.txt'){
 $path=str_starts_with($uri,'/storage/')?$audit.'/preview-storage/'.substr($uri,9):$staged.'/public'.$uri;
 if(!is_file($path))$path=$project.'/public'.$uri;
 if(is_file($path)){$type=match(strtolower(pathinfo($path,PATHINFO_EXTENSION))){'js'=>'application/javascript','css'=>'text/css','svg'=>'image/svg+xml','webp'=>'image/webp','png'=>'image/png','jpg','jpeg'=>'image/jpeg','woff'=>'font/woff','woff2'=>'font/woff2','txt'=>'text/plain',default=>'application/octet-stream'};header('Content-Type: '.$type);header('Cache-Control: public, max-age='.(str_contains($path,'media-cache')?'31536000, immutable':'86400'));if(in_array($type,['application/javascript','text/css','image/svg+xml','text/plain'],true))ob_start('ob_gzhandler');readfile($path);exit;}http_response_code(404);exit;
}
// The preview accepts read-only navigation; application mutations are blocked.
if(!in_array($_SERVER['REQUEST_METHOD'],['GET','HEAD'],true)){http_response_code(405);exit;}
require $audit.'/dependencies/vendor/autoload.php';
spl_autoload_register(function($class)use($project,$staged){if(str_starts_with($class,'App\\')){$relative='/app/'.str_replace('\\','/',substr($class,4)).'.php';$path=is_file($staged.$relative)?$staged.$relative:$project.$relative;if(is_file($path))require $path;}},true,true);
$app=require $project.'/bootstrap/app.php';
$app->booted(function()use($app,$audit,$staged){
 config(['view.compiled'=>$audit.'/preview-compiled','session.driver'=>'array','cache.default'=>'array','queue.default'=>'sync','logging.default'=>'preview_audit','logging.channels.preview_audit'=>['driver'=>'single','path'=>$audit.'/preview.log'],'filesystems.disks.public.root'=>$audit.'/preview-storage']);
 $app['view']->getFinder()->prependLocation($staged.'/resources/views');
 $app['router']->setRoutes(new Illuminate\Routing\RouteCollection);$app['router']->middleware('web')->group($staged.'/routes/web.php');$app['router']->getRoutes()->refreshNameLookups();$app['router']->getRoutes()->refreshActionLookups();
 $app['view']->share('errors',new Illuminate\Support\ViewErrorBag);
});
// Rendering product pages increments views in the normal controller. Use its view directly here.
if(preg_match('#^/product/([^/]+)$#',$uri,$matches)){
 $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
 $product=App\Models\Product::with(['categories','images','tags','options.values','optionValues','variants'])->where('status','active')->where('slug',$matches[1])->first();
 if(!$product){http_response_code(404);exit;}
 $request=Illuminate\Http\Request::capture();$route=$app['router']->getRoutes()->getByName('products.show');$route->bind($request);$request->setRouteResolver(fn()=>$route);$app->instance('request',$request);
 $app['view']->share('errors',new Illuminate\Support\ViewErrorBag);echo view('products.show',['product'=>$product,'relatedProducts'=>collect()])->render();exit;
}
ob_start('ob_gzhandler');$app->handleRequest(Illuminate\Http\Request::capture());
