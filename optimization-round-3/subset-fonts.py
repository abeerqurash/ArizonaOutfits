from pathlib import Path
import sys,re,json,shutil
out=Path(__file__).resolve().parent;stage=out.parent/'optimization-round-2/_staged'
sys.path.insert(0,str(out/'font-tools'))
from fontTools import subset
from fontTools.ttLib import TTFont
css=(stage/'public/asset/css/storefront-icons.css').read_text(encoding='utf-8')
codes={int(x,16) for x in re.findall(r'--fa:["\']\\([0-9a-fA-F]+)',css)}
changed=json.loads((out/'changed.json').read_text())
for name in ['fa-solid-900','fa-regular-400','fa-brands-400']:
 font=TTFont(out/'fonts-original'/f'{name}.woff2');opts=subset.Options();opts.flavor='woff2';opts.name_IDs=['*'];opts.name_legacy=True;opts.name_languages=['*'];opts.recalc_timestamp=False
 worker=subset.Subsetter(options=opts);worker.populate(unicodes=codes);worker.subset(font)
 rel=f'public/asset/fonts/storefront-{name}.woff2';path=stage/rel;path.parent.mkdir(parents=True,exist_ok=True);font.save(path);changed.append(rel)
 css=css.replace(f'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/webfonts/{name}.woff2',f'../fonts/storefront-{name}.woff2')
 print(name,'original', (out/'fonts-original'/f'{name}.woff2').stat().st_size,'subset',path.stat().st_size)
(stage/'public/asset/css/storefront-icons.css').write_text(css,encoding='utf-8')
rel='resources/views/partials/icon-font-display.blade.php';s=(stage/rel).read_text()
for name in ['fa-solid-900','fa-regular-400','fa-brands-400']:
 s=s.replace(f'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/webfonts/{name}.woff2',f'/asset/fonts/storefront-{name}.woff2')
(stage/rel).write_text(s,encoding='utf-8');changed.append(rel)
rel='public/asset/fonts/FontAwesome-LICENSE.txt';shutil.copyfile(out/'FontAwesome-LICENSE.txt',stage/rel);changed.append(rel)
(out/'changed.json').write_text(json.dumps(changed),encoding='utf-8')
print('Mapped unicode values',len(codes))
