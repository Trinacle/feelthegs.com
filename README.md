# Feel The G's — WordPress Theme

Bespoke dark-editorial child theme of **Astra** for [feelthegs.com](https://feelthegs.com) — an adult boutique (sex toys, lingerie, bondage). Purple + gold brand system, light/dark toggle, mega menu, and a **Fantasies-Boutique-style collection filter rebuilt natively for WooCommerce**.

## Stack
- **Parent:** Astra (we strip most of its output)
- **Design system:** Vanilla CSS custom properties — one stylesheet (`assets/css/styles.css`)
- **JS:** Vanilla (no jQuery) — mega menu, reveals, theme toggle, filter slider
- **Fonts:** Inter (body) + Inter Tight (display) via Google Fonts
- **Filters:** WC product attributes as facets + per-category config (ACF on `product_cat`)
- **SEO:** Yoast (filtered, never duplicated) + JSON-LD schema
- **Deploy:** GitHub Actions → SSH rsync to PrivateSystems (LiteSpeed cache)

## Architecture
```
feelthegs-theme/
├── style.css              Theme header + :root design tokens (purple #7034fd + gold #fedb62)
├── functions.php          Setup, WC, query filters, Yoast, schema, wrapper swap
├── header.php             Nav + categories mega menu (built from live WC cats) + cart
├── footer.php             Newsletter, footer grid (live WC cats), theme toggle
├── front-page.php         Retail homepage: hero, categories, new arrivals, deals, CTAs
├── 404.php / home.php / single.php / search.php / searchform.php / category.php
├── woocommerce/
│   ├── archive-product.php  Shop/category archive: filter sidebar + WC native grid
│   └── single-product.php   Product detail (sticky gallery, tabs, related)
├── inc/
│   ├── enqueue.php          CSS/JS enqueues + ajaxUrl
│   ├── acf-fields.php       Per-category "Collection Filter Config" field group
│   ├── filters.php          FTGS_Collection_Filter_Widget + price slider + URL builders
│   └── helpers.php          Shop card, cart link, active chips, CTA, sdn_ aliases
└── assets/
    ├── css/styles.css       Full design system (~1600 lines) — accent ramp at top
    ├── js/main.js           Mega menu, reveals, theme toggle, newsletter, search
    └── js/filter.js         Price slider, collapsible groups, mobile drawer
```

## The collection filter
Recreates Fantasies Boutique's Shopify filter for WooCommerce — **better** than the original:

| Fantasies Boutique (Shopify) | Feel The G's (WooCommerce) |
|---|---|
| Tag-intersection URLs `/collections/h/x+y` | WC attribute facets `?filter_color=black,red` |
| N parallel AJAX calls merged client-side | Single server query (no fan-out, no dupes) |
| Per-collection `metafields.custom.*` | Per-category ACF "Collection Filter Config" |
| JS-dependent results | Works without JS (progressive enhancement) |
| OR-within / AND-across via cartesian | Native WC layered-nav (same semantics) |

Filter groups mirror Fantasies: **Price slider**, **Color** (swatches), **Size**, **Material**, **Type**, **Brand**, **Features**, **On sale** — each toggleable per category.

## Design tokens
Kept the `--green*` token names (so `styles.css` works untouched) but swapped values to the Feel The G's purple `#7034fd` ramp + gold `#fedb62` accent. Re-theme by editing the `:root` blocks in **both** `style.css` and `assets/css/styles.css`.

## Deploy
1. Push to `main`.
2. GitHub Actions rsyncs to `${{ secrets.DEPLOY_PATH }}` on the server.
3. Cache-bust by bumping `FTGS_VERSION` in `functions.php`.
4. Verify: `curl 'https://feelthegs.com/?nc=<ts>'` → check `ver=<FTGS_VERSION>` in HTML.

## Verify after deploy
- `ver=X.Y.Z` present in CSS/JS URLs (cache-busted)
- `</html>` present (no PHP fatal truncated output)
- Filter sidebar renders on a sample category
- Mobile drawer opens/closes
- LiteSpeed purged (`?litespeed_purgeall=1` if needed)
