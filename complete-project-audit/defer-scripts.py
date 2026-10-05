from pathlib import Path
import json,re
r=Path.cwd();o=r/'complete-project-audit/replacement-files';m=json.loads((o/'manifest.json').read_text())
def save(p,s):
 old=next((x for x in m if x['destination']==p),None);n=old['file'] if old else f'{len(m)+1:02d}_'+Path(p).name+'.txt'
 if not old:m.append({'file':n,'destination':p})
 (o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
p='resources/views/layouts/app.blade.php';s=(o/'_staged'/p).read_text(encoding='utf-8');s=s.replace('    @php($publicSeo',"    @php($needsPhoneInput = str_contains($__env->yieldContent('content'), 'phone-field'))\n    @php($publicSeo",1)
s=re.sub(r'\s*<script\s+src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"\s*></script>','',s)
s=re.sub(r'(<script\s+src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"\s*></script>)',r'@if($needsPhoneInput)\1@endif',s)
s=re.sub(r'(<link\s+rel="stylesheet"\s+href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css"[^>]*>)',r'@if($needsPhoneInput)\1@endif',s)
s=re.sub(r'<script(\s+src="[^"]+")\s*>',r'<script\1 defer>',s);save(p,s)
for item in m:
 p=item['destination']
 if p.startswith('resources/views/') and p!='resources/views/layouts/app.blade.php':
  s=(o/'_staged'/p).read_text(encoding='utf-8');s=re.sub(r'<script(\s+src="[^"]+")\s*>',r'<script\1 defer>',s);save(p,s)
save('public/asset/js/product.js',(r/'public/asset/js/product.js').read_text(encoding='utf-8-sig'))
(o/'manifest.json').write_text(json.dumps(m,indent=2),encoding='utf-8')
