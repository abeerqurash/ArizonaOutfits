from pathlib import Path
import json,re
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'complete-project-audit/replacement-files';o.mkdir(exist_ok=True);items=[]
def save(p,s):
 n=f'{len(items)+1:02d}_'+Path(p).name+'.txt';(o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8');items.append({'file':n,'destination':p})
def read(p):return (r/p).read_text(encoding='utf-8-sig')
s=read('app/Services/PublicMediaService.php').replace("asset('asset/images/product-placeholder.svg')","asset('asset/media/product-placeholder.svg')");save('app/Services/PublicMediaService.php',s)
s=read('app/Http/Controllers/Admin/SupplierDocumentController.php').replace("'public'","'local'");save('app/Http/Controllers/Admin/SupplierDocumentController.php',s)
save('app/Console/Commands/ProtectSupplierDocuments.php','''<?php
namespace App\\Console\\Commands;
use App\\Models\\SupplierDocument;
use Illuminate\\Console\\Command;
use Illuminate\\Support\\Facades\\Storage;
class ProtectSupplierDocuments extends Command
{
    protected $signature='suppliers:protect-documents {--dry-run : Report files without moving them}';
    protected $description='Move supplier documents from public to private storage after verifying each copy';
    public function handle(): int
    {
        $public=Storage::disk('public');$private=Storage::disk('local');$moved=0;$missing=0;$failed=0;
        foreach(SupplierDocument::query()->cursor() as $document){
            $path=(string)$document->file_path;
            if(!str_starts_with($path,'suppliers/'.$document->supplier_id.'/documents/') || str_contains($path,'..') || str_contains($path,'\\\\')){$this->error('Unsafe path on document '.$document->id);$failed++;continue;}
            if(!$public->exists($path)){if(!$private->exists($path)){$this->warn('Missing file for document '.$document->id);$missing++;}continue;}
            if($this->option('dry-run')){$this->line('Would protect document '.$document->id);continue;}
            try{
                if(!$private->exists($path)){
                    $stream=$public->readStream($path);
                    if(!is_resource($stream))throw new \\RuntimeException('Unable to read source.');
                    try{if(!$private->put($path,$stream))throw new \\RuntimeException('Unable to save private copy.');}finally{fclose($stream);}
                }
                if(hash_file('sha256',$public->path($path))!==hash_file('sha256',$private->path($path)))throw new \\RuntimeException('Private copy differs; public file retained.');
                if(!$public->delete($path))throw new \\RuntimeException('Private copy saved but public file removal failed.');
                $moved++;
            }catch(\\Throwable $error){$this->error('Document '.$document->id.': '.$error->getMessage());$failed++;}
        }
        $this->info("Protected: {$moved}; missing files: {$missing}; failed: {$failed}.");
        return $failed?self::FAILURE:self::SUCCESS;
    }
}
''')
s=read('app/Services/AdminBackupService.php');s=s.replace("        $this->addDirectory(\n            $zip,\n            public_path('uploads'),", "        $this->addDirectory($zip, Storage::disk('local')->path('suppliers'), 'files/private-suppliers');\n        $this->addDirectory(\n            $zip,\n            public_path('uploads'),",1);s=s.replace('"Always restore on a test copy first.', '"Private supplier files: copy files/private-suppliers into the local disk suppliers folder, never public storage.\\r\\n" .\n            "Always restore on a test copy first.');save('app/Services/AdminBackupService.php',s)
save('app/Services/PublicSlugRegistry.php','''<?php
namespace App\\Services;
use App\\Models\\{Page,Post,PostRedirect};
use Illuminate\\Support\\Facades\\Route;
class PublicSlugRegistry
{
    public function reserved(string $slug,bool $cms=false): bool
    {
        if($cms && in_array($slug,['privacy-policy','terms-and-conditions'],true))return false;
        foreach(Route::getRoutes() as $route)if(trim($route->uri(),'/')===$slug && !str_contains($route->uri(),'{'))return true;
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
''')
s=read('app/Http/Controllers/Admin/AdminPageController.php');a=s.index('        $reserved=collect(');b=s.index('\n        return $candidate;',a)
s=s[:a]+'''        $candidate = $base;
        $suffix = 2;
        while(app(\\App\\Services\\PublicSlugRegistry::class)->occupied($candidate,$page?->id,null,true)){
            $candidate=$base.'-'.$suffix++;
        }
'''+s[b:];save('app/Http/Controllers/Admin/AdminPageController.php',s)
s=read('app/Http/Controllers/Admin/PostController.php');a=s.index('        while (',s.index('    private function uniqueSlug('));b=s.index('\n        return $slug;',a)
s=s[:a]+'''        while(app(\\App\\Services\\PublicSlugRegistry::class)->occupied($slug,null,$ignoreId)){
            $slug=$base.'-'.$counter++;
        }
'''+s[b:]
s=s.replace("                if ($slugOwner || ($redirectOwner", "                if (app(\\App\\Services\\PublicSlugRegistry::class)->occupied($attributes['slug'],null,$lockedPost->id) || $slugOwner || ($redirectOwner",1);save('app/Http/Controllers/Admin/PostController.php',s)
save('app/Exports/SafeExportValueBinder.php','''<?php
namespace App\\Exports;
use Maatwebsite\\Excel\\DefaultValueBinder;
use PhpOffice\\PhpSpreadsheet\\Cell\\{Cell,DataType};
class SafeExportValueBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell,$value): bool
    {
        if(is_string($value)){
            // Prevent spreadsheet formulas and preserve SKU leading zeroes.
            $cell->setValueExplicit($value,DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell,$value);
    }
}
''')
for name in ['PurchaseOrderExport','StockValuationExport','ReorderDashboardExport']:
 p='app/Exports/'+name+'.php';s=read(p).replace('class '+name+' implements','class '+name+' extends SafeExportValueBinder implements\n    \\Maatwebsite\\Excel\\Concerns\\WithCustomValueBinder,',1);save(p,s)
# Avoid invisible directory cards from legacy entrance CSS even when page script aborts.
p='resources/views/products/categories.blade.php';s=read(p)
if 'opacity:1!important' not in s:s+='\n@push(\'page-styles\')<style>.az-collection-grid>.list-item{opacity:1!important;visibility:visible!important;transform:none!important}</style>@endpush\n'
save(p,s)
(o/'manifest.json').write_text(json.dumps(items,indent=2));print('Prepared',len(items),'application fixes')
