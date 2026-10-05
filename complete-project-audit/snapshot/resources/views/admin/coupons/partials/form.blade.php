@php
    $editing = isset($coupon) && $coupon;
    $selectedProducts = array_map('intval', old('product_ids', $editing ? ($coupon->product_ids ?? []) : []));
    $selectedCategories = array_map('intval', old('category_ids', $editing ? ($coupon->category_ids ?? []) : []));
    $selectedVariants = array_map('intval', old('variant_ids', $editing ? ($coupon->variant_ids ?? []) : []));
    $targetType = old('target_type', $editing ? ($coupon->target_type ?? 'all') : 'all');
@endphp

@if($errors->any())
<div class="coupon-popup" id="couponValidationPopup" role="dialog" aria-modal="true">
    <div class="coupon-popup-bg" data-coupon-validation-close></div>
    <div class="coupon-popup-box">
        <button type="button" class="coupon-popup-x" data-coupon-validation-close><i class="fa-solid fa-xmark"></i></button>
        <div class="coupon-popup-icon is-error"><i class="fa-solid fa-circle-exclamation"></i></div>
        <span class="admin-page-eyebrow">Coupon validation</span>
        <h3>Please correct these details</h3>
        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        <button type="button" class="admin-button admin-button-primary" data-coupon-validation-close>Review Fields</button>
    </div>
</div>
@endif

<div class="coupon-form-grid">
    <div class="coupon-form-main">
        <section class="admin-panel coupon-panel">
            <div class="coupon-panel-head"><div><span class="admin-page-eyebrow">Coupon identity</span><h3>Code & Discount</h3><p>Define the customer-facing code and discount calculation.</p></div><span class="coupon-section-icon"><i class="fa-solid fa-ticket"></i></span></div>
            <div class="coupon-fields two">
                <div class="coupon-field">
                    <label for="code">Coupon Code <b>*</b></label>
                    <div class="coupon-code-row">
                        <input id="code" name="code" value="{{ old('code', $editing ? $coupon->code : '') }}" maxlength="100" required placeholder="SUMMER25" autocomplete="off">
                        <button type="button" class="admin-button coupon-generate" id="generateCouponCode"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate</button>
                    </div>
                    <small>Letters and numbers are normalized to an uppercase coupon code.</small>
                </div>
                <div class="coupon-field">
                    <label for="type">Discount Type <b>*</b></label>
                    <select id="type" name="type" required>
                        <option value="fixed" @selected(old('type', $editing ? $coupon->type : 'fixed') === 'fixed')>Fixed Amount</option>
                        <option value="percentage" @selected(old('type', $editing ? $coupon->type : 'fixed') === 'percentage')>Percentage</option>
                    </select>
                </div>
                <div class="coupon-field">
                    <label for="value">Discount Value <b>*</b></label>
                    <input id="value" type="number" min="0.01" step="0.01" name="value" value="{{ old('value', $editing ? $coupon->value : '') }}" required placeholder="10.00">
                </div>
                <div class="coupon-field">
                    <label for="maximum_discount">Maximum Discount</label>
                    <input id="maximum_discount" type="number" min="0.01" step="0.01" name="maximum_discount" value="{{ old('maximum_discount', $editing ? $coupon->maximum_discount : '') }}" placeholder="No maximum">
                    <small>Especially useful for percentage coupons.</small>
                </div>
            </div>
        </section>

        <section class="admin-panel coupon-panel">
            <div class="coupon-panel-head"><div><span class="admin-page-eyebrow">Validity timer</span><h3>Start & Expiration</h3><p>Set exact date and time. Leave either side blank for an open-ended period.</p></div><span class="coupon-section-icon"><i class="fa-regular fa-clock"></i></span></div>
            <div class="coupon-fields two">
                <div class="coupon-field">
                    <label for="start_date">Starts At</label>
                    <input id="start_date" type="datetime-local" name="start_date" value="{{ old('start_date', $editing && $coupon->start_date ? $coupon->start_date->format('Y-m-d\TH:i') : '') }}">
                </div>
                <div class="coupon-field">
                    <label for="end_date">Expires At</label>
                    <input id="end_date" type="datetime-local" name="end_date" value="{{ old('end_date', $editing && $coupon->end_date ? $coupon->end_date->format('Y-m-d\TH:i') : '') }}">
                </div>
            </div>
            <div class="coupon-timer-preview" id="couponTimerPreview"><i class="fa-solid fa-hourglass-half"></i><span>Open-ended validity</span></div>
        </section>

        <section class="admin-panel coupon-panel">
            <div class="coupon-panel-head"><div><span class="admin-page-eyebrow">Eligibility</span><h3>Order & Usage Rules</h3><p>Control minimum spend and how often this coupon can be redeemed.</p></div><span class="coupon-section-icon"><i class="fa-solid fa-shield-halved"></i></span></div>
            <div class="coupon-fields three">
                <div class="coupon-field"><label for="minimum_order_amount">Minimum Order</label><input id="minimum_order_amount" type="number" min="0" step="0.01" name="minimum_order_amount" value="{{ old('minimum_order_amount', $editing ? $coupon->minimum_order_amount : 0) }}"></div>
                <div class="coupon-field"><label for="usage_limit">Total Usage Limit</label><input id="usage_limit" type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $editing ? $coupon->usage_limit : '') }}" placeholder="Unlimited"></div>
                <div class="coupon-field"><label for="per_user_usage_limit">Per-user Limit</label><input id="per_user_usage_limit" type="number" min="1" name="per_user_usage_limit" value="{{ old('per_user_usage_limit', $editing ? $coupon->per_user_usage_limit : '') }}" placeholder="Unlimited"></div>
            </div>
        </section>

        <section class="admin-panel coupon-panel">
            <div class="coupon-panel-head"><div><span class="admin-page-eyebrow">Targeting</span><h3>Products, Categories & Variations</h3><p>Choose exactly what the coupon can discount.</p></div><span class="coupon-section-icon"><i class="fa-solid fa-bullseye"></i></span></div>
            <div class="coupon-field coupon-target-field">
                <label for="target_type">Target <b>*</b></label>
                <select id="target_type" name="target_type" required>
                    <option value="all" @selected($targetType === 'all')>All Products</option>
                    <option value="products" @selected($targetType === 'products')>Specific Products</option>
                    <option value="categories" @selected($targetType === 'categories')>Specific Categories</option>
                    <option value="variants" @selected($targetType === 'variants')>Specific Variations</option>
                </select>
            </div>

            <div class="coupon-target-box" data-target-box="products">
                <div class="coupon-choice-head"><strong>Select Products</strong><input type="search" placeholder="Search products..." data-choice-search="products"></div>
                <div class="coupon-choice-list" data-choice-list="products">
                    @foreach($products as $product)
                    <label class="coupon-choice-item" data-choice-text="{{ strtolower($product->title . ' ' . ($product->sku ?? '')) }}">
                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" @checked(in_array((int)$product->id, $selectedProducts, true))>
                        <span class="coupon-check"></span><span><strong>{{ $product->title }}</strong><small>{{ $product->sku ?: 'No SKU' }}</small></span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="coupon-target-box" data-target-box="categories">
                <div class="coupon-choice-head"><strong>Select Categories</strong><input type="search" placeholder="Search categories..." data-choice-search="categories"></div>
                <label class="coupon-switch-line"><input type="hidden" name="include_child_categories" value="0"><input type="checkbox" name="include_child_categories" value="1" @checked(old('include_child_categories', $editing ? $coupon->include_child_categories : true))><span class="coupon-switch"></span><span>Include child categories</span></label>
                <div class="coupon-choice-list" data-choice-list="categories">
                    @foreach($categories as $category)
                    <label class="coupon-choice-item" data-choice-text="{{ strtolower($category->title) }}">
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array((int)$category->id, $selectedCategories, true))>
                        <span class="coupon-check"></span><span><strong>{{ $category->title }}</strong><small>{{ $category->parent_id ? 'Child category' : 'Parent category' }}</small></span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="coupon-target-box" data-target-box="variants">
                <div class="coupon-choice-head"><strong>Select Variations</strong><input type="search" placeholder="Search variation / SKU..." data-choice-search="variants"></div>
                <div class="coupon-choice-list" data-choice-list="variants">
                    @foreach($variants as $variant)
                    @php
                        $optionLabel = collect($variant->options ?? [])->map(fn($v,$k) => is_string($k) ? $k . ': ' . $v : $v)->implode(' / ');
                    @endphp
                    <label class="coupon-choice-item" data-choice-text="{{ strtolower(($variant->product?->title ?? '') . ' ' . ($variant->sku ?? '') . ' ' . $optionLabel) }}">
                        <input type="checkbox" name="variant_ids[]" value="{{ $variant->id }}" @checked(in_array((int)$variant->id, $selectedVariants, true))>
                        <span class="coupon-check"></span><span><strong>{{ $variant->product?->title ?? 'Product' }} — {{ $optionLabel ?: 'Variant #' . $variant->id }}</strong><small>{{ $variant->sku ?: 'No SKU' }}</small></span>
                    </label>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="admin-panel coupon-panel">
            <div class="coupon-panel-head"><div><span class="admin-page-eyebrow">Campaign</span><h3>Promotion Event</h3><p>Optionally associate this coupon with a campaign or event.</p></div><span class="coupon-section-icon"><i class="fa-solid fa-bullhorn"></i></span></div>
            <div class="coupon-field">
                <label for="event_name">Event / Campaign</label>
                <input id="event_name" name="event_name" list="coupon-event-suggestions" value="{{ old('event_name', $editing ? $coupon->event_name : '') }}" maxlength="120" placeholder="e.g. Black Friday">
                <datalist id="coupon-event-suggestions"><option value="Black Friday"><option value="Seasonal Offer"><option value="Limited-time Promotion"><option value="Summer Sale"><option value="Winter Sale"></datalist>
                <small>You can type a custom event name.</small>
            </div>
        </section>
    </div>

    <aside class="admin-panel coupon-side">
        <span class="admin-page-eyebrow">Publishing</span>
        <div class="coupon-status-card"><span>Status</span><label class="coupon-switch-line"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" @checked(old('status', $editing ? $coupon->status : true))><span class="coupon-switch"></span><strong>Active</strong></label></div>
        @if($editing)
        <div class="coupon-summary"><div><span>Used</span><strong>{{ number_format((int)$coupon->used_count) }}</strong></div><div><span>Runtime</span><strong>{{ $coupon->runtime_status_label }}</strong></div><div><span>Created</span><strong>{{ $coupon->created_at?->format('M d, Y') }}</strong></div></div>
        @endif
        <button type="submit" class="admin-button admin-button-primary coupon-save"><i class="fa-solid fa-floppy-disk"></i>{{ $editing ? 'Save Coupon' : 'Create Coupon' }}</button>
        <a href="{{ route('admin.coupons.index') }}" class="admin-button admin-button-secondary coupon-cancel">Cancel</a>
    </aside>
</div>

@push('page-styles')
<style>
.coupon-form-grid{display:grid;grid-template-columns:minmax(0,1fr) 275px;gap:16px;align-items:start}.coupon-form-main{display:grid;gap:16px}.coupon-panel,.coupon-side{border:1px solid #e6eaf1!important;border-radius:11px!important;background:#fff!important;box-shadow:none!important}.coupon-panel{padding:19px}.coupon-side{position:sticky;top:18px;padding:18px}.coupon-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;padding-bottom:15px;margin-bottom:16px;border-bottom:1px solid #edf0f4}.coupon-panel-head h3{margin:3px 0;color:#172033;font-size:15px;font-weight:800}.coupon-panel-head p{margin:4px 0 0;color:#7b8497;font-size:10px}.coupon-section-icon{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:9px;background:#eeedff;color:#635bff}.coupon-fields{display:grid;gap:14px}.coupon-fields.two{grid-template-columns:repeat(2,minmax(0,1fr))}.coupon-fields.three{grid-template-columns:repeat(3,minmax(0,1fr))}.coupon-field label{display:block;margin-bottom:7px;color:#667085;font-size:9px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.coupon-field label b{color:#d14343}.coupon-field input,.coupon-field select{width:100%;min-height:41px;padding:0 11px;border:1px solid #e0e5ed;border-radius:7px;background:#fff;color:#344054;font-size:11px}.coupon-field input:focus{border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,.10);outline:none}.coupon-field small{display:block;margin-top:6px;color:#98a2b3;font-size:9px}.coupon-code-row{display:grid;grid-template-columns:1fr auto;gap:7px}.coupon-generate{min-height:41px;white-space:nowrap}.coupon-timer-preview{display:flex;align-items:center;gap:8px;margin-top:13px;padding:10px 12px;border:1px solid #e2e1ff;border-radius:7px;background:#f7f6ff;color:#635bff;font-size:10px;font-weight:700}.coupon-target-field{max-width:330px}.coupon-target-box{display:none;margin-top:14px;padding:13px;border:1px solid #e6eaf1;border-radius:9px;background:#fafbfc}.coupon-target-box.active{display:block}.coupon-choice-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px}.coupon-choice-head strong{font-size:10px;color:#344054}.coupon-choice-head input{width:240px;min-height:34px;padding:0 9px;border:1px solid #e0e5ed;border-radius:6px;font-size:10px}.coupon-choice-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;max-height:280px;overflow:auto}.coupon-choice-item{display:flex!important;align-items:center;gap:9px;margin:0!important;padding:9px;border:1px solid #e6eaf1;border-radius:7px;background:#fff;cursor:pointer;text-transform:none!important;letter-spacing:0!important}.coupon-choice-item:hover{border-color:#c9c6ff;background:#f8f7ff}.coupon-choice-item>input{position:absolute;opacity:0;pointer-events:none}.coupon-check{display:flex;flex:0 0 auto;align-items:center;justify-content:center;width:17px;height:17px;border:1px solid #cfd5df;border-radius:5px;background:#fff}.coupon-choice-item>input:checked+.coupon-check{border-color:#635bff;background:#635bff}.coupon-choice-item>input:checked+.coupon-check:after{content:"\2713";color:#fff;font-size:10px;font-weight:900}.coupon-choice-item strong{display:block;color:#344054;font-size:10px}.coupon-choice-item small{display:block;margin-top:2px;color:#98a2b3;font-size:8px}.coupon-switch-line{display:flex!important;align-items:center;gap:8px;margin:9px 0!important;text-transform:none!important;letter-spacing:0!important;cursor:pointer}.coupon-switch-line input[type=checkbox]{position:absolute;opacity:0;pointer-events:none}.coupon-switch{position:relative;width:32px;height:18px;border-radius:999px;background:#d8dde6;transition:.18s}.coupon-switch:after{content:"";position:absolute;top:3px;left:3px;width:12px;height:12px;border-radius:50%;background:#fff;transition:.18s}.coupon-switch-line input:checked~.coupon-switch{background:#635bff}.coupon-switch-line input:checked~.coupon-switch:after{transform:translateX(14px)}.coupon-status-card{margin:12px 0;padding:12px;border:1px solid #e6eaf1;border-radius:8px}.coupon-status-card>span{display:block;margin-bottom:7px;color:#98a2b3;font-size:8px;font-weight:800;text-transform:uppercase}.coupon-summary{margin:12px 0}.coupon-summary>div{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #edf0f4;font-size:9px}.coupon-summary span{color:#7b8497}.coupon-summary strong{color:#172033}.coupon-save,.coupon-cancel{width:100%;min-height:40px;justify-content:center;margin-top:8px}.coupon-popup{position:fixed;inset:0;z-index:14000;display:flex;align-items:center;justify-content:center;padding:20px}.coupon-popup-bg{position:absolute;inset:0;background:rgba(15,23,42,.58);backdrop-filter:blur(3px)}.coupon-popup-box{position:relative;z-index:2;width:100%;max-width:460px;padding:30px;border-radius:14px;background:#fff;box-shadow:0 24px 80px rgba(15,23,42,.24);text-align:center}.coupon-popup-x{position:absolute;right:12px;top:12px;width:32px;height:32px;border:0;border-radius:7px;background:#f3f5f8;color:#687386}.coupon-popup-icon{display:flex;align-items:center;justify-content:center;width:58px;height:58px;margin:0 auto 15px;border-radius:50%;font-size:20px}.coupon-popup-icon.is-error{background:#fff0f0;color:#d14343}.coupon-popup-box h3{margin:5px 0 12px;color:#172033;font-size:18px}.coupon-popup-box ul{margin:0 0 18px;padding:12px 16px;border:1px solid #fecaca;border-radius:9px;background:#fff7f7;color:#b42318;font-size:10px;text-align:left}
.az-coupon-select{position:relative}.az-coupon-native{position:absolute!important;width:1px!important;height:1px!important;opacity:0!important;pointer-events:none!important}.az-coupon-btn{display:flex;align-items:center;justify-content:space-between;width:100%;min-height:41px;padding:0 11px;border:1px solid #e0e5ed;border-radius:7px;background:#fff;color:#344054;font-size:11px;font-weight:600;cursor:pointer}.az-coupon-select.open .az-coupon-btn,.az-coupon-btn:focus{border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,.10);outline:none}.az-coupon-menu{position:absolute;z-index:13000;top:calc(100% + 6px);left:0;display:none;width:100%;padding:5px;border:1px solid #e0e5ed;border-radius:9px;background:#fff;box-shadow:0 14px 38px rgba(15,23,42,.16)}.az-coupon-select.open .az-coupon-menu{display:block}.az-coupon-option{display:flex;justify-content:space-between;width:100%;padding:8px 9px;border:0;border-radius:6px;background:transparent;color:#344054;font-size:11px;font-weight:600;text-align:left;cursor:pointer}.az-coupon-option:hover,.az-coupon-option.selected{background:#eeedff;color:#5149d8}.az-coupon-option.selected:after{content:"\2713";color:#635bff}
@media(max-width:950px){.coupon-form-grid{grid-template-columns:1fr}.coupon-side{position:static}}@media(max-width:650px){.coupon-fields.two,.coupon-fields.three,.coupon-choice-list{grid-template-columns:1fr}.coupon-choice-head{align-items:stretch;flex-direction:column}.coupon-choice-head input{width:100%}.coupon-code-row{grid-template-columns:1fr}}
</style>
@endpush

@push('page-scripts')
<script>
'use strict';
document.addEventListener('DOMContentLoaded',function(){
 document.querySelectorAll('.coupon-field select:not([multiple])').forEach(function(s){
  const w=document.createElement('div');w.className='az-coupon-select';const b=document.createElement('button');b.type='button';b.className='az-coupon-btn';const v=document.createElement('span'),i=document.createElement('i');i.className='fa-solid fa-chevron-down';b.append(v,i);const m=document.createElement('div');m.className='az-coupon-menu';s.parentNode.insertBefore(w,s);w.append(s,b,m);s.classList.add('az-coupon-native');
  function render(){m.innerHTML='';Array.from(s.options).forEach(o=>{const x=document.createElement('button');x.type='button';x.className='az-coupon-option'+(String(o.value)===String(s.value)?' selected':'');x.textContent=o.textContent.trim();x.onclick=()=>{s.value=o.value;s.dispatchEvent(new Event('change',{bubbles:true}));render();w.classList.remove('open');b.focus()};m.append(x)});const o=s.options[s.selectedIndex];v.textContent=o?o.textContent.trim():'Select'}
  b.onclick=()=>w.classList.toggle('open');document.addEventListener('click',e=>{if(!w.contains(e.target))w.classList.remove('open')});render();
 });
 const code=document.getElementById('code'),gen=document.getElementById('generateCouponCode');
 if(gen&&code)gen.onclick=()=>{const prefix=['ARIZONA','STYLE','VIP','SAVE','JACKET'][Math.floor(Math.random()*5)];const token=Math.random().toString(36).slice(2,8).toUpperCase();code.value=prefix+token};
 const target=document.getElementById('target_type');
 function showTarget(){document.querySelectorAll('[data-target-box]').forEach(x=>x.classList.toggle('active',x.dataset.targetBox===target.value))}
 if(target){target.addEventListener('change',showTarget);showTarget()}
 document.querySelectorAll('[data-choice-search]').forEach(input=>input.addEventListener('input',function(){const q=input.value.toLowerCase().trim(),list=document.querySelector('[data-choice-list="'+input.dataset.choiceSearch+'"]');if(list)list.querySelectorAll('.coupon-choice-item').forEach(x=>x.style.display=!q||x.dataset.choiceText.includes(q)?'flex':'none')}));
 const start=document.getElementById('start_date'),end=document.getElementById('end_date'),preview=document.getElementById('couponTimerPreview');
 function timer(){if(!preview)return;const now=new Date(),s=start&&start.value?new Date(start.value):null,e=end&&end.value?new Date(end.value):null;let text='Open-ended validity';if(s&&now<s)text='Scheduled — starts '+s.toLocaleString();else if(e&&now>e)text='Expired '+e.toLocaleString();else if(e){const ms=e-now,days=Math.floor(ms/86400000),hrs=Math.floor((ms%86400000)/3600000);text='Active — '+days+'d '+hrs+'h remaining'}else if(s)text='Active since '+s.toLocaleString();preview.querySelector('span').textContent=text}
 [start,end].forEach(x=>x&&x.addEventListener('change',timer));timer();
 const pop=document.getElementById('couponValidationPopup');if(pop){document.body.style.overflow='hidden';pop.querySelectorAll('[data-coupon-validation-close]').forEach(x=>x.onclick=()=>{pop.remove();document.body.style.overflow=''})}
});
</script>
@endpush
