# Optimization round 3 — measured results

100 performance is achievable on several pages, but this round does not establish 100 on every page or every run. The table records the final measured runs, including scores below 100.

The real source files and php.ini were not overwritten. Install the 13 separate replacements in FILES.md after round 2; three are binary font files, the other ten are text replacements.

## Final Lighthouse runs

These are isolated preview results with gzip, optimized image copies and temporary PHP OPcache enabled. The PHP cache was warmed before each page pair. Each Lighthouse run starts a clean browser session.

| Page | Mobile performance | Desktop performance | Mobile accessibility | Desktop accessibility |
| --- | ---: | ---: | ---: | ---: |
| Home | [99](home-final-mobile.json) | [100](home-final-desktop.json) | 100 | 100 |
| About | [98](about-final-mobile.json) | [99](about-final-desktop.json) | 100 | 100 |
| Blogs | [97](blogs-final-mobile.json) | [100](blogs-final-desktop.json) | 100 | 100 |
| Shop | [100](shop-final-mobile.json) | [100](shop-final-desktop.json) | 100 | 100 |
| Contact | [99](contact-final-mobile.json) | [100](contact-final-desktop.json) | 100 | 100 |
| Login | [99](login-final-mobile.json) | [100](login-final-desktop.json) | 100 | 100 |
| Favourites | [99](favorites-final-mobile.json) | [100](favorites-final-desktop.json) | 100 | 100 |

Best Practices scored 100 in these final runs. Public Home, About, Blogs, Shop and Contact SEO scored 100. Login and Favourites retain their intentional noindex policy; their lower SEO scores are expected and have not been changed to expose those pages.

## Changes

- Closed mega-menu links are inert, preventing keyboard focus inside hidden navigation. Opening the menu restores interaction.
- Icon-only About and Contact links now have accessible names. Login’s visible brand link is no longer hidden from assistive technology.
- Shop filter help text has stronger contrast.
- Storefront icon CSS is about 17 KB rather than about 100 KB. The three local subset fonts total about 23 KB instead of about 233 KB. The included Font Awesome licence must remain with the fonts.
- About, Blogs and Contact hero backgrounds are preloaded at the appropriate mobile/desktop width.
- Home newsletter animation pauses offscreen and respects reduced-motion preferences. Visible animation remains available.

## Local server difference

The actual port-8000 Contact baseline transferred about 170 KB of uncompressed HTML. The isolated preview compresses that response. Follow INSTALL.txt to check XAMPP compression and the previous OPcache guide, then measure your own server again. PHP supports automatic compression through its zlib.output_compression configuration; see the [official PHP documentation](https://www.php.net/manual/en/zlib.configuration.php). The real php.ini has not been edited.

## Validation and remaining work

Responsive browser checks: 56 page/width combinations, 0 failures and 0 JavaScript errors. See browser-checks.json.

Customer checks use the existing synthetic customer fixture and assess rendering, not real authenticated database latency. Other public pages have the earlier round-2 measurements; they were not all rebenchmarked after this shared layout update. Full live-domain PageSpeed Insights and authenticated production measurements remain to be checked.

The final measured mobile performance gaps are mainly loading/rendering time and remaining main-thread work. A score of 100 is still a target on those pages. These changes do not guarantee every Google Ads, security, payment or SEO requirement simply because an automated category scores 100.
