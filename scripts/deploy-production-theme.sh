#!/usr/bin/env bash

set -Eeuo pipefail

STAGING_HOST="staging.envitechal.com"
PRODUCTION_HOST="envitechal.com"
REPO="${HOME}/repositories/envitechal-wordpres"
THEME_REL="wp-content/themes/generatepress-envitechal"
VALIDATED_PR_COMMIT="778f619"
BACKUP_DIR="${HOME}/backups/envitechal-production"

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
            xargs -0 sha256sum
    ) | sha256sum | awk '{print $1}'
}

STAGING_ROOT="$(realpath -e "$(get_docroot "$STAGING_HOST")")"
PRODUCTION_ROOT="$(realpath -e "$(get_docroot "$PRODUCTION_HOST")")"
# This script deploys to PRODUCTION. The staging root is resolved only so the
# two can be proven distinct.
DEPLOY_ROOT="$PRODUCTION_ROOT"
THEMES_PARENT="${DEPLOY_ROOT}/wp-content/themes"
TARGET="${DEPLOY_ROOT}/${THEME_REL}"
STAGING_TARGET="${STAGING_ROOT}/${THEME_REL}"
[[ "${ETA_CONFIRM_PRODUCTION:-}" == "yes" ]] || stop "set ETA_CONFIRM_PRODUCTION=yes to deploy to envitechal.com."

printf 'Staging root:    %s\nProduction root: %s\n' "$STAGING_ROOT" "$PRODUCTION_ROOT"

[[ "$DEPLOY_ROOT" == "$HOME/"* ]] || stop "production root is outside this cPanel home."
[[ "$STAGING_ROOT" != "$PRODUCTION_ROOT" ]] || stop "staging and production resolve to the same directory."
test -f "$DEPLOY_ROOT/wp-load.php" || stop "production WordPress root was not confirmed."
test -d "$TARGET" && test -f "$TARGET/functions.php" || stop "the production child theme was not found."
test -d "$REPO/.git" || stop "repository clone was not found."

THEMES_PARENT_REAL="$(realpath -e "$THEMES_PARENT")"
TARGET_REAL="$(realpath -e "$TARGET")"
[[ "$THEMES_PARENT_REAL" == "$THEMES_PARENT" ]] || stop "a production themes path component is a symlink."
[[ "$TARGET_REAL" == "$TARGET" ]] || stop "the production child theme path is a symlink."

case "$TARGET_REAL" in
    "$STAGING_ROOT" | "$STAGING_ROOT"/*) stop "production theme resolves inside the staging document root." ;;
esac

if test -e "$STAGING_TARGET"; then
    STAGING_TARGET_REAL="$(realpath -e "$STAGING_TARGET")"
    [[ "$TARGET_REAL" != "$STAGING_TARGET_REAL" ]] || stop "staging and production themes resolve to the same directory."
fi

test -w "$THEMES_PARENT" && test -w "$TARGET" || stop "production theme area is not writable."

if find "$TARGET" -type l -print -quit | grep -q .; then
    stop "existing production theme contains a symlink and cannot produce a guaranteed rollback archive."
fi

if test -n "$(git -C "$REPO" status --porcelain)"; then
    git -C "$REPO" status --short >&2
    stop "repository contains local changes."
fi

git -C "$REPO" fetch origin --prune
git -C "$REPO" switch main
git -C "$REPO" pull --ff-only origin main

git -C "$REPO" cat-file -e "${VALIDATED_PR_COMMIT}^{commit}" || stop "validated PR commit is unavailable locally."
git -C "$REPO" diff --quiet "$VALIDATED_PR_COMMIT" HEAD -- "$THEME_REL" || stop "main's theme differs from the validated PR theme."

PHP_BIN="$(command -v php || true)"
test -x "$PHP_BIN" || stop "PHP CLI was not found."

umask 077
mkdir -p "$BACKUP_DIR"
chmod 0700 "$BACKUP_DIR"

STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BUILD_PARENT="${THEMES_PARENT}/.eta-deploy-${STAMP}"
NEW_THEME="${BUILD_PARENT}/generatepress-envitechal"
OLD_SWAP="${THEMES_PARENT}/.eta-previous-${STAMP}"
BACKUP="${BACKUP_DIR}/envitechal-com-theme-before-${STAMP}.tar.gz"
SWAP_STATE="preparing"

cleanup() {
    local status=$?
    trap - EXIT INT TERM

    if ((status != 0)); then
        if ! test -e "$TARGET" && test -d "$OLD_SWAP"; then
            mv "$OLD_SWAP" "$TARGET" || echo "CRITICAL: previous theme could not be restored to TARGET." >&2
        elif [[ "$SWAP_STATE" != "verified" && "$SWAP_STATE" != "complete" ]] && test -d "$TARGET" && test -d "$OLD_SWAP"; then
            if mv "$TARGET" "$NEW_THEME"; then
                if mv "$OLD_SWAP" "$TARGET"; then
                    echo "The previous production theme was restored automatically." >&2
                else
                    mv "$NEW_THEME" "$TARGET" || true
                    echo "CRITICAL: automatic production restoration failed; use the verified backup." >&2
                fi
            fi
        fi

        if test -d "$BUILD_PARENT" && test -d "$TARGET"; then
            case "$BUILD_PARENT" in
                "$THEMES_PARENT"/.eta-deploy-*) rm -rf --one-file-system -- "$BUILD_PARENT" || true ;;
            esac
        fi
    fi

    exit "$status"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

mkdir -m 0700 "$BUILD_PARENT"
git -C "$REPO" archive --format=tar "$VALIDATED_PR_COMMIT" "$THEME_REL" |
    tar -xf - -C "$BUILD_PARENT" --strip-components=2

test -f "$NEW_THEME/functions.php" || stop "pinned Git archive did not contain the child theme."
if find "$NEW_THEME" -type l -print -quit | grep -q .; then
    stop "pinned theme tree contains a symlink."
fi

echo "Normalizing public theme permissions..."
find "$NEW_THEME" -type d -exec chmod 0755 {} +
find "$NEW_THEME" -type f -exec chmod 0644 {} +

echo "Linting the prepared pinned production tree..."
find "$NEW_THEME" -type f -name '*.php' -print0 |
    xargs -0 -n1 "$PHP_BIN" -l
PREPARED_DIGEST="$(tree_digest "$NEW_THEME")"

tar -czf "$BACKUP" -C "$DEPLOY_ROOT" "$THEME_REL"
tar -tzf "$BACKUP" >/dev/null
sha256sum "$BACKUP" >"$BACKUP.sha256"
sha256sum -c "$BACKUP.sha256"
printf '%s\n' "$BACKUP" >"$BACKUP_DIR/LAST_PRODUCTION_THEME_BACKUP"
chmod 0600 "$BACKUP" "$BACKUP.sha256" "$BACKUP_DIR/LAST_PRODUCTION_THEME_BACKUP"

echo "Swapping the validated theme into production..."
SWAP_STATE="moving-old"
mv "$TARGET" "$OLD_SWAP"
SWAP_STATE="old-moved"
SWAP_STATE="moving-new"
mv "$NEW_THEME" "$TARGET"
SWAP_STATE="new-active"

TARGET_REAL_AFTER="$(realpath -e "$TARGET")"
[[ "$TARGET_REAL_AFTER" == "$TARGET" ]] || stop "deployed theme path did not verify."
[[ "$(tree_digest "$TARGET")" == "$PREPARED_DIGEST" ]] || stop "deployed production tree digest did not verify."

find "$TARGET" -type f -name '*.php' -print0 |
    xargs -0 -n1 "$PHP_BIN" -l

SWAP_STATE="verified"
echo "Requesting a production LiteSpeed cache purge..."
WP_CLI="$(command -v wp || true)"
if test -n "$WP_CLI"; then
    if "$WP_CLI" --path="$DEPLOY_ROOT" eval '
        if (!has_action("litespeed_purge_all")) {
            fwrite(STDERR, "LiteSpeed purge hook is unavailable.\n");
            exit(1);
        }
        do_action("litespeed_purge_all");
    '; then
        echo "Production LiteSpeed cache purge requested."
    else
        echo "WARNING: WP-CLI could not request the production LiteSpeed purge; purge production manually." >&2
    fi
else
    echo "WARNING: WP-CLI is unavailable; purge the production LiteSpeed cache manually." >&2
fi

chmod -R u+rwX,go-rwx "$OLD_SWAP" || true
case "$OLD_SWAP" in
    "$THEMES_PARENT"/.eta-previous-*) rm -rf --one-file-system -- "$OLD_SWAP" || echo "WARNING: protected previous tree needs manual cleanup." >&2 ;;
esac
rmdir "$BUILD_PARENT" || echo "WARNING: empty protected build directory needs manual cleanup." >&2
SWAP_STATE="complete"
trap - EXIT INT TERM

printf '\nPRODUCTION THEME DEPLOYED\nPinned theme commit: %s\nRepository HEAD: %s\nBackup: %s\n' \
    "$VALIDATED_PR_COMMIT" "$(git -C "$REPO" rev-parse HEAD)" "$BACKUP"
