<script>
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
