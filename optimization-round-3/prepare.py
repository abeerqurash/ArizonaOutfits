from pathlib import Path
import re,json,hashlib
root=Path(__file__).resolve().parents[1]
out=root/'optimization-round-3'
stage=root/'optimization-round-2/_staged'
changed=[]
def save(rel,s):
 p=stage/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8');changed.append(rel)
def read(rel):return (root/rel).read_text(encoding='utf-8-sig')
rel='resources/views/partials/header.blade.php'
s=read(rel).replace('id="arizonaMegaMenu" aria-hidden="true"','id="arizonaMegaMenu" aria-hidden="true" inert')
s=s.replace("panel.setAttribute('aria-hidden', String(!open));","panel.setAttribute('aria-hidden', String(!open));\n        panel.inert = !open;")
save(rel,s)
rel='resources/views/auth/partials/visual-copy.blade.php'
save(rel,read(rel).replace(' aria-hidden="true"',''))
for rel in ['resources/views/about.blade.php','resources/views/contact.blade.php']:
 s=read(rel)
 s=re.sub(r'<a([^>]*class="moving-circle"[^>]*)>',lambda m:'<a'+m[1]+' aria-label="'+('Explore contact options' if 'contact' in rel else 'Explore more about us')+'">' if 'aria-label' not in m[1] else m[0],s)
 labels={'facebook-f':'Facebook','instagram':'Instagram','linkedin':'LinkedIn','linkedin-in':'LinkedIn','youtube':'YouTube','x-twitter':'X'}
 def label(m):
  if 'aria-label=' in m[1]:return m[0]
  icon=re.search(r'fa-(facebook-f|instagram|linkedin-in|linkedin|youtube|x-twitter)\b',m[2])
  return '<a'+m[1]+' aria-label="'+labels[icon[1]]+'">'+m[2]+'</a>' if icon else m[0]
 s=re.sub(r'<a([^>]*)>(\s*<i[^>]*></i>\s*)</a>',label,s)
 save(rel,s)
rel='resources/views/products/index.blade.php'
save(rel,read(rel).replace('color:#6e7488','color:#606779'))
# Keep Font Awesome's base styles, font declarations and all icons referenced in application code.
icons=set()
for folder in ['resources','app','public/asset/js']:
 for p in (root/folder).rglob('*'):
  if p.is_file() and p.suffix in ['.php','.js','.css']:
   icons.update(re.findall(r'\bfa-([a-z0-9-]+)',p.read_text(encoding='utf-8-sig',errors='ignore')))
css=(out/'fontawesome-original.css').read_text(encoding='utf-8-sig')
def subset(m):
 selector,body=m[1],m[2]
 if re.search(r'--fa:["\']',body):
  names=set(re.findall(r'\.fa-([a-z0-9-]+)',selector))
  if names and not names.intersection(icons):return ''
 return m[0]
css=re.sub(r'([^{}]+)\{([^{}]*)\}',subset,css)
css=css.replace('font-display:block','font-display:swap').replace('../webfonts/','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/webfonts/')
save('public/asset/css/storefront-icons.css',css)
rel='resources/views/layouts/app.blade.php'
s=read(rel).replace('href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"','href="{{ app(\\App\\Services\\PublicAssetService::class)->url(\'asset/css/storefront-icons.css\') }}"')
# Discover CSS background hero images early on these public routes.
needle="    @stack('head-seo')"
s=s.replace(needle,"    @if(request()->routeIs('about-page','blogs-page','contact-page'))\n        <link rel=\"preload\" as=\"image\" href=\"{{ app(\\App\\Services\\ResponsiveMediaService::class)->url(asset('asset/media/hero.webp'), request()->header('Sec-CH-Viewport-Width',1280)<=880?640:1280) }}\" media=\"(min-width:881px)\" fetchpriority=\"high\">\n        <link rel=\"preload\" as=\"image\" href=\"{{ app(\\App\\Services\\ResponsiveMediaService::class)->url(asset('asset/media/hero.webp'),640) }}\" media=\"(max-width:880px)\" fetchpriority=\"high\">\n    @endif\n"+needle)
save(rel,s)
(out/'changed.json').write_text(json.dumps(changed),encoding='utf-8')
print('Prepared',len(changed),'files; icon CSS bytes',len(css.encode()))
