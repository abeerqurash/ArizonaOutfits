<?php
namespace App\Console\Commands;
use App\Models\SupplierDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
class ProtectSupplierDocuments extends Command
{
    protected $signature='suppliers:protect-documents {--dry-run : Report files without moving them}';
    protected $description='Move supplier documents from public to private storage after verifying each copy';
    public function handle(): int
    {
        $public=Storage::disk('public');$private=Storage::disk('local');$moved=0;$missing=0;$failed=0;
        $paths=[];
        foreach(SupplierDocument::query()->cursor() as $document){
            $path=(string)$document->file_path;
            if(!str_starts_with($path,'suppliers/'.$document->supplier_id.'/documents/') || str_contains($path,'..') || str_contains($path,'\\')){$this->error('Unsafe path on document '.$document->id);$failed++;continue;}
            $paths[$path]='document '.$document->id;
        }
        foreach($public->allFiles('suppliers') as $path){
            if(preg_match('#^suppliers/[0-9]+/documents/[^/]+$#D',$path) && !str_contains($path,'..'))$paths[$path]??='unlinked supplier document';
        }
        foreach($paths as $path=>$label){
            if(!$public->exists($path)){if(!$private->exists($path)){$this->warn('Missing file for '.$label);$missing++;}continue;}
            if($this->option('dry-run')){$this->line('Would protect '.$label);continue;}
            try{
                if(!$private->exists($path)){
                    $stream=$public->readStream($path);
                    if(!is_resource($stream))throw new \RuntimeException('Unable to read source.');
                    try{if(!$private->put($path,$stream))throw new \RuntimeException('Unable to save private copy.');}finally{fclose($stream);}
                }
                if(hash_file('sha256',$public->path($path))!==hash_file('sha256',$private->path($path)))throw new \RuntimeException('Private copy differs; public file retained.');
                if(!$public->delete($path))throw new \RuntimeException('Private copy saved but public file removal failed.');
                $moved++;
            }catch(\Throwable $error){$this->error($label.': '.$error->getMessage());$failed++;}
        }
        $this->info("Protected: {$moved}; missing files: {$missing}; failed: {$failed}.");
        return $failed?self::FAILURE:self::SUCCESS;
    }
}
