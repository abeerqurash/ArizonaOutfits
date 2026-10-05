<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/_staged/app/Services/PublicMediaService.php';
require __DIR__.'/_staged/app/Http/Controllers/HomeController.php';
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
app('view')->getFinder()->prependLocation(__DIR__.'/_staged/resources/views');
function storefrontCheck($pass,$name){if(!$pass)throw new RuntimeException($name);echo 'PASS: '.$name.PHP_EOL;}
$media=new App\Services\PublicMediaService;
storefrontCheck(!$media->exists('products/missing.jpg'),'Missing local media detected.');
storefrontCheck(str_contains($media->url('products/missing.jpg'),'product-placeholder.svg'),'Missing local media uses placeholder.');
storefrontCheck($media->url('https://example.com/image.jpg')==='https://example.com/image.jpg','External media URLs retained.');
for($i=1;$i<=8;$i++)App\Models\Product::create(['title'=>'Sync product '.$i,'slug'=>'sync-product-'.$i,'status'=>'active','regular_price'=>25,'stock'=>5,'purchase_count'=>$i]);
App\Models\Product::create(['title'=>'Draft sync','slug'=>'draft-sync','status'=>'draft','regular_price'=>25,'purchase_count'=>999]);
$v=(new App\Http\Controllers\HomeController)->index();$d=$v->getData();
storefrontCheck($d['latestProducts']->count()===6 && $d['popularProducts']->count()===6,'Home fetches six latest and six popular products.');
storefrontCheck(!$d['popularProducts']->contains('slug','draft-sync'),'Draft excluded from popular products.');
storefrontCheck($d['popularProducts']->first()->slug==='sync-product-8','Popularity follows purchase ranking.');
foreach(['home','privacy-policy','terms-and-conditions','about'] as $name){$html=view($name,$d)->render();storefrontCheck(strlen($html)>1000,$name.' renders with shared dependencies.');}
$category=App\Models\ProductCategory::create(['title'=>'Sync Collection','slug'=>'sync-collection']);
$categories=App\Models\ProductCategory::withCount('products')->paginate(15);
$html=view('products.categories',compact('categories'))->render();
storefrontCheck(str_contains($html,'Sync Collection')&&str_contains($html,'product-category/sync-collection'),'Category hub renders project cards and category URLs.');
$reviewProduct=App\Models\Product::where('slug','sync-product-1')->first();
App\Models\Review::create(['product_id'=>$reviewProduct->id,'name'=>'Approved reviewer','rating'=>5,'title'=>'Approved title','review'=>'Visible approved review','status'=>'approved']);
App\Models\Review::create(['product_id'=>$reviewProduct->id,'name'=>'Pending reviewer','rating'=>1,'review'=>'Private pending review','status'=>'pending']);
foreach(['home','privacy-policy','terms-and-conditions'] as $name){$html=view($name,$d)->render();storefrontCheck(str_contains($html,'Visible approved review')&&!str_contains($html,'Private pending review'),$name.' includes approved product reviews only.');}
$blogView=(new App\Http\Controllers\BlogController)->index(Illuminate\Http\Request::create('/blogs','GET',['q'=>'needle']));$html=$blogView->render();storefrontCheck(str_contains($html,'<details class="az-blog-filter-box"')&&str_contains($html,'value="needle"'),'Blog filter collapsible markup retains search.');
$migration=require __DIR__.'/_staged/database/migrations/2026_10_03_000001_add_storefront_footer_settings.php';$migration->up();
App\Models\EcommerceSetting::current();DB::table('ecommerce_settings')->where('id',1)->update(['facebook_url'=>'https://facebook.com/arizona','footer_copyright'=>'Custom copyright']);
$html=view('partials.footer')->render();storefrontCheck(str_contains($html,'https://facebook.com/arizona')&&str_contains($html,'Custom copyright'),'Footer reads dashboard social and copyright settings.');

