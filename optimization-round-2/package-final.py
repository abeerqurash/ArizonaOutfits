import json, hashlib, shutil
from pathlib import Path
p=Path(__file__).resolve().parent
manifest=json.loads((p/'manifest.json').read_text(encoding='utf-8-sig'))
for item in manifest:
    source=p/'_staged'/item['destination']
    shutil.copyfile(source,p/item['file'])
    item['sha256']=hashlib.sha256(source.read_bytes()).hexdigest()
(p/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
rows=[('Home','home-final'),('About','about'),('Blogs','blogs'),('Shop','shop'),('Contact','contact'),('Login','login'),('Individual article','article-final'),('Individual product','product'),('Product categories','categories'),('Blog categories','blogcategories'),('Individual product category','productcategory'),('Individual blog category','blogcategory'),('Privacy policy','policy'),('Terms and conditions','terms'),('Cart','cart'),('Register','register'),('Forgot password','forgot'),('Order tracking','tracking'),('Sale','sale'),('Phone login','phonelogin'),('Phone register','phoneregister'),('Thank you','thanks'),('Customer overview (fixture)','customer'),('Customer orders (fixture)','customerorders'),('Customer profile (fixture)','customerprofile'),('Customer security (fixture)','customersecurity')]
lines=['# Optimization round 2 results','','Prepared as 22 separate full replacement text files. The application source and real php.ini have not been overwritten. Follow INSTALL.txt and FILES.md.','','## Measured performance','','Latest available Lighthouse runs for the isolated preview; mobile and desktop are separate runs. Home and article final runs include temporary PHP OPcache. Other rows are earlier runs from this round and were not rerun after every shared styling change. Scores vary; these results do not guarantee a score on the deployed server.','','| Page | Mobile | Desktop |','| --- | ---: | ---: |']
for label,name in rows:
    scores=[]
    for device in ['mobile','desktop']:
        f=p/f'{name}-{device}.json'
        r=json.loads(f.read_text(encoding='utf-8-sig'))
        scores.append(f"[{round(r['categories']['performance']['score']*100)}]({f.name})")
    lines.append(f"| {label} | {' | '.join(scores)} |")
checks=json.loads((p/'browser-checks.json').read_text(encoding='utf-8-sig'))
failed=[x for x in checks['results'] if x['http']!=200 or not x['carouselWidthsPass'] or x.get('nextMoved') is False or x['horizontalOverflow']]
badmenus=[x for x in checks['results'] if x.get('mobileMenu') and (not x['mobileMenu']['visible'] or x['mobileMenu']['columns']!=4)]
lines+=['','## Changes and validation','','- Previous/next arrows above the right edge of responsive card collections; two cards at 880px and below, one at 600px and below.','- Dashboard-managed navigation in the mobile mega menu, four links per row before categories.','- Responsive WebP backgrounds and thumbnails, deferred phone widgets, paused offscreen animations, full initial layout styles and font-display overrides.',f"- Browser checks: {len(checks['results'])} page/width combinations at 1440, 880, 600 and 390px; {len(failed)} HTTP/carousel/overflow failures, {len(badmenus)} mobile menu failures, {len(checks['errors'])} JavaScript errors. See browser-checks.json.",'- Customer overview, orders, profile and security use a synthetic customer and order fixture. These verify rendering/responsiveness, not real authenticated database performance or all account actions.','- Missing legacy /projects, /services and /abeerkhan templates now return 404 through controller guards instead of a 500 error. Those routes are not performance passes.','','## Important limits','','The preview emulates gzip and static cache headers and uses optimized image copies. Run media:optimize after installation. The final Home/article tests also use PHP OPcache; follow XAMPP-PHP-SETTINGS.txt. php artisan serve ignores Apache .htaccess, so its compression/cache behavior can differ from Apache or live hosting.','','This round is a performance and responsive UI check, not proof that every security, payment, email, customer action or Google Ads requirement passes. Live HTTPS PageSpeed Insights and real authenticated dashboard measurements remain to be checked after installation/deployment. Private login/account pages should retain noindex even when Lighthouse gives a lower SEO score. No live Google Ads approval or Core Web Vitals field-data pass is claimed.','','Full storefront CSS is embedded in storefront-styles.blade.php; future style.css edits must be reflected there. Original images remain available. No database migration/reset or APP_KEY change is needed.']
(p/'RESULTS.md').write_text('\n'.join(lines)+'\n',encoding='utf-8')
assert all(hashlib.sha256((p/i['file']).read_bytes()).hexdigest()==i['sha256'] for i in manifest)
print(f'Packaged {len(manifest)} verified replacement files; browser failures={len(failed)}, menu failures={len(badmenus)}, errors={len(checks["errors"])}')
