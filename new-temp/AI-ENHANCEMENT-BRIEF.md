# Hero Photograph Enhancement — Master Brief

You are being given six real photographs used as the full-bleed hero slider on the homepage of
**Muteesa I Royal University** (MRU), a chartered private university of the Buganda Kingdom in
Uganda. Your task is to **enhance the technical and visual quality of each photograph** —
resolution, sharpness, noise, exposure, colour accuracy — so that each one reads as genuinely
world-class, editorial-grade photography that belongs on a modern, elegant university website,
**without altering what the photograph actually documents.**

Read this entire brief before touching any file. Then read the per-image section for the specific
photo you are working on. Each image has its own section below with a factual description of what
is actually in the frame and photograph-specific instructions — ground your work in that
description; do not guess at content from the image alone.

## The one rule that overrides every other instruction

**These are real photographs of real, named or identifiable people at real, documented
institutional events — a Kingdom-sponsored games ceremony, a visiting scholar's interview, a
graduation, a student election, a lecture hall, a partnership visit.** They are not stock
photography and not source material for creative reinterpretation. Treat this the way a museum
photo-restoration lab treats a damaged historical print: the job is to recover and clarify what is
genuinely there, never to invent, beautify, or reimagine it.

Do **not**, under any circumstance:

- Change any person's facial structure, expression, skin tone, hairstyle, or apparent age.
  "Beautification" or "face enhancement" presets are forbidden — they change identity.
- Add, remove, or relocate any person. The headcount and arrangement in every photo must stay
  exactly as given.
- Invent, alter, or blur any text, logo, or crest that is legible in the source — this includes
  banner text, jersey numbers, T-shirt slogans, and institutional crests. If it's real text in the
  photo, the enhanced version must show the *same* text, more clearly, not different or
  approximated text.
- Outpaint, extend, or generatively fill in the canvas beyond what the source frame actually
  contains. If a tool's upscaler defaults to generative fill at the edges, disable it or crop back
  to the original frame afterward.
- Change the colour of clothing, skin, sky, or branded material beyond correcting a genuine white-
  balance or exposure fault. A jersey is the colour it is; do not "improve" it into a different
  colour.
- Apply an artificial HDR, "AI art," painterly, or plastic/over-smoothed look. The goal is a
  sharper, cleaner *photograph* — not a stylised render of one.

If you are ever unsure whether a change would alter the documentary content of the image, do not
make it. A smaller, honest improvement is always preferable to a larger one that risks
misrepresenting real people or a real event.

## What "improve" does mean

- **Resolution.** Upscale to at least **2400px on the long edge** (3000–3200px preferred where the
  tool supports it cleanly). The site currently derives 700/1100/1600px responsive tiers from
  whatever you return, so more real detail here is genuine headroom, not wasted work.
- **Noise and compression artefacts.** Several of these were shot in mixed available light or
  compressed by a messaging app before they reached us — clean up visible luminance/colour noise
  and JPEG blocking, especially in skies, walls, and skin tones, without smearing away real
  texture (fabric weave, skin pores, hair strands should stay believable, not waxy).
- **Sharpening.** Sharpen genuinely in-focus subjects (the people and objects the photographer was
  actually focusing on). Do not sharpen a deliberately out-of-focus background into fake detail —
  a soft crowd behind a sharp foreground is normal optical depth of field, not a flaw to fix.
- **Exposure and dynamic range.** Recover clipped highlights (bright sky, white fabric, window
  light) and lifted shadows where detail is genuinely recoverable, so the full tonal range reads
  cleanly. Correct any obvious colour cast from mixed indoor lighting (fluorescent green/yellow
  tints are a common fault in a few of these).
- **Mild vibrance, not saturation.** The website's own CSS already applies
  `saturate(1.14) contrast(1.05)` on top of whatever you deliver, plus a navy gradient scrim over
  roughly the left 55–60% of the frame that fades to fully clear by 60% of the width. Deliver a
  clean, accurately colour-balanced image rather than a pre-saturated one — the site will push
  vividness itself, and stacking two saturation boosts looks synthetic.
- **Minor geometry.** Straighten a slightly tilted horizon or doorway if present. Do not re-crop
  or reframe unless a specific image's section below explicitly says so.

## Technical context for calibration

- Final display: full-viewport-height (`100svh`) hero banner, `object-fit: cover`,
  `object-position: center 32%` — the crop favours a horizontal band roughly through the upper-
  middle third of the frame. Keep your most important content (faces, banner text) reasonably
  central; don't push it to the extreme top/bottom edge where a wide-viewport crop could lose it.
- Aspect ratio: keep each photograph's original aspect ratio. Do not force-crop to 16:9 or any
  other fixed ratio — the site crops responsively per-viewport from whatever you deliver, and a
  wider source gives more cropping flexibility, not less.
- Output format: high-quality JPEG (or PNG if you must, we'll re-encode). The site's own build
  pipeline (`php artisan mru:make-hero-images`) re-compresses your output down to 700/1100/1600px
  JPEG tiers for production, so deliver the cleanest, highest-quality version you can — don't
  pre-compress or pre-resize down "to be safe."
- Return each enhanced image using the **same number** as its source file (`1.jpg` in →
  `1-enhanced.jpg` or similar out) so it can be matched back to its slide without ambiguity.

---

## Image 1 — `1.jpg` — Buganda Kingdom Ommanyi Games, 2025 ("Rooted in heritage" slide)

**What's actually in the frame:** An outdoor group photo at the "OMMANYI? EMIZANNYO
GY'EBITONGOLE BY'OBWAKABAKA 2025" event (Buganda Kingdom inter-institutional games). A wide navy
banner spans the background reading that title in Luganda/English, flanked by the hexagonal
Kingdom-of-Buganda crest on the left and the Muteesa I Royal University crest on the right, with a
row of sponsor logos beneath. A row of standing people in mixed sports jerseys (light blue, red,
white-and-blue, one Manchester United-style red kit) hold gold trophies with red/white/blue
ribbons above a seated row of dignitaries, including men in dark and blue tops and women in formal
dress (one in a hijab, one in a maroon gown). Kampala's skyline, trees, and blue sky with light
cloud are visible above and around the banner; decorative kingdom-branded flags/banners frame both
edges of the shot.

**Specific instructions:**
- This is the lowest native resolution of the six (native ~1600×1066) — prioritise this one for
  upscaling; it needs the most real detail added back.
- The banner text and both crests are the institutional core of this photograph — they must be
  perfectly legible and pixel-accurate to the source after enhancement. Zoom in on the banner text
  in your own review before finishing; if it's not crisply legible, keep working on it.
- Recover any highlight clipping in the bright sky without introducing a halo around the banner's
  top edge.
- Preserve every trophy, ribbon colour, and jersey exactly — this documents who actually won.

## Image 2 — `2.jpg` — Dr Liezel Williams, visiting scholar ("Scholarship & Exchange" slide)

**What's actually in the frame:** A close portrait of a woman with dark hair pulled back, wearing
glasses, mid-gesture with one hand raised while speaking, dressed in a red/black/gold African-
print jacket over a black top, with pearl drop earrings and a silver pendant necklace. She's
seated in an office chair against a plain, softly-lit cream/off-white wall, with a blurred
blue-and-white flag visible in soft bokeh behind her shoulder.

**Specific instructions:**
- This is already the sharpest, best-lit photograph of the six — it needs the *least*
  intervention. Light polish only: fine-detail sharpening on the face and jacket pattern, minor
  noise cleanup if any is visible on close inspection, and resolution upscaling to spec.
- Do not touch her expression, facial proportions, or skin tone. Do not sharpen or "clean up" the
  background flag bokeh — the shallow depth of field is intentional and should stay soft.
- If anything, this image is your quality benchmark for how the other five should look once
  finished.

## Image 3 — `3.jpg` — Graduation ceremony ("Muteesa I Royal University" slide)

**What's actually in the frame:** An outdoor graduation ceremony under a large white marquee tent.
Rows of graduates in dark academic gowns, mortarboards, and blue/gold sashes and medallions fill
the frame; several are on their phones or holding certificates/gifts. The nearest row is sharp and
well-lit (a woman with braids and colourful beadwork on the left, several men in gowns with visible
university-crest medallions in the centre), with the crowd naturally softening into the deep
background under the tent. Trees and a fence are visible at the frame's edges in daylight.

**Specific instructions:**
- The foreground row (roughly the nearest third of the frame) is where real detail lives —
  prioritise sharpening and noise cleanup there: faces, medallions, gown texture, phone screens.
- The deep background crowd is naturally out of focus from the camera's own depth of field. Do
  **not** AI-sharpen it into fabricated facial detail — leave its natural soft falloff alone. This
  is the single biggest risk on this image: an aggressive "face restoration" pass over a soft
  background crowd is exactly the kind of hallucination this brief forbids.
- Recover any blown-out highlights in the white tent fabric without flattening its texture.
- Every medallion, sash colour, and item of academic dress must stay exactly as photographed —
  these denote real qualifications and honours.

## Image 4 — `4.jpg` — Student Guild Elections ("Student Voice" slide)

**What's actually in the frame:** An outdoor campus scene at a registration table. In the
foreground, a woman in a red-and-white striped shirt over an NFL-branded jersey (number 12) stands
reading a folded paper/ballot; a second woman in a high-visibility neon vest and cap sits at the
table, which holds water and soft-drink bottles and paperwork. Behind them, several young women in
colourful outfits (a floral maxi dress, an orange dress, a blue-and-white patterned skirt) stand
talking near a white campus building with a large, colourful abstract mural painted on its wall.
Yellow tape marks off the registration area on a sunlit green lawn.

**Specific instructions:**
- Already vivid and well-lit in natural daylight — focus on sharpening the two foreground subjects
  and resolution upscaling rather than major colour correction.
- The mural is real campus artwork — reproduce its exact colours and shapes; do not reinterpret or
  "improve" its composition.
- Preserve the jersey number, every visible logo/brand mark, and the yellow tape exactly.
- Do not alter the number of people visible in the midground/background group.

## Image 5 — `5.jpg` — Lecture hall during orientation ("In the Classroom" slide)

**What's actually in the frame:** A packed university lecture hall shot from the side/front. Red
curtains and a barred window are on the left letting in natural daylight; a black PA speaker on a
stand sits against the back wall; a man in a red shirt stands near the front with his back to
camera. Rows of wooden desk-chairs are filled with a visibly mixed group of students — several
wear headscarves, one wears a black T-shirt printed "THEY ARE DILIGENT" — in a mix of candid poses
(resting a chin on a hand, writing, listening, talking quietly).

**Specific instructions:**
- This was shot in mixed/available indoor light and is the most likely of the six to show visible
  ISO noise/grain — prioritise clean, natural-looking noise reduction here, but stop before skin
  and fabric texture starts looking waxy or plastic.
- The window side of the room is naturally brighter than the far side; balance this gently so the
  whole crowd reads clearly, without flattening the daylight-vs-interior contrast into something
  artificial.
- Every visible face in this crowd is a real student in a real orientation session — do not smooth,
  beautify, or regularise any of them. Preserve the "THEY ARE DILIGENT" T-shirt text exactly.
- Do not invent detail in faces that are genuinely too small/soft in the deep background to resolve
  clearly — leave them honestly soft rather than fabricating features.

## Image 6 — `6.jpg` — International partners visit ("International" slide)

**What's actually in the frame:** Six people standing in a line in an indoor hallway/corridor with
blue-grey walls (some construction scaffolding visible through a doorway behind them): a young man
in a dark green polo shirt at the far left; an older woman with grey hair in a cream top; a woman
with reddish-blonde hair in a white blouse and teal statement necklace; a woman with auburn hair in
a pink blazer over black; an older man in a light-blue blazer with a lanyard; and a woman in a
vivid orange/black/yellow geometric-print traditional dress at the far right.

**Specific instructions:**
- This is the softest/lowest-fidelity photograph of the six ("cell-phone group photo" quality) —
  it benefits the most from careful sharpening and noise cleanup, but is also the highest risk for
  identity-altering artefacts given how soft the source faces already are. Work conservatively:
  recover real detail, don't fabricate it.
- Correct the likely indoor colour cast (mixed fluorescent/daylight can push this greenish or
  yellowish) so skin tones read naturally and accurately, without shifting any individual's actual
  skin tone.
- Optional, bounded: if your tool supports a clean *crop-only* re-frame (no generative fill), a
  slightly tighter crop that reduces the empty wall space at the far left is welcome — but this
  must be a crop of the existing frame, never an outpainted extension, and every one of the six
  people must remain fully visible afterward.
- Preserve the traditional print dress on the right exactly — its pattern is a real, specific
  textile, not a generic "African print" to be redrawn.
