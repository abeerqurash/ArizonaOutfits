<?php
namespace App\Services;
use App\Models\{Page,Post,PostRedirect};
use Illuminate\Support\Facades\Route;
class PublicSlugRegistry
{
    public function reserved(string $slug,bool $cms=false): bool
    {
        if($cms && in_array($slug,['privacy-policy','terms-and-conditions'],true))return false;
        foreach(Route::getRoutes() as $route){$first=explode('/',trim($route->uri(),'/'))[0];if($first===$slug && !str_contains($first,'{'))return true;}
        return false;
    }
    public function occupied(string $slug,?int $ignorePage=null,?int $ignorePost=null,bool $cms=false): bool
    {
        return $this->reserved($slug,$cms)
            || Page::where('slug',$slug)->when($ignorePage,fn($q)=>$q->whereKeyNot($ignorePage))->exists()
            || Post::where('slug',$slug)->when($ignorePost,fn($q)=>$q->whereKeyNot($ignorePost))->exists()
            || PostRedirect::where('old_slug',$slug)->when($ignorePost,fn($q)=>$q->where('post_id','!=',$ignorePost))->exists();
    }
}
