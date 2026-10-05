from pathlib import Path
import json
base=Path(__file__).parent;root=base.parent;stage=base/'_staged';files=[]
def put(path,s):
 p=stage/path;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8');files.append(path)
path='public/asset/css/arizona-admin-controls.css';s=(root/path).read_text(encoding='utf-8')
for a,b in {'#23746e':'#635bff','#215f5b':'#e7e4ff','#287570':'#4f46e5','#edf7f5':'#f1f0ff','#185d58':'#4f46e5','#345f59':'#514a98','#d0e5e1':'#64748b','#dce6e5':'#e3e7f0','#f2f7f6':'#f5f5ff'}.items():s=s.replace(a,b)
s+='''
/* Preserve one visible control when a page already has its own select component. */
.admin-content select[class*="select-native"],.admin-content input.az-date-native{display:none!important}
.admin-content [class*="select-field"]:has(.az-select-wrap)>i,.admin-content [class*="select-field"]:has(.az-select-wrap)>.payment-select-arrow{display:none!important}
.admin-content [class*="search"]>input{padding-left:38px!important}
.admin-content [class*="select-field"] .az-select-trigger{padding-left:13px!important}
.admin-content input[type=file]{display:block;width:100%;box-sizing:border-box;max-width:100%;min-width:0;margin-top:8px;border:1px solid #e3e7f0;background:#f8f9ff;border-radius:10px;padding:8px;font-size:12px;color:#64748b}
.admin-content input[type=file]::file-selector-button{padding:9px 12px;margin-right:10px;border:0;border-radius:7px;background:#635bff;color:white;font:inherit;font-weight:600;cursor:pointer}
.admin-content .product-variant-field{min-width:0}.admin-content .product-variant-field input[type=file]{min-height:44px;padding:6px}
.admin-content .settings-panel-body.settings-grid-two{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;padding:20px}
.admin-content .settings-panel-body.settings-grid-two>label{display:flex;flex-direction:column;gap:8px;font-size:12px;font-weight:600;min-width:0}
.admin-content .settings-panel-body.settings-grid-two input{width:100%}
.admin-content .settings-panel-body>label:has(input[type=file]){display:block;max-width:580px;margin-top:12px}
.admin-content .settings-panel-body>label:has(input[type=checkbox]){display:flex;align-items:center;gap:8px}
.admin-content :is(.meta-keywords-input,.keyword-input,.tags-input) input{border:0!important;box-shadow:none!important}
.admin-content .az-page-banner{position:relative;display:flex;align-items:center;justify-content:space-between;gap:20px;min-height:130px;padding:24px 28px!important;margin:0 0 22px;box-sizing:border-box;border:1px solid #e3e7f0!important;border-radius:18px!important;background:linear-gradient(105deg,#fff 55%,#ece9ff)!important;color:#172033!important;overflow:hidden}
.admin-content .az-page-banner :is(h1,h2){font-size:30px!important;line-height:1.2;margin:6px 0 10px!important;color:#172033!important;letter-spacing:-.03em}
.admin-content .az-page-banner p{color:#72809b!important;font-size:13px!important;line-height:1.65;max-width:680px;margin:0!important}
.admin-content .az-page-banner :is(.admin-page-eyebrow,.admin-panel-eyebrow)>*,.admin-content .az-page-banner>div>span:first-child{color:#635bff!important}
.admin-content .az-page-banner>div>span:first-child{font-size:10px;letter-spacing:.12em;font-weight:800;text-transform:uppercase}
.admin-content .az-page-banner .admin-button-secondary,.admin-content .az-page-banner a.secondary{background:#fff!important;color:#172033!important;border:1px solid #e3e7f0!important}
.admin-content :is(.az-page-banner button[type=submit],.az-page-banner .admin-button-primary){background:#635bff!important;color:#fff!important}
.admin-content :is(.settings-tabs,.settings-navigation) .active{color:#635bff!important;background:#f1f0ff!important}
@media(max-width:700px){.admin-content .settings-panel-body.settings-grid-two{grid-template-columns:minmax(0,1fr)}.admin-content .az-page-banner{padding:20px!important;align-items:flex-start;flex-direction:column}.admin-content .az-page-banner :is(h1,h2){font-size:25px!important}.az-page-banner .admin-page-actions{width:100%;flex-wrap:wrap}}
'''
put(path,s)
path='public/asset/js/arizona-admin-controls.js';s=(root/path).read_text(encoding='utf-8')
s=s.replace("select.multiple||select.dataset.azEnhanced||select.closest", "select.multiple||/select-native/.test(select.className)||select.dataset.azEnhanced||select.closest")
s=s.replace("input.dataset.azEnhanced='true';const trigger", "input.dataset.azEnhanced='true';input.classList.add('az-date-native');input.tabIndex=-1;const trigger")
s=s.replace("trigger.textContent='Open calendar ▦';", "function refreshDate(){trigger.textContent=(input.value?input.value.replace('T',' · '):'Choose date')+' ▦';trigger.disabled=input.disabled;}refreshDate();input.addEventListener('change',refreshDate);input.addEventListener('invalid',()=>trigger.focus());")
s=s.replace("'.az-select-native').forEach", "'.az-select-native,.az-date-native').forEach")
s=s.replace("function init(){document.querySelectorAll", "function init(){banner();document.querySelectorAll")
pos=s.index('function init(){')
s=s[:pos]+'''function banner(){
 const content=document.querySelector('.admin-content');if(!content||content.querySelector('.az-page-banner'))return;
 const existing=content.querySelector('.admin-page-header,.team-header,.roles-header,.roles-hero,.audit-header,.audit-hero,.backup-hero,.notification-hero,.analytics-header,.analytics-hero,.inventory-page-header,.page-header');
 if(existing){existing.classList.add('az-page-banner');return;}
 const banner=document.createElement('header');banner.className='az-page-banner';const copy=document.createElement('div');const eyebrow=document.createElement('span');eyebrow.textContent='ARIZONA OUTFITS · ADMINISTRATION';const title=document.createElement('h2');title.textContent=document.title.split('|')[0].trim()||'Dashboard';copy.append(eyebrow,title);banner.append(copy);content.prepend(banner);
}
'''+s[pos:]
put(path,s)
path='resources/views/admin/layouts/app.blade.php';s=(root/path).read_text(encoding='utf-8')
if 'arizona-admin-controls.css' not in s:s=s.replace('</head>',"<link rel=\"stylesheet\" href=\"{{ asset('asset/css/arizona-admin-controls.css') }}\">\n</head>")
if 'arizona-admin-controls.js' not in s:s=s.replace('</body>',"<script src=\"{{ asset('asset/js/arizona-admin-controls.js') }}\"></script>\n</body>")
put(path,s)
(base/'changed.json').write_text(json.dumps(files,indent=2))
lines=['# Dashboard UI correction','', 'Replace all three files together. No migration is required. Live sources remain unchanged.','', '| Replacement | Exact destination |','|---|---|']
for i,path in enumerate(files,1):
 name=f'{i:02d}_{Path(path).name}.txt';(base/name).write_bytes((stage/path).read_bytes());lines.append(f'| [{name}]({name}) | `{root/path}` |')
(base/'FILES.md').write_text('\n'.join(lines),encoding='utf-8')
print('Prepared three replacements')
