<?php

define('ABSPATH', __DIR__);

$eta_redirect_test_actions = [];
$eta_redirect_test_filters = [];

function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
    global $eta_redirect_test_actions;
    $eta_redirect_test_actions[] = [$hook, $callback, $priority, $accepted_args];
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    global $eta_redirect_test_filters;
    $eta_redirect_test_filters[] = [$hook, $callback, $priority, $accepted_args];
}

require dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal/inc/legacy-redirects.php';

function eta_redirect_test_same($expected, $actual, $label)
{
    if ($expected === $actual) {
        return;
    }

    file_put_contents('php://stderr', "FAILED: {$label}\nExpected: " . var_export($expected, true) . "\nActual:   " . var_export($actual, true) . "\n", FILE_APPEND);
    exit(1);
}

$expected_redirects = [
    '/certificates-approvals/' => '/accreditations-certifications/',
    '/hiring-an-environmental-lab/' => '/how-to-choose-the-suitable-environmental-lab/',
    '/environmental-water-testing-lab-in-pakistan/' => '/services/water-testing-lab-services/',
    '/environmental-testing-lab-in-lahore/' => '/lahore-environmental-lab/',
    '/frequently-asked-questions-water-testing-in-karachi/' => '/environmental-testing-faqs-pakistan/',
    '/sindh-epa-noc-guide/' => '/services/environmental-consultancy/',
    '/22653-2/' => '/services/water-testing-lab-services/',
    '/https-envitechal-com-calibration-of-equipment-in-karachi/' => '/services/equipment-calibration-services/',
    '/https-envitechal-com-services-environmental-consultancy/' => '/services/environmental-consultancy/',
];

foreach ($expected_redirects as $source => $target) {
    eta_redirect_test_same($target, eta_modern_legacy_redirect_target($source), "redirect {$source}");
    eta_redirect_test_same($target, eta_modern_legacy_redirect_target(rtrim($source, '/')), "redirect without trailing slash {$source}");
    eta_redirect_test_same($target, eta_modern_legacy_redirect_target($source . '?utm_source=test'), "redirect with query {$source}");
}

eta_redirect_test_same(
    '/services/equipment-calibration-services/',
    eta_modern_legacy_redirect_target('/unlock-precision-why-calibration-services-in-karachi-are-non%E2%80%90negotiable-for-industry-success/'),
    'percent-encoded unicode calibration slug redirects'
);

eta_redirect_test_same(null, eta_modern_legacy_redirect_target('/services/analytical-lab-services/'), 'canonical target does not redirect');
eta_redirect_test_same(null, eta_modern_legacy_redirect_target('not-an-absolute-path'), 'invalid relative path does not redirect');
eta_redirect_test_same(
    '/services/water-testing-lab-services/',
    eta_modern_legacy_request_target('GET', '/22653-2/?utm_source=legacy'),
    'GET request resolves before plugin redirects'
);
eta_redirect_test_same(
    '/services/equipment-calibration-services/',
    eta_modern_legacy_request_target('head', '/https-envitechal-com-calibration-of-equipment-in-karachi/'),
    'HEAD request resolves before plugin redirects'
);
eta_redirect_test_same(null, eta_modern_legacy_request_target('POST', '/22653-2/'), 'POST request does not redirect');
eta_redirect_test_same(null, eta_modern_legacy_request_target('', '/22653-2/'), 'missing method does not redirect');
eta_redirect_test_same(null, eta_modern_legacy_request_target('GET', '/not-in-the-reviewed-map/'), 'unmapped request does not redirect');

eta_redirect_test_same(
    ['init', 'eta_modern_maybe_redirect_legacy_request', -9999, 1],
    $eta_redirect_test_actions[0] ?? null,
    'reviewed redirect handler is registered before plugin redirect rules'
);

$canonical_sitemap_post = (object) [
    'ID' => 9001,
    'post_name' => 'analytical-lab-services',
    'post_type' => 'services',
];
eta_redirect_test_same(
    $canonical_sitemap_post,
    eta_modern_filter_legacy_redirect_sitemap_post_object($canonical_sitemap_post),
    'canonical post object remains in the sitemap'
);
eta_redirect_test_same(
    false,
    eta_modern_filter_legacy_redirect_sitemap_post_object((object) [
        'ID' => 9002,
        'post_name' => 'hiring-an-environmental-lab',
        'post_type' => 'post',
    ]),
    'legacy post object is excluded before its permalink is canonicalized'
);
eta_redirect_test_same(
    false,
    eta_modern_filter_legacy_redirect_sitemap_post_object((object) [
        'ID' => 9003,
        'post_name' => 'water-testing-services',
        'post_type' => 'services',
    ]),
    'nested legacy service object is excluded by its exact source slug'
);
eta_redirect_test_same(
    false,
    eta_modern_filter_legacy_redirect_sitemap_post_object((object) [
        'ID' => 9004,
        'post_name' => 'unlock-precision-why-calibration-services-in-karachi-are-non%E2%80%90negotiable-for-industry-success',
        'post_type' => 'post',
    ]),
    'percent-encoded Unicode legacy post slug is excluded'
);
$sitemap_post_without_slug = (object) ['ID' => 9005, 'post_type' => 'post'];
eta_redirect_test_same(
    $sitemap_post_without_slug,
    eta_modern_filter_legacy_redirect_sitemap_post_object($sitemap_post_without_slug),
    'post object without a slug remains unchanged'
);
$lookalike_sitemap_post = (object) [
    'ID' => 9006,
    'post_name' => 'hiring-an-environmental-lab-review',
];
eta_redirect_test_same(
    $lookalike_sitemap_post,
    eta_modern_filter_legacy_redirect_sitemap_post_object($lookalike_sitemap_post),
    'lookalike post slug remains unchanged'
);

eta_redirect_test_same(
    true,
    in_array(
        [
            'rank_math/sitemap/post_object',
            'eta_modern_filter_legacy_redirect_sitemap_post_object',
            10,
            1,
        ],
        $eta_redirect_test_filters,
        true
    ),
    'Rank Math post-object exclusion filter is registered'
);

$canonical_sitemap_entry = [
    'loc' => 'https://envitechal.com/services/analytical-lab-services/',
    'mod' => '2026-07-16T00:00:00+00:00',
];
eta_redirect_test_same(
    $canonical_sitemap_entry,
    eta_modern_filter_legacy_redirect_sitemap_entry($canonical_sitemap_entry, 'post', null),
    'canonical sitemap entry remains'
);
eta_redirect_test_same(
    false,
    eta_modern_filter_legacy_redirect_sitemap_entry([
        'loc' => 'https://envitechal.com/hiring-an-environmental-lab/?utm_source=sitemap-test',
    ], 'post', null),
    'redirected sitemap entry is excluded'
);
eta_redirect_test_same(
    false,
    eta_modern_filter_legacy_redirect_sitemap_entry([
        'loc' => 'https://envitechal.com/unlock-precision-why-calibration-services-in-karachi-are-non%E2%80%90negotiable-for-industry-success/',
    ], 'post', null),
    'encoded unicode redirect sitemap entry is excluded'
);
eta_redirect_test_same([], eta_modern_filter_legacy_redirect_sitemap_entry([], 'post', null), 'empty sitemap entry remains');
eta_redirect_test_same(false, eta_modern_disable_rank_math_sitemap_transient_cache(true), 'Rank Math sitemap transient cache is disabled');

$redirects = eta_modern_legacy_redirect_map();
$redirect_source_slugs = [];
$redirect_target_slugs = [];
foreach ($redirects as $source => $target) {
    $source_path = eta_modern_normalize_legacy_redirect_path($source);
    $source_slug = rawurldecode(basename(rtrim($source_path, '/')));
    if (isset($redirect_source_slugs[$source_slug])) {
        file_put_contents(
            'php://stderr',
            "FAILED: duplicate legacy source slug {$source_slug}: " .
                "{$redirect_source_slugs[$source_slug]} and {$source}\n",
            FILE_APPEND
        );
        exit(1);
    }
    $redirect_source_slugs[$source_slug] = $source;

    $target_path = eta_modern_normalize_legacy_redirect_path($target);
    $target_slug = rawurldecode(basename(rtrim($target_path, '/')));
    $redirect_target_slugs[$target_slug] = $target;

    eta_redirect_test_same(
        false,
        eta_modern_filter_legacy_redirect_sitemap_post_object((object) [
            'post_name' => rawurlencode($source_slug),
        ]),
        "redirect source post object is excluded before permalink generation {$source}"
    );
    eta_redirect_test_same(
        false,
        eta_modern_filter_legacy_redirect_sitemap_entry([
            'loc' => 'https://envitechal.com' . $source,
        ], 'post', null),
        "redirect source is excluded from sitemap {$source}"
    );
    if (isset($redirects[$target])) {
        file_put_contents('php://stderr', "FAILED: redirect chain {$source} -> {$target} -> {$redirects[$target]}\n", FILE_APPEND);
        exit(1);
    }
    if ($source === $target) {
        file_put_contents('php://stderr', "FAILED: self redirect {$source}\n", FILE_APPEND);
        exit(1);
    }
}

$redirect_slug_collisions = array_intersect_key($redirect_source_slugs, $redirect_target_slugs);
if ($redirect_slug_collisions !== []) {
    file_put_contents(
        'php://stderr',
        'FAILED: a canonical redirect target reuses a retired source slug: ' .
            var_export($redirect_slug_collisions, true) . "\n",
        FILE_APPEND
    );
    exit(1);
}

echo "Legacy redirect map tests passed.\n";
