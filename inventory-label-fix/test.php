<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/InventoryCatalogService.php';
require __DIR__.'/InventoryReportController.php';
require __DIR__.'/../inventory-purchasing-fixes/_checks/bootstrap.php';
use App\Services\InventoryCatalogService;
$cases=[
    [[['option_id'=>1,'option_name'=>'color','value_id'=>1,'value_label'=>'Red'],['option_id'=>2,'option_name'=>'Material','value_id'=>5,'value_label'=>'Leather']],'Color: Red, Material: Leather'],
    [['color'=>'Black','Material'=>'Leather'],'Color: Black, Material: Leather'],
    [json_encode([['option_name'=>'color','value_label'=>'Green']]),'Color: Green'],
    [[['option_name'=>'Size','value_label'=>'0']],'Size: 0'],
    [null,''],
    [[['option_id'=>1,'value_id'=>2]],''],
];
foreach($cases as [$options,$expected]) {if(InventoryCatalogService::variantLabel($options)!==$expected) throw new RuntimeException('Formatter mismatch');}
$product=App\Models\Product::create(['title'=>'Test Product','stock'=>100,'regular_price'=>20,'cost_price'=>10]);
$variant=$product->variants()->create(['stock'=>1,'sku'=>'RED-LEATHER','options'=>$cases[0][0]]);
$row=app(InventoryCatalogService::class)->rows()->first();
if($row->title!=='Test Product — Color: Red, Material: Leather' || $row->stock!==1) throw new RuntimeException('Projection mismatch');
App\Models\InventoryHistory::create(['product_id'=>$product->id,'product_variant_id'=>$variant->id,'admin_id'=>99,'quantity_change'=>1,'stock_before'=>0,'stock_after'=>1,'movement_type'=>'restock']);
$controller=new App\Http\Controllers\Admin\InventoryReportController;
$html=$controller->index(req())->render();
if(!str_contains($html,'Color: Red, Material: Leather') || str_contains($html,'option_id')) throw new RuntimeException('Report render mismatch');
ob_start();$controller->export(req())->sendContent();$csv=ob_get_clean();
if(!str_contains($csv,'Color: Red, Material: Leather') || str_contains($csv,'option_id')) throw new RuntimeException('CSV mismatch');
echo "PASS: 6 formatter cases, variant stock projection, report page and CSV export. SQLite memory only.\n";
