#!/usr/bin/env bash

set -Eeuo pipefail

STAGING_HOST="staging.envitechal.com"
PRODUCTION_HOST="envitechal.com"
THEME_REL="wp-content/themes/generatepress-envitechal"
BACKUP_DIR="${HOME}/backups/envitechal-ai-visibility"

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
THEMES_PARENT="${STAGING_ROOT}/wp-content/themes"
TARGET="${STAGING_ROOT}/${THEME_REL}"
PRODUCTION_TARGET="${PRODUCTION_ROOT}/${THEME_REL}"
BACKUP_MARKER="${BACKUP_DIR}/LAST_STAGING_THEME_BACKUP"
PHP_BIN="$(command -v php || true)"

[[ "$STAGING_ROOT" == "$HOME/"* ]] || stop "staging root is outside this cPanel home."
[[ "$STAGING_ROOT" != "$PRODUCTION_ROOT" ]] || stop "staging and production resolve to the same directory."
test -f "$STAGING_ROOT/wp-load.php" || stop "staging WordPress root was not confirmed."
test -d "$TARGET" && test -f "$TARGET/functions.php" || stop "the deployed staging child theme was not found."
test -x "$PHP_BIN" || stop "PHP CLI was not found."

if find "$TARGET" -type l -print -quit | grep -q .; then
    stop "deployed staging theme contains a symlink and cannot produce a guaranteed failed-version archive."
fi

THEMES_PARENT_REAL="$(realpath -e "$THEMES_PARENT")"
TARGET_REAL="$(realpath -e "$TARGET")"
[[ "$THEMES_PARENT_REAL" == "$THEMES_PARENT" ]] || stop "a staging themes path component is a symlink."
[[ "$TARGET_REAL" == "$TARGET" ]] || stop "the staging child theme path is a symlink."

case "$TARGET_REAL" in
    "$PRODUCTION_ROOT" | "$PRODUCTION_ROOT"/*) stop "staging theme resolves inside the production document root." ;;
esac

if test -e "$PRODUCTION_TARGET"; then
    PRODUCTION_TARGET_REAL="$(realpath -e "$PRODUCTION_TARGET")"
    [[ "$TARGET_REAL" != "$PRODUCTION_TARGET_REAL" ]] || stop "staging and production themes resolve to the same directory."
fi

test -f "$BACKUP_MARKER" || stop "staging backup marker was not found."
BACKUP="$(<"$BACKUP_MARKER")"

case "$BACKUP" in
    "$BACKUP_DIR"/staging-envitechal-com-theme-before-*.tar.gz) ;;
    *) stop "unexpected backup path." ;;
esac

test -f "$BACKUP" && test -f "$BACKUP.sha256" || stop "backup or checksum file is missing."
sha256sum -c "$BACKUP.sha256"

umask 077
STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BUILD_PARENT="${THEMES_PARENT}/.eta-restore-${STAMP}"
RESTORE_DIR="${BUILD_PARENT}/generatepress-envitechal"
OLD_SWAP="${THEMES_PARENT}/.eta-failed-${STAMP}"
FAILED_ARCHIVE="${BACKUP_DIR}/staging-theme-failed-${STAMP}.tar.gz"
SWAP_STATE="preparing"

cleanup() {
    local status=$?
    trap - EXIT INT TERM

    if ((status != 0)); then
        if ! test -e "$TARGET" && test -d "$OLD_SWAP"; then
            mv "$OLD_SWAP" "$TARGET" || echo "CRITICAL: deployed theme could not be restored to TARGET." >&2
        elif [[ "$SWAP_STATE" != "verified" && "$SWAP_STATE" != "complete" ]] && test -d "$TARGET" && test -d "$OLD_SWAP"; then
            if mv "$TARGET" "$RESTORE_DIR"; then
                if mv "$OLD_SWAP" "$TARGET"; then
                    echo "The deployed staging theme was restored automatically." >&2
                else
                    mv "$RESTORE_DIR" "$TARGET" || true
                    echo "CRITICAL: automatic rollback recovery failed." >&2
                fi
            fi
        fi

        if test -d "$BUILD_PARENT" && test -d "$TARGET"; then
            case "$BUILD_PARENT" in
                "$THEMES_PARENT"/.eta-restore-*) rm -rf --one-file-system -- "$BUILD_PARENT" || true ;;
            esac
        fi
    fi

    exit "$status"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

mkdir -m 0700 "$BUILD_PARENT"
tar -xzf "$BACKUP" -C "$BUILD_PARENT" --strip-components=2
test -f "$RESTORE_DIR/functions.php" || stop "restored archive does not contain the child theme."

if find "$RESTORE_DIR" -type l -print -quit | grep -q .; then
    stop "restored archive contains a symlink."
fi

echo "Normalizing restored public theme permissions..."
find "$RESTORE_DIR" -type d -exec chmod 0755 {} +
find "$RESTORE_DIR" -type f -exec chmod 0644 {} +

find "$RESTORE_DIR" -type f -name '*.php' -print0 |
    xargs -0 -n1 "$PHP_BIN" -l
RESTORE_DIGEST="$(tree_digest "$RESTORE_DIR")"

tar -czf "$FAILED_ARCHIVE" -C "$STAGING_ROOT" "$THEME_REL"
tar -tzf "$FAILED_ARCHIVE" >/dev/null
sha256sum "$FAILED_ARCHIVE" >"$FAILED_ARCHIVE.sha256"
sha256sum -c "$FAILED_ARCHIVE.sha256"
chmod 0600 "$FAILED_ARCHIVE" "$FAILED_ARCHIVE.sha256"

SWAP_STATE="moving-old"
mv "$TARGET" "$OLD_SWAP"
SWAP_STATE="old-moved"
SWAP_STATE="moving-new"
mv "$RESTORE_DIR" "$TARGET"
SWAP_STATE="new-active"

TARGET_REAL_AFTER="$(realpath -e "$TARGET")"
[[ "$TARGET_REAL_AFTER" == "$TARGET" ]] || stop "restored theme path did not verify."
[[ "$(tree_digest "$TARGET")" == "$RESTORE_DIGEST" ]] || stop "restored theme digest did not verify."

find "$TARGET" -type f -name '*.php' -print0 |
    xargs -0 -n1 "$PHP_BIN" -l

SWAP_STATE="verified"
chmod -R u+rwX,go-rwx "$OLD_SWAP" || true
case "$OLD_SWAP" in
    "$THEMES_PARENT"/.eta-failed-*) rm -rf --one-file-system -- "$OLD_SWAP" || echo "WARNING: protected failed tree needs manual cleanup." >&2 ;;
esac
rmdir "$BUILD_PARENT" || echo "WARNING: empty protected restore directory needs manual cleanup." >&2
SWAP_STATE="complete"
trap - EXIT INT TERM

printf 'ROLLBACK COMPLETE\nRestored: %s\nFailed version archive: %s\n' \
    "$BACKUP" "$FAILED_ARCHIVE"
