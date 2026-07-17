<?php
/**
 * One-hop consolidations for obsolete or unsafe public URLs.
 */

if (!defined('ABSPATH')) {
    exit;
}

function eta_modern_legacy_redirect_map()
{
    return [
        '/certificates-approvals/' => '/accreditations-certifications/',
        '/newsupdates/' => '/blognewsupdates/',
        '/our-services/' => '/services/',
        '/air-quality-testing/' => '/gaseous-air-emission-testing-lab-near-me/',
        '/environmental-testing-services/' => '/services/analytical-lab-services/',
        '/water-testing-lab-karachi/' => '/services/water-testing-lab-services/',
        '/services/water-testing-services/' => '/services/water-testing-lab-services/',
        '/water-testing-in-pakistan/' => '/services/water-testing-lab-services/',
        '/water-testing-lab-near-me/' => '/services/water-testing-lab-services/',
        '/water-quality-testing-mastering-your-ultimate-guide-to-excellence/' => '/services/water-testing-lab-services/',
        '/get-accurate-results-from-our-water-testing-lab-in-lahore/' => '/lahore-environmental-lab/',
        '/reliable-water-testing-services-environmental-lab-karachi/' => '/karachi-environmental-lab/',
        '/discover-the-best-testing-laboratory-near-you-for-reliable-and-accurate-results/' => '/how-to-choose-the-suitable-environmental-lab/',
        '/https-envitechal-com-services-environmental-consultancy/' => '/services/environmental-consultancy/',
        '/https-envitechal-com-calibration-of-equipment-in-karachi/' => '/services/equipment-calibration-services/',
        '/22653-2/' => '/services/water-testing-lab-services/',

        // Consolidate duplicate or high-risk legacy content into reviewed canonical pages.
        '/hiring-an-environmental-lab/' => '/how-to-choose-the-suitable-environmental-lab/',
        '/environmental-water-testing-lab-in-pakistan/' => '/services/water-testing-lab-services/',
        '/environmental-lab-excellence-the-services-of-envi-tech-al/' => '/services/analytical-lab-services/',
        '/whats-new-focused-insightful-of-environmental-lab/' => '/services/analytical-lab-services/',
        '/environmental-testing-lab-in-lahore/' => '/lahore-environmental-lab/',
        '/environmental-testing-lab-in-karachi-lahore/' => '/services/analytical-lab-services/',
        '/frequently-asked-questions-water-testing-in-karachi/' => '/environmental-testing-faqs-pakistan/',
        '/sindh-epa-noc-guide/' => '/services/environmental-consultancy/',
        '/consulting-services-for-ginners-gots-ocs-regenagri-certification/' => '/services/certification-advisory/',
        '/unlock-precision-why-calibration-services-in-karachi-are-non‐negotiable-for-industry-success/' => '/services/equipment-calibration-services/',
        '/unlock-precision-why-calibration-services-in-karachi-are-non-negotiable-for-industry-success/' => '/services/equipment-calibration-services/',
    ];
}

function eta_modern_normalize_legacy_redirect_path($path)
{
    if (!is_string($path) || $path === '') {
        return null;
    }

    $parsed_path = parse_url($path, PHP_URL_PATH);
    if (!is_string($parsed_path) || $parsed_path === '') {
        return null;
    }

    $parsed_path = rawurldecode($parsed_path);
    if ($parsed_path === '' || $parsed_path[0] !== '/' || strpos($parsed_path, "\0") !== false) {
        return null;
    }

    return rtrim($parsed_path, '/') . '/';
}

function eta_modern_legacy_redirect_target($path)
{
    $normalized_path = eta_modern_normalize_legacy_redirect_path($path);
    if ($normalized_path === null) {
        return null;
    }

    $redirects = eta_modern_legacy_redirect_map();
    return isset($redirects[$normalized_path]) ? $redirects[$normalized_path] : null;
}

/**
 * Return the exact hostnames that are owned by this WordPress site.
 *
 * The explicit aliases cover production content rendered on staging (and old
 * absolute links that still include www). No other subdomain is treated as
 * internal, so a matching path on an external host is never rewritten.
 *
 * @return array<int,string>
 */
function eta_modern_internal_link_hosts()
{
    $hosts = [
        'envitechal.com',
        'www.envitechal.com',
        'staging.envitechal.com',
    ];

    if (function_exists('home_url')) {
        $home_host = parse_url((string) home_url('/'), PHP_URL_HOST);
        if (is_string($home_host) && $home_host !== '') {
            $hosts[] = strtolower(rtrim($home_host, '.'));
        }
    }

    return array_values(array_unique($hosts));
}

/**
 * Replace a rendered internal legacy URL with its reviewed canonical target.
 *
 * Only site-root-relative URLs and HTTP(S) URLs on an exact owned hostname are
 * eligible. The original query string, fragment, scheme, host spelling and
 * port are retained. Relative paths, external URLs, user-info URLs and other
 * schemes are deliberately left byte-for-byte unchanged.
 *
 * @param mixed $url Candidate rendered URL.
 * @return mixed Canonicalized URL, or the original value when ineligible.
 */
function eta_modern_canonicalize_rendered_internal_url($url)
{
    if (!is_string($url) || $url === '') {
        return $url;
    }

    $parts = parse_url($url);
    if (!is_array($parts)) {
        return $url;
    }

    $path = isset($parts['path']) && is_string($parts['path']) ? $parts['path'] : '';
    $target = eta_modern_legacy_redirect_target($path);
    if ($target === null) {
        return $url;
    }

    $prefix = '';
    if (strncmp($url, '//', 2) === 0) {
        // A protocol-relative non-default port cannot be assessed without a
        // scheme, so only the site's ordinary origin form is eligible.
        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['port']) || !isset($parts['host'])) {
            return $url;
        }

        $host = strtolower(rtrim((string) $parts['host'], '.'));
        if (!in_array($host, eta_modern_internal_link_hosts(), true) ||
            !preg_match('~^(//[^/?#]+)~', $url, $matches)) {
            return $url;
        }
        $prefix = $matches[1];
    } elseif (isset($parts['scheme']) || isset($parts['host'])) {
        $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : '';
        if (!in_array($scheme, ['http', 'https'], true) ||
            isset($parts['user']) || isset($parts['pass']) || !isset($parts['host'])) {
            return $url;
        }
        if (isset($parts['port'])) {
            $port = (int) $parts['port'];
            if (($scheme === 'http' && $port !== 80) || ($scheme === 'https' && $port !== 443)) {
                return $url;
            }
        }

        $host = strtolower(rtrim((string) $parts['host'], '.'));
        if (!in_array($host, eta_modern_internal_link_hosts(), true) ||
            !preg_match('~^([a-z][a-z0-9+.-]*://[^/?#]+)~i', $url, $matches)) {
            return $url;
        }
        $prefix = $matches[1];
    } elseif ($url[0] !== '/' || strncmp($url, '//', 2) === 0) {
        return $url;
    }

    // Keep even empty query/fragment delimiters exactly as they were rendered.
    $query_position = strpos($url, '?');
    $fragment_position = strpos($url, '#');
    if ($query_position === false) {
        $suffix_position = $fragment_position;
    } elseif ($fragment_position === false) {
        $suffix_position = $query_position;
    } else {
        $suffix_position = min($query_position, $fragment_position);
    }
    $suffix = $suffix_position === false ? '' : substr($url, $suffix_position);

    return $prefix . $target . $suffix;
}

/**
 * Canonicalize legacy internal anchor destinations in rendered public HTML.
 *
 * WordPress's streaming HTML processor is used when present. The conservative
 * fallback only touches quoted href attributes inside anchor start tags.
 * Neither route writes to the database or changes non-link attributes.
 *
 * @param mixed $html Rendered HTML.
 * @return mixed Filtered HTML, or the original non-string value.
 */
function eta_modern_canonicalize_rendered_internal_links($html)
{
    if (!is_string($html) || $html === '' ||
        (function_exists('is_admin') && is_admin())) {
        return $html;
    }

    if (class_exists('WP_HTML_Tag_Processor')) {
        $processor = new WP_HTML_Tag_Processor($html);
        while ($processor->next_tag('A')) {
            $href = $processor->get_attribute('href');
            if (!is_string($href)) {
                continue;
            }

            $canonical = eta_modern_canonicalize_rendered_internal_url($href);
            if ($canonical !== $href) {
                $processor->set_attribute('href', $canonical);
            }
        }

        return $processor->get_updated_html();
    }

    return preg_replace_callback(
        '/<a\b[^>]*>/i',
        function ($anchor_match) {
            return preg_replace_callback(
                '/(\bhref\s*=\s*)(["\'])(.*?)\2/is',
                function ($href_match) {
                    return $href_match[1] . $href_match[2] .
                        eta_modern_canonicalize_rendered_internal_url($href_match[3]) .
                        $href_match[2];
                },
                $anchor_match[0],
                1
            );
        },
        $html
    );
}

/**
 * Canonicalize the href emitted for a classic WordPress navigation-menu item.
 *
 * @param array<mixed> $attributes Menu-link attributes.
 * @return array<mixed>
 */
function eta_modern_canonicalize_nav_menu_link_attributes($attributes)
{
    if (!is_array($attributes) || !isset($attributes['href'])) {
        return $attributes;
    }

    $attributes['href'] = eta_modern_canonicalize_rendered_internal_url($attributes['href']);
    return $attributes;
}

/**
 * Resolve an eligible public request against the reviewed legacy map.
 *
 * @param string $request_method HTTP request method.
 * @param string $request_uri    Request URI, optionally including a query.
 * @return string|null Reviewed site-relative destination, or null.
 */
function eta_modern_legacy_request_target($request_method, $request_uri)
{
    if (!is_string($request_method) || !is_string($request_uri)) {
        return null;
    }

    $request_method = strtoupper(trim($request_method));
    if (!in_array($request_method, ['GET', 'HEAD'], true)) {
        return null;
    }

    return eta_modern_legacy_redirect_target($request_uri);
}

/**
 * Apply the reviewed redirect map before plugin/database redirect rules run.
 *
 * Rank Math may contain older redirect records for the same source paths. This
 * early exact-map handler makes the version-controlled destination authoritative
 * without affecting non-GET/HEAD requests or URLs outside the reviewed map.
 *
 * @return void
 */
function eta_modern_maybe_redirect_legacy_request()
{
    $request_method = isset($_SERVER['REQUEST_METHOD']) ? (string) $_SERVER['REQUEST_METHOD'] : '';
    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $target = eta_modern_legacy_request_target($request_method, $request_uri);

    if ($target === null) {
        return;
    }

    if (wp_safe_redirect(home_url($target), 301, 'Envi Tech AL')) {
        exit;
    }
}

/**
 * Keep a legacy post object out of Rank Math before its permalink is built.
 *
 * Rank Math resolves get_permalink() before applying its sitemap-entry filter.
 * The public-link canonicalizer therefore turns a legacy permalink into its
 * canonical target too early for the later entry filter to identify the source
 * object. Rank Math's post-object filter explicitly accepts false to omit an
 * object, so match the object's exact stored slug against the reviewed map.
 *
 * Legacy-map source slugs are unique. Both the stored WordPress slug and the
 * map slug are decoded before comparison so the reviewed Unicode-hyphen route
 * is handled without broad or substring matching.
 *
 * @param object|false $post Post object supplied by Rank Math.
 * @return object|false Original object, or false for a reviewed legacy source.
 */
function eta_modern_filter_legacy_redirect_sitemap_post_object($post)
{
    if (!is_object($post) || !isset($post->post_name) || !is_string($post->post_name)) {
        return $post;
    }

    $post_slug = rawurldecode($post->post_name);
    if ($post_slug === '' || strpos($post_slug, '/') !== false || strpos($post_slug, "\0") !== false) {
        return $post;
    }

    foreach (array_keys(eta_modern_legacy_redirect_map()) as $source) {
        $source_path = eta_modern_normalize_legacy_redirect_path($source);
        if ($source_path === null) {
            continue;
        }

        $source_slug = rawurldecode(basename(rtrim($source_path, '/')));
        if ($source_slug === $post_slug) {
            return false;
        }
    }

    return $post;
}

/**
 * Keep redirected legacy URLs out of Rank Math XML sitemaps.
 *
 * @param array|false $url    Sitemap entry data.
 * @param string      $type   Sitemap object type.
 * @param object|null $object Source object.
 * @return array|false
 */
function eta_modern_filter_legacy_redirect_sitemap_entry($url, $type = '', $object = null)
{
    unset($type, $object);

    if (!is_array($url) || !isset($url['loc']) || !is_string($url['loc']) || $url['loc'] === '') {
        return $url;
    }

    return eta_modern_legacy_redirect_target($url['loc']) === null ? $url : false;
}

/**
 * Keep Rank Math from reusing a sitemap generated before the redirect map changed.
 *
 * Rank Math documents this filter for sites where sitemap freshness is more
 * important than its transient cache. Full-page/edge caches still need a purge
 * or a sitemap exclusion rule at their respective layers.
 *
 * @param bool $enabled Whether Rank Math's sitemap transient cache is enabled.
 * @return bool
 */
function eta_modern_disable_rank_math_sitemap_transient_cache($enabled)
{
    unset($enabled);
    return false;
}

if (function_exists('add_action')) {
    add_action('init', 'eta_modern_maybe_redirect_legacy_request', -9999);
}

if (function_exists('add_filter')) {
    add_filter('rank_math/sitemap/post_object', 'eta_modern_filter_legacy_redirect_sitemap_post_object', 10, 1);
    add_filter('rank_math/sitemap/entry', 'eta_modern_filter_legacy_redirect_sitemap_entry', 10, 3);
    add_filter('rank_math/sitemap/enable_caching', 'eta_modern_disable_rank_math_sitemap_transient_cache', 10, 1);
    add_filter('nav_menu_link_attributes', 'eta_modern_canonicalize_nav_menu_link_attributes', 99, 4);
    add_filter('post_link', 'eta_modern_canonicalize_rendered_internal_url', 99, 3);
    add_filter('page_link', 'eta_modern_canonicalize_rendered_internal_url', 99, 3);
    add_filter('post_type_link', 'eta_modern_canonicalize_rendered_internal_url', 99, 4);
    add_filter('the_content', 'eta_modern_canonicalize_rendered_internal_links', 99, 1);
    add_filter('the_excerpt', 'eta_modern_canonicalize_rendered_internal_links', 99, 1);
    add_filter('widget_text', 'eta_modern_canonicalize_rendered_internal_links', 99, 1);
    add_filter('widget_text_content', 'eta_modern_canonicalize_rendered_internal_links', 99, 1);
    add_filter('render_block', 'eta_modern_canonicalize_rendered_internal_links', 99, 2);
}
