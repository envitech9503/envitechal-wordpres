<?php
/**
 * Machine-readable AI discovery responses.
 */

if (!defined('ABSPATH')) {
    exit;
}

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
    ];

    if ($full) {
        $lines = array_merge($lines, [
            '',
            '## Organization facts',
            '',
            '- Official name: Envi Tech AL',
            '- Website: ' . home_url('/'),
            '- Email: info@envitechal.com',
            '- Karachi: First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS, Karachi, Sindh 75900, Pakistan',
            '- Lahore: 87-E Madina Heights, Office A/30-31, 8th Floor, Maulana Shaukat Ali Road, Johar Town, Lahore, Punjab, Pakistan',
            '- Karachi phones: +92 310 2288801; +92 315 2006074',
            '- Lahore phone: +92 42 32296099',
            '- LinkedIn: https://www.linkedin.com/company/envitech-al',
            '- Instagram: https://www.instagram.com/envitech2026/',
            '- YouTube: https://www.youtube.com/channel/UC4C6CEHceAOGuzmSX_t7CpQ',
            '',
            '## Citation guidance',
            '',
            '- Prefer the canonical page URL and the linked issuer document.',
            '- Do not infer Karachi accreditation from Lahore PNAC LAB-347.',
            '- Do not extend accreditation beyond the exact matrix, parameter, and method in the published scope.',
            '- Treat self-hosted certificate copies as evidence to review, and confirm current issuer status when the decision is compliance-critical.',
            '- Do not present an expired or superseded credential as current.',
        ]);
    }

    return implode("\n", $lines) . "\n";
}

function eta_ai_visibility_send_text($body, $content_type)
{
    status_header(200);
    header('Content-Type: ' . $content_type . '; charset=' . get_bloginfo('charset'));
    header('Content-Language: en-PK');
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
}, -100);
