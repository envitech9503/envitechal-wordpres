#!/usr/bin/env bash

set -Eeuo pipefail

PRODUCTION_HOST="envitechal.com"
STAGING_HOST="staging.envitechal.com"
BACKUP_DIR="${HOME}/backups/envitechal-ai-visibility"
RECOVERY_SET_INPUT="${DISCOVERY_CACHE_RECOVERY_SET:-}"

stop() {
    echo "STOP: $*" >&2
    exit 1
}

get_docroot() {
    local domain="$1"
    uapi --output=json DomainInfo single_domain_data domain="$domain" |
        php -r '
            $json = json_decode(stream_get_contents(STDIN), true);
            $root = (int) ($json["result"]["status"] ?? 0) === 1
                ? rtrim((string) ($json["result"]["data"]["documentroot"] ?? ""), "/") : "";
            if ($root === "") { fwrite(STDERR, "cPanel document-root lookup failed.\n"); exit(1); }
            echo $root;
        '
}

file_digest() {
    sha256sum "$1" | awk '{print $1}'
}

metadata_value() {
    local key="$1"
    awk -F '\t' -v key="$key" '$1 == key { if (++count > 1) exit 2; value=$2 } END { if (count != 1) exit 1; print value }' \
        "$RECOVERY_SET/metadata.tsv"
}

purge_application_cache() {
    "$WP_CLI" --path="$TARGET_ROOT" eval \
        'if (!has_action("litespeed_purge_all")) { fwrite(STDERR, "LiteSpeed purge hook is unavailable.\n"); exit(1); } do_action("litespeed_purge_all");'
}

verify_public_availability() {
    local request_dir="$1"
    local path method status mime headers body metadata
    local -a paths curl_args

    paths=('/' '/robots.txt' '/llms.txt' '/llms-full.txt')
    if [[ "$ENVIRONMENT" == "production" ]]; then
        paths+=('/.well-known/agent-skills/index.json')
    fi
    mkdir -m 0700 "$request_dir"

    for path in "${paths[@]}"; do
        for method in GET HEAD; do
            headers="$request_dir/${method}-$(printf '%s' "$path" | sha256sum | awk '{print $1}').headers"
            body="$request_dir/${method}.body"
            metadata="$request_dir/${method}-$(printf '%s' "$path" | sha256sum | awk '{print $1}').metadata"
            curl_args=(
                --http1.1 --silent --show-error --path-as-is --max-redirs 0
                --connect-timeout 10 --max-time 30
                --user-agent 'Mozilla/5.0 (compatible; EnviTechRollbackVerifier/1.0)'
                --header 'Cache-Control: no-cache'
                --dump-header "$headers" --output "$body"
                --write-out $'%{http_code}\n%{content_type}\n'
            )
            if [[ "$method" == "HEAD" ]]; then curl_args+=(--head); fi
            curl "${curl_args[@]}" "https://${HOST}${path}?eta_rollback_verify=${STAMP}" >"$metadata" ||
                stop "$method availability request failed for $path."
            sleep 0.25
            status="$(sed -n '1p' "$metadata")"
            mime="$(sed -n '2p' "$metadata")"
            [[ "$status" == "200" ]] || stop "$method $path returned $status after rollback."
            if [[ "$path" == '/' ]]; then
                [[ "${mime,,}" == *'text/html'* ]] || stop "homepage returned '$mime' after rollback."
            elif [[ "$path" == '/.well-known/agent-skills/index.json' ]]; then
                [[ "${mime,,}" == *'application/json'* ]] || stop "$path returned '$mime' after rollback."
            else
                [[ "${mime,,}" == *'text/plain'* ]] || stop "$path returned '$mime' after rollback."
            fi
            if [[ "$method" == "GET" ]] && grep -Eiq 'One moment, please|Checking your browser|Verify you are human|Access denied' "$body"; then
                stop "$path returned a challenge or access-denied response after rollback."
            fi
        done
    done
}

for command in uapi php curl flock realpath sha256sum wp; do
    command -v "$command" >/dev/null || stop "$command is required."
done
WP_CLI="$(command -v wp)"
test -x "$WP_CLI" || stop "WP-CLI is required before rollback."
[[ "$RECOVERY_SET_INPUT" == /* ]] || stop "set DISCOVERY_CACHE_RECOVERY_SET to the absolute printed recovery-set path."

HOME_REAL="$(realpath -e "$HOME")"
BACKUP_DIR_REAL="$(realpath -e "$BACKUP_DIR")"
RECOVERY_SET="$(realpath -e "$RECOVERY_SET_INPUT")"
[[ "$BACKUP_DIR_REAL" == "$BACKUP_DIR" ]] || stop "the private backup root is not canonical."
case "$RECOVERY_SET" in
    "$BACKUP_DIR_REAL"/*) ;;
    *) stop "the recovery set is outside the private backup root." ;;
esac
test -d "$RECOVERY_SET" && test ! -L "$RECOVERY_SET" || stop "the recovery set is not a regular directory."
if find "$RECOVERY_SET" -type l -print -quit | grep -q .; then
    stop "the recovery set contains a symlink."
fi
test -f "$RECOVERY_SET/MANIFEST.sha256" && test -f "$RECOVERY_SET/metadata.tsv" || stop "recovery metadata is incomplete."
(
    cd "$RECOVERY_SET"
    sha256sum -c MANIFEST.sha256
) || stop "the recovery-set manifest failed verification."

ENVIRONMENT="$(metadata_value environment)" || stop "environment metadata is invalid."
HOST="$(metadata_value host)" || stop "host metadata is invalid."
RECORDED_ROOT="$(metadata_value document_root)" || stop "document-root metadata is invalid."
ORIGINAL_STATE="$(metadata_value original_state)" || stop "original-state metadata is invalid."
ORIGINAL_MODE="$(metadata_value original_mode)" || stop "original-mode metadata is invalid."
ORIGINAL_DIGEST="$(metadata_value original_digest)" || stop "original-digest metadata is invalid."
CANDIDATE_DIGEST="$(metadata_value candidate_digest)" || stop "candidate-digest metadata is invalid."

case "$ENVIRONMENT:$HOST" in
    "staging:$STAGING_HOST" | "production:$PRODUCTION_HOST") ;;
    *) stop "recovery metadata has an invalid environment/host pair." ;;
esac
[[ "${CONFIRM_DISCOVERY_CACHE_ROLLBACK:-}" == "$HOST" ]] ||
    stop "set CONFIRM_DISCOVERY_CACHE_ROLLBACK=$HOST to authorize this rollback."
[[ "$ORIGINAL_STATE" == "present" || "$ORIGINAL_STATE" == "absent" ]] || stop "original state is invalid."
[[ "$ORIGINAL_MODE" =~ ^[0-7]{3,4}$ ]] || stop "original mode is invalid."
[[ "$CANDIDATE_DIGEST" =~ ^[0-9a-f]{64}$ ]] || stop "candidate digest is invalid."
if [[ "$ORIGINAL_STATE" == "present" ]]; then
    [[ "$ORIGINAL_DIGEST" =~ ^[0-9a-f]{64}$ ]] || stop "original digest is invalid."
    test -f "$RECOVERY_SET/htaccess.before" && test ! -L "$RECOVERY_SET/htaccess.before" ||
        stop "the prior .htaccess backup is missing or unsafe."
    [[ "$(file_digest "$RECOVERY_SET/htaccess.before")" == "$ORIGINAL_DIGEST" ]] ||
        stop "the prior .htaccess backup digest is wrong."
else
    [[ "$ORIGINAL_DIGEST" == "absent" ]] || stop "absent-state metadata is inconsistent."
fi

PRODUCTION_ROOT_RAW="$(get_docroot "$PRODUCTION_HOST")"
STAGING_ROOT_RAW="$(get_docroot "$STAGING_HOST")"
PRODUCTION_ROOT="$(realpath -e "$PRODUCTION_ROOT_RAW")"
STAGING_ROOT="$(realpath -e "$STAGING_ROOT_RAW")"
[[ "$PRODUCTION_ROOT_RAW" == "$PRODUCTION_ROOT" && "$STAGING_ROOT_RAW" == "$STAGING_ROOT" ]] ||
    stop "a document root contains a symlink or non-canonical component."
[[ "$PRODUCTION_ROOT" == "$HOME_REAL/"* && "$STAGING_ROOT" == "$HOME_REAL/"* ]] ||
    stop "a document root is outside this cPanel home."
[[ "$PRODUCTION_ROOT" != "$STAGING_ROOT" ]] || stop "production and staging resolve to the same directory."
case "$PRODUCTION_ROOT" in "$STAGING_ROOT"/*) stop "production is nested inside staging." ;; esac
case "$STAGING_ROOT" in "$PRODUCTION_ROOT"/*) stop "staging is nested inside production." ;; esac
[[ "$(stat -Lc '%d:%i' "$PRODUCTION_ROOT")" != "$(stat -Lc '%d:%i' "$STAGING_ROOT")" ]] ||
    stop "production and staging identify the same filesystem directory."
if [[ "$ENVIRONMENT" == "production" ]]; then TARGET_ROOT="$PRODUCTION_ROOT"; else TARGET_ROOT="$STAGING_ROOT"; fi
[[ "$TARGET_ROOT" == "$RECORDED_ROOT" ]] || stop "the current document root differs from recovery metadata."
test -f "$TARGET_ROOT/wp-load.php" || stop "the target WordPress root was not confirmed."

case "$BACKUP_DIR_REAL" in
    "$PRODUCTION_ROOT" | "$PRODUCTION_ROOT"/* | "$STAGING_ROOT" | "$STAGING_ROOT"/*)
        stop "the private backup root is inside a public webroot."
        ;;
esac

LOCK_FILE="$BACKUP_DIR_REAL/discovery-cache-remediation.lock"
test ! -L "$LOCK_FILE" || stop "the transaction lock is a symlink."
exec 9>"$LOCK_FILE"
chmod 0600 "$LOCK_FILE"
flock -n 9 || stop "another discovery-cache transaction is running."

HTACCESS="$TARGET_ROOT/.htaccess"
test -f "$HTACCESS" && test ! -L "$HTACCESS" || stop "the active .htaccess is missing or unsafe."
[[ "$(file_digest "$HTACCESS")" == "$CANDIDATE_DIGEST" ]] ||
    stop "active .htaccess has drifted since deployment; refusing to overwrite it."
ACTIVE_MODE="$(stat -Lc '%a' "$HTACCESS")"
[[ "$ACTIVE_MODE" =~ ^[0-7]{3,4}$ ]] || stop "the active .htaccess mode is invalid."

STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
ROLLBACK_SET="$BACKUP_DIR_REAL/${ENVIRONMENT}-discovery-cache-rollback-before-${STAMP}"
RESTORE_TMP="$TARGET_ROOT/.eta-discovery-restore-${STAMP}"
REMOVED_ACTIVE="$TARGET_ROOT/.eta-discovery-removed-${STAMP}"
for path in "$ROLLBACK_SET" "$RESTORE_TMP" "$REMOVED_ACTIVE"; do
    test ! -e "$path" && test ! -L "$path" || stop "protected rollback path exists: $path"
done
mkdir -m 0700 "$ROLLBACK_SET"
install -m 0600 -- "$HTACCESS" "$ROLLBACK_SET/active-before-rollback.htaccess"
[[ "$(file_digest "$ROLLBACK_SET/active-before-rollback.htaccess")" == "$CANDIDATE_DIGEST" ]] ||
    stop "could not verify the pre-rollback active backup."
printf 'source_recovery_set\t%s\nhost\t%s\ncreated_utc\t%s\nactive_digest\t%s\n' \
    "$RECOVERY_SET" "$HOST" "$STAMP" "$CANDIDATE_DIGEST" >"$ROLLBACK_SET/metadata.tsv"

RESTORE_CURRENT=1
cleanup() {
    local status=$?
    local recovery_failed=0
    trap - EXIT
    trap '' INT TERM
    if ((status != 0 && RESTORE_CURRENT != 0)); then
        echo "Rollback did not commit; restoring the pre-rollback active .htaccess..." >&2
        if test -f "$REMOVED_ACTIVE"; then
            mv -f -- "$REMOVED_ACTIVE" "$HTACCESS" || recovery_failed=1
        else
            install -m "$ACTIVE_MODE" -- "$ROLLBACK_SET/active-before-rollback.htaccess" "$RESTORE_TMP" || recovery_failed=1
            if ((recovery_failed == 0)); then
                mv -f -- "$RESTORE_TMP" "$HTACCESS" || recovery_failed=1
            fi
        fi
        if ((recovery_failed != 0)) || ! test -f "$HTACCESS" || test -L "$HTACCESS" ||
            [[ "$(file_digest "$HTACCESS" 2>/dev/null || true)" != "$CANDIDATE_DIGEST" ]] ||
            [[ "$(stat -Lc '%a' "$HTACCESS" 2>/dev/null || true)" != "$ACTIVE_MODE" ]]; then
            echo "CRITICAL: pre-rollback active .htaccess recovery failed digest/mode verification." >&2
        else
            echo "Pre-rollback active .htaccess restored and verified." >&2
        fi
        purge_application_cache || true
    fi
    rm -f -- "$RESTORE_TMP" || true
    exit "$status"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

if [[ "$ORIGINAL_STATE" == "present" ]]; then
    install -m "$ORIGINAL_MODE" -- "$RECOVERY_SET/htaccess.before" "$RESTORE_TMP"
    mv -f -- "$RESTORE_TMP" "$HTACCESS"
    [[ "$(file_digest "$HTACCESS")" == "$ORIGINAL_DIGEST" ]] || stop "restored .htaccess digest mismatch."
else
    mv -- "$HTACCESS" "$REMOVED_ACTIVE"
    test ! -e "$HTACCESS" || stop "could not restore the original absence of .htaccess."
fi

purge_application_cache
verify_public_availability "$ROLLBACK_SET/live-verification"

if test -f "$REMOVED_ACTIVE"; then
    install -m 0600 -- "$REMOVED_ACTIVE" "$ROLLBACK_SET/removed-active.htaccess"
    [[ "$(file_digest "$ROLLBACK_SET/removed-active.htaccess")" == "$CANDIDATE_DIGEST" ]] ||
        stop "the removed active file could not be preserved."
    rm -f -- "$REMOVED_ACTIVE"
fi
if [[ "$ENVIRONMENT" == "staging" && -f "$BACKUP_DIR_REAL/LAST_STAGING_DISCOVERY_CACHE_VERIFICATION" ]]; then
    mv -- "$BACKUP_DIR_REAL/LAST_STAGING_DISCOVERY_CACHE_VERIFICATION" \
        "$ROLLBACK_SET/staging-attestation.invalidated"
fi
(
    cd "$ROLLBACK_SET"
    find . -type f ! -name MANIFEST.sha256 -printf '%P\0' |
        LC_ALL=C sort -z | xargs -0 -r sha256sum >MANIFEST.sha256
    sha256sum -c MANIFEST.sha256
)
find "$ROLLBACK_SET" -type f -exec chmod 0600 {} +

RESTORE_CURRENT=0
printf '\n%s DISCOVERY CACHE ROLLBACK COMMITTED\n' "${ENVIRONMENT^^}"
printf 'Restored state: %s\nSource recovery set: %s\nRollback evidence: %s\n' \
    "$ORIGINAL_STATE" "$RECOVERY_SET" "$ROLLBACK_SET"
printf 'WAF status: unchanged; rollback never disables or weakens the firewall.\n'
