#!/usr/bin/env bash
# Regenerates the production autoloader and uploads it.
#
# Run this after any deploy that adds a NEW class, before rebuilding the caches.
#
# The release is built with `composer install --classmap-authoritative`, which
# tells the autoloader that the classmap is the whole truth: it will not fall
# back to searching the filesystem. Uploading a new class file therefore does
# nothing on its own — the class is still unloadable, and every page that
# touches it returns 500. Editing an existing class is fine; adding one is not.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
REL="${1:-$ROOT/release}"
PHP="${PHP_BIN:-/Applications/MAMP/bin/php/php8.3.9/bin/php}"
COMPOSER="${COMPOSER_BIN:-/usr/local/bin/composer}"

: "${FTP_HOST:?set FTP_HOST}"; : "${FTP_USER:?set FTP_USER}"; : "${FTP_PASS:?set FTP_PASS}"

echo "==> syncing app/, routes/ and resources/ into the release tree"
for d in app routes resources config database; do
  [ -d "$ROOT/$d" ] && rsync -a --delete "$ROOT/$d/" "$REL/laravel/$d/"
done

echo "==> regenerating the classmap"
( cd "$REL/laravel" && "$PHP" "$COMPOSER" dump-autoload \
    --no-dev --optimize --classmap-authoritative --no-interaction 2>&1 | tail -2 )

echo "==> uploading vendor/composer"
for f in autoload_classmap.php autoload_static.php autoload_files.php \
         autoload_namespaces.php autoload_psr4.php autoload_real.php installed.php; do
  [ -f "$REL/laravel/vendor/composer/$f" ] || continue
  curl -sS --ssl-reqd --ftp-create-dirs -u "$FTP_USER:$FTP_PASS" \
       -T "$REL/laravel/vendor/composer/$f" "ftp://$FTP_HOST/laravel/vendor/composer/$f" >/dev/null
  echo "    $f"
done

echo "==> done. Now rebuild the caches on the server."
