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
