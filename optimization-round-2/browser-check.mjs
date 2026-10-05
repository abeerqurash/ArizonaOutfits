import puppeteer from '../complete-project-audit/lighthouse-tools/node_modules/puppeteer-core/lib/puppeteer/puppeteer-core.js';
import fs from 'node:fs';
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--disable-gpu']});
const results=[];const errors=[];const page=await browser.newPage();await page.emulateMediaFeatures([{name:'prefers-reduced-motion',value:'reduce'}]);
page.on('pageerror',e=>errors.push({url:page.url(),error:e.message}));
for(const path of ['/','/about','/blogs','/products','/contact','/login','/privacy-policy','/terms-and-conditions','/product-categories','/category','/audit-customer/dashboard','/audit-customer/orders','/audit-customer/profile','/audit-customer/security']){
 for(const width of [1440,880,600,390]){
  await page.setViewport({width,height:900});const response=await page.goto('http://127.0.0.1:8123'+path,{waitUntil:'networkidle2'});
  const data=await page.evaluate(()=>({title:document.title,status:document.querySelector('meta[name="robots"]')?.content,canonical:document.querySelector('link[rel="canonical"]')?.href,description:document.querySelector('meta[name="description"]')?.content,schemas:Array.from(document.querySelectorAll('script[type="application/ld+json"]')).map(x=>JSON.parse(x.textContent)),tracks:Array.from(document.querySelectorAll('[data-card-carousel]')).map(x=>({label:x.getAttribute('aria-label'),cards:x.children.length,display:getComputedStyle(x).display,width:x.clientWidth,cardWidth:x.firstElementChild?.getBoundingClientRect().width,gap:parseFloat(getComputedStyle(x).gap),scrollWidth:x.scrollWidth,controlsHidden:x.previousElementSibling?.hidden}))}));
  data.horizontalOverflow=await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+2);
  if(width<=880 && !path.startsWith('/audit-customer/')){const toggle=await page.$('.menu-button');if(toggle){await toggle.click();data.mobileMenu=await page.evaluate(()=>{let n=document.querySelector('.az-mobile-mega-nav');return {visible:n&&getComputedStyle(n).display==='grid',columns:n?getComputedStyle(n).gridTemplateColumns.split(' ').length:0}});await toggle.click();}}
  const valid=data.tracks.every(t=>width>880?t.display!=='flex':Math.abs(t.cardWidth-(width<=600?t.width:(t.width-t.gap)/2))<2);
  results.push({path,width,http:response.status(),carouselWidthsPass:valid,...data});
  if(width===390){await page.screenshot({path:`optimization-round-2/carousel-${path==='/'?'home':path.slice(1).replaceAll('/','-')}-390.png`,fullPage:true});}
  if(width<=880){const button=await page.$('.az-carousel-controls:not([hidden]) button:last-child');if(button){await button.evaluate(x=>x.scrollIntoView({block:"center"}));await new Promise(r=>setTimeout(r,200));await button.click();await new Promise(r=>setTimeout(r,300));results.at(-1).nextMoved=await page.evaluate(()=>Array.from(document.querySelectorAll('[data-card-carousel]')).some(x=>x.scrollLeft>0));}}
 }
}
fs.writeFileSync('optimization-round-2/browser-checks.json',JSON.stringify({results,errors},null,2));console.log(JSON.stringify({checks:results.length,failed:results.filter(x=>x.http!==200||!x.carouselWidthsPass||x.nextMoved===false).map(x=>({path:x.path,width:x.width,tracks:x.tracks})),errors},null,2));await browser.close();
