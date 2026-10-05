# Arizona Outfits audit — 3 October 2026

The audit produced 63 separate replacement text files. **They have not been installed into the original application.** Open [the file list](replacement-files/FILES.md) and [installation guide](replacement-files/INSTALL.txt). This is a broad audit with reproducible findings; it is not a guarantee that no undiscovered issues remain.

## Scope and evidence

- 580 extracted source files matched the installed project; the audit left original application source unchanged.
- 277 routes reviewed, including administrator middleware and permission mappings.
- 524 custom regression checks passed across administration, content, customers, checkout, purchasing, inventory, notifications, settings, security fixes, SEO and signed Stripe events.
- All 238 Blade templates compiled in the isolated harness. Original PHP/JavaScript syntax checks passed; final replacement PHP/JavaScript syntax checks also passed. The 63 text files match their staged source versions and have refreshed SHA-256 hashes in manifest.json.
- Earlier browser checks passed 16 page/viewport combinations at 1440, 880, 600 and 390 pixels, including carousel widths and next-button movement, with no JavaScript errors.
- Isolated compatible dependency updates: Composer reported zero advisories and npm zero vulnerabilities; the production asset build passed. This result concerns the prepared updated dependencies, not the unchanged installed dependencies.
- The legacy Pest suite is **not green**: 24 failures were blocked by MySQL-specific migrations running on SQLite; one test passed. The custom checks do not replace that entire suite.

## Prepared changes

Security: private supplier-document storage and verified migration, backup inclusion of private documents, safe spreadsheet cell binding, reserved/colliding public slug checks, request limits, saved and legacy HTML sanitization, stricter image filename/type validation and MIME-derived stored extensions, updated vulnerable dependencies, and stronger public asset rules.

Consistency: CMS blog-data fallback, settings cache invalidation after saves, corrected missing-image placeholder, real related-post fallback, removal of dummy article cards, and corrected JavaScript null guards and global animation-name collision.

Responsive design: home latest/popular products and recent blogs, privacy/terms recent blogs, and custom blog related cards use two visible cards at widths up to 880px and one up to 600px. Desktop related sections show up to six available real published posts. Controls support buttons, keyboard and native swipe; motion preferences are respected. Existing desktop grids remain in use.

SEO: centralized page metadata, canonical URLs, social metadata, valid article/product/organization structured data, approved review data, noindex treatment for private/filter pages, pagination canonicals, chunked XML sitemaps and robots rules. Placeholder business text still needs owner review.

Performance: reusable WebP derivatives, appropriately sized cards/thumbnails, image fallbacks, local asset versioning, deferred scripts, conditional phone-library loading, homepage critical CSS, compression and caching rules. New images use originals until media:optimize is rerun. The optimizer retains originals.

Stripe: signature/event checks, actual amount_received verification, current intent matching, replay handling and consistent currency conversion. Unsupported three-decimal store currencies fail closed rather than silently charging incorrect amounts.

## Lighthouse results and limits

Earlier completed local reports with the prepared changes and simulated compression:

| Page | Device | Performance | Accessibility | Best practices | SEO |
|---|---|---:|---:|---:|---:|
| Home | Desktop | 99 | 100 | 100 | 100 |
| Shop | Mobile | 100 | 100 | 100 | 100 |
| Blog | Mobile | 91 | 100 | 100 | 100 |
| Product categories | Mobile | 95 | 100 | 100 | 100 |
| Privacy policy | Mobile | 83 | 100 | 100 | 100 |

The final homepage mobile run failed to produce a valid performance score. Earlier mobile homepage runs showed a layout shift; a footer-position fix is prepared, but its final mobile verification is incomplete. Do not consider homepage mobile performance passed.

On resuming at approximately 21:53 Pakistan time, the read-only preview returned database-connection errors because MySQL at 127.0.0.1:3306 was unavailable. The final Lighthouse/browser rerun could therefore not verify the latest state. Earlier browser results remain evidence for the previous successful run, not a substitute for that rerun.

These are local lab results, not public PageSpeed Insights field results or Google Ads approval. Compression was simulated because PHP's development server does not execute Apache .htaccess rules. Actual hosting modules, HTTPS, latency and caching must be checked after deployment. Field INP was not measured. [Google's Core Web Vitals criteria](https://web.dev/articles/vitals) require real-user evaluation; a Lighthouse SEO score does not guarantee indexing or ranking.

## Remaining release gates

1. **Stripe stock/payment reconciliation:** a signed payment-success event can arrive after stock/coupon availability changes. The local check fails closed, but funds may already have been captured. A reservation/reconciliation/refund workflow is still required; complete live-server webhook, retry and refund testing before accepting live Stripe payments.
2. Restore MySQL availability and rerun the final browser and homepage mobile Lighthouse checks. Check the Tailwind 4 asset build visually throughout administrator/customer forms.
3. Verify production email delivery, SMS provider setup, and a complete backup restoration. These were not established by local fixtures.
4. Validate Apache private-file access, active-file restrictions, compression, cache headers and HTTPS document-root configuration on the real host.
5. Replace placeholder/Lorem content with accurate business, product and policy information. Review real contact, shipping, returns and payment information for [Google Ads destination requirements](https://support.google.com/adspolicy/answer/16427615?hl=en). No Ads/Analytics account or conversion tracking was connected during this audit.
6. Submit the real-domain sitemap and inspect canonical/indexing behavior in Search Console. Review [Google's ecommerce URL guidance](https://developers.google.com/search/docs/specialty/ecommerce/designing-a-url-structure-for-ecommerce-sites).

Do not deploy the complete-project-audit directory: it contains test tools, copied dependencies and copied media. Install the application replacements using the guide. No database wipe or application-key regeneration is required.
