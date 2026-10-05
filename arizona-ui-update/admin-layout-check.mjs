import puppeteer from '../complete-project-audit/lighthouse-tools/node_modules/puppeteer-core/lib/puppeteer/puppeteer-core.js';
import fs from 'node:fs';import path from 'node:path';
const root=process.cwd(),stage=path.join(root,'arizona-ui-update/_staged');
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--disable-gpu']});const page=await browser.newPage();let errors=[];page.on('pageerror',e=>errors.push(e.message));
await page.setRequestInterception(true);page.on('request',request=>{try{const url=new URL(request.url());if(url.protocol==='data:')return request.continue();const relative=decodeURIComponent(url.pathname).replace(/^\//,'');let candidate=path.join(stage,'public',relative);if(!fs.existsSync(candidate))candidate=path.join(root,'public',relative);if(!candidate.startsWith(root)||!fs.existsSync(candidate)||!fs.statSync(candidate).isFile())return request.respond({status:404,body:''});const ext=path.extname(candidate),types={'.css':'text/css','.js':'application/javascript','.png':'image/png','.svg':'image/svg+xml','.woff2':'font/woff2'};return request.respond({status:200,contentType:types[ext]||'application/octet-stream',body:fs.readFileSync(candidate)});}catch{request.abort();}});
const checks=[];
for(const file of fs.readdirSync('arizona-ui-update/admin-rendered'))for(const width of [1440,390]){
 errors=[];await page.goto('about:blank');await page.setViewport({width,height:1000});await page.setContent(fs.readFileSync('arizona-ui-update/admin-rendered/'+file,'utf8'),{waitUntil:'domcontentloaded'});await new Promise(r=>setTimeout(r,300));
 const result=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth+2,dropdowns:document.querySelectorAll('.az-select-trigger').length,calendars:document.querySelectorAll('.az-date-trigger').length}));checks.push({file,width,...result,errors:[...errors]});
 if(['settings.html','create-product.html'].includes(file))await page.screenshot({path:'arizona-ui-update/'+file.replace('.html','')+'-'+width+'.png',fullPage:false});
}
await browser.close();fs.writeFileSync('arizona-ui-update/admin-layout-checks.json',JSON.stringify(checks,null,2));console.log(JSON.stringify({pages:checks.length,overflows:checks.filter(c=>c.overflow),exceptions:checks.filter(c=>c.errors.length)},null,2));
