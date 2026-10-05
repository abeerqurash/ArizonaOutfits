<?php
namespace App\Http\Controllers;
use App\Models\{Product,Post,Page,ProductCategory,Category};
use Illuminate\Http\Response;
class SitemapController extends Controller
{
 private const CHUNK=1000;
 private function query(string $type){return match($type){'products'=>Product::where('status','active'),'posts'=>Post::published()->where('robots_index',true),'pages'=>Page::published(),'product-categories'=>ProductCategory::query(),'blog-categories'=>Category::whereHas('posts',fn($q)=>$q->published()),default=>abort(404)};}
 private function xml(string $value):string{return htmlspecialchars($value,ENT_XML1|ENT_QUOTES,'UTF-8');}
 public function index():Response
 {
  $xml='<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
  $xml.='<sitemap><loc>'.$this->xml(route('sitemap.part',['type'=>'static','page'=>1])).'</loc></sitemap>';
  foreach(['products','posts','pages','product-categories','blog-categories'] as $type){$pages=(int)ceil($this->query($type)->count()/self::CHUNK);for($p=1;$p<=$pages;$p++)$xml.='<sitemap><loc>'.$this->xml(route('sitemap.part',['type'=>$type,'page'=>$p])).'</loc></sitemap>';}
  return response($xml.'</sitemapindex>',200,['Content-Type'=>'application/xml; charset=UTF-8','Cache-Control'=>'public, max-age=300']);
 }
 public function part(string $type,int $page):Response
 {
  abort_if($page<1,404);$items=[];
  if($type==='static'){
   abort_unless($page===1,404);foreach(['home-page','about-page','contact-page','products.index','blogs-page','product-categories-page','categories-page'] as $name)$items[]=['url'=>route($name),'date'=>null];
  }else{
   $query=$this->query($type);$total=$query->count();abort_if(($page-1)*self::CHUNK>=$total,404);
   foreach($query->orderBy('id')->skip(($page-1)*self::CHUNK)->take(self::CHUNK)->get() as $item){
    $url=match($type){'products'=>route('products.show',$item->slug),'posts'=>route('blog-show',$item->slug),'pages'=>$item->public_url,'product-categories'=>route('products.category',$item->slug),'blog-categories'=>route('category-show',$item->slug)};
    if($type==='posts'&&($item->canonical_url&&rtrim($item->canonical_url,'/')!==rtrim($url,'/')||app(\App\Services\PublicSlugRegistry::class)->reserved($item->slug)||Page::where('slug',$item->slug)->exists()))continue;
    if($type==='pages'&&app(\App\Services\PublicSlugRegistry::class)->reserved($item->slug,true))continue;
    $items[]=['url'=>$url,'date'=>$item->updated_at?->toIso8601String()];
   }
  }
  $xml='<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
  foreach($items as $item){$xml.='<url><loc>'.$this->xml($item['url']).'</loc>';if($item['date'])$xml.='<lastmod>'.$this->xml($item['date']).'</lastmod>';$xml.='</url>';}
  return response($xml.'</urlset>',200,['Content-Type'=>'application/xml; charset=UTF-8','Cache-Control'=>'public, max-age=300']);
 }
}
