<button type="button" class="size-chart-link" data-size-chart-open>Size chart</button>
<dialog class="az-size-chart" aria-label="Size charts"><button type="button" data-size-chart-close aria-label="Close size charts">Close ×</button><h2>Size charts</h2>
<div class="az-size-chart-tabs" role="tablist" aria-label="Size chart">
@foreach(['men'=>'Men','women'=>'Women'] as $chartKey=>$chartLabel)
<button type="button" role="tab" id="size-chart-tab-{{ $chartKey }}" aria-controls="size-chart-panel-{{ $chartKey }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}" data-size-chart-tab="{{ $chartKey }}">{{ $chartLabel }}</button>
@endforeach
</div>
@foreach(['men'=>'Men','women'=>'Women'] as $chartKey=>$chartLabel)
@php
$chartPath = config('size_charts.'.$chartKey);
@endphp
<section role="tabpanel" id="size-chart-panel-{{ $chartKey }}" aria-labelledby="size-chart-tab-{{ $chartKey }}" @if(!$loop->first) hidden @endif>
@if($chartPath && is_file(public_path($chartPath)))
<img src="{{ asset($chartPath) }}" alt="{{ $chartLabel }} size chart" style="display:block;width:100%;height:auto" loading="lazy" decoding="async">
@else
<p>{{ $chartLabel }} size chart will be available soon.</p>
@endif
</section>
@endforeach
</dialog>
<style>.size-chart-link{cursor:pointer;padding:10px 0;text-decoration:underline;color:#172033;background:none;border:0}.az-size-chart{max-width:850px;width:calc(100% - 40px);max-height:85dvh;overflow:auto;padding:24px;border:1px solid #d5dbea;border-radius:12px}.az-size-chart::backdrop{background:#0008}.az-size-chart table{border-collapse:collapse;width:100%}.az-size-chart th,.az-size-chart td{padding:12px;border:1px solid #d5dbea;text-align:left}.az-size-chart [data-size-chart-close]{float:right;padding:10px;cursor:pointer}</style>
<style>.az-size-chart-tabs{display:flex;gap:10px;margin:20px 0}.az-size-chart-tabs button{padding:12px 24px;cursor:pointer;background:#fff;color:#172033;border:1px solid #172033;border-radius:6px}.az-size-chart-tabs [aria-selected=true]{background:#172033;color:#fff}</style>
<script>
(function(){
function activate(tab){const dialog=tab.closest('dialog');dialog.querySelectorAll('[data-size-chart-tab]').forEach(button=>{const selected=button===tab;button.setAttribute('aria-selected',String(selected));button.tabIndex=selected?0:-1;dialog.querySelector('#'+button.getAttribute('aria-controls')).hidden=!selected;});}
document.addEventListener('click',function(event){if(event.target.closest('[data-size-chart-open]'))document.querySelector('.az-size-chart')?.showModal();if(event.target.closest('[data-size-chart-close]'))event.target.closest('dialog').close();const tab=event.target.closest('[data-size-chart-tab]');if(tab)activate(tab);});
document.addEventListener('keydown',function(event){const tab=event.target.closest('[data-size-chart-tab]');if(!tab||!['ArrowLeft','ArrowRight','Home','End'].includes(event.key))return;event.preventDefault();const tabs=Array.from(tab.closest('[role=tablist]').querySelectorAll('[role=tab]'));const next=event.key==='Home'?tabs[0]:event.key==='End'?tabs.at(-1):tabs[(tabs.indexOf(tab)+(event.key==='ArrowRight'?1:tabs.length-1))%tabs.length];activate(next);next.focus();});
})();
</script>
