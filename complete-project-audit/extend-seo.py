from pathlib import Path
import json,re
r=Path.cwd();o=r/'complete-project-audit/replacement-files';m=json.loads((o/'manifest.json').read_text())
def save(p,s):
 old=next((x for x in m if x['destination']==p),None);n=old['file'] if old else f'{len(m)+1:02d}_'+Path(p).name+'.txt'
 if not old:m.append({'file':n,'destination':p})
 (o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
save('app/Services/PublicSeoService.php',(r/'complete-project-audit/PublicSeoService.php').read_text(encoding='utf-8-sig'))
save('app/Http/Controllers/SitemapController.php',(r/'complete-project-audit/SitemapController.php').read_text(encoding='utf-8-sig'))
p='routes/web.php';s=(o/'_staged'/p).read_text(encoding='utf-8');pos=s.rfind('Route::get(');assert "'/{slug}'" in s[pos:]
s=s[:pos]+"""Route::get('/sitemap.xml', [\\App\\Http\\Controllers\\SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap/{type}/{page}.xml', [\\App\\Http\\Controllers\\SitemapController::class, 'part'])
    ->whereIn('type', ['static','products','posts','pages','product-categories','blog-categories'])
    ->whereNumber('page')->name('sitemap.part');

"""+s[pos:];save(p,s)
save('public/robots.txt','''User-agent: *
Disallow: /admin
Disallow: /account
Disallow: /dashboard
Disallow: /cart
Disallow: /checkout
Disallow: /favorites
Disallow: /login
Disallow: /register
Disallow: /track-order
# After deployment, add: Sitemap: https://your-real-domain.com/sitemap.xml
''')
p='resources/views/layouts/app.blade.php';s=(r/p).read_text(encoding='utf-8-sig');start=s.index('    @php');end=s.index("    @stack('head-seo')",start)
s=s[:start]+'''    @php($publicSeo = app(\\App\\Services\\PublicSeoService::class)->values(get_defined_vars(), trim($__env->yieldContent('title')), trim($__env->yieldContent('meta_description'))))
    <title>{{ $publicSeo['title'] }}</title>
    <meta name="description" content="{{ $publicSeo['description'] }}">
    <meta name="robots" content="{{ $publicSeo['robots'] }}">
    <link rel="canonical" href="{{ $publicSeo['canonical'] }}">
    <meta property="og:type" content="{{ $publicSeo['type'] }}">
    <meta property="og:site_name" content="{{ $publicSeo['store'] }}">
    <meta property="og:title" content="{{ $publicSeo['ogTitle'] }}">
    <meta property="og:description" content="{{ $publicSeo['ogDescription'] }}">
    <meta property="og:url" content="{{ $publicSeo['canonical'] }}">
    <meta name="twitter:card" content="{{ $publicSeo['image'] ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $publicSeo['ogTitle'] }}">
    <meta name="twitter:description" content="{{ $publicSeo['ogDescription'] }}">
    @if($publicSeo['image'])
        <meta property="og:image" content="{{ $publicSeo['image'] }}">
        <meta name="twitter:image" content="{{ $publicSeo['image'] }}">
    @endif
    @if($publicSeo['graph'])
        <script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@graph'=>$publicSeo['graph']], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    @if(request()->routeIs('home-page'))
        <link rel="preload" as="image" href="{{ asset('asset/media/hero.webp') }}" fetchpriority="high">
    @endif

'''+s[end:]
s=s.replace("    @stack('page-styles')",'''    <link rel="stylesheet" href="{{ asset('asset/css/card-carousel.css') }}">
    <style>:root{--body-display:#606779}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}</style>
    @stack('page-styles')''')
s=s.replace("    @stack('page-scripts')",'''    <script src="{{ asset('asset/js/card-carousel.js') }}" defer></script>
    @stack('page-scripts')''')
save(p,s)
# Guard stale hard-coded testimonial code and remove the unreliable third-party IP lookup.
for p in ['public/asset/js/main.js','public/asset/js/contact.js','public/asset/js/blogs.js','public/asset/js/policy-condtions.js']:
 s=(r/p).read_text(encoding='utf-8-sig')
 s=re.sub(r'geoIpLookup:\s*function\s*\(callback\)\s*\{\s*fetch\("https://ipapi.co/json"\).*?\n\s*\},?', '',s,flags=re.S)
 s=s.replace('initialCountry: "auto"','initialCountry: "pk"')
 if p.endswith('main.js'):s=s.replace('    /* ---------- SMOOTH TRANSITIONS ---------- */','    if (!bg || texts.some(t => !t) || !next || !prev) return;\n\n    /* ---------- SMOOTH TRANSITIONS ---------- */')
 save(p,s)
p='resources/views/blogs/partials/article-reviews.blade.php';s=(r/p).read_text(encoding='utf-8-sig');s=s.replace('class="arizona-review-stars" aria-label=', 'class="arizona-review-stars" role="img" aria-label=')
s=s.replace('aria-label="View {{ $firstProduct[\'title\'] }}"','aria-label="Product {{ $firstProduct[\'title\'] }}"')
s=s.replace("'View ' + product.title","'Product ' + product.title")
save(p,s)
p='resources/views/partials/footer.blade.php';s=(r/p).read_text(encoding='utf-8-sig').replace('<h1 class="text-color-white">','<h2 class="text-color-white">').replace('</h1>','</h2>');save(p,s)
# Section headings remain h2 so card titles are a valid h3 level.
for x in m:
 if x['destination']=='resources/views/home.blade.php' or x['destination'].startswith(('resources/views/pages/custom/','resources/views/blogs/posts/')):
  p=x['destination'];s=(o/'_staged'/p).read_text(encoding='utf-8');s=s.replace('<h1 class="fs-48 text-color-dark">', '<h2 class="fs-48 text-color-dark">').replace("What's New</h1>","What's New</h2>").replace('Related Posts</h1>','Related Posts</h2>');save(p,s)
(o/'manifest.json').write_text(json.dumps(m,indent=2),encoding='utf-8')
