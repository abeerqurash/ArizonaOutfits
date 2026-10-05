from pathlib import Path
import shutil
base=Path(__file__).parent
root=base.parent
stage=base/'_staged'
files=['public/asset/js/arizona-admin-controls.js','public/asset/css/arizona-admin-controls.css','resources/views/admin/layouts/app.blade.php','resources/views/customer/layouts/app.blade.php','resources/views/customer/partials/styles.blade.php']
for name in files:
 p=stage/name;p.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(root/name,p)
p=stage/files[0];s=p.read_text(encoding='utf-8')
s=s.replace("/select-native/.test(select.className)","/(?:select-native|cat-index-native|az-cat-native|az-coupon-native|az-coupon-filter-native)/.test(select.className)")
s=s.replace(".az-product-select,.az-product-select-wrap,.az-calendar",".az-product-select,.az-product-select-wrap,.az-index-select,.cat-index-select,.az-cat-select,.az-coupon-select,.az-coupon-filter,.az-select-wrap,.az-calendar")
s=s.replace("trigger.textContent=(select.selectedOptions[0]?.textContent||'Choose option')+' ▾';", "const text=(select.selectedOptions[0]?.textContent||'Choose option')+' ▾';if(trigger.textContent!==text)trigger.textContent=text;")
s=s.replace("document.querySelector('.admin-content');", "document.querySelector('.admin-content,.customer-dashboard-content');")
s=s.replace(".inventory-page-header,.page-header'", ".inventory-page-header,.page-header,.customer-page-heading'")
s=s.replace("'ARIZONA OUTFITS · ADMINISTRATION'", "content.matches('.admin-content')?'ARIZONA OUTFITS · ADMINISTRATION':'ARIZONA OUTFITS · YOUR ACCOUNT'")
s=s.replace("'.admin-content select'", "'.admin-content select,.customer-dashboard-content select'")
s=s.replace("'.admin-content input[type=date],.admin-content input[type=datetime-local]'", "'.admin-content input[type=date],.admin-content input[type=datetime-local],.customer-dashboard-content input[type=date],.customer-dashboard-content input[type=datetime-local]'")
s=s.replace("document.querySelector('.admin-content')||document.body", "document.querySelector('.admin-content,.customer-dashboard-content')||document.body")
s=s.replace("attributes:true,attributeFilter:['class','hidden','style']", "attributes:true,attributeFilter:['hidden']")
s=s.replace('// Keep the original date input editable as an accessible typed fallback.', '// Preserve the native submitted value; the visible button opens the calendar.')
s=s.replace("document.addEventListener('click',event=>{if(activePopup", "document.addEventListener('click',event=>{requestAnimationFrame(init);if(activePopup")
p.write_text(s,encoding='utf-8')
p=stage/files[1];s=p.read_text(encoding='utf-8').replace('.admin-content',':is(.admin-content,.customer-dashboard-content)')
s+='''\n/* Existing page dropdowns own their backing selects; never show a second control. */
:is(.admin-content,.customer-dashboard-content) select:is(.cat-index-native,.az-cat-native,.az-coupon-native,.az-coupon-filter-native){display:none!important}
.admin-content :is(.cat-index-button,.az-cat-button,.az-coupon-btn,.az-coupon-filter-btn,.az-index-select-button,.az-product-select-button){width:100%;min-height:42px;box-sizing:border-box;border:1px solid #e3e7f0!important;border-radius:10px!important;background:#fff!important;color:#172033!important;padding:10px 14px!important;text-align:left}
.admin-content :is(.cat-index-menu,.az-cat-menu,.az-coupon-menu,.az-coupon-filter-menu,.az-index-select-menu){border-color:#e3e7f0!important;border-radius:12px!important;box-shadow:0 12px 32px rgba(23,32,51,.12)!important}
.customer-dashboard-content :is(.customer-primary-button,.customer-filters button,.customer-form-actions button){background:#635bff;color:#fff}
.customer-dashboard-content :is(.customer-panel-heading a,.customer-row-action,.customer-empty a,.customer-summary-body>a){color:#635bff}
'''
p.write_text(s,encoding='utf-8')
for name in files[2:4]:
 p=stage/name;s=p.read_text(encoding='utf-8').replace('20261005-purple-fix','20261005-single-controls')
 if 'customer/' in name:
  s=s.replace("@stack('page-styles')","@stack('page-styles')\n    <link rel=\"stylesheet\" href=\"{{ asset('asset/css/arizona-admin-controls.css') }}?v=20261005-single-controls\">")
  s=s.replace("@stack('page-scripts')","@stack('page-scripts')\n    <script src=\"{{ asset('asset/js/arizona-admin-controls.js') }}?v=20261005-single-controls\"></script>")
 p.write_text(s,encoding='utf-8')
p=stage/files[4];p.write_text(p.read_text(encoding='utf-8').replace('#0f766e','#635bff'),encoding='utf-8')
for name in files:
 out=base/'replacement-files'/(name+'.txt');out.parent.mkdir(parents=True,exist_ok=True);shutil.copyfile(stage/name,out)
(base/'FILES.md').write_text('\n'.join(f'- `{name}.txt` → `{root/name}`' for name in files),encoding='utf-8')
print('Prepared five complete replacement files; live source unchanged.')
