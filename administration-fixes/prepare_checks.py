from pathlib import Path
import json,subprocess
ROOT=Path(__file__).resolve().parent.parent;OUT=ROOT/'administration-fixes';CHECK=OUT/'_checks';CHECK.mkdir(exist_ok=True)
bootstrap=r'''<?php
require __DIR__.'/../../vendor/autoload.php';
spl_autoload_register(function($class){if(str_starts_with($class,'App\\')){$path=__DIR__.'/../_staged/app/'.str_replace('\\','/',substr($class,4)).'.php';if(is_file($path))require $path;}},true,true);
require __DIR__.'/../../administration-audit/_checks/bootstrap.php';
(require __DIR__.'/../../database/migrations/2026_10_01_000001_repair_inventory_purchasing_schema.php')->up();
config(['view.compiled'=>__DIR__.'/compiled']);@mkdir(__DIR__.'/compiled',0777,true);
$app['view']->getFinder()->setPaths([__DIR__.'/../_staged/resources/views',resource_path('views')]);
// Load the exact staged route file, with its unchanged auth include in the fixture directory.
$app['router']->setRoutes(new Illuminate\Routing\RouteCollection);
require __DIR__.'/../_staged/routes/web.php';
$app['router']->getRoutes()->refreshNameLookups();$app['router']->getRoutes()->refreshActionLookups();$app['url']->setRoutes($app['router']->getRoutes());
Illuminate\Support\Facades\Schema::table('orders',function(Illuminate\Database\Schema\Blueprint $t){foreach(['shipping_method','estimated_delivery','billing_state','billing_zip','shipping_address','shipping_city','shipping_state','shipping_zip','order_notes'] as $name)if(!Illuminate\Support\Facades\Schema::hasColumn('orders',$name))$t->string($name)->nullable();foreach(['tax','shipping_price'] as $name)if(!Illuminate\Support\Facades\Schema::hasColumn('orders',$name))$t->decimal($name,12,2)->default(0);});
'''
(CHECK/'bootstrap.php').write_text(bootstrap,encoding='utf-8')
(OUT/'_staged/routes/auth.php').write_bytes((ROOT/'routes/auth.php').read_bytes())
s=(ROOT/'administration-audit/_checks/verify.php').read_text(encoding='utf-8')
# Independent requests must not inherit another action's success flash in this fixture session.
s=s.replace("$response=$controller->send(req($data));check('Notification rejects backslash", "$appSession=app('session')->driver();$appSession->forget(['success','error','errors']);$response=$controller->send(req($data));check('Notification rejects backslash")
# Keep the original audit evidence intact; write results alongside this delivery.
(CHECK/'verify.php').write_text(s,encoding='utf-8')
for package in ['inventory-purchasing','customer-content']:
    source=package+'-fixes' if package=='inventory-purchasing' else package+'-audit'
    s=(ROOT/(source+'/_checks/verify.php')).read_text(encoding='utf-8')
    s=s.replace("require __DIR__.'/extend.php';", "require __DIR__.'/../../inventory-purchasing-fixes/_checks/extend.php';")
    s=s.replace("__DIR__.'/../checks.json'", "__DIR__.'/../"+package+"-regression-checks.json'")
    s=s.replace("__DIR__.'/../page-render-checks.json'", "__DIR__.'/../"+package+"-regression-page-checks.json'")
    (CHECK/(package+'-regression.php')).write_text(s,encoding='utf-8')
s=(ROOT/'administration-audit/_checks/routes.php').read_text(encoding='utf-8')
s=s.replace("__DIR__.'/../../resources/views/'", "__DIR__.'/../_staged/resources/views/'")
# Check a merged list of installed/staged views below, rather than only changed directories.
s=s.replace("$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../_staged/resources/views/'.$directory));", "$viewDirectory=__DIR__.'/../_staged/resources/views/'.$directory;if(!is_dir($viewDirectory))$viewDirectory=__DIR__.'/../../resources/views/'.$directory;$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewDirectory));")
(CHECK/'routes.php').write_text(s,encoding='utf-8')
s=(ROOT/'inventory-purchasing-fixes/_checks/extra.php').read_text(encoding='utf-8')
s=s.replace("__DIR__.'/../_staged/database/migrations/2026_10_01_000001_repair_inventory_purchasing_schema.php'", "__DIR__.'/../../database/migrations/2026_10_01_000001_repair_inventory_purchasing_schema.php'")
s=s.replace("__DIR__.'/../extra-checks.json'", "__DIR__.'/../inventory-extra-regression-checks.json'")
(CHECK/'inventory-extra-regression.php').write_text(s,encoding='utf-8')
syntax=[]
for item in json.loads((OUT/'manifest.json').read_text()):
    p=OUT/'_staged'/item['destination']
    if p.suffix=='.php' and not p.name.endswith('.blade.php'):
        result=subprocess.run([r'C:\xampp\php\php.exe','-l',str(p)],capture_output=True,text=True)
        syntax.append({'path':item['destination'],'pass':result.returncode==0,'output':result.stdout+result.stderr})
(OUT/'syntax-checks.json').write_text(json.dumps(syntax,indent=2),encoding='utf-8')
print(f'Syntax: {sum(x["pass"] for x in syntax)}/{len(syntax)} pass')
for x in syntax:
    if not x['pass']:print(x)
