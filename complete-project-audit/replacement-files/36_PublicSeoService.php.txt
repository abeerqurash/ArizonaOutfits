<?php
namespace App\Services;
use App\Models\{Page,Post,Product};
class PublicSeoService
{
 public function values(array $data=[],string $sectionTitle='',string $sectionDescription=''): array
 {
  $request=request();$settings=app(StoreSettingsService::class)->settings();$store=$settings->store_name ?: 'Arizona Outfits';
  $post=$data['post']??null;$page=$data['page']??null;$product=$data['product']??null;
  $cms=$page instanceof Page;
  $article=!$cms&&$request->routeIs('blog-show')&&$post instanceof Post;
  $singleProduct=$request->routeIs('products.show')&&$product instanceof Product;
  $category=$data['currentCategory']??$data['category']??null;
  $fallbacks=['home-page'=>$store.' — Latest Collections & Style Stories','about-page'=>'About '.$store,'contact-page'=>'Contact '.$store,'blogs-page'=>'Style Stories & Articles | '.$store,'products.index'=>'Shop All Products | '.$store,'product-categories-page'=>'Product Categories | '.$store,'categories-page'=>'Blog Categories | '.$store];
  $title=$article?($post->meta_title ?: $post->title):($singleProduct?($product->meta_title ?: $product->title):($cms?($page->meta_title ?: $page->title):($sectionTitle ?: ($data['title']??($category?->title ? $category->title.' | '.$store : ($fallbacks[$request->route()?->getName()]??$store))))));
  $description=$article?($post->meta_description ?: $post->excerpt):($singleProduct?($product->meta_description ?: $product->short_description):($cms?($page->meta_description ?: $page->excerpt):($sectionDescription ?: ($data['meta_description']??''))));
  $description=mb_substr(trim(preg_replace('/\s+/u',' ',strip_tags((string)$description))),0,320);
  if($description==='')$description=$category?->title?'Explore '.$category->title.' at '.$store.'.':'Explore the latest collections, products and style stories at '.$store.'.';
  $private=$request->is('admin','admin/*','account','account/*','dashboard','dashboard/*','cart','cart/*','favorites','favorites/*','checkout','checkout/*','login','register','forgot-password','reset-password/*','verify-email','verify-email/*','confirm-password','track-order','thank-you');
  $collection=$request->routeIs('products.index','products.category','blogs-page','category-show','categories-page','product-categories-page');
  $filterKeys=array_diff(array_keys($request->query()),['page','utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','gbraid','wbraid']);
  $filtered=$collection&&count($filterKeys)>0;
  $robots=$private||$filtered?'noindex, follow':($article?(($post->robots_index?'index':'noindex').', '.($post->robots_follow?'follow':'nofollow')):($data['robots']??'index, follow'));
  $canonical=$article&&$post->canonical_url?$post->canonical_url:$request->url();
  if($collection&&!$filtered&&filter_var($request->query('page'),FILTER_VALIDATE_INT)>1)$canonical.='?page='.(int)$request->query('page');
  $image=$article?($post->og_image_url ?: $post->feature_image_url):($singleProduct?app(PublicMediaService::class)->productImage($product):asset('asset/media/hero.webp'));
  $graph=[];$organizationId=url('/').'#organization';
  if(!$private){
   $organization=['@type'=>'Organization','@id'=>$organizationId,'name'=>$store,'url'=>url('/')];
   $social=collect(['facebook_url','instagram_url','x_url','linkedin_url','youtube_url'])->map(fn($field)=>$settings->$field)->filter(fn($url)=>is_string($url)&&preg_match('~^https?://~i',$url))->values()->all();
   if($social)$organization['sameAs']=$social;
   $graph[]=$organization;
   if($request->routeIs('home-page'))$graph[]=['@type'=>'WebSite','@id'=>url('/').'#website','url'=>url('/'),'name'=>$store,'publisher'=>['@id'=>$organizationId]];
   if($article){
    $node=['@type'=>'BlogPosting','headline'=>$post->title,'description'=>$description,'url'=>$canonical,'mainEntityOfPage'=>$canonical,'datePublished'=>($post->published_at ?: $post->scheduled_at ?: $post->created_at)?->toIso8601String(),'dateModified'=>$post->updated_at?->toIso8601String(),'publisher'=>['@id'=>$organizationId]];
    if($image)$node['image']=$image;if($post->author)$node['author']=['@type'=>'Person','name'=>$post->author->name];$graph[]=$node;
   }
   if($singleProduct){
    $product->loadMissing('variants');$price=(float)$product->final_price;
    $inStock=!app(StoreSettingsService::class)->managed() || ($product->variants->isNotEmpty() ? $product->variants->sum('stock')>0 : $product->stock>0);
    $offer=['@type'=>'Offer','url'=>$canonical,'price'=>number_format($price,2,'.',''),'priceCurrency'=>app(StoreSettingsService::class)->currency(),'availability'=>'https://schema.org/'.($inStock?'InStock':'OutOfStock')];
    // Parent prices can differ from variant prices; report the real variant range.
    if($product->variants->isNotEmpty()){
     $prices=$product->variants->map(function($v)use($product){$regular=$v->regular_price??$product->regular_price;$sale=$v->sale_price??$product->sale_price;return $sale!==null&&(float)$sale<(float)$regular?(float)$sale:(float)$regular;});
     $offer=['@type'=>'AggregateOffer','url'=>$canonical,'lowPrice'=>number_format($prices->min(),2,'.',''),'highPrice'=>number_format($prices->max(),2,'.',''),'offerCount'=>$prices->count(),'priceCurrency'=>app(StoreSettingsService::class)->currency()];
    }
    $node=['@type'=>'Product','name'=>$product->title,'description'=>$description,'image'=>$image,'url'=>$canonical,'offers'=>$offer];if($product->sku)$node['sku']=$product->sku;
    $count=$product->approvedReviews()->count();if($count)$node['aggregateRating']=['@type'=>'AggregateRating','ratingValue'=>round((float)$product->approvedReviews()->avg('rating'),2),'reviewCount'=>$count];$graph[]=$node;
   }
  }
  return ['title'=>strip_tags((string)$title),'description'=>$description,'robots'=>$robots,'canonical'=>$canonical,'type'=>$article?'article':'website','ogTitle'=>$article?($post->og_title ?: $title):$title,'ogDescription'=>$article?($post->og_description ?: $description):$description,'image'=>$image,'store'=>$store,'graph'=>$graph];
 }
}
