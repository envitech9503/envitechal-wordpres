#!/usr/bin/env bash
# Copy the report-verification endpoint and shared secret defines from the
# staging wp-config.php into the live wp-config.php, server-side only.
# The secret is never printed. A timestamped backup of live wp-config.php is kept.
set -Eeuo pipefail
STG=/home/envitechal/staging.envitechal.com/wp-config.php
LIVE=/home/envitechal/public_html/wp-config.php
test -f "$STG" && test -f "$LIVE" || { echo "STOP: wp-config.php not found"; exit 1; }
LINES="$(grep -E "^[[:space:]]*define\([[:space:]]*'ETA_VERIFY_(ENDPOINT|SECRET)'" "$STG" || true)"
[[ "$(printf '%s\n' "$LINES" | grep -c ETA_VERIFY_)" -eq 2 ]] || { echo "STOP: staging does not define both ETA_VERIFY_ENDPOINT and ETA_VERIFY_SECRET"; exit 1; }
if grep -qE "ETA_VERIFY_(ENDPOINT|SECRET)" "$LIVE"; then echo "Live wp-config already defines ETA_VERIFY_*; no change."; exit 0; fi
BK="$HOME/backups/envitechal-production/wp-config-before-$(date -u +%Y%m%dT%H%M%SZ).php"
cp -p "$LIVE" "$BK"; chmod 600 "$BK"
php -r '
$f=$argv[1]; $add=$argv[2]; $s=file_get_contents($f);
$marker="/* That'"'"'s all, stop editing!";
$pos=strpos($s,$marker);
if($pos===false){ $pos=strpos($s,"require_once ABSPATH"); }
if($pos===false){ fwrite(STDERR,"STOP: insertion point not found\n"); exit(1); }
$s=substr($s,0,$pos)."/* Report verification (copied from staging ".gmdate("d-m-Y").") */\n".$add."\n\n".substr($s,$pos);
file_put_contents($f,$s);' "$LIVE" "$LINES"
php -l "$LIVE" >/dev/null || { cp -p "$BK" "$LIVE"; echo "STOP: syntax check failed, restored backup"; exit 1; }
echo "Instant verification configured on live (backup: $BK)"
