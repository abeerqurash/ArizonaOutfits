from pathlib import Path
import re,json
out=Path(__file__).resolve().parent;root=out.parent
s=(root/'public/asset/js/main.js').read_text()
pattern=r'function animate\(\)\{(.*?)requestAnimationFrame\(animate\)\}animate\(\);'
s,n=re.subn(pattern,lambda m:'let newsletterVisible=false,newsletterFrame=0;function animate(){if(!newsletterVisible)return;'+m[1]+'newsletterFrame=requestAnimationFrame(animate)}new IntersectionObserver(entries=>{newsletterVisible=entries.some(e=>e.isIntersecting)&&!matchMedia("(prefers-reduced-motion:reduce)").matches;cancelAnimationFrame(newsletterFrame);if(newsletterVisible)animate()},{rootMargin:"100px"}).observe(newsletterBg.closest(".news-letter")||newsletterBg);',s,count=1)
assert n==1
(root/'optimization-round-2/_staged/public/asset/js/main.js').write_text(s,encoding='utf-8')
p=out/'changed.json';v=json.loads(p.read_text());v.append('public/asset/js/main.js');p.write_text(json.dumps(v))
print('Paused offscreen Home animation')
