from pathlib import Path
import json
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'commerce-menu-fixes';T=O/'_checks';T.mkdir(exist_ok=True)
loader='''<?php
require __DIR__.'/../../vendor/autoload.php';
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\\\')) {
        $path = __DIR__.'/../_staged/app/'.str_replace('\\\\','/',substr($class,4)).'.php';
        if (is_file($path)) require $path;
    }
}, true, true);
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
$app['router']->setRoutes(new Illuminate\\Routing\\RouteCollection);
require __DIR__.'/../_staged/routes/web.php';
$app['router']->getRoutes()->refreshNameLookups();
$app['router']->getRoutes()->refreshActionLookups();
$app['view']->getFinder()->prependLocation(__DIR__.'/../_staged/resources/views');
'''
s=(R/'commerce-menu-audit/_checks/verify.php').read_text(encoding='utf-8-sig')
s=s[s.index('config(['):];s=loader+s
s=s.replace("$admin=(new Admin)->forceFill", "$admin=(new Admin)->forceFill")
s=s.replace("$admin->is_super_admin=false", "$admin->is_super_admin=true")
s=s.replace("'is_super_admin'=>false", "'is_super_admin'=>true")
old="record(!$customerController->orders(req())->getData()['orders']->contains('id',$own->id),'Admin archiving removes order from customer history',true);"
s=s.replace(old,"record($customerController->orders(req())->getData()['orders']->contains('id',$own->id),'Archived order remains in authenticated customer history');")
s=s.replace("try{$customerController->invoice(req(),$own->id);throw new RuntimeException('Archived invoice unexpectedly visible');}catch(Illuminate\\Database\\Eloquent\\ModelNotFoundException){record(true,'Archived customer invoice becomes inaccessible',true);}","record($customerController->invoice(req(),$own->id)->getData()['order']->id===$own->id,'Customer can open own archived invoice');")
s=s.replace("record(!OrderActivity::where('order_id',$bank->id)->where('type','payment_status_changed')->exists(),'Bank verification does not populate customer payment-status activity timeline',true);", "record(OrderActivity::where('order_id',$bank->id)->where('type','payment_status_changed')->count()===1,'Bank verification records payment timeline once');")
s=s.replace("record(!$bank->activities()->where('type','payment_status_changed')->exists(),'Bank verification does not populate customer payment-status activity timeline',true);", "record($bank->activities()->where('type','payment_status_changed')->count()===1,'Bank verification records payment timeline once');")
s=s.replace("$closed->fresh()->payment_status==='paid' && $closed->fresh()->order_status==='cancelled','Cancelled bank order can be verified into paid/cancelled state',true", "$closed->fresh()->payment_status==='pending' && $closed->fresh()->order_status==='cancelled','Cancelled bank order verification is blocked'")
s=s.replace("$refunded->fresh()->payment_status==='paid'", "$refunded->fresh()->payment_status==='refunded'")
s=s.replace("'Refunded bank order can become paid/refunded without re-deducting stock',true", "'Refunded bank order verification preserves refund and stock'")
old="$categoryMethod->invoke($categories,req(['title'=>'Root','parent_id'=>$child->id]),$parent);record(true,'Category validation permits parent to point to its descendant',true);"
s=s.replace(old,"try{$categoryMethod->invoke($categories,req(['title'=>'Root','parent_id'=>$child->id]),$parent);throw new RuntimeException('Cycle permitted');}catch(Illuminate\\Validation\\ValidationException){record(true,'Category rejects descendant as parent');}")
s=s.replace("record($query->whereKey($unfeatured->id)->exists(),'Storefront featured filter is ignored when no search term is provided',true)", "record(!$query->whereKey($unfeatured->id)->exists(),'Featured filter excludes unfeatured product without search')")
s=s.replace("(float)$sqlPrice===100.0,'Zero sale price differs between model/cart pricing and shop SQL',true", "(float)$sqlPrice===0.0,'Zero sale price agrees between model/cart and shop SQL'")
s=s.replace("record($query->exists(),'Shop reports variable product in stock based on parent stock despite sold-out variants',true)", "record(!$query->exists(),'Variable product with sold-out variants is excluded from in-stock filter')")
s=s.replace("record(!$query->exists(),'Shop sale filter misses discounted variant with inherited regular price',true)", "record($query->exists(),'Sale filter includes variant inheriting regular price')")
s=s.replace("record($query->exists(),'Price range can match different parent/variant prices with no price inside the requested range',true)", "record(!$query->exists(),'Price range must match one purchasable item')")
s=s.replace("record($couponService->evaluate($limited->fresh(),$cart,$customer->id)['valid'],'Archiving paid coupon order lets same customer bypass per-user usage check',true)", "record(!$couponService->evaluate($limited->fresh(),$cart,$customer->id)['valid'],'Archived coupon purchase still consumes per-customer usage')")
old="$coupons->destroy($pendingCoupon);$pendingOrder->payment_status='paid';try{DB::transaction(fn()=>$redemption->redeemPaidOrder($pendingOrder));throw new RuntimeException('Deleted coupon redeemed');}catch(RuntimeException $e){record(str_contains($e->getMessage(),'no longer exists'),'Deleting coupon used by pending order prevents payment finalization',true);}"
s=s.replace(old,"$coupons->destroy($pendingCoupon);record(Coupon::whereKey($pendingCoupon->id)->exists(),'Pending order protects coupon from deletion');$pendingOrder->payment_status='paid';DB::transaction(fn()=>$redemption->redeemPaidOrder($pendingOrder));record(DB::table('coupon_redemptions')->where('order_id',$pendingOrder->id)->exists(),'Protected pending coupon can finalize payment');")
s=s.replace("new App\\Http\\Controllers\\Webhooks\\StripeWebhookController($inventory,$mail)", "new App\\Http\\Controllers\\Webhooks\\StripeWebhookController($inventory,$mail,$redemption)")
s=s.replace("record(!(new ReflectionClass($webhook))->hasProperty('couponRedemptionService'),'Stripe webhook calls a coupon service that is not declared/injected',true)", "record((new ReflectionClass($webhook))->hasProperty('couponRedemptionService'),'Stripe webhook has injected coupon service')")
start=s.index('try{(new ReflectionMethod($webhook,\'handlePaymentSucceeded\'))');end=s.index('$stripePayment=',start)
s=s[:start]+'''(new ReflectionMethod($webhook,'handlePaymentSucceeded'))->invoke($webhook,$intent,'evt_fixture');
record($stripeOrder->fresh()->payment_status==='paid' && $product->fresh()->stock===$beforeStock-1,'Offline Stripe success finalizes payment and inventory');
(new ReflectionMethod($webhook,'handlePaymentSucceeded'))->invoke($webhook,$intent,'evt_fixture_repeat');
record($product->fresh()->stock===$beforeStock-1 && OrderActivity::where('order_id',$stripeOrder->id)->where('type','payment_status_changed')->count()===1,'Repeated Stripe success preserves inventory and single activity');
$stripeOrder->delete();record((new ReflectionMethod($webhook,'findAndLockOrder'))->invoke($webhook,$intent)->id===$stripeOrder->id,'Webhook lookup resolves archived Stripe order');
'''+s[end:]
s=s.replace("$shipping===5.0 && (float)config('shipping.methods.standard.price')===9.99,'Stripe Pakistan shipping differs from configured standard shipping',true", "$shipping===(float)config('shipping.methods.standard.price'),'Stripe Pakistan default shipping matches configured standard shipping'")
insert='''
// Additional regression and permission checks for the repaired behaviors.
$archivedBank=order();item($archivedBank,$product);$archivedBank->delete();$stockBefore=$product->fresh()->stock;
$payments->verifyBankTransfer(req(['payment_reference'=>'ARCHIVE-BANK']),$archivedBank,$inventory,$redemption,$mail);
record(Order::withTrashed()->find($archivedBank->id)->payment_status==='paid' && $product->fresh()->stock===$stockBefore-1,'Archived pending bank order can be verified without restore');
$archivedStripe=order(['payment_provider'=>'stripe','payment_method'=>'stripe','payment_intent_id'=>'pi_archived','total'=>10]);item($archivedStripe,$product);$archivedStripe->delete();$stockBefore=$product->fresh()->stock;
$archivedIntent=Stripe\\PaymentIntent::constructFrom(['id'=>'pi_archived','amount'=>1000,'amount_received'=>1000,'currency'=>'usd','status'=>'succeeded','latest_charge'=>null,'metadata'=>['order_id'=>(string)$archivedStripe->id]]);
(new ReflectionMethod($webhook,'handlePaymentSucceeded'))->invoke($webhook,$archivedIntent,'evt_archived');
record(Order::withTrashed()->find($archivedStripe->id)->payment_status==='paid' && $product->fresh()->stock===$stockBefore-1,'Payment can finalize after admin archives pending Stripe order');
$payments->rejectBankTransfer(req(['admin_notes'=>'Invalid retry']),$refunded,$mail);record($refunded->fresh()->payment_status==='refunded','Bank rejection cannot overwrite refunded payment');
$couponData=['code'=>'CHANGED','type'=>'fixed','value'=>10,'target_type'=>'all','status'=>1,'usage_limit'=>10,'per_user_usage_limit'=>1];
try{$coupons->update(req($couponData),$limited->fresh());throw new RuntimeException('Referenced code changed');}catch(Illuminate\\Validation\\ValidationException){record(true,'Referenced coupon code cannot be renamed');}
$coupons->destroy($limited->fresh());record(Coupon::whereKey($limited->id)->exists() && DB::table('coupon_redemptions')->where('order_id',$redeem->id)->count()===1,'Coupon deletion preserves archived redemption ledger');
$unusedCoupon=Coupon::create(['code'=>'UNUSED','type'=>'fixed','value'=>2,'status'=>true]);$coupons->destroy($unusedCoupon);record(!Coupon::whereKey($unusedCoupon->id)->exists(),'Unreferenced coupon can be deleted');
$query=Product::query()->whereKey($unfeatured->id);$filter->invoke($shop,$query,['search'=>'Not','featured'=>'1']);record(!$query->exists(),'Featured filter applies alongside search');
$query=Product::query()->whereKey($variable->id);$filter->invoke($shop,$query,['availability'=>['out_of_stock']]);record($query->exists(),'Sold-out variants appear in out-of-stock filter');
$query=Product::query()->whereKey($free->id);$filter->invoke($shop,$query,['offers'=>['on_sale']]);record($query->exists(),'Zero-priced sale is included in sale filter');
$query=Product::query()->whereKey($split->id);$filter->invoke($shop,$query,['min_price'=>90,'max_price'=>110]);record($query->exists(),'Price filter matches valid variant within range');
try{$query=Product::query();$filter->invoke($shop,$query,['min_price'=>100,'max_price'=>10]);throw new RuntimeException('Inverted range accepted');}catch(Illuminate\\Validation\\ValidationException){record(true,'Inverted price range rejected');}
$parentSale=Product::create(['title'=>'Parent sale only','slug'=>'parent-sale-only','regular_price'=>100,'sale_price'=>20,'status'=>'active']);$v=ProductVariant::create(['product_id'=>$parentSale->id,'sku'=>'NO-SALE','regular_price'=>null,'sale_price'=>null,'stock'=>1]);
$vSql=(new ReflectionMethod($shop,'variantEffectivePriceSql'))->invoke($shop);record((float)ProductVariant::whereKey($v->id)->selectRaw($vSql.' AS effective')->first()->effective===100.0,'Variant without own sale agrees with cart regular-price inheritance');
$priceRange=(new ReflectionMethod($shop,'getAvailablePriceRange'))->invoke($shop);record($priceRange['min']===0.0 && $priceRange['max']>=100,'Available range includes inherited variant prices and zero sale');
foreach(['economy','standard','express'] as $method){$result=(new ReflectionMethod($stripePayment,'calculateShipping'))->invoke($stripePayment,500.0,'PK',$method);record($result===(float)config('shipping.methods.'.$method.'.price'),'Stripe selected shipping matches configuration: '.$method);}
// The pending-payment matcher stores the same human-readable shipping name
// as bank checkout and refuses reusing an intent for a changed method.
$details=['name'=>'Customer A','email'=>'a@example.test','phone'=>'123','address'=>'Street','country'=>'PK','state'=>'Punjab','city'=>'Lahore','zip'=>'54000'];
$checkout=['shipping_method'=>'standard','billing_name'=>'Customer A','billing_email'=>'a@example.test','billing_phone'=>'123','billing_address'=>'Street','billing_country'=>'PK','billing_state'=>'Punjab','billing_city'=>'Lahore','billing_zip'=>'54000'];
$matchOrder=(new Order)->forceFill(['total'=>100,'currency'=>'USD','shipping_method'=>config('shipping.methods.standard.name'),'coupon_code'=>null,'discount'=>0]);
foreach($checkout as $key=>$value)if(str_starts_with($key,'billing_'))$matchOrder->setAttribute($key,$value);
foreach($details as $key=>$value)$matchOrder->setAttribute('shipping_'.$key,$value);
$matchOrder->setRelation('items',new Illuminate\\Database\\Eloquent\\Collection);session()->forget('cart_coupon');
$match=new ReflectionMethod($stripePayment,'pendingOrderMatchesCheckout');record($match->invoke($stripePayment,$matchOrder,[],$checkout,$details,100.0,'USD'),'Pending Stripe intent matches saved shipping method name');
$checkout['shipping_method']='express';record(!$match->invoke($stripePayment,$matchOrder,[],$checkout,$details,100.0,'USD'),'Changed shipping method cannot reuse pending Stripe intent');
try{(new ReflectionMethod($stripePayment,'calculateShipping'))->invoke($stripePayment,100.0,'PK','invalid');throw new RuntimeException('Unknown shipping accepted');}catch(Symfony\\Component\\HttpKernel\\Exception\\HttpException $e){record($e->getStatusCode()===422,'Unknown Stripe shipping method rejected');}
foreach(['admin.orders.index','admin.orders.archived','admin.payment-verifications.index','admin.products.index','admin.product-categories.index','admin.product-tags.index','admin.coupons.index'] as $name){
    $restricted=Mockery::mock(Admin::class)->makePartial();$restricted->shouldReceive('isActive')->andReturn(true);$restricted->shouldReceive('hasAdminPermission')->andReturn(false);$app['auth']->guard('admin')->setUser($restricted);
    $r=req();$route=$app['router']->getRoutes()->getByName($name);$r->setRouteResolver(fn()=>$route);
    try{(new App\\Http\\Middleware\\AdminMiddleware)->handle($r,fn()=>response('allowed'));throw new RuntimeException('Restricted admin allowed');}catch(Symfony\\Component\\HttpKernel\\Exception\\HttpException $e){record($e->getStatusCode()===403,'Server permission enforced: '.$name);}
}
$app['auth']->guard('admin')->setUser($admin);
foreach(['admin.payment-verifications.show','admin.payment-verifications.verify-bank-transfer','admin.payment-verifications.reject-bank-transfer','admin.orders.show','admin.orders.invoice','admin.orders.invoice.download'] as $name)record($app['router']->getRoutes()->getByName($name)->allowsTrashedBindings(),'Archived order binding enabled: '.$name);
$tracking=new App\\Http\\Controllers\\OrderTrackingController;
record($tracking->lookup(req(['order_number'=>$archivedBank->order_number,'email'=>$customer->email]))->getData()['order']->id===$archivedBank->id,'Public tracking can resolve archived order with matching email');
record($tracking->lookup(req(['order_number'=>$archivedBank->order_number,'email'=>$other->email])) instanceof Illuminate\\Http\\RedirectResponse,'Public tracking rejects mismatched email on archived order');
$pendingEdit=Coupon::create(['code'=>'EDITSAFE','type'=>'fixed','value'=>10,'status'=>true,'usage_limit'=>10]);$waiting=order(['coupon_code'=>'EDITSAFE','discount'=>10]);
try{$coupons->update(req(['code'=>'EDITSAFE','type'=>'fixed','value'=>10,'status'=>1,'target_type'=>'all','usage_limit'=>1]),$pendingEdit);throw new RuntimeException('Pending coupon limit reduced');}catch(Illuminate\\Validation\\ValidationException){record(true,'Coupon usage limit cannot shrink while payment pending');}
$coupons->update(req(['code'=>'EDITSAFE','type'=>'fixed','value'=>10,'status'=>0,'target_type'=>'all','usage_limit'=>10]),$pendingEdit);record(!$pendingEdit->fresh()->status,'Referenced coupon can be deactivated for new purchases');
$legacy=Coupon::create(['code'=>'LEGACY','type'=>'fixed','value'=>10,'status'=>true,'usage_limit'=>2,'used_count'=>1]);$legacyOrder=order(['payment_status'=>'paid','coupon_code'=>'LEGACY','discount'=>10]);DB::transaction(fn()=>$redemption->redeemPaidOrder($legacyOrder));record($legacy->fresh()->used_count===2,'New redemption preserves pre-ledger usage count');
record(!$couponService->evaluate($legacy->fresh(),$cart,$customer->id)['valid'],'Legacy coupon reaches correct global limit');
$paidCoupon=Coupon::create(['code'=>'STRIPECOUPON','type'=>'fixed','value'=>10,'status'=>true,'usage_limit'=>1]);$paidCouponOrder=order(['payment_provider'=>'stripe','payment_method'=>'stripe','payment_intent_id'=>'pi_coupon','total'=>90,'coupon_code'=>'STRIPECOUPON','discount'=>10]);item($paidCouponOrder,$product);
$couponIntent=Stripe\\PaymentIntent::constructFrom(['id'=>'pi_coupon','amount'=>9000,'amount_received'=>9000,'currency'=>'usd','status'=>'succeeded','latest_charge'=>null,'metadata'=>['order_id'=>(string)$paidCouponOrder->id]]);
(new ReflectionMethod($webhook,'handlePaymentSucceeded'))->invoke($webhook,$couponIntent,'evt_coupon');(new ReflectionMethod($webhook,'handlePaymentSucceeded'))->invoke($webhook,$couponIntent,'evt_coupon_repeat');
record($paidCoupon->fresh()->used_count===1 && DB::table('coupon_redemptions')->where('order_id',$paidCouponOrder->id)->count()===1,'Stripe coupon redemption remains once after duplicate success');
$refundedStripe=order(['payment_provider'=>'stripe','payment_method'=>'stripe','payment_status'=>'refunded','order_status'=>'refunded','payment_intent_id'=>'pi_refunded','total'=>10]);
$closedIntent=Stripe\\PaymentIntent::constructFrom(['id'=>'pi_refunded','amount'=>1000,'amount_received'=>1000,'currency'=>'usd','status'=>'succeeded','latest_charge'=>null,'last_payment_error'=>null,'metadata'=>['order_id'=>(string)$refundedStripe->id]]);
foreach(['handlePaymentProcessing','handlePaymentFailed','handlePaymentCancelled','handlePaymentSucceeded'] as $method)(new ReflectionMethod($webhook,$method))->invoke($webhook,$closedIntent,'evt_closed');
record($refundedStripe->fresh()->payment_status==='refunded' && $refundedStripe->fresh()->order_status==='refunded','Late Stripe events cannot overwrite refunded order');
Schema::create('inventory_alerts',function(Blueprint $t){$t->id();$t->string('status');});DB::table('inventory_alerts')->insert([['status'=>'active'],['status'=>'active'],['status'=>'resolved']]);
'''
s=s.replace("config(['view.compiled'",insert+"\nconfig(['view.compiled'",1)
extra='''
req();$sidebarView=view('admin.partials.sidebar');$sidebarHtml=$sidebarView->render();
record($sidebarView->getData()['activeInventoryAlertCount']===2 && $sidebarView->getData()['reorderRequiredCount']>0,'Actual sidebar receives inventory and reorder badge counts');
$r=req();$r->setRouteResolver(fn()=>$app['router']->getRoutes()->getByName('admin.orders.archived'));$html=view('admin.partials.sidebar')->render();preg_match_all('/<a\\b[^>]*class="[^"]*admin-menu-link[^"]*active[^"]*"/s',$html,$active);record(count($active[0])===1,'Only archive menu is active on archived-orders page');
$restricted=Mockery::mock(Admin::class)->makePartial();$restricted->shouldReceive('hasAdminPermission')->andReturn(false);$app['auth']->guard('admin')->setUser($restricted);req();$html=view('admin.partials.sidebar')->render();
foreach(['orders.index','orders.archived','payment-verifications.index','products.index','product-categories.index','product-tags.index','coupons.index'] as $suffix)record(!str_contains($html,'href="'.route('admin.'.$suffix).'"'),'Sidebar hides unauthorized menu: '.$suffix);
$app['auth']->guard('admin')->setUser($admin);
foreach(['admin.orders.index','admin.products.index','admin.coupons.index'] as $name){$r=req();$r->setRouteResolver(fn()=>$app['router']->getRoutes()->getByName($name));record((new App\\Http\\Middleware\\AdminMiddleware)->handle($r,fn()=>response('ok'))->getStatusCode()===200,'Super admin retains module access: '.$name);}
'''
s=s.replace("$pages=[",extra+"\n$pages=[",1)
s=s.replace("No provider calls or live database writes.","No provider calls or live database writes. Staged replacements tested; app sources untouched.")
(T/'verify.php').write_text(s,encoding='utf-8')
r=(R/'commerce-menu-audit/_checks/routes.php').read_text(encoding='utf-8-sig');r=loader+r[r.index('$routes='):];(T/'routes.php').write_text(r,encoding='utf-8')
print('Prepared staged-code regression harness.')
