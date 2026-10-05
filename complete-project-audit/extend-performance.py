from pathlib import Path
import json,re
r=Path.cwd();o=r/'complete-project-audit/replacement-files';m=json.loads((o/'manifest.json').read_text())
def save(p,s):
 old=next((x for x in m if x['destination']==p),None);n=old['file'] if old else f'{len(m)+1:02d}_'+Path(p).name+'.txt'
 if not old:m.append({'file':n,'destination':p})
 (o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
for name,folder in [('ResponsiveMediaService','Services'),('OptimizePublicMedia','Console/Commands')]:save('app/'+folder+'/'+name+'.php',(r/'complete-project-audit'/f'{name}.php').read_text(encoding='utf-8-sig'))
p='resources/views/products/partials/product-card.blade.php';s=(r/p).read_text(encoding='utf-8-sig')
s=s.replace("{{ $mainImageUrl }}",r"{{ app(\App\Services\ResponsiveMediaService::class)->url($mainImageUrl,640) }}")
for f in ["$galleryImage['url']","$fifthGalleryImage['url']"]:
 s=s.replace('src="{{ '+f+' }}"',r'src="{{ app(\App\Services\ResponsiveMediaService::class)->url('+f+',180) }}" width="80" height="80"')
 s=s.replace('data-image="{{ '+f+' }}"',r'data-image="{{ app(\App\Services\ResponsiveMediaService::class)->url('+f+',640) }}"')
save(p,s)
p='resources/views/partials/post-card.blade.php';s=(r/p).read_text(encoding='utf-8-sig').replace("{{ $backgroundImage }}",r"{{ app(\App\Services\ResponsiveMediaService::class)->url($backgroundImage,640) }}").replace("$item->created_at->format('m.d.y')","($item->published_at ?: $item->scheduled_at ?: $item->created_at)->format('m.d.y')")
s=s.replace('$item->expert','$item->excerpt');save(p,s)
for x in list(m):
 p=x['destination']
 if p.startswith('resources/views/'):
  s=(o/'_staged'/p).read_text(encoding='utf-8');s=re.sub(r'(<h2 class="fs-48 text-color-dark">.*?)</h1>',r'\1</h2>',s,flags=re.S);save(p,s)
p='resources/views/blogs/partials/article-reviews.blade.php';s=(o/'_staged'/p).read_text(encoding='utf-8');s=s.replace("$reviewShowcaseData =", "$reviewShowcaseData =",1)
# Optimize image URLs in the public review data before its first card and JSON use.
pos=s.index('    $firstProduct =') if '    $firstProduct =' in s else -1
if pos>=0:s=s[:pos]+"    $reviewShowcaseData = collect($reviewShowcaseData)->map(fn($item)=>array_replace($item,['image'=>app(\\App\\Services\\ResponsiveMediaService::class)->url($item['image'],640)]))->all();\n"+s[pos:]
s=s.replace("'View ' + (product.title", "'Product ' + (product.title")
save(p,s)
(o/'manifest.json').write_text(json.dumps(m,indent=2),encoding='utf-8')
