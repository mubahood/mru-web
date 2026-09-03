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

## 2026-09-03 — Phase I: buttons that size to their own words, and a real slider

### Buttons sized by text nobody could see

Every "hover reveals a longer label" button (24 of them, across 13 views) stacked two spans in
one grid cell — a short resting label and a longer hover label — so the button was always sized
by whichever was wider, even though only one was ever visible at a time. Measured before the
fix: "Apply Now" rendered at **341px** because "Apply on the E-Portal" was hiding underneath it,
while "WhatsApp Admissions" — a genuinely longer, fully visible label — sat at 258px. Buttons of
unrelated width, set by invisible strings.

Fixed by collapsing every `<span class="cta-a">…</span><span class="cta-b" aria-hidden>…</span>`
pair to the single label actually worth keeping (the hover label's icon, the resting label's
words), and deleting the `.cta` grid-stack rule and its touch-only override from the stylesheet
entirely. Re-measured after: `Apply Now` 170px, `Explore Programmes` 246px, `WhatsApp Admissions`
258px — every button now the width of its own words.
`CallToActionConsistencyTest` was rewritten from "the hover label matches across pages" (the
mechanism that caused the bug) to "no button may carry a hidden second label, in the markup or
the stylesheet, ever again."

### A full-height, modern home slider

Three real photographs of the university — a graduation, the Buganda Leaders' Retreat MRU
hosted, a visit from international partners — replaced the flat hero. Built from scratch:

- **Markup/data split.** `university.partials.hero-slider` renders from the
  `university.hero_slides` setting (title, text, two CTAs, an image base name), so the slides are
  content, not markup. `UniversityContentSeeder` carries the curated three; the legacy importer
  was repointed to write `university.hero_slides_legacy` instead, so re-running it can never
  silently put the raw, uncurated import rows back on the home page.
- **`100svh`, transparent header.** The stage fills the viewport (`svh`, not `vh` — on a phone
  `vh` is the tallest the viewport ever gets, so a `100vh` hero sits partly under the browser's
  own toolbar on load). The header has no background of its own while a hero is present
  (`body.has-hero`) and reads its colours from three CSS variables (`--hdr-fg`, `--hdr-brand`,
  `--hdr-crest`) so the transparent and solid states differ by *value*, not by a second copy of
  every rule; it turns solid on scroll or whenever the mega menu is open (a white panel needs a
  white bar under it).
- **First-paint discipline.** Only the first slide's image loads eager/high-priority/sync; the
  rest are lazy. Each slide ships a real `srcset` (700/1100/1600w).
- **Interaction, built and verified with real browser input, not assumptions.** Autoplay (6.5s,
  the active dot doubles as the countdown via a CSS fill animation), arrows, dots, swipe,
  Escape-equivalent (arrow keys), and pause-on-hover/focus/hidden-tab. 14 checks dispatched over
  CDP (real `Input.dispatchMouseEvent`/keyboard/touch events, not just reading the DOM): fills the
  viewport, header floats and reads light on the photo, arrows/dots navigate, exactly one slide is
  ever exposed to assistive tech, autoplay genuinely advances unattended, hover genuinely holds it
  still. One of those checks caught a real bug before it shipped: pausing only *cleared* the
  timer, so a stray `play()` call (a second `mouseenter`, a visibility change landing mid-
  transition) could start a fresh interval that outlived the pause — the slider would keep moving
  under a reader who had stopped it by hovering. Fixed by having the tick itself check a `paused`
  flag, not just relying on the timer being cleared.
- **Three visual bugs found in the first screenshot, not left in:** the secondary button's label
  was unreadable (`.hs-ghost` was one class competing with `.btn.ghost`'s two, and the navy from
  the base button won); the headline broke as "…future at / MRU", stranding three letters alone
  on their own line (`text-wrap:balance` plus a wider measure); the previous-arrow sat at
  mid-height directly on top of the headline it was meant to help navigate away from (both arrows
  moved into the control bar at the foot of the stage, alongside the dots).
- **Reproducibility.** The original three responsive image tiers were made by hand with `sips` —
  a step nobody else could repeat. Replaced with `php artisan mru:make-hero-images`, an idempotent
  Intervention-Image command (matching the project's existing `AvatarService` pattern) that
  derives the 700/1100/1600px JPEG set from the source photographs already on disk, `--force` to
  regenerate. Its own output is smaller than the manual pass (187 KB vs 301 KB at the top tier, same
  quality) simply because GD's encoder is more efficient than `sips` at an equivalent setting.
- **A promise the code didn't keep, caught by the tests I wrote to guard it.** The intro band's
  own comment said "the strapline lives here now"; it never actually did — "Rooted in Heritage.
  Focused on the Future." had nowhere left to appear once the text hero was replaced. Restored it
  as the institutional statement opening the band under the slider, in the display serif, rather
  than weakening the test that caught the gap.

### A real caching bug, found while chasing a flaky test

Two of the new slider tests failed in a way that only made sense if settings written *during* a
test were invisible to code that had already read them once in the same test run. They were:
`App\Support\University::get()` kept its own bare `private static array $cache`, entirely
separate from `Settings`' own cache (which already covers the whole table via Laravel's cache
store and is correctly invalidated on every write via `Settings::flush()`). The first call to
`University::get('hero_slides')` in a process pinned it there for the rest of that process — a
write afterwards was invisible to it. Harmless in the common case (a fresh PHP-FPM process per
web request), but a real bug in every longer-lived process this codebase already has: an artisan
command that seeds then reads, a queue worker taking more than one job, the test suite itself.
Removed the redundant layer; `University::get()` now reads through to `Settings::get()` on every
call, which costs an array lookup against an already-cached blob, not a query. A regression test
(`test_settings_written_after_the_first_read_are_not_stale`) pins the fix.

Full suite: **1134 passed**. Menu contract (10/10 interaction checks) and slider contract
(14/14) re-verified over CDP after every change in this phase, not just at the end.

## 2026-09-03 — Phase J: vivid photography, and a real glass header

Feedback on Phase I's slider: too much overlay on the photos, and the header needed to read as
genuine "glass" at the top of the slider rather than a bare transparent bar. Both addressed, and
the second one went through a wrong first attempt that's worth recording.

### The scrim, cut down to almost nothing

The original wash covered the whole frame at up to 95% navy opacity — legible, but the effect was
"a dark navy-tinted photo," which is the opposite of vivid. Replaced with one tight gradient behind
the text column (74% opacity at the left edge, fully clear by 60% of the width) and a shallow one
at the floor for the control bar; everywhere else in every slide is now essentially untouched.
`.hs-media` got a deliberate `saturate(1.14) contrast(1.05)` lift so colour reads as vivid on
purpose, not left to the raw JPEG. Legibility that the scrim used to provide moved to a soft,
wide text-shadow on the copy instead (blur with almost no offset — a lift off the photo, not a
hard drop shadow) and the title's weight went from 600 to 700 for the "bold" ask.

### The glass header: a wrong first attempt, caught by measuring pixels instead of trusting CSS

First pass: a **light** frosted pane — `rgba(255,255,255,.14)` + blur — under the existing white
nav text (`rgba(255,255,255,.95)`). It looked plausible in one screenshot. A computed-style probe
confirmed the values were applied exactly as written; the mistake wasn't in the CSS; it was in
the choice. White text over a translucent *white* pane has contrast that depends entirely on
what's blurred behind it — reliable over a dark patch of photo, unreliable over a bright one (sky,
a pale shirt, the cream tent fabric on the graduation slide), and this header sits over three
different photographs with three different tonal ranges.

Caught this by measuring real rendered pixels rather than trusting the CSS or a single
screenshot: located each `.nav-link`'s rendered rectangle via the DOM, took a screenshot at 1:1
device-pixel scale so the coordinates lined up exactly, and sampled the backdrop pixels directly
behind where the glyphs sit (deliberately above the text's own vertical band, inside the `.bar`
row and below the separate `.topbar` row above it — the first sampling pass caught the edges of
*topbar* glyphs by mistake and reported false failures around 1.4:1, which is itself a reminder to
scope a measurement to the exact region a claim is about). Computed WCAG contrast from the actual
composited colour. The light-glass version's worst case, correctly measured, would have failed
badly wherever a slide's photo was bright.

Fix: tint the glass **dark** instead — `rgba(1,15,38,.58)` + `blur(22px) saturate(160%)` — so
white text composites against something close to navy regardless of what photograph is behind it.
Re-measured the same way against all three real slides: worst case **5.0:1** (heritage slide, the
brightest — a room with cream walls and window light), typical case 10–18:1 (graduation and
international slides). All comfortably clear the WCAG AA floor of 4.5:1 for normal text, not just
the 3:1 floor for large UI text. The principle that falls out of this: the wash a *photograph*
needs dialled back for vividness, and the wash behind *functional chrome text* that has to stay
legible against three different unpredictable backgrounds, are different jobs — conflating them
into one "glass" treatment was the actual mistake, not any single CSS value.

Verified after: menu interaction contract 10/10, slider interaction contract 14/14 (one assertion
in the scratch verification script itself had to be fixed along the way — a nested-escaping bug
silently turned `\d` into `d` inside a regex built through several layers of string interpolation,
which is its own small lesson: prefer `split()`/`indexOf()` over a regex when a value is being
built through more than one layer of templating). Full suite unaffected (**1134 passed** — this
phase touched only `public/css/mru.css`).

## 2026-09-03 — Phase K: the utility row waits for the scroll

Feedback on Phase J: the topbar's utility row (E-Portal, e-Learning, Library, MRU Scholar, phone,
Staff Login) was visible from the very first frame, competing with the photograph for attention
exactly where the slider is supposed to make its opening impression. Every other page keeps the
Chrome-wide rule — topbar visible at rest, folds away only past 40px of scroll — but those pages
have no photograph to protect. A hero page now inverts it: the topbar opens at `max-height:0` and
only reaches its usual 44px once `html.is-scrolled` is set. At rest, a hero page shows just the
crest, wordmark, and six-section menu directly over the photo; scrolling brings the utility row
back for its keep as persistent navigation.

`.hs-copy`'s own header-clearance padding (`var(--hdr-total)`, keeping the slide's eyebrow and
title clear of the floating header) dropped from 120px to 104px on desktop to match the shorter
header a hero page now starts with. Mobile is unchanged at 88px — the topbar there is already
hidden outright below 900px, hero or not, so nothing about its clearance needed to change.

CSS-only change, confined to the floating-header block in `public/css/mru.css`. Re-verified over
CDP: menu contract 10/10, slider contract 14/14. Full suite unaffected (**1134 passed, 1
skipped**).

## 2026-09-03 — Phase L: the hero learns the site's own signature, and a second glass mistake

Feedback on Phase K: the slider itself still read as generic — "not elegant, still blander" —
distinct from the header-visibility fix. Two things changed to answer it, one visual device and
one real accessibility bug found the same way the header's was.

### Borrowing `.sec-idx` instead of ignoring it

Every interior section on the site opens with the same signature: a small gold pill-rule plus a
tracked uppercase two-digit label. The homepage's own hero — the first thing anyone sees — never
used it, which is exactly backwards. The eyebrow now carries a matching gold `::before` rule and a
per-slide index rendered server-side (`str_pad($i + 1, 2, '0', STR_PAD_LEFT)` in
`hero-slider.blade.php`, the same zero-padding convention `$idx()` already uses on interior
pages) — "01 Muteesa I Royal University", "02 Rooted in heritage", "03 International" — so it's a
real string in the response, not a value only CSS knows about. A slim vertical gold spine (a
gradient rule, faded at both ends) now anchors the text column the same way, and the dot/arrow
controls — previously two bare widgets floating directly on the photo — each sit inside their own
dark-glass chip, so the control row reads as one designed object.

### The ghost button had the header's original mistake — just not caught yet

Phase J measured the header carefully and fixed its light-glass contrast problem. It did not
occur to measure the slider's *other* glass surface: the secondary "ghost" CTA button kept its
original `rgba(255,255,255,.10)` treatment straight through that fix, because nobody had pointed
the same pixel-sampling method at it specifically. Doing that now, on all three real slides: worst
case **2.07:1** (international, where the button happens to land over a pale blouse) and **3.2:1**
(graduation) — both clear WCAG AA failures for text this size (floor is 4.5:1), with only the
heritage slide scraping past at 4.64:1. Same fix, same reasoning as the header: dark glass
composites the same white label against something close to navy no matter what the photo
underneath is doing. Re-measured after: worst case **5.98:1**.

The new dot/arrow chips got the same scrutiny rather than being assumed safe because they reuse a
proven recipe: arrow icons measured 8.6:1+ against their chip in the worst case (no change
needed), but the inactive dot bars measured only ~2.8:1 against their new dark-glass backing —
under WCAG's 3:1 floor for non-text UI components, a smaller problem than the ghost button but a
real one. Raised from 32%
to 50% white opacity; re-measured at 5.1:1+.

**The lesson this phase reinforces:** a contrast fix verified on one element doesn't transfer to a
sibling element just because it looks similar — the ghost button sat two phases away from the
header fix, using the exact treatment that fix had already disproven, because it was never itself
measured. Every glass surface on this page has now actually been sampled, not assumed.

Blade change confined to `hero-slider.blade.php` (the eyebrow numeral); everything else is CSS.
Re-verified over CDP: menu contract 10/10, slider contract 14/14. `HeroSliderTest` unaffected (10
passed). Full suite: **1134 passed, 1 skipped**. 45-route sweep clean.

### Source photography: one swap, one left alone

Two of the three slides' source photos read as generic snapshots rather than vivid hero
photography — the heritage slide was an indoor conference room with no visible tie to Buganda
heritage, and the international slide is a static posed lineup. Which photographs represent the
university is a content call, not a styling one, so this was surfaced rather than changed
unilaterally: a survey of the imported media library (`storage/app/public/news`, cross-referenced
against the `posts` table) turned up candidate replacements for both, each viewed directly rather
than judged by filename.

The heritage candidate was a clear upgrade on every axis — a photo of the 2025 Ommanyi
inter-institutional games: outdoor, vivid, the Kingdom of Buganda's own branding and the MRU crest
visible on the backdrop it was taken in front of. Approved and applied:
`MakeHeroImages::SOURCES['hero-heritage']` now points at
`news/WhatsApp-Image-2025-11-26-at-11.48.59.jpeg`; the derived 700/1100/1600px set was
regenerated with `--force`; the seeder's `alt` text and doc-comment were corrected to describe
what the new photo actually shows; the live `university.hero_slides` setting was patched in place
(reading the current array and replacing only the heritage entry's `alt`, rather than re-running
the whole seeder and risking any admin edits made to the other two slides or unrelated settings
since the initial seed). `MakeHeroImagesTest`'s two hardcoded references to the old filename were
updated to match — a reminder that a source-file rename has to chase its own test fixtures, not
just the command that reads them.

The international candidate was a genuine tradeoff rather than a clean win — more candid and
elegant as a photograph, but with no visible connection to MRU, Uganda, or even Africa, where the
current (weaker) photo at least self-evidently shows a diverse group together. Left unchanged.

Re-verified after the swap: `MakeHeroImagesTest` (4 passed), ghost-button contrast re-measured
against the *new* photo specifically rather than assumed to inherit the earlier fix (worst case
7.94:1 — the previous fix wasn't photo-specific, but the claim that it holds here still needed its
own measurement), slider contract 14/14, full suite **1134 passed, 1 skipped**, 45-route sweep
clean.

## 2026-09-03 — Phase M: three more slides, and motion that actually moves

The brief this time: find genuinely elegant, meaningful photographs already sitting in the
imported media library — not more of the same three themes — and use them to show more of who is
actually at this university. Add roughly three. Give the whole slider more motion. Don't let
quality slip to hit the number.

### Sourcing, the same way as the heritage swap: survey, view, judge honestly

A broader survey (this time across the whole `public` disk, cross-referenced against the `posts`,
`staff_members` and `gallery_photos` tables, not just `news/`) turned up six real candidates and
was explicit about what it *couldn't* find: no hero-quality photograph of the Masaka campus exists
anywhere in the imported library — the only Masaka-tagged photos at usable resolution show a
beige conference room that doesn't read as "Masaka" without its caption, and the only genuinely
Masaka-*looking* candidate fails the resolution bar outright. Rather than force a weak photo in to
check a box, that gap was left open and reported rather than papered over — the same call made for
the international slide's alternate candidate in the previous phase.

Of the five real candidates, six were personally viewed at full resolution (not judged by
filename or the survey's own description) before choosing three:

- **Dr Liezel Williams** (Nelson Mandela University, visiting) — genuinely excellent: sharp,
  candid mid-gesture, vivid African-print jacket against a plain backdrop. The obvious best single
  find, and landscape-native (2560×1704), so it needed no special cropping.
- **Student Guild Elections** — outdoor, colourful, real candid energy, an actual campus building
  and mural visible behind the registration table. The only candidate that shows campus *grounds*
  at all; every other photo in the slider (old and new) is either a crowd, a portrait, or an
  interior.
- **A packed lecture hall during orientation** — genuine classroom life, visible diversity
  (hijab-wearing students among a mixed crowd), busier and less polished than the other two but
  the only photo anywhere in the slider showing what a normal academic day actually looks like.

**Deliberately set aside:**

- A portrait of Prof. Maria Musoke (University Council) was, if anything, more visually striking
  than the Williams photo — but it's a vertical source (1709×2560) against a slider built for
  full-bleed landscape frames, and it serves almost the same theme Williams already covers
  (a distinguished woman in academia, mid-conversation, vivid dress). Using both would have been
  thematically redundant; the vertical one was the one with the crop risk, so it lost.
- A curriculum review workshop photo (staff diversity) was sharp and genuine but read as another
  generic seated-meeting room — exactly the composition already ruled out once this session for
  the old heritage photo. Passing on it was the same lesson applied a second time, not a new one.

### The slider learns to move

The Ken Burns zoom already in `.hs-media` (`scale(1.07) → scale(1)` over 9s) settled every slide
identically. Odd and even slides now drift from opposite sides as they settle
(`translate3d(1.4%,0,0) → 0` vs `translate3d(-1.4%,0,0) → 0`, alternating by `:nth-child`), so a
run of six slides doesn't repeat one mechanical zoom six times. Verified over CDP by sampling the
actual computed `transform` mid-transition on a real slide change (not just the settled state) —
at 400ms into slide 2's activation the matrix showed `scale(1.086) translateX(-23.3px)`, correctly
almost-unmoved from its 1.09/-1.4% starting point and correctly signed for an even slide; sampling
again after autoplay had advanced to slide 3 showed `scale(1.059) translateX(+15.6px)`, matching
the maths for ~34% into an odd slide's *own* transition. The very first slide on first paint
intentionally shows no zoom at all — it ships already `.is-active` in the server-rendered HTML, so
there is no prior state for the transition to animate from, which is the correct behaviour (no
jarring zoom on the very first thing a visitor sees), not a gap.

Two more entrances were choreographed rather than left instant: the vertical gold spine now
unfurls top-to-bottom (`scaleY(0)→1`, 0.7s) the instant a slide activates, and the eyebrow's own
gold marker draws left-to-right a beat later (`scaleX(0)→1`, 0.5s, 0.12s delay) — both feed into
`prefers-reduced-motion:reduce`, which now also has to name the two new `:nth-child` transform
rules explicitly (their extra specificity would otherwise have beaten the old blanket
`.hs-slide.is-active .hs-media{transform:none}` override inside that same media query).

### Six dots, still one design

`.hs-dots`' chip now needs to hold up to twice as many dots as it was built for. Shrunk from 58px
to 36px per dot at the point they first got their glass chip (Phase L), with a further mobile-only
reduction to 22px/8px-gap here — measured directly rather than assumed: the six-dot chip's real
`getBoundingClientRect()` on a 375px viewport comes to 202px, comfortably inside frame.

Every new glass surface was measured against the new photographs specifically, the same discipline
as every prior phase: ghost-button contrast worst case across all **six** slides is 5.31:1 (the
Williams photo's plain, bright backdrop — the least forgiving background tested yet — still
clears WCAG AA's 4.5:1 floor with margin); arrow icons 5.59:1+; inactive dot bars 4.33:1+ against
their own chip (floor is 3:1 for non-text UI).

### What had to move to fit six

`MakeHeroImages::SOURCES` gained three entries; `mru:make-hero-images` (no `--force` needed, since
the existing three were untouched) derived the new responsive sets.
`UniversityContentSeeder`'s hero-slide array grew from three entries to six, in a deliberately
chosen order — the graduation photo still opens (it alone pays the first-paint cost and was
already the strongest image in the set) — and the live `university.hero_slides` setting was
replaced with the complete six-slide array directly, the same targeted-write approach as the
heritage swap, rather than re-running the whole seeder and risking any settings changed since.
`MakeHeroImagesTest`'s fixture and count assertions moved from three sources / 9 tiers to six
sources / 18 tiers.

Re-verified: `MakeHeroImagesTest` (4 passed), slider contract 14/14, menu contract 10/10, full
suite **1134 passed, 1 skipped** (assertion count up by exactly 9 — the new test's 6×3 existence
checks replacing 3×3), 45-route sweep clean.

## 2026-09-03 — Phase N: a real animation bug, a tighter header, a reorder

Three pieces of feedback on Phase M's six-slide slider, one of them a genuine bug rather than a
taste call.

### The first slide never moved — a `transition` needs a "before"

The report: the slider "isn't animating until clicked next." Verified before touching anything —
sampled the first slide's `.hs-media` computed `transform` at 300ms, 2.3s and 5.3s after a fresh
page load with zero interaction, and it read the identical settled matrix every time. The cause is
inherent to `transition`, not a mistake in the values: the first slide ships `class="hs-slide
is-active"` already in the server-rendered HTML, so there is no discrete "before → after" class
change for a transition to interpolate from on that slide's first paint — the browser paints the
final value immediately. Every *other* slide activation is a genuine class change (JS adding
`.is-active`), which is exactly why Phase M's own verification (sampling a slide change, not the
initial load) missed this: it happened to test the one case where the mechanism doesn't apply.

Fixed by switching `.hs-media`'s zoom/pan from a `transition` to a named `@keyframes animation`
(`hs-zoom-odd` / `hs-zoom-even`), applied via `.hs-slide:nth-child(odd/even).is-active .hs-media`.
An `animation` runs from its own `from` keyframe the instant it is first applied to an element,
on-load class or not — the same reason `.hs-rise` already animated the first slide's text in
correctly, which is what made this an inconsistency between two adjacent rules rather than two
separate design decisions. `prefers-reduced-motion` updated to match: `animation:none` on the two
`:nth-child` rules (replacing the old `transform:none`), plus the resting `transform` neutralised
so a reduced-motion visitor never sees the 1.09× resting zoom either.

Re-verified by sampling again after the fix: 300ms/2.3s/5.3s now show a genuinely progressing
matrix (`scale(1.087)→1.067→1.037`, translate correctly signed for the slide's parity) — motion
confirmed from the very first frame, not inferred from the code reading correctly.

### Header height, and the second variable that had to move with it

`--hd` (76px) was set when the header last needed room for a wider menu; asked to tighten it now.
The crest, not the two-line wordmark, was the tallest thing in the row (46px vs. the text block's
own ~31px), so it led the cut: `--hd` 76→66px, crest 46→40px (38→34px scrolled), scrolled bar
64→56px. `--hdr-total` (the hero copy's own clearance for the floating header) is a *second*,
independently-set variable that happens to describe the same header — it isn't derived from `--hd`
automatically, so it had to be walked down by the same 10px by hand (104→94px desktop, 88→78px
mobile) or the hero text would have kept its old clearance above a header that no longer needed it,
opening a gap that wasn't there before.

### Reordered: the strongest of the six leads

The heritage photo (Ommanyi games — outdoor, vivid, explicit Kingdom of Buganda branding) swapped
into slide 1; the graduation photo moved to slide 3. Since `hero-slider.blade.php` derives
first-paint priority from array position (`$i === 0`), not a hardcoded slide name, this needed no
template change — only the seeder's array order and a matching live-settings write.

Re-verified: menu contract 10/10, slider contract 14/14, ghost-button contrast re-measured at the
shifted positions (header height changes moved where the copy block sits on the photo) — worst
case still 5.24:1, full suite **1134 passed, 1 skipped**, 45-route sweep clean.

## 2026-09-03 — Phase O: autoplay was never running for reduced-motion visitors

Feedback: "the first photo fails to leave the slide until clicked, and takes too long." Phase N's
own fresh-load verification (sampling the *zoom transform*) had already shown the first slide
correctly animating — but that check never asked whether the slide itself ever advances on its
own, only whether its decorative motion plays. A separate, precise re-check — polling the active
slide index every 300ms from a stone-cold load with zero interaction — showed perfectly regular
~6.5s advances with no anomaly at all on a plain browser. The discrepancy pointed at the one
environmental variable that check couldn't see: `prefers-reduced-motion`.

`initHeroSlider()`'s `play()` had `if (still || paused) return;` — `still` being
`window.matchMedia('(prefers-reduced-motion: reduce)').matches`. For any visitor with that
preference set, `play()` did nothing at all, ever: the `setInterval` that drives autoplay never
started, so the slide could only change if the visitor clicked, swiped, or used arrow keys. Six
slides now exist specifically to show a range of who is at this university — a reduced-motion
visitor landing on the homepage would only ever see the first one.

This reads like a defensible accessibility choice in isolation, but it's stricter than what's
actually required: WCAG 2.2.2 asks that auto-moving content be *pausable*, which this slider
already is (hover, focus, and a visible pause state), not that it never move at all for a
reduced-motion visitor. The fix keeps every genuinely decorative motion gated behind
`prefers-reduced-motion` exactly as before (the dot's animated countdown fill via `.no-autoplay`,
the media zoom/pan, the spine and rule entrance animations) and removes only the `still` check
from `play()`, so the slides still advance — just without the flashy motion around each change.

Verified by emulating `prefers-reduced-motion: reduce` over CDP end to end: `matchMedia` reports
`true`, `.no-autoplay` is applied, `.hs-media`'s transform reads the static `none` (no zoom/pan
plays) — and the active slide still advances from 0 to 1 within the expected window, both before
and after the swap. Re-verified normal (non-reduced-motion) behaviour is unaffected: slider
contract 14/14, menu contract 10/10, full suite **1134 passed, 1 skipped**, 45-route sweep clean.

The lesson: a "the first slide is stuck" report and a "the first slide's zoom doesn't play" fix
are not the same claim, and confirming one doesn't confirm the other — this phase's own earlier
verification tested the motion, not the fact of advancing, and the two turned out to fail under
different conditions.

## 2026-09-03 — Phase P: the topbar goes on a diet, and onto phones

Feedback: thin out the topbar's own vertical padding, and put it on mobile too — but not the whole
thing, just whichever single link actually earns a permanent line on a small screen.

`.topbar` (and its inner `.wrap`) dropped from 44px to 36px, matching height everywhere it's
mentioned: the resting state, and the hero-page scrolled-reveal state that shares the same number.
Below 900px the row used to be `display:none` outright; that blanket rule is gone, replaced with a
`.tb-desktop-only` class on everything except E-Portal (e-Learning, Library, MRU Scholar, the
phone number, Staff Login), hidden only under 900px. E-Portal was the obvious single survivor —
the one utility link a phone visitor mid-browse is actually likely to want *now*, versus the other
five, which are all a normal nav tap or a footer link away already.

This is a global change, not homepage-only: `.topbar` renders in the shared marketing layout, so
every public page's mobile header now carries the thin E-Portal line, not just the hero pages.
Checked deliberately across all four combinations that matter — desktop and mobile, hero and
non-hero, at rest and scrolled — since a hero page's topbar has its own separate hide-at-rest
rule (Phase K) that had to keep working unchanged alongside the new mobile-visibility rule, not
be replaced by it. On a hero page the topbar still opens at zero height on every viewport; the
mobile-vs-desktop question only decides which links show *once revealed* by scrolling.

`--hdr-total` (the hero copy's own header clearance) needed no change here — it only governs the
gap before the topbar reveals itself, which didn't move; what happens on scroll after that point
isn't something it has to account for.

Re-verified across the full range: 360px, 390px, 820px (just under the breakpoint) and 1600px
screenshots, menu contract 10/10, slider contract 14/14, full suite **1134 passed, 1 skipped**,
45-route sweep clean. No PHPUnit test needed updating — every link is still server-rendered on
every page, just carrying one new class; nothing was removed from the markup, only from what's
visible under 900px.

## 2026-09-04 — Phase Q: the homepage sections, per `docs/06-HOMEPAGE-SECTIONS-PLAN.md`

A planning document went in first (`docs/06-HOMEPAGE-SECTIONS-PLAN.md`) — a line-by-line audit of
every homepage section, cross-checked against the existing `03-RESEARCH-TRENDS-BEST-PRACTICES.md`
competitor research, before any code changed. What follows is what actually shipped from that
plan, in the order it happened. Testimonials was explicitly excluded on instruction — no
fabricated quotes.

### Two real data bugs, fixed at the schema level

**Programmes showed a random six on every page load.** `Programme::published()->inRandomOrder()`
meant the homepage's "best foot forward" academic showcase was actually a lottery — reload and
get six different ones. Added a `featured` boolean (migration
`2026_09_03_233241_add_featured_to_programmes_table`, defaults `false`), a matching checkbox on
the existing Programme admin form, and swapped the query for
`Programme::published()->featured()->orderBy('sort_order')->limit(6)`. Marking `featured=false`
by default on a fresh migration meant the section would have gone from "six random" to "zero" the
moment this shipped — so six real, published programmes were deliberately chosen to seed the
flag, one per faculty (Bachelor of Education Secondary, Bachelor of Business Administration,
Bachelor of Mass Communication, Bachelor of Information Technology, Master of Business
Administration, Bachelor of Civil Engineering) plus a second STEAD pick for breadth, covering all
five faculties rather than clustering in one.

**Partners had no visibility flag at all.** Every row in the `partners` table rendered on the
homepage, forever, with no way to curate. Added `show_on_home` (migration
`2026_09_03_233242_add_show_on_home_to_partners_table`, defaults **true** specifically so the four
partners already showing didn't silently vanish the moment this shipped), a matching admin
checkbox, and a `Partner::showOnHome()` scope. Confirmed after migrating: all four existing rows
still read `show_on_home=true`.

**The Scholar section's latent N+1** (`authorNames()` walking an unloaded `authorRows.scholar`
relation per publication) was closed with one eager-load added to the controller query — harmless
today at `limit(3)`, the correct pattern if that number ever grows.

Also removed while touching this controller: the dead `$heroSlides` fetch (the hero partial
already re-fetches the same Settings key itself, independently).

### The homepage's own numbered-section language, applied to two things that were dormant

**A count-up stat animation existed fully built in the shared layout JS
(`[data-count]` → `countUp()`) and had never been wired to anything, anywhere on the site.** It
already knows to leave a non-numeric value (`NCHE`) alone and animate only the ones with a real
number in them. Turning it on for the homepage stats band took exactly one attribute
(`data-count` on `.stat-row`) — no JS, no design work, a capability that had already been paid for
and never spent.

**An intake-deadline strip** now sits between the stats and the quick-action cards — a slim gold
pill, not a second section, reading `university.admissions.deadline_note` (already seeded, never
surfaced on the homepage before). Directly named in the existing research doc as a pattern worth
copying (UCU's "Applications for September Intake now open").

### Two new sections

**"Two Campuses"** (now `.sec-idx` `03`, after Faculties rather than before — a perfectly
reasonable "what can I study → where would I study it" order, differing slightly from the plan
doc's original `02` placement without changing the plan's intent). Needed zero new data:
`university.contacts.campuses` already had name, location, and a real Google Maps link for both
Kakeeka (Mengo, Kampala) and Kirumba (Kirumba, Masaka), confirmed by reading the seeder directly
before writing a line of view code. Two cards, a thin top accent rule distinguishing them (gold
first, navy second — confirmed by reading the actual computed `::before` styles over CDP, not
assumed from the CSS source), reusing the site's existing `.card` treatment rather than inventing
a new one. Deliberately sidesteps a real gap surfaced during the hero-slider work: no hero-quality
photograph of the Masaka campus exists in the imported media library, so a map-and-facts section
was chosen specifically because it needs no campus photography at all.

**"Campus Life"** (`.sec-idx` `05`, after Programmes), a photo-wall teaser linking through to the
existing (separate) `/campus-life` page rather than duplicating it. This reuses `$gallery` — a
`GalleryPhoto` query the controller already ran but the view never rendered — and required real
photographs to actually populate it:

- Four photos from earlier in this week's hero-slider research were personally reviewed again
  (three not previously viewed directly — a Buganda formal cultural event, the university gate
  decorated for a Katikkiro visit, and re-confirming a weak Masaka governance-meeting photo that
  was *excluded* for the same "generic meeting room" reason a hero candidate was excluded earlier
  this week) before choosing the final four: Prof. Maria Musoke's council portrait, a curriculum
  review workshop, the university gate welcoming the Katikkiro of Buganda, and a formal Kiganda
  ceremony.
- Processed through the exact same pipeline the admin's own upload flow uses
  (`GalleryPhotoController::attach()` — `magick -auto-orient -strip -resize 1600x1600> -quality
  82`, a WebP copy, an 800px thumb) rather than a shortcut, so these four rows are indistinguishable
  from a real admin upload.
- Published as genuine `GalleryPhoto` rows (`is_published`, `is_featured`, real `width`/`height`
  read back from the processed files, factual captions/alt text that describe only what's visibly
  confirmable — the exact discipline already established for the hero captions: no invented
  ceremony names, no claimed identities beyond what the source material actually supports).

**A real layout bug, caught by looking at the actual result, not just the code.** The first
version of `.gallery-wall` was a CSS grid. A grid's row height is set by its *tallest* member, so
the one portrait photo (Prof. Musoke's) stretched the whole row and left the three shorter
landscape photos sharing it stranded above a dead gap — visible immediately in a screenshot,
invisible in the CSS. Fixed by switching to a true masonry layout via CSS multi-column
(`columns: 3 220px` + `break-inside: avoid`), where each tile's height is exactly its own content,
not a shared row's. Re-screenshotted to confirm: tight, fully packed, no gap. Reduced from an
initial `columns:4` to `columns:3` for the same reason — four columns left a visibly empty fourth
column with only four photos in the set; three columns fill completely today and still have room
to grow as more photos are added later through the admin panel.

### Smaller polish

Faculties wrapped in a defensive `@if($faculties->isNotEmpty())`, matching every other conditional
section, instead of being the one section that would render an empty grid if faculty data were
ever missing. News timestamps switched from a raw date to `diffForHumans()` ("3 months ago");
events kept their exact date/time (still needed to plan around) and gained the same relative
phrase alongside it as a secondary cue. Faculties' hover polish, planned as new CSS, turned out to
already exist site-wide (`.card:hover` already lifts, already shifts its icon gold-on-navy) — found
by reading the CSS before writing more of it, so nothing was duplicated.

### Verification

Two new admin form checkboxes were rendered directly (with a real authenticated user and a shared
empty `$errors` bag, since neither is present outside a real HTTP request) rather than assumed
correct from reading the Blade source — both confirmed present in the rendered HTML. Menu contract
10/10, slider contract 14/14 (neither section touches header or slider code, but both re-run
anyway). Full suite **1134 passed, 1 skipped** — which, running against SQLite, also confirmed the
two new migrations are valid there too, not only against the MySQL dev database they were written
and manually verified against. 45-route sweep clean. Every new section screenshotted at desktop
and mobile widths, with genuine scroll-reveal settle time (900ms was sometimes too short and
caught elements mid-fade — bumped to 2.5s for these checks specifically) so contrast and layout
were judged on the settled state, not a transitional one.

## 2026-09-04 — Phase R: the opening band gets an actual interface, not four more links

Feedback on Phase Q: the intro band (eyebrow, stats, quick actions) still read as static — a list
of facts, not something with anything to interact with. This wasn't a request to polish what was
there; it was a request to reconsider it.

### "I am a…" — a named pattern, not an invented one

`docs/03-RESEARCH-TRENDS-BEST-PRACTICES.md` already named the fix, months before this feedback
arrived: *"Audience personalisation, lightweight: University of Arizona's 'I am a…' dropdown is
the cheap, effective pattern."* The four quick-action links were always written for one audience
(a prospective student) and shown to everyone. They're now one of four tabbed panels — Prospective
Student, Current Student, Parent/Guardian, International — each with its own four links to real,
different destinations, not the same four relabelled:

- **Prospective** (unchanged): View Programmes, How to Apply, Fees, Intake Dates — plus the
  intake-deadline strip, which moved from a standalone element into this panel specifically, since
  a parent checking accommodation costs doesn't need an admissions-deadline nudge in their way.
- **Current Student**: E-Portal, e-Learning, Library, Academic Calendar.
- **Parent/Guardian**: Fees, Accommodation, Scholarships, Contact Us.
- **International**: International Admissions, Entry Requirements, How to Apply, Contact Us.

Every route was verified with `Route::has()` before being written into the view — `courses.index`,
`library`, `almanac`, `accommodation`, `admissions.scholarships`, `admissions.requirements` all
confirmed resolvable first, rather than guessed from the URL shape.

Built as a standard ARIA tabs pattern (`role="tablist"`/`"tab"`/`"tabpanel"`, roving `tabindex`,
arrow keys move focus and selection together) in the same vanilla JS the hero slider and mega menu
already use — no Livewire, no Alpine, matching the homepage's existing all-server-rendered
architecture. `initAudiencePicker()` follows `initHeroSlider()`'s own conventions exactly: a
`dataset.wired` guard against double-binding, called both on first load and inside the
`livewire:navigated` handler.

### A real bug caught by an off-screen test click, not a real bug in the site

First interaction-test pass showed 5 of 17 checks failing — clicking a tab appeared to do nothing
at all. Before concluding the feature was broken, checked where the click was actually landing:
the tab's `getBoundingClientRect()` put it at `y≈1473` against a `1200`px-tall test viewport — the
synthetic click was dispatched *below the visible viewport*, hitting nothing. Not a site bug; a
test bug, from a viewport sized for the old, shorter section. Re-run at a tall-enough viewport:
14/17 passed.

The remaining 3 "failures" were a second test bug, not a second site bug: they asserted
`getComputedStyle(panel).opacity === '1'` as a proxy for "is this panel visible," polled 300ms
after each click. The panel's own entrance animation runs 450ms. Polled the actual opacity value
over time instead of trusting a boolean — `0.39` at 100ms, `0.92` at 300ms, `1` by 400ms+ — which
is the animation working exactly as designed, just not finished yet at the moment a hasty assertion
checked it. Confirmed the real, meaningful checks (the `hidden` attribute and `aria-selected`
toggling correctly, keyboard `ArrowRight` wrapping from the last tab to the first) all passed
cleanly once the viewport was fixed — 17/17 on the corrected assertions.

### Two of four stats became real links

Faculties and Programmes now link to `faculties.index` and `programmes.index` — the two stats with
an obvious next page. Campuses and NCHE stayed plain text rather than forcing a link onto a
destination that doesn't actually exist yet (no dedicated accreditation page, and the two-campuses
section three sections down isn't worth a same-page jump-link competing with the tab panels right
below it). A small negative-margin hover treatment gives the two real links a bigger, more forgiving
hit area without shifting their neighbours.

### Verified

The count-up animation (Phase Q) still had to work on stats now wrapped in `<a>` rather than bare
`<div>` — checked by polling the actual digits every 80ms after a scroll-into-view, not just
trusting the DOM structure change was harmless: `0 → 1 → 2 → 3 → 4 → 5` and `0+ → 12+ → 21+ → 29+
→ … → 46+` both counted genuinely from zero, `NCHE` stayed static throughout exactly as designed.
Menu contract 10/10, slider contract 14/14, full suite **1134 passed, 1 skipped**, 45-route sweep
clean. Screenshotted at both desktop and mobile widths — the four tab labels don't fit one row at
phone width without either wrapping the pill in half or shrinking type past comfort, so the tab
strip scrolls horizontally there, the same resolution a native segmented control uses when it runs
out of room.

A real bug was hit and fixed mid-implementation, unrelated to the audience picker itself: the
Current Student panel's E-Portal link used `$eportalUrl`, a variable that exists in
`layouts/marketing.blade.php`'s own top-of-file scope but is never shared with a child view's
`@extends` content — `home.blade.php` only ever defined `$applyUrl`. A 500 on first load
(`Undefined variable $eportalUrl`) was caught immediately by the same "does the page even return
200" smoke check this project runs before trusting anything else about a change, fixed by defining
the same variable locally from `University::links()['eportal']`, the actual underlying source
`marketing.blade.php` itself reads.

## 2026-09-04 — Phase S: About MRU stops being two paragraphs next to a crest

Feedback singled out one section for full focus: "01 About MRU" — the heritage paragraphs, the
h2, and, until now, nothing else. Everything either side of it (the intro band, the audience
picker) had already been rebuilt into something with texture; this section was still the plainest
thing on the page.

### Checking what already existed before adding anything

Before designing new content, checked whether `university.identity` had more to offer than the two
history paragraphs already in use. It does: `vision`, `mission`, `values` (six entries, each with a
name and description), `namesake`, and `accreditation` all live in the same Settings blob. The
first instinct — that this was unused data free for the taking — was wrong and was checked before
acting on it: `grep -rln` across `resources/views/` showed `values` and the rest already rendered
in full on `/about` and `/who-we-are`. Building the same full values grid on the homepage would
have been a duplicate, not an addition. The homepage treatment was designed instead as a
deliberately lighter teaser — collapsed by default, one line of description revealed on demand —
and it reuses the *exact* `$valueIcons` array already defined in `who-we-are.blade.php`
(`fa-award`, `fa-scale-balanced`, `fa-drum`, `fa-hands-holding-circle`, `fa-lightbulb`,
`fa-hand-holding-heart`) rather than inventing a second icon mapping for the same six values.

### What was added

- **A credential row** between the heritage paragraphs and the "Discover MRU" button: two pills
  surfacing `$identity['namesake']` (crown icon) and `$identity['accreditation']` (certificate
  icon) — real seeded facts ("Named for Kabaka Muteesa I of Buganda (1856–1884)", "Accredited by
  the Uganda National Council for Higher Education (NCHE)"), not invented copy.
- **A values teaser** spanning the full section width below the two-column split: six
  `.value-tile` elements, each a button (icon, name, chevron) that expands its own description on
  hover, click, or keyboard focus. Tiles are independent, not a single-open accordion — opening one
  doesn't close another, matching how little content each holds (one sentence) and avoiding a
  false sense of exclusivity between six things the university holds equally.
- `initValueTiles()` in `marketing.blade.php`, following the same shape as every other homepage
  script this session: `dataset.wired` guard, click toggles `is-open` and `aria-expanded`, wired at
  both initial load and inside `livewire:navigated`. The CSS does the actual expand/collapse
  (`max-height` transition, triggered by `:hover`, `:focus-within`, or `.is-open`); the JS only
  makes the open state persist after the pointer or focus leaves, which `:hover`/`:focus-within`
  alone can't do.

### Two test bugs, not two site bugs — found by checking, not by re-running

The first interaction-test pass showed 3 of 18 checks failing: a second click failing to close a
tile, its `aria-expanded` not resetting, and Enter appearing not to toggle a focused tile at all.
Same discipline as Phase R's audience-picker false failures — checked what was actually happening
before concluding anything was broken.

The first two failures shared one cause. `window.scrollY` was logged before and after opening a
tile: `1886` → `1936`. Opening the tile grows the page, and Chrome's scroll anchoring shifted the
viewport by 50px to compensate — a real, standard browser behaviour, not a bug. The test's second
click reused a `getBoundingClientRect()` captured *before* that shift, so it fired at stale
viewport coordinates. `document.elementFromPoint()` at that exact stale point confirmed what it
now landed on: `<p class="value-desc">`, not the button — a paragraph with no click handler,
clicked twice for no visible effect. Fixed by re-fetching the trigger's rect immediately before
every click instead of caching it once; re-run clean.

The third failure was a different mistake, in the key event itself. `Input.dispatchKeyEvent` with
`type: 'rawKeyDown'` and virtual key codes but no `text`/`unmodifiedText` field never reached the
button's native activation handling. Attached a temporary capture-phase listener for `keydown`,
`keyup`, and `click` to see what the browser actually did with each variant tried. With `text:
'\r'` added, the log showed exactly the sequence the HTML button-activation spec describes:
`keydown:Enter` → `click:value-trigger` → `keyup:Enter`, and the tile opened. A second check
confirmed Space activates on `keyup` instead of `keydown`, also per spec, also fully working. Both
keyboard paths were already correct; the first test just wasn't constructing a complete enough
synthetic event to prove it. Corrected test: **18/18.**

### Verified

Value-tile contract 18/18 (six tiles present and independently collapsible, real non-empty
description text, click toggles both the CSS hook class and `aria-expanded`, two tiles can be open
simultaneously, keyboard focus reaches every trigger and both Enter and Space activate it, the
credential row shows both real facts). Menu contract 10/10, slider contract 14/14, full suite
**1134 passed, 1 skipped** — unchanged from baseline. Swept every registered GET route this time
(84, not a fixed 45) rather than a hand-picked subset: 82 returned 200 or the expected 301/302
(admin routes correctly redirect unauthenticated requests to login), one 204 is Sanctum's
`csrf-cookie` route working exactly as designed (a bare grep for `200|301|302` simply didn't list
204 as expected, not a site problem), and the one genuine 500 — `_debugbar/open` — is Laravel
Debugbar's own internal AJAX open-handler, which requires an `id` query parameter to do anything
and is never linked from any real page; unrelated to anything touched this session, confirmed by
route name (`debugbar.openhandler`, from the `barryvdh/laravel-debugbar` package) rather than
assumed. Screenshotted at mobile width (390px): the two-column split collapses to one column, both
credential pills wrap to full width without clipping, and an opened value tile renders its full
description with the chevron rotated and no horizontal overflow on the document.

## 2026-09-04 — Phase T: the values teaser moves in beside the crest

Feedback: the full-width "What We Stand For" block Phase S added below the two-column split
should come out, and move to "the side." Read literally rather than as a request
to just delete it — "remove… move it to" describes a relocation, not a deletion — and confirmed
before touching anything that this really was ambiguous enough to be worth a direct check: the
"side" could reasonably have meant the right-hand column of the split, or the `/about` page behind
the "Discover MRU" button (the section's actual "details" destination), and those two readings
would have produced different work. The user's own reply was "make best decision" — taken together
with "remove… move it to," the right-hand column reading is the one that actually matches "move,"
since the full grid already lives on `/about` and `/who-we-are` (dropping it from the homepage
entirely would be a delete, not a move).

### What changed

The six value tiles moved from a full-width block below the split into the right-hand column
itself, stacked under the crest: crest, a divider, the "What We Stand For" label, then the six
tiles in a single column. `.value-tiles` was `grid-template-columns:repeat(auto-fit,minmax(190px,
1fr))` — built for a full-width row, and two columns worth of 190px tiles doesn't sit comfortably
in a ~1fr column that's now sharing the row with a 1.4fr text column. Rather than add a modifier
class for what is now the component's only call site, simplified `.value-tiles` directly to a
single-column stack (`grid-template-columns:1fr`) and trimmed `.values-teaser`'s top spacing
(`--s-7`/`--s-6` down to `--s-6`/`--s-5`) to sit naturally under a crest instead of under a
full-width row. No new classes were introduced for a component now used in exactly one place.

An unplanned but welcome side effect: the two columns now carry comparable visual weight. Before,
the right column was just a small centered crest floating above a lot of empty space next to a
noticeably taller text column. Now it holds crest-plus-six-tiles, and the credential-row pills on
the left echo the value-tile pills on the right — a visual rhyme neither side was deliberately
designed to share, but that emerged from putting them in the same column width.

### Verified

Re-ran the value-tile contract from Phase S unchanged (coordinates are computed fresh each click,
so the new position needed no test edits): **18/18.** Menu contract 10/10, slider contract 14/14.
The audience-picker contract — a different section entirely, above this one — was re-run as a
sanity check that nothing upstream shifted: 14/17, the exact same three failures already diagnosed
in Phase R as an impatient opacity-timing assertion, not a regression. Full suite **1134 passed, 1
skipped**, unchanged. Screenshotted at both 1440px and 390px: no horizontal overflow at either
width, the crest-and-values column reads as one coherent block at desktop width, and at mobile
width everything still stacks in the same top-to-bottom order the section already had — text,
credentials, CTA, crest, values — since the values now live inside the same right-column `<div>`
that was already collapsing to a single column below 820px.
