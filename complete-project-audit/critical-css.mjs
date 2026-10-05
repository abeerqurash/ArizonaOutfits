import puppeteer from './lighthouse-tools/node_modules/puppeteer-core/lib/puppeteer/puppeteer-core.js';import fs from 'node:fs';import * as esbuild from './dependencies/node_modules/esbuild/lib/main.js';
const b=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});const p=await b.newPage();const rules=new Map();
for(const width of [390,600,880,1440]){await p.setViewport({width,height:900});await p.goto('http://127.0.0.1:8123/',{waitUntil:'networkidle2'});const css=await p.evaluate(()=>{
 const out=[];const visible=e=>{const r=e.getBoundingClientRect();return r.top<innerHeight&&r.bottom>0&&r.width>0&&r.height>0;};
 const walk=(rules,condition='')=>{for(const rule of rules){if(rule.type===CSSRule.STYLE_RULE){try{const selector=rule.selectorText.replace(/::?(before|after|marker|placeholder)/g,'');if(Array.from(document.querySelectorAll(selector)).some(visible))out.push(condition?condition+'{'+rule.cssText+'}':rule.cssText);}catch{}}else if(rule.type===CSSRule.MEDIA_RULE&&matchMedia(rule.conditionText).matches)walk(rule.cssRules,'@media '+rule.conditionText);}};
 for(const sheet of document.styleSheets){if(sheet.href&&sheet.href.includes('/asset/css/style.css'))walk(sheet.cssRules);}return out;
 });for(const x of css)rules.set(x,x);}
await b.close();const css=(await esbuild.transform(Array.from(rules.values()).join('\n'),{loader:'css',minify:true})).code.replaceAll('../media/','/asset/media/');fs.writeFileSync('complete-project-audit/critical-home.css',css);console.log('Critical homepage CSS:',css.length,'characters');
