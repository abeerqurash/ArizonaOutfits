<?php
require __DIR__.'/render-check.php';
Illuminate\Support\Facades\DB::connection()->getPdo()->sqliteCreateFunction('GREATEST', fn(...$values)=>max($values));
$admin=App\Models\Admin::first();
if(!$admin)$admin=App\Models\Admin::create(['name'=>'Fixture administrator','email'=>'admin-fixture@example.test','password'=>bcrypt('fixture-password'),'status'=>'active']);
auth('admin')->setUser($admin);
$output=__DIR__.'/admin-rendered';if(!is_dir($output))mkdir($output);
$pages=[
 'settings'=>['/admin/settings',App\Http\Controllers\Admin\EcommerceSettingController::class,'edit'],
 'analytics'=>['/admin/analytics',App\Http\Controllers\Admin\AdminAnalyticsController::class,'index'],
 'users'=>['/admin/admin-users',App\Http\Controllers\Admin\AdminUserController::class,'index'],
 'roles'=>['/admin/admin-roles',App\Http\Controllers\Admin\AdminRoleController::class,'index'],
 'notifications'=>['/admin/notifications',App\Http\Controllers\Admin\AdminNotificationController::class,'index'],
 'audit'=>['/admin/audit-logs',App\Http\Controllers\Admin\AdminAuditLogController::class,'index'],
 'backups'=>['/admin/backups',App\Http\Controllers\Admin\AdminBackupController::class,'index'],
 'create-product'=>['/admin/products/create',App\Http\Controllers\Admin\ProductController::class,'create'],
];
$checks=[];
foreach($pages as $name=>[$url,$class,$method]){
 try{
 $request=Illuminate\Http\Request::create('http://127.0.0.1:8000'.$url);$request->setLaravelSession($session);$app->instance('request',$request);
 $reflection=new ReflectionMethod($class,$method);$view=$reflection->getNumberOfRequiredParameters()?app($class)->$method($request):app($class)->$method();
 file_put_contents($output.'/'.$name.'.html',$view->render());$checks[$name]='rendered';
 }catch(Throwable $error){$checks[$name]=$error->getMessage();}
}
file_put_contents(__DIR__.'/admin-render-checks.json',json_encode($checks,JSON_PRETTY_PRINT));echo json_encode($checks,JSON_PRETTY_PRINT)."\n";
