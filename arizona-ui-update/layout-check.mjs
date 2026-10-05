import puppeteer from '../complete-project-audit/lighthouse-tools/node_modules/puppeteer-core/lib/puppeteer/puppeteer-core.js';
import fs from 'node:fs';import path from 'node:path';
const root=process.cwd(),stage=path.join(root,'arizona-ui-update/_staged');
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--disable-gpu']});const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
await page.setRequestInterception(true);page.on('request',request=>{try{let url=new URL(request.url());if(url.protocol==='data:')return request.continue();let relative=decodeURIComponent(url.pathname).replace(/^\//,'');let candidate=path.join(stage,'public',relative);if(!fs.existsSync(candidate))candidate=path.join(root,'public',relative);if(!candidate.startsWith(root)||!fs.existsSync(candidate)||!fs.statSync(candidate).isFile())return request.respond({status:404,body:''});let ext=path.extname(candidate),types={'.css':'text/css','.js':'application/javascript','.woff2':'font/woff2','.jpg':'image/jpeg','.png':'image/png','.webp':'image/webp'};request.respond({status:200,contentType:types[ext]||'application/octet-stream',body:fs.readFileSync(candidate)});}catch{request.abort();}});
const results=[];
for(const file of fs.readdirSync('arizona-ui-update/rendered')){
 const html=fs.readFileSync('arizona-ui-update/rendered/'+file,'utf8');
 for(const width of [1440,880,600,390]){
  await page.goto('about:blank');await page.setViewport({width,height:900});await page.setContent(html,{waitUntil:'domcontentloaded'});await new Promise(resolve=>setTimeout(resolve,350));
  const result=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth+2,categoryHeight:document.querySelector('.az-category-intro')?.getBoundingClientRect().height,relatedCards:document.querySelector('.az-related-articles [data-card-carousel]')?.children.length,contactAuthorLabel:document.querySelector('.page-description-wrapper .author-subtitle')?.textContent.trim()}));
  if(file==='home.html'&&width===1440){const parent=await page.$('.az-header-category-item:has(.az-header-category-panel)');await parent.hover();result.hoverOpens=await parent.$eval('.az-header-category-panel',el=>getComputedStyle(el).display!=='none');result.parentHref=await parent.$eval('a',a=>a.getAttribute('href'));}
  if(file==='home.html'&&width===390){await page.click('.menu-button');result.mobileChildren=await page.$$eval('.az-mobile-menu-group details',list=>list.length);const toggle=await page.$('.az-mobile-menu-group details summary');await toggle.click();result.mobileExpanded=await page.$eval('.az-mobile-menu-group details',d=>d.open);}
  if(file==='product.html'){
   await page.click('[data-size-chart-open]');result.sizeChartOpens=await page.$eval('.az-size-chart',dialog=>dialog.open);
   await page.waitForFunction(()=>document.querySelector('#size-chart-panel-men img')?.naturalWidth>0);
   result.menImageLoaded=true;
   await page.click('[data-size-chart-tab="women"]');result.womenTabWorks=await page.$eval('#size-chart-panel-women',panel=>!panel.hidden);
   await page.waitForFunction(()=>document.querySelector('#size-chart-panel-women img')?.naturalWidth>0);
   result.womenImageLoaded=true;
   result.chartFitsViewport=await page.$eval('.az-size-chart',d=>d.getBoundingClientRect().width<=innerWidth);
   await page.keyboard.press('Escape');result.escapeClosesChart=await page.$eval('.az-size-chart',dialog=>!dialog.open);
  }
  if(result.relatedCards){result.relatedBeforeReviews=await page.evaluate(()=>{const related=document.querySelector('.az-related-articles'),reviews=document.querySelector('.arizona-product-reviews');return !!reviews&&!!(related.compareDocumentPosition(reviews)&Node.DOCUMENT_POSITION_FOLLOWING);});}
  results.push({file,width,...result});
 }
}
await browser.close();fs.writeFileSync('arizona-ui-update/layout-checks.json',JSON.stringify({results,errors},null,2));console.log(JSON.stringify({pages:results.length,overflows:results.filter(x=>x.overflow),errors},null,2));
