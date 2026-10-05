# Arizona Outfits project analysis

Reviewed: 1 October 2026. Scope: the current working tree, including existing uncommitted changes, and `C:/Users/Laptronics.co/Downloads/arizonaoutfits (9).sql`.

## Assessment

This is a substantial Laravel application with a storefront, a publishing system, customer authentication and account management, payment processing, inventory, purchasing, supplier management, shipping records, and an administration portal. The structure is recognizable and several services contain thoughtful concurrency and idempotency safeguards. However, the current code has payment completion failures, ineffective role enforcement, publishing defects, and deployment inconsistencies. I would not consider this version ready for production until the high-priority findings below are addressed and integration checks pass.

This was an analysis, not a repair. Application source and the supplied database were not changed. Validation generated ignored frontend build files; this report is the review deliverable.

## Project map

| Area | Implementation and observations |
|---|---|
| Backend | Laravel 12, PHP requirement `^8.2`, Composer dependencies and lockfile. Local CLI is PHP 8.2.12. |
| Frontend | Blade views, Alpine, Axios, Vite; custom assets in `public/asset`, with additional styles/scripts embedded in views. |
| Publishing | Posts, hierarchical categories, author profiles, metadata, status/date fields, redirects, and revisions. Public article bodies use manually authored Blade templates. |
| Commerce | Products, categories, tags, options, variants, session cart, favorites, moderated reviews, coupons, checkout, order documents and tracking. |
| Payments | Stripe PaymentIntents and signed webhook handling; manual verification of bank transfers; full refund workflow. PayPal is listed as a dependency but the reviewed routes do not expose a completed PayPal checkout integration. |
| Customers | Password, Google/Facebook, and phone OTP flows; account security and login-method management. Phone delivery is a development stub. |
| Administration | Separate `admin` guard, admin users, roles/permissions, audit records, backups, analytics, email templates, pages and navigation. |
| Inventory/purchasing | Stock movements and alerts, valuation, reorder reports, suppliers, purchase orders, receiving, and returns. |
| Shipping | Shipment identities, normalized events and status synchronization. Courier webhook processing is an internal foundation, not a publicly routed provider integration. |
| Operations | Database queues, scheduler pruning tasks, XAMPP/Hostinger notes. README is largely the Laravel skeleton. |

Inventory: 85 controller files, 53 model files, 20 service files, 215 Blade templates, and 276 registered non-vendor routes. Counts describe this checkout, not deployment completeness.

## Verified checks

| Check | Result |
|---|---|
| PHP syntax | All 312 PHP files checked under app, routes, config, migrations and tests passed `php -l`. Blade templates and vendor code were outside this syntax count. |
| Route registration | `php artisan route:list --except-vendor --json` succeeded: 276 routes. Registration does not exercise controller actions. |
| Frontend compilation | `npm.cmd run build` passed using installed Vite 7.3.5; 55 modules transformed. |
| Existing tests | `php vendor/bin/pest --compact`: 24 failed, 1 passed. Feature tests fail during migration setup, before their application assertions. |
| Database inventory | Supplied SQL contains 72 tables and 101 migration names; every current migration filename was found in its recorded migration names. Includes post redirects/revisions and the author-profile upgrade. |
| CMS route matching | Local router checks for `/page/example` and `/page/shipping-policy` returned NotFoundHttpException. |
| HTML sanitization | Two harmless test strings containing unquoted and character-reference-obfuscated script URL attributes survived `CmsContentSanitizer::clean`. No scripts were executed. |
| Stripe dependency | Reflection confirmed that StripeWebhookController does not declare `couponRedemptionService`, despite calling it. |

The SQL dump was inspected as text. It was not imported, and its data was not restored into the local or production database. No real payment, refund, SMS, email, or external webhook was sent.

## High-priority findings

### 1. Stripe successful-payment processing calls an uninitialized service

Evidence: [StripeWebhookController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Webhooks/StripeWebhookController.php:44) and its call at [line 506](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Webhooks/StripeWebhookController.php:506).

The constructor injects InventoryService and OrderEmailService. The successful-payment handler later calls `$this->couponRedemptionService->redeemPaidOrder(...)`, but that property is neither declared nor initialized. The call is unconditional, so even an order without a coupon reaches it. It fails inside the database transaction; local stock/payment updates roll back and the webhook returns 500, although Stripe may already have collected the payment.

Inject CouponRedemptionService and verify successful payments with and without coupons, duplicate callbacks, and rollback behavior. This is the first payment blocker to fix.

### 2. Admin roles are stored but permissions are not enforced by the reviewed actions

Evidence: [AdminMiddleware.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Middleware/AdminMiddleware.php:46), [AdminController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Admin/AdminController.php:17), and [admin route group](C:/xampp/htdocs/ArizonaOutfits/routes/web.php:3015).

The middleware checks permissions only when a permission argument is provided. The routes and inherited controller middleware apply plain `admin`; no permission-specific middleware argument was found. Searching admin controllers/providers found no alternative Gate, authorize, or hasAdminPermission checks. Thus an active limited-role admin can reach sensitive actions such as user/role management, refunds, customer exports and backup downloads. AdminUserController accepts the super-admin flag, making missing authorization particularly serious.

Apply explicit server-side permissions to reads and writes. Restrict admin/role changes to the appropriate administrative permission and test a limited-role account. Hiding navigation alone is insufficient.

### 3. CMS HTML sanitization permits unsafe link attributes

Evidence: [CmsContentSanitizer.php](C:/xampp/htdocs/ArizonaOutfits/app/Services/CmsContentSanitizer.php:9), [page rendering](C:/xampp/htdocs/ArizonaOutfits/resources/views/pages/show.blade.php:5), and [AdminPageController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Admin/AdminPageController.php:66).

The regular expressions filter only some quoted URL forms and do not parse or decode attributes. These inputs survived unchanged: `<a href=javascript:alert(1)>test</a>` and `<a href="java&#x73;cript:alert(1)">test</a>`. CMS pages output content as raw HTML. This creates a stored XSS path when editable content contains such attributes; no browser exploit was executed during review. EmailTemplateService repeats a related filtering approach.

Use an HTML parser/purifier with an attribute and URL-scheme allowlist. Test encoded, unquoted and malformed attributes. Review style and image attributes as part of the same policy.

### 4. Stripe and bank-transfer checkout use different shipping rules

Evidence: [CheckoutController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/CheckoutController.php:782), [StripePaymentController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Payment/StripePaymentController.php:3542), and [shipping configuration](C:/xampp/htdocs/ArizonaOutfits/config/shipping.php).

Bank transfer uses the customer's selected economy/standard/express method and configured price. Stripe instead uses a hard-coded country table and free shipping at subtotal 400. For example, configured standard shipping costs 9.99, while a US Stripe order below the threshold uses 10 regardless of that selected method. Express selection can likewise produce a different charge from the displayed method.

Share a shipping quotation service across the displayed quote, both order creation paths, and pending-payment reuse. Persist the chosen method and authoritative quote together.

### 5. Payment capture can precede a stock or coupon failure with no compensation path

Evidence: [Stripe success handler](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Webhooks/StripeWebhookController.php), [InventoryService.php](C:/xampp/htdocs/ArizonaOutfits/app/Services/InventoryService.php:371), and [CouponRedemptionService.php](C:/xampp/htdocs/ArizonaOutfits/app/Services/CouponRedemptionService.php).

Stock is checked before checkout but deducted after successful Stripe payment. Coupon usage is also consumed after payment, and the redemption service throws if the limit has since been reached. Two customers can pay against the same last unit or remaining coupon usage. The second local transaction can fail after the provider has charged the customer. Once the missing service injection is fixed, these independent failure paths still remain.

Introduce stock/coupon reservations with expiry, or a documented compensating refund/reconciliation workflow. Persist a captured-but-unfulfilled state so provider retries are not the only recovery mechanism.

### 6. Successful Stripe callbacks lack a terminal refund/cancellation boundary

Evidence: [StripeWebhookController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Webhooks/StripeWebhookController.php) and [InventoryService.php](C:/xampp/htdocs/ArizonaOutfits/app/Services/InventoryService.php:86).

The success handler unconditionally writes payment_status `paid`. InventoryService returns without rededuction when inventory_restored_at exists. After the service-injection defect is repaired, a replayed success event following a refund can therefore overwrite the payment state while stock remains restored and order_status remains refunded. This is a code-path finding, not a live reproduced refund.

Make allowed payment transitions explicit. Protect terminal states and reconcile provider events by their meaning, rather than merely repeating an earlier success update.

### 7. Pending Stripe refunds are immediately treated as completed refunds

Evidence: [OrderRefundController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Admin/OrderRefundController.php).

The controller accepts both provider statuses `succeeded` and `pending`, restores inventory, and marks the local payment and order refunded in either case. Stripe webhook handling covers PaymentIntent events, not refund completion/failure events. A pending refund can later fail without a local transition reflecting that result.

Represent refund pending separately, retain the provider refund ID, and reconcile refund success/failure. The existing stable refund idempotency key is a useful safeguard to keep.

### 8. Supplier and receiving records still write admin IDs into customer foreign keys

Evidence: [PurchaseOrderReceivingController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Admin/PurchaseOrderReceivingController.php:172), [SupplierDocumentController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Admin/SupplierDocumentController.php:99), [SupplierRatingController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Admin/SupplierRatingController.php:31), and [SupplierPurchaseOrderDeliveryController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Admin/SupplierPurchaseOrderDeliveryController.php:152).

These actions use Auth::id() for received_by, uploaded_by, rated_by, sent_by and related ownership fields. With auth:admin, the authenticated default guard is the admin guard; the SQL dump still defines these fields as references to users. SupplierController also writes created_by this way. A matching numeric customer ID causes wrong attribution; an absent user ID causes a foreign-key error.

Complete the admin-ownership migration for these tables, relationships and views. Preserve legacy customer references explicitly instead of relying on numeric ID overlap.

## Publishing and public-route findings

### 9. CMS page route constraint rejects ordinary slugs

Evidence: [routes/web.php](C:/xampp/htdocs/ArizonaOutfits/routes/web.php:6362).

The slug regex ends with an escaped asterisk rather than the intended repetition operator. Ordinary single-word and hyphenated page URLs failed local router matching. Correct the constraint and verify actual database-backed pages as well as invalid slug rejection.

### 10. Scheduled articles have no automatic publication mechanism

Evidence: [Post.php](C:/xampp/htdocs/ArizonaOutfits/app/Models/Post.php:103), [PostController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Admin/PostController.php:3485), and [routes/console.php](C:/xampp/htdocs/ArizonaOutfits/routes/console.php:33).

Scheduled posts store status scheduled and a date, but the public scope accepts only status published with a reached published_at. No job, command or scheduler entry was found promoting scheduled posts. Reaching scheduled_at alone does not make them public.

Implement a scheduled publishing command or deliberately include due scheduled posts in the public scope; apply the same behavior to lists, article access, counts and metadata.

### 11. Public post lists do not consistently apply publication filtering

Evidence: [HomeController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/HomeController.php), [AppServiceProvider.php](C:/xampp/htdocs/ArizonaOutfits/app/Providers/AppServiceProvider.php:71), and [Post.php](C:/xampp/htdocs/ArizonaOutfits/app/Models/Post.php:96).

The homepage selects recent posts without published(). The global view composer calls latestPosts(), which only sorts; it does not restrict status/date. Category counts also count all posts. Depending on the consuming view, draft/archived/future article titles, images or links can appear publicly, even though the article controller blocks their bodies. Public views can consequently advertise inaccessible articles.

Use one public visibility scope consistently and count only publicly visible posts.

### 12. Publishing depends on a separately deployed Blade article body

Evidence: [BlogController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/BlogController.php).

Public rendering resolves blogs.posts.{template-or-slug} and returns 404 when that view does not exist. A database backup/restore alone cannot restore all article content. Admin template validation accepts a string without checking the file exists. This may be an intentional publishing model, but the admin workflow needs a template-existence check and deployment/backup instructions. Renaming an article should also preserve its body template deliberately.

## Testing, performance and deployment findings

### 13. SQLite defaults are incompatible with the migration chain

Evidence: [phpunit.xml](C:/xampp/htdocs/ArizonaOutfits/phpunit.xml:27), [.env.example](C:/xampp/htdocs/ArizonaOutfits/.env.example), and [password migration](C:/xampp/htdocs/ArizonaOutfits/database/migrations/2026_09_16_000001_add_multi_login_security_fields_to_users_table.php:22).

All 24 feature tests failed on `ALTER TABLE users MODIFY password VARCHAR(255) NULL`, which SQLite cannot execute. Further migrations use MySQL MODIFY and joined UPDATE statements; some services use GREATEST. The example environment also selects SQLite. Make the intended MySQL/MariaDB requirement explicit or make the complete schema/query chain portable. Never repoint RefreshDatabase tests at the normal working database.

### 14. Automated coverage is far smaller than the implemented domain

The existing tests are mainly Breeze account/profile tests plus examples. No dedicated payment, stock concurrency, coupon, refund, permission, supplier receiving, shipment or publishing feature tests were found. Some tests still submit `password` while the application defaults require stronger passwords. Fixing database setup will not by itself establish that the remaining suite passes.

Prioritize domain tests for the critical flows identified above, using a disposable production-compatible database for integration tests.

### 15. Linux deployment contains class/path capitalization mismatches

Evidence: [privacyPolicyController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/privacyPolicyController.php), [termsAndCondtionsController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/termsAndCondtionsController.php), and [app/jobs/SendSupplierPurchaseOrderEmail.php](C:/xampp/htdocs/ArizonaOutfits/app/jobs/SendSupplierPurchaseOrderEmail.php).

Routes reference PrivacyPolicyController and TermsAndCondtionsController while those filenames/classes start lowercase. The job namespace/import uses App\\Jobs but its directory is app/jobs. Windows hides filesystem case differences. PSR-4 lookup on a case-sensitive Linux checkout can fail; optimized classmaps may affect when this surfaces. Normalize names and verify a clean Linux deployment/autoload build before Hostinger rollout.

### 16. A wildcard view composer repeats database work and overwrites specialized data

Evidence: [AppServiceProvider.php](C:/xampp/htdocs/ArizonaOutfits/app/Providers/AppServiceProvider.php:71).

The composer runs for every public view/partial and executes category and latest-post queries each time. It also writes variables named popularCategories/latestPosts, potentially replacing the carefully filtered, eager-loaded values passed by BlogController. This adds repeated queries and contributes to the publication-filter issue above. Scope it to the actual layout/partials and share a request-local or cached result.

The reorder-count composer is registered for admin.layouts.sidebar, while the admin layout includes admin.partials.sidebar. Verify/fix that view-name mismatch if the count is expected in the sidebar.

### 17. Phone OTP delivery is intentionally local-only

Evidence: [PhoneAuthController.php](C:/xampp/htdocs/ArizonaOutfits/app/Http/Controllers/Auth/PhoneAuthController.php:836).

OTP delivery writes to logs in the local environment and throws outside local because no SMS provider is configured. Production phone signup/login/recovery cannot work as presented. Implement a real provider or disable the relevant UI until delivery is available. Keep OTPs out of production logs.

### 18. Backup export is not a consistent database snapshot or a complete recovery package

Evidence: [AdminBackupService.php](C:/xampp/htdocs/ArizonaOutfits/app/Services/AdminBackupService.php).

Tables are exported sequentially with offset-based chunking and no consistent-snapshot transaction. Concurrent orders/stock changes can produce mutually inconsistent records. The full archive includes the database, public storage and public/uploads; it excludes source, manual article Blade bodies and private files. The name full therefore requires a clear recovery contract. Synchronous web-request execution can also become slow as data grows.

Use a consistent snapshot/native dump or a tested equivalent, preserve a separate code release and required private documents, and run backups in background jobs. Test restoration into an isolated database.

### 19. The example environment contains a populated APP_KEY and incomplete commerce setup

Evidence: [.env.example](C:/xampp/htdocs/ArizonaOutfits/.env.example:3), [config/payments.php](C:/xampp/htdocs/ArizonaOutfits/config/payments.php), and [deployment notes](C:/xampp/htdocs/ArizonaOutfits/PHASE-14.2-XAMPP-HOSTINGER.txt).

Leave APP_KEY empty in the example and generate a unique key per installation. I did not compare or disclose the real .env key, so this does not establish that the deployed key is compromised. Payment variables expected by the configuration are absent from the example. The production notes put artisan beneath public_html without explaining that only public/ should be web-accessible. Confirm the actual document root and private application directory layout before deployment.

The README needs project-specific setup, supported database/runtime requirements, environment variables, queue/scheduler setup, storage links, manual article deployment, backup recovery, and validation instructions. Composer currently uses unrestricted `*` constraints for Stripe and PayPal; lockfiles pin today's installation, but future updates need deliberate review.

### 20. Public submissions and customer analytics need additional constraints

Reviews are moderated and duplicate checks exist, but the reviewed public review/form/order-tracking routes have no endpoint-specific throttling. The check-then-create review duplicate guard is also vulnerable to concurrent submissions without a suitable database guarantee. Add limits appropriate to these workflows and verify duplicate behavior.

CustomerController's total_spent includes pending/unpaid orders because it excludes only cancelled/refunded order statuses. If this metric is intended to represent actual customer spending, filter by paid payment state and define how refunds affect the metric. The customer detail action loads all orders/reviews rather than paginating them, which can become expensive for long-lived accounts.

## Database assessment

The SQL dump has a broad relational schema, indexes and foreign keys covering the principal domains. Useful uniqueness constraints exist for coupon order redemption, shipment provider event IDs, supplier-product identity, post redirects, post revision numbers and customer provider identities. All current migration names occur in the dump, which supports schema-history alignment but does not prove every schema or data invariant is correct.

Two variant_id migrations illustrate history drift: the earlier one adds an unsigned field/index; the later one adds a foreign key only if the column is absent. The supplied order_items constraints contain no variant foreign key. Deleted variants can leave dangling variant IDs, obstructing later stock restoration. Repair forward with a dedicated migration after checking existing orphan records; avoid changing already-applied history casually.

Legacy users-based ownership columns remain alongside new admins-based ownership. Some domains were migrated explicitly, while supplier/receiving fields were left behind. Complete this transition before relying on attribution, permissions or reporting. Old migration copies and database/migrations.zip also create ambiguity about the authoritative schema source.

No row-by-row business reconciliation was performed. Order totals versus item totals, inventory balances, missing media, orphan records, supplier balances and shipment consistency still require isolated database checks. Presence of migration names is not a substitute for a restore or reconciliation test.

## Existing strengths

- Customer and administrator authentication use separate guards and dedicated reset routes.
- Inventory deductions and restorations use order locks, conditional stock updates and persistent idempotency markers.
- Stripe webhooks verify the raw payload signature and compare provider amount/currency with the local order.
- Bank-transfer orders snapshot payment instructions rather than inheriting later setting changes.
- Order notification records provide durable queue handoff deduplication. Their sent_at denotes queued, not confirmed delivery, as the code documents.
- Public thank-you pages check ownership or the recent-order session marker. Order tracking requires an order reference and email.
- Reviews start pending moderation. Social linking deliberately avoids silent reconnection by email.
- Shipment processing contains provider identity, normalized-status, stale-event and deduplication safeguards. Its absence from public routes is appropriate until an authenticated provider adapter exists.
- Publishing has dedicated redirects/revisions, category hierarchy and media validation, though visibility and scheduling need consolidation.

## Recommended repair order

1. Fix Stripe dependency injection; enforce admin permissions; replace CMS sanitization.
2. Consolidate checkout/shipping and introduce captured-payment reconciliation, reservation/compensation, and terminal refund boundaries.
3. Complete supplier/receiving admin ownership and repair the CMS slug route.
4. Implement scheduled publishing and consistently filter public lists; validate manual templates.
5. Make database/runtime expectations explicit, repair test infrastructure, and add critical domain tests.
6. Verify Linux autoload paths, deployment document root, production phone behavior, queues and isolated backup restoration.
7. Reduce repeated view queries, paginate large admin datasets, correct spending metrics, and document operational procedures.

## Review limits

This is a broad static review with targeted local checks, not an exhaustive proof of correctness. I did not run a browser-based visual/accessibility review, restore the SQL dump, run clean-install MySQL migrations, exercise every admin action, inspect production infrastructure, audit every dependency advisory, or contact external providers. Frontend compilation and syntax success do not validate those areas. The strongest findings above are supported by source inspection and the listed reproductions; concurrency and external-payment failure findings describe concrete code paths that still need isolated integration tests.
