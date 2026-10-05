<?php
require __DIR__.'/bootstrap.php';
use Illuminate\Support\Facades\{Storage,Artisan,RateLimiter};
use App\Models\{Page,Post,PostRedirect,SupplierDocument};
use PhpOffice\PhpSpreadsheet\{Spreadsheet,Cell\DataType};
use Symfony\Component\Console\Output\BufferedOutput;
run('Export cell types',function(){
 $sheet=new Spreadsheet;$binder=new App\Exports\SafeExportValueBinder;
 foreach(['=HYPERLINK("https://example.test","click")','00123','+123','@SUM(1,2)'] as $i=>$v){$cell=$sheet->getActiveSheet()->getCell('A'.($i+1));$binder->bindValue($cell,$v);check('Untrusted export text stays literal '.($i+1),$cell->getDataType()===DataType::TYPE_STRING && $cell->getValue()===$v);}
 $cell=$sheet->getActiveSheet()->getCell('B1');$binder->bindValue($cell,12.5);check('Export amount remains numeric',$cell->getDataType()===DataType::TYPE_NUMERIC);
 foreach([App\Exports\PurchaseOrderExport::class,App\Exports\StockValuationExport::class,App\Exports\ReorderDashboardExport::class] as $c)check('Export uses safe custom binder '.$c,is_subclass_of($c,App\Exports\SafeExportValueBinder::class)&&is_subclass_of($c,Maatwebsite\Excel\Concerns\WithCustomValueBinder::class));
});
run('Shared root namespace',function(){
 $registry=new App\Services\PublicSlugRegistry;
 check('Static shop URL reserved',$registry->occupied('products'));
 check('Blog cannot take policy URL',$registry->occupied('privacy-policy'));
 check('CMS may edit dedicated policy URL',!$registry->reserved('privacy-policy',true));
 $page=Page::create(['title'=>'Audit page','slug'=>'audit-page','status'=>'draft']);
 check('CMS draft blocks blog slug',$registry->occupied('audit-page',null,100));
 check('CMS edit can retain its own slug',!$registry->occupied('audit-page',$page->id,null,true));
 $post=Post::create(['title'=>'Audit post','slug'=>'audit-post','status'=>'draft']);
 check('Draft blog blocks CMS slug',$registry->occupied('audit-post',null,null,true));
 PostRedirect::create(['post_id'=>$post->id,'old_slug'=>'audit-previous']);
 check('Historical blog redirect blocks CMS slug',$registry->occupied('audit-previous',null,null,true));
 check('Blog can reclaim its own former slug',!$registry->occupied('audit-previous',null,$post->id));
});
run('Supplier private storage migration',function(){
 foreach(['public'=>'public','local'=>'private'] as $disk=>$folder){if(realpath(Storage::disk($disk)->path(''))!==realpath(__DIR__.'/files/'.$folder))throw new RuntimeException('Fixture isolation failed');}
 $s=supplier();$path='suppliers/'.$s->id.'/documents/audit.txt';$doc=SupplierDocument::create(['supplier_id'=>$s->id,'title'=>'Private fixture','document_type'=>'bank_details','file_path'=>$path,'original_name'=>'audit.txt']);
 Storage::disk('local')->delete($path);Storage::disk('public')->put($path,'sensitive-fixture-only');
 $command=new App\Console\Commands\ProtectSupplierDocuments;$command->setLaravel($GLOBALS['app']);Artisan::registerCommand($command);
 $out=new BufferedOutput;$exit=Artisan::call('suppliers:protect-documents',['--dry-run'=>true],$out);
 check('Dry-run leaves source intact',$exit===0&&Storage::disk('public')->exists($path)&&!Storage::disk('local')->exists($path));
 $exit=Artisan::call('suppliers:protect-documents',[],$out);
 check('Migration creates identical private copy',$exit===0&&Storage::disk('local')->get($path)==='sensitive-fixture-only');
 check('Migration removes public access copy',!Storage::disk('public')->exists($path));
 check('Migration safely reruns',Artisan::call('suppliers:protect-documents',[],$out)===0);
 $controller=new App\Http\Controllers\Admin\SupplierDocumentController;
 check('Authorized supplier download finds private file',$controller->download($s,$doc) instanceof Symfony\Component\HttpFoundation\StreamedResponse);
 $other=supplier();check('Cross-supplier download denied',mustDeny(fn()=>$controller->download($other,$doc)));
 Storage::disk('public')->put($path,'new-conflicting-public');
 check('Migration refuses mismatched private copy',Artisan::call('suppliers:protect-documents',[],$out)===1&&Storage::disk('public')->exists($path));
 Storage::disk('public')->delete($path);$controller->destroy($s,$doc);
 check('Document removal deletes private file',!Storage::disk('local')->exists($path)&&!SupplierDocument::find($doc->id));
});
run('Public throttling and CMS fallback',function(){
 foreach(['public-forms'=>5,'public-tracking'=>10,'checkout-attempts'=>10] as $name=>$n){$limiter=RateLimiter::limiter($name);check('Named limiter '.$name,$limiter(req())->maxAttempts===$n);}
 $source=file_get_contents(__DIR__.'/../replacement-files/_staged/routes/web.php');
 foreach(['home.form.submit','contact.form.submit','blog.form.submit','orders.track.lookup','checkout.place','checkout.stripe.intent'] as $name)check('Route has named throttle '.$name,preg_match("/~never~/",'')===0&&preg_match("/->middleware\('throttle:[^']+'\)->name\('".preg_quote($name,'/')."'\)/",$source)===1);
 $r=Illuminate\Http\Request::create('http://arizona.test/privacy-policy');$GLOBALS['app']->instance('request',$r);
 $view=view('pages.custom.privacy-policy');$GLOBALS['app']['events']->dispatch('composing: pages.custom.privacy-policy',[$view]);
 check('Custom CMS receives published blog list',isset($view->getData()['posts'])&&$view->getData()['posts']->every(fn($p)=>$p->status==='published'));
 check('Custom CMS receives latest sidebar blogs',isset($view->getData()['latestPosts']));
 check('Product placeholder is installed',is_file(public_path('asset/media/product-placeholder.svg')));
});
$failed=count(array_filter($results,fn($r)=>$r['result']!=='PASS'));
file_put_contents(__DIR__.'/../targeted-checks.json',json_encode($results,JSON_PRETTY_PRINT));echo count($results).' targeted checks; '.$failed." failures.\n";exit($failed?1:0);
