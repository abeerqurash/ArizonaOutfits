from pathlib import Path
import zipfile,json,hashlib,re
base=Path(__file__).parent;root=base.parent;snapshot=base/'snapshot';snapshot.mkdir(exist_ok=True)
with zipfile.ZipFile(root.parent/'ArizonaOutfits.zip') as archive:
 names=archive.namelist();prefix=next(name[:-len('artisan')] for name in names if name.endswith('/artisan') and name.count('/')<=2)
 selected=[];different=[]
 for name in names:
  if not name.startswith(prefix):continue
  relative=name[len(prefix):]
  if not relative or name.endswith('/'):continue
  if not (relative.startswith(('app/','resources/views/','routes/','config/','database/migrations/','public/asset/css/','public/asset/js/')) or relative in ['composer.json','package.json']):continue
  p=snapshot/relative
  if '..' in Path(relative).parts:raise RuntimeError('Unexpected archive path')
  p.parent.mkdir(parents=True,exist_ok=True);data=archive.read(name);p.write_bytes(data);selected.append(relative)
  if not(root/relative).is_file() or hashlib.sha256(data).digest()!=hashlib.sha256((root/relative).read_bytes()).digest():different.append(relative)
sql=Path('C:/Users/Laptronics.co/Downloads/arizonaoutfits (11).sql').read_text(encoding='utf-8-sig')
tables=re.findall(r'CREATE TABLE\s+`([^`]+)`',sql,re.I)
report={'archive_prefix':prefix,'selected_files':len(selected),'different_from_workspace':different,'sql_tables':tables}
(base/'inventory.json').write_text(json.dumps(report,indent=2));print(json.dumps({'archive_files':len(selected),'different_files':different,'sql_table_count':len(tables)},indent=2))
