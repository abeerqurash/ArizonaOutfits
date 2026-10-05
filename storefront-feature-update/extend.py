exec((Path(__file__).parent/'build.py').read_text()) if False else None
from pathlib import Path
import re,json
ROOT=Path(__file__).resolve().parents[1];STAGE=ROOT/'storefront-feature-update'/'_staged'
changed=json.loads((STAGE.parent/'changed.json').read_text())
def read(p): return ((STAGE/p) if (STAGE/p).exists() else (ROOT/p)).read_text(encoding='utf-8-sig')
def write(p,s):
 q=STAGE/p;q.parent.mkdir(parents=True,exist_ok=True);q.write_text(s,encoding='utf-8');
 if p not in changed: changed.append(p)
def divend(s,start):
 depth=0
 for m in re.finditer(r'</?div\b[^>]*>',s[start:],re.I):
  depth+= -1 if m.group().startswith('</') else 1
  if depth==0:return start+m.end()
 raise ValueError('Unclosed div')
s=read('app/Services/NavigationMenuService.php');i=s.index('    public function forget')
s=s[:i]+'''    public function categoryFor($item): ?\\App\\Models\\ProductCategory
    {
        $url = $item->resolved_url;
        $host = parse_url($url, PHP_URL_HOST);
        if ($host && strtolower($host) !== strtolower((string) parse_url(url('/'), PHP_URL_HOST))) return null;
        $path = trim(rawurldecode((string) parse_url($url, PHP_URL_PATH)), '/');
        if (!preg_match('~^(?:product-category/)?([^/]+)$~', $path, $match)) return null;
        static $categories = null;
        $categories ??= \\App\\Models\\ProductCategory::with(['children' => fn($query) => $query->orderBy('title')])->get()->keyBy('slug');
        return $categories->get($match[1]);
    }
'''+s[i:];write('app/Services/NavigationMenuService.php',s)
s=read('resources/views/partials/header-menu-links.blade.php');start=s.index('@forelse($configuredMenuItems');end=s.index('@empty',start)
block=s[start:end];block=block.replace('@forelse($configuredMenuItems as $menuItem)','''@forelse($configuredMenuItems as $menuItem)
@php($headerCategory = app(\\App\\Services\\NavigationMenuService::class)->categoryFor($menuItem))
<div class="az-header-category-item">''');block+='''
@if($headerCategory && $headerCategory->children->isNotEmpty())
<div class="az-header-category-panel mega-menu-wrapper"><div class="categories-wrapper"><div class="categories-description"><div class="title">{{ $headerCategory->title }}</div></div><div class="category-list"><div class="list-collection az-mega-category-grid">
@foreach($headerCategory->children as $child)
<div class="list-item"><a href="{{ route('products.category',$child->slug) }}" class="list"><div class="list-description"><div class="list-item-text">{{ $child->title }}</div></div><i class="fa-solid fa-arrow-right-long right-arrow-icon" aria-hidden="true"></i></a></div>
@endforeach
</div></div></div></div>
@endif
</div>
''';s=s[:start]+block+s[end:];s+='''
<style>.az-header-category-panel{display:none;position:absolute;top:100%;left:0;width:100%;max-height:70vh;overflow:auto;background:#fff;box-shadow:0 14px 25px #0002;z-index:1000;padding:30px;box-sizing:border-box}.az-header-category-item:hover>.az-header-category-panel,.az-header-category-item:focus-within>.az-header-category-panel{display:block}.az-header-category-item{display:flex}.az-header-category-panel .list{color:#172033}.az-header-category-panel .categories-wrapper{width:100%}@media(max-width:880px){.az-header-category-panel{display:none!important}}</style>
''';write('resources/views/partials/header-menu-links.blade.php',s)
s=read('resources/views/partials/header.blade.php');start=s.index('<a href="{{ $mobileMenuItem->resolved_url }}"');end=s.index('\n@empty',start)
old=s[start:end];s=s[:start]+'''<div class="az-mobile-menu-group">
'''+old+'''
@php($mobileCategory = app(\\App\\Services\\NavigationMenuService::class)->categoryFor($mobileMenuItem))
@if($mobileCategory && $mobileCategory->children->isNotEmpty())
<details><summary aria-label="Show {{ $mobileCategory->title }} subcategories">Subcategories</summary><div>
@foreach($mobileCategory->children as $child)<a href="{{ route('products.category',$child->slug) }}">{{ $child->title }}</a>@endforeach
</div></details>
@endif
</div>'''+s[end:];s+='''<style>.az-mobile-menu-group{min-width:0}.az-mobile-menu-group summary{cursor:pointer;padding:10px 4px;font-size:11px;text-align:center;color:#172033}.az-mobile-menu-group details a{margin:4px 0;border-radius:8px}.az-mobile-menu-group details[open]{background:#f4f6fa}</style>''';write('resources/views/partials/header.blade.php',s)
s=read('app/Http/Controllers/ReviewController.php');s=re.sub(r"\s*'title' => \[.*?\],",'',s,flags=re.S);s=re.sub(r"\s*'title' => !empty\(\$validated\['title'\]\).*?: null,",'',s,flags=re.S);write('app/Http/Controllers/ReviewController.php',s)
s=read('app/Models/Review.php').replace("        'title',\n",'');write('app/Models/Review.php',s)
s=read('app/Http/Controllers/Admin/ReviewController.php');s=re.sub(r"\s*->orWhere\('title',\s*'like',\s*[^\n]*\)",'',s);write('app/Http/Controllers/Admin/ReviewController.php',s)
s=read('resources/views/products/show.blade.php');s=re.sub(r'\s*@if\s*\(\$review->title\).*?@endif','',s,flags=re.S)
m=re.search(r'<div class="review-form-group">\s*<label\s*for="review-title"',s)
if m:s=s[:m.start()]+s[divend(s,m.start()):]
s=s.replace('<th>Stock</th>','');s=re.sub(r'<td>\s*@if\s*\(\s*app\(\\App\\Services\\StoreSettingsService::class\)->available\(\(int\)\$variant->stock\).*?</td>','',s,flags=re.S);write('resources/views/products/show.blade.php',s)
s=read('resources/views/admin/reviews/index.blade.php');s=s.replace("{{ $review->title ?: 'Product Review' }}",'Product Review');s=re.sub(r'\s*data-review-title="[^"]*"','',s);s=s.replace('dataset.reviewTitle','dataset.reviewName');write('resources/views/admin/reviews/index.blade.php',s)
s=read('resources/views/blogs/partials/article-reviews.blade.php');s=s.replace("                    'title' => $review->title,\n",'');s=re.sub(r"\s*@if\(!empty\(\$review\['title'\]\)\).*?@endif",'',s,flags=re.S);s=re.sub(r"const title = review.title\s*\?[^;]+;","const title = '';",s);write('resources/views/blogs/partials/article-reviews.blade.php',s)
write('database/migrations/2026_10_04_000002_remove_review_title.php','''<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;
return new class extends Migration {
 public function up(): void { if(Schema::hasColumn('reviews','title')) Schema::table('reviews',fn(Blueprint $table)=>$table->dropColumn('title')); }
 public function down(): void { if(!Schema::hasColumn('reviews','title')) Schema::table('reviews',fn(Blueprint $table)=>$table->string('title')->nullable()); }
};
''')
write('resources/views/blogs/partials/related-article-cards.blade.php','''@if($relatedPosts->isNotEmpty())
<section class="blog-posts service-content"><div class="wrapper"><h2 class="fs-48 text-color-dark">Related Articles</h2><div class="parent-wrapper az-responsive-cards" data-card-carousel aria-label="Related articles">@foreach($relatedPosts->take(6) as $relatedPost) @include('partials.post-card',['post'=>$relatedPost]) @endforeach</div></div></section>
@endif
''')
for path in (ROOT/'resources/views/blogs/posts').glob('*.blade.php'):
 p=path.relative_to(ROOT).as_posix();s=read(p)
 for cls in ['news-letter','blog-posts service-content']:
  marker='<div class="'+cls+'">'
  if marker in s:
   a=s.index(marker);s=s[:a]+s[divend(s,a):]
 s=re.sub(r"\s*@include\('blogs.partials.article-reviews',\s*\[.*?\]\)",'',s,flags=re.S)
 s=s.replace('@endsection',"@include('blogs.partials.related-article-cards')\n@include('blogs.partials.article-reviews', ['reviewProducts' => $reviewProducts])\n@endsection",1)
 write(p,s)
s=read('resources/views/blogs/partials/article-author-latest.blade.php');s=s.replace('$author = $post->author;',"$author = isset($post) ? $post->author : ($profile ?? null);");s=s.replace('                        AUTHOR',"                        @if($showAuthorLabel ?? true) AUTHOR @endif");s=s.replace('                        ArizonaOutfits',"                        {{ $author?->name ?: 'Arizona Outfits' }}");write('resources/views/blogs/partials/article-author-latest.blade.php',s)
s=read('resources/views/contact.blade.php');a=s.index('<div class="form-description-box">');b=divend(s,a)
s=s[:b]+'''
@php($contactPosts = \\App\\Models\\Post::with('author')->published()->latestPosts()->take(5)->get())
@include('blogs.partials.article-author-latest', ['profile'=>$contactPosts->first()?->author, 'latestPosts'=>$contactPosts, 'showAuthorLabel'=>false])
'''+s[b:];s+='''<style>.page-description-wrapper{display:grid!important;grid-template-columns:minmax(0,2fr) minmax(260px,1fr);gap:40px}.page-description-wrapper .form-description-box,.page-description-wrapper .post-categories{width:100%!important;min-width:0}@media(max-width:880px){.page-description-wrapper{grid-template-columns:1fr}}</style>''';write('resources/views/contact.blade.php',s)
(STAGE.parent/'changed.json').write_text(json.dumps(changed,indent=2));print('Prepared',len(changed),'files')
