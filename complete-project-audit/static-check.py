import json,subprocess,concurrent.futures
from pathlib import Path
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'complete-project-audit';php=r'C:\xampp\php\php.exe';node=r'C:\Program Files\nodejs\node.exe'
files=[p for d in ['app','bootstrap','config','database','routes','tests'] for p in (r/d).rglob('*.php') if 'cache' not in p.parts]
js=list((r/'public/asset/js').glob('*.js'))
def check(p):
 cmd=[node,'--check',str(p)] if p.suffix=='.js' else [php,'-l',str(p)];v=subprocess.run(cmd,capture_output=True,text=True);return {'path':str(p.relative_to(r)),'pass':v.returncode==0,'error':(v.stderr+v.stdout)[-1500:] if v.returncode else ''}
with concurrent.futures.ThreadPoolExecutor(max_workers=6) as pool:results=list(pool.map(check,files+js))
(o/'syntax-results.json').write_text(json.dumps(results,indent=2));print('Syntax checked:',len(results),'Failures:',[x for x in results if not x['pass']])
routes=json.loads((o/'routes.json').read_text(encoding='utf-8-sig'));print('Routes:',len(routes));print('Admin routes without admin middleware:',[(x['uri'],x['name']) for x in routes if x['uri'].startswith('admin') and not any('AdminMiddleware' in v or v.startswith('admin') for v in x['middleware']) and not any(s in x['uri'] for s in ['login','forgot-password','reset-password','logout'])]);
