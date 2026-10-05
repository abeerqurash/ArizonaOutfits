<?php
require __DIR__.'/bootstrap.php';
use App\Models\{Admin,AdminRole,AdminPermission,EcommerceSetting,Order,Product,ProductVariant};
use App\Services\{StoreSettingsService,InventoryService,SafeContentUrl};
use Illuminate\Support\Facades\{Auth,Mail,Notification,Storage};
Mail::fake();Notification::fake();
$settings=EcommerceSetting::current();$settings->update(['currency'=>'GBP','currency_symbol'=>'£','order_prefix'=>'SYNC','shipping_fee'=>17,'tax_percentage'=>10,'free_shipping_threshold'=>null,'stock_management_enabled'=>true,'guest_checkout_enabled'=>true,'cash_on_delivery_enabled'=>true,'bank_transfer_enabled'=>true,'checkout_notice'=>'Fixture checkout notice','order_email_message'=>'Fixture email note']);
$pricing=new StoreSettingsService;
run('Pricing rules',function()use($pricing,$settings){
 check('Standard shipping uses saved fee',$pricing->shipping('standard',100)===17.0);
 check('Economy keeps method difference',$pricing->shipping('economy',100)===12.0);
 check('Express keeps method difference',$pricing->shipping('express',100)===27.0);
 check('Tax uses merchandise after discounts',$pricing->tax(100,20)===8.0);
 check('Tax cannot become negative',$pricing->tax(10,20)===0.0);
 $settings->update(['free_shipping_threshold'=>80]);check('Free shipping inclusive threshold',$pricing->shipping('express',100,20)===0.0);check('Below free threshold still charged',$pricing->shipping('standard',100,21)===17.0);$settings->update(['free_shipping_threshold'=>null]);
 check('Saved currency and symbol agree',$pricing->currency()==='GBP' && $pricing->money(12)==='£12.00');
 check('Saved prefix used for new numbers',str_starts_with($pricing->orderNumber(),'SYNC-'));
 $item=new Product(['reorder_point'=>2]);check('Per-item stock threshold remains override',App\Services\InventoryCatalogService::threshold($item)===2);
});
function frontend(array $data=[],string $routeName='checkout.place',string $method='POST'):Illuminate\Http\Request{
 global $app;$r=Illuminate\Http\Request::create('http://arizona.test/checkout',$method,$data);$r->setLaravelSession($app['session']->driver());$r->setUserResolver(fn()=>Auth::guard('web')->user());$route=clone $app['router']->getRoutes()->getByName($routeName);$route->bind($r);$r->setRouteResolver(fn()=>$route);$app->instance('request',$r);Auth::shouldUse('web');return $r;
}
function cartFor(Product $product,int $qty=1):array{return ['p'.$product->id=>['product_id'=>$product->id,'title'=>$product->title,'slug'=>$product->slug,'price'=>(float)$product->regular_price,'quantity'=>$qty,'sku'=>$product->sku]];}
function checkoutData(string $method='cash_on_delivery'):array{return ['billing_name'=>'Fixture Customer','billing_email'=>'customer@example.test','billing_phone'=>'03000000000','billing_address'=>'Fixture Address','billing_country'=>'PK','billing_state'=>'Punjab','billing_city'=>'Lahore','billing_zip'=>'54000','shipping_method'=>'standard','payment_method'=>$method,'terms'=>'1'];}
$product=Product::create(['title'=>'Sync product','slug'=>'sync-product','sku'=>'SYNC-1','status'=>'active','regular_price'=>100,'stock'=>10]);
run('Checkout quote and COD order',function()use($app,$product){
 frontend();$app['session']->put('cart',cartFor($product,2));$checkout=app(App\Http\Controllers\CheckoutController::class);
 $view=$checkout->index();$data=$view->getData();check('Checkout page totals use saved pricing',$data['subtotal']===200.0 && $data['shipping']===17.0 && $data['tax']===20.0 && $data['total']===237.0 && $data['currency']==='GBP');
 $html=$view->render();file_put_contents(__DIR__.'/checkout-render.html',$html);check('Checkout view renders notice and COD option',str_contains($html,'Fixture checkout notice') && str_contains($html,'value="cash_on_delivery"'));check('Checkout view renders tax row',str_contains($html,'checkout-tax-amount'));
 $quote=$checkout->shippingQuote(frontend(['shipping_method'=>'express'],'checkout.shipping-quote'))->getData(true);
 check('Shipping quote includes same tax and currency',$quote['shipping']==27 && $quote['tax']==20 && $quote['total']==247 && $quote['currency']==='GBP' && $quote['formatted_total']==='£247.00');
 $app['session']->put('cart',cartFor($product,2));$response=$checkout->placeOrder(frontend(checkoutData()));$order=Order::where('payment_method','cash_on_delivery')->latest('id')->first();
 check('COD saves an unpaid order',$order!==null && $order->payment_status==='pending' && $order->paid_at===null);
 check('COD persists same pricing and prefix',$order && (float)$order->total===237.0 && (float)$order->tax===20.0 && $order->currency==='GBP' && str_starts_with($order->order_number,'SYNC-'));
 check('COD reserves physical inventory once',$product->fresh()->stock===8 && $order->inventory_deducted_at!==null);
 app(InventoryService::class)->deductForOrder($order);check('Repeated deduction remains idempotent',$product->fresh()->stock===8);
 app(InventoryService::class)->restoreForOrder($order,'cancelled');app(InventoryService::class)->restoreForOrder($order,'cancelled');check('COD cancellation restores exactly once',$product->fresh()->stock===10);
 $app['session']->put('cart',cartFor($product));$checkout->placeOrder(frontend(checkoutData('bank_transfer')));$bank=Order::where('payment_method','bank_transfer')->latest('id')->first();check('Bank transfer still waits for verification',$bank && $bank->inventory_deducted_at===null && $product->fresh()->stock===10);
});
run('Disabled stock management and policy snapshot',function()use($settings,$product,$app){
 $settings->update(['stock_management_enabled'=>false]);$product->update(['stock'=>0]);$app['session']->put('cart',cartFor($product,2));frontend();$checkout=app(App\Http\Controllers\CheckoutController::class);check('Non-managed checkout allows zero physical stock',$checkout->index() instanceof Illuminate\View\View);
 $checkout->placeOrder(frontend(checkoutData()));$order=Order::where('payment_method','cash_on_delivery')->latest('id')->first();check('Non-managed order snapshot records disabled policy',data_get($order->payment_metadata,'inventory_managed')===false);
 check('Non-managed order has no physical deduction',$product->fresh()->stock===0 && $order->inventory_deducted_at===null);
 $settings->update(['stock_management_enabled'=>true]);$order->update(['payment_metadata'=>['provider'=>'fixture']]);check('Metadata replacement preserves original policy',data_get($order->fresh()->payment_metadata,'inventory_managed')===false);
 app(InventoryService::class)->deductForOrder($order);app(InventoryService::class)->restoreForOrder($order,'cancelled');check('Later setting change never fabricates stock',$product->fresh()->stock===0);
 $product->update(['stock'=>5]);$managed=Order::create(['payment_method'=>'cash_on_delivery','payment_status'=>'pending','order_status'=>'pending']);$managed->items()->create(['product_id'=>$product->id,'product_title'=>'Fixture','price'=>100,'quantity'=>1,'total'=>100]);$settings->update(['stock_management_enabled'=>false]);app(InventoryService::class)->deductForOrder($managed);check('Existing managed order still deducts after setting disabled',$product->fresh()->stock===4);app(InventoryService::class)->restoreForOrder($managed,'cancelled');check('Existing managed order restores after setting disabled',$product->fresh()->stock===5);
});
run('Cash collection and coupon ledger',function()use($settings,$product){
 $settings->update(['stock_management_enabled'=>true]);$product->update(['stock'=>10]);
 $coupon=App\Models\Coupon::create(['code'=>'CASHFIX','type'=>'fixed','value'=>10,'status'=>true,'target_type'=>'all']);
 $order=Order::create(['user_id'=>1,'order_number'=>'CASH-COLLECTION','currency'=>'GBP','payment_method'=>'cash_on_delivery','payment_provider'=>'cash_on_delivery','order_status'=>'pending','payment_status'=>'pending','total'=>90,'discount'=>10,'coupon_code'=>'CASHFIX']);
 $order->items()->create(['product_id'=>$product->id,'product_title'=>'Cash fixture','price'=>100,'quantity'=>1,'total'=>100]);app(InventoryService::class)->deductForOrder($order);$order->refresh();
 AuthFix();$controller=app(App\Http\Controllers\Admin\OrderController::class);$data=['order_status'=>'pending','payment_status'=>'paid'];
 $update=fn()=> $controller->update(req($data),$order,app(App\Services\OrderActivityService::class),app(App\Services\OrderShipmentService::class),app(App\Services\OrderEmailService::class));
 $update();$order->refresh();check('Collected COD records paid timestamp',$order->payment_status==='paid' && $order->paid_at!==null);
 check('COD cash collection records coupon usage',App\Models\CouponRedemption::where('order_id',$order->id)->count()===1 && $coupon->fresh()->used_count===1);
 $update();check('Repeated collection never deducts or redeems twice',$product->fresh()->stock===9 && App\Models\CouponRedemption::where('order_id',$order->id)->count()===1 && $coupon->fresh()->used_count===1);
 check('Collected cash cannot be reset to pending',validationBlocked(fn()=> $controller->update(req(['order_status'=>'pending','payment_status'=>'pending']),$order,app(App\Services\OrderActivityService::class),app(App\Services\OrderShipmentService::class),app(App\Services\OrderEmailService::class))));
});
run('Web policy and configuration isolation',function()use($settings){
 $middleware=new App\Http\Middleware\StorePolicyMiddleware;$settings->update(['maintenance_mode'=>true]);Auth::guard('admin')->forgetUser();Auth::guard('web')->forgetUser();
 $r=frontend([], 'home-page','GET');check('Maintenance blocks public storefront',$middleware->handle($r,fn()=>response('public'))->getStatusCode()===503);
 $r=frontend([], 'checkout.stripe.return','GET');check('Maintenance preserves payment return',$middleware->handle($r,fn()=>response('return'))->getStatusCode()===200);
 $r=frontend([], 'checkout.stripe.webhook');$r=Illuminate\Http\Request::create('http://arizona.test/stripe/webhook','POST');$r->setLaravelSession(app('session')->driver());check('Maintenance preserves signed webhook endpoint',$middleware->handle($r,fn()=>response('webhook'))->getStatusCode()===200);
 $r=Illuminate\Http\Request::create('http://arizona.test/admin/settings','GET');$r->setLaravelSession(app('session')->driver());check('Maintenance keeps admin accessible',$middleware->handle($r,fn()=>response('admin'))->getStatusCode()===200);
 $settings->update(['maintenance_mode'=>false,'guest_checkout_enabled'=>false]);foreach(['checkout.index','checkout.shipping-quote','checkout.place','checkout.stripe.intent'] as $name){$r=frontend([], $name);check('Guest checkout denied: '.$name,$middleware->handle($r,fn()=>response('allowed'))->isRedirect());}
 Auth::guard('web')->setUser(App\Models\User::find(1));$original=config('payments.currency');$r=frontend([], 'checkout.index','GET');$response=$middleware->handle($r,function(){check('Request config follows saved currency',config('payments.currency')==='GBP'&&config('inventory.currency')==='GBP');return response('allowed');});check('Signed-in customer can checkout',$response->getStatusCode()===200);check('Runtime config restored after response',config('payments.currency')===$original);
 $settings->update(['guest_checkout_enabled'=>true]);AuthFix();
});
run('Safer links and email placeholders',function(){
 frontend();foreach(['javascript:alert(1)','java&#x73;cript:alert(1)','/\\evil.test','//evil.test','https://user:password@arizona.test/path','ftp://arizona.test/path',"/bad\npath"]as $url)check('Unsafe link rejected: '.json_encode($url),!SafeContentUrl::allowed($url));
 check('Internal HTTP same-origin accepted',SafeContentUrl::internal('http://arizona.test/admin/orders'));
 check('Different port rejected',!SafeContentUrl::internal('http://arizona.test:9000/admin'));
 $sanitizer=new App\Services\HtmlContentSanitizer;$clean=$sanitizer->clean('<p>Hello <strong>safe</strong></p><a href="java&#x73;cript:alert(1)" onmouseover=bad()>bad</a><img src=x onerror=bad()><svg><script>bad()</script></svg>');$doc=new DOMDocument;@$doc->loadHTML($clean);check('Sanitizer keeps formatting',str_contains($clean,'<strong>safe</strong>'));check('Sanitizer strips executable URL and attributes',!str_contains($clean,'javascript')&&!str_contains($clean,'onerror')&&!str_contains($clean,'onmouseover')&&!str_contains($clean,'<svg'));
 $email=new App\Services\EmailTemplateService;check('Spaced placeholders render and unknown placeholders disappear',$email->replace('Hi {{ name }} / {{ unknown }}',['name'=>'<Alex>'])==='Hi &lt;Alex&gt; / ');
});
run('Delegated administration cannot escalate',function(){
 $role=AdminRole::create(['name'=>'Limited fixture','slug'=>'limited-fixture']);$role->permissions()->sync([AdminPermission::where('slug','admin-users.manage')->value('id')]);$staff=Admin::create(['name'=>'Limited','email'=>'limited@example.test','status'=>'active','is_super_admin'=>false]);$staff->adminRoles()->sync([$role->id]);Auth::guard('admin')->setUser($staff);$policy=new App\Services\AdminAccessPolicy;
 check('Delegated manager cannot grant super access',validationBlocked(fn()=>$policy->team(true,[])));
 check('Delegated manager cannot grant missing permissions',validationBlocked(fn()=>$policy->permissions([AdminPermission::where('slug','settings.manage')->value('id')])));
 check('Delegated manager can grant held permissions',!validationBlocked(fn()=>$policy->permissions([AdminPermission::where('slug','admin-users.manage')->value('id')])));AuthFix();
});
file_put_contents(__DIR__.'/../integration-checks.json',json_encode($results,JSON_PRETTY_PRINT));echo 'INTEGRATION '.count($results).' checks; '.count(array_filter($results,fn($r)=>$r['result']!=='PASS'))." failures. No live emails, orders or database writes.\n";
