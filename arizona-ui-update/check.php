<?php
$root=dirname(__DIR__);$stage=__DIR__.'/_staged';require $root.'/vendor/autoload.php';
spl_autoload_register(function($class)use($stage){if(str_starts_with($class,'App\\')){$p=$stage.'/app/'.str_replace('\\','/',substr($class,4)).'.php';if(is_file($p))require $p;}},true,true);
$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','cache.default'=>'array']);
Illuminate\Support\Facades\DB::purge('sqlite');
$checks=[];function verify($name,$pass){global $checks;$checks[$name]=(bool)$pass;if(!$pass)throw new RuntimeException($name);}
Illuminate\Support\Facades\Schema::create('products',function($t){$t->id();$t->string('title');$t->string('status');$t->timestamps();});
(require $root.'/database/migrations/2026_10_04_000001_create_catalog_order_state_table.php')->up();
App\Models\Product::create(['title'=>'Sizing fixture','status'=>'active']);

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
Schema::table('products',function($t){$t->string('slug')->nullable();$t->string('sku')->nullable();$t->decimal('regular_price')->default(100);$t->decimal('sale_price')->nullable();$t->integer('stock')->default(10);$t->string('featured_image')->nullable();$t->integer('cart_count')->default(0);});
Schema::create('product_images',function($t){$t->id();$t->unsignedBigInteger('product_id');$t->string('image')->nullable();$t->timestamps();});
Schema::create('product_options',function($t){$t->id();$t->string('name');$t->timestamps();});
Schema::create('product_option_values',function($t){$t->id();$t->unsignedBigInteger('product_option_id');$t->string('label');$t->string('value');$t->timestamps();});
foreach(['product_option_product'=>'product_option_id','product_option_value_product'=>'product_option_value_id'] as $table=>$column)Schema::create($table,function($t)use($column){$t->id();$t->unsignedBigInteger('product_id');$t->unsignedBigInteger($column);if($column==='product_option_value_id')$t->unsignedInteger('position')->default(0);$t->timestamps();});
Schema::create('product_variants',function($t){$t->id();$t->unsignedBigInteger('product_id');$t->string('sku')->nullable();$t->decimal('regular_price')->nullable();$t->decimal('sale_price')->nullable();$t->integer('stock')->default(10);$t->string('image')->nullable();$t->text('options');$t->timestamps();});
$product=App\Models\Product::first();$product->update(['slug'=>'sizing-test','sku'=>'SIZE-1']);
$option=App\Models\ProductOption::create(['name'=>'Size']);
$value=App\Models\ProductOptionValue::create(['product_option_id'=>$option->id,'label'=>'Custom','value'=>'custom']);
$product->options()->attach($option->id);$product->optionValues()->attach($value->id);
$variant=App\Models\ProductVariant::create(['product_id'=>$product->id,'stock'=>10,'regular_price'=>120,'options'=>[['option_id'=>$option->id,'value_id'=>$value->id]]]);
config(['session.driver'=>'array']);$session=app('session')->driver();$session->start();
$submit=function($measurements)use($product,$option,$value,$variant,$session,$app){
 $request=Illuminate\Http\Request::create('/cart','POST',['product_id'=>$product->id,'variant_id'=>$variant->id,'quantity'=>1,'product_options'=>[$option->id=>$value->id],'custom_measurements'=>$measurements],[],[],['HTTP_ACCEPT'=>'application/json']);
 $request->setLaravelSession($session);$app->instance('request',$request);
 return app(App\Http\Controllers\CartController::class)->add($request);
};
$measurements=['chest'=>40,'waist'=>34,'shoulder'=>18,'sleeve_length'=>25,'body_length'=>29];
verify('Custom cart add succeeds',$submit($measurements)->getStatusCode()===200);
verify('Measurements retained in session',array_values($session->get('cart'))[0]['custom_measurements']['chest']==='40.00');
$other=$measurements;$other['chest']=42;$submit($other);
verify('Different measurements create separate cart lines',count($session->get('cart'))===2);
$submit($measurements);
verify('Same measurements merge quantity',array_values($session->get('cart'))[0]['quantity']===2);
$bad=$measurements;unset($bad['chest']);
try{$submit($bad);verify('Cart rejects missing custom field',false);}catch(Illuminate\Validation\ValidationException $e){verify('Cart rejects missing custom field',true);}
verify('Invalid request leaves cart unchanged',count($session->get('cart'))===2);
$variant->update(['stock'=>3]);$extra=$measurements;$extra['chest']=44;
verify('Custom lines share the same stock limit',$submit($extra)->getStatusCode()===422);
Schema::create('order_items',function($table){$table->id();$table->text('options');$table->timestamps();});
$line=App\Models\OrderItem::create(['options'=>app(App\Services\CustomMeasurementsService::class)->orderOptions(array_values($session->get('cart'))[0]['options'],$measurements)]);
$saved=$line->fresh();
verify('Order measurements survive database serialization',count($saved->options)===6 && $saved->options[1]['value_label']==='40.00');
verify('Customer/admin display receives measurements',count($saved->display_options)===6);
file_put_contents(__DIR__.'/cart-checks.json',json_encode($checks,JSON_PRETTY_PRINT));echo "Cart integration checks passed\n";

// Product-specific ordering survives reload independently of another product.
$medium=App\Models\ProductOptionValue::create(['product_option_id'=>$option->id,'label'=>'Medium','value'=>'medium']);
$small=App\Models\ProductOptionValue::create(['product_option_id'=>$option->id,'label'=>'Small','value'=>'small']);
$second=App\Models\Product::create(['title'=>'Second product','status'=>'active']);
$product->optionValues()->sync([$medium->id=>['position'=>0],$small->id=>['position'=>1]]);
$second->optionValues()->sync([$small->id=>['position'=>0],$medium->id=>['position'=>1]]);
verify('First product order saved', $product->fresh()->optionValues->pluck('id')->all()===[$medium->id,$small->id]);
verify('Second product order independent', $second->fresh()->optionValues->pluck('id')->all()===[$small->id,$medium->id]);
foreach(['product_category_product'=>'product_category_id','product_product_tag'=>'product_tag_id'] as $table=>$column)Schema::create($table,function($t)use($column){$t->unsignedBigInteger('product_id');$t->unsignedBigInteger($column);$t->timestamps();});
$syncMethod=new ReflectionMethod(App\Http\Controllers\Admin\ProductController::class,'syncProductRelationships');
$syncMethod->invoke(app(App\Http\Controllers\Admin\ProductController::class),$product,['product_options'=>[$option->id],'product_option_values'=>[$medium->id,$small->id],'product_value_order'=>[$small->id=>0,$medium->id=>1]]);
verify('Admin save persists submitted order', $product->fresh()->optionValues->pluck('id')->all()===[$small->id,$medium->id]);
// Verify checkout creates an actual order item with all measurements.
Illuminate\Support\Facades\Schema::table('order_items',function($t){$t->unsignedBigInteger('order_id')->nullable();$t->unsignedBigInteger('product_id')->nullable();$t->unsignedBigInteger('product_variant_id')->nullable();$t->string('product_title')->nullable();$t->string('product_sku')->nullable();$t->integer('quantity')->nullable();$t->decimal('unit_price')->nullable();$t->decimal('line_total')->nullable();});
Illuminate\Support\Facades\Schema::table('order_items',function($t){$t->unsignedBigInteger('variant_id')->nullable();$t->string('sku')->nullable();$t->decimal('price')->nullable();$t->decimal('total')->nullable();});
$order=new App\Models\Order;$order->id=99;
$method=new ReflectionMethod(App\Http\Controllers\CheckoutController::class,'createOrderItems');
$method->invoke(app(App\Http\Controllers\CheckoutController::class),$order,$session->get('cart'));
verify('Checkout persists actual item measurements', App\Models\OrderItem::where('order_id',99)->first()->display_options[1]['value']==='40.00');
$compiler=app('blade.compiler');foreach(json_decode(file_get_contents(__DIR__.'/changed.json'),true) as $path){if(!str_ends_with($path,'.blade.php'))continue;$target=__DIR__.'/compiled-check.php';file_put_contents($target,$compiler->compileString(file_get_contents($stage.'/'.$path)));exec('"'.PHP_BINARY.'" -l '.escapeshellarg($target).' 2>&1',$out,$code);verify('Blade compiles: '.$path,$code===0);$out=[];}@unlink(__DIR__.'/compiled-check.php');
file_put_contents(__DIR__.'/checks.json',json_encode($checks,JSON_PRETTY_PRINT));echo count($checks)." package checks passed\n";
