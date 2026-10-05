#!/usr/bin/env bash
# Enable instant report verification on live using the endpoint and secret
# already configured on staging. Values are read through WordPress, written to
# a private config file outside the web root, and never printed.
set -Eeuo pipefail
STG=/home/envitechal/staging.envitechal.com
LIVE=/home/envitechal/public_html
CFG=/home/envitechal/eta-verify-config.php
WPC="$LIVE/wp-config.php"
if grep -q "eta-verify-config.php" "$WPC"; then echo "Live wp-config already loads $CFG; no change."; exit 0; fi
umask 077
CFG_OUT="$CFG" wp --path=$STG eval '
  if (!defined("ETA_VERIFY_ENDPOINT") || !ETA_VERIFY_ENDPOINT || !defined("ETA_VERIFY_SECRET") || !ETA_VERIFY_SECRET) { fwrite(STDERR, "STOP: staging has no endpoint/secret\n"); exit(1); }
  $out = "<?php\n// Report verification settings for envitechal.com (copied from staging " . gmdate("d-m-Y") . ").\n"
       . "if (!defined(\"ETA_VERIFY_ENDPOINT\")) { define(\"ETA_VERIFY_ENDPOINT\", " . var_export(ETA_VERIFY_ENDPOINT, true) . "); }\n"
       . "if (!defined(\"ETA_VERIFY_SECRET\")) { define(\"ETA_VERIFY_SECRET\", " . var_export(ETA_VERIFY_SECRET, true) . "); }\n";
  file_put_contents(getenv("CFG_OUT"), $out); chmod(getenv("CFG_OUT"), 0600); echo "config file written\n";
' --skip-plugins --skip-themes
php -l "$CFG" >/dev/null || { echo "STOP: generated config invalid"; rm -f "$CFG"; exit 1; }
BK="$HOME/backups/envitechal-production/wp-config-before-$(date -u +%Y%m%dT%H%M%SZ).php"; cp -p "$WPC" "$BK"
php -r '
$f=$argv[1]; $s=file_get_contents($f);
$line="\n/* Report verification (instant check) */\nif (file_exists(\"/home/envitechal/eta-verify-config.php\")) { require_once \"/home/envitechal/eta-verify-config.php\"; }\n\n";
$pos=strpos($s,"/* That'"'"'s all, stop editing!"); if($pos===false){$pos=strpos($s,"require_once ABSPATH");}
if($pos===false){fwrite(STDERR,"STOP: insertion point not found\n");exit(1);}
file_put_contents($f, substr($s,0,$pos).$line.substr($s,$pos));' "$WPC"
php -l "$WPC" >/dev/null || { cp -p "$BK" "$WPC"; echo "STOP: syntax check failed, restored backup"; exit 1; }
wp --path=$LIVE eval 'echo (function_exists("eta_verify_is_active") && eta_verify_is_active()) ? "Instant verification ACTIVE on live\n" : "WARNING: not active\n";'
wp --path=$LIVE litespeed-purge all 2>&1 | tail -1
