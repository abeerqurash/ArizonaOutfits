from pathlib import Path
import json,hashlib,html
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'inventory-purchasing-fixes';manifest=json.loads((O/'manifest.json').read_text())
for item in manifest:
 p=R/item['relative']
 assert (hashlib.sha256(p.read_bytes()).hexdigest() if p.exists() else None)==item['original_sha256'],f'Installed source changed: {p}'
checks=json.loads((O/'checks.json').read_text())+json.loads((O/'extra-checks.json').read_text());assert all(c['result']=='PASS' for c in checks)
guide='''ARIZONA OUTFITS — INVENTORY AND PURCHASING REPAIRS
Prepared 1 October 2026. Separate complete TXT files; no ZIP.

WHAT YOU WILL INSTALL
44 complete files covering inventory alerts, history, reports, stock valuation,
reorder dashboard, purchase orders/receiving/returns, and suppliers/documents/ratings.
The application source and live database have NOT been changed by this repair set.

INSTALL ALL 44 FILES TOGETHER
1. Back up your project and export your current database in phpMyAdmin first.
   You can stop using the admin dashboard while replacing the files.
2. Open FILES.html for a clickable list, or use the destination list below.
3. Open each numbered TXT file. Select all its text and copy it.
4. Open the exact destination file below in your editor. Select ALL its old text,
   paste the replacement, and save. Do not append the replacement to old code.
   For files marked NEW, create the file at the listed path.
   Save actual destination files as .php / .blade.php, not .php.txt.
   Use UTF-8. Keep the original file name from the destination list.
5. After every replacement is saved, open PowerShell in the project folder.
   Run these commands one at a time:

Set-Location 'C:\\xampp\\htdocs\\ArizonaOutfits'
& 'C:\\xampp\\php\\php.exe' artisan optimize:clear
& 'C:\\xampp\\php\\php.exe' artisan migrate --path=database/migrations/2026_10_01_000001_repair_inventory_purchasing_schema.php

6. The migration must finish successfully before you test these menus. It adds
   administrator ownership columns, a draft version column, and missing supplier
   rating fields. Existing user ownership and existing records remain intact.
   It also supplies the two missing document/rating tables on a fresh schema.
   If the migration fails, stop testing and keep the complete error message.
7. Refresh the browser with Ctrl+F5. Existing drafts already open in browser tabs
   must be reopened before saving because the edit-version format changed.

IMPORTANT DETAILS
- Do not run migrate:fresh, migrate:refresh, or reset: these can delete your data.
- This is an additive migration. Its down method deliberately retains added
  columns/data. To revert the entire installation, restore the backup of both
  project files and database instead of relying on migrate:rollback.
- Legacy user IDs are preserved. The migration does not guess administrator IDs
  from equal customer IDs. New work records the real signed-in administrator.
- Inventory catalog costs/prices retain the existing GBP display default.
  Purchase orders use each supplier/order currency, with separate currency totals.
  This does not convert money or change stored prices. If your product cost and
  selling prices use another currency, set INVENTORY_CURRENCY to that code in
  .env and run optimize:clear. Currency labels alone do not convert old values.
- Supplier monthly spend charts show the supplier's current currency only;
  summary cards show totals/averages for every recorded order currency.
- Products with variants use individual variant stock. Adjust variants rather
  than the parent stock. Variant costs use the parent cost_price because your
  variant schema has no separate cost field.
- Roles need inventory.manage, purchase-orders.manage, or suppliers.manage for
  their respective modules. Your existing super administrator keeps full access.
- No route replacements, payment changes, or live Stripe tests are required here.
- Ignore build.py, deliver.py, prepare_tests.py, manifest.json, JSON evidence,
  _checks and _staged: those are preparation/testing files, not installation files.

TEST AFTER INSTALLATION
1. Open each of the seven menus. Check sidebar links and counts.
2. Add stock to a simple product. History must show your administrator name.
3. Remove more than available stock. It must show an error and retain the stock.
4. Adjust a variant. Reports/valuation must count variant units without parent
   stock being added a second time; variant SKU/options must appear in exports.
5. Set a product/variant reorder point, e.g. 10. At stock 8 it should be low in
   alerts, reports, valuation and reorder; at equality suggested order is >=1.
6. Recheck low stock twice: there should be one active alert, not duplicates.
7. Create/update a supplier, add a contact/document/rating, and view its details.
8. Select reorder items, create a draft purchase order, then edit it.
9. Open that draft in two browser tabs. Save the first, then save the old second
   tab. The second must require reloading instead of overwriting the newer draft.
10. Mark the draft ordered, receive a partial quantity, then receive the balance.
    Stock/history must change correctly and the alert must resolve above threshold.
11. Try receiving too much or an item from another purchase order: reject both.
12. Correct a received quantity. Stock/received totals must reduce correctly.
13. Submit a supplier return, then cancel it: stock must restore once.
    Complete a separate return: credit records without another stock deduction.
14. Received/partially received orders cannot be cancelled or reset to ordered.
15. Export history CSV, valuation CSV/Excel/PDF, reorder reports and purchase-order
    documents. Check SKU, administrator, quantities, prices and currency labels.
16. Try valid and invalid custom report dates; recent movements must obey dates.
17. If you have limited admin roles, confirm allowed menus work and prohibited
    modules return 403 even when their URL is entered directly.

VERIFICATION BEFORE DELIVERY
115 isolated behavioral checks passed, including migration repeat/fresh-table
compatibility, preserved legacy attribution, admin FKs and stock guards.
13 menu/detail/create pages rendered; draft edit rendered separately.
Valuation PDF and Excel workbook generated successfully.
53 endpoints and 84 literal route references checked without errors.
30 non-Blade PHP source/migration/config files passed PHP syntax checks.
Tests used in-memory SQLite; live MariaDB migration execution and browser actions
remain your installation checks. No real supplier email was sent by the tests.

EXACT REPLACEMENT / CREATION PATHS
'''
for item in manifest:
 guide+=f"\n{item['txt']}\n{'CREATE NEW' if item['new'] else 'REPLACE COMPLETE FILE'}: {item['destination']}\n"
(O/'00_INSTALL_AND_TEST_GUIDE.txt').write_text(guide,encoding='utf-8')
rows=''.join(f'<tr><td>{i}</td><td><a href="{html.escape(item["txt"],quote=True)}" download>{html.escape(item["txt"])}</a></td><td>{"Create new" if item["new"] else "Replace"}</td><td><code>{html.escape(item["destination"])}</code></td></tr>' for i,item in enumerate(manifest,1))
page='''<!doctype html><html lang="en"><meta charset="utf-8"><title>Arizona Outfits — Inventory files</title><style>body{font:16px/1.5 system-ui;margin:32px;color:#182032;background:#f4f6fa}h1{font-size:26px}table{border-collapse:collapse;background:white;width:100%}td,th{padding:12px;text-align:left;border:1px solid #dbe0e9}code{font-size:13px;overflow-wrap:anywhere}a{color:#134cab}th{background:#e8edf5}</style><h1>Inventory and purchasing — 44 separate TXT files</h1><p>Start with <a href="00_INSTALL_AND_TEST_GUIDE.txt">the installation and testing guide</a>. Each TXT contains a complete file. Save its content at the listed destination; do not keep the TXT extension.</p><p>115 isolated checks passed. Install all files together, then run the one migration named in the guide. Existing project files and the live database have not been modified.</p><table><thead><tr><th>#</th><th>Open / save TXT</th><th>Action</th><th>Destination</th></tr></thead><tbody>'''+rows+'</tbody></table></html>'
(O/'FILES.html').write_text(page,encoding='utf-8')
print(f'Delivered {len(manifest)} TXT files and install guide. {len(checks)} PASS. Original source hashes unchanged.')
