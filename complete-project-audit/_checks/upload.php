<?php
require __DIR__.'/bootstrap.php';
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
// Original controller is loaded under a fixture-only class name to reproduce the extension gap without saving files.
$original=file_get_contents(__DIR__.'/../../app/Http/Controllers/Admin/ProductCategoryController.php');$original=str_replace('class ProductCategoryController extends','class OriginalProductCategoryController extends',$original);file_put_contents(__DIR__.'/OriginalProductCategoryController.php',$original);require __DIR__.'/OriginalProductCategoryController.php';
function imageRequest($filename){$r=req(['title'=>'Upload audit']);$r->files->set('image',UploadedFile::fake()->image($filename,20,20)->mimeType('image/jpeg'));return $r;}
$before=new App\Http\Controllers\Admin\OriginalProductCategoryController;$after=new App\Http\Controllers\Admin\ProductCategoryController;
run('Category image extension validation',function()use($before,$after){
 $old=new ReflectionMethod($before,'validateCategory');$new=new ReflectionMethod($after,'validateCategory');
 foreach(['unsafe.html','unsafe.svg','unsafe.shtml']as$name){$accepted=true;try{$old->invoke($before,imageRequest($name));}catch(Illuminate\Validation\ValidationException){$accepted=false;}check('Original accepted mismatched image filename '.$name,$accepted);check('Replacement rejects mismatched extension '.$name,validationBlocked(fn()=>$new->invoke($after,imageRequest($name))));}
 check('Replacement accepts a real JPEG',isset($new->invoke($after,imageRequest('safe.jpg'))['image']));
 $store=new ReflectionMethod($after,'storeSeoImage');$path=$store->invoke($after,UploadedFile::fake()->image('safe.png',20,20),'category-audit-fixture');check('Stored extension comes from verified MIME',str_ends_with($path,'.png')&&Storage::disk('public')->exists($path));Storage::disk('public')->delete($path);
});
$failed=count(array_filter($results,fn($r)=>$r['result']!=='PASS'));file_put_contents(__DIR__.'/../upload-checks.json',json_encode($results,JSON_PRETTY_PRINT));echo count($results).' upload checks; '.$failed." failures.\n";exit($failed?1:0);
