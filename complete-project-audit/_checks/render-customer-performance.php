<?php
require __DIR__.'/bootstrap.php';
$app['view']->getFinder()->prependLocation(__DIR__.'/../../optimization-round-2/_staged/resources/views');
$customer=customerFixture();$controller=new App\Http\Controllers\CustomerDashboardController;
for($i=1;$i<=12;$i++){App\Models\Order::create(['user_id'=>$customer->id,'order_number'=>'AUDIT-ORDER-'.$i,'payment_status'=>'paid','order_status'=>$i%2?'processing':'completed','currency'=>'GBP','total'=>100+$i,'billing_name'=>$customer->name,'billing_email'=>$customer->email]);}
$out=__DIR__.'/../../optimization-round-2/customer-fixtures';@mkdir($out,0777,true);
foreach(['dashboard'=>'index','orders'=>'orders'] as $name=>$method){$request=customerRequest($customer,[],$name==='dashboard'?'customer.dashboard':'customer.orders.index');file_put_contents($out.'/'.$name.'.html',$controller->$method($request)->render());}
echo "Rendered synthetic customer overview and orders; no live customer data used.\n";
file_put_contents($out.'/profile.html',(new App\Http\Controllers\ProfileController)->edit(customerRequest($customer,[],'profile.edit'))->render());
file_put_contents($out.'/security.html',(new App\Http\Controllers\Customer\AccountSecurityController)->index(customerRequest($customer,[],'customer.security'),app(App\Services\CustomerLoginSecurity::class))->render());

foreach(glob($out.'/*.html') as $file){file_put_contents($file,str_replace('http://127.0.0.1:8000','http://127.0.0.1:8123',file_get_contents($file)));}
