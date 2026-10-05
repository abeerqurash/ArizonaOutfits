from pathlib import Path
import json,re
r=Path.cwd();o=r/'complete-project-audit/replacement-files';m=json.loads((o/'manifest.json').read_text())
def save(p,s):
 old=next((x for x in m if x['destination']==p),None);n=old['file'] if old else f'{len(m)+1:02d}_'+Path(p).name+'.txt'
 if not old:m.append({'file':n,'destination':p})
 (o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
p='app/Services/ResponsiveMediaService.php';s=(o/'_staged'/p).read_text(encoding='utf-8');s=s.replace("  if(!in_array($width,self::WIDTHS,true))return $url;$source=", "  if(!in_array($width,self::WIDTHS,true))return $url;\n  if(preg_match('#/storage/media-cache/([a-f0-9]{64})-(180|640|1280)\\.webp$#',parse_url($url,PHP_URL_PATH)??'',$match)){\n   $candidate='media-cache/'.$match[1].'-'.$width.'.webp';return Storage::disk('public')->exists($candidate)?asset('storage/'.$candidate):$url;\n  }\n  $source=");save(p,s)
p='app/Services/PublicMediaService.php';s=(o/'_staged'/p).read_text(encoding='utf-8').replace("if(preg_match('~^https?://~i',$path))return $path;", "if(preg_match('~^https?://~i',$path))return app(ResponsiveMediaService::class)->url($path,1280);")
s=s.replace("return is_file(public_path($path))?asset($path):asset('storage/'.$path);", "return app(ResponsiveMediaService::class)->url(is_file(public_path($path))?asset($path):asset('storage/'.$path),1280);");save(p,s)
p='app/Models/Post.php';s=(r/p).read_text(encoding='utf-8-sig');s=s.replace('            return $path;',r'            return app(\App\Services\ResponsiveMediaService::class)->url($path,1280);')
s=s.replace('return asset($normalized);',r'return app(\App\Services\ResponsiveMediaService::class)->url(asset($normalized),1280);').replace("return Storage::disk('public')->url($normalized);",r"return app(\App\Services\ResponsiveMediaService::class)->url(asset('storage/'.$normalized),1280);").replace("return asset('storage/' . $normalized);",r"return app(\App\Services\PublicMediaService::class)->url($normalized);");save(p,s)
p='resources/views/partials/blog-author-card-info.blade.php';s=(r/p).read_text(encoding='utf-8-sig')
for icon,label in [('facebook-f','Facebook'),('instagram','Instagram'),('x-twitter','X'),('linkedin','LinkedIn'),('youtube','YouTube')]:
 s=s.replace('rel="noopener noreferrer"><i class="fa-brands fa-'+icon,'rel="noopener noreferrer" aria-label="'+label+'"><i class="fa-brands fa-'+icon)
save(p,s)
p='resources/views/blogs/partials/article-author-latest.blade.php';s=(r/p).read_text(encoding='utf-8-sig').replace('color: #777;','color: #606779;').replace('Read More\n',"Read More <span class=\"sr-only\">about {{ $latestPost->title }}</span>\n")
s=s.replace("{{ $latestPost->feature_image_url }}",r"{{ app(\App\Services\ResponsiveMediaService::class)->url($latestPost->feature_image_url,180) }}");save(p,s)
p='resources/views/home.blade.php';s=(o/'_staged'/p).read_text(encoding='utf-8').replace('href="#services" class="moving-circle"','href="#services" class="moving-circle" aria-label="Explore latest products"');save(p,s)
p='resources/views/products/partials/product-card.blade.php';s=(o/'_staged'/p).read_text(encoding='utf-8')
for a,b in [('Quick view {{ $product->title }}','View now: {{ $product->title }}'),('Select options for {{ $product->title }}','Add to cart: select options for {{ $product->title }}'),('Login to add {{ $product->title }} to favorites','Login to add favorite: {{ $product->title }}'),('Select options and buy {{ $product->title }}','Buy now: select options for {{ $product->title }}'),('Buy {{ $product->title }} now','Buy now: {{ $product->title }}')]:s=s.replace('aria-label="'+a+'"','aria-label="'+b+'"')
save(p,s)
(o/'manifest.json').write_text(json.dumps(m,indent=2),encoding='utf-8')
