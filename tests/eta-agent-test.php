<?php

define('ABSPATH', __DIR__);
define('MINUTE_IN_SECONDS', 60);

class WP_Error
{
    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }
}

class WP_REST_Request
{
    private $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function get_param($name)
    {
        return $this->params[$name] ?? null;
    }
}

$GLOBALS['eta_agent_test_transients'] = [];
$GLOBALS['eta_agent_test_remote_queue'] = [];
$GLOBALS['eta_agent_test_remote_calls'] = [];

function add_action()
{
    // Registration is intentionally inert in this pure helper test.
}

function get_option($name, $default = '')
{
    return $default;
}

function sanitize_key($value)
{
    return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value));
}

function sanitize_textarea_field($value)
{
    return trim(strip_tags((string) $value));
}

function get_transient($key)
{
    return $GLOBALS['eta_agent_test_transients'][$key] ?? false;
}

function set_transient($key, $value)
{
    $GLOBALS['eta_agent_test_transients'][$key] = $value;
    return true;
}

function delete_transient($key)
{
    unset($GLOBALS['eta_agent_test_transients'][$key]);
    return true;
}

function is_wp_error($value)
{
    return $value instanceof WP_Error;
}

function wp_json_encode($value, $flags = 0)
{
    return json_encode($value, $flags);
}

function wp_remote_post($url, $args)
{
    $GLOBALS['eta_agent_test_remote_calls'][] = ['url' => $url, 'args' => $args];
    return array_shift($GLOBALS['eta_agent_test_remote_queue']);
}

function wp_remote_retrieve_response_code($response)
{
    return $response['response']['code'] ?? 0;
}

function wp_remote_retrieve_body($response)
{
    return $response['body'] ?? '';
}

function rest_ensure_response($value)
{
    return $value;
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
eta_agent_test_true(strpos($services['answer'], 'exact premises, matrix, parameter, and method') !== false, 'service summary cannot generalise accreditation');

$specific_service = eta_agent_curated_response('Do you provide stack emission monitoring?');
eta_agent_test_true(strpos($specific_service['answer'], 'stack and gaseous-emission monitoring') !== false, 'specific service questions receive a focused verified answer');
eta_agent_test_true(strpos($specific_service['answer'], 'water and wastewater') === false && strpos($specific_service['answer'], 'equipment calibration') === false, 'stack-emission answer omits unrelated services');
eta_agent_test_true($specific_service['citations'] === ['https://envitechal.com/gaseous-air-emission-testing-lab-near-me/'], 'stack-emission answer cites only its service page');

$hyphenated_service = eta_agent_curated_response('Do you provide stack-emission monitoring?');
eta_agent_test_true($hyphenated_service === $specific_service, 'punctuation variants route to the same focused answer');

$ambient = eta_agent_curated_response('Do you provide ambient air monitoring?');
eta_agent_test_true(strpos($ambient['answer'], 'ambient-air monitoring') !== false && count($ambient['citations']) === 1, 'ambient-air question receives only the relevant capability');

$calibration = eta_agent_curated_response('Can you calibrate a pH meter?');
eta_agent_test_true(strpos($calibration['answer'], 'pH meters') !== false, 'calibration is not misclassified by the word fragment rate');
eta_agent_test_true(strpos($calibration['answer'], 'estimate prices') === false, 'pH-meter question does not receive pricing prose');

$arsenic = eta_agent_curated_response('Can you test drinking water for arsenic?');
eta_agent_test_true(strpos($arsenic['answer'], 'arsenic') !== false && $arsenic['citations'] === ['https://envitechal.com/drinking-water-testing-lab/'], 'drinking-water parameter question receives a focused sourced answer');
eta_agent_test_true(strpos($arsenic['answer'], 'lead') === false && strpos($arsenic['answer'], 'coliform') === false, 'arsenic question omits unrelated drinking-water parameters');

$soil = eta_agent_curated_response('Do you perform soil testing?');
eta_agent_test_true(strpos($soil['answer'], 'soil') !== false && $soil['citations'] === ['https://envitechal.com/soil-hazardous-waste-testing/'], 'soil question receives a focused sourced answer');
eta_agent_test_true(strpos($soil['answer'], 'sludge') === false && strpos($soil['answer'], 'hazardous') === false, 'soil question omits adjacent sample types');
eta_agent_test_true(eta_agent_curated_response('Can you test soil?') === $soil, 'verb-first soil wording routes to the same focused answer');

$sludge = eta_agent_curated_response('Can you test sludge?');
eta_agent_test_true(strpos($sludge['answer'], 'hazardous-waste and sludge testing') !== false, 'verb-first sludge wording receives the verified waste-testing answer');

$general_drinking_water = eta_agent_curated_response('Can you test drinking water?');
eta_agent_test_true(strpos($general_drinking_water['answer'], 'provides drinking-water testing') !== false, 'verb-first drinking-water wording receives the verified service answer');

$wastewater = eta_agent_curated_response('Can you test textile wastewater COD?');
eta_agent_test_true(strpos($wastewater['answer'], 'COD') !== false && $wastewater['citations'] === ['https://envitechal.com/wastewater-testing-services/'], 'wastewater COD question receives a focused sourced answer');

$seqs = eta_agent_curated_response('What are SEQS limits for industrial effluent COD?');
eta_agent_test_true(strpos($seqs['answer'], '150 mg/L') !== false && strpos($seqs['answer'], '400 mg/L') !== false, 'SEQS COD response uses the published table values');

$eia = eta_agent_curated_response('Do you do EIA reports for Punjab?');
eta_agent_test_true(strpos($eia['answer'], 'Punjab EPA') !== false && strpos($eia['answer'], 'Sindh') === false, 'Punjab EIA response references the correct provincial authority');

$turnaround = eta_agent_curated_response('What is your turnaround time?');
eta_agent_test_true(strpos($turnaround['answer'], 'cannot state or estimate a turnaround time') !== false, 'turnaround requests cannot reach a generative answer');

$report = eta_agent_curated_response('How do I verify a report you issued?');
eta_agent_test_true($report['citations'] === ['https://envitechal.com/report-verification-portal/'], 'report verification routes to the canonical portal');

$contact = eta_agent_curated_response('What is your email and WhatsApp number?');
eta_agent_test_true(strpos($contact['answer'], 'info@envitechal.com') !== false && strpos($contact['answer'], '+92 310 2288801') !== false, 'contact questions use only the published contact route');

$karachi_address = eta_agent_curated_response('What is your Karachi address?');
eta_agent_test_true(strpos($karachi_address['answer'], 'Karachi head office') !== false && strpos($karachi_address['answer'], 'Lahore') === false, 'city-specific address omits the other office');

$whatsapp = eta_agent_curated_response('What is your WhatsApp number?');
eta_agent_test_true(strpos($whatsapp['answer'], '+92 310 2288801') !== false && strpos($whatsapp['answer'], 'info@') === false && strpos($whatsapp['answer'], 'working hours') === false, 'WhatsApp question returns only the requested channel');

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

$safe_fallback = eta_agent_safe_fallback_response();
eta_agent_test_true(strpos($safe_fallback['answer'], 'cannot verify') !== false, 'unmatched questions decline unsupported answers');
eta_agent_test_true(
    $safe_fallback['citations'] === ['https://envitechal.com/contact-us-envi-tech-al/'],
    'the deterministic fallback supplies only the canonical contact source'
);
eta_agent_test_true(eta_agent_verified_catalogue_ready(), 'the local verified catalogue passes its readiness contract');
eta_agent_test_true(!eta_agent_remote_enabled(), 'generative answers are fail-closed by default');

$GLOBALS['eta_agent_test_remote_calls'] = [];
$verified_message = eta_agent_chat_response(new WP_REST_Request(['message' => 'How do I verify my report?']));
eta_agent_test_true($verified_message['citations'] === ['https://envitechal.com/report-verification-portal/'], 'the public message route serves verified catalogue answers without an upstream call');
$unmatched_message = eta_agent_chat_response(new WP_REST_Request(['message' => 'Tell me something completely unrelated.']));
eta_agent_test_true(strpos($unmatched_message['answer'], 'cannot verify') !== false, 'the public message route declines unmatched questions immediately');
eta_agent_test_true(count($GLOBALS['eta_agent_test_remote_calls']) === 0, 'verified and unmatched public messages make no external request while generation is disabled');

putenv('ETA_AGENT_ENDPOINT=https://example.agents.do-ai.run');
putenv('ETA_AGENT_ACCESS_KEY=server-only-test-key');
$GLOBALS['eta_agent_test_remote_calls'] = [];
$GLOBALS['eta_agent_test_remote_queue'] = [new WP_Error('http_request_failed')];
$failed = eta_agent_remote_completion([['role' => 'user', 'content' => 'test']], 5);
eta_agent_test_true(is_wp_error($failed) && $failed->code === 'eta_agent_connection', 'a connection failure returns the safe proxy error');
eta_agent_test_true(count($GLOBALS['eta_agent_test_remote_calls']) === 1, 'a connection timeout is never retried');

$circuit_result = eta_agent_remote_completion([['role' => 'user', 'content' => 'test']], 5);
eta_agent_test_true(is_wp_error($circuit_result) && $circuit_result->code === 'eta_agent_circuit_open', 'an open circuit fails immediately');
eta_agent_test_true(count($GLOBALS['eta_agent_test_remote_calls']) === 1, 'the open circuit makes no upstream request');

$GLOBALS['eta_agent_test_remote_calls'] = [];
$first_health = eta_agent_health_response();
$second_health = eta_agent_health_response();
eta_agent_test_true(
    $first_health === ['status' => 'ready', 'mode' => 'verified'] && $second_health === ['status' => 'ready', 'mode' => 'verified'],
    'healthy preflight returns the provider-independent verified state'
);
eta_agent_test_true(count($GLOBALS['eta_agent_test_remote_calls']) === 0, 'public readiness never waits for an external model or knowledge base');

putenv('ETA_AGENT_REMOTE_ENABLED=true');
eta_agent_test_true(eta_agent_remote_enabled(), 'grounded generation can be deliberately enabled server-side after validation');
eta_agent_test_true(eta_agent_health_response() === ['status' => 'ready', 'mode' => 'hybrid'], 'health reports hybrid mode when remote generation is explicitly enabled');
putenv('ETA_AGENT_REMOTE_ENABLED');

echo "Agent proxy safety tests passed.\n";
