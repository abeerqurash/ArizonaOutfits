from pathlib import Path
root=Path(__file__).parent
p=root/'routes/web.php'
s=p.read_text(encoding='utf-8')
anchor="        | Product options"
pos=s.index("        Route::post(",s.index(anchor))
s=s[:pos]+"        Route::delete('/product-options/{productOption}', [AdminProductOptionController::class, 'destroy'])->name('product-options.destroy');\n        Route::delete('/product-option-values/{productOptionValue}', [AdminProductOptionController::class, 'destroyValue'])->name('product-options.values.destroy');\n\n"+s[pos:]
p.write_text(s,encoding='utf-8')
p=root/'resources/views/admin/products/partials/form.blade.php'
s=p.read_text(encoding='utf-8')
s+='''
<script>
document.addEventListener('DOMContentLoaded', () => {
 const optionUrl = @json(route('admin.product-options.destroy', ['productOption'=>'__ID__']));
 const valueUrl = @json(route('admin.product-options.values.destroy', ['productOptionValue'=>'__ID__']));
 const csrf = @json(csrf_token());
 function addButtons() {
  document.querySelectorAll('[data-option-card]').forEach(card => {
   const heading = card.querySelector('.product-option-heading');
   if (heading && !heading.querySelector('[data-delete-attribute]')) {
    const b=document.createElement('button'); b.type='button'; b.textContent='Delete attribute'; b.dataset.deleteAttribute=card.dataset.optionId;
    b.className='product-add-value-button'; heading.append(b);
   }
   card.querySelectorAll('[data-option-value]').forEach(label => {
    const input=label.querySelector('[data-option-value-checkbox]');
    if (!input || label.querySelector('[data-delete-value]')) return;
    const b=document.createElement('button'); b.type='button'; b.textContent='Delete'; b.dataset.deleteValue=input.value;
    b.setAttribute('aria-label','Delete '+input.dataset.valueLabel); label.append(b);
   });
  });
 }
 addButtons();
 const list=document.getElementById('productOptionsList');
 if(list) new MutationObserver(addButtons).observe(list,{childList:true,subtree:true});
 document.addEventListener('click', async event => {
  const button=event.target.closest('[data-delete-attribute], [data-delete-value]');
  if(!button) return;
  event.preventDefault(); event.stopPropagation();
  const value=button.hasAttribute('data-delete-value');
  const target=button.closest(value?'[data-option-value]':'[data-option-card]');
  if(target.querySelector('input:checked')) { alert('Uncheck this attribute or value and remove its generated variants before deleting it.'); return; }
  if(!confirm('Permanently delete this unused '+(value?'value':'attribute')+'?')) return;
  button.disabled=true;
  try {
   const response=await fetch((value?valueUrl:optionUrl).replace('__ID__',value?button.dataset.deleteValue:button.dataset.deleteAttribute),{
    method:'DELETE', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}
   });
   const data=await response.json();
   if(!response.ok) throw new Error(data.message || 'Deletion failed.');
   target.remove();
  } catch(error) { alert(error.message); button.disabled=false; }
 });
});
</script>
'''
p.write_text(s,encoding='utf-8')
out=root/'product-option-deletion-fix';out.mkdir(exist_ok=True)
paths=['app/Http/Controllers/Admin/ProductOptionController.php','routes/web.php','resources/views/admin/products/partials/form.blade.php']
lines=['# Replacement files','', 'These changes are also applied locally.','']
for i,path in enumerate(paths,1):
 name=f'{i:02d}_{Path(path).name}.txt';(out/name).write_bytes((root/path).read_bytes());lines.append(f'- {name} → {root/path}')
(out/'FILES.md').write_text('\n'.join(lines),encoding='utf-8')
