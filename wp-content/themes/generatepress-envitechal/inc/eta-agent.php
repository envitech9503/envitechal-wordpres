<?php
/**
 * First-party AI agent proxy. Secrets remain server-side at every stage.
 */

if (!defined('ABSPATH')) {
    exit;
}

function eta_agent_config_value($constant, $environment, $option)
{
    if (defined($constant) && is_string(constant($constant))) {
        return trim(constant($constant));
    }

    $environment_value = getenv($environment);
    if (is_string($environment_value) && trim($environment_value) !== '') {
        return trim($environment_value);
    }

    $option_value = get_option($option, '');
    return is_string($option_value) ? trim($option_value) : '';
}

function eta_agent_endpoint()
{
    $endpoint = rtrim(eta_agent_config_value('ETA_AGENT_ENDPOINT', 'ETA_AGENT_ENDPOINT', 'eta_agent_endpoint'), '/');
    if ($endpoint === '' || !wp_http_validate_url($endpoint)) {
        return '';
    }

    $host = strtolower((string) wp_parse_url($endpoint, PHP_URL_HOST));
    if (!preg_match('/(?:^|\.)(?:agents\.do-ai\.run|ondigitalocean\.app)$/', $host)) {
        return '';
    }

    return $endpoint;
}

function eta_agent_access_key()
{
    return eta_agent_config_value('ETA_AGENT_ACCESS_KEY', 'ETA_AGENT_ACCESS_KEY', 'eta_agent_access_key');
}

function eta_agent_is_configured()
{
    return eta_agent_endpoint() !== '' && eta_agent_access_key() !== '';
}

function eta_agent_system_instruction()
{
    return implode(' ', [
        'You are the Envi Tech AL environmental laboratory information assistant.',
        'Use only Envi Tech AL canonical pages and the supplied knowledge base.',
        'PNAC LAB-347 applies to the Lahore premises only and only to the matrix, parameter, and method combinations in the published scope; never state or imply that the Karachi laboratory is PNAC accredited.',
        'Distinguish ISO/IEC 17025 accreditation from ISO management-system certification.',
        'Never invent or extrapolate a credential number, validity date, method reference, accredited parameter, price, package price, turnaround time, address, operating hour, regulatory outcome, or guarantee.',
        'Do not provide any price. For pricing, uncertain scope, turnaround, or facts not supported by a canonical source, direct the user to info@envitechal.com or https://wa.me/923102288801.',
        'Do not guarantee that a facility will pass an EPA inspection or receive an approval.',
        'Every substantive answer must cite the exact https://envitechal.com/ canonical page URL used.',
        'For accreditation-scope questions, defer to https://www.pnac.gov.pk/index.php/pdfFiles/LAB-347 and state that the published scope controls.',
        'For report verification, direct users to https://envitechal.com/report-verification-portal/.',
    ]);
}

function eta_agent_remote_completion($messages, $timeout = 20)
{
    if (!eta_agent_is_configured()) {
        return new WP_Error('eta_agent_unavailable', 'The AI assistant is not configured.', ['status' => 503]);
    }

    $response = wp_remote_post(eta_agent_endpoint() . '/api/v1/chat/completions', [
        'timeout' => $timeout,
        'redirection' => 0,
        'headers' => [
            'Authorization' => 'Bearer ' . eta_agent_access_key(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'body' => wp_json_encode([
            'messages' => $messages,
            'stream' => false,
            'include_retrieval_info' => true,
            'include_guardrails_info' => true,
            'include_functions_info' => false,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('eta_agent_connection', 'The AI assistant could not be reached.', ['status' => 503]);
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    $data = json_decode((string) wp_remote_retrieve_body($response), true);
    if ($status < 200 || $status >= 300 || !is_array($data)) {
        return new WP_Error('eta_agent_upstream', 'The AI assistant is temporarily unavailable.', ['status' => 503]);
    }

    return $data;
}

function eta_agent_collect_citations($value, &$citations)
{
    if (is_array($value)) {
        foreach ($value as $item) {
            eta_agent_collect_citations($item, $citations);
        }
        return;
    }
    if (!is_string($value)) {
        return;
    }

    preg_match_all('#https://envitechal\.com/[^\s<>"\']*#i', $value, $matches);
    foreach ($matches[0] ?? [] as $url) {
        $url = rtrim($url, '.,;:)\']');
        if (wp_http_validate_url($url)) {
            $citations[$url] = $url;
        }
    }
}

function eta_agent_health_response()
{
    if (!eta_agent_is_configured()) {
        return new WP_Error('eta_agent_unavailable', 'AI assistant unavailable; WhatsApp support remains available.', ['status' => 503]);
    }

    $result = eta_agent_remote_completion([
        ['role' => 'system', 'content' => eta_agent_system_instruction()],
        ['role' => 'user', 'content' => 'Health check only. Reply with READY.'],
    ], 12);
    if (is_wp_error($result)) {
        return $result;
    }

    return rest_ensure_response(['status' => 'ready']);
}

function eta_agent_chat_response(WP_REST_Request $request)
{
    $message = sanitize_textarea_field((string) $request->get_param('message'));
    if ($message === '') {
        return new WP_Error('eta_agent_empty_message', 'Please enter a question.', ['status' => 400]);
    }
    if (function_exists('mb_strlen') ? mb_strlen($message) > 1200 : strlen($message) > 1200) {
        return new WP_Error('eta_agent_long_message', 'Please shorten the question to 1,200 characters.', ['status' => 400]);
    }

    $messages = [['role' => 'system', 'content' => eta_agent_system_instruction()]];
    $history = $request->get_param('history');
    if (is_array($history)) {
        foreach (array_slice($history, -8) as $entry) {
            if (!is_array($entry) || !in_array($entry['role'] ?? '', ['user', 'assistant'], true)) {
                continue;
            }
            $content = sanitize_textarea_field((string) ($entry['content'] ?? ''));
            if ($content !== '') {
                $messages[] = ['role' => $entry['role'], 'content' => $content];
            }
        }
    }
    $messages[] = ['role' => 'user', 'content' => $message];

    $result = eta_agent_remote_completion($messages, 25);
    if (is_wp_error($result)) {
        return $result;
    }

    $answer = $result['choices'][0]['message']['content'] ?? '';
    if (!is_string($answer) || trim($answer) === '') {
        return new WP_Error('eta_agent_empty_response', 'The AI assistant returned no answer.', ['status' => 503]);
    }

    $citations = [];
    eta_agent_collect_citations($result, $citations);
    return rest_ensure_response([
        'answer' => trim($answer),
        'citations' => array_values($citations),
    ]);
}

add_action('rest_api_init', function () {
    register_rest_route('eta/v1', '/agent/health', [
        'methods' => 'GET',
        'callback' => 'eta_agent_health_response',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('eta/v1', '/agent/message', [
        'methods' => 'POST',
        'callback' => 'eta_agent_chat_response',
        'permission_callback' => '__return_true',
    ]);
});

