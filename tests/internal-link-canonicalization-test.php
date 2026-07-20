<?php

define('ABSPATH', __DIR__);

$eta_link_test_actions = [];
$eta_link_test_filters = [];

function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
    global $eta_link_test_actions;
    $eta_link_test_actions[] = [$hook, $callback, $priority, $accepted_args];
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    global $eta_link_test_filters;
    $eta_link_test_filters[] = [$hook, $callback, $priority, $accepted_args];
}

function home_url($path = '')
{
    return 'https://envitechal.com' . $path;
}

function is_admin()
{
    return false;
}

require dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal/inc/legacy-redirects.php';

function eta_link_test_same($expected, $actual, $label)
{
    if ($expected === $actual) {
        return;
    }

    file_put_contents(
        'php://stderr',
        "FAILED: {$label}\nExpected: " . var_export($expected, true) .
            "\nActual:   " . var_export($actual, true) . "\n",
        FILE_APPEND
    );
    exit(1);
}

$url_cases = [
    [
        '/22653-2/?utm_source=menu#scope',
        '/services/water-testing-lab-services/?utm_source=menu#scope',
        'root-relative query and fragment are retained',
    ],
    [
        '/22653-2/?#',
        '/services/water-testing-lab-services/?#',
        'empty query and fragment delimiters are retained',
    ],
    [
        'HTTPS://EnviTechAL.com:443/newsupdates/?a=1&amp;b=2#latest',
        'HTTPS://EnviTechAL.com:443/blognewsupdates/?a=1&amp;b=2#latest',
        'absolute internal origin spelling, port and HTML query remain unchanged',
    ],
    [
        '//www.envitechal.com/our-services/#testing',
        '//www.envitechal.com/services/#testing',
        'protocol-relative www alias is internal',
    ],
    [
        '//envitechal.com:8443/22653-2/',
        '//envitechal.com:8443/22653-2/',
        'protocol-relative URL with an indeterminate port is unchanged',
    ],
    [
        'https://envitechal.com:8443/22653-2/',
        'https://envitechal.com:8443/22653-2/',
        'owned hostname on a non-default HTTPS port is unchanged',
    ],
    [
        'http://envitechal.com:80/22653-2/',
        'http://envitechal.com:80/services/water-testing-lab-services/',
        'explicit default HTTP port remains eligible and preserved',
    ],
    [
        'https://staging.envitechal.com/https-envitechal-com-services-environmental-consultancy/?preview=1',
        'https://staging.envitechal.com/services/environmental-consultancy/?preview=1',
        'staging absolute link is internal',
    ],
    [
        '/unlock-precision-why-calibration-services-in-karachi-are-non%E2%80%90negotiable-for-industry-success/',
        '/services/equipment-calibration-services/',
        'percent-encoded mapped path is canonicalized',
    ],
    [
        'https://example.com/22653-2/?utm_source=external#keep',
        'https://example.com/22653-2/?utm_source=external#keep',
        'external host is unchanged',
    ],
    [
        'https://envitechal.com.evil.example/22653-2/',
        'https://envitechal.com.evil.example/22653-2/',
        'lookalike external host is unchanged',
    ],
    [
        'https://envitechal.com@evil.example/22653-2/',
        'https://envitechal.com@evil.example/22653-2/',
        'userinfo lookalike URL is unchanged',
    ],
    [
        'https://user:pass@envitechal.com/22653-2/',
        'https://user:pass@envitechal.com/22653-2/',
        'internal URL containing userinfo is unchanged',
    ],
    ['newsupdates/', 'newsupdates/', 'document-relative URL is unchanged'],
    ['?next=/22653-2/', '?next=/22653-2/', 'query-only URL is unchanged'],
    ['#22653-2', '#22653-2', 'fragment-only URL is unchanged'],
    ['mailto:info@envitechal.com', 'mailto:info@envitechal.com', 'mailto URL is unchanged'],
    ["/22653-2/\0ignored", "/22653-2/\0ignored", 'URL containing NUL is unchanged'],
    ['http://[malformed/22653-2/', 'http://[malformed/22653-2/', 'malformed URL is unchanged'],
    [
        '/services/water-testing-lab-services/?from=canonical',
        '/services/water-testing-lab-services/?from=canonical',
        'canonical URL is unchanged',
    ],
];

// A reverse-proxy Host header is request-controlled and must never expand the
// exact owned-host allowlist used for rendered-link canonicalization.
$_SERVER['HTTP_HOST'] = 'attacker-controlled.example';
$url_cases[] = [
    'https://attacker-controlled.example/22653-2/?keep=hostile',
    'https://attacker-controlled.example/22653-2/?keep=hostile',
    'hostile request Host header cannot make an external URL internal',
];

foreach ($url_cases as [$input, $expected, $label]) {
    eta_link_test_same($expected, eta_modern_canonicalize_rendered_internal_url($input), $label);
}

eta_link_test_same(null, eta_modern_canonicalize_rendered_internal_url(null), 'non-string null remains null');
eta_link_test_same([], eta_modern_canonicalize_rendered_internal_url([]), 'non-string array remains unchanged');

$html = <<<'HTML'
<p data-url="/22653-2/">
  <a class="internal" href="/22653-2/?utm_source=body#methods">Water testing</a>
  <a href='https://envitechal.com/newsupdates/#latest'>Updates</a>
  <a href="https://example.com/22653-2/?keep=1#yes">External</a>
  <img src="/22653-2/" alt="Attribute must remain">
  Text /22653-2/ must remain.
</p>
HTML;

$expected_html = <<<'HTML'
<p data-url="/22653-2/">
  <a class="internal" href="/services/water-testing-lab-services/?utm_source=body#methods">Water testing</a>
  <a href='https://envitechal.com/blognewsupdates/#latest'>Updates</a>
  <a href="https://example.com/22653-2/?keep=1#yes">External</a>
  <img src="/22653-2/" alt="Attribute must remain">
  Text /22653-2/ must remain.
</p>
HTML;

eta_link_test_same(
    $expected_html,
    eta_modern_canonicalize_rendered_internal_links($html),
    'rendered content only rewrites eligible anchor href values'
);
eta_link_test_same(
    $expected_html,
    eta_modern_canonicalize_rendered_internal_links($expected_html),
    'rendered HTML canonicalization is byte-idempotent across stacked filters'
);
eta_link_test_same(false, eta_modern_canonicalize_rendered_internal_links(false), 'non-string HTML is unchanged');

$classic_post_html = <<<'HTML'
<p>Envi Tech AL is a <a href="https://staging.envitechal.com/environmental-water-testing-lab-in-pakistan/" target="_blank" rel="noopener">PNAC-accredited environmental laboratory in Pakistan</a>, providing professional <a href="https://staging.envitechal.com/reliable-water-testing-services-environmental-lab-karachi/" target="_blank" rel="noopener">environmental laboratory services</a>.</p>
HTML;

$expected_classic_post_html = <<<'HTML'
<p>Envi Tech AL is a <a href="https://staging.envitechal.com/services/water-testing-lab-services/" target="_blank" rel="noopener">PNAC-accredited environmental laboratory in Pakistan</a>, providing professional <a href="https://staging.envitechal.com/karachi-environmental-lab/" target="_blank" rel="noopener">environmental laboratory services</a>.</p>
HTML;

eta_link_test_same(
    $expected_classic_post_html,
    eta_modern_canonicalize_rendered_internal_links($classic_post_html),
    'the two legacy hrefs observed in classic staging post content are canonicalized'
);

$functions_source = file_get_contents(
    dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal/functions.php'
);
eta_link_test_same(true, is_string($functions_source), 'theme functions source is readable');
$functions_source = str_replace("\r\n", "\n", $functions_source);

$clean_content_match = [];
$clean_content_found = preg_match(
    '/function eta_modern_clean_content\(\$content\)\s*\{(?P<body>.*?)\n\}\n\nfunction eta_modern_is_premium_legacy_html/s',
    $functions_source,
    $clean_content_match
);
eta_link_test_same(1, $clean_content_found, 'custom renderer clean-content sink is located');

$clean_content_body = $clean_content_match['body'] ?? '';
eta_link_test_same(
    1,
    substr_count($clean_content_body, 'return eta_modern_canonicalize_rendered_internal_links($content);'),
    'all clean-content branches converge on the rendered-link canonicalizer'
);
eta_link_test_same(
    1,
    substr_count($clean_content_body, 'return '),
    'clean-content has no early return that can bypass canonicalization'
);
eta_link_test_same(
    true,
    strpos($functions_source, '$post_content = eta_modern_clean_content($rendered_post_content);') !== false,
    'the custom single-post renderer uses the protected clean-content sink'
);

$menu_attributes = [
    'class' => 'menu-link',
    'href' => 'https://envitechal.com/our-services/?utm_source=navigation#all',
    'target' => '_self',
];
$expected_menu_attributes = $menu_attributes;
$expected_menu_attributes['href'] = 'https://envitechal.com/services/?utm_source=navigation#all';
eta_link_test_same(
    $expected_menu_attributes,
    eta_modern_canonicalize_nav_menu_link_attributes($menu_attributes),
    'classic navigation menu href is canonicalized without changing other attributes'
);

$filter_index = [];
foreach ($eta_link_test_filters as $registration) {
    $filter_index[$registration[0]] = $registration;
}

foreach ([
    'nav_menu_link_attributes' => ['eta_modern_canonicalize_nav_menu_link_attributes', 99, 4],
    'post_link' => ['eta_modern_canonicalize_rendered_internal_url', 99, 3],
    'page_link' => ['eta_modern_canonicalize_rendered_internal_url', 99, 3],
    'post_type_link' => ['eta_modern_canonicalize_rendered_internal_url', 99, 4],
    'the_content' => ['eta_modern_canonicalize_rendered_internal_links', 99, 1],
    'the_excerpt' => ['eta_modern_canonicalize_rendered_internal_links', 99, 1],
    'widget_text' => ['eta_modern_canonicalize_rendered_internal_links', 99, 1],
    'widget_text_content' => ['eta_modern_canonicalize_rendered_internal_links', 99, 1],
    'render_block' => ['eta_modern_canonicalize_rendered_internal_links', 99, 2],
] as $hook => [$callback, $priority, $accepted_args]) {
    eta_link_test_same(
        [$hook, $callback, $priority, $accepted_args],
        $filter_index[$hook] ?? null,
        "{$hook} filter registration"
    );
}

echo "Internal link canonicalization tests passed.\n";
