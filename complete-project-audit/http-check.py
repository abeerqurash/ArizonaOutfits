from pathlib import Path
import urllib.request,urllib.error,json
r=Path(r'C:\xampp\htdocs\ArizonaOutfits');out=r/'complete-project-audit';results=[]
for path in ['/','/about','/blogs','/category','/product-categories','/products','/product-category/test','/privacy-policy','/terms-and-conditions','/login','/register','/cart','/favorites','/admin/login','/asset/images/product-placeholder.svg','/asset/media/product-placeholder.svg']:
 try:
  res=urllib.request.urlopen('http://127.0.0.1:8000'+path,timeout=10);body=res.read().decode('utf-8','replace');status=res.status
  results.append({'path':path,'status':status,'final_url':res.url,'bytes':len(body),'error_page':any(v in body for v in ['Illuminate\\Contracts\\Container\\BindingResolutionException','Undefined variable','View [products.category] not found'])})
 except urllib.error.HTTPError as e:results.append({'path':path,'status':e.code})
 except Exception as e:results.append({'path':path,'error':str(e)})
(out/'http-results.json').write_text(json.dumps(results,indent=2));print(json.dumps(results,indent=2))
