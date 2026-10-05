import puppeteer from '../complete-project-audit/lighthouse-tools/node_modules/puppeteer-core/lib/puppeteer/puppeteer-core.js';
import fs from 'node:fs';
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--disable-gpu']});
const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
const source=fs.readFileSync('storefront-feature-update/_staged/public/asset/js/product.js','utf8');
const checks=[];
const variants=(available=true)=>[{id:'1',available,regular_price:100,options:[{option_id:'1',value_id:'1'}]},{id:'2',available,regular_price:100,options:[{option_id:'1',value_id:'2'}]}];
async function fixture(data,quick=false){
 await page.setContent(`<html><body><div class="${quick?'quick-view-product':'single-product-page'}"><form data-product-form action="/cart"><select class="product-option-select" data-option-id="1" name="product_options[1]" aria-label="Choose Size" required><option value="">Choose Size</option><option value="1" data-label="M">M</option><option value="2" data-label="Custom">Custom</option></select><input name="variant_id"><fieldset data-custom-measurements hidden disabled>${['chest','waist','shoulder','sleeve_length','body_length'].map(k=>`<input name="custom_measurements[${k}]" type="number" required min="0.1" max="200" step="0.01">`).join('')}</fieldset><div class="variant-message"></div><button data-add-to-cart data-ready-text="Add To Cart"><span class="button-text">Out of Stock</span></button><button data-buy-now data-ready-text="Buy Now"><span class="button-text">Out of Stock</span></button><script type="application/json" data-product-variants>${JSON.stringify(data)}</script></form></div><script>${source}</script></body></html>`,{waitUntil:'load'});
}
const record=(name,pass)=>{checks.push({name,pass});if(!pass)throw new Error(name);};
for(const quick of [false,true]){
 await fixture(variants(),quick);
 record(`${quick?'Quick view':'Product'} keeps Add To Cart before selection`,await page.$eval('[data-add-to-cart]',b=>!b.disabled&&b.textContent==='Add To Cart'));
 await page.select('.product-option-select','2');
 record('Custom size opens enabled required fields',await page.$eval('fieldset',f=>!f.hidden&&!f.disabled));
 record('Empty custom measurements prevent submission',await page.$eval('form',f=>!f.checkValidity()));
 await page.$$eval('fieldset input',inputs=>inputs.forEach(input=>input.value='35'));
 record('Complete custom measurements allow native validation',await page.$eval('form',f=>f.checkValidity()));
 await page.select('.product-option-select','1');
 record('Ordinary size hides and disables custom fields',await page.$eval('fieldset',f=>f.hidden&&f.disabled));
 await fixture([variants()[0]],quick);
 record('Single variation selects itself',await page.$eval('[name=variant_id]',f=>f.value==='1'));
 record('Single variation enables buying',await page.$eval('[data-buy-now]',b=>!b.disabled&&b.textContent==='Buy Now'));
 await fixture(variants(false),quick);
 record('Actual out of stock remains disabled',await page.$eval('[data-add-to-cart]',b=>b.disabled&&b.textContent==='Out of Stock'));
}
if(errors.length) console.log(errors);
record('No JavaScript exceptions',errors.length===0);
fs.writeFileSync('storefront-feature-update/browser-checks.json',JSON.stringify(checks,null,2));
await browser.close();console.log(checks.length+' browser interaction checks passed');

