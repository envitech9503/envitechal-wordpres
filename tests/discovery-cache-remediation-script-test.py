#!/usr/bin/env python3

"""Static safety contract for the cPanel discovery-cache transaction."""

from pathlib import Path
import re
import subprocess


ROOT = Path(__file__).resolve().parents[1]
SCRIPT = ROOT / "scripts/remediate-discovery-cache-headers.sh"
ROLLBACK = ROOT / "scripts/rollback-discovery-cache-headers.sh"
TRANSFORMER = ROOT / "scripts/lib/discovery-cache-htaccess.php"
VALIDATOR = ROOT / "scripts/lib/validate-discovery-cache-headers.php"


def require(source: str, token: str, label: str) -> None:
    if token not in source:
        raise AssertionError(f"missing {label}: {token!r}")


def main() -> None:
    subprocess.run(["bash", "-n", str(SCRIPT)], check=True)
    subprocess.run(["bash", "-n", str(ROLLBACK)], check=True)
    script = SCRIPT.read_text(encoding="utf-8")
    rollback = ROLLBACK.read_text(encoding="utf-8")
    transformer = TRANSFORMER.read_text(encoding="utf-8")
    validator = VALIDATOR.read_text(encoding="utf-8")

    required_script_tokens = {
        'staging confirmation gate': 'CONFIRM_STAGING_DISCOVERY_CACHE',
        'production confirmation gate': 'CONFIRM_PRODUCTION_DISCOVERY_CACHE',
        'staging attestation': 'LAST_STAGING_DISCOVERY_CACHE_VERIFICATION',
        'origin fetch': 'fetch origin --prune',
        'exact repository branch': 'symbolic-ref --quiet --short HEAD',
        'exact origin/main equality': 'local main is not exactly origin/main',
        'attested repository commit': 'repo_commit=${REPO_COMMIT}',
        'attested remediation script': 'remediation_script_sha256=${REMEDIATION_SCRIPT_DIGEST}',
        'attested transformer': 'htaccess_helper_sha256=${HTACCESS_HELPER_DIGEST}',
        'attested header validator': 'header_helper_sha256=${HEADER_HELPER_DIGEST}',
        '24-hour attestation limit': 'attestation_age <= 86400',
        'private backup root': '${HOME}/backups/envitechal-ai-visibility',
        'transaction lock': 'flock -n 9',
        'private manifest verification': 'sha256sum -c MANIFEST.sha256',
        'atomic activation': 'mv -f -- "$CANDIDATE" "$HTACCESS"',
        'rollback activation': 'mv -f -- "$ROLLBACK_CANDIDATE" "$HTACCESS"',
        'WordPress cache purge': 'do_action("litespeed_purge_all")',
        'canonical/cache-busted loop': 'for suffix in \'\' "?eta_discovery_cache_verify=${STAMP}"',
        'cache-busted request bypass': "curl_args+=(--header 'Cache-Control: no-cache')",
        'GET/HEAD loop': 'for method in GET HEAD',
        'redirect ownership check': 'X-Redirect-By',
        'redirect GET/HEAD loop': 'for method in GET HEAD',
        'direct target 200 check': 'redirect target $target returned $target_status instead of a direct 200',
        'firewall invariant': 'WAF status: unchanged',
        'negative scope test': 'managed discovery cache policy leaked onto ordinary path',
        'pre-attestation drift guard': 'active .htaccess drifted during live verification',
        'automatic rollback proof': 'ROLLBACK VERIFIED: the exact prior .htaccess state and mode were restored',
        'failed attestation invalidation': 'INVALIDATED_STAGING_DISCOVERY_CACHE_VERIFICATION',
        'paced public verification': 'sleep 0.25',
        'bounded 429 retry': 'returned 429; retrying in',
        'Retry-After support': '[Rr]etry-[Aa]fter:',
    }
    for label, token in required_script_tokens.items():
        require(script, token, label)

    for path in (
        '/robots.txt',
        '/llms.txt',
        '/llms-full.txt',
        '/.well-known/agent-skills/index.json',
    ):
        require(script, path, f"managed discovery path {path}")

    require(script, 'SetEnvIf Request_URI', 'Request_URI-scoped environment rule')
    require(script, 'PRODUCTION_ROOT_RAW="$(get_docroot "$PRODUCTION_HOST")"', 'independent production-root resolution')
    require(script, 'STAGING_ROOT_RAW="$(get_docroot "$STAGING_HOST")"', 'independent staging-root resolution')
    require(script, 'production and staging resolve to the same directory', 'equal-root rejection')
    require(script, 'production is nested inside staging', 'nested production-root rejection')
    require(script, 'staging is nested inside production', 'nested staging-root rejection')
    require(script, "stat -Lc '%d:%i'", 'same-inode rejection')
    require(
        script,
        'public, max-age=300, s-maxage=3600, must-revalidate',
        'reviewed short cache policy',
    )
    require(script, 'Header onsuccess unset Expires', 'successful-response Expires removal')
    require(script, 'Header always unset Expires', 'always-table Expires removal')
    require(script, 'Header onsuccess unset Cache-Control', 'normal-table Cache-Control removal')
    require(
        script,
        'Header always set Cache-Control "public, max-age=300, s-maxage=3600, must-revalidate"',
        'always-table Cache-Control replacement',
    )
    if 'Header always unset Cache-Control' in script:
        raise AssertionError('always-table Cache-Control must be replaced, not removed before an onsuccess set')
    if 'Header onsuccess set Cache-Control' in script:
        raise AssertionError('PHP/LSAPI Cache-Control must be set in the always table')
    if script.index('Header onsuccess unset Cache-Control') > script.index('Header always set Cache-Control'):
        raise AssertionError('normal-table Cache-Control removal must precede always-table replacement')

    if re.search(r'\b(?:iptables|nft|ufw)\b', script, flags=re.IGNORECASE):
        raise AssertionError('remediation must not invoke firewall administration commands')
    if re.search(r'cloudflare[^\n]*(?:disable|off)', script, flags=re.IGNORECASE):
        raise AssertionError('remediation must not disable Cloudflare or its WAF')
    if re.search(r'\brsync\b|\bcp\s+-a\b', script):
        raise AssertionError('remediation must use verified install/mv operations, not in-place tree copies')

    for source_name, source in (('remediation', script), ('rollback', rollback)):
        if '< <(' in source or re.search(r'\b(?:mapfile|readarray)\b', source):
            raise AssertionError(f'{source_name} must not require /dev/fd process substitution on cPanel')

    rollback_tokens = {
        'explicit recovery set': 'DISCOVERY_CACHE_RECOVERY_SET',
        'explicit host confirmation': 'CONFIRM_DISCOVERY_CACHE_ROLLBACK',
        'manifest verification': 'sha256sum -c MANIFEST.sha256',
        'drift guard': 'active .htaccess has drifted since deployment',
        'shared lock before inspection': 'flock -n 9',
        'same-directory restore': 'mv -f -- "$RESTORE_TMP" "$HTACCESS"',
        'active-mode preservation': 'install -m "$ACTIVE_MODE"',
        'cache purge': 'do_action("litespeed_purge_all")',
        'post-rollback recheck': 'verify_public_availability',
        'failed recovery proof': 'CRITICAL: pre-rollback active .htaccess recovery failed digest/mode verification',
        'WAF invariant': 'rollback never disables or weakens the firewall',
    }
    for label, token in rollback_tokens.items():
        require(rollback, token, f'rollback {label}')
    require(rollback, 'sleep 0.25', 'rollback paced public verification')
    if rollback.index('flock -n 9') > rollback.index('active .htaccess has drifted since deployment'):
        raise AssertionError('rollback must acquire the shared lock before inspecting the active digest')

    reviewed_sources = (
        'water-testing-in-pakistan',
        'water-testing-lab-near-me',
        'water-quality-testing-mastering-your-ultimate-guide-to-excellence',
        'get-accurate-results-from-our-water-testing-lab-in-lahore',
        'reliable-water-testing-services-environmental-lab-karachi',
        'discover-the-best-testing-laboratory-near-you-for-reliable-and-accurate-results',
        'https-envitechal-com-services-environmental-consultancy',
        'https-envitechal-com-calibration-of-equipment-in-karachi',
        '22653-2',
    )
    for source in reviewed_sources:
        require(transformer, f"'{source}' =>", f"exact duplicate RewriteRule source {source}")
    require(transformer, '$removedCount !== 0 && $removedCount !== count($reviewedRules)', 'all-or-none rule removal')
    require(transformer, 'unexpected source or target', 'fail-closed source/target check')
    require(transformer, 'duplicated:', 'duplicate rule rejection')
    require(transformer, 'governed by one or more RewriteCond', 'RewriteCond attachment rejection')
    require(transformer, "RewriteRule[\\t ]+/i", 'case-insensitive RewriteRule detection')
    require(transformer, '$candidate .= $block;', 'managed block appended after existing directives')

    require(validator, 'count($cacheValues) !== 1', 'single Cache-Control field requirement')
    require(validator, '$finalHeaders', 'final response header-block selection')
    require(validator, '$status >= 100 && $status < 200', 'informational response exclusion')
    require(validator, '$absenceMode', 'explicit managed-policy absence mode')
    require(validator, '(int) $ageMatch[1] > 3600', 'maximum age safety cap')
    require(validator, "preg_match('/^expires\\s*:/mi'", 'Expires rejection')

    redirect_pairs = re.findall(r"^\s*'(/[^']+\|/[^']+)'$", script, flags=re.MULTILINE)
    if len(redirect_pairs) != 27:
        raise AssertionError(f"expected 27 exact production redirect checks, found {len(redirect_pairs)}")


if __name__ == "__main__":
    main()
