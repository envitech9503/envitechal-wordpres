<?php

define('ABSPATH', __DIR__);

$eta_robots_test_actions = [];
$eta_robots_test_filters = [];

function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
{
    global $eta_robots_test_actions;
    $eta_robots_test_actions[] = [$hook, $callback, $priority, $accepted_args];
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1)
{
    global $eta_robots_test_filters;
    $eta_robots_test_filters[] = [$hook, $callback, $priority, $accepted_args];
}

require dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal/inc/robots-directives.php';

function eta_robots_test_same($expected, $actual, $label)
{
    if ($expected === $actual) {
        return;
    }

    file_put_contents('php://stderr', "FAILED: {$label}\nExpected: " . var_export($expected, true) . "\nActual:   " . var_export($actual, true) . "\n", FILE_APPEND);
    exit(1);
}

$_SERVER['HTTP_HOST'] = 'envitechal.com';
$_SERVER['REQUEST_URI'] = '/robots.txt';
$rank_math_production = ['index' => 'index', 'follow' => 'follow', 'max-snippet' => '-1'];
$wordpress_production = ['max-image-preview' => 'large'];
eta_robots_test_same(
    $rank_math_production,
    eta_modern_filter_rank_math_staging_robots($rank_math_production),
    'Rank Math production directives remain unchanged'
);
eta_robots_test_same(
    $wordpress_production,
    eta_modern_filter_wordpress_staging_robots($wordpress_production),
    'WordPress production directives remain unchanged'
);
eta_robots_test_same(
    "User-agent: *\n"
        . "Disallow: /wp-admin/\n"
        . "Allow: /wp-admin/admin-ajax.php\n"
        . "Content-Signal: ai-train=no, search=yes, ai-input=yes\n\n"
        . "Sitemap: https://envitechal.com/sitemap_index.xml\n",
    eta_modern_filter_robots_txt("User-agent: *\nAllow: /\n", true),
    'production robots.txt keeps Content-Signal inside the user-agent group'
);

$_SERVER['HTTP_HOST'] = 'staging.envitechal.com';
eta_robots_test_same(
    [
        'max-snippet' => '-1',
        'index' => 'noindex',
        'follow' => 'nofollow',
        'noarchive' => 'noarchive',
    ],
    eta_modern_filter_rank_math_staging_robots([
        'index' => 'index',
        'follow' => 'follow',
        'archive' => 'archive',
        'noindex' => 'noindex',
        'max-snippet' => '-1',
    ]),
    'Rank Math staging directives are restrictive and non-contradictory'
);
eta_robots_test_same(
    [
        'max-image-preview' => 'large',
        'noindex' => true,
        'nofollow' => true,
        'noarchive' => true,
    ],
    eta_modern_filter_wordpress_staging_robots([
        'index' => true,
        'follow' => true,
        'archive' => true,
        'max-image-preview' => 'large',
    ]),
    'WordPress staging directives are restrictive and non-contradictory'
);
eta_robots_test_same(
    "User-agent: *\nDisallow: /\n",
    eta_modern_filter_robots_txt("User-agent: *\nAllow: /\n", true),
    'staging robots.txt remains closed to crawlers'
);

eta_robots_test_same(
    [
        'Content-Type' => 'text/plain; charset=utf-8',
        'Cache-Control' => 'public, max-age=300, s-maxage=3600',
        'X-LiteSpeed-Cache-Control' => 'no-cache',
    ],
    eta_modern_robots_txt_response_headers('/robots.txt?cache-bust=1'),
    'robots.txt receives short-lived edge and LiteSpeed bypass headers'
);
eta_robots_test_same([], eta_modern_robots_txt_response_headers('/robots.txt/'), 'only the exact robots.txt path owns the headers');
eta_robots_test_same([], eta_modern_robots_txt_response_headers('/services/'), 'ordinary pages do not receive robots.txt cache headers');

eta_robots_test_same(
    ['send_headers', 'eta_modern_send_robots_headers', PHP_INT_MAX, 1],
    $eta_robots_test_actions[0] ?? null,
    'robots headers run after ordinary send_headers callbacks'
);
eta_robots_test_same(
    [
        ['robots_txt', 'eta_modern_filter_robots_txt', 20, 2],
        ['rank_math/frontend/robots', 'eta_modern_filter_rank_math_staging_robots', 99, 1],
        ['wp_robots', 'eta_modern_filter_wordpress_staging_robots', 99, 1],
    ],
    $eta_robots_test_filters,
    'robots filters are registered with the intended priorities'
);

echo "Robots directive tests passed.\n";
