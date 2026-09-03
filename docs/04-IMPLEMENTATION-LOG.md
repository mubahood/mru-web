# MRU Website Rebuild — Implementation Log

Running log of work, decisions and challenges. Newest entries at the bottom.

## 2026-09-02/03 — Phase 0: Foundation
- Copied model app `/Applications/MAMP/htdocs/muhindo-app` → `/Applications/MAMP/htdocs/mru-new-web` (rsync, `.git` excluded; 3.2 GB incl. vendor/node_modules so the site runs immediately).
- Created DB `mru_new_web` (utf8mb4) and cloned all 74 tables from `muhindo_app` via mysqldump (single-transaction, routines, triggers). Safety dump kept at `~/Desktop/mru-db-backups/muhindo_app_clone_2026-09-02.sql` (162 MB).
- Repointed `.env` (APP_NAME, APP_URL http://localhost:8888/mru-new-web, APP_FOLDER, DB_DATABASE=mru_new_web). Cleared config/cache/view/route caches. All 46 migrations report Ran.
- Verified over HTTP: /, /e-learning, /about, /blog, /gallery, /login, /register, /verify, /services, /sitemap.xml all 200; Vite build assets present.
- **Challenge:** Homebrew PHP broken (missing libtidy dylib) → use MAMP `/Applications/MAMP/bin/php/php8.3.9/bin/php` for artisan. MAMP MySQL client lives at `/Applications/MAMP/Library/bin/mysql80/bin/`.
- Removed `credentials.txt` (unrelated hosting creds + private SSH key from the model author's other project — gitignored anyway, no place in this repo) and `.phpunit.result.cache`.
- `git init -b main`; baseline commit of the pristine import.
- Ran three deep analyses (model app features; legacy backup incl. both SQL dumps; 2025-26 higher-ed web research) → docs 01/02/03. Captured the live mru.ac.ug IA + programme list + scholar structure as cross-check.
- **Key discovery:** the "custom site DB" dump `mru_mru.sql` (363 MB) is actually a staging WordPress dump; the real custom-CMS DB is `mru_mru2.sql` (195 KB). Live WP DB is `mru_wp435.sql` (news archive: 249 posts).
- **Security notes recorded** (legacy): unauthenticated `admin/autologin.php` backdoor, plaintext prod DB password, malware droppers + casino spam posts in WP. Decision: migrate data only, never code/files verbatim; curated asset copying.

## 2026-09-03 — Phase A: Rebrand (commit 9b71f82)
- Palette #05275C/#D4A843 across marketing layout, td-admin.css, manifest; crest as brand mark + favicon; header CTAs → E-Portal + Apply Now; footer institutionalized; WhatsApp launcher → MRU Admissions intents on +256 752 033 889; emails/PDFs/certificates/errors/payment copy reworded (University/Bursar); UniversityContentSeeder (identity, contacts, socials, links, stats, admissions, accommodation).
- **Motto discrepancy resolved:** crest ribbon "Seeking Greater Horizons in Thought and Action" chosen over WP blogdescription variant ("...Horizon In Thoughts and Actions").
- Tests updated to new brand strings; full suite green (1147 passed, 1 skipped).

## 2026-09-03 — Phase B: University data model
- Migrations `create_university_tables` (faculties, staff_members, programmes, university_events, almanac_entries, scholarships, vacancies, partners; faculties⇄staff dean FK added after both exist) and `create_scholar_tables` (research_areas, scholars, publications, publication_author with external co-authors, publication_research_area).
- 11 models + `GeneratesSlug` trait (unique slug on save, per-table).

## 2026-09-03 — Phase C: Legacy content migration (`mru:import-legacy`)
- Scratch DBs: `mru_legacy` (custom CMS, mru_mru2.sql) and `mru_legacy_wp` (live WP, mru_wp435.sql).
- Imported: 5 faculties · 93 staff + 18 council + 34 committee + 8 guild (155 people) · 44 programmes (46 legacy − 2 true dupes across categories) · 14 almanac rows · 6 scholarships · 4 partners · 6 sports + 2 hero slides (settings JSON) · 70 newsletter subscribers · **30 scholars** (WP, real lecturers; test rows dropped) · **19 publications** (18 with PDFs copied) + 10 research areas · **111 genuine news posts** with 68 cover images.
- **Spam scale correction:** 127 casino/gambling posts filtered (not ~10 as first estimated) — the WP compromise was extensive. Keyword filter + manual sample verification.
- **Fees decision (deviation from blueprint §7):** the 128-row `programme_fees` table is PDF-scrape damage (name/faculty/level/duration/mode and the amount's leading digit concatenated into `programme_name`); reconstruction would still leave the billing period ambiguous. Imported instead the three **verified** faculty bands the university itself publishes (live /fees page + `fees_structure`): IT/Computing 1.5M, Business 1.2M, Education & Arts 1.0M UGX/semester; postgraduate programmes carry "Contact the Graduate School" note. Every tuition figure marked "Estimated; confirm with the Bursar".
- **Council data quirk handled:** legacy `council_members.role` holds the NAME and `description` the role.
- Legacy `events` table was mostly theme demo junk ("adam", "Falar's Career Fair") — only dated real rows imported (2).
- Documents: **44 PDFs** curated from wp-content/uploads (strategic plan 2025-2030, almanac, e-portal guide, application forms, short-courses brochure, orientation, 2 journals, research register, Luganda for beginners, 31 policies) → `storage/app/public/documents/` + `university.documents` settings index. **Prospectus 2022-2027 and Students' Handbook PDFs are NOT in the backup** (only link text in DB) — university must re-supply.
- WP publication files lived as bare filenames in one folder — importer prefixes `wp-content/uploads/mru-scholar/`.
- Model's two personal blog posts removed; news category standardized to "News".

## 2026-09-03 — Phase D: University routes, controllers, flagship pages
- routes/web.php rebuilt: university IA (about cluster, /admissions/* with legacy-URL 301s, faculties, programmes, events, vacancies, /scholar/*, /news with /blog+/insights 301s); portfolio public routes removed (views deleted; PortfolioController reduced to the admin messages inbox); /contact is a real page again.
- **Laravel gotcha logged:** a controller-group route action named `directory` broke route registration — `prependGroupController()` skips prefixing when `class_exists($action)` and PHP's built-in `Directory` class matches case-insensitively. Method renamed `scholarsDirectory`.
- Public controllers: University\{PageController, AdmissionsController, FacultyController, ProgrammeController (filterable finder), EventController, VacancyController, ScholarPortalController (search/filters, view+download counters, streamed PDFs)} + App\Support\University settings accessor.
- SiteNav rebuilt: 6 topic items (About/Admissions/Academics/Research/Student Life/News & Events) with mega-menu children per docs/03 IA; footer inherits automatically.
- Flagship views hand-built: homepage (hero, quick actions, about band, faculties, programme teaser, news+events, Scholar band, partners, CTA band, mobile action bar with Apply), faculties index+show, programme finder+programme page (Course JSON-LD, buy-box style decision panel), full MRU Scholar portal (home, publications with 4-way filtering, publication detail with ScholarlyArticle JSON-LD + DOI links, scholars directory, profile).
- SitemapController rebuilt for the university URL set; robots kept. Privacy + Terms rewritten institutionally (Data Protection & Privacy Act 2019 referenced). Gallery made standalone (personal rail removed) and the model owner's 24 personal photos unpublished pending real university photography. e-Learning catalogue hero copy institutionalized.
- New tests: UniversityPagesTest (whole-nav smoke + JSON-LD + legacy redirects + sitemap), ProgrammeFinderTest (13 assertions incl. filters, unpublished-404, Course JSON-LD), ScholarPortalTest (publish gating, author order, view/download counters, streamed PDF, search), ContactFormTest (FormShield honeypot success-lie, timing stamp, validation). Obsolete portfolio tests removed; SiteNav/WhatsApp/CTA/ActionBar tests updated to the university IA.

## 2026-09-03 — Phase D completed by parallel build agents + integration
- Agent-built: 14 institutional pages (about, who-we-are, governance, council, searchable staff directory, campus life, accommodation, sports, guild, alumni, library, almanac, downloads, contact with shielded form) — all 200 with real content; 12 admissions/events/vacancies pages (FAQPage + Event JSON-LD, Google-Calendar links, careers application box); 11 admin CRUD resources (22 views) on td-admin + University/Scholar nav groups + admin smoke tests (4 passed / 37 assertions).
- **Environment bug found by an agent:** `public/storage` was a stale symlink into the model project (`muhindo-app`) — every storage asset 403'd; `artisan storage:link --force` fixed it.
- Testimonials band restored to the homepage ("What our students say", auto-hidden until real quotes exist) — the old-site homepage had one and the admin CRUD already existed; the two failing testimonial tests were the product telling us the feature was missing, not the tests being stale.
- Test reconciliation: e-learning discoverability assertions moved from the removed home strip to the catalogue; /contact redirect test now expects a real page; JSON-LD home test expects CollegeOrUniversity; verification/manifest page lists updated; ampersand double-escape regression test repointed to /faculties ("Faculties & Schools").
- **Full suite: 1112 passed, 1 skipped, 0 failed (4404 assertions).**

## 2026-09-03 — Phase E: polish
- Pint clean on all new code; PHPStan: new code clean (relation generics `@return HasMany<...>` added to all university models); remaining 34 errors are pre-existing model-app code (analytics listeners, Livewire Today, Insights) — left untouched deliberately.
- README rewritten for the university project (setup, legacy import, production checklist incl. key rotation and the missing prospectus/handbook note).
- Final sweep: 41 public URLs all 200 against the real database.

## 2026-09-03 — Phase F: visual redesign (look & feel)

Full design pass against the brand brief. Documented in [05-DESIGN-SYSTEM.md](05-DESIGN-SYSTEM.md).

- **CSS architecture:** the layout's 1,398-line inline `<style>` was extracted to `public/css/mru.css`, so the sheet is downloaded and cached once instead of being re-sent inside the HTML of every page. The file is two layers — the inherited base, and a clearly-bannered *2026 refresh* layer that comes last and settles it.
- **Palette:** navy `#023479`, amber `#F5A623`, white ground, `#333333` ink, as briefed. The accent carries two tokens on purpose — `--gold` for surfaces, `--gold-d` (`#9A6205`) for text, because `#F5A623` on white is ~2:1 and fails WCAG for copy. `--tx3` was darkened from `#8A929E` to `#6E7683` for the same reason (3.2:1 → 4.6:1).
- **Typography:** Inter replaced by a pairing — **Fraunces** (display serif, heritage) + **Plus Jakarta Sans** (UI/body sans, modern). Both self-hosted **variable** fonts, Latin subset: 128 KB for the entire 300–800 range, preloaded, no third-party request. (First attempt pulled static instances at 66 KB *per weight*; switching to a weight-range request returned one variable file per family.)
- **Header:** now `position:sticky` rather than fixed, in two rows — a navy utility bar (E-Portal, e-Learning, Library, MRU Scholar, phone, Staff Login) over a taller 76px main bar on a wider 1240px shell. Past 40px of scroll the utility row folds away and the bar tightens to 64px with a shadow, driven by an `.is-scrolled` class on `<html>` with a 40-down/10-up hysteresis gap. The header's second CTA was dropped: two buttons plus a six-section menu was what pushed the wordmark into the navigation.
- **Footer:** rebuilt in four zones — newsletter invitation, the two campus cards with map links, five link columns, accreditation bar — over a navy gradient with an oversized crest watermark. The **newsletter now works**: `NewsletterController` + `/newsletter`, honeypot-protected, writing to the table that already held 70 migrated subscribers.
- **Geometry & space:** one radius family (6/10/14/20/28/pill) applied to buttons, cards, inputs, chips, panels and tiles; four navy-tinted shadows replace hard borders; section padding raised to 88px; body set at 15.5px/1.7.
- **Consistency:** the same tokens, fonts and radii were applied to the back office (`td-admin.css`) and the auth screens.
- **Verified in a real browser.** Chrome was driven over the DevTools Protocol (Node 22's built-in `WebSocket`) to screenshot and measure pages at desktop and mobile. This caught that the apparent mobile overflow was a headless artifact — without device emulation the viewport meta tag never applies — while genuine emulation showed `scrollWidth === clientWidth === 390`. It also found the real bug: the hover-swap button labels have no hover on touch, so the hidden longer label was sizing the button and pushing the visible one past its padding.
- **Removed:** the oversized ghost word behind page titles (cropped by its own section at every width) and the hard offset "plate" shadows on cards (a square motif fighting rounded geometry).
- Test suite green — **1117 passed** — after updating the tests the redesign legitimately invalidated: header CTA assertions, footer coverage (now: every menu *section* must stay reachable, and a section given a column must list all of it), and the CSS-inspecting tests, which now read `public/css/mru.css` instead of grepping page HTML.
- Page weight (Debugbar off): 63–84 KB HTML per page, plus 128 KB CSS and 93 KB fonts, both cached across the site.

## 2026-09-03 — Phase G: menu rebuild, footer diet, content clean-up

- **The hover bug, found and fixed.** The mega panel was anchored to the header while its
  `.nav-item` was left `position:static`, so each item's invisible hover-bridge resolved against
  the header too and spanned the full header width. Six stacked full-width bridges meant a
  pointer under the bar was simultaneously inside several menu items — panels opened over one
  another, and travelling toward one left the item that opened it. Rebuilt to remove the gap
  instead of bridging it: nav items stretch the full bar height, the panel is anchored at
  `top:100%` (that same edge), and because the panel is a DOM child of the item, hovering it
  keeps the item hovered. Verified by dispatching real pointer moves over CDP: exactly **one**
  panel stays visible when the pointer travels from trigger into panel.
- **A genuinely wide menu.** Shell widened 1240 → 1400px; the panel is now full-bleed with a
  `290px + 1fr` inner grid — section intro beside a three-column link grid (two at 1180px,
  stacked at 1000px). Header fit re-measured at 1600/1440/1280/1100/960px: no overflow, nav
  never wraps.
- **Footer cut from ~1000px to 597px.** Removed the section-sized newsletter block with its own
  heading, the two large campus cards (repeating an address printed directly above them), and
  the "quick links" column that duplicated its neighbours. What remains: one five-column link
  grid, a slim strip carrying the newsletter and the portals, and the legal bar. Also reserved
  space so the floating WhatsApp button stops covering the "Contact" link.
- **Content bugs found while investigating the home page** (all were visible on it):
  - **38 lorem-ipsum filler posts** imported from the old WordPress site carried the newest
    dates in the archive, so they were the three stories the home page led with. Deleted, and
    the importer's spam filter now catches them.
  - **3 developer test posts** ("Publication capability test…", "test123123") likewise. The
    importer now has a title-only junk-title filter — title-only because "test" appears inside
    plenty of real words and a body-wide match would take genuine stories with it.
  - **The testimonials were the model author's own contacts** — real, named people (a professor,
    a vice chancellor) carried over with *empty* quotes, presented on MRU's home page as if they
    endorsed the university. Cleared; the band auto-hides until MRU adds real ones via the admin.
  - News now leads with genuine stories: the 2026 Pepsi University Football League title, the UFL
    final, the Buganda Leaders' Retreat. 70 real articles remain.
- **Not bugs, verified:** the blank sections and missing partner logos in full-page screenshots
  were capture artifacts — the first from screenshotting mid-reveal-transition, the second from
  `loading="lazy"` images never entering view during `captureBeyondViewport`. Probing the live
  DOM confirmed the reveal observer leaves nothing hidden in the viewport, and the partner logos
  load at 233×52 when scrolled to.
- CSS tidied: the shell width has one home in the token block, and the retired watermark's four
  tuning rules collapsed to the single line that decides. Braces balanced, 2,217 lines.
- Suite green: **1117 passed**.

## 2026-09-03 — Phase H: the menu answers to hover, click, touch and keyboard

- **Why it felt click-only.** The panel was opened purely by CSS `:hover` / `:focus-within`.
  Clicking a trigger focuses it, so `:focus-within` pinned the panel open and a second click
  could not close it — CSS has no way to let a click dismiss a hover state. The open state is
  now a class the script owns (`.nav-item.is-open`), with the CSS-only behaviour scoped to
  `html:not(.js-nav)` as the no-JS fallback.
- Interaction model: hover opens after a 70ms intent delay (instantly if a panel is already
  open, so moving along the bar feels like one menu) and closes after a 180ms grace period;
  click always toggles and beats any pending hover timer; a `pointerdown` flag stops the focus
  the click causes from re-opening what it just closed, and keyboard focus is told apart with
  `:focus-visible`; Escape closes and restores focus to the trigger; click-outside closes;
  following a link closes (nothing else would under `wire:navigate`). Touch screens report no
  hover and get the click toggle alone.
- Polish: `aria-haspopup`/`aria-expanded` kept truthful, a 10px slide-in on the panel, an amber
  marker under the open section, and a `pointer-events:none` scrim dimming the page behind.
- **Verified with real browser input**, not assumptions: pointer moves, clicks and key events
  dispatched over CDP — 10/10 checks including *click again closes*, *switching sections never
  leaves two panels open*, and *Escape refocuses the trigger*.
- **A cascade trap, found and fixed.** §15's `.nav{display:flex}` was written after
  `@media(max-width:900px){.nav{display:none}}`, and since a media query adds no specificity it
  won at every width — the desktop menu was sitting on top of the phone header. The
  small-screen collapse is now the last block in the sheet (§17) so no later component rule can
  undo it.
- Dead CSS removed: §12 still carried the *original* broken anchoring (`.nav-item.has-menu
  {position:static}` plus a centred `.mega` and an unscoped `:hover` transform) that §15 had
  superseded. `SiteNavTest` now also fails on the comma-separated form of that selector, which
  the first regex let through.
- Suite green: **1119 passed**.
