import fs from 'node:fs';import * as esbuild from './dependencies/node_modules/esbuild/lib/main.js';
const root='complete-project-audit/replacement-files';const manifest=JSON.parse(fs.readFileSync(root+'/manifest.json','utf8'));
for(const item of manifest.filter(x=>x.destination.endsWith('.js')&&x.destination.startsWith('public/'))){const p=root+'/_staged/'+item.destination;const code=(await esbuild.transform(fs.readFileSync(p,'utf8'),{loader:'js',minify:true})).code;fs.writeFileSync(p,code);fs.writeFileSync(root+'/'+item.file,code);}
console.log('Prepared JavaScript minified.');
