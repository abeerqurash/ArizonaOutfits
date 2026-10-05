from pathlib import Path
b=Path(__file__).parent;r=b.parent
s=(r/'dashboard-ui-correction/render-check.php').read_text()
s=s.replace("$checks=[];", """$pages['categories']=['/admin/product-categories',App\\Http\\Controllers\\Admin\\ProductCategoryController::class,'index'];
$pages['create-category']=['/admin/product-categories/create',App\\Http\\Controllers\\Admin\\ProductCategoryController::class,'create'];
$pages['coupons']=['/admin/coupons',App\\Http\\Controllers\\Admin\\CouponController::class,'index'];
$pages['create-coupon']=['/admin/coupons/create',App\\Http\\Controllers\\Admin\\CouponController::class,'create'];
$checks=[];""")
s+='''
foreach(glob(dirname(__DIR__).'/app/Http/Controllers/Admin/*.php') as $controllerFile){
 $class='App\\\\Http\\\\Controllers\\\\Admin\\\\'.basename($controllerFile,'.php');
 foreach(['index','create'] as $method){
 if(!method_exists($class,$method))continue;
 $reflection=new ReflectionMethod($class,$method);$parameters=$reflection->getParameters();
 if(count($parameters)>1||(count($parameters)===1&&(string)$parameters[0]->getType()!=='Illuminate\\\\Http\\\\Request'))continue;
 $name='all-'.basename($controllerFile,'.php').'-'.$method;
 try{$request=Illuminate\\Http\\Request::create('http://127.0.0.1:8000/admin');$request->setLaravelSession($session);$app->instance('request',$request);$view=count($parameters)?app($class)->$method($request):app($class)->$method();if($view instanceof Illuminate\\Contracts\\View\\View){file_put_contents($output.'/'.$name.'.html',$view->render());$checks[$name]='rendered';}}catch(Throwable $e){$checks[$name]=get_class($e).': '.$e->getMessage();}
 }
}
$customer=App\\Models\\User::firstOrCreate(['email'=>'customer-ui@example.test'],['name'=>'UI fixture','status'=>'active','is_admin'=>false,'is_super_admin'=>false,'password'=>bcrypt('fixture-only')]);
auth()->setUser($customer);
foreach(['customer-dashboard'=>'index','customer-orders'=>'orders'] as $name=>$method){
 try{$request=Illuminate\\Http\\Request::create('http://127.0.0.1:8000/dashboard');$request->setLaravelSession($session);$request->setUserResolver(fn()=>$customer);$app->instance('request',$request);file_put_contents($output.'/'.$name.'.html',app(App\\Http\\Controllers\\CustomerDashboardController::class)->$method($request)->render());$checks[$name]='rendered';}catch(Throwable $e){$checks[$name]=$e->getMessage();}
}
foreach(['customer-profile'=>[App\\Http\\Controllers\\ProfileController::class,'edit'],'customer-security'=>[App\\Http\\Controllers\\Customer\\AccountSecurityController::class,'index']] as $name=>[$class,$method]){try{file_put_contents($output.'/'.$name.'.html',app($class)->$method($request)->render());$checks[$name]='rendered';}catch(Throwable $e){$checks[$name]=get_class($e).': '.$e->getMessage();}}
file_put_contents(__DIR__.'/admin-render-checks.json',json_encode($checks,JSON_PRETTY_PRINT));
echo json_encode($checks,JSON_PRETTY_PRINT);
'''
s=s.replace('app($class)->$method($request)->render()', "app()->call([app($class),$method], ['request'=>$request])->render()")
(b/'render-check.php').write_text(s)
s=(r/'dashboard-ui-correction/layout-check.mjs').read_text().replace('dashboard-ui-correction','project-ui-audit')
s=s.replace("select[class*=select-native],input.az-date-native", "select[class*=native],input.az-date-native")
s=s.replace("await upload.uploadFile(path.join(root,'project-ui-audit/create-product-1440.png'));", "await upload.uploadFile(path.join(root,'dashboard-ui-correction/create-product-1440.png'));")
s=s.replace("calendars:document.querySelectorAll('.az-date-trigger').length", "calendars:document.querySelectorAll('.az-date-trigger').length,nestedControls:document.querySelectorAll('.az-select-wrap .az-select-wrap,.cat-index-select .az-select-wrap,.az-cat-select .az-select-wrap,.az-coupon-select .az-select-wrap,.az-coupon-filter .az-select-wrap').length")
s=s.replace("await browser.close();", "if(checks.some(c=>c.visibleNative||c.nestedControls||c.overflow||c.errors.length||c.banners!==1))process.exitCode=1;await browser.close();")
(b/'layout-check.mjs').write_text(s)
s=(r/'dashboard-ui-correction/browser-check.mjs').read_text().replace('dashboard-ui-correction','project-ui-audit')
(b/'browser-check.mjs').write_text(s)
