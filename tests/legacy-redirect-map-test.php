<?php

define('ABSPATH', __DIR__);

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

$redirects = eta_modern_legacy_redirect_map();
foreach ($redirects as $source => $target) {
    if (isset($redirects[$target])) {
        file_put_contents('php://stderr', "FAILED: redirect chain {$source} -> {$target} -> {$redirects[$target]}\n", FILE_APPEND);
        exit(1);
    }
    if ($source === $target) {
        file_put_contents('php://stderr', "FAILED: self redirect {$source}\n", FILE_APPEND);
        exit(1);
    }
}

echo "Legacy redirect map tests passed.\n";
