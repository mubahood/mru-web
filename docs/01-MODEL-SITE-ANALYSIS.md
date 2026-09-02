# Model Website Analysis — Feature Inventory & Reuse Assessment

Deep analysis of the model application (`muhindo-app`, copied here as the foundation of the new MRU website). Compiled 2026-09-03.

**Stack:** Laravel 12 · PHP 8.2+ · Blade + Livewire 3 + Alpine.js · MySQL · Sanctum API · Spatie Permission + Activitylog · DomPDF · CommonMark · Intervention Image · endroid/QR · anhskohbo/no-captcha · Horizon/Telescope (dev) · Predis.
**Scale:** 56 Eloquent models · 96 migrations · 201 Blade views · 11 Livewire components · 68 controllers · 158 test files.

---

## 1. Routes

Files: `routes/web.php` (458 lines), `routes/auth.php`, `routes/api.php`, `routes/console.php`.
Helper `$movedPermanently()` at `routes/web.php:51` — 301 redirects built with `url()` so they survive a sub-directory install.

### 1.1 Public (no auth)

| URI | Name | Handler |
|---|---|---|
| `GET /` | `home` | `PortfolioController@home` |
| `GET /sitemap.xml` | `sitemap` | `SitemapController@sitemap` |
| `GET /robots.txt` | `robots` | `SitemapController@robots` |
| `GET /work`, `/work/all`, `/work/{slug}` | `portfolio.work/projects/project` | `PortfolioController` |
| `GET /about`, `/services`, `/skills`, `/experience`, `/education`, `/research`, `/cv`, `/products` | `portfolio.*` | `PortfolioController` |
| `GET /gallery` | `gallery.index` | `GalleryController@index` |
| `GET /blog`, `/blog/{post:slug}` | `insights.*` | `InsightController` (301 from `/insights`) |
| `GET /source-code`, `/source-code/{slug}` | `shop.*` | `Shop\ShopController` (301 from `/shop`) |
| `GET/POST/PATCH/DELETE /cart` | `cart.*` | `Shop\CartController` |
| `POST /currency` | `currency.switch` | `Support\Catalog\Currency::set()` |
| `GET /e-learning`, `/{slug}`, `/{slug}/preview/{lesson}` | `courses.*` | `CourseCatalogueController` (301 from `/courses`) |
| `POST /e-learning/{slug}/notify-me` | `courses.notify` | waitlist, throttle 6/1, no auth |
| `GET /hire` | `hire` | `HireController` (invokable role-router; 301 from `/contact`, `/start-a-project`) |
| `GET /privacy`, `/terms` | — | `Route::view` |
| `GET /verify`, `/verify/{certificate}` | `certificates.*` | `CertificateVerificationController` |
| `POST /_a` | `analytics.beacon` | CSRF-exempt, encrypted page handle |
| `GET/POST gateway/flutterwave/callback|webhook` | `gateway.*` | Flutterwave callback + signed webhook |
| `Route::fallback` | — | 404 recorded in analytics |

### 1.2 Auth
`login`, `logout`, `register` (student/client/both), `forgot-password`, `reset-password/{token}`, `verify-email` (signed+throttled), `confirm-password`, forced `password/change`.

### 1.3 Signed-in (any role)
`/dashboard` (+ onboarding dismiss), `POST /theme`, `/notifications` (+read/read-all), `my/orders`, `pay/{invoice}` (+direct/online/cancel/recheck), `account.*` (edit/update/type/avatar), `checkout.*`, `shop.downloads`, `GET|POST /propose`.

### 1.4 Admin (`/admin`, middleware `['auth','admin']`)
- **Portfolio CMS:** resources for `portfolio-projects`, `skills`, `experience`, `education`, `services`, `posts`, `gallery`, `products`; bespoke `testimonials` routes (settings-JSON backed); `messages` inbox.
- **LMS:** `courses` resource + modules/lessons shallow resources, quick-store, toggle-publish, markdown preview, video-duration fetch, curriculum reorder, materials, content-images; `quizzes` (+analysis) & `questions`; `assignments`; `announcements` (+publish); per-course `analytics`, `gradebook` (Livewire GradeMatrix) + CSV export, `discussions`, `students`, `bulk-enroll`; `enrollments` (index/drilldown/store/update/invoice/destroy); `grading-queue`, `reviews`, `waitlist`.
- **Work:** `today` (Livewire day planner).
- **Analytics** (permission `analytics.view`): index/content/sources + Livewire live/visitors/visitor-profile.
- **Clients & Projects:** `clients`, `projects` resources; `project-inquiries` (index/show/status/destroy); project `tasks`/`notes`/`updates`/`documents`.
- **Billing:** `invoices` (+pdf/payments/flutterwave), `payments/{payment}/receipt`, `coupons`.
- **System:** `users` (permission `manage-users`), `settings` (super-admin; only 5 fields).

### 1.5 Student `/learn`
Index; certificate page + download; course resume; quizzes (show/start/attempt/answer/submit/review); assignments (show/draft/submit/download); grades; announcements; review; discussions (CRUD+reply+resolve); THEN generic `{course}/{lesson}` routes (ordering constraint documented at `routes/web.php:372-377`): lesson, complete, heartbeat, time, materials download/preview, `video-stream` (**signed** URL), content-images, notes.

### 1.6 Client `/portal` (middleware `ClientMustPropose`)
Index, project detail, document download (policy + confidentiality checks), invoices + PDF + pay.

### 1.7 API v1 (Sanctum, `routes/api.php`)
Public: `openapi.json`, `auth/login`, courses index/show, signed `video-stream` for native players. Authed: me/logout, my/enrollments, enroll, lesson complete/heartbeat, notes CRUD, quizzes+attempts, assignments, grades, my/projects, invoices, device-tokens. Uniform envelope via `App\Support\ApiResponse`.

---

## 2. Data Model (56 models)

### CMS / portfolio
- **PortfolioProject** — title, slug, description, client, problem, approach, mechanics(json), stack(json), constraints(json), role, period, tags(json), highlights(json), cover_image, external_link, is_featured, sort_order. Case-study shaped.
- **Skill** — name, category, proficiency(0-100), sort_order.
- **Experience** / **Education** — org, role/degree, dates, description, sort_order.
- **Service** — title, description, icon, sort_order.
- **Post** (blog) — title, slug, excerpt, body(longText), category, tags(json), cover_image, is_published, published_at, read_minutes, author_id.
- **GalleryPhoto** — title, caption, alt, category, path, webp_path, thumb_path, dimensions, is_published, is_featured, sort_order.
- **Product** (digital shop) — full digital-product schema incl. file deliverable, licences (**ProductLicense**), install guide.
- **ContactMessage**, **NewsletterSubscriber** (no UI), **Setting** (key/value store via `App\Support\Settings`, cache-forever).

### Courses / LMS
- **Course** — uuid, title, slug, course_number, description, tagline, outcomes(json), requirements(json), prerequisites_note, cover_image(+alt), playlist_url, price/price_usd/currency, level, tier, category, is_published, is_coming_soon, is_featured, progression(free|sequential), access_duration_days, created_by, softDeletes. `isSellable()`, `isComingSoon()`, `isFree()`, `isLessonLocked()`, `seoDescription()`.
- **CourseModule** — course_id, title, sort_order, softDeletes.
- **Lesson** — module_id, title, content(+format plain|markdown), video_url, is_embeddable, video_disk_path, captions_url, resource_url, is_external, duration_minutes, min_active_seconds, completion_rule(manual|min_watch|quiz_pass|submission), completion_threshold, sort_order, is_published, is_free_preview. YouTube ID extraction handles all URL shapes.
- **LessonMaterial** — pdf|zip|link|file.
- **Enrollment** — uuid, user, course, invoice, status(pending|active|completed|cancelled), progress_percent, total_watch_seconds, at_risk_reason, last_lesson_id, expires_at, **unique (user_id, course_id)**.
- **LessonProgress** — watch/active seconds, last position; unique per (enrollment, lesson).
- **LearningEvent** — telemetry stream (12 event types).
- **Quiz** — time limit, max attempts, pass %, grading method (highest|latest|average|first), shuffles, sampling, pagination, feedback modes, availability window, counts_toward_certificate.
- **Question** — 9 types (mcq_single, mcq_multi, true_false, fill_blank, numeric, matching, ordering, short_text, essay) + **QuestionOption**, **QuizAttempt** (uuid, score, integrity json), **AttemptAnswer** (auto_graded, grader_feedback).
- **Assignment** — due_at, points, late policy, file constraints, resubmit; **AssignmentSubmission** — drafts, attempts, late flag, grading.
- **Certificate** — unique per enrollment, uuid, certificate_no, PDF path (private disk).
- **Announcement**, **Discussion** (threaded, instructor-answer, resolvable), **LessonNote**, **EnrollmentNote**, **CourseReview**, **UserBadge** (4 types), **Coupon**, **CourseNotifyRequest** (waitlist w/ WhatsApp).

### Clients / projects / billing
- **Client** — uuid, user_id, client_number, contact fields, district_id, softDeletes.
- **Project** — uuid, project_number, client_id, category, status(proposal|active|on_hold|completed|cancelled), priority, dates, budget, `project_team` pivot.
- **ProjectTask** (nullable project → personal todo; recurrence), **ProjectNote** (`is_client_visible`), **ProjectUpdate** (percent_complete), **ProjectDocument** (private disk, `is_confidential`).
- **ProjectInquiry** — rich brief (name/email/phone/country/organisation, project_type, category, budget, timeline, description, who_uses_it, success_looks_like) + status pipeline (new|contacted|converted|closed).
- **Invoice** — uuid, invoice_no, **polymorphic billable** (Client|User), project_id, money fields, status (draft|issued|partially_paid|paid|void|refunded), direct_payment_at; **InvoiceItem** (polymorphic source → Course/Product); **Payment** (cash|mobile_money|bank|flutterwave, balance_after, receipt); **GatewayLog** (tx_ref unique, verified, meta).

### Users / roles / infra
- **User** — role(super_admin|admin|student|client) mirrored into Spatie roles on save; independent `is_student`/`is_client` capability flags; theme, avatar, phone, bio, forced password change, last_active_at.
- Spatie permission tables; `activity_log`; `notifications`; **DeviceToken** (web|android|ios); **District** (Uganda reference data).

### Analytics
**Visitor** (token, attribution, device, revenue, is_bot), **Visit** (session, channel, bounce), **PageView** (route, response_ms, engaged_seconds, scroll), **AnalyticsEvent**, **AnalyticsDaily** (rollups incl. signups/enrollments/orders/revenue by channel/country/device).

---

## 3. Admin Back Office

Blade CRUD (`layouts/admin.blade.php`) + 11 Livewire full-page components for interactive screens. Styling: hand-written `public/css/td-admin.css` (836 lines, `.tb-*` vocabulary) — **no Tailwind in practice**. Nav defined once in `resources/views/partials/admin-nav.blade.php` (capability-aware `$groups` + `$allow()` gate); students/clients get a reduced same-shell menu.

Manageable areas: portfolio projects, skills, experience, education, services, blog posts, gallery (auto JPEG+WebP+thumb), products, testimonials (settings-JSON), messages, courses (incl. 25KB lesson editor with markdown preview + YouTube duration fetch + drag reorder), quizzes/questions (+analysis), assignments, announcements, enrollments (drilldown, cancel+refund, extend, nudge, notes), grading queue, grade matrix + CSV export, reviews moderation, waitlist, discussions, students-per-course, bulk enrol, clients, projects (tasks/notes/updates/documents), project inquiries, invoices (+PDF), payments (+receipt), coupons, analytics suite, users, settings (5 fields only).

**Biggest CMS gap:** `portfolio.identity`, `.about`, `.stats`, `.contact`, `.clients`, `.research`, `.products`, `.languages`, `courses.faq` settings JSON have **no admin UI** — seeded only (`database/seeders/PortfolioContentSeeder.php`).

---

## 4. Public Site

**Layout:** `resources/views/layouts/marketing.blade.php` — 112 KB single file: inline design system (lines 19–1411), header/nav (1432–1528), mobile sheet (1530–1567), footer (1573–1631).

**Homepage** (`portfolio/home.blade.php`): hero + stats → about → testimonials band (auto-hides when empty) → 3 featured projects → e-learning strip (3 courses) → products band → gallery (6 featured) → blog (3 posts) → services (4) → client logos band → final CTA.

**Content sources:** module tables (projects/skills/experience/education/services/posts/gallery/products/courses) + `settings` JSON blobs (identity, contact, stats, about, clients, testimonials, research, products, languages, courses.faq) via `App\Support\Settings` — and navigation as code in `app/Support/SiteNav.php` (one array → desktop bar + mega menu + mobile sheet + footer columns).

---

## 5. LMS capabilities (production-grade)

- **Enrolment:** free self-enrol (idempotent), paid → invoice → Flutterwave (mobile money UG, card, bank, USSD) or manual "direct payment"; coupons; dual currency UGX/USD with visitor toggle; coming-soon → waitlist (`CourseNotifyRequest`, admin launch-order screen); bulk enrol; access windows (`access_duration_days`).
- **Lesson player:** self-hosted video via 6-hour **signed streaming URLs**, YouTube (incl. nocookie/shorts/embed), generic embeds, non-embeddable external fallback; captions; resume; markdown lesson content; timestamped student notes; materials.
- **Progress:** 15s heartbeats + visibility-gated active time (sendBeacon on exit) → per-lesson watch/active seconds + denormalized course progress; completion rules per lesson (manual / min_watch% / quiz_pass / submission) + sequential locking.
- **Assessment:** quizzes (9 types, sampling, shuffles, timing, 4 grading methods, feedback modes, integrity log, analysis screen) + assignments (drafts, late penalties, resubmission, grading queue) + grade matrix + CSV export.
- **Certificates:** auto-issue on eligibility, DomPDF + QR, private-disk streamed, public `/verify/{certificate}`.
- **Retention:** badges, streaks, at-risk detection (nightly), weekly instructor digest, student nudges.

---

## 6. Client Portal
Reuses the admin shell with capability-filtered nav. Gate: `ClientMustPropose` (no inquiry → forced to `/propose`). Shows projects w/ completion bars, proposals, tasks, updates, client-visible notes, non-confidential documents (private-disk streamed through policy checks), invoices + PDF + pay.

---

## 7. Auth & Roles
- 4 roles + independent capability flags (one account can be student AND client).
- Permissions (RbacSeeder): access-admin, manage-users, manage-settings, portfolio.manage, courses.manage, clients.manage, projects.manage, billing.manage, analytics.view.
- **FormShield** (honeypot `referral_note` + encrypted min/max timing) + **reCAPTCHA v2** (inert until keys set) on every public form.
- Email verification available but **not enforced** (no `verified` middleware anywhere). Password policy: min 6 (NIST rationale). Forced-password-change flow for admin-created accounts (WelcomeCredentials mail).
- Registration picks account_type (student|client|both) with context pre-selection; post-login routing via `AfterAuth::destination()`.

---

## 8. Design System / Layout

Three style systems:

| Surface | Layout | CSS | Palette |
|---|---|---|---|
| Public | `layouts/marketing.blade.php` | inline lines 19–1411 | `--pri:#0b1f3a` navy, `--gold:#b8933f`, cream bg `#f7f6f2` |
| Admin/portal/learn | `layouts/admin.blade.php`, `layouts/learn.blade.php` | `public/css/td-admin.css` | blue `--br:#0a6ebd` |
| Auth | `layouts/auth.blade.php` | inline | navy+gold |

- Fonts: **Inter self-hosted** (`public/vendor/fonts/inter/`). Icons: **Font Awesome 6 self-hosted**, print-media loading trick. Tailwind configured but **unused** (single dead `@vite` in unused `layouts/guest`).
- Dark mode **not implemented** (theme column + endpoint exist, nothing consumes them).
- ~20 responsive breakpoints, 44px tap targets, prefers-reduced-motion guards. PWA manifest + service-worker killer.

### Branding touchpoints (exact files)

| What | Where |
|---|---|
| Public header wordmark + badge | `layouts/marketing.blade.php:1434` |
| Public footer brand/blurb/copyright | `layouts/marketing.blade.php:1577-1579, 1627-1628` |
| Admin sidebar brand + title suffix | `layouts/admin.blade.php:1,10,35,37,73,134` |
| Learn shell title | `layouts/learn.blade.php:1,20` |
| Auth brand | `layouts/auth.blade.php:7,111` |
| Public palette | `layouts/marketing.blade.php:20-26` |
| Admin palette | `public/css/td-admin.css:7-19` (+ learn overrides) |
| SEO defaults | `components/seo.blade.php` + `layouts/marketing.blade.php:6-10` |
| PWA | `public/manifest.json` |
| Logo assets | `public/brand/*` (SVG set), `public/images/logo*.png`, `og.png`, `favicon.png` |
| DB settings | `settings` rows: site_name, tagline, contact_email, contact_phone (+ seeded JSON blobs) |
| Nav | `app/Support/SiteNav.php`; admin nav `partials/admin-nav.blade.php` |
| "Muhindo Mubaraka" hardcoded | **45 files** — controllers (Portfolio, CourseCatalogue, Payment, OpenApi), BuyerPaymentService, all `portfolio/*` views, email header/footer, PDFs (invoice/receipt/signature), certificates, errors, privacy/terms, shop, courses, insights, gallery, gateway result, register, payments |

---

## 9. Infrastructure

- **Analytics:** first-party subsystem (TrackVisitor terminable middleware — skips admin/non-GET/Livewire/assets, bot-flagging; beacon endpoint; hourly rollups; live dashboard).
- **Mail:** SMTP; 13 notifications; branded email templates; `mail:test` command.
- **Queue:** `sync` (nothing queued); Horizon installed but idle.
- **Storage:** `local` private disk (documents, submissions, certificates, lesson videos — always streamed, never web-served); `public` disk (avatars, covers, gallery); s3 configured unused.
- **Scheduler:** at-risk detection (02:00), instructor digest (Mon 07:00), streak badges (02:30), learning-event pruning (monthly), analytics rollup (hourly) + prune (weekly) + geolocate (hourly), recurring tasks (05:00).
- **Commands:** courses:import/apply-pricing/make-covers/open-first-lessons/verify-links/purge-demo, gallery:import, shop:deliverability, mail:test.
- **Seeders:** RbacSeeder, AdminUserSeeder (**default owner password `111111` — must change**), DistrictSeeder (Uganda), PortfolioContentSeeder (+CaseStudySeeder); optional PublicCourseCatalogueSeeder (21 courses), SourceCodeSeeder, GalleryCaptionSeeder.
- **Local-only baggage:** `_db_seed/` (128 MB predecessor dump), `course-content/` (private course sources), `_deploy-oneoffs/` (web-executable cPanel deploy scripts — replace with a real pipeline before production).
- **Quality:** 158 test files (SQLite in-memory), Pint, PHPStan/Larastan, smoke.sh, CI scripts (empty-file check, secrets scan).

---

## 10. Reuse Assessment for the University Website

| # | Module | Rating | Justification |
|---|---|---|---|
| 1 | Auth + Spatie RBAC | **as-is** | 4 roles + capability flags; add lecturer/staff rows in the same matrix |
| 2 | Spam shield (FormShield + Captcha) | **as-is** | Reuse verbatim on admissions/enquiry forms |
| 3 | Settings store | **as-is** | Generic cached config; write university keys |
| 4 | Analytics subsystem | **as-is** | Exactly what programme-page traffic needs; first-party |
| 5 | Courses → short courses / e-learning | **adapt** | Structure maps to units; degree programmes need terms/cohorts later |
| 6 | Quizzes + assignments + gradebook | **as-is** | Production-grade coursework tooling |
| 7 | Enrolment + progress + player | **adapt** | Great for self-paced; unique(user,course) blocks per-term registration |
| 8 | Certificates + verification | **as-is** | QR + `/verify` is what employers need |
| 9 | Discussions/announcements/notes/reviews | **as-is** | Course Q&A, notice board; reviews → evaluations |
| 10 | Waitlist | **rename** | "Register interest in an intake" — launch-order ranking useful for admissions |
| 11 | Portfolio projects | **rename** | Near-perfect research-project record (add PI/funder/publications) |
| 12 | Research settings blob | **adapt** | Promote to real publications tables (MRU Scholar) |
| 13 | Skills/Experience/Education | **adapt** | Reframe as staff profiles keyed on user_id |
| 14 | Services | **rename** | → Faculties/Schools cards or consultancy |
| 15 | Posts (blog) | **as-is** | News; add events fields or separate events table |
| 16 | Gallery | **as-is** | Campus gallery; WebP pipeline done |
| 17 | Clients | **adapt** | Partner orgs/sponsors, or pattern for student records |
| 18 | Projects + portal | **adapt** | Research-grant/consultancy management with sponsor portal |
| 19 | Inquiries + /hire + /propose | **adapt** | Strongest admissions-application candidate: pipeline + portal visibility |
| 20 | Invoices/payments/coupons | **as-is** | Tuition/application fees; polymorphic billable; part-payments modelled |
| 21 | Flutterwave gateway | **as-is** | Mobile money + card + bank + USSD = Ugandan fee mix; swappable interface |
| 22 | Shop | **not-needed** (optional) | No university analogue; optionally publications/past-papers store |
| 23 | Testimonials (JSON) | **adapt** | Student/alumni stories deserve a real table |
| 24 | Contact messages/newsletter | **rename** | Enquiry inbox + mailing list (reinstate a contact form) |
| 25 | Today planner | **as-is** | Staff task board unchanged |
| 26 | Dashboard | **adapt** | Composition right; metrics need academic equivalents |
| 27 | API v1 + OpenAPI | **as-is** | Ready for a student mobile app; signed video route solves native players |
| 28 | Marketing layout | **adapt (heavy)** | Navy+gold suits a university; restructure content + consider extracting CSS |
| 29 | Admin design system | **as-is** | Complete `.tb-*` vocabulary; build university back office on it |
| 30 | Tailwind toolchain | **not-needed** | Unused; remove or commit — not both |
| 31 | SiteNav | **adapt** | Right pattern, wrong content; university IA needs audience bar |
| 32 | Districts | **as-is** | Uganda reference data |
| 33 | Seeders/course-content/_db_seed | **not-needed** | Personal content; keep RbacSeeder + seeder shapes |
| 34 | _deploy-oneoffs | **not-needed** | Replace with a real pipeline |

### Three structural gaps to plan for
1. **No CMS UI for identity/about/stats/FAQ settings JSON** — build settings editors or a real pages/content layer for university pages.
2. **`enrollments` unique on (user_id, course_id)** — fine for lifetime course access; add term/cohort dimension before modelling degree registration.
3. **Single-owner assumptions** — one instructor, one CV, one signature; a university needs staff profiles, departments, and course↔instructor relations.
