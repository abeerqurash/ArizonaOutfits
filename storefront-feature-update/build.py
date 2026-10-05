from pathlib import Path
import re,json
ROOT=Path(__file__).resolve().parents[1]
STAGE=ROOT/'storefront-feature-update'/'_staged'
changed=[]
def read(p): return (ROOT/p).read_text(encoding='utf-8-sig')
def write(p,s):
 q=STAGE/p;q.parent.mkdir(parents=True,exist_ok=True);q.write_text(s,encoding='utf-8');changed.append(p)
write('app/Services/CatalogOrderService.php', '''<?php
namespace App\\Services;
use App\\Models\\Product;
use Illuminate\\Database\\Eloquent\\Builder;
use Illuminate\\Support\\Facades\\DB;
class CatalogOrderService
{
    public function apply(Builder $query): Builder
    {
        $generation = DB::table('catalog_order_state')->where('id', 1)->value('generation');
        $ids = Product::query()->pluck('id')->all();
        usort($ids, fn ($a, $b) => strcmp(hash('sha256', $generation.':'.$a), hash('sha256', $generation.':'.$b)) ?: $a <=> $b);
        if (!$ids) return $query->orderBy('products.id');
        $cases = [];
        foreach ($ids as $position => $id) $cases[] = 'WHEN '.(int)$id.' THEN '.(int)$position;
        return $query->orderByRaw('CASE products.id '.implode(' ', $cases).' ELSE '.count($ids).' END')->orderBy('products.id');
    }
}
''')
write('database/migrations/2026_10_04_000001_create_catalog_order_state_table.php', '''<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;
use Illuminate\\Support\\Facades\\DB;
return new class extends Migration {
 public function up(): void {
  Schema::create('catalog_order_state', function(Blueprint $table) { $table->unsignedInteger('id')->primary(); $table->unsignedBigInteger('generation')->default(1); });
  DB::table('catalog_order_state')->insert(['id'=>1,'generation'=>1]);
 }
 public function down(): void { Schema::dropIfExists('catalog_order_state'); }
};
''')
s=read('app/Models/Product.php');s=s.replace('    protected $fillable = [', '''    protected static function booted(): void
    {
        static::created(function () {
            if (\\Illuminate\\Support\\Facades\\Schema::hasTable('catalog_order_state')) {
                \\Illuminate\\Support\\Facades\\DB::table('catalog_order_state')->where('id', 1)->increment('generation');
            }
        });
    }

    protected $fillable = [''',1);write('app/Models/Product.php',s)
s=read('app/Http/Controllers/HomeController.php')
start=s.index('        $latestProducts =');end=s.index('        /*',s.index('            ->get();',s.index('        $popularProducts =')))
s=s[:start]+'''        $catalogOrder = app(\\App\\Services\\CatalogOrderService::class);
        $query = Product::query()->with($productRelations)
            ->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating')
            ->withCount('approvedReviews as approved_reviews_count')
            ->where('status', 'active');
        $latestProducts = $catalogOrder->apply(clone $query)->take(6)->get();
        $popularProducts = $catalogOrder->apply((clone $query)->whereNotIn('products.id', $latestProducts->modelKeys()))->take(6)->get();

'''+s[end:];write('app/Http/Controllers/HomeController.php',s)
s=read('app/Http/Controllers/ShopController.php')
for name in ['popular','best_selling']:
 s=re.sub(r"'"+name+r"' => \$query\s*->orderByDesc\('[^']+'\)\s*->orderByDesc\('[^']+'\)\s*->orderByDesc\('id'\),", "'"+name+"' => app(\\\\App\\\\Services\\\\CatalogOrderService::class)->apply($query),",s)
s=s.replace("default => $query\n                ->latest('id'),","default => app(\\App\\Services\\CatalogOrderService::class)->apply($query),")
write('app/Http/Controllers/ShopController.php',s)
s=read('resources/views/home.blade.php');a=s.index('    <div class="about">');b=s.index('    <div class="what-i-do">',a);s=s[:a]+s[b:];s=s.replace('Popular Products','Trending Products').replace('Latest Products','Popular Products');write('resources/views/home.blade.php',s)
s=read('routes/web.php');s=s.replace(")->name('blog-show');", ")->name('content.resolve');")
i=s.index("Route::get(\n",s.index("->whereNumber('page')->name('sitemap.part');"))
s=s[:i]+"Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog-show');\n\n"+s[i:];write('routes/web.php',s)
s=read('app/Http/Controllers/CmsPageController.php');s=s.replace("return app(BlogController::class)->show($slug);", "app(BlogController::class)->show($slug);\n        return redirect()->route('blog-show', ['slug' => $slug], 301);");write('app/Http/Controllers/CmsPageController.php',s)
s=read('resources/views/products/index.blade.php');s=s.replace('<span>Arizona Outfits collection</span>', '''@if($currentCategory->featured_image)
                        <img class="az-category-banner-image" src="{{ app(\\App\\Services\\ResponsiveMediaService::class)->url(asset('storage/'.ltrim($currentCategory->featured_image, '/')), 1280) }}" alt="" width="1400" height="500" fetchpriority="high">
                        @endif''');s+='''
<style>
.az-category-intro{position:relative;isolation:isolate;max-width:1400px;width:100%;min-height:500px;box-sizing:border-box;display:flex;flex-direction:column;justify-content:center;padding:48px;background:#172033!important;color:white!important;overflow:hidden}
.az-category-banner-image{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:-2}
.az-category-intro:after{content:"";position:absolute;inset:0;background:rgba(0,0,0,.55);z-index:-1}
.az-category-intro h1,.az-category-intro p,.az-category-intro a{color:#fff!important}.az-category-intro .az-category-children a{background:#172033}
@media(max-width:600px){.az-category-intro{padding:24px}}
</style>
''';write('resources/views/products/index.blade.php',s)
s=read('resources/views/products/show.blade.php')
s=re.sub(r'\s*<p>\s*<strong>Status:</strong>.*?</p>','',s,flags=re.S)
s=re.sub(r'\s*@if \(\$productTags->isNotEmpty\(\)\).*?@endif\s*<p>\s*<strong>Views:</strong>.*?</p>\s*<p>\s*<strong>Favorites:</strong>.*?</p>','',s,flags=re.S)
write('resources/views/products/show.blade.php',s)
(STAGE.parent/'changed.json').write_text(json.dumps(changed,indent=2),encoding='utf-8')
print('Prepared',len(changed),'files')
