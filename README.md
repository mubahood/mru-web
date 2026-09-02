# Muteesa I Royal University — Website

The official website of **Muteesa I Royal University** (mru.ac.ug): the public university site,
the admissions funnel, university news and events, the **MRU Scholar** research repository, and
**MRU e-Learning** (short online courses with verifiable certificates) — one Laravel application
with a complete admin back office.

Built on Laravel 12 (PHP 8.2+), Blade + Livewire + Alpine.js. Derived from a production-grade
model application; see `docs/00-BLUEPRINT.md` for the full rebuild story, and
`docs/04-IMPLEMENTATION-LOG.md` for how the legacy site's content was migrated.

## What's here

- **Public site** (`/`) — homepage, About cluster (governance, council, staff directory),
  Admissions cluster (requirements, how to apply, fees, scholarships, intakes, international,
  FAQs), Faculties & the filterable Programme Finder, Student Life pages, News (`/news`),
  Events, Vacancies, Gallery, Downloads (44 official documents), Contact (spam-shielded form).
- **MRU Scholar** (`/scholar`) — the research repository: searchable publications with DOI
  links and counted PDF downloads, research areas, scholars directory and profiles.
- **MRU e-Learning** (`/e-learning`) — course catalogue; free courses self-enrol, paid courses
  check out via Flutterwave (mobile money, card, bank, USSD). Students learn at `/learn`
  (video player, progress, quizzes, assignments, certificates verifiable at `/verify`).
- **`/admin`** — back office: all university content (faculties, programmes, staff, events,
  almanac, scholarships, vacancies, partners, scholars, publications, research areas), news,
  gallery, the full LMS (courses → modules → lessons, gradebook, grading queue, enrolments),
  invoices/payments, first-party analytics, users and settings.

## Requirements

- PHP 8.2+, Composer 2
- MySQL 5.7+/8 (MAMP socket supported); the app uses the `mru_new_web` database
- Node 18+ (for building front-end assets)

## Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
#   Set DB_DATABASE=mru_new_web, DB_USERNAME/DB_PASSWORD, and (MAMP) DB_SOCKET.

mysql -uroot -proot -e "CREATE DATABASE mru_new_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed          # roles, owner account, districts
php artisan db:seed --class=UniversityContentSeeder   # university identity & admissions copy
php artisan storage:link            # public images, documents, publication PDFs
npm run build                       # or `npm run dev` while developing
php artisan serve
```

### Migrating the legacy content (one-time)

With the legacy dumps loaded into scratch databases `mru_legacy` (custom CMS, `mru_mru2.sql`)
and `mru_legacy_wp` (live WordPress, `mru_wp435.sql`), and the backup tree available at the
path in `App\Console\Commands\ImportLegacyContent::BACKUP_ROOT`:

```bash
php artisan mru:import-legacy       # idempotent; add --skip-wp to skip news/scholar
```

This imports faculties, programmes (with the university's published fee bands), the staff/
council/committees/guild directory, events, almanac, scholarships, partners, newsletter
subscribers, the MRU Scholar repository (scholars, publications, PDFs) and the genuine news
archive (spam from the old site's compromise is filtered). Details and data-quality decisions:
`docs/04-IMPLEMENTATION-LOG.md`.

## Spam protection on public forms

Every public form runs two independent layers:

1. **Honeypot + timing** (`App\Support\Spam\FormShield`) — always on, needs no keys.
2. **Google reCAPTCHA v2** (`anhskohbo/no-captcha`) — **off until you add keys**:

```
NOCAPTCHA_SITEKEY=your-site-key
NOCAPTCHA_SECRET=your-secret
```

## Tests

```bash
php artisan test              # SQLite in-memory; 1100+ tests
vendor/bin/pint               # code style
vendor/bin/phpstan analyse    # static analysis
```

## Before production deploy

- Rotate `APP_KEY`, all mail credentials, and the Flutterwave keys (`FLW_*`); set real
  reCAPTCHA keys.
- Replace the seeded owner account password (`database/seeders/AdminUserSeeder.php`).
- The legacy production DB password from the old site must be rotated regardless — it was
  committed to that site's web root in plaintext (`docs/02-OLD-SITE-ANALYSIS.md` §5).
- Point `APP_URL` at https://mru.ac.ug and serve `public/` as the web root (never the repo
  root, which was the old site's fatal habit).
- Re-obtain the Prospectus and Students' Handbook PDFs from the university — they were linked
  on the old site but absent from its backup.

## Payments

Flutterwave, behind a swappable `PaymentGateway` interface (`app/Services/Gateway/`).
Set `FLW_*` keys in `.env` to enable checkout. `FLW_PAYMENT_OPTIONS` defaults to
`mobilemoneyuganda,card,banktransfer,ussd`.
