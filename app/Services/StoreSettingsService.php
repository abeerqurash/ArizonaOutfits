<?php
namespace App\Services;
use App\Models\EcommerceSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
class StoreSettingsService
{
    public function settings(): EcommerceSetting
    {
        $request=request();$key='arizona.store_settings';
        if($request->attributes->has($key))return $request->attributes->get($key);
        $settings=(Schema::hasTable('ecommerce_settings')?EcommerceSetting::find(1):null)??new EcommerceSetting([
            'store_name'=>config('app.name'),'currency'=>'USD','currency_symbol'=>'$','order_prefix'=>'ORD','tax_percentage'=>0,'shipping_fee'=>0,
            'low_stock_threshold'=>5,'stock_management_enabled'=>true,'guest_checkout_enabled'=>true,'cash_on_delivery_enabled'=>false,'maintenance_mode'=>false,
        ]);
        $request->attributes->set($key,$settings);return $settings;
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
