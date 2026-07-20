<?php
/**
 * Generate reviewed AI discovery files from the site's rendered primary content.
 *
 * Run after the Markdown extractor is deployed so the corpus and negotiated
 * representations use the same rendered source.
 */

declare(strict_types=1);

define('ABSPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

function add_action()
{
    // Hook registration is intentionally inert in this standalone generator.
}

function home_url($path = '')
{
    return 'https://envitechal.com' . $path;
}

require dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal/inc/ai-visibility.php';

function eta_discovery_fetch(string $url): string
{
    $marker = "\n__ETA_STATUS__:";
    $command = [
        'curl', '-sS', '-L', '--max-time', '30',
        '-A', 'EnviTechAL-Discovery-Generator/1.0',
        '-H', 'Accept: text/html',
        '--write-out', $marker . '%{http_code}',
        $url,
    ];
    $pipes = [];
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start curl for ' . $url);
    }
    $response = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit_code = proc_close($process);
    $marker_position = strrpos((string) $response, $marker);
    if ($exit_code !== 0 || $marker_position === false) {
        throw new RuntimeException('Unable to fetch ' . $url . ': ' . trim((string) $error));
    }

    $body = substr((string) $response, 0, $marker_position);
    $status = (int) substr((string) $response, $marker_position + strlen($marker));
    if ($status !== 200) {
        throw new RuntimeException(sprintf('Expected HTTP 200 for %s; received %d', $url, $status));
    }

    return $body;
}

function eta_discovery_last_modified_map(): array
{
    $map = [];
    foreach (['page-sitemap.xml', 'services-sitemap.xml', 'post-sitemap.xml'] as $sitemap) {
        $xml = eta_discovery_fetch(home_url('/' . $sitemap));
        if (!preg_match_all('#<url>\s*<loc>(.*?)</loc>\s*<lastmod>(.*?)</lastmod>#s', $xml, $matches, PREG_SET_ORDER)) {
            throw new RuntimeException('No dated URLs found in ' . $sitemap);
        }
        foreach ($matches as $match) {
            $map[html_entity_decode(trim($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')] = date('d-m-Y', strtotime(trim($match[2])));
        }
    }
    return $map;
}

$urls = [
    home_url('/'),
    home_url('/services/'),
    home_url('/accreditations-certifications/'),
    home_url('/environmental-testing-faqs-pakistan/'),
    home_url('/karachi-environmental-lab/'),
    home_url('/lahore-environmental-lab/'),
    home_url('/services/water-testing-lab-services/'),
    home_url('/services/analytical-lab-services/'),
    home_url('/services/environmental-consultancy/'),
    home_url('/services/equipment-calibration-services/'),
    home_url('/report-verification-portal/'),
    home_url('/aboutus/'),
    home_url('/contact-us-envi-tech-al/'),
    home_url('/sindh-environmental-quality-standards-seqs/'),
    home_url('/water-testing-cost-karachi/'),
    home_url('/environmental-monitoring-report-emr-emp-sindh-epa/'),
];

$last_modified = eta_discovery_last_modified_map();
$sections = [
    '# Envi Tech AL full AI-readable corpus',
    '',
    '> Rendered primary content from Envi Tech AL core pages and high-value Knowledge Hub references.',
    '',
    'Use page-specific evidence and verify the laboratory location, matrix, parameter, method, validity, and current credential before describing work as accredited.',
    '',
    'PNAC LAB-347 applies only to the Lahore premises and only to the matrix, parameter, and method combinations in the published scope. Do not infer Karachi accreditation. Do not infer or publish prices or turnaround times.',
];

foreach ($urls as $url) {
    if (!isset($last_modified[$url])) {
        throw new RuntimeException('Sitemap post_modified date missing for ' . $url);
    }
    $extracted = eta_ai_visibility_extract_main_markdown(eta_discovery_fetch($url), $url);
    if (strlen(strip_tags($extracted['content'])) < 80) {
        throw new RuntimeException('Rendered primary content was empty for ' . $url);
    }

    $sections[] = '';
    $sections[] = '---';
    $sections[] = '';
    $sections[] = '## ' . $url;
    $sections[] = '';
    $sections[] = 'Last updated: ' . $last_modified[$url];
    $sections[] = '';
    $sections[] = $extracted['content'];
}

$corpus = implode("\n", $sections) . "\n";
$bytes = strlen($corpus);
if ($bytes < 40000 || $bytes > 120000) {
    throw new RuntimeException(sprintf('Generated corpus is %d bytes; required range is 40000-120000', $bytes));
}

$root = dirname(__DIR__);
$outputs = [
    $root . '/deploy/public_html/llms.txt' => eta_ai_visibility_llms_text(false),
    $root . '/deploy/public_html/llms-full.txt' => $corpus,
    $root . '/wp-content/themes/generatepress-envitechal/inc/llms-full.txt' => $corpus,
];

foreach ($outputs as $path => $contents) {
    if (file_put_contents($path, $contents, LOCK_EX) !== strlen($contents)) {
        throw new RuntimeException('Failed to write ' . $path);
    }
    echo sprintf("%s: %d bytes\n", $path, strlen($contents));
}
