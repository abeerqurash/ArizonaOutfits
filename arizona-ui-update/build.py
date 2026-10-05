from pathlib import Path
import json, shutil
base=Path(__file__).parent;root=base.parent;stage=base/'_staged';changed=[]
def put(path,text):
 p=stage/path;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(text,encoding='utf-8');
 if path not in changed:changed.append(path)
def read(path):return (root/path).read_text(encoding='utf-8')
def prior(path):put(path,(root/'storefront-feature-update/_staged'/path).read_text(encoding='utf-8'))
for path in ['app/Http/Controllers/CartController.php','app/Http/Controllers/CheckoutController.php','app/Services/CustomMeasurementsService.php','resources/views/products/partials/measurement-summary.blade.php']:prior(path)
for path in ['resources/views/cart/index.blade.php','resources/views/checkout/index.blade.php']:
 s=read(path)
 if 'measurement-summary' not in s:
  old=(root/'storefront-feature-update/_staged'/path).read_text(encoding='utf-8')
  # The prior complete view already places the summary beside the item options.
  s=old
 put(path,s)
path='app/Models/Product.php';s=read(path)
start=s.index('    public function optionValues()');end=s.index('    public function variants()',start)
part=s[start:end].replace(')->withTimestamps();',")->withPivot('position')->orderByPivot('position')->orderBy('product_option_values.id')->withTimestamps();")
put(path,s[:start]+part+s[end:])
path='app/Http/Controllers/Admin/ProductController.php';s=read(path)
needle="                'product_option_values' => ["
s=s.replace(needle,"                'product_value_order' => ['nullable', 'array'],\n                'product_value_order.*' => ['integer', 'min:0', 'max:10000'],\n"+needle,1)
start=s.index('        $product->optionValues()->sync(',s.index('private function syncProductRelationships'))
end=s.index('\n    }',start)
s=s[:start]+'''        $valueIds = $this->collectProductOptionValueIds($validated);
        $positions = $validated['product_value_order'] ?? [];
        $sync = [];
        foreach ($valueIds as $index => $valueId) {
            $sync[$valueId] = ['position' => (int) ($positions[$valueId] ?? $index)];
        }
        $product->optionValues()->sync($sync);
'''+s[end:]
put(path,s)
put('database/migrations/2026_10_05_000001_add_product_value_order.php', '''<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('product_option_value_product', function(Blueprint $table){ $table->unsignedInteger('position')->default(0); }); }
 public function down(): void { Schema::table('product_option_value_product', function(Blueprint $table){ $table->dropColumn('position'); }); }
};
''')
path='resources/views/admin/products/partials/form.blade.php';s=read(path)
s=s.replace('@forelse($option->values as $value)', '''@php
 $savedValueOrder = old('product_value_order', $editing ? $product->optionValues->mapWithKeys(fn($v)=>[$v->id => $v->pivot->position ?? 0])->all() : []);
 $orderedValues = $option->values->sortBy(fn($v)=>$savedValueOrder[$v->id] ?? 10000);
@endphp
@forelse($orderedValues as $value)''',1)
s+='''
@include('admin.products.partials.value-order')
'''
put(path,s)
put('resources/views/admin/products/partials/value-order.blade.php','''<script>
document.addEventListener('DOMContentLoaded', () => {
 const list=document.getElementById('productOptionsList'); if(!list)return;
 function update(){
  list.querySelectorAll('.product-option-values').forEach(group=>{
   const labels=[...group.querySelectorAll('[data-option-value]')];
   labels.forEach((label,index)=>{
    const checkbox=label.querySelector('[data-option-value-checkbox]');if(!checkbox)return;
    let input=label.querySelector('[data-value-position]');
    if(!input){input=document.createElement('input');input.type='hidden';input.dataset.valuePosition='';input.name='product_value_order['+checkbox.value+']';label.append(input);}
    input.value=index;
    if(!label.querySelector('[data-move-value]')){
     for(const [direction,title] of [[-1,'Move earlier'],[1,'Move later']]){
      const button=document.createElement('button');button.type='button';button.dataset.moveValue=direction;button.textContent=direction<0?'↑':'↓';button.setAttribute('aria-label',title+' '+checkbox.dataset.valueLabel);button.className='az-value-move';label.append(button);
     }
    }
    label.querySelector('[data-move-value="-1"]').disabled=index===0;
    label.querySelector('[data-move-value="1"]').disabled=index===labels.length-1;
   });
  });
 }
 update();
 let scheduled=false;new MutationObserver(()=>{if(!scheduled){scheduled=true;queueMicrotask(()=>{scheduled=false;update();});}}).observe(list,{childList:true,subtree:true});
 list.addEventListener('click',event=>{const button=event.target.closest('[data-move-value]');if(!button)return;event.preventDefault();event.stopPropagation();const row=button.closest('[data-option-value]'),group=row.parentElement,rows=[...group.querySelectorAll('[data-option-value]')],index=rows.indexOf(row),next=rows[index+Number(button.dataset.moveValue)];if(next){Number(button.dataset.moveValue)<0?group.insertBefore(row,next):group.insertBefore(next,row);update();button.focus();}});
});
</script>
<p class="az-order-help">Use ↑ and ↓ beside a value to set its order for this product. Save the product to apply the order to its page and quick view. Shared attributes and values stay unchanged.</p>
''')
for path in ['resources/views/products/show.blade.php','resources/views/products/partials/quick-view.blade.php']:
 s=read(path)
 s=s.replace("@include('products.partials.custom-measurements')",'')
 # Place the fieldset after the complete set of size buttons, outside the button row.
 marker='''data-option-error="{{ $option->id }}"'''
 pos=s.index(marker);end=s.index('</div>',pos)+len('</div>')
 s=s[:end]+"\n@if(preg_match('/\\bsize\\b/i', $option->name))\n@include('products.partials.custom-measurements')\n@endif\n"+s[end:]
 put(path,s)
put('resources/views/products/partials/custom-measurements.blade.php','''<fieldset class="az-custom-measurements" data-custom-measurements hidden disabled>
<legend>Custom size · measurements in inches</legend>
<p>Enter your measurements for this item. These details will be saved with your order.</p>
<div class="az-measurement-grid">
@foreach(\\App\\Services\\CustomMeasurementsService::FIELDS as $measurementKey=>$measurementLabel)
<label>{{ $measurementLabel }} <span>inches</span><input type="number" name="custom_measurements[{{ $measurementKey }}]" min="0.1" max="200" step="0.01" required inputmode="decimal" value="{{ old('custom_measurements.'.$measurementKey) }}"></label>
@endforeach
</div></fieldset>
''')
path='resources/views/products/partials/size-chart.blade.php';s=read(path)
s+='''<style>
dialog.az-size-chart{position:fixed!important;inset:0!important;margin:auto!important;box-sizing:border-box;width:min(850px,calc(100vw - 32px));height:fit-content;max-height:88dvh;padding:24px;border:0;border-radius:20px;box-shadow:0 24px 100px #0005}
.az-size-chart [data-size-chart-close],.az-size-chart-tabs button{font-family:inherit;border-radius:999px;padding:12px 22px;letter-spacing:.08em;font-weight:600;border:1px solid #172033;background:#fff;color:#172033;cursor:pointer}
.az-size-chart-tabs [aria-selected=true],.az-size-chart [data-size-chart-close]{background:#172033;color:white}
.az-size-chart [data-size-chart-close]:hover,.az-size-chart-tabs button:hover{background:#287570;color:white}
.az-size-chart h2{font-size:24px;margin:0 0 24px}.az-size-chart img{border-radius:12px}
@media(max-width:600px){dialog.az-size-chart{padding:16px}.az-size-chart [data-size-chart-close]{padding:10px 14px}}
</style>'''
put(path,s)
path='public/asset/js/product.js';s=read(path)
s+='''
;(()=>{
 function sync(form){
  form.querySelectorAll('[data-custom-measurements]').forEach(fields=>{
   const group=fields.closest('.product-option-group'),select=group?.querySelector('.product-option-select');
   const custom=/\\bcustom\\b/i.test(select?.selectedOptions[0]?.dataset.label||select?.selectedOptions[0]?.textContent||'');
   fields.hidden=!custom;fields.disabled=!custom;
  });
 }
 function init(root){root.querySelectorAll('[data-product-form]').forEach(sync);if(root.matches?.('[data-product-form]'))sync(root);}
 document.addEventListener('DOMContentLoaded',()=>{init(document);new MutationObserver(records=>records.forEach(r=>r.addedNodes.forEach(n=>{if(n.nodeType===1)init(n);}))).observe(document.body,{childList:true,subtree:true});});
 document.addEventListener('change',event=>{const form=event.target.closest('[data-product-form]');if(form)sync(form);});
 document.addEventListener('click',event=>{const button=event.target.closest('[data-value-id]');if(button)setTimeout(()=>{const form=button.closest('[data-product-form]');if(form)sync(form);},0);});
})();
'''
put(path,s)
path='resources/views/layouts/app.blade.php';s=read(path);s=s.replace('</head>',"<link rel=\"stylesheet\" href=\"{{ asset('asset/css/arizona-commerce-ui.css') }}\">\n</head>",1);put(path,s)
put('public/asset/css/arizona-commerce-ui.css','''.az-custom-measurements{clear:both;width:100%;box-sizing:border-box;margin:18px 0 0;padding:20px;border:1px solid #d7e4e3;border-radius:16px;background:#f4f9f8}.az-custom-measurements[hidden]{display:none!important}.az-custom-measurements legend{padding:0 8px;font-size:14px;font-weight:700}.az-custom-measurements p{font-size:13px;line-height:1.6;color:#536362}.az-measurement-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.az-measurement-grid label{font-size:13px;font-weight:600}.az-measurement-grid label span{font-weight:400;color:#657674}.az-measurement-grid input{display:block;width:100%;box-sizing:border-box;padding:12px;margin-top:7px;border:1px solid #bacdca;border-radius:9px;background:white;color:#172033}.custom-size-summary{margin:10px 0;padding:12px;background:#f1f7f6;border-radius:10px;font-size:12px}.custom-size-summary div{display:flex;gap:12px;justify-content:space-between}.custom-size-summary dt{font-weight:500}.custom-size-summary dd{margin:0}@media(max-width:480px){.az-measurement-grid{grid-template-columns:1fr}}''')
path='app/Http/Controllers/Admin/AdminBackupController.php';put(path,read(path).replace('in:database,full','in:database,full,website'))
path='app/Services/AdminBackupService.php';s=read(path).replace("['database','full']","['database','full','website']").replace("$type === 'full' ? 'zip'", "$type !== 'database' ? 'zip'")
s=s.replace("if ($type === 'full') {", "if ($type !== 'database') {")
s=s.replace('''                    $temporarySql
                );''','''                    $temporarySql,
                    $type === 'website'
                );''',1)
s=s.replace('''        string $databaseDump
    ): void''','''        string $databaseDump,
        bool $website = false
    ): void''',1)
s=s.replace("        $zip->addFile(\n            $databaseDump,\n            'database/database.sql.gz'\n        );", "        if (!$zip->addFile($databaseDump, 'database/database.sql.gz')) throw new RuntimeException('Unable to add the database dump.');\n        if ($website) $this->addWebsite($zip, base_path(), 'website');")
s=s.replace('''        $this->addDirectory(
            $zip,
            storage_path('app/public'),''','''        if (!$website) $this->addDirectory(
            $zip,
            storage_path('app/public'),''',1)
s=s.replace("        $this->addDirectory($zip, Storage::disk('local')->path('suppliers')", "        if (!$website) $this->addDirectory($zip, Storage::disk('local')->path('suppliers')",1)
s=s.replace('''        $this->addDirectory(
            $zip,
            public_path('uploads'),''','''        if (!$website) $this->addDirectory(
            $zip,
            public_path('uploads'),''',1)
s=s.replace('''            "Database: decompress''','''            ($website ? "Website: copy website/ into your destination project. Includes .env and private files; keep this archive private. Recreate public/storage with artisan storage:link.\\r\\n" : "") .
            "Database: decompress''',1)
pos=s.index('    private function addDirectory(')
s=s[:pos]+'''    private function addWebsite(ZipArchive $zip, string $directory, string $archiveRoot): void
    {
        $root = realpath($directory);
        if ($root === false) throw new RuntimeException('Website directory is missing.');
        $excluded = ['.git', 'node_modules', 'storage/app/admin-backups', 'storage/app/private/admin-backups', 'storage/framework', 'storage/logs', 'bootstrap/cache'];
        $iterator = new \\RecursiveIteratorIterator(new \\RecursiveCallbackFilterIterator(
            new \\RecursiveDirectoryIterator($root, \\FilesystemIterator::SKIP_DOTS),
            function ($entry) use ($root, $excluded) {
                if ($entry->isLink()) return false;
                $relative = str_replace('\\\\', '/', substr($entry->getPathname(), strlen($root) + 1));
                foreach ($excluded as $prefix) if ($relative === $prefix || str_starts_with($relative, $prefix.'/')) return false;
                return true;
            }
        ));
        foreach ($iterator as $entry) {
            if (!$entry->isFile()) continue;
            $relative = str_replace('\\\\', '/', substr($entry->getPathname(), strlen($root) + 1));
            if (!$zip->addFile($entry->getPathname(), $archiveRoot.'/'.$relative)) throw new RuntimeException('Unable to add website file: '.$relative);
        }
    }

'''+s[pos:]
put(path,s)
path='resources/views/admin/backups/index.blade.php';s=read(path)
needle='<div class="backup-warning">'
s=s.replace(needle,'''<label class="backup-option"><input type="radio" name="type" value="website"><span class="option-icon"><i class="fa-solid fa-globe"></i></span><span><strong>Complete website backup</strong><small>Database, application code, vendor dependencies, configuration (.env), public assets, uploads and private files. Excludes Git, node_modules, runtime caches/logs and existing backups. Contains sensitive settings: store privately.</small></span></label>
                '''+needle,1)
put(path,s)
path='resources/views/admin/layouts/app.blade.php';s=read(path).replace("    @stack('page-styles')","    @stack('page-styles')\n    <link rel=\"stylesheet\" href=\"{{ asset('asset/css/arizona-admin-controls.css') }}\">")
s=s.replace("    @stack('page-scripts')","    @stack('page-scripts')\n    <script src=\"{{ asset('asset/js/arizona-admin-controls.js') }}\"></script>")
put(path,s)
(base/'changed.json').write_text(json.dumps(changed,indent=2))
print('Staged',len(changed),'files')
