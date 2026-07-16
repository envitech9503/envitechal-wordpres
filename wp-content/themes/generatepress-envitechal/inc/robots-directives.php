<?php
/**
 * Robots directives and short-lived discovery-file response headers.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return whether the current request is for the isolated staging hostname.
 *
 * @return bool
 */
function eta_modern_is_staging_host()
{
    $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
    return $host === 'staging.envitechal.com';
}

/**
 * Return the response headers owned by the theme for the virtual robots.txt.
 *
 * LiteSpeed must not retain a stale generated robots policy. Shared edge caches
 * may keep it briefly, while browsers revalidate after five minutes.
 *
 * @param string $request_uri Request URI, optionally including a query string.
 * @return array<string,string>
 */
function eta_modern_robots_txt_response_headers($request_uri)
{
    if (!is_string($request_uri) || $request_uri === '') {
        return [];
    }

    $request_path = parse_url($request_uri, PHP_URL_PATH);
    if ($request_path !== '/robots.txt') {
        return [];
    }

    return [
        'Content-Type' => 'text/plain; charset=utf-8',
        'Cache-Control' => 'public, max-age=300, s-maxage=3600',
        'X-LiteSpeed-Cache-Control' => 'no-cache',
    ];
}

/**
 * Send staging crawl protection and robots.txt cache headers.
 *
 * @return void
 */
function eta_modern_send_robots_headers()
{
    if (eta_modern_is_staging_host()) {
        header('X-Robots-Tag: noindex, nofollow, noarchive', true);
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    foreach (eta_modern_robots_txt_response_headers($request_uri) as $name => $value) {
        header($name . ': ' . $value, true);
    }
}

/**
 * Keep staging's virtual robots.txt closed while production remains unchanged.
 *
 * @param string $output Existing virtual robots.txt body.
 * @param bool   $public Whether WordPress marks the site as public.
 * @return string
 */
function eta_modern_filter_robots_txt($output, $public)
{
    unset($public);

    // The filter runs immediately before WordPress emits the virtual file, so
    // repeat the headers here in case an earlier cache/plugin hook replaced them.
    eta_modern_send_robots_headers();

    if (!eta_modern_is_staging_host()) {
        return $output;
    }

    return "User-agent: *\nDisallow: /\n";
}

/**
 * Align Rank Math's HTML robots meta tag with staging's HTTP header.
 *
 * @param array<mixed> $robots Rank Math robots directives.
 * @return array<mixed>
 */
function eta_modern_filter_rank_math_staging_robots($robots)
{
    if (!eta_modern_is_staging_host() || !is_array($robots)) {
        return $robots;
    }

    unset(
        $robots['index'],
        $robots['follow'],
        $robots['archive'],
        $robots['noindex'],
        $robots['nofollow'],
        $robots['noarchive']
    );

    // Rank Math uses semantic slots for index/follow and a directive key for
    // noarchive. Keeping noindex in the index slot also triggers Rank Math's
    // canonical suppression for a noindexed response.
    $robots['index'] = 'noindex';
    $robots['follow'] = 'nofollow';
    $robots['noarchive'] = 'noarchive';

    return $robots;
}

/**
 * Align WordPress core's HTML robots meta tag on staging as a fallback.
 *
 * @param array<mixed> $robots WordPress robots directives.
 * @return array<mixed>
 */
function eta_modern_filter_wordpress_staging_robots($robots)
{
    if (!eta_modern_is_staging_host() || !is_array($robots)) {
        return $robots;
    }

    unset(
        $robots['index'],
        $robots['follow'],
        $robots['archive'],
        $robots['noindex'],
        $robots['nofollow'],
        $robots['noarchive']
    );

    $robots['noindex'] = true;
    $robots['nofollow'] = true;
    $robots['noarchive'] = true;

    return $robots;
}

if (function_exists('add_action')) {
    add_action('send_headers', 'eta_modern_send_robots_headers', PHP_INT_MAX);
}

if (function_exists('add_filter')) {
    add_filter('robots_txt', 'eta_modern_filter_robots_txt', 20, 2);
    add_filter('rank_math/frontend/robots', 'eta_modern_filter_rank_math_staging_robots', 99, 1);
    add_filter('wp_robots', 'eta_modern_filter_wordpress_staging_robots', 99, 1);
}
