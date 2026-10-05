from pathlib import Path
import json,re
r=Path.cwd();o=r/'complete-project-audit/replacement-files';m=json.loads((o/'manifest.json').read_text())
def save(p,s):
 old=next((x for x in m if x['destination']==p),None);n=old['file'] if old else f'{len(m)+1:02d}_'+Path(p).name+'.txt'
 if not old:m.append({'file':n,'destination':p})
 (o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
p='public/asset/css/card-carousel.css';s=(o/'_staged'/p).read_text(encoding='utf-8');s=s.replace(' .az-responsive-cards.az-responsive-cards {display:flex',' .az-responsive-cards.az-responsive-cards {height:auto!important;display:flex').replace('scroll-snap-align:start;transform:none;opacity:1;', 'scroll-snap-align:start;transform:none;opacity:1;margin-top:0!important;margin-bottom:0!important;')
s=s.replace('.blog-posts .post-cards-parent:has(.az-responsive-cards) {min-width:0;width:100%;}', '.blog-posts .post-cards-parent:has(.az-responsive-cards) {min-width:0;width:100%;grid-area:auto!important;}\n .blog-posts .post-and-categories:has(.az-responsive-cards) > .post-categories{grid-area:auto!important;position:relative;}')
s+='\n@media(min-width:881px){.blog-show .parent-wrapper[data-card-carousel]{grid-template-columns:repeat(3,minmax(0,1fr));}}\n';save(p,s)
for p in ['public/asset/js/blogs.js','public/asset/js/policy-condtions.js']:
 # Read unminified current source and reapply reviewed guards and phone changes.
 s=(r/p).read_text(encoding='utf-8-sig');s=re.sub(r'geoIpLookup:\s*function\s*\(callback\)\s*\{\s*fetch\("https://ipapi.co/json"\).*?\n\s*\},?', '',s,flags=re.S).replace('initialCountry: "auto"','initialCountry: "pk"')
 s=s.replace('    bg.style.transition =','    if (!bg || texts.some(t => !t) || !next || !prev) return;\n\n    bg.style.transition =',1)
 s=s.replace('    newsletterBg.style.transform =','    if (!newsletterBg || window.matchMedia("(prefers-reduced-motion:reduce)").matches) return;\n    newsletterBg.style.transform =',1);save(p,s)
p='app/Services/StoreSettingsService.php';s=(r/p).read_text(encoding='utf-8-sig');s=s.replace("        return (Schema::", "        $request=request();$key='arizona.store_settings';\n        if($request->attributes->has($key))return $request->attributes->get($key);\n        $settings=(Schema::",1);s=s.replace("        ]);\n    }", "        ]);\n        $request->attributes->set($key,$settings);return $settings;\n    }",1);save(p,s)
p='app/Providers/AppServiceProvider.php';s=(o/'_staged'/p).read_text(encoding='utf-8');mark='        Password::defaults';s=s.replace(mark,"        \\App\\Models\\EcommerceSetting::saved(fn() => request()->attributes->remove('arizona.store_settings'));\n\n"+mark,1);save(p,s)
p='resources/views/layouts/app.blade.php';s=(o/'_staged'/p).read_text(encoding='utf-8');s=s.replace("{{ asset('asset/media/hero.webp') }}\" fetchpriority=", "{{ app(\\App\\Services\\ResponsiveMediaService::class)->url(asset('asset/media/hero.webp'),1280) }}\" fetchpriority=")
s=s.replace("    @stack('page-styles')", "    @if(request()->routeIs('home-page'))\n    <style>.home-hero .background-hero{background-image:url('{{ app(\\App\\Services\\ResponsiveMediaService::class)->url(asset('asset/media/hero.webp'),1280) }}')}</style>\n    @endif\n    @stack('page-styles')");save(p,s)
(o/'manifest.json').write_text(json.dumps(m,indent=2),encoding='utf-8')
