<?php
/**
 * Machine-readable AI discovery responses.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return whether the request explicitly prefers the Markdown representation.
 */
function eta_ai_visibility_wants_markdown()
{
    $accept = isset($_SERVER['HTTP_ACCEPT']) ? strtolower((string) $_SERVER['HTTP_ACCEPT']) : '';
    if ($accept === '' || strpos($accept, 'text/markdown') === false) {
        return false;
    }

    foreach (explode(',', $accept) as $range) {
        $parts = array_map('trim', explode(';', $range));
        if (($parts[0] ?? '') !== 'text/markdown') {
            continue;
        }

        foreach (array_slice($parts, 1) as $parameter) {
            if (preg_match('/^q\s*=\s*0(?:\.0*)?$/', $parameter)) {
                return false;
            }
        }

        return true;
    }

    return false;
}

/**
 * Resolve the canonical URL for the rendered representation.
 */
function eta_ai_visibility_canonical_url()
{
    if (is_singular() || is_front_page()) {
        $post_id = get_queried_object_id();
        $permalink = $post_id ? get_permalink($post_id) : '';
        if (is_string($permalink) && $permalink !== '') {
            return $permalink;
        }
    }

    if (is_post_type_archive()) {
        $post_type = get_query_var('post_type');
        $post_type = is_array($post_type) ? reset($post_type) : $post_type;
        $archive_url = $post_type ? get_post_type_archive_link($post_type) : '';
        if (is_string($archive_url) && $archive_url !== '') {
            return $archive_url;
        }
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash((string) $_SERVER['REQUEST_URI']) : '/';
    $path = parse_url($request_uri, PHP_URL_PATH);
    return home_url(is_string($path) && $path !== '' ? $path : '/');
}

/**
 * Derive a truthful update date from WordPress post_modified values.
 */
function eta_ai_visibility_last_updated()
{
    $post_id = get_queried_object_id();
    if ($post_id) {
        $modified = get_post_field('post_modified', $post_id);
        if (is_string($modified) && $modified !== '') {
            return wp_date('d-m-Y', strtotime($modified));
        }
    }

    $modified = get_lastpostmodified('blog');
    return $modified ? wp_date('d-m-Y', strtotime($modified)) : '';
}

/**
 * Make a rendered link absolute without changing mail, phone, or fragment links.
 */
function eta_ai_visibility_absolute_url($url, $canonical)
{
    $url = html_entity_decode(trim((string) $url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($url === '' || preg_match('#^(?:https?:|mailto:|tel:)#i', $url)) {
        return $url;
    }
    if ($url[0] === '#') {
        return rtrim($canonical, '#') . $url;
    }
    if ($url[0] === '/') {
        return home_url($url);
    }

    $canonical_path = parse_url($canonical, PHP_URL_PATH);
    $base_path = is_string($canonical_path) ? rtrim(str_replace('\\', '/', dirname($canonical_path)), '/') : '';
    return home_url(($base_path ? $base_path : '') . '/' . ltrim($url, './'));
}

function eta_ai_visibility_normalize_markdown_text($text)
{
    $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_ireplace('AI FAQ Center', 'Environmental Testing Questions and Answers', $text);
    $text = preg_replace('/[\t\r\n ]+/u', ' ', $text);
    return trim(is_string($text) ? $text : '');
}

/**
 * Convert inline DOM content to Markdown.
 */
function eta_ai_visibility_inline_markdown($node, $canonical)
{
    if ($node instanceof DOMText) {
        return eta_ai_visibility_normalize_markdown_text($node->nodeValue);
    }
    if (!($node instanceof DOMElement)) {
        return '';
    }

    $tag = strtolower($node->tagName);
    if (in_array($tag, ['script', 'style', 'noscript', 'template', 'svg', 'img'], true)) {
        return '';
    }
    if ($tag === 'br') {
        return "\n";
    }

    $content = '';
    foreach ($node->childNodes as $child) {
        $piece = eta_ai_visibility_inline_markdown($child, $canonical);
        if ($piece === '') {
            continue;
        }
        if ($content !== '' && !preg_match('/[\s\n]$/u', $content) && !preg_match('/^[\s\n.,;:!?\)\]]/u', $piece)) {
            $content .= ' ';
        }
        $content .= $piece;
    }
    $content = trim($content);

    if ($tag === 'a') {
        $href = eta_ai_visibility_absolute_url($node->getAttribute('href'), $canonical);
        return $content !== '' && $href !== '' ? '[' . $content . '](' . $href . ')' : $content;
    }
    if (in_array($tag, ['strong', 'b'], true) && $content !== '') {
        return '**' . $content . '**';
    }
    if (in_array($tag, ['em', 'i'], true) && $content !== '') {
        return '*' . $content . '*';
    }

    return $content;
}

function eta_ai_visibility_table_markdown($table, $canonical)
{
    $rows = [];
    foreach ($table->getElementsByTagName('tr') as $row) {
        $cells = [];
        foreach ($row->childNodes as $cell) {
            if (!($cell instanceof DOMElement) || !in_array(strtolower($cell->tagName), ['th', 'td'], true)) {
                continue;
            }
            $value = str_replace('|', '\\|', eta_ai_visibility_inline_markdown($cell, $canonical));
            $cells[] = eta_ai_visibility_normalize_markdown_text($value);
        }
        if ($cells) {
            $rows[] = $cells;
        }
    }
    if (!$rows) {
        return '';
    }

    $width = max(array_map('count', $rows));
    foreach ($rows as &$row) {
        $row = array_pad($row, $width, '');
    }
    unset($row);

    $lines = ['| ' . implode(' | ', $rows[0]) . ' |', '| ' . implode(' | ', array_fill(0, $width, '---')) . ' |'];
    foreach (array_slice($rows, 1) as $row) {
        $lines[] = '| ' . implode(' | ', $row) . ' |';
    }
    return implode("\n", $lines) . "\n\n";
}

/**
 * Recursively convert the supported primary-content elements to Markdown.
 */
function eta_ai_visibility_node_markdown($node, $canonical)
{
    if ($node instanceof DOMText) {
        return eta_ai_visibility_normalize_markdown_text($node->nodeValue);
    }
    if (!($node instanceof DOMElement)) {
        return '';
    }

    $tag = strtolower($node->tagName);
    if (in_array($tag, ['script', 'style', 'noscript', 'template', 'svg', 'img', 'nav'], true)) {
        return '';
    }
    if (preg_match('/^h([1-4])$/', $tag, $heading)) {
        $text = eta_ai_visibility_inline_markdown($node, $canonical);
        return $text !== '' ? str_repeat('#', (int) $heading[1]) . ' ' . $text . "\n\n" : '';
    }
    if ($tag === 'p') {
        $text = eta_ai_visibility_inline_markdown($node, $canonical);
        return $text !== '' ? $text . "\n\n" : '';
    }
    if ($tag === 'table') {
        return eta_ai_visibility_table_markdown($node, $canonical);
    }
    if (in_array($tag, ['ul', 'ol'], true)) {
        $lines = [];
        $position = 0;
        foreach ($node->childNodes as $child) {
            if (!($child instanceof DOMElement) || strtolower($child->tagName) !== 'li') {
                continue;
            }
            $position++;
            $text = eta_ai_visibility_inline_markdown($child, $canonical);
            if ($text !== '') {
                $lines[] = ($tag === 'ol' ? $position . '. ' : '- ') . $text;
            }
        }
        return $lines ? implode("\n", $lines) . "\n\n" : '';
    }
    if (in_array($tag, ['a', 'strong', 'b', 'em', 'i', 'span', 'small', 'label', 'button'], true)) {
        return eta_ai_visibility_inline_markdown($node, $canonical);
    }

    $content = '';
    foreach ($node->childNodes as $child) {
        $piece = eta_ai_visibility_node_markdown($child, $canonical);
        if ($piece === '') {
            continue;
        }
        if ($content !== '' && !preg_match('/\n\n$/', $content) && !preg_match('/^\n/', $piece)) {
            $content .= "\n\n";
        }
        $content .= $piece;
    }
    return $content;
}

/**
 * Extract the final rendered primary region and convert it to Markdown.
 */
function eta_ai_visibility_extract_main_markdown($html, $canonical)
{
    if (!class_exists('DOMDocument') || !is_string($html) || stripos($html, '<main') === false) {
        return ['title' => '', 'content' => ''];
    }

    $document = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $loaded = $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) {
        return ['title' => '', 'content' => ''];
    }

    $xpath = new DOMXPath($document);
    $main = $xpath->query('//main[1]')->item(0);
    if (!$main) {
        $main = $xpath->query('//*[@role="main"][1]')->item(0);
    }
    if (!$main) {
        $main = $xpath->query('//*[@id="primary"][1]')->item(0);
    }
    if (!$main) {
        return ['title' => '', 'content' => ''];
    }

    $remove_query = './/script|.//style|.//noscript|.//template|.//nav|.//*[@id="secondary"]|.//*[contains(concat(" ", normalize-space(@class), " "), " eta-chatbot-root ")]|.//*[contains(concat(" ", normalize-space(@class), " "), " sidebar ")]|.//*[contains(concat(" ", normalize-space(@class), " "), " widget-area ")]';
    $remove = [];
    foreach ($xpath->query($remove_query, $main) as $node) {
        $remove[] = $node;
    }
    foreach ($remove as $node) {
        if ($node->parentNode) {
            $node->parentNode->removeChild($node);
        }
    }

    $heading = $xpath->query('.//h1[1]', $main)->item(0);
    $title = $heading ? eta_ai_visibility_normalize_markdown_text($heading->textContent) : '';
    $content = eta_ai_visibility_node_markdown($main, $canonical);
    $content = preg_replace("/\n[ \t]+/", "\n", (string) $content);
    $content = preg_replace("/\n{3,}/", "\n\n", (string) $content);

    return [
        'title' => html_entity_decode(trim($title), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'content' => trim((string) $content),
    ];
}

function eta_ai_visibility_build_markdown_document($html)
{
    $canonical = eta_ai_visibility_canonical_url();
    $extracted = eta_ai_visibility_extract_main_markdown($html, $canonical);
    if (strlen(wp_strip_all_tags($extracted['content'])) < 80) {
        return '';
    }

    $title = $extracted['title'];
    if ($title === '') {
        $title = html_entity_decode(wp_strip_all_tags(wp_get_document_title()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    $updated = eta_ai_visibility_last_updated();
    $lines = [
        '# ' . trim($title),
        '',
        'Environmental Lab & Consultancy',
        '',
        'Canonical URL: ' . $canonical,
        'Last updated: ' . $updated,
        '',
        '## Content',
        '',
        $extracted['content'],
        '',
        '## Discovery',
        '',
        '- Website: ' . home_url('/'),
        '- llms.txt: ' . home_url('/llms.txt'),
        '- Robots and Content Signals: ' . home_url('/robots.txt'),
        '- Sitemap: ' . home_url('/sitemap_index.xml'),
        '- Agent skills index: ' . home_url('/.well-known/agent-skills/index.json'),
        '- Full Markdown corpus: ' . home_url('/llms-full.txt'),
        '- Service catalogue: ' . home_url('/services/'),
        '- Report verification: ' . home_url('/report-verification-portal/'),
        '- Security contact: ' . home_url('/.well-known/security.txt'),
    ];

    return implode("\n", $lines) . "\n";
}

function eta_ai_visibility_markdown_cache_key()
{
    $version = (string) get_option('eta_ai_markdown_cache_version', '1');
    return 'eta_ai_md_' . md5($version . '|' . eta_ai_visibility_canonical_url());
}

function eta_ai_visibility_send_markdown_headers($status = 200)
{
    status_header($status);
    header('Content-Type: text/markdown; charset=UTF-8', true);
    header('Content-Language: en-GB', true);
    header('Vary: Accept', false);
    header('Cache-Control: private, no-store, no-cache, must-revalidate', true);
    header('X-Content-Type-Options: nosniff', true);
}

function eta_ai_visibility_markdown_output($html)
{
    $markdown = eta_ai_visibility_build_markdown_document($html);
    if ($markdown === '') {
        eta_ai_visibility_send_markdown_headers(406);
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD'
            ? ''
            : "Not Acceptable: no substantive primary content could be extracted.\n";
    }

    set_transient(eta_ai_visibility_markdown_cache_key(), $markdown, DAY_IN_SECONDS);
    eta_ai_visibility_send_markdown_headers(200);
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD' ? '' : $markdown;
}

add_action('template_redirect', function () {
    if (
        is_admin()
        || is_feed()
        || wp_doing_ajax()
        || (defined('REST_REQUEST') && REST_REQUEST)
        || (isset($_SERVER['HTTP_X_ETA_MARKDOWN_SOURCE']) && $_SERVER['HTTP_X_ETA_MARKDOWN_SOURCE'] === '1')
        || !eta_ai_visibility_wants_markdown()
    ) {
        return;
    }

    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, ['GET', 'HEAD'], true)) {
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash((string) $_SERVER['REQUEST_URI']) : '';
    $path = $request_uri !== '' ? parse_url($request_uri, PHP_URL_PATH) : '';
    if (
        in_array($path, ['/robots.txt', '/llms.txt', '/llms-full.txt', '/.well-known/security.txt', '/wp-json'], true)
        || strpos((string) $path, '/wp-json/') === 0
        || isset($_GET['rest_route'])
    ) {
        return;
    }

    $cached = get_transient(eta_ai_visibility_markdown_cache_key());
    if (is_string($cached) && $cached !== '') {
        eta_ai_visibility_send_markdown_headers(200);
        if ($method !== 'HEAD') {
            echo $cached; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        exit;
    }

    $source_url = add_query_arg('eta_markdown_source', '1', eta_ai_visibility_canonical_url());
    $response = wp_remote_get($source_url, [
        'headers' => [
            'Accept' => 'text/html',
            'X-ETA-Markdown-Source' => '1',
        ],
        'redirection' => 3,
        'timeout' => 20,
        'user-agent' => 'EnviTechAL-MarkdownRenderer/1.0',
    ]);
    $html = is_wp_error($response) ? '' : (string) wp_remote_retrieve_body($response);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        eta_ai_visibility_send_markdown_headers(406);
        if ($method !== 'HEAD') {
            echo "Not Acceptable: the rendered primary content could not be retrieved.\n";
        }
        exit;
    }

    echo eta_ai_visibility_markdown_output($html); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit;
}, -2000);

add_action('save_post', function ($post_id) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }
    update_option('eta_ai_markdown_cache_version', (string) microtime(true), false);
}, 10, 1);

/**
 * Identify only the legacy raw chatbot script attributes confirmed in production.
 */
function eta_ai_visibility_is_legacy_chatbot_script($tag)
{
    if (!is_string($tag) || !preg_match('#^<script\b(?P<attributes>[^>]*)>#is', $tag, $match)) {
        return false;
    }

    $attributes = $match['attributes'];
    if (preg_match('/(?:^|\s)data-(?:agent|chatbot)-id(?=\s|=|\/|$)/i', $attributes)) {
        return true;
    }

    if (!preg_match('~(?:^|\s)src\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'<>]+))~i', $attributes, $source_match)) {
        return false;
    }

    $source = '';
    foreach ([1, 2, 3] as $index) {
        if (isset($source_match[$index]) && $source_match[$index] !== '') {
            $source = $source_match[$index];
            break;
        }
    }

    $source = html_entity_decode(trim($source), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $source_path = parse_url($source, PHP_URL_PATH);
    if (!is_string($source_path) || $source_path === '') {
        return false;
    }

    return (bool) preg_match('#/static/chatbot/widget\.js$#i', $source_path);
}

function eta_ai_visibility_is_legacy_chatbot_style($tag)
{
    if (!is_string($tag) || !preg_match('#^<style\b[^>]*>([\s\S]*)</style\s*>$#i', $tag, $match)) {
        return false;
    }

    return (bool) preg_match('/(?<![A-Za-z0-9_-])\.chatbot-button(?![A-Za-z0-9_-])/i', $match[1]);
}

/**
 * Remove complete confirmed chatbot script tags and only their adjacent raw style.
 */
function eta_ai_visibility_remove_legacy_chatbot_html($html)
{
    if (!is_string($html) || $html === '') {
        return $html;
    }

    $count = preg_match_all(
        '#<(script|style)\b[^>]*>[\s\S]*?</\1\s*>#i',
        $html,
        $matches,
        PREG_OFFSET_CAPTURE
    );
    if (!$count) {
        return $html;
    }

    $tokens = [];
    foreach ($matches[0] as $index => $match) {
        $tokens[] = [
            'tag' => $match[0],
            'type' => strtolower($matches[1][$index][0]),
            'offset' => $match[1],
            'length' => strlen($match[0]),
        ];
    }

    $ranges = [];
    $add_range = static function ($token) use (&$ranges) {
        $ranges[$token['offset'] . ':' . $token['length']] = [$token['offset'], $token['length']];
    };
    $is_immediately_adjacent = static function ($left, $right) use ($html) {
        $between_start = $left['offset'] + $left['length'];
        $between_length = $right['offset'] - $between_start;
        return $between_length >= 0 && preg_match('/^\s*$/', substr($html, $between_start, $between_length));
    };

    foreach ($tokens as $index => $token) {
        if ($token['type'] !== 'script' || !eta_ai_visibility_is_legacy_chatbot_script($token['tag'])) {
            continue;
        }

        $add_range($token);

        if (isset($tokens[$index - 1])) {
            $previous = $tokens[$index - 1];
            if (
                $previous['type'] === 'style'
                && eta_ai_visibility_is_legacy_chatbot_style($previous['tag'])
                && $is_immediately_adjacent($previous, $token)
            ) {
                $add_range($previous);
            }
        }

        if (isset($tokens[$index + 1])) {
            $next = $tokens[$index + 1];
            if (
                $next['type'] === 'style'
                && eta_ai_visibility_is_legacy_chatbot_style($next['tag'])
                && $is_immediately_adjacent($token, $next)
            ) {
                $add_range($next);
            }
        }
    }

    if (!$ranges) {
        return $html;
    }

    $ranges = array_values($ranges);
    usort($ranges, static function ($left, $right) {
        return $right[0] <=> $left[0];
    });
    foreach ($ranges as $range) {
        $html = substr_replace($html, '', $range[0], $range[1]);
    }

    return $html;
}

function eta_ai_visibility_filter_legacy_chatbot_response($html)
{
    if (!is_string($html) || $html === '') {
        return $html;
    }

    foreach (array_reverse(headers_list()) as $header) {
        if (stripos($header, 'Content-Type:') !== 0) {
            continue;
        }

        if (!preg_match('#(?:text/html|application/xhtml\+xml)(?:\s*;|\s*$)#i', $header)) {
            return $html;
        }

        return eta_ai_visibility_remove_legacy_chatbot_html($html);
    }

    if (!preg_match('/(?:<!doctype\s+html\b|<html\b)/i', $html)) {
        return $html;
    }

    return eta_ai_visibility_remove_legacy_chatbot_html($html);
}

add_action('template_redirect', function () {
    if (is_admin() || is_feed() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash((string) $_SERVER['REQUEST_URI']) : '';
    $path = $request_uri !== '' ? parse_url($request_uri, PHP_URL_PATH) : '';
    if (
        $path === '/llms.txt'
        || $path === '/llms-full.txt'
        || $path === '/wp-json'
        || strpos((string) $path, '/wp-json/') === 0
        || isset($_GET['rest_route'])
    ) {
        return;
    }

    ob_start('eta_ai_visibility_filter_legacy_chatbot_response');
}, -1000);

function eta_ai_visibility_llms_text($full = false)
{
    if ($full) {
        $corpus_file = __DIR__ . '/llms-full.txt';
        if (is_readable($corpus_file)) {
            return (string) file_get_contents($corpus_file);
        }
    }

    $lines = [
        '# Envi Tech AL',
        '',
        '> Official source for Envi Tech AL environmental laboratory, monitoring, calibration, consultancy, and compliance-support information in Pakistan.',
        '',
        'Use page-specific evidence and verify the laboratory location, matrix, parameter, method, validity, and current credential before describing work as accredited.',
        '',
        '## Official pages',
        '',
        '- [Home](' . home_url('/') . ')',
        '- [About Envi Tech AL](' . home_url('/aboutus/') . ')',
        '- [Services](' . home_url('/services/') . ')',
        '- [Credentials and verification](' . home_url('/accreditations-certifications/') . ')',
        '- [Environmental testing FAQs](' . home_url('/environmental-testing-faqs-pakistan/') . ')',
        '- [Report verification](' . home_url('/report-verification-portal/') . ')',
        '- [Contact](' . home_url('/contact-us-envi-tech-al/') . ')',
        '- [Extended entity facts](' . home_url('/llms-full.txt') . ')',
        '',
        '## Core services',
        '',
        '- [Environmental laboratory and analytical services](' . home_url('/services/analytical-lab-services/') . ')',
        '- [Water and wastewater testing](' . home_url('/services/water-testing-lab-services/') . ')',
        '- [Environmental consultancy](' . home_url('/services/environmental-consultancy/') . ')',
        '- [Equipment calibration](' . home_url('/services/equipment-calibration-services/') . ')',
        '- [Ballast water testing](' . home_url('/services/ballast-water-testing-services/') . ')',
        '- [Ambient air monitoring](' . home_url('/ambient-air-monitoring-services/') . ')',
        '',
        '## Locations',
        '',
        '- [Karachi environmental laboratory](' . home_url('/karachi-environmental-lab/') . ') — current credential and method scope must be confirmed for each assignment.',
        '- [Lahore environmental laboratory](' . home_url('/lahore-environmental-lab/') . ') — PNAC LAB-347 applies only to the Lahore premises and listed water/wastewater matrix, parameter, and method combinations.',
        '',
        '## Primary credential evidence',
        '',
        '- [PNAC LAB-347 official scope](https://www.pnac.gov.pk/index.php/pdfFiles/LAB-347) — Lahore laboratory; ISO/IEC 17025:2017; document states validity through 21 September 2028; only listed matrix, parameter, and method combinations are accredited.',
        '- [Punjab EPA official 2025–2028 certificate](https://epd.punjab.gov.pk/system/files/EnviTech_%202025-2028_merged.pdf) — review its conditions for the requested work.',
        '- [ISO 9001:2015 certificate](' . home_url('/wp-content/uploads/2026/01/ISO-9001-Certificate.pdf') . ') — registration TPAK-080177324-QMS; certificate states validity through 27 August 2027.',
        '- [ISO 14001:2015 certificate](' . home_url('/wp-content/uploads/2026/01/ISO-14001-Certificate.pdf') . ') — registration TPAK-080177424-EMS; certificate states validity through 27 August 2027.',
        '- [Published Sindh EPA document](' . home_url('/wp-content/uploads/2026/01/SEPA-NOC.pdf') . ') — confirm current issuer status, validity, conditions, and scope before reliance.',
        '',
        '## Knowledge Hub',
        '',
        '- [How to read a water test report: PEQS, WHO and SEQS](' . home_url('/how-to-read-water-test-report-peqs-who/') . ') — explains how to interpret parameters, limits, units, and compliance context.',
        '- [Sindh EPA vs Punjab EPA: NOC and approvals compared](' . home_url('/sindh-epa-vs-punjab-epa-noc-lahore/') . ') — compares provincial environmental approval pathways and evidence needs.',
        '- [Water testing cost in Karachi](' . home_url('/water-testing-cost-karachi/') . ') — explains quotation factors without publishing or inventing prices.',
        '- [Wastewater and ETP effluent testing for SEQS and NEQS](' . home_url('/wastewater-etp-effluent-testing-seqs-neqs/') . ') — covers sampling, parameters, and standards-based interpretation.',
        '- [Environmental Monitoring Report and EMP guide](' . home_url('/environmental-monitoring-report-emr-emp-sindh-epa/') . ') — practical evidence and reporting workflow for environmental monitoring.',
        '- [TDAP registered laboratory overview](' . home_url('/what-is-tdap-registered-lab-essential-information/') . ') — explains the role and verification of a TDAP-registered laboratory.',
        '- [Engine performance testing and fuel use](' . home_url('/fuel-saving-through-engine-performance-testing/') . ') — discusses measurement-led engine performance assessment.',
        '- [Choosing a reliable water testing company](' . home_url('/choosing-a-reliable-water-testing-company/') . ') — selection criteria for defensible water analysis.',
        '- [Drinking-water testing laboratory guide](' . home_url('/water-testing-laboratory-for-drinking-water/') . ') — covers drinking-water sampling and report interpretation.',
        '- [Gaseous and stack-emission testing](' . home_url('/gaseous-air-emission-testing-lab-near-me/') . ') — explains emissions monitoring scope and reporting considerations.',
        '- [Water testing laboratory solutions in Karachi](' . home_url('/envi-tech-al-water-testing-lab-solutions-karachi/') . ') — overview of water-testing use cases and scope selection.',
        '- [Certification consultancy in Pakistan](' . home_url('/consultancy-of-modern-certification-in-pakistan/') . ') — outlines certification-readiness and advisory support.',
        '- [Environmental consultancy services in Karachi](' . home_url('/environmental-consultancy-services-in-karachi-envi-tech-al/') . ') — environmental documentation and compliance-support overview.',
        '- [Ballast water testing services in Karachi](' . home_url('/ballast-water-testing-services-karachi/') . ') — maritime sampling and testing coordination overview.',
        '- [Benefits of environmental laboratory consultancy](' . home_url('/what-are-the-benefits-of-environmental-lab-consultancy/') . ') — explains how laboratory and advisory work connect.',
        '- [Services provided by Envi Tech AL](' . home_url('/what-services-does-envi-tech-al-provide/') . ') — overview of laboratory, monitoring, calibration, and consultancy services.',
        '- [Nine environmental laboratory and consultancy services](' . home_url('/9-services-provided-by-an-environmental-lab-and-consultancy/') . ') — reference guide to common technical service categories.',
        '- [Why consult an environmental laboratory](' . home_url('/why-you-should-consult-with-an-environmental-lab/') . ') — discusses scoping, evidence quality, and compliance decisions.',
        '- [Water-testing equipment and professional analysis](' . home_url('/high-tech-equipment-water-testing/') . ') — overview of instruments and controlled testing workflows.',
        '- [Understanding water-testing quality and procedures](' . home_url('/unveiling-water-testing-understanding-its-quality-and-procedures/') . ') — explains sampling, analysis, and quality controls.',
        '- [Benefits of environmental certification](' . home_url('/common-benefits-of-environmental-certification/') . ') — explains operational and compliance uses of certification systems.',
        '- [Environmental consultancy guide for Pakistan](' . home_url('/environmental-consultancy-karachi-guide/') . ') — detailed guide to consultancy scope and regulatory documentation.',
        '- [Equipment calibration in Karachi](' . home_url('/calibration-of-equipment-in-karachi/') . ') — covers calibration planning, traceability, and records.',
        '- [Qualities of a reliable water-testing laboratory](' . home_url('/3-qualities-of-a-great-water-testing-lab/') . ') — detailed laboratory selection and evidence guide.',
        '- [GOTS certification overview](' . home_url('/everything-you-need-to-know-about-gots-certification/') . ') — introduction to GOTS requirements and preparation.',
        '- [Drinking water and kidney-health risk context](' . home_url('/revealing-the-hidden-link-drinking-water-and-kidney-failure-risks/') . ') — discusses why water-quality evidence matters for health decisions.',
        '- [Water-testing guide](' . home_url('/new-insightful-guide-as-to-reveal-water-testing/') . ') — introductory guide to water-quality testing decisions.',
        '- [Environmental consultancy pathways](' . home_url('/5-ways-environmental-consultancy-karachi/') . ') — detailed environmental consultancy use cases.',
        '- [Complete environmental testing solutions](' . home_url('/complete-environmental-testing-solutions-at-envi-tech-al/') . ') — overview of coordinated field and laboratory services.',
        '- [Selecting a suitable environmental laboratory](' . home_url('/how-to-choose-the-suitable-environmental-lab/') . ') — practical criteria for laboratory selection.',
        '- [Water-testing challenges and strategies](' . home_url('/water-testing-challenges-expert-solutions-and-strategies/') . ') — common sampling and interpretation challenges.',
        '- [CE marking certification consultancy](' . home_url('/ce-marking-certification-in-pakistan/') . ') — overview of CE-marking preparation support in Pakistan.',
        '',
        '## Compliance references',
        '',
        '- [Sindh Environmental Quality Standards (SEQS)](' . home_url('/sindh-environmental-quality-standards-seqs/') . ') — site reference for Sindh parameters and compliance context.',
        '- [Punjab Environmental Quality Standards (PEQS)](' . home_url('/how-to-read-water-test-report-peqs-who/') . ') — PEQS interpretation within the water-report guide.',
        '- [National Environmental Quality Standards (NEQS)](' . home_url('/wastewater-etp-effluent-testing-seqs-neqs/') . ') — NEQS context within the wastewater and effluent guide.',
        '- [WHO drinking-water guideline context](' . home_url('/how-to-read-water-test-report-peqs-who/') . ') — WHO comparison guidance for water reports.',
    ];

    return implode("\n", $lines) . "\n";
}

function eta_ai_visibility_security_text($expires = '')
{
    if ($expires === '') {
        $expires = gmdate('Y-m-d\TH:i:s\Z', strtotime('+12 months'));
    }

    return "Contact: mailto:info@envitechal.com\n"
        . "Preferred-Languages: en, ur\n"
        . "Canonical: https://envitechal.com/.well-known/security.txt\n"
        . 'Expires: ' . $expires . "\n";
}

function eta_ai_visibility_send_text($body, $content_type)
{
    status_header(200);
    header('Content-Type: ' . $content_type . '; charset=' . get_bloginfo('charset'));
    header('Content-Language: en-GB');
    header('Cache-Control: public, max-age=300, s-maxage=3600');
    header('X-Content-Type-Options: nosniff');
    if (function_exists('eta_modern_is_staging_host') && eta_modern_is_staging_host()) {
        header('X-Robots-Tag: noindex, nofollow, noarchive', true);
    } else {
        header('X-Robots-Tag: index, follow', true);
    }

    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    exit;
}

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, ['GET', 'HEAD'], true)) {
        return;
    }

    $path = isset($_SERVER['REQUEST_URI']) ? parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '';
    if ($path === '/llms.txt') {
        eta_ai_visibility_send_text(eta_ai_visibility_llms_text(false), 'text/plain');
    }
    if ($path === '/llms-full.txt') {
        eta_ai_visibility_send_text(eta_ai_visibility_llms_text(true), 'text/plain');
    }
    if ($path === '/.well-known/security.txt') {
        eta_ai_visibility_send_text(eta_ai_visibility_security_text(), 'text/plain');
    }
}, -100);

add_action('wp_head', function () {
    if (is_admin() || !(is_front_page() || is_singular() || is_post_type_archive())) {
        return;
    }

    printf(
        '<link rel="alternate" type="text/markdown" href="%s" title="Markdown version">' . "\n",
        esc_url(eta_ai_visibility_canonical_url())
    );
    printf(
        '<link rel="llms" type="text/plain" href="%s">' . "\n",
        esc_url(home_url('/llms.txt'))
    );
}, 20);
