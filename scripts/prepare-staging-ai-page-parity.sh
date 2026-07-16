#!/usr/bin/env bash

set -Eeuo pipefail

STAGING_HOST="staging.envitechal.com"
PRODUCTION_HOST="envitechal.com"

stop()
{
    printf 'STOP: %s\n' "$*" >&2
    exit 1
}

get_docroot()
{
    local domain="$1"

    uapi --output=json DomainInfo single_domain_data domain="$domain" |
        "$PHP_BIN" -r '
            $json = json_decode(stream_get_contents(STDIN), true);
            if (!is_array($json) || (int) ($json["result"]["status"] ?? 0) !== 1) {
                fwrite(STDERR, "cPanel could not return the document root.\n");
                exit(1);
            }

            $root = rtrim((string) ($json["result"]["data"]["documentroot"] ?? ""), "/");
            if ($root === "" || $root[0] !== "/") {
                fwrite(STDERR, "The cPanel document root was invalid.\n");
                exit(1);
            }

            echo $root;
        '
}

url_host()
{
    "$PHP_BIN" -r '
        $url = trim(stream_get_contents(STDIN));
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === "") {
            fwrite(STDERR, "A WordPress URL did not contain a valid host.\n");
            exit(1);
        }
        echo strtolower($host);
    '
}

command -v uapi >/dev/null || stop "cPanel UAPI was not found."
PHP_BIN="$(command -v php || true)"
WP_BIN="$(command -v wp || true)"
test -x "$PHP_BIN" || stop "PHP CLI was not found."
test -x "$WP_BIN" || stop "WP-CLI was not found."

HOME_REAL="$(realpath -e "$HOME")"
STAGING_ROOT_RAW="$(get_docroot "$STAGING_HOST")"
PRODUCTION_ROOT_RAW="$(get_docroot "$PRODUCTION_HOST")"
STAGING_ROOT="$(realpath -e "$STAGING_ROOT_RAW")"
PRODUCTION_ROOT="$(realpath -e "$PRODUCTION_ROOT_RAW")"

[[ "$STAGING_ROOT_RAW" == "$STAGING_ROOT" ]] || stop "the staging document root is symlinked or non-canonical."
[[ "$PRODUCTION_ROOT_RAW" == "$PRODUCTION_ROOT" ]] || stop "the production document root is symlinked or non-canonical."
[[ "$STAGING_ROOT" == "$HOME_REAL/"* ]] || stop "the staging document root is outside this cPanel account."
[[ "$STAGING_ROOT" != "$PRODUCTION_ROOT" ]] || stop "staging and production resolve to the same directory."
case "$STAGING_ROOT" in
    "$PRODUCTION_ROOT" | "$PRODUCTION_ROOT"/*) stop "staging resolves inside production." ;;
esac
case "$PRODUCTION_ROOT" in
    "$STAGING_ROOT" | "$STAGING_ROOT"/*) stop "production resolves inside staging." ;;
esac

test -f "$STAGING_ROOT/wp-load.php" || stop "the staging WordPress root was not confirmed."
test -f "$PRODUCTION_ROOT/wp-load.php" || stop "the production WordPress root was not confirmed."

STAGING_HOME="$("$WP_BIN" --path="$STAGING_ROOT" option get home)"
STAGING_SITEURL="$("$WP_BIN" --path="$STAGING_ROOT" option get siteurl)"
[[ "$(printf '%s' "$STAGING_HOME" | url_host)" == "$STAGING_HOST" ]] || stop "staging home does not use $STAGING_HOST."
[[ "$(printf '%s' "$STAGING_SITEURL" | url_host)" == "$STAGING_HOST" ]] || stop "staging siteurl does not use $STAGING_HOST."

STAGING_DB="$("$WP_BIN" --path="$STAGING_ROOT" db query 'SELECT DATABASE()' --skip-column-names | tail -n 1 | tr -d '\r')"
PRODUCTION_DB="$("$WP_BIN" --path="$PRODUCTION_ROOT" db query 'SELECT DATABASE()' --skip-column-names | tail -n 1 | tr -d '\r')"
STAGING_PREFIX="$("$WP_BIN" --path="$STAGING_ROOT" db prefix)"
PRODUCTION_PREFIX="$("$WP_BIN" --path="$PRODUCTION_ROOT" db prefix)"
test -n "$STAGING_DB" && test -n "$PRODUCTION_DB" || stop "a database identity was empty."
[[ "$STAGING_DB:$STAGING_PREFIX" != "$PRODUCTION_DB:$PRODUCTION_PREFIX" ]] ||
    stop "staging and production use the same database and table prefix."

WPS=("$WP_BIN" "--path=$STAGING_ROOT" "--url=$STAGING_HOME")
PAGE_SPECS=(
    "accreditations-certifications|Accreditations & Certifications"
    "karachi-environmental-lab|Karachi Environmental Laboratory"
    "environmental-testing-faqs-pakistan|Environmental Testing FAQs Pakistan"
)

declare -A PAGE_IDS=()
declare -A PAGE_ACTIONS=()

echo "Preflighting staging-only page records..."
for spec in "${PAGE_SPECS[@]}"; do
    IFS='|' read -r slug title <<<"$spec"
    active_ids="$("${WPS[@]}" post list --post_type=page --post_parent=0 --post_status=any --pagename="$slug" --format=ids)"
    trash_ids="$("${WPS[@]}" post list --post_type=page --post_parent=0 --post_status=trash --pagename="$slug" --format=ids)"
    read -r -a matches <<<"$active_ids $trash_ids"

    if ((${#matches[@]} > 1)); then
        stop "more than one staging page matched /$slug/."
    fi

    if ((${#matches[@]} == 1)); then
        page_id="${matches[0]}"
        [[ "$page_id" =~ ^[0-9]+$ ]] || stop "unexpected page ID for /$slug/: $page_id"
        page_status="$("${WPS[@]}" post get "$page_id" --field=post_status)"
        [[ "$page_status" == "publish" ]] || stop "/$slug/ exists with status '$page_status'; it was not changed."
        PAGE_IDS["$slug"]="$page_id"
        PAGE_ACTIONS["$slug"]="reused existing"
    else
        PAGE_IDS["$slug"]=""
        PAGE_ACTIONS["$slug"]="create"
    fi
done

echo "Creating only missing staging page records..."
for spec in "${PAGE_SPECS[@]}"; do
    IFS='|' read -r slug title <<<"$spec"
    if [[ "${PAGE_ACTIONS[$slug]}" == "create" ]]; then
        page_id="$("${WPS[@]}" post create \
            --post_type=page \
            --post_parent=0 \
            --post_status=publish \
            --post_title="$title" \
            --post_name="$slug" \
            --post_content='' \
            --porcelain)"
        [[ "$page_id" =~ ^[0-9]+$ ]] || stop "WP-CLI returned an invalid page ID for /$slug/: $page_id"
        PAGE_IDS["$slug"]="$page_id"
        PAGE_ACTIONS["$slug"]="created"
    fi

    page_id="${PAGE_IDS[$slug]}"
    [[ "$("${WPS[@]}" post get "$page_id" --field=post_type)" == "page" ]] || stop "ID $page_id is not a page."
    [[ "$("${WPS[@]}" post get "$page_id" --field=post_status)" == "publish" ]] || stop "ID $page_id is not published."
    [[ "$("${WPS[@]}" post get "$page_id" --field=post_name)" == "$slug" ]] || stop "ID $page_id has an unexpected slug."
done

printf '\nSTAGING AI PAGE PARITY READY\n'
printf 'Staging root: %s\n' "$STAGING_ROOT"
for spec in "${PAGE_SPECS[@]}"; do
    IFS='|' read -r slug title <<<"$spec"
    printf '%s: %s (ID %s) — %s/%s/\n' \
        "$title" "${PAGE_ACTIONS[$slug]}" "${PAGE_IDS[$slug]}" "${STAGING_HOME%/}" "$slug"
done
printf 'Next: run scripts/deploy-staging-theme.sh to install the pinned candidate and purge staging caches.\n'
