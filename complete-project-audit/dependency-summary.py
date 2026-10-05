from pathlib import Path
import json
r=Path(r'C:\xampp\htdocs\ArizonaOutfits/complete-project-audit')
for file in ['composer-audit.json','npm-audit.json']:
 j=json.loads((r/file).read_text(encoding='utf-8-sig'))
 if 'advisories' in j:print('Composer:',{k:[{'title':a['title'],'severity':a.get('severity'),'link':a['link']} for a in v] for k,v in j['advisories'].items()})
 else:print('npm:',{k:{'severity':v['severity'],'fixAvailable':v['fixAvailable'],'via':[a.get('title') if isinstance(a,dict) else a for a in v['via']]} for k,v in j['vulnerabilities'].items()});print(j.get('metadata',{}).get('vulnerabilities',{}))
