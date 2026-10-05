from pathlib import Path
import json
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'administration-audit'
checks=json.loads((O/'checks.json').read_text());routes=json.loads((O/'route-checks.json').read_text());render=json.loads((O/'page-render-checks.json').read_text())
passed=sum(x['result']=='PASS' for x in checks);issues=sum(x['result']=='ISSUE' for x in checks)
report=f'''ARIZONA OUTFITS — REMAINING ADMINISTRATION MENUS AUDIT
2 October 2026, Asia/Karachi

VERDICT
These remaining 10 sidebar menus are not yet ready to mark as passed.
The earlier 18 menus plus these 10 make 28 sidebar menus covered overall.
This phase audited the current installed project; it did not install repairs.

EVIDENCE
{len(checks)} isolated behavioral checks: {passed} PASS, {issues} ISSUE, 0 test errors.
The ISSUE count includes overlapping permission/analytics cases, not 26 separate bugs.
{len(render)} admin screens tested: {sum(x['result']=='PASS' for x in render.values())} rendered successfully; Email Template Edit failed.
{len(routes['endpoints'])} routes audited, including the public CMS page route: 1 route-matching failure.
{len(routes['literal_view_route_references'])} literal route references: all registered.
Current table columns/foreign keys were read from local MariaDB without writes.
All action tests ran in SQLite memory and separate fixture storage.
No live database records, actual backup files, uploaded assets or installed source
files were changed. No real email or real administrator broadcast was sent.

PRIORITY FINDINGS

1. HIGH — Remaining module permissions are not enforced by their action URLs.
   All 10 tested routes allowed an active administrator whose permission check
   explicitly returned false. Sidebar visibility alone does not protect actions.
   Required mappings: settings/email templates -> settings.manage;
   CMS/menu builder -> content.manage; analytics -> dashboard.view;
   team/roles -> admin-users.manage; notifications -> notifications.manage;
   logs -> audit-logs.view; backups -> backups.manage.
   File: C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Middleware\\AdminMiddleware.php
   Store Settings sidebar link is also not wrapped in its permission check.
   File: C:\\xampp\\htdocs\\ArizonaOutfits\\resources\\views\\admin\\partials\\sidebar.blade.php
   Administration-links uses the default auth guard; use the explicit admin guard
   to remain consistent with your separated admin/customer authentication.
   File: C:\\xampp\\htdocs\\ArizonaOutfits\\resources\\views\\admin\\partials\\administration-links.blade.php

2. HIGH — Backup names collide within the same second.
   create() names files from type + a timestamp with second precision. In an
   isolated test, a completed fixture backup already existed at the generated path.
   A second create attempt at that same second failed and its cleanup DELETED the
   earlier completed file. Two backup records then shared one path.
   Use unique filenames/temp files and cleanup only the attempt's own files.
   Do not repeatedly click Create Backup until this is repaired.
   File: C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Services\\AdminBackupService.php
   Limit: a complete real MySQL dump/restore was not executed. This test reproduced
   failure cleanup with an isolated SQLite connection, which the exporter rejects.
   The same catch/cleanup path is used for a real database/ZIP write failure.

3. HIGH — CMS HTML sanitization accepts executable link variants.
   Unquoted javascript: hrefs and HTML-entity encoded schemes survive clean().
   DOM parsing confirmed they become executable href values in rendered HTML.
   The email sanitizer uses a similar regex approach and also needs review.
   Files: C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Services\\CmsContentSanitizer.php
          C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Services\\EmailTemplateService.php

4. HIGH — Normal public CMS URLs do not match their route.
   /page/fixture-page fails routing because the slug constraint ends in a literal
   escaped asterisk, instead of a repetition quantifier. The CMS controller can
   render the published page directly, but visiting its generated URL fails.
   This also breaks Menu Builder links that point to CMS pages.
   File: C:\\xampp\\htdocs\\ArizonaOutfits\\routes\\web.php

5. HIGH — Several Store Settings options are saved but do not control behavior.
   The saved low-stock default was set to 22 in the fixture; InventoryCatalogService
   still returned configured threshold 5. Individual reorder_point overrides are
   valid and should remain overrides; this mismatch affects the fallback default.
   Source review confirms tax stays hardcoded to zero, shipping uses shipping.methods,
   and order numbers use AO- instead of the saved order_prefix.
   guest_checkout_enabled, cash_on_delivery_enabled, maintenance_mode,
   stock_management_enabled and free_shipping_threshold have no runtime consumption
   outside their settings persistence/model. Checkout notice/order email message
   likewise are not connected to the current frontend/email rendering.
   Currency also has inconsistent sources: checkout page reads saved currency,
   while checkout calculation and Stripe use configured payment/shipping currency.
   Bank transfer enablement and bank instruction snapshot ARE wired into checkout.
   These settings need deliberate integration or clear removal of unsupported
   controls; merely saving values is insufficient.
   Files: C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Controllers\\Admin\\EcommerceSettingController.php
          C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Models\\EcommerceSetting.php
          C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Controllers\\CheckoutController.php
          C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Controllers\\Payment\\StripePaymentController.php
          C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Services\\InventoryCatalogService.php

6. MEDIUM — Global Analytics disagrees with archived orders and currencies.
   With paid orders USD 40 archived + USD 20 live, revenue showed 20 instead of 60.
   Summary item units excluded the archived order, while the joined top-product
   query included it: summary 1 unit versus product rows 2 units.
   Adding GBP revenue also adds it to the same scalar displayed as dollars.
   Use one consistent archive policy/query and separate currencies, with matching
   charts, totals, top products/countries and CSV labels. No exchange conversion
   should be invented for this repair.
   File: C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Controllers\\Admin\\AdminAnalyticsController.php
   View: C:\\xampp\\htdocs\\ArizonaOutfits\\resources\\views\\admin\\analytics\\index.blade.php

7. MEDIUM — CMS changes leave Menu Builder's cached page URLs stale.
   Warming header menu cache, changing a page slug, then reading the menu returns
   the previous slug until expiry. Page publication changes can also leave cached
   page status stale. Invalidate menu caches when linked pages change or are deleted.
   Reorder also accepts duplicate IDs and only partially applies invalid lists.
   Custom URL validation accepts a slash followed by a backslash, which is unsafe
   as a browser navigation target. Both model resolution and controller validation
   should use the same strict URL rule.
   Files: C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Controllers\\Admin\\AdminPageController.php
          C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Controllers\\Admin\\AdminNavigationMenuController.php
          C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Services\\NavigationMenuService.php
          C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Models\\NavigationMenuItem.php

8. MEDIUM — Notification action URLs accept unsafe forms.
   / followed by a backslash is accepted as internal. A same-host ftp: URL is also
   accepted because only the hostname is checked for complete URLs.
   Notification ownership itself passed: one administrator cannot read/delete
   another's notification; mark read/clear read stay scoped to the current admin.
   File: C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Controllers\\Admin\\AdminNotificationController.php

9. MEDIUM — Email Template Edit is the listing page duplicated into the edit view.
   The controller supplies emailTemplate; edit.blade.php expects templates and
   crashes with Undefined variable $templates. It needs the actual edit form.
   Unknown spaced placeholders such as double-brace + spaces + variable name are
   also not validated/replaced consistently. Compact placeholders are validated.
   Disabling a template correctly selects the built-in fallback; it does NOT
   disable the actual order/inventory email. UI text should explain that behavior.
   Files: C:\\xampp\\htdocs\\ArizonaOutfits\\resources\\views\\admin\\email-templates\\edit.blade.php
          C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Controllers\\Admin\\AdminEmailTemplateController.php
          C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Services\\EmailTemplateService.php

10. MEDIUM — Audit validation failures are logged as server errors.
    A ValidationException was recorded with status_code 500 instead of its normal
    422 response semantics. Redirect responses can also be recorded as success
    even if the controller returns validation/errors rather than completing work.
    Secret redaction, separate admin_id attribution, actor search and CSV passed.
    File: C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Middleware\\AdminAuditMiddleware.php

WHAT PASSED
- Store Settings form saves values and rejects invalid tax amounts.
- CMS CRUD, administrator ownership, draft/public visibility and custom-template
  existence validation work when controllers are called with valid data.
- Menu item add/edit/delete/cache clearing works for direct menu mutations.
- Admin Team creates independent administrator accounts and copies a customer
  without modifying that customer or rehashing its password incorrectly.
- Role assignments/changes work; duplicate permission IDs are rejected; assigned
  roles and system roles refuse deletion; own/last-super-admin guards passed.
- Notification ownership/read/clear-read behavior works.
- Audit attribution, nested secret redaction, actor search and CSV work.
- Email template update records admin ownership, escapes variable HTML, rejects
  unknown compact variables and supplies fallback/preview behavior.
- All 10 main menu pages rendered; the failure is the extra email edit screen.

LIMITATIONS / NEXT REPAIR SCOPE
- This is the audit report, not a claim that those 10 modules are repaired.
- No real email test was sent and no live database/full backup was made/restored.
- Last-super-admin protection was checked for sequential actions, not two
  concurrent administrator changes; its current pre-transaction count needs
  lock-aware review while implementing permission/role repairs.
- Financial settings/Stripe changes must be tested together before live payments.
- Your custom article/page architecture remains relevant: repair CMS routing and
  template validation while retaining manually created Blade bodies.
- Existing records and prior working menu changes must be preserved in the repair
  set. Replacement files should be delivered separately as TXT with exact paths.

MACHINE-READABLE EVIDENCE
checks.json — each behavioral assertion
page-render-checks.json — render results
route-checks.json — route matching, controller methods and view route references
live-schema.json — read-only column/foreign-key metadata
'''
(O/'REPORT.txt').write_text(report,encoding='utf-8')
print(f'Audit report written: {len(checks)} checks; {passed} PASS / {issues} ISSUE; 10 remaining menus require repairs.')
