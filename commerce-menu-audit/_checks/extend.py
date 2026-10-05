from pathlib import Path
p=Path(r'C:\xampp\htdocs\ArizonaOutfits\commerce-menu-audit\_checks\verify.php')
s=p.read_text(encoding='utf-8-sig').replace("'status'=>'succeeded','metadata'=>", "'status'=>'succeeded','latest_charge'=>null,'metadata'=>")
marker="DB::disconnect('commerce_audit');"
extra=r'''
config(['view.compiled'=>__DIR__.'/compiled']);@mkdir(__DIR__.'/compiled');$app['view']->share('errors',new Illuminate\Support\ViewErrorBag);
$admin->is_super_admin=true;$app['auth']->shouldUse('admin');
$pages=[
'Admin Orders'=>fn()=>$adminOrders->index(req()),
'Archived Orders'=>fn()=>$adminOrders->archived(req()),
'Payment Verifications'=>fn()=>$payments->index(req()),
'Payment Verification detail'=>fn()=>$payments->show($bank->fresh()),
'Products'=>fn()=>$catalog->index(req()),
'Product Categories'=>fn()=>$categories->index(req()),
'Product Tags'=>fn()=>$tags->index(req()),
'Coupons'=>fn()=>$coupons->index(req()),
];
$renderResults=[];
foreach($pages as $label=>$factory){try{$view=$factory();$html=$view->render();record(strlen($html)>1000,'Fixture page renders: '.$label);$renderResults[$label]=['result'=>'PASS','bytes'=>strlen($html)];}catch(Throwable $e){$renderResults[$label]=['result'=>'RENDER ERROR','message'=>$e->getMessage()];echo 'RENDER ERROR: '.$label.' '.$e->getMessage()."\n";}}
$app['auth']->shouldUse('web');
foreach(['Customer order list'=>fn()=>$customerController->orders(req()),'Customer order detail'=>fn()=>$customerController->show(req(),$bank->id),'Customer invoice'=>fn()=>$customerController->invoice(req(),$bank->id)] as $label=>$factory){try{$html=$factory()->render();record(strlen($html)>1000,'Fixture page renders: '.$label);$renderResults[$label]=['result'=>'PASS','bytes'=>strlen($html)];}catch(Throwable $e){$renderResults[$label]=['result'=>'RENDER ERROR','message'=>$e->getMessage()];echo 'RENDER ERROR: '.$label.' '.$e->getMessage()."\n";}}
file_put_contents(__DIR__.'/../page-render-checks.json',json_encode($renderResults,JSON_PRETTY_PRINT));
'''
assert marker in s;s=s.replace(marker,extra+'\n'+marker,1);p.write_text(s,encoding='utf-8')
