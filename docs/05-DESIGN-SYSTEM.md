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

## 5. Chrome

**Header** is `position: sticky` (not fixed), on a 1400px shell, in two rows:

- `.topbar` — navy utility row: E-Portal, e-Learning, Library, MRU Scholar, phone, Staff Login.
  Hidden below 900px.
- `.bar` — crest + wordmark, six-section navigation, one standing **Apply Now** action.

Past 40px of scroll, JS adds `.is-scrolled` to `<html>`: the utility row folds away, the bar
tightens 76 → 64px, the crest shrinks, and a shadow appears. A 40-down/10-up hysteresis gap
stops the bar flickering. The class goes on `<html>` so one hook reaches every part.

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

## 6. Rules worth keeping

- **Touch has no hover.** The two-label buttons (`.cta`) collapse to a single label under
  `(hover: none)`; otherwise the hidden longer label sizes the button and pushes the visible
  one out of its own padding.
- **Anchors clear the sticky bar** — `[id] { scroll-margin-top: calc(var(--hd) + 24px) }`.
- **Motion is a courtesy.** `prefers-reduced-motion` removes transforms and transitions.
- **Tap targets** are ≥44px (`.btn` min-height 48px, `.burger` 46px).
- The same tokens, fonts and radii are applied to the back office
  (`public/css/td-admin.css`) and the auth screens, so moving between the public site and the
  admin never feels like moving between two products.
