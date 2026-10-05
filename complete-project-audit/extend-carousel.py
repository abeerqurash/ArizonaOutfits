from pathlib import Path
import json,re
r=Path.cwd();o=r/'complete-project-audit/replacement-files';m=json.loads((o/'manifest.json').read_text())
def save(p,s):
 old=next((x for x in m if x['destination']==p),None);n=old['file'] if old else f'{len(m)+1:02d}_'+Path(p).name+'.txt'
 if not old:m.append({'file':n,'destination':p})
 (o/n).write_text(s,encoding='utf-8');t=o/'_staged'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_text(s,encoding='utf-8')
def matching(s,start):
 depth=0
 for x in re.finditer(r'<div\b[^>]*>|</div\s*>',s[start:]):
  depth+=-1 if x.group().startswith('</') else 1
  if depth==0:return start+x.start(),start+x.end()
 raise Exception('unclosed div')
paths=['resources/views/home.blade.php','resources/views/pages/custom/privacy-policy.blade.php','resources/views/pages/custom/terms-and-conditions.blade.php']
paths+=['resources/views/'+str(p.relative_to(r/'resources/views')).replace('\\','/') for p in (r/'resources/views/blogs/posts').glob('*.blade.php')]
for p in paths:
 s=(r/p).read_text(encoding='utf-8-sig');changed=False
 # Only footer/recent card grids; never individual article body.
 for match in list(re.finditer(r'<div class="parent-wrapper">\s*@foreach\(\$(posts|relatedPosts) as \$(post|relatedPost)\)',s))[::-1]:
  start=match.start();end,after=matching(s,start);part=s[start:end]
  loop=re.search(r'@foreach\(.*?@endforeach',part,re.S);assert loop,p
  text='<div class="parent-wrapper az-responsive-cards" data-card-carousel aria-label="'+('Related articles' if 'relatedPosts' in loop.group() else 'Recent articles')+'">\n'+loop.group()+'\n'
  s=s[:start]+text+s[end:];changed=True
 if p.endswith('home.blade.php'):
  s=s.replace("@section('title', 'Ideostream — Every Topic the World Is Talking About')", "@section('title', 'Arizona Outfits — Latest Collections & Style Stories')")
  s=re.sub(r"@section\('meta_description', '[^']*'\)","@section('meta_description', 'Shop the latest collections and popular products at Arizona Outfits. Explore our style stories, product reviews and new arrivals.')",s,count=1)
  s=s.replace('class="services-grid products"','class="services-grid products az-responsive-cards" data-card-carousel aria-label="Products"')
 if changed or p.endswith('home.blade.php'):
  s=s.replace('<a href="#" class="btn-style-2', '<a href="{{ route(\'blogs-page\') }}" class="btn-style-2')
  save(p,s)
save('public/asset/css/card-carousel.css','''/* Responsive card collections retain a grid on desktop and native swipe below 880px. */
.az-responsive-cards {min-width:0;}
.az-carousel-controls {display:none;align-items:center;justify-content:flex-end;gap:12px;padding:18px 0;}
.az-carousel-controls button {border:1px solid #d5dbea;border-radius:999px;background:#090b19;color:#fff;padding:12px 20px;font:inherit;cursor:pointer;}
.az-carousel-controls button:disabled {opacity:.45;cursor:default;}
.az-carousel-controls button:focus-visible,.az-responsive-cards:focus-visible {outline:3px solid #2563eb;outline-offset:4px;}
.az-carousel-status {color:#374151;font-size:13px;}
@media(max-width:880px){
 .az-responsive-cards.az-responsive-cards {display:flex!important;gap:20px!important;overflow-x:auto;scroll-snap-type:x mandatory;scroll-behavior:smooth;overscroll-behavior-x:contain;scrollbar-width:thin;padding-bottom:12px;grid-template-columns:none!important;width:100%;}
 .az-responsive-cards.az-responsive-cards > * {flex:0 0 calc((100% - 20px)/2);min-width:0;max-width:none;scroll-snap-align:start;transform:none;opacity:1;}
 .az-carousel-controls:not([hidden]) {display:flex;}
 .blog-posts .post-and-categories:has(.az-responsive-cards) {grid-template-columns:minmax(0,1fr);}
 .blog-posts .post-cards-parent:has(.az-responsive-cards) {min-width:0;width:100%;}
}
@media(max-width:600px){.az-responsive-cards.az-responsive-cards > * {flex-basis:100%;}}
@media(prefers-reduced-motion:reduce){.az-responsive-cards.az-responsive-cards{scroll-behavior:auto!important;}.az-responsive-cards *{animation:none!important;transition:none!important;}}
''')
save('public/asset/js/card-carousel.js','''(() => {
 'use strict';
 const compact=window.matchMedia('(max-width:880px)');
 const reduced=window.matchMedia('(prefers-reduced-motion:reduce)');
 document.querySelectorAll('[data-card-carousel]').forEach((track,index)=>{
  if(track.dataset.carouselReady)return;
  track.dataset.carouselReady='1';track.id ||= `az-card-carousel-${index}`;
  const controls=document.createElement('div');controls.className='az-carousel-controls';
  const previous=document.createElement('button'),next=document.createElement('button'),status=document.createElement('span');
  previous.type=next.type='button';previous.textContent='← Previous';next.textContent='Next →';
  previous.setAttribute('aria-label','Previous cards');next.setAttribute('aria-label','Next cards');
  previous.setAttribute('aria-controls',track.id);next.setAttribute('aria-controls',track.id);
  status.className='az-carousel-status';status.setAttribute('aria-live','polite');status.setAttribute('aria-atomic','true');
  controls.append(previous,status,next);track.after(controls);
  let frame=0;
  const update=()=>{
   const visible=compact.matches;const overflowing=track.scrollWidth>track.clientWidth+2;
   controls.hidden=!visible||!overflowing;
   if(visible){track.tabIndex=0;track.setAttribute('role','region');}else{track.removeAttribute('tabindex');track.removeAttribute('role');}
   previous.disabled=track.scrollLeft<=2;next.disabled=track.scrollLeft+track.clientWidth>=track.scrollWidth-2;
   const cards=Array.from(track.children);const step=cards[0]?.getBoundingClientRect().width+parseFloat(getComputedStyle(track).gap||0);
   const first=step?Math.min(cards.length,Math.round(track.scrollLeft/step)+1):0;
   const count=window.matchMedia('(max-width:600px)').matches?1:2;
   status.textContent=cards.length?`${first}–${Math.min(cards.length,first+count-1)} of ${cards.length}`:'';
  };
  const move=direction=>track.scrollBy({left:direction*(track.clientWidth+parseFloat(getComputedStyle(track).gap||0)),behavior:reduced.matches?'instant':'smooth'});
  previous.addEventListener('click',()=>move(-1));next.addEventListener('click',()=>move(1));
  track.addEventListener('keydown',event=>{if(event.target!==track||!compact.matches)return;if(['ArrowLeft','ArrowRight'].includes(event.key)){event.preventDefault();move(event.key==='ArrowLeft'?-1:1);}});
  track.addEventListener('scroll',()=>{cancelAnimationFrame(frame);frame=requestAnimationFrame(update);},{passive:true});
  new ResizeObserver(update).observe(track);compact.addEventListener('change',()=>{track.scrollLeft=0;update();});update();
 });
})();
''')
# Extend actual related results to six where enough other published articles exist.
p='app/Http/Controllers/BlogController.php';s=(r/p).read_text(encoding='utf-8-sig');mark='        $latestPosts = Post::with(['
insert="""        if ($relatedPosts->count() < 6) {
            $relatedPosts = $relatedPosts->concat(Post::with(['categories', 'primaryCategory', 'author'])
                ->published()->where('id', '!=', $post->id)
                ->whereNotIn('id', $relatedPosts->pluck('id'))
                ->latestPosts()->take(6 - $relatedPosts->count())->get());
        }

"""
assert mark in s;s=s.replace(mark,insert+mark,1);save(p,s)
(o/'manifest.json').write_text(json.dumps(m,indent=2),encoding='utf-8')
