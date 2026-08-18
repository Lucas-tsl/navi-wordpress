=== Saito Navi ===
Contributors: lucastsl
Tags: woocommerce, cookie consent, accessibility, sticky add to cart, stories
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires Plugins: woocommerce

One floating button for cookie consent, accessibility, sticky add-to-cart and product video stories.

== Description ==

Saito Navi gathers several customer engagement modules for WooCommerce
behind **a single floating button** (gear icon, corner of the screen):

* **Cookie consent** — GDPR banner, Google Consent Mode V2, preferences
  modal, logo auto-detected from the site identity if no URL is set.
* **Accessibility** — language switcher (WPML-compatible, falls back to
  GTranslate if WPML is absent), text size, high contrast, enlarged
  cursor, underlined links.
* **Sticky add-to-cart** — a panel that follows the visitor on the
  WooCommerce product page, works with simple and variable products, a
  keyboard-accessible swatch selector, compatible with WCBoost Variation
  Swatches, and a custom CSS selector setting for themes not covered by
  the built-in fallback chain.
* **Stories** — up to 4 video bubbles per product (YouTube or an
  uploaded MP4), a dedicated tab on the WooCommerce product page, a
  desktop panel with a pure-CSS phone mockup (no external image), and a
  mobile full-screen stories-style view.

Built from the ground up to welcome new modules without touching the
core: each module registers itself and talks to the central button
through a generic event, and no module knows about the others.

Everything is configurable from the Back Office (**Navi** menu): colors,
corner radius, button position, and for each module its own "Show on
desktop" / "Show on mobile" setting.

Sibling of [Navi for PrestaShop](https://github.com/Lucas-tsl/navi-prestashop)
— same name, same spirit (one hub rather than independent widgets), two
separate implementations adapted to each ecosystem.

= External services and embedded content =

* **Google Consent Mode V2**: the Cookie consent module pushes the
  visitor's choices into `window.dataLayer` (the standard Google Tag
  Manager/gtag.js mechanism). The plugin itself never sends any request
  to a Google server — the site must already have gtag.js/GTM in place
  for this signal to be used.
* **YouTube ("no-cookie" mode)**: the Stories module displays configured
  videos via `youtube-nocookie.com` in an iframe, only on product pages
  where a YouTube story has been configured by the site administrator.
  See the [YouTube privacy policy](https://policies.google.com/privacy).

== Installation ==

1. Upload the `navi` folder to `/wp-content/plugins/`, or install
   directly from **Plugins > Add New**.
2. Activate the plugin from the **Plugins** menu.
3. WooCommerce must be installed and active (required for the Sticky
   add-to-cart and Stories modules; the Cookie consent and Accessibility
   modules work without it).
4. Configure the modules from the new **Navi** menu in the Back Office.

== Frequently Asked Questions ==

= Is WooCommerce required? =

The core and the Cookie consent / Accessibility modules work without
WooCommerce. The Sticky add-to-cart and Stories modules are tied to the
WooCommerce product page and therefore require it to be active — a
notice is shown in the Back Office if WooCommerce is missing or inactive.

= Where are uploaded MP4 story videos stored? =

In the standard WordPress uploads folder
(`wp-content/uploads/navi-stories/`), never inside the plugin folder —
the latter can be overwritten on every plugin update, unlike the uploads
folder.

= Does the Accessibility module's language switcher work without WPML? =

Yes: if it detects the GTranslate plugin installed, it falls back to it;
otherwise the language switcher simply doesn't appear (the other
accessibility settings remain available).

= Does the sticky add-to-cart work with my theme? =

A fallback chain of CSS selectors covers standard WooCommerce markup and
the most common themes. If your theme has an unusual structure, a
"custom CSS selector" setting per field (price, name, image) is
available under Navi > Cart.

== Screenshots ==

1. Navi dashboard (Back Office): module activation, floating button
   position, appearance (colors, corner radius).
2. Navi menu in the Back Office, with the plugin icon.
3. The Navi floating button and its module menu (front-end).
4. Sticky add-to-cart panel on a variable product page, swatch selector.
5. Cookie preferences (GDPR, Google Consent Mode V2) on the visitor side.
6. Stories tab on the WooCommerce product page (Back Office).
7. Stories module settings, with a live preview of the phone mockup.
8. Story video bubble and desktop panel (phone mockup) on the product
   page.

== Changelog ==

= 0.4.0 =
* New Stories module: product video bubbles (YouTube/MP4), desktop
  panel, mobile full-screen view, appearance settings under Navi >
  Stories.

= 0.3.0 =
* Appearance parity with Navi for PrestaShop: configurable corner
  radius, per-module visibility by device, plugin logo as the admin
  menu icon.

= 0.2.0 =
* Sticky add-to-cart module: simple and variable products, swatch
  selector, custom CSS selector settings.

= 0.1.0 =
* Initial release: core (3-state floating button), Cookie consent and
  Accessibility modules.

== Upgrade Notice ==

= 0.4.0 =
Adds the Stories module (product video bubbles) — no action required on
upgrade.
