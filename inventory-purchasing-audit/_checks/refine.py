from pathlib import Path
P=Path(r'C:\xampp\htdocs\ArizonaOutfits\inventory-purchasing-audit\_checks\verify.php')
s=P.read_text(encoding='utf-8')
a=s.index('$stockBefore=$simple->fresh()->stock;');b=s.index('// Use an overlapping',a)
s=s[:a]+s[b:]
s=s.replace("$receipt=$po2->receipts()->first();", "$receipt=PurchaseOrderReceipt::where('purchase_order_id',$po2->id)->first();")
s=s.replace("$line2->fresh()->quantity_received===1", "$line2->fresh()->quantity_received===3")
needle="run('Receipt quantity and ownership guards'"
idx=s.index(needle)
s=s[:idx]+'''run('Receiving synchronizes alerts',function()use($receiving,$po2,$line2,$simple){$receiving->store(req(['items'=>[['purchase_order_item_id'=>$line2->id,'quantity_received'=>2]]]),$po2);check('Replenishment resolves active alert after stock exceeds threshold',!InventoryAlert::where('product_id',$simple->id)->where('status','active')->exists(),'Stock now '.$simple->fresh()->stock.'; threshold 5');check('Full receipt changes order to received',$po2->fresh()->status==='received');});
'''+s[idx:]
old="check('Reports reject malformed custom dates',validationBlocked(fn()=>$reports->index(req(['date_range'=>'custom','start_date'=>'not-a-date','end_date'=>'2026-10-01']))));"
new="try{$reports->index(req(['date_range'=>'custom','start_date'=>'not-a-date','end_date'=>'2026-10-01']));check('Reports reject malformed dates with validation',false,'Invalid date accepted');}catch(Illuminate\\Validation\\ValidationException){check('Reports reject malformed dates with validation',true);}catch(Carbon\\Exceptions\\InvalidFormatException $e){check('Reports reject malformed dates with validation',false,'Uncaught Carbon parse exception instead of validation');}"
assert old in s;s=s.replace(old,new)
s=s.replace("$r=req(['title'=>'Fixture contract','document_type'=>'contract']);", "$r=req(['title'=>'Fixture contract','document_type'=>'contract','expires_at'=>now()->addDays(10)->toDateString()]);")
s=s.replace("'Purchase Order create'=>fn()=>$purchasing->create(req())", "'Purchase Order create'=>fn()=>$purchasing->createFromReorder(req(['selected_items'=>['product:'.$simple->id]]))")
idx=s.index('// Full actual menu pages')
s=s[:idx]+'''run('Supplier document expiry alias',function()use($supplier){$document=$supplier->documents()->first();check('Document expiry alias exposes a date object for the view',$document->expires_at instanceof Carbon\\CarbonInterface,'Alias value type='.get_debug_type($document->expires_at));});
'''+s[idx:]
P.write_text(s,encoding='utf-8')
print('Refined fixtures to test only reachable receiving flow and actual create path.')
