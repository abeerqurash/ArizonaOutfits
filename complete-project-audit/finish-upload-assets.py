from pathlib import Path
import json
r=Path.cwd();o=r/'complete-project-audit/replacement-files';m=json.loads((o/'manifest.json').read_text())
def save(p,s):
 old=next((x for x in m if x['destination']==p),None);n=old['file'] if old else f'{len(m)+1:02d}_'+Path(p).name+'.txt'
 if not old:m.append({'file':n,'destination':p})
 (o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
p='app/Http/Controllers/Admin/ProductCategoryController.php';s=(r/p).read_text(encoding='utf-8-sig').replace("'mimes:jpg,jpeg,png,webp',","'mimes:jpg,jpeg,png,webp',\n                'extensions:jpg,jpeg,png,webp',");s=s.replace("$file->getClientOriginalExtension()", "$file->extension() ?: $file->getClientOriginalExtension()")
mark='        $baseName = Str::slug($originalName);';s=s.replace(mark,"        if (!in_array($extension,['jpg','jpeg','png','webp'],true)) {\n            throw \\Illuminate\\Validation\\ValidationException::withMessages(['image'=>'Only supported image extensions are allowed.']);\n        }\n\n"+mark);save(p,s)
save('app/Services/PublicAssetService.php',r'''<?php
namespace App\Services;
class PublicAssetService
{
 public function url(string $path):string
 {
  $file=public_path($path);$version=is_file($file)?filemtime($file).'-'.filesize($file):'1';return asset($path).'?v='.$version;
 }
}
''')
for item in list(m):
 p=item['destination']
 if p.startswith('resources/views/'):
  s=(o/'_staged'/p).read_text(encoding='utf-8');import re
  s=re.sub(r"asset\('(asset/(?:js|css)/[^']+)'\)",lambda z:r"app(\App\Services\PublicAssetService::class)->url('"+z[1]+"')",s);save(p,s)
p='public/.htaccess';s=(r/p).read_text(encoding='utf-8-sig').replace('    RewriteEngine On',r'''    RewriteEngine On
    # Uploaded files must never execute as scripts or active HTML.
    RewriteRule ^(?:storage|uploads)/.*\.(?:php[0-9]*|phtml|phar|cgi|pl|py|sh|shtml?|html?)$ - [F,NC]
''')
s+='''
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 day"
    ExpiresByType application/javascript "access plus 1 day"
    ExpiresByType image/jpeg "access plus 1 day"
    ExpiresByType image/png "access plus 1 day"
    ExpiresByType image/webp "access plus 1 day"
    ExpiresByType image/svg+xml "access plus 1 day"
    ExpiresByType font/woff2 "access plus 1 day"
</IfModule>
<IfModule mod_headers.c>
    <FilesMatch "\\.(css|js|png|jpe?g|webp|svg|woff2?)$">
        Header set Cache-Control "public, max-age=86400"
        Header set X-Content-Type-Options "nosniff"
    </FilesMatch>
</IfModule>
''';save(p,s)
(o/'manifest.json').write_text(json.dumps(m,indent=2),encoding='utf-8')
