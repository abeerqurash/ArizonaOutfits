<?php
// Current installed classes/routes only; no replacement/staged autoloader.
require __DIR__.'/../../administration-audit/_checks/bootstrap.php';
(require __DIR__.'/../../database/migrations/2026_10_01_000001_repair_inventory_purchasing_schema.php')->up();
use Illuminate\Support\Facades\{Schema,Auth,Mail,Notification,Bus,DB};
use Illuminate\Database\Schema\Blueprint;
Schema::table('orders',function(Blueprint $t){foreach(['shipping_method','estimated_delivery','billing_state','billing_zip','shipping_address','shipping_city','shipping_state','shipping_zip','order_notes']as $n)if(!Schema::hasColumn('orders',$n))$t->string($n)->nullable();foreach(['tax','shipping_price']as $n)if(!Schema::hasColumn('orders',$n))$t->decimal($n,12,2)->default(0);});
if(!Schema::hasColumn('order_notes','is_customer_visible'))Schema::table('order_notes',fn(Blueprint $t)=>$t->boolean('is_customer_visible')->default(false));
Schema::table('customer_email_identities',function(Blueprint $t){foreach(['provider_user_id']as $n)if(!Schema::hasColumn('customer_email_identities',$n))$t->string($n)->nullable();foreach(['provider_verified_at','email_verified_at','email_verification_pending_at','verified_at','verification_pending_at']as $n)if(!Schema::hasColumn('customer_email_identities',$n))$t->dateTime($n)->nullable();});
(require __DIR__.'/../../database/migrations/2026_09_23_000002_create_password_histories_table.php')->up();
(require __DIR__.'/../../database/migrations/2026_09_16_000002_create_phone_verification_codes_table.php')->up();
(require __DIR__.'/../../database/migrations/2026_07_28_200222_create_order_notification_logs_table.php')->up();
config(['view.compiled'=>__DIR__.'/compiled','logging.channels.inventory_audit.path'=>__DIR__.'/diagnostic.log','filesystems.disks.local.root'=>__DIR__.'/files/private','filesystems.disks.public.root'=>__DIR__.'/files/public']);
@mkdir(__DIR__.'/compiled',0777,true);Mail::fake();Notification::fake();Bus::fake();
function customerFixture(array $extra=[]): App\Models\User {static $i=0;$u=App\Models\User::create(array_merge(['name'=>'Sync customer '.++$i,'email'=>'sync'.$i.'@example.test','password'=>'FixturePassword123!','password_set_at'=>now(),'email_login_enabled_at'=>now(),'status'=>'active','registration_method'=>'email'],$extra));$u->forceFill(['email_verified_at'=>now()])->save();App\Models\CustomerEmailIdentity::create(['user_id'=>$u->id,'source'=>'custom','email'=>$u->email,'normalized_email'=>strtolower($u->email),'connected_at'=>now(),'email_verified_at'=>now(),'is_login_enabled'=>true]);return $u;}
function customerRequest(App\Models\User $u,array $data=[],string $name='customer.dashboard',string $method='GET'):Illuminate\Http\Request {
 global $app,$admin;Auth::shouldUse('web');Auth::guard('web')->setUser($u);Auth::guard('admin')->setUser($admin);
 $route=$app['router']->getRoutes()->getByName($name);$params=[];foreach($route?->parameterNames()??[]as $parameter)$params[$parameter]=$parameter==='provider'?'google':($parameter==='hash'?str_repeat('a',40):1);
 $url=$route?route($name,$params):'http://arizona.test/account';$r=Illuminate\Http\Request::create($url,$method,$data);$r->setLaravelSession($app['session']->driver());$r->setUserResolver(fn($guard=null)=>$guard==='admin'?$admin:$u);
 if($route){$copy=clone $route;$copy->bind($r);$r->setRouteResolver(fn()=>$copy);}$app->instance('request',$r);return $r;
}
function mustDeny(callable $action,int $status=404):bool{try{$action();return false;}catch(Illuminate\Database\Eloquent\ModelNotFoundException){return $status===404;}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){return $e->getStatusCode()===$status;}}
