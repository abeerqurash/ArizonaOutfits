# Optimization round 2 results

Prepared as 22 separate full replacement text files. The application source and real php.ini have not been overwritten. Follow INSTALL.txt and FILES.md.

## Measured performance

Latest available Lighthouse runs for the isolated preview; mobile and desktop are separate runs. Home and article final runs include temporary PHP OPcache. Other rows are earlier runs from this round and were not rerun after every shared styling change. Scores vary; these results do not guarantee a score on the deployed server.

| Page | Mobile | Desktop |
| --- | ---: | ---: |
| Home | [96](home-final-mobile.json) | [100](home-final-desktop.json) |
| About | [98](about-mobile.json) | [99](about-desktop.json) |
| Blogs | [99](blogs-mobile.json) | [100](blogs-desktop.json) |
| Shop | [100](shop-mobile.json) | [100](shop-desktop.json) |
| Contact | [100](contact-mobile.json) | [100](contact-desktop.json) |
| Login | [96](login-mobile.json) | [99](login-desktop.json) |
| Individual article | [97](article-final-mobile.json) | [100](article-final-desktop.json) |
| Individual product | [97](product-mobile.json) | [100](product-desktop.json) |
| Product categories | [100](categories-mobile.json) | [100](categories-desktop.json) |
| Blog categories | [100](blogcategories-mobile.json) | [100](blogcategories-desktop.json) |
| Individual product category | [100](productcategory-mobile.json) | [100](productcategory-desktop.json) |
| Individual blog category | [100](blogcategory-mobile.json) | [100](blogcategory-desktop.json) |
| Privacy policy | [99](policy-mobile.json) | [100](policy-desktop.json) |
| Terms and conditions | [98](terms-mobile.json) | [99](terms-desktop.json) |
| Cart | [100](cart-mobile.json) | [100](cart-desktop.json) |
| Register | [100](register-mobile.json) | [100](register-desktop.json) |
| Forgot password | [99](forgot-mobile.json) | [99](forgot-desktop.json) |
| Order tracking | [97](tracking-mobile.json) | [98](tracking-desktop.json) |
| Sale | [100](sale-mobile.json) | [100](sale-desktop.json) |
| Phone login | [99](phonelogin-mobile.json) | [100](phonelogin-desktop.json) |
| Phone register | [98](phoneregister-mobile.json) | [99](phoneregister-desktop.json) |
| Thank you | [100](thanks-mobile.json) | [100](thanks-desktop.json) |
| Customer overview (fixture) | [100](customer-mobile.json) | [100](customer-desktop.json) |
| Customer orders (fixture) | [100](customerorders-mobile.json) | [100](customerorders-desktop.json) |
| Customer profile (fixture) | [100](customerprofile-mobile.json) | [100](customerprofile-desktop.json) |
| Customer security (fixture) | [99](customersecurity-mobile.json) | [100](customersecurity-desktop.json) |

## Changes and validation

- Previous/next arrows above the right edge of responsive card collections; two cards at 880px and below, one at 600px and below.
- Dashboard-managed navigation in the mobile mega menu, four links per row before categories.
- Responsive WebP backgrounds and thumbnails, deferred phone widgets, paused offscreen animations, full initial layout styles and font-display overrides.
- Browser checks: 56 page/width combinations at 1440, 880, 600 and 390px; 0 HTTP/carousel/overflow failures, 0 mobile menu failures, 0 JavaScript errors. See browser-checks.json.
- Customer overview, orders, profile and security use a synthetic customer and order fixture. These verify rendering/responsiveness, not real authenticated database performance or all account actions.
- Missing legacy /projects, /services and /abeerkhan templates now return 404 through controller guards instead of a 500 error. Those routes are not performance passes.

## Important limits

The preview emulates gzip and static cache headers and uses optimized image copies. Run media:optimize after installation. The final Home/article tests also use PHP OPcache; follow XAMPP-PHP-SETTINGS.txt. php artisan serve ignores Apache .htaccess, so its compression/cache behavior can differ from Apache or live hosting.

This round is a performance and responsive UI check, not proof that every security, payment, email, customer action or Google Ads requirement passes. Live HTTPS PageSpeed Insights and real authenticated dashboard measurements remain to be checked after installation/deployment. Private login/account pages should retain noindex even when Lighthouse gives a lower SEO score. No live Google Ads approval or Core Web Vitals field-data pass is claimed.

Full storefront CSS is embedded in storefront-styles.blade.php; future style.css edits must be reflected there. Original images remain available. No database migration/reset or APP_KEY change is needed.
