from pathlib import Path
import zipfile,hashlib,json
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');out=r/'complete-project-audit';out.mkdir(exist_ok=True);snap=out/'snapshot';snap.mkdir(exist_ok=True);manifest=[]
with zipfile.ZipFile(r.parent/'ArizonaOutfits.zip') as z:
 names=z.namelist();prefix=names[0].split('/')[0]+'/' if names and names[0].startswith('ArizonaOutfits/') else ''
 for entry in z.infolist():
  name=entry.filename[len(prefix):] if prefix and entry.filename.startswith(prefix) else entry.filename
  if entry.is_dir() or '..' in Path(name).parts:continue
  parts=Path(name).parts
  if not parts or not (parts[0] in ['app','bootstrap','config','database','resources','routes','tests'] or name in ['composer.json','composer.lock','package.json','package-lock.json','phpunit.xml','.gitignore','.env.example'] or name.startswith('public/asset/js/') or name.startswith('public/asset/css/')):continue
  if parts[0]=='bootstrap' and 'cache' in parts:continue
  data=z.read(entry);dest=snap/name;dest.parent.mkdir(parents=True,exist_ok=True);dest.write_bytes(data);live=r/name
  manifest.append({'path':name,'sha256':hashlib.sha256(data).hexdigest(),'matches_workspace':live.is_file() and hashlib.sha256(live.read_bytes()).digest()==hashlib.sha256(data).digest()})
(out/'archive-manifest.json').write_text(json.dumps(manifest,indent=2));print('Snapshot files:',len(manifest));print('Different/missing in workspace:',[x['path'] for x in manifest if not x['matches_workspace']][:30])
