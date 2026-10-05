from pathlib import Path
import re,json
r=Path(r'C:\xampp\htdocs\ArizonaOutfits'); o=r/'storefront-sync-files';o.mkdir(exist_ok=True); manifest=[]
def read(p):return (r/p).read_text(encoding='utf-8-sig')
def save(p,s):
 n=f'{len(manifest)+1:02d}_'+Path(p).name+'.txt';(o/n).write_text(s,encoding='utf-8'); t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8');manifest.append({'file':n,'destination':p})
def replace_div(s,needle,new):
 a=s.index(needle);depth=0
 for m in re.finditer(r'<div\b[^>]*>|</div\s*>',s[a:]):
  depth+= -1 if m.group().startswith('</') else 1
  if depth==0:return s[:a]+new+s[a+m.end():]
 raise Exception(needle)
# Reviews shared query, identical design.
rev=read('resources/views/blogs/partials/article-reviews.blade.php').replace('$products = $reviewProducts ?? collect();',"""$products = $reviewProducts ?? \\App\\Models\\Product::where('status','active')->whereHas('approvedReviews')->with(['images','approvedReviews'=>fn($q)=>$q->latest()->limit(20)])->latest('id')->take(10)->get();""")
save('resources/views/blogs/partials/article-reviews.blade.php',rev)
for page in ['home','privacy-policy','terms-and-conditions']:
 s=read('resources/views/'+page+'.blade.php');s=replace_div(s,'<div class="testiomonials">',"@include('blogs.partials.article-reviews')")
 if page!='home':s=s.replace("@include('partials.latest-posts')","@include('partials.latest-posts', ['latestPosts'=>\\App\\Models\\Post::published()->latestPosts()->take(6)->get()])")
 else:s+='''\n@push('page-styles')<style>.services-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px}.services-grid>.post-and-categories{margin:0!important;min-width:0}@media(max-width:900px){.services-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.services-grid{grid-template-columns:1fr}}</style>@endpush\n'''
 save('resources/views/'+page+'.blade.php',s)
s=read('app/Http/Controllers/HomeController.php').replace("'title' => 'Novahub'","'title' => 'Arizona Outfits'").replace("'Learn more about our company.'","'Shop the latest collections and discover popular products at Arizona Outfits.'").replace("->withAvg('approvedReviews', 'rating')","->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating')").replace("->withCount('approvedReviews')","->withCount('approvedReviews as approved_reviews_count')")
save('app/Http/Controllers/HomeController.php',s)
s=read('resources/views/about.blade.php');a=s.index('<div class="categories-and-title">');b=s.index('</div>',a)
# replace first categories panel, with live product categories
s=replace_div(s,'<div class="categories-and-title">','''<div class="categories-and-title"><div class="list-heading"><div class="subtitle fs-12 letter-space-4px text-uppercase text-color-dark">Product Categories</div></div><div class="categories-list"><div class="list-wrapper">@foreach(\\App\\Models\\ProductCategory::orderBy('title')->get() as $category)<div class="list-item"><a href="{{ route('products.category',$category->slug) }}" class="menu-item fs-18-400 text-color-body"><div class="list-item-text">{{ $category->title }}</div><i class="fa-solid fa-arrow-right-long list-item-icon"></i></a></div>@endforeach</div></div></div>''')
save('resources/views/about.blade.php',s)
# Filters retain all existing backend fields, inherit product animation.
s=read('resources/views/blogs/index.blade.php');s=s.replace('<div class="az-blog-filter-box">','<details class="az-blog-filter-box" id="article-filter-box">',1)
s=s.replace('<div class="az-blog-filter-heading">','<summary class="az-blog-filter-heading" aria-expanded="false">',1);s=s.replace('<h2 id="article-filter-title">Filter articles</h2>','<h2 id="article-filter-title">Filter articles <i class="fa-solid fa-chevron-down az-shop-filter-chevron"></i></h2>',1)
s=s.replace('Search by topic, category, author, or publication date.</p>\n                </div>','Search by topic, category, author, or publication date.</p>\n                </summary>',1)
s=s.replace('class="az-blog-filter-form" role=', 'id="article-filter-form" class="az-blog-filter-form" role=',1)
s=s.replace('''                @endif
            </div>
        </div>
    </section>''','''                @endif
            </details>
        </div>
    </section>''',1)
shop=read('resources/views/products/index.blade.php');anim=re.search(r"<script>\s*\(function \(\) \{\s*const box = document.getElementById\('shop-filter-box'\);.*?</script>",shop,re.S).group().replace('shop-filter-box','article-filter-box').replace('shop-filter-form','article-filter-form')
s+='''\n@push('page-scripts')'''+anim+'''@endpush
@push('page-styles')<style>
.az-blog-filter-box.has-smooth-filter{padding:24px 28px;background:#f3f6fc}.az-blog-filter-box>summary{cursor:pointer;list-style:none}.az-blog-filter-box>summary::-webkit-details-marker{display:none}.az-blog-filter-box>summary h2{display:inline-flex;align-items:center;gap:16px;border-radius:30px;background:#172033;color:white;padding:12px 20px;font-size:13px;letter-spacing:2px;text-transform:uppercase}.az-blog-filter-box>summary:focus-visible{outline:2px solid #172033;outline-offset:4px}.az-blog-filter-form{margin-top:28px;padding-top:28px;border-top:1px solid #d5d9e2}.az-blog-filter-box.is-animating{overflow:hidden}.az-shop-filter-chevron{transition:transform .36s ease}.az-blog-filter-box[open] .az-shop-filter-chevron{transform:rotate(180deg)}.az-blog-filter-box.is-closing .az-shop-filter-chevron{transform:rotate(0)}.az-blog-filter-field input,.az-blog-filter-field select{min-height:54px;border-radius:0;transition:border-color .2s,box-shadow .2s}.az-blog-filter-field label{letter-spacing:3px;text-transform:uppercase}.az-blog-filter-submit,.az-blog-filter-clear{border-radius:999px;transition:background .2s,box-shadow .2s}@media(prefers-reduced-motion:reduce){.az-blog-filter-box *{transition:none!important;animation:none!important}}</style>@endpush
'''
save('resources/views/blogs/index.blade.php',s)
# Product categories hub before catchall.
s=read('routes/web.php');mark=s.index("    '/product-category/{slug}'");start=s.rfind('Route::get(',0,mark)
s=s[:start]+"Route::get('/product-categories', function () { $categories=\\App\\Models\\ProductCategory::withCount(['products'=>fn($q)=>$q->where('status','active')])->orderBy('title')->paginate(15); return view('products.categories',compact('categories')); })->name('product-categories-page');\n\n"+s[start:]
save('routes/web.php',s)
hub='''@extends('layouts.app')
@section('title','Product Categories | Arizona Outfits')
@section('content')
<div class="page-wrapper"><div class="wrapper" style="padding-top:150px"><h1>Product Categories</h1><p>Explore our collections.</p></div><div class="recent-projects"><div class="wrapper"><div class="project-list az-collection-grid">
@foreach($categories as $category)
<div class="list-item"><div class="project-item"><div class="porject-image"><div class="background-image" style="background-image:url('{{ app(\\App\\Services\\PublicMediaService::class)->url($category->featured_image) }}')"><div class="image-overlay"></div><div class="project-card-circle"></div></div></div><div class="project-info"><div class="project-top-info"><div class="subtitle fs-12 letter-space-4px text-uppercase text-color-white">{{ $category->products_count }} products</div><h2 class="title fs-24 text-color-white">{{ $category->title }}</h2></div><div class="project-link"><div class="link-wrapper"><a href="{{ route('products.category',$category->slug) }}" class="moving-circle">View</a></div></div></div></div></div>
@endforeach</div>{{ $categories->links() }}</div></div></div>
@endsection
@push('page-styles')<style>.az-collection-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px}.az-collection-grid>.list-item{margin:0!important;min-width:0}.az-collection-grid .project-item{position:relative;min-height:420px;background:#172033}.az-collection-grid .porject-image,.az-collection-grid .background-image{position:absolute;inset:0;background-size:cover;background-position:center}.az-collection-grid .image-overlay{position:absolute;inset:0;background:linear-gradient(transparent,#090b19cc)}.az-collection-grid .project-info{position:absolute;bottom:24px;left:24px;right:24px;display:flex;align-items:end;justify-content:space-between;gap:12px}.az-collection-grid .title{overflow-wrap:anywhere}.az-collection-grid .moving-circle{width:70px;height:70px;border:1px solid white;color:white;border-radius:50%;display:grid;place-items:center;text-decoration:none;transition:background .25s}.az-collection-grid .moving-circle:hover{background:#ffffff30}@media(max-width:900px){.az-collection-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.az-collection-grid{grid-template-columns:1fr}}@media(prefers-reduced-motion:reduce){.az-collection-grid *{transition:none!important}}</style>@endpush
'''
save('resources/views/products/categories.blade.php',hub)
# Stable media URLs and missing local asset filtering. No-image asset is shipped as SVG.
media='''<?php
namespace App\\Services;
use Illuminate\\Support\\Facades\\Storage;
class PublicMediaService
{
    public function exists(?string $path): bool
    {
        if(!$path)return false;
        if(preg_match('~^https?://~i',$path))return true;
        $path=ltrim(str_replace('\\\\','/',$path),'/');
        if(str_starts_with($path,'storage/'))$path=substr($path,8);
        if(str_contains($path,'..'))return false;
        return Storage::disk('public')->exists($path)||is_file(public_path($path));
    }
    public function url(?string $path): string
    {
        if(!$this->exists($path))return asset('asset/images/product-placeholder.svg');
        if(preg_match('~^https?://~i',$path))return $path;
        $path=ltrim(str_replace('\\\\','/',$path),'/');
        if(str_starts_with($path,'storage/'))$path=substr($path,8);
        return is_file(public_path($path))?asset($path):asset('storage/'.$path);
    }
}
'''
save('app/Services/PublicMediaService.php',media)
save('public/asset/images/product-placeholder.svg','<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600"><rect width="600" height="600" fill="#f3f6fc"/><path d="M200 230h200v160H200zM230 350l55-60 40 40 30-30 35 50" fill="none" stroke="#94a3b8" stroke-width="8"/><text x="300" y="440" text-anchor="middle" fill="#64748b" font-family="Arial" font-size="20">Arizona Outfits</text></svg>')
s=read('resources/views/products/partials/product-card.blade.php');a=s.index('$getCardImageUrl = function');b=s.index('\n};',a)+3;s=s[:a]+"$getCardImageUrl = fn($path) => app(\\App\\Services\\PublicMediaService::class)->url($path);"+s[b:]
s=s.replace('if (!empty($productImage->image)) {','if (app(\\App\\Services\\PublicMediaService::class)->exists($productImage->image)) {').replace('if (empty($variant->image)) {','if (!app(\\App\\Services\\PublicMediaService::class)->exists($variant->image)) {')
save('resources/views/products/partials/product-card.blade.php',s)
s=read('resources/views/admin/products/partials/form.blade.php');s=s.replace('''                                data-existing-remove-checkbox''','''                                data-existing-remove-checkbox''')
# expose native checkbox independent of gallery JS, preserving its existing integration
s=re.sub(r'(data-existing-remove-checkbox[\s\S]*?@checked\([\s\S]*?\)\s*\))\s*hidden',r'\1',s,count=1)
s=s.replace('''                            <input
                                type="checkbox"
                                name="remove_gallery_images[]"''','''                            <label class="az-gallery-remove-label">Remove after saving
                            <input
                                type="checkbox"
                                name="remove_gallery_images[]"''',1)
needle='''                            <div
                                class="product-gallery-remove-state"''';s=s.replace(needle,'</label>\n'+needle,1)
s+='''\n<style>.product-existing-image .product-gallery-image-overlay{opacity:1!important;visibility:visible!important;pointer-events:auto!important}.az-gallery-remove-label{display:flex!important;align-items:center;gap:8px;padding:8px;background:#fff;color:#b91c1c;font-size:12px}.az-gallery-remove-label input{display:inline-block!important;position:static!important;width:18px!important;height:18px!important;opacity:1!important}.product-existing-image{min-height:160px}.product-existing-image img{min-height:100px}</style>\n'''
save('resources/views/admin/products/partials/form.blade.php',s)
# Editable footer social links and copyright / description via Store Settings.
fields=['facebook_url','instagram_url','linkedin_url','youtube_url','x_url','footer_copyright','footer_about']
s=read('app/Models/EcommerceSetting.php').replace("'store_name',","'store_name',\n"+''.join("        '"+f+"',\n" for f in fields),1);save('app/Models/EcommerceSetting.php',s)
s=read('app/Http/Controllers/Admin/EcommerceSettingController.php');rules=''.join("            '"+f+"' => ['nullable','url:http,https','max:1000'],\n" for f in fields[:5])+"            'footer_copyright'=>['nullable','string','max:255'],\n            'footer_about'=>['nullable','string','max:2000'],\n";s=s.replace("            'store_name' =>",rules+"            'store_name' =>",1);save('app/Http/Controllers/Admin/EcommerceSettingController.php',s)
s=read('resources/views/admin/settings/edit.blade.php');section='''<section class="admin-panel"><div class="settings-panel-header"><h3>Footer & Social Links</h3></div><div class="settings-panel-body settings-grid-two">'''
for f in fields:
 section+=f'''<label>{f.replace('_',' ').title()}<input class="form-control" name="{f}" value="{{{{ old('{f}',$settings->{f}) }}}}" maxlength="{2000 if f=='footer_about' else 255 if f=='footer_copyright' else 1000}" {'type="url"' if f.endswith('_url') else ''}></label>'''
section+='</div></section>'
s=s.replace('<div class="settings-main">','<div class="settings-main">\n'+section,1);save('resources/views/admin/settings/edit.blade.php',s)
migration='''<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;
return new class extends Migration {
 public function up():void { Schema::table('ecommerce_settings',function(Blueprint $t){FIELDS}); }
 public function down():void { Schema::table('ecommerce_settings',function(Blueprint $t){$t->dropColumn(COLS);}); }
};
'''.replace('FIELDS',''.join("$t->"+('text' if f=='footer_about' else 'string')+"('"+f+"'"+(',1000' if f.endswith('_url') else '')+")->nullable();" for f in fields)).replace('COLS',"['"+"','".join(fields)+"']")
save('database/migrations/2026_10_03_000001_add_storefront_footer_settings.php',migration)
s=read('resources/views/partials/footer.blade.php');s="@php($footerSettings=app(\\App\\Services\\StoreSettingsService::class)->settings())\n"+s
social='''<div class="follow-social"><div class="subtitle">Follow Us</div><div class="social-link-list">@foreach(['facebook'=>['Facebook','facebook-f'],'instagram'=>['Instagram','instagram'],'linkedin'=>['LinkedIn','linkedin'],'youtube'=>['YouTube','youtube'],'x'=>['X','x-twitter']] as $network=>$details)@php($socialUrl=$footerSettings->{$network.'_url'})@if($socialUrl && \\App\\Services\\SafeContentUrl::allowed($socialUrl))<a href="{{ $socialUrl }}" class="list-item" target="_blank" rel="noopener noreferrer"><div class="social-link-icon"><i class="fa-brands fa-{{ $details[1] }} text-color-dark"></i></div><div class="social-link-text">{{ $details[0] }}</div><i class="fa-solid fa-arrow-right-long social-right-arrow"></i></a>@endif@endforeach</div></div>'''
s=replace_div(s,'<div class="follow-social">',social)
s=re.sub(r'<p class="about-para">.*?</p>',"<p class=\"about-para\">{{ $footerSettings->footer_about ?: 'Discover the latest collections at Arizona Outfits.' }}</p>",s,flags=re.S)
s=replace_div(s,'<div class="footer-copyright">',"<div class=\"footer-copyright\"><div>{{ $footerSettings->footer_copyright ?: '© '.date('Y').' '.($footerSettings->store_name ?: 'Arizona Outfits').'. All rights reserved.' }}</div></div>")
save('resources/views/partials/footer.blade.php',s)
s=read('resources/views/partials/latest-posts.blade.php').replace('More from IDEOSTREAM','More from Arizona Outfits');save('resources/views/partials/latest-posts.blade.php',s)
s=read('resources/views/partials/blog-author-card-info.blade.php').replace('IDEOSTREAM',"{{ app(\\App\\Services\\StoreSettingsService::class)->settings()->store_name ?: 'Arizona Outfits' }}")
for network,icon in [('facebook','facebook-f'),('instagram','instagram'),('x','x-twitter'),('linkedin','linkedin'),('youtube','youtube')]:
 pattern=r'<a href="#" class="icon-and-title text-decoration-none all-border">\s*<i class="fa-brands fa-'+icon+r' text-color-dark"></i>\s*</a>'
 new="@php($profileSocial=app(\\App\\Services\\StoreSettingsService::class)->settings()->"+network+"_url)\n@if($profileSocial)<a href=\"{{ $profileSocial }}\" class=\"icon-and-title text-decoration-none all-border\" target=\"_blank\" rel=\"noopener noreferrer\"><i class=\"fa-brands fa-"+icon+" text-color-dark\"></i></a>@endif"
 s=re.sub(pattern,lambda m:new,s)
save('resources/views/partials/blog-author-card-info.blade.php',s)
(o/'manifest.json').write_text(json.dumps(manifest,indent=2));print('Prepared',len(manifest),'files')
