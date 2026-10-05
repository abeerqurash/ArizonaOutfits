from pathlib import Path
import json,re
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'storefront-sync-files';m=json.loads((o/'manifest.json').read_text())
for item in m:
 p=o/'_staged'/item['destination'];s=p.read_text()
 if p.name.endswith('.blade.php'):
  s=s.replace('@endif@endforeach','@endif\n@endforeach').replace('@endforeach</','@endforeach\n</')
 if item['destination']=='resources/views/products/index.blade.php':
  s=s.replace("                                'products.partials.product-card',","                                isset($currentCategory) ? 'products.partials.category-product-card' : 'products.partials.product-card',",1)
 p.write_text(s);(o/item['file']).write_text(s)
