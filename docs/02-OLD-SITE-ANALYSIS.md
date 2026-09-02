# Old MRU Website (mru.ac.ug) — Legacy Backup Analysis

Comprehensive analysis of the backup directory `/Users/mac/Downloads/uploads-1.zip` (~21 GB). Compiled 2026-09-03.
Three site generations coexist in one web root, plus three SQL dumps.

**Critical correction:** `mru_mru.sql` is **not** the custom-site DB — it is a WordPress dump (prefixes `wp_` and `wpxo_`), matching the `mruweb.rf.gd` staging install. The custom PHP CMS DB is **`mru_mru2.sql` (195 KB)**, confirmed by `config/db_config.php` → `'dbname' => 'mru_mru2'`.

| Dump | Size | Prefix | Belongs to |
|---|---|---|---|
| `mru_wp435.sql` | 337 MB | `wp8a_` | **Live WordPress** on mru.ac.ug |
| `mru_mru.sql` | 363 MB | `wp_` + `wpxo_` | Staging/older WordPress (mruweb.rf.gd) |
| `mru_mru2.sql` | 195 KB | *(none)* | **Custom PHP CMS — the real migration target** |

---

## 1. Custom PHP site

### How it works
- **`router.php`** reads `?page=` (default `home`), sanitizes, routes hard-coded faculty slugs → `pages/faculty.php`, `scholar/profile` → scholar-detail, otherwise `pages/{page}.php`; **falls through to WordPress** via `wp-loader.php` when nothing matches — the hybrid glue.
- **`.htaccess`** (canonical copy in `MRU2/.htaccess`): physical files pass; reserved WP slugs (`mru-scholar`, `scholar-profile`, `publications`, …) → wp-loader; 301 strips `.php`; 1–2 segment slugs → router; rest → WordPress.
- **`index.php`** (43 KB homepage): header include (opens PDO), newsletter POST, hero slider (`hero_slides`), quick actions (Programs/Apply/Fees), About section (settings), programme cards, Why-Choose-Us stats, student-life strip, latest news + events sidebar, Twitter widget, testimonials, partners, popups.
- **`includes/`**: `header.php` (26.6K — CSS vars `--mru-blue #002366`, `--mru-gold #D29D2B`, logo `assets/images/MRU-LOGO.png`, DB-driven topbar + recursive nav from `nav_links`), `footer.php` (14.6K — CTA band, quick/resource links JSON, newsletter; uses `str_starts_with()` → fatal on PHP < 8), `popup_renderer.php`, `ai_assistant.php` (floating "MRU AI Student Assistant" widget).
- **`config/db_config.php`** contains **live production credentials in plaintext** (db `mru_mru2`, user `mru_bckup`, password `Lalana31@`).

### Pages (64 files) — grouped
- **Institutional:** about, who-we-are (mission/vision/6 values/story), vice-chancellor (Prof. Kakembo Vincent), deputy-vice-chancellor (Prof. Kasule A M Umar), academic-registrar (Dr Musisi Fred Kamoga), dean-of-students (Mr Ssenkungu Paddy), university-governance, university-council, committees (+ template + 3 stubs), staff-directory, policies, contact.
- **Academics:** academics (levels of study, fees & payment), faculties, faculty.php (43K single-faculty template: about/vision/mission, departments JSON, careers, programmes by category, staff, events), programs, research, library, mru-ict-centre, almanac, page.php (generic CMS renderer).
- **Admissions:** admissions (3-step), admission-requirements (+ timeline), application-procedure (7 steps, fee, deadline), fees, scholarships, bursaries-financial-aid, intakes, faqs, international-students.
- **Student life:** campus-life, student-life, students-guild, sports-and-lifestyle, accommodation (3 halls), alumni-network.
- **Orientation cluster (7):** orientation hub + welcome/registration/programs/campuses (Kirumba–Masaka & Kakeeka–Mengo)/financial-aid/ict-support.
- **News/Events:** news, post-single, blog (hardcoded samples), events, events-2, event-single, networking-events.
- **Careers:** jobs (vacancies), careers, career-counseling, resume-interview-prep, internship-program.
- **Scholar:** `pages/scholar/index.php` (public directory), `pages/legacy-root/scholar{,-detail}.php` (older versions).

### Admin (44 entries)
Dashboard with live counts; 25+ content managers: almanac, committees (40K), council, events (34K), upcoming_events, faculties, fees (`fees_structure` + `programme_fees`), footer_settings, guild, hero slides, homepage sections, **other_pages.php (113K — edits ~18 hero images + hundreds of `settings` text keys)**, jobs, messages inbox, nav_links, posts, notifications banners, pages, partners, popups, programs, scholarships, social-media, sports, staff, testimonials, topbar_settings. System: backup.php (DB/files/full backup + **restore from upload**), media library, settings. Auth: bcrypt login + session; **`autologin.php` = unauthenticated admin backdoor**. `admin/api/ai_assist.php` (Gemini/OpenAI content generator via settings keys), `upload-media.php` (CKEditor uploads, MIME allowlist).

### MRU Scholar module — TWO implementations

**A. Custom-CMS version** (`admin/scholar/` + `pages/scholar/`): scholar profiles (bio, research interests, image, CV), publications (journal/conference/book/thesis; DOI, volume, issue, pages, citations, PDF), multi-author, research-areas taxonomy (M2M), moderation workflow, public directory, sync logs. 8 tables: `scholar_profiles`, `scholar_publications`, `scholar_publication_authors`, `scholar_publication_files`, `scholar_research_areas`, `scholar_research_mapping`, `scholar_notifications`, `scholar_sync_log`.

**B. WordPress plugin** (`wp-content/plugins/mru-scholar/` — the richer, live one): 14 classes (admin dashboard 146K, shortcodes 80K, user dashboard 73K, auth 66K, publication manager, **Campus Dynamics v2.2 API integration** — `auth.aspx`, `student.aspx?action=verify|lookup|search`, `staff.aspx?action=lookup`, `campus.aspx?action=faculties|departments|programmes`, token login + mock mode). Custom roles `mru_lecturer`/`mru_student`; caps for submit/manage/approve. Workflow: `draft → pending → approved | rejected | revision_requested` with email + in-app notifications. Richer schema: publications add `category_id`, `visibility(public|restricted|private)`, `publisher`, `keywords`, `downloads`, `views`, `admin_notes`, `rejection_reason`, `approved_by`, `is_featured`, `cover_image`; users link `wp_user_id` + `mru_id`, role student|lecturer, faculty/department/program, `mru_sync_status`. **21 real publication PDFs (17 MB)** in `wp-content/uploads/mru-scholar/`.

---

## 2. Database content

### `mru_mru2.sql` — custom CMS DB (37 tables, dumped 2026-09-01) — exact row counts

| Table | Rows | | Table | Rows |
|---|---:|---|---|---:|
| settings | **203** | | events | 12 |
| programme_fees | **128** | | guild_members | 8 |
| staff | **96** | | sports | 6 |
| newsletter_subscribers | 70 | | scholarships | 6 |
| programs | **46** | | scholar_publications | 6 |
| nav_links | 37 | | pages | 6 |
| committee_members | 34 | | media | 6 |
| council_members | 18 | | job_openings | 5 |
| academic_almanac | 14 | | faculties | **5** |
| social_media | 4 | | partners | 4 |
| notifications | 3 | | fees_structure | 3 |
| committees | 3 | | users (admins) | 2 |
| posts | **2** | | hero_slides / popups / scholar_profiles | 2 each |

(testimonials, contact_messages, scholar_* others, twitter_widget_settings = 0)

Key schemas captured: `programs(name, category, description, image, icon, link)`; `faculties(slug, name, short, icon, color, tagline, description, about, vision, mission, programs_cat, departments JSON, careers JSON, responsible_title, responsible_staff_id)`; `posts(title, slug, content, image, category, status)`; `scholar_publications(scholar_id, title, abstract, type enum, journal, volume, issue, pages, date, doi, url, pdf_file, citations, status)`; `programme_fees(programme_name, programme_level, amount, currency, faculty, duration, display_order, status)`; `staff(name, title, department, email, phone, image, bio, education, faculty_id, staff_role enum(lecturer|other))`; `settings(setting_key varchar(50) PK, setting_value text)`.

### `mru_wp435.sql` — live WordPress DB (137 tables, `wp8a_`)
`wp8a_posts` by type: revision 7,559 · attachment **877** · **post (news) 249** · **page 140** · nav_menu_item 103 · elementor_library 29 · **program CPT 20** · tribe_events **14** · popup 13 · docs 10 · ninja-table 9 · events CPT 9 · wpcf7 8 · product 7 · **career CPT 5**. Status: publish 614, draft 25.
Options: `blogname = MUTEESA I ROYAL UNIVERSITY`, `blogdescription = "Seeking Greater Horizon In Thoughts and Actions"`, theme **Falar** (+falar-toolkit). 28 active plugins incl. Elementor (+Pro), ACF Pro, The Events Calendar, Smart Slider 3 Pro, Ninja Tables, Popup Maker, Redirection, LiteSpeed Cache, WP WhatsApp, **mru-scholar**.

### `mru_mru.sql` — staging WP (215 tables, `wp_` + `wpxo_`) — staler superset; historical only.

---

## 3. University content inventory (to migrate)

### Identity
- **Name:** Muteesa I Royal University (MRU)
- **Motto:** *"Seeking Greater Horizon In Thoughts and Actions"*
- **Vision:** "To be a premier African university of choice, recognized for academic excellence, cultural pride, and producing graduates who are leaders in their fields."
- **Mission:** "…providing quality, career-focused education rooted in Buganda's cultural heritage."
- **Core values (6):** Excellence · Integrity · Cultural Pride · Inclusivity · Innovation · Community Service
- **History:** inspired by Ssabasajja Kabaka Muwenda Mutebi II with support of the Buganda Kingdom Executive Committee led by the Katikkiro; named for **Kabaka Muteesa I of Buganda (1856–1884)**.

### Campuses & contacts
- **Kirumba Campus — Masaka** (main); **Kakeeka Campus — Mengo, Kampala**; P.O. Box 1339 Kampala. (Job ads also mention Rubaga & Mubende campuses.)
- Phone +256 200 903 000 · info@ / admissions@ / careers@ (ext. 3500) / accommodation@ (ext. 2800) / vc@ / dvc@ / registrar@ / dean-students@ / librarian@ / finance@ mru.ac.ug
- **WhatsApp +256 752 033 889** (enabled, preset greeting)
- Socials (authoritative `social_media` table): X @MRU_Uganda · facebook.com/MuteesaRoyalUniversity · instagram @muteesa_royal_university · linkedin /company/muteesa-royal-university
- External: **eportal.mru.ac.ug** (+/apply) · **eadmin.mru.ac.ug** · /library/ · /mru-scholar/

### Faculties (5) with departments (JSON)
1. **Faculty of Education** (FE) — "where professional and ethical teachers are made" — Arts & Humanities Ed · Science & Tech Ed · Educational Mgmt · Early Childhood & Primary Ed. Dean: Dr. Kiggundu Stephen.
2. **Faculty of Business & Management** (FBM) — Business Admin · Accounting & Finance · Procurement & Supply Chain · Entrepreneurship.
3. **Faculty of Social Sciences, Arts and Humanities** (FSSAH) — SWSA · Mass Comm · Tourism & Hotel Mgmt · Development Studies. Dean: Assoc. Prof. Nakijoba Rose Mary.
4. **Faculty of Science, Technology, Engineering, Art and Design** (FSTEAD) — CS & IT · Civil & Construction Eng · Fine Art & Industrial Design · Agriculture. Dean (Eng): Dr. Bukenya Patrick.
5. **Graduate School** (GS) — "Advanced Learning, Research & Professional Development".

### Programmes — 46 (see full list in `programs` table; categorised "{Faculty} {Level}"); WP has 20 richer `program` CPT posts.

### Fees
- `fees_structure` bands (UGX/semester): IT & Computing 1,500,000 · Business & Mgmt 1,200,000 · Education & Arts 1,000,000.
- `programme_fees`: **128 rows** — certificates 595,000; diplomas 292,000–940,500; bachelors 704,000/804,000/904,000 (engineering 624,000); masters 736,000–876,000; PGD 876,000. ⚠️ **`programme_name` strings are mangled PDF-scrapes** — faculty/level/duration/mode and the *leading thousands digit of the amount* are glued into the name (e.g. real fee 2,704,000 recorded as name "…Day2," + amount 704,000). **Must re-parse before migration.**
- Application fee UGX 50,000; admission processing UGX 60,000. Mobile money: MTN `*165*80#`, Airtel `*185*6*2#`.

### Admissions
- Bachelors: UACE ≥2 principals / diploma / NCHE-recognized professional qual. Diplomas: UCE ≥5 passes + docs. International: authenticated transcripts, proof of funds, IELTS ≥6.0 / TOEFL ≥79 / CAE C.
- Timeline: apply Jan–Feb · close Apr–May · results Jun–Jul · enrol Aug. Deadline of record **May 31, 2026** (August 2026 intake). Portal eportal.mru.ac.ug (7 steps).

### People
- **Staff directory: 96 rows** (⚠️ ~40 with placeholder emails/phones). Senior officers as above; deans included.
- **Council: 18 members with portraits** (Chair Dr. Saturninus Kasozi Mulindwa; Deputy Assoc. Prof. Irene Nalukenge; incl. Guild President Ms. Nampijja Jackline).
- **Committees: 3** (Disciplinary, Extended Management, Management) + 34 members (Bursar, HR Manager, Campus Director Kakeeka, Manager Information Systems Mr Muhindo Mubarak, Internal Auditor, International Office, Estates).
- **Guild cabinet: 8** (President H.E. Kato Ivan, VP H.E. Namubiru Sarah, PM, Speaker + 4 ministers).

### News / events
- **Real news archive is WordPress: 249 published posts + 877 attachments** (2023–2026; graduation ceremonies, admission lists, NCHE accreditation, sports galas, elections, workshops). ⚠️ ~10+ casino/gambling spam posts from the compromise — filter on migration.
- Custom CMS: 2 posts, 12 events (several with `http://localhost/wordpress/...` image URLs to rewrite), almanac 14 rows (2026/27 semesters: orientation, registration, CATs, Buganda Kingdom Cultural Week, finals, guild elections, graduation).

### Documents — **112 distinct PDFs** in wp-content/uploads
Flagship: Prospectus 2022-2027 · **Strategic Plan 2025-2030** · Students' Handbook 2022 · Almanac 2025-26 · Alumni Constitution · E-portal Guide. Forms: application forms, short-course form. **~30 institutional policies** (Assessment, QA, Examinations, Fees, Library, ICT, Research Grants, HR/Promotions, H&S, …). Journals: 2022 MRU Journal, Scientific Research Journal 2022, MRU Talks Ed.1, Luganda for Beginners. Admissions/graduation lists, orientation programmes. ~18 job-advert PDFs. 21 scholar publication PDFs. (Junk to drop: XSS_Guide.pdf, personal CVs.)

### Other content
- **Scholarships (6):** Academic Excellence (50–100%) · Financial Need (25–75%) · **Kabaka's Scholarship** (Buganda Kingdom, 50–100%) · Sports Excellence · Persons with Disabilities (50–100%) · Partner Organization.
- **Accommodation:** Kabaka Hall (male, 400 beds, 800K–1.2M) · Princess Hall (female, 350 beds) · Graduate Residence (150 beds, 1.5–2M) + facilities/eligibility/rules.
- **Sports (6):** Football (Royals FC), Netball, Basketball, Volleyball, Athletics, Indoor games.
- **Partners (4):** GENSS · NCHE · NEMRA · RENU (webp logos).
- Media: 37 custom-CMS images (council portraits, officers, hero, student-life) + 2.0 GB WP uploads.

---

## 4. Sibling folders
- **`MRU2/`** — canonical hybrid `.htaccess` + stale configs + final zip. Value: the rewrite rules only.
- **`mru.ac.ug/`** — empty (ftpquota).
- **`mruweb.rf.gd/`** (3.8 GB) — older standalone staging WordPress (DB `mru_mru`, prefix `wp_`); once Hostinger-hosted; only unique bits: custom-twitter-feeds config. Historical only.
- **`root/`** (5.5 GB) — cPanel migration staging tree; no unique site code.
- Loose zips + `backups/` — redundant snapshots.

---

## 5. Issues in the old code (why the rebuild is justified)

1. **`admin/autologin.php` — unauthenticated admin backdoor** (8 lines: sets `$_SESSION['admin_logged_in']=true` for anyone). Most severe finding.
2. **Live DB credentials in plaintext** inside web root (`config/db_config.php`, copies in MRU2/ and backups). Password must be rotated.
3. **Active compromise evidence:** random-named 0-byte dropper files + fake plugin folders (`6nxn2otm*`, `bib3q6pl*`, `wp-helper-*`, `wp-security-helper`, `easypost`, mu-plugin `ea_9d6de6da.php`) + casino spam posts in WP. **Migrate data, never files.**
4. **Stored XSS:** hero slide title/subtitle and many settings values echoed unescaped.
5. **Permissive router** — arbitrary `pages/**/*.php` include surface + root fallback across a web root that also contains WordPress.
6. **Backup/restore = RCE vector** — admin upload-and-extract over the live tree; naive SQL splitter; substring traversal guard.
7. **No CSRF protection anywhere**; single boolean session flag; `users.role` never consulted after login.
8. **PHP-version fragility:** `str_starts_with()` fatals on PHP 7.x hosts (seen in error_log).
9. **Three overlapping architectures** (custom CMS + live WP + staging WP) with duplicated pages, two scholar implementations, no single source of truth.
10. **Data-quality debt:** `settings` as a 203-row content model in a varchar(50)-key table; mangled `programme_fees` names; placeholder staff emails; localhost image URLs in events; web-accessible migrations/seeders.
