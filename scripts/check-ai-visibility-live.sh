#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${AI_VISIBILITY_BASE_URL:-https://envitechal.com}"
BASE_URL="${BASE_URL%/}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
HEADER_VALIDATOR="$SCRIPT_DIR/lib/validate-discovery-cache-headers.php"
WORK_DIR="$(mktemp -d)"
trap 'rm -rf "$WORK_DIR"' EXIT

command -v curl >/dev/null || { printf 'curl is required.\n' >&2; exit 1; }
command -v php >/dev/null || { printf 'php is required.\n' >&2; exit 1; }
test -f "$HEADER_VALIDATOR" || { printf 'Discovery cache-header validator is missing.\n' >&2; exit 1; }

FAILURES=0
REQUEST_NUMBER=0
FETCH_STATUS=''
FETCH_TYPE=''
FETCH_URL=''
FETCH_BODY=''
FETCH_HEADERS=''

fail()
{
    printf 'FAIL: %s\n' "$*" >&2
    FAILURES=$((FAILURES + 1))
}

pass()
{
    printf 'PASS: %s\n' "$*"
}

fetch()
{
    local label="$1"
    local user_agent="$2"
    local path="$3"
    local method="${4:-GET}"
    local accept="${5:-*/*}"
    local cache_control="${6-no-cache}"
    local metadata request_path retry_after
    local attempt=1
    local -a curl_args

    REQUEST_NUMBER=$((REQUEST_NUMBER + 1))
    FETCH_BODY="$WORK_DIR/body-${REQUEST_NUMBER}"
    FETCH_HEADERS="$WORK_DIR/headers-${REQUEST_NUMBER}"
    curl_args=(
        --http1.1
        --silent
        --show-error
        --location
        --connect-timeout 10
        --max-time 30
        --user-agent "$user_agent"
        --header "Accept: $accept"
        --dump-header "$FETCH_HEADERS"
        --output "$FETCH_BODY"
        --write-out $'%{http_code}\n%{content_type}\n%{url_effective}\n'
    )
    if [[ -n "$cache_control" ]]; then
        curl_args+=(--header "Cache-Control: $cache_control")
    fi
    if [[ "$method" == 'HEAD' ]]; then
        curl_args+=(--head)
    fi

    while :; do
        request_path="$path"
        if ((attempt > 1)); then
            if [[ "$request_path" == *\?* ]]; then
                request_path="${request_path}&eta_live_retry=${attempt}"
            else
                request_path="${request_path}?eta_live_retry=${attempt}"
            fi
        fi
        if ! metadata="$(curl "${curl_args[@]}" "$BASE_URL$request_path")"; then
            fail "$label request failed"
            FETCH_STATUS='000'
            FETCH_TYPE=''
            FETCH_URL=''
            : >"$FETCH_BODY"
            return 1
        fi

        FETCH_STATUS="$(sed -n '1p' <<<"$metadata")"
        if [[ "$FETCH_STATUS" != '429' || "$attempt" -ge 3 ]]; then
            break
        fi

        retry_after="$(sed -nE 's/^[Rr]etry-[Aa]fter:[[:space:]]*([0-9]+).*$/\1/p' "$FETCH_HEADERS" | tail -n 1)"
        [[ "$retry_after" =~ ^[0-9]+$ ]] || retry_after=5
        ((retry_after > 15)) && retry_after=15
        fail "$label returned 429 on attempt $attempt; retrying with a fresh cache key in ${retry_after}s"
        sleep "$retry_after"
        attempt=$((attempt + 1))
    done

    FETCH_TYPE="$(sed -n '2p' <<<"$metadata")"
    FETCH_URL="$(sed -n '3p' <<<"$metadata")"
    printf '%s: status=%s type=%s url=%s\n' "$label" "$FETCH_STATUS" "$FETCH_TYPE" "$FETCH_URL"
}

assert_reviewed_discovery_cache()
{
    local label="$1"
    if php "$HEADER_VALIDATOR" "$FETCH_HEADERS" >/dev/null; then
        pass "$label uses the exact reviewed short cache policy and no Expires header"
    else
        fail "$label has an unsafe or inconsistent discovery cache policy"
    fi
}

assert_status_200()
{
    local label="$1"
    if [[ "$FETCH_STATUS" == '200' ]]; then
        pass "$label returned 200"
    else
        fail "$label returned $FETCH_STATUS instead of 200"
    fi
}

assert_type_contains()
{
    local label="$1"
    local expected="$2"
    if [[ "${FETCH_TYPE,,}" == *"${expected,,}"* ]]; then
        pass "$label content type contains $expected"
    else
        fail "$label content type is '$FETCH_TYPE', expected $expected"
    fi
}

assert_body_contains()
{
    local label="$1"
    local expected="$2"
    if grep -Fq -- "$expected" "$FETCH_BODY"; then
        pass "$label contains expected marker"
    else
        fail "$label is missing marker: $expected"
    fi
}

assert_body_excludes_challenge()
{
    local label="$1"
    if grep -Eiq 'One moment, please|Checking your browser|Verify you are human|Access denied' "$FETCH_BODY"; then
        fail "$label contains a verification or access-denied page"
    else
        pass "$label contains no challenge marker"
    fi
}

ordinary_ua='Mozilla/5.0 (compatible; EnviTechAIMonitor/1.0; +https://envitechal.com/)'
googlebot_ua='Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
bingbot_ua='Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)'
oai_search_ua='Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; OAI-SearchBot/1.0; +https://openai.com/searchbot'

fetch 'ordinary homepage' "$ordinary_ua" '/' 'GET' 'text/html' ''
assert_status_200 'ordinary homepage'
assert_type_contains 'ordinary homepage' 'text/html'
assert_body_contains 'ordinary homepage' 'Environmental testing and compliance support for teams that need clear, defensible reports.'
assert_body_contains 'ordinary homepage' 'eta-home-hero-1500.webp'
assert_body_excludes_challenge 'ordinary homepage'
homepage_flat="$(tr '\n' ' ' <"$FETCH_BODY")"
hero_tag="$(grep -oE '<img[^>]*class="eta-home-bg-img"[^>]*>' <<<"$homepage_flat" | sed -n '1p' || true)"
if [[ -z "$hero_tag" ]]; then
    fail 'ordinary homepage is missing the reviewed LCP hero tag'
else
    hero_contract_ok=1
    for marker in \
        'data-spai-excluded="true"' \
        'loading="eager"' \
        'fetchpriority="high"' \
        'sizes="100vw"' \
        'eta-home-hero-520.webp' \
        'eta-home-hero-900.webp' \
        'eta-home-hero-1500.webp'; do
        if [[ "$hero_tag" != *"$marker"* ]]; then
            fail "ordinary homepage hero is missing: $marker"
            hero_contract_ok=0
        fi
    done
    for marker in 'loading="lazy"' 'cdn.shortpixel.ai' 'data-spai="' 'data-spai-loading'; do
        if [[ "$hero_tag" == *"$marker"* ]]; then
            fail "ordinary homepage hero contains forbidden optimizer marker: $marker"
            hero_contract_ok=0
        fi
    done
    if ((hero_contract_ok != 0)); then
        pass 'ordinary homepage hero remains eager, responsive, and excluded from SPAI rewriting'
    fi
fi
if grep -Eiq 'digitalocean|/static/chatbot/widget\.js|data-(agent|chatbot)-id' "$FETCH_BODY"; then
    fail 'ordinary homepage contains a legacy DigitalOcean chatbot marker'
else
    pass 'ordinary homepage contains no legacy DigitalOcean chatbot marker'
fi
homepage_html_body="$FETCH_BODY"
homepage_html_sha256="$(sha256sum "$homepage_html_body" | awk '{print $1}')"

fetch 'Googlebot homepage' "$googlebot_ua" '/'
assert_status_200 'Googlebot homepage'
assert_type_contains 'Googlebot homepage' 'text/html'
assert_body_contains 'Googlebot homepage' 'Environmental testing and compliance support for teams that need clear, defensible reports.'
assert_body_excludes_challenge 'Googlebot homepage'

fetch 'Bingbot homepage' "$bingbot_ua" '/'
assert_status_200 'Bingbot homepage'
assert_type_contains 'Bingbot homepage' 'text/html'
assert_body_excludes_challenge 'Bingbot homepage'

fetch 'homepage with Markdown Accept' "$ordinary_ua" '/' 'GET' 'text/markdown' ''
assert_status_200 'homepage with Markdown Accept'
if [[ "${FETCH_TYPE,,}" == *'text/html'* ]]; then
    pass 'Markdown Accept safely remained HTML'
    assert_body_contains 'homepage with Markdown Accept' 'Environmental testing and compliance support for teams that need clear, defensible reports.'
elif [[ "${FETCH_TYPE,,}" == *'text/markdown'* ]]; then
    pass 'Markdown Accept returned the controlled Markdown representation'
    assert_body_contains 'homepage with Markdown Accept' 'Environmental testing and compliance support for teams that need clear, defensible reports.'
    assert_body_contains 'homepage with Markdown Accept' 'PNAC LAB-347'
    if grep -Eiq '^vary:.*accept' "$FETCH_HEADERS"; then
        pass 'Markdown representation varies on Accept'
    else
        fail 'Markdown representation is missing Vary: Accept'
    fi
    if grep -Eiq '^cache-control:.*(private|no-store)' "$FETCH_HEADERS"; then
        pass 'Markdown representation is excluded from shared caching'
    else
        fail 'Markdown representation is not marked private or no-store'
    fi
else
    fail "Markdown Accept returned unexpected content type: $FETCH_TYPE"
fi
assert_body_excludes_challenge 'homepage with Markdown Accept'

fetch 'ordinary homepage after Markdown Accept' "$ordinary_ua" '/' 'GET' 'text/html' ''
assert_status_200 'ordinary homepage after Markdown Accept'
assert_type_contains 'ordinary homepage after Markdown Accept' 'text/html'
assert_body_contains 'ordinary homepage after Markdown Accept' 'Environmental testing and compliance support for teams that need clear, defensible reports.'
assert_body_excludes_challenge 'ordinary homepage after Markdown Accept'
homepage_html_after_sha256="$(sha256sum "$FETCH_BODY" | awk '{print $1}')"
if [[ "$homepage_html_sha256" == "$homepage_html_after_sha256" ]]; then
    pass 'HTML response remained byte-identical across the Markdown request'
else
    fail 'HTML response changed across the Markdown request; investigate representation cache isolation'
fi

fetch 'robots.txt' "$googlebot_ua" '/robots.txt'
assert_status_200 'robots.txt'
assert_type_contains 'robots.txt' 'text/plain'
assert_body_contains 'robots.txt' 'User-agent: OAI-SearchBot'
assert_body_contains 'robots.txt' 'User-agent: GPTBot'
assert_body_contains 'robots.txt' 'User-agent: *'
assert_body_contains 'robots.txt' 'Sitemap: https://envitechal.com/sitemap_index.xml'
assert_body_excludes_challenge 'robots.txt'
assert_reviewed_discovery_cache 'robots.txt'
robots_body="$(tr -d '\r' <"$FETCH_BODY")"
if [[ "$robots_body" == *$'User-agent: OAI-SearchBot\nAllow: /\nContent-Signal: ai-train=no, search=yes, ai-input=yes'* ]]; then
    pass 'robots.txt explicitly permits OAI-SearchBot discovery'
else
    fail 'robots.txt does not contain the reviewed OAI-SearchBot allow group'
fi
if [[ "$robots_body" == *$'User-agent: GPTBot\nDisallow: /\nContent-Signal: ai-train=no, search=yes, ai-input=yes'* ]]; then
    pass 'robots.txt explicitly blocks GPTBot training crawl'
else
    fail 'robots.txt does not contain the reviewed GPTBot disallow group'
fi

fetch 'sitemap index' "$googlebot_ua" '/sitemap_index.xml' 'GET' '*/*' ''
assert_status_200 'sitemap index'
assert_type_contains 'sitemap index' 'xml'
assert_body_contains 'sitemap index' '<sitemapindex'
assert_body_excludes_challenge 'sitemap index'
if grep -Fq 'staging.envitechal.com' "$FETCH_BODY"; then
    fail 'sitemap index contains a staging URL'
else
    pass 'sitemap index contains no staging URL'
fi
sitemap_index_body="$FETCH_BODY"
mapfile -t sitemap_urls < <(
    grep -oE '<loc>[^<]+</loc>' "$sitemap_index_body" |
        sed -E 's#</?loc>##g'
)
sitemap_body_files=()
if ((${#sitemap_urls[@]} == 0)); then
    fail 'sitemap index contains no child sitemap URLs'
fi
for sitemap_url in "${sitemap_urls[@]}"; do
    if [[ "$sitemap_url" != "$BASE_URL/"* ]]; then
        fail "sitemap index contains an unexpected external child: $sitemap_url"
        continue
    fi
    sitemap_path="/${sitemap_url#"$BASE_URL/"}"
    fetch "child sitemap $sitemap_path" "$googlebot_ua" "$sitemap_path" 'GET' '*/*' ''
    assert_status_200 "child sitemap $sitemap_path"
    assert_type_contains "child sitemap $sitemap_path" 'xml'
    assert_body_contains "child sitemap $sitemap_path" '<urlset'
    assert_body_excludes_challenge "child sitemap $sitemap_path"
    sitemap_body_files+=("$FETCH_BODY")
done

for legacy_sitemap_path in \
    '/certificates-approvals/' \
    '/newsupdates/' \
    '/our-services/' \
    '/air-quality-testing/' \
    '/environmental-testing-services/' \
    '/water-testing-lab-karachi/' \
    '/services/water-testing-services/' \
    '/water-testing-in-pakistan/' \
    '/water-testing-lab-near-me/' \
    '/water-quality-testing-mastering-your-ultimate-guide-to-excellence/' \
    '/get-accurate-results-from-our-water-testing-lab-in-lahore/' \
    '/reliable-water-testing-services-environmental-lab-karachi/' \
    '/discover-the-best-testing-laboratory-near-you-for-reliable-and-accurate-results/' \
    '/https-envitechal-com-services-environmental-consultancy/' \
    '/https-envitechal-com-calibration-of-equipment-in-karachi/' \
    '/22653-2/' \
    '/hiring-an-environmental-lab/' \
    '/environmental-water-testing-lab-in-pakistan/' \
    '/environmental-lab-excellence-the-services-of-envi-tech-al/' \
    '/whats-new-focused-insightful-of-environmental-lab/' \
    '/environmental-testing-lab-in-lahore/' \
    '/environmental-testing-lab-in-karachi-lahore/' \
    '/sindh-epa-noc-guide/' \
    '/frequently-asked-questions-water-testing-in-karachi/' \
    '/consulting-services-for-ginners-gots-ocs-regenagri-certification/' \
    '/unlock-precision-why-calibration-services-in-karachi-are-non‐negotiable-for-industry-success/' \
    '/unlock-precision-why-calibration-services-in-karachi-are-non%E2%80%90negotiable-for-industry-success/' \
    '/unlock-precision-why-calibration-services-in-karachi-are-non-negotiable-for-industry-success/'; do
    legacy_sitemap_found=0
    for sitemap_body_file in "${sitemap_body_files[@]}"; do
        if grep -Fiq "$BASE_URL$legacy_sitemap_path" "$sitemap_body_file"; then
            legacy_sitemap_found=1
            break
        fi
    done
    if ((legacy_sitemap_found != 0)); then
        fail "a child sitemap still contains redirected URL: $legacy_sitemap_path"
    else
        pass "all child sitemaps exclude redirected URL: $legacy_sitemap_path"
    fi
done

fetch 'llms.txt' "$oai_search_ua" '/llms.txt'
assert_status_200 'llms.txt'
assert_type_contains 'llms.txt' 'text/plain'
assert_body_contains 'llms.txt' '# Envi Tech AL'
assert_body_excludes_challenge 'llms.txt'
assert_reviewed_discovery_cache 'llms.txt'

fetch 'llms-full.txt' "$oai_search_ua" '/llms-full.txt'
assert_status_200 'llms-full.txt'
assert_type_contains 'llms-full.txt' 'text/plain'
assert_body_contains 'llms-full.txt' '# Envi Tech AL full AI-readable corpus'
assert_body_excludes_challenge 'llms-full.txt'
assert_reviewed_discovery_cache 'llms-full.txt'

fetch 'agent-skills index' "$oai_search_ua" '/.well-known/agent-skills/index.json'
assert_status_200 'agent-skills index'
assert_type_contains 'agent-skills index' 'application/json'
assert_body_excludes_challenge 'agent-skills index'
if php -r '$body = file_get_contents($argv[1]); $json = is_string($body) ? json_decode($body, true) : null; exit(is_array($json) && $json !== [] && json_last_error() === JSON_ERROR_NONE ? 0 : 1);' "$FETCH_BODY"; then
    pass 'agent-skills index is valid non-empty JSON'
else
    fail 'agent-skills index is not valid non-empty JSON'
fi
assert_reviewed_discovery_cache 'agent-skills index'

for head_path in '/robots.txt' '/sitemap_index.xml' '/llms.txt' '/llms-full.txt' '/.well-known/agent-skills/index.json'; do
    fetch "OAI-SearchBot HEAD $head_path" "$oai_search_ua" "$head_path" 'HEAD'
    assert_status_200 "OAI-SearchBot HEAD $head_path"
    if [[ "$head_path" == '/sitemap_index.xml' ]]; then
        assert_type_contains "OAI-SearchBot HEAD $head_path" 'xml'
    elif [[ "$head_path" == '/.well-known/agent-skills/index.json' ]]; then
        assert_type_contains "OAI-SearchBot HEAD $head_path" 'application/json'
        assert_reviewed_discovery_cache "OAI-SearchBot HEAD $head_path"
    else
        assert_type_contains "OAI-SearchBot HEAD $head_path" 'text/plain'
        assert_reviewed_discovery_cache "OAI-SearchBot HEAD $head_path"
    fi
done

redirect_metadata="$(curl --http1.1 --silent --show-error --connect-timeout 10 --max-time 30 \
    --user-agent "$ordinary_ua" --output /dev/null \
    --write-out $'%{http_code}\n%{redirect_url}\n' \
    "$BASE_URL/certificates-approvals/")"
redirect_status="$(sed -n '1p' <<<"$redirect_metadata")"
redirect_url="$(sed -n '2p' <<<"$redirect_metadata")"
if [[ "$redirect_status" == '301' && "$redirect_url" == "$BASE_URL/accreditations-certifications/" ]]; then
    pass 'legacy credentials URL is a one-hop 301 to the canonical page'
else
    fail "legacy credentials redirect is status=$redirect_status location=$redirect_url"
fi

legacy_redirect_pairs=(
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

for redirect_pair in "${legacy_redirect_pairs[@]}"; do
    redirect_source="${redirect_pair%%|*}"
    redirect_target="${redirect_pair#*|}"
    redirect_metadata="$(curl --http1.1 --silent --show-error --connect-timeout 10 --max-time 30 \
        --max-redirs 0 --user-agent "$ordinary_ua" --output /dev/null \
        --write-out $'%{http_code}\n%{redirect_url}\n' \
        "$BASE_URL$redirect_source")"
    redirect_status="$(sed -n '1p' <<<"$redirect_metadata")"
    redirect_url="$(sed -n '2p' <<<"$redirect_metadata")"
    if [[ "$redirect_status" == '301' && "$redirect_url" == "$BASE_URL$redirect_target" ]]; then
        pass "$redirect_source is a one-hop 301 to $redirect_target"
    else
        fail "$redirect_source redirect is status=$redirect_status location=$redirect_url; expected $BASE_URL$redirect_target"
    fi
done

if ((FAILURES > 0)); then
    printf '\nAI visibility live check failed with %d finding(s).\n' "$FAILURES" >&2
    exit 1
fi

printf '\nAI visibility live check passed.\n'
