from pathlib import Path
import json,re
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'storefront-sync-files';m=json.loads((o/'manifest.json').read_text())
def put(p,s):
 item=next((x for x in m if x['destination']==p),None)
 if not item:item={'file':f'{len(m)+1:02d}_'+Path(p).name+'.txt','destination':p};m.append(item)
 (o/item['file']).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
def read(p):return (r/p).read_text(encoding='utf-8-sig')
s=read('resources/views/products/show.blade.php');a=s.index('$getProductImageUrl = function');b=s.index('\n};',a)+3;s=s[:a]+"$getProductImageUrl = fn($path)=>app(\\App\\Services\\PublicMediaService::class)->url($path);"+s[b:];s=s.replace('$productImages = $product->images ?: collect();',"$productImages = ($product->images ?: collect())->filter(fn($image)=>app(\\App\\Services\\PublicMediaService::class)->exists($image->image));");put('resources/views/products/show.blade.php',s)
s=read('resources/views/partials/header.blade.php');s=re.sub(r'<a href="#" class="icon-and-title text-decoration-none">\s*<i class="fa-brands fa-(facebook-f|instagram|x-twitter|linkedin|youtube) text-color-dark"></i>\s*</a>',lambda match: "@php($headerSocial=app(\\App\\Services\\StoreSettingsService::class)->settings()->"+{'facebook-f':'facebook','instagram':'instagram','x-twitter':'x','linkedin':'linkedin','youtube':'youtube'}[match[1]]+"_url)\n@if($headerSocial)<a href=\"{{ $headerSocial }}\" class=\"icon-and-title text-decoration-none\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"fa-brands fa-"+match[1]+" text-color-dark\"></i></a>@endif",s);put('resources/views/partials/header.blade.php',s)
# Review images use same missing-file fallback.
s=(o/'_staged/resources/views/blogs/partials/article-reviews.blade.php').read_text();a=s.index('        if ($imagePath) {');b=s.index("        return [",a);s=s[:a]+"        $imageUrl=app(\\App\\Services\\PublicMediaService::class)->url($imagePath ?: $product->featured_image);\n\n"+s[b:];put('resources/views/blogs/partials/article-reviews.blade.php',s)
# Project-list product cards on individual category pages, retaining detailed Shop cards elsewhere.
card='''<div class="list-item"><div class="project-item"><div class="porject-image"><div class="background-image" style="background-image:url('{{ app(\\App\\Services\\PublicMediaService::class)->url($product->featured_image) }}')"><div class="image-overlay"></div><div class="project-card-circle"></div></div></div><div class="project-info"><div class="project-top-info"><div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">{{ app(\\App\\Services\\StoreSettingsService::class)->money($product->final_price) }}</div><h2 class="title fs-24 text-color-white">{{ $product->title }}</h2></div><div class="project-link"><div class="link-wrapper"><a href="{{ route('products.show',$product->slug) }}" class="moving-circle" aria-label="{{ 'View '.$product->title }}">View</a></div></div></div></div></div>'''
put('resources/views/products/partials/category-product-card.blade.php',card)
s=read('resources/views/products/index.blade.php');pattern=r"@include\('products.partials.product-card',\s*\[\s*'product'\s*=>\s*\$product\s*\]\)";s,n=re.subn(pattern,"@include(isset($currentCategory) ? 'products.partials.category-product-card' : 'products.partials.product-card', ['product'=>$product])",s)
if n==0:
 s=s.replace("@include('products.partials.product-card'", "@include(isset($currentCategory) ? 'products.partials.category-product-card' : 'products.partials.product-card'")
s=s.replace('class="products-grid"','class="products-grid {{ isset($currentCategory) ? \'project-list az-collection-grid\' : \'\' }}"')
hub=(o/'_staged/resources/views/products/categories.blade.php').read_text();css=re.search(r'@push\(\'page-styles\'\).*',hub,re.S).group();s+='\n'+css;put('resources/views/products/index.blade.php',s)
(o/'manifest.json').write_text(json.dumps(m,indent=2));print(len(m),'replacement files')
