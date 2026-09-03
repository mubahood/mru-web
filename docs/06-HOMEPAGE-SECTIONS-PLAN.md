# Homepage Sections — Master Plan

**Status: implemented.** Everything in §3/§4 below shipped except Testimonials, which was
explicitly deferred (no fabricated quotes) — see `docs/04-IMPLEMENTATION-LOG.md`, Phase Q, for
what actually happened, in what order, and how it was verified. The plan below is kept as
written (not rewritten after the fact) so it still shows the reasoning the implementation was
built from.

Planning document for a deliberate pass over every homepage section below the hero slider (the
slider itself is out of scope here — it went through its own six rounds of work this week and is
current). Written before any implementation, per instruction: read this, tell me what to adjust,
then the actual section work follows as its own set of commits — the same plan-first pattern this
project has used for the hero enhancement brief.

This builds on two things already in the repo rather than starting cold:

- **`docs/03-RESEARCH-TRENDS-BEST-PRACTICES.md`** — the existing competitor/best-practice research
  (Makerere, UCU, Strathmore; NN/g, OHO, Kanopi, Vardot). Its "Notable features" table and
  "High-value roadmap" tier already name several of the moves below — this plan cites which ones
  and cross-checks them against what MRU's homepage *actually does today*, not just what the
  research recommends in the abstract.
- **A fresh, line-by-line audit of `resources/views/university/home.blade.php`, its controller,
  and every model/Settings-key it touches** — done today, specifically to ground this plan in the
  real current code rather than assumption.

## How to read this document

1. **§1 Current State** — every section that exists today, in order, with what it actually does.
2. **§2 What's Actually Wrong** — concrete bugs and gaps found in that audit, not aesthetic opinion.
3. **§3 The Proposed Lineup** — the section-by-section plan: keep, fix, add, or flag, with reasons.
4. **§4 Change Log** — one scannable table: every section, current state, action, why.
5. **§5 Content You Need To Supply** — the parts of this plan that are blocked on real content
   (photos, quotes) that I can't fabricate, spelled out explicitly as asked.
6. **§6 Technical Notes** — migrations, query fixes, new Settings keys, in enough detail to
   implement directly from this document.
7. **§7 Consistency Framework** — how nine-ish sections stay visually harmonious while each is
   still distinct, using the motifs already established this week on the hero.

---

## §1. Current State

The homepage (`PageController::home()` → `university/home.blade.php`, 301 lines) renders, in
order, after the hero slider:

| # | Section | `.sec-idx` | Band | Data source | Always renders? |
|---|---|---|---|---|---|
| — | Intro / Stats band | *(none — reads as hero continuation)* | white, flush under hero | Settings (`identity`, `stats`) + 4 hardcoded quick-links | yes |
| 1 | About MRU | `01` | surface + `tex-glow` | Settings (`identity.history`) | yes |
| 2 | Faculties | `02` | white | **Eloquent** `Faculty` | yes (even if empty) |
| 3 | Programmes | `03` | surface + `tex-grid` | **Eloquent** `Programme`, `inRandomOrder()`, 6 | only if non-empty |
| 4 | News & Events | `04` | white | **Eloquent** `Post` + `UniversityEvent` | only if either non-empty |
| 5 | MRU Scholar | `05` | deep (dark navy) | **Eloquent** `Publication` (featured) | yes (empty-state text if none) |
| 6 | Student Voices | `06`* | surface + `tex-glow` | Settings (`portfolio.testimonials`, has admin CRUD) | **only if real testimonials exist** |
| 7 | Partners | `06/07`* | white, centered heading | **Eloquent** `Partner`, **no visibility flag — every row always shows** | only if non-empty |
| — | Closing CTA ("Join Us") | *(unnumbered — shared boilerplate)* | deep | Settings (`links.apply`, `contacts.whatsapp_link`) | yes, **shared by every university page**, not homepage-only |

\* `$idx()` only increments for sections that actually render, so the numbering never has a gap —
if Testimonials is empty, Partners becomes `06` instead of `07` automatically.

Four tiers of data architecture are already in use across these sections (full detail in the
audit, condensed here since it matters for what a *new* section should do):

- **Tier 1 — Eloquent model + full admin CRUD.** Faculties, Programmes, News, Events,
  Publications, Partners. The site's dominant, most mature pattern — a real table, a real admin
  screen, individual add/edit/delete. Use this for anything that's a genuinely growing list.
- **Tier 2 — Settings JSON blob + dedicated admin CRUD controller.** Only Testimonials today
  (`Admin\TestimonialController`). Right for a short, hand-curated, rarely-changing list that
  still needs a real edit form rather than raw JSON.
- **Tier 3 — Settings JSON blob, seeder-only, no admin UI.** `identity`, `stats`, `hero_slides`,
  `admissions`, `contacts`, `links`. Nobody can change this from the admin panel today — editing
  it means touching the seeder and redeploying.
- **Tier 4 — flat Settings key, generic form.** Site-wide config (`site_name` etc.), not really
  a homepage-section concern.

---

## §2. What's Actually Wrong

Found during the audit, not invented for this plan — each of these is a concrete thing in the
code today:

1. **Programmes are shown in random order on every single page load** (`Programme::published()->inRandomOrder()->limit(6)`). A prospective student who reloads the homepage sees a different six programmes each time. For a section whose entire job is "put your best foot forward," a random lottery is the opposite of a deliberate first impression.
2. **Partners have no visibility flag at all.** Every row in the `partners` table renders on the homepage, unconditionally, forever. There's no way today to add a partner to the system without it appearing here — curation is impossible without a code change.
3. **The Scholar section has a latent N+1 query** (`authorNames()` walks an unloaded relation per publication). Harmless at `limit(3)` today, but the wrong pattern to carry forward if this section grows.
4. **A count-up-from-zero stat animation is fully built in the shared layout JS and used nowhere** (`[data-count]`, `layouts/marketing.blade.php`). The stats band (5 faculties, 46+ programmes, 2 campuses, NCHE) just fades in as static digits — an easy, already-paid-for "wow" moment currently left on the table.
5. **Two campuses, zero campus-specific content on the homepage.** Kampala and Masaka are both named in copy (`.stats`, footer) but neither has a homepage presence beyond text. The research doc flags this pattern by name (UCU's 6-location map section, Middlebury's interactive map) and it's a real gap here.
6. **`$gallery` (6 featured, published `GalleryPhoto` rows) is fetched by the controller and never rendered anywhere in the view.** Either dead code to remove, or — more interestingly — a ready-made hook for a Campus Life section that nobody finished wiring up. *(Caveat in §5: today's actual `gallery_photos` table rows are unrelated personal-portfolio content, all unpublished — this hook exists but has no real MRU photos behind it yet.)*
7. **`$heroSlides` is fetched by the controller and never used** — the hero partial independently re-fetches the identical data itself. Not a bug (Settings are cached, so it's not an extra query in practice), just dead plumbing worth removing while other things in this file are being touched.
8. **Testimonials is very likely rendering nothing right now.** The section only appears if real entries exist in `portfolio.testimonials`, and nothing found in this audit confirms any do. A "What Our Students Say" section that's actually invisible is worse than an honest gap — worth surfacing explicitly rather than assuming it's live.

---

## §3. The Proposed Lineup

Ordered as they'd appear on the page. Each entry: what it is, why it's classified the way it is,
and what changes concretely.

### 1. Intro / Stats Band — **KEEP, IMPROVE**

Stays exactly where it is (the bridge from photo to content), keeps its identity/stats Settings
source and its four quick-links. Two concrete improvements:

- **Wire up the count-up animation that already exists.** Add `data-count` to the four stat
  values; the layout JS already knows what to do with it. Genuinely free — no new JS, no new
  design, just using a capability that's sitting there unused.
- **Fold in an intake-deadline strip.** `university.admissions` already carries `deadline_note`
  and structured `intakes` data (seeded, unused on the homepage). A single slim line — "Applications for the [Month] intake close [date]" — directly matches the research doc's cited UCU pattern ("Applications for September Intake now open") and costs nothing content-wise since the data already exists; it just needs a place to show.

### 2. About MRU (`01`) — **KEEP, POLISH**

Sound as-is: real institutional copy, the `tex-glow` treatment, left-aligned numbered header. No
structural change proposed — light typography/spacing polish only, done as part of the general
consistency pass (§7), not because anything here is actually broken.

### 3. *(New)* Two Campuses (`02`) — **ADD**

**Why:** named explicitly in the existing research (`03-RESEARCH-TRENDS-BEST-PRACTICES.md` §8,
"Multi-campus map section — UCU (6 locations), Middlebury… Show Kakeeka (Mengo) + Kirumba
(Masaka) campuses"), and confirmed as a real gap by the audit — nothing on the homepage today
gives Kampala and Masaka their own presence. It also deliberately **sidesteps a photography
problem surfaced during this week's hero-slider work**: no hero-quality photograph of the Masaka
campus exists anywhere in the imported media library. A map-and-facts section needs no campus
photography at all — two embedded location cards (address, a static map embed or simple SVG pin
graphic, a one-line description, distance/travel note) rather than photos carrying the section.

**Data:** already fully seeded, confirmed by reading the seeder directly —
`university.contacts.campuses` is a two-item array with exactly `name`, `location`, and a real
Google Maps link for each: Kakeeka Campus (Mengo, Kampala) and Kirumba Campus (Kirumba, Masaka).
This section needs **zero new data work**, only a view built around what already exists — the
lowest-risk, lowest-effort addition in this whole plan.

**Visual:** two-column card layout, white band (breaks up the surface/deep alternation without
introducing a third texture), `.sec-idx` `02` (Faculties and everything after shifts down by one).

### 4. Faculties (`03`) — **KEEP, POLISH**

Structurally fine. Two small fixes: add hover micro-interaction (card lift + icon color shift, matching the kind of restrained polish the research doc calls out — "micro-interactions sparingly… nothing that delays content") and wrap it in a defensive `@if($faculties->isNotEmpty())` to match every other conditional section, rather than being the one section that would render an empty grid.

### 5. Programmes (`04`) — **KEEP, FIX**

The section stays. The **random selection is fixed**: add a `featured` boolean to the `programmes`
table (Tier 1 pattern, same shape as `Publication::is_featured` already in use one section down),
default `false`, and change the query to `Programme::published()->where('featured', true)->limit(6)`. This means an admin deliberately chooses which six programmes anchor the homepage — the institution's actual best foot forward, not a lottery — and it's re-curatable any time from the existing Programme admin screen (just needs the one new field added to that form).

### 6. *(New, conditional)* Campus Life Gallery (`05`) — **ADD, if content lands (see §5)**

**Why:** also named explicitly in the existing research roadmap ("High-value: Video campus tour +
photo galleries per campus"), and there's already a half-built hook for it (`$gallery` in the
controller, dead in the view). Reusing that dead code productively, rather than deleting it, is
the more interesting outcome of finding it.

**The real blocker:** the `gallery_photos` table's current rows are unrelated personal-portfolio
content, unpublished. This section **cannot ship with real MRU photography until someone publishes
genuine campus-life photos through the existing Gallery admin screen** — this is a content
dependency, not a code dependency, and it's called out again in §5 rather than glossed over.

**A concrete bootstrap option:** several photos already vetted this week for the hero slider —
specifically the ones *not* chosen for it (Prof. Musoke's portrait, the curriculum-review workshop
photo) — are real, high-quality, rights-available MRU photographs already sitting in
`storage/app/public/`. Publishing a handful of those through the Gallery admin would give this
section real content on day one without waiting on new photography, while a genuine campus-life
photo shoot happens on its own timeline.

**Visual:** a masonry or fixed-grid photo wall, `.sec-idx` `05`, no new texture — this section's
visual interest comes from the photography itself, not decorative CSS.

### 7. News & Events (`06`) — **KEEP, POLISH**

Structurally sound, degrades gracefully already. Improvement: relative freshness signals ("3 days
ago" / "in 5 days") rather than raw dates — a small, well-evidenced pattern (Bowdoin's "Moments," cited in the research doc, is built on exactly this kind of low-cost freshness signal) that makes the section read as more alive without needing more content.

### 8. MRU Scholar (`07`) — **KEEP, FIX**

Stays as the site's one `.band-deep` "punctuation" mid-page (the CSS's own stated philosophy is a
dark band "once or twice per page" — it's already used exactly twice, here and the closing CTA, so
this plan doesn't add a third). Fix: eager-load `authorRows.scholar` in the controller query to
close the N+1 before it matters.

### 9. Testimonials (`08` if it renders) — **FLAG, DO NOT FABRICATE**

The *mechanism* is genuinely well-built — Tier 2, real admin CRUD, photo upload, no code needed.
The *content* almost certainly doesn't exist yet, meaning this section is likely invisible on the
live site right now. This plan does not add placeholder or invented quotes attributed to
students — that would misrepresent real people, exactly the line this project already drew for
the hero photographs. See §5 for what's actually needed to make this section real.

### 10. Partners (`08/09`) — **KEEP, FIX**

Stays centered (the one intentionally-centered header on the page — a reasonable way to make a
logo strip read differently from every other left-aligned content section, keep it). Fix: add a
`show_on_home` boolean to the `partners` table, default `true` for existing rows (so nothing
currently showing disappears unannounced), and scope the homepage query to it. This is the same
shape as the Programmes fix — a curation flag where none currently exists.

### Closing CTA Band — **NO CHANGE**

Shared by every `university.*` page, not homepage-specific. Touching it is a sitewide decision, not a homepage-sections one — out of scope here unless raised separately.

---

## §4. Change Log

| Section | Current state | Action | Why |
|---|---|---|---|
| Intro / Stats | Static digits, no deadline info | **Improve** — count-up animation, intake-deadline strip | Both already have the data/JS; just unused |
| About MRU | Solid | **Polish only** | Nothing broken |
| Two Campuses | **Does not exist** | **Add** | Named gap in existing research; sidesteps the Masaka-photo shortage |
| Faculties | Solid, always renders even if empty | **Polish** — hover state, defensive `@if` | Consistency with other conditional sections |
| Programmes | Random 6 every load | **Fix** — `featured` flag, curated 6 | Removes a real "random lottery" bug |
| Campus Life Gallery | **Does not exist**; `$gallery` fetched, unused | **Add, content-gated** | Reuses dead code; real photos needed first (§5) |
| News & Events | Solid | **Polish** — relative timestamps | Low-cost freshness signal |
| MRU Scholar | Solid, latent N+1 | **Fix** — eager-load | Technical correctness before the section grows |
| Testimonials | Almost certainly empty/invisible | **Flag, no fabrication** | Needs real, consented student quotes (§5) |
| Partners | No visibility flag, all rows always show | **Fix** — `show_on_home` flag | Makes curation possible at all |
| Closing CTA | Shared sitewide | **No change** | Out of scope — sitewide, not homepage-only |

Nothing on this page is being **removed** outright — every existing section earns its place; the
work is fixing two real data bugs, polishing five sound sections, adding one well-evidenced new
section outright, and adding one more that's gated on content that doesn't exist yet.

---

## §5. Content You Need To Supply

Spelled out plainly, since code alone can't finish these:

1. **Campus Life Gallery photos.** Either (a) publish a handful of the already-vetted, unused
   hero-slider candidates (Prof. Musoke's portrait, the curriculum-review workshop photo — both
   real, high-quality, already-cleared MRU photography sitting in storage) through the existing
   Gallery admin screen as a starting point, or (b) commission/gather new campus-life photography
   specifically for this section, or (c) both — bootstrap now, replace with dedicated photography
   later. Without at least option (a), this section doesn't ship.
2. **Real student testimonials.** Name, programme, a real quote, ideally a photo, ideally
   consent on record for using it publicly. The admin form to enter these already exists
   (`admin/testimonials`) — this is purely a content-collection task, not a build task. I will not
   generate placeholder quotes attributed to invented or unconsented real students.
3. ~~Campus address/location details for the Two Campuses section~~ — **not needed.** Confirmed
   by reading the seeder directly: `university.contacts.campuses` already has name, location, and
   a real Google Maps link for both Kakeeka (Mengo, Kampala) and Kirumba (Masaka). Listed here
   only to show it was checked, not assumed.

---

## §6. Technical Notes

For when this moves from plan to implementation:

- **Migrations needed:** `programmes.featured` (boolean, default false), `partners.show_on_home`
  (boolean, default **true** for existing rows specifically, so nothing currently visible vanishes
  the moment this ships).
- **Admin form updates:** add the new boolean field to the existing Programme and Partner admin
  edit screens (both already have full CRUD — this is one field each, not new screens).
- **Controller changes:** `PageController::home()` — swap `inRandomOrder()` for the `featured`
  scope; add `->with('authorRows.scholar')` to the Publications query; remove the dead `$gallery`
  fetch if the Gallery section isn't built yet, or wire it to the view if it is; remove the
  redundant `$heroSlides` fetch (the partial already gets it independently).
- **No new Settings key needed for Two Campuses** — `university.contacts.campuses` already has
  everything the section needs; this is purely a new view reading existing data.
- **No new Livewire, no new AJAX** — every existing homepage section is server-rendered Blade with
  no client-side data fetching, and nothing in this plan needs to change that.
- **Testing:** each fixed/added section gets the same treatment the hero slider did this week —
  a PHPUnit contract test for the server-rendered content, a full-suite and route-sweep re-run
  after each change, no section shipped unverified.

---

## §7. Consistency Framework

Nine-ish sections staying visually coherent while each still reads as deliberate, not
copy-pasted:

- **The `.sec-idx` numbered motif** (gold pill-rule + tracked label + number) continues exactly as
  today — every section keeps its number, the counter keeps only incrementing for sections that
  actually render.
- **Band alternation stays deliberate, not decorative-for-its-own-sake.** `.band-deep` (dark navy)
  remains rare — used exactly twice today (Scholar, closing CTA) and this plan doesn't add a
  third, preserving its "punctuation, not wallpaper" role. `.tex-glow` and `.tex-grid` are reused
  rather than inventing a third decorative texture for new sections — the new Two Campuses section
  deliberately uses a plain white band specifically *because* it sits between two textured
  sections and benefits from being the visual rest note.
- **Left-aligned headers stay left-aligned**, Partners stays the one deliberate exception
  (centered, because a logo strip reads better centered) — not "fixed" to match the rest, since
  that inconsistency is intentional and already reasoned about in the existing CSS.
- **Every new/changed section gets the same rigor already established this week for the hero**:
  real contrast verification wherever text sits over an image or color, CDP-driven interaction
  testing wherever there's a hover/click behavior, a documented reason for every visual choice,
  and a route-sweep plus full test-suite run before anything is called done.
