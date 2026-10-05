from pathlib import Path
from html.parser import HTMLParser
import subprocess,json
OUT=Path(__file__).resolve().parent;CHECK=OUT/'_checks'
class Scripts(HTMLParser):
    def __init__(self):super().__init__();self.data=[];self.current=None
    def handle_starttag(self,tag,attrs):
        attrs=dict(attrs)
        if tag=='script' and 'src' not in attrs and attrs.get('type','') not in ['application/json','application/ld+json']:self.current=[]
    def handle_endtag(self,tag):
        if tag=='script' and self.current is not None:self.data.append(''.join(self.current));self.current=None
    def handle_data(self,data):
        if self.current is not None:self.current.append(data)
parser=Scripts();parser.feed((CHECK/'checkout-render.html').read_text(encoding='utf-8'))
paths=[OUT/'_staged/public/asset/js/checkout-stripe.js']
for i,source in enumerate(parser.data):
    p=CHECK/f'checkout-inline-{i}.js';p.write_text(source,encoding='utf-8');paths.append(p)
rows=[]
for p in paths:
    r=subprocess.run([r'C:\Program Files\nodejs\node.exe','--check',str(p)],text=True,capture_output=True)
    rows.append({'path':str(p.relative_to(OUT)),'pass':r.returncode==0,'output':r.stdout+r.stderr})
(OUT/'javascript-checks.json').write_text(json.dumps(rows,indent=2),encoding='utf-8')
assert all(x['pass'] for x in rows),rows
print(f'JavaScript syntax: {len(rows)} PASS (asset and rendered checkout inline scripts).')
