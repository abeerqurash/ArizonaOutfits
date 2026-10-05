<?php
require __DIR__.'/../../vendor/autoload.php';
spl_autoload_register(function($class){if(str_starts_with($class,'App\\')){$relative='app/'.str_replace('\\','/',substr($class,4)).'.php';foreach([__DIR__.'/../_staged/',__DIR__.'/../../customer-admin-sync-files/_staged/'] as $base){if(is_file($base.$relative)){require $base.$relative;return;}}}},true,true);
require __DIR__.'/../../customer-side-audit/_checks/bootstrap.php';
Illuminate\Support\Facades\Notification::swap(new Illuminate\Notifications\ChannelManager(app()));

$app['view']->getFinder()->setPaths([__DIR__.'/../../customer-admin-sync-files/_staged/resources/views',resource_path('views')]);
$app['router']->setRoutes(new Illuminate\Routing\RouteCollection);
require __DIR__.'/../../customer-admin-sync-files/_staged/routes/web.php';
$app['router']->getRoutes()->refreshNameLookups();$app['router']->getRoutes()->refreshActionLookups();$app['url']->setRoutes($app['router']->getRoutes());
