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

function eta_agent_curated_response($message, $context = '')
{
    $normalise = static function ($value) {
        $value = function_exists('mb_strtolower') ? mb_strtolower((string) $value) : strtolower((string) $value);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
        return trim(preg_replace('/\s+/u', ' ', (string) $value));
    };
    $question = $normalise($message);
    $topic_context = trim($question . ' ' . $normalise($context));
    $has = static function ($terms) use ($question, $normalise) {
        foreach ((array) $terms as $term) {
            $needle = $normalise($term);
            if ($needle !== '' && strpos(' ' . $question . ' ', ' ' . $needle . ' ') !== false) {
                return true;
            }
        }
        return false;
    };
    $topic_has = static function ($terms) use ($topic_context, $normalise) {
        foreach ((array) $terms as $term) {
            $needle = $normalise($term);
            if ($needle !== '' && strpos(' ' . $topic_context . ' ', ' ' . $needle . ' ') !== false) {
                return true;
            }
        }
        return false;
    };
    $asks_parameters = $has([
        'parameter',
        'parameters',
        'parametr',
        'parametrs',
        'paramter',
        'paramters',
        'which test',
        'which tests',
        'what tests',
        'what to test',
        'what should i test',
        'what should we test',
        'what should be tested',
        'what should be checked',
        'what should be measured',
        'what should be analysed',
        'what should be analyzed',
        'what do you test',
        'what can you test',
        'what do you check',
        'what is checked',
        'what do you measure',
        'what is measured',
        'what do you analyse',
        'what do you analyze',
        'which indicators',
        'what pollutants',
        'which pollutants',
        'what analytes',
        'which analytes',
        'what equipment',
        'which equipment',
        'what instruments',
        'which instruments',
        'tests required',
        'test required',
        'test panel',
        'testing panel',
    ]);
    $asks_sampling = $has([
        'sample',
        'sampling',
        'collect',
        'collection',
        'bottle',
        'container',
        'preserve',
        'preservation',
        'holding time',
        'sample it',
        'sample this',
    ]);
    $asks_standard = $has([
        'standard',
        'standards',
        'guideline',
        'guidelines',
        'limit',
        'limits',
        'compliance',
        'comparison basis',
    ]);
    $contextual_follow_up = $asks_parameters
        || $asks_sampling
        || $asks_standard
        || $has([
            'what about',
            'and arsenic',
            'and lead',
            'and mercury',
            'and bacteria',
            'bacteria',
            'e coli',
            'coliform',
            'arsenic',
            'lead',
            'mercury',
            'chromium',
            'cod',
            'bod',
            'tss',
            'tds',
        ]);

    if (in_array($question, ['hi', 'hello', 'hey', 'salam', 'assalam o alaikum', 'assalamualaikum', 'adaab', 'good morning', 'good afternoon', 'good evening'], true)) {
        return [
            'answer' => 'Hello. Ask a specific question about testing parameters, monitoring, calibration, consultancy, locations, accreditation, or report verification.',
            'citations' => ['https://envitechal.com/services/'],
        ];
    }

    $urdu_water = $has(['پانی', 'پانی کا ٹیسٹ', 'پانی کی جانچ', 'واٹر ٹیسٹ']);
    if ($urdu_water) {
        if ($has(['قیمت', 'ریٹ', 'کتنے پیسے', 'خرچہ'])) {
            return [
                'answer' => 'میں قیمت کا اندازہ نہیں دے سکتا۔ درست کوٹیشن کے لیے پانی کا ذریعہ، مطلوبہ ٹیسٹ، سیمپلنگ اور رپورٹ کا مقصد بتانا ضروری ہے۔ براہ کرم info@envitechal.com یا WhatsApp +92 310 2288801 پر رابطہ کریں۔',
                'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
            ];
        }
        if ($has(['کون سے ٹیسٹ', 'کیا ٹیسٹ', 'پیرامیٹر', 'جراثیم'])) {
            return [
                'answer' => 'پینے کے پانی کے عام ٹیسٹوں میں pH، رنگ، turbidity، TDS، hardness، chloride، sulfate، nitrate، fluoride، iron، manganese، lead، arsenic، residual chlorine، total coliform اور E. coli شامل ہو سکتے ہیں۔ حتمی فہرست پانی کے ماخذ اور رپورٹ کے مقصد پر منحصر ہے۔',
                'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
            ];
        }
        return [
            'answer' => 'جی۔ Envi Tech AL گھروں، پینے کے پانی، بور، زیرِ زمین پانی اور RO پانی کی جانچ فراہم کرتا ہے۔ درست ٹیسٹ اور سیمپلنگ کے لیے پانی کا ماخذ، شہر اور رپورٹ کا مقصد بتائیں۔',
            'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
        ];
    }

    $roman_urdu_water = $has([
        'pani test',
        'paani test',
        'pani check',
        'paani check',
        'pani ka test',
        'paani ka test',
        'pani ki testing',
        'paani ki testing',
        'pani ke kon se test',
        'paani ke kon se test',
        'pani ke kaun se test',
        'paani ke kaun se test',
        'pani mein kya test',
        'paani mein kya test',
        'water test karwana',
    ]);
    if ($roman_urdu_water) {
        if ($has(['kitne paise', 'kitna kharcha', 'rate kya', 'price kya'])) {
            return [
                'answer' => 'Main price estimate nahin de sakta. Sahi quotation ke liye pani ka source, required tests, sampling aur report ka purpose confirm karna zaroori hai. info@envitechal.com ya WhatsApp +92 310 2288801 par requirement bhej dein.',
                'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
            ];
        }
        if ($has(['kon se test', 'kaun se test', 'kya test', 'parameters', 'bacteria', 'jaraseem'])) {
            return [
                'answer' => 'Drinking water ke practical panel mein pH, color, turbidity, TDS, hardness, chloride, sulfate, nitrate, fluoride, iron, manganese, lead, arsenic, residual chlorine, total coliform aur E. coli shamil ho sakte hain. Final panel pani ke source aur report ke purpose par depend karta hai.',
                'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
            ];
        }
        return [
            'answer' => 'Ji. Envi Tech AL ghar ke drinking water, bore water, groundwater aur RO water ki testing provide karta hai. Sahi tests aur sampling ke liye pani ka source, city aur report ka purpose bata dein.',
            'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
        ];
    }

    if ($has(['who are you', 'what are you'])) {
        return [
            'answer' => 'I am Envi Tech AL\'s source-checked information assistant. I answer from the company\'s published service, location, accreditation, compliance, and report-verification information.',
            'citations' => ['https://envitechal.com/aboutus/'],
        ];
    }

    if ($has('karachi') && $has(['pnac', 'accredit', 'iso 17025', 'iso/iec 17025'])) {
        return [
            'answer' => 'No. PNAC LAB-347 applies to Envi Tech AL\'s Lahore premises only, and only to the matrix, parameter, and method combinations listed in the published scope. It must not be applied to the Karachi laboratory. Verify the current published scope before relying on an accreditation claim.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/', 'https://envitechal.com/accreditations-certifications/'],
        ];
    }

    if ($has('lahore') && $has(['accredit', 'accredited', 'accreditation']) && $has([
        'arsenic',
        'lead',
        'mercury',
        'chromium',
        'cadmium',
        'coliform',
        'e coli',
        'water',
        'wastewater',
        'parameter',
        'method',
    ])) {
        return [
            'answer' => 'Do not infer accreditation from the Lahore location alone. PNAC LAB-347 applies only to the exact water or wastewater parameter and method combinations in its published scope. I cannot confirm the requested item as accredited unless that exact combination appears in the controlling document.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/', 'https://envitechal.com/accreditations-certifications/'],
        ];
    }

    if ($has('pnac') && $has(['number', 'identifier', 'valid', 'expiry', 'expire', 'accreditation'])) {
        return [
            'answer' => 'The PNAC accreditation identifier is LAB-347. It applies to the Lahore premises only and only to the matrix, parameter, and method combinations in the published scope. The published document states validity through 21-09-2028, subject to surveillance and current status.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/', 'https://envitechal.com/accreditations-certifications/'],
        ];
    }

    if ($has(['hexavalent chromium', 'chromium vi', 'cr(vi)', 'cr vi']) && $has(['scope', 'accredit', 'accredited', 'accreditation'])) {
        return [
            'answer' => 'I cannot assert that hexavalent chromium is within the accredited scope. PNAC LAB-347 applies only to the Lahore premises and only to exact matrix, parameter, and method combinations in the published scope. Confirm the requested matrix and method with the laboratory.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/', 'https://envitechal.com/accreditations-certifications/'],
        ];
    }

    if ($has(['are you iso certified', 'iso certification status', 'which iso certification', 'which iso certifications'])) {
        return [
            'answer' => 'Envi Tech AL publishes ISO 9001:2015 and ISO 14001:2015 management-system credential categories. ISO/IEC 17025 is laboratory accreditation, not certification. Verify the current certificate copy and, for laboratory work, the exact premises, matrix, parameter, and method scope.',
            'citations' => ['https://envitechal.com/accreditations-certifications/'],
        ];
    }

    if ($has(['price', 'pricing', 'cost', 'rate', 'fee', 'quotation', 'quote'])) {
        return [
            'answer' => 'I cannot provide or estimate prices. The team must confirm the matrix, parameters, sampling, method, and reporting purpose before issuing a quotation. Contact info@envitechal.com or WhatsApp +92 310 2288801.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['turnaround', 'how long', 'completion time', 'reporting time', 'when will', 'how many days'])) {
        return [
            'answer' => 'I cannot state or estimate a turnaround time. Timing depends on the matrix, parameters, methods, sampling, and reporting requirements. Contact info@envitechal.com or WhatsApp +92 310 2288801 for a requirement-specific schedule.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['tomorrow', 'urgent visit', 'urgent field visit', 'send a technician', 'technician availability', 'site visit availability'])) {
        return [
            'answer' => 'I cannot confirm technician or field-team availability from the published information. Share the city, site, service, required date, and reporting deadline with info@envitechal.com or WhatsApp +92 310 2288801 for scheduling confirmation.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    $stack_terms = [
        'stack emission',
        'stack emissions',
        'gaseous emission',
        'gaseous emissions',
        'chimney emission',
        'chimney emissions',
        'chimney gas',
        'stack gas',
        'generator exhaust',
        'boiler exhaust',
        'process exhaust',
    ];
    if ($has($stack_terms) || ($contextual_follow_up && $topic_has($stack_terms))) {
        if ($asks_parameters) {
            return [
                'answer' => 'Stack-emission scopes are source-specific. The published service covers gases, particulates, and combustion-related parameters; the exact panel depends on whether the source is a boiler, generator, chimney, or process stack and on its operating conditions.',
                'citations' => ['https://envitechal.com/gaseous-air-emission-testing-lab-near-me/'],
            ];
        }
        return [
            'answer' => 'Yes. Envi Tech AL provides stack and gaseous-emission monitoring for boilers, generators, chimneys, and process exhausts. The exact parameters, locations, operating conditions, and reporting purpose must be confirmed before fieldwork.',
            'citations' => ['https://envitechal.com/gaseous-air-emission-testing-lab-near-me/'],
        ];
    }

    $ambient_terms = ['ambient air', 'air quality monitoring', 'ambient survey', 'pm2 5', 'pm10', 'particulate matter'];
    if ($has($ambient_terms) || ($contextual_follow_up && $topic_has($ambient_terms))) {
        $ambient_indicators = [
            'pm2 5' => 'PM2.5',
            'pm10' => 'PM10',
            'so2' => 'SO2',
            'nox' => 'NOx',
            'co2' => 'CO2',
            'ozone' => 'ozone',
        ];
        $requested_ambient = [];
        foreach ($ambient_indicators as $term => $label) {
            if ($has($term)) {
                $requested_ambient[$label] = $label;
            }
        }
        if ($requested_ambient && !$asks_parameters) {
            $labels = array_values($requested_ambient);
            $listed = count($labels) === 1 ? $labels[0] : implode(', ', array_slice($labels, 0, -1)) . ' and ' . end($labels);
            return [
                'answer' => sprintf('Yes. %s %s listed among Envi Tech AL\'s published ambient-air indicators. Share the site, monitoring purpose, duration, and reporting requirement so the exact plan can be confirmed.', $listed, count($labels) === 1 ? 'is' : 'are'),
                'citations' => ['https://envitechal.com/ambient-air-monitoring-services/'],
            ];
        }
        if ($asks_parameters) {
            return [
                'answer' => 'Published ambient-air indicators include PM10, PM2.5, SO2, NOx, CO, CO2, and ozone where required, together with meteorological and site-condition notes. The final panel depends on the site and reporting purpose.',
                'citations' => ['https://envitechal.com/ambient-air-monitoring-services/'],
            ];
        }
        return [
            'answer' => 'Yes. Envi Tech AL provides ambient-air monitoring for industrial sites, construction projects, and other compliance-sensitive facilities. The monitoring plan and required indicators are confirmed for each site.',
            'citations' => ['https://envitechal.com/ambient-air-monitoring-services/'],
        ];
    }

    $noise_terms = ['noise monitoring', 'noise survey', 'dosimetry', 'noise dosimetry', 'boundary noise', 'workplace noise', 'factory noise', 'construction noise', 'measure noise', 'monitor noise', 'noise at our'];
    if ($has($noise_terms) || ($contextual_follow_up && $topic_has($noise_terms))) {
        if ($has(['boundary noise', 'factory boundary', 'site boundary'])) {
            return [
                'answer' => 'Yes. Boundary-noise monitoring is a published Envi Tech AL service. Share the site, boundary locations, operating activities, monitoring duration, and reporting purpose so the field plan can be confirmed.',
                'citations' => ['https://envitechal.com/noise-monitoring-dosimetry/'],
            ];
        }
        if ($asks_parameters) {
            return [
                'answer' => 'Noise monitoring can include Leq, Lmax, Lmin, personal dose where applicable, time-weighted exposure, monitoring-location notes, and activity conditions. The exact set depends on workplace, boundary, or project-monitoring needs.',
                'citations' => ['https://envitechal.com/noise-monitoring-dosimetry/'],
            ];
        }
        return [
            'answer' => 'Yes. Envi Tech AL provides boundary-noise, workplace-noise, area-monitoring, and personal-exposure dosimetry services. The activity, locations, duration, and reporting purpose must be scoped before monitoring.',
            'citations' => ['https://envitechal.com/noise-monitoring-dosimetry/'],
        ];
    }

    $calibration_terms = ['calibrate', 'calibration', 'calibaration', 'equipment calibration'];
    if ($has($calibration_terms) || ($contextual_follow_up && $topic_has($calibration_terms))) {
        if ($asks_parameters || $has(['which instrument', 'which instruments', 'what instruments', 'equipment list', 'what equipment', 'which equipment'])) {
            return [
                'answer' => 'Published calibration categories include balances, thermometers, pH meters, conductivity meters, pressure gauges, flow meters, and environmental meters. Capability is confirmed against the exact instrument type and range before booking.',
                'citations' => ['https://envitechal.com/services/equipment-calibration-services/'],
            ];
        }
        if ($has(['ph meter', 'ph meters'])) {
            return [
                'answer' => 'Yes. Envi Tech AL lists pH meters among the common instrument categories supported by its equipment-calibration service. Share the instrument range, location, acceptance criteria, and certificate requirement before booking.',
                'citations' => ['https://envitechal.com/services/equipment-calibration-services/'],
            ];
        }
        if ($has(['weighing balance', 'weighing balances', 'balance', 'balances', 'conductivity meter', 'conductivity meters', 'pressure gauge', 'pressure gauges', 'flow meter', 'flow meters', 'thermometer', 'thermometers'])) {
            return [
                'answer' => 'Yes. That instrument category appears in Envi Tech AL\'s published calibration list. Share the exact instrument type, range, location, acceptance criteria, and certificate requirement so capability can be confirmed before booking.',
                'citations' => ['https://envitechal.com/services/equipment-calibration-services/'],
            ];
        }
        return [
            'answer' => 'Envi Tech AL provides equipment-calibration services. Share the instrument type, range, location, acceptance criteria, and certificate requirement so the team can confirm the exact capability.',
            'citations' => ['https://envitechal.com/services/equipment-calibration-services/'],
        ];
    }

    if ($has(['soil testing', 'soil test', 'test soil', 'testing soil', 'contaminated soil', 'soil for heavy metals', 'analyse soil', 'analyze soil']) || ($contextual_follow_up && $topic_has('soil'))) {
        if ($has(['heavy metal', 'heavy metals'])) {
            return [
                'answer' => 'Yes. Heavy metals are listed among Envi Tech AL\'s published soil-testing indicators. Share the site history, target metals, sampling locations and depths, and intended report use so the exact scope can be confirmed.',
                'citations' => ['https://envitechal.com/soil-hazardous-waste-testing/'],
            ];
        }
        if ($asks_parameters) {
            return [
                'answer' => 'Published soil-testing indicators include pH, moisture, heavy metals, oil and grease where scoped, organic indicators where required, and site-specific analytes. The final panel depends on the site history and decision the report must support.',
                'citations' => ['https://envitechal.com/soil-hazardous-waste-testing/'],
            ];
        }
        return [
            'answer' => 'Yes. Envi Tech AL provides soil testing. Share the site context, target analytes, and intended use of the report so the correct sampling and testing scope can be confirmed.',
            'citations' => ['https://envitechal.com/soil-hazardous-waste-testing/'],
        ];
    }

    if ($has(['sludge testing', 'sludge test', 'test sludge']) || ($asks_parameters && $topic_has('sludge'))) {
        if ($asks_parameters) {
            return [
                'answer' => 'Sludge parameters must be selected from the sludge type and report purpose. Published indicators include pH, moisture, heavy metals, oil and grease where scoped, organic indicators where required, and waste-characterization indicators.',
                'citations' => ['https://envitechal.com/soil-hazardous-waste-testing/'],
            ];
        }
        return [
            'answer' => 'Yes. Envi Tech AL provides sludge testing support. Share the sludge type, target analytes, and intended use of the report so the exact scope can be confirmed.',
            'citations' => ['https://envitechal.com/soil-hazardous-waste-testing/'],
        ];
    }

    if ($has(['hazardous waste', 'waste characterization', 'waste characterisation']) || ($contextual_follow_up && $topic_has(['hazardous waste', 'waste characterization', 'waste characterisation']))) {
        if ($asks_parameters) {
            return [
                'answer' => 'Hazardous-waste parameters must be selected from the waste type and intended decision. Published indicators include pH, moisture, heavy metals, oil and grease where scoped, organic indicators where required, and waste-characterization indicators.',
                'citations' => ['https://envitechal.com/soil-hazardous-waste-testing/'],
            ];
        }
        return [
            'answer' => 'Yes. Envi Tech AL provides hazardous-waste testing support. Share the waste type, target analytes, and intended use of the report so the exact scope can be confirmed.',
            'citations' => ['https://envitechal.com/soil-hazardous-waste-testing/'],
        ];
    }

    $drinking_water_terms = [
        'drinking water',
        'potable water',
        'kitchen water',
        'tap water',
        'home water',
        'water at home',
        'water tested at home',
        'test water at home',
        'house water',
        'household water',
        'domestic water',
        'cooking water',
        'storage tank water',
        'tank water',
        'bore water',
        'groundwater',
        'ground water',
        'well water',
        'ro water',
        'ro plant water',
    ];
    $process_water_terms = [
        'process water',
        'utility water',
        'thermal power plant water',
        'power plant water',
        'boiler water',
        'boiler feed water',
        'boiler feed',
        'cooling water',
        'cooling tower water',
        'product water',
    ];
    $process_water_topic = $has($process_water_terms) || ($contextual_follow_up && $topic_has($process_water_terms));
    if ($process_water_topic) {
        if ($asks_parameters) {
            return [
                'answer' => 'For process or utility water, the published representative parameters include conductivity, hardness, alkalinity, silica, iron, chloride, and microbial load where required. Boiler-feed and cooling-water scopes depend on the system, operating issue, and buyer or industry specification.',
                'citations' => ['https://envitechal.com/services/water-testing-lab-services/'],
            ];
        }
        return [
            'answer' => 'Yes. Envi Tech AL publishes process and utility-water analysis, including boiler-feed and cooling-water review. Share the system, operating concern, required parameters, and reporting purpose so the laboratory can confirm the exact scope.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/'],
        ];
    }

    if ($has(['swimming pool water', 'pool water'])) {
        return [
            'answer' => 'Swimming-pool water is not specifically confirmed in Envi Tech AL\'s published testing scope. Share the required parameters, standard, city, and reporting purpose with the laboratory so availability and method can be verified before sampling.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/'],
        ];
    }

    $drinking_water_analytes = [
        'arsenic',
        'lead',
        'cadmium',
        'chromium',
        'mercury',
        'fluoride',
        'nitrate',
        'e coli',
        'total coliform',
    ];
    $drinking_water_topic = $has($drinking_water_terms)
        || ($has('water') && $has($drinking_water_analytes))
        || ($contextual_follow_up && $topic_has($drinking_water_terms));
    if ($drinking_water_topic) {
        $drinking_water_parameters = [
            'ph' => 'pH',
            'color' => 'color',
            'turbidity' => 'turbidity',
            'tds' => 'TDS',
            'total dissolved solids' => 'TDS',
            'hardness' => 'hardness',
            'chloride' => 'chloride',
            'sulfate' => 'sulfate',
            'sulphate' => 'sulfate',
            'nitrate' => 'nitrate',
            'fluoride' => 'fluoride',
            'iron' => 'iron',
            'manganese' => 'manganese',
            'copper' => 'copper',
            'lead' => 'lead',
            'arsenic' => 'arsenic',
            'cadmium' => 'cadmium',
            'chromium' => 'chromium',
            'mercury' => 'mercury',
            'residual chlorine' => 'residual chlorine',
            'total coliform' => 'total coliform',
            'e coli' => 'E. coli',
            'alpha emitters' => 'alpha emitters',
            'beta emitters' => 'beta emitters',
        ];
        $requested_parameters = [];
        foreach ($drinking_water_parameters as $term => $label) {
            if ($has($term)) {
                $requested_parameters[$label] = $label;
            }
        }
        if ($requested_parameters) {
            $labels = array_values($requested_parameters);
            $listed = count($labels) === 1 ? $labels[0] : implode(', ', array_slice($labels, 0, -1)) . ' and ' . end($labels);
            return [
                'answer' => count($labels) === 1
                    ? sprintf('Yes. Envi Tech AL lists %s as an available drinking-water testing parameter. Confirm the water source and reporting purpose so the correct sampling and method requirements are used.', $listed)
                    : sprintf('Yes. Envi Tech AL lists %s as available drinking-water testing parameters. Confirm the water source and reporting purpose so the correct sampling and method requirements are used.', $listed),
                'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
            ];
        }
        if ($asks_parameters) {
            if ($topic_has(['ro water', 'ro plant water'])) {
                return [
                    'answer' => 'For RO performance, the published scope includes feed, permeate, and reject TDS; conductivity; and recovery indicators. Add health or compliance parameters only when the water use or reporting purpose requires them.',
                    'citations' => ['https://envitechal.com/services/water-testing-lab-services/'],
                ];
            }
            return [
                'answer' => 'For kitchen, tap, or drinking water, a practical panel can include pH, color, turbidity, TDS, hardness, chloride, sulfate, nitrate, fluoride, iron, manganese, copper, lead, arsenic, residual chlorine, total coliform, and E. coli. The final panel depends on the source and reporting purpose.',
                'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
            ];
        }
        if ($has(['bacteria', 'microbiological', 'microbiology', 'germs', 'جراثیم'])) {
            return [
                'answer' => 'For drinking-water microbiological screening, the published parameters are total coliform and E. coli. Confirm the water source and reporting purpose so the correct sterile sampling requirements are used.',
                'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
            ];
        }
        if ($has(['standard', 'standards', 'guideline', 'guidelines', 'who limit', 'safe limit'])) {
            return [
                'answer' => 'The comparison basis may include WHO guideline values, Pakistan drinking-water requirements, or a specific facility, buyer, or project standard. Confirm the required standard before selecting the final panel.',
                'citations' => ['https://envitechal.com/services/water-testing-lab-services/'],
            ];
        }
        if ($has(['sample', 'sampling', 'bottle', 'container', 'collect', 'collection', 'preserve', 'preservation'])) {
            return [
                'answer' => 'Confirm the parameter panel with the laboratory before collecting the water sample. Bottle type, sterilization, preservation, sample volume, and transit time depend on whether chemistry, metals, or microbiology will be tested.',
                'citations' => ['https://envitechal.com/services/water-testing-lab-services/'],
            ];
        }
        if ($has(['test', 'tested', 'testing', 'check', 'checked', 'analyse', 'analyze', 'analysed', 'analyzed', 'analysis'])) {
            return [
                'answer' => 'Yes. Envi Tech AL provides drinking-water testing for kitchen, tap, bore, groundwater, and RO water. Share the water source and reporting purpose so the correct panel and sampling requirements can be selected.',
                'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
            ];
        }
        return [
            'answer' => 'Please specify whether the water is from a kitchen tap, bore, storage tank, or RO plant and whether the result is for health screening, operations, an audit, or compliance. I can then identify the relevant published testing scope.',
            'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
        ];
    }

    if ($has(['e coli', 'total coliform', 'coliform', 'microbiological testing for water', 'water microbiology', 'water bacteria'])) {
        return [
            'answer' => 'Total coliform and E. coli are published for drinking-water microbiological screening. Please identify the sample matrix and source before collection; availability and sampling requirements must not be assumed for a different matrix.',
            'citations' => ['https://envitechal.com/drinking-water-testing-lab/'],
        ];
    }

    $wastewater_terms = ['wastewater', 'waste water', 'effluent', 'etp', 'sewage', 'industrial discharge'];
    $wastewater_topic = $has($wastewater_terms) || ($contextual_follow_up && $topic_has($wastewater_terms));
    if ($wastewater_topic && !$has('seqs')) {
        $wastewater_parameters = [
            'temperature' => 'temperature',
            'ph' => 'pH',
            'bod' => 'BOD',
            'biochemical oxygen demand' => 'BOD',
            'cod' => 'COD',
            'chemical oxygen demand' => 'COD',
            'tss' => 'TSS',
            'total suspended solids' => 'TSS',
            'tds' => 'TDS',
            'total dissolved solids' => 'TDS',
            'oil and grease' => 'oil and grease',
            'phenolic compounds' => 'phenolic compounds',
            'phenols' => 'phenolic compounds',
            'sulfide' => 'sulfide',
            'sulphide' => 'sulfide',
            'ammonia' => 'ammonia',
            'chloride' => 'chloride',
            'sulfate' => 'sulfate',
            'sulphate' => 'sulfate',
            'chromium' => 'chromium',
            'copper' => 'copper',
            'zinc' => 'zinc',
            'nickel' => 'nickel',
            'lead' => 'lead',
            'cadmium' => 'cadmium',
            'mercury' => 'mercury',
        ];
        $requested_parameters = [];
        foreach ($wastewater_parameters as $term => $label) {
            if ($has($term)) {
                $requested_parameters[$label] = $label;
            }
        }
        if ($requested_parameters) {
            $labels = array_values($requested_parameters);
            $listed = count($labels) === 1 ? $labels[0] : implode(', ', array_slice($labels, 0, -1)) . ' and ' . end($labels);
            return [
                'answer' => sprintf('Yes. Envi Tech AL lists %s among its published wastewater testing parameters. Confirm the sample point, discharge route, and reporting standard before finalizing the panel.', $listed),
                'citations' => ['https://envitechal.com/wastewater-testing-services/'],
            ];
        }
        if ($asks_parameters) {
            return [
                'answer' => 'Common wastewater parameters include temperature, pH, BOD, COD, TSS, TDS, oil and grease, phenolic compounds, sulfide, ammonia, chloride, sulfate, and relevant metals. The final panel depends on the discharge route and reporting standard.',
                'citations' => ['https://envitechal.com/wastewater-testing-services/'],
            ];
        }
        if ($has(['standard', 'standards', 'limit', 'limits', 'compliance'])) {
            return [
                'answer' => 'The applicable wastewater comparison may be SEQS, NEQS, PEQS, an approval condition, or a buyer specification. The province, discharge destination, and report purpose must be confirmed before selecting limits and parameters.',
                'citations' => ['https://envitechal.com/wastewater-testing-services/'],
            ];
        }
        if ($asks_sampling) {
            return [
                'answer' => 'Confirm the wastewater panel before sampling. Grab or composite sampling, bottle type, preservation, sample volume, and holding time depend on the discharge point, parameters, and reporting purpose.',
                'citations' => ['https://envitechal.com/wastewater-testing-services/'],
            ];
        }
        if ($has(['test', 'testing', 'cod', 'bod', 'tss', 'tds', 'check', 'analyse', 'analyze', 'analysis'])) {
            return [
                'answer' => 'Yes. Envi Tech AL tests industrial wastewater, effluent, and ETP inlet or outlet samples. The final panel and comparison standard depend on the discharge route and report purpose.',
                'citations' => ['https://envitechal.com/wastewater-testing-services/'],
            ];
        }
        return [
            'answer' => 'Please specify whether the sample is raw effluent, ETP inlet, ETP outlet, sewage, or another industrial discharge and what the report must support. I can then identify the relevant published testing scope.',
            'citations' => ['https://envitechal.com/wastewater-testing-services/'],
        ];
    }

    if ($has('water') && !$has(['ballast water', 'deballast water']) && $has(['test', 'tested', 'testing', 'check', 'checked', 'analyse', 'analyze', 'analysis', 'quality'])) {
        return [
            'answer' => 'Envi Tech AL publishes testing for drinking, bore or groundwater, RO, process or utility water, and wastewater. Please identify the water source and report purpose so the relevant published scope and sampling requirements can be selected without guessing.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/'],
        ];
    }

    if ($has(['water testing', 'wastewater testing', 'drinking water testing']) && !$has(['ballast water', 'deballast water'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides drinking-water, groundwater, process-water, and wastewater testing. The correct parameters and reporting basis are selected from the water source and intended use.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/'],
        ];
    }

    $industrial_hygiene_terms = [
        'industrial hygiene',
        'workplace exposure',
        'heat stress',
        'workplace dust',
        'occupational dust',
        'dust exposure',
        'factory dust',
        'monitor dust',
        'measure dust',
        'dust in our factory',
        'occupational exposure',
        'illumination survey',
        'illumination surveys',
        'indoor air quality',
    ];
    if ($has($industrial_hygiene_terms) || ($contextual_follow_up && $topic_has($industrial_hygiene_terms))) {
        if ($has(['occupational dust', 'workplace dust', 'factory dust', 'dust exposure', 'monitor dust', 'measure dust', 'dust in our factory'])) {
            return [
                'answer' => 'Yes. Envi Tech AL publishes respirable or inhalable dust monitoring where required. Share the process, worker groups, work areas, shifts, and reporting purpose so the exposure plan can be confirmed.',
                'citations' => ['https://envitechal.com/industrial-hygiene-monitoring/'],
            ];
        }
        if ($has(['illumination survey', 'illumination surveys'])) {
            return [
                'answer' => 'Yes. Illumination is listed as an industrial-hygiene indicator where applicable. Share the work areas, activities, shifts, and reporting requirement so the measurement plan can be confirmed.',
                'citations' => ['https://envitechal.com/industrial-hygiene-monitoring/'],
            ];
        }
        if ($has('indoor air quality')) {
            return [
                'answer' => 'Envi Tech AL publishes workplace-air, dust, and exposure monitoring, but a complete indoor-air panel is not specified on the cited page. Share the concern, work areas, suspected sources, and required standard so exact capability can be verified without assumption.',
                'citations' => ['https://envitechal.com/industrial-hygiene-monitoring/'],
            ];
        }
        if ($asks_parameters) {
            return [
                'answer' => 'Industrial-hygiene scope can include respirable or inhalable dust where required, noise exposure, temperature and humidity, illumination where applicable, work-area observations, and exposure-group notes. The exact panel depends on the process and worker groups.',
                'citations' => ['https://envitechal.com/industrial-hygiene-monitoring/'],
            ];
        }
        return [
            'answer' => 'Yes. Envi Tech AL provides industrial-hygiene monitoring for workplace air, dust, noise, heat stress, and other exposure-related conditions. Share the process, work areas, shifts, and reporting purpose to scope the monitoring.',
            'citations' => ['https://envitechal.com/industrial-hygiene-monitoring/'],
        ];
    }

    if ($has(['ballast water', 'deballast water', 'maritime testing'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides ballast and deballast water testing support, including port-call planning, sampling coordination, pathogen screening where scoped, and reporting support. Vessel schedule and inspection requirements must be confirmed first.',
            'citations' => ['https://envitechal.com/services/ballast-water-testing-services/'],
        ];
    }

    if ($has(['thermal imaging', 'thermography', 'thermal inspection'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides thermal-imaging inspection for electrical, mechanical, and facility equipment to identify abnormal heat patterns that may support preventive-maintenance decisions. It does not replace repair or maintenance work.',
            'citations' => ['https://envitechal.com/services/thermal-imaging-inspection/'],
        ];
    }

    if ($has(['analytical laboratory', 'analytical lab', 'laboratory analysis', 'lab analysis'])) {
        return [
            'answer' => 'Envi Tech AL provides analytical support for water, wastewater, air and emissions, soil, waste, and other industrial samples. The laboratory, method, parameter, and any accreditation claim must be confirmed for the exact requested scope.',
            'citations' => ['https://envitechal.com/services/analytical-lab-services/'],
        ];
    }

    if ($has(['iso 9001', 'iso 14001', 'certification advisory']) && $has(['help', 'support', 'consult', 'consultancy', 'advisory', 'prepare', 'preparation'])) {
        return [
            'answer' => 'Envi Tech AL provides certification advisory such as gap assessment, document review, and audit-preparation support. It does not issue ISO certification; certification decisions belong to the selected certification body.',
            'citations' => ['https://envitechal.com/services/certification-advisory/'],
        ];
    }

    if ($has([
        'what services',
        'services do you provide',
        'services does envi tech al provide',
        'services do you offer',
        'services available',
        'list your services',
        'tell me your services',
        'what do you do',
        'what can you do',
        'how can you help',
        'how can your company help',
        'help my factory',
        'help our factory',
    ])) {
        return [
            'answer' => 'Envi Tech AL provides water and wastewater testing, analytical laboratory services, equipment calibration, air-emissions and noise monitoring, and environmental consultancy including EIA, EMP, and EMR support.',
            'citations' => ['https://envitechal.com/services/'],
        ];
    }

    if ($has('seqs') && $has(['cod', 'chemical oxygen demand'])) {
        return [
            'answer' => 'The SEQS 2016 table lists industrial-effluent COD limits of 150 mg/L for discharge into inland waters, 400 mg/L for discharge into sewage treatment, and 400 mg/L for discharge into the sea. The official notification and the facility\'s approval conditions control, so verify the current binding text before relying on these figures.',
            'citations' => ['https://envitechal.com/sindh-environmental-quality-standards-seqs/'],
        ];
    }

    if ($has(['eia', 'environmental impact assessment']) && $has(['punjab', 'punjab epa'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides environmental consultancy support for EIA work on Punjab projects. Punjab EPA is the relevant provincial authority, and the study category, submission route, and approval depend on the project and current rules.',
            'citations' => ['https://envitechal.com/services/environmental-consultancy/'],
        ];
    }

    if ($has(['what is eia', 'what is an eia', 'define eia', 'eia meaning', 'what does eia mean'])) {
        return [
            'answer' => 'An Environmental Impact Assessment is a detailed environmental study used for projects with potentially significant impacts. The required study category, content, submission route, and authority depend on the project and current rules.',
            'citations' => ['https://envitechal.com/emp-emr-iee-eia-compliance/'],
        ];
    }

    if ($has(['what is iee', 'what is an iee', 'define iee', 'iee meaning', 'what does iee mean'])) {
        return [
            'answer' => 'An Initial Environmental Examination is a project environmental review used in certain approval pathways. Whether an IEE is required depends on the project type, location, and current authority requirements.',
            'citations' => ['https://envitechal.com/emp-emr-iee-eia-compliance/'],
        ];
    }

    if ($has(['what is emp', 'what is an emp', 'define emp', 'emp meaning', 'what does emp mean'])) {
        return [
            'answer' => 'An Environmental Management Plan defines the controls, monitoring, mitigation measures, responsibilities, and follow-up actions for a project or operating facility.',
            'citations' => ['https://envitechal.com/emp-emr-iee-eia-compliance/'],
        ];
    }

    if ($has(['what is emr', 'what is an emr', 'define emr', 'emr meaning', 'what does emr mean'])) {
        return [
            'answer' => 'An Environmental Monitoring Report documents monitoring and testing results against required conditions over a reporting period.',
            'citations' => ['https://envitechal.com/emp-emr-iee-eia-compliance/'],
        ];
    }

    if ($has(['eia', 'environmental impact assessment', 'iee', 'initial environmental examination', 'emp', 'environmental management plan', 'emr', 'environmental monitoring report'])) {
        return [
            'answer' => 'Yes. Envi Tech AL provides consultancy support for IEE, EIA, EMP, and EMR work, including monitoring evidence and documentation pathways. The project type, province, authority requirement, and deadline must be confirmed before scope.',
            'citations' => ['https://envitechal.com/emp-emr-iee-eia-compliance/'],
        ];
    }

    if ($has(['environmental consultancy', 'environmental consultant', 'sepa compliance', 'epa compliance', 'regulatory consultancy'])) {
        return [
            'answer' => 'Envi Tech AL provides environmental consultancy for new projects and operating facilities, including monitoring plans, regulatory submissions, audits, corrective actions, and IEE, EIA, EMP, or EMR support where applicable.',
            'citations' => ['https://envitechal.com/services/environmental-consultancy/'],
        ];
    }

    if ($has(['are you accredited', 'is your lab accredited', 'is your laboratory accredited', 'is the lab accredited', 'is the laboratory accredited', 'accreditation status'])) {
        return [
            'answer' => 'Envi Tech AL holds PNAC LAB-347 for the Lahore premises only, and only for the exact matrix, parameter, and method combinations in the published scope. It must not be applied to the Karachi laboratory or to unlisted work.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/', 'https://envitechal.com/accreditations-certifications/'],
        ];
    }

    if ($has('email') && !$has(['whatsapp', 'phone', 'telephone'])) {
        return [
            'answer' => 'Envi Tech AL\'s published email address is info@envitechal.com.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['whatsapp', 'phone', 'telephone']) && !$has('email')) {
        return [
            'answer' => 'Envi Tech AL\'s published WhatsApp number is +92 310 2288801.',
            'citations' => ['https://envitechal.com/contact-us-envi-tech-al/'],
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

    if ($has(['islamabad', 'rawalpindi', 'faisalabad', 'multan', 'peshawar', 'quetta', 'another city', 'other city']) && $has(['service', 'services', 'test', 'testing', 'sample', 'sampling', 'cover', 'available'])) {
        return [
            'answer' => 'Published field-sampling coordination is centered on Karachi and Lahore. For another city, contact the laboratory before dispatch or travel so service availability, container, preservation, transit, and schedule can be confirmed for the exact requirement.',
            'citations' => ['https://envitechal.com/services/water-testing-lab-services/', 'https://envitechal.com/contact-us-envi-tech-al/'],
        ];
    }

    if ($has(['office', 'offices', 'address', 'addresses', 'where are you', 'where is your lab', 'where is your laboratory', 'lab location', 'laboratory location', 'location', 'locations'])) {
        return [
            'answer' => 'Envi Tech AL has published offices in Karachi and Lahore. Karachi: First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS. Lahore: 87-E Madina Heights, Office A/30-31, 8th Floor, Maulana Shaukat Ali Road, Johar Town.',
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
            'answer' => 'ISO/IEC 17025 is laboratory accreditation, not management-system certification. Envi Tech AL\'s PNAC LAB-347 accreditation applies to the Lahore premises only and only to the matrix, parameter, and method combinations in its published scope; it does not apply to the Karachi laboratory.',
            'citations' => ['https://envitechal.com/accreditations-certifications/'],
        ];
    }

    if ($has('report') && $has(['verify', 'verification', 'authenticate', 'authentic'])) {
        return [
            'answer' => 'Use Envi Tech AL\'s Report Verification Portal and enter the verification details printed on the report. If they do not validate, contact info@envitechal.com before relying on the document.',
            'citations' => ['https://envitechal.com/report-verification-portal/'],
        ];
    }

    if ($has(['report', 'results', 'result']) && $has(['explain', 'interpret', 'understand', 'meaning', 'review'])) {
        return [
            'answer' => 'Envi Tech AL can clarify the reported parameters, units, comparison basis, and any stated limits. The interpretation must use the actual report and its intended purpose; the assistant should not invent a compliance, health, or approval conclusion without that evidence.',
            'citations' => ['https://envitechal.com/report-verification-portal/'],
        ];
    }

    if ($has(['sample', 'sampling', 'sample collection', 'collect sample', 'deliver sample', 'send sample', 'sample bottle', 'sample label'])) {
        return [
            'answer' => 'Confirm the testing scope before collecting or dispatching a sample. Location, container, preservation, holding time, volume, label, and chain-of-custody requirements depend on the sample type and parameters; critical samples should not be sent without laboratory guidance.',
            'citations' => ['https://envitechal.com/environmental-testing-faqs-pakistan/'],
        ];
    }

    if ($has(['guarantee', 'guaranteed', 'ensure i pass', 'ensure we pass']) && $has(['epa', 'inspection', 'approval', 'pass'])) {
        return [
            'answer' => 'No. Envi Tech AL cannot guarantee that a facility will pass an EPA inspection or receive regulatory approval. The authority decides the outcome from applicable law, permit conditions, evidence, and inspection findings. Envi Tech AL can provide testing and consultancy support without promising a regulatory result.',
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

    $messages = [];
    $curated_context = '';
    $history = $request->get_param('history');
    if (is_array($history)) {
        foreach (array_slice($history, -8) as $entry) {
            if (!is_array($entry) || !in_array($entry['role'] ?? '', ['user', 'assistant'], true)) {
                continue;
            }
            $content = sanitize_textarea_field((string) ($entry['content'] ?? ''));
            if ($content !== '') {
                $messages[] = ['role' => $entry['role'], 'content' => $content];
                if ($entry['role'] === 'user') {
                    $curated_context = $content;
                }
            }
        }
    }

    $curated = eta_agent_curated_response($message, $curated_context);
    if (is_array($curated)) {
        return rest_ensure_response($curated);
    }

    if (!eta_agent_remote_enabled()) {
        return rest_ensure_response(eta_agent_safe_fallback_response());
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
