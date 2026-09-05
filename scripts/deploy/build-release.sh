#!/usr/bin/env bash
# Builds a deployable release of the university site.
#
# The target is cPanel shared hosting (Byethost/iFastNet) with **no SSH**:
# port 22 is refused, so nothing can be run on the server. Everything that
# normally happens during a deploy — composer install, the asset build, the
# config/route/view caches — therefore has to happen here, and the result is
# shipped as finished files.
#
# Layout produced, matching the standard cPanel split so that the framework,
# the .env and storage/ are never inside the document root:
#
#   release/laravel/       app, config, routes, vendor, storage, artisan, .env
#   release/public_html/   the contents of public/, with index.php repointed
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
OUT="${1:-$ROOT/release}"
PHP="${PHP_BIN:-/Applications/MAMP/bin/php/php8.3.9/bin/php}"
COMPOSER="${COMPOSER_BIN:-/usr/local/bin/composer}"

echo "==> building release into $OUT"
rm -rf "$OUT"
mkdir -p "$OUT/laravel" "$OUT/public_html"

echo "==> frontend assets"
( cd "$ROOT" && npm run build >/dev/null )

echo "==> application files"
# Everything the framework needs, and nothing that only matters on a laptop.
rsync -a --delete \
  --exclude '.git' --exclude '.github' --exclude 'node_modules' \
  --exclude 'tests' --exclude 'release' --exclude '.env' \
  --exclude 'storage/app/image-originals' \
  --exclude 'storage/logs/*' \
  `# Local-only bulk that must never reach a public server. storage/app/private` \
  `# is 4.3GB of the LMS's private uploads, which the university domain does` \
  `# not serve at all (FEATURE_COMMERCE=false); _db_seed holds a full database` \
  `# dump and an owner password hash; storage/debugbar is 135MB of profiler` \
  `# output. Left in, the first build was 4.6GB.` \
  --exclude 'storage/app/private' \
  --exclude 'storage/debugbar' \
  --exclude '_db_seed' \
  --exclude '_deploy-oneoffs' \
  --exclude 'new-temp' \
  --exclude 'release' \
  --exclude '*.sql' \
  --exclude '.env.*' \
  --exclude 'storage/framework/cache/data/*' \
  --exclude 'storage/framework/sessions/*' \
  --exclude 'storage/framework/views/*' \
  `# Anchored to the transfer root. Unanchored, 'public' also matched` \
  `# storage/app/public, so the release shipped with no images or PDFs at all` \
  `# and every one of them 404'd.` \
  --exclude '/public' \
  --exclude 'storage/app/prod' \
  --exclude 'phpunit.xml' --exclude 'ssh-credentials.txt' \
  "$ROOT/./" "$OUT/laravel/"

echo "==> document root"
rsync -a "$ROOT/public/./" "$OUT/public_html/"

echo "==> production dependencies (no dev)"
# The server runs PHP 8.2.33; this laptop runs 8.3.9. Without pinning the
# platform, composer is free to resolve a dependency that requires 8.3 and the
# release then fatals on the first request.
( cd "$OUT/laravel" \
  && "$PHP" "$COMPOSER" config platform.php 8.2.33 >/dev/null \
  && "$PHP" "$COMPOSER" install \
    --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --classmap-authoritative >/dev/null )

# public/.htaccess sends requests to `../index.php`, which suits the layout the
# platform this codebase came from is deployed under. Here the document root IS
# public_html and index.php sits beside .htaccess, so `../index.php` escapes the
# root and Apache answers 400 Bad Request to every path except `/`. The local
# file is left alone; only the shipped copy is corrected.
echo "==> correcting the rewrite target for a public_html document root"
sed -i.bak 's|RewriteRule \^ \.\./index\.php \[L\]|RewriteRule ^ index.php [L]|' "$OUT/public_html/.htaccess"
rm -f "$OUT/public_html/.htaccess.bak"
grep -q 'RewriteRule \^ index\.php \[L\]' "$OUT/public_html/.htaccess" \
  || { echo "    !! front-controller rule missing from .htaccess" >&2; exit 1; }
grep -q '\.\./index\.php' "$OUT/public_html/.htaccess" \
  && { echo "    !! an ../index.php rule survived" >&2; exit 1; }
echo "    .htaccess front controller -> index.php"

echo "==> repointing index.php at ../laravel"
python3 - "$OUT/public_html/index.php" <<'PY'
import sys, re
p = sys.argv[1]
s = open(p).read()
s = s.replace("__DIR__.'/../vendor/autoload.php'", "__DIR__.'/../laravel/vendor/autoload.php'")
s = s.replace("__DIR__.'/../bootstrap/app.php'",   "__DIR__.'/../laravel/bootstrap/app.php'")
s = s.replace("__DIR__.'/../storage/framework/maintenance.php'",
              "__DIR__.'/../laravel/storage/framework/maintenance.php'")

# The framework is not above the document root here, so public_path() would
# resolve to ~/laravel/public, which does not exist. Anything that stats a
# file under the document root — asset versioning, uploads — needs this.
s = s.replace("$app = require_once __DIR__.'/../laravel/bootstrap/app.php';",
              "$app = require_once __DIR__.'/../laravel/bootstrap/app.php';\n\n"
              "// The document root is this directory, not ~/laravel/public.\n"
              "$app->usePublicPath(__DIR__);")
assert '$app->usePublicPath(__DIR__);' in s, "public path was not set"
assert '/../laravel/vendor/autoload.php' in s, "autoload path was not rewritten"
assert '/../laravel/bootstrap/app.php'   in s, "bootstrap path was not rewritten"
open(p, 'w').write(s)
print("    index.php rewritten")
PY

# public_html/storage is a symlink to ../laravel/storage/app/public, created
# on the server by setup.php. symlink() is available there (probed), so the
# 132MB of published files are uploaded once, not twice.
echo "==> public_html/storage will be symlinked on the server, not copied"
rm -rf "$OUT/public_html/storage"

echo "==> writable directories"
mkdir -p "$OUT/laravel/storage/framework/"{cache/data,sessions,views} \
         "$OUT/laravel/storage/logs" "$OUT/laravel/bootstrap/cache"
chmod -R 775 "$OUT/laravel/storage" "$OUT/laravel/bootstrap/cache"

printf '==> done\n    laravel     %s\n    public_html %s\n' \
  "$(du -sh "$OUT/laravel" | cut -f1)" "$(du -sh "$OUT/public_html" | cut -f1)"
echo
echo "Next: copy .env.production.example to $OUT/laravel/.env, fill in the database"
echo "credentials and APP_KEY, then run scripts/deploy/push-ftps.sh"
