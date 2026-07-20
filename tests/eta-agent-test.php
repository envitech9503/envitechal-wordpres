<?php

define('ABSPATH', __DIR__);

function add_action()
{
    // Registration is intentionally inert in this pure helper test.
}

function get_option($name, $default = '')
{
    return $default;
}

function wp_http_validate_url($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
}

function wp_parse_url($url, $component = -1)
{
    return parse_url($url, $component);
}

require dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal/inc/eta-agent.php';

function eta_agent_test_true($condition, $label)
{
    if ($condition) {
        return;
    }
    file_put_contents('php://stderr', "FAILED: {$label}\n", FILE_APPEND);
    exit(1);
}

putenv('ETA_AGENT_ENDPOINT=https://example.agents.do-ai.run');
putenv('ETA_AGENT_ACCESS_KEY=server-only-test-key');
eta_agent_test_true(eta_agent_is_configured(), 'approved DigitalOcean endpoint and server-side key configure the proxy');

putenv('ETA_AGENT_ENDPOINT=https://example.invalid');
eta_agent_test_true(eta_agent_endpoint() === '', 'unapproved hosts are rejected to prevent SSRF');

$instruction = eta_agent_system_instruction();
eta_agent_test_true(strpos($instruction, 'Karachi laboratory is PNAC accredited') !== false, 'Karachi accreditation prohibition is explicit');
eta_agent_test_true(strpos($instruction, 'Do not provide any price') !== false, 'price prohibition is explicit');
eta_agent_test_true(strpos($instruction, 'turnaround time') !== false, 'turnaround fabrication prohibition is explicit');
eta_agent_test_true(strpos($instruction, 'Every substantive answer must cite') !== false, 'canonical citation requirement is explicit');

$citations = [];
eta_agent_collect_citations([
    'answer' => 'Use https://envitechal.com/report-verification-portal/.',
    'untrusted' => 'https://example.invalid/not-a-source',
], $citations);
eta_agent_test_true(
    array_values($citations) === ['https://envitechal.com/report-verification-portal/'],
    'only canonical Envi Tech AL citations are returned to the browser'
);

echo "Agent proxy safety tests passed.\n";

