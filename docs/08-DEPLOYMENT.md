# Deployment — mru.ac.ug

**Live.** Deployed 2026-09-05 from commit `a2e1f50`.

## What the host is, and what that forces

| | |
|---|---|
| Panel | cPanel at `sv23.byethost23.org:2083` |
| Web server | LiteSpeed, fronted by openresty |
| PHP | 8.2.33 — every extension the framework needs, **no disabled functions** |
| SSH | **none.** Port 22 is refused, on both A records |
| Transport | FTPS only (Pure-FTPd, explicit TLS) |
| Database | SQLite |

No SSH means nothing can be run on the server. Everything a deploy normally
does there — `composer install`, the asset build, `config:cache` — happens
before upload, or through one short-lived PHP script fetched over HTTPS.

SQLite rather than MySQL because a database and user cannot be provisioned
without cPanel API access, which the deployment credentials do not carry.
`pdo_sqlite` is present (probed). For a site that is overwhelmingly cached
reads this is a sound choice rather than a compromise. Moving to MySQL later
is four lines of `.env` and a re-run of `mru:build-sqlite`'s counterpart.

## Layout

    /home/mru/
      laravel/            framework, .env, vendor, storage, database/database.sqlite
      public_html/        document root for mru.ac.ug — public/ with index.php repointed
        storage  ->  ../laravel/storage/app/public   (symlink)

Nothing outside those two paths is written. **The account also serves live
systems on subdomains** — `eportal`, `elearning`, `apply`, `eadmissions`,
`omnipass`, and `eadmin`, which is the API the MRU mobile app calls. A deploy
that touched them would take the university's student systems down.

`public_html_old` holds whatever was there before; `public_html` itself was
empty when this went out.

## Deploying

    export FTP_HOST=… FTP_USER=… FTP_PASS=…      # never committed
    scripts/deploy/build-release.sh              # release/
    cp .env.production.example release/laravel/.env   # then fill it in
    php release/laravel/artisan key:generate --force
    scripts/deploy/deploy.sh

`deploy.sh` zips the release, uploads three archives over FTPS, drops a
`setup.php` carrying a freshly generated token, calls it over HTTPS to unpack,
symlink and warm the caches, then smoke-tests ten URLs and removes the upload
directory. `setup.php` deletes itself on success and refuses to run without its
token, so a leftover copy is inert.

## Three bugs this found, all by testing rather than assuming

**The front controller escaped the document root.** `public/.htaccess` sends
requests to `../index.php`, which suits the layout the platform this codebase
came from is deployed under. Here the document root *is* `public_html`, so
Apache answered **400** to every path except `/` — while `/index.php/about`
returned 200, which is what said Laravel was fine and the rewrite was not. The
build now rewrites the shipped copy and asserts both that the rule is present
and that no `../index.php` survived.

**`public_path()` pointed at nothing.** In a split layout it resolves to
`~/laravel/public`, which does not exist, so `filemtime()` in three layouts
fatal'd every page. `index.php` now calls `usePublicPath()`, and
`App\Support\Asset::versioned()` degrades to an unversioned URL rather than a
500 when a file is missing.

**`rsync --exclude 'public'` is unanchored.** It also matched
`storage/app/public`, so the first release shipped with no images or PDFs at
all. Anchored to `/public`.

A fourth was caught before it shipped: composer resolved against this laptop's
PHP 8.3 while the server runs 8.2.33. The platform is now pinned.

## Verified live

33 of 34 public URLs return 200; the 34th is `/jobs`, an intended 301 to
`/vacancies`. Every image loads — 41 of 41 on `/gallery`, none broken.
Documents download. `sitemap.xml` carries 233 URLs on the right host.
`/dashboard` redirects to login. The commercial pages stay 404 behind
`FEATURE_COMMERCE=false`. `.env` is unreachable, the SQLite file 404s, and both
`setup.php` and the capability probe are gone.

## Open, and needing a human

1. **MySQL.** Worth moving to when someone has the cPanel password — SQLite's
   weak spot here is concurrent writes, and analytics writes on every request.
2. **Backups.** Nothing backs up `laravel/database/database.sqlite`. It holds
   every content edit made through the admin. This is the most urgent item.
3. **Mail.** `MAIL_*` is unset, so the contact form stores messages but sends
   no notification.
4. **gzip.** Still not enabled at the server; unchanged from the earlier audit.

## The classmap trap

The release is built with `composer install --classmap-authoritative`, which tells the autoloader
the classmap is the whole truth: it will not fall back to searching the filesystem. Editing an
existing class and uploading it works. **Adding a new one does not** — the class stays unloadable,
and every page that touches it returns 500.

Shipping the ODEL section this way took the entire site down. `routes/web.php` referenced
`App\Support\Odel` at registration time, so the failure was not confined to `/odel`: route loading
threw on every request, and the main site went with it. Recovery was two minutes once the cause was
clear, but the cause is invisible from the symptom.

`scripts/deploy/refresh-autoload.sh` regenerates the classmap and uploads it. Run it after any
deploy that adds a class, before rebuilding the caches. A full `deploy.sh` does not need it — the
classmap is generated as part of the build.
