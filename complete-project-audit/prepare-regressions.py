from pathlib import Path
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'complete-project-audit';
for name in ['inventory-purchasing-regression.php','inventory-extra-regression.php','bell.php','dashboard.php']:
 s=(r/'customer-dashboard-files/_checks'/name).read_text(encoding='utf-8-sig').replace("require __DIR__.'/bootstrap.php';","require __DIR__.'/../customer-side-audit/_checks/bootstrap.php';")
 (o/name).write_text(s,encoding='utf-8')
