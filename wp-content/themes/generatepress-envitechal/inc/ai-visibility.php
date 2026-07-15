<?php
/**
 * Machine-readable discovery and Markdown responses.
 */

if (!defined('ABSPATH')) {
    exit;
}

function eta_ai_visibility_lines($values)
{
    $lines = [];
    foreach ((array) $values as $value) {
        $value = trim(wp_strip_all_tags((string) $value));
        if ($value !== '') {
            $lines[] = $value;
        }
    }

    return $lines;
}

function eta_ai_visibility_absolute_url($path)
{
    if (preg_match('#^https?://#i', (string) $path)) {
        return (string) $path;
    }

    return home_url('/' . ltrim((string) $path, '/'));
}

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

function eta_ai_visibility_page_markdown()
{
    $services_archive = is_post_type_archive('services');
    $canonical = is_front_page() ? home_url('/') : ($services_archive ? get_post_type_archive_link('services') : get_permalink());
    $title = is_front_page() ? 'Envi Tech AL' : ($services_archive ? 'Envi Tech AL Environmental Services' : eta_modern_display_title(get_the_ID()));
    $description = eta_modern_clamp_meta_description(eta_modern_meta_description(), 320);
    $lines = [
        '# ' . $title,
        '',
        '> ' . $description,
        '',
        '- Canonical URL: ' . $canonical,
        '- Publisher: Envi Tech AL',
        '- Contact: info@envitechal.com',
    ];

    if (is_front_page()) {
        return eta_ai_visibility_llms_text(true);
    }

    if ($services_archive) {
        $lines = array_merge($lines, [
            '',
            '## Service index',
            '',
            '- [Environmental laboratory and analytical services](' . home_url('/services/analytical-lab-services/') . ')',
            '- [Water and wastewater testing](' . home_url('/services/water-testing-lab-services/') . ')',
            '- [Environmental consultancy](' . home_url('/services/environmental-consultancy/') . ')',
            '- [Equipment calibration](' . home_url('/services/equipment-calibration-services/') . ')',
            '- [Ballast water testing](' . home_url('/services/ballast-water-testing-services/') . ')',
            '',
            'Credential status and accreditation scope must be confirmed for the requested laboratory location, matrix, parameter, and method.',
        ]);

        return implode("\n", eta_ai_visibility_lines_preserve_blanks($lines)) . "\n";
    }

    if (is_singular('services')) {
        $slug = get_post_field('post_name', get_the_ID());
        $profile = eta_modern_service_profile($slug);
        $lines = array_merge($lines, [
            '',
            '## Short answer',
            '',
            $profile['lead'],
            '',
            '## Typical outcomes',
            '',
        ]);
        foreach (eta_ai_visibility_lines($profile['outcomes'] ?? []) as $item) {
            $lines[] = '- ' . $item;
        }
        $lines = array_merge($lines, ['', '## Best for', '']);
        foreach (eta_ai_visibility_lines($profile['best_for'] ?? []) as $item) {
            $lines[] = '- ' . $item;
        }

        if (in_array($slug, ['analytical-lab-services', 'water-testing-lab-services'], true)) {
            $lines = array_merge($lines, [
                '',
                '## Credential and scope note',
                '',
                'Testing capability and accreditation are location-, matrix-, parameter-, and method-specific. PNAC LAB-347 applies only to the Lahore laboratory and the matrix, parameter, and method combinations listed in its published water/wastewater scope. Confirm current scope before booking.',
                '',
                '- [Credentials and verification](' . home_url('/accreditations-certifications/') . ')',
                '- [Official PNAC LAB-347 scope](https://www.pnac.gov.pk/index.php/pdfFiles/LAB-347)',
            ]);
        }

        $faqs = $slug === 'water-testing-lab-services' ? eta_modern_water_testing_faqs() : eta_modern_service_faqs($slug);
        if ($faqs) {
            $lines = array_merge($lines, ['', '## Frequently asked questions', '']);
            foreach ($faqs as $faq) {
                $question = $faq['question'] ?? ($faq[0] ?? '');
                $answer = $faq['answer'] ?? ($faq[1] ?? '');
                if ($question && $answer) {
                    $lines[] = '### ' . wp_strip_all_tags($question);
                    $lines[] = '';
                    $lines[] = wp_strip_all_tags($answer);
                    $lines[] = '';
                }
            }
        }
    } elseif (is_page(['karachi-environmental-lab', 'lahore-environmental-lab'])) {
        $lahore = is_page('lahore-environmental-lab');
        $lines = array_merge($lines, [
            '',
            '## Location facts',
            '',
            $lahore
                ? '- Address: 87-E Madina Heights, Office A/30-31, 8th Floor, Maulana Shaukat Ali Road, Johar Town, Lahore, Punjab, Pakistan'
                : '- Address: First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS, Karachi, Sindh 75900, Pakistan',
            $lahore ? '- Phone: +92 42 32296099' : '- Phones: +92 310 2288801; +92 315 2006074',
            '',
            '## Credential note',
            '',
            $lahore
                ? 'PNAC LAB-347 applies to the Lahore laboratory and only the listed water/wastewater matrix, parameter, and method combinations. The PNAC document states validity through 21 September 2028, subject to surveillance and current status.'
                : 'Do not infer current Karachi accreditation from the Lahore scope. Confirm the current Karachi credential, matrix, parameter, method, and validity before relying on an accreditation claim.',
        ]);
    } elseif (is_page(['certificates-approvals', 'accreditations-certifications'])) {
        $lines = array_merge($lines, [
            '',
            '## Verified evidence',
            '',
            '- [PNAC LAB-347 official scope](https://www.pnac.gov.pk/index.php/pdfFiles/LAB-347) — Lahore only; listed water/wastewater matrix, parameter, and method combinations; document states validity through 21 September 2028.',
            '- [Punjab EPA official 2025–2028 certificate](https://epd.punjab.gov.pk/system/files/EnviTech_%202025-2028_merged.pdf)',
            '- [ISO 9001:2015 certificate](' . home_url('/wp-content/uploads/2026/01/ISO-9001-Certificate.pdf') . ') — TPAK-080177324-QMS; certificate states validity through 27 August 2027.',
            '- [ISO 14001:2015 certificate](' . home_url('/wp-content/uploads/2026/01/ISO-14001-Certificate.pdf') . ') — TPAK-080177424-EMS; certificate states validity through 27 August 2027.',
            '- [Published Sindh EPA document](' . home_url('/wp-content/uploads/2026/01/SEPA-NOC.pdf') . ') — current issuer status, validity, conditions, and scope require confirmation.',
            '',
            'Do not infer Karachi accreditation from Lahore LAB-347 or extend accreditation beyond the exact published scope.',
        ]);
    } elseif (is_page('aboutus')) {
        $lines = array_merge($lines, [
            '',
            '## Organization facts',
            '',
            '- Official name: Envi Tech AL',
            '- Website: ' . home_url('/'),
            '- Email: info@envitechal.com',
            '- Karachi phones: +92 310 2288801; +92 315 2006074',
            '- Lahore phone: +92 42 32296099',
            '- [Credentials and verification](' . home_url('/accreditations-certifications/') . ')',
            '- [Services](' . home_url('/services/') . ')',
        ]);
    } elseif (is_page('contact-us-envi-tech-al')) {
        $lines = array_merge($lines, [
            '',
            '## Contact routes',
            '',
            '- Email: info@envitechal.com',
            '- Karachi phones: +92 310 2288801; +92 315 2006074',
            '- Lahore phone: +92 42 32296099',
            '- WhatsApp: https://wa.me/923102288801',
            '',
            'Share the city, sample or site, required parameters, report purpose, applicable standard or approval condition, and deadline. The team must confirm scope and current credential status before work begins.',
        ]);
    } elseif (is_page('downloads')) {
        $lines = array_merge($lines, ['', '## Downloads', '']);
        foreach (eta_modern_download_groups() as $group => $items) {
            $lines[] = '### ' . $group;
            $lines[] = '';
            foreach ($items as $item) {
                $lines[] = '- [' . wp_strip_all_tags($item[0]) . '](' . eta_ai_visibility_absolute_url($item[1]) . ')';
            }
            $lines[] = '';
        }
    } elseif (is_page(['frequently-asked-questions-water-testing-in-karachi', 'environmental-testing-faqs-pakistan'])) {
        $faqs = is_page('environmental-testing-faqs-pakistan') ? eta_modern_ai_faq_center_questions() : eta_modern_faq_page_questions();
        $lines = eta_ai_visibility_append_faqs($lines, $faqs);
    } elseif (is_page() && eta_modern_cluster_page_data(get_post_field('post_name', get_the_ID()))) {
        $cluster = eta_modern_cluster_page_data(get_post_field('post_name', get_the_ID()));
        $lines = array_merge($lines, ['', '## Short answer', '', $cluster['summary'], '', '## Scope examples', '']);
        foreach (eta_ai_visibility_lines(array_merge($cluster['covered'] ?? [], $cluster['parameters'] ?? [])) as $item) {
            $lines[] = '- ' . $item;
        }
        $lines = eta_ai_visibility_append_faqs($lines, $cluster['faqs'] ?? []);
    }

    return implode("\n", eta_ai_visibility_lines_preserve_blanks($lines)) . "\n";
}

function eta_ai_visibility_lines_preserve_blanks($lines)
{
    $clean = [];
    foreach ((array) $lines as $line) {
        if ($line === '') {
            $clean[] = '';
            continue;
        }

        $clean[] = trim(wp_strip_all_tags((string) $line));
    }

    return $clean;
}

function eta_ai_visibility_append_faqs($lines, $faqs)
{
    $lines = array_merge((array) $lines, ['', '## Frequently asked questions', '']);
    foreach ((array) $faqs as $faq) {
        $question = $faq['question'] ?? ($faq[0] ?? '');
        $answer = $faq['answer'] ?? ($faq[1] ?? '');
        if (!$question || !$answer) {
            continue;
        }

        $lines[] = '### ' . wp_strip_all_tags($question);
        $lines[] = '';
        $lines[] = wp_strip_all_tags($answer);
        $lines[] = '';
    }

    return $lines;
}

function eta_ai_visibility_accepts_markdown($accept)
{
    foreach (explode(',', (string) $accept) as $range) {
        $parts = array_map('trim', explode(';', $range));
        if (strtolower((string) array_shift($parts)) !== 'text/markdown') {
            continue;
        }

        $quality = 1.0;
        foreach ($parts as $part) {
            if (stripos($part, 'q=') === 0) {
                $quality = (float) substr($part, 2);
            }
        }

        return $quality > 0;
    }

    return false;
}

function eta_ai_visibility_has_complete_markdown()
{
    if (is_front_page() || is_post_type_archive('services') || is_singular('services')) {
        return true;
    }

    if (!is_page()) {
        return false;
    }

    $slug = get_post_field('post_name', get_the_ID());
    $supported = [
        'aboutus',
        'contact-us-envi-tech-al',
        'downloads',
        'karachi-environmental-lab',
        'lahore-environmental-lab',
        'certificates-approvals',
        'accreditations-certifications',
        'frequently-asked-questions-water-testing-in-karachi',
        'environmental-testing-faqs-pakistan',
    ];

    return in_array($slug, $supported, true) || (bool) eta_modern_cluster_page_data($slug);
}

function eta_ai_visibility_send_text($body, $content_type, $negotiated = false)
{
    status_header(200);
    header('Content-Type: ' . $content_type . '; charset=' . get_bloginfo('charset'));
    header('Content-Language: en-PK');
    header($negotiated ? 'Cache-Control: private, no-store, max-age=0' : 'Cache-Control: public, max-age=300, s-maxage=3600');
    header('Vary: Accept', false);
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

add_action('send_headers', function () {
    if (!is_admin() && !(defined('REST_REQUEST') && REST_REQUEST)) {
        header('Vary: Accept', false);
    }
}, 20);

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

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!in_array($method, ['GET', 'HEAD'], true)) {
        return;
    }

    $accept = isset($_SERVER['HTTP_ACCEPT']) ? (string) $_SERVER['HTTP_ACCEPT'] : '';
    if (!eta_ai_visibility_accepts_markdown($accept)) {
        return;
    }

    if (!eta_ai_visibility_has_complete_markdown()) {
        return;
    }

    if (is_singular()) {
        $post = get_post();
        if (!$post || $post->post_status !== 'publish' || post_password_required($post) || is_preview()) {
            return;
        }
    }

    if (is_user_logged_in()) {
        return;
    }

    $canonical = is_front_page() ? home_url('/') : (is_post_type_archive('services') ? get_post_type_archive_link('services') : get_permalink());
    if ($canonical) {
        header('Link: <' . esc_url_raw($canonical) . '>; rel="canonical"', false);
    }

    eta_ai_visibility_send_text(eta_ai_visibility_page_markdown(), 'text/markdown', true);
}, 20);
