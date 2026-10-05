<?php
require __DIR__.'/bootstrap.php';
use Illuminate\Support\Facades\{DB,Schema};
use Illuminate\Database\Schema\Blueprint;
use App\Models\{Product,ProductVariant,InventoryHistory,PurchaseOrder,SupplierDocument,SupplierRating};
use App\Services\{InventoryAdjustmentService,InventoryCatalogService,PurchasingCurrencyService};

run('Stock guards and actor identity', function () {
    $product=Product::create(['title'=>'Guarded','stock'=>3,'regular_price'=>20,'cost_price'=>10]);
    $service=app(InventoryAdjustmentService::class);$before=InventoryHistory::count();
    try {$service->adjustProduct($product,'remove',4,'Invalid',null,auth('admin')->user());check('Negative stock rejected',false);}
    catch (RuntimeException) {check('Negative stock rejected',$product->fresh()->stock===3 && InventoryHistory::count()===$before);}
    $service->adjustProduct($product,'add',1,'Valid',null,auth('admin')->user());$row=InventoryHistory::latest('id')->first();
    check('Manual movement records admin without customer FK',$row->admin_id===99 && $row->user_id===null);
    $variant=$product->variants()->create(['stock'=>2,'regular_price'=>null,'sale_price'=>0,'sku'=>'VAR-GUARD','reorder_point'=>8]);
    try {$service->adjustProduct($product,'set',99,'Invalid parent',null,auth('admin')->user());check('Variable parent adjustment rejected',false);}
    catch (RuntimeException) {check('Variable parent adjustment rejected',$product->fresh()->stock===4);}
    $rows=app(InventoryCatalogService::class)->rows()->where('id',$product->id);
    check('Variable parent appears only as variant inventory',$rows->count()===1 && $rows->first()->stock===2);
    check('Variant regular price inherits parent and zero sale remains zero',(float)$rows->first()->regular_price===20.0 && (float)$rows->first()->selling_price===0.0);
    $variant->update(['sale_price'=>null]);$rows=app(InventoryCatalogService::class)->rows()->where('id',$product->id);
    check('Variant missing sale uses inherited regular price',(float)$rows->first()->selling_price===20.0);
    $service->adjustVariant($variant,'add',1,'Variant adjustment',null,auth('admin')->user());
    check('Variant adjustment records actual admin',InventoryHistory::latest('id')->first()->admin_id===99);
});
run('Schema idempotence and legacy actors', function () {
    $supplier=supplier(['created_by'=>1]);$doc=$supplier->documents()->create(['title'=>'Legacy document','file_name'=>'old.pdf','file_path'=>'legacy/old.pdf','uploaded_by'=>1]);
    $rating=$supplier->ratings()->create(['quality_rating'=>4,'rated_by'=>1]);$old=$doc->fresh()->getRawOriginal();
    (require __DIR__.'/../_staged/database/migrations/2026_10_01_000001_repair_inventory_purchasing_schema.php')->up();
    check('Repeated additive migration preserves document records',$doc->fresh()->getRawOriginal()===$old);
    check('Legacy document actor remains original customer',$doc->fresh()->uploader_actor instanceof App\Models\User && $doc->fresh()->uploaded_by_admin_id===null);
    check('Legacy supplier and rating actors preserved',$supplier->fresh()->creator_actor instanceof App\Models\User && $rating->fresh()->ratedBy_actor instanceof App\Models\User);
    foreach(['suppliers'=>'created_by_admin_id','purchase_order_receipts'=>'received_by_admin_id','supplier_returns'=>'created_by_admin_id','supplier_documents'=>'uploaded_by_admin_id','supplier_ratings'=>'rated_by_admin_id','supplier_purchase_order_deliveries'=>'sent_by_admin_id'] as $table=>$column) {
        $fks=Schema::getForeignKeys($table);check('Admin foreign key: '.$table,collect($fks)->contains(fn($fk)=>in_array($column,$fk['columns']) && $fk['foreign_table']==='admins'));
    }
});
run('Currency totals and stale cancellation',function(){
    $gbp=supplier(['currency'=>'GBP']);$usd=supplier(['currency'=>'USD']);$a=po($gbp,['total_amount'=>10]);$b=po($usd,['total_amount'=>25]);
    $totals=PurchasingCurrencyService::totals(collect([$a,$b]));check('Currency totals remain separate',$totals===['GBP'=>10.0,'USD'=>25.0]);
    $data=(new App\Http\Controllers\Admin\PurchaseOrderController)->index(req())->getData();
    check('Dashboard uses per-currency totals',is_array($data['openOrderValue']) && isset($data['totalPurchaseValue']['GBP'],$data['totalPurchaseValue']['USD']));
    $html=(new App\Http\Controllers\Admin\SupplierController)->index(req())->render();check('Supplier list labels both currencies',str_contains($html,'GBP 10.00') && str_contains($html,'USD 25.00'));
    $stale=PurchaseOrder::find($a->id);$a->update(['status'=>'received']);(new App\Http\Controllers\Admin\PurchaseOrderController)->cancel($stale);
    check('Stale cancel cannot cancel received order',$a->fresh()->status==='received');
    $export=new App\Exports\PurchaseOrderExport($b);check('Purchase workbook uses order currency',str_contains($export->columnFormats()['J'],'USD'));
});
run('Exports and page render',function(){
    $valuation=new App\Http\Controllers\Admin\StockValuationController;
    $response=$valuation->exportPdf(req());check('Valuation PDF renders',$response->getStatusCode()===200 && str_starts_with($response->getContent(),'%PDF'));
    $xlsx=\Maatwebsite\Excel\Facades\Excel::raw(new App\Exports\StockValuationExport,\Maatwebsite\Excel\Excel::XLSX);check('Valuation Excel workbook generates',str_starts_with($xlsx,'PK'));
    $draft=po(supplier(),['status'=>'draft']);$product=Product::first();line($draft,$product);
    $view=(new App\Http\Controllers\Admin\PurchaseOrderDraftController)->edit($draft->fresh());$html=$view->render();check('Draft edit form carries monotonic version',str_contains($html,'name="version"') && !str_contains($html,'getTimestamp'));
});
run('Fresh schema installs', function () {
    $previous=config('database.default');config(['database.connections.inventory_fresh'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'','foreign_key_constraints'=>true],'database.default'=>'inventory_fresh']);
    try {
        foreach(['admins','users','suppliers','purchase_orders'] as $name) Schema::create($name,function(Blueprint $table){$table->id();});
        (require __DIR__.'/../_staged/database/migrations/2026_10_01_000001_repair_inventory_purchasing_schema.php')->up();
        check('Fresh schema creates document and rating tables',Schema::hasTable('supplier_documents') && Schema::hasTable('supplier_ratings'));
        check('Fresh schema includes rating UI fields',Schema::hasColumn('supplier_ratings','would_recommend') && Schema::hasColumn('supplier_ratings','rated_at') && Schema::hasColumn('supplier_ratings','title'));
    } finally {config(['database.default'=>$previous]);}
});
file_put_contents(__DIR__.'/../extra-checks.json',json_encode($results,JSON_PRETTY_PRINT));
echo 'COMPLETED '.count($results).' extra checks: '.count(array_filter($results,fn($r)=>$r['result']==='PASS')).' PASS; '.count(array_filter($results,fn($r)=>$r['result']!=='PASS'))." failures.\n";
