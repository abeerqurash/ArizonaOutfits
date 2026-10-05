<?php
require dirname(__DIR__).'/storefront-feature-update/cart-check.php';
$controller = app(App\Http\Controllers\Admin\ProductOptionController::class);
$unused = App\Models\ProductOptionValue::create(['product_option_id'=>$option->id,'label'=>'Unused','value'=>'unused']);
verify('Unused sibling value deletes', $controller->destroyValue($unused)->getStatusCode()===200);
verify('Assigned value blocked', $controller->destroyValue($value)->getStatusCode()===422);
verify('Assigned attribute blocked', $controller->destroy($option)->getStatusCode()===422);
$empty = App\Models\ProductOption::create(['name'=>'Empty']);
verify('Empty attribute deletes', $controller->destroy($empty)->getStatusCode()===200);
$unassigned = App\Models\ProductOption::create(['name'=>'Unused attribute']);
App\Models\ProductOptionValue::create(['product_option_id'=>$unassigned->id,'label'=>'Unused','value'=>'unused']);
verify('Unused attribute with values deletes', $controller->destroy($unassigned)->getStatusCode()===200);
$product->optionValues()->detach($value->id);
verify('Variant-only value reference blocked', $controller->destroyValue($value)->getStatusCode()===422);
echo "Deletion checks passed.\n";
