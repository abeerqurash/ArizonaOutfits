from pathlib import Path
import json,shutil,hashlib
out=Path(__file__).resolve().parent;root=out.parent;stage=root/'optimization-round-2/_staged'
rows=[]
for n,rel in enumerate(json.loads((out/'changed.json').read_text()),1):
 source=stage/rel
 name=f'{n:02d}_{source.name}'+('' if source.suffix=='.woff2' else '.txt')
 shutil.copyfile(source,out/name)
 rows.append({'file':name,'destination':rel,'sha256':hashlib.sha256(source.read_bytes()).hexdigest()})
(out/'manifest.json').write_text(json.dumps(rows,indent=2),encoding='utf-8')
lines=['# Round 3 replacement files','','Install after round 2. Replace whole files at the exact destinations below. The three WOFF2 font files are binary files: copy them directly and retain .woff2. Do not convert them to PHP or paste their contents. Everything else is provided as a separate text file.','','Read INSTALL.txt before starting. The real application files and php.ini have not been overwritten.','']
for r in rows:lines.append(f"- [{r['file']}]({(out/r['file']).as_posix()}) → `{(root/r['destination'])}`")
(out/'FILES.md').write_text('\n'.join(lines)+'\n',encoding='utf-8')
for r in rows:assert hashlib.sha256((out/r['file']).read_bytes()).hexdigest()==r['sha256']
print('Packaged',len(rows),'files:',sum(r['file'].endswith('.woff2') for r in rows),'binary fonts, remaining files as text.')
