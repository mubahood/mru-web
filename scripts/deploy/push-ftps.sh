#!/usr/bin/env bash
# Uploads a built release to the live host over FTPS.
#
# This host has no SSH — port 22 is refused — so FTP is the only transport.
# It is always FTPS (explicit TLS, --ssl-reqd): plain FTP would put the
# account password on the wire in clear text on every connection.
#
# Credentials come from the environment, never from this file:
#   FTP_HOST  FTP_USER  FTP_PASS   (and optionally FTP_BASE, default /)
#
# Usage:
#   FTP_HOST=… FTP_USER=… FTP_PASS=… scripts/deploy/push-ftps.sh [release-dir]
#   … --dry-run     list what would be sent, send nothing
#   … --only=public_html|laravel
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
REL="$ROOT/release"
DRY=0
ONLY=""

for a in "$@"; do
  case "$a" in
    --dry-run) DRY=1 ;;
    --only=*)  ONLY="${a#--only=}" ;;
    -*)        echo "unknown flag: $a" >&2; exit 2 ;;
    *)         REL="$a" ;;
  esac
done

: "${FTP_HOST:?set FTP_HOST}"
: "${FTP_USER:?set FTP_USER}"
: "${FTP_PASS:?set FTP_PASS}"
FTP_BASE="${FTP_BASE:-/}"

[ -d "$REL/public_html" ] || { echo "no release at $REL — run build-release.sh first" >&2; exit 1; }

if command -v lftp >/dev/null 2>&1; then
  echo "==> lftp mirror over FTPS to $FTP_HOST$FTP_BASE"
  for dir in laravel public_html; do
    [ -n "$ONLY" ] && [ "$ONLY" != "$dir" ] && continue
    echo "    $dir"
    lftp -c "
      set ftp:ssl-force true;
      set ftp:ssl-protect-data true;
      set ssl:verify-certificate no;
      set net:max-retries 3;
      set net:timeout 20;
      open -u '$FTP_USER','$FTP_PASS' '$FTP_HOST';
      mirror -R $( [ $DRY = 1 ] && echo --dry-run ) --parallel=4 --verbose=1 \
             --exclude-glob .DS_Store \
             '$REL/$dir' '$FTP_BASE$dir';
    "
  done
  exit 0
fi

# No lftp: fall back to curl, one file at a time. Slower, but curl is
# everywhere and this still speaks TLS.
echo "==> lftp not installed; using curl (slower). brew install lftp for a faster deploy."
cd "$REL"
find laravel public_html -type f ! -name '.DS_Store' | while read -r f; do
  [ -n "$ONLY" ] && case "$f" in "$ONLY"/*) ;; *) continue;; esac
  if [ "$DRY" = 1 ]; then echo "    would send $f"; continue; fi
  curl -sS --ssl-reqd --ftp-create-dirs -u "$FTP_USER:$FTP_PASS" \
       -T "$f" "ftp://$FTP_HOST$FTP_BASE$f" || echo "    FAILED $f" >&2
done
echo "==> done"
