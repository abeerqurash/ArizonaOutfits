from pathlib import Path
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'dashboard-author-image-files';o.mkdir(exist_ok=True)
files=[]
def save(p,s):
 n=f'{len(files)+1:02d}_'+Path(p).name+'.txt';(o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8');files.append((n,p))
s=(r/'app/Models/EcommerceSetting.php').read_text().replace("'store_name',","'store_name',\n        'author_card_image',",1);save('app/Models/EcommerceSetting.php',s)
s=(r/'app/Http/Controllers/Admin/EcommerceSettingController.php').read_text();s=s.replace("            'facebook_url' =>", "            'author_card_image'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],\n            'remove_author_card_image'=>['nullable','boolean'],\n            'facebook_url' =>",1)
s=s.replace('        EcommerceSetting::current()->update($validated);','''        $settings=EcommerceSetting::current();
        $oldImage=$settings->author_card_image;
        $newImage=null;
        unset($validated['author_card_image'],$validated['remove_author_card_image']);
        if($request->hasFile('author_card_image')){
            $newImage=$request->file('author_card_image')->store('store/author-card','public');
            $validated['author_card_image']=$newImage;
        }elseif($request->boolean('remove_author_card_image')){
            $validated['author_card_image']=null;
        }
        try{$settings->update($validated);}catch(\\Throwable $error){
            if($newImage)\\Illuminate\\Support\\Facades\\Storage::disk('public')->delete($newImage);
            throw $error;
        }
        if(array_key_exists('author_card_image',$validated) && $oldImage && $oldImage!==$validated['author_card_image'] && str_starts_with($oldImage,'store/author-card/') && !str_contains($oldImage,'..')){
            \\Illuminate\\Support\\Facades\\Storage::disk('public')->delete($oldImage);
        }''');save('app/Http/Controllers/Admin/EcommerceSettingController.php',s)
s=(r/'resources/views/admin/settings/edit.blade.php').read_text().replace('<form\n','<form enctype="multipart/form-data"\n',1)
section='''<section class="admin-panel"><div class="settings-panel-header"><h3>Author Card Image</h3></div><div class="settings-panel-body">
@if($settings->author_card_image)<img src="{{ app(\\App\\Services\\PublicMediaService::class)->url($settings->author_card_image) }}" alt="Current author card image" style="width:100px;height:100px;object-fit:cover;border-radius:50%;margin-bottom:15px">@endif
<label>Upload or replace image<input type="file" name="author_card_image" accept="image/jpeg,image/png,image/webp"></label><p>JPG, PNG or WebP, maximum 5 MB. Used by the shared CEO card on Privacy Policy and Terms pages.</p>
@error('author_card_image')<p role="alert">{{ $message }}</p>@enderror
@if($settings->author_card_image)<label><input type="checkbox" name="remove_author_card_image" value="1"> Remove current image</label>@endif
</div></section>'''
s=s.replace('<div class="settings-main">','<div class="settings-main">\n'+section,1);save('resources/views/admin/settings/edit.blade.php',s)
s=(r/'resources/views/partials/blog-author-card-info.blade.php').read_text();s=s.replace("{{ asset('asset/media/about-abeer.png') }}","{{ app(\\App\\Services\\PublicMediaService::class)->url(app(\\App\\Services\\StoreSettingsService::class)->settings()->author_card_image) }}");save('resources/views/partials/blog-author-card-info.blade.php',s)
save('database/migrations/2026_10_03_000002_add_author_card_image_to_store_settings.php','''<?php
use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;
return new class extends Migration {
 public function up():void{Schema::table('ecommerce_settings',function(Blueprint $table){$table->string('author_card_image')->nullable();});}
 public function down():void{Schema::table('ecommerce_settings',function(Blueprint $table){$table->dropColumn('author_card_image');});}
};
''')
(o/'INSTALL.txt').write_text('\n\n'.join(n+'\nC:\\xampp\\htdocs\\ArizonaOutfits\\'+p.replace('/','\\') for n,p in files)+'\n\nCopy full contents. Run php artisan migrate and php artisan optimize:clear. Open Store Settings > Author Card Image, upload and Save Settings. If public/storage is missing run php artisan storage:link.',encoding='utf-8')
