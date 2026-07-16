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
