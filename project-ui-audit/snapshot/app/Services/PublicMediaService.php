<?php
namespace App\Services;
use Illuminate\Support\Facades\Storage;
class PublicMediaService
{
    public function exists(?string $path): bool
    {
        if(!$path)return false;
        if(preg_match('~^https?://~i',$path))return true;
        $path=ltrim(str_replace('\\','/',$path),'/');
        if(str_starts_with($path,'storage/'))$path=substr($path,8);
        if(str_contains($path,'..'))return false;
        return Storage::disk('public')->exists($path)||is_file(public_path($path));
    }
    public function productImage(\App\Models\Product $product): string
    {
        if($this->exists($product->featured_image))return $this->url($product->featured_image);
        foreach($product->images as $image)if($this->exists($image->image))return $this->url($image->image);
        return $this->url(null);
    }
    public function url(?string $path): string
    {
        if(!$this->exists($path))return asset('asset/media/product-placeholder.svg');
        if(preg_match('~^https?://~i',$path))return app(ResponsiveMediaService::class)->url($path,1280);
        $path=ltrim(str_replace('\\','/',$path),'/');
        if(str_starts_with($path,'storage/'))$path=substr($path,8);
        return app(ResponsiveMediaService::class)->url(is_file(public_path($path))?asset($path):asset('storage/'.$path),1280);
    }
}
