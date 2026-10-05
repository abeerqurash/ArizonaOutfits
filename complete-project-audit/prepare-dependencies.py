from pathlib import Path
import json
p=Path(r'C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\dependencies\composer.json');j=json.loads(p.read_text(encoding='utf-8-sig'));j['require']['maatwebsite/excel']='^3.1.70';p.write_text(json.dumps(j,indent=4)+'\n',encoding='utf-8')
