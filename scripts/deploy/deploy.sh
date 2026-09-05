#!/usr/bin/env bash
# End-to-end deploy of the university site to mru.ac.ug.
#
# The host is cPanel/LiteSpeed shared hosting with **no SSH** (port 22 is
# refused) and no cPanel API access, so this is FTPS the whole way, with one
# short-lived PHP script run over HTTPS to do the work that would otherwise be
# `php artisan` on the server.
#
# The account also serves live systems on subdomains — eportal, eadmin (the API
# the mobile app calls), elearning, apply, eadmissions, omnipass. This script
# writes to exactly two paths and never touches those:
#
#   /home/mru/public_html   the document root for mru.ac.ug
#   /home/mru/laravel       the framework, .env, database and storage
#
# Credentials come from the environment:
#   FTP_HOST FTP_USER FTP_PASS
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
REL="$ROOT/release"
STAGE="${STAGE:-$ROOT/.deploy-stage}"
SITE="${SITE_URL:-https://mru.ac.ug}"

: "${FTP_HOST:?set FTP_HOST}"
: "${FTP_USER:?set FTP_USER}"
: "${FTP_PASS:?set FTP_PASS}"

step() { printf '\n\033[1m==> %s\033[0m\n' "$*"; }

lftp_do() {
  lftp -c "
    set ftp:ssl-force true; set ftp:ssl-protect-data true;
    set ssl:verify-certificate no; set net:max-retries 3; set net:timeout 30;
    set xfer:clobber on;
    open -u '$FTP_USER','$FTP_PASS' '$FTP_HOST';
    $1
  "
}

step "1/6  archives"
rm -rf "$STAGE"; mkdir -p "$STAGE"
( cd "$REL/laravel"     && zip -qr "$STAGE/laravel.zip" . -x 'storage/app/public/*' )
( cd "$REL/public_html" && zip -qr "$STAGE/public.zip"  . )
( cd "$REL/laravel/storage/app/public" && zip -qr "$STAGE/storage.zip" . )
ls -lh "$STAGE" | awk 'NR>1{printf "    %-14s %s\n",$9,$5}'

step "2/6  upload"
lftp_do "
  mkdir -p /_upload;
  put -O /_upload '$STAGE/laravel.zip';
  put -O /_upload '$STAGE/public.zip';
  put -O /_upload '$STAGE/storage.zip';
  cls -l /_upload;
"

step "3/6  setup script"
TOKEN="$(openssl rand -hex 16)"
sed "s/__TOKEN__/$TOKEN/" "$ROOT/scripts/deploy/setup.php" > "$STAGE/setup.php"
lftp_do "put -O /public_html '$STAGE/setup.php';"

step "4/6  unpack + link + cache (over HTTPS)"
curl -sS --max-time 900 "$SITE/setup.php?t=$TOKEN" | sed 's/^/    /'

step "5/6  smoke test"
fail=0
for u in / /about /programmes /faculties /news /gallery /admissions /almanac /contact /sports; do
  code=$(curl -s -o /dev/null -w '%{http_code}' --max-time 45 "$SITE$u")
  printf '    %-14s %s\n' "$u" "$code"
  [ "$code" = "200" ] || fail=1
done

step "6/6  tidy"
lftp_do "rm -rf /_upload;" || true
rm -rf "$STAGE"

if [ "$fail" = 0 ]; then
  printf '\n\033[32mDeployed. %s is live.\033[0m\n' "$SITE"
else
  printf '\n\033[31mDeployed with failures — see the codes above.\033[0m\n'
  exit 1
fi
