# Handoff — CosyPaw theme: cart & checkout upsell (2026-10-14), promo clips, gift bundle picker, Cuddle Puff "Cvetić" pillows

State as of 2026-10-15, `main` at `6d724ca` (pushed, deployed to cosypaw.rs, deploy run `38644091753` = `success`). The working tree is clean except for this file, which is untracked on purpose. The feature branch `feat/upsell-everywhere` still exists locally (merged).

# Goal

Four pieces of work are live. The first was built and shipped in the latest session (2026-10-14); the other three are unchanged and still have open checks.

0. **Cart and checkout upsell (2026-10-14).** The owner sent a screenshot of "Zbir korpe" (Besplatna dostava: Još 1.210 RSD / Ukupno 790 RSD) and asked, in Serbian:
   - an upsell block in the cart **and** on the checkout also when free delivery is already earned;
   - an upsell when the cart is empty;
   - an upsell after a product has been removed;
   - a note in "Zbir korpe" that postage costs **500–700 RSD**, so buyers understand why ordering more pays off.
   All four are implemented, merged, deployed and checked on the live site in a headless browser. See *Cart and checkout upsell* below.
1. **Promo clips (2026-10-12).** The owner supplied short vertical promo videos and asked for them on the site, in three requests:
   - "Bundeva i Krofna promo" into the single product pages.
   - Then the same for "Sova promo i Konjić promo".
   - Then, on the landing page, replace the two photographs in the section "U tvom domu" with the Krofna promo and a new clip (`Milana_Ves-masina.mp4`).
   All three are implemented, merged, deployed and checked on the live site in a headless browser. What remains is a look on a real phone and a few owner decisions.
2. **Gift bundle picker ("Složi svoj paket").** Live since 2026-10-09. Its end-to-end checks on the live site are still open.
3. **Cuddle Puff jastuk Cvetić.** Live since 2026-10-11. A few defaults are still unconfirmed.

**Not covered by any handoff:** five other feature commits landed on `main` between the 2026-10-12 handoff and this session, from other sessions (see *Changes Made → Other sessions*). This file only names them; their details were not reviewed.

# Current State

## Cart and checkout upsell — implemented, deployed, live (2026-10-14)

All of it lives in `Theme\Upsell` (`inc/Upsell.php`), which already existed; this session changed what it says and when it is silent. The cart page is **https://cosypaw.rs/cart/** (not `/korpa/`, which is a 404) and the checkout is **https://cosypaw.rs/checkout/**.

**What was already live before this session** (found by a live headless check first, not changed):
- The cart had the strip ("Još malo, pa povoljnije", the next towel's price, the progress bar, a slider of towel motifs not in the cart) under the totals.
- The empty cart already showed "Možda vam se svidi" with the whole strip, also after an AJAX removal of the last item.
- The gap was: once free delivery was earned **and** the cart had no cheaper next towel (a package optimum), `offer()` printed nothing, on the cart and the checkout alike. No postage price was quoted anywhere.

**What changed:**
- **The strip no longer goes silent.** `offer()` now returns nothing only when there is no cheaper next towel, no delivery gap **and** no motif left to offer (every towel already in the cart). Heading:
  - next towel cheaper or delivery gap → "Još malo, pa povoljnije" (unchanged);
  - delivery earned, no towel argument → "Dostava je na nama";
  - neither (no free-delivery method configured, or no shipping needed) → "Možda vam se svidi".
- **Earned line.** When delivery is earned the panel prints `<p class="cosypaw-upsell__ship cosypaw-upsell__ship--earned">` "Besplatna dostava je ostvarena — sve što dodaš stiže bez poštarine." — under the towel line if there is one.
- **Postage 500–700 RSD in "Zbir korpe"** (`cart_shipping_row()`, the row above "Ukupno"):
  - below the threshold: value "Još X RSD", then `<small>` "Dostava je besplatna preko 2.000 RSD." (or "Dodaj još jedan peškir i pređeš 2.000 RSD — dostava je onda na nama." when one towel closes the gap), then a second `<small>` "Ispod toga poštarinu plaćaš kuriru, 500–700 RSD.";
  - earned: value "Ostvarena", then `<small>` "Ne plaćaš poštarinu od 500–700 RSD." (before this session the earned row had no note at all).
- **Postage on the strip's delivery line** (`shipping_line()`, formerly `shipping_bar()`): "Još 1.210 RSD do besplatne dostave — umesto 500–700 RSD poštarine kuriru". The checkout now prints this line too (without the bar); before, the checkout showed no delivery line.
- **The range is one constant:** `CheckoutSetup::COURIER_COST = array( 400, 600 )`, read through `CheckoutSetup::courier_cost()` (filter `cosypaw_courier_cost`). It returns `array()` when the range is invalid, and every postage sentence then disappears. A single value (e.g. `array( 500, 500 )`) prints "500 RSD".
- **A removed towel leads the strip.** `offer_motifs()` puts motifs from `WC_Cart::get_removed_cart_contents()` (most recent first) at the front, on the filled cart, the checkout and the empty cart. Only towel motifs; a removed package or pillow is not offered back.
- **Translations:** five new msgids with English and Russian in `tools/build-translations.php`; `.po`/`.mo` rebuilt.
- **CSS:** one rule, `.cosypaw-upsell__ship--earned { margin: 4px 0 0; }` in `assets/css/woocommerce.css`.

**Verification:**
- **Tests:** `OK (203 tests, 1111 assertions)` locally at `f111ef9`. New or changed in `tests/UpsellTest.php`: `test_the_row_states_a_win_plainly` (now expects the postage note), `test_the_totals_row_quotes_the_postage`, `test_a_complete_package_still_gets_the_strip` (replaced `test_a_complete_package_gets_no_panel`), `test_won_delivery_keeps_the_strip`, `test_the_panel_prices_the_postage`, `test_a_removed_towel_leads_the_strip`. `tests/stubs.php`: `WC_Cart` gained a public `$removed` and `get_removed_cart_contents()`.
- **Before the deploy:** the new markup (rendered by a throwaway PHPUnit test) was injected into the live cart page with the local `woocommerce.css` inlined, at 1280px and 390px, in both the "gap" and the "earned" state. Layout fine, no horizontal overflow.
- **After the deploy, live, headless Chrome, 2026-10-14** (one fresh session cart):
  - 1 towel (Krofnica, 790): row "Još 1.210 RSD" + both notes; strip with towel line and "…umesto 500–700 RSD poštarine kuriru".
  - 4 towels (Krofnica, Sovica, Konjić, Bundeva): row "Ostvarena" + "Ne plaćaš poštarinu od 500–700 RSD."; strip still shown with the earned line.
  - `/checkout/` with the same cart: strip at the top with the earned line.
  - After removing Krofnica via the cart's AJAX remove: row "Još 510 RSD"; strip re-rendered, **Krofnica first**.
  - No JavaScript errors.
- **Built assets on the live site now:** `app-BVpNx7Xk.js`, `app-ByEAP6LN.css`, `woocommerce-DiOXvdMr.css`.

### Not verified for the upsell (NEEDS VERIFICATION)
- The checkout page **visually**: only its text was read live. It uses the same markup and CSS as the cart strip.
- A real phone and Safari.
- The English and Russian cart and checkout.
- The "Dostava je na nama" heading on the live site: the 4-towel test cart still had a cheaper next towel, so it showed "Još malo, pa povoljnije" + the earned line. The "Dostava je na nama" variant was seen only in the pre-deploy render.
- How long WooCommerce keeps `removed_cart_contents` in the session (reasoned: until the cart is emptied or the item restored; not checked).

### Decisions made without item-by-item confirmation
The owner saw the summary and answered "go" before the merge; reading that as acceptance is this session's interpretation.
- **Only towel motifs** are offered in the strip, as before. Pillows, cloths, long towels and bundles are not.
- **"ti" form** in the new copy ("plaćaš", "dodaš", "Ne plaćaš"), matching the existing "pređeš". The empty-cart heading "Možda vam se svidi" (vi form) was kept.
- **500–700 RSD is quoted, never charged.** The courier rate stays at 0 and the buyer pays the courier; the number is the owner's.
- **A cart with every towel and a package optimum gets no box** (the empty slot stays for `CartUpsell.js`). Unreachable in practice with 20 motifs.

### Known limitations of the upsell
- The shipping-method label stored in the database, "Dostava kurirskom službom (poštarina se plaća kuriru)", was not changed and does not quote the range. Changing `COURIER_LABEL` in code would not reach the live zone (database).
- The announcement bar, the gift banner, the FAQ and the package cards still say only "Besplatna dostava preko 2.000 RSD" without the postage price.
- The thank-you page picks (`thankyou_picks()`) are unchanged.
- `footer.php` still contains a **cart drawer** (`assets/js/components/CartDrawer.js`). Its own docblock calls it a client-side demo cart in `sessionStorage`; the header cart icon on the live site links to `/cart/`. It has a plain empty state and no upsell. Not changed.

## Promo clips in the product gallery — implemented, deployed, live

- **Four towels have a clip:** Bundeva (`/product/bundeva/`, product 197, motif `bundeva`), Krofnica (`/product/peskiric-krofnica/`, 59, `krofna`), Sovica (`/product/peskiric-sovica/`, 22, `sova`) and Konjić (`/product/peskiric-konjic/`, 108, `konjic`).
- **Where it sits.** The clip is the last slide of the product's own WooCommerce gallery. Each of these towels had one photograph, so the gallery now has two slides and a thumbnail strip under the photograph; the clip's thumbnail carries a play badge.
- **How it plays.** Muted, looping, no native controls. It starts when its slide is on show and stops when the visitor goes back to the photograph. The mp4 is not requested until then (`preload="none"`, poster only). A pause/play button sits bottom-left; a pause the visitor asked for sticks.
- **Reduced motion.** Where `prefers-reduced-motion: reduce` is set, the clip does not start by itself and the button shows "play".
- **Shape.** The clips are 9:16. The slide takes the shape of the product's photograph (read from the `woocommerce_single` image size: 600×900 for Bundeva, 600×800 for the other three), so the gallery does not change height between slides. The clip is cropped into that box; each clip has a focus point that says which part of the frame survives.
- **Lightbox and zoom.** The clip is not in the lightbox. The magnifier button is hidden while the clip's slide is active.
- **How a product is matched.** Through the motif id it is stored under in the option `cosypaw_product_map`, looked up in `ProductVideos::CLIPS`. The files ship with the theme in `assets/videos/`, three per clip: `<id>.mp4`, `<id>.webp` (poster) and `<id>-th.webp` (200×200 thumbnail).

## Clips on the landing page ("U tvom domu") — implemented, deployed, live

- The section `#dom` on the front page shows two clips where it showed two photographs: the Krofna promo (the same file the product page plays) and `ves-masina` (towels on and inside a washing machine, with the line "POV: ušla si da kupiš samo jedan, a izašla sa…" written across the frame, ending on a CosyPaw logo card).
- **Layout change.** The cards are 9:16 instead of 4:5, so both clips are shown whole. The pair is held to a 760px centred column (369×656 per card on desktop) and stays side by side at every width (167×297 per card on a 390px phone). Before, the photographs stacked in one column below 880px.
- **Captions removed.** "Spremni za jutarnju rutinu" and "Na kuki, uvek pri ruci" described the photographs. The pause/play button now sits where they were. Their strings are gone from the translation maps.
- **How they play.** As in the gallery: muted, looping, started only while on screen, nothing but the poster fetched before that, a play button under reduced motion.
- The section's eyebrow, heading and lead text are unchanged.

## Verification of the clips

- **Tests:** `OK (193 tests, 943 assertions)` locally at `8f3b823`. Nine are new, in `tests/ProductVideosTest.php`.
- **Deploys:** GitHub Actions run `38223503948` on `457bee7` and run `38225302422` on `8f3b823` both concluded `success`.
- **Live files:** all 14 files in `assets/videos/` return 200 from `https://cosypaw.rs/wp-content/themes/cosypaw/assets/videos/`.
- **Live product pages, headless Chrome, 2026-10-12** (desktop 1320px and phone 390px, all four products): two thumbnails; no mp4 requested before the click; after clicking the clip's thumbnail the clip is the active slide and playing; the gallery keeps its size; the magnifier is hidden on the clip and back on the photograph; the clip is paused after returning to the photograph; no JavaScript errors. `/product/peskiric-zirafa/` (no clip) has no slide and no extra class.
- **Live landing page, headless Chrome, 2026-10-12** (desktop, tablet 768px, phone 390px, and desktop with reduced motion): no mp4 requested on load; both clips play once the section is scrolled into view; pausing one leaves the other playing; with reduced motion both wait and the play button starts one; no horizontal overflow; no JavaScript errors.
- **Checked only before the deploy**, on a copy of the live pages running the local build: reduced motion on the product pages, "a visitor's pause sticks", and the lightbox still opening the photograph ("1 / 1").

### Not verified for the clips (NEEDS VERIFICATION)
- A real iPhone or any Safari. Open questions there: autoplay when the slide comes on show, swipe between slides, the rounded corners clipping the video, and Low Power Mode (expected: the clip stays on its poster and the button shows "play").
- The English and Russian versions of the pages. The new strings are in the `.po`/`.mo` files; nobody has looked at the rendered pages.
- Whether the text inside the washing-machine clip is comfortable to read at 167px card width on a real phone. It was legible in a 390px headless screenshot.

### Decisions made without item-by-item confirmation
The owner was shown these in the summaries before each deploy and answered "go". Reading that as acceptance was the 2026-10-12 session's interpretation.
- **Sound removed.** `Krofna_promo.MP4` and `Milana_Ves-masina.mp4` have an audio track; the shipped files have none, because every clip plays muted in a loop.
- **Clips live in the theme, not the media library**, because a deploy ships files and never the database.
- **Clip as a gallery slide**, rather than a separate block on the product page.
- **Cropping in the gallery, no cropping on the landing page**, and the 9:16 cards, the 760px column and the two-up phone layout that follow from it.
- **Captions on the landing cards dropped.**
- **The gift banner's button changed with the shared component:** it now shows "play" while its clip is paused, including before the clip first starts. Before, it always started out showing "pause".

### Known limitations of the clips
- **Replacing a clip under the same file name will not reach returning visitors.** The theme's `.htaccess` serves `.webp` with `Cache-Control: public, max-age=31536000, immutable` (confirmed on the live poster), and the clip URLs carry no version. The live mp4 has no `Cache-Control` at all, so browsers cache it heuristically. Give a replaced clip a new file name.
- **The Konjić clip shows prices** on a phone screen inside the video: "790 RSD" and "3 kom · 497 RSD / kom". They go stale if the shop's prices change.
- **The line in the washing-machine clip is burnt in, in Serbian.** English and Russian visitors see it as is; only the `aria-label` is translated.
- **Data on scroll.** When the landing section comes on screen it fetches about 2.0 MB (Krofna 0.9 MB + washing machine 1.1 MB). The gift banner's own clip (1.1 MB, hosted in uploads) is separate.
- **Without JavaScript** the clip's slide is hidden (a CSS `:has()` rule keeps it out until the slider has built itself), and the landing cards show their posters with no way to play.
- **Browsers without `:has()`** show the clip as a quarter-width tile until the slider builds, and keep the magnifier visible on the clip's slide. Neither was tested.
- **The thumbnail strip is WooCommerce's default:** flush under the photograph, square corners, no gap. The pillow pages have looked like this since they got a second picture; it was left alone.
- **Only towels can have a clip.** `ProductVideos::clip_for()` looks only in `cosypaw_product_map`. Pillows, long towels, cloths and bundles are in other maps.
- **The clip must stay the last slide.** The lightbox numbers the photographs by their position in the gallery.
- **The pause/play button markup now exists three times** (`template-parts/gift-banner.php`, `template-parts/product-video.php`, `front-page.php`). It was repeated rather than extracted.
- **Unused images.** `assets/lifestyle2*.avif` is no longer referenced by any template (`lifestyle3*.avif` already was not). `lifestyle1.avif` is still the fallback share image in `inc/Seo.php:237`. `tools/build-images.mjs` still builds all three.
- On the landing cards both `<video>` elements state `width="720" height="1280"`, although `ves-masina.mp4` is 576×1024. The ratio is the same, so the layout is unaffected.

## Cvetić pillows — implemented, deployed, live (unchanged since 2026-10-11)

- **Four new products** in the pillow line, each a simple product at the line's launch price of 1.599 RSD: `cvetic-zuti`, `cvetic-roze`, `cvetic-zeleni` (mint green), `cvetic-sivi`. Each has its own photograph, alt text, short description and Yoast title/description. The group shot is the second gallery picture of all four.
- **Their own copy.** `ProductLine::ensure()` accepts an optional per-item `description`, and the flowers use it. Their spec list reads "Cvetić, sa punašnim laticama." and "Prošivene latice i sredina u drugoj boji."
- **Three cards in one row.** The swatch cards (front page section and `/cuddle-puff-jastuci/`) get the class `swatch-cards--three` when exactly three groups are on sale: three columns on desktop; below 880px two share a row and the third is centred under them; at 420px and below they stack.
- **Page copy** no longer says there are two shapes. English and Russian translations were rebuilt.
- **Live check of the HTML** (2026-10-11): both pages print `class="swatch-cards swatch-cards--three"` with 3 cards (Kružić 5 colours, Kockasti 3, Cvetić 4); all four product pages return 200 with the right title, price, 2 gallery images and an add-to-cart button.
- **Seen in passing on 2026-10-12:** the gallery of `/product/cuddle-puff-jastuk-cvetic-zuti/` rendered in headless Chrome with its two thumbnails (the flower and the group shot). Nothing else about the pillows was re-checked on 2026-10-12.

### Not verified for Cvetić (NEEDS VERIFICATION)
- The live three-card layout in a real browser and on a real phone. Only the live HTML structure and a local static render were checked.
- A real add-to-cart of a flower pillow, and its cart, checkout and order rows.
- The English and Russian versions of the flower pages on the live site.
- Whether the four sampled swatch colours read well on real screens.

### Open decisions for Cvetić (chosen by default, not confirmed item by item)
- **Price:** 1.599 RSD, the same as the other pillows. It is written once at creation; only wp-admin changes it now.
- **Colours on sale:** only the four that have their own photograph. The group photograph also shows a **light pink plush flower with a yellow centre** that is not sold. Adding it needs its own photograph.
- **Colour order on the card:** žuti (shown first), roze, zeleni, sivi.

### Left alone on purpose
- The H1 on the pillow page still reads "Mekani Cuddle Puff jastuci sa okicama", although the flowers have no eyes. It is SEO wording.
- `Pages::REGISTRY['jastuci']['seo_description']` (`inc/Pages.php:77`) still says "u obliku kružića ili kockasti". It is a database value written once; changing it needs an entry in `Pages::REWORDED` and a `Pages::VERSION` bump.
- No gift bundle contains a Cvetić, and the picker only offers pillows of the same shape as the suggested one, so flowers never appear as bundle choices.
- `GiftBundles::slot_label()` (`inc/GiftBundles.php:607`) only tells Kockasti from "everything else", so a Cvetić in a bundle slot would be labelled "Jastuk Kružić". Unreachable today.

## Bundle picker — implemented and verified (unchanged since 2026-10-09)

- **Picker on every gift bundle product page.** A box titled "Složi svoj paket" sits inside WooCommerce's add-to-cart form, above the quantity field and the button, with one row per part of the bundle:
  - Cuddle Puff pillow: colour swatches, limited to the colours of **the same shape**.
  - Children's towel ("Peškir"): a "Promeni" button opens a `<details>` panel with a photo tile for every towel motif on sale.
  - Long towel ("Dugi peškir"): colour swatches for its 4 colours.
  - Cloth set ("Magične krpice"): fixed, shown as text and posted through a hidden input.
  - The bundle's suggested contents (`contains`) are pre-selected.
- **Server-side validation.** Picks are checked against `GiftBundles::slots()`. An invalid or unavailable pick refuses the add with a WooCommerce error notice.
- **Cart, checkout and order.** Every bundle cart line carries cart item data `cosypaw_bundle` = `"pillow:kruzic-zuti,pillow:kockasti-sivi,towel:sova"`. Cart and checkout show one row per part; the order line item stores the same string under meta key `cosypaw_bundle`, displayed as "U paketu".
- **Live check of the page HTML**, repeated on 2026-10-12 on https://cosypaw.rs/product/poklon-paket-kutak-za-decju-sobu/: `data-bundle-picker` is present with 3 slots, 8 swatch inputs (5 + 3) and 23 towel tile inputs. The same as on 2026-10-11.

### Not verified for the picker (NEEDS VERIFICATION)
- A real add-to-cart on the live site with changed picks, and the resulting cart, checkout, order admin screen and emails. It would create a production cart/order; it needs the owner's go-ahead.
- How the cart drawer (`assets/js/components/CartDrawer.js`) renders the per-part rows, above all for the 6-part bundle "Praznična kutija mekoće".
- The refusal notice on the live site (a stale or invalid pick).
- Pillow and long-towel thumbnails in the picker on the live site.

### Known limitations of the picker (by design or not done)
- **Pillow shape is fixed per slot.** Only the colour changes. The owner has not confirmed this reading of the request. To allow any pillow, change the `self::PILLOW` branch in `GiftBundles::choices()` (`inc/GiftBundles.php:656`), stop `slot_label()` deriving the label from the default's shape, and add a Cvetić label.
- **Like-for-like price rule.** A choice must have exactly the same live price as the suggested part (or the launch price when the suggested part is off sale). A single colour repriced in wp-admin silently disappears from bundle choices.
- `GiftBundles::offers()` is unchanged. Bundle cards are listed only while **all default parts** are on sale, their quick-add always adds the defaults, and they do not say that the bundle can be customised on its page.
- Bundle texts and the collage photo still describe the default contents; the cart thumbnail is still the collage.
- **"Order again" is not supported.** There is no `woocommerce_order_again_cart_item_data` handler. Reasoned from the code; NEEDS VERIFICATION in WooCommerce.
- **Old data.** Orders placed before the 2026-10-09 deploy have no `cosypaw_bundle` meta: pack the `contains` defaults for them.

# Active Files

| File | What it does / why it matters |
|---|---|
| `inc/Upsell.php` | **Changed on 2026-10-14.** Cart/checkout/empty-cart/thank-you offer. Hooks in the constructor: `woocommerce_cart_collaterals` → `cart_panel` (l.113), `woocommerce_before_checkout_form` → `checkout_panel` (l.120), `the_content` → `append_empty_cart_picks` (l.281), totals rows at `woocommerce_cart_totals_before_order_total`. Key methods: `cart_shipping_row()` (l.195), `offer()` (l.366), `motif_slider( $stay, $offer )` (l.472, now takes the list), `offer_motifs()` (l.543), `removed_motif_ids()` (l.571), `shipping_line()` (l.598, was `shipping_bar()`), `postage()` (l.969). |
| `inc/CheckoutSetup.php` | **Changed on 2026-10-14.** `COURIER_COST` (l.105) and `courier_cost()` (l.560). Also owns `FREE_SHIPPING_MIN` = 2500 (l.83), `COURIER_LABEL` (l.93), the zone seeding and `free_shipping_threshold()`. |
| `assets/js/components/CartUpsell.js` | Not changed. Re-renders `[data-upsell-panel]` from the HTML of WooCommerce's AJAX cart responses; that is why the removed-first order and the new copy also appear after an AJAX remove. |
| `assets/css/woocommerce.css` (upsell) | **Changed on 2026-10-14:** `.cosypaw-upsell__ship--earned` (l.1596), next to `.cosypaw-upsell__ship` (l.1587). Totals-row note styles at about l.1044–1121; strip styles from about l.1261. |
| `tests/UpsellTest.php`, `tests/stubs.php` | **Changed on 2026-10-14.** Six upsell tests added or changed (l.277–565); `WC_Cart` stub gained `get_removed_cart_contents()` (stubs l.186). The test now `require_once`s `inc/CheckoutSetup.php`. |
| `tools/build-translations.php` (upsell) | **Changed on 2026-10-14:** five msgids after "Možda vam se svidi" (from l.571). |
| `inc/ProductVideos.php` | **New on 2026-10-12.** The clips' registry and hooks. `DIR` = `'assets/videos/'` (l.37); `GALLERY_CLASS` (l.42); `CLIPS` motif id ⇒ focus point (l.62–67); hooks in the constructor (l.72): filter `woocommerce_single_product_image_gallery_classes`, action `woocommerce_product_thumbnails` at priority 30; `motifs()` (l.86), `clip_for()` (l.96), `gallery_classes()` (l.125), `slide()` (l.140), `photo_ratio()` (l.183). |
| `template-parts/product-video.php` | **New on 2026-10-12.** The slide's markup: a `.woocommerce-product-gallery__image.cosypaw-gallery-video` with `data-thumb`, the `<video data-inview-video data-video-manual>` and the pause/play button. Ratio and focus are passed as the CSS custom properties `--clip-ratio` and `--clip-focus`. |
| `assets/videos/` | **New on 2026-10-12.** 14 files: `{bundeva,krofna,sova,konjic}.mp4`, `.webp`, `-th.webp`, plus `ves-masina.mp4` and `ves-masina.webp` (landing only, no thumbnail). |
| `assets/js/components/InViewVideo.js` | Drives every silent looping clip. **Changed on 2026-10-12:** `auto` starts as `wanted()` (l.43) instead of `true`; the button's state is synced once at bind (l.90); the observer is skipped where `IntersectionObserver` is missing (l.92). |
| `assets/js/app.js` | **Changed on 2026-10-12:** boots `InViewVideo` for every `[data-inview-video]` when autoplay is wanted, and for those marked `data-video-manual` even when it is not (l.54–59). The bundle picker's boot moved to l.76. |
| `assets/css/woocommerce.css` | **Changed on 2026-10-12:** "Promo clip in the gallery" (l.256–315): the slide's box, the rule hiding it until `.flex-viewport` exists, the clip's fill, the magnifier rule, the play badge on the last thumbnail. The picker's styles now start at about l.725. |
| `front-page.php` | **Changed on 2026-10-12:** "U TVOM DOMU" (l.630–693), now two clip cards built from an array of `file` + `label` (l.649–658), served from `\Theme\ProductVideos::DIR` (l.659). The pillow section is at l.574, unchanged. |
| `assets/css/landing.css` | **Changed on 2026-10-12:** "Lifestyle clips" (l.378–410): `.lifestyle` with `max-width: 760px`, `.lifestyle-card` at 9:16; the caption rules and the 880px single-column rule were removed; a 12px gap at 560px (l.2354). Because of the removal `.swatch-cards--three` is now at l.2020–2038 and its 420px line at l.2169. |
| `tools/package-theme.ps1` | The deploy's allowlist. **Changed on 2026-10-12:** `.mp4` added to `$imgExt` (l.72). Without it no clip reaches the server. |
| `tools/build-translations.php` | Source of all translations. **Changed on 2026-10-12:** the two landing labels replaced the two caption strings (l.455–458); `'Video: %s'` added (l.463). The bundle picker block is still near l.342. |
| `languages/{en_US,ru_RU}.{po,mo}` | Regenerated from the tool above. |
| `tests/ProductVideosTest.php` | **New on 2026-10-12.** Nine tests: motif ⇒ files, no clip for everything else, every registered clip has its three files (l.110), the landing's clips have video and poster (l.128, reads `front-page.php`), the allowlist contains `.mp4` (l.152), the slide's ratio, the gallery class. |
| `tests/WooCommerceTest.php` | **Changed on 2026-10-12:** one `require_once` for `inc/ProductVideos.php` (l.55), because `Bootstrap` now constructs it. |
| `inc/Bootstrap.php` | **Changed on 2026-10-12:** property `$product_videos` (l.168) and `new ProductVideos()` inside the WooCommerce branch (l.224). |
| `template-parts/gift-banner.php`, `assets/css/blocks.css` | The original clip markup and the button's styles (`.pkg-banner__toggle`), which the new slide and the landing cards reuse by class name. Not changed. |
| `inc/Pillows.php`, `inc/ProductLine.php`, `template-parts/swatch-cards.php`, `tests/ProductLinesTest.php` | The Cvetić work of 2026-10-11. Not changed since 2026-10-11; the line numbers were last checked on 2026-10-12 (`Pillows::VERSION` l.42, `PRICE` l.68, `FLOWER` l.80, `spec_rows()` l.276; `ProductLine::ensure()` description at l.152). |
| `inc/GiftBundles.php`, `inc/GiftBundlePicker.php`, `template-parts/bundle-picker.php`, `assets/js/components/BundlePicker.js`, `tests/GiftBundlesTest.php` | The bundle picker. Not changed by the handoff sessions since 2026-10-09; line numbers as of 2026-10-12, and `e713e9c` / `87c8f52` have since edited `inc/GiftBundles.php`, so they may have moved (`slots()` l.457, `slot_label()` l.607, `choices()` l.656, `spec_rows()` l.793). |
| Context only | `inc/WooCommerce.php` (`PRODUCT_MAP_OPTION`, `register_towel()`, `towel_key()`), `inc/Catalog.php`, `inc/Setup.php` (gallery zoom/lightbox/slider support, l.91–93), `inc/Seo.php`, `.htaccess`, `.github/workflows/deploy.yml`. |

# Changes Made

## This session (2026-10-14)

One commit on its own branch, merged with `--no-ff` and pushed to `origin/main` after the owner's "go".

**`f111ef9 feat(cart): the offer stays once delivery is free, and the bar has a price`** on `feat/upsell-everywhere`, merged by `6d724ca`.
- **Modified:** `inc/Upsell.php`, `inc/CheckoutSetup.php`, `assets/css/woocommerce.css`, `tools/build-translations.php`, `languages/{en_US,ru_RU}.{po,mo}`, `tests/UpsellTest.php`, `tests/stubs.php`.
- **No new files.** No database change, no option, no `VERSION` bump. Live the moment the files deployed.
- **Config/dependencies:** unchanged.
- Deploy run `38644091753` concluded `success`.

## Other sessions, not covered by a handoff (2026-10-12 to 2026-10-14)

Landed on `main` after the 2026-10-12 handoff and before this session. Listed from `git log` only; not reviewed:
- `60473da feat(landing): pillows, long towels, then cloths follow the bundle builder` (merge `e2592e9`) — `front-page.php`.
- `87c8f52 feat(copy): the long towels are "peškiri", no longer "dugi peškiri"` (merge `77de468`) — `inc/Wording.php`, `inc/LongTowels.php`, `inc/GiftBundles.php`, `inc/Pages.php`, page templates, translations. Wording elsewhere in this file ("Dugi peškir") may be out of date.
- `b768afa feat(collection): bagged towels lead, and the cartoon towels get a name` (merge `e1b8c35`) — `inc/Storefront.php`, `page-deciji-peskiri-sa-motivima-zivotinja.php`, `tests/StorefrontTest.php`.
- `e713e9c feat(shop): six more gift bundles, so every occasion offers three` (merge `1472a88`) — `inc/GiftBundles.php`, six new collages in `assets/bundles/`.
- `e2a365a feat(builder): choosing a package size scrolls on to the towels` (merge `86f170b`) — `assets/js/components/BundleBuilder.js`.

## 2026-10-12 session

Two commits, each on its own branch, merged with `--no-ff` and pushed to `origin/main`. Both branches exist only locally.

**1. `df14813 feat(shop): four towels show their promo clip in the product gallery`** on `feat/product-videos`, merged by `457bee7`.
- **New files:** `inc/ProductVideos.php`, `template-parts/product-video.php`, `tests/ProductVideosTest.php`, and twelve files in `assets/videos/`.
- **Modified:** `inc/Bootstrap.php`, `assets/css/woocommerce.css`, `assets/js/app.js`, `assets/js/components/InViewVideo.js`, `tools/package-theme.ps1`, `tools/build-translations.php`, the four language files, `tests/WooCommerceTest.php`.
- **Logic:** product id ⇒ motif id through `cosypaw_product_map` ⇒ `CLIPS`; the slide is printed after WooCommerce's own thumbnails; the gallery gets the class `cosypaw-gallery--video`.
- **Shared component:** `InViewVideo` gained the manual mode and the initial sync described above. One refinement was made before shipping: the initial sync was first limited to manual mode and then made unconditional, so that an autoplay the browser refuses also leaves the button on "play".

**2. `c2b7209 feat(landing): "U tvom domu" plays two clips where two photographs were`** on `feat/landing-clips`, merged by `8f3b823`.
- **New files:** `assets/videos/ves-masina.mp4`, `assets/videos/ves-masina.webp`.
- **Modified:** `front-page.php`, `assets/css/landing.css`, `inc/ProductVideos.php` (one docblock), `tools/build-translations.php`, the four language files, `tests/ProductVideosTest.php` (one test added).

**Across both:**
- **Database:** nothing. No option, product or attachment was created or changed; no `VERSION` was bumped. The clips appeared the moment the files were deployed.
- **Config/dependencies:** `package.json`, `composer.json` and the deploy workflow are unchanged. The only build-side change is the `.mp4` in the packaging allowlist.
- **Built assets on the live site after that deploy:** `app-BVpNx7Xk.js`, `woocommerce-D51Q0mni.css`, `landing-BmH4yZjd.css`. These names change with the source, so they show whether a later deploy has landed (current names are under *Cart and checkout upsell*).

**How the clip files were made** (ffmpeg, from originals that are outside the repo):

| File | Original | Settings | Size |
|---|---|---|---|
| `bundeva.mp4` | `Bundeva_promo.MP4`, 720×1280, 4.0 s, 2.9 MB, no sound | `-crf 24 -r 24` | 467 KB |
| `krofna.mp4` | `Krofna_promo.MP4`, 720×1280, 5.0 s, 3.0 MB, with sound | `-an -crf 24 -r 24` | 905 KB |
| `sova.mp4` | `Sova_promo.MOV`, 1080×1920, 60 fps, 3.8 s, 11.9 MB | `scale=720:1280`, `-crf 26 -r 30` | 1.14 MB |
| `konjic.mp4` | `Konjic_promo.MOV`, 2160×3840, 60 fps, 5.05 s, 15.3 MB | `scale=720:1280`, `-crf 24 -r 30` | 795 KB |
| `ves-masina.mp4` | `Milana_Ves-masina.mp4`, 576×1024, 13.5 s, 2.66 MB, with sound | kept at 576×1024, `-an -crf 30 -r 30` | 1.12 MB |

- Common to all: `-c:v libx264 -preset veryslow -profile:v high -pix_fmt yuv420p -movflags +faststart`.
- Posters are the first frame as WebP at quality 78 (66 for `sova`, 72 for `ves-masina`).
- Thumbnails are a 720×720 cut of the first frame scaled to 200×200, WebP quality 80, taken at these vertical offsets in the 720×1280 frame: `bundeva` 300, `krofna` 420, `sova` 295, `konjic` 190.
- The originals are in `C:\Users\Korisnik\Desktop\Maxim\Posao\Posao\Ècommerce\CosyPaw\Novi proizvodi\OG\` and, for the washing-machine clip, one folder up in `…\CosyPaw\`.

## Earlier

- **2026-10-11:** `8669a73 feat(shop): Cuddle Puff pillows come in a third shape, the Cvetić flower`, merged by `f0a4c64`. Four products, five photographs in `assets/pillows/`, the three-card layout, reworded page copy. On the live site: four products, five attachments, four keys in `cosypaw_pillow_map`, `cosypaw_pillows_version` = 2.
- **2026-10-09:** `eafb3cb feat(shop): every part of a gift bundle can be swapped on its page`, merged by `7a6acc6`. The picker, its validation, and the cart item data / order meta key `cosypaw_bundle` in the format `line:key[,line:key…]`.

# Failed Attempts

## This session (2026-10-14)

1. **A wrong test, written and removed before the commit.** `test_a_cart_holding_everything_gets_no_panel` put all 20 motifs in the cart once each and expected an empty slot. 20 single towels are not a package optimum, so the panel correctly still offered the next towel. There is no simple stubbed cart that reaches "every motif in the cart **and** at an optimum", so that path has no test.
2. **`https://cosypaw.rs/korpa/` is a 404.** The cart is `/cart/`. The first live check read the 404 page's empty-cart markup and wrongly suggested the empty-cart strip was missing.
3. **`/cart/?add-to-cart=<id>` alone redirects to the product page** (`WooCommerce::add_to_cart_redirect()`). Add `&cosypaw-stay=cart` to stay on the cart.
4. **Loading the local build from `http://127.0.0.1:8765` into the live `https://cosypaw.rs` page failed silently** (Chrome blocks a public page from fetching localhost — Private Network Access): the page rendered without the theme's WooCommerce styles. Fix: read the built CSS in Node and inject it as a `<style>` replacing the `<link>`.
5. **`page.setContent()` with the downloaded live HTML timed out** after 30 s. Fix: `page.goto()` the live cart and change the DOM with `page.evaluate()`.
6. **`elementHandle.screenshot()` on `.cart-collaterals` failed** ("Node is either not visible…") because it is `display: contents` on desktop. Fix: compute a clip from the bounding boxes of `.cart_totals` and `[data-upsell-panel]`.
7. **Bash heredocs failed twice:** a Python heredoc containing a Windows path with backslashes raised `SyntaxError: unterminated string literal`, and a long heredoc with mixed quotes made Bash report `unexpected EOF while looking for matching`. Write scripts with the Write tool into the scratchpad and run the file.
8. **Python printing "ć"** crashed with `UnicodeEncodeError` (cp1252) again — see 12 below.
9. **`node -e "…"` with a regex containing backslashes** broke in Bash quoting. Put the script in a `.mjs` file.

## 2026-10-12 session

No implementation was built and then abandoned. Two layouts were ruled out by reasoning before any code was written, and are recorded so they are not explored again:

1. **A separate clip block under the gallery photograph** (left column on desktop). The summary column was estimated at about 830px against a 704–804px photograph, so a second 3:4 box under the photograph would leave roughly 600px of blank space beside it, and a 9:16 one more. This was an estimate, not a measurement.
2. **Keeping the landing cards at 4:5 and cropping the clips.** A 4:5 box shows about 70% of a 9:16 frame. The washing-machine clip needs about 74% (its text starts at 14% from the top and the machine's door ends at 88%), so either the "POV:" line or the door is cut.

Tooling problems, each with the fix that worked:

3. **`ffprobe -show_entries …:stream_side_data=rotation`** fails in the `ffprobe-static` build with `No match for section 'stream_side_data'`. Leave that section out.
4. **Piping the browser script through `tee file | head`** closed the pipe early and left a truncated JSON file. Redirect straight to the file and read it afterwards.
5. **A false "the clips do not autoplay" on the landing page.** The test scrolled with `scrollIntoView()` followed by `scrollBy()`; the page has smooth scrolling, the second call cancelled the first, and the section never came on screen. `window.scrollTo({ top, behavior: 'instant' })` fixed the test. The theme was fine.
6. **Headings rendered in a fallback font in the local preview copies** of the live pages, while the live site rendered them in the theme's font. The cause was not investigated; it did not affect what was being checked.
7. **The GitHub MCP server failed to connect** (HTTP 400, badly formatted Authorization header) — again on 2026-10-14. On 2026-10-12 `gh` was not authenticated either; **on 2026-10-14 `gh run list`, `gh run watch` and `gh run view` worked.**

## Carried over from earlier sessions

8. **`git merge --no-ff <branch> -F -` with a heredoc on stdin** fails in this Git Bash with `error: could not read file '-'`. Write the message to a file and use `-F <file>`, or (worked on 2026-10-14) `-m "<one-line message>"`. `git commit -F -` with a heredoc does work.
9. **Headless Edge started from Git Bash wrote no screenshot and reported no error.** It works from PowerShell with `Start-Process -Wait` and an explicit `--user-data-dir`. The browser harness described under *Important Context* replaces this for most checks.
10. **Static preview pitfall:** two copies of a swatch card on one preview page must use different radio `name`s.
11. **`grep -o '<input[^>]*…>'` finds nothing in the live picker HTML,** because each input's attributes are on separate lines. Count attribute occurrences instead, e.g. `grep -o 'name="cosypaw_bundle\[[0-9]*\]"' | sort | uniq -c`.
12. **Python on this machine prints through cp1252** and crashes on "ć", "ž" and similar. Run it with `PYTHONIOENCODING=utf-8`.

# Specific Next Steps

## Cart upsell (from this session)
U1. **Look at the checkout on a phone and a desktop:** add one towel at https://cosypaw.rs/cart/?add-to-cart=59&cosypaw-stay=cart, open https://cosypaw.rs/checkout/ and check the strip above the form (heading, the "Još … — umesto 500–700 RSD poštarine kuriru" line, the slider, its arrows). Then add towels until "Ostvarena" and check the earned line.
U2. **See "Dostava je na nama" live:** it appears only when delivery is earned and the next towel is not cheaper (a whole package). Try a 2+1 package plus one pillow, or adjust until the towel line disappears.
U3. **Check English and Russian** of the cart (flags in the header) for the five new strings.
U4. **Ask the owner:**
   - Is 500–700 RSD right, and should it also appear in the announcement bar / FAQ / package cards / the courier label at checkout? (The label lives in the database: change it in wp-admin → WooCommerce → Settings → Shipping → Srbija, or extend `CheckoutSetup::sync_free_shipping()`.)
   - Should the strip offer pillows, cloths and long towels too, not only towel motifs?
   - Should the drawer in `footer.php` (`CartDrawer.js`, a demo cart) be removed, or given the same empty-state upsell?
U5. *Optional:* give the empty cart a different heading when something was just removed (e.g. "Predomislili ste se?"); today it is always "Možda vam se svidi".
U6. **To change the postage range:** edit `CheckoutSetup::COURIER_COST` (or hook `cosypaw_courier_cost`), run the tests (`test_the_row_states_a_win_plainly`, `test_the_totals_row_quotes_the_postage`, `test_the_panel_prices_the_postage` assert "500–700 RSD"), push after the owner's "go".
U7. *Housekeeping:* delete the merged local branch with `git branch -d feat/upsell-everywhere` if branches are not being kept (earlier feature branches were kept locally).

## Clips
1. **Look at the clips on a real iPhone (Safari)** and report what differs from the list under *Verification of the clips*:
   - https://cosypaw.rs/product/bundeva/ and https://cosypaw.rs/product/peskiric-sovica/ — tap the second thumbnail, swipe between the slides, press pause, go back to the photograph.
   - https://cosypaw.rs/#dom — both clips start when the section comes on screen; the text in the right-hand clip is readable; the corners are rounded.
   - Repeat one page with Low Power Mode on. Expected: the poster stays and the button shows "play".
2. **Ask the owner:**
   - The Konjić clip shows "790 RSD" and "3 kom · 497 RSD / kom". Keep it, or supply a cut without prices?
   - Is the removed sound acceptable? Restoring it means an unmute control, which does not exist yet.
   - Does the heading "Tvoj kutak, malo mekši" and its lead still fit the two clips, and are the removed captions missed?
   - May `assets/lifestyle2*.avif` and `assets/lifestyle3*.avif` be deleted? (`lifestyle1.avif` must stay for `inc/Seo.php`.)
3. *Optional:* check the English and Russian versions of one product page and of the landing section, using the flags in the header.
4. *Optional:* style the gallery's thumbnail strip (gap under the photograph, rounded thumbnails). It would change the pillow pages too, which use the same strip.
5. *Optional:* move the pause/play button into one template part and use it in `gift-banner.php`, `product-video.php` and `front-page.php`.
6. *Optional:* re-run the reduced-motion and lightbox checks against the live product pages. They were only run before the deploy.

**To add a clip to another towel:**
1. Find its motif id: it is the `motif=` value in the "Dodaj u paket" link on its product page.
2. Encode `<id>.mp4` at 720×1280 with the settings in the table above, and make `<id>.webp` and `<id>-th.webp`.
3. Put the three files in `assets/videos/`.
4. Add `'<id>' => '50% NN%'` to `ProductVideos::CLIPS`. The second number is the vertical focus: 50% is centred, lower shows more of the top of the frame.
5. Run the tests. `test_every_clip_has_its_three_files` fails if a file is missing.

**To replace a clip:** use a new file name and point the code at it (see the caching limitation above).

## Cvetić follow-ups (carried over, none done yet)
7. **Look at the live pages in a real browser and on a phone:** https://cosypaw.rs/cuddle-puff-jastuci/ and the front page section `#cuddle-puff-jastuci`. Check the three-card row, the centred third card at tablet width, the swatches and each colour's photo. Open one flower product page and check the gallery, the spec list and the description.
8. **Confirm with the owner:** the 1.599 RSD price; whether the light pink plush flower should be sold (it needs its own photo); the colour order. To add a colour: add the item to `Pillows::ITEMS` with `'group' => self::FLOWER` and `'description' => self::FLOWER_DESCRIPTION`, put a 1088×1448 webp in `assets/pillows/`, bump `Pillows::VERSION` to 3, add the name and short text to `tools/build-translations.php` and rebuild, and raise the two `12`s and the flower count `4` in `tests/ProductLinesTest.php`.
9. **Ask the owner about the wording** of the pillow page H1 ("…sa okicama") and its Yoast description, which still describe only the pillows with eyes.

## Bundle picker (carried over, none done yet)
10. **Live end-to-end test** on https://cosypaw.rs/product/poklon-paket-kutak-za-decju-sobu/. It creates a production cart and a test order, so get the owner's go-ahead first.
    - Pick a different Kružić colour, a different Kockasti colour and another towel, then add to cart.
    - Confirm the cart and checkout rows (e.g. "Jastuk Kružić: Zelena", "Peškir: Kapibara").
    - Place a test COD order and check the admin order screen and both emails for "U paketu" with one line per part.
    - Cancel or delete the test order afterwards.
11. **Check the cart drawer** (`CartDrawer.js`) with "Poklon paket Praznična kutija mekoće", which has 6 rows. This only creates a session cart. *Note from 2026-10-14:* the drawer's docblock calls it a demo cart in `sessionStorage` and the live header cart links to `/cart/`, so this check may be moot — first confirm whether the drawer is reachable at all.
12. **Check the refusal path:** POST to the product URL with `add-to-cart=<bundle id>` and `cosypaw_bundle[2]=meda-krem`. Expect the notice "Nešto iz paketa trenutno nije dostupno — izaberi drugu boju ili oblik." and no cart line.
13. **Visual check of the live picker** on a phone: thumbnails load, the swatches wrap below the row, the tile grid opens and closes.
14. **Ask the owner** whether pillow *shape* should also be swappable in a bundle (Kružić ↔ Kockasti ↔ Cvetić). If yes, see *Known limitations of the picker*, give `slot_label()` a Cvetić branch with a new msgid "Jastuk Cvetić", and update `test_each_slot_offers_like_for_like_swaps` in `tests/GiftBundlesTest.php`.
15. *Optional:* a hint on the bundle cards (`template-parts/bundle-cards.php`), e.g. "Boje i oblike biraš na stranici paketa".
16. *Optional:* support "order again" by hooking `woocommerce_order_again_cart_item_data`.
17. *Optional:* build the cart thumbnail from the chosen parts, following `WooCommerce::motifs_cart_item_thumbnail()`.

# Important Context

- **PHP for tests:** use `C:\xamppp\php83\php.exe`. The `php` on PATH is 8.0 and PHPUnit 10.5 refuses it. CI uses PHP 8.2.
  - Tests: `/c/xamppp/php83/php.exe vendor/bin/phpunit`
  - Translations: edit the maps in `tools/build-translations.php`, then run `/c/xamppp/php83/php.exe tools/build-translations.php`. Commit the `.po` and `.mo` files. When a msgid in a template is reworded or removed, replace or remove its key in the map.
- **Assets:** `npm run build` (Vite 5, Node 22). `dist/` is gitignored; CI builds it.
- **Video tooling:** ffmpeg is not installed on this machine. `npm i ffmpeg-static ffprobe-static` in a temporary folder outside the repo gives working binaries (`node -e "console.log(require('ffmpeg-static'))"` prints the path). Nothing was added to the project's `package.json`.
- **Images:** Python 3.12 with Pillow and Node with `sharp` are both installed. `tools/build-images.mjs` covers only the towel motifs, lifestyle photos and the logo.
- **Knowledge graph:** per `CLAUDE.md`, run `graphify update .` after code changes. `graphify-out/` is gitignored. It was updated after the change on 2026-10-14. `graphify query "<question>"` found `inc/Upsell.php` directly.
- **Deploy:**
  - Every push to `main` runs `.github/workflows/deploy.yml`: phpunit → `npm ci && npm run build` → `tools/package-theme.ps1` (allowlist) → FTPS to the live theme directory. **Pushing to main = production deploy.** A run takes about 75 seconds.
  - The run's result is readable without login: `curl -s "https://api.github.com/repos/Maksim-Manojlovic/CosyPaw_theme/actions/runs?per_page=1&head_sha=<sha>"`.
  - `handoff.md` is not in the package allowlist, so it is never published.
  - **A deploy ships files, never the database.** Products of a `ProductLine` and the pages of `\Theme\Pages` are created the first time a user with the right capability opens wp-admin after the registry's `VERSION` was bumped. The clips needed none of this.
  - To see what a deploy would publish, run `powershell -File tools/package-theme.ps1` after a build and look in `build/cosypaw/` (gitignored).
- **Live URLs:** cart `https://cosypaw.rs/cart/`, checkout `https://cosypaw.rs/checkout/`. Add a product and stay on the cart with `?add-to-cart=<product id>&cosypaw-stay=cart` (towel ids: Krofnica 59, Sovica 22, Konjić 108, Bundeva 197). Headless sessions create only session carts, no orders.
- **Free delivery and postage:** threshold `CheckoutSetup::FREE_SHIPPING_MIN` = 2500, measured on the payable total after the package saving (`BundlePricing::payable_total()`). The courier rate is seeded at cost 0 — the buyer pays postage to the courier — and `CheckoutSetup::COURIER_COST` (500–700) is only quoted.
- **Checking behaviour in a real browser without a working local WordPress.** The local WordPress (`C:\xamppp\htdocs\cosypaw`) holds a stale copy of the theme. This session used the following instead, and it found things a screenshot would not:
  1. Download the live page's HTML with `curl`.
  2. Rewrite the theme's `dist/assets/app-*.css|js`, `woocommerce-*.css` and `landing-*.css|js` references to the local build (names from `dist/.vite/manifest.json`) and insert the new markup. Leave WooCommerce's and jQuery's scripts pointing at cosypaw.rs, so the real gallery and cart scripts run.
  3. Render template parts outside WordPress with a few-line PHP stub (`__`, `esc_url`, `esc_attr`, `esc_attr_e`, `get_template_directory_uri`, `ABSPATH`).
  4. Serve the folder with `python -m http.server` (module scripts do not load from `file://`).
  5. Drive it with `puppeteer-core` (installed with npm in a temporary folder, no browser download) and `C:/Program Files/Google/Chrome/Application/chrome.exe`: viewport and phone emulation, clicks, `emulateMediaFeatures` for reduced motion, `page.evaluate` to read state.
  - On 2026-10-14 the cart was checked the other way round: drive the **live** page and inject the new markup and the locally built CSS (inlined as `<style>`, see *Failed Attempts* 4) with `page.evaluate()`. New markup can be rendered by a throwaway PHPUnit test that reuses `UpsellTest`'s `setUp()` and helpers and writes the HTML to a file (delete the test afterwards).
  - The scripts lived in the session's temporary folder and are gone. Scroll with `behavior: 'instant'` (see *Failed Attempts* 5). The same driver pointed at `https://cosypaw.rs/…` is the post-deploy check.
- **How the gallery treats the clip.** Read from `single-product.js` in the local WooCommerce 11.0.0 copy (`C:\xamppp\htdocs\cosypaw\wp-content\plugins\woocommerce`); the live site runs 11.2.0, and every browser check on 2026-10-12, before and after the deploy, ran the live site's 11.2.0 scripts:
  - Any `.woocommerce-product-gallery__image` is a slide. One slide means no slider; two or more turn on the slider and its thumbnail strip, built from each slide's `data-thumb` and `data-thumb-alt`.
  - Zoom and lightbox look for an `<img>` in the slide and skip a slide without one, which is why the clip's slide has neither a link nor an image.
  - The lightbox's start index is the clicked slide's position in the gallery, so a clip placed before a photograph would shift the photographs after it.
  - An off-screen slide is clipped by `.flex-viewport`, so `IntersectionObserver` reports it as not intersecting. That is what starts and stops the clip.
- **Git workflow** used throughout the history:
  - Work on a feature branch and commit with a Conventional Commit message ending in `Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>`. Commit bodies explain the change in full sentences and bullets.
  - Merge into `main` with `git merge --no-ff <branch> -F <file>`. The message format is `Merge branch '<branch>': <summary>`, followed by the commit subject and a short paragraph.
  - Push `main`.
  - The owner approves each commit and deploy with a short "go"; nothing was committed or pushed before that, and an earlier "go" was not taken to cover later work.
- **Do not change without care:**
  - The `cosypaw_bundle` meta/cart key name and its `line:key` format. Live orders depend on them.
  - The like-for-like price rule, which keeps `offers()` savings truthful.
  - The rule that a `ProductLine` product is never filed under the towel category (`WooCommerce::TOWEL_CATEGORY` = `peskiri`), or `BundlePricing` would count it in 2+1 packages.
  - The stored Serbian texts. They double as gettext msgids. Texts of products that already exist live in the database and are not rewritten by a code change.
  - The `ITEMS` keys and slugs of existing products (`kruzic-*`, `kockasti-*`, `cvetic-*`), and the motif ids in `cosypaw_product_map`. The option maps, the bundles' `contains` lists and now `ProductVideos::CLIPS` refer to them.
  - The `[data-upsell-panel]` slot printed by `Upsell::panel()` even when empty: `CartUpsell.js` re-renders into it after AJAX cart updates.
  - `InViewVideo` and `.pkg-banner__toggle`. Three places use them now: the gift banner (landing and every product page), the gallery clips and the landing's "U tvom domu".
  - The file names in `assets/videos/`. They are in code, and the posters are cached for a year under those names.
- **Option names** (ITEMS key ⇒ product id): `cosypaw_gift_bundle_map`, `cosypaw_pillow_map`, `cosypaw_long_towel_map`, `cosypaw_cloth_map`. Towel motifs (motif id ⇒ product id): `cosypaw_product_map`. Line versions: `cosypaw_pillows_version` (now 2), and one per other line.
- **Edge cases:**
  - Live product names override the seeded ones (e.g. "Sovica", "Krofnica"). The clip's label uses the live name: "Video: Sovica".
  - A towel added in wp-admin gets its motif id from its slug, less a leading `peskiric-` (`WooCommerce::towel_key()`); if that id is taken it becomes `<slug>-<product id>`. Bundeva and Konjić were added this way.
  - Towels added in wp-admin appear as bundle choices automatically, via `WooCommerce::append_shop_motifs()`.
  - If the owner adds more photographs to a towel that has a clip, the clip stays last and nothing needs changing.
  - `$_REQUEST['cosypaw_bundle']` is read on any add-to-cart request but only acted on for bundle product ids.
  - `<details name=…>` (only one panel open at a time) works only in newer browsers.
  - The swatch cards have layouts for 1, 2 and 3 groups. A fourth pillow shape needs its own rule.
- **Copy rule for product lines:** the texts describe what the photographs show and nothing else. No sizes and no fibre content until the shop states them. The clips' labels follow it: each says what the clip shows.
- **Code conventions:** dense "why" docblocks; template variables prefixed `$cosypaw_` (the inline loop in `front-page.php` predates this and uses bare names); storefront strings are Serbian msgids in the `cosypaw` text domain; hooks are registered in class constructors, which are instantiated from `inc/Bootstrap.php`.
