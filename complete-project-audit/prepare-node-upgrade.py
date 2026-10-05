from pathlib import Path
import json,shutil
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');d=r/'complete-project-audit/dependencies';p=d/'package.json';j=json.loads(p.read_text());j['devDependencies']['tailwindcss']='^4.3.3';j['devDependencies']['@tailwindcss/vite']='^4.3.3';j['overrides']={'esbuild':'^0.28.2'};p.write_text(json.dumps(j,indent=4)+'\n')
for name in ['resources/js/app.js','resources/js/bootstrap.js']:
 t=d/name;t.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(r/name,t)
s=(r/'vite.config.js').read_text();s=s.replace("import laravel from 'laravel-vite-plugin';","import laravel from 'laravel-vite-plugin';\nimport tailwindcss from '@tailwindcss/vite';").replace('    plugins: [','    plugins: [\n        tailwindcss(),');(d/'vite.config.js').write_text(s)
p=d/'resources/css/app.css';p.parent.mkdir(parents=True,exist_ok=True);p.write_text('@import "tailwindcss";\n@source "../../../resources/views/**/*.blade.php";\n@plugin "@tailwindcss/forms";\n')
