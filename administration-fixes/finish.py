from pathlib import Path
import json,hashlib,subprocess
ROOT=Path(__file__).resolve().parent.parent;OUT=ROOT/'administration-fixes'
manifest=json.loads((OUT/'manifest.json').read_text())
changed=[]
for x in manifest:
    path=ROOT/x['destination'];actual=hashlib.sha256(path.read_bytes()).hexdigest() if path.exists() else None
    if actual!=x['original_sha256']:changed.append(x['destination'])
assert not changed,changed
groups=['checks.json','integration-checks.json','backup-roundtrip-checks.json','customer-content-regression-checks.json','inventory-purchasing-regression-checks.json','inventory-extra-regression-checks.json']
counts={};total=0
for name in groups:
    rows=json.loads((OUT/name).read_text());fail=[x for x in rows if x['result']!='PASS'];assert not fail,(name,fail)
    counts[name]=len(rows);total+=len(rows)
syntax=json.loads((OUT/'syntax-checks.json').read_text());assert all(x['pass'] for x in syntax)
blade=json.loads((OUT/'blade-syntax-checks.json').read_text());assert all(x['pass'] for x in blade)
javascript=json.loads((OUT/'javascript-checks.json').read_text());assert all(x['pass'] for x in javascript)
installer=json.loads((OUT/'installer-checks.json').read_text());assert all(x['pass'] for x in installer)
routes=json.loads((OUT/'route-checks.json').read_text());assert not any(x['errors'] for x in routes['endpoints']);assert all(x['registered'] for x in routes['literal_view_route_references'])
mapping='\n\n'.join(f'{n:02d}. {x["file"]}\n    '+('CREATE NEW FILE: ' if x['new'] else 'REPLACE COMPLETE FILE: ')+str(ROOT/x['destination']) for n,x in enumerate(manifest,1))
guide=f'''ARIZONA OUTFITS — SYNCHRONIZED ADMINISTRATION FILES
2 October 2026 — Asia/Karachi

{len(manifest)} separate replacement TXT files. No ZIP.
I prepared and tested this set. I have NOT installed it in your application.
All existing application-file hashes still match their originals.

INSTALL ALL FILES BEFORE TESTING
These files depend on one another. Installing only a few can cause a missing
class, missing method, or mismatched controller/view error.
The TXT contents are complete source files, not fragments to append.
Files ending .blade.php stay .blade.php; the JavaScript file stays .js.
Do not rename every replacement to .php.

OPTION A — INSTALL THE WHOLE SET TOGETHER (RECOMMENDED)
1. Stop the running php artisan serve terminal with Ctrl+C. Stop a queue worker
   too if you have one running.
2. Open PowerShell in C:\\xampp\\htdocs\\ArizonaOutfits.
3. Run this command:

   powershell -NoProfile -ExecutionPolicy Bypass -File .\\administration-fixes\\Install-All.ps1

The script validates every file, backs up every existing destination in
administration-fixes\\_installation-backups, then copies the separate TXT
contents to the correct PHP/Blade/JS destinations. It does not modify the DB,
.env or vendor files. It stops if a source file changed after preparation.
This script was tested on an isolated copy: all 51 replacements and 45 original
file backups verified. I have NOT run it on your actual application.

OPTION B — MANUAL REPLACEMENT
1. Stop the server/queue worker before replacing files.
2. Save a copy of every destination file first.
3. Open each numbered TXT, select all, and copy its contents.
4. Open/create its exact destination below, select all, paste, and save UTF-8.
5. Create the NEW service/view/middleware files first. Replace bootstrap/app.php
   and routes/web.php last. Finish ALL {len(manifest)} files before restarting.
6. Keep existing files not listed below.

AFTER INSTALLING EITHER WAY
In the project folder run:

   C:\\xampp\\php\\php.exe artisan optimize:clear
   C:\\xampp\\php\\php.exe artisan serve

Restart your usual queue worker if you use one. Refresh the browser with Ctrl+F5.
There is no new migration in this set. The earlier inventory migration
2026_10_01_000001_repair_inventory_purchasing_schema.php remains required and
is already present in your project; this set does not replace it.

SETTINGS NOW CONTROL THESE BEHAVIORS
- Currency is used by checkout, Stripe amount creation and storefront price
  displays. Existing orders keep their own currency; analytics reports each
  currency separately. Changing currency does not convert catalog prices.
- Tax applies to merchandise AFTER coupon discounts, before shipping.
- Shipping Fee is the standard delivery fee. Economy/express retain their
  configured price difference from standard. With standard=17, economy=12
  and express=27 using your current shipping configuration.
- Free Shipping Threshold uses merchandise AFTER discounts, inclusively.
- Order Prefix affects new orders; existing order numbers remain unchanged.
- Guest Checkout off requires customer sign-in for checkout/payment creation.
  Bank transfer continues to require sign-in even when guest checkout is on.
- Cash on Delivery appears only when enabled. It creates a pending/unpaid
  order and reserves managed stock once. In Orders, mark the payment PAID only
  after collecting the money. Paid timestamp and coupon usage then synchronize.
  COD payment changes use individual order editing, not bulk payment updates.
  Use the dedicated cancellation/refund actions for reversals.
- Stock Management off disables storefront stock limits and physical sale
  deduction for NEW orders. Product existence/status/variant validation stays
  enforced. Each order keeps an immutable policy snapshot; old managed orders
  still deduct/restore correctly when this setting later changes.
- Default Low Stock Threshold reaches alerts/reports/reorder. An individual
  product or variant reorder_point continues to override the default.
- Maintenance returns a 503 public-store page. Admin remains reachable;
  signed payment webhook/return endpoints remain reachable. Signed-in active
  administrators can preview the storefront.
- Checkout Notice appears at checkout; Order Email Message appears in order
  confirmation/new-order emails, including their custom-template wrapper.
- CMS pages can still use custom Blade layouts with database metadata. Normal
  /page/slug links now route correctly; page edits clear cached navigation.

TEST IN THIS ORDER
1. Sign in as Super Administrator. Open each of the remaining 10 menus.
2. Store Settings: save your intended currency/tax/shipping/prefix/stock flags.
   Use a small test product and compare checkout quote with the saved order.
3. COD: create a test order, inspect pending payment and stock reservation,
   mark paid after simulated collection, then use the cancellation/refund
   actions where appropriate. Repeated actions must not duplicate stock/usage.
4. CMS: publish a test page, link it through Menu Builder, rename/unpublish it,
   and check the public link and navigation. Keep your custom Blade layout.
5. Analytics: pick currency and dates; compare paid archived/live orders,
   product units and CSV. Different currencies must never be added together.
6. Admin Team/Roles: use a limited test admin; hidden modules must also deny
   direct action URLs. A delegated manager cannot grant permissions it lacks
   or create/manage Super Administrators.
7. Notifications: send only an intentional test message; check ownership,
   read/clear actions and valid internal links.
8. Audit Logs: inspect successful/failed changes and date filters; validation
   failures show 422, and passwords/bank numbers remain redacted.
9. Backups: create a database backup and full backup; download both. Restore
   on a TEST database/project copy, never overwrite the live project to test.
10. Email Templates: edit, save, preview and intentionally send one test email.
    Disabling a custom template uses the built-in email. Confirm your actual
    SMTP/queue delivery separately; the automated tests used fakes.
11. Smoke-test earlier order/product/coupon/customer/blog/inventory menus,
    including Purchase Orders receiving/returns. The receipt model correction
    is included because the installed model lacked its admin receiver relation.
12. Stripe end-to-end payment/webhook testing remains for your live server,
    as you requested. No real card payment was made in these tests.

VERIFICATION
{total} behavioral/regression/backup checks passed; {len(syntax)} PHP source syntax checks
passed; {len(routes['endpoints'])} routes matched and {len(routes['literal_view_route_references'])} view route references were registered.
{len(blade)} Blade views compiled without syntax errors; {len(javascript)} JavaScript assets/inline
scripts passed syntax checks. The installer passed on an isolated project copy.
The 14 requested administration/detail screens rendered successfully.
Real backup dump/restore was tested with temporary isolated MariaDB databases,
including Unicode, binary, NULL and generated fields; fixture databases removed.
The production application database and actual uploads were not modified.
This is a tested replacement set; your own browser checks after installation
are still needed before marking every menu as passed on your installed site.

ALL EXACT DESTINATIONS

{mapping}
'''
(OUT/'INSTALL.txt').write_text(guide,encoding='utf-8')
(OUT/'TEST-RESULTS.txt').write_text('Verification results\n'+ '\n'.join(f'{k}: {v} PASS' for k,v in counts.items())+f'\nTotal: {total} PASS\nPHP syntax: {len(syntax)} PASS\nBlade syntax: {len(blade)} PASS\nJavaScript syntax: {len(javascript)} PASS\nRoutes: {len(routes["endpoints"])} PASS\nInstaller: 51 replacements and 45 backups verified on isolated copy\nInstalled application unchanged: verified SHA-256\nStripe live payment and actual SMTP delivery: pending user testing\n',encoding='utf-8')
index=(OUT/'FILES.html').read_text(encoding='utf-8');index=index.replace('Read INSTALL.txt first.','<a href="INSTALL.txt">Read the installation guide first</a>.');(OUT/'FILES.html').write_text(index,encoding='utf-8')
print(f'Delivery finalized: {len(manifest)} TXT files, {total} behavioral checks, installed source unchanged.')
