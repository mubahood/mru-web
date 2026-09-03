# MRU Website Rebuild — Master Blueprint

**Project:** Rebuild of the Muteesa I Royal University website (mru.ac.ug)
**Foundation:** Full copy of the model Laravel 12 application (muhindo-app) with its database cloned to `mru_new_web`
**Location:** `/Applications/MAMP/htdocs/mru-new-web` · Local URL `http://localhost:8888/mru-new-web/`
**Date:** 2026-09-03

Companion documents:
- [01-MODEL-SITE-ANALYSIS.md](01-MODEL-SITE-ANALYSIS.md) — full feature inventory of the model app + reuse ratings
- [02-OLD-SITE-ANALYSIS.md](02-OLD-SITE-ANALYSIS.md) — full analysis of the legacy backup (custom PHP + WordPress + DBs) and the content inventory to migrate
- [03-RESEARCH-TRENDS-BEST-PRACTICES.md](03-RESEARCH-TRENDS-BEST-PRACTICES.md) — 2025–26 higher-ed web research with prioritized recommendations
- [04-IMPLEMENTATION-LOG.md](04-IMPLEMENTATION-LOG.md) — running log of what was built, challenges, and decisions
- [05-DESIGN-SYSTEM.md](05-DESIGN-SYSTEM.md) — the visual language: palette, type, geometry, chrome

---

## 1. Executive summary

The old mru.ac.ug is three overlapping systems in one web root (a custom PHP CMS, the live WordPress it falls back to, and a staging WordPress), carrying an unauthenticated admin backdoor, plaintext production credentials, active malware droppers, and mangled data. It cannot be incrementally fixed; its **content** is valuable, its **code** is not.

The model application is a production-grade Laravel 12 monolith (Blade + Livewire + Alpine) with auth/RBAC, a CMS back office, a full LMS (quizzes, assignments, gradebook, certificates with QR verification), payments (Flutterwave: mobile money/card/bank/USSD), first-party analytics, notifications, and 158 tests. Its navy-and-gold design system is already institutional in character.

**Strategy: keep the model's engine, replace its identity and content.** Rebrand the shells, replace the personal-portfolio IA with a university IA, add university-specific modules (faculties, programmes, staff, events, MRU Scholar, vacancies), migrate the legacy content (programmes, fees, people, news, documents), and keep the LMS as MRU e-Learning for short courses. Maximum code reuse; minimum new invention.

---

## 2. What we reuse from the model (prioritized)

### P0 — Reused as-is (the engine)
| Module | University use |
|---|---|
| Auth + Spatie RBAC + capability flags | Same roles; later add `lecturer` for Scholar submissions |
| FormShield honeypot + reCAPTCHA gate | Every public form (contact, inquiries, waitlist) |
| Settings key/value store (cached) | University identity, contacts, admissions copy |
| First-party analytics suite | Programme-page traffic, admissions micro-conversions |
| Invoices/payments/coupons + Flutterwave gateway | Short-course fees, (later) application fees |
| LMS end-to-end (courses→modules→lessons, player, progress, quizzes, assignments, gradebook, discussions, announcements, reviews, waitlist, badges) | **MRU e-Learning** (short/professional courses) |
| Certificates + public `/verify` | Short-course certificates; employer verification |
| Posts (blog) engine | **University News** |
| Gallery (WebP pipeline + lightbox) | Campus gallery |
| Notifications, dashboard shell, Today planner | Staff back office |
| API v1 (Sanctum) + OpenAPI | Future mobile-app integration |
| Admin design system (`td-admin.css`) | All new admin CRUD screens |
| Sitemap/robots controller, SEO component, WhatsApp launcher, analytics beacon | Adapted content |

### P1 — Adapted (rename/repoint)
| Model module | Becomes |
|---|---|
| SiteNav (single source for header/mega/mobile/footer) | University IA (see §4) |
| Marketing layout + design tokens | MRU brand shell (see §6) |
| Testimonials (settings JSON) | Student/alumni testimonials |
| Contact messages inbox | Reinstated public contact form → inbox |
| Waitlist (`CourseNotifyRequest`) | "Register interest" pattern (courses now; intakes later) |
| Newsletter subscribers | Footer newsletter signup (70 legacy subscribers imported) |
| Portfolio projects (case-study schema) | Hidden from public nav; retained for future research-projects use |
| Project inquiries + portal | Retained (hidden) — future consultancy/admissions-inquiry machinery |

### P2 — Parked (kept in code, removed from public surface)
Shop/source-code store, cart/checkout (still used by course payments), hire/propose flow, CV/skills/experience pages. Routes stay functional where harmless but leave the navigation; personal content pages are replaced by university pages.

---

## 3. What we retain from the old site (prioritized)

### P0 — Core university content (migrated into new structured tables)
- **Identity:** name, motto ("Seeking Greater Horizon In Thoughts and Actions"), vision, mission, 6 core values, history (Kabaka Muteesa I, Buganda Kingdom), logo.
- **Faculties (5)** with taglines, about/vision/mission, departments, deans.
- **Programmes (46)** + cleaned **programme fees (128 rows — re-parsed)**; application fee UGX 50,000; MTN/Airtel paycodes.
- **Admissions content:** requirements (bachelors/diploma/international), 7-step procedure, timeline, intakes, FAQs.
- **People:** staff directory (96), University Council (18, with portraits), committees (3 + 34 members), senior officers (VC/DVC/AR/Dean of Students), guild cabinet (8).
- **News:** 249 WordPress posts (spam-filtered) + 2 CMS posts; **Events** (12) + almanac (14 rows).
- **MRU Scholar:** publications (6 CMS + richer WP schema), scholar profiles, research areas, 21 publication PDFs.
- **Scholarships (6, incl. Kabaka's Scholarship)**, accommodation (3 halls), sports (6), partners (4: GENSS/NCHE/NEMRA/RENU), job openings (5).
- **Documents:** ~112 PDFs — prospectus, strategic plan 2025-2030, handbook, ~30 policies, journals, forms.
- **Contacts/socials/WhatsApp** + eportal/eadmin links.

### P1 — Retained as pages/settings
Orientation cluster, campus life, students' guild, library, ICT centre, international students, alumni network, careers services.

### NOT retained
All legacy **code** (router, includes, admin, WP theme/plugins), the WP install, malware artifacts, spam posts, placeholder staff rows' fake emails (imported but flagged), personal files (CVs), `autologin.php` pattern, backup/restore-from-upload feature.

---

## 4. New information architecture

Per research (5–6 topic items + utility bar + sticky Apply):

**Utility bar:** E-Portal · E-Learning · Library · MRU Scholar · Staff Login — plus WhatsApp launcher and gold **Apply Now** button (→ eportal.mru.ac.ug/apply).

**Main nav (6):**
1. **About** — About MRU · Who We Are (mission/vision/values) · Governance (Council, Committees, VC/DVC/AR/Dean offices) · Staff Directory · Campuses · Policies · Contact
2. **Admissions** — Overview · Requirements · How to Apply · Fees Structure · Scholarships & Bursaries · Intakes & Deadlines · International Students · FAQs
3. **Academics** — Faculties & Schools · All Programmes (finder) · Academic Almanac · E-Learning · Library · ICT Centre
4. **Research** — MRU Scholar (publications, scholars directory) · Journals & publications documents
5. **Student Life** — Campus Life · Accommodation · Sports · Students' Guild · Orientation · Alumni
6. **News & Events** — News · Events · Gallery · Vacancies

**Footer:** brand + motto + campuses/P.O. Box; nav columns (auto from SiteNav); Resources (Prospectus, Strategic Plan, Policies, Almanac, Handbook); Quick links (E-Portal, Apply, E-Learning, Verify Certificate, Vacancies, Tenders); contacts + socials + newsletter; NCHE accreditation line; Privacy/Terms (real pages).

---

## 5. New university modules (data model)

New migrations (all additive — model tables untouched):

1. **`faculties`** — name, slug, short_name, tagline, description, about, vision, mission, color, icon, departments(json), careers(json), dean_staff_id→staff_members, cover_image, sort_order, is_published.
2. **`programmes`** — faculty_id, name, slug, award_code (BBA…), level enum(certificate|diploma|bachelor|pgd|masters), duration_years, study_modes(json: day/evening/weekend), tuition_per_semester, tuition_currency, tuition_note, entry_requirements, description, career_prospects, intake_months(json), is_published, sort_order. → Filterable finder + Course schema.org.
3. **`staff_members`** — name, title, staff_role enum(leadership|dean|lecturer|administrative|council|guild|committee), group_label (e.g. "University Council"), faculty_id, department, email, phone, bio, education, photo, sort_order, is_published. (Council/guild/committees = groups of the same table via role+group_label; committees keep their membership in `group_label`.)
4. **`university_events`** — title, slug, description, venue, campus, category, starts_at, ends_at, image, faculty_id, is_published. → Event schema + ICS download.
5. **`almanac_entries`** — academic_year, semester, week/period label, starts_on, ends_on, activity, sort.
6. **`scholarships`** — name, coverage, criteria, amount_note, category, sort, is_published.
7. **`vacancies`** — title, reference_no, department, type, location, deadline_on, summary, requirements, attachment, is_published.
8. **`partners`** — name, logo, url, sort.
9. **MRU Scholar** (modeled on the richer WP-plugin schema, simplified):
   - `scholars` — user_id (nullable), staff_member_id (nullable), name, slug, title, faculty_id, department, bio, research_interests, photo, cv_path, email, google_scholar_url, orcid, is_published.
   - `publications` — title, slug, abstract, type enum(journal|conference|book|book_chapter|thesis|report), journal_name, publisher, volume, issue, pages, publication_date, doi, url, pdf_path, keywords, citations, views, downloads, status enum(draft|pending|published|featured), submitted_by, approved_by, approved_at, is_featured.
   - `publication_author` pivot (publication_id, scholar_id, author_order, external_name for non-MRU co-authors).
   - `research_areas` + `publication_research_area` pivot.
   - Workflow now: admin-managed CRUD with status; later: lecturer self-submission using existing auth + a `lecturer` role (machinery exists).
10. **Settings JSON blobs (new keys):** `university.identity` (name, motto, vision, mission, values), `university.contacts` (phones, emails, whatsapp, pobox, campuses), `university.social`, `university.admissions` (requirements lists, steps, timeline, deadlines, app fee, paycodes), `university.accommodation`, `university.stats`, `university.homepage` (hero, why-choose-us). Rendered by pages; editable via extended admin settings screen.

**Admin:** standard resource controllers + Blade CRUD on `td-admin.css` for faculties, programmes, staff, events, scholarships, vacancies, partners, scholars, publications, research areas + extended settings editor. New "University" group in admin nav.

---

## 6. Branding decisions

- **Palette:** standardize on the mobile-app design system — navy **#05275C**, gold **#D4A843** (old web used #002366/#D29D2B; the app palette wins for cross-platform consistency; both golds fail WCAG on white for body text → gold reserved for large text/accents/on-navy, per research).
- **Logo:** legacy `MRU-LOGO.png` migrated from backup into `public/images/`; badge mark "MRU".
- **Fonts:** keep self-hosted Inter (performance; Poppins is the app font but Inter is already subset + self-hosted; revisit if brand requires).
- **Tone:** "Rooted in Heritage. Focused on the Future." — heritage (royal navy/gold, Buganda story) + modern layout. Design language of the model (sharp geometry, ruled-paper textures, offset plates, indexed sections) is retained — it reads institutional.
- Header CTAs: ghost **E-Portal** + gold **Apply Now**. WhatsApp launcher retained with MRU number (+256 752 033 889).

---

## 7. Data migration plan

Source-of-truth per content type:
| Content | Source | Method |
|---|---|---|
| Faculties, programmes, staff, council, guild, committees, events, almanac, scholarships, sports, partners, jobs, settings copy | `mru_mru2.sql` (imported into scratch DB `mru_legacy`) | PHP artisan importer commands (idempotent) |
| Programme fees | `programme_fees` (128 rows) | **Re-parser** that splits the mangled scraped names and reconstructs the true amount (prefix digit + amount), verified row-by-row |
| News (249 posts) | `mru_wp435.sql` → `wp8a_posts` (+ featured images via `wp8a_postmeta`) | Import into scratch DB `mru_legacy_wp`; transform to `posts`; spam-filter (casino keyword list); strip Elementor shortcodes; download/copy images from backup uploads |
| Scholar publications + PDFs | CMS scholar tables + `wp-content/uploads/mru-scholar/` | Import + copy PDFs to private/public storage |
| Documents (112 PDFs) | `wp-content/uploads/**` | Copy curated set to `public/documents/` with a documents index page (junk excluded) |
| Images (portraits, heroes, student life, partners) | `uploads/imgs/` + WP uploads | Copy referenced files only |
| Newsletter subscribers (70) | CMS table | Import |

Legacy dumps stay outside the repo; scratch DBs are disposable.

---

## 8. Implementation phases

- **Phase 0 (done):** copy model → mru-new-web; clone DB; git baseline; docs.
- **Phase A — Rebrand the shell:** palette + brand marks + SEO defaults + footer + SiteNav IA + admin/auth/learn shells + emails/PDFs + privacy/terms + remove personal-brand strings (45 files list in 01 §8).
- **Phase B — University modules:** migrations, models, admin CRUD, admin nav group.
- **Phase C — Content migration:** importers from legacy dumps + asset copying + fee re-parser.
- **Phase D — Public pages:** homepage; about cluster; admissions cluster; faculties + programme finder + programme pages; research/Scholar; student life; news/events/gallery/vacancies; contact.
- **Phase E — Polish:** structured data (CollegeOrUniversity, Course, Event, Article, FAQPage, Breadcrumb), sitemap update, accessibility pass, performance pass, smoke tests, README, final commit.

Each phase = one or more git commits with working state; challenges recorded in 04-IMPLEMENTATION-LOG.md.

---

## 9. Deliberate decisions & risks

1. **Old admin's "restore from upload" feature is NOT rebuilt** (it was an RCE vector). Backups belong to ops, not the web UI.
2. **Application form stays on eportal.mru.ac.ug** — the website drives to it (correct funnel separation); an on-site inquiry form (short, ≤5 fields) captures pre-application leads instead.
3. **APP_KEY retained from model** so cloned encrypted data stays readable; rotate at production deploy together with all secrets (the model `.env` carries live Flutterwave test keys — replace before launch).
4. **Legacy WP posts import** is best-effort: Elementor markup flattened to clean HTML; unresolvable images dropped rather than hot-linked.
5. **Fees data quality:** re-parsed programmatically + spot-checked; ambiguous rows flagged `tuition_note = 'verify'` rather than guessed silently.
6. **Enrollment/term modelling** (degree registration per semester) is out of scope — the site is informational + short-course LMS; the student information system remains Campus Dynamics (eportal/eadmin).
7. **Default admin password** in seeder (`111111`) and legacy DB password `Lalana31@` are flagged for rotation; local dev only.
8. **Scholar self-submission workflow** ships admin-managed first; the approval-state machine fields exist so lecturer self-service can be enabled without schema change.
