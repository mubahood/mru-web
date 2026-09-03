# MRU Design System

The visual language of the Muteesa I Royal University website. One idea underpins it:
**heritage in the typography, modernity in the geometry.** A serif display face carries the
institution's age; a geometric sans carries its systems.

Everything lives in one file — [`public/css/mru.css`](../public/css/mru.css) — loaded once and
cached across every page. It has two layers:

1. **Base** — the inherited component sheet (page classes: `.card`, `.proj-card`, `.buy-box`,
   `.tl`, `.gal-grid`, and so on).
2. **`MRU visual system — 2026 refresh`** — the current design language, which deliberately
   comes last and settles anything the base still asserts.

When changing a component, edit it in the **refresh layer**. `StylesheetScopeTest` asserts a
component is defined only once *there*, which is what stops the "last edit silently wins" bug
that this sheet has had before.

---

## 1. Colour

| Token | Value | Use |
|---|---|---|
| `--pri` | `#023479` | University navy. Headings, primary buttons, links, footer. |
| `--pri-d` / `--pri-dd` | `#012456` / `#01193C` | Pressed states, deep bands, footer floor. |
| `--pri-l` | `#0A4FA8` | Links on white where navy is too heavy. |
| `--pri-soft` / `--pri-soft-2` | `#EDF3FC` / `#DBE7F8` | Tinted fills, active nav, icon plates. |
| `--gold` | `#F5A623` | Accent **surface**: buttons, rules, marks on navy. |
| `--gold-d` | `#9A6205` | Accent **text on white** (5.4:1). |
| `--gold-l` | `#FFC768` | Accent text on navy. |
| `--gold-soft` | `#FEF5E6` | Accent tint. |
| `--bg` / `--surface` / `--surface-2` | `#FFFFFF` / `#F6F8FB` / `#EEF2F8` | Ground, alternating band, deeper band. |
| `--tx` / `--tx2` / `--tx3` | `#333333` / `#5C6470` / `#6E7683` | Body, secondary, meta. |
| `--line` / `--line-2` | `#E5EAF1` / `#CFD8E4` | Hairlines, input borders. |

**The accent has two forms on purpose.** `#F5A623` on white is about 2:1 and fails WCAG for
body copy, so amber *text* always uses `--gold-d`. Amber as a *surface* (a filled button, a
rule, a dot) is fine, and gold buttons carry `#3D2500` ink for ~7:1.

Every foreground/background pair in the system meets WCAG 2.2 AA:
navy on white 12.6:1 · body ink on white 12.6:1 · `--tx2` 6.4:1 · `--tx3` 4.6:1.

## 2. Typography

Two self-hosted **variable** fonts, Latin subset, 128 KB total for the whole 300–800 range —
no third-party request, and no weight is ever synthesised.

| Role | Family | Notes |
|---|---|---|
| Display (`h1`, `h2`, `.display`, stat figures, prices) | **Fraunces** (`--font-display`) | Old-style serif, `opsz` 120 for large sizes. Heritage. |
| UI and body (everything else, incl. `h3`/`h4`) | **Plus Jakarta Sans** (`--font`) | Geometric humanist, 300–800. Modern. |

Scale: body `15.5px / 1.7`; `.lead` `17.5px / 1.75`; `h1` `clamp(34px, 4.6vw, 56px)`;
`h2` `clamp(25px, 3vw, 38px)`; `h3` 18px/700. Headings sit at `-0.018em` tracking.

`.eyebrow` and `.sec-idx` are the small amber labels above a title — uppercase, `.15em`
tracking, `--gold-d`. `.sec-idx` draws a short amber rule before the label.

## 3. Geometry

Rounded, consistently: `--r-xs 6` · `--r-sm 10` · `--r 14` · `--r-lg 20` · `--r-xl 28` ·
`--r-pill 999`.

- **Pill** — buttons, tags, chips, filter inputs, social icons.
- **`--r-lg`** — cards, feature boxes, mega panel, campus cards, gallery tiles.
- **`--r`** — text inputs, textareas, icon plates.

Depth comes from four navy-tinted shadows (`--sh-1` … `--sh-4`), never from grey mush. Cards
rest at `--sh-1` and lift to `--sh-3` with a 4px translate on hover.

## 4. Rhythm and space

Spacing scale `--s-1 8` … `--s-8 112`. Sections are `--s-7` (88px) tall by default, dropping
to `--s-6` under 760px. The shell is `--wrap: 1400px` with 32px gutters. Bands alternate
white and `--surface`; whitespace separates sections rather than rules.

## 5. Buttons

`.btn` sizes to its own label — nothing else. It did not always: every button that named its
destination on hover ("Apply Now" → "Apply on the E-Portal") stacked both labels in one CSS grid
cell so the transition had somewhere to slide from, which meant the button was sized by whichever
label was *wider*, seen or not. Measured before the fix, "Apply Now" rendered at 341px because a
hidden "Apply on the E-Portal" was sizing it; a genuinely long, fully visible label like "WhatsApp
Admissions" sat at only 258px. That mechanism is gone. A button now carries exactly the words on
it — an icon and a label, nothing hidden — and its width follows from that alone.
`CallToActionConsistencyTest` asserts no button markup or stylesheet rule may reintroduce a
second, hidden label.

## 6. Chrome

**Header** is `position: sticky` (not fixed), on a 1400px shell, in two rows:

- `.topbar` — navy utility row, 36px tall: E-Portal, e-Learning, Library, MRU Scholar, phone,
  Staff Login. Below 900px, everything but E-Portal (`.tb-desktop-only`) disappears — a phone
  visitor gets the one link worth a permanent line of a small screen, not six.
- `.bar` — crest + wordmark, six-section navigation, one standing **Apply Now** action.

Past 40px of scroll, JS adds `.is-scrolled` to `<html>`: the utility row folds away, the bar
tightens 66 → 56px, the crest shrinks (40 → 34px), and a shadow appears. A 40-down/10-up hysteresis
gap stops the bar flickering. The class goes on `<html>` so one hook reaches every part.

The bar's resting height (`--hd`) is a single variable read everywhere the header's footprint
matters — `main`'s top padding, sticky rail/buy-box offsets, the mobile menu's inset, anchor
`scroll-margin-top` — so tightening it (76→66px, the crest was the tallest thing in the row, not
the two-line wordmark) moved correctly everywhere in one edit. The one place it *doesn't* reach
automatically is `--hdr-total`, the hero copy's own clearance for the floating header on a hero
page: it's a second, independently-set variable that happens to describe the same header, not a
calculation derived from `--hd`, so a change to one has to be walked into the other by hand (a 10px
cut here became a 10px cut there — 104→94px desktop, 88→78px mobile) or the hero text keeps
clearance for a header that no longer needs it.

### The mega menu, and the bug it is built to avoid

The panel is **full-width and flush against the header's bottom edge**. Getting there matters,
because the obvious construction is broken:

> A panel anchored to the header while its `.nav-item` stays `position: static` makes that
> item's hover-bridge resolve against the header too — so every item's bridge spans the whole
> header width. Six full-width bridges stack, a pointer anywhere under the bar is "inside"
> several menu items at once, panels open over each other, and moving toward one leaves the
> item that opened it.

The fix removes the gap rather than bridging it:

1. every `.nav-item` stretches the **full height of the bar**, so its bottom edge *is* the
   header's bottom edge;
2. `.mega` is anchored to the header at `top: 100%` — that same edge, so trigger and panel touch;
3. the panel is a DOM child of the item, so hovering the panel keeps the item hovered.

Inside, `.mega-inner` is a `290px + 1fr` grid: a section intro (label, blurb, "Go to …") beside
a three-column link grid, dropping to two columns at 1180px and stacking at 1000px.

### Hover *and* click, without them fighting

The menu answers to four people at once: a mouse user who expects hover, someone who expects a
click to toggle, a touch user who has no hover at all, and a keyboard user. **CSS alone cannot
do that** — a click cannot dismiss a `:hover` state, and clicking the trigger focuses it, so
`:focus-within` pinned the panel open and the second click appeared to do nothing. That is why
the menu felt click-only once it had been clicked.

So the open state is a class the script owns:

- `html.js-nav` is added when the script runs; the stylesheet's `:hover` / `:focus-within` rules
  are scoped to `html:not(.js-nav)` and remain the **no-JS fallback**.
- `.nav-item.is-open > .mega` is what actually opens the panel.
- Hover (only under `(hover: hover) and (pointer: fine)`) opens after a 70ms intent delay —
  or **instantly** if a panel is already open, so moving along the bar feels like one menu — and
  closes after a 180ms grace period.
- A click always toggles and always beats a pending hover timer. A `pointerdown` flag stops the
  focus the click itself causes from re-opening what the click just closed; keyboard focus is
  told apart by `:focus-visible`.
- Escape closes and returns focus to the trigger; a click outside closes; following a link
  closes (nothing else would, since `wire:navigate` swaps the page without a reload).
- An open panel dims the page with a `pointer-events: none` scrim on `html.menu-open`.

`SiteNavTest` pins both halves of that contract: the class-driven rule must exist, the CSS-only
rule must stay scoped, and no unscoped `.nav-item:hover > .mega` may reappear.

> **A cascade trap worth remembering.** A media query adds no specificity, so a plain
> `.nav { display: flex }` written *later* than `@media(max-width:900px){ .nav{display:none} }`
> wins at every width — which is how restyling the navigation put the desktop menu back on top
> of the phone header. §17 keeps the small-screen collapse last in the sheet for that reason.

**Footer** is a navy gradient with a crest watermark, in three zones: a five-column link grid
(brand + three menu sections + "More") → a slim strip carrying the newsletter and the portals →
the accreditation bar. It was roughly twice this height before: a section-sized newsletter block
with its own heading, two large campus cards repeating an address given directly above them, and
a "quick links" column duplicating its neighbours. `SiteNavTest` asserts every menu section
stays reachable from it, and that a section given a column lists all of it.

### The home slider

A full-height (`100svh`, not `100vh` — a phone's `vh` is the tallest the viewport ever gets, so a
`vh` hero sits partly under the browser's own toolbar on load) stage of real photographs, shown
**vivid** — full colour, minimal wash — under a frosted-glass header that floats on top of it
until the reader scrolls.

**Content is data, not markup.** `university.partials.hero-slider` renders from the
`university.hero_slides` setting — each slide names an image base path, an eyebrow, a title, body
text, and up to two calls to action. `UniversityContentSeeder` seeds the six currently in use;
the legacy importer writes to `university.hero_slides_legacy` instead, specifically so re-running
it can never silently overwrite the curated slider with raw import rows.

**Six slides, chosen to show a range, not padded to hit a number.** The set grew from three to six
by surveying the whole imported media library for photographs that actually add something —
a visiting scholar in mid-conversation, student guild elections on real campus grounds, a packed
lecture hall — each viewed at full resolution before being chosen, not judged by filename. Two
strong candidates were deliberately left out and documented rather than quietly dropped: a second
striking woman-in-academia portrait was vertical (a crop risk against this slider's landscape
frames) and thematically close enough to the scholar photo that using both would have been
redundant; a curriculum-workshop photo was genuine but read as another generic seated-meeting
room, the same composition already ruled out once for the old heritage photo. A real gap was also
surfaced rather than papered over: no hero-quality photograph of the Masaka campus exists anywhere
in the imported library yet. Order is deliberate — the graduation photo still opens, since it
alone pays the first-paint cost and was already the strongest image in the set.

**Which photograph earns a slide is itself a decision, not just which one happens to exist.** The
heritage slide originally pointed at a fluorescent-lit conference room — a real photograph, but one
with no visible connection to "heritage" or the Kingdom of Buganda the slide's own copy names. It
was replaced with a photograph of the 2025 Ommanyi inter-institutional games: outdoor, vivid,
carrying the Kingdom of Buganda's own branding and the MRU crest on the backdrop banner it was
actually taken in front of — the same institutional link the copy already claimed, now visible in
the frame rather than asserted over an unrelated photo. `MakeHeroImages::SOURCES` names the file;
`mru:make-hero-images --force` re-derives the responsive set from it.

**The photograph carries almost no wash.** A first pass covered the entire frame in a navy tint at
up to 95% opacity — legible, but the result read as "a dark navy-tinted photo," not photography.
The scrim is now one tight, fast-fading gradient behind the text column only (74% at the left edge,
clear by 60% of the width) and a shallow one at the floor, just enough for the control bar — the
majority of every frame, including whatever it is actually a photograph of, is left alone. Legibility
moved to where it belongs: `.hs-media` gets a deliberate `saturate(1.14) contrast(1.05)` lift so
colour reads as vivid rather than apologised-for, and the text itself carries a soft, wide
text-shadow (blur with almost no offset, so it reads as a lift off the photo, not a hard drop
shadow) to stay crisp wherever it happens to sit.

**Motion has direction, not just duration — and it has to run on `.is-active`, not `transition`.**
`.hs-media` carries a slow zoom-and-pan (`scale(1.09)→scale(1)` over 9s, odd slides settling in
from the right and even ones from the left, `:nth-child` driven, ±1.4% translate) so a run of six
slides doesn't repeat one mechanical zoom six times. It first shipped as a `transition`, verified
by sampling a slide *change* mid-flight — which is exactly the one case that couldn't have caught
the bug that was actually there: the first slide ships `class="hs-slide is-active"` already in the
server-rendered HTML, so a `transition` (which only fires on a discrete before→after state change)
never had a "before" to interpolate from on that slide's first paint, and it sat completely still
until a reader clicked next. Fixed by moving the zoom to a named `@keyframes` animation
(`hs-zoom-odd` / `hs-zoom-even`) applied via `.hs-slide:nth-child(odd/even).is-active .hs-media` —
an `animation` runs from its own `from` keyframe the instant it's first applied, on-load class or
not, which is also why `.hs-rise` (below) never had this problem on the very same slide. The
vertical gold spine beside the text column unfurls top-to-bottom the instant a slide activates, and
the eyebrow's own gold marker draws itself left-to-right a beat later, so the frame reads as being
assembled rather than simply appearing. All of it (the pan, the spine, the rule) is named
explicitly inside `prefers-reduced-motion:reduce`, not just the original zoom — an alternating
`:nth-child` rule is *more* specific than the blanket override it's meant to be covered by, so the
override has to name it directly or reduced-motion silently stops working for exactly the rule
added on top of it. Re-verified after the fix by sampling the first slide's computed `transform` on
a completely fresh load with zero interaction — a progressing matrix at 300ms, 2.3s and 5.3s, not
the frozen identity matrix the transition-based version showed at every one of those points.

**The floating header is genuine frosted glass, not a bare transparent bar** — and specifically a
*dark*-tinted glass (`rgba(1,15,38,.58)` + `blur(22px) saturate(160%)`), not a light one. A light
tint was the first attempt and it was wrong: white nav text over a translucent white pane has
contrast that depends entirely on what photograph is blurred behind it, and over a bright patch
(sky, a pale shirt, cream tent fabric) the two would nearly disappear into each other. A dark tint
composites the same white text against something close to navy however bright the photo
underneath is — the wash a photograph needs dialled back for vividness and the wash behind
functional chrome text are different jobs, and conflating them was the mistake.

*Verified, not assumed.* Contrast here was checked by taking a real screenshot of all three
slides, locating each nav-link's rendered position, and sampling actual composited pixels
(post-blur, post-tint, post-photograph) from the backdrop directly behind where the glyphs sit —
not the CSS values, the final pixels a reader's screen would actually show. Worst case across all
three photographs: **5.0:1** (against a WCAG AA floor of 4.5:1 for body text; typical/median
across all three sits at 10–18:1). The header reads its colours from three variables — `--hdr-fg`,
`--hdr-brand`, `--hdr-crest` — rather than carrying a second copy of every header rule for the
glass state. `body.has-hero` sets them to light values (and inverts the crest); scrolling past
40px or opening the mega menu (a white panel needs a white bar under it) restores the solid-header
values on top. The body class is derived from the DOM (`syncHeroFlag()`) rather than hand-set per
view, so it survives `wire:navigate` without every page having to remember to declare it.

**The utility topbar follows the opposite rule on a hero page.** Every other page shows the
topbar at rest and folds it away only past 40px of scroll; a hero page starts with it hidden
(`max-height:0`) and reveals it only once `html.is-scrolled` is set, so the opening view is the
photograph with just the crest and six-section menu over it, not a row of portal links competing
with it for the first glance. `.hs-copy`'s own header-clearance padding (`var(--hdr-total)`) is
sized to match — 104px on desktop, 88px under 900px, where the topbar is already hidden outright
regardless of hero status — rather than reserving space for a row that isn't there yet.

**First paint pays for one image, not four.** Only the active-on-load slide's `<img>` is
`loading="eager" fetchpriority="high" decoding="sync"`; the rest are `lazy`. Every slide ships a
real `srcset` (700/1100/1600w). The three responsive tiers are generated by
`php artisan mru:make-hero-images` (Intervention Image, the same library `AvatarService` already
uses) — not a manual one-off, so a fresh clone with the source photographs in place can reproduce
the slider's assets with one command; `--force` regenerates.

**Interaction** — autoplay every 6.5s (the active dot's fill animation *is* the visible countdown,
not a separate progress bar), arrows and dots, swipe, arrow-key navigation, and pause on
hover/focus/hidden-tab. The pause is a flag the tick itself checks, not only a cleared timer: a
stray second `play()` call (another `mouseenter`, a visibility change landing mid-transition)
must not start a fresh interval that outlives the pause and carries the slider on under a reader
who stopped it deliberately. Only the active slide is exposed to assistive tech — the rest carry
`aria-hidden="true" inert`, which pulls them out of the tab order as well as the accessibility
tree, not one or the other.

`HeroSliderTest` pins the server-rendered half of that contract (one active slide, first-paint
image budget, the responsive set, degrading cleanly with zero or one slides configured); the
interaction half is exercised with real pointer/keyboard/touch events over CDP, not by reading
the DOM and assuming the handlers work.

**Autoplay runs regardless of `prefers-reduced-motion` — only the decorative motion around it
doesn't.** It briefly didn't: `play()` used to bail out entirely for a reduced-motion visitor, so
the slide could only change if they clicked, swiped, or used arrow keys — six slides exist
specifically to show a range of who is at this university, and a reduced-motion visitor would only
ever have seen the first one. WCAG 2.2.2 only requires auto-moving content be pausable, which this
already is (hover, focus), not that it never move for that preference. The dot's animated
countdown fill, the media zoom/pan, and the spine/rule entrance still all correctly stop under
`prefers-reduced-motion:reduce` — only the underlying `setInterval` that advances the slide no
longer checks it. Caught because a bug report ("the first slide won't leave until clicked") and an
earlier verification of a *different* claim ("the first slide's zoom plays correctly") sound like
the same thing and aren't — confirming one doesn't confirm the other, and this is what it looks
like when that gap actually matters.

**The hero borrows the site's own signature, instead of ignoring it.** Every interior section on
the site opens with `.sec-idx` — a small gold pill-rule marker plus a tracked uppercase two-digit
label — but the homepage's own hero, the first thing anyone sees, never used it: it read like a
generic template slider bolted onto a site that has a real visual language everywhere else. The
eyebrow now carries a matching gold `::before` rule and a per-slide index rendered in Blade
(`str_pad($i + 1, 2, '0', STR_PAD_LEFT)`, the same zero-padding convention `$idx()` uses on
interior pages) — "01 Muteesa I Royal University", "02 Rooted in heritage", and so on — so the
numeral is a real, testable string in the response rather than a value only CSS knows about. A
slim vertical gold spine (a gradient rule, faded at both ends rather than running edge to edge)
anchors the whole text column the same way, turning the site's one horizontal accent into a
vertical one for a taller block of copy.

**The dot and arrow controls used to float on the photo as two unrelated widgets; now they're one
designed object.** Each cluster sits inside its own dark-glass chip — the same
`rgba(1,15,38,~.46)` + blur recipe already verified safe for the header — so the control row reads
as something someone actually designed, not leftover template chrome. Verified over CDP and real
pixel sampling: the arrow icons measure 8.6:1+ against their chip in the worst case; the inactive
dot bars, which only measured ~2.8:1 against their new chip background (under WCAG's 3:1 floor for
non-text UI components) before their opacity was raised, now measure 5.1:1+.

**The secondary "ghost" button had the header's original mistake, just not caught yet.** It kept
its first light-glass treatment — `rgba(255,255,255,.10)` — through the header's own light→dark
correction, because nobody had measured *this* element specifically. Real pixel sampling behind
its actual rendered position on all three slides found worst-case contrast of **2.07:1**
(international, sitting over a pale blouse) and **3.2:1** (graduation) — both clear WCAG AA
failures for text this size, with only the heritage slide scraping past at 4.64:1. Same fix as the
header, same reason: dark glass composites the same white label against something close to navy
regardless of what the photo underneath is doing. Re-measured after the fix: worst case **5.98:1**
across all three real slides.

## 7. Rules worth keeping

- **A static cache with no invalidation is not a cache, it is a bug waiting for a second
  caller.** `App\Support\University` once kept its own `private static array $cache`, layered on
  top of `Settings`' own (correctly invalidated) cache. The first call in any process — an artisan
  command, a queue worker, the test suite — pinned it there for the rest of that process; a write
  afterwards was invisible to it. Harmless for a single web request, real everywhere a PHP process
  outlives one request. Removed; read through to `Settings::get()` every time instead, which is
  already cheap.
- **A button is the size of the words on it.** No second, hidden label sizing it from underneath
  — see §5.
  one out of its own padding.
- **Anchors clear the sticky bar** — `[id] { scroll-margin-top: calc(var(--hd) + 24px) }`.
- **Motion is a courtesy.** `prefers-reduced-motion` removes transforms and transitions.
- **Tap targets** are ≥44px (`.btn` min-height 48px, `.burger` 46px).
- The same tokens, fonts and radii are applied to the back office
  (`public/css/td-admin.css`) and the auth screens, so moving between the public site and the
  admin never feels like moving between two products.
