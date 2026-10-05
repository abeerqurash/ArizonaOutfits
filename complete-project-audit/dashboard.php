<?php
require __DIR__.'/../customer-side-audit/_checks/bootstrap.php';
use App\Models\{User, Order, Product};
use Illuminate\Support\Facades\Auth;
$customer=customerFixture();$other=customerFixture();$controller=new App\Http\Controllers\CustomerDashboardController;
function dashboardOrder(User $u, string $number, array $extra=[]): Order {
 return Order::create(array_merge(['user_id'=>$u->id,'order_number'=>$number,'payment_status'=>'paid','order_status'=>'completed','currency'=>'GBP','total'=>10],$extra));
}
$delivered=dashboardOrder($customer,'D-DELIVERY',['order_status'=>'out_for_delivery']);
$paid=dashboardOrder($customer,'D-USD',['currency'=>'USD','total'=>20]);
$archived=dashboardOrder($customer,'D-ARCHIVE',['total'=>30]);$archived->delete();
$refunded=dashboardOrder($customer,'D-REFUND',['order_status'=>'refunded','total'=>100]);
$pending=dashboardOrder($customer,'D-PENDING',['payment_status'=>'pending','order_status'=>'pending']);
$partial=dashboardOrder($customer,'D-PARTIAL',['payment_status'=>'partially_paid','order_status'=>'processing']);
$cancelled=dashboardOrder($customer,'D-CANCELLED',['payment_status'=>'pending','order_status'=>'cancelled']);
$archivedPending=dashboardOrder($customer,'D-OLD-PENDING',['payment_status'=>'pending','order_status'=>'pending']);$archivedPending->delete();
dashboardOrder($other,'D-FOREIGN',['total'=>999]);
run('Customer dashboard synchronized totals',function()use($customer,$controller){
 $data=$controller->index(customerRequest($customer))->getData();
 check('Out-for-delivery is included in transit count',$data['stats']['shipped']===1);
 check('Out-for-delivery and archived orders excluded from preparation count',$data['stats']['processing']===2);
 check('Customer order count includes archives but excludes another customer',$data['stats']['orders']===8);
 check('Dashboard paid spend groups GBP and USD separately',$data['paidSpend']===['GBP'=>40.0,'USD'=>20.0]);
 $adminData=(new App\Http\Controllers\Admin\CustomerController)->show($customer)->getData();check('Paid spend exactly matches admin Customer detail',$adminData['totalSpent']===$data['paidSpend']);
 check('Pending payment count excludes cancelled and archived orders',$data['stats']['pending_payment']===2);
 $html=$controller->index(customerRequest($customer))->render();check('Dashboard renders currency totals and payment column',str_contains($html,'GBP') && str_contains($html,'USD') && str_contains($html,'<th>Payment</th>'));
 check('Dashboard renders security and favorite links',str_contains($html,route('customer.security')) && str_contains($html,route('favorites.index')));
});
run('Customer payment filters',function()use($customer,$controller,$pending,$partial){
 $data=$controller->orders(customerRequest($customer,['payment_status'=>'awaiting_payment'],'customer.orders.index'))->getData();$ids=$data['orders']->pluck('id')->sort()->values()->all();$expected=[$pending->id,$partial->id];sort($expected);check('Summary payment link returns exactly the counted orders',$ids===$expected);
 $data=$controller->orders(customerRequest($customer,['payment_status'=>'paid','search'=>'D-USD','status'=>'completed'],'customer.orders.index'))->getData();check('Payment/search/status filters combine within customer ownership',$data['orders']->count()===1 && $data['orders']->first()->order_number==='D-USD');
 check('Invalid payment filter rejected',validationBlocked(fn()=>$controller->orders(customerRequest($customer,['payment_status'=>'unknown'],'customer.orders.index'))));
 $html=$controller->orders(customerRequest($customer,['payment_status'=>'pending'],'customer.orders.index'))->render();check('Order view includes payment filter and reset',str_contains($html,'name="payment_status"') && str_contains($html,'Reset'));
});
run('Explicit customer guard and disabled account protection',function()use($customer,$controller){
 $request=customerRequest($customer);Auth::shouldUse('admin');check('Dashboard uses web customer when default guard is admin',$controller->index($request)->getData()['stats']['orders']===8);
 $customer->update(['status'=>'blocked']);check('Controller refuses blocked customer even without route middleware',mustDeny(fn()=>$controller->index(customerRequest($customer)),403));$customer->update(['status'=>'active']);
});
run('Invoice PDF and empty dashboard',function()use($customer,$controller,$paid){
 $response=$controller->downloadInvoice(customerRequest($customer,[],'customer.orders.invoice.download'),$paid->id);check('Own invoice PDF generates',$response->getStatusCode()===200 && str_starts_with($response->getContent(),'%PDF'));
 $empty=customerFixture();$html=$controller->index(customerRequest($empty))->render();check('No-orders dashboard renders its empty state',str_contains($html,'No orders yet') && str_contains($html,'No paid purchases yet'));
});
file_put_contents(__DIR__.'/../dashboard-checks.json',json_encode($results,JSON_PRETTY_PRINT));
echo count($results).' dashboard checks; '.count(array_filter($results,fn($r)=>$r['result']!=='PASS'))." failures.\n";
