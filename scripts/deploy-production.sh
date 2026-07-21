#!/usr/bin/env bash

set -Eeuo pipefail

PRODUCTION_HOST="envitechal.com"
STAGING_HOST="staging.envitechal.com"
REPO="${HOME}/repositories/envitechal-wordpres"
THEME_REL="wp-content/themes/generatepress-envitechal"
ROBOTS_REL="deploy/public_html/robots.txt"
LLMS_REL="deploy/public_html/llms.txt"
LLMS_FULL_REL="deploy/public_html/llms-full.txt"

# SECURITY PIN: change this only to a commit whose theme and discovery files
# have passed staging validation and review. The script archives this exact tree.
VALIDATED_PRODUCTION_COMMIT="5caab23ecf39231d42d5dace340e2db0dd29500a"

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

backup_public_file() {
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
        [[ "$before" == "$after" ]] || stop "$target changed while it was being backed up."
        [[ "$(file_digest "$backup")" == "$before" ]] || stop "the private backup of $target did not verify."
        printf -v "$state_var" '%s' present
        printf -v "$digest_var" '%s' "$before"
    else
        printf -v "$state_var" '%s' absent
        printf -v "$digest_var" '%s' absent
    fi
}

assert_unchanged_public_file() {
    local target="$1"
    local prior_state="$2"
    local prior_digest="$3"

    assert_regular_or_absent "$target"
    if [[ "$prior_state" == "present" ]]; then
        test -f "$target" || stop "$target disappeared after the backup."
        [[ "$(file_digest "$target")" == "$prior_digest" ]] || stop "$target changed after the backup."
    else
        test ! -e "$target" || stop "$target appeared after the backup."
    fi
}

recover_public_file() {
    local state="$1"
    local had_old="$2"
    local target="$3"
    local old_swap="$4"
    local failed_path="$5"

    # The protected old path is the durable transaction indicator. Inspecting
    # it covers a signal after mv succeeds but before the next state assignment.
    if [[ "$had_old" == "1" ]]; then
        if test -f "$old_swap"; then
            if test -e "$target" || test -L "$target"; then
                if ! test -f "$target" || test -L "$target" || ! mv -- "$target" "$failed_path"; then
                    echo "CRITICAL: could not move the replacement away from $target (state: $state)." >&2
                    return 1
                fi
            fi
            if ! mv -- "$old_swap" "$target"; then
                if test -f "$failed_path" && ! test -e "$target"; then
                    mv -- "$failed_path" "$target" || true
                fi
                echo "CRITICAL: could not restore the previous $target (state: $state)." >&2
                return 1
            fi
        elif [[ "$state" != "preparing" && "$state" != "moving-old" ]]; then
            echo "CRITICAL: $old_swap is missing in transaction state $state." >&2
            return 1
        fi
    else
        # The original path was absent. If the prepared file has already been
        # renamed into place (including the post-mv/pre-state window), move it
        # back out so recovery reproduces that absence exactly.
        if [[ "$state" == "moving-new" || "$state" == "new-active" || "$state" == "verified" ]] &&
            { test -e "$target" || test -L "$target"; }; then
            if ! test -f "$target" || test -L "$target" || ! mv -- "$target" "$failed_path"; then
                echo "CRITICAL: could not recover the original absence of $target (state: $state)." >&2
                return 1
            fi
        fi
    fi
}

recover_theme() {
    local state="$1"
    local target="$2"
    local old_swap="$3"
    local failed_path="$4"

    # As with public files, OLD_THEME existing proves that the first rename
    # completed even if a signal arrived before THEME_STATE was advanced.
    if test -d "$old_swap"; then
        if test -e "$target" || test -L "$target"; then
            if ! test -d "$target" || test -L "$target" || ! mv -- "$target" "$failed_path"; then
                echo "CRITICAL: could not move the replacement theme away (state: $state)." >&2
                return 1
            fi
        fi
        if ! mv -- "$old_swap" "$target"; then
            if test -d "$failed_path" && ! test -e "$target"; then
                mv -- "$failed_path" "$target" || true
            fi
            echo "CRITICAL: could not restore the previous production theme (state: $state)." >&2
            return 1
        fi
    elif [[ "$state" != "preparing" && "$state" != "moving-old" ]]; then
        echo "CRITICAL: $old_swap is missing in production theme state $state." >&2
        return 1
    elif ! test -d "$target" || test -L "$target"; then
        echo "CRITICAL: neither the original production theme nor its protected swap is available (state: $state)." >&2
        return 1
    fi
}

[[ "${CONFIRM_PRODUCTION_DEPLOY:-}" == "$PRODUCTION_HOST" ]] ||
    stop "set CONFIRM_PRODUCTION_DEPLOY=$PRODUCTION_HOST to authorize this production transaction."

command -v uapi >/dev/null || stop "cPanel UAPI was not found."
PHP_BIN="$(command -v php || true)"
GIT_BIN="$(command -v git || true)"
FLOCK_BIN="$(command -v flock || true)"
test -x "$PHP_BIN" || stop "PHP CLI was not found."
test -x "$GIT_BIN" || stop "Git was not found."
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

printf 'Production root: %s\nStaging root:    %s\nPinned content: %s\n' \
    "$PRODUCTION_ROOT" "$STAGING_ROOT" "$VALIDATED_PRODUCTION_COMMIT"

test -f "$PRODUCTION_ROOT/wp-load.php" || stop "production WordPress root was not confirmed."
test -f "$STAGING_ROOT/wp-load.php" || stop "staging WordPress root was not confirmed."

THEMES_PARENT="${PRODUCTION_ROOT}/wp-content/themes"
TARGET="${PRODUCTION_ROOT}/${THEME_REL}"
STAGING_TARGET="${STAGING_ROOT}/${THEME_REL}"
ROBOTS_TARGET="${PRODUCTION_ROOT}/robots.txt"
LLMS_TARGET="${PRODUCTION_ROOT}/llms.txt"
LLMS_FULL_TARGET="${PRODUCTION_ROOT}/llms-full.txt"

test -d "$THEMES_PARENT" || stop "the production themes directory was not found."
test -d "$TARGET" && test -f "$TARGET/functions.php" || stop "the production child theme was not found."
test -d "$STAGING_TARGET" && test -f "$STAGING_TARGET/functions.php" ||
    stop "the deployed staging child theme was not found; production promotion requires the tested staging tree."

THEMES_PARENT_REAL="$(realpath -e "$THEMES_PARENT")"
TARGET_REAL="$(realpath -e "$TARGET")"
[[ "$THEMES_PARENT_REAL" == "$THEMES_PARENT" ]] || stop "a production themes path component is a symlink."
[[ "$TARGET_REAL" == "$TARGET" ]] || stop "the production child theme path is a symlink."

case "$TARGET_REAL" in
    "$STAGING_ROOT" | "$STAGING_ROOT"/*) stop "the production theme resolves inside staging." ;;
esac

if test -e "$STAGING_TARGET"; then
    STAGING_TARGET_REAL="$(realpath -e "$STAGING_TARGET")"
    [[ "$STAGING_TARGET_REAL" == "$STAGING_TARGET" ]] || stop "the deployed staging child theme path is a symlink."
    [[ "$STAGING_TARGET_REAL" != "$TARGET_REAL" ]] || stop "production and staging themes resolve to the same directory."
    [[ "$(stat -Lc '%d:%i' "$STAGING_TARGET_REAL")" != "$(stat -Lc '%d:%i' "$TARGET_REAL")" ]] ||
        stop "production and staging themes identify the same filesystem directory."
fi

if find "$STAGING_TARGET" -type l -print -quit | grep -q .; then
    stop "the deployed staging theme contains a symlink and cannot be used as promotion evidence."
fi

test -w "$PRODUCTION_ROOT" || stop "the production webroot is not writable."
test -w "$THEMES_PARENT" && test -w "$TARGET" || stop "the production theme area is not writable."

if find "$TARGET" -type l -print -quit | grep -q .; then
    stop "the production theme contains a symlink and cannot produce a guaranteed recovery archive."
fi
assert_regular_or_absent "$LLMS_TARGET"
assert_regular_or_absent "$LLMS_FULL_TARGET"
assert_regular_or_absent "$ROBOTS_TARGET"

test -d "$REPO/.git" || stop "repository clone was not found."
REPO_REAL="$(realpath -e "$REPO")"
[[ "$REPO_REAL" == "$REPO" ]] || stop "the repository path contains a symlink or non-canonical component."
case "$REPO_REAL" in
    "$PRODUCTION_ROOT" | "$PRODUCTION_ROOT"/* | "$STAGING_ROOT" | "$STAGING_ROOT"/*)
        stop "the Git repository must remain outside both webroots."
        ;;
esac

if test -n "$(git -C "$REPO" status --porcelain)"; then
    git -C "$REPO" status --short >&2
    stop "repository contains local changes."
fi

git -C "$REPO" fetch origin --prune
git -C "$REPO" switch main
git -C "$REPO" pull --ff-only origin main

test -z "$(git -C "$REPO" status --porcelain)" || stop "repository became dirty while updating main."
git -C "$REPO" cat-file -e "${VALIDATED_PRODUCTION_COMMIT}^{commit}" || stop "the validated production commit is unavailable locally."
git -C "$REPO" merge-base --is-ancestor "$VALIDATED_PRODUCTION_COMMIT" HEAD ||
    stop "the validated production commit is not an ancestor of main."
for required_path in "$THEME_REL" "$ROBOTS_REL" "$LLMS_REL" "$LLMS_FULL_REL"; do
    git -C "$REPO" cat-file -e "${VALIDATED_PRODUCTION_COMMIT}:${required_path}" ||
        stop "the validated commit does not contain $required_path."
done
git -C "$REPO" diff --quiet "$VALIDATED_PRODUCTION_COMMIT" HEAD -- \
    "$THEME_REL" "$ROBOTS_REL" "$LLMS_REL" "$LLMS_FULL_REL" ||
    stop "main's production payload differs from the validated production commit."

umask 077
mkdir -p "$BACKUP_DIR"
chmod 0700 "$BACKUP_DIR"
BACKUP_DIR_REAL="$(realpath -e "$BACKUP_DIR")"
[[ "$BACKUP_DIR_REAL" == "$BACKUP_DIR" ]] || stop "the private backup path contains a symlink or non-canonical component."
case "$BACKUP_DIR_REAL" in
    "$PRODUCTION_ROOT" | "$PRODUCTION_ROOT"/* | "$STAGING_ROOT" | "$STAGING_ROOT"/*)
        stop "the private backup directory is inside a webroot."
        ;;
esac

LOCK_FILE="${BACKUP_DIR}/production-deploy.lock"
test ! -L "$LOCK_FILE" || stop "the production transaction lock is a symlink."
exec 9>"$LOCK_FILE"
chmod 0600 "$LOCK_FILE"
flock -n 9 || stop "another production deploy or rollback is running."

STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BUILD_PARENT="${THEMES_PARENT}/.eta-production-build-${STAMP}"
ARCHIVE_ROOT="${BUILD_PARENT}/archive"
NEW_THEME="${ARCHIVE_ROOT}/${THEME_REL}"
OLD_THEME="${THEMES_PARENT}/.eta-production-previous-${STAMP}"
DISCOVERY_STAGE="${PRODUCTION_ROOT}/.eta-production-public-${STAMP}"
NEW_ROBOTS="${DISCOVERY_STAGE}/robots.txt"
NEW_LLMS="${DISCOVERY_STAGE}/llms.txt"
NEW_LLMS_FULL="${DISCOVERY_STAGE}/llms-full.txt"
OLD_ROBOTS="${PRODUCTION_ROOT}/.eta-robots-previous-${STAMP}.txt"
OLD_LLMS="${PRODUCTION_ROOT}/.eta-llms-previous-${STAMP}.txt"
OLD_LLMS_FULL="${PRODUCTION_ROOT}/.eta-llms-full-previous-${STAMP}.txt"
BACKUP_SET="${BACKUP_DIR}/production-before-${STAMP}"

for protected_path in "$BUILD_PARENT" "$OLD_THEME" "$DISCOVERY_STAGE" "$OLD_ROBOTS" "$OLD_LLMS" "$OLD_LLMS_FULL" "$BACKUP_SET"; do
    test ! -e "$protected_path" && test ! -L "$protected_path" || stop "protected transaction path already exists: $protected_path"
done

mkdir -m 0700 "$BUILD_PARENT" "$ARCHIVE_ROOT" "$DISCOVERY_STAGE" "$BACKUP_SET"

git -C "$REPO" archive --format=tar "$VALIDATED_PRODUCTION_COMMIT" \
    "$THEME_REL" "$ROBOTS_REL" "$LLMS_REL" "$LLMS_FULL_REL" |
    tar -xf - -C "$ARCHIVE_ROOT"

test -f "$NEW_THEME/functions.php" || stop "the pinned Git archive did not contain the child theme."
test -s "${ARCHIVE_ROOT}/${ROBOTS_REL}" || stop "the pinned Git archive did not contain robots.txt."
test -s "${ARCHIVE_ROOT}/${LLMS_REL}" || stop "the pinned Git archive did not contain llms.txt."
test -s "${ARCHIVE_ROOT}/${LLMS_FULL_REL}" || stop "the pinned Git archive did not contain llms-full.txt."
if find "$ARCHIVE_ROOT" -type l -print -quit | grep -q .; then
    stop "the pinned production payload contains a symlink."
fi

echo "Normalizing and linting the exact pinned production payload..."
find "$NEW_THEME" -type d -exec chmod 0755 {} +
find "$NEW_THEME" -type f -exec chmod 0644 {} +
find "$NEW_THEME" -type f -name '*.php' -print0 |
    xargs -0 -r -n1 "$PHP_BIN" -l

if find "$NEW_THEME" -type d ! -perm 0755 -print -quit | grep -q .; then
    stop "a prepared theme directory does not have mode 0755."
fi
if find "$NEW_THEME" -type f ! -perm 0644 -print -quit | grep -q .; then
    stop "a prepared theme file does not have mode 0644."
fi

install -m 0644 -- "${ARCHIVE_ROOT}/${ROBOTS_REL}" "$NEW_ROBOTS"
install -m 0644 -- "${ARCHIVE_ROOT}/${LLMS_REL}" "$NEW_LLMS"
install -m 0644 -- "${ARCHIVE_ROOT}/${LLMS_FULL_REL}" "$NEW_LLMS_FULL"

PREPARED_THEME_DIGEST="$(tree_digest "$NEW_THEME")"
PREPARED_ROBOTS_DIGEST="$(file_digest "$NEW_ROBOTS")"
PREPARED_LLMS_DIGEST="$(file_digest "$NEW_LLMS")"
PREPARED_LLMS_FULL_DIGEST="$(file_digest "$NEW_LLMS_FULL")"
ORIGINAL_THEME_DIGEST="$(tree_digest "$TARGET")"
STAGING_THEME_DIGEST="$(tree_digest "$STAGING_TARGET")"
[[ "$STAGING_THEME_DIGEST" == "$PREPARED_THEME_DIGEST" ]] ||
    stop "the deployed staging theme is not the exact pinned production candidate; redeploy and validate staging first."

echo "Creating and verifying a private production recovery set..."
tar -czf "$BACKUP_SET/theme.tar.gz" -C "$PRODUCTION_ROOT" "$THEME_REL"
tar -tzf "$BACKUP_SET/theme.tar.gz" >/dev/null
[[ "$(tree_digest "$TARGET")" == "$ORIGINAL_THEME_DIGEST" ]] || stop "the production theme changed while it was being backed up."
printf '%s\n' "$ORIGINAL_THEME_DIGEST" >"$BACKUP_SET/theme-tree.sha256"

ROBOTS_PRIOR_STATE=""
ROBOTS_PRIOR_DIGEST=""
LLMS_PRIOR_STATE=""
LLMS_PRIOR_DIGEST=""
LLMS_FULL_PRIOR_STATE=""
LLMS_FULL_PRIOR_DIGEST=""
backup_public_file "$ROBOTS_TARGET" "$BACKUP_SET/robots.txt.before" ROBOTS_PRIOR_STATE ROBOTS_PRIOR_DIGEST
backup_public_file "$LLMS_TARGET" "$BACKUP_SET/llms.txt.before" LLMS_PRIOR_STATE LLMS_PRIOR_DIGEST
backup_public_file "$LLMS_FULL_TARGET" "$BACKUP_SET/llms-full.txt.before" LLMS_FULL_PRIOR_STATE LLMS_FULL_PRIOR_DIGEST

printf 'robots.txt\t%s\nllms.txt\t%s\nllms-full.txt\t%s\n' \
    "$ROBOTS_PRIOR_STATE" "$LLMS_PRIOR_STATE" "$LLMS_FULL_PRIOR_STATE" >"$BACKUP_SET/discovery-state.tsv"
printf 'host\t%s\ncreated_utc\t%s\nvalidated_commit\t%s\nrepository_head\t%s\nproduction_root\t%s\nstaging_theme_digest\t%s\noriginal_theme_digest\t%s\nreplacement_theme_digest\t%s\nreplacement_robots_digest\t%s\nreplacement_llms_digest\t%s\nreplacement_llms_full_digest\t%s\n' \
    "$PRODUCTION_HOST" "$STAMP" "$VALIDATED_PRODUCTION_COMMIT" \
    "$(git -C "$REPO" rev-parse HEAD)" "$PRODUCTION_ROOT" \
    "$STAGING_THEME_DIGEST" "$ORIGINAL_THEME_DIGEST" "$PREPARED_THEME_DIGEST" \
    "$PREPARED_ROBOTS_DIGEST" "$PREPARED_LLMS_DIGEST" "$PREPARED_LLMS_FULL_DIGEST" >"$BACKUP_SET/deployment-metadata.tsv"

(
    cd "$BACKUP_SET"
    find . -maxdepth 1 -type f ! -name MANIFEST.sha256 -printf '%P\0' |
        LC_ALL=C sort -z |
        xargs -0 -r sha256sum >MANIFEST.sha256
    sha256sum -c MANIFEST.sha256
)
find "$BACKUP_SET" -type f -exec chmod 0600 {} +

MARKER_TMP="${BACKUP_DIR}/.LAST_PRODUCTION_BACKUP.${STAMP}"
test ! -e "$MARKER_TMP" && test ! -L "$MARKER_TMP" || stop "the temporary production backup marker already exists."
printf '%s\n' "$BACKUP_SET" >"$MARKER_TMP"
chmod 0600 "$MARKER_TMP"
mv -f -- "$MARKER_TMP" "$BACKUP_MARKER"

THEME_STATE="preparing"
ROBOTS_STATE="preparing"
LLMS_STATE="preparing"
LLMS_FULL_STATE="preparing"
if [[ "$ROBOTS_PRIOR_STATE" == "present" ]]; then ROBOTS_HAD_OLD=1; else ROBOTS_HAD_OLD=0; fi
if [[ "$LLMS_PRIOR_STATE" == "present" ]]; then LLMS_HAD_OLD=1; else LLMS_HAD_OLD=0; fi
if [[ "$LLMS_FULL_PRIOR_STATE" == "present" ]]; then LLMS_FULL_HAD_OLD=1; else LLMS_FULL_HAD_OLD=0; fi

cleanup() {
    local status=$?
    local recovery_failed=0
    trap - EXIT
    trap '' INT TERM

    if ((status != 0)); then
        echo "Production transaction did not commit; restoring every path..." >&2

        recover_public_file "$ROBOTS_STATE" "$ROBOTS_HAD_OLD" \
            "$ROBOTS_TARGET" "$OLD_ROBOTS" "$DISCOVERY_STAGE/failed-robots.txt" || recovery_failed=1
        recover_public_file "$LLMS_FULL_STATE" "$LLMS_FULL_HAD_OLD" \
            "$LLMS_FULL_TARGET" "$OLD_LLMS_FULL" "$DISCOVERY_STAGE/failed-llms-full.txt" || recovery_failed=1
        recover_public_file "$LLMS_STATE" "$LLMS_HAD_OLD" \
            "$LLMS_TARGET" "$OLD_LLMS" "$DISCOVERY_STAGE/failed-llms.txt" || recovery_failed=1

        recover_theme "$THEME_STATE" "$TARGET" "$OLD_THEME" \
            "$BUILD_PARENT/failed-active-theme" || recovery_failed=1

        if ((recovery_failed == 0)) && test -d "$TARGET"; then
            case "$BUILD_PARENT" in
                "$THEMES_PARENT"/.eta-production-build-*) rm -rf --one-file-system -- "$BUILD_PARENT" || true ;;
            esac
            case "$DISCOVERY_STAGE" in
                "$PRODUCTION_ROOT"/.eta-production-public-*) rm -rf --one-file-system -- "$DISCOVERY_STAGE" || true ;;
            esac
            echo "All production paths were restored; the verified private backup remains at $BACKUP_SET." >&2
        else
            echo "CRITICAL: recovery was incomplete. Preserve $BACKUP_SET and inspect the protected swap paths." >&2
        fi
    fi

    exit "$status"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

[[ "$(tree_digest "$TARGET")" == "$ORIGINAL_THEME_DIGEST" ]] || stop "the production theme changed after the backup."
[[ "$(tree_digest "$STAGING_TARGET")" == "$PREPARED_THEME_DIGEST" ]] ||
    stop "the staging theme changed after promotion validation."
assert_unchanged_public_file "$LLMS_TARGET" "$LLMS_PRIOR_STATE" "$LLMS_PRIOR_DIGEST"
assert_unchanged_public_file "$LLMS_FULL_TARGET" "$LLMS_FULL_PRIOR_STATE" "$LLMS_FULL_PRIOR_DIGEST"
assert_unchanged_public_file "$ROBOTS_TARGET" "$ROBOTS_PRIOR_STATE" "$ROBOTS_PRIOR_DIGEST"

echo "Atomically activating the validated production theme..."
THEME_STATE="moving-old"
mv -- "$TARGET" "$OLD_THEME"
THEME_STATE="old-moved"
THEME_STATE="moving-new"
mv -- "$NEW_THEME" "$TARGET"
THEME_STATE="new-active"

echo "Atomically activating the reviewed AI discovery files..."
if test -f "$ROBOTS_TARGET"; then
    ROBOTS_STATE="moving-old"
    mv -- "$ROBOTS_TARGET" "$OLD_ROBOTS"
    ROBOTS_STATE="old-moved"
else
    ROBOTS_STATE="no-old"
fi
ROBOTS_STATE="moving-new"
mv -- "$NEW_ROBOTS" "$ROBOTS_TARGET"
ROBOTS_STATE="new-active"

if test -f "$LLMS_TARGET"; then
    LLMS_STATE="moving-old"
    mv -- "$LLMS_TARGET" "$OLD_LLMS"
    LLMS_STATE="old-moved"
else
    LLMS_STATE="no-old"
fi
LLMS_STATE="moving-new"
mv -- "$NEW_LLMS" "$LLMS_TARGET"
LLMS_STATE="new-active"

if test -f "$LLMS_FULL_TARGET"; then
    LLMS_FULL_STATE="moving-old"
    mv -- "$LLMS_FULL_TARGET" "$OLD_LLMS_FULL"
    LLMS_FULL_STATE="old-moved"
else
    LLMS_FULL_STATE="no-old"
fi
LLMS_FULL_STATE="moving-new"
mv -- "$NEW_LLMS_FULL" "$LLMS_FULL_TARGET"
LLMS_FULL_STATE="new-active"

[[ "$(realpath -e "$TARGET")" == "$TARGET" ]] || stop "the deployed production theme path did not verify."
[[ "$(realpath -e "$ROBOTS_TARGET")" == "$ROBOTS_TARGET" ]] || stop "the deployed robots.txt path did not verify."
[[ "$(realpath -e "$LLMS_TARGET")" == "$LLMS_TARGET" ]] || stop "the deployed llms.txt path did not verify."
[[ "$(realpath -e "$LLMS_FULL_TARGET")" == "$LLMS_FULL_TARGET" ]] || stop "the deployed llms-full.txt path did not verify."
[[ "$(tree_digest "$TARGET")" == "$PREPARED_THEME_DIGEST" ]] || stop "the deployed production theme digest did not verify."
[[ "$(file_digest "$ROBOTS_TARGET")" == "$PREPARED_ROBOTS_DIGEST" ]] || stop "the deployed robots.txt digest did not verify."
[[ "$(file_digest "$LLMS_TARGET")" == "$PREPARED_LLMS_DIGEST" ]] || stop "the deployed llms.txt digest did not verify."
[[ "$(file_digest "$LLMS_FULL_TARGET")" == "$PREPARED_LLMS_FULL_DIGEST" ]] || stop "the deployed llms-full.txt digest did not verify."
[[ "$(stat -Lc '%a' "$ROBOTS_TARGET")" == "644" ]] || stop "robots.txt does not have mode 0644."
[[ "$(stat -Lc '%a' "$LLMS_TARGET")" == "644" ]] || stop "llms.txt does not have mode 0644."
[[ "$(stat -Lc '%a' "$LLMS_FULL_TARGET")" == "644" ]] || stop "llms-full.txt does not have mode 0644."
if find "$TARGET" -type d ! -perm 0755 -print -quit | grep -q .; then
    stop "a deployed theme directory does not have mode 0755."
fi
if find "$TARGET" -type f ! -perm 0644 -print -quit | grep -q .; then
    stop "a deployed theme file does not have mode 0644."
fi
find "$TARGET" -type f -name '*.php' -print0 |
    xargs -0 -r -n1 "$PHP_BIN" -l

THEME_STATE="verified"
ROBOTS_STATE="verified"
LLMS_STATE="verified"
LLMS_FULL_STATE="verified"

# All public paths and their digests have verified. Commit the transaction
# before deleting the protected prior paths so a signal cannot roll back only
# part of an already verified deployment.
trap - EXIT INT TERM

chmod -R u+rwX,go-rwx "$OLD_THEME" || true
case "$OLD_THEME" in
    "$THEMES_PARENT"/.eta-production-previous-*) rm -rf --one-file-system -- "$OLD_THEME" || echo "WARNING: protected prior theme needs manual cleanup." >&2 ;;
esac
rm -f -- "$OLD_ROBOTS" "$OLD_LLMS" "$OLD_LLMS_FULL" || echo "WARNING: protected prior discovery files need manual cleanup." >&2
case "$BUILD_PARENT" in
    "$THEMES_PARENT"/.eta-production-build-*) rm -rf --one-file-system -- "$BUILD_PARENT" || echo "WARNING: protected build tree needs manual cleanup." >&2 ;;
esac
case "$DISCOVERY_STAGE" in
    "$PRODUCTION_ROOT"/.eta-production-public-*) rm -rf --one-file-system -- "$DISCOVERY_STAGE" || echo "WARNING: protected discovery staging tree needs manual cleanup." >&2 ;;
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

printf '\nPRODUCTION DEPLOYMENT COMMITTED\nPinned content commit: %s\nRepository HEAD: %s\nTheme digest: %s\nrobots.txt digest: %s\nllms.txt digest: %s\nllms-full.txt digest: %s\nRecovery set: %s\nApplication cache: %s\n\n' \
    "$VALIDATED_PRODUCTION_COMMIT" "$(git -C "$REPO" rev-parse HEAD)" \
    "$PREPARED_THEME_DIGEST" "$PREPARED_ROBOTS_DIGEST" "$PREPARED_LLMS_DIGEST" "$PREPARED_LLMS_FULL_DIGEST" \
    "$BACKUP_SET" "$PURGE_RESULT"
printf '%s\n' \
    "EXTERNAL EDGE/WAF ACTION STILL REQUIRED: purge the CDN/edge cache separately and configure crawler/discovery-path allow rules without disabling the firewall."
