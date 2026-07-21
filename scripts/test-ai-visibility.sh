#!/usr/bin/env bash

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

include_live=0
PYTHON_BIN="${PYTHON_BIN:-python3}"
if (($# > 1)); then
    printf 'Usage: %s [--include-live]\n' "$0" >&2
    exit 2
fi
if (($# == 1)); then
    [[ "$1" == '--include-live' ]] || {
        printf 'Usage: %s [--include-live]\n' "$0" >&2
        exit 2
    }
    include_live=1
fi

for command_name in bash php "$PYTHON_BIN"; do
    command -v "$command_name" >/dev/null 2>&1 || {
        printf 'Required command is unavailable: %s\n' "$command_name" >&2
        exit 1
    }
done

while IFS= read -r -d '' php_file; do
    php -l "$php_file" >/dev/null
done < <(find wp-content/themes/generatepress-envitechal -type f -name '*.php' -print0)

while IFS= read -r -d '' script_file; do
    bash -n "$script_file"
done < <(find scripts -type f -name '*.sh' -print0)

while IFS= read -r -d '' test_file; do
    case "$test_file" in
        *.php) php "$test_file" ;;
        *.py) "$PYTHON_BIN" "$test_file" ;;
    esac
done < <(find tests -maxdepth 1 -type f \( -name '*.php' -o -name '*.py' \) -print0 | sort -z)

if grep -R -n -E --exclude='test-ai-visibility.sh' \
    'API_KEY[[:space:]]*:|BEGIN (RSA|OPENSSH|EC) PRIVATE KEY|DB_PASSWORD|SECURE_AUTH_KEY' \
    wp-content/themes/generatepress-envitechal deploy scripts tests; then
    printf 'A possible secret was found in a tracked project path.\n' >&2
    exit 1
fi

if ((include_live != 0)); then
    command -v curl >/dev/null 2>&1 || {
        printf 'curl is required for live validation.\n' >&2
        exit 1
    }
    bash scripts/check-ai-visibility-live.sh
fi

printf 'AI visibility validation completed successfully.\n'
