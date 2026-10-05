from pathlib import Path
import json,hashlib
base=Path(__file__).parent;root=base.parent;stage=base/'_staged';files=json.loads((base/'changed.json').read_text())
manifest=[];lines=['# Replacement files','', 'Copy each complete text replacement into the destination below. Copy PNG assets as image files. Create new files/folders when needed.','', '| Replacement | Destination |','|---|---|']
for i,p in enumerate(files,1):
 name=f'{i:02d}_{Path(p).name}' + ('' if Path(p).suffix.lower()=='.png' else '.txt');content=(stage/p).read_bytes();(base/name).write_bytes(content)
 destination=str(root/p)
 lines.append(f'| [{name}]({name}) | `{destination}` |')
 manifest.append({'replacement':name,'destination':destination,'sha256':hashlib.sha256(content).hexdigest(),'new':not(root/p).exists()})
(base/'manifest.json').write_text(json.dumps(manifest,indent=2));(base/'FILES.md').write_text('\n'.join(lines),encoding='utf-8')
(base/'INSTALL.txt').write_text('''STOREFRONT FEATURE UPDATE — FULL REPLACEMENT FILES

Project: C:\\xampp\\htdocs\\ArizonaOutfits

The live application files have not been replaced. This folder contains separate
complete .txt replacements and two original PNG assets. FILES.md maps every file to its exact destination.
Do not replace admin/products/index.blade.php with the storefront index file.

INSTALL AS ONE UPDATE
1. Back up your application files and database. The review-title migration deletes
   the old review titles as requested; review text and ratings remain intact.
2. In the project terminal run: C:\\xampp\\php\\php.exe artisan down
3. Copy ALL replacements listed in FILES.md to their exact destination paths.
   Remove the .txt suffix from text replacements at the destination. Copy the PNG\n   assets directly to their listed image paths. Create the new files where listed.
4. Run: C:\\xampp\\php\\php.exe artisan optimize:clear
5. Run: C:\\xampp\\php\\php.exe artisan migrate --force
6. Run: C:\\xampp\\php\\php.exe artisan up
7. Refresh the browser with Ctrl+F5.

CATEGORY MENUS
In Admin product categories, set each child category's Parent Category to Men
Outfits or Women Outfits. Keep the header custom URL as /product-category/slug.
Desktop hover and keyboard focus reveal the children. The parent link opens its
category page. Mobile has a separate expandable Subcategories control, so tapping
the parent still navigates. Mobile main menu remains four columns.
Upload the category Featured Image in its existing category editor for the banner.
The banner is 1400px maximum width and 500px tall, with the category heading as H1.
Run artisan media:optimize after image uploads if optimized image variants are needed.

POPULAR / TRENDING / SORTS
Home Popular Products and Trending Products use disjoint sets, up to six each.
Trending can contain fewer than six when the active catalog has fewer than twelve.
Latest, Popularity and Best Sellers all share a stable catalog shuffle across users.
Editing a product or refreshing does not reshuffle; adding a product does.
Price, highest rating and biggest discount retain their existing calculations.
Activation/deletion naturally changes which products are eligible for a listing.

CUSTOM SIZES
Create an attribute named Size and add values S, M, L, Custom (or Custom Size).
Attach the values to each product and create its variants with price/stock as usual.
Choosing Custom reveals five required inch measurements. Distinct measurements
create distinct cart lines; identical measurements merge. Measurements are retained
in order options for existing customer/admin order and invoice displays.

SIZE CHART IMAGES
The Men and Women popup tabs display your supplied original PNG charts.
Copy the two image assets to:
  public/asset/images/size-chart-men.png
  public/asset/images/size-chart-women.png
The updated config/size_charts.php points to these PNG paths.
If the previous update is already installed, copy only these two PNG assets and
the updated config replacement, then run artisan config:clear.

BLOGS
Directory: /blogs. Articles: /blog/slug. Published legacy /slug article URLs redirect
permanently to the new path. Existing CMS pages keep their root URLs.
All five existing article templates preserve their content, use the shared sidebar,
and end with Related Articles, Reviews, then footer. The newsletter form is removed.
For future article templates, use the same related-article-cards and article-reviews
partials. Existing manually authored templates are not generated automatically.

CHECKS
checks.json: catalog, measurements, routes, category-link resolution and Blade checks.
cart-checks.json: real cart-controller requests, separate/merged sizing lines, stock
limits and order-option database serialization (isolated SQLite database).
browser-checks.json: product and quick-view interaction checks in Chrome.
render-checks.json: full rendering of ten storefront/article pages using fixtures.
These checks do not claim a new Lighthouse score or a live payment-provider test.
''',encoding='utf-8')
print(f'Packaged {len(files)} replacement files (38 text replacements and 2 PNG assets)')
