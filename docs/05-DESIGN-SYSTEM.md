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
to `--s-6` under 760px. The shell is `--wrap: 1240px` with 32px gutters. Bands alternate
white and `--surface`; whitespace separates sections rather than rules.

## 5. Chrome

**Header** is `position: sticky` (not fixed), in two rows:

- `.topbar` — navy utility row: E-Portal, e-Learning, Library, MRU Scholar, phone, Staff Login.
  Hidden below 900px.
- `.bar` — crest + wordmark, six-section navigation, one standing **Apply Now** action.

Past 40px of scroll, JS adds `.is-scrolled` to `<html>`: the utility row folds away, the bar
tightens 76 → 64px, the crest shrinks, and a shadow appears. A 40-down/10-up hysteresis gap
stops the bar flickering. The class goes on `<html>` so one hook reaches every part.

The mega panel is anchored to the bar, not to its trigger, so a panel opened by the last menu
item cannot hang off the window edge. It opens on `:hover` **and** `:focus-within`, so it works
with a mouse, a keyboard, and with JavaScript off.

**Footer** is a navy gradient with an oversized crest watermark, in four zones: newsletter
invitation → the two campus cards → five link columns (brand + three menu sections + quick
links) → accreditation bar. `SiteNavTest` asserts every menu section stays reachable from it.

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
