<?php
require __DIR__.'/../../vendor/autoload.php';
spl_autoload_register(function($class){if(str_starts_with($class,'App\\')){$path=__DIR__.'/../_staged/app/'.str_replace('\\','/',substr($class,4)).'.php';if(is_file($path))require $path;}},true,true);
require __DIR__.'/../../customer-side-audit/_checks/bootstrap.php';
$app['view']->getFinder()->setPaths([__DIR__.'/../_staged/resources/views',resource_path('views')]);
$app['router']->setRoutes(new Illuminate\Routing\RouteCollection);
require __DIR__.'/../_staged/routes/web.php';
$app['router']->getRoutes()->refreshNameLookups();$app['router']->getRoutes()->refreshActionLookups();$app['url']->setRoutes($app['router']->getRoutes());
