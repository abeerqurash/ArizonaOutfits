<?php
require __DIR__.'/bootstrap.php';
use App\Models\{Product,Order};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
config(['payments.stripe.webhook_secret'=>'whsec_audit_fixture_only']);
function webhookFixture(Order $order,array $changes=[],bool $valid=true){$object=array_replace(['object'=>'payment_intent','id'=>$order->payment_intent_id,'status'=>'succeeded','amount'=>2000,'amount_received'=>2000,'currency'=>'usd','latest_charge'=>'ch_fixture','metadata'=>['order_id'=>(string)$order->id,'order_number'=>$order->order_number]],$changes);$raw=json_encode(['id'=>'evt_audit_'.$order->id,'object'=>'event','type'=>'payment_intent.succeeded','data'=>['object'=>$object]]);$time=time();$sig=hash_hmac('sha256',$time.'.'.$raw,$valid?'whsec_audit_fixture_only':'wrong-fixture-secret');return Request::create('http://arizona.test/stripe/webhook','POST',[],[],[],['HTTP_STRIPE_SIGNATURE'=>'t='.$time.',v1='.$sig,'CONTENT_TYPE'=>'application/json'],$raw);}
function stripeOrder(int $stock=2){static $i=0;$i++;$p=Product::create(['title'=>'Stripe fixture','slug'=>'stripe-'.$i,'status'=>'active','stock'=>$stock,'regular_price'=>20]);$o=Order::create(['order_number'=>'STRIPE-AUDIT-'.$i,'payment_method'=>'stripe','payment_status'=>'pending','order_status'=>'payment_pending','total'=>20,'subtotal'=>20,'currency'=>'USD','payment_intent_id'=>'pi_audit_'.$i]);$o->items()->create(['product_id'=>$p->id,'product_title'=>$p->title,'sku'=>'AUDIT','price'=>20,'quantity'=>1,'total'=>20]);return [$o,$p];}
$controller=app(App\Http\Controllers\Webhooks\StripeWebhookController::class);
run('Signed Stripe processing',function()use($controller){
 [$order,$product]=stripeOrder();check('Forged signature denied',$controller->handle(webhookFixture($order,[],false))->getStatusCode()===400);check('Forgery changes neither payment nor stock',$order->fresh()->payment_status==='pending'&&$product->fresh()->stock===2);
 check('Partial received payment rejected',$controller->handle(webhookFixture($order,['amount_received'=>1000]))->getStatusCode()===500);check('Partial capture does not fulfill',$order->fresh()->payment_status==='pending'&&$product->fresh()->stock===2);
 check('Wrong currency rejected',$controller->handle(webhookFixture($order,['currency'=>'gbp']))->getStatusCode()===500);check('Different current PaymentIntent rejected',$controller->handle(webhookFixture($order,['id'=>'pi_foreign']))->getStatusCode()===500);
 check('Valid signed full payment accepted',$controller->handle(webhookFixture($order))->getStatusCode()===200);check('Full payment marks order paid',$order->fresh()->payment_status==='paid');check('Full payment deducts one unit',$product->fresh()->stock===1);
 check('Webhook replay accepted safely',$controller->handle(webhookFixture($order))->getStatusCode()===200);check('Replay does not double deduct',$product->fresh()->stock===1);
 [$raced,$empty]=stripeOrder(0);check('Stock race fails closed for fulfillment',$controller->handle(webhookFixture($raced))->getStatusCode()===500&&$raced->fresh()->payment_status==='pending'&&$empty->fresh()->stock===0);
});
run('Stripe currency units',function(){
 $amount=new App\Services\StripeAmountService;
 foreach([[12.34,'GBP',1234],[12.34,'USD',1234],[12,'JPY',12],[5,'UGX',500],[5,'ISK',500],[12.34,'HUF',1234],[12.34,'TWD',1234]]as[$major,$currency,$minor])check('Charge units '.$currency,$amount->minor($major,$currency)===$minor);
 foreach([[5.5,'UGX'],[5.5,'JPY'],[1.23,'BHD']]as[$major,$currency]){try{$amount->minor($major,$currency);check('Unsafe precision rejected '.$currency,false);}catch(InvalidArgumentException){check('Unsafe precision rejected '.$currency,true);}}
});
$failed=count(array_filter($results,fn($r)=>$r['result']!=='PASS'));file_put_contents(__DIR__.'/../stripe-checks.json',json_encode($results,JSON_PRETTY_PRINT));echo count($results).' Stripe fixture checks; '.$failed." failures. No Stripe API calls.\n";exit($failed?1:0);
