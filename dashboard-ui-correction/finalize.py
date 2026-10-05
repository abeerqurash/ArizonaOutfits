from pathlib import Path
import json
base=Path(__file__).parent;root=base.parent
s=(root/'arizona-ui-update/browser-check.mjs').read_text(encoding='utf-8')
s=s.replace("const stage='arizona-ui-update/_staged/';", "const stage='dashboard-ui-correction/_staged/';")
s=s.replace("fs.readFileSync(stage+'public/asset/js/product.js'", "fs.readFileSync('arizona-ui-update/_staged/public/asset/js/product.js'")
s=s.replace("fs.readFileSync(stage+'resources/views/admin/products/partials/value-order.blade.php'", "fs.readFileSync('arizona-ui-update/_staged/resources/views/admin/products/partials/value-order.blade.php'")
s=s.replace("fs.writeFileSync('arizona-ui-update/browser-checks.json'", "fs.writeFileSync('dashboard-ui-correction/browser-checks.json'")
s=s.replace("record('Admin controls fit viewport '+width,", "record('Native enhanced controls are hidden '+width,await page.$$eval('.az-date-native,.az-select-native',items=>items.every(item=>getComputedStyle(item).display==='none')));record('One short page banner '+width,await page.$$eval('.az-page-banner',items=>items.length===1));record('Purple primary accent '+width,await page.$eval('.az-date-trigger',button=>getComputedStyle(button).borderRadius==='10px'));record('Admin controls fit viewport '+width,")
(base/'browser-check.mjs').write_text(s,encoding='utf-8')
for i,path in enumerate(json.loads((base/'changed.json').read_text()),1):
 name=f'{i:02d}_{Path(path).name}.txt';(base/name).write_bytes((base/'_staged'/path).read_bytes())
