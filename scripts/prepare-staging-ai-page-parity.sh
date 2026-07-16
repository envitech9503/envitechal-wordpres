#!/usr/bin/env bash

set -Eeuo pipefail
umask 077

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

wordpress_database_identity()
{
    local root="$1"
    local output
    local -a matches=()

    if ! output="$("$WP_BIN" --path="$root" --skip-plugins --skip-themes eval '
        global $wpdb;
        if (!is_object($wpdb) || !isset($wpdb->prefix)) {
            fwrite(STDERR, "WordPress database identity is unavailable.\n");
            exit(1);
        }

        $database = (string) $wpdb->get_var("SELECT DATABASE()");
        $prefix = (string) $wpdb->prefix;
        if ($database === "" || $prefix === "") {
            fwrite(STDERR, "WordPress database identity is empty.\n");
            exit(1);
        }

        echo "\nETA_DB_IDENTITY:" . hash("sha256", $database . "\0" . $prefix) . "\n";
    ' 2>&1)"; then
        stop "WordPress could not calculate a database identity for $root."
    fi

    mapfile -t matches < <(
        printf '%s\n' "$output" |
            tr -d '\r' |
            sed -nE 's/^ETA_DB_IDENTITY:([0-9a-f]{64})$/\1/p'
    )
    ((${#matches[@]} == 1)) || stop "WordPress returned an invalid database identity for $root."
    printf '%s' "${matches[0]}"
}

ensure_private_directory()
{
    local directory="$1"

    if [[ -L "$directory" ]]; then
        stop "$directory is a symlink."
    fi
    if [[ -e "$directory" && ! -d "$directory" ]]; then
        stop "$directory exists but is not a directory."
    fi
    if [[ ! -e "$directory" ]]; then
        mkdir -m 0700 -- "$directory" || stop "could not create private directory $directory."
    fi
    [[ ! -L "$directory" && -d "$directory" ]] || stop "$directory is not a private directory."
    [[ "$(realpath -e "$directory")" == "$directory" ]] || stop "$directory is symlinked or non-canonical."
    chmod 0700 -- "$directory"
}

command -v uapi >/dev/null || stop "cPanel UAPI was not found."
command -v flock >/dev/null || stop "flock was not found."
PHP_BIN="$(command -v php || true)"
WP_BIN="$(command -v wp || true)"
test -x "$PHP_BIN" || stop "PHP CLI was not found."
test -x "$WP_BIN" || stop "WP-CLI was not found."

HOME_REAL="$(realpath -e "$HOME")"
BACKUP_ROOT="$HOME_REAL/backups"
LOCK_DIR="$BACKUP_ROOT/envitechal-ai-visibility"
ensure_private_directory "$BACKUP_ROOT"
ensure_private_directory "$LOCK_DIR"
exec 9<"$LOCK_DIR"
flock -n 9 || stop "another staging page-parity run is already active."

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

STAGING_DB_ID="$(wordpress_database_identity "$STAGING_ROOT")"
PRODUCTION_DB_ID="$(wordpress_database_identity "$PRODUCTION_ROOT")"
[[ "$STAGING_DB_ID" != "$PRODUCTION_DB_ID" ]] ||
    stop "staging and production use the same database and table prefix."

WPS=("$WP_BIN" "--path=$STAGING_ROOT" "--url=$STAGING_HOME")
PAGE_SPECS=(
    "accreditations-certifications|Accreditations & Certifications"
    "karachi-environmental-lab|Karachi Environmental Laboratory"
    "environmental-testing-faqs-pakistan|Environmental Testing FAQs Pakistan"
)

declare -A PAGE_IDS=()
declare -A PAGE_ACTIONS=()
declare -A SEEN_IDS=()
declare -a CREATED_IDS=()
COMMITTED=0

cleanup_created_pages()
{
    local exit_code=$?
    local cleanup_failed=0
    local created_id

    trap - EXIT INT TERM
    if ((COMMITTED == 0 && ${#CREATED_IDS[@]} > 0)); then
        printf 'Rolling back %d page record(s) created by this run...\n' "${#CREATED_IDS[@]}" >&2
        set +e
        for created_id in "${CREATED_IDS[@]}"; do
            if [[ ! "$created_id" =~ ^[0-9]+$ ]] || ! "${WPS[@]}" post delete "$created_id" --force; then
                printf 'CRITICAL: failed to remove staging page ID %s; inspect staging immediately.\n' "$created_id" >&2
                cleanup_failed=1
            fi
        done
        set -e
    fi

    if ((cleanup_failed != 0)); then
        exit_code=1
    fi
    exit "$exit_code"
}

trap cleanup_created_pages EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

echo "Preflighting staging-only page records..."
for spec in "${PAGE_SPECS[@]}"; do
    IFS='|' read -r slug title <<<"$spec"
    active_ids="$("${WPS[@]}" post list --post_type=page --post_parent=0 --post_status=any --pagename="$slug" --format=ids)"
    trash_ids="$("${WPS[@]}" post list --post_type=page --post_parent=0 --post_status=trash --pagename="$slug" --format=ids)"
    auto_draft_ids="$("${WPS[@]}" post list --post_type=page --post_parent=0 --post_status=auto-draft --pagename="$slug" --format=ids)"
    attachment_ids="$("${WPS[@]}" post list --post_type=attachment --post_parent=0 --post_status=inherit --name="$slug" --format=ids)"

    SEEN_IDS=()
    matches=()
    for candidate_id in $active_ids $trash_ids $auto_draft_ids $attachment_ids; do
        [[ "$candidate_id" =~ ^[0-9]+$ ]] || stop "unexpected collision ID for /$slug/: $candidate_id"
        if [[ -z "${SEEN_IDS[$candidate_id]+present}" ]]; then
            SEEN_IDS["$candidate_id"]=1
            matches+=("$candidate_id")
        fi
    done

    if ((${#matches[@]} > 1)); then
        stop "more than one staging page or attachment matched /$slug/."
    fi

    if ((${#matches[@]} == 1)); then
        page_id="${matches[0]}"
        [[ "$page_id" =~ ^[0-9]+$ ]] || stop "unexpected page ID for /$slug/: $page_id"
        page_type="$("${WPS[@]}" post get "$page_id" --field=post_type)"
        page_status="$("${WPS[@]}" post get "$page_id" --field=post_status)"
        page_name="$("${WPS[@]}" post get "$page_id" --field=post_name)"
        page_parent="$("${WPS[@]}" post get "$page_id" --field=post_parent)"
        [[ "$page_type" == "page" && "$page_status" == "publish" && "$page_name" == "$slug" && "$page_parent" == "0" ]] ||
            stop "/$slug/ collides with a $page_type record (status '$page_status', slug '$page_name', parent '$page_parent'); it was not changed."
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
        CREATED_IDS+=("$page_id")
        PAGE_IDS["$slug"]="$page_id"
        PAGE_ACTIONS["$slug"]="created"
    fi

    page_id="${PAGE_IDS[$slug]}"
    [[ "$("${WPS[@]}" post get "$page_id" --field=post_type)" == "page" ]] || stop "ID $page_id is not a page."
    [[ "$("${WPS[@]}" post get "$page_id" --field=post_status)" == "publish" ]] || stop "ID $page_id is not published."
    [[ "$("${WPS[@]}" post get "$page_id" --field=post_name)" == "$slug" ]] || stop "ID $page_id has an unexpected slug."
    [[ "$("${WPS[@]}" post get "$page_id" --field=post_parent)" == "0" ]] || stop "ID $page_id is not a root page."
    resolved_id="$("${WPS[@]}" post url-to-id "${STAGING_HOME%/}/$slug/" | tr -d '\r')"
    [[ "$resolved_id" == "$page_id" ]] || stop "/$slug/ resolves to ID '$resolved_id' instead of ID '$page_id'."
done

COMMITTED=1
printf '\nSTAGING AI PAGE PARITY READY\n'
printf 'Staging root: %s\n' "$STAGING_ROOT"
for spec in "${PAGE_SPECS[@]}"; do
    IFS='|' read -r slug title <<<"$spec"
    printf '%s: %s (ID %s) — %s/%s/\n' \
        "$title" "${PAGE_ACTIONS[$slug]}" "${PAGE_IDS[$slug]}" "${STAGING_HOME%/}" "$slug"
done
printf 'Next: run scripts/deploy-staging-theme.sh to install the pinned candidate and purge staging caches.\n'
