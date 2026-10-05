<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/_staged/app/Models/NavigationMenuItem.php';
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
auth('web')->logout();app('view')->getFinder()->prependLocation(__DIR__.'/_staged/resources/views');
$items=collect([
new App\Models\NavigationMenuItem(['link_type'=>'route','route_name'=>'customer.dashboard','label'=>'Login']),
new App\Models\NavigationMenuItem(['link_type'=>'route','route_name'=>'favorites.index','label'=>'Favorites']),
new App\Models\NavigationMenuItem(['link_type'=>'route','route_name'=>'cart.index','label'=>'Cart page']),
new App\Models\NavigationMenuItem(['link_type'=>'route','route_name'=>'cart.sidebar','label'=>'Cart sidebar'])]);
function commerceCheck($p,$m){if(!$p)throw new RuntimeException($m);echo 'PASS: '.$m.PHP_EOL;}
commerceCheck($items[0]->display_label==='Login'&&str_ends_with($items[0]->resolved_url,'/login'),'Guest account label and login URL.');
$html=view('partials.header-menu-links',['navigationMenuItems'=>$items])->render();
commerceCheck(substr_count($html,'<span>Favorites</span>')===1,'Configured favorites appears once.');
commerceCheck(substr_count($html,'data-header-cart-trigger')>=1&&str_contains($html,'Cart page')&&str_contains($html,'Cart sidebar'),'Both cart options render.');
$user=App\Models\User::create(['name'=>'Menu customer','email'=>'menu@example.com','password'=>bcrypt('secret'),'status'=>'active']);auth('web')->setUser($user);
commerceCheck($items[0]->display_label==='My Account'&&str_contains($items[0]->resolved_url,'/account'),'Logged-in account label and dashboard URL.');
$html=view('partials.header-menu-links',['navigationMenuItems'=>$items])->render();commerceCheck(str_contains($html,'data-header-favorite-count'),'Logged-in favorite count rendered.');
foreach(['header-menu-links','footer-menu-links'] as $view){$s=file_get_contents(__DIR__.'/_staged/resources/views/partials/'.$view.'.blade.php');$out=__DIR__.'/'.$view.'.compiled.php';file_put_contents($out,app('blade.compiler')->compileString($s));passthru('C:\\xampp\\php\\php.exe -l '.escapeshellarg($out));}

