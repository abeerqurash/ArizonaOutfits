from pathlib import Path
import json,re
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');o=r/'storefront-sync-files';m=json.loads((o/'manifest.json').read_text())
def update(p,s):
 item=next(x for x in m if x['destination']==p);(o/item['file']).write_text(s);(o/'_staged'/p).write_text(s)
s=(o/'_staged/app/Services/PublicMediaService.php').read_text();pos=s.index('    public function url')
s=s[:pos]+'''    public function productImage(\\App\\Models\\Product $product): string
    {
        if($this->exists($product->featured_image))return $this->url($product->featured_image);
        foreach($product->images as $image)if($this->exists($image->image))return $this->url($image->image);
        return $this->url(null);
    }
'''+s[pos:];update('app/Services/PublicMediaService.php',s)
for p in ['resources/views/products/partials/product-card.blade.php','resources/views/products/show.blade.php']:
 s=(o/'_staged'/p).read_text()
 if 'product-card' in p:s=re.sub(r'\$mainImageUrl = \$getCardImageUrl\(\s*\$product->featured_image\s*\);',r'$mainImageUrl = app(\\App\\Services\\PublicMediaService::class)->productImage($product);',s)
 else:s=re.sub(r'\$featuredImageUrl = \$getProductImageUrl\(\s*\$product->featured_image\s*\);',r'$featuredImageUrl = app(\\App\\Services\\PublicMediaService::class)->productImage($product);',s)
 update(p,s)
p='resources/views/products/partials/category-product-card.blade.php';s=(o/'_staged'/p).read_text().replace('->url($product->featured_image)','->productImage($product)');update(p,s)
# Supply full install mapping and verification guidance.
lines=['INSTALL ALL 24 FILES AS ONE UPDATE','', 'Open each .txt file, copy its complete contents, and replace/create the destination below. Save without the final .txt extension.','']
for x in m:lines += [x['file'],'C:\\xampp\\htdocs\\ArizonaOutfits\\'+x['destination'].replace('/','\\'),'']
lines += ['After copying ALL files, open PowerShell in C:\\xampp\\htdocs\\ArizonaOutfits and run:', 'C:\\xampp\\php\\php.exe artisan migrate','C:\\xampp\\php\\php.exe artisan optimize:clear','','If public/storage is missing, run: C:\\xampp\\php\\php.exe artisan storage:link','Refresh browser with Ctrl+F5.','','Dashboard: Store Settings > Footer & Social Links. Enter URLs/copyright/about text and save. Header/footer navigation: Menu Builder > Header Navigation / Footer Navigation. Mega Menu categories remain in their own panel.','','Product category directory: http://127.0.0.1:8000/product-categories','Blog category directory: http://127.0.0.1:8000/category','Single product category: http://127.0.0.1:8000/product-category/your-slug','Single blog category: http://127.0.0.1:8000/category/your-slug','','IMAGE RECOVERY','Git ignores uploaded files in storage/app/public. For a laptop move, copy that folder separately from the old laptop. Import the matching database and create public/storage link. Uploads are not recovered from Git by this update.','For lost images: Admin > Products > Edit > Gallery Images. Tick Remove after saving beneath each old image, upload replacement files, and save the product. Replace/remove the featured image separately. Missing local gallery images are omitted from storefront galleries; the main image uses an available gallery image or placeholder. Remote image availability is not checked.','','TEST NOW','Home: up to 6 latest and 6 popular active products. If fewer than six exist, only available products appear.','Home/Privacy/Terms: same product review showcase as blogs, approved reviews only; product/review navigation works.','About: product category links open category pages.','Blogs: filter defaults closed, opens smoothly, closes on submit and refresh; selected filters persist.','Category directory and individual product categories: project-list card design.','Footer: saved social URLs/copyright appear, empty social URLs are hidden.','Product edit: removal checkboxes accessible even for missing images. Save and verify old records disappear.','','VALIDATION','23 PHP/compiled Blade syntax checks; isolated checks for media fallback, six-product limits, active-only popularity, four page renders, category links, approved-only reviews, blog filter markup/query retention, footer settings migration/render. Browser visual layout and animations need your check.']
(o/'INSTALL.txt').write_text('\n'.join(lines),encoding='utf-8')
md=['All paths are inside `C:\\xampp\\htdocs\\ArizonaOutfits`. Copy full contents; create destinations that do not exist.','', '| Download | Destination |','|---|---|']
for x in m:md.append(f"| [{x['file']}](C:/xampp/htdocs/ArizonaOutfits/storefront-sync-files/{x['file']}) | `{x['destination']}` |")
(o/'FILES.md').write_text('\n'.join(md),encoding='utf-8')
print('Installation guide and clickable file list written.')
