from pathlib import Path
import json
base=Path(__file__).parent;root=base.parent;stage=base/'_staged';files=json.loads((base/'changed.json').read_text())
path='resources/views/customer/orders/show.blade.php';s=(root/path).read_text(encoding='utf-8')
needle='<strong>{{ $itemName }}</strong>'
assert needle in s
s=s.replace(needle,needle+'''
@if(!empty($item->display_options))
<dl class="customer-item-measurements" style="margin:10px 0;font-size:12px;display:grid;gap:5px">
@foreach($item->display_options as $itemOption)
<div style="display:flex;gap:12px"><dt>{{ $itemOption['option_name'] ?? 'Option' }}</dt><dd style="margin:0">{{ $itemOption['value_label'] ?? '' }}</dd></div>
@endforeach
</dl>
@endif
''',1)
p=stage/path;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8');files.append(path)
(base/'changed.json').write_text(json.dumps(files,indent=2))
# Reuse the previously verified cart tests with the new package's models/controllers.
prefix='''<?php
$root=dirname(__DIR__);$stage=__DIR__.'/_staged';require $root.'/vendor/autoload.php';
spl_autoload_register(function($class)use($stage){if(str_starts_with($class,'App\\\\')){$p=$stage.'/app/'.str_replace('\\\\','/',substr($class,4)).'.php';if(is_file($p))require $p;}},true,true);
$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','cache.default'=>'array']);
Illuminate\\Support\\Facades\\DB::purge('sqlite');
$checks=[];function verify($name,$pass){global $checks;$checks[$name]=(bool)$pass;if(!$pass)throw new RuntimeException($name);}
Illuminate\\Support\\Facades\\Schema::create('products',function($t){$t->id();$t->string('title');$t->string('status');$t->timestamps();});
(require $root.'/database/migrations/2026_10_04_000001_create_catalog_order_state_table.php')->up();
App\\Models\\Product::create(['title'=>'Sizing fixture','status'=>'active']);
'''
cart=(root/'storefront-feature-update/cart-check.php').read_text(encoding='utf-8')
cart=cart.replace("<?php\nrequire __DIR__.'/check.php';",'').replace("<?php\r\nrequire __DIR__.'/check.php';",'')
cart=cart.replace("$t->unsignedBigInteger($column);$t->timestamps();", "$t->unsignedBigInteger($column);if($column==='product_option_value_id')$t->unsignedInteger('position')->default(0);$t->timestamps();")
extra='''
// Product-specific ordering survives reload independently of another product.
$medium=App\\Models\\ProductOptionValue::create(['product_option_id'=>$option->id,'label'=>'Medium','value'=>'medium']);
$small=App\\Models\\ProductOptionValue::create(['product_option_id'=>$option->id,'label'=>'Small','value'=>'small']);
$second=App\\Models\\Product::create(['title'=>'Second product','status'=>'active']);
$product->optionValues()->sync([$medium->id=>['position'=>0],$small->id=>['position'=>1]]);
$second->optionValues()->sync([$small->id=>['position'=>0],$medium->id=>['position'=>1]]);
verify('First product order saved', $product->fresh()->optionValues->pluck('id')->all()===[$medium->id,$small->id]);
verify('Second product order independent', $second->fresh()->optionValues->pluck('id')->all()===[$small->id,$medium->id]);
// Verify checkout creates an actual order item with all measurements.
Illuminate\\Support\\Facades\\Schema::table('order_items',function($t){$t->unsignedBigInteger('order_id')->nullable();$t->unsignedBigInteger('product_id')->nullable();$t->unsignedBigInteger('product_variant_id')->nullable();$t->string('product_title')->nullable();$t->string('product_sku')->nullable();$t->integer('quantity')->nullable();$t->decimal('unit_price')->nullable();$t->decimal('line_total')->nullable();});
$order=new App\\Models\\Order;$order->id=99;
$method=new ReflectionMethod(App\\Http\\Controllers\\CheckoutController::class,'createOrderItems');
$method->invoke(app(App\\Http\\Controllers\\CheckoutController::class),$order,$session->get('cart'));
verify('Checkout persists actual item measurements', App\\Models\\OrderItem::where('order_id',99)->first()->display_options[1]['value_label']==='40.00');
$compiler=app('blade.compiler');foreach(json_decode(file_get_contents(__DIR__.'/changed.json'),true) as $path){if(!str_ends_with($path,'.blade.php'))continue;$target=__DIR__.'/compiled-check.php';file_put_contents($target,$compiler->compileString(file_get_contents($stage.'/'.$path)));exec('"'.PHP_BINARY.'" -l '.escapeshellarg($target).' 2>&1',$out,$code);verify('Blade compiles: '.$path,$code===0);$out=[];}@unlink(__DIR__.'/compiled-check.php');
file_put_contents(__DIR__.'/checks.json',json_encode($checks,JSON_PRETTY_PRINT));echo count($checks)." package checks passed\\n";
'''
(base/'check.php').write_text(prefix+cart+extra,encoding='utf-8')
render=(root/'storefront-feature-update/render-check.php').read_text(encoding='utf-8')
render=render.replace("foreach(glob($stage.'/database/migrations/*.php') as $file)(require $file)->up();", "foreach(glob($stage.'/database/migrations/*.php') as $file) if(!is_file($root.'/database/migrations/'.basename($file)))(require $file)->up();")
render=render.replace("config(['size_charts'=>require $stage.'/config/size_charts.php']);", "config(['size_charts'=>require $root.'/config/size_charts.php']);").replace("$app->usePublicPath($stage.'/public');", "$app->usePublicPath($root.'/public');")
(base/'render-check.php').write_text(render,encoding='utf-8')
layout=(root/'storefront-feature-update/layout-check.mjs').read_text(encoding='utf-8').replace('storefront-feature-update','arizona-ui-update')
(base/'layout-check.mjs').write_text(layout,encoding='utf-8')
