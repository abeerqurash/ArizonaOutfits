<?php
require __DIR__.'/check.php';
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
Schema::table('products',function($t){$t->string('slug')->nullable();$t->string('sku')->nullable();$t->decimal('regular_price')->default(100);$t->decimal('sale_price')->nullable();$t->integer('stock')->default(10);$t->string('featured_image')->nullable();$t->integer('cart_count')->default(0);});
Schema::create('product_images',function($t){$t->id();$t->unsignedBigInteger('product_id');$t->string('image')->nullable();$t->timestamps();});
Schema::create('product_options',function($t){$t->id();$t->string('name');$t->timestamps();});
Schema::create('product_option_values',function($t){$t->id();$t->unsignedBigInteger('product_option_id');$t->string('label');$t->string('value');$t->timestamps();});
foreach(['product_option_product'=>'product_option_id','product_option_value_product'=>'product_option_value_id'] as $table=>$column)Schema::create($table,function($t)use($column){$t->id();$t->unsignedBigInteger('product_id');$t->unsignedBigInteger($column);$t->timestamps();});
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
