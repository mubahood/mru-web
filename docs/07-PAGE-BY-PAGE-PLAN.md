# Page-by-page plan — mastering the whole site

**Status: in progress.** Steps 0, 1 and the SEO pass are complete; page-depth work continues.

| Measure | At audit | Now |
|---|---|---|
| Commercial pages reachable under MRU brand | 6 | **0** (gated, `features.commerce`) |
| Templates using the numbered section motif | ~30 | **0** |
| Pages with no images at all | 24 of 32 | **10 of 32** (measured, not estimated) |
| Pages with SEO issues (title/desc/canonical/OG/uniqueness) | not measured | **0 of 32** |
| Pages with structured data | 4 | **32** |
| Test suite | 1134 | **1209** |

Still open, counted against the running site rather than estimated: **10** pages carry no
photograph. Seven are legitimately data-first — `/almanac`, `/downloads`, `/admissions/fees`,
`/admissions/intakes`, `/admissions/faqs`, `/scholar/publications`, `/scholar/directory`. Three
want one and have no fitting picture in any archive: `/accommodation` (it names Kabaka Hall,
Princess Hall and the Graduate Residence and can show none of them), `/admissions/scholarships`
and `/jobs`. See §6.2 — this is now a request to the university, not a task.

Also open: `/university-council` role lines, and the deeper content work in §3.

**Original status: proposed, not started.** Written 2026-09-04, after the homepage reached production
standard (Phases Q–AE in `04-IMPLEMENTATION-LOG.md`). Every number below was measured against the
running site, not estimated.

---

## 1. Where the site actually stands

36 university page templates exist. All 32 public ones return **200**, all have **exactly one
`h1`**, and all render the shared header, footer and CTA band. The structural foundation is sound.
The gap is depth and pictures.

| Measured across the 32 public pages | Finding |
|---|---|
| Pages with **no images at all** | **24 of 32** |
| Pages under 220 words ("thin") | **13** |
| Pages still using the numbered `sec-idx` motif | **all of them** — while the homepage no longer does |
| Homepage, for comparison | 1,031 words, 29 images |

The site reads as a well-built text document. The homepage now reads as a university. Closing that
distance, page by page, is the whole job.

### The one thing that must be fixed before any launch

`/source-code`, `/projects-for-sale` and `/shop` serve a page headed **"Source code for sale"**
listing items at UGX 650,000 / 450,000 / 800,000 — the inherited portfolio platform's commercial
marketplace, live under the Muteesa I Royal University brand. `/hire`, `/start-a-project` and
`/propose` likewise resolve to that platform's account and enquiry flows.

They were publicly reachable and linked from the sign-in page and the news index.

**Correction to an earlier draft of this document:** it claimed seven of these URLs were in
`sitemap.xml`. That was wrong — a grep for `shop|hire` had matched news slugs
(`…-workshop-…`, `hosted-the-arch-bishop`) and an e-learning course called "Build a complete
online shop". The sitemap is generated from an explicit allow-list of university routes and never
contained the commercial pages. The reachability was real; the indexing claim was not.

**Resolved** (see `04-IMPLEMENTATION-LOG.md`, Phase AF): gated behind
`config('features.commerce')`, off by default, so these URLs now return 404 on the university
domain while the same code still serves them on the portfolio domain.

---

## 2. Cross-cutting decisions to settle once

These affect every page, so they are decided once and applied everywhere rather than re-argued per
page.

**a. The numbered-section motif.** The homepage had `01 / 02 / 03` section labels removed on
request; ~30 other templates still carry them. The site is currently inconsistent with itself.
Recommendation: remove sitewide, matching the homepage. It is one grep and a visual pass per page,
and it is the single change that most makes the rest of the site feel like the homepage.

**b. Photography.** 24 pages have none. The archive is real and already inventoried:
~70 news photographs, the gallery library, six processed hero images, four staff portraits, five
faculty covers. **Every photograph must be chosen by opening it and looking** — this session has
now rejected images three separate times whose filenames and post titles were actively misleading
(an "Agriculture NCHE Inspection" that is a boardroom; a "World Teachers' Day" that is an arrival
by car; a coronation "photo" that is a designed poster). No stock, no generated imagery.

**c. Page-hero variety.** Every page opens with the same `page-hero` partial: eyebrow, title,
lead. It is clean but identical 32 times. Recommendation: extend the partial to optionally accept a
background photograph, then give the ~10 highest-traffic pages one.

**d. Empty states.** `HomepageResilienceTest` proved the homepage degrades cleanly. No other page
has that guard. Each page hand-made should leave behind a matching assertion.

---

## 3. The tiers

Ordered by *(visits × distance from standard)*, not by convenience.

### Tier 1 — the admissions cluster (7 pages)

The reason most visitors come, and the weakest cluster on the site: **all seven have zero images**,
three are thin.

| Page | Now | Work |
|---|---|---|
| `/admissions` | 273 w, 0 img | The hub. Add the seven-step path as a visual spine, intake status, fee summary, a real campus photograph. |
| `/admissions/requirements` | 234 w, 0 img | Entry requirements per level as a comparison table that survives a phone; currently prose. |
| `/admissions/how-to-apply` | 256 w, 0 img | Already has a table; make it a genuine numbered walkthrough with the E-Portal CTA at each decision point. |
| `/admissions/fees` | 644 w, 0 img | Best content in the cluster. Needs a sticky faculty filter, clear per-semester framing, payment-code block. |
| `/admissions/scholarships` | 292 w, 0 img | Now has a homepage teaser pointing here; must deliver the six schemes in full, Kabaka's first. |
| `/admissions/intakes` | 204 w, 0 img | Thin. Should key off the real almanac dates now in the database rather than restating them. |
| `/admissions/international` | 186 w, 0 img | Thin. Country-agnostic requirements, visa/permit guidance, a named contact. |

### Tier 2 — academics (5 pages)

| Page | Now | Work |
|---|---|---|
| `/programmes` | 1,472 w, 0 img | The single most valuable page (NN/g: the programme finder is the #1 conversion tool). Filters exist; needs level/faculty facets that hold on mobile, and result cards that show fees + duration. |
| `/faculties` | 346 w, 0 img | The five faculty covers already exist and are used on the homepage — use them here. |
| `/faculties/{slug}` | — | Per-faculty: cover photo, dean, departments, careers, its programmes. Data is all present. |
| `/programmes/{slug}` | — | Must answer, on one page: requirements, duration, fees in UGX/semester, intakes, careers, contact, two CTAs. |
| `/almanac` | 4,257 w | **Done** (Phase AD). The benchmark for a data-driven page. |

### Tier 3 — about & governance (6 pages)

| Page | Now | Work |
|---|---|---|
| `/about` | 394 w, 5 img | Closest to standard. Tighten, add the heritage photography. |
| `/who-we-are` | 349 w, **0 img** | Vision, mission, six values — currently pure text. |
| `/governance` | 382 w, 18 img | Portraits already present; needs structure (officers → committees → council). |
| `/university-council` | 178 w, 18 img | Thin text under many portraits; each member needs a role line. |
| `/staff-directory` | 316 w, 4 img | Needs faculty filter + search; the data supports it. |
| `/contact` | **117 w, 0 img** | Thinnest page on the site. Needs both campuses, a map, departmental contacts, hours. |

### Tier 4 — student life (5 pages)

`/campus-life` (214 w, 4 img — should be the most photographic page on the site),
`/accommodation` (183 w, 0 img — halls need photographs and prices),
`/sports` (241 w, 0 img — the football archive is rich and currently unused),
`/students-guild` (268 w, 0 img), `/alumni` (237 w, 0 img).

### Tier 5 — news, events & research (7 pages)

`/news` (436 w, 9 img — healthiest of the group), `/events` (**87 w**), `/gallery` (**82 w**, 5 img),
`/jobs` (**75 w**), `/scholar` + `/scholar/publications` + `/scholar/directory`.
The three thinnest pages on the entire site are here; all are index pages whose emptiness is a
*content* problem as much as a design one, and each needs a designed empty state.

---

## 4. Method — how each page gets hand-made

The loop that produced the homepage, applied per page:

1. **Read the data first.** Query the models behind the page before designing. Every fabricated
   assumption this session was caught this way.
2. **Inventory the photographs** available for that page, and open every candidate.
3. **Draft, then screenshot at 1440px and 390px** and look at the result.
4. **Measure the claims** — computed styles, element positions, query counts — never trust a
   screenshot alone for anything fine (a 1.5px border is invisible when a 2880px shot is scaled).
5. **Leave a test behind**: at minimum an empty-state assertion.
6. **Log it** in `04-IMPLEMENTATION-LOG.md`, then commit that page alone.

**Definition of done, per page:** 200; one `h1`, no heading skips; at least one real photograph;
no horizontal overflow at 1440/390; no console errors; every link resolves; a designed empty state;
an empty-state test; screenshots reviewed at both widths.

---

## 5. Suggested sequence

| Step | Work | Why first |
|---|---|---|
| 0 | Remove/redirect the commercial pages; purge them from `sitemap.xml` | Brand risk, actively indexed |
| 1 | Strip `sec-idx` sitewide; extend `page-hero` to take a photograph | One change, felt on all 32 pages |
| 2 | Tier 1 — admissions (7) | Highest intent |
| 3 | Tier 2 — programmes & faculties (4 remaining) | Highest conversion value |
| 4 | Tier 3 — about & governance (6) | Institutional credibility |
| 5 | Tier 4 — student life (5) | Where the photography pays off most |
| 6 | Tier 6 — news, events, research (7) | Needs content decisions alongside design |

Steps 0 and 1 are quick and change how the entire site feels. Steps 2–6 are roughly one page per
working pass, at the standard the homepage now sets.

---

## 6. Open questions for the university

1. **The commercial pages** — delete outright, or keep them alive on a separate non-MRU domain?
2. **Photography** — the MUHINDO archive (127 frames, Phase AH) answered this for the library,
   sports, engineering, agriculture, governance and student life. Still missing, and still needed:
   **the halls of residence** — `/accommodation` names Kabaka Hall, Princess Hall and the Graduate
   Residence and can show none of them — and the campus buildings as buildings, for `/who-we-are`.
3. **Fees** — are the published figures current for 2026/2027, and cleared for publication?
4. **The almanac** is published as the Registrar's *draft pending Senate approval*, per the document
   itself. Confirm that is acceptable publicly, or gate it until approved.
5. **Contact** — per-department emails and phone numbers, and office hours.
