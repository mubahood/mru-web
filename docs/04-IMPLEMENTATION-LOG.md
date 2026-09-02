# MRU Website Rebuild — Implementation Log

Running log of work, decisions and challenges. Newest entries at the bottom.

## 2026-09-02/03 — Phase 0: Foundation
- Copied model app `/Applications/MAMP/htdocs/muhindo-app` → `/Applications/MAMP/htdocs/mru-new-web` (rsync, `.git` excluded; 3.2 GB incl. vendor/node_modules so the site runs immediately).
- Created DB `mru_new_web` (utf8mb4) and cloned all 74 tables from `muhindo_app` via mysqldump (single-transaction, routines, triggers). Safety dump kept at `~/Desktop/mru-db-backups/muhindo_app_clone_2026-09-02.sql` (162 MB).
- Repointed `.env` (APP_NAME, APP_URL http://localhost:8888/mru-new-web, APP_FOLDER, DB_DATABASE=mru_new_web). Cleared config/cache/view/route caches. All 46 migrations report Ran.
- Verified over HTTP: /, /e-learning, /about, /blog, /gallery, /login, /register, /verify, /services, /sitemap.xml all 200; Vite build assets present.
- **Challenge:** Homebrew PHP broken (missing libtidy dylib) → use MAMP `/Applications/MAMP/bin/php/php8.3.9/bin/php` for artisan. MAMP MySQL client lives at `/Applications/MAMP/Library/bin/mysql80/bin/`.
- Removed `credentials.txt` (unrelated hosting creds + private SSH key from the model author's other project — gitignored anyway, no place in this repo) and `.phpunit.result.cache`.
- `git init -b main`; baseline commit of the pristine import.
- Ran three deep analyses (model app features; legacy backup incl. both SQL dumps; 2025-26 higher-ed web research) → docs 01/02/03. Captured the live mru.ac.ug IA + programme list + scholar structure as cross-check.
- **Key discovery:** the "custom site DB" dump `mru_mru.sql` (363 MB) is actually a staging WordPress dump; the real custom-CMS DB is `mru_mru2.sql` (195 KB). Live WP DB is `mru_wp435.sql` (news archive: 249 posts).
- **Security notes recorded** (legacy): unauthenticated `admin/autologin.php` backdoor, plaintext prod DB password, malware droppers + casino spam posts in WP. Decision: migrate data only, never code/files verbatim; curated asset copying.

## 2026-09-03 — Phase A: Rebrand (commit 9b71f82)
- Palette #05275C/#D4A843 across marketing layout, td-admin.css, manifest; crest as brand mark + favicon; header CTAs → E-Portal + Apply Now; footer institutionalized; WhatsApp launcher → MRU Admissions intents on +256 752 033 889; emails/PDFs/certificates/errors/payment copy reworded (University/Bursar); UniversityContentSeeder (identity, contacts, socials, links, stats, admissions, accommodation).
- **Motto discrepancy resolved:** crest ribbon "Seeking Greater Horizons in Thought and Action" chosen over WP blogdescription variant ("...Horizon In Thoughts and Actions").
- Tests updated to new brand strings; full suite green (1147 passed, 1 skipped).

## 2026-09-03 — Phase B: University data model
- Migrations `create_university_tables` (faculties, staff_members, programmes, university_events, almanac_entries, scholarships, vacancies, partners; faculties⇄staff dean FK added after both exist) and `create_scholar_tables` (research_areas, scholars, publications, publication_author with external co-authors, publication_research_area).
- 11 models + `GeneratesSlug` trait (unique slug on save, per-table).

## 2026-09-03 — Phase C: Legacy content migration (`mru:import-legacy`)
- Scratch DBs: `mru_legacy` (custom CMS, mru_mru2.sql) and `mru_legacy_wp` (live WP, mru_wp435.sql).
- Imported: 5 faculties · 93 staff + 18 council + 34 committee + 8 guild (155 people) · 44 programmes (46 legacy − 2 true dupes across categories) · 14 almanac rows · 6 scholarships · 4 partners · 6 sports + 2 hero slides (settings JSON) · 70 newsletter subscribers · **30 scholars** (WP, real lecturers; test rows dropped) · **19 publications** (18 with PDFs copied) + 10 research areas · **111 genuine news posts** with 68 cover images.
- **Spam scale correction:** 127 casino/gambling posts filtered (not ~10 as first estimated) — the WP compromise was extensive. Keyword filter + manual sample verification.
- **Fees decision (deviation from blueprint §7):** the 128-row `programme_fees` table is PDF-scrape damage (name/faculty/level/duration/mode and the amount's leading digit concatenated into `programme_name`); reconstruction would still leave the billing period ambiguous. Imported instead the three **verified** faculty bands the university itself publishes (live /fees page + `fees_structure`): IT/Computing 1.5M, Business 1.2M, Education & Arts 1.0M UGX/semester; postgraduate programmes carry "Contact the Graduate School" note. Every tuition figure marked "Estimated; confirm with the Bursar".
- **Council data quirk handled:** legacy `council_members.role` holds the NAME and `description` the role.
- Legacy `events` table was mostly theme demo junk ("adam", "Falar's Career Fair") — only dated real rows imported (2).
- Documents: **44 PDFs** curated from wp-content/uploads (strategic plan 2025-2030, almanac, e-portal guide, application forms, short-courses brochure, orientation, 2 journals, research register, Luganda for beginners, 31 policies) → `storage/app/public/documents/` + `university.documents` settings index. **Prospectus 2022-2027 and Students' Handbook PDFs are NOT in the backup** (only link text in DB) — university must re-supply.
- WP publication files lived as bare filenames in one folder — importer prefixes `wp-content/uploads/mru-scholar/`.
- Model's two personal blog posts removed; news category standardized to "News".
