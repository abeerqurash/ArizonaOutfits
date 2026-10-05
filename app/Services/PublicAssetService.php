<?php
namespace App\Services;
class PublicAssetService
{
 public function url(string $path):string
 {
  $file=public_path($path);$version=is_file($file)?filemtime($file).'-'.filesize($file):'1';return asset($path).'?v='.$version;
 }
}
