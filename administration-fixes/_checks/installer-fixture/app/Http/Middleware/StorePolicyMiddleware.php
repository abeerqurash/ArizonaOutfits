<?php
namespace App\Http\Middleware;
use App\Services\StoreSettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
class StorePolicyMiddleware
{
    public function handle(Request $request,Closure $next): Response
    {
        $service=app(StoreSettingsService::class);$settings=$service->settings();
        $keys=['payments.currency','shipping.currency','inventory.currency','inventory.low_stock_threshold','payments.bank_transfer.enabled','payments.bank_transfer.bank_name','payments.bank_transfer.account_name','payments.bank_transfer.account_number','payments.bank_transfer.iban','payments.bank_transfer.swift_code','payments.bank_transfer.instructions'];
        $previous=[];foreach($keys as $key)$previous[$key]=config($key);
        config(['payments.currency'=>$service->currency(),'shipping.currency'=>$service->currency(),'inventory.currency'=>$service->currency(),'inventory.low_stock_threshold'=>$settings->low_stock_threshold,
            'payments.bank_transfer.enabled'=>(bool)$settings->bank_transfer_enabled,'payments.bank_transfer.bank_name'=>$settings->bank_name,'payments.bank_transfer.account_name'=>$settings->bank_account_name,
            'payments.bank_transfer.account_number'=>$settings->bank_account_number,'payments.bank_transfer.iban'=>$settings->bank_iban,'payments.bank_transfer.swift_code'=>$settings->bank_swift_code,'payments.bank_transfer.instructions'=>$settings->bank_transfer_instructions]);
        View::share('storeSettings',$settings);
        try{
            // Keep administration, sign-in and payment finalization reachable during maintenance.
            if($settings->maintenance_mode && !$request->is('admin','admin/*','stripe/webhook','login','logout','register','forgot-password','reset-password/*')
                && !$request->routeIs('checkout.stripe.return','checkout.thankyou') && !Auth::guard('admin')->user()?->isActive()){
                return response()->view('errors.store-maintenance',['storeName'=>$settings->store_name],503)->header('Retry-After','3600');
            }
            if(!$settings->guest_checkout_enabled && $request->routeIs('checkout.index','checkout.shipping-quote','checkout.place','checkout.stripe.intent') && !Auth::guard('web')->check()){
                $request->session()->put('customer.url.intended',route('checkout.index',absolute:false));
                if($request->expectsJson())return response()->json(['success'=>false,'message'=>'Please sign in before checkout.','redirect_url'=>route('login')],401);
                return redirect()->route('login')->with('error','Please sign in before checkout.');
            }
            return $next($request);
        }finally{config($previous);}
    }
}
