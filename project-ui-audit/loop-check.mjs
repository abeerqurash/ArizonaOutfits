import puppeteer from '../complete-project-audit/lighthouse-tools/node_modules/puppeteer-core/lib/puppeteer/puppeteer-core.js';
import fs from 'node:fs';
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
const page=await browser.newPage();const css=fs.readFileSync('project-ui-audit/_staged/public/asset/css/arizona-admin-controls.css','utf8'),js=fs.readFileSync('project-ui-audit/_staged/public/asset/js/arizona-admin-controls.js','utf8');
const checks=[];
for(const root of ['admin-content','customer-dashboard-content']){
 await page.goto('about:blank');await page.setContent(`<style>${css}</style><body class="admin-body"><main class="${root}"><select name="status"><option value="a">A</option><option value="b">B</option></select><div class="cat-index-select"><select class="cat-index-native"><option>Category</option></select><button type="button">Category</button></div><div class="az-coupon-select"><select class="az-coupon-native"><option>Coupon</option></select><button type="button">Coupon</button></div></main><script>${js}</script>`);
 await new Promise(r=>setTimeout(r,100));
 const result=await page.evaluate(async()=>{let mutations=0;const observer=new MutationObserver(records=>mutations+=records.length);observer.observe(document.querySelector('main'),{childList:true,subtree:true,attributes:true});await new Promise(r=>setTimeout(r,250));observer.disconnect();return {mutations,nested:document.querySelectorAll('.cat-index-select .az-select-wrap,.az-coupon-select .az-select-wrap').length,triggers:document.querySelectorAll('.az-select-trigger').length};});
 if(result.mutations||result.nested||result.triggers!==1)throw new Error(JSON.stringify(result));
 await page.evaluate(()=>{const s=document.createElement('select');s.name='dynamic';s.innerHTML='<option>New field</option>';document.querySelector('main').append(s);});await new Promise(r=>setTimeout(r,100));
 await page.waitForFunction(()=>document.querySelectorAll('.az-select-trigger').length===2,{timeout:5000});
 checks.push({root,...result,dynamic:true});
}
await browser.close();fs.writeFileSync('project-ui-audit/loop-checks.json',JSON.stringify(checks,null,2));console.log('Both dashboards: no idle observer loop, no nested filters, dynamic controls work.');
