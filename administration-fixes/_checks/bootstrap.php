<?php
require __DIR__.'/../../vendor/autoload.php';
spl_autoload_register(function($class){if(str_starts_with($class,'App\\')){$path=__DIR__.'/../_staged/app/'.str_replace('\\','/',substr($class,4)).'.php';if(is_file($path))require $path;}},true,true);
require __DIR__.'/../../administration-audit/_checks/bootstrap.php';
(require __DIR__.'/../../database/migrations/2026_10_01_000001_repair_inventory_purchasing_schema.php')->up();
config(['view.compiled'=>__DIR__.'/compiled']);@mkdir(__DIR__.'/compiled',0777,true);
$app['view']->getFinder()->setPaths([__DIR__.'/../_staged/resources/views',resource_path('views')]);
// Load the exact staged route file, with its unchanged auth include in the fixture directory.
$app['router']->setRoutes(new Illuminate\Routing\RouteCollection);
require __DIR__.'/../_staged/routes/web.php';
$app['router']->getRoutes()->refreshNameLookups();$app['router']->getRoutes()->refreshActionLookups();$app['url']->setRoutes($app['router']->getRoutes());
Illuminate\Support\Facades\Schema::table('orders',function(Illuminate\Database\Schema\Blueprint $t){foreach(['shipping_method','estimated_delivery','billing_state','billing_zip','shipping_address','shipping_city','shipping_state','shipping_zip','order_notes'] as $name)if(!Illuminate\Support\Facades\Schema::hasColumn('orders',$name))$t->string($name)->nullable();foreach(['tax','shipping_price'] as $name)if(!Illuminate\Support\Facades\Schema::hasColumn('orders',$name))$t->decimal($name,12,2)->default(0);});
