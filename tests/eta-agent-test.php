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

putenv('ETA_AGENT_ACCESS_KEY');
putenv('ETA_AGENT_UUID=7aad85f9-f10f-11ef-bf8f-4e013e2ddde4');
putenv('ETA_AGENT_CHATBOT_KEY=server-only-widget-test-key');
eta_agent_test_true(eta_agent_is_configured(), 'approved endpoint plus UUID and server-side widget key configure the proxy');

putenv('ETA_AGENT_ENDPOINT=https://example.invalid');
eta_agent_test_true(eta_agent_endpoint() === '', 'unapproved hosts are rejected to prevent SSRF');

$instruction = eta_agent_system_instruction();
eta_agent_test_true(strpos($instruction, 'Karachi laboratory is PNAC accredited') !== false, 'Karachi accreditation prohibition is explicit');
eta_agent_test_true(strpos($instruction, 'Do not provide any price') !== false, 'price prohibition is explicit');
eta_agent_test_true(strpos($instruction, 'turnaround time') !== false, 'turnaround fabrication prohibition is explicit');
eta_agent_test_true(strpos($instruction, 'Every substantive answer must cite') !== false, 'canonical citation requirement is explicit');
eta_agent_test_true(strpos(eta_agent_policy_prompt('Test question'), 'Visitor question: Test question') !== false, 'stored-agent-compatible user policy wraps the visitor question');

$karachi = eta_agent_curated_response('Is your Karachi laboratory PNAC accredited?');
eta_agent_test_true(strpos($karachi['answer'], 'Lahore premises only') !== false, 'Karachi PNAC question is answered with the Lahore-only rule');

$credential = eta_agent_curated_response('What is your PNAC accreditation number and validity?');
eta_agent_test_true(strpos($credential['answer'], 'LAB-347') !== false && strpos($credential['answer'], '21-09-2028') !== false, 'verified PNAC identifier and validity are returned');

$pricing = eta_agent_curated_response('How much does water testing cost in Karachi?');
eta_agent_test_true(strpos($pricing['answer'], 'cannot provide or estimate prices') !== false, 'pricing requests cannot reach a generative answer');
eta_agent_test_true(!preg_match('/(?:PKR|Rs\.?|\$)\s*\d/i', $pricing['answer']), 'pricing fallback contains no quoted price');

$services = eta_agent_curated_response('What services does Envi Tech AL provide?');
eta_agent_test_true(strpos($services['answer'], 'PNAC LAB-347 applies only to the Lahore premises') !== false, 'service summary cannot generalise accreditation');

$seqs = eta_agent_curated_response('What are SEQS limits for industrial effluent COD?');
eta_agent_test_true(strpos($seqs['answer'], '150 mg/L') !== false && strpos($seqs['answer'], '400 mg/L') !== false, 'SEQS COD response uses the published table values');

$eia = eta_agent_curated_response('Do you do EIA reports for Punjab?');
eta_agent_test_true(strpos($eia['answer'], 'Punjab EPA') !== false && strpos($eia['answer'], 'Sindh') === false, 'Punjab EIA response references the correct provincial authority');

$turnaround = eta_agent_curated_response('What is your turnaround time?');
eta_agent_test_true(strpos($turnaround['answer'], 'cannot state or estimate a turnaround time') !== false, 'turnaround requests cannot reach a generative answer');

$report = eta_agent_curated_response('How do I verify a report you issued?');
eta_agent_test_true($report['citations'] === ['https://envitechal.com/report-verification-portal/'], 'report verification routes to the canonical portal');

$guarantee = eta_agent_curated_response('Can you guarantee my facility passes the EPA inspection?');
eta_agent_test_true(strpos($guarantee['answer'], 'cannot guarantee') !== false, 'EPA outcome guarantee is explicitly declined');

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
