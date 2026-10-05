put('app/Services/StoreSettingsService.php',r'''<?php
namespace App\Services;
use App\Models\EcommerceSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
class StoreSettingsService
{
    public function settings(): EcommerceSetting
    {
        return (Schema::hasTable('ecommerce_settings')?EcommerceSetting::find(1):null)??new EcommerceSetting([
            'store_name'=>config('app.name'),'currency'=>'USD','currency_symbol'=>'$','order_prefix'=>'ORD','tax_percentage'=>0,'shipping_fee'=>0,
            'low_stock_threshold'=>5,'stock_management_enabled'=>true,'guest_checkout_enabled'=>true,'cash_on_delivery_enabled'=>false,'maintenance_mode'=>false,
        ]);
    }
    public function currency(): string {return strtoupper($this->settings()->currency?:'USD');}
    public function symbol(): string {return $this->settings()->currency_symbol?:$this->currency().' ';}
    public function money(float $value): string {return $this->symbol().number_format($value,2);}
    public function managed(): bool {return (bool)$this->settings()->stock_management_enabled;}
    public function available(int $stock): int {return $this->managed()?max(0,$stock):1000000;}
    public function tax(float $subtotal,float $discount): float {return round(max(0,$subtotal-$discount)*(float)$this->settings()->tax_percentage/100,2);}
    public function shipping(string $method,float $subtotal,float $discount=0): float
    {
        $configured=config('shipping.methods.'.$method);abort_unless(is_array($configured),422,'The selected shipping method is unavailable.');
        $settings=$this->settings();$net=max(0,$subtotal-$discount);
        if($settings->free_shipping_threshold!==null && $net>=(float)$settings->free_shipping_threshold)return 0;
        // Saved standard fee is the base; retain configured differences between delivery methods.
        $standard=(float)config('shipping.methods.standard.price',0);
        return round(max(0,(float)$settings->shipping_fee+(float)($configured['price']??0)-$standard),2);
    }
    public function orderNumber(): string
    {
        $prefix=trim((string)$this->settings()->order_prefix,"- \t\n\r\0\x0B")?:'ORD';
        do{$number=$prefix.'-'.strtoupper(Str::random(10));}while(\App\Models\Order::withTrashed()->where('order_number',$number)->exists());
        return $number;
    }
}
''')
put('app/Http/Middleware/StorePolicyMiddleware.php',r'''<?php
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
''')
put('resources/views/errors/store-maintenance.blade.php',r'''<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Store maintenance</title><body style="font-family:Arial;background:#f4f6fb;color:#172033"><main style="max-width:600px;margin:15vh auto;padding:36px;background:white;border-radius:16px"><h1>{{ $storeName }}</h1><h2>We will be back soon</h2><p>Our store is undergoing maintenance. Please check again later.</p></main></body></html>''')
change('bootstrap/app.php',"$middleware->appendToGroup('web', \\App\\Http\\Middleware\\CustomerAccountStatus::class);", "$middleware->appendToGroup('web', \\App\\Http\\Middleware\\CustomerAccountStatus::class);\n        $middleware->appendToGroup('web', \\App\\Http\\Middleware\\StorePolicyMiddleware::class);")
change('app/Services/InventoryCatalogService.php',"config('inventory.low_stock_threshold', 5)","app(\\App\\Services\\StoreSettingsService::class)->settings()->low_stock_threshold")
p='app/Http/Controllers/Admin/EcommerceSettingController.php'
change(p,"'currency' => ['required', 'string', 'max:10']", "'currency' => ['required', 'string', 'regex:/^[A-Za-z]{3}$/D']")
change(p,"'order_prefix' => ['required', 'string', 'max:20']", "'order_prefix' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/D']")
change(p,"EcommerceSetting::current()->update($validated);", "$validated['currency']=strtoupper($validated['currency']);\n        EcommerceSetting::current()->update($validated);")
p='resources/views/admin/settings/edit.blade.php'
change(p,'Standard Shipping Fee <span>*</span>','Standard Shipping Fee <span>*</span>')
change(p,'Track and manage product inventory.','Enforce stock limits and deduct sold stock. Existing orders keep their original inventory policy.')
change(p,'Allow customers to checkout without an account.','Allow guest card and cash-on-delivery checkout. Bank transfer still requires sign-in.')
change(p,'Allow eligible orders to use COD.','Enable cash on delivery. Mark payment paid in Orders only after collection.')
change(p,'<label for="shipping_fee">Standard Shipping Fee <span>*</span></label>', '<label for="shipping_fee">Standard Shipping Fee <span>*</span></label><small>Other methods keep their configured price difference from standard. Free shipping is based on merchandise after discount. Tax applies to merchandise after discount.</small>')

# Order policy snapshot is immutable across provider metadata updates and settings changes.
p='app/Models/Order.php';s=read(p);pos=s.index('    public function items()')
put(p,s[:pos]+r'''    protected static function booted(): void
    {
        static::creating(function(self $order):void{
            $metadata=$order->payment_metadata??[];
            $metadata['inventory_managed']??=app(\App\Services\StoreSettingsService::class)->managed();
            $order->payment_metadata=$metadata;
        });
        static::updating(function(self $order):void{
            if(!$order->isDirty('payment_metadata'))return;
            $original=json_decode((string)$order->getRawOriginal('payment_metadata'),true)??[];
            if(array_key_exists('inventory_managed',$original)){
                $metadata=$order->payment_metadata??[];$metadata['inventory_managed']=$original['inventory_managed'];$order->payment_metadata=$metadata;
            }
        });
    }

'''+s[pos:])
change(p,"'inventory_deducted_at' => 'datetime',", "'inventory_deducted_at' => 'datetime',\n        'inventory_restored_at' => 'datetime',")
p='app/Services/InventoryService.php'
change(p,"                if ($lockedOrder->items->isEmpty()) {", "                if (data_get($lockedOrder->payment_metadata, 'inventory_managed', true) === false) return $lockedOrder;\n\n                if ($lockedOrder->items->isEmpty()) {")

for p in ['app/Http/Controllers/CheckoutController.php','app/Http/Controllers/Payment/StripePaymentController.php','app/Http/Controllers/CartController.php']:
    s=files.get(p,read(p));s,n=re.subn(r'(\$availableStock\s*=\s*\$variant\s*\?\s*\(int\)\s*\$variant->stock\s*:\s*\(int\)\s*\$product->stock;)',r'\1\n            $availableStock = app(\\App\\Services\\StoreSettingsService::class)->available($availableStock);',s);assert n,(p,'stock');put(p,s)
for p in ['app/Http/Controllers/CheckoutController.php','app/Http/Controllers/Payment/StripePaymentController.php']:
    change(p,'$tax = 0;', '$tax = app(\\App\\Services\\StoreSettingsService::class)->tax($subtotal, $discount);')
    method(p,'generateOrderNumber',r'''private function generateOrderNumber(): string {return app(\App\Services\StoreSettingsService::class)->orderNumber();}''')
p='app/Http/Controllers/CheckoutController.php';s=files[p]
s=re.sub(r"\$shipping\s*=\s*\$shippingDetails\['price'\];", "$shipping = app(\\\\App\\\\Services\\\\StoreSettingsService::class)->shipping($shippingDetails['key'], $subtotal, $discount);",s)
s=re.sub(r"\$shipping\s*=\s*\$shippingMethodDetails\['price'\];", "$shipping = app(\\\\App\\\\Services\\\\StoreSettingsService::class)->shipping($shippingMethodDetails['key'], $subtotal, $discount);",s)
s=re.sub(r"\$currency = strtoupper\(\s*config\(\s*'payments.currency',\s*config\('shipping.currency', 'USD'\)\s*\)\s*\);",r'$currency = app(\\App\\Services\\StoreSettingsService::class)->currency();',s)
put(p,s)
if 'receiverAdmin' not in read('app/Models/PurchaseOrderReceipt.php'):
    p='app/Models/PurchaseOrderReceipt.php'
    change(p,"        'received_by',", "        'received_by',\n        'received_by_admin_id',")
    change(p,'    public function items(): HasMany',r'''    public function receiverAdmin(): BelongsTo {return $this->belongsTo(Admin::class, 'received_by_admin_id');}
    public function getReceiverActorAttribute(): Admin|User|null {return $this->receiverAdmin ?? $this->receiver;}

    public function items(): HasMany''')
p='app/Http/Controllers/CheckoutController.php'
change(p,"'$' . number_format(","app(\\App\\Services\\StoreSettingsService::class)->symbol() . number_format(")
# Use calculated shipping amounts in the method choices too.
change(p,'$shippingMethods = $this->getShippingMethods();', '$shippingMethods = $this->getShippingMethods();')
s=files[p];anchor='$tax = app(\\App\\Services\\StoreSettingsService::class)->tax($subtotal, $discount);';idx=s.index(anchor);s=s[:idx]+"foreach ($shippingMethods as $key => &$method) $method['price'] = app(\\App\\Services\\StoreSettingsService::class)->shipping($key, $subtotal, $discount);\n        unset($method);\n        "+s[idx:];put(p,s)
# Enable COD on the same validated offline order creation path, reserving inventory once.
s=files[p];s,n=re.subn(r"\$validated\['payment_method'\]\s*!==\s*'bank_transfer'", "!in_array($validated['payment_method'], ['bank_transfer','cash_on_delivery'], true)",s);assert n;put(p,s)
change(p,"if (!auth()->check()) {", "if ($validated['payment_method'] === 'bank_transfer' && !auth('web')->check()) {")
change(p,"if (!$ecommerceSettings->bank_transfer_enabled) {", "if ($validated['payment_method'] === 'bank_transfer' && !$ecommerceSettings->bank_transfer_enabled) {")
s=files[p];s=re.sub(r"'payment_method'\s*=>\s*'bank_transfer'", "'payment_method' => $validated['payment_method']",s);s=re.sub(r"'payment_provider'\s*=>\s*'bank_transfer'", "'payment_provider' => $validated['payment_method']",s);s=re.sub(r"'method'\s*=>\s*'bank_transfer'", "'method' => $validated['payment_method']",s);put(p,s)
s=files[p];s,n=re.subn(r'(\$this->createOrderItems\(\s*\$order,\s*\$cart\s*\);)',r"\1\n                    if ($validated['payment_method'] === 'cash_on_delivery') app(\\App\\Services\\InventoryService::class)->deductForOrder($order);",s);assert n;put(p,s)
# Add COD to validation only when enabled.
loc=files[p].index('        $shippingMethods =',files[p].index('private function validateCheckout'));s=files[p];put(p,s[:loc]+"        if (EcommerceSetting::current()->cash_on_delivery_enabled) $availablePaymentMethods[] = 'cash_on_delivery';\n"+s[loc:])
p='app/Http/Controllers/Payment/StripePaymentController.php'
method(p,'calculateShipping',r'''private function calculateShipping(float $subtotal, ?string $countryCode = null, ?string $method = null): float {return app(\App\Services\StoreSettingsService::class)->shipping($method ?? (string)config('shipping.default','standard'),$subtotal);}''')
change(p,'$this->calculateShipping($subtotal, $deliveryCountry, $validated[\'shipping_method\'])',"app(\\App\\Services\\StoreSettingsService::class)->shipping($validated['shipping_method'], $subtotal, $discount)")
s=files[p];s,n=re.subn(r"\$currency = strtolower\(\s*trim\(\s*\(string\) config\(\s*'payments.currency',\s*'USD'\s*\)\s*\)\s*\);",r'$currency = strtolower(app(\\App\\Services\\StoreSettingsService::class)->currency());',s);assert n;put(p,s)

p='resources/views/checkout/index.blade.php'
change(p,'${{ number_format(',"{{ app(\\App\\Services\\StoreSettingsService::class)->symbol() }}{{ number_format(")
change(p,"'payments.bank_transfer.enabled'", "'payments.bank_transfer.enabled'")
s=files[p];loc=s.index('                                    @if (',s.index('checkout-payment-methods'));put(p,s[:loc]+r'''                                    @if($ecommerceSettings->cash_on_delivery_enabled)
                                    <label class="checkout-payment-option"><input type="radio" name="payment_method" value="cash_on_delivery" @checked(old('payment_method') === 'cash_on_delivery') required><span class="checkout-payment-option-content"><strong>Cash on delivery</strong><small>Pay when your order arrives.</small></span></label>
                                    @endif
'''+s[loc:])
change(p,'<div\n    class="page-wrapper checkout-page"', '@if(filled($ecommerceSettings->checkout_notice))<div class="wrapper" role="note" style="padding:16px">{{ $ecommerceSettings->checkout_notice }}</div>@endif\n<div\n    class="page-wrapper checkout-page"')
change(p,'                                    id="checkout-selected-shipping"', '                                    id="checkout-selected-shipping"')
s=files[p];loc=s.index('                                <div\n                                    id="checkout-selected-shipping"');put(p,s[:loc]+r'''                                <div class="checkout-summary-row d-flex justify-content-between"><span>Tax</span><strong id="checkout-tax-amount">{{ app(\App\Services\StoreSettingsService::class)->money($tax) }}</strong></div>
'''+s[loc:])
change(p,'                                .formatted_total;', '                                .formatted_total;\n                        const taxAmount=document.getElementById("checkout-tax-amount");if(taxAmount)taxAmount.textContent=(payload.currency || "")+" "+Number(payload.tax || 0).toFixed(2);')
# Inspect quote script response identifier during tests; use actual payload name below.
p='public/asset/js/checkout-stripe.js';s=read(p)
s=s.replace("selectedMethod ===\n                'bank_transfer'", "['bank_transfer','cash_on_delivery'].includes(selectedMethod)")
put(p,s)

# Store currency display and non-managed stock must agree on product/cart pages.
for p in ['resources/views/products/show.blade.php','resources/views/products/partials/product-card.blade.php','resources/views/products/partials/quick-view.blade.php','resources/views/cart/index.blade.php']:
    s=read(p);s=s.replace('${{', '{{ app(\\App\\Services\\StoreSettingsService::class)->symbol() }}{{')
    s=re.sub(r"data-currency-symbol=[\"']\$[\"']",lambda m:'data-currency-symbol="{{ app(\\App\\Services\\StoreSettingsService::class)->symbol() }}"',s)
    s=re.sub(r'\(int\)\s*\(\s*\$product->stock\s*\?:\s*0\s*\)',r'app(\\App\\Services\\StoreSettingsService::class)->available((int)$product->stock)',s)
    s=re.sub(r'\(int\)\s*\(\s*\$variant->stock\s*(?:\?\?|\?:)\s*0\s*\)',r'app(\\App\\Services\\StoreSettingsService::class)->available((int)$variant->stock)',s)
    s=s.replace('(int) $variant->stock > 0','app(\\App\\Services\\StoreSettingsService::class)->available((int)$variant->stock) > 0')
    put(p,s)
change('resources/views/layouts/app.blade.php','<body\n','<body\n    data-currency-symbol="{{ app(\\App\\Services\\StoreSettingsService::class)->symbol() }}"\n')
change('resources/views/checkout/thank-you.blade.php','<div class="order-thank-you-header mb-20px">','<div class="order-thank-you-header mb-20px">\n@if($paymentMethod === "cash_on_delivery" && !$isPaid)<p class="fs-16">Your cash-on-delivery order has been received. Payment will be collected on delivery.</p>@endif')
p='app/Http/Controllers/ShopController.php'
change(p,'if ($hasInStock && !$hasOutOfStock)', 'if (app(\\App\\Services\\StoreSettingsService::class)->managed() && $hasInStock && !$hasOutOfStock)')
change(p,'if ($hasOutOfStock && !$hasInStock)', 'if (!app(\\App\\Services\\StoreSettingsService::class)->managed() && $hasOutOfStock && !$hasInStock) $query->whereRaw("1=0");\n        if (app(\\App\\Services\\StoreSettingsService::class)->managed() && $hasOutOfStock && !$hasInStock)')
for p in ['app/Mail/CustomerOrderConfirmationMail.php','app/Mail/AdminNewOrderMail.php']:
    change(p,"'store_name'=>config('app.name')", "'store_name'=>app(\\App\\Services\\StoreSettingsService::class)->settings()->store_name")
for p in ['resources/views/emails/orders/customer-confirmation.blade.php','resources/views/emails/orders/admin-new-order.blade.php','resources/views/emails/orders/dynamic-template.blade.php']:
    s=read(p);i=s.index('</body>');put(p,s[:i]+r'''@if(filled(app(\App\Services\StoreSettingsService::class)->settings()->order_email_message))<p style="text-align:center;padding:16px;font-family:Arial">{{ app(\App\Services\StoreSettingsService::class)->settings()->order_email_message }}</p>@endif
'''+s[i:])
p='app/Http/Controllers/Admin/AdminAuditLogController.php'
change(p,"$search = trim($request->string('search')->value());", "$request->validate(['date_from'=>['nullable','date_format:Y-m-d'],'date_to'=>['nullable','date_format:Y-m-d']]);\n        if ($request->filled('date_from') && $request->filled('date_to') && $request->string('date_from')->value() > $request->string('date_to')->value()) throw \\Illuminate\\Validation\\ValidationException::withMessages(['date_to'=>'End date must be on or after start date.']);\n        $search = trim($request->string('search')->value());")

# COD payment collection uses the same inventory/coupon services as verified payments.
p='app/Http/Controllers/Admin/OrderController.php';s=read(p)
a=s.index('public function update(');b=s.index('public function ',a+len('public function update('));part=s[a:b]
part=part.replace('$order->update($validated);',r'''if ($order->payment_method === 'cash_on_delivery') {
                $locked=Order::withTrashed()->lockForUpdate()->findOrFail($order->id);
                if ($locked->payment_status === 'paid' && $validated['payment_status'] !== 'paid') throw \Illuminate\Validation\ValidationException::withMessages(['payment_status'=>'Collected cash payments cannot be reverted here. Use the refund action.']);
                if (in_array($locked->order_status,['cancelled','refunded'],true) && $validated['payment_status'] === 'paid') throw \Illuminate\Validation\ValidationException::withMessages(['payment_status'=>'A cancelled or refunded order cannot collect payment.']);
                if ($validated['payment_status'] === 'paid') $validated['paid_at']=$locked->paid_at ?? now();
            }
            $order->update($validated);
            if ($order->payment_method === 'cash_on_delivery' && $order->payment_status === 'paid') {
                app(\App\Services\InventoryService::class)->deductForOrder($order);
                app(\App\Services\CouponRedemptionService::class)->redeemPaidOrder($order);
            }''')
assert 'Collected cash payments' in part;s=s[:a]+part+s[b:]
needle="$field = $validated['action'];";assert needle in s;s=s.replace(needle,needle+"\n                if ($order->payment_method === 'cash_on_delivery' && $field === 'payment_status') continue;")
put(p,s)
