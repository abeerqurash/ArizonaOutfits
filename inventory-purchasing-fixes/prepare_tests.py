from pathlib import Path
import re
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'inventory-purchasing-fixes';T=O/'_checks';T.mkdir(exist_ok=True)
for name in ['bootstrap.php','verify.php','extend.php','routes.php']:
 s=(R/'inventory-purchasing-audit/_checks'/name).read_text(encoding='utf-8-sig')
 if name=='bootstrap.php':
  s=s.replace("require __DIR__.'/../../vendor/autoload.php';",'''require __DIR__.'/../../vendor/autoload.php';
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\\\')) {
        $path = __DIR__.'/../_staged/app/'.str_replace('\\\\', '/', substr($class, 4)).'.php';
        if (is_file($path)) require $path;
    }
}, true, true);
''')
  needle='$customer=User::create('
  s=s.replace(needle,"(require __DIR__.'/../_staged/database/migrations/2026_10_01_000001_repair_inventory_purchasing_schema.php')->up();\n$app['view']->getFinder()->prependLocation(__DIR__.'/../_staged/resources/views');\n"+needle)
 # Adapt audit assertions to the new actor accessors/version and intentional validation.
 if name=='verify.php':
  a=s.index("run('Purchase order create and duplicate validation'");b=s.index('// Use an overlapping customer ID',a)
  s=s[:a]+'''run('Purchase order create and duplicate validation',function()use($purchasing,$supplier,$simple){
    $data=['supplier_id'=>$supplier->id,'order_date'=>now()->toDateString(),'items'=>[['identifier'=>'product:'.$simple->id,'quantity'=>2,'unit_cost'=>12],['identifier'=>'product:'.$simple->id,'quantity'=>2,'unit_cost'=>12]]];
    check('Purchase-order creation rejects duplicate item identifiers',validationBlocked(fn()=>$purchasing->store(req($data))));
    array_pop($data['items']);$purchasing->store(req($data));$created=PurchaseOrder::latest('id')->first();
    check('Purchase-order creator is admin not customer',$created->admin_id===99 && $created->created_by===null);
    $purchasing->markOrdered($created);check('Draft can transition to ordered',$created->fresh()->status==='ordered');
});
'''+s[b:]
 if name in ['verify.php','extend.php']:
  s=s.replace('->receiver','->receiver_actor').replace('->uploader','->uploader_actor').replace('->ratedBy','->ratedBy_actor').replace('->sentBy','->sentBy_actor').replace('->creator instanceof App\\Models\\Admin','->creator_actor instanceof App\\Models\\Admin').replace('->completer instanceof App\\Models\\Admin','->completer_actor instanceof App\\Models\\Admin').replace('->canceller instanceof App\\Models\\Admin','->canceller_actor instanceof App\\Models\\Admin')
  s=s.replace("$draft->updated_at->getTimestamp()", "$draft->lock_version").replace("$draft->fresh()->updated_at->getTimestamp()", "$draft->fresh()->lock_version")
  s=s.replace("$po->updated_at->getTimestamp()", "$po->lock_version")
 if name=='extend.php':
  s=s.replace("$data=$reports->index(req(['date_range'=>'custom','start_date'=>'2026-10-02','end_date'=>'2026-10-01']))->getData();check('Inverted report dates are rejected',$data['startDate']->lte($data['endDate']),'Accepted start after end');", "check('Inverted report dates are rejected',validationBlocked(fn()=>$reports->index(req(['date_range'=>'custom','start_date'=>'2026-10-02','end_date'=>'2026-10-01']))));")
 (T/name).write_text(s,encoding='utf-8')
print('Isolated staged-class/view harness prepared.')
