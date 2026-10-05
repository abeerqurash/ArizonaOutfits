from pathlib import Path
import json,hashlib
base=Path(__file__).parent;stage=base/'_staged';root=base.parent
files=json.loads((base/'changed.json').read_text())
for file in ['public/asset/css/arizona-admin-controls.css','public/asset/js/arizona-admin-controls.js']:
 if file not in files:files.append(file)
(base/'changed.json').write_text(json.dumps(files,indent=2))
lines=['# Complete replacement files','', 'Project: `C:\\xampp\\htdocs\\ArizonaOutfits`','', 'Replace each destination with the entire linked text file. New files must be created. Install these files together and run the migration.','', '| Replacement | Exact destination |','|---|---|'];manifest=[]
for index,path in enumerate(files,1):
 name=f'{index:02d}_{Path(path).name}.txt';content=(stage/path).read_bytes();(base/name).write_bytes(content)
 lines.append(f'| [{name}]({name}) | `{root/path}` |');manifest.append({'replacement':name,'destination':str(root/path),'sha256':hashlib.sha256(content).hexdigest()})
(base/'FILES.md').write_text('\n'.join(lines),encoding='utf-8');(base/'manifest.json').write_text(json.dumps(manifest,indent=2))
print('Packaged',len(files),'replacement files')
