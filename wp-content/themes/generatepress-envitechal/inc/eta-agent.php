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

function eta_agent_uuid()
{
    $uuid = strtolower(eta_agent_config_value('ETA_AGENT_UUID', 'ETA_AGENT_UUID', 'eta_agent_uuid'));
    return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-5][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/', $uuid) ? $uuid : '';
}

function eta_agent_chatbot_key()
{
    return eta_agent_config_value('ETA_AGENT_CHATBOT_KEY', 'ETA_AGENT_CHATBOT_KEY', 'eta_agent_chatbot_key');
}

function eta_agent_is_configured()
{
    if (eta_agent_endpoint() === '') {
        return false;
    }

    return eta_agent_access_key() !== '' || (eta_agent_uuid() !== '' && eta_agent_chatbot_key() !== '');
}

/**
 * Generative answers are an optional enhancement, never an availability
 * dependency. They remain off until an operator has validated grounded
 * retrieval and deliberately enables them server-side.
 */
function eta_agent_remote_enabled()
{
    $value = strtolower(eta_agent_config_value('ETA_AGENT_REMOTE_ENABLED', 'ETA_AGENT_REMOTE_ENABLED', 'eta_agent_remote_enabled'));
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

function eta_agent_runtime_cache_key($suffix)
{
    $identity = eta_agent_endpoint() . '|' . eta_agent_uuid();
    return 'eta_agent_' . sanitize_key($suffix) . '_' . substr(hash('sha256', $identity), 0, 12);
}

function eta_agent_runtime_unavailable()
{
    return (bool) get_transient(eta_agent_runtime_cache_key('circuit_open'));
}

function eta_agent_open_runtime_circuit()
{
    delete_transient(eta_agent_runtime_cache_key('health_ready'));
    set_transient(eta_agent_runtime_cache_key('circuit_open'), 1, 5 * MINUTE_IN_SECONDS);
}

function eta_agent_close_runtime_circuit()
{
    delete_transient(eta_agent_runtime_cache_key('circuit_open'));
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

function eta_agent_policy_prompt($message)
{
    return eta_agent_system_instruction() . ' Visitor question: ' . trim((string) $message);
}

function eta_agent_bearer_token($force_refresh = false)
{
    $direct_key = eta_agent_access_key();
    if ($direct_key !== '') {
        return $direct_key;
    }

    if (eta_agent_uuid() === '' || eta_agent_chatbot_key() === '') {
        return new WP_Error('eta_agent_unavailable', 'The AI assistant is not configured.', ['status' => 503]);
    }

    $transient_key = 'eta_agent_widget_bearer_' . substr(hash('sha256', eta_agent_uuid()), 0, 12);
    if (!$force_refresh) {
        $cached = get_transient($transient_key);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }
    }

    delete_transient($transient_key);
    $response = wp_remote_post(
        'https://cloud.digitalocean.com/gen-ai/auth/agents/' . rawurlencode(eta_agent_uuid()) . '/token',
        [
            'timeout' => 12,
            'redirection' => 0,
            'headers' => [
                'X-Api-Key' => eta_agent_chatbot_key(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => '{}',
        ]
    );

    if (is_wp_error($response)) {
        return new WP_Error('eta_agent_auth_connection', 'The AI assistant could not be authenticated.', ['status' => 503]);
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    $data = json_decode((string) wp_remote_retrieve_body($response), true);
    $token = is_array($data) && is_string($data['access_token'] ?? null) ? trim($data['access_token']) : '';
    if ($status < 200 || $status >= 300 || $token === '') {
        return new WP_Error('eta_agent_auth_upstream', 'The AI assistant could not be authenticated.', ['status' => 503]);
    }

    set_transient($transient_key, $token, 240);
    return $token;
}

function eta_agent_remote_completion($messages, $timeout = 20)
{
    if (!eta_agent_is_configured()) {
        return new WP_Error('eta_agent_unavailable', 'The AI assistant is not configured.', ['status' => 503]);
    }

    if (eta_agent_runtime_unavailable()) {
        return new WP_Error('eta_agent_circuit_open', 'The AI assistant is temporarily unavailable.', ['status' => 503]);
    }

    $messages = array_values(array_filter($messages, function ($entry) {
        return is_array($entry) && in_array($entry['role'] ?? '', ['user', 'assistant'], true);
    }));
    for ($attempt = 0; $attempt < 2; $attempt++) {
        $token = eta_agent_bearer_token($attempt > 0);
        if (is_wp_error($token)) {
            eta_agent_open_runtime_circuit();
            return $token;
        }

        $response = wp_remote_post(eta_agent_endpoint() . '/api/v1/chat/completions', [
            'timeout' => $timeout,
            'redirection' => 0,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
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
            eta_agent_open_runtime_circuit();
            return new WP_Error('eta_agent_connection', 'The AI assistant could not be reached.', ['status' => 503]);
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $data = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($status >= 200 && $status < 300 && is_array($data)) {
            eta_agent_close_runtime_circuit();
            return $data;
        }

        if (in_array($status, [401, 403], true) && $attempt === 0 && eta_agent_access_key() === '') {
            continue;
        }

        if ($status === 429 || $status >= 500) {
            eta_agent_open_runtime_circuit();
        }
        return new WP_Error('eta_agent_upstream', 'The AI assistant is temporarily unavailable.', ['status' => 503]);
    }

    eta_agent_open_runtime_circuit();
    return new WP_Error('eta_agent_upstream', 'The AI assistant is temporarily unavailable.', ['status' => 503]);
}

function eta_agent_curated_response($message)
{
    $normalise = static function ($value) {
        $value = function_exists('mb_strtolower') ? mb_strtolower((string) $value) : strtolower((string) $value);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
        return trim(preg_replace('/\s+/u', ' ', (string) $value));
    };
    $question = $normalise($message);
    $has = static function ($terms) use ($question, $normalise) {
        foreach ((array) $terms as $term) {
            $needle = $normalise($term);
            if ($needle !== '' && strpos(' ' . $question . ' ', ' ' . $needle . ' ') !== false) {
                return true;
            }
        }
        return false;
    };

    if ($has('karachi') && $has(['pnac', 'accredit', 'iso 17025', 'iso/iec 17025'])) {
        return [
            'answer' => 'No. PNAC LAB-347 applies to Envi Tech AL\'s Lahore premises only, and only to the matrix, parameter, and method combinations listed in the published scope. It must not be applied to the Karachi laboratory. Verify the current scope at https://www.pnac.gov.pk/index.php/pdfFiles/LAB-347 and see https://envitechal.com/accreditations-certifications/.',
            'citations' => ['https://envitechal.com/accreditations-certifications/'],
        ];
    }

    if ($has('pnac') && $has(['number', 'identifier', 'valid', 'expiry', 'expire', 'accreditation'])) {
        return [
            'answer' => 'The PNAC accreditation identifier is LAB-347. It applies to the Lahore premises only and only to the matrix, parameter, and method combinations in the published scope. The PNAC document states validity through 21-09-2028, subject to surveillance and current status. Verify it at https://www.pnac.gov.pk/index.php/pdfFiles/LAB-347 and see https://envitechal.com/accreditations-certifications/.',
            'citations' => ['https://envitechal.com/accreditations-certifications/'],
        ];
    }

    if ($has(['price', 'pricing', 'cost', 'rate', 'fee', 'quotation', 'quote'])) {
        return [
            'answer' => 'I cannot provide or estimate prices. The team must confirm the matrix, parameters, sampling, method, and reporting purpose before issuing a quotation. Contact info@envitechal.com or WhatsApp +92 310 2288801.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['stack emission', 'stack emissions', 'gaseous emission', 'gaseous emissions', 'chimney emission', 'chimney emissions'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides stack and gaseous-emission monitoring for boilers, generators, chimneys, and process exhausts. The exact parameters, locations, operating conditions, and reporting purpose must be confirmed before fieldwork.',
            'citations' => ['https://envitechal.com/gaseous-air-emission-testing-lab-near-me/'],
        ];
    }

    if ($has(['ambient air', 'air quality monitoring'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides ambient-air monitoring for industrial sites, construction projects, and other compliance-sensitive facilities. The monitoring plan and required indicators are confirmed for each site.',
            'citations' => ['https://envitechal.com/ambient-air-monitoring-services/'],
        ];
    }

    if ($has(['noise monitoring', 'noise survey', 'dosimetry', 'noise dosimetry'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides boundary-noise, workplace-noise, area-monitoring, and personal-exposure dosimetry services. The activity, locations, duration, and reporting purpose must be scoped before monitoring.',
            'citations' => ['https://envitechal.com/noise-monitoring-dosimetry/'],
        ];
    }

    if ($has(['calibrate', 'calibration', 'equipment calibration'])) {
        if ($has(['ph meter', 'ph meters'])) {
            return [
                'answer' => 'Yes. Envi Tech AL lists pH meters among the common instrument categories supported by its equipment-calibration service. Share the instrument range, location, acceptance criteria, and certificate requirement before booking.',
                'citations' => ['https://envitechal.com/services/equipment-calibration-services/'],
            ];
        }
        return [
            'answer' => 'Envi Tech AL provides equipment-calibration services. Share the instrument type, range, location, acceptance criteria, and certificate requirement so the team can confirm the exact capability.',
            'citations' => ['https://envitechal.com/services/equipment-calibration-services/'],
        ];
    }

    if ($has(['soil testing', 'soil test', 'test soil', 'testing soil'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides soil testing. Share the site context, target analytes, and intended use of the report so the correct sampling and testing scope can be confirmed.',
            'citations' => ['https://envitechal.com/soil-hazardous-waste-testing/'],
        ];
    }

    if ($has(['sludge testing', 'sludge test', 'test sludge'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides sludge testing support. Share the sludge type, target analytes, and intended use of the report so the exact scope can be confirmed.',
            'citations' => ['https://envitechal.com/soil-hazardous-waste-testing/'],
        ];
    }

    if ($has(['hazardous waste testing', 'hazardous waste test', 'test hazardous waste'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides hazardous-waste testing support. Share the waste type, target analytes, and intended use of the report so the exact scope can be confirmed.',
            'citations' => ['https://envitechal.com/soil-hazardous-waste-testing/'],
        ];
    }

    if ($has(['drinking water', 'potable water'])) {
        $drinking_water_parameters = [
            'arsenic' => 'arsenic',
            'lead' => 'lead',
            'iron' => 'iron',
            'chromium' => 'chromium',
            'total coliform' => 'total coliform',
            'e coli' => 'E. coli',
        ];
        foreach ($drinking_water_parameters as $term => $label) {
            if ($has($term)) {
                return [
                    'answer' => sprintf('Yes. Envi Tech AL lists %s as an available drinking-water testing parameter. Confirm the water source and reporting purpose so the correct sampling and method requirements are used.', $label),
                    'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
                ];
            }
        }
        if ($has('bacteria')) {
            return [
                'answer' => 'Yes. Drinking-water testing can include microbiological indicators. Confirm the water source and reporting purpose so the exact organisms, sampling, and method requirements can be selected.',
                'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
            ];
        }
        if ($has(['test', 'testing'])) {
            return [
                'answer' => 'Yes. Envi Tech AL provides drinking-water testing. Share the water source and reporting purpose so the correct parameters, sampling, and method requirements can be selected.',
                'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
            ];
        }
    }

    if ($has(['wastewater', 'waste water', 'effluent', 'etp']) && $has(['test', 'testing', 'cod', 'bod', 'tss', 'tds']) && !$has('seqs')) {
        return [
            'answer' => 'Yes. Envi Tech AL tests industrial wastewater and ETP samples; COD is among the published parameters. The final parameter list and comparison standard depend on the discharge route and report purpose.',
            'citations' => ['https://envitechal.com/wastewater-testing-services/'],
        ];
    }

    if ($has(['water testing', 'wastewater testing', 'drinking water testing'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides drinking-water, groundwater, process-water, and wastewater testing. The correct parameters and reporting basis are selected from the water source and intended use.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/'],
        ];
    }

    if ($has(['what services', 'services do you provide', 'services does envi tech al provide', 'services do you offer', 'list your services'])) {
        return [
            'answer' => 'Envi Tech AL provides water and wastewater testing, analytical laboratory services, equipment calibration, air-emissions and noise monitoring, and environmental consultancy including EIA, EMP, and EMR support. Accreditation applies only where the exact premises, matrix, parameter, and method appear in the published scope.',
            'citations' => ['https://envitechal.com/services/'],
        ];
    }

    if ($has(['hexavalent chromium', 'chromium vi', 'cr(vi)', 'cr vi']) && $has(['scope', 'accredit'])) {
        return [
            'answer' => 'I cannot assert that hexavalent chromium is within the accredited scope. PNAC LAB-347 applies only to the Lahore premises and only to the exact matrix, parameter, and method combinations in the published scope. Check the controlling document at https://www.pnac.gov.pk/index.php/pdfFiles/LAB-347, then confirm the requested matrix and method with the laboratory through https://envitechal.com/contact-us-envi-tech-al/.',
            'citations' => ['https://envitechal.com/accreditations-certifications/', 'https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['turnaround', 'how long', 'completion time', 'reporting time'])) {
        return [
            'answer' => 'I cannot state or estimate a turnaround time. Timing depends on the matrix, parameters, methods, sampling, and reporting requirements. Contact info@envitechal.com or WhatsApp +92 310 2288801 for a requirement-specific schedule.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has('seqs') && $has(['cod', 'chemical oxygen demand'])) {
        return [
            'answer' => 'The SEQS 2016 table lists industrial-effluent COD limits of 150 mg/L for discharge into inland waters, 400 mg/L for discharge into sewage treatment, and 400 mg/L for discharge into the sea. The official SEQS notification and the facility\'s applicable approval conditions control, so verify the current binding text before relying on these figures. See https://envitechal.com/sindh-environmental-quality-standards-seqs/.',
            'citations' => ['https://envitechal.com/sindh-environmental-quality-standards-seqs/'],
        ];
    }

    if ($has(['eia', 'environmental impact assessment']) && $has(['punjab', 'punjab epa'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides environmental consultancy support for EIA work on Punjab projects. Punjab EPA is the relevant provincial authority, and the required study category, submission route, and approval depend on the project and current rules; laboratory accreditation must not be treated as accreditation of consultancy work. See https://envitechal.com/services/environmental-consultancy/ and contact the team through https://envitechal.com/contact-us-envi-tech-al/.',
            'citations' => ['https://envitechal.com/services/environmental-consultancy/', 'https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has('karachi') && $has(['office', 'address', 'where are you', 'location'])) {
        return [
            'answer' => 'Karachi head office: First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS, Karachi, Sindh 75900, Pakistan.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has('lahore') && $has(['office', 'address', 'where are you', 'location'])) {
        return [
            'answer' => 'Lahore regional office: 87-E Madina Heights, Office A/30-31, 8th Floor, Maulana Shaukat Ali Road, Johar Town, Lahore, Punjab, Pakistan.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['office', 'offices', 'address', 'addresses', 'where are you', 'location', 'locations'])) {
        return [
            'answer' => 'Envi Tech AL has published offices in Karachi and Lahore. Karachi: First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS. Lahore: 87-E Madina Heights, Office A/30-31, 8th Floor, Maulana Shaukat Ali Road, Johar Town.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['whatsapp', 'phone', 'telephone']) && !$has('email')) {
        return [
            'answer' => 'Envi Tech AL\'s published WhatsApp number is +92 310 2288801.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has('email') && !$has(['whatsapp', 'phone', 'telephone'])) {
        return [
            'answer' => 'Envi Tech AL\'s published email address is info@envitechal.com.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['working hour', 'working hours', 'opening hour', 'opening hours'])) {
        return [
            'answer' => 'Current working hours are not confirmed in the published source. Please verify them on WhatsApp at +92 310 2288801 before visiting.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['contact', 'email', 'phone', 'telephone', 'whatsapp'])) {
        return [
            'answer' => 'Contact Envi Tech AL at info@envitechal.com or on WhatsApp at +92 310 2288801.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['iso 17025', 'iso/iec 17025']) && $has(['certified', 'certification', 'accredited', 'accreditation'])) {
        return [
            'answer' => 'ISO/IEC 17025 is laboratory accreditation, not management-system certification. Envi Tech AL\'s PNAC LAB-347 accreditation applies to the Lahore premises only and only to the matrix, parameter, and method combinations in its published scope; it does not apply to the Karachi laboratory. Verify the scope at https://www.pnac.gov.pk/index.php/pdfFiles/LAB-347 and see https://envitechal.com/accreditations-certifications/.',
            'citations' => ['https://envitechal.com/accreditations-certifications/'],
        ];
    }

    if ($has('report') && $has(['verify', 'verification', 'authenticate', 'authentic'])) {
        return [
            'answer' => 'Use Envi Tech AL\'s Report Verification Portal and enter the verification details printed on the report. If they do not validate, contact info@envitechal.com before relying on the document.',
            'citations' => ['https://envitechal.com/report-verification-portal/'],
        ];
    }

    if ($has(['guarantee', 'guaranteed', 'ensure i pass', 'ensure we pass']) && $has(['epa', 'inspection', 'approval', 'pass'])) {
        return [
            'answer' => 'No. Envi Tech AL cannot guarantee that a facility will pass an EPA inspection or receive regulatory approval. The authority decides the outcome based on applicable law, permit conditions, evidence, and inspection findings. Envi Tech AL can provide testing and consultancy support without promising a regulatory result; see https://envitechal.com/services/environmental-consultancy/.',
            'citations' => ['https://envitechal.com/services/environmental-consultancy/'],
        ];
    }

    return null;
}

/**
 * A deterministic, source-linked response for questions outside the verified
 * catalogue. This prevents an upstream outage from becoming a slow or
 * speculative brand experience.
 */
function eta_agent_safe_fallback_response()
{
    return [
        'answer' => 'I cannot verify that from Envi Tech AL\'s published sources. Please send the exact requirement to info@envitechal.com or WhatsApp +92 310 2288801.',
        'citations' => [
            'https://envitechal.com/contact-us-envi-tech-al/',
        ],
    ];
}

function eta_agent_verified_catalogue_ready()
{
    $probe = eta_agent_curated_response('How do I verify a report you issued?');
    return is_array($probe)
        && is_string($probe['answer'] ?? null)
        && strpos($probe['answer'], 'Report Verification Portal') !== false
        && ($probe['citations'] ?? null) === ['https://envitechal.com/report-verification-portal/'];
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
    if (!eta_agent_verified_catalogue_ready()) {
        return new WP_Error('eta_agent_catalogue_unavailable', 'The verified information assistant is unavailable.', ['status' => 503]);
    }

    return rest_ensure_response([
        'status' => 'ready',
        'mode' => eta_agent_remote_enabled() ? 'hybrid' : 'verified',
    ]);
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

    $curated = eta_agent_curated_response($message);
    if (is_array($curated)) {
        return rest_ensure_response($curated);
    }

    if (!eta_agent_remote_enabled()) {
        return rest_ensure_response(eta_agent_safe_fallback_response());
    }

    $messages = [];
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
    $messages[] = ['role' => 'user', 'content' => eta_agent_policy_prompt($message)];

    $result = eta_agent_remote_completion($messages, 15);
    if (is_wp_error($result)) {
        return rest_ensure_response(eta_agent_safe_fallback_response());
    }

    $answer = $result['choices'][0]['message']['content'] ?? '';
    if (!is_string($answer) || trim($answer) === '') {
        return rest_ensure_response(eta_agent_safe_fallback_response());
    }

    $citations = [];
    eta_agent_collect_citations($answer, $citations);
    if (!$citations) {
        return rest_ensure_response(eta_agent_safe_fallback_response());
    }
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
