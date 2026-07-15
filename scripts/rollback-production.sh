#!/usr/bin/env bash

set -Eeuo pipefail

PRODUCTION_HOST="envitechal.com"
STAGING_HOST="staging.envitechal.com"
THEME_REL="wp-content/themes/generatepress-envitechal"
BACKUP_DIR="${HOME}/backups/envitechal-ai-visibility"
BACKUP_MARKER="${BACKUP_DIR}/LAST_PRODUCTION_BACKUP"

stop() {
    echo "STOP: $*" >&2
    exit 1
}

get_docroot() {
    local domain="$1"

    uapi --output=json DomainInfo single_domain_data domain="$domain" |
        php -r '
            $json = json_decode(stream_get_contents(STDIN), true);
            if (!is_array($json) || (int) ($json["result"]["status"] ?? 0) !== 1) {
                fwrite(STDERR, "cPanel could not return the document root.\n");
                exit(1);
            }

            $root = rtrim((string) ($json["result"]["data"]["documentroot"] ?? ""), "/");
            if ($root === "") {
                fwrite(STDERR, "The cPanel document root was empty.\n");
                exit(1);
            }

            echo $root;
        '
}

tree_digest() {
    local root="$1"

    (
        cd "$root"
        find . -type f -print0 |
            LC_ALL=C sort -z |
            xargs -0 -r sha256sum
    ) | sha256sum | awk '{print $1}'
}

file_digest() {
    sha256sum "$1" | awk '{print $1}'
}

assert_regular_or_absent() {
    local path="$1"

    test ! -L "$path" || stop "$path is a symlink."
    if test -e "$path"; then
        test -f "$path" || stop "$path is not a regular file."
    fi
}

backup_current_public_file() {
    local target="$1"
    local backup="$2"
    local state_var="$3"
    local digest_var="$4"
    local before after

    assert_regular_or_absent "$target"
    if test -f "$target"; then
        before="$(file_digest "$target")"
        install -m 0600 -- "$target" "$backup"
        after="$(file_digest "$target")"
        [[ "$before" == "$after" ]] || stop "$target changed while the rollback recovery set was being created."
        [[ "$(file_digest "$backup")" == "$before" ]] || stop "the rollback recovery copy of $target did not verify."
        printf -v "$state_var" '%s' present
        printf -v "$digest_var" '%s' "$before"
    else
        printf -v "$state_var" '%s' absent
        printf -v "$digest_var" '%s' absent
    fi
}

assert_current_public_file_unchanged() {
    local target="$1"
    local current_state="$2"
    local current_digest="$3"

    assert_regular_or_absent "$target"
    if [[ "$current_state" == "present" ]]; then
        test -f "$target" || stop "$target disappeared after the pre-rollback archive was created."
        [[ "$(file_digest "$target")" == "$current_digest" ]] ||
            stop "$target changed after the pre-rollback archive was created."
    else
        test ! -e "$target" || stop "$target appeared after the pre-rollback archive was created."
    fi
}

recover_current_public_file() {
    local state="$1"
    local had_current="$2"
    local target="$3"
    local current_swap="$4"
    local failed_restore="$5"

    # The protected current path is the durable transaction indicator. This
    # covers a signal after mv succeeds but before the state assignment does.
    if [[ "$had_current" == "1" ]]; then
        if test -f "$current_swap"; then
            if test -e "$target" || test -L "$target"; then
                if ! test -f "$target" || test -L "$target" || ! mv -- "$target" "$failed_restore"; then
                    echo "CRITICAL: could not move the restored file away from $target (state: $state)." >&2
                    return 1
                fi
            fi
            if ! mv -- "$current_swap" "$target"; then
                if test -f "$failed_restore" && ! test -e "$target"; then
                    mv -- "$failed_restore" "$target" || true
                fi
                echo "CRITICAL: could not recover the pre-rollback $target (state: $state)." >&2
                return 1
            fi
        elif [[ "$state" != "preparing" && "$state" != "moving-current" ]]; then
            echo "CRITICAL: $current_swap is missing in rollback state $state." >&2
            return 1
        fi
    else
        # The pre-rollback path was absent. A target now present can only be
        # the desired restore that crossed its rename boundary, so move it out.
        if [[ "$state" == "moving-desired" || "$state" == "desired-active" || "$state" == "verified" ]] &&
            { test -e "$target" || test -L "$target"; }; then
            if ! test -f "$target" || test -L "$target" || ! mv -- "$target" "$failed_restore"; then
                echo "CRITICAL: could not recover the pre-rollback absence of $target (state: $state)." >&2
                return 1
            fi
        fi
    fi
}

recover_current_theme() {
    local state="$1"
    local target="$2"
    local current_swap="$3"
    local failed_restore="$4"

    if test -d "$current_swap"; then
        if test -e "$target" || test -L "$target"; then
            if ! test -d "$target" || test -L "$target" || ! mv -- "$target" "$failed_restore"; then
                echo "CRITICAL: could not move the restored theme away (state: $state)." >&2
                return 1
            fi
        fi
        if ! mv -- "$current_swap" "$target"; then
            if test -d "$failed_restore" && ! test -e "$target"; then
                mv -- "$failed_restore" "$target" || true
            fi
            echo "CRITICAL: could not recover the pre-rollback production theme (state: $state)." >&2
            return 1
        fi
    elif [[ "$state" != "preparing" && "$state" != "moving-current" ]]; then
        echo "CRITICAL: $current_swap is missing in production rollback theme state $state." >&2
        return 1
    elif ! test -d "$target" || test -L "$target"; then
        echo "CRITICAL: neither the pre-rollback theme nor its protected swap is available (state: $state)." >&2
        return 1
    fi
}

[[ "${CONFIRM_PRODUCTION_ROLLBACK:-}" == "$PRODUCTION_HOST" ]] ||
    stop "set CONFIRM_PRODUCTION_ROLLBACK=$PRODUCTION_HOST to authorize this production rollback."

command -v uapi >/dev/null || stop "cPanel UAPI was not found."
PHP_BIN="$(command -v php || true)"
FLOCK_BIN="$(command -v flock || true)"
test -x "$PHP_BIN" || stop "PHP CLI was not found."
test -x "$FLOCK_BIN" || stop "flock was not found."

HOME_REAL="$(realpath -e "$HOME")"
PRODUCTION_ROOT_RAW="$(get_docroot "$PRODUCTION_HOST")"
STAGING_ROOT_RAW="$(get_docroot "$STAGING_HOST")"
PRODUCTION_ROOT="$(realpath -e "$PRODUCTION_ROOT_RAW")"
STAGING_ROOT="$(realpath -e "$STAGING_ROOT_RAW")"

[[ "$PRODUCTION_ROOT_RAW" == "$PRODUCTION_ROOT" ]] || stop "the production document root contains a symlink or non-canonical component."
[[ "$STAGING_ROOT_RAW" == "$STAGING_ROOT" ]] || stop "the staging document root contains a symlink or non-canonical component."
[[ "$PRODUCTION_ROOT" == "$HOME_REAL/"* ]] || stop "production is outside this cPanel home."
[[ "$STAGING_ROOT" == "$HOME_REAL/"* ]] || stop "staging is outside this cPanel home."
[[ "$PRODUCTION_ROOT" != "$STAGING_ROOT" ]] || stop "production and staging resolve to the same directory."
case "$PRODUCTION_ROOT" in
    "$STAGING_ROOT"/*) stop "production is nested inside staging." ;;
esac
case "$STAGING_ROOT" in
    "$PRODUCTION_ROOT"/*) stop "staging is nested inside production." ;;
esac
[[ "$(stat -Lc '%d:%i' "$PRODUCTION_ROOT")" != "$(stat -Lc '%d:%i' "$STAGING_ROOT")" ]] ||
    stop "production and staging identify the same filesystem directory."

test -f "$PRODUCTION_ROOT/wp-load.php" || stop "production WordPress root was not confirmed."
test -f "$STAGING_ROOT/wp-load.php" || stop "staging WordPress root was not confirmed."

THEMES_PARENT="${PRODUCTION_ROOT}/wp-content/themes"
TARGET="${PRODUCTION_ROOT}/${THEME_REL}"
STAGING_TARGET="${STAGING_ROOT}/${THEME_REL}"
LLMS_TARGET="${PRODUCTION_ROOT}/llms.txt"
LLMS_FULL_TARGET="${PRODUCTION_ROOT}/llms-full.txt"

test -d "$TARGET" && test -f "$TARGET/functions.php" || stop "the deployed production child theme was not found."
THEMES_PARENT_REAL="$(realpath -e "$THEMES_PARENT")"
TARGET_REAL="$(realpath -e "$TARGET")"
[[ "$THEMES_PARENT_REAL" == "$THEMES_PARENT" ]] || stop "a production themes path component is a symlink."
[[ "$TARGET_REAL" == "$TARGET" ]] || stop "the production child theme path is a symlink."
case "$TARGET_REAL" in
    "$STAGING_ROOT" | "$STAGING_ROOT"/*) stop "the production theme resolves inside staging." ;;
esac
if test -e "$STAGING_TARGET"; then
    STAGING_TARGET_REAL="$(realpath -e "$STAGING_TARGET")"
    [[ "$STAGING_TARGET_REAL" != "$TARGET_REAL" ]] || stop "production and staging themes resolve to the same directory."
    [[ "$(stat -Lc '%d:%i' "$STAGING_TARGET_REAL")" != "$(stat -Lc '%d:%i' "$TARGET_REAL")" ]] ||
        stop "production and staging themes identify the same filesystem directory."
fi
if find "$TARGET" -type l -print -quit | grep -q .; then
    stop "the deployed production theme contains a symlink and cannot be archived safely."
fi
assert_regular_or_absent "$LLMS_TARGET"
assert_regular_or_absent "$LLMS_FULL_TARGET"
test -w "$PRODUCTION_ROOT" || stop "the production webroot is not writable."
test -w "$THEMES_PARENT" && test -w "$TARGET" || stop "the production theme area is not writable."

test -d "$BACKUP_DIR" || stop "the private backup directory was not found."
BACKUP_DIR_REAL="$(realpath -e "$BACKUP_DIR")"
[[ "$BACKUP_DIR_REAL" == "$BACKUP_DIR" ]] || stop "the private backup path contains a symlink or non-canonical component."
case "$BACKUP_DIR_REAL" in
    "$PRODUCTION_ROOT" | "$PRODUCTION_ROOT"/* | "$STAGING_ROOT" | "$STAGING_ROOT"/*)
        stop "the private backup directory is inside a webroot."
        ;;
esac

test ! -L "$BACKUP_MARKER" || stop "the production backup marker is a symlink."
test -f "$BACKUP_MARKER" || stop "the production backup marker was not found."
[[ "$(wc -l <"$BACKUP_MARKER" | tr -d ' ')" == "1" ]] || stop "the production backup marker is malformed."
BACKUP_SET="$(<"$BACKUP_MARKER")"
[[ "$(dirname -- "$BACKUP_SET")" == "$BACKUP_DIR" ]] || stop "the production backup marker points outside the private backup directory."
BACKUP_NAME="$(basename -- "$BACKUP_SET")"
[[ "$BACKUP_NAME" =~ ^production-before-[0-9]{8}T[0-9]{6}Z$ ]] || stop "the production backup marker has an unexpected target."
test -d "$BACKUP_SET" || stop "the marked production recovery set was not found."
BACKUP_SET_REAL="$(realpath -e "$BACKUP_SET")"
[[ "$BACKUP_SET_REAL" == "$BACKUP_SET" ]] || stop "the marked production recovery set contains a symlink or non-canonical component."
test -f "$BACKUP_SET/MANIFEST.sha256" || stop "the production recovery manifest was not found."
(
    cd "$BACKUP_SET"
    sha256sum -c MANIFEST.sha256
)

test -f "$BACKUP_SET/theme.tar.gz" || stop "the production theme recovery archive was not found."
test -f "$BACKUP_SET/theme-tree.sha256" || stop "the production theme tree digest was not found."
BACKED_THEME_DIGEST="$(<"$BACKUP_SET/theme-tree.sha256")"
[[ "$BACKED_THEME_DIGEST" =~ ^[0-9a-f]{64}$ ]] || stop "the saved production theme digest is malformed."
tar -tzf "$BACKUP_SET/theme.tar.gz" >/dev/null

test -f "$BACKUP_SET/discovery-state.tsv" || stop "the discovery recovery state was not found."
mapfile -t DISCOVERY_STATE <"$BACKUP_SET/discovery-state.tsv"
[[ "${#DISCOVERY_STATE[@]}" == "2" ]] || stop "the discovery recovery state is malformed."
IFS=$'\t' read -r LLMS_KEY LLMS_DESIRED_STATE LLMS_EXTRA <<<"${DISCOVERY_STATE[0]}"
IFS=$'\t' read -r LLMS_FULL_KEY LLMS_FULL_DESIRED_STATE LLMS_FULL_EXTRA <<<"${DISCOVERY_STATE[1]}"
[[ "$LLMS_KEY" == "llms.txt" && -z "$LLMS_EXTRA" ]] || stop "the llms.txt recovery state is malformed."
[[ "$LLMS_FULL_KEY" == "llms-full.txt" && -z "$LLMS_FULL_EXTRA" ]] || stop "the llms-full.txt recovery state is malformed."
[[ "$LLMS_DESIRED_STATE" == "present" || "$LLMS_DESIRED_STATE" == "absent" ]] || stop "the llms.txt recovery state is invalid."
[[ "$LLMS_FULL_DESIRED_STATE" == "present" || "$LLMS_FULL_DESIRED_STATE" == "absent" ]] || stop "the llms-full.txt recovery state is invalid."

if [[ "$LLMS_DESIRED_STATE" == "present" ]]; then
    test -f "$BACKUP_SET/llms.txt.before" || stop "the saved llms.txt is missing."
else
    test ! -e "$BACKUP_SET/llms.txt.before" || stop "the saved llms.txt conflicts with its absence state."
fi
if [[ "$LLMS_FULL_DESIRED_STATE" == "present" ]]; then
    test -f "$BACKUP_SET/llms-full.txt.before" || stop "the saved llms-full.txt is missing."
else
    test ! -e "$BACKUP_SET/llms-full.txt.before" || stop "the saved llms-full.txt conflicts with its absence state."
fi

LOCK_FILE="${BACKUP_DIR}/production-deploy.lock"
test ! -L "$LOCK_FILE" || stop "the production transaction lock is a symlink."
exec 9>"$LOCK_FILE"
chmod 0600 "$LOCK_FILE"
flock -n 9 || stop "another production deploy or rollback is running."

umask 077
STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
RESTORE_BUILD="${THEMES_PARENT}/.eta-production-restore-${STAMP}"
RESTORE_THEME="${RESTORE_BUILD}/generatepress-envitechal"
CURRENT_THEME="${THEMES_PARENT}/.eta-production-rollback-current-${STAMP}"
DISCOVERY_STAGE="${PRODUCTION_ROOT}/.eta-production-restore-public-${STAMP}"
RESTORE_LLMS="${DISCOVERY_STAGE}/llms.txt"
RESTORE_LLMS_FULL="${DISCOVERY_STAGE}/llms-full.txt"
CURRENT_LLMS="${PRODUCTION_ROOT}/.eta-llms-rollback-current-${STAMP}.txt"
CURRENT_LLMS_FULL="${PRODUCTION_ROOT}/.eta-llms-full-rollback-current-${STAMP}.txt"
FAILED_SET="${BACKUP_DIR}/production-rolled-back-from-${STAMP}"

for protected_path in "$RESTORE_BUILD" "$CURRENT_THEME" "$DISCOVERY_STAGE" "$CURRENT_LLMS" "$CURRENT_LLMS_FULL" "$FAILED_SET"; do
    test ! -e "$protected_path" && test ! -L "$protected_path" || stop "protected rollback path already exists: $protected_path"
done

mkdir -m 0700 "$RESTORE_BUILD" "$DISCOVERY_STAGE" "$FAILED_SET"
tar -xzf "$BACKUP_SET/theme.tar.gz" -C "$RESTORE_BUILD" --strip-components=2
test -f "$RESTORE_THEME/functions.php" || stop "the recovery archive did not contain the production child theme."
if find "$RESTORE_THEME" -type l -print -quit | grep -q .; then
    stop "the recovered production theme contains a symlink."
fi
find "$RESTORE_THEME" -type d -exec chmod 0755 {} +
find "$RESTORE_THEME" -type f -exec chmod 0644 {} +
find "$RESTORE_THEME" -type f -name '*.php' -print0 |
    xargs -0 -r -n1 "$PHP_BIN" -l
[[ "$(tree_digest "$RESTORE_THEME")" == "$BACKED_THEME_DIGEST" ]] || stop "the prepared rollback theme digest did not verify."

if [[ "$LLMS_DESIRED_STATE" == "present" ]]; then
    install -m 0644 -- "$BACKUP_SET/llms.txt.before" "$RESTORE_LLMS"
fi
if [[ "$LLMS_FULL_DESIRED_STATE" == "present" ]]; then
    install -m 0644 -- "$BACKUP_SET/llms-full.txt.before" "$RESTORE_LLMS_FULL"
fi

CURRENT_THEME_DIGEST="$(tree_digest "$TARGET")"
tar -czf "$FAILED_SET/theme.tar.gz" -C "$PRODUCTION_ROOT" "$THEME_REL"
tar -tzf "$FAILED_SET/theme.tar.gz" >/dev/null
[[ "$(tree_digest "$TARGET")" == "$CURRENT_THEME_DIGEST" ]] || stop "the current production theme changed while it was being archived."
printf '%s\n' "$CURRENT_THEME_DIGEST" >"$FAILED_SET/theme-tree.sha256"

LLMS_CURRENT_STATE=""
LLMS_FULL_CURRENT_STATE=""
LLMS_CURRENT_DIGEST=""
LLMS_FULL_CURRENT_DIGEST=""
backup_current_public_file "$LLMS_TARGET" "$FAILED_SET/llms.txt.before" LLMS_CURRENT_STATE LLMS_CURRENT_DIGEST
backup_current_public_file "$LLMS_FULL_TARGET" "$FAILED_SET/llms-full.txt.before" LLMS_FULL_CURRENT_STATE LLMS_FULL_CURRENT_DIGEST
printf 'llms.txt\t%s\nllms-full.txt\t%s\n' \
    "$LLMS_CURRENT_STATE" "$LLMS_FULL_CURRENT_STATE" >"$FAILED_SET/discovery-state.tsv"
printf 'host\t%s\ncreated_utc\t%s\nrestored_from\t%s\npre_rollback_theme_digest\t%s\nrestored_theme_digest\t%s\n' \
    "$PRODUCTION_HOST" "$STAMP" "$BACKUP_SET" "$CURRENT_THEME_DIGEST" "$BACKED_THEME_DIGEST" \
    >"$FAILED_SET/rollback-metadata.tsv"
(
    cd "$FAILED_SET"
    find . -maxdepth 1 -type f ! -name MANIFEST.sha256 -printf '%P\0' |
        LC_ALL=C sort -z |
        xargs -0 -r sha256sum >MANIFEST.sha256
    sha256sum -c MANIFEST.sha256
)
find "$FAILED_SET" -type f -exec chmod 0600 {} +

ROLLBACK_MARKER_TMP="${BACKUP_DIR}/.LAST_PRODUCTION_ROLLBACK_SOURCE.${STAMP}"
test ! -e "$ROLLBACK_MARKER_TMP" && test ! -L "$ROLLBACK_MARKER_TMP" || stop "the temporary rollback marker already exists."
printf '%s\n' "$FAILED_SET" >"$ROLLBACK_MARKER_TMP"
chmod 0600 "$ROLLBACK_MARKER_TMP"
mv -f -- "$ROLLBACK_MARKER_TMP" "$BACKUP_DIR/LAST_PRODUCTION_ROLLBACK_SOURCE"

THEME_STATE="preparing"
LLMS_STATE="preparing"
LLMS_FULL_STATE="preparing"
if [[ "$LLMS_CURRENT_STATE" == "present" ]]; then LLMS_HAD_CURRENT=1; else LLMS_HAD_CURRENT=0; fi
if [[ "$LLMS_FULL_CURRENT_STATE" == "present" ]]; then LLMS_FULL_HAD_CURRENT=1; else LLMS_FULL_HAD_CURRENT=0; fi

cleanup() {
    local status=$?
    local recovery_failed=0
    trap - EXIT
    trap '' INT TERM

    if ((status != 0)); then
        echo "Production rollback did not commit; recovering the pre-rollback paths..." >&2

        recover_current_public_file "$LLMS_FULL_STATE" "$LLMS_FULL_HAD_CURRENT" \
            "$LLMS_FULL_TARGET" "$CURRENT_LLMS_FULL" "$DISCOVERY_STAGE/failed-restored-llms-full.txt" || recovery_failed=1
        recover_current_public_file "$LLMS_STATE" "$LLMS_HAD_CURRENT" \
            "$LLMS_TARGET" "$CURRENT_LLMS" "$DISCOVERY_STAGE/failed-restored-llms.txt" || recovery_failed=1

        recover_current_theme "$THEME_STATE" "$TARGET" "$CURRENT_THEME" \
            "$RESTORE_BUILD/failed-restored-theme" || recovery_failed=1

        if ((recovery_failed == 0)) && test -d "$TARGET"; then
            case "$RESTORE_BUILD" in
                "$THEMES_PARENT"/.eta-production-restore-*) rm -rf --one-file-system -- "$RESTORE_BUILD" || true ;;
            esac
            case "$DISCOVERY_STAGE" in
                "$PRODUCTION_ROOT"/.eta-production-restore-public-*) rm -rf --one-file-system -- "$DISCOVERY_STAGE" || true ;;
            esac
            echo "The pre-rollback production state was recovered; its verified archive remains at $FAILED_SET." >&2
        else
            echo "CRITICAL: rollback recovery was incomplete. Preserve both private recovery sets and inspect the swap paths." >&2
        fi
    fi

    exit "$status"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

[[ "$(tree_digest "$TARGET")" == "$CURRENT_THEME_DIGEST" ]] || stop "the production theme changed after the rollback recovery archive was created."
assert_current_public_file_unchanged "$LLMS_TARGET" "$LLMS_CURRENT_STATE" "$LLMS_CURRENT_DIGEST"
assert_current_public_file_unchanged "$LLMS_FULL_TARGET" "$LLMS_FULL_CURRENT_STATE" "$LLMS_FULL_CURRENT_DIGEST"

echo "Atomically restoring the prior production theme..."
THEME_STATE="moving-current"
mv -- "$TARGET" "$CURRENT_THEME"
THEME_STATE="current-moved"
THEME_STATE="moving-desired"
mv -- "$RESTORE_THEME" "$TARGET"
THEME_STATE="new-active"

echo "Restoring the exact prior discovery-file presence and content..."
if test -f "$LLMS_TARGET"; then
    LLMS_STATE="moving-current"
    mv -- "$LLMS_TARGET" "$CURRENT_LLMS"
    LLMS_STATE="current-moved"
else
    LLMS_STATE="no-current"
fi
if [[ "$LLMS_DESIRED_STATE" == "present" ]]; then
    LLMS_STATE="moving-desired"
    mv -- "$RESTORE_LLMS" "$LLMS_TARGET"
fi
LLMS_STATE="desired-active"

if test -f "$LLMS_FULL_TARGET"; then
    LLMS_FULL_STATE="moving-current"
    mv -- "$LLMS_FULL_TARGET" "$CURRENT_LLMS_FULL"
    LLMS_FULL_STATE="current-moved"
else
    LLMS_FULL_STATE="no-current"
fi
if [[ "$LLMS_FULL_DESIRED_STATE" == "present" ]]; then
    LLMS_FULL_STATE="moving-desired"
    mv -- "$RESTORE_LLMS_FULL" "$LLMS_FULL_TARGET"
fi
LLMS_FULL_STATE="desired-active"

[[ "$(realpath -e "$TARGET")" == "$TARGET" ]] || stop "the restored production theme path did not verify."
[[ "$(tree_digest "$TARGET")" == "$BACKED_THEME_DIGEST" ]] || stop "the restored production theme digest did not verify."
find "$TARGET" -type f -name '*.php' -print0 |
    xargs -0 -r -n1 "$PHP_BIN" -l
if find "$TARGET" -type d ! -perm 0755 -print -quit | grep -q .; then
    stop "a restored theme directory does not have mode 0755."
fi
if find "$TARGET" -type f ! -perm 0644 -print -quit | grep -q .; then
    stop "a restored theme file does not have mode 0644."
fi

if [[ "$LLMS_DESIRED_STATE" == "present" ]]; then
    [[ "$(realpath -e "$LLMS_TARGET")" == "$LLMS_TARGET" ]] || stop "the restored llms.txt path did not verify."
    [[ "$(file_digest "$LLMS_TARGET")" == "$(file_digest "$BACKUP_SET/llms.txt.before")" ]] || stop "the restored llms.txt digest did not verify."
    [[ "$(stat -Lc '%a' "$LLMS_TARGET")" == "644" ]] || stop "the restored llms.txt does not have mode 0644."
else
    test ! -e "$LLMS_TARGET" && test ! -L "$LLMS_TARGET" || stop "llms.txt should have been removed by rollback."
fi
if [[ "$LLMS_FULL_DESIRED_STATE" == "present" ]]; then
    [[ "$(realpath -e "$LLMS_FULL_TARGET")" == "$LLMS_FULL_TARGET" ]] || stop "the restored llms-full.txt path did not verify."
    [[ "$(file_digest "$LLMS_FULL_TARGET")" == "$(file_digest "$BACKUP_SET/llms-full.txt.before")" ]] || stop "the restored llms-full.txt digest did not verify."
    [[ "$(stat -Lc '%a' "$LLMS_FULL_TARGET")" == "644" ]] || stop "the restored llms-full.txt does not have mode 0644."
else
    test ! -e "$LLMS_FULL_TARGET" && test ! -L "$LLMS_FULL_TARGET" || stop "llms-full.txt should have been removed by rollback."
fi

THEME_STATE="verified"
LLMS_STATE="verified"
LLMS_FULL_STATE="verified"
trap - EXIT INT TERM

chmod -R u+rwX,go-rwx "$CURRENT_THEME" || true
case "$CURRENT_THEME" in
    "$THEMES_PARENT"/.eta-production-rollback-current-*) rm -rf --one-file-system -- "$CURRENT_THEME" || echo "WARNING: protected pre-rollback theme needs manual cleanup." >&2 ;;
esac
rm -f -- "$CURRENT_LLMS" "$CURRENT_LLMS_FULL" || echo "WARNING: protected pre-rollback discovery files need manual cleanup." >&2
case "$RESTORE_BUILD" in
    "$THEMES_PARENT"/.eta-production-restore-*) rm -rf --one-file-system -- "$RESTORE_BUILD" || echo "WARNING: protected restore tree needs manual cleanup." >&2 ;;
esac
case "$DISCOVERY_STAGE" in
    "$PRODUCTION_ROOT"/.eta-production-restore-public-*) rm -rf --one-file-system -- "$DISCOVERY_STAGE" || echo "WARNING: protected discovery restore tree needs manual cleanup." >&2 ;;
esac

PURGE_RESULT="not requested (WP-CLI unavailable)"
if WP_BIN="$(command -v wp || true)" && test -x "$WP_BIN"; then
    if "$WP_BIN" --path="$PRODUCTION_ROOT" eval '
        if (!has_action("litespeed_purge_all")) {
            fwrite(STDERR, "LiteSpeed purge hook is unavailable.\n");
            exit(1);
        }
        do_action("litespeed_purge_all");
    ' >/dev/null; then
        PURGE_RESULT="LiteSpeed purge requested through the official WordPress action"
    else
        PURGE_RESULT="WARNING: the LiteSpeed WordPress purge request failed"
    fi
fi

printf '\nPRODUCTION ROLLBACK COMMITTED\nRestored from: %s\nRestored theme digest: %s\nPre-rollback recovery set: %s\nApplication cache: %s\n\n' \
    "$BACKUP_SET" "$BACKED_THEME_DIGEST" "$FAILED_SET" "$PURGE_RESULT"
printf '%s\n' \
    "EXTERNAL EDGE/WAF ACTION STILL REQUIRED: purge the CDN/edge cache separately and recheck the public origin responses."
