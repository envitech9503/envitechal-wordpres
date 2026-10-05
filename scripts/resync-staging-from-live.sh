#!/usr/bin/env bash
# Refresh staging's database and uploads from production. Production is only read.
set -Eeuo pipefail
LIVE=/home/envitechal/public_html
STG=/home/envitechal/staging.envitechal.com
BK="$HOME/backups/envitechal-staging"; mkdir -p "$BK"; chmod 700 "$BK"
STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
[[ "$(wp --path=$STG option get home)" == "https://staging.envitechal.com" ]] || { echo "STOP: staging home URL unexpected"; exit 1; }
[[ "$(wp --path=$LIVE option get home)" == "https://envitechal.com" ]] || { echo "STOP: live home URL unexpected"; exit 1; }
wp --path=$STG db export "$BK/staging-db-before-resync-$STAMP.sql" >/dev/null && echo "staging DB backed up: $BK/staging-db-before-resync-$STAMP.sql"
wp --path=$LIVE db export "$BK/live-db-for-resync-$STAMP.sql" >/dev/null && echo "live DB exported"
wp --path=$STG db import "$BK/live-db-for-resync-$STAMP.sql" >/dev/null && echo "live DB imported into staging"
wp --path=$STG search-replace 'https://envitechal.com' 'https://staging.envitechal.com' --all-tables-with-prefix --skip-columns=guid --precise --report-changed-only --format=count | sed 's/^/https replacements: /'
wp --path=$STG search-replace 'https://www.envitechal.com' 'https://staging.envitechal.com' --all-tables-with-prefix --skip-columns=guid --precise --format=count | sed 's/^/www replacements: /'
wp --path=$STG option update blog_public 0 >/dev/null && echo "staging set to discourage search engines"
rsync -a --ignore-existing "$LIVE/wp-content/uploads/" "$STG/wp-content/uploads/" && echo "new uploads copied to staging"
wp --path=$STG cache flush >/dev/null; wp --path=$STG litespeed-purge all 2>&1 | tail -1
echo "home=$(wp --path=$STG option get home) posts=$(wp --path=$STG post list --post_type=post --post_status=publish --format=count) pages=$(wp --path=$STG post list --post_type=page --post_status=publish --format=count)"
echo RESYNCED
