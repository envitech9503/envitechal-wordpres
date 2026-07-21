#!/usr/bin/env bash

set -Eeuo pipefail

PRODUCTION_HOST="envitechal.com"
STAGING_HOST="staging.envitechal.com"
TARGET_ENVIRONMENT="${DISCOVERY_CACHE_TARGET:-}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
REPO="$(cd "$SCRIPT_DIR/.." && pwd -P)"
REMEDIATION_SCRIPT="$SCRIPT_DIR/remediate-discovery-cache-headers.sh"
HTACCESS_HELPER="$SCRIPT_DIR/lib/discovery-cache-htaccess.php"
HEADER_HELPER="$SCRIPT_DIR/lib/validate-discovery-cache-headers.php"
BACKUP_DIR="${HOME}/backups/envitechal-ai-visibility"
STAGING_ATTESTATION="$BACKUP_DIR/LAST_STAGING_DISCOVERY_CACHE_VERIFICATION"
MANAGED_BEGIN="# BEGIN Envi Tech AL discovery cache policy"
MANAGED_END="# END Envi Tech AL discovery cache policy"

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

file_digest() {
    sha256sum "$1" | awk '{print $1}'
}

purge_application_cache() {
    local root="$1"

    "$WP_CLI" --path="$root" eval \
        'if (!has_action("litespeed_purge_all")) { fwrite(STDERR, "LiteSpeed purge hook is unavailable.\n"); exit(1); } do_action("litespeed_purge_all");'
}

assert_body() {
    local environment="$1"
    local path="$2"
    local body="$3"
    local normalized_body

    if grep -Eiq 'One moment, please|Checking your browser|Verify you are human|Access denied' "$body"; then
        stop "$path returned a challenge or access-denied response."
    fi

    case "$path" in
        '/robots.txt')
            grep -Fq 'User-agent: *' "$body" || stop "robots.txt is missing its user-agent policy."
            if [[ "$environment" == "staging" ]]; then
                grep -Fq 'Disallow: /' "$body" || stop "staging robots.txt is not closed to crawling."
            else
                normalized_body="$(tr -d '\r' <"$body")"
                grep -Fq 'User-agent: OAI-SearchBot' "$body" ||
                    stop "production robots.txt is missing the OAI-SearchBot policy."
                [[ "$normalized_body" == *$'User-agent: OAI-SearchBot\nAllow: /\nContent-Signal: ai-train=no, search=yes, ai-input=yes'* ]] ||
                    stop "production robots.txt does not explicitly allow OAI-SearchBot."
                [[ "$normalized_body" == *$'User-agent: GPTBot\nDisallow: /\nContent-Signal: ai-train=no, search=yes, ai-input=yes'* ]] ||
                    stop "production robots.txt does not explicitly disallow GPTBot."
                grep -Fq 'Sitemap: https://envitechal.com/sitemap_index.xml' "$body" ||
                    stop "production robots.txt is missing the canonical sitemap."
            fi
            ;;
        '/llms.txt')
            grep -Fq '# Envi Tech AL' "$body" || stop "llms.txt is missing its reviewed identity marker."
            ;;
        '/llms-full.txt')
            grep -Fq '# Envi Tech AL full AI-readable corpus' "$body" || stop "llms-full.txt is missing its reviewed corpus marker."
            ;;
        '/.well-known/agent-skills/index.json')
            php -r '
                $body = file_get_contents($argv[1]);
                $json = is_string($body) ? json_decode($body, true) : null;
                if (!is_array($json) || json_last_error() !== JSON_ERROR_NONE || $json === []) {
                    fwrite(STDERR, "Agent-skills index is not a non-empty JSON object or array.\n");
                    exit(1);
                }
            ' "$body" || stop "agent-skills index body validation failed."
            ;;
    esac
}

verify_discovery_request() {
    local environment="$1"
    local host="$2"
    local path="$3"
    local method="$4"
    local suffix="$5"
    local request_dir="$6"
    local label metadata status mime url request_url headers body expected_mime attempt retry_after
    local -a curl_args

    label="${method}-${path//\//_}-${suffix:-canonical}"
    headers="$request_dir/${label}.headers"
    body="$request_dir/${label}.body"
    metadata="$request_dir/${label}.metadata"
    url="https://${host}${path}${suffix}"

    curl_args=(
        --http1.1 --silent --show-error --path-as-is
        --connect-timeout 10 --max-time 30
        --user-agent 'Mozilla/5.0 (compatible; OAI-SearchBot/1.0; +https://openai.com/searchbot)'
        --header 'Accept: */*'
        --dump-header "$headers"
        --output "$body"
        --write-out $'%{http_code}\n%{content_type}\n%{url_effective}\n'
    )
    if [[ -n "$suffix" ]]; then
        curl_args+=(--header 'Cache-Control: no-cache')
    fi
    if [[ "$method" == "HEAD" ]]; then
        curl_args+=(--head)
    fi

    attempt=1
    while :; do
        request_url="$url"
        if ((attempt > 1)); then
            if [[ "$url" == *\?* ]]; then
                request_url="${url}&eta_discovery_cache_retry=${attempt}"
            else
                request_url="${url}?eta_discovery_cache_retry=${attempt}"
            fi
        fi
        curl "${curl_args[@]}" "$request_url" >"$metadata" || stop "$method $request_url failed."
        status="$(sed -n '1p' "$metadata")"
        if [[ "$status" != "429" || "$attempt" -ge 4 ]]; then
            break
        fi
        retry_after="$(sed -nE 's/^[Rr]etry-[Aa]fter:[[:space:]]*([0-9]+).*$/\1/p' "$headers" | tail -n 1)"
        [[ "$retry_after" =~ ^[0-9]+$ ]] || retry_after=15
        ((retry_after > 60)) && retry_after=60
        printf 'WARN: %s %s returned 429; retrying in %ss (attempt %s/4).\n' \
            "$method" "$request_url" "$retry_after" "$attempt" >&2
        sleep "$retry_after"
        attempt=$((attempt + 1))
    done
    sleep 0.25
    mime="$(sed -n '2p' "$metadata")"
    [[ "$status" == "200" ]] || stop "$method $url returned HTTP $status."

    if [[ "$path" == '/.well-known/agent-skills/index.json' ]]; then
        expected_mime='application/json'
    else
        expected_mime='text/plain'
    fi
    [[ "${mime,,}" == *"$expected_mime"* ]] ||
        stop "$method $url returned '$mime' instead of $expected_mime."

    php "$HEADER_HELPER" "$headers" >/dev/null ||
        stop "$method $url did not return the one reviewed short cache policy."

    if [[ "$method" == "GET" ]]; then
        test -s "$body" || stop "GET $url returned an empty body."
        assert_body "$environment" "$path" "$body"
    fi

    printf 'PASS: %s %s (%s)\n' "$method" "$url" "$mime"
}

verify_discovery_host() {
    local environment="$1"
    local host="$2"
    local request_dir="$3"
    local suffix path method
    local -a paths

    paths=('/robots.txt' '/llms.txt' '/llms-full.txt')
    if [[ "$environment" == "production" ]]; then
        paths+=('/.well-known/agent-skills/index.json')
    fi

    for path in "${paths[@]}"; do
        for suffix in '' "?eta_discovery_cache_verify=${STAMP}"; do
            for method in GET HEAD; do
                verify_discovery_request "$environment" "$host" "$path" "$method" "$suffix" "$request_dir"
            done
        done
    done
}

verify_negative_scope() {
    local host="$1"
    local request_dir="$2"
    local path headers status

    for path in '/' '/wp-content/themes/generatepress-envitechal/style.css'; do
        headers="$request_dir/negative-$(printf '%s' "$path" | sha256sum | awk '{print $1}').headers"
        status="$(curl --http1.1 --silent --show-error --path-as-is --max-redirs 0 \
            --connect-timeout 10 --max-time 30 --head --output /dev/null \
            --user-agent 'Mozilla/5.0 (compatible; EnviTechCacheScopeVerifier/1.0)' \
            --header 'Cache-Control: no-cache' --dump-header "$headers" --write-out '%{http_code}' \
            "https://${host}${path}?eta_negative_cache_scope=${STAMP}")" ||
            stop "negative-scope request failed for $path."
        sleep 0.25
        [[ "$status" == "200" ]] || stop "negative-scope path $path returned $status instead of 200."
        php "$HEADER_HELPER" --assert-absent "$headers" >/dev/null ||
            stop "managed discovery cache policy leaked onto ordinary path $path."
        printf 'PASS: managed discovery cache policy is absent from %s\n' "$path"
    done
}

verify_production_redirects() {
    local request_dir="$1"
    local pair source target method headers body metadata status location redirect_by target_status
    local -a redirect_pairs
    local -a redirect_curl_args
    declare -A verified_targets=()

    redirect_pairs=(
        '/certificates-approvals/|/accreditations-certifications/'
        '/newsupdates/|/blognewsupdates/'
        '/our-services/|/services/'
        '/air-quality-testing/|/gaseous-air-emission-testing-lab-near-me/'
        '/environmental-testing-services/|/services/analytical-lab-services/'
        '/water-testing-lab-karachi/|/services/water-testing-lab-services/'
        '/services/water-testing-services/|/services/water-testing-lab-services/'
        '/water-testing-in-pakistan/|/services/water-testing-lab-services/'
        '/water-testing-lab-near-me/|/services/water-testing-lab-services/'
        '/water-quality-testing-mastering-your-ultimate-guide-to-excellence/|/services/water-testing-lab-services/'
        '/get-accurate-results-from-our-water-testing-lab-in-lahore/|/lahore-environmental-lab/'
        '/reliable-water-testing-services-environmental-lab-karachi/|/karachi-environmental-lab/'
        '/discover-the-best-testing-laboratory-near-you-for-reliable-and-accurate-results/|/how-to-choose-the-suitable-environmental-lab/'
        '/https-envitechal-com-services-environmental-consultancy/|/services/environmental-consultancy/'
        '/https-envitechal-com-calibration-of-equipment-in-karachi/|/services/equipment-calibration-services/'
        '/22653-2/|/services/water-testing-lab-services/'
        '/hiring-an-environmental-lab/|/how-to-choose-the-suitable-environmental-lab/'
        '/environmental-water-testing-lab-in-pakistan/|/services/water-testing-lab-services/'
        '/environmental-lab-excellence-the-services-of-envi-tech-al/|/services/analytical-lab-services/'
        '/whats-new-focused-insightful-of-environmental-lab/|/services/analytical-lab-services/'
        '/environmental-testing-lab-in-lahore/|/lahore-environmental-lab/'
        '/environmental-testing-lab-in-karachi-lahore/|/services/analytical-lab-services/'
        '/frequently-asked-questions-water-testing-in-karachi/|/environmental-testing-faqs-pakistan/'
        '/sindh-epa-noc-guide/|/services/environmental-consultancy/'
        '/consulting-services-for-ginners-gots-ocs-regenagri-certification/|/services/certification-advisory/'
        '/unlock-precision-why-calibration-services-in-karachi-are-non%E2%80%90negotiable-for-industry-success/|/services/equipment-calibration-services/'
        '/unlock-precision-why-calibration-services-in-karachi-are-non-negotiable-for-industry-success/|/services/equipment-calibration-services/'
    )

    for pair in "${redirect_pairs[@]}"; do
        source="${pair%%|*}"
        target="${pair#*|}"
        for method in GET HEAD; do
            headers="$request_dir/redirect-${method}-$(printf '%s' "$source" | sha256sum | awk '{print $1}').headers"
            body="$request_dir/redirect-${method}.body"
            metadata="$request_dir/redirect-${method}.metadata"
            redirect_curl_args=(
                --http1.1 --silent --show-error --path-as-is --max-redirs 0
                --connect-timeout 10 --max-time 30
                --user-agent 'Mozilla/5.0 (compatible; EnviTechRedirectVerifier/1.0)'
                --header 'Cache-Control: no-cache'
                --dump-header "$headers" --output "$body"
                --write-out '%{http_code}'
            )
            if [[ "$method" == "HEAD" ]]; then
                redirect_curl_args+=(--head)
            fi
            curl "${redirect_curl_args[@]}" \
                "https://${PRODUCTION_HOST}${source}?eta_redirect_verify=${STAMP}" >"$metadata" ||
                stop "$method redirect verification request failed for $source."
            sleep 0.25
            status="$(cat "$metadata")"
            location="$(sed -nE 's/^[Ll]ocation:[[:space:]]*(.*)\r?$/\1/p' "$headers" | tail -n 1)"
            redirect_by="$(sed -nE 's/^[Xx]-[Rr]edirect-[Bb]y:[[:space:]]*(.*)\r?$/\1/p' "$headers" | tail -n 1)"
            [[ "$status" == "301" ]] || stop "$method $source returned $status instead of 301."
            [[ "$location" == "https://${PRODUCTION_HOST}${target}" ]] ||
                stop "$method $source redirected to '$location' instead of https://${PRODUCTION_HOST}${target}."
            [[ "$redirect_by" == "Envi Tech AL" ]] ||
                stop "$method $source was not handled by the reviewed theme redirect handler (X-Redirect-By='$redirect_by')."
            printf 'PASS: %s %s -> %s (X-Redirect-By: Envi Tech AL)\n' "$method" "$source" "$target"
        done

        if [[ -z "${verified_targets[$target]:-}" ]]; then
            target_status="$(curl --http1.1 --silent --show-error --path-as-is --max-redirs 0 \
                --connect-timeout 10 --max-time 30 --head --output /dev/null \
                --user-agent 'Mozilla/5.0 (compatible; EnviTechRedirectVerifier/1.0)' \
                --header 'Cache-Control: no-cache' --write-out '%{http_code}' \
                "https://${PRODUCTION_HOST}${target}?eta_redirect_target_verify=${STAMP}")" ||
                stop "direct target verification failed for $target."
            sleep 0.25
            [[ "$target_status" == "200" ]] || stop "redirect target $target returned $target_status instead of a direct 200."
            verified_targets[$target]=1
            printf 'PASS: direct target %s returned 200\n' "$target"
        fi
    done
}

case "$TARGET_ENVIRONMENT" in
    staging)
        TARGET_HOST="$STAGING_HOST"
        [[ "${CONFIRM_STAGING_DISCOVERY_CACHE:-}" == "$STAGING_HOST" ]] ||
            stop "set CONFIRM_STAGING_DISCOVERY_CACHE=$STAGING_HOST to authorize the staging transaction."
        ;;
    production)
        TARGET_HOST="$PRODUCTION_HOST"
        [[ "${CONFIRM_PRODUCTION_DISCOVERY_CACHE:-}" == "$PRODUCTION_HOST" ]] ||
            stop "set CONFIRM_PRODUCTION_DISCOVERY_CACHE=$PRODUCTION_HOST to authorize the production transaction."
        ;;
    *)
        stop "set DISCOVERY_CACHE_TARGET to staging or production."
        ;;
esac

for command in uapi php curl git flock realpath sha256sum wp; do
    command -v "$command" >/dev/null || stop "$command is required."
done
WP_CLI="$(command -v wp)"
test -x "$WP_CLI" || stop "WP-CLI is required before beginning the transaction."
test -f "$REMEDIATION_SCRIPT" && test ! -L "$REMEDIATION_SCRIPT" || stop "the remediation script is missing or a symlink."
test -f "$HTACCESS_HELPER" && test ! -L "$HTACCESS_HELPER" || stop "the .htaccess helper is missing or a symlink."
test -f "$HEADER_HELPER" && test ! -L "$HEADER_HELPER" || stop "the response-header helper is missing or a symlink."
test -d "$REPO/.git" || stop "run this script from the cPanel Git clone."
test -z "$(git -C "$REPO" status --porcelain)" || stop "the repository has local changes."
git -C "$REPO" fetch origin --prune
[[ "$(git -C "$REPO" symbolic-ref --quiet --short HEAD)" == "main" ]] || stop "the repository must be on main."
REPO_COMMIT="$(git -C "$REPO" rev-parse HEAD)"
ORIGIN_MAIN_COMMIT="$(git -C "$REPO" rev-parse refs/remotes/origin/main)"
[[ "$REPO_COMMIT" == "$ORIGIN_MAIN_COMMIT" ]] || stop "local main is not exactly origin/main."
REMEDIATION_SCRIPT_DIGEST="$(file_digest "$REMEDIATION_SCRIPT")"
HTACCESS_HELPER_DIGEST="$(file_digest "$HTACCESS_HELPER")"
HEADER_HELPER_DIGEST="$(file_digest "$HEADER_HELPER")"

HOME_REAL="$(realpath -e "$HOME")"
PRODUCTION_ROOT_RAW="$(get_docroot "$PRODUCTION_HOST")"
STAGING_ROOT_RAW="$(get_docroot "$STAGING_HOST")"
PRODUCTION_ROOT="$(realpath -e "$PRODUCTION_ROOT_RAW")"
STAGING_ROOT="$(realpath -e "$STAGING_ROOT_RAW")"
[[ "$PRODUCTION_ROOT_RAW" == "$PRODUCTION_ROOT" ]] || stop "the production document root contains a symlink or non-canonical component."
[[ "$STAGING_ROOT_RAW" == "$STAGING_ROOT" ]] || stop "the staging document root is not canonical."
[[ "$PRODUCTION_ROOT" == "$HOME_REAL/"* ]] || stop "the production document root is outside this cPanel home."
[[ "$STAGING_ROOT" == "$HOME_REAL/"* ]] || stop "the staging document root is outside this cPanel home."
[[ "$PRODUCTION_ROOT" != "$STAGING_ROOT" ]] || stop "production and staging resolve to the same directory."
case "$PRODUCTION_ROOT" in
    "$STAGING_ROOT"/*) stop "production is nested inside staging." ;;
esac
case "$STAGING_ROOT" in
    "$PRODUCTION_ROOT"/*) stop "staging is nested inside production." ;;
esac
[[ "$(stat -Lc '%d:%i' "$PRODUCTION_ROOT")" != "$(stat -Lc '%d:%i' "$STAGING_ROOT")" ]] ||
    stop "production and staging identify the same filesystem directory."
test -f "$PRODUCTION_ROOT/wp-load.php" || stop "the production WordPress root was not confirmed."
test -f "$STAGING_ROOT/wp-load.php" || stop "the staging WordPress root was not confirmed."
if [[ "$TARGET_ENVIRONMENT" == "production" ]]; then
    TARGET_ROOT="$PRODUCTION_ROOT"
else
    TARGET_ROOT="$STAGING_ROOT"
fi

umask 077
mkdir -p "$BACKUP_DIR"
chmod 0700 "$BACKUP_DIR"
BACKUP_DIR_REAL="$(realpath -e "$BACKUP_DIR")"
[[ "$BACKUP_DIR_REAL" == "$BACKUP_DIR" ]] || stop "the private backup path is not canonical."
case "$BACKUP_DIR_REAL" in
    "$PRODUCTION_ROOT" | "$PRODUCTION_ROOT"/* | "$STAGING_ROOT" | "$STAGING_ROOT"/*)
        stop "the private backup directory is inside a public webroot."
        ;;
esac

LOCK_FILE="$BACKUP_DIR/discovery-cache-remediation.lock"
test ! -L "$LOCK_FILE" || stop "the transaction lock is a symlink."
exec 9>"$LOCK_FILE"
chmod 0600 "$LOCK_FILE"
flock -n 9 || stop "another discovery-cache transaction is running."

STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BLOCK_FILE="$BACKUP_DIR/.discovery-cache-block-${STAMP}"
cat >"$BLOCK_FILE" <<'HTACCESS_BLOCK'
# BEGIN Envi Tech AL discovery cache policy
<IfModule mod_setenvif.c>
    SetEnvIf Request_URI "^/(robots\.txt|llms\.txt|llms-full\.txt|\.well-known/agent-skills/index\.json)(\?.*)?$" ETA_DISCOVERY_SHORT_CACHE=1
</IfModule>
<IfModule mod_headers.c>
    Header onsuccess unset Expires env=ETA_DISCOVERY_SHORT_CACHE
    Header always unset Expires env=ETA_DISCOVERY_SHORT_CACHE
    # PHP/LSAPI response headers live in Apache's always table. Remove any
    # normal-table value, then replace the always-table value exactly once.
    Header onsuccess unset Cache-Control env=ETA_DISCOVERY_SHORT_CACHE
    Header always set Cache-Control "public, max-age=300, s-maxage=3600, must-revalidate" env=ETA_DISCOVERY_SHORT_CACHE
    # Production serves these resources as physical files. A hosting/plugin
    # FilesMatch context can run after the outer per-directory header rules,
    # so replace both header tables again in an equally specific, URI-gated
    # context. Staging's virtual WordPress responses resolve to index.php and
    # continue to use the outer PHP/LSAPI handling above.
    <FilesMatch "^(robots\.txt|llms\.txt|llms-full\.txt|index\.json)$">
        Header unset Expires env=ETA_DISCOVERY_SHORT_CACHE
        Header always unset Expires env=ETA_DISCOVERY_SHORT_CACHE
        Header always unset Cache-Control env=ETA_DISCOVERY_SHORT_CACHE
        Header set Cache-Control "public, max-age=300, s-maxage=3600, must-revalidate" env=ETA_DISCOVERY_SHORT_CACHE
    </FilesMatch>
</IfModule>
# END Envi Tech AL discovery cache policy
HTACCESS_BLOCK
chmod 0600 "$BLOCK_FILE"
BLOCK_DIGEST="$(file_digest "$BLOCK_FILE")"

if [[ "$TARGET_ENVIRONMENT" == "production" ]]; then
    test -f "$STAGING_ATTESTATION" && test ! -L "$STAGING_ATTESTATION" ||
        stop "no staging discovery-cache attestation exists; run the staging transaction first."
    grep -Fxq "host=${STAGING_HOST}" "$STAGING_ATTESTATION" || stop "the staging attestation host is invalid."
    grep -Fxq "block_sha256=${BLOCK_DIGEST}" "$STAGING_ATTESTATION" ||
        stop "the staging attestation is for a different managed block."
    grep -Fxq "repo_commit=${REPO_COMMIT}" "$STAGING_ATTESTATION" ||
        stop "the staging attestation is for a different repository commit."
    grep -Fxq "remediation_script_sha256=${REMEDIATION_SCRIPT_DIGEST}" "$STAGING_ATTESTATION" ||
        stop "the staging attestation used a different remediation script."
    grep -Fxq "htaccess_helper_sha256=${HTACCESS_HELPER_DIGEST}" "$STAGING_ATTESTATION" ||
        stop "the staging attestation used a different .htaccess transformer."
    grep -Fxq "header_helper_sha256=${HEADER_HELPER_DIGEST}" "$STAGING_ATTESTATION" ||
        stop "the staging attestation used a different response-header validator."
    attestation_age=$(( $(date +%s) - $(stat -Lc '%Y' "$STAGING_ATTESTATION") ))
    ((attestation_age >= 0 && attestation_age <= 86400)) ||
        stop "the staging attestation is older than 24 hours; rerun staging verification."

    STAGING_HTACCESS="$STAGING_ROOT/.htaccess"
    test -f "$STAGING_HTACCESS" && test ! -L "$STAGING_HTACCESS" ||
        stop "staging .htaccess is missing or not a regular file."
    STAGING_EXPECTED="$BACKUP_DIR/.staging-htaccess-expected-${STAMP}"
    php "$HTACCESS_HELPER" "$STAGING_HTACCESS" "$BLOCK_FILE" "$STAGING_EXPECTED" staging >/dev/null
    [[ "$(file_digest "$STAGING_HTACCESS")" == "$(file_digest "$STAGING_EXPECTED")" ]] ||
        stop "staging no longer contains the exact reviewed managed block."
    rm -f -- "$STAGING_EXPECTED"

    STAGING_RECHECK="$BACKUP_DIR/.staging-discovery-recheck-${STAMP}"
    mkdir -m 0700 "$STAGING_RECHECK"
    verify_discovery_host staging "$STAGING_HOST" "$STAGING_RECHECK"
    verify_negative_scope "$STAGING_HOST" "$STAGING_RECHECK"
    rm -rf -- "$STAGING_RECHECK"
fi

HTACCESS="$TARGET_ROOT/.htaccess"
test ! -L "$HTACCESS" || stop "$HTACCESS is a symlink."
if test -e "$HTACCESS"; then
    test -f "$HTACCESS" || stop "$HTACCESS is not a regular file."
    ORIGINAL_STATE=present
    ORIGINAL_MODE="$(stat -Lc '%a' "$HTACCESS")"
    [[ "$ORIGINAL_MODE" =~ ^[0-7]{3,4}$ ]] || stop "could not read the original .htaccess mode."
else
    ORIGINAL_STATE=absent
    ORIGINAL_MODE=0644
fi
test -w "$TARGET_ROOT" || stop "the target document root is not writable."

BACKUP_SET="$BACKUP_DIR/${TARGET_ENVIRONMENT}-discovery-cache-before-${STAMP}"
CANDIDATE="$TARGET_ROOT/.eta-discovery-htaccess-${STAMP}"
ROLLBACK_CANDIDATE="$TARGET_ROOT/.eta-discovery-rollback-${STAMP}"
for path in "$BACKUP_SET" "$CANDIDATE" "$ROLLBACK_CANDIDATE"; do
    test ! -e "$path" && test ! -L "$path" || stop "protected transaction path exists: $path"
done
mkdir -m 0700 "$BACKUP_SET"

if [[ "$ORIGINAL_STATE" == "present" ]]; then
    ORIGINAL_DIGEST="$(file_digest "$HTACCESS")"
    install -m 0600 -- "$HTACCESS" "$BACKUP_SET/htaccess.before"
    [[ "$(file_digest "$BACKUP_SET/htaccess.before")" == "$ORIGINAL_DIGEST" ]] ||
        stop "the private .htaccess backup did not verify."
    SOURCE="$BACKUP_SET/htaccess.before"
else
    ORIGINAL_DIGEST=absent
    SOURCE="$BACKUP_SET/htaccess.absent"
    : >"$SOURCE"
    chmod 0600 "$SOURCE"
fi

TRANSFORM_RESULT="$(php "$HTACCESS_HELPER" "$SOURCE" "$BLOCK_FILE" "$CANDIDATE" "$TARGET_ENVIRONMENT")" ||
    stop "the fail-closed .htaccess transformation rejected the current file."
if [[ "$TARGET_ENVIRONMENT" == "production" ]]; then
    [[ "$TRANSFORM_RESULT" == 'removed_rules=0' || "$TRANSFORM_RESULT" == 'removed_rules=9' ]] ||
        stop "production did not remove either all nine reviewed duplicate rules or none."
else
    [[ "$TRANSFORM_RESULT" == 'removed_rules=0' ]] || stop "staging unexpectedly changed redirect rules."
fi
chmod "$ORIGINAL_MODE" "$CANDIDATE"
CANDIDATE_DIGEST="$(file_digest "$CANDIDATE")"

printf 'environment\t%s\nhost\t%s\ncreated_utc\t%s\ndocument_root\t%s\nrepo_commit\t%s\nremediation_script_digest\t%s\nhtaccess_helper_digest\t%s\nheader_helper_digest\t%s\noriginal_state\t%s\noriginal_mode\t%s\noriginal_digest\t%s\ncandidate_digest\t%s\nmanaged_block_digest\t%s\ntransform\t%s\n' \
    "$TARGET_ENVIRONMENT" "$TARGET_HOST" "$STAMP" "$TARGET_ROOT" \
    "$REPO_COMMIT" "$REMEDIATION_SCRIPT_DIGEST" "$HTACCESS_HELPER_DIGEST" "$HEADER_HELPER_DIGEST" \
    "$ORIGINAL_STATE" "$ORIGINAL_MODE" "$ORIGINAL_DIGEST" "$CANDIDATE_DIGEST" "$BLOCK_DIGEST" "$TRANSFORM_RESULT" \
    >"$BACKUP_SET/metadata.tsv"
(
    cd "$BACKUP_SET"
    find . -maxdepth 1 -type f ! -name MANIFEST.sha256 -printf '%P\0' |
        LC_ALL=C sort -z |
        xargs -0 -r sha256sum >MANIFEST.sha256
    sha256sum -c MANIFEST.sha256
)
find "$BACKUP_SET" -type f -exec chmod 0600 {} +

COMMITTED=0
RESTORE_REQUIRED=0
cleanup() {
    local status=$?
    local active_digest=''
    local active_mode=''
    local drift_snapshot
    local failed_path
    local invalidated_attestation
    local rollback_verified=0
    trap - EXIT
    trap '' INT TERM

    if ((status != 0 && RESTORE_REQUIRED != 0)); then
        echo "Discovery-cache verification failed; restoring the exact prior .htaccess state..." >&2
        if [[ "$ORIGINAL_STATE" == "present" ]]; then
            if test -f "$HTACCESS" && test ! -L "$HTACCESS" &&
                active_digest="$(file_digest "$HTACCESS")" &&
                active_mode="$(stat -Lc '%a' "$HTACCESS")"; then
                if [[ "$active_digest" == "$ORIGINAL_DIGEST" && "$active_mode" == "$ORIGINAL_MODE" ]]; then
                    rollback_verified=1
                elif [[ "$active_digest" == "$CANDIDATE_DIGEST" && "$active_mode" == "$ORIGINAL_MODE" ]]; then
                    if install -m "$ORIGINAL_MODE" -- "$BACKUP_SET/htaccess.before" "$ROLLBACK_CANDIDATE" &&
                        mv -f -- "$ROLLBACK_CANDIDATE" "$HTACCESS"; then
                        if test -f "$HTACCESS" && test ! -L "$HTACCESS" &&
                            active_digest="$(file_digest "$HTACCESS")" &&
                            active_mode="$(stat -Lc '%a' "$HTACCESS")" &&
                            [[ "$active_digest" == "$ORIGINAL_DIGEST" ]] &&
                            [[ "$active_mode" == "$ORIGINAL_MODE" ]]; then
                            rollback_verified=1
                        fi
                    else
                        echo "CRITICAL: the verified prior .htaccess could not be atomically restored." >&2
                    fi
                else
                    drift_snapshot="$BACKUP_SET/drifted-active.htaccess"
                    if install -m 0600 -- "$HTACCESS" "$drift_snapshot"; then
                        echo "Preserved a private snapshot of the drifted active .htaccess at $drift_snapshot." >&2
                    else
                        echo "CRITICAL: the drifted active .htaccess could not be snapshotted." >&2
                    fi
                    echo "CRITICAL: active .htaccess drifted after activation; automatic rollback refused to avoid overwriting an external change." >&2
                fi
            elif test -e "$HTACCESS" || test -L "$HTACCESS"; then
                echo "CRITICAL: active .htaccess is not a safe readable regular file; automatic rollback refused." >&2
            else
                echo "CRITICAL: active .htaccess disappeared after activation; automatic rollback refused." >&2
            fi
        elif test -e "$HTACCESS" || test -L "$HTACCESS"; then
            if test -f "$HTACCESS" && test ! -L "$HTACCESS" &&
                active_digest="$(file_digest "$HTACCESS")" &&
                active_mode="$(stat -Lc '%a' "$HTACCESS")"; then
                if [[ "$active_digest" == "$CANDIDATE_DIGEST" && "$active_mode" == "$ORIGINAL_MODE" ]]; then
                    failed_path="$BACKUP_SET/failed-active.htaccess"
                    if mv -- "$HTACCESS" "$failed_path"; then
                        if test ! -e "$HTACCESS" && test ! -L "$HTACCESS"; then
                            rollback_verified=1
                        fi
                    else
                        echo "CRITICAL: could not restore the original absence of .htaccess." >&2
                    fi
                else
                    drift_snapshot="$BACKUP_SET/drifted-active.htaccess"
                    if install -m 0600 -- "$HTACCESS" "$drift_snapshot"; then
                        echo "Preserved a private snapshot of the drifted active .htaccess at $drift_snapshot." >&2
                    else
                        echo "CRITICAL: the drifted active .htaccess could not be snapshotted." >&2
                    fi
                    echo "CRITICAL: active .htaccess drifted after activation; automatic rollback refused to avoid deleting an external change." >&2
                fi
            else
                echo "CRITICAL: active .htaccess is not a safe readable regular file; automatic rollback refused." >&2
            fi
        else
            rollback_verified=1
        fi
        if ((rollback_verified != 0)); then
            echo "ROLLBACK VERIFIED: the exact prior .htaccess state and mode were restored." >&2
        else
            echo "CRITICAL: the exact prior .htaccess state, digest, or mode was not restored." >&2
        fi
        if [[ "$TARGET_ENVIRONMENT" == "staging" ]] &&
            (test -e "$STAGING_ATTESTATION" || test -L "$STAGING_ATTESTATION"); then
            if test -f "$STAGING_ATTESTATION" && test ! -L "$STAGING_ATTESTATION"; then
                invalidated_attestation="$BACKUP_DIR/INVALIDATED_STAGING_DISCOVERY_CACHE_VERIFICATION.${STAMP}"
                if mv -- "$STAGING_ATTESTATION" "$invalidated_attestation" &&
                    chmod 0600 "$invalidated_attestation"; then
                    echo "Invalidated the prior staging attestation after the failed transaction." >&2
                else
                    echo "CRITICAL: the prior staging attestation could not be safely invalidated." >&2
                fi
            else
                echo "CRITICAL: the staging attestation is not a safe regular file and was not moved." >&2
            fi
        fi
        purge_application_cache "$TARGET_ROOT" || true
        echo "The verified private recovery set remains at $BACKUP_SET." >&2
    fi

    rm -f -- "$CANDIDATE" "$ROLLBACK_CANDIDATE" "$BLOCK_FILE" || true
    exit "$status"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

if [[ "$ORIGINAL_STATE" == "present" ]]; then
    [[ "$(file_digest "$HTACCESS")" == "$ORIGINAL_DIGEST" ]] || stop ".htaccess changed after backup."
else
    test ! -e "$HTACCESS" || stop ".htaccess appeared after backup."
fi

RESTORE_REQUIRED=1
mv -f -- "$CANDIDATE" "$HTACCESS"
[[ "$(file_digest "$HTACCESS")" == "$CANDIDATE_DIGEST" ]] || stop "activated .htaccess digest mismatch."
[[ "$(grep -Fxc "$MANAGED_BEGIN" "$HTACCESS")" == "1" ]] || stop "managed begin marker count is not one."
[[ "$(grep -Fxc "$MANAGED_END" "$HTACCESS")" == "1" ]] || stop "managed end marker count is not one."

purge_application_cache "$TARGET_ROOT"
REQUEST_DIR="$BACKUP_SET/live-verification"
mkdir -m 0700 "$REQUEST_DIR"
verify_discovery_host "$TARGET_ENVIRONMENT" "$TARGET_HOST" "$REQUEST_DIR"
verify_negative_scope "$TARGET_HOST" "$REQUEST_DIR"
if [[ "$TARGET_ENVIRONMENT" == "production" ]]; then
    verify_production_redirects "$REQUEST_DIR"
fi
[[ "$(file_digest "$HTACCESS")" == "$CANDIDATE_DIGEST" ]] ||
    stop "active .htaccess drifted during live verification."

if [[ "$TARGET_ENVIRONMENT" == "staging" ]]; then
    ATTESTATION_TMP="$BACKUP_DIR/.LAST_STAGING_DISCOVERY_CACHE_VERIFICATION.${STAMP}"
    printf 'host=%s\nverified_utc=%s\nblock_sha256=%s\nhtaccess_sha256=%s\nrepo_commit=%s\nremediation_script_sha256=%s\nhtaccess_helper_sha256=%s\nheader_helper_sha256=%s\nbackup_set=%s\n' \
        "$STAGING_HOST" "$STAMP" "$BLOCK_DIGEST" "$CANDIDATE_DIGEST" "$REPO_COMMIT" \
        "$REMEDIATION_SCRIPT_DIGEST" "$HTACCESS_HELPER_DIGEST" "$HEADER_HELPER_DIGEST" \
        "$BACKUP_SET" >"$ATTESTATION_TMP"
    chmod 0600 "$ATTESTATION_TMP"
    mv -f -- "$ATTESTATION_TMP" "$STAGING_ATTESTATION"
fi
COMMITTED=1
RESTORE_REQUIRED=0

printf '\n%s DISCOVERY CACHE REMEDIATION COMMITTED\n' "${TARGET_ENVIRONMENT^^}"
printf 'Managed block digest: %s\nActivated .htaccess digest: %s\nTransform: %s\nRecovery set: %s\n' \
    "$BLOCK_DIGEST" "$CANDIDATE_DIGEST" "$TRANSFORM_RESULT" "$BACKUP_SET"
printf 'WAF status: unchanged; this script never disables or weakens the firewall.\n'
