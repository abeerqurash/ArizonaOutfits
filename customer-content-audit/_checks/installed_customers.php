<?php
require __DIR__.'/bootstrap.php';
use App\Models\Order;
$paid=Order::create(['user_id'=>$customer->id,'order_number'=>'INSTALLED-CUSTOMER-CHECK','total'=>50,'currency'=>'USD','payment_status'=>'paid','order_status'=>'completed']);
$paid->delete();
Order::create(['user_id'=>$customer->id,'total'=>30,'currency'=>'USD','payment_status'=>'pending','order_status'=>'pending']);
$controller=new App\Http\Controllers\Admin\CustomerController;
$detail=$controller->show($customer);
if ($detail->getData()['totalSpent']!==['USD'=>50.0]) throw new RuntimeException('Customer totals mismatch');
foreach ([$detail,$controller->index(req())] as $view) {
    if (!str_contains($view->render(),'USD 50.00')) throw new RuntimeException('Customer page render mismatch');
}
echo "PASS: Installed customer list and detail render with currency arrays; archived paid order counted and unpaid order excluded. In-memory database only.\n";
