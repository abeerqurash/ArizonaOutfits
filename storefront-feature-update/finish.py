from pathlib import Path
import json,re
ROOT=Path(__file__).resolve().parents[1];STAGE=ROOT/'storefront-feature-update'/'_staged';changed=json.loads((STAGE.parent/'changed.json').read_text())
def read(p):return ((STAGE/p) if (STAGE/p).exists() else ROOT/p).read_text(encoding='utf-8-sig')
def write(p,s):
 q=STAGE/p;q.parent.mkdir(parents=True,exist_ok=True);q.write_text(s,encoding='utf-8')
 if p not in changed:changed.append(p)
s=read('resources/views/products/show.blade.php');s=re.sub(r'\s*@if\s*\(!empty\(\$review->title\)\).*?@endif','',s,flags=re.S);s=re.sub(r'\s*<!-- <p>\s*<strong>Purchases:</strong>.*?</p> -->','',s,flags=re.S);write('resources/views/products/show.blade.php',s)
for p in ['resources/views/cart/index.blade.php','resources/views/checkout/index.blade.php']:
 s=read(p);s=re.sub(r'(@(?:foreach|forelse)\s*\(\s*\$cart\s+as\s+(?:\$cartKey\s*=>\s*)?\$item\s*\))',r"\1\n@include('products.partials.measurement-summary')",s);write(p,s)
s=read('app/Http/Controllers/CmsPageController.php');s=s.replace("app(BlogController::class)->show($slug);\n        return", "$response = app(BlogController::class)->show($slug);\n        if ($response instanceof \\Illuminate\\Http\\RedirectResponse) return $response;\n        return");write('app/Http/Controllers/CmsPageController.php',s)
write('config/size_charts.php','''<?php
// Enter your verified garment measurements in inches. No generic sizing is assumed.
return [
 'men' => ['S'=>[], 'M'=>[], 'L'=>[], 'XL'=>[]],
 'women' => ['S'=>[], 'M'=>[], 'L'=>[], 'XL'=>[]],
];
''')
write('resources/views/products/partials/size-chart.blade.php','''<button type="button" class="size-chart-link" data-size-chart-open>Size chart</button>
<dialog class="az-size-chart" aria-label="Size charts"><button type="button" data-size-chart-close aria-label="Close size charts">Close ×</button><h2>Size charts</h2><p>All measurements are in inches.</p>
@foreach(['men'=>'Men','women'=>'Women'] as $chartKey=>$chartLabel)
<section><h3>{{ $chartLabel }}</h3><div style="overflow:auto"><table><thead><tr><th>Size</th>@foreach(\\App\\Services\\CustomMeasurementsService::FIELDS as $label)<th>{{ $label }}</th>@endforeach</tr></thead><tbody>
@foreach(config('size_charts.'.$chartKey,[]) as $size=>$measurements)<tr><th>{{ $size }}</th>@foreach(\\App\\Services\\CustomMeasurementsService::FIELDS as $key=>$label)<td>{{ $measurements[$key] ?? '—' }}</td>@endforeach</tr>@endforeach
</tbody></table></div></section>
@endforeach
<p>For a custom size, select Custom and enter your measurements. Standard size measurements will be listed here once confirmed.</p></dialog>
<style>.size-chart-link{cursor:pointer;padding:10px 0;text-decoration:underline;color:#172033;background:none;border:0}.az-size-chart{max-width:850px;width:calc(100% - 40px);max-height:85dvh;overflow:auto;padding:24px;border:1px solid #d5dbea;border-radius:12px}.az-size-chart::backdrop{background:#0008}.az-size-chart table{border-collapse:collapse;width:100%}.az-size-chart th,.az-size-chart td{padding:12px;border:1px solid #d5dbea;text-align:left}.az-size-chart [data-size-chart-close]{float:right;padding:10px;cursor:pointer}</style>
<script>document.addEventListener('click',function(event){if(event.target.closest('[data-size-chart-open]'))document.querySelector('.az-size-chart')?.showModal();if(event.target.closest('[data-size-chart-close]'))event.target.closest('dialog').close();});</script>
''')
s=read('resources/views/products/show.blade.php');m=re.search(r'<div class="single-product-short-description[^>]*>.*?</div>',s,re.S);assert m;s=s[:m.end()]+"\n@include('products.partials.size-chart')\n"+s[m.end():];write('resources/views/products/show.blade.php',s)
# Ensure the custom fieldset always follows the option selects in keyboard order.
for p in ['resources/views/products/show.blade.php','resources/views/products/partials/quick-view.blade.php']:
 s=read(p);s=s.replace("\n@include('products.partials.custom-measurements')\n",'\n');m=re.search(r'<form\b[^>]*data-product-form[^>]*>',s,re.S);assert m;end=s.index('</form>',m.end());s=s[:end]+"@include('products.partials.custom-measurements')\n"+s[end:];write(p,s)
(STAGE.parent/'changed.json').write_text(json.dumps(changed,indent=2));print('Prepared',len(changed),'files')
