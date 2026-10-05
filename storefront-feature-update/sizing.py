from pathlib import Path
import re,json
ROOT=Path(__file__).resolve().parents[1];STAGE=ROOT/'storefront-feature-update'/'_staged';changed=json.loads((STAGE.parent/'changed.json').read_text())
def read(p):return ((STAGE/p) if (STAGE/p).exists() else ROOT/p).read_text(encoding='utf-8-sig')
def write(p,s):
 q=STAGE/p;q.parent.mkdir(parents=True,exist_ok=True);q.write_text(s,encoding='utf-8')
 if p not in changed:changed.append(p)
write('app/Services/CustomMeasurementsService.php','''<?php
namespace App\\Services;
use Illuminate\\Support\\Facades\\Validator;
class CustomMeasurementsService
{
 public const FIELDS = ['chest'=>'Chest','waist'=>'Waist','shoulder'=>'Shoulder','sleeve_length'=>'Sleeve length','body_length'=>'Jacket/body length'];
 public function required(array $options): bool {
  foreach($options as $option) {
   if(preg_match('/\\bsize\\b/i',(string)($option['option_name']??'')) && preg_match('/\\bcustom\\b/i',(string)($option['value_label']??''))) return true;
  }
  return false;
 }
 public function validate(array $options, mixed $measurements): array {
  if(!$this->required($options)) return [];
  $rules=['custom_measurements'=>'required|array'];
  foreach(self::FIELDS as $key=>$label) $rules['custom_measurements.'.$key]='required|numeric|min:0.1|max:200';
  $validated=Validator::make(['custom_measurements'=>$measurements],$rules)->validate();
  $result=[];
  foreach(self::FIELDS as $key=>$label) $result[$key]=number_format((float)$validated['custom_measurements'][$key],2,'.','');
  return $result;
 }
 public function orderOptions(array $options, mixed $measurements): array {
  foreach($this->validate($options,$measurements) as $key=>$value) $options[]=['option_name'=>self::FIELDS[$key].' (inches)','value_label'=>$value];
  return $options;
 }
}
''')
s=read('app/Http/Controllers/CartController.php');needle='        $cartKey = $this->createCartKey('
s=s.replace(needle,"        $customMeasurements = app(\\App\\Services\\CustomMeasurementsService::class)->validate($selectedOptions, $request->input('custom_measurements'));\n\n"+needle,1)
m=re.search(r'\$cartKey = \$this->createCartKey\(.*?\);',s,re.S);assert m
s=s[:m.end()]+"\n        if ($customMeasurements) $cartKey .= '_'.substr(hash('sha256', json_encode($customMeasurements)), 0, 20);\n"+s[m.end():]
s=s.replace("'options' => $selectedOptions,","'options' => $selectedOptions,\n                'custom_measurements' => $customMeasurements,",1);write('app/Http/Controllers/CartController.php',s)
s=read('app/Http/Controllers/CheckoutController.php');s=re.sub(r"'options' =>\s*is_array\(\s*\$item\['options'\]\s*\?\? null\s*\)\s*\? \$item\['options'\]\s*: \[\],","'options' => app(\\\\App\\\\Services\\\\CustomMeasurementsService::class)->orderOptions((array) ($item['options'] ?? []), $item['custom_measurements'] ?? []),",s,count=1)
assert '->orderOptions(' in s
write('app/Http/Controllers/CheckoutController.php',s)
write('resources/views/products/partials/custom-measurements.blade.php','''<fieldset data-custom-measurements hidden disabled><legend>Custom size measurements (inches)</legend>
@foreach(\\App\\Services\\CustomMeasurementsService::FIELDS as $measurementKey=>$measurementLabel)
<label style="display:block;margin:10px 0">{{ $measurementLabel }} — inches <input type="number" name="custom_measurements[{{ $measurementKey }}]" min="0.1" max="200" step="0.01" required inputmode="decimal" value="{{ old('custom_measurements.'.$measurementKey) }}" style="display:block;width:100%;padding:10px"></label>
@endforeach
</fieldset>''')
for p in ['resources/views/products/show.blade.php','resources/views/products/partials/quick-view.blade.php']:
 s=read(p);m=re.search(r'<form\b[^>]*data-product-form[^>]*>',s,re.S);assert m;s=s[:m.end()]+"\n@include('products.partials.custom-measurements')\n"+s[m.end():];write(p,s)
s=(ROOT/'storefront-feature-update/product-readable.js').read_text()
s=s.replace('    N(t);\n    const n = Array.from', '''    if (e.length === 1 && S(e[0])) {
      const onlyOptions = M(e[0]);
      t.querySelectorAll('.product-option-select').forEach(function(select) {
        if (!select.value) select.value = onlyOptions[String(select.dataset.optionId || select.name.match(/\\[(.*?)\\]/)?.[1])] || '';
      });
    }
    customSizing(t);
    N(t);
    const n = Array.from''',1)
s=s.replace('L(t, "Please select one value from every option.", false), E(t, false);','L(t, "Please select one value from every option.", false), E(t, e.some(S));')
s=s.replace('u.disabled = true, l.textContent = "Select Options";','u.disabled = false, l.textContent = d;')
at=s.index('  function I(t)')
s=s[:at]+'''  function customSizing(form) {
    const fields = form.querySelector('[data-custom-measurements]');
    if (!fields) return;
    const needed = Array.from(form.querySelectorAll('.product-option-select')).some(select => {
      const name = select.getAttribute('aria-label') || select.closest('.product-option-group')?.textContent || '';
      const label = select.selectedOptions[0]?.dataset.label || select.selectedOptions[0]?.textContent || '';
      return /\\bsize\\b/i.test(name) && /\\bcustom\\b/i.test(label);
    });
    fields.hidden = !needed;
    fields.disabled = !needed;
  }
'''+s[at:];write('public/asset/js/product.js',s)
# Measurements remain separate from variant IDs, and are displayed in every cart summary.
write('resources/views/products/partials/measurement-summary.blade.php','''@if(!empty($item['custom_measurements']))
<dl class="custom-size-summary">@foreach($item['custom_measurements'] as $measurementKey=>$measurementValue)<div><dt>{{ \\App\\Services\\CustomMeasurementsService::FIELDS[$measurementKey] ?? $measurementKey }} (inches)</dt><dd>{{ $measurementValue }}</dd></div>@endforeach</dl>
@endif''')
for path in [ROOT/'resources/views/cart/index.blade.php',ROOT/'resources/views/checkout/index.blade.php']:
 if not path.exists():continue
 p=path.relative_to(ROOT).as_posix();s=read(p)
 # Add beside the quantity/price area within each item loop rather than outside it.
 match=re.search(r'@foreach\s*\([^\n]*\bas \$cartKey\s*=>\s*\$item\)',s)
 if match:s=s[:match.end()]+"\n@include('products.partials.measurement-summary')\n"+s[match.end():];write(p,s)
s=read('resources/views/partials/header-menu-links.blade.php');s=s.replace("            @forelse ($headerCart as $cartKey => $item)","            @forelse ($headerCart as $cartKey => $item)\n@include('products.partials.measurement-summary')");write('resources/views/partials/header-menu-links.blade.php',s)
(STAGE.parent/'changed.json').write_text(json.dumps(changed,indent=2));print('Prepared',len(changed),'files')
