from pathlib import Path
import shutil
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');d=r/'complete-project-audit/dependencies';(d/'resources/css/app.css').write_text('@import "tailwindcss";\n@source "../views/**/*.blade.php";\n@plugin "@tailwindcss/forms";\n')
shutil.copytree(r/'resources/views',d/'resources/views',dirs_exist_ok=True)
o=r/'complete-project-audit/_checks';o.mkdir(exist_ok=True)
s=(r/'customer-content-audit/_checks/bootstrap.php').read_text(encoding='utf-8-sig');s=s.replace("require __DIR__.'/../../vendor/autoload.php';","require __DIR__.'/../dependencies/vendor/autoload.php';\nspl_autoload_register(function($class){if(str_starts_with($class,'App\\\\')){$path=__DIR__.'/../replacement-files/_staged/app/'.str_replace('\\\\','/',substr($class,4)).'.php';if(is_file($path))require $path;}},true,true);")
(o/'base.php').write_text(s,encoding='utf-8')
s=(r/'administration-audit/_checks/bootstrap.php').read_text(encoding='utf-8-sig').replace("require __DIR__.'/../../customer-content-audit/_checks/bootstrap.php';","require __DIR__.'/base.php';");(o/'admin-bootstrap.php').write_text(s,encoding='utf-8')
s=(r/'customer-side-audit/_checks/bootstrap.php').read_text(encoding='utf-8-sig').replace("require __DIR__.'/../../administration-audit/_checks/bootstrap.php';","require __DIR__.'/admin-bootstrap.php';");(o/'bootstrap.php').write_text(s,encoding='utf-8')
# copied checks use current app plus only proposed fix autoloader, with new dependencies.
for label,source in [('admin','administration-audit/_checks/verify.php'),('customer','customer-side-audit/_checks/verify.php'),('checkout','customer-side-audit/_checks/checkout-sync.php'),('content','customer-content-audit/_checks/verify.php')]:
 s=(r/source).read_text(encoding='utf-8-sig');(o/(label+'.php')).write_text(s,encoding='utf-8')
for label in ['inventory-purchasing-regression','inventory-extra-regression','bell','dashboard']:
 s=(r/'complete-project-audit'/ (label+'.php')).read_text(encoding='utf-8-sig').replace("require __DIR__.'/../customer-side-audit/_checks/bootstrap.php';","require __DIR__.'/bootstrap.php';").replace("__DIR__.'/../inventory-purchasing-fixes/","__DIR__.'/../../inventory-purchasing-fixes/").replace("__DIR__.'/../database/","__DIR__.'/../../database/")
 (o/(label+'.php')).write_text(s,encoding='utf-8')
