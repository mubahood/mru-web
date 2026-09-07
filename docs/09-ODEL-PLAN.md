# The ODEL site — plan

A section at `/odel` with its own layout, menu and identity, covering Muteesa I Royal
University's Open, Distance and E-Learning system.

## 1. What the university actually has

Everything below was read out of the university's own records, not assumed.

**Two policies, approved by the University Council in December 2019, commenced January 2020**,
both scanned rather than typed — so nothing in them is searchable, linkable or quotable today.
Extracted by OCR for this work:

| | |
|---|---|
| Distance Learning Policy | 11 pages. Defines DL and Flexible & Blended Learning; objectives; the eight criteria a programme must meet before it can be offered in an alternative delivery format; enrolment, induction, contact hours; who is responsible (Senate, Faculties, Department & Faculty Committees); the Office of Distance Learning; ten things a DL student is entitled to; fees; quality assurance, assessment and testing, academic integrity, reporting and auditing |
| Flexible Learning Policy | 9 pages. Defines flexible learning; five things it lets a learner do; the scope of flexible provision; administrative and learning support; principles; **six flexible study modes**; quality assurance; flexible learning awards, including a *MRU Certificate of Credit* |

**Named bodies:** the Office of Distance Learning (ODL); the Centre for Flexible and Distance
Learning (CFDL), *to be established under the Flexible Learning Policy*; a DL Assessment and
Testing Unit inside the Academic Registrar's Office.

**The six modes** — the most useful thing either policy contains, and the spine of this site:
Part-Time · Distance Learning · Intensive Mode · Project-Based Study · Individual Course Unit ·
Learning through Employment.

**Live systems:** `elearning.mru.ac.ug`; 21 published courses and 425 lessons on this site's own
e-learning platform.

**Real dates in the almanac:** ODEL system training at Kakeeka on 8 September 2026 and at Kirumba
on 15 September 2026; a Weekend Graduate Centre running its own semester calendar.

### The two gaps, stated plainly

1. **No programme is marked as available by distance.** The `programmes` table has a
   `study_modes` column and it is empty for all 44. Nothing in the database says which
   qualifications an ODEL student can actually take.
2. **Both policies say "shall" as often as they say "is".** The CFDL "will be established"; the
   Academic Success Coach, the toolkits, the Curriculum Design Guide are commitments, not
   observations. The five-yearly review named in both documents fell due in 2025.

## 2. The one decision that shapes everything

The main site exists to persuade a school-leaver to apply. **ODEL's reader is not that person.**
The Flexible Learning Policy says who they are: adults and other non-traditional students
"balancing work, family, and other commitments."

So the organising question is not *why this university* but **"how would this fit my life?"** That
single change drives the structure: the six modes stop being a list buried in a policy and become
the primary navigation, because choosing among them *is* the reader's real decision.

## 3. Honesty as the design

A site that renders both policies as though everything in them were already running would be the
easiest version to build and the one most likely to mislead an applicant into paying fees.

So every structural claim carries a status, shown in the interface rather than hidden:

- **In place** — demonstrable today: the policies, the LMS, the September training dates, the courses.
- **Established by policy** — committed and Council-approved, with the clause cited, but not
  something the reader should assume is staffed and running this week.

This is the section's most trustworthy feature, not a disclaimer bolted on. Every claim links to
the clause it came from, and both PDFs are one click away throughout.

## 4. Structure

`/odel` with its own layout, header, footer and persistent section nav — a left rail on desktop,
a sticky control on mobile — so it reads as a system rather than as more marketing pages.

| Route | What it answers |
|---|---|
| `/odel` | What ODEL is, who it is for, the six modes, what happens next |
| `/odel/modes` | The six modes in full — the heart of the section |
| `/odel/modes/{mode}` | One mode in depth: who it suits, how it runs, what it demands |
| `/odel/how-it-works` | DL vs Flexible & Blended; enrolment, induction, contact hours; the LMS |
| `/odel/what-you-get` | The ten entitlements a DL student is guaranteed, by clause |
| `/odel/credit` | Credit for prior learning, the Certificate of Credit, short awards |
| `/odel/programmes` | Which qualifications are available this way — **data-driven, empty until set** |
| `/odel/quality` | Approval criteria, QA, assessment and testing, academic integrity |
| `/odel/governance` | ODL, CFDL, DL/AT, and who is answerable for what |
| `/odel/calendar` | ODEL dates, read from the almanac already in the database |
| `/odel/support` | Who to contact, orientation, the portals |
| `/odel/faqs` | With FAQPage structured data, as elsewhere on the site |

## 5. Identity

It must read as MRU, so navy stays and the crest stays. Distinctiveness comes from structure and
rhythm rather than from a different palette: a compact header instead of the mega menu, the
persistent rail, a lighter and more utilitarian surface — this is a service, not a prospectus — and
one controlled accent used only inside `/odel`, to mark the boundary without leaving the family.

## 6. Content model

Policy content becomes editable data, not hard-coded markup, so the university can maintain it:
an `OdelTopic`-style store with clause references and the in-place/committed status, seeded from
the OCR, editable in the existing admin. `study_modes` becomes editable on the programme form so
gap 1 can be closed by staff rather than by a developer.

## 7. Sequence

1. Foundation — routes, layout, nav, tokens, the hub page
2. The six modes (index and detail) — the spine
3. How it works · What you get · Credit
4. Quality · Governance
5. Programmes (data-driven) · Calendar · Support · FAQs
6. Admin editing for ODEL content and `study_modes`
7. SEO, structured data, tests, and the section's own empty-state guards

## 8. Worth telling the university

- The two policies are **scanned images**: unsearchable, unquotable, and unreadable to a screen
  reader. Publishing text versions would be worth more than most of this site.
- Both were due for review in **2025**.
- Someone must say **which programmes** are actually offered by distance. Until then `/odel/programmes`
  will honestly say so rather than invent a list.
- The Academic Success Coach, the CFDL and the toolkits are promises in a policy. If they are
  running, the site should say so and name them; if not, the page will say what it can support.
