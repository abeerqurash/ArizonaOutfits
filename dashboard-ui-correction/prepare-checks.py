from pathlib import Path
base=Path(__file__).parent;prior=base.parent/'arizona-ui-update'
s=(prior/'admin-render-check.php').read_text(encoding='utf-8')
s=s.replace("require __DIR__.'/render-check.php';", "require dirname(__DIR__).'/arizona-ui-update/render-check.php';\napp('view')->getFinder()->prependLocation(__DIR__.'/_staged/resources/views');")
s=s.replace(" 'create-product'=>", " 'products'=>['/admin/products',App\\Http\\Controllers\\Admin\\ProductController::class,'index'],\n 'create-product'=>")
(base/'render-check.php').write_text(s,encoding='utf-8')
s=(prior/'admin-layout-check.mjs').read_text(encoding='utf-8').replace('arizona-ui-update','dashboard-ui-correction')
s=s.replace("const result=await page.evaluate(()=>({overflow:", "const result=await page.evaluate(()=>({banners:document.querySelectorAll('.az-page-banner').length,visibleNative:[...document.querySelectorAll('select[class*=select-native],input.az-date-native')].filter(e=>e.offsetParent).length,overflow:")
(base/'layout-check.mjs').write_text(s,encoding='utf-8')
