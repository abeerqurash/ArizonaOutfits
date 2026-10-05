<?php
require dirname(__DIR__).'/arizona-ui-update/render-check.php';
app('view')->getFinder()->prependLocation(__DIR__.'/_staged/resources/views');
Illuminate\Support\Facades\DB::connection()->getPdo()->sqliteCreateFunction('GREATEST', fn(...$values)=>max($values));
$admin=App\Models\Admin::first();
if(!$admin)$admin=App\Models\Admin::create(['name'=>'Fixture administrator','email'=>'admin-fixture@example.test','password'=>bcrypt('fixture-password'),'status'=>'active']);
auth('admin')->setUser($admin);
$output=__DIR__.'/admin-rendered';if(!is_dir($output))mkdir($output);
$pages=[
 'pages'=>['/admin/pages',App\Http\Controllers\Admin\AdminPageController::class,'index'],
 'navigation'=>['/admin/navigation-menus',App\Http\Controllers\Admin\AdminNavigationMenuController::class,'index'],
 'email-templates'=>['/admin/email-templates',App\Http\Controllers\Admin\AdminEmailTemplateController::class,'index'],
 'payments'=>['/admin/payment-verifications',App\Http\Controllers\Admin\PaymentVerificationController::class,'index'],
 'inventory-alerts'=>['/admin/inventory-alerts',App\Http\Controllers\Admin\InventoryAlertController::class,'index'],
 'settings'=>['/admin/settings',App\Http\Controllers\Admin\EcommerceSettingController::class,'edit'],
 'analytics'=>['/admin/analytics',App\Http\Controllers\Admin\AdminAnalyticsController::class,'index'],
 'users'=>['/admin/admin-users',App\Http\Controllers\Admin\AdminUserController::class,'index'],
 'roles'=>['/admin/admin-roles',App\Http\Controllers\Admin\AdminRoleController::class,'index'],
 'notifications'=>['/admin/notifications',App\Http\Controllers\Admin\AdminNotificationController::class,'index'],
 'audit'=>['/admin/audit-logs',App\Http\Controllers\Admin\AdminAuditLogController::class,'index'],
 'backups'=>['/admin/backups',App\Http\Controllers\Admin\AdminBackupController::class,'index'],
 'products'=>['/admin/products',App\Http\Controllers\Admin\ProductController::class,'index'],
 'create-product'=>['/admin/products/create',App\Http\Controllers\Admin\ProductController::class,'create'],
];
$checks=[];
$size=App\Models\ProductOption::create(['name'=>'Size','type'=>'select']);
$value=App\Models\ProductOptionValue::create(['product_option_id'=>$size->id,'label'=>'Medium','value'=>'medium']);
$fixtureProduct=App\Models\Product::first();$fixtureProduct->options()->attach($size->id);$fixtureProduct->optionValues()->attach($value->id);
App\Models\ProductVariant::create(['product_id'=>$fixtureProduct->id,'sku'=>'VARIANT-FIXTURE-1','regular_price'=>100,'stock'=>5,'options'=>[['option_id'=>$size->id,'option_name'=>'Size','value_id'=>$value->id,'value_label'=>'Medium']]]);
$pages['edit-product']=['/admin/products/'.$fixtureProduct->id.'/edit',App\Http\Controllers\Admin\ProductController::class,'edit'];
foreach($pages as $name=>[$url,$class,$method]){
 try{
 $request=Illuminate\Http\Request::create('http://127.0.0.1:8000'.$url);$request->setLaravelSession($session);$app->instance('request',$request);
 $reflection=new ReflectionMethod($class,$method);$view=$name==='edit-product'?app($class)->edit($fixtureProduct):($reflection->getNumberOfRequiredParameters()?app($class)->$method($request):app($class)->$method());
 file_put_contents($output.'/'.$name.'.html',$view->render());$checks[$name]='rendered';
 }catch(Throwable $error){$checks[$name]=$error->getMessage();}
}
file_put_contents(__DIR__.'/admin-render-checks.json',json_encode($checks,JSON_PRETTY_PRINT));echo json_encode($checks,JSON_PRETTY_PRINT)."\n";
