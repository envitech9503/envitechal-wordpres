#!/usr/bin/env bash

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

declare -a missing_packages=()

command -v git >/dev/null 2>&1 || missing_packages+=(git)
command -v curl >/dev/null 2>&1 || missing_packages+=(curl)
if command -v php >/dev/null 2>&1; then
    php -r 'exit(class_exists("DOMDocument") ? 0 : 1);' || missing_packages+=(php-xml)
else
    missing_packages+=(php-cli php-xml)
fi
command -v python3 >/dev/null 2>&1 || missing_packages+=(python3)

if ((${#missing_packages[@]} > 0)); then
    if ! command -v apt-get >/dev/null 2>&1; then
        printf 'Missing required tools and apt-get is unavailable: %s\n' "${missing_packages[*]}" >&2
        exit 1
    fi

    if [[ "$(id -u)" -eq 0 ]]; then
        apt-get update
        apt-get install -y --no-install-recommends "${missing_packages[@]}"
    elif command -v sudo >/dev/null 2>&1; then
        sudo apt-get update
        sudo apt-get install -y --no-install-recommends "${missing_packages[@]}"
    else
        printf 'Missing required tools and setup cannot elevate: %s\n' "${missing_packages[*]}" >&2
        exit 1
    fi
fi

git config --global --add safe.directory "$REPO_ROOT"

for command_name in bash git curl php python3; do
    command -v "$command_name" >/dev/null 2>&1 || {
        printf 'Required command is unavailable after setup: %s\n' "$command_name" >&2
        exit 1
    }
done

bash scripts/test-ai-visibility.sh

printf 'Codex cloud setup and local validation completed successfully.\n'
