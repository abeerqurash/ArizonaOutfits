<?php
namespace App\Services;
use Illuminate\Support\Facades\Storage;
class ResponsiveMediaService
{
 public const WIDTHS=[180,640,1280];
 public function source(string $url):?string
 {
  $parsed=parse_url($url);if($parsed===false)return null;
  if(isset($parsed['host'])&&strtolower($parsed['host'])!==strtolower((string)parse_url(url('/'),PHP_URL_HOST)))return null;
  $path=rawurldecode($parsed['path']??$url);if(str_contains($path,'..')||str_contains($path,'\\'))return null;
  $path=ltrim($path,'/');$root=str_starts_with($path,'storage/')?Storage::disk('public')->path(''):public_path();
  $candidate=str_starts_with($path,'storage/')?Storage::disk('public')->path(substr($path,8)):public_path($path);
  $real=realpath($candidate);$realRoot=realpath($root);if(!$real||!$realRoot||!str_starts_with(strtolower($real),strtolower(rtrim($realRoot,'/\\').DIRECTORY_SEPARATOR))||!is_file($real))return null;
  return $real;
 }
 public function cachePath(string $source,int $width):string{return 'media-cache/'.hash('sha256',realpath($source).'|'.filemtime($source).'|'.filesize($source)).'-'.$width.'.webp';}
 public function url(string $url,int $width=640):string
 {
  if(!in_array($width,self::WIDTHS,true))return $url;
  if(preg_match('#/storage/media-cache/([a-f0-9]{64})-(180|640|1280)\.webp$#',parse_url($url,PHP_URL_PATH)??'',$match)){
   $candidate='media-cache/'.$match[1].'-'.$width.'.webp';return Storage::disk('public')->exists($candidate)?asset('storage/'.$candidate):$url;
  }
  $source=$this->source($url);if(!$source)return $url;
  $path=$this->cachePath($source,$width);return Storage::disk('public')->exists($path)?asset('storage/'.$path):$url;
 }
}
