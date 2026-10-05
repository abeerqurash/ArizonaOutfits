import puppeteer from '../complete-project-audit/lighthouse-tools/node_modules/puppeteer-core/lib/puppeteer/puppeteer-core.js';
import fs from 'node:fs';import path from 'node:path';
const root=process.cwd(),stage=path.join(root,'project-ui-audit/_staged');
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--disable-gpu']});const page=await browser.newPage();let errors=[];page.on('pageerror',e=>errors.push(e.message));
await page.setRequestInterception(true);page.on('request',request=>{try{const url=new URL(request.url());if(url.protocol==='data:')return request.continue();const relative=decodeURIComponent(url.pathname).replace(/^\//,'');let candidate=path.join(stage,'public',relative);if(!fs.existsSync(candidate))candidate=path.join(root,'public',relative);if(!candidate.startsWith(root)||!fs.existsSync(candidate)||!fs.statSync(candidate).isFile())return request.respond({status:404,body:''});const ext=path.extname(candidate),types={'.css':'text/css','.js':'application/javascript','.png':'image/png','.svg':'image/svg+xml','.woff2':'font/woff2'};return request.respond({status:200,contentType:types[ext]||'application/octet-stream',body:fs.readFileSync(candidate)});}catch{request.abort();}});
const checks=[];
let currentHTML='';
page.on('request',request=>{});
await page.removeAllListeners('request');
page.on('request',request=>{try{const url=new URL(request.url());if(url.pathname==='/ui-fixture')return request.respond({status:200,contentType:'text/html',body:currentHTML});if(url.protocol==='data:')return request.continue();const relative=decodeURIComponent(url.pathname).replace(/^\//,'');let candidate=path.join(stage,'public',relative);if(!fs.existsSync(candidate))candidate=path.join(root,'public',relative);if(!candidate.startsWith(root)||!fs.existsSync(candidate)||!fs.statSync(candidate).isFile())return request.respond({status:404,body:''});const ext=path.extname(candidate),types={'.css':'text/css','.js':'application/javascript','.png':'image/png','.svg':'image/svg+xml','.woff2':'font/woff2'};return request.respond({status:200,contentType:types[ext]||'application/octet-stream',body:fs.readFileSync(candidate)});}catch{request.abort();}});
for(const file of fs.readdirSync('project-ui-audit/admin-rendered'))for(const width of [1440,390]){
 errors=[];await page.goto('about:blank');await page.setViewport({width,height:1000});currentHTML=fs.readFileSync('project-ui-audit/admin-rendered/'+file,'utf8');await page.goto('http://127.0.0.1:8000/ui-fixture',{waitUntil:'domcontentloaded'});await new Promise(r=>setTimeout(r,300));
 const result=await page.evaluate(()=>({banners:document.querySelectorAll('.az-page-banner').length,visibleNative:[...document.querySelectorAll('select[class*=native],input.az-date-native')].filter(e=>e.offsetParent).length,overflow:document.documentElement.scrollWidth>innerWidth+2,dropdowns:document.querySelectorAll('.az-select-trigger').length,calendars:document.querySelectorAll('.az-date-trigger').length,nestedControls:document.querySelectorAll('.az-select-wrap .az-select-wrap,.cat-index-select .az-select-wrap,.az-cat-select .az-select-wrap,.az-coupon-select .az-select-wrap,.az-coupon-filter .az-select-wrap').length}));
 if(file==='edit-product.html'){
  const upload=await page.$('input[name^="variants["][type=file]');
  if(!upload)throw new Error('Variant upload input missing');
  result.uploadButtonPurple=await upload.evaluate(input=>getComputedStyle(input,'::file-selector-button').backgroundColor==='rgb(99, 91, 255)');
  await upload.uploadFile(path.join(root,'dashboard-ui-correction/create-product-1440.png'));
  result.uploadChoosesFile=await upload.evaluate(input=>input.files.length===1);
  await new Promise(r=>setTimeout(r,150));
  const currentUpload=await page.$('input[name^="variants["][type=file]');
  result.uploadVisible=await currentUpload.evaluate(input=>!!input.offsetParent);
  await currentUpload.evaluate(input=>input.scrollIntoView({block:'center'}));
  await new Promise(r=>setTimeout(r,150));
  await page.screenshot({path:'project-ui-audit/variation-upload-'+width+'.png'});
 }
 checks.push({file,width,...result,errors:[...errors]});
 if(['settings.html','create-product.html'].includes(file))await page.screenshot({path:'project-ui-audit/'+file.replace('.html','')+'-'+width+'.png',fullPage:false});
}
if(checks.some(c=>c.visibleNative||c.nestedControls||c.overflow||c.errors.length||c.banners!==1))process.exitCode=1;await browser.close();fs.writeFileSync('project-ui-audit/admin-layout-checks.json',JSON.stringify(checks,null,2));console.log(JSON.stringify({pages:checks.length,overflows:checks.filter(c=>c.overflow),exceptions:checks.filter(c=>c.errors.length)},null,2));
