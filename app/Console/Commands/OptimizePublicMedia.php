<?php
namespace App\Console\Commands;
use App\Services\ResponsiveMediaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
class OptimizePublicMedia extends Command
{
 protected $signature='media:optimize {--dry-run : Report images without generating derivatives}';
 protected $description='Create WebP card and thumbnail derivatives while preserving original images';
 public function handle():int
 {
  if(!extension_loaded('gd')||!function_exists('imagewebp')){$this->error('GD with WebP support is required.');return self::FAILURE;}
  $disk=Storage::disk('public');$media=app(ResponsiveMediaService::class);$sources=[];$done=0;$failed=0;
  foreach($disk->allFiles() as $path){if(str_starts_with($path,'media-cache/')||str_starts_with($path,'suppliers/'))continue;if(preg_match('/\.(jpe?g|png|webp)$/i',$path))$sources[]=$disk->path($path);}
  $static=public_path('asset/media');if(is_dir($static))foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($static,\FilesystemIterator::SKIP_DOTS)) as $file)if($file->isFile()&&preg_match('/\.(jpe?g|png|webp)$/i',$file->getFilename()))$sources[]=$file->getPathname();
  foreach(array_unique($sources) as $source){
   if($this->option('dry-run')){$this->line('Would optimize '.basename($source));continue;}
   try{
    $info=@getimagesize($source);if(!$info||$info[0]*$info[1]>64000000||filesize($source)>50000000)throw new \RuntimeException('Unsupported or excessively large image.');
    $limit=ini_get('memory_limit');$limitBytes=(int)$limit*(str_ends_with(strtolower($limit),'g')?1073741824:(str_ends_with(strtolower($limit),'m')?1048576:(str_ends_with(strtolower($limit),'k')?1024:1)));
    if($limit!=='-1'&&$info[0]*$info[1]*6+memory_get_usage(true)>$limitBytes)throw new \RuntimeException('Not enough PHP memory; use a larger CLI memory limit.');
    $missing=array_filter(ResponsiveMediaService::WIDTHS,fn($width)=>!$disk->exists($media->cachePath($source,$width)));if(!$missing)continue;
    $image=@imagecreatefromstring(file_get_contents($source));if(!$image)throw new \RuntimeException('Cannot decode image.');
    try{foreach($missing as $width){$w=min($width,$info[0]);$h=max(1,(int)round($info[1]*$w/$info[0]));$target=imagecreatetruecolor($w,$h);imagealphablending($target,false);imagesavealpha($target,true);imagefill($target,0,0,imagecolorallocatealpha($target,0,0,0,127));
     try{imagecopyresampled($target,$image,0,0,0,0,$w,$h,$info[0],$info[1]);ob_start();try{if(!imagewebp($target,null,82))throw new \RuntimeException('WebP encoding failed.');$bytes=ob_get_contents();}finally{ob_end_clean();}if(!$disk->put($media->cachePath($source,$width),$bytes))throw new \RuntimeException('Cannot write derivative.');$done++;}finally{imagedestroy($target);}
    }}finally{imagedestroy($image);}
   }catch(\Throwable $error){$this->warn(basename($source).': '.$error->getMessage());$failed++;}
  }
  $this->info("Created {$done} derivatives; {$failed} images need attention. Originals preserved.");return $failed?self::FAILURE:self::SUCCESS;
 }
}
