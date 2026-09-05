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

## 2026-09-04 — Phase U: no more numbers, and the values teaser is gone from home

Two instructions: drop the "02 Faculties"-style numbering from the homepage, and remove "What We
Stand For" — Phase T's relocation, not just Phase S's original full-width version — from the
homepage entirely.

### Scoping the numbering removal to home, not the design system

`.sec-idx` (the small gold-line-plus-number eyebrow) is not a homepage invention — `grep -rln
"sec-idx" resources/views/` found it live on roughly 30 templates: `/about`, `/who-we-are`, every
admissions page, faculty and programme detail pages, the scholar section, vacancies, the shared
CTA-band partial, and more. The request named one homepage example ("02 Faculties") and, in the
same message, referred to "the page" — singular, matching a conversation that has been about the
homepage exclusively for the length of this log. Reworking a motif used on 30 templates neither
one of us has looked at this session would be a far bigger and riskier change than what was asked,
so the fix is scoped to `home.blade.php`'s 9 call sites only. The `.sec-idx` CSS rule itself, and
every other page that uses it, is untouched — confirmed after the edit by curling `/about` and
checking its section labels still render (`01 Our History`, `02 Vision & Mission`, `03
Leadership`, all present, all numbered, exactly as before).

Removed along with the 9 markup lines: the `$idx` closure and the `$n` counter it read from
(`home.blade.php`'s own top-of-file `@php` block), since nothing calls `$idx()` anymore. Left in
place: `$applyUrl` and `$eportalUrl`, which several sections still use. No CSS change was needed
to make the headings sit correctly without their eyebrow — `.sec-idx` carried its own
`margin-bottom`, so removing the element removes that spacing along with it rather than leaving a
gap.

### The values teaser comes back out

"What we stand for" — the six collapsible tiles Phase S added and Phase T moved beside the crest —
is now gone from the homepage completely, on a direct, unambiguous instruction (unlike Phase T's
"move it to the side," which was ambiguous enough to be worth asking about, this one had only one
reading). The full, always-expanded version already lives on `/about` and `/who-we-are`, so nothing
about the values themselves disappeared site-wide — only the homepage's third copy of them.

Confirmed the removal was total before calling it done, not just deleting the visible block:
`initValueTiles()` (definition and both call sites — initial load and `livewire:navigated`) came
out of `marketing.blade.php`, since it wired click handlers for an element that no longer exists;
and `.values-teaser`, `.values-teaser-label`, `.value-tiles`, `.value-tile`, `.value-trigger`,
`.value-name`, `.value-chevron`, `.value-desc`, and their `prefers-reduced-motion` override came
out of `mru.css` — a repeat `grep` for all of those class names across `resources/views/` and
`public/css/mru.css` afterward returned nothing, confirming no orphaned rule or dangling handler
was left behind. `.credential-row`/`.credential-item` (the namesake and accreditation pills — a
separate piece of Phase S, never in question here) stayed exactly as they were.

The right-hand column of the About MRU split is back to just the crest, as it was before Phase S
ever touched this section — sized up from the 200px Phase T had shrunk it to (to leave room for
the tiles that no longer exist) to 260px, since a lone image no longer needs to make room for
anything underneath it.

### Verified

Homepage returns 200; CSS brace count balanced (1393/1393) before screenshotting anything. Screenshotted
every section of the page — not just the ones edited — at both 1440px and 390px, since removing a
label from 9 sections touches more surface area than any single-section change this log has made
before: every heading now sits directly at the top of its `.sec-head` block with no orphaned
spacing, the About MRU columns read as balanced with the larger crest, and the shared CTA-band
partial's own "— JOIN US" eyebrow (a static label, never wired to `$idx()`, safe by construction)
rendered exactly as before. No horizontal overflow at either width. Menu contract 10/10, slider
contract 14/14, audience-picker 14/17 (same three pre-diagnosed timing-assertion failures, not a
new regression), full suite **1134 passed, 1 skipped**, unchanged. The value-tile interaction test
from Phase S/T was retired rather than kept failing-by-design — it tested a component that no
longer exists on this page.

## 2026-09-04 — Phase V: Faculties, and a heading that was quietly wrong

Asked to give full focus to "Five faculties, one Graduate School." Before redesigning anything,
checked what that heading actually claims against what's in the database — five faculties plus one
Graduate School is six academic units, and `Faculty::all()` returns exactly five, total: four
`Faculty of …` records plus the Graduate School. The heading had been overcounting by one, silently,
since before this session touched the page. Flagged it rather than quietly rewrite a factual claim
on my own judgment; told to fix it.

### The heading fixes itself now, not just this once

The count is computed, not hardcoded: `$mainFaculties = $faculties->reject(fn ($f) => $gradSchool &&
$f->is($gradSchool))`, then a small word-form lookup (`['One','Two',...]`) turns the real count into
the same spelled-out style the rest of the page already uses ("One university, two homes"). Add a
fifth faculty later and the heading becomes "Five faculties, one Graduate School" again, truthfully,
without anyone having to remember this section exists. The Graduate School itself is matched by
name (`str_contains($f->name, 'Graduate School')`) rather than a new schema flag — this template
already hardcodes plenty this specific to this one university (the campus copy a few sections down
names Kampala and Masaka directly), so a name match is consistent with the file's existing level of
specificity, not a new kind of fragility.

### Why the Graduate School gets a different shape, not a fifth box

The heading itself frames the Graduate School as different in kind ("four faculties, **one**
Graduate School"), and the data backs that up: it's postgraduate, the other four aren't. The old
section rendered all five as identical cards in one auto-flowing grid — a comment on that grid even
warned about five items stranding a lone card on its own row, which is exactly the risk four items
in an auto-fit grid reintroduces (three fitting per row, one left over) if left unconstrained. Fixed
both problems together: the four real faculties render as a deliberate 2×2
(`minmax(min(480px,100%),1fr)`, sized so exactly two fit the wrap width and one fits below it — the
`min()` matters, see below), and the Graduate School renders as its own full-width horizontal strip
underneath, not a fifth tile.

### Two fields that existed but had never been shown anywhere on the homepage

`Faculty` carries `short_name`, `departments`, and `careers` — all real, seeded, and already used
one level down (the `/faculties` index page shows department pills; the faculty `/show` page has a
whole "Where this faculty takes you" careers section), but none of it had ever reached the homepage
teaser, which showed only an icon, a name, a tagline, and a programme count. Rather than copy what
`/faculties` already does (department pills), each card now leads with two real career-outcome
chips — "Secondary School Teacher," "Software Developer," "Business Manager" — reusing the exact
`.pill` treatment the faculty show page already established for the same field, just fewer of them.
This was a deliberate choice, not the only option: departments describe the university's own
structure, careers describe the student's future, and the research this project already did
(`docs/03-RESEARCH-TRENDS-BEST-PRACTICES.md`, citing NN/g) specifically names career-outcome
visibility as something prospective students look for — a homepage teaser has more reason to lead
with the second than the first. `short_name` (FE, FBM, FSSAH, FSTEAD) is now a `.tag` badge next to
each icon, again reusing `/faculties`' own convention rather than inventing a new one. The
card's description also switched from `tagline` to `description` as the primary line (tagline as
fallback, same as before, just reversed) — `tagline` is written casually and inconsistently cased
("welcome to the FSTEAD faculty"), `description` reads as complete, properly-cased sentences; this
is a presentation choice about which existing real field to surface, not new or edited copy.

### Two real bugs, both caught before calling this done

**500 on first load.** A Blade `{{-- --}}` comment placed inside `@php … @endphp` broke the page —
that comment syntax is a Blade-template-layer construct; inside a raw PHP block it isn't recognized
at all, so PHP tried to parse the comment's own English prose as code and failed on the first
capitalized word it hit (`syntax error, unexpected identifier "Graduate"`). Fixed by using a plain
PHP `/* */` comment instead. A reminder that `@php` blocks are PHP, not Blade, all the way down.

**The Graduate School strip rendered stacked, not horizontal — twice.** First attempt gave
`.grad-school-strip` `display:flex` with no explicit `flex-direction`, assuming that was enough;
screenshotted it and found the icon sitting above the text, centered, not beside it. Root cause:
the element is `.proj-card.grad-school-strip`, and `.proj-card{flex-direction:column}` was still
active — a rule with no competing `flex-direction` declaration doesn't get overridden by a *sibling*
rule that also fails to set it. Added `flex-direction:row` to `.grad-school-strip` and re-checked —
still stacked. Second root cause, found by asking the live page which CSS rules actually matched
the element rather than guessing again: `a.card, a.proj-card{flex-direction:column}` is a
*compound* selector (element + class), specificity 0-1-1, beating a plain `.grad-school-strip`
class selector's 0-1-0 regardless of source order. Fixed by matching specificity —
`a.grad-school-strip{flex-direction:row}` — and had to apply the same fix to the `max-width:700px`
mobile override, which had the identical specificity gap. Verified with
`getComputedStyle(...).flexDirection` directly rather than trusting a screenshot a second time:
`row` at 1440px, `column` at 390px, both confirmed live before re-screenshotting.

A third, smaller issue surfaced the same way real bugs have all session: `minmax(480px,1fr)` on the
four-card grid is exactly right at desktop width and forces a 480px-wide card into a 350px mobile
viewport, because `minmax()`'s lower bound doesn't shrink below the container just because the
container is smaller — a classic, well-documented CSS Grid trap. `document.documentElement
.scrollWidth` confirmed the overflow (501 vs 390) before fixing it, and `getBoundingClientRect()`
on the card confirmed the exact forced width (480px) before trusting the fix. Wrapped the lower
bound in `min(480px,100%)`, which caps it at the container's own width — same 2-column behaviour
at desktop, no overflow at any width below it.

### Verified

Homepage 200. Every one of the five faculty show-page links resolved (200), including the Graduate
School's — checked directly with `route('faculties.show', $faculty)` for all five rather than
assumed from the URL pattern. `/faculties` (the index page) and two individual faculty show pages
still return 200, confirming the CSS added this round — all written as `.faculty-card …` /
`.grad-school-strip …` descendant selectors — never leaks into the `.tag-row`/`.pill`/`.link`
elements those other pages already use with their own, different styling. CSS brace count balanced
(1412/1412). No horizontal overflow at 1440px or 390px, confirmed both before and after each of the
two layout fixes above, not just at the end. Menu contract 10/10, slider contract 14/14,
audience-picker 14/17 (same three pre-diagnosed timing-assertion failures, not new), full suite
**1134 passed, 1 skipped**, unchanged.

## 2026-09-04 — Phase W: every faculty card gets a real photograph

The instruction: each faculty card should carry a relevant, rounded image on its right-hand side,
found "in our content" — not stock, not generated. That last constraint is the whole job. The
`Faculty` model has had a `cover_image` column since the schema was written, with a complete admin
upload path (`store('faculties', 'public')`, a form field, old-file cleanup on replace) — and no
public view has ever rendered it, and no faculty has ever had one set. So the mechanism was already
designed; what was missing was the content and the rendering.

### Finding five real photographs, by looking at them

Surveyed every image pool in the project before choosing anything: the 28 gallery records (24 turn
out to be inherited from the model app's personal portfolio — desks, thesis nights, someone else's
graduation — only 4 are MRU's, and all 4 already appear in the Campus Life section of this same
page), the six hero-slider photos (already on this page, one at a time), and the ~70 news cover
images, which is where the real material lives because each one comes with a post title that says
what it actually shows. Every candidate was opened and looked at before being accepted or
rejected — post titles suggested, eyes decided. Rejected for cause: the "Agriculture Programme NCHE
Inspection" photo (title says agriculture, image is a boardroom), the World Teachers' Day photo (an
arrival-by-car scene), the NorDev25 photo (a coffee-break mingle), two wide group photos whose
subjects shrink to specks at card size. Chosen:

- **Education** → the packed-lecture-hall photo (`DSC_9769`) — teaching, in progress, legible
  small, and deliberately *not* the slider's own classroom photo, so the page never shows the same
  image twice.
- **Business & Management** → the Strategic Plan Alignment Seminar (presenter, laptops, boardroom).
- **Social Sciences, Arts & Humanities** → the Luwalo ceremony delegation in kanzus and gomesi —
  cultural heritage carried by the attire itself, readable at any size.
- **FSTEAD** → students working across books and phones, the foreground textbook literally titled
  "Data Processing" — the closest honest match to the faculty's lead department (Computer Science
  & IT) in a pool that contains no lab or studio photography.
- **Graduate School** → rows of graduands in MRU-sashed gowns from the 13th Graduation coverage.

All five were processed with the exact ImageMagick pipeline `GalleryPhotoController::attach()`
already uses (auto-orient, strip, quality 82), centre-cropped to a uniform 4:5 (800×1000), written
into `storage/app/public/faculties/` — the same folder the admin uploader targets — and wired up by
setting each faculty's `cover_image` in the database. Nothing is hardcoded in the template: the
university can replace any photo through the existing admin form and the homepage follows. The two
riskiest centre-crops (the Luwalo group, the seminar) were re-opened after cropping to confirm
nobody's head got cut before anything was wired to them.

### Rendering, and one lesson actually learned

Cards became a media object: text column left, photo right, `border-radius:var(--r-lg)`,
stretching to card height via `object-fit:cover`. Below 640px the photo moves to the top of the
card full-width at 16:9 (`order:-1`) rather than squeezing beside phone-width text. The Graduate
School strip got the same treatment at its right end — sized as a fixed landscape thumb
(260×168) after the first attempt let the portrait crop's intrinsic height inflate the whole
strip. The `alt` on every card photo is deliberately empty: the link's own text already names the
faculty, and a described image inside it would be announced twice by a screen reader.

Phase V's specificity bug did not get to happen twice: `a.card` pins `flex-direction:column` at
element+class specificity, so the row layout was written as `a.faculty-card{flex-direction:row}`
from the start, with the same-specificity override in the mobile media query. Verified with
`getComputedStyle` the first time, not the third.

### A 2-failure test run that this round did not cause

The full suite came back **2 failed, 1132 passed** against an all-session baseline of 1134 — on
two analytics rollup tests that nothing in a Blade/CSS/image change plausibly touches. Rather than
argue plausibility, ran the decisive control: `git stash` (pristine committed tree), re-run the
failing file — **still 2 failed** — `git stash pop`. The mechanism, confirmed rather than guessed:
the app clock is UTC, the fixtures stamp visits `started_at: now()->subMinutes(30)`, and the run
happened at 00:06 UTC (03:06 EAT), so every fixture visit landed on *yesterday* and today's rollup
correctly summed to zero against assertions expecting 3 and 2. A pre-existing time-of-day flake
with a daily 00:00–00:30 UTC window — every earlier green run this session simply ran outside it.
Left unfixed in this round on purpose (test-suite surgery doesn't belong in a homepage commit),
flagged for a follow-up.

### Verified

Homepage 200, CSS braces 1420/1420. All five photos confirmed loading on the live page
(`img.complete && naturalWidth > 0` for every `.faculty-card-photo` and the strip's). No
horizontal overflow at 1440px or 390px. Menu 10/10, slider 14/14, audience-picker 14/17 (the same
pre-diagnosed three). `/faculties` and two faculty show pages still 200 — the new CSS is all
`.faculty-card`/`.grad-school-*` scoped and `cover_image` is still rendered by no other public
view, so nothing else changed appearance. Screenshotted desktop and mobile, including the
mobile strip with its photo-on-top collapse.

## 2026-09-04 — Phase X: the audience picker learns hover, and stops moving the page

Three asks for "I am a…": switch on hover, organise it properly on phones, and make it stable and
elegant. Each turned into one specific engineering decision.

### Hover, with intent

Hovering a tab now switches to it — but only where hover is real (`matchMedia('(hover: hover) and
(pointer: fine)')`, so a touch screen never gets ghost-hover behaviour), and only after a 130ms
dwell. The dwell is the difference between a control and a nuisance: without it, a cursor crossing
the strip on its way to the content below riffles through all four panels. The timer arms on
`mouseenter` and cancels on `mouseleave`; a genuine pause switches, a pass-through does nothing.
Click and keyboard behave exactly as before, through the same `show()`.

### Stability: the panels stopped being display-toggled

The old mechanism (`hidden` attribute + a replay-animation class) had a flaw that hover made
worse: panels differ in height (Prospective carries the intake strip), so every switch reflowed
everything below the picker. Replaced with four panels stacked in one CSS grid cell
(`grid-area:1/1`), hidden by `visibility` + `opacity` rather than `display`, cross-fading through
a 280ms transition. The block is now permanently as tall as its tallest panel — the contract test
asserts the stack's height changes by less than a pixel across switches. `visibility:hidden` keeps
inactive panels out of the accessibility tree and tab order just as `display:none` did, so the
ARIA tabs semantics are unchanged. The `is-entering` keyframe and the remove-reflow-re-add replay
trick are deleted along with the `hidden` toggling; grep confirms zero references left.

### Phones: a 2×2 grid instead of a hidden scroll

Phase R resolved the four-labels-don't-fit problem with a horizontal scroll on the tab strip.
Honest reassessment: a scroll with the scrollbar hidden is a control that has to be discovered.
Four self-contained pills in a 2×2 grid show every choice at once, cut nothing off, and give each
a full-width touch target. The segmented-container look (shared border, joined background) makes
no sense split across two rows, so below 640px each tab carries its own border and the container
dissolves.

### Two harness artifacts dispatched on the way

The contract test was rewritten for the new mechanism — and this time it passes whole: **19/19**,
including three checks the old suite couldn't express: a hover dwell switches, a sub-dwell
pass-through does not, and the stack height is identical before and after every switch. The three
permanently-flaky opacity assertions from Phase R are gone with the mechanism that made them
flaky.

A mobile "tap does nothing" scare turned out to be the test again, twice over: raw
`dispatchTouchEvent` pairs don't run Chrome's tap recogniser (the proper API is
`Input.synthesizeTapGesture`), and — the actual killer — `scrollIntoView({block:'start'})` had
parked the tab row underneath the fixed header, so the tap landed on the header. Asked the page
(`elementsFromPoint`: `DIV.wrap | HEADER.site | BUTTON.audience-tab`) instead of assuming; with
the picker scrolled to centre and a real tap gesture, the panel switches on touch exactly as built
— the click path was never gated behind the hover media query.

### The intake strip stops talking about a deadline that passed

Same round, same component: the strip inside the Prospective panel read "Applications for the
August intake close on 31 May. Late applications are considered on merit." — stiff, and stale
twice over: it is September, and the seeded intake calendar itself says the August intake's
window is January–May while the **January intake's window is September–December**. So the
truthful, current message is that applications are ongoing right now. The note is a Settings
value (`university.admissions.deadline_note`) feeding five pages (the home strip plus the leads
on `/admissions`, `/admissions/intakes`, `/admissions/how-to-apply` and the FAQs page), so it was
reworded once at the source — "Applications for the January intake are ongoing." — and all five
pages follow; confirmed by curling `/admissions` and finding the new line, not by assuming the
cache flushed. The strip's link changed from the internal intake-dates page to the thing the
message now invites: `University::applyUrl()` (`https://eportal.mru.ac.ug/apply`), labelled
"Apply now", `rel="external"`, no `wire:navigate` since it leaves the site.

### Verified

Audience contract **19/19** (up from 14-of-17-with-three-known-flakes). Menu 10/10, slider 14/14.
Homepage 200, braces 1419/1419, no horizontal overflow at 1440px or 390px. Desktop screenshot
captures the hover switch live (cursor resting on Parent/Guardian, its panel shown); mobile
screenshots show the 2×2 grid and a successful tap switch. Full suite ran at 03:20 EAT — 00:20
UTC, inside Phase W's documented analytics flake window — and failed exactly the two documented
fixture-clock tests; the analytics file was re-run after 00:30 UTC and came back green, keeping
the real baseline intact.

## 2026-09-04 — Phase Y: the picker stops hoarding height, and About gets a photograph

Feedback came with a screenshot: with Parent/Guardian selected, a wall of blank space sat between
the four cards and the next section. That was Phase X's own trade-off showing — the grid-stacked
panels sized the block to the *tallest* panel permanently, which bought zero layout shift at the
price of dead space under every shorter panel. And a second instruction: give "A royal university
with a modern mission" the full-focus treatment.

### The stage now hugs the active panel — measured, not stretched

The fix keeps both halves of the bargain: no dead space *and* no jump. `fitStage()` pins the
stack's height to the active panel's own height and CSS transitions it (280ms), so switching
settles instead of snapping; re-measured on debounced resize because wrapping changes every
panel's height. The first attempt didn't work and the reason is worth writing down: grid items
**stretch to the row by default**, so every panel measured 244px — the tallest panel's height —
and the "short" panels weren't short at all, just stretched with empty insides
(`offsetHeight` said 244 for all four; the intake strip alone is 49px, so that was clearly
wrong). `align-items:start` on the stack made each panel own its true height, and the numbers
came apart properly. The contract test now asserts the opposite of what Phase X asserted — that
the stage *equals the active panel's height* after every switch, and that the short panel really
is >20px shorter than Prospective — because "no dead space" is now the promise, where "never
changes height" was before. **21/21.**

### About: a photograph of the exact thing the paragraphs claim

The right column was a crest floating in white space — accurate, but flat, and the crest already
appears in the header a hand's width above it. The paragraphs' core claim is "strong support from
the Executive Committee of the Buganda Kingdom, led by the Katikkiro" — and the news library has
a photograph of precisely that: the Buganda Partnership Symposium coverage, a speaker at the
lectern holding the Kingdom's own *Social Transformation* booklet. Rejected on the way (looked
at, not guessed): the AMATIKKIRA coronation-anniversary image (a designed social-media poster
with text overlays, not a photograph) and the Leaders' Retreat photo (a generic conference hall).

The composition is a "letterhead" figure: the photograph rounded and shadowed, the crest reduced
to an 88px seal sitting half-over the photo's bottom edge (anchored to a dedicated
`.heritage-frame` wrapper, because anchoring to the figure would have measured the caption too),
and a one-line caption in the quiet text tone. The crest survives — as a seal, which is what
crests are for — instead of being the whole exhibit. The processed image lives in
`public/images/about-heritage.jpg` as a design asset (not a `storage/` news path a future admin
clean-up could break), cropped 4:5 with the same ImageMagick pipeline as everything else this
week, subject confirmed intact after the crop. Alt text describes the scene without asserting
any individual's name; the caption says where, not who.

### Verified

Audience contract 21/21 (two new height assertions replacing Phase X's fixed-height one — the
sub-dwell hover check and everything else unchanged). Menu 10/10, slider 14/14. Homepage 200,
braces 1429/1429, no horizontal overflow at 1440px or 390px. Screenshots: desktop shows the
Parent/Guardian panel sitting tight above the About band (the complained-about gap gone), the
heritage card balanced against the text column; mobile shows the card stacking under the text at
340px with the seal and caption intact. Full suite at 03:39 EAT — outside the flake window this
time — **1134 passed, 1 skipped**: the true baseline, green, further confirming Phase W's
diagnosis by passing exactly when the clock says it should.

## 2026-09-04 — Phase Z: slide one loses the Katikkiro, the menu becomes a shelf

Two instructions: the slider's first image had to change — the Katikkiro appeared in both the
opening slide (the OMMANYI games group) and the new About photo card, twice above the fold — and
the mega menu was to be re-imagined: no per-item icons, square corners, full-bleed width, a
photograph on the far left of *some* panels.

### Slide one: same words, better picture

The heritage slide's copy ("A royal university of the Buganda Kingdom") was never the problem —
the OMMANYI photo was: a sponsor-wall sports group with small faces, and the Katikkiro seated at
its centre. Surveyed the archive properly before choosing (the coronation poster is a designed
graphic, the retreat is a generic hall, the registration and football shots are the wrong
register, the two legacy `slide_*` files duplicate themes already in rotation) and landed on the
ceremony photograph of university leaders in full academic regalia — reds, blues and purples with
the MRU crest woven into the gowns, 2560×2560-class source at the slider's own 3:2. Built the
-700/-1100/-1600 set with the same pipeline as the existing slides, pointed
`university.hero_slides[0]` at `hero-royal`, rewrote the alt to describe the new scene, and
re-ran the slider contract: 14/14, mechanics untouched — the "core concept" was never in play.

### The menu: one architecture instead of three

The stylesheet turned out to hold **three** generations of `.mega` rules — a 560px card, a 660px
card, and a later full-width rebuild — with the last one winning the cascade. The first attempt
at this round added a fourth. Caught it when the screenshot's numbers disagreed with the code
just written (panel spanning 20→1420 with rounded bottom corners, neither of which the new rules
said), traced the computed values to the real owner, deleted the redundant block, and made the
changes in the section that actually wins:

- **Truly full-bleed**: the panel's containing block is the centred `.bar`, so `left:50%;
  width:100vw; transform:translate(-50%,…)` — with the translate folded into the open/close
  animation states, which also carry `-50%` now — and an `html{overflow-x:clip}` guard for
  browsers whose scrollbars make 100vw wider than the page.
- **Square everywhere**: the shelf's bottom radius went from `0 0 20px 20px` to 0, and the link
  tiles inside followed — a full-width shelf reads as part of the header, not a floating card.
- **Icons gone** from both the desktop grid and the mobile sheet's children — each link is now a
  bold title over a quiet description with a 2px gold left-edge on hover/active. The dead `.mi`
  rules were deleted from all three generations; a grep for the class across views and CSS
  returns nothing.
- **A photograph on the far left of some panels**: SiteNav items may declare `image`; About
  (the retired OMMANYI photo — off the top of the page, still true to the section), Admissions
  (a graduand crowd from a legacy slide never used anywhere), and Student Life (the guild
  elections registration table) declare one; Academics, Research and News stay two-column. The
  media column is square-cornered, fills the panel height, and is the first thing to yield at
  1180px, disappearing entirely below 1000px.
- **Hover/active truth**: the one real flaw found — an unguarded legacy rule rotated the caret on
  bare hover, so a click that closed the panel left the caret pointing up while the pointer
  rested on the trigger. It now rotates only with `.is-open` (the no-JS fallback keeps hover
  rotation, where hover *is* the open state).

While in SiteNav, three stale facts died: the Admissions blurb still said "August intake closes
31 May" (now the ongoing-January line, matching Phase X's Settings fix), and both the Academics
blurb and the Faculties child said "five faculties" (now four, matching Phase V).

### Verified

Menu contract 10/10 — the suite tests behaviour, not geometry, and every behaviour survived the
rebuild. Slider 14/14 with the new first image; audience 21/21; homepage 200; braces 1430/1430;
no horizontal overflow at 1440px or 390px (the 100vw shelf measured 0→1440 exactly). Screenshots
reviewed: slide one in regalia under its royal title; the About shelf with photo, blurb, CTA and
icon-free columns; the Academics shelf two-column with the corrected copy; the mobile sheet
icon-free with its disclosure groups. Full suite **1134 passed, 1 skipped** — which also re-runs
the SiteNav URL smoke tests against the edited nav, so every menu destination still resolves.

## 2026-09-04 — Phase AA: three sections leave, the news floats on a photograph

Instructions: kill the underline on the faculty cards' hover and replace it with something more
elegant; remove three sections outright (Two Campuses, the programme teaser, the Campus Life
gallery); re-imagine News & Events with a fixed photograph behind floating content; and — mid-
round — "the website is too compacted", so the whole page gets more air.

### The faculty cards' hover learns to speak without underlining

The line under "9 programmes" came from the sitewide `*:hover > .link::after` reveal. It is
switched off for this section only (`content:none` scoped to the cards and the strip) and the
hover now speaks four other ways at once: a gold rule draws itself along the card's top edge
(`::after`, scaleX 0→1), the photograph breathes inside a new clipped `.faculty-card-media`
frame (a slow 1.05 zoom — the img could never be clipped by its own border-radius while
transformed, hence the wrapper), the icon flips navy-and-gold via the shared `.card` rule that
was already there, and the arrow keeps its slide. All of it under `prefers-reduced-motion`
guards. The Graduate School strip gets the same zoom and the same underline removal. The photo
swap offer was checked and declined: the remaining unviewed archive candidates (a staff seminar,
a portrait, a second kanzu-and-gomesi group nearly identical to the Luwalo shot already in use)
beat none of the five in place — verified by looking, recorded here so nobody re-litigates it
from filenames.

### Three sections deleted whole

Two Campuses, "Find the programme that fits you", and the gallery wall are gone: markup,
their CSS blocks (grep confirmed all three were homepage-only before deletion), their
controller data (`campuses`, `programmes`, `gallery` keys and the now-unused `Programme`
import — `GalleryPhoto` stays, `/campus-life` still queries it), and nothing else. The
`featured`/`show_on_home` admin switches survive; they simply steer nothing on the homepage
until some future section wants them. The page now runs hero → intro → About → Faculties →
News on photograph → Scholar → Partners → CTA: eight beats instead of eleven.

### News & Events: the page scrolls, the picture doesn't

The section sits on the university's own Buganda Institutions Games photograph (tents, pitch,
crowd — chosen over a procession shot and a busier games frame after viewing all three),
processed to 1600px/287KB and buried under a double navy scrim so it reads as texture, not
content. `background-attachment:fixed` gives the float-on-scroll effect — verified by
screenshotting the band at two scroll positions 320px apart and watching the figures in the
background hold still while the cards moved — and is explicitly downgraded to `scroll` where
`(hover:none)` or the viewport is narrow, because mobile browsers ignore or jank fixed
attachment and a broken promise is worse than a scrolling photo. News cards keep their exact
inner markup, floated on heavy shadows; the events list moved from a `feature-box` with seven
inline styles to a proper `.events-panel` with classes. One honest-data edge the first
screenshot exposed: with no upcoming events seeded, the panel was a tall blank slab stretched
to the news column's height — `.events-panel:has(.event-row)` now decides whether it stands
full-height or hugs its single line (142px against the 504px column, measured after the fix).

### Air

"Too compacted" was fixed at the token layer, not section by section: `section` padding
--s-7→--s-8 (88→112px), `.sec-head` clearance --s-5→--s-6 (48→64px), the faculty grid's gap and
its margin to the strip widened, the About split 40→56px, the news split 24→28px. Two token
changes move every band on the page together, which is the point of having tokens.

### Verified

Menu 10/10, slider 14/14, audience 21/21. Homepage 200 with all three removed headings absent
from the HTML (grep count 0); `/campus-life`, `/programmes`, `/faculties` all still 200 — the
deleted teasers' destinations live on. Hover state screenshotted (gold rule drawn, no underline
— computed `::after` content none while hovered); the fixed background proven at two scroll
offsets; mobile band screenshotted with `background-attachment: scroll` confirmed computed. No
horizontal overflow at 1440px or 390px. Braces 1449/1449, orphan grep for the deleted classes:
zero. Full suite **1134 passed, 1 skipped**, unchanged.

## 2026-09-04 — Phase AB: three new sections, chosen by inventory, not imagination

The brief asked for three creative additions "based on our backed up data, images available" —
so the round began as an audit, not a sketch. Candidates were kept only if a real model with real
rows and, where needed, real photographs stood behind them. Chosen: **Scholarships** (a
`Scholarship` model with six published schemes — verifying that also proved the nav blurb's "six
schemes" claim true), **the academic year** (fourteen 2026/27 `AlmanacEntry` rows whose year we
are actually inside), and **Leadership** (four officers with portrait files on disk — two more
rows are photo-less placeholders and are filtered out in the controller, not papered over in the
view). All three also map directly onto the NN/g findings in `docs/03` — cost and scholarship
visibility, deadline awareness, and "people pages persuade". Rejected on data grounds:
e-learning short courses (inherited LMS content, brand-odd for this page), vacancies (rarely
populated), and anything the user has previously removed (values, testimonials, gallery,
programme teasers stay gone).

### Scholarships: a bento with the Kabaka's Scholarship in the flagship cell

The one scheme no other university can offer holds a tall featured card — crown icon, a standing
gold rule, and its actual `coverage` ("50–100% tuition fee waiver") and `criteria` rendered as
labelled facts, not marketing copy. The first cut stranded the fifth compact card on a ragged
third row: a featured card spanning two rows leaves a 2×2 pocket, and five into four does not
go. The fix made the geometry honest — the featured cell spans three rows, giving a 2×3 pocket
that seats the five schemes plus a dashed "All scholarships & bursaries" closer tile in the
sixth cell, which also replaced the redundant centre button below the grid.

### The year on one line

Four landmarks — freshers' orientation (Aug), Buganda Kingdom Cultural Week (Oct), Semester I
finals (Dec), graduation (Apr) — as stops on a gold line, horizontal at desktop, a left rail on
phones. The almanac stores its dates as human text ("Week 1 — Aug 18 - Aug 24, 2026"), so the
section shows the year's shape rather than pretending to a live countdown, and the controller
picks landmarks by what they say (`orientation`, `cultural`, `final examinations` preferring
Semester I, `graduation`) with a first-four fallback if an admin rewrites the almanac out from
under the keywords.

### Leadership: four real faces

VC, Deputy VC, Academic Registrar, Dean of Students — studio portraits on white, framed 4:5 over
a surface tint so the white backgrounds don't bleed into white cards, names and titles beneath,
everything linking to `/governance`. Hover borrows the faculty cards' established language: the
gold rule draws along the top, the portrait breathes. No new interaction vocabulary invented for
a page that already has one.

### Verified

All three destination routes (`admissions.scholarships`, `governance`, `almanac`) confirmed with
`Route::has()` before being written into the view, then curled 200 after. Homepage 200 with all
three headings present; braces 1496/1496; no horizontal overflow at 1440px or 390px; desktop
screenshots reviewed for each section and the mobile timeline confirmed on its left rail. Menu
10/10, slider 14/14, audience 21/21, full suite **1134 passed, 1 skipped** — unchanged. Page
order now: hero → intro → About → Faculties → Scholarships (surface) → News on photograph →
Scholar (deep) → Year at a glance (plain) → Leadership (surface) → Partners → CTA, keeping the
light/dark alternation intact.

## 2026-09-04 — Phase AC: officers become medallions, and cards stop drawing lines

Two instructions: the leadership section looked basic, title included — circles and people were
suggested, creativity invited; and the hover treatment "on top" was to change for all such cards.

### Why circles were the right answer here, not just a nice one

Checked the four portraits before designing around them: 300×372, 300×365, 300×365, 300×333 —
small, differently cropped, and three with white baked into the background against one with a
real alpha channel. Rectangular cards had been showing exactly that inconsistency. A circular
crop is the one frame that reconciles them: it clips every portrait to the same silhouette
regardless of how each was shot, and a near-white fill behind them makes the three opaque
backgrounds vanish into the medallion. It also rhymes with the crest-as-seal in the About band,
so it reads as this page's language rather than a new one. Titles moved to a gold uppercase
micro-label under a display-font name, and the heading became **"Leadership you can put a name
to"** — which is what the section actually does, and the NN/g "people pages persuade" note made
literal. The sub-line was deliberately left general ("the officers who run the university day to
day") rather than naming the four roles, so an admin publishing a fifth officer cannot make the
copy lie.

### The hover: a ring, not a line racing along an edge

The gold rule that drew across a card's top edge was a line reacting, not the card reacting.
Replaced everywhere with a gold ring — a crisp 1.5px gold border plus a soft cream halo, drawn
with `box-shadow` so there is no pseudo-element geometry to mis-clip or mis-origin, and it works
identically on a rectangle or a circle. That last property is the point: the medallions and the
cards now speak the same hover. Applied to the homepage card family (faculty cards, the Graduate
School strip, both scholarship card types) and deliberately **not** to the sitewide `.card:hover`
— pages nobody has reviewed this round keep the hover they were designed with, the same scoping
judgement as the `.sec-idx` removal in Phase U.

### Two bugs, the second one created by fixing the first

The medallion carries a small navy seal that rises over the rim on hover. First attempt put it
inside `.lead-ring` — which clips to a circle, so the seal rendered as a sliced blob. Moving it
out to a `.lead-frame` wrapper fixed the clipping and immediately exposed what the clipping had
been hiding: at rest the seal, merely translated downward, now came to rest **on top of the name
below it**, obscuring three of the four. Screenshots caught both. The seal is now hidden by
`opacity` and scale rather than by relying on something to crop it, which is the version that
cannot break when its container changes.

A third suspected bug was measured and dismissed: the mobile screenshot showed seals visible over
names, but on a genuine touch viewport with the synthetic pointer parked away, all four report
`opacity: 0` and none matches `:hover` — my own script's stale CDP cursor had carried across the
viewport change, the same class of artifact as the stale-coordinate findings in Phases S and X.
Hover is nonetheless now gated behind `@media (hover:hover)` with `:focus-visible` kept outside
it, so a tap can never leave a medallion wearing a stuck ring, and keyboard users always get the
affordance.

### Verified

The ring was confirmed by magnified clip captures rather than by squinting at a full-page
screenshot, where a 1.5px border is genuinely too fine to read: hovered shows the gold border,
the cream halo and the navy-and-gold icon flip; resting shows a grey hairline and a pale icon.
(The first clip attempt came back blank — `Page.captureScreenshot`'s clip takes document
coordinates, not viewport ones, and the page had been scrolled.) Computed values cross-checked
throughout: `::after` content `none` on faculty cards, resting seal opacity `0,0,0,0`, and a
programmatic check that no seal's box overlaps its own name's box. Menu 10/10, slider 14/14,
audience 21/21, `/governance` 200, no horizontal overflow at 1440px or 390px, braces 1500/1500,
orphan grep for the replaced `.lead-grid`/`.lead-card`/`.lead-media` classes: zero. Full suite
**1134 passed, 1 skipped**.

## 2026-09-04 — Phase AD: the real almanac replaces fourteen placeholder rows

The Academic Registrar's consolidated Academic Almanac 2026/2027 arrived in full — the cycle
overview, twelve months of dated activity, the cross-year commitments, an abbreviation list and
the Senate confirmation register. The table held fourteen invented rows. This round replaced the
content and rebuilt both surfaces that read it.

### What the schema could not hold

Two of the source's three columns had nowhere to go. `almanac_entries` had `activity` and a
free-text `period`, but no field for **who is answerable** — a primary column in the Registrar's
document and arguably its most useful one. It also had `starts_on`/`ends_on` columns that every
existing row left **null**, so the almanac could not be sorted, filtered or asked "what is next"
without parsing English. Added `responsible`, `category` and `is_key_date`; populated the date
columns properly for all 230 dated entries. Sorting, month grouping, the homepage strip and the
category filter all now read structured data rather than guessing at prose — the homepage's
`yearGlance()` in particular dropped its keyword-matching heuristic ("find an activity containing
the word *graduation*") for a straight `is_key_date` + `upcoming()` query.

### Transcription, and two classes of source problem handled openly

252 entries: 112 in Semester I, 97 in Semester II, 21 in the recess, 22 undated standing
commitments; 14 public holidays; 17 flagged as key dates. Two problems in the source were handled
explicitly rather than papered over, and both are recorded in the seeder's own docblock:

- **Year typos inside a month section.** The April 2027 table carries "3/5/6 April 2026" for
  Easter and "8 April 2026" for a Faculty Board; January 2027 carries "6 January 2026". These are
  normalised to the year of the section they sit in — the same treatment the document's own
  "Editorial normalisations applied" table describes. But the Easter *dates* are 2026's dates (in
  2027 Good Friday falls in March), so rather than silently substitute dates I computed myself,
  those three rows are published as "date pending confirmation" and flagged to the university.
- **Undated work.** "TBC" and "Ongoing throughout the academic year" entries keep no start date
  and group under a separate "Running through the year" section, so they can never masquerade as
  belonging to a month.

The document's internal draft-control apparatus — the confirmation register and the editorial
normalisation table — is deliberately *not* seeded as calendar entries: it is Senate review
material, not the academic year. The abbreviation list is, because a reader meeting "FSTEAD" or
"CGC" needs it. A standing note on the page states that this is the consolidated draft pending
Senate approval, which is what the document says of itself.

### The two surfaces

**Homepage.** Four stops became four real dated cards — a navy day/month chip beside the period,
activity and semester — reading the next key dates from today rather than four hand-picked
landmarks. Today that is 7 Sep tests → 16 Oct timetable → 16 Nov examinations → 29 Nov end of
semester. Once the year ends it falls back to the last four rather than rendering empty.

**The almanac page** was a single inline-styled table grouped year → semester. It is now: a
milestone grid where past dates dim themselves and are marked "Completed"; a "Next key date"
panel; a category filter (eight kinds, progressive enhancement — every row ships visible, and a
month whose rows are all filtered out hides its own heading so no empty table headers are left
behind); month-by-month tables carrying date, activity, category and the office in charge, with
public holidays tinted gold; the standing cross-year commitments; and the abbreviations.

### Verified

Filter proven by measurement, not appearance: clicking *Examinations* leaves 24 visible rows
across 9 months, and every visible row's category is `examination` — 24 matching the database's
own count exactly. The first filter test appeared to fail entirely; the cause was my own script
scrolling the chip under the sticky header (`scrollIntoView({block:'start'})`), the same trap as
Phase X — `elementFromPoint` identified it, and a programmatic click proved the logic sound before
the pointer test was corrected. The seeder is idempotent (re-running holds at 252, not 504) and is
registered in `DatabaseSeeder`, so a fresh install gets the real almanac rather than nothing.
Admin CRUD extended for all three new fields. `/almanac` 200, admin 302 (auth). Menu 10/10,
slider 14/14, audience 21/21, no horizontal overflow at 1440px or 390px (the month tables scroll
inside their own container by design), braces 1543/1543, full suite **1134 passed, 1 skipped**.

## 2026-09-04 — Phase AE: a production audit, and the four real defects it found

"Make sure it is 100% ready for production" was answered with measurement rather than a read-
through: console and network capture, request-by-request weight, heading outline, image hygiene,
link integrity, a server-side query trace, throttled-network timing, and a new empty-database
render. Most of it came back clean — no console errors, no failed requests, no broken images, no
missing `alt`, no textless links, `lang`/`title`/meta-description/JSON-LD/landmarks all present,
no heading-level skips. Four things did not.

### 1. Six `<h1>` elements on one page

The hero renders a heading per slide, so a six-slide carousel produced six `h1`s — a document-
outline error for search engines and screen readers both. The first slide now carries the `h1`
and the rest are `h2`; styling hangs off `.hs-title`, so nothing moved visually. Outline verified
after: **h1 count 1**, no skips.

### 2. Every hero photograph downloaded before first paint — 862KB

`loading="lazy"` was doing nothing, and the reason is structural: all six slides are stacked
*inside* the viewport, so the browser considers every one visible and fetches it. Measured
without scrolling at all: six images, 862KB, of which only the first 228KB is needed to paint.
Slides 2..n now ship with their URLs in `data-` attributes and are hydrated later, with a
`<noscript>` copy so a scripting-free visitor still sees the photography.

The first attempt at this was wrong and throttled measurement caught it. Hydrating on
`requestIdleCallback` looked correct but idle means *CPU* idle — on a slow connection that is
true almost immediately, so the deferred images began downloading while the LCP image was still
in flight. On emulated Slow 3G, slide 2's photograph landed at 6.2s and slide 1's not until
24.6s: the fix had made the important image *later*. Rebuilt on the `load` event instead, which
fires only once first-paint resources are actually in. Re-measured on the same throttled profile:
the LCP image now arrives **first, at 18.3s** (from 24.6s), with the deferred photograph behind
it at 45.3s. A slide is also hydrated on activation, so a fast click can never reach an empty
frame.

### 3. Forty-five queries a page, thirty of them redundant

A server-side query trace found `select exists (… table_name = 'settings' …)` running **fourteen
times per request** — `Settings::all()` re-checking that its table exists on every one of the ~16
lookups a homepage render makes — plus sixteen repeated cache reads. The schema check is now
memoised in one direction only: remembered once the table is seen, but a *missing* table is
re-checked every time, so a fresh install still works the moment migrations finish. Deliberately
**not** memoised: the settings values themselves, because `App\Support\University` already carries
a comment recording that a static value cache with no invalidation pinned long-lived processes
(queue workers, the test suite) to whatever settings existed at first call. That warning was
earned; a per-request value memo would have reintroduced exactly it.

**45 queries → 17, and no query now runs more than once.**

### 4. The cache driver was not the one they configured

`.env` and the committed `.env.example` both set `CACHE_DRIVER=file` — the Laravel ≤10 name.
Laravel 12 reads `CACHE_STORE`, so the variable was inert and the cache silently ran on the
**database** store, which is why every "cached" settings read cost a SQL round trip. Corrected to
`CACHE_STORE=file` in both, `config:clear`ed, and confirmed `config('cache.default')` now reports
`file`. `SESSION_DRIVER` and `QUEUE_CONNECTION` were checked at the same time and are still the
correct names for this version.

### An empty-database guard, kept

The homepage assembles nine independent content sources, each of which an administrator can empty
by unpublishing rows. Rather than test that once by hand, `HomepageResilienceTest` now asserts it
permanently: the page renders with a completely empty database and omits every section rather
than rendering an empty one; content unpublished after the fact does not break it; a leader
without a portrait is dropped rather than shown as a broken medallion; the year strip falls back
when every key date has passed; and absent publications or events do not take down the bands that
use them. The first version of that test failed on its own imprecision, not on the site — it
asserted heading *text*, and "Four faculties" also appears in the mega-menu blurb on every page;
it now asserts each section's own markup.

### Verified

Menu 10/10, slider 14/14 (re-run because the hero markup changed), audience 21/21. Nine public
routes 200. Full suite **1139 passed, 1 skipped** — the baseline rises by the five new guards.
Recorded but not acted on, because they are deployment decisions rather than code: the server is
not gzip-compressing responses (`Accept-Encoding: gzip` returns identical bytes), and the
first-load payload is dominated by `livewire.js` (379KB) and FontAwesome (155KB), both cacheable
and both framework-level choices.

---

## Phase AH — the MUHINDO photograph archive

A folder of 127 professional photographs (`460A*.JPG` at 5760×3840, `DSC_*.JPG` at 2704×1800,
554MB in total) was supplied. Every frame was reviewed — four contact sheets to survey them, then
eight larger sheets to judge the shortlist properly, then full-resolution crops to read the text a
caption would rely on. Nothing was selected on a filename.

### What was in it

Material the site had no equivalent of: the **Signature Building** launch — the architect's
rendering on a banner, the signing, hard hats, an excavator on the ground; the **University
Football League** trophy, the squad, a stadium line-up, campus fixtures, volleyball and beach
soccer; **engineering and agriculture** — a structural-model design challenge, solar and
microcontroller project benches, a Muteesa Engineering Association vest, a research poster
session on crop nutrient deficiency, a bench-scale process rig, Ankole cattle, a shade-net house;
and the **library building**, several hundred students on the grass slope, the Guild swearing-in,
a University dinner, a shared meal, kanzu worn with the blazer.

### 28 photographs, curated rather than dumped

`gallery:import` already existed but derived each title from its filename, which for a camera dump
means `460A4341` on the public site. It now takes `--manifest=`, a JSON file naming the files to
import and giving each a slug, title, caption, alt text, category and order. The curation
therefore lives in `database/data/gallery-muhindo.json` and is reviewable in a diff. The manifest
supplies the copy on first import only; after that the record belongs to whoever edits it in the
admin gallery, and a re-run leaves their wording alone unless `--force` says otherwise. A
manifested photograph arrives published; a bulk drop keeps the column default, unchanged.

Captions state only what is visible in the frame. No dates, no names, no campus attribution —
none of that is legible in the pictures, and inventing it is the one thing this project does not
do. Where a caption leans on printed text (the Signature Building banner, the Muteesa Engineering
Association motto, the LIBRARY sign, the trophy's *University Football League Champions*), that
text was read at full resolution first.

The gallery goes from **4 published photographs to 32**, across eight categories. 121MB of
originals became 8.9MB served, averaging 286KB, each with a WebP sibling and an 800px thumbnail.

### Where they landed

| Surface | Before | Now |
|---|---|---|
| `/library` hero | the *Guild* photograph, reused | the library building, signed |
| `/students-guild` hero | students at a registration desk | the swearing-in |
| `/governance`, `/university-council`, `/staff-directory`, `/events`, `/scholar` | no photograph | one each |
| Mega menu | 3 of 6 sections had a picture | 5 — Academics and Research gained one |
| Faculty covers | a boardroom for Business, a group photo for Social Sciences, two readers for STEAD | work that matches the faculty |
| Homepage news band | people crossing a field | several hundred students on the slope |
| `/sports` | hero only | six sporting photographs, from the gallery |
| `/campus-life` strip | unordered, portraits included | ordered, portraits excluded |

The `/sports` strip is driven by the gallery's `Sport` category rather than hard-coded, so it
follows what the admin publishes and disappears cleanly when nothing is categorised that way.

### Three defects found on the way

**1. The nav label vanished on hover.** `body.has-hero .nav-link:hover{color:#fff}` was written for
the frosted header over the hero photograph. But the header turns solid white the moment the
reader scrolls *or* opens any panel, at which point every other label switches to `--hdr-fg` and
this one did not: measured `rgb(255,255,255)` on `rgba(255,255,255,.94)`, a contrast ratio of
1:1. Hovering any top-level item on the home page made its own label disappear. The hover, open
and current states now run through their own `--hdr-fg-on` token, following the same state machine
as the rest — which is what the three-variable design was for.

**2. `.gal-grid` left a hole.** It is a CSS multi-column layout, which balances by height. With six
tiles of near-identical aspect it settled on 1-1-2-2 and left an empty quarter under the first two
columns. Replacing `columns` with `auto-fit` made it worse — five across at 1440px and the sixth
alone on its own row. `.gal-strip` is a plain grid on a fixed three columns (two at 860px, one at
520px) with one aspect ratio, which cannot do either. `.gal-grid` keeps its masonry on `/gallery`,
where a mixed-ratio wall is the point.

**3. An unordered `limit(8)`.** The campus-life strip took whichever eight rows the engine reached
first — free to differ between two loads of the same page, and never honouring the order set in
the admin gallery. Now ordered, and portraits are excluded: the heading promises the campus, and a
head-and-shoulders of one person is not that.

Also corrected while in the file: the Academics menu blurb claimed "46+ programmes" against 44
published — the same drift `liveStats()` fixed on the homepage. The count is dropped rather than
retyped, because a number a human maintains by hand is a number that will be wrong again.

### Verified

`PageImageryTest` (12) asserts each page renders the photograph it names *and* that the file exists
on disk — a blade pointing at a missing asset still returns 200 and simply shows a broken frame,
which is how two of these reached a commit earlier. `GalleryManifestTest` (10) pins the manifest's
shape: required fields, unique slugs and source files, slugs that are readable words rather than
camera filenames, and alt text that describes the picture rather than repeating the title.

136 image assets across 23 pages all resolve 200. No horizontal overflow and no broken images at
390px or 1440px on any changed page. Full suite **1209 passed, 1 skipped**.

Pages carrying no photograph, counted across all 32 public pages: **24 → 10**. A first draft of
this note said 6; that number was asserted rather than measured, and measuring it gave 11 — which
is also what surfaced that `hero-research.jpg` had been built and never wired. `/scholar` now uses
it, in the shared two-column header structure rather than a second copy of it. Of the 10 that
remain, seven are data-first pages where a photograph would be decoration, and three
(`/accommodation`, `/admissions/scholarships`, `/jobs`) have nothing fitting in any archive held.

Note for deployment: `storage/app/public` is gitignored, as it always has been, so the gallery
files and faculty covers are not in the repository. They are reproduced with
`php artisan gallery:import <folder> --manifest=database/data/gallery-muhindo.json`. The chrome
images under `public/images/photos/` are tracked.

### Phase AH.1 — and onto the landing page itself

The first pass put the new photography on the inner pages and left the home page alone, which was
the wrong call: the landing is where most readers only ever get to.

**Two slides added, at positions two and three.** *Learning by building it* — students and a
lecturer testing a paper structural model — and *A classroom with the gate open*, the Ankole herd
on the farm. Both were chosen against the slider's own scrim, which is a `100deg` gradient running
from `rgba(1,12,30,.74)` to transparent by 60%: the copy sits over the darkened left, so a hero
photograph needs its subject on the **right**. Compositing the candidates under that gradient
before choosing is what ruled out the Muteesa Engineering Association vest (subject centre-left,
would have been swallowed) and chose these two. *A royal university of the Buganda Kingdom* stays
first, on request.

Sources live on the public disk and are named in `MakeHeroImages::SOURCES`, so the responsive
700/1100/1600 set is reproducible from a clone rather than made by hand. First paint is unchanged:
one slide loads eagerly and always has; the other seven hydrate after `load`, so the two additions
cost 345KB *after* the page is usable and nothing before it.

**A picture desk on the home page.** Six photographs under the faculty grid — the Signature
Building signing, the squad and the trophy, a structural model under test, a solar control bench,
the poster session, the Ankole herd — each with its category, title and caption **printed under
the frame**. Not a hover reveal: a hover caption does not exist on a phone at all, and does not
exist for anyone skimming on a desktop either, which is most readers.

The selection is the gallery's own `is_featured` flag, so an editor changes what the home page
shows from the admin gallery without touching a template. There is deliberately no fallback to
"whatever is newest": a home-page gallery is an editorial choice, and with nothing featured the
section stays away rather than filling itself.

Slider contract 14/14, menu 10/10. The slider test had been asserting a literal *five* inert
slides; with eight slides that failed on the site being **edited**, not on the site being wrong,
so the check now derives the count. Suite **1211 passed, 1 skipped**.

### Phase AH.2 — the picture desk moves to the close, and the news band opens up

**The gallery now closes the page,** below the partner strip and above the standing call to
action. Everything above it is told in words and numbers — faculties, schemes, dates, names — so it
lands as the same university with the words taken away, rather than interrupting the academic
block. On a phone it is one column with the full caption under each frame; two columns to 900px,
three above.

**The news band's photograph is now visible.** The wash was a flat `.87–.92` navy, which is a navy
band with a rumour of a photograph behind it. It is now a graded scrim: heavy at the top where the
white heading sits, opening to `.40–.46` through the middle where the cards carry their own white
background and need no help, closing to `.58` at the foot so the band meets the next section rather
than fraying into it. Only the photograph is `background-attachment:fixed`; the gradient scrolls,
so its stops track the section instead of the viewport — with both fixed, the heading's cover
drifts as the reader scrolls.

The photograph itself was re-encoded. It had been built at quality 76 and 1600px on the assumption
nothing would ever see it; it is now 1900px at quality 84.

Contrast was measured against the rendered pixels rather than judged by eye. White on the mean
behind the heading is **14.0:1**; on the single brightest pixel in that region — rgb(86,98,115), a
patch of sunlit grass — still **6.2:1**. The lead line under it was `rgba(255,255,255,.75)`, which
composites to **4.3:1** over that same pixel, under AA by a hair; at `.85` it is 5.0:1 and looks
identical. A first draft of the CSS comment claimed 8.9:1 before any of this was measured; the
figure in the file is now the measured one.

No overflow at 390px or 1440px. Slider 14/14, menu 10/10, suite 1211 passed, 1 skipped.
