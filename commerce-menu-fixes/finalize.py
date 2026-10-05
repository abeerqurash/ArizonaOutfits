from pathlib import Path
import json, hashlib, subprocess
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'commerce-menu-fixes'
manifest=json.loads((O/'manifest.json').read_text())
for e in manifest:
    source=Path(e['destination'])
    assert hashlib.sha256(source.read_bytes()).hexdigest()==e['source_sha256'],f'Installed source changed: {source}'
    staged=O/'_staged'/source.relative_to(R)
    assert (O/e['download']).read_bytes()==staged.read_bytes()
    if source.suffix=='.php' and not source.name.endswith('.blade.php'):
        p=subprocess.run([r'C:\xampp\php\php.exe','-l',str(O/e['download'])],capture_output=True,text=True)
        assert p.returncode==0,p.stdout+p.stderr
    e['replacement_sha256']=hashlib.sha256((O/e['download']).read_bytes()).hexdigest()
(O/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
checks=json.loads((O/'checks.json').read_text());pages=json.loads((O/'page-render-checks.json').read_text());routes=json.loads((O/'route-checks.json').read_text())
assert all(c['result']=='PASS' for c in checks)
assert all(c['result']=='PASS' for c in pages.values())
assert not any(c['errors'] for c in routes['endpoints'])
guide='''ARIZONA OUTFITS - COMPLETE COMMERCE REPLACEMENT FILES
Date: 1 October 2026
Project folder: C:\\xampp\\htdocs\\ArizonaOutfits

READ THIS FIRST
There are 19 complete source files, each in its own TXT file. There is no ZIP.
I have prepared and tested the replacements. Your installed application files
have not been replaced. Install this set together before testing the website.
Files 16-18 preserve your already-passing admin Order/Product/Tag controllers;
the other files contain the connected repairs. They retain your custom blog
architecture and current Pakistan timezone configuration.

STEP 1 - BACK UP
Copy the existing destination files listed below to a backup folder outside the
website folder, for example a folder on your Desktop named ArizonaOutfits-Before-Commerce-Fixes.
Keep the existing database backup. This set needs no new database migration.

STEP 2 - REPLACE EACH COMPLETE FILE
For each numbered TXT file:
1. Open the TXT file and copy ALL its contents (Ctrl+A, Ctrl+C).
2. Open its exact destination PHP/Blade file listed below in your code editor.
3. Select ALL the old contents and paste the complete new contents.
4. Save as UTF-8. Keep the destination filename and extension exactly as listed.
Do not paste snippets into the old code. Do not leave a .txt extension on a PHP
destination. Blade views must keep .blade.php. Do not copy this guide into PHP.
Complete all 19 replacements before opening or testing the changed pages.

EXACT FILE MAP
'''
for i,e in enumerate(manifest,1):
    guide+=f"\n{i:02d}. Open: {e['download']}\n    Replace: {e['destination']}\n"
guide+='''
STEP 3 - CLEAR CACHED ROUTES, VIEWS AND CONFIGURATION
Open a terminal in C:\\xampp\\htdocs\\ArizonaOutfits and run:

    C:\\xampp\\php\\php.exe artisan optimize:clear

Then refresh your browser with Ctrl+F5. Do not run migrate:fresh, db:wipe, or
delete your database. No npm build is needed because this set changes no JS/CSS.

STEP 4 - VERIFY YOUR ADMIN LOGIN
Super admins retain access to all seven modules. Other admins now need the
existing role permissions. Use your super admin account to assign permissions
through the existing Roles & Permissions menu when needed:
  orders.manage: Orders, Archived Orders, Payment Verifications
  products.manage: Products, Product Categories, Product Tags
  coupons.manage: Coupons
An unauthorized admin will see no corresponding sidebar link and receive 403
for a direct request. This is intentional. Do not disable the middleware.

WHAT CHANGES
Archive now hides an order from the active admin list while preserving customer
history, invoices and tracking. Archived payment records remain available to
the bank verification page and Stripe webhook. Read-only admin documents can
open archived orders; restore an archived order before ordinary editing.
Archive/restore alone does not change stock.

Cancelled/refunded bank orders cannot be verified or rejected as fresh payments.
Normal verification deducts stock once and records customer-safe timeline events.
Customer order and invoice access remains limited to that logged-in customer.

Coupon use includes archived purchases, redemption history and legacy counters.
Referenced coupons cannot be deleted or renamed. Deactivate them for new
purchases instead. Usage limits cannot be reduced while orders await payment;
pending orders retain their recorded discount. Unreferenced coupons remain deletable.

Storefront featured filtering works with and without search. Price ranges must
match one actual parent/simple item or variant, rather than mixing two prices.
Variant regular-price inheritance and zero sale prices match cart rules; a
variant does not inherit its parent's sale price. Sold-out variants determine
variable-product stock filters. Invalid price ranges and descendant-category
parents are rejected. Price sorting uses the lowest effective variant price.

Stripe gets its missing coupon service and protects repeated/late callbacks.
It uses the selected configured shipping method, price, name and delivery estimate,
matching the bank checkout configuration. The old country prices and Stripe-only
free shipping threshold are removed. This does not change config/shipping.php.

The sidebar has one active archive link, working inventory/reorder badge
composers, and permission-aware links. Three unused broken show routes are removed;
use the existing Edit pages for categories, tags and coupons.

STEP 5 - LOCAL ADMIN AND CUSTOMER CHECKS
Use test orders/products; do not mark a real pending bank payment paid just to test.
1. Open all seven admin menu pages and refresh each.
2. Log in as a customer. Open only that customer's history/detail/invoice.
3. Archive a test order in admin. It leaves active admin Orders, appears in
   Archived Orders, and remains available in that customer's history/invoice.
   Restore it and confirm stock is unchanged by archive/restore.
4. For a legitimate test bank receipt, verify a pending test order. Check paid/
   processing state, a single stock deduction and the customer timeline.
   A cancelled/refunded order must not show usable payment-review forms.
5. Check featured, price, sale and stock storefront filters against your products.
6. Check assigned category/tag deletion protection and category parent validation.
7. Apply a test coupon, confirm limits, and confirm an archived paid purchase
   does not reset its per-customer allowance. Referenced coupon deletion must fail.
8. Check sidebar links using a restricted admin account if you have one.

STRIPE HOSTED-SERVER PHASE
No real provider API, charge, signed webhook, refund or email delivery was used
in this verification. Test Stripe in test mode on your hosted server first using:
C:\\xampp\\htdocs\\ArizonaOutfits\\commerce-menu-audit\\STRIPE-LIVE-SERVER-CHECKLIST.txt
Real payments and provider refunds still need separate end-to-end verification.
Concurrent last-unit/coupon purchases remain a hosted test requirement; this set
does not introduce a reservation system or automatic compensating refunds.
A successful charge against a cancelled/refunded order requires reconciliation;
the webhook rejects that invalid fulfillment transition rather than reopening it.

VERIFICATION LIMITS
The tests use actual staged controllers/services with in-memory SQLite, mocked
mail and inventory-alert delivery. They cover local state transitions, customer
ownership, permissions and full Blade rendering. MariaDB concurrency, browser
interaction, provider signatures/HTTP delivery, PDFs and mail delivery are not
proven by these isolated checks.

Do not copy _checks, _staged, Python scripts or JSON evidence into application
directories. Only install the 19 numbered source TXT files at their mapped paths.
'''
(O/'00-READ-FIRST-INSTALLATION-GUIDE.txt').write_text(guide,encoding='utf-8')
report=f'''VERIFICATION RESULTS - COMMERCE REPLACEMENT SET
Date: 1 October 2026
{len(manifest)} complete separate TXT source files; application source hashes unchanged.
{len(checks)} isolated assertions: PASS; no remaining reproduced-issue assertions.
{len(pages)} full page renders: PASS.
{len(routes['endpoints'])} related route endpoints: no route/action/view errors.
{len(routes['literal_view_route_references'])} literal view route references: all registered.
17 PHP replacement files: syntax PASS. Two Blade replacements were rendered.
Each delivered TXT is byte-identical to the corresponding staged test source.
No live database writes, payment API calls or real charges.
No new migration required. Existing roles/permissions and coupon ledger are used.

Evidence: checks.json, page-render-checks.json, route-checks.json, manifest.json.
Harness: _checks/verify.php, _checks/routes.php; staged source: _staged.
See 00-READ-FIRST-INSTALLATION-GUIDE.txt for behavior, installation and limitations.
'''
(O/'20-VERIFICATION-RESULTS.txt').write_text(report,encoding='utf-8')
print(report)
