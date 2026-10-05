<?php
require __DIR__.'/bootstrap.php';
use App\Models\{Admin, AdminRole, AdminPermission, Order, Product, Review, InventoryAlert, InventoryHistory, AdminAuditLog, AdminBackup};
use Illuminate\Support\Facades\DB;
function bellAudit(array $data): void { AdminAuditLog::create(array_merge(['action'=>'fixture','method'=>'PATCH','url'=>'http://arizona.test/admin/fixture','status_code'=>302,'created_at'=>now()],$data)); }
function clearBell(): void { DB::table('notifications')->delete(); }
function bellCount(): int { return DB::table('notifications')->where('notifiable_id',99)->count(); }
function bellData(): array { return json_decode(DB::table('notifications')->where('notifiable_id',99)->latest('created_at')->first()->data,true); }
$product=Product::create(['title'=>'Bell fixture','slug'=>'bell-fixture','status'=>'active','regular_price'=>10,'stock'=>10]);
clearBell();
run('Order event integration',function()use($product){
 $order=Order::create(['order_number'=>'BELL-1','payment_method'=>'bank_transfer','payment_status'=>'pending','order_status'=>'pending']);
 check('New order creates unread administrator bell notification',bellCount()===1 && DB::table('notifications')->whereNull('read_at')->count()===1);
 check('New order notification links to orders',bellData()['action_url']==='/admin/orders');
 $order->save();check('Unchanged order save creates no duplicate',bellCount()===1);
 $order->update(['payment_status'=>'paid']);check('Payment verification creates payment event',bellCount()===2);
 check('Payment event points to verification menu',DB::table('notifications')->get()->contains(fn($n)=>json_decode($n->data,true)['action_url']==='/admin/payment-verifications'));
 $order->update(['order_status'=>'shipped','tracking_number'=>'BELL-TRACK']);check('Status and tracking in one save create one event',bellCount()===3);
 $order->delete();check('Order archival produces bell event',bellCount()===4);
 $order->restore();check('Order restoration produces bell event',bellCount()===5);
});
run('Commit and rollback safety',function(){
 clearBell();DB::beginTransaction();Order::create(['order_number'=>'BELL-ROLLBACK','payment_method'=>'cash_on_delivery']);
 check('Order notification waits for commit',bellCount()===0);DB::rollBack();check('Rolled back order emits no notification',bellCount()===0);
 DB::beginTransaction();Order::create(['order_number'=>'BELL-COMMIT','payment_method'=>'cash_on_delivery']);DB::commit();check('Committed order emits notification',bellCount()===1);
 DB::beginTransaction();DB::beginTransaction();Order::create(['order_number'=>'BELL-NESTED','payment_method'=>'cash_on_delivery']);DB::commit();check('Inner commit does not publish early',bellCount()===1);DB::commit();check('Outer commit publishes once',bellCount()===2);
});
run('Inventory integration',function()use($product){
 clearBell();$alert=InventoryAlert::create(['product_id'=>$product->id,'alert_type'=>'low_stock','stock_level'=>2,'threshold'=>5,'status'=>'active']);check('Low stock warning reaches bell',bellCount()===1);
 $alert->update(['stock_level'=>1,'notified_at'=>now()]);check('Same active alert stock/email updates do not repeat bell',bellCount()===1);
 $alert->update(['status'=>'resolved','resolved_at'=>now()]);check('Resolved stock alert reaches bell',bellCount()===2);
 InventoryAlert::create(['product_id'=>$product->id,'alert_type'=>'out_of_stock','stock_level'=>0,'threshold'=>5,'status'=>'active']);check('Out of stock danger reaches bell',bellCount()===3 && DB::table('notifications')->where('data','like','%danger%')->exists());
 InventoryHistory::create(['product_id'=>$product->id,'movement_type'=>'manual_adjustment','quantity_change'=>2,'stock_before'=>0,'stock_after'=>2]);check('Inventory movement reaches bell',bellCount()===4);
});
run('Customer and review integration',function()use($product){
 clearBell();$u=customerFixture();check('Customer registration reaches bell',bellCount()===1);$u->update(['status'=>'blocked']);check('Admin customer status reaches bell',bellCount()===2);
 $review=Review::create(['product_id'=>$product->id,'user_id'=>$u->id,'name'=>'Fixture','email'=>$u->email,'rating'=>5,'review'=>'Fixture review','status'=>'pending']);check('Customer review submission reaches bell',bellCount()===3);
 $review->update(['status'=>'approved']);check('Review moderation reaches bell',bellCount()===4);$review->delete();check('Review deletion reaches bell',bellCount()===5);
});
run('All editable admin menus',function(){
 $modules=['products','product-categories','product-tags','coupons','posts','categories','settings','pages','navigation-menus','admin-users','admin-roles','email-templates','purchase-orders','suppliers'];
 foreach($modules as $module){clearBell();bellAudit(['admin_id'=>99,'route_name'=>'admin.'.$module.'.update','action'=>$module.'.update','outcome'=>'success','method'=>'PATCH','status_code'=>302,'created_at'=>now()]);$data=bellCount()?bellData():[];check('Successful '.$module.' action notifies with valid link',bellCount()===1 && !empty($data['action_url']));}
 clearBell();bellAudit(['admin_id'=>99,'route_name'=>'admin.products.update','outcome'=>'failed','status_code'=>422,'created_at'=>now()]);check('Failed validation does not announce successful edit',bellCount()===0);
 foreach(['notifications.read','notifications.send','audit-logs.export','orders.update','reviews.update'] as $route)bellAudit(['admin_id'=>99,'route_name'=>'admin.'.$route,'outcome'=>'success','created_at'=>now()]);
 check('Read/send/audit operations do not recurse or duplicate model events',bellCount()===0);
});
run('Backup completion and failure',function(){
 clearBell();$b=AdminBackup::create(['name'=>'Fixture archive','type'=>'database','disk'=>'local','file_path'=>'fixture.gz','status'=>'processing','admin_id'=>99]);check('Processing backup is not falsely completed',bellCount()===0);$b->update(['status'=>'completed']);check('Backup completion broadcasts exactly once',bellCount()===1);$b->update(['file_size'=>100]);check('Unchanged completion creates no duplicate',bellCount()===1);
 AdminBackup::create(['name'=>'Failed fixture','type'=>'database','disk'=>'local','file_path'=>'failed-fixture.gz','status'=>'failed','error_message'=>'private database credential fixture']);check('Failed backup reaches bell without exception details',bellCount()===2 && !DB::table('notifications')->where('data','like','%private database credential%')->exists());
});
run('Recipients and idempotency',function(){
 $role=AdminRole::create(['name'=>'Bell orders','slug'=>'bell-orders']);
 foreach(['orders.manage','notifications.manage']as $p){$permission=AdminPermission::firstOrCreate(['slug'=>$p],['name'=>$p]);$role->permissions()->attach($permission->id);}
 $staff=Admin::create(['name'=>'Orders staff','email'=>'bell-staff@example.test','status'=>'active','is_super_admin'=>false]);$staff->adminRoles()->attach($role->id);
 $disabled=Admin::create(['name'=>'Disabled','email'=>'bell-disabled@example.test','status'=>'blocked','is_super_admin'=>true]);
 clearBell();$service=app(App\Services\AdminBellService::class);$service->publish('retry-fixture','orders.manage','Fixture','Safe message','admin.orders.index');$service->publish('retry-fixture','orders.manage','Fixture','Safe message','admin.orders.index');
 check('Repeated event delivered once per recipient',bellCount()===1 && DB::table('notifications')->where('notifiable_id',$staff->id)->count()===1);
 check('Disabled administrators excluded',!DB::table('notifications')->where('notifiable_id',$disabled->id)->exists());
 $service->publish('inventory-permissions','inventory.manage','Fixture','Safe message','admin.inventory-alerts.index');check('Staff cannot receive unauthorized inventory details',DB::table('notifications')->where('notifiable_id',$staff->id)->count()===1);
 $noBell=Admin::create(['name'=>'No bell','email'=>'bell-none@example.test','status'=>'active','is_super_admin'=>false]);$only=AdminRole::create(['name'=>'Orders only','slug'=>'orders-only']);$only->permissions()->attach(AdminPermission::where('slug','orders.manage')->value('id'));$noBell->adminRoles()->attach($only->id);
 $service->publish('bell-permission','orders.manage','Fixture','Safe message','admin.orders.index');check('Recipients require access to notification center',!DB::table('notifications')->where('notifiable_id',$noBell->id)->exists());
});
run('Bell rendering and actual audit middleware',function(){
 clearBell();AuthFix();$r=req(['password'=>'private-fixture-value']);$route=clone app('router')->getRoutes()->getByName('admin.products.store');$route->bind($r);$r->setRouteResolver(fn()=>$route);
 (new App\Http\Middleware\AdminAuditMiddleware)->handle($r,fn()=>response('successful change'));
 check('Real audit middleware publishes successful admin change',bellCount()===1);
 check('Notification excludes sensitive request payload',!DB::table('notifications')->where('data','like','%private-fixture-value%')->exists());
 $html=view('admin.partials.topbar')->render();check('Existing bell renders new module notification and unread count',str_contains($html,'Products updated') && str_contains($html,'1 unread update'));
 $id=DB::table('notifications')->first()->id;(new App\Http\Controllers\Admin\AdminNotificationController)->markAsRead(req(),$id);
 check('Reading automatic notification changes count without new notification',bellCount()===1 && DB::table('notifications')->whereNull('read_at')->count()===0);
});
file_put_contents(__DIR__.'/../bell-checks.json',json_encode($results,JSON_PRETTY_PRINT));
echo count($results).' bell checks; '.count(array_filter($results,fn($r)=>$r['result']!=='PASS'))." failures.\n";
