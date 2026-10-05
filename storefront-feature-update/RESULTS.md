The update is prepared as 38 complete replacement text files and two original PNG image assets. The live application source and database have not been replaced.

- Category header links resolve child categories, retain parent navigation, support desktop hover/focus, and expose expandable child links in the four-column mobile menu.
- Home Popular and Trending sections use separate sets from a stable catalog shuffle. The unrelated About IDEOSTREAM section is removed.
- Latest, Popularity and Best Sellers use the shared shuffle. Product creation advances its durable database generation; editing and refreshing preserve it. Other requested sorts retain their calculations.
- Category banners use their existing dashboard Featured Image, category H1, maximum width 1400px and height 500px.
- Contact uses the shared profile and latest-article sidebar without an Author label.
- Articles use `/blog/slug`; legacy article paths redirect. All five existing article bodies are preserved. Shared sidebars, six related article cards, reviews, and footer appear in the requested order; the newsletter form is removed.
- Product metadata is reduced to SKU, Availability and Categories. The variant information table omits Stock.
- Product and quick-view buttons retain purchase labels before selection, validate options when submitted, automatically select a sole available variation, and continue to block actual out-of-stock variants.
- Custom Size exposes five required inch measurements. Server validation, separate cart keys, shared variation stock checks, order serialization, and existing order-option displays retain them.
- Review titles are removed from submission, validation, model, search, display and the current database schema through a new migration.
- The size-chart popup supports separate Men/Women images and keyboard-accessible tabs. Both supplied original charts are included, with PNG paths configured for the Men/Women tabs.

Verification: 35 backend/Blade/routing checks, cart-controller and order serialization checks, 17 Chrome interaction checks, and 40 responsive checks across 10 fully rendered pages at 1440/880/600/390px. Responsive checks found no horizontal overflow or JavaScript exceptions. Category banners measured 500px at every tested width; desktop hover, mobile submenu expansion, size-chart tabs and Escape closure passed.

Tests use isolated fixture databases and browser-rendered fixture pages. Existing MySQL-specific legacy data migrations were adapted or omitted only in the empty SQLite render fixture. This is not a production payment-provider test or a new Lighthouse/Google Ads certification.

Install every replacement together and run the two new migrations using [INSTALL.txt](INSTALL.txt). See [FILES.md](FILES.md) for exact destinations.
