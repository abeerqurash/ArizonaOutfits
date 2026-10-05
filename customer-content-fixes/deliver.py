from pathlib import Path
import re,json,hashlib,html
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'customer-content-fixes';A=R/'customer-content-audit'
# Reuse the route inspector, with this phase's staged autoloader/views and menus.
s=(R/'inventory-purchasing-audit/_checks/routes.php').read_text(encoding='utf-8-sig')
a=s.index("require __DIR__");b=s.index('$routes=',a);s=s[:a]+"require __DIR__.'/bootstrap.php';\n"+s[b:]
s=re.sub(r'\$prefixes=\[[^\]]*\]',"$prefixes=['admin.customers.','admin.reviews.','admin.posts.','admin.categories.']",s)
s=re.sub(r'\$directories=\[[^\]]*\]',"$directories=['admin/customers','admin/reviews','admin/posts','admin/categories']",s)
s=s.replace("file_get_contents($file->getPathname())", "file_get_contents(is_file(__DIR__.'/../../customer-content-fixes/_staged/resources/views/'.$directory.'/'.$file->getFilename()) ? __DIR__.'/../../customer-content-fixes/_staged/resources/views/'.$directory.'/'.$file->getFilename() : $file->getPathname())")
s=s.replace("__DIR__.'/../route-checks.json'","__DIR__.'/../../customer-content-fixes/route-checks.json'")
(A/'_checks/routes.php').write_text(s,encoding='utf-8')
manifest=json.loads((O/'manifest.json').read_text());checks=json.loads((O/'checks.json').read_text())+json.loads((O/'extra-checks.json').read_text());assert all(x['result']=='PASS' for x in checks)
for x in manifest:
 p=R/x['relative'];assert (hashlib.sha256(p.read_bytes()).hexdigest() if p.exists() else None)==x['original_sha256'],x['relative']
guide='''CUSTOMERS, REVIEWS, BLOG POSTS AND BLOG CATEGORIES
13 separate complete TXT files. Prepared 1 October 2026.

WHAT CHANGED
- Module permissions protect both sidebar links and direct/admin action URLs.
- Customers retain current and archived order history; either prevents deletion.
- Spending counts paid purchases only, excluding cancelled/refunded orders.
  Different currencies are shown separately, with no exchange conversion.
- Blocking/deactivating a customer removes access on the next web request,
  including an already logged-in customer. Administrator sessions stay separate.
- Reviews use the actual customer login; blocked customers and inactive products
  cannot receive submissions. Phone customers without email can enter a review
  contact email without changing their account's login or verification state.
- Published/scheduled articles require their custom Blade file to exist. Drafts
  can still be saved before the Blade file is created. Unsafe template names are
  rejected; the existing manual article architecture is retained.
- Category image cleanup preserves files shared by other categories.
- Existing review moderation and post revision/slug redirects are preserved.

INSTALLATION
1. Back up your project files first. Open FILES.html or the list below.
2. Replace ALL destination file contents with the corresponding complete TXT.
   Create files marked NEW at the listed path. Keep the destination .php or
   .blade.php extension; do not save installed files as .txt.
3. Install all 13 files together. Two destinations have the name show.blade.php;
   one is the customer admin page and one is the public product page.
   ReviewController here is the PUBLIC controller, not Admin/ReviewController.
4. Open PowerShell in the project folder and run:

Set-Location 'C:\\xampp\\htdocs\\ArizonaOutfits'
& 'C:\\xampp\\php\\php.exe' artisan optimize:clear

5. Refresh your browser with Ctrl+F5. No migration is needed for this phase.
   Existing database schema was checked read-only and supports these files.
6. Ignore _staged, build.py, deliver.py and JSON files; they are preparation/test
   evidence and should not be copied into the application.

WHAT TO TEST
Customers:
- Search by name/email/phone, filter status, open details and update a name.
- Confirm archived orders appear and prevent deleting their customer.
- Confirm unpaid orders do not increase spending; currency codes are accurate.
- Block a customer who is logged in using another browser/session. Their next
  account request should return them to login. Unblock to allow login again.
Reviews:
- Submit a product review, approve it, then reject it: public visibility follows
  the approved status. Try search, rating filter and delete a disposable review.
- Verify phone-only customer can supply a contact email for the review.
Blog Posts:
- Create/edit a draft with categories and a primary category.
- Before publishing/scheduling, create the corresponding custom article file:
  resources/views/blogs/posts/YOUR-TEMPLATE-NAME.blade.php
  Enter YOUR-TEMPLATE-NAME in the Template Name field, without .blade.php.
  A missing article file now produces a validation message; save as Draft until
  it exists. Database content still does not replace your custom article body.
- Update title/slug, verify revision history, restore a revision, and check that
  the old public URL redirects. Confirm drafts/archived articles stay private.
Blog Categories:
- Create/edit/delete an unused category, add a child, and test category search.
- A category used by posts or containing children must refuse deletion.
- A category cannot be its own parent or use a descendant as its parent.
Permissions:
- Super administrator keeps access. Limited roles require customers.manage,
  reviews.manage and content.manage for the relevant menus and actions.

VERIFIED BEFORE DELIVERY
85 isolated behavioral checks passed, including 9 admin page renders and the
public product page for a phone customer. PHP syntax checks passed.
Tests used SQLite memory and fixture storage; no customer records, live orders,
live database data, actual uploaded files or application source were changed.
Browser checks after you install remain necessary.

EXACT FILE DESTINATIONS
'''
for x in manifest:guide+=f"\n{x['txt']}\n{'CREATE NEW' if x['new'] else 'REPLACE COMPLETE FILE'}: {x['destination']}\n"
(O/'00_INSTALL_AND_TEST_GUIDE.txt').write_text(guide,encoding='utf-8')
rows=''.join(f'<tr><td>{i}</td><td><a href="{x["txt"]}" download>{html.escape(x["txt"])}</a></td><td>{"Create new" if x["new"] else "Replace"}</td><td><code>{html.escape(x["destination"])}</code></td></tr>' for i,x in enumerate(manifest,1))
(O/'FILES.html').write_text('''<!doctype html><html lang="en"><meta charset="utf-8"><title>Customers and Content — replacement files</title><style>body{font:16px/1.5 system-ui;margin:32px;background:#f4f6fa;color:#182032}table{width:100%;border-collapse:collapse;background:white}td,th{padding:12px;border:1px solid #dbe0e9;text-align:left}code{font-size:13px;overflow-wrap:anywhere}th{background:#e8edf5}a{color:#134cab}</style><h1>Customers and Content — 12 separate TXT files</h1><p><a href="00_INSTALL_AND_TEST_GUIDE.txt">Read the installation and testing guide first</a>. Install all files together, then clear Laravel caches. No migration needed.</p><p>85 isolated checks passed. Installed source and live database unchanged.</p><table><tr><th>#</th><th>Complete TXT replacement</th><th>Action</th><th>Destination</th></tr>'''+rows+'</table></html>',encoding='utf-8')
# Record the audit findings separately from the replacement installation guide.
(O/'FILES.html').write_text((O/'FILES.html').read_text(encoding='utf-8').replace('12 separate TXT files', '13 separate TXT files'),encoding='utf-8')
original=json.loads((A/'checks.json').read_text());issues=[x for x in original if x['result']!='PASS']
(A/'REPORT.txt').write_text('CUSTOMERS / REVIEWS / BLOG POSTS / BLOG CATEGORIES AUDIT\n\nInstalled schema inspected read-only. Behavioral tests used SQLite memory.\n\nOriginal findings:\n'+''.join('- '+x['check']+': '+x.get('detail','')+'\n' for x in issues)+'\nRepair set: '+str(O)+'\nAll 85 staged checks pass; existing source retained.\n',encoding='utf-8')
print(f'{len(manifest)} separate replacements and guide ready. {len(checks)} PASS. Original source hashes unchanged.')
