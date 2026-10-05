import fs from 'node:fs';import * as esbuild from './dependencies/node_modules/esbuild/lib/main.js';
const root='complete-project-audit/replacement-files';const manifest=JSON.parse(fs.readFileSync(root+'/manifest.json','utf8'));
const css=fs.readFileSync('public/asset/css/style.css','utf8');const out=await esbuild.transform(css,{loader:'css',minify:true});
const file=`${String(manifest.length+1).padStart(2,'0')}_style.css.txt`;const destination='public/asset/css/style.css';fs.mkdirSync(root+'/_staged/public/asset/css',{recursive:true});fs.writeFileSync(root+'/_staged/'+destination,out.code);fs.writeFileSync(root+'/'+file,out.code);manifest.push({file,destination});
for(const item of manifest.filter(x=>x.destination.endsWith('.js')&&x.destination.startsWith('public/'))){const p=root+'/_staged/'+item.destination;const code=(await esbuild.transform(fs.readFileSync(p,'utf8'),{loader:'js',minify:true})).code;fs.writeFileSync(p,code);fs.writeFileSync(root+'/'+item.file,code);}
fs.writeFileSync(root+'/manifest.json',JSON.stringify(manifest,null,2));console.log(`CSS minified ${css.length} -> ${out.code.length} characters; prepared JS minified.`);
