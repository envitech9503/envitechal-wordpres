<?php
/**
 * Envi Tech AL Modern child theme bootstrap.
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_stylesheet_directory() . '/inc/premium-post-patterns.php';
require_once get_stylesheet_directory() . '/inc/ai-visibility.php';

add_action('wp_enqueue_scripts', function () {
    $modern_css = get_stylesheet_directory() . '/assets/css/eta-modern.css';

    wp_enqueue_style(
        'generatepress-parent',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme('generatepress')->get('Version')
    );

    wp_enqueue_style(
        'eta-modern',
        get_stylesheet_directory_uri() . '/assets/css/eta-modern.css',
        ['generatepress-parent'],
        file_exists($modern_css) ? (string) filemtime($modern_css) : wp_get_theme()->get('Version')
    );

    wp_add_inline_style(
        'eta-modern',
        ':root{--eta-green-2:#1f6f52}.single-post .eta-post-side-panel{display:none!important}:where(a,button,input,select,textarea,summary,[tabindex]):focus-visible{outline:3px solid #ffcc4d!important;outline-offset:3px!important}.eta-chatbot-launcher:focus-visible{box-shadow:0 0 0 2px #102229,0 0 0 6px #ffcc4d!important}'
    );
}, 20);

add_action('wp_enqueue_scripts', function () {
    if (is_admin()) {
        return;
    }

    $unused_public_scripts = [
        'mailin-front',
        'ppress-flatpickr',
        'ppress-select2',
        'ppress-frontend',
        'wp-user-avatar',
        'wp-user-avatar-frontend',
    ];

    foreach ($unused_public_scripts as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}, 1000);

function eta_modern_page_has_contact_form()
{
    if (is_admin()) {
        return false;
    }

    if (is_page(['contact-us-envi-tech-al', 'careers-at-envi-tech-al', 'report-verification-portal'])) {
        return true;
    }

    if (is_singular()) {
        $post = get_post();
        return $post && has_shortcode((string) $post->post_content, 'contact-form-7');
    }

    return false;
}

add_action('wp_enqueue_scripts', function () {
    if (eta_modern_page_has_contact_form()) {
        return;
    }

    foreach (['google-recaptcha', 'wpcf7-recaptcha', 'iqfix-recaptcha', 'anr-google-recaptcha'] as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}, 9999);

add_filter('wpcf7_load_js', function ($load) {
    return eta_modern_page_has_contact_form() ? $load : false;
});

add_filter('wpcf7_load_css', function ($load) {
    return eta_modern_page_has_contact_form() ? $load : false;
});

add_filter('post_thumbnail_html', function ($html, $post_id) {
    if (is_admin()) {
        return $html;
    }

    $slug = $post_id ? get_post_field('post_name', $post_id) : '';
    $modern_custom_pages = [
        'contact-us-envi-tech-al',
        'aboutus',
        'downloads',
        'sindh-environmental-quality-standards-seqs',
        'report-verification-portal',
        'blognewsupdates',
        'newsupdates',
        'careers-at-envi-tech-al',
        'frequently-asked-questions-water-testing-in-karachi',
        'ourclients',
        'lahore-environmental-lab',
        'karachi-environmental-lab',
        'certificates-approvals',
        'accreditations-certifications',
        'environmental-testing-faqs-pakistan',
        'wastewater-testing-services',
        'drinking-water-testing-lab',
        'ambient-air-monitoring-services',
        'noise-monitoring-dosimetry',
        'industrial-hygiene-monitoring',
        'soil-hazardous-waste-testing',
        'emp-emr-iee-eia-compliance',
        'maritime-environmental-testing',
        'tdap-registered-lab-in-karachi-pakistan',
    ];

    if (is_page($modern_custom_pages) && in_array($slug, $modern_custom_pages, true)) {
        return '';
    }

    return $html;
}, 20, 2);

add_filter('body_class', function ($classes) {
    if (is_admin() || !is_page()) {
        return $classes;
    }

    $modern_custom_pages = [
        'contact-us-envi-tech-al',
        'aboutus',
        'downloads',
        'sindh-environmental-quality-standards-seqs',
        'report-verification-portal',
        'blognewsupdates',
        'newsupdates',
        'careers-at-envi-tech-al',
        'frequently-asked-questions-water-testing-in-karachi',
        'ourclients',
        'lahore-environmental-lab',
        'karachi-environmental-lab',
        'certificates-approvals',
        'accreditations-certifications',
        'environmental-testing-faqs-pakistan',
        'wastewater-testing-services',
        'drinking-water-testing-lab',
        'ambient-air-monitoring-services',
        'noise-monitoring-dosimetry',
        'industrial-hygiene-monitoring',
        'soil-hazardous-waste-testing',
        'emp-emr-iee-eia-compliance',
        'maritime-environmental-testing',
        'tdap-registered-lab-in-karachi-pakistan',
    ];

    if (is_page($modern_custom_pages)) {
        $classes[] = 'eta-modern-custom-page';
    }

    return $classes;
}, 20);

add_filter('pre_get_document_title', function ($title) {
    if (is_front_page()) {
        return 'Envi Tech AL | Environmental Testing Lab & Consultancy';
    }

    if (is_page('contact-us-envi-tech-al')) {
        return 'Contact Envi Tech AL | Environmental Testing Lab Karachi & Lahore';
    }

    if (is_page('aboutus')) {
        return 'About Envi Tech AL | Environmental Testing Laboratory & Consultancy in Pakistan';
    }

    if (is_page('downloads')) {
        return 'Downloads | Environmental Laws, Certificates & Compliance Resources';
    }

    if (is_page('sindh-environmental-quality-standards-seqs')) {
        return eta_modern_seqs_title();
    }

    if (is_page('report-verification-portal')) {
        return 'Report Verification Portal | Verify Envi Tech AL Test Reports';
    }

    if (is_page('careers-at-envi-tech-al')) {
        return 'Careers at Envi Tech AL | Environmental Lab & Field Jobs';
    }

    if (is_page('frequently-asked-questions-water-testing-in-karachi')) {
        return 'Environmental Testing FAQ | Envi Tech AL Karachi & Lahore';
    }

    if (is_page('environmental-testing-faqs-pakistan')) {
        return 'Environmental Testing FAQs Pakistan | Envi Tech AL';
    }

    if (is_page('ourclients')) {
        return 'Clients | Envi Tech AL Environmental Testing Portfolio';
    }

    if (is_page('lahore-environmental-lab')) {
        return 'Environmental Lab in Lahore | Envi Tech AL';
    }

    if (is_page('karachi-environmental-lab')) {
        return 'Environmental Testing Lab in Karachi | Envi Tech AL';
    }

    if (is_page(['certificates-approvals', 'accreditations-certifications'])) {
        return 'Certifications, Approvals & Laboratory Quality Credentials | Envi Tech AL';
    }

    if (is_page()) {
        $cluster = eta_modern_cluster_page_data(get_post_field('post_name', get_the_ID()));
        if ($cluster) {
            return $cluster['seo_title'];
        }
    }

    if (is_page('tdap-registered-lab-in-karachi-pakistan')) {
        return 'TDAP Export Testing Support in Karachi | Envi Tech AL';
    }

    if (is_page(['blognewsupdates', 'newsupdates']) || is_home() || (is_archive() && !is_post_type_archive('services'))) {
        return 'Knowledge Hub | Envi Tech AL';
    }

    if (is_post_type_archive('services')) {
        return 'Our Services | Envi Tech AL';
    }

    if (is_singular('services')) {
        if (get_post_field('post_name', get_the_ID()) === 'water-testing-lab-services') {
            return 'Water Testing Laboratory in Karachi & Lahore | Envi Tech AL';
        }

        if (get_post_field('post_name', get_the_ID()) === 'analytical-lab-services') {
            return 'Analytical Lab Services | Envi Tech AL';
        }

        return eta_modern_display_title(get_the_ID()) . ' | Envi Tech AL';
    }

    if (is_singular('post')) {
        if (get_post_field('post_name', get_the_ID()) === 'gaseous-air-emission-testing-lab-near-me') {
            return 'Gaseous & Stack Emission Testing Lab in Karachi & Lahore | Envi Tech AL';
        }

        return eta_modern_post_seo_title(get_the_ID());
    }

    if (is_404()) {
        return 'Page Not Found - Envi Tech AL';
    }

    return $title;
}, 20);

function eta_modern_is_water_testing_service()
{
    return !is_admin() && is_singular('services') && get_post_field('post_name', get_the_ID()) === 'water-testing-lab-services';
}

function eta_modern_water_testing_seo_title()
{
    return 'Water Testing Laboratory in Karachi & Lahore | Envi Tech AL';
}

function eta_modern_water_testing_og_image()
{
    return 'https://envitechal.com/wp-content/uploads/2026/05/water-testing-services-karachi-lahore.png';
}

function eta_modern_default_share_image()
{
    return 'https://envitechal.com/wp-content/uploads/2026/06/envitechal-share-default-1200x630.png';
}

function eta_modern_emr_emp_post_slug()
{
    return 'environmental-monitoring-report-emr-emp-sindh-epa';
}

function eta_modern_is_emr_emp_post($post = null)
{
    $post = get_post($post);
    if (!$post && is_singular('post')) {
        $post = get_post(get_the_ID());
    }

    return $post && $post->post_type === 'post' && $post->post_name === eta_modern_emr_emp_post_slug();
}

function eta_modern_emr_emp_seo_title()
{
    return 'Environmental Monitoring Report (EMR/EMP) | Sindh EPA Guide';
}

function eta_modern_emr_emp_meta_description()
{
    return 'Environmental Monitoring Report (EMR/EMP) guidance for Sindh EPA submissions, monitoring scope, sampling evidence and compliance reporting in Pakistan.';
}

function eta_modern_emr_emp_share_image()
{
    return 'https://envitechal.com/wp-content/uploads/2026/06/environmental-monitoring-report-emr-emp-sindh-epa-card-safe.webp';
}

function eta_modern_social_image()
{
    if (is_singular('post')) {
        return eta_modern_post_image_url(get_the_ID()) ?: eta_modern_default_share_image();
    }

    return eta_modern_is_water_testing_service() ? eta_modern_water_testing_og_image() : eta_modern_default_share_image();
}

function eta_modern_round3_page_title()
{
    if (is_page('contact-us-envi-tech-al')) {
        return 'Contact Envi Tech AL | Environmental Testing Lab Karachi & Lahore';
    }

    if (is_page('aboutus')) {
        return 'About Envi Tech AL | Environmental Testing Laboratory & Consultancy in Pakistan';
    }

    if (is_page('sindh-environmental-quality-standards-seqs')) {
        return eta_modern_seqs_title();
    }

    if (is_page('environmental-testing-faqs-pakistan')) {
        return 'Environmental Testing FAQs Pakistan | Envi Tech AL';
    }

    if (is_page(['blognewsupdates', 'newsupdates']) || is_home() || (is_archive() && !is_post_type_archive('services'))) {
        return 'Knowledge Hub | Envi Tech AL';
    }

    if (is_post_type_archive('services')) {
        return 'Our Services | Envi Tech AL';
    }

    if (eta_modern_is_water_testing_service()) {
        return eta_modern_water_testing_seo_title();
    }

    if (is_singular('post')) {
        return eta_modern_post_seo_title(get_the_ID());
    }

    return '';
}

add_filter('rank_math/frontend/title', function ($title) {
    $modern_title = eta_modern_round3_page_title();
    return $modern_title ?: $title;
}, 20);

add_filter('rank_math/opengraph/facebook/title', function ($title) {
    $modern_title = eta_modern_round3_page_title();
    return $modern_title ?: $title;
}, 20);

add_filter('rank_math/opengraph/twitter/title', function ($title) {
    $modern_title = eta_modern_round3_page_title();
    return $modern_title ?: $title;
}, 20);

add_filter('rank_math/opengraph/facebook/image', function ($image) {
    return eta_modern_social_image() ?: $image;
}, 20);

add_filter('rank_math/opengraph/twitter/image', function ($image) {
    return eta_modern_social_image() ?: $image;
}, 20);

add_filter('rank_math/json_ld', function ($data) {
    return eta_modern_rank_math_schema_templates(is_array($data) ? $data : []);
}, 99);

add_action('template_redirect', function () {
    if (is_admin() || is_feed() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    ob_start('eta_modern_normalize_head_meta');
}, 0);

function eta_modern_normalize_head_meta($html)
{
    if (!is_string($html) || stripos($html, '</head>') === false || stripos($html, '<html') === false) {
        return $html;
    }

    $title = '';
    if (preg_match('/<title>\s*(.*?)\s*<\/title>/is', $html, $matches)) {
        $title = html_entity_decode(wp_strip_all_tags($matches[1]), ENT_QUOTES, get_bloginfo('charset'));
    }

    $title = trim($title);
    if ($title === '') {
        return $html;
    }

    $image = eta_modern_social_image();
    $description = eta_modern_clamp_meta_description(eta_modern_meta_description(), 155);
    $tags = [
        ['property', 'og:title', $title],
        ['name', 'twitter:title', $title],
        ['property', 'og:locale', 'en_GB'],
        ['name', 'twitter:site', '@al_envi'],
        ['name', 'twitter:creator', '@al_envi'],
        ['property', 'og:image', $image],
        ['name', 'twitter:image', $image],
    ];

    if ($description !== '') {
        $tags[] = ['property', 'og:description', $description];
        $tags[] = ['name', 'twitter:description', $description];
    }

    foreach ($tags as $tag) {
        $attribute = $tag[0] === 'property' ? 'property' : 'name';
        $key = preg_quote($tag[1], '/');
        $html = preg_replace('/<meta\b(?=[^>]*\b' . $attribute . '=["\']' . $key . '["\'])[^>]*>\s*/i', '', $html);
    }

    $tag_html = '';
    foreach ($tags as $tag) {
        $attribute = $tag[0] === 'property' ? 'property' : 'name';
        $tag_html .= sprintf(
            '<meta %1$s="%2$s" content="%3$s">' . "\n",
            $attribute,
            esc_attr($tag[1]),
            esc_attr($tag[2])
        );
    }

    return preg_replace('/<\/head>/i', $tag_html . '</head>', $html, 1);
}

function eta_modern_meta_description()
{
    if (is_front_page()) {
        return 'Envi Tech AL provides environmental testing, water and wastewater analysis, calibration, monitoring, and regulatory consultancy in Karachi and Lahore.';
    }

    if (is_page('contact-us-envi-tech-al')) {
        return 'Contact Envi Tech AL for environmental testing, water and wastewater analysis, calibration, monitoring and consultancy in Karachi and Lahore.';
    }

    if (is_page('aboutus')) {
        return 'Learn about Envi Tech AL, an environmental testing laboratory and consultancy supporting industry with lab analysis, monitoring, calibration, and compliance services.';
    }

    if (is_page('about-envi-tech-al-for-ai-search-engines')) {
        return 'Entity facts for Envi Tech AL, including official service categories, locations, contact routes, citation guidance and AI/search source preferences.';
    }

    if (is_page('downloads')) {
        return 'Download Envi Tech AL certificates, environmental laws, Sindh EPA resources, national compliance documents, and Pakistan Accord references for audit and regulatory use.';
    }

    if (is_page('sindh-environmental-quality-standards-seqs')) {
        return eta_modern_seqs_meta_description();
    }

    if (is_page('report-verification-portal')) {
        return 'Verify Envi Tech AL test reports using QR code guidance or manual verification with report number, report date, and company details.';
    }

    if (is_page('careers-at-envi-tech-al')) {
        return 'Explore careers at Envi Tech AL for laboratory, field, business development, environmental monitoring, and compliance support roles in Karachi and Lahore.';
    }

    if (is_page('frequently-asked-questions-water-testing-in-karachi')) {
        return 'Answers to common questions about Envi Tech AL environmental testing, water testing, calibration, consultancy, EPA compliance, and lab services in Karachi and Lahore.';
    }

    if (is_page('environmental-testing-faqs-pakistan')) {
        return 'Practical FAQs about environmental testing, water and wastewater testing, emissions monitoring, EPA compliance, calibration, sampling and report verification in Pakistan.';
    }

    if (is_page('ourclients')) {
        return 'See the industries and organizations served by Envi Tech AL across environmental testing, consultancy, calibration, monitoring, and compliance support.';
    }

    if (is_page('lahore-environmental-lab')) {
        return 'Envi Tech AL Lahore supports environmental testing, water testing, consultancy, monitoring, calibration, and compliance-ready reporting for Punjab clients.';
    }

    if (is_page('karachi-environmental-lab')) {
        return 'Envi Tech AL provides environmental testing, water and wastewater testing, stack emission testing, air monitoring, noise monitoring, soil testing and compliance support in Karachi, Sindh.';
    }

    if (is_page(['certificates-approvals', 'accreditations-certifications'])) {
        return 'Review Envi Tech AL certifications, approvals, laboratory quality credentials and verification notes for clients, auditors, regulators and procurement teams.';
    }

    if (is_page()) {
        $cluster = eta_modern_cluster_page_data(get_post_field('post_name', get_the_ID()));
        if ($cluster) {
            return $cluster['meta'];
        }
    }

    if (is_page('tdap-registered-lab-in-karachi-pakistan')) {
        return 'Testing and documentation support for exporters navigating TDAP or PTA program requirements in Karachi. Confirm current eligibility and registration with the relevant authority.';
    }

    if (is_page(['blognewsupdates', 'newsupdates']) || is_home() || (is_archive() && !is_post_type_archive('services'))) {
        return 'Expert insights on environmental testing, water quality, calibration, EPA compliance and laboratory decisions in Pakistan from Envi Tech AL.';
    }

    if (is_singular('post')) {
        if (get_post_field('post_name', get_the_ID()) === 'gaseous-air-emission-testing-lab-near-me') {
            return 'Envi Tech AL provides gaseous and stack emission testing for boilers, generators, industrial chimneys and process exhausts in Karachi and Lahore, with reporting support for defined regulatory requirements.';
        }

        return eta_modern_post_meta_description(get_the_ID());
    }

    if (is_post_type_archive('services')) {
        return 'Environmental testing, water and wastewater analysis, calibration, consultancy, ballast water testing and monitoring services by Envi Tech AL in Karachi and Lahore.';
    }

    if (is_singular('services')) {
        if (get_post_field('post_name', get_the_ID()) === 'water-testing-lab-services') {
            return 'Water testing laboratory services in Karachi and Lahore for drinking water, wastewater, process and RO water, with scope-confirmed methods and verifiable reports.';
        }

        return eta_modern_service_profile_value(get_post_field('post_name', get_the_ID()), 'seo_description', eta_modern_plain_excerpt(get_the_ID(), 26));
    }

    return '';
}

function eta_modern_clamp_meta_description($description, $max = 220)
{
    $description = trim(eta_modern_preg_replace('/\s+/', ' ', wp_strip_all_tags((string) $description)));
    if ($description === '') {
        return '';
    }

    $length = function_exists('mb_strlen') ? mb_strlen($description) : strlen($description);
    if ($length <= $max) {
        return $description;
    }

    $slice = function_exists('mb_substr') ? mb_substr($description, 0, $max - 1) : substr($description, 0, $max - 1);
    $slice = preg_replace('/\s+\S*$/', '', $slice);
    $slice = rtrim($slice, " \t\n\r\0\x0B,;:-");

    return $slice . '.';
}

function eta_modern_services_item_list_schema()
{
    $services = [
        ['Environmental Consultancy', '/services/environmental-consultancy/'],
        ['Certification Advisory', '/services/certification-advisory/'],
        ['Technical Advisory', '/services/technical-advisory-2/'],
        ['Analytical Lab Services', '/services/analytical-lab-services/'],
        ['Water Testing Services', '/services/water-testing-lab-services/'],
        ['Equipment Calibration', '/services/equipment-calibration-services/'],
        ['Environmental Advisory', '/services/environmental-advisory/'],
        ['Ballast Water Testing', '/services/ballast-water-testing-services/'],
        ['Thermal Imaging Inspection', '/services/thermal-imaging-inspection/'],
    ];

    return [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'Envi Tech AL Services',
        'url' => home_url('/services/'),
        'itemListElement' => array_map(function ($service, $index) {
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $service[0],
                'url' => home_url($service[1]),
            ];
        }, $services, array_keys($services)),
    ];
}

function eta_modern_rank_math_active()
{
    return defined('RANK_MATH_VERSION') || class_exists('RankMath');
}

function eta_modern_schema_organization()
{
    return [
        '@type' => 'Organization',
        '@id' => home_url('/#organization'),
        'name' => 'Envi Tech AL',
        'url' => home_url('/'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => 'https://envitechal.com/wp-content/uploads/2026/06/envitechal-logo-header-hq-transparent.png',
        ],
        'image' => eta_modern_default_share_image(),
        'email' => 'info@envitechal.com',
        'telephone' => ['+923102288801', '+923152006074', '+924232296099'],
        'sameAs' => [
            'https://www.facebook.com/envitechal',
            'https://www.linkedin.com/company/envitech-al',
            'https://twitter.com/al_envi',
            'https://www.instagram.com/envitech2026/',
            'https://www.youtube.com/channel/UC4C6CEHceAOGuzmSX_t7CpQ',
        ],
        'subOrganization' => [
            ['@id' => home_url('/#karachi-lab')],
            ['@id' => home_url('/#lahore-lab')],
        ],
    ];
}

function eta_modern_schema_local_business($location, $description = '')
{
    $branches = [
        'karachi' => [
            '@id' => home_url('/#karachi-lab'),
            'name' => 'Envi Tech AL Karachi Environmental Laboratory',
            'url' => home_url('/karachi-environmental-lab/'),
            'telephone' => '+923102288801',
            'description' => 'Environmental testing, field monitoring, and consultancy coordination for Karachi and Sindh. Confirm the laboratory location, parameter, method, and current credential before relying on an accreditation claim.',
            'areaServed' => ['Karachi', 'Sindh', 'Pakistan'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS',
                'addressLocality' => 'Karachi',
                'addressRegion' => 'Sindh',
                'postalCode' => '75900',
                'addressCountry' => 'PK',
            ],
        ],
        'lahore' => [
            '@id' => home_url('/#lahore-lab'),
            'name' => 'Envi Tech AL Lahore Environmental Laboratory',
            'url' => home_url('/lahore-environmental-lab/'),
            'telephone' => '+924232296099',
            'description' => 'Environmental water and wastewater testing for Lahore and Punjab. PNAC LAB-347 applies only to the Lahore laboratory and the methods listed in its published scope.',
            'areaServed' => ['Lahore', 'Punjab', 'Pakistan'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '87-E Madina Heights, Office A/30-31, 8th Floor, Maulana Shaukat Ali Road, Johar Town',
                'addressLocality' => 'Lahore',
                'addressRegion' => 'Punjab',
                'addressCountry' => 'PK',
            ],
        ],
    ];

    if (!isset($branches[$location])) {
        return [];
    }

    $branch = $branches[$location];
    return array_merge([
        '@type' => 'LocalBusiness',
        'parentOrganization' => ['@id' => home_url('/#organization')],
        'logo' => [
            '@type' => 'ImageObject',
            'url' => 'https://envitechal.com/wp-content/uploads/2026/06/envitechal-logo-header-hq-transparent.png',
        ],
        'image' => eta_modern_default_share_image(),
        'email' => 'info@envitechal.com',
        'description' => $description ?: eta_modern_meta_description(),
        'priceRange' => 'Quotation-based',
        'makesOffer' => [
        ['@type' => 'Offer', 'name' => 'Environmental Lab & Analytical Services'],
        ['@type' => 'Offer', 'name' => 'Water Testing Lab Services'],
        ['@type' => 'Offer', 'name' => 'Equipment Calibration'],
        ['@type' => 'Offer', 'name' => 'Environmental Consultancy'],
        ['@type' => 'Offer', 'name' => 'Ballast Water Testing Services'],
        ],
    ], $branch);
}

function eta_modern_schema_faq_page($faqs, $name = '')
{
    $entities = [];
    foreach ((array) $faqs as $faq) {
        $question = is_array($faq) && isset($faq['question']) ? $faq['question'] : ($faq[0] ?? '');
        $answer = is_array($faq) && isset($faq['answer']) ? $faq['answer'] : ($faq[1] ?? '');
        if ($question === '' || $answer === '') {
            continue;
        }

        $entities[] = [
            '@type' => 'Question',
            'name' => wp_strip_all_tags($question),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => wp_strip_all_tags($answer),
            ],
        ];
    }

    if (!$entities) {
        return null;
    }

    return [
        '@type' => 'FAQPage',
        '@id' => get_permalink() . '#faq',
        'name' => $name ?: eta_modern_display_title(get_the_ID()),
        'url' => get_permalink(),
        'mainEntity' => $entities,
    ];
}

function eta_modern_emr_emp_faqs()
{
    return [
        [
            'question' => 'What is an Environmental Monitoring Report (EMR/EMP)?',
            'answer' => 'An Environmental Monitoring Report documents the monitoring completed against an approved EMP, including sampling scope, parameters, laboratory results, field evidence, deviations, corrective actions, and compliance status.',
        ],
        [
            'question' => 'When is an EMR or EMP report needed for Sindh EPA?',
            'answer' => 'Facilities usually need EMR or EMP reporting when an approval, NOC, renewal, audit, inspection, buyer requirement, or environmental management commitment asks for proof that monitoring and mitigation controls are being followed.',
        ],
        [
            'question' => 'Which parameters are normally checked for EMR/EMP reporting?',
            'answer' => 'The scope depends on the project and approval conditions, but it often includes wastewater, drinking water, ambient air, stack emissions, noise, soil, waste handling, occupational exposure, and site-specific mitigation records.',
        ],
        [
            'question' => 'Can Envi Tech AL support EMR/EMP monitoring and reporting?',
            'answer' => 'Yes. Envi Tech AL can help define the monitoring scope, collect samples, perform laboratory testing, review evidence, and prepare reporting support for EPA, audit, buyer, or internal compliance use.',
        ],
    ];
}

function eta_modern_emr_emp_howto_schema($post = null)
{
    if (!eta_modern_is_emr_emp_post($post)) {
        return null;
    }

    return [
        '@type' => 'HowTo',
        '@id' => get_permalink($post) . '#howto',
        'name' => 'How to prepare an Environmental Monitoring Report (EMR/EMP) for Sindh EPA',
        'description' => 'A practical workflow for preparing EMR/EMP monitoring evidence, lab reports, and compliance documentation for EPA-related review.',
        'totalTime' => 'P7D',
        'supply' => [
            ['@type' => 'HowToSupply', 'name' => 'Approved EMP, NOC conditions, or monitoring requirement'],
            ['@type' => 'HowToSupply', 'name' => 'Sampling plan, site photographs, field records, and chain-of-custody evidence'],
            ['@type' => 'HowToSupply', 'name' => 'Laboratory test reports and corrective action records'],
        ],
        'tool' => [
            ['@type' => 'HowToTool', 'name' => 'Environmental monitoring instruments and accredited laboratory methods'],
            ['@type' => 'HowToTool', 'name' => 'Compliance review checklist'],
        ],
        'step' => [
            [
                '@type' => 'HowToStep',
                'position' => 1,
                'name' => 'Confirm the approved monitoring scope',
                'text' => 'Review the approval, NOC, EMP, buyer requirement, or audit checklist so parameters, locations, frequency, and reporting format are clear before field work starts.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 2,
                'name' => 'Plan sampling and field evidence',
                'text' => 'Prepare sampling locations, containers, preservation, calibration status, site photographs, observations, and chain-of-custody records before visiting the facility.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 3,
                'name' => 'Complete monitoring and laboratory testing',
                'text' => 'Collect samples, record field conditions, test the required parameters, and verify results against relevant limits or project-specific conditions.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 4,
                'name' => 'Review compliance and corrective actions',
                'text' => 'Compare results with Sindh EPA, EMP, buyer, or internal requirements, then document any exceedance, root cause, action taken, and follow-up monitoring need.',
            ],
            [
                '@type' => 'HowToStep',
                'position' => 5,
                'name' => 'Assemble the final EMR/EMP package',
                'text' => 'Compile the executive summary, monitoring tables, laboratory reports, site evidence, corrective actions, conclusion, and annexures into a clear review package.',
            ],
        ],
    ];
}

function eta_modern_schema_breadcrumbs()
{
    if (is_front_page()) {
        return null;
    }

    $items = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => home_url('/'),
        ],
    ];

    if (is_singular('services')) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Services',
            'item' => home_url('/services/'),
        ];
    } elseif (is_singular('post')) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Knowledge Hub',
            'item' => home_url('/blognewsupdates/'),
        ];
    }

    $items[] = [
        '@type' => 'ListItem',
        'position' => count($items) + 1,
        'name' => is_post_type_archive('services') ? 'Services' : eta_modern_display_title(get_the_ID()),
        'item' => is_post_type_archive('services') ? home_url('/services/') : get_permalink(),
    ];

    return [
        '@type' => 'BreadcrumbList',
        '@id' => trailingslashit(is_post_type_archive('services') ? home_url('/services/') : get_permalink()) . '#breadcrumb',
        'itemListElement' => $items,
    ];
}

function eta_modern_rank_math_schema_templates($data)
{
    if (is_admin()) {
        return $data;
    }

    $description = eta_modern_clamp_meta_description(eta_modern_meta_description());
    if (!$description) {
        return $data;
    }

    $schema = [
        'eta-organization' => eta_modern_schema_organization(),
        'eta-karachi-branch' => eta_modern_schema_local_business('karachi', $description),
        'eta-lahore-branch' => eta_modern_schema_local_business('lahore', $description),
        'eta-website' => [
            '@type' => 'WebSite',
            '@id' => home_url('/#website'),
            'name' => 'Envi Tech AL',
            'url' => home_url('/'),
            'publisher' => ['@id' => home_url('/#organization')],
            'inLanguage' => 'en-PK',
        ],
    ];

    if (is_front_page()) {
        $schema['eta-homepage'] = [
            '@type' => 'WebPage',
            '@id' => home_url('/#webpage'),
            'name' => 'Envi Tech AL',
            'url' => home_url('/'),
            'description' => $description,
            'isPartOf' => ['@id' => home_url('/#website')],
            'about' => ['@id' => home_url('/#organization')],
        ];
    } elseif (is_page('aboutus')) {
        $schema['eta-about-page'] = [
            '@type' => 'AboutPage',
            '@id' => get_permalink() . '#webpage',
            'name' => 'About Envi Tech AL',
            'url' => get_permalink(),
            'description' => $description,
            'isPartOf' => ['@id' => home_url('/#website')],
            'about' => ['@id' => home_url('/#organization')],
        ];
    } elseif (is_page(['karachi-environmental-lab', 'lahore-environmental-lab'])) {
        $location = is_page('karachi-environmental-lab') ? 'karachi' : 'lahore';
        $branch_id = home_url('/#' . $location . '-lab');
        $schema['eta-location-page'] = [
            '@type' => 'WebPage',
            '@id' => get_permalink() . '#webpage',
            'name' => eta_modern_display_title(get_the_ID()),
            'url' => get_permalink(),
            'description' => $description,
            'isPartOf' => ['@id' => home_url('/#website')],
            'about' => ['@id' => $branch_id],
            'mainEntity' => ['@id' => $branch_id],
        ];
        $faq_schema = eta_modern_schema_faq_page(eta_modern_location_faqs($location), eta_modern_display_title(get_the_ID()) . ' FAQ');
        if ($faq_schema) {
            $schema['eta-location-faq'] = $faq_schema;
        }
    } elseif (is_page(['certificates-approvals', 'accreditations-certifications'])) {
        $schema['eta-certifications-page'] = [
            '@type' => 'WebPage',
            '@id' => get_permalink() . '#webpage',
            'name' => 'Certifications, Approvals & Laboratory Quality Credentials',
            'url' => get_permalink(),
            'description' => $description,
            'isPartOf' => ['@id' => home_url('/#website')],
            'about' => ['@id' => home_url('/#organization')],
        ];
    } elseif (is_post_type_archive('services')) {
        $schema['eta-services-item-list'] = eta_modern_services_item_list_schema();
    } elseif (is_singular('services')) {
        $slug = get_post_field('post_name', get_the_ID());
        $profile = eta_modern_service_profile($slug);
        $schema['eta-service'] = [
            '@type' => 'Service',
            '@id' => get_permalink() . '#service',
            'name' => eta_modern_display_title(get_the_ID()),
            'url' => get_permalink(),
            'description' => $description,
            'serviceType' => $profile['category'],
            'provider' => ['@id' => home_url('/#organization')],
            'areaServed' => ['Karachi, Sindh, Pakistan', 'Lahore, Punjab, Pakistan'],
        ];
        $faq_schema = eta_modern_schema_faq_page($slug === 'water-testing-lab-services' ? eta_modern_water_testing_faqs() : eta_modern_service_faqs($slug), eta_modern_display_title(get_the_ID()) . ' FAQ');
        if ($faq_schema) {
            $schema['eta-service-faq'] = $faq_schema;
        }
    } elseif (is_singular('post')) {
        $post_id = get_the_ID();
        $schema['eta-article'] = [
            '@type' => 'Article',
            '@id' => get_permalink() . '#article',
            'headline' => eta_modern_display_title($post_id),
            'description' => $description,
            'url' => get_permalink(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => ['@id' => home_url('/#organization')],
            'publisher' => ['@id' => home_url('/#organization')],
            'mainEntityOfPage' => get_permalink(),
            'image' => eta_modern_post_image_url($post_id),
        ];
        $faq_schema = eta_modern_is_emr_emp_post($post_id) ? eta_modern_schema_faq_page(eta_modern_emr_emp_faqs(), 'Environmental Monitoring Report (EMR/EMP) FAQ') : null;
        if ($faq_schema) {
            $schema['eta-post-faq'] = $faq_schema;
        }
        $howto_schema = eta_modern_emr_emp_howto_schema($post_id);
        if ($howto_schema) {
            $schema['eta-post-howto'] = $howto_schema;
        }
    } elseif (is_page('frequently-asked-questions-water-testing-in-karachi')) {
        $faq_schema = eta_modern_schema_faq_page(eta_modern_faq_page_questions(), 'Environmental Testing FAQ');
        if ($faq_schema) {
            $schema['eta-faq-page'] = $faq_schema;
        }
    } elseif (is_page('environmental-testing-faqs-pakistan')) {
        $faq_schema = eta_modern_schema_faq_page(eta_modern_ai_faq_center_questions(), 'Environmental Testing FAQs Pakistan');
        if ($faq_schema) {
            $schema['eta-ai-faq-center'] = $faq_schema;
        }
    } elseif (is_page() && eta_modern_cluster_page_data(get_post_field('post_name', get_the_ID()))) {
        $cluster = eta_modern_cluster_page_data(get_post_field('post_name', get_the_ID()));
        $schema['eta-cluster-service'] = [
            '@type' => 'Service',
            '@id' => get_permalink() . '#service',
            'name' => $cluster['title'],
            'url' => get_permalink(),
            'description' => $description,
            'provider' => ['@id' => home_url('/#organization')],
            'areaServed' => ['Karachi, Sindh, Pakistan', 'Lahore, Punjab, Pakistan'],
        ];
        $faq_schema = eta_modern_schema_faq_page($cluster['faqs'], $cluster['title'] . ' FAQ');
        if ($faq_schema) {
            $schema['eta-cluster-faq'] = $faq_schema;
        }
    } elseif (is_page('sindh-environmental-quality-standards-seqs')) {
        $schema['eta-seqs-article'] = [
            '@type' => 'Article',
            '@id' => get_permalink() . '#article',
            'headline' => 'Sindh Environmental Quality Standards (SEQS): Limits, Parameters and Compliance',
            'description' => $description,
            'url' => get_permalink(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => ['@id' => home_url('/#organization')],
            'publisher' => ['@id' => home_url('/#organization')],
            'mainEntityOfPage' => get_permalink(),
            'image' => eta_modern_default_share_image(),
        ];
        $faq_schema = eta_modern_schema_faq_page(eta_modern_seqs_faqs(), 'SEQS FAQ');
        if ($faq_schema) {
            $schema['eta-seqs-faq'] = $faq_schema;
        }
    } else {
        $schema['eta-webpage'] = [
            '@type' => 'WebPage',
            '@id' => get_permalink() . '#webpage',
            'name' => is_page() ? eta_modern_display_title(get_the_ID()) : wp_get_document_title(),
            'url' => is_page() ? get_permalink() : home_url(add_query_arg([], $GLOBALS['wp']->request ?? '')),
            'description' => $description,
            'isPartOf' => ['@id' => home_url('/#website')],
        ];
    }

    $breadcrumbs = eta_modern_schema_breadcrumbs();
    if ($breadcrumbs) {
        $schema['eta-breadcrumbs'] = $breadcrumbs;
    }

    return $schema;
}

add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }

    if (is_front_page()) {
        $hero_base = get_stylesheet_directory_uri() . '/assets/images/';
        printf(
            '<link rel="preload" as="image" href="%1$s" imagesrcset="%2$s 520w, %3$s 900w, %4$s 1500w" imagesizes="(max-width: 640px) 520px, 1180px">' . "\n",
            esc_url($hero_base . 'eta-home-hero-900.webp'),
            esc_url($hero_base . 'eta-home-hero-520.webp'),
            esc_url($hero_base . 'eta-home-hero-900.webp'),
            esc_url($hero_base . 'eta-home-hero-1500.webp')
        );
    }

    $description = eta_modern_clamp_meta_description(eta_modern_meta_description());
    if (!$description) {
        return;
    }

    if (eta_modern_rank_math_active()) {
        return;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => array_values(eta_modern_rank_math_schema_templates([])),
    ];

    $page_schema = null;
    $extra_page_schema = [];
    if (is_page('report-verification-portal')) {
        $page_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Report Verification Portal',
            'url' => get_permalink(),
            'description' => $description,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Envi Tech AL',
                'url' => home_url('/'),
            ],
            'about' => [
                '@type' => 'Thing',
                'name' => 'Laboratory report verification',
            ],
        ];
    } elseif (is_page('sindh-environmental-quality-standards-seqs')) {
        $page_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => 'Sindh Environmental Quality Standards (SEQS): Limits, Parameters and Compliance',
            'description' => $description,
            'url' => get_permalink(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => [
                '@type' => 'Organization',
                'name' => 'Envi Tech AL',
                'url' => home_url('/'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Envi Tech AL',
                'url' => home_url('/'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => 'https://envitechal.com/wp-content/uploads/2026/06/envitechal-logo-header-hq-transparent.png',
                ],
            ],
            'mainEntityOfPage' => get_permalink(),
            'image' => eta_modern_default_share_image(),
            'about' => [
                '@type' => 'Thing',
                'name' => 'Sindh Environmental Quality Standards',
            ],
        ];
        $extra_page_schema[] = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq[0],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq[1],
                    ],
                ];
            }, eta_modern_seqs_faqs()),
        ];
    } elseif (is_page('frequently-asked-questions-water-testing-in-karachi')) {
        $page_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'name' => 'Environmental Testing FAQ',
            'url' => get_permalink(),
            'description' => $description,
            'mainEntity' => array_map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer'],
                    ],
                ];
            }, eta_modern_faq_page_questions()),
        ];
    } elseif (is_page(['karachi-environmental-lab', 'lahore-environmental-lab'])) {
        $faqs = is_page('karachi-environmental-lab') ? eta_modern_location_faqs('karachi') : eta_modern_location_faqs('lahore');
        $page_schema = [
            '@context' => 'https://schema.org',
            '@type' => ['WebPage', 'FAQPage'],
            'name' => eta_modern_display_title(get_the_ID()),
            'url' => get_permalink(),
            'description' => $description,
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Home',
                        'item' => home_url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => eta_modern_display_title(get_the_ID()),
                        'item' => get_permalink(),
                    ],
                ],
            ],
            'mainEntity' => array_map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq[0],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq[1],
                    ],
                ];
            }, $faqs),
            'about' => [
                '@type' => 'Service',
                'name' => is_page('karachi-environmental-lab') ? 'Environmental Testing Lab in Karachi' : 'Environmental Testing Lab in Lahore',
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'Envi Tech AL',
                    'url' => home_url('/'),
                ],
                'areaServed' => is_page('karachi-environmental-lab') ? 'Karachi, Sindh, Pakistan' : 'Lahore, Punjab, Pakistan',
            ],
        ];
    } elseif (is_page('certificates-approvals')) {
        $page_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Certificates, Approvals & Laboratory Credentials',
            'url' => get_permalink(),
            'description' => $description,
            'about' => [
                '@type' => 'Organization',
                'name' => 'Envi Tech AL',
                'url' => home_url('/'),
            ],
            'mainEntity' => [
                '@type' => 'ItemList',
                'name' => 'Envi Tech AL credentials',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'PNAC LAB-347 Lahore laboratory accreditation scope'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Sindh EPA document requiring current confirmation'],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => 'Punjab EPA approval information'],
                    ['@type' => 'ListItem', 'position' => 4, 'name' => 'ISO 9001:2015 and ISO 14001:2015 system certificates'],
                ],
            ],
        ];
    } elseif (is_page() && eta_modern_cluster_page_data(get_post_field('post_name', get_the_ID()))) {
        $cluster = eta_modern_cluster_page_data(get_post_field('post_name', get_the_ID()));
        $page_schema = [
            '@context' => 'https://schema.org',
            '@type' => ['WebPage', 'FAQPage'],
            'name' => $cluster['title'],
            'url' => get_permalink(),
            'description' => $description,
            'about' => [
                '@type' => 'Service',
                'name' => $cluster['title'],
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'Envi Tech AL',
                    'url' => home_url('/'),
                ],
                'areaServed' => ['Karachi, Sindh, Pakistan', 'Lahore, Punjab, Pakistan'],
            ],
            'mainEntity' => array_map(function ($faq) {
                return [
                    '@type' => 'Question',
                    'name' => $faq[0],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq[1],
                    ],
                ];
            }, $cluster['faqs']),
        ];
    } elseif (is_post_type_archive('services')) {
        $page_schema = eta_modern_services_item_list_schema();
    } elseif (is_page(['blognewsupdates', 'newsupdates']) || is_home() || is_archive()) {
        $page_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Envi Tech AL Knowledge Hub',
            'url' => get_permalink(),
            'description' => $description,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Envi Tech AL',
                'url' => home_url('/'),
            ],
        ];
    } elseif (is_singular('services')) {
        $slug = get_post_field('post_name', get_the_ID());
        $profile = eta_modern_service_profile($slug);
        if ($slug === 'water-testing-lab-services') {
            $page_schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'serviceType' => 'Water Testing Laboratory Services',
                'name' => 'Water Testing Laboratory Services',
                'description' => 'Water and wastewater testing with scope-confirmed methods and verifiable reports. PNAC LAB-347 applies only to the Lahore location and its listed methods.',
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'Envi Tech AL',
                    'url' => 'https://envitechal.com/',
                    'logo' => 'https://envitechal.com/wp-content/uploads/2026/06/envitechal-logo-header-hq-transparent.png',
                    'telephone' => '+92-310-2288801',
                    'email' => 'info@envitechal.com',
                ],
                'areaServed' => [
                    ['@type' => 'City', 'name' => 'Karachi'],
                    ['@type' => 'City', 'name' => 'Lahore'],
                    ['@type' => 'Country', 'name' => 'Pakistan'],
                ],
                'url' => 'https://envitechal.com/services/water-testing-lab-services/',
            ];
            $extra_page_schema[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(function ($faq) {
                    return [
                        '@type' => 'Question',
                        'name' => $faq[0],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $faq[1],
                        ],
                    ];
                }, eta_modern_water_testing_faqs()),
            ];
        } else {
            $page_schema = [
                '@context' => 'https://schema.org',
                '@type' => ['Service', 'FAQPage'],
                'name' => eta_modern_display_title(get_the_ID()),
                'url' => get_permalink(),
                'description' => $description,
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'Envi Tech AL',
                    'url' => home_url('/'),
                ],
                'areaServed' => ['Karachi, Sindh, Pakistan', 'Lahore, Punjab, Pakistan'],
                'serviceType' => $profile['category'],
                'mainEntity' => array_map(function ($faq) {
                    return [
                        '@type' => 'Question',
                        'name' => $faq[0],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $faq[1],
                        ],
                    ];
                }, eta_modern_service_faqs($slug)),
            ];
        }
    } elseif (is_singular('post')) {
        $page_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => eta_modern_display_title(get_the_ID()),
            'description' => $description,
            'url' => get_permalink(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => [
                '@type' => 'Organization',
                'name' => 'Envi Tech AL',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Envi Tech AL',
                'url' => home_url('/'),
            ],
            'mainEntityOfPage' => get_permalink(),
            'image' => eta_modern_post_image_url(get_the_ID()),
        ];
    }
    ?>
    <meta name="description" content="<?php echo esc_attr($description); ?>">
    <?php if (is_singular('services') && get_post_field('post_name', get_the_ID()) === 'water-testing-lab-services') : ?>
        <meta property="og:title" content="Water Testing Laboratory in Karachi &amp; Lahore | Envi Tech AL">
        <meta property="og:image" content="https://envitechal.com/wp-content/uploads/2026/05/water-testing-services-karachi-lahore.png">
    <?php endif; ?>
    <script type="application/ld+json"><?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    <?php
}, 12);

add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }
    ?>
    <script id="eta-modern-mobile-menu-fallback" data-no-optimize="1" data-no-defer="1" data-cfasync="false">
        (function () {
            function onReady(callback) {
                if (document.readyState !== 'loading') {
                    callback();
                    return;
                }

                document.addEventListener('DOMContentLoaded', callback, { once: true });
            }

            onReady(function () {
                var control = document.getElementById('mobile-menu-control-wrapper');
                var button = (control && control.querySelector('.menu-toggle')) || document.querySelector('.menu-toggle[aria-controls="primary-menu"]');
                var nav = document.getElementById('site-navigation') || document.querySelector('.main-navigation:not(#mobile-menu-control-wrapper)');
                var menu = document.getElementById('primary-menu');
                var mobileQuery = window.matchMedia ? window.matchMedia('(max-width: 1024px)') : null;

                if (!button || !nav || !menu || button.dataset.etaMenuFallback === '1') {
                    return;
                }

                button.dataset.etaMenuFallback = '1';
                if (!button.hasAttribute('aria-controls')) {
                    button.setAttribute('aria-controls', 'primary-menu');
                }
                button.setAttribute('aria-expanded', 'false');

                function isMobile() {
                    return !mobileQuery || mobileQuery.matches;
                }

                function setOpen(isOpen) {
                    button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    button.classList.toggle('toggled', isOpen);
                    nav.classList.toggle('toggled', isOpen);
                    if (control) {
                        control.classList.toggle('toggled', isOpen);
                    }
                    document.body.classList.toggle('eta-mobile-menu-open', isOpen);

                    if (isMobile()) {
                        menu.style.display = isOpen ? 'block' : '';
                    }
                }

                button.addEventListener('click', function (event) {
                    if (!isMobile()) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    setOpen(button.getAttribute('aria-expanded') !== 'true');
                }, true);

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') {
                        setOpen(false);
                        button.focus();
                    }
                });

                document.addEventListener('click', function (event) {
                    if (!isMobile() || button.getAttribute('aria-expanded') !== 'true') {
                        return;
                    }

                    if (nav.contains(event.target) || button.contains(event.target) || (control && control.contains(event.target))) {
                        return;
                    }

                    setOpen(false);
                });

                window.addEventListener('resize', function () {
                    if (!isMobile()) {
                        setOpen(false);
                        menu.style.display = '';
                        document.body.classList.remove('eta-mobile-menu-open');
                    }
                });
            });
        }());
    </script>
    <?php
}, 5);

add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }
    ?>
    <div class="eta-chatbot-root">
        <a id="eta-chatbot-launcher" class="eta-chatbot-launcher" href="https://wa.me/923102288801" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Chat with Envi Tech AL on WhatsApp', 'envi-tech-al-modern'); ?>" title="<?php esc_attr_e('Chat with Envi Tech AL on WhatsApp', 'envi-tech-al-modern'); ?>">
            <span class="screen-reader-text"><?php esc_html_e('Chat with Envi Tech AL on WhatsApp', 'envi-tech-al-modern'); ?></span>
        </a>
    </div>
    <?php
}, 100);

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_editor_style('assets/css/eta-editor.css');

    register_nav_menus([
        'eta-footer' => __('Footer Links', 'envi-tech-al-modern'),
    ]);
});

add_action('init', function () {
    if (wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    if (!get_page_by_path('sindh-environmental-quality-standards-seqs')) {
        wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Sindh Environmental Quality Standards (SEQS): Limits, Parameters and Compliance',
            'post_name' => 'sindh-environmental-quality-standards-seqs',
            'post_content' => 'SEQS compliance hub rendered by the GeneratePress child theme.',
            'post_excerpt' => eta_modern_seqs_meta_description(),
        ]);
    }
}, 9);

add_action('init', function () {
    if (post_type_exists('services')) {
        return;
    }

    register_post_type('services', [
        'labels' => [
            'name' => __('Services', 'envi-tech-al-modern'),
            'singular_name' => __('Service', 'envi-tech-al-modern'),
            'add_new_item' => __('Add New Service', 'envi-tech-al-modern'),
            'edit_item' => __('Edit Service', 'envi-tech-al-modern'),
            'new_item' => __('New Service', 'envi-tech-al-modern'),
            'view_item' => __('View Service', 'envi-tech-al-modern'),
            'search_items' => __('Search Services', 'envi-tech-al-modern'),
            'not_found' => __('No services found', 'envi-tech-al-modern'),
            'not_found_in_trash' => __('No services found in Trash', 'envi-tech-al-modern'),
            'all_items' => __('All Services', 'envi-tech-al-modern'),
            'menu_name' => __('Services', 'envi-tech-al-modern'),
        ],
        'public' => true,
        'has_archive' => 'services',
        'rewrite' => [
            'slug' => 'services',
            'with_front' => false,
        ],
        'menu_position' => 20,
        'menu_icon' => 'dashicons-clipboard',
        'show_in_rest' => true,
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
    ]);
}, 1);

add_filter('generatepress_option_defaults', function ($defaults) {
    $defaults['container_width'] = 1180;
    $defaults['header_layout_setting'] = 'contained-header';
    $defaults['nav_layout_setting'] = 'contained-nav';
    $defaults['layout_setting'] = 'right-sidebar';
    $defaults['blog_layout_setting'] = 'right-sidebar';
    $defaults['single_layout_setting'] = 'right-sidebar';
    return $defaults;
});

add_action('send_headers', function () {
    if (!eta_modern_is_staging_host()) {
        return;
    }

    header('X-Robots-Tag: noindex, nofollow, noarchive', true);
});

add_filter('robots_txt', function ($output, $public) {
    if (!eta_modern_is_staging_host()) {
        return $output;
    }

    return "User-agent: *\nDisallow: /\n";
}, 20, 2);

add_action('wp_enqueue_scripts', function () {
    if (is_admin() || eta_modern_allow_legacy_builder_assets()) {
        return;
    }

    $legacy_styles = [
        'js_composer_front',
        'mowasalat_framework_frontend',
        'mowasalat_framework_heading',
        'xmenu-menu-amination',
        'sr7css',
    ];

    $legacy_scripts = [
        'wpb_composer_front_js',
        'vc_jquery_skrollr_js',
        'vc_accordion_script',
        'vc_tta_autoplay_script',
        'xmenu-menu-js',
        'tp-tools',
        'sr7',
    ];

    foreach ($legacy_styles as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }

    foreach ($legacy_scripts as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
}, 999);

add_filter('wp_resource_hints', function ($urls, $relation_type) {
    if (is_admin() || eta_modern_allow_legacy_builder_assets()) {
        return $urls;
    }

    return array_values(array_filter($urls, function ($url) {
        $url = is_array($url) && isset($url['href']) ? $url['href'] : $url;
        return is_string($url) && strpos($url, 'g5plus-mowasalat') === false;
    }));
}, 20, 2);

function eta_modern_preg_replace($pattern, $replacement, $subject, $limit = -1)
{
    $result = \preg_replace($pattern, $replacement, $subject, $limit);
    return $result === null ? $subject : $result;
}

function eta_modern_is_rest_like_request()
{
    if ((defined('REST_REQUEST') && REST_REQUEST) || wp_doing_ajax()) {
        return true;
    }

    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    if ($uri !== '' && (strpos($uri, '/wp-json/') !== false || strpos($uri, 'rest_route=') !== false)) {
        return true;
    }

    return isset($_GET['rest_route']);
}

add_action('init', function () {
    if (is_admin() || eta_modern_is_rest_like_request() || eta_modern_is_admin_request()) {
        return;
    }

    ob_start(function ($html) {
        if (eta_modern_is_rest_like_request()) {
            return $html;
        }

        if (eta_modern_allow_legacy_builder_assets()) {
            return $html;
        }

        $original_html = is_string($html) ? $html : '';

        $html = eta_modern_preg_replace(
            '#<script\b[^>]*(?:mowasalat-framework/core/xmenu/assets/js/app\.min\.js)[^>]*>\s*</script>#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '#<script\b[^>]*(?:wp-content/plugins/js_composer/[^"\']+|id=["\'](?:vc_accordion_script-js|vc_tta_autoplay_script-js|vc_jquery_skrollr_js-js)["\'])[^>]*>\s*</script>#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '#<style\b[^>]*(?:data-type=["\']vc_shortcodes[^"\']*["\']|class=["\']options-output["\']|title=["\']dynamic-css["\'])[^>]*>[\s\S]*?</style>\s*#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '#<link\b[^>]+href=["\'](?:https?:)?//(?:fonts\.googleapis\.com|fonts\.gstatic\.com|ajax\.googleapis\.com|cdn\.shortpixel\.ai)[^"\']*["\'][^>]*>\s*#i',
            '',
            $html
        );

        $shortpixel_rewrite = preg_replace_callback(
            '#https://cdn\.shortpixel\.ai/spai/[^"\')\s]*/(?:staging\.)?envitechal\.com(/wp-content/uploads/[^"\')\s?]+)(?:\?[^"\')\s]*)?#i',
            function ($matches) {
                return home_url($matches[1]);
            },
            $html
        );
        if (is_string($shortpixel_rewrite)) {
            $html = $shortpixel_rewrite;
        }

        $html = eta_modern_preg_replace(
            '#<noscript>\s*<style>\s*\.wpb_animate_when_almost_visible[\s\S]*?</style>\s*</noscript>\s*#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '#<script\b[^>]*(?:wp-content/plugins/wp-user-avatar/|wp-content/plugins/mailin/)[^>]*>\s*</script>\s*#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '#<script\b[^>]*id=["\']codex-[^"\']*["\'][\s\S]*?</script>\s*#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '#<style\b[^>]*id=["\']codex-[^"\']*["\'][\s\S]*?</style>\s*#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '#<sr7-module\b[^>]*\bdata-id=["\']ERROR["\'][\s\S]*?</sr7-module>\s*<script\b[^>]*>[\s\S]*?SR7_ERROR_[\s\S]*?</script>#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '#<script\b[^>]*>[\s\S]*?(?:SR7\.E\.plugin_url|revslider/revslider\.php|wp-content/plugins/revslider/)[\s\S]*?</script>#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '#<link\b[^>]*(?:g5plus-mowasalat/assets/plugins/fonts-awesome/fonts/fontawesome-webfont\.woff2)[^>]*>#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '#<link\b[^>]*(?:mowasalat-framework/shortcodes/heading/assets/css/heading\.min\.css)[^>]*>#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '~@font-face\{[^{}]*g5plus-mowasalat/assets/plugins/(?:fonts-awesome|flaticon|light-gallery)/[^{}]*\}~i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '~@font-face\s*\{[^{}]*(?:wp-content/plugins/js_composer|vcpb-plugin-icons|vc_grid_v1)[^{}]*\}~i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '~@font-face\s*\{.*?(?:wp-content/plugins/js_composer|vcpb-plugin-icons|vc_grid_v1).*?\}~is',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '~url\(["\']?https?://[^)"\']*wp-content/plugins/js_composer/[^)"\']*["\']?\)~i',
            'none',
            $html
        );

        $html = eta_modern_preg_replace(
            '#\[(?:/?vc_[^\]]*|/?g5plus_[^\]]*|rev_slider[^\]]*)\]#i',
            '',
            $html
        );

        $html = eta_modern_preg_replace(
            '/\s(?:wpb-js-composer|js-comp-ver-[^\s"\']+|vc_responsive)\b/i',
            '',
            $html
        );

        $html = str_replace(
            [' x-nav-menu', 'x-nav-menu_', 'x-animate-slide-right', 'g5plus-contact-form'],
            ['', 'eta-menu-', '', 'eta-cf7-form'],
            $html
        );

        $html = str_replace('codex-', 'eta-modern-', $html);

        $html = str_replace(
            [
                ',".rev_slider_wrapper .tp-revslider-slidesli:nth-child(1) .tp-bgimg":{"lazy":0,"cdn":1,"resize":1,"lqip":0,"crop":0}',
                ',"path:\/revslider\/public\/assets\/assets\/transparent.png":{"lazy":0,"cdn":0,"resize":0,"lqip":0,"crop":-1}',
            ],
            '',
            $html
        );

        if (false === stripos($html, '<title') && false !== stripos($html, '</head>')) {
            $title = wp_get_document_title();
            if (!$title) {
                $title = get_bloginfo('name');
            }
            $html = eta_modern_preg_replace('/<\/head>/i', '<title>' . esc_html($title) . '</title>' . "\n</head>", $html, 1);
        }

        $modern_description = eta_modern_clamp_meta_description(eta_modern_meta_description());
        if ($modern_description) {
            $html = eta_modern_preg_replace('#<meta\b[^>]*name=["\']description["\'][^>]*>\s*#i', '', $html);
            $html = eta_modern_preg_replace(
                '/<head([^>]*)>/i',
                '<head$1>' . "\n" . '<meta name="description" content="' . esc_attr($modern_description) . '">',
                $html,
                1
            );
        }

        $html = str_replace(
            'https://envitechal.com/wp-content/themes/g5plus-mowasalat/assets/images/close.png',
            '',
            $html
        );

        $site_origin = untrailingslashit(home_url());
        $html = str_replace(
            [
                'https://envitechal.com',
                'http://staging.envitechal.com',
                'https:\/\/staging.envitechal.com',
                'http:\/\/staging.envitechal.com',
            ],
            [
                $site_origin,
                $site_origin,
                str_replace('/', '\/', $site_origin),
                str_replace('/', '\/', $site_origin),
            ],
            $html
        );

        $fallback_image_alt = esc_attr(eta_modern_display_title() ?: get_bloginfo('name'));
        $image_alt_rewrite = preg_replace_callback(
            '#<img\b(?![^>]*\baria-hidden=["\']true["\'])([^>]*)>#i',
            function ($matches) use ($fallback_image_alt) {
                $tag = $matches[0];
                if (preg_match('/\salt=["\'][^"\']+["\']/i', $tag)) {
                    return $tag;
                }
                if (preg_match('/\salt=["\']\s*["\']/i', $tag)) {
                    return preg_replace('/\salt=["\']\s*["\']/i', ' alt="' . $fallback_image_alt . '"', $tag, 1);
                }
                return preg_replace('/<img\b/i', '<img alt="' . $fallback_image_alt . '"', $tag, 1);
            },
            $html
        );
        if (is_string($image_alt_rewrite)) {
            $html = $image_alt_rewrite;
        }

        $html = eta_modern_normalize_legacy_copy($html);

        if (!is_string($html) || trim($html) === '') {
            return $original_html;
        }

        return $html;
    });
}, 0);

add_action('template_redirect', function () {
    $path = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';

    if ($path === '/services/technical-advisory/') {
        wp_safe_redirect(home_url('/services/technical-advisory-2/'), 301);
        exit;
    }

    if ($path === '/services/theraml-imaging-inspection/') {
        wp_safe_redirect(home_url('/services/thermal-imaging-inspection/'), 301);
        exit;
    }

    if ($path === '/gf_footer/mail-chilmp-transparent/') {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }

    if ($path === '/contactus/' || $path === '/contact/') {
        wp_safe_redirect(home_url('/contact-us-envi-tech-al/'), 301);
        exit;
    }

    $legacy_redirects = [
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
    ];

    $redirect_key = untrailingslashit((string) $path) . '/';
    if (isset($legacy_redirects[$redirect_key])) {
        wp_safe_redirect(home_url($legacy_redirects[$redirect_key]), 301);
        exit;
    }

    if (strpos($path, '/downloaddocs/') === 0) {
        $legacy_file = ABSPATH . ltrim($path, '/');
        if (is_file($legacy_file)) {
            return;
        }

        wp_safe_redirect(home_url('/downloads/'), 301);
        exit;
    }
}, 0);

function eta_modern_is_admin_request()
{
    $path = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
    return is_string($path) && strpos($path, '/wp-admin/') === 0;
}

function eta_modern_is_staging_host()
{
    $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
    return $host === 'staging.envitechal.com';
}

function eta_modern_allow_legacy_builder_assets()
{
    if (is_admin()) {
        return true;
    }

    $path = parse_url(add_query_arg([]), PHP_URL_PATH);
    $legacy_required_paths = [
        '/wp-admin/',
    ];

    foreach ($legacy_required_paths as $legacy_path) {
        if (strpos($path, $legacy_path) === 0) {
            return true;
        }
    }

    return false;
}

add_filter('template_include', function ($template) {
    $theme_dir = get_stylesheet_directory();
    $request_path = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';

    if (is_singular('services') && file_exists($theme_dir . '/single-services.php')) {
        return $theme_dir . '/single-services.php';
    }

    if (($request_path === '/services/' || is_post_type_archive('services')) && file_exists($theme_dir . '/archive-services.php')) {
        return $theme_dir . '/archive-services.php';
    }

    if ((is_archive() || is_home()) && file_exists($theme_dir . '/archive.php')) {
        return $theme_dir . '/archive.php';
    }

    if (is_singular('post') && file_exists($theme_dir . '/single.php')) {
        return $theme_dir . '/single.php';
    }

    return $template;
}, 99);

function eta_modern_service_posts($limit = -1)
{
    return get_posts([
        'post_type' => 'services',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    ]);
}

function eta_modern_latest_posts($limit = 3)
{
    return get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
}

function eta_modern_strip_legacy_shortcodes($content)
{
    $content = preg_replace_callback('/\[vc_raw_html[^\]]*\]([\s\S]*?)\[\/vc_raw_html\]/i', function ($matches) {
        $decoded = base64_decode(trim($matches[1]), true);
        if (!is_string($decoded) || $decoded === '') {
            return '';
        }

        if (strpos($decoded, '%3C') !== false || strpos($decoded, '%3c') !== false) {
            $decoded = rawurldecode($decoded);
        }

        $decoded = eta_modern_preg_replace('#<style\b[^>]*>[\s\S]*?</style>#i', '', $decoded);
        $decoded = eta_modern_preg_replace('#<script\b[^>]*>[\s\S]*?</script>#i', '', $decoded);

        return $decoded;
    }, (string) $content);

    $content = strip_shortcodes((string) $content);

    return eta_modern_preg_replace('#\[(?:/?vc_[^\]]*|/?g5plus_[^\]]*|/?rev_slider[^\]]*)\]#i', '', $content);
}

function eta_modern_strip_invisible_content($content)
{
    $content = (string) $content;
    $content = str_replace(["\xEF\xBB\xBF", "\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D"], '', $content);
    $content = eta_modern_preg_replace('/^\x{FEFF}+/u', '', $content);

    return $content;
}

function eta_modern_clean_content($content)
{
    $content = eta_modern_strip_invisible_content($content);
    $content = eta_modern_strip_legacy_shortcodes($content);
    $content = eta_modern_strip_invisible_content($content);
    $content = eta_modern_normalize_legacy_copy($content);
    $content = eta_modern_preg_replace('#<style\b[^>]*>[\s\S]*?</style>#i', '', $content);
    $content = eta_modern_preg_replace('#<script\b[^>]*>[\s\S]*?</script>#i', '', $content);
    $content = eta_modern_strip_invisible_content($content);
    $content = eta_modern_preg_replace('#^(?:\s|&nbsp;|<p>\s*</p>)+#i', '', $content);

    if (eta_modern_is_premium_legacy_html($content)) {
        $content = eta_modern_preg_replace('/>\s+</', ">\n<", $content);
        return wp_kses_post(trim(eta_modern_strip_invisible_content($content)));
    }

    $content = eta_modern_preg_replace('#<\s*h1([^>]*)>#i', '<h2$1>', $content);
    $content = eta_modern_preg_replace('#<\s*/\s*h1\s*>#i', '</h2>', $content);
    $content = eta_modern_preg_replace('/\s+/', ' ', $content);
    $content = trim(eta_modern_strip_invisible_content($content));

    return wp_kses_post(wpautop($content));
}

function eta_modern_is_premium_legacy_html($content)
{
    return (bool) preg_match('/class=["\'][^"\']*\b(?:etl-|eta-wq-premium\b|eta-wq-)[^"\']*["\']/i', (string) $content);
}

add_filter('rest_prepare_post', 'eta_modern_harden_rest_post_output', 20, 3);

function eta_modern_harden_rest_post_output($response, $post, $request)
{
    if (!($post instanceof WP_Post) || !eta_modern_is_emr_emp_post($post) || !($response instanceof WP_REST_Response)) {
        return $response;
    }

    $data = $response->get_data();
    foreach (['content', 'excerpt'] as $field) {
        if (!isset($data[$field]['rendered']) || !is_string($data[$field]['rendered'])) {
            continue;
        }

        $rendered = eta_modern_strip_invisible_content($data[$field]['rendered']);
        $rendered = eta_modern_preg_replace('#<script\b[^>]*>[\s\S]*?</script>#i', '', $rendered);
        $rendered = eta_modern_preg_replace('#<style\b[^>]*>[\s\S]*?</style>#i', '', $rendered);

        if ($field === 'content') {
            $rendered = eta_modern_preg_replace('#<figure\b[^>]*>\s*<img\b[\s\S]*?</figure>#i', '', $rendered);
            $rendered = eta_modern_preg_replace('#<img\b[^>]*>#i', '', $rendered);
        }

        $data[$field]['rendered'] = trim($rendered);
    }

    $response->set_data($data);
    return $response;
}

function eta_modern_fallback_post_content($post = null)
{
    $post = get_post($post);
    if (!$post) {
        return '';
    }

    $title = eta_modern_display_title($post);
    $topic = eta_modern_post_topic_label($post);
    $summary = eta_modern_plain_excerpt($post, 30);
    if (!$summary) {
        $summary = sprintf(
            'This Envi Tech AL knowledge article supports teams reviewing %s requirements, testing decisions, compliance expectations, and the practical next step before requesting a formal service scope.',
            strtolower($topic)
        );
    }

    ob_start();
    ?>
    <section class="eta-generated-post-brief">
        <p><?php echo esc_html($summary); ?></p>
        <h2><?php esc_html_e('What this guide helps you clarify', 'envi-tech-al-modern'); ?></h2>
        <ul>
            <li><?php esc_html_e('The technical or regulatory decision behind the requirement.', 'envi-tech-al-modern'); ?></li>
            <li><?php esc_html_e('The information a lab, auditor, buyer, or authority may expect.', 'envi-tech-al-modern'); ?></li>
            <li><?php esc_html_e('The next practical step before testing, monitoring, calibration, or advisory work begins.', 'envi-tech-al-modern'); ?></li>
        </ul>
        <h2><?php echo esc_html($title); ?></h2>
        <p><?php esc_html_e('For business-critical compliance work, Envi Tech AL recommends confirming the exact scope, location, deadline, sample type, and intended report use before proceeding. This keeps the technical work aligned with the final decision the report must support.', 'envi-tech-al-modern'); ?></p>
    </section>
    <?php
    return ob_get_clean();
}

function eta_modern_normalize_legacy_copy($content)
{
    $content = (string) $content;
    $replacements = [
        home_url('/contact/') => home_url('/contact-us-envi-tech-al/'),
        'https://envitechal.com/contact/' => home_url('/contact-us-envi-tech-al/'),
        '&amp;lt;strong&amp;gt;' => '',
        '&amp;lt;/strong&amp;gt;' => '',
        '&lt;strong&gt;' => '',
        '&lt;/strong&gt;' => '',
        'Beta emitters1.' => 'Beta emitters. Temperature.',
        'Fluride' => 'Fluoride',
        'Cooper' => 'Copper',
        'E-Coli' => 'E. coli',
        'E-coli' => 'E. coli',
        'An-ionic' => 'Anionic',
        'An-ionic detergents' => 'Anionic detergents',
        'Our head office is in Bahadurabad, Karachi, and we have a regional office in Lahore.' => 'Our Karachi head office is in Bahadurabad Block 3, with a regional office in Lahore for Punjab coordination.',
        'Address: 345, First Floor, Street-15, Block-3, Bahadurabad, Karachi. 75900, Pakistan.' => 'Address: First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS, Karachi, Sindh 75900, Pakistan.',
        '345, First Floor, Street-15, Block-3, Bahadurabad, Karachi. 75900, Pakistan.' => 'First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS, Karachi, Sindh 75900, Pakistan.',
        '345, First Floor, Street-15, Block-3,<br />Bahadurabad, Karachi. 75900, Pakistan.' => 'First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS, Karachi, Sindh 75900, Pakistan.',
    ];

    $content = str_replace(array_keys($replacements), array_values($replacements), $content);
    $content = eta_modern_preg_replace('/&(?:amp;)?lt;\/?strong(?:\s+[^&]*)?(?:amp;)?gt;/i', '', $content);
    $content = eta_modern_preg_replace('/\bJTND(?:c3R5bGU|L3N0eWxl|c2NyaXB0|L3NjcmlwdA)[A-Za-z0-9+\/=]{40,}/i', '', $content);
    $content = eta_modern_preg_replace('/%3C(?:style|script)\b[\s\S]*?%3C\/(?:style|script)%3E/i', '', $content);
    $content = eta_modern_preg_replace('#<img\b[^>]+src=["\'][^"\']*/wp-content/uploads/2023/08/1\.jpg[^"\']*["\'][^>]*>\s*#i', '', $content);

    return $content;
}

function eta_modern_plain_excerpt($post = null, $length = 28)
{
    $post = get_post($post);
    if (!$post) {
        return '';
    }

    $text = has_excerpt($post) ? $post->post_excerpt : eta_modern_strip_legacy_shortcodes($post->post_content);
    $text = eta_modern_normalize_legacy_copy($text);
    $text = wp_strip_all_tags($text);
    $text = eta_modern_preg_replace('/\s+/', ' ', trim($text));

    return wp_trim_words($text, $length, '...');
}

function eta_modern_section_title($eyebrow, $title, $lead = '')
{
    ?>
    <header class="eta-section-head">
        <?php if ($eyebrow) : ?>
            <p class="eta-eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php endif; ?>
        <h2><?php echo esc_html($title); ?></h2>
        <?php if ($lead) : ?>
            <p><?php echo esc_html($lead); ?></p>
        <?php endif; ?>
    </header>
    <?php
}

function eta_modern_card_link($post, $class = '')
{
    $post = get_post($post);
    if (!$post) {
        return;
    }

    $title = eta_modern_display_title($post);
    $service_image = $post->post_type === 'services' ? eta_modern_service_profile_value($post->post_name, 'image', '') : '';
    ?>
    <article class="eta-card <?php echo esc_attr($class); ?>">
        <?php if ($service_image) : ?>
            <a class="eta-card-media" href="<?php echo esc_url(get_permalink($post)); ?>" aria-label="<?php echo esc_attr($title); ?>">
                <img src="<?php echo esc_url($service_image); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
            </a>
        <?php elseif (has_post_thumbnail($post)) : ?>
            <a class="eta-card-media" href="<?php echo esc_url(get_permalink($post)); ?>" aria-label="<?php echo esc_attr($title); ?>">
                <?php echo get_the_post_thumbnail($post, 'medium_large'); ?>
            </a>
        <?php endif; ?>
        <div class="eta-card-body">
            <h3><a href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html($title); ?></a></h3>
            <p><?php echo esc_html(eta_modern_service_summary($post) ?: eta_modern_plain_excerpt($post, 24)); ?></p>
            <a class="eta-text-link" href="<?php echo esc_url(get_permalink($post)); ?>" aria-label="<?php echo esc_attr(sprintf(__('View details: %s', 'envi-tech-al-modern'), eta_modern_display_title($post))); ?>"><?php esc_html_e('View details', 'envi-tech-al-modern'); ?></a>
        </div>
    </article>
    <?php
}

function eta_modern_display_title($post = null)
{
    $post = get_post($post);
    $title = $post ? get_the_title($post) : get_the_title();
    $charset = get_bloginfo('charset') ?: 'UTF-8';
    $title = (string) $title;
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($title, ENT_QUOTES, $charset);
        if ($decoded === $title) {
            break;
        }
        $title = $decoded;
    }
    $title = wp_strip_all_tags($title);
    return trim($title);
}

function eta_modern_service_summary($post)
{
    $post = get_post($post);
    if (!$post || $post->post_type !== 'services') {
        return '';
    }

    $summaries = [
        'analytical-lab-services' => 'Environmental analysis for industrial, commercial, and regulatory teams that need defensible lab results and scope-confirmed methods for compliance decisions.',
        'environmental-advisory' => 'Practical advisory support for environmental initiatives, gap assessments, and improvement programs before audits or regulatory submissions.',
        'technical-advisory-2' => 'Technical audit and compliance guidance for facilities that need clear corrective actions, documentation support, and operational confidence.',
        'certification-advisory' => 'ISO and regulatory certification advisory for organizations preparing systems, evidence, and teams for successful assessment.',
        'environmental-consultancy' => 'SEPA and environmental regulatory consultancy for projects, plants, and facilities from planning through operational compliance.',
        'equipment-calibration-services' => 'Calibration support for laboratory and industrial equipment so measurements remain traceable, reliable, and audit-ready.',
        'water-testing-lab-services' => 'Drinking water and wastewater testing for homes, industries, hospitals, hotels, exporters, and facilities that need safe, compliant water decisions.',
        'ballast-water-testing-services' => 'Ballast water testing for vessels, operators, and maritime stakeholders that need documentation aligned with compliance expectations.',
        'theraml-imaging-inspection' => 'Thermal imaging inspection for electrical, mechanical, and facility teams that need early fault detection and reduced downtime risk.',
    ];

    return $summaries[$post->post_name] ?? '';
}

function eta_modern_service_profiles()
{
    return [
        'analytical-lab-services' => [
            'category' => 'Environmental laboratory',
            'hero' => 'Environmental lab analysis for compliance-critical decisions.',
            'lead' => 'Analytical testing support for industrial, commercial, healthcare, hospitality, maritime, and facility teams that need defensible environmental results.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Testing-Lab.png',
            'seo_description' => 'Environmental lab and analytical testing services in Karachi and Lahore for water, wastewater, air, soil, and compliance-critical reporting by Envi Tech AL.',
            'outcomes' => ['Water and wastewater parameters', 'Air and emission support', 'Industrial sample analysis', 'Regulatory report inputs'],
            'proof' => ['Published scope checks', 'EPA-related documentation', 'Technical review', 'Karachi and Lahore support'],
            'best_for' => ['Industrial compliance teams', 'Hospitals and hotels', 'Exporters and buyers', 'Facility managers'],
        ],
        'water-testing-lab-services' => [
            'category' => 'Water and wastewater',
            'hero' => 'Water testing that turns samples into clear safety and compliance decisions.',
            'lead' => 'Drinking water, wastewater, groundwater, process water, RO performance, and industrial discharge testing for customers that need reliable reporting.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/05/water-testing-services-karachi-lahore.png',
            'seo_description' => 'Water testing lab services in Karachi and Lahore for drinking water, wastewater, process water, RO plants, and industrial discharge compliance.',
            'outcomes' => ['Drinking water safety', 'Wastewater compliance', 'RO plant checks', 'Industrial discharge testing'],
            'proof' => ['Parameter-led scope', 'Lab-reviewed results', 'Compliance-ready reports', 'Fast sample guidance'],
            'best_for' => ['Homes and buildings', 'Industries', 'Hospitals and hotels', 'Contractors and exporters'],
        ],
        'equipment-calibration-services' => [
            'category' => 'Calibration and traceability',
            'hero' => 'Calibration support for instruments that must be trusted.',
            'lead' => 'Calibration services for laboratory and industrial equipment so measurement systems remain reliable, traceable, and audit-ready.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Calibration-Services.png',
            'seo_description' => 'Equipment calibration services for laboratory and industrial instruments in Karachi with traceability, accuracy, and audit-ready documentation.',
            'outcomes' => ['Instrument reliability', 'Audit readiness', 'Measurement confidence', 'Controlled documentation'],
            'proof' => ['Reference methodology', 'Technical discipline', 'Certificate support', 'Industrial coverage'],
            'best_for' => ['Laboratories', 'Manufacturing plants', 'EHS teams', 'Quality departments'],
        ],
        'environmental-consultancy' => [
            'category' => 'Regulatory consultancy',
            'hero' => 'Environmental consultancy for projects that need approvals, clarity, and control.',
            'lead' => 'SEPA and environmental regulatory consultancy for new projects, operating facilities, audits, monitoring plans, and compliance submissions.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Consulting-Services.png',
            'seo_description' => 'Environmental consultancy services for SEPA compliance, IEE, EIA, EMP, EMR, audits, and regulatory support in Karachi and Lahore.',
            'outcomes' => ['IEE and EIA support', 'EMP and EMR planning', 'Regulatory submissions', 'Operational compliance'],
            'proof' => ['SEPA-aware guidance', 'Field and lab alignment', 'Audit context', 'Practical corrective actions'],
            'best_for' => ['New projects', 'Operating facilities', 'Compliance managers', 'Consultants and contractors'],
        ],
        'certification-advisory' => [
            'category' => 'Certification advisory',
            'hero' => 'Certification advisory that turns evidence into audit confidence.',
            'lead' => 'ISO and regulatory certification guidance for organizations preparing systems, documents, actions, and teams for assessment.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Certification-Advisory-Services.png',
            'seo_description' => 'Certification and regulatory compliance advisory for ISO systems, audits, evidence preparation, and environmental compliance improvement.',
            'outcomes' => ['Gap assessment', 'Documentation support', 'Audit preparation', 'Corrective action planning'],
            'proof' => ['ISO 9001 awareness', 'ISO 14001 awareness', 'Evidence-led approach', 'Practical training support'],
            'best_for' => ['Management systems teams', 'Factories', 'Export units', 'Compliance departments'],
        ],
        'environmental-advisory' => [
            'category' => 'Environmental advisory',
            'hero' => 'Environmental advisory for teams improving performance before pressure arrives.',
            'lead' => 'Training, gap assessment, improvement planning, and advisory support for environmental initiatives and compliance maturity.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Industrial-compliance-Monitoring.png',
            'seo_description' => 'Environmental advisory services for training, gap assessments, improvement planning, audits, and compliance maturity support in Pakistan.',
            'outcomes' => ['Gap assessment', 'Training support', 'Improvement planning', 'Compliance maturity'],
            'proof' => ['Practical recommendations', 'Facility-aware guidance', 'Audit preparation', 'Documentation clarity'],
            'best_for' => ['EHS managers', 'Facility teams', 'Internal auditors', 'Leadership teams'],
        ],
        'technical-advisory-2' => [
            'category' => 'Technical advisory',
            'hero' => 'Technical advisory for audits, evidence, and corrective action clarity.',
            'lead' => 'Technical guidance for facilities that need clear findings, practical actions, and documentation support before or after audits.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Regulatory-compliance-Advisory.png',
            'seo_description' => 'Technical advisory services in Pakistan for audits, gap assessment, corrective action planning, and compliance documentation support.',
            'outcomes' => ['Technical audits', 'Gap assessment', 'Corrective actions', 'Evidence preparation'],
            'proof' => ['Clear action lists', 'Operational context', 'Documentation discipline', 'Audit support'],
            'best_for' => ['Factories', 'Project teams', 'Compliance leaders', 'Technical managers'],
        ],
        'ballast-water-testing-services' => [
            'category' => 'Maritime compliance',
            'hero' => 'Ballast water testing for vessels that need fast, defensible compliance support.',
            'lead' => 'Ballast and deballast water testing support for vessels, operators, and maritime teams planning around inspection and reporting requirements.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Ballast-Water-Testing-Services.png',
            'seo_description' => 'Ballast water testing services for vessels and maritime operators needing fast sampling coordination and compliance-focused reports in Karachi.',
            'outcomes' => ['Pathogen screening', 'Port-call planning', 'Sampling coordination', 'Marine report support'],
            'proof' => ['Method and credential scope confirmation', 'Maritime-aware handling', 'Fast coordination', 'Compliance reporting'],
            'best_for' => ['Vessel operators', 'Shipping agents', 'Port stakeholders', 'Marine compliance teams'],
        ],
        'thermal-imaging-inspection' => [
            'category' => 'Inspection and reliability',
            'hero' => 'Thermal imaging inspection for early fault detection and safer operations.',
            'lead' => 'Thermal imaging support for electrical, mechanical, and facility teams that need to identify abnormal heat patterns before they become failures.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Thermal-Imaging-Services.png',
            'seo_description' => 'Thermal imaging inspection services for electrical, mechanical, and facility reliability checks in Karachi and Lahore.',
            'outcomes' => ['Electrical risk screening', 'Mechanical inspection', 'Preventive maintenance', 'Downtime reduction'],
            'proof' => ['Non-invasive inspection', 'Actionable findings', 'Facility safety support', 'Maintenance planning'],
            'best_for' => ['Maintenance teams', 'Facility managers', 'Electrical departments', 'Industrial plants'],
        ],
    ];
}

function eta_modern_service_profile($slug = '')
{
    $profiles = eta_modern_service_profiles();
    $slug = (string) $slug;

    return $profiles[$slug] ?? [
        'category' => 'Environmental service',
        'hero' => 'Environmental testing and advisory support for compliance-critical work.',
        'lead' => 'Envi Tech AL helps customers define the right scope, complete the technical work, and receive usable reporting for operational and regulatory decisions.',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Testing-Lab.png',
        'seo_description' => 'Environmental testing, monitoring, calibration, and consultancy support from Envi Tech AL in Karachi and Lahore.',
        'outcomes' => ['Defined scope', 'Technical execution', 'Reviewed report', 'Compliance support'],
        'proof' => ['Lab discipline', 'Field support', 'Documentation clarity', 'Customer guidance'],
        'best_for' => ['Industrial teams', 'Commercial facilities', 'Compliance managers', 'Project teams'],
    ];
}

function eta_modern_service_profile_value($slug, $key, $fallback = '')
{
    $profile = eta_modern_service_profile($slug);
    return $profile[$key] ?? $fallback;
}

function eta_modern_render_ai_summary_block($title, $summary, $points = [], $heading_level = 'h2')
{
    $points = array_values(array_filter((array) $points));
    $heading_level = in_array($heading_level, ['h2', 'h3'], true) ? $heading_level : 'h2';
    ?>
    <section class="eta-ai-summary" aria-labelledby="eta-ai-summary-title-<?php echo esc_attr(sanitize_title($title)); ?>">
        <div>
            <p class="eta-eyebrow"><?php esc_html_e('Service summary', 'envi-tech-al-modern'); ?></p>
            <<?php echo esc_html($heading_level); ?> id="eta-ai-summary-title-<?php echo esc_attr(sanitize_title($title)); ?>"><?php echo esc_html($title); ?></<?php echo esc_html($heading_level); ?>>
            <p><?php echo esc_html($summary); ?></p>
        </div>
        <?php if ($points) : ?>
            <ul>
                <?php foreach (array_slice($points, 0, 4) as $point) : ?>
                    <li><?php echo esc_html($point); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php
}

function eta_modern_service_group_label($slug)
{
    $profile = eta_modern_service_profile($slug);
    return $profile['category'] ?? 'Environmental service';
}

function eta_modern_service_faqs($slug)
{
    $faqs = [
        'analytical-lab-services' => [
            ['What analytical laboratory services does Envi Tech AL provide?', 'Envi Tech AL supports environmental laboratory analysis for water, wastewater, air, emissions, soil, waste, and industrial samples where the final report must support compliance, audit, or operational decisions.'],
            ['Can analytical lab reports support EPA submissions?', 'Reports can support EPA-related pathways when the scope, parameters, sampling plan, and report purpose are confirmed before analysis begins.'],
            ['Do you provide analytical lab services in Karachi and Lahore?', 'Yes. Envi Tech AL supports Karachi/Sindh and Lahore/Punjab clients through laboratory, field, and advisory coordination.'],
        ],
        'water-testing-lab-services' => [
            ['Does Envi Tech AL provide water testing in Karachi?', 'Yes. Drinking water, wastewater, bore water, RO plant water, process water, and industrial discharge testing can be scoped for Karachi customers.'],
            ['Does Envi Tech AL provide water testing in Lahore?', 'Yes. Lahore and Punjab customers can request water testing with parameter selection based on the intended report use.'],
            ['What sample details are required before quotation?', 'Share city, sample type, report purpose, deadline, required parameters, standard or buyer requirement, and whether field sampling is needed.'],
        ],
        'equipment-calibration-services' => [
            ['Why is calibration support important?', 'Calibration helps laboratories and industrial teams maintain measurement confidence, traceability, audit readiness, and controlled documentation.'],
            ['Which teams usually request calibration support?', 'Laboratories, manufacturers, quality departments, maintenance teams, and compliance teams usually request calibration support for audit and measurement control.'],
            ['What should I share before requesting calibration?', 'Share instrument type, range, location, certificate requirement, deadline, and whether on-site or coordination support is needed.'],
        ],
        'environmental-consultancy' => [
            ['Can Envi Tech AL support SEPA or Punjab EPA compliance work?', 'Yes. The team can support environmental documentation, monitoring, advisory, and reporting pathways for SEPA and Punjab EPA related requirements.'],
            ['What is covered under environmental consultancy?', 'Consultancy may include IEE, EIA, EMP, EMR, audits, monitoring plans, corrective actions, regulatory coordination, and compliance documentation.'],
            ['When should a project contact the consultancy team?', 'Contact the team before submission deadlines, site changes, buyer audits, inspections, expansion plans, or when results need technical interpretation.'],
        ],
        'certification-advisory' => [
            ['Does certification advisory include fake certificate issuance?', 'No. Certification advisory supports preparation, documentation, gap review, and evidence readiness. It does not include unsupported or fake certification claims.'],
            ['Which organizations need certification advisory?', 'Factories, exporters, management system teams, and compliance departments preparing for audits or certification reviews often need advisory support.'],
            ['Can lab reports support certification audits?', 'Relevant testing and monitoring reports can support audits when the scope and evidence requirements are correctly defined.'],
        ],
        'environmental-advisory' => [
            ['How is environmental advisory different from testing?', 'Testing produces technical data. Advisory helps interpret requirements, identify gaps, plan actions, and prepare documentation around that data.'],
            ['Can advisory support buyer audits?', 'Yes. Advisory can help facilities prepare evidence, monitoring plans, corrective actions, and environmental documentation for buyer or customer audits.'],
            ['Do advisory services apply to Karachi and Lahore?', 'Yes. Advisory support is available for Karachi/Sindh and Lahore/Punjab clients depending on scope and site requirements.'],
        ],
        'technical-advisory-2' => [
            ['What does technical advisory help with?', 'Technical advisory helps customers review findings, define corrective actions, prepare evidence, and improve documentation before or after audits.'],
            ['Can technical advisory connect with lab testing?', 'Yes. Advisory can connect testing results with practical next steps, monitoring plans, and compliance documentation.'],
            ['What should be shared for technical advisory?', 'Share the audit finding, report, regulatory requirement, project stage, deadline, and any existing evidence or corrective action record.'],
        ],
        'ballast-water-testing-services' => [
            ['Does Envi Tech AL support ballast water testing in Karachi?', 'Yes. Ballast and deballast water testing support can be coordinated for maritime operators, agents, and vessel teams around port-call and reporting requirements.'],
            ['What details are needed before ballast water testing?', 'Share vessel schedule, port call timing, sample requirement, reporting deadline, and any inspection or compliance context.'],
            ['Can reports support maritime compliance review?', 'Reports can support compliance review when the sampling scope and report purpose are confirmed before work begins.'],
        ],
        'thermal-imaging-inspection' => [
            ['What is thermal imaging inspection used for?', 'Thermal imaging helps identify abnormal heat patterns in electrical, mechanical, and facility systems before they become failures or safety risks.'],
            ['Which facilities request thermal imaging?', 'Industrial plants, commercial buildings, maintenance teams, electrical departments, and reliability teams often request thermal inspection.'],
            ['Does thermal imaging replace repair work?', 'No. Thermal imaging identifies risk indicators and supports maintenance decisions. Repair decisions remain with the facility and qualified technical teams.'],
        ],
    ];

    return $faqs[$slug] ?? [
        ['What details should I share for a quotation?', 'Share city, site or sample type, report purpose, deadline, required parameters, and whether field support is needed.'],
        ['Can reports support compliance or audit work?', 'Reports can support compliance or audit work when the scope and documentation requirements are confirmed before execution.'],
        ['Does Envi Tech AL support Karachi and Lahore?', 'Yes. Envi Tech AL serves Karachi/Sindh and Lahore/Punjab clients through laboratory, field, and advisory support.'],
    ];
}

function eta_modern_service_parameter_groups($slug)
{
    $groups = [
        'analytical-lab-services' => [
            'Water and wastewater' => ['pH', 'TDS', 'TSS', 'BOD', 'COD', 'Oil and grease', 'Heavy metals', 'Microbiological indicators'],
            'Air and emissions' => ['Stack emission parameters', 'Ambient air indicators', 'Particulate matter', 'Combustion gases', 'Workplace exposure indicators'],
            'Soil, waste, and industrial samples' => ['Heavy metals', 'Moisture', 'Organic/inorganic indicators', 'Hazardous waste screening', 'Process-specific parameters'],
        ],
        'water-testing-lab-services' => eta_modern_water_parameter_groups(),
        'equipment-calibration-services' => [
            'Common instrument categories' => ['Balances', 'Thermometers', 'pH meters', 'Conductivity meters', 'Pressure gauges', 'Flow meters', 'Environmental meters'],
            'Documentation needs' => ['Instrument ID', 'Range', 'Location', 'Acceptance criteria', 'Certificate requirement', 'Due date'],
        ],
        'environmental-consultancy' => [
            'Regulatory documents' => ['IEE', 'EIA', 'EMP', 'EMR', 'Environmental audits', 'Monitoring plans'],
            'Compliance support areas' => ['SEPA coordination', 'Punjab EPA support', 'Corrective action planning', 'Submission preparation', 'Operational compliance review'],
        ],
        'certification-advisory' => [
            'System support' => ['ISO 9001 readiness', 'ISO 14001 readiness', 'Gap assessment', 'Document review', 'Internal audit preparation'],
            'Evidence support' => ['Testing reports', 'Monitoring records', 'Calibration records', 'Corrective actions', 'Training evidence'],
        ],
        'environmental-advisory' => [
            'Advisory workstreams' => ['Gap assessment', 'Training support', 'Improvement planning', 'Environmental performance review', 'Audit response planning'],
            'Operational focus' => ['Water and wastewater', 'Air and emissions', 'Noise', 'Waste handling', 'Workplace environmental conditions'],
        ],
        'technical-advisory-2' => [
            'Technical review areas' => ['Audit findings', 'Corrective actions', 'Evidence mapping', 'Documentation quality', 'Technical report interpretation'],
            'Support outputs' => ['Action lists', 'Monitoring recommendations', 'Report review', 'Compliance explanation', 'Follow-up scope'],
        ],
        'ballast-water-testing-services' => [
            'Maritime scope areas' => ['Ballast water sampling', 'Deballast water testing', 'Port-call timing', 'Vessel coordination', 'Report preparation'],
            'Planning details' => ['Vessel name', 'Arrival schedule', 'Sampling location', 'Reporting deadline', 'Inspection context'],
        ],
        'thermal-imaging-inspection' => [
            'Inspection targets' => ['Electrical panels', 'Switchgear', 'Motors', 'Bearings', 'Mechanical equipment', 'Facility hotspots'],
            'Report outputs' => ['Thermal images', 'Observed abnormality', 'Priority indication', 'Maintenance recommendation', 'Follow-up record'],
        ],
    ];

    return $groups[$slug] ?? [];
}

function eta_modern_service_process_steps($slug)
{
    if ($slug === 'environmental-consultancy') {
        return [
            ['Scope', 'Confirm project type, regulatory pathway, site context, authority requirement, and required documentation.'],
            ['Prepare', 'Build the advisory, monitoring, report, or submission route with controlled supporting evidence.'],
            ['Support', 'Help the customer understand findings, next steps, corrective actions, and follow-up requirements.'],
        ];
    }

    if ($slug === 'equipment-calibration-services') {
        return [
            ['Scope', 'Confirm instrument type, range, location, certificate need, acceptance criteria, and timeline.'],
            ['Execute', 'Coordinate calibration support with controlled identification, records, and technical discipline.'],
            ['Report', 'Provide documentation that supports measurement confidence, audits, and traceability review.'],
        ];
    }

    return [
        ['Scope', 'Confirm objective, sample or site context, city, timeline, parameters, and final report user.'],
        ['Execute', 'Carry out sampling, testing, monitoring, calibration, inspection, or advisory work with controlled documentation.'],
        ['Report', 'Deliver results and context that can be used by customers, auditors, regulators, and internal teams.'],
    ];
}

function eta_modern_related_service_items($slug, $current_id)
{
    $preferred = [
        'water-testing-lab-services' => ['analytical-lab-services', 'environmental-consultancy', 'equipment-calibration-services'],
        'analytical-lab-services' => ['water-testing-lab-services', 'environmental-consultancy', 'ballast-water-testing-services'],
        'environmental-consultancy' => ['analytical-lab-services', 'water-testing-lab-services', 'certification-advisory'],
        'environmental-advisory' => ['environmental-consultancy', 'technical-advisory-2', 'equipment-calibration-services'],
        'technical-advisory-2' => ['environmental-consultancy', 'certification-advisory', 'analytical-lab-services'],
        'certification-advisory' => ['environmental-consultancy', 'technical-advisory-2', 'analytical-lab-services'],
        'equipment-calibration-services' => ['analytical-lab-services', 'technical-advisory-2', 'environmental-advisory'],
        'ballast-water-testing-services' => ['analytical-lab-services', 'water-testing-lab-services', 'environmental-consultancy'],
        'thermal-imaging-inspection' => ['technical-advisory-2', 'environmental-advisory', 'equipment-calibration-services'],
    ];

    $services = eta_modern_service_posts(-1);
    $by_slug = [];
    foreach ($services as $service) {
        $by_slug[get_post_field('post_name', $service)] = $service;
    }

    $related = [];
    foreach (($preferred[$slug] ?? []) as $preferred_slug) {
        if (isset($by_slug[$preferred_slug])) {
            $related[] = $by_slug[$preferred_slug];
        }
    }

    if (count($related) < 3) {
        foreach ($services as $service) {
            if ((int) $service->ID === (int) $current_id || in_array($service, $related, true)) {
                continue;
            }
            $related[] = $service;
            if (count($related) >= 3) {
                break;
            }
        }
    }

    return array_slice($related, 0, 3);
}

function eta_modern_water_parameter_groups()
{
    return [
        'Drinking water parameters' => [
            'pH at 25 C',
            'Color',
            'Total hardness as CaCO3',
            'Total dissolved solids',
            'Turbidity',
            'Chloride',
            'Sulfate',
            'Nitrate',
            'Fluoride',
            'Iron',
            'Manganese',
            'Copper',
            'Lead',
            'Arsenic',
            'Cadmium',
            'Chromium',
            'Mercury',
            'Residual chlorine',
            'Total coliform',
            'E. coli',
            'Alpha emitters',
            'Beta emitters',
        ],
        'Wastewater parameters' => [
            'Temperature',
            'pH',
            'Biochemical oxygen demand',
            'Chemical oxygen demand',
            'Total suspended solids',
            'Total dissolved solids',
            'Oil and grease',
            'Phenolic compounds',
            'Fluoride',
            'Sulfide',
            'Ammonia',
            'Anionic detergents (MBAS)',
            'Chloride',
            'Sulfate',
            'Total chromium',
            'Hexavalent chromium',
            'Copper',
            'Zinc',
            'Nickel',
            'Lead',
            'Cadmium',
            'Mercury',
        ],
        'Additional water categories' => [
            'Bore water and groundwater screening',
            'Process water quality checks',
            'Swimming pool and recreational water testing',
            'RO plant and filtration performance checks',
            'Industrial discharge compliance testing',
        ],
    ];
}

function eta_modern_render_water_testing_page()
{
    ?>
    <section class="eta-service-opening">
        <p><?php esc_html_e('Water quality decisions affect health, production, compliance, and reputation. Envi Tech AL supports drinking water, wastewater, process water, and industrial discharge testing for clients that need clear results and practical next steps.', 'envi-tech-al-modern'); ?></p>
        <div class="eta-check-grid">
            <span><?php esc_html_e('Homes and residential facilities', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Hospitals, hotels, and commercial buildings', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Textile, food, and industrial units', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Exporters, contractors, and compliance teams', 'envi-tech-al-modern'); ?></span>
        </div>
    </section>

    <section class="eta-parameter-section">
        <h2><?php esc_html_e('Water testing parameters', 'envi-tech-al-modern'); ?></h2>
        <p><?php esc_html_e('The old combined parameter list has been separated into practical testing groups so clients and auditors can quickly understand the scope.', 'envi-tech-al-modern'); ?></p>
        <div class="eta-parameter-grid">
            <?php foreach (eta_modern_water_parameter_groups() as $group => $parameters) : ?>
                <article class="eta-parameter-card">
                    <h3><?php echo esc_html($group); ?></h3>
                    <ul>
                        <?php foreach ($parameters as $parameter) : ?>
                            <li><?php echo esc_html($parameter); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="eta-service-opening eta-service-opening-muted">
        <h2><?php esc_html_e('Compliance outcome', 'envi-tech-al-modern'); ?></h2>
        <p><?php esc_html_e('Testing can support internal safety reviews, customer requirements, plant troubleshooting, discharge compliance, and report verification needs. The lab team can help match the parameter set to the intended use of the report.', 'envi-tech-al-modern'); ?></p>
    </section>
    <?php
}

function eta_modern_water_testing_faqs()
{
    return [
        ['How much does water testing cost in Pakistan?', 'Cost depends on the parameters required: a basic drinking water panel costs considerably less than a full SEQS effluent panel with heavy metals. Send your requirement through the quotation form or WhatsApp and the laboratory will return a precise quotation in PKR.'],
        ['How long does laboratory water analysis take?', 'Routine analysis time depends on the parameter list, sample condition and method requirements. Microbiological tests require incubation periods set by the method. If a deadline is critical - a vessel sailing, an audit date, an EPA submission - tell us when you book and the laboratory will plan around it.'],
        ['Which standards do you test water against?', 'The applicable comparison basis can include WHO guideline values, Pakistan drinking-water requirements, SEQS, NEQS, PEQS, or an industry or buyer specification. Confirm the exact standard, laboratory, parameters, methods, and reporting format before testing.'],
        ['I am not in Karachi or Lahore - can you still test my water?', 'Field sampling teams operate across Karachi and Lahore. For other cities, contact the laboratory before dispatch so the team can guide bottle selection, preservation, labelling and transit conditions before accepting the sample.'],
        ['How do I know the report is genuine?', 'Reports carrying supported verification details can be checked through the report verification portal using the report number and date. Contact Envi Tech AL if a report cannot be verified online.'],
    ];
}

function eta_modern_render_water_testing_flagship_page()
{
    $hero_image = 'https://envitechal.com/wp-content/uploads/2026/05/water-testing-services-karachi-lahore.png';
    $parameters = [
        ['Drinking water', 'pH, TDS, turbidity, hardness, chloride, sulphate, nitrate, fluoride, arsenic, lead, iron, total coliforms, E. coli', 'WHO guidelines; PEQS (drinking water)'],
        ['Wastewater / effluent', 'pH, BOD5, COD, TSS, oil and grease, sulphide, phenols, temperature, Cr, Cu, Zn, Pb, Cd, Ni', 'SEQS 2016; NEQS; PEQS'],
        ['Process / utility', 'Conductivity, hardness, alkalinity, silica, iron, chloride, microbial load', 'Industry / buyer specifications'],
        ['RO performance', 'Feed-permeate-reject TDS, conductivity, recovery indicators', 'Plant design specification'],
    ];
    $faqs = eta_modern_water_testing_faqs();
    ?>
    <section class="eta-service-hero eta-water-flagship-hero" aria-labelledby="eta-water-title">
        <img class="eta-service-hero-img" src="<?php echo esc_url($hero_image); ?>" alt="<?php esc_attr_e('Analyst performing water quality testing in an Envi Tech AL laboratory', 'envi-tech-al-modern'); ?>" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-service-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Water & Wastewater Analysis', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-water-title"><?php esc_html_e('Water Testing Laboratory for Drinking, Process and Wastewater Compliance', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Envi Tech AL coordinates water and wastewater testing for industries, hospitals, hotels, exporters, housing societies, and maritime operators. Published ISO/IEC 17025:2017 accreditation applies only to the Lahore laboratory and the water/wastewater methods listed in PNAC LAB-347. Every other location, matrix, parameter, and method requires separate scope confirmation before it may be described as accredited.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request a quotation', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-service-hero-panel">
                <span><?php esc_html_e('Laboratory trust', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('PNAC LAB-347 — Lahore', 'envi-tech-al-modern'); ?></strong>
                <strong><?php esc_html_e('PNAC document states validity through 21 September 2028', 'envi-tech-al-modern'); ?></strong>
                <strong><?php esc_html_e('Punjab EPA listed laboratory', 'envi-tech-al-modern'); ?></strong>
                <strong><?php esc_html_e('Method and location scope verification required', 'envi-tech-al-modern'); ?></strong>
            </aside>
        </div>
    </section>

    <section class="eta-band eta-service-detail-band">
        <div class="eta-shell eta-service-detail-grid">
            <article class="eta-service-main-card">
                <section class="eta-service-opening">
                    <h2><?php esc_html_e('One coordinated route for water testing requirements', 'envi-tech-al-modern'); ?></h2>
                    <p><?php esc_html_e('Water quality decisions carry regulatory, commercial and public-health consequences. Our water testing lab supports the people accountable for those decisions: facility and utility managers proving drinking water safety, compliance officers meeting Sindh EPA and Punjab EPA monitoring conditions, QA teams protecting product quality, exporters answering buyer audits, and building operators safeguarding residents and guests. Whatever the requirement, the deliverable is the same - accurate results, clear reporting and a defensible paper trail.', 'envi-tech-al-modern'); ?></p>
                </section>

                <section class="eta-service-opening eta-service-opening-muted">
                    <h2><?php esc_html_e('Drinking water testing to WHO and PEQS requirements', 'envi-tech-al-modern'); ?></h2>
                    <p><?php esc_html_e('We analyse drinking water, bottled and filtered water, and municipal or borehole supplies against the World Health Organisation guideline values and Pakistan\'s Environmental Quality Standards for drinking water. Routine physico-chemical analysis covers pH, total dissolved solids, turbidity, hardness, chloride, sulphate, nitrate and fluoride; trace metal analysis by atomic absorption spectrometry covers arsenic, lead, iron, chromium and other health-significant metals; and microbiological examination screens for total coliforms and E. coli - the decisive indicators of faecal contamination. Reports state each result against its applicable limit, so safe is never a matter of interpretation.', 'envi-tech-al-modern'); ?></p>
                    <a class="eta-text-link" href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('report verification portal', 'envi-tech-al-modern'); ?></a>
                </section>

                <section class="eta-service-opening">
                    <h2><?php esc_html_e('Wastewater and effluent testing against SEQS limits', 'envi-tech-al-modern'); ?></h2>
                    <p><?php esc_html_e('Industrial units discharging to sewer, sea or inland water are subject to the Sindh Environmental Quality Standards or corresponding Punjab requirements. Testing requests can be scoped for pH, BOD5, COD, total suspended solids, oil and grease, sulphides, phenolic compounds, temperature, and relevant metals. The laboratory confirms availability, method, location, reporting scope, and any applicable accreditation before accepting the work.', 'envi-tech-al-modern'); ?></p>
                    <p>
                        <a class="eta-text-link" href="<?php echo esc_url(home_url('/sindh-environmental-quality-standards-seqs/')); ?>"><?php esc_html_e('SEQS limits', 'envi-tech-al-modern'); ?></a>
                        <span aria-hidden="true"> | </span>
                        <a class="eta-text-link" href="<?php echo esc_url(home_url('/wastewater-testing-services/')); ?>"><?php esc_html_e('wastewater testing services', 'envi-tech-al-modern'); ?></a>
                        <span aria-hidden="true"> | </span>
                        <a class="eta-text-link" href="<?php echo esc_url(home_url('/services/environmental-consultancy/')); ?>"><?php esc_html_e('environmental consultancy', 'envi-tech-al-modern'); ?></a>
                    </p>
                </section>

                <section class="eta-service-opening eta-service-opening-muted">
                    <h2><?php esc_html_e('Process, RO and utility water analysis', 'envi-tech-al-modern'); ?></h2>
                    <p><?php esc_html_e('Production lines, boilers, cooling towers and reverse osmosis plants each impose their own water quality demands. We test process and product water for food, beverage, pharmaceutical and textile manufacturers; monitor boiler feed and cooling water for hardness, conductivity, silica and scaling indices; and assess RO plant performance through feed, permeate and reject analysis - identifying membrane deterioration before it becomes product risk or energy waste.', 'envi-tech-al-modern'); ?></p>
                </section>

                <section class="eta-parameter-section">
                    <h2><?php esc_html_e('Parameters we test', 'envi-tech-al-modern'); ?></h2>
                    <div class="eta-responsive-table-wrap">
                        <table class="eta-responsive-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Category', 'envi-tech-al-modern'); ?></th>
                                    <th><?php esc_html_e('Representative parameters', 'envi-tech-al-modern'); ?></th>
                                    <th><?php esc_html_e('Reference standard', 'envi-tech-al-modern'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parameters as $row) : ?>
                                    <tr>
                                        <td><?php echo esc_html($row[0]); ?></td>
                                        <td><?php echo esc_html($row[1]); ?></td>
                                        <td><?php echo esc_html($row[2]); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p><?php esc_html_e('The table is representative, not exhaustive - if a parameter, method or buyer specification is not listed, send the requirement and the laboratory will confirm scope and method.', 'envi-tech-al-modern'); ?></p>
                </section>
            </article>

            <aside class="eta-service-command-panel">
                <h2><?php esc_html_e('Plan the request', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Send parameters, water source, location, report purpose and deadline so the laboratory team can confirm scope, quotation and sampling schedule.', 'envi-tech-al-modern'); ?></p>
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request a quotation', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
                <div class="eta-service-mini-list">
                    <a href="<?php echo esc_url(home_url('/karachi-environmental-lab/')); ?>"><?php esc_html_e('Karachi environmental testing lab', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/lahore-environmental-lab/')); ?>"><?php esc_html_e('Lahore environmental testing lab', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/services/equipment-calibration-services/')); ?>"><?php esc_html_e('Equipment calibration', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/ambient-air-monitoring-services/')); ?>"><?php esc_html_e('Ambient air monitoring', 'envi-tech-al-modern'); ?></a>
                </div>
            </aside>
        </div>
    </section>

    <section class="eta-service-method">
        <div class="eta-shell eta-service-method-grid">
            <div>
                <?php eta_modern_section_title('From sample to verified report', 'A controlled path from requirement to usable laboratory evidence', 'Good scoping protects cost, turnaround planning, sample validity and report usefulness.'); ?>
            </div>
            <ol class="eta-service-method-list">
                <li><span>01</span><strong><?php esc_html_e('Define the requirement', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Tell us the purpose: EPA submission, buyer audit, internal QA or troubleshooting. We match parameters, methods and report format to it.', 'envi-tech-al-modern'); ?></p></li>
                <li><span>02</span><strong><?php esc_html_e('Sampling', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Our field teams collect samples across Karachi and Lahore using correct containers, preservation and chain-of-custody documentation. For other cities, contact the laboratory before dispatch so sample acceptance and transit conditions can be confirmed.', 'envi-tech-al-modern'); ?></p></li>
                <li><span>03</span><strong><?php esc_html_e('Scope-confirmed analysis', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('The laboratory confirms the location, matrix, parameter, method, and credential status before work begins. Lahore PNAC LAB-347 covers only its published water and wastewater methods.', 'envi-tech-al-modern'); ?></p></li>
                <li><span>04</span><strong><?php esc_html_e('Verifiable reporting', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Results are issued in the agreed reporting format. Reports carrying supported verification details can be checked through the online report verification portal.', 'envi-tech-al-modern'); ?></p></li>
            </ol>
        </div>
    </section>

    <section class="eta-band eta-service-faq">
        <div class="eta-shell">
            <?php eta_modern_section_title('Frequently asked questions', 'Answers before you request a quotation'); ?>
            <div class="eta-service-faq-grid">
                <?php foreach ($faqs as $faq) : ?>
                    <article class="eta-service-faq-card">
                        <h2><?php echo esc_html($faq[0]); ?></h2>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-band eta-service-related">
        <div class="eta-shell eta-service-detail-grid">
            <div>
                <?php eta_modern_section_title('Why facilities choose our water testing laboratory', 'Scope-confirmed analysis, coordinated sampling and verifiable reporting'); ?>
                <div class="eta-check-grid">
                    <span><?php esc_html_e('Evidence that can be checked: PNAC LAB-347 for the Lahore location and listed methods, plus the official Punjab EPA laboratory record.', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('One coordinated workflow: sampling, laboratory analysis, monitoring and consultancy delivered by one team.', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Verifiable reports: each report carries verification details your regulator or buyer can check independently.', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Two laboratories: Karachi and Lahore, giving responsive coverage to Pakistan\'s main industrial corridors.', 'envi-tech-al-modern'); ?></span>
                </div>
            </div>
            <aside class="eta-service-command-panel">
                <h2><?php esc_html_e('Credential links', 'envi-tech-al-modern'); ?></h2>
                <a class="eta-text-link" href="<?php echo esc_url(home_url('/accreditations-certifications/')); ?>"><?php esc_html_e('certificates and approvals', 'envi-tech-al-modern'); ?></a>
                <a class="eta-text-link" href="<?php echo esc_url(home_url('/karachi-environmental-lab/')); ?>"><?php esc_html_e('Karachi environmental testing lab', 'envi-tech-al-modern'); ?></a>
                <a class="eta-text-link" href="<?php echo esc_url(home_url('/lahore-environmental-lab/')); ?>"><?php esc_html_e('Lahore environmental testing lab', 'envi-tech-al-modern'); ?></a>
                <a class="eta-text-link" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Contact page', 'envi-tech-al-modern'); ?></a>
            </aside>
        </div>
    </section>

    <section class="eta-services-final-cta">
        <div class="eta-shell eta-services-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Need water testing with a deadline attached?', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Send the requirement today - parameters, purpose and timeline - and the laboratory team will confirm scope, quotation and sampling schedule.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-services-final-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request a quotation', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_download_links($content)
{
    $legacy_public_docs = [
        home_url('/downloaddocs/SindhLaws/Environmental%20Samples%20Rules%202014.pdf'),
        home_url('/downloaddocs/SindhLaws/Hazardous%20Substance%20Rules%202014.pdf'),
        home_url('/downloaddocs/SindhLaws/SEQS%202016.pdf'),
        home_url('/downloaddocs/SindhLaws/Sindh%20Environmental%20Protection%20Tribunal%20Rules%202014.pdf'),
        home_url('/downloaddocs/SindhLaws/SMART%20Rules%202014.pdf'),
        home_url('/downloaddocs/SindhLaws/Sindh%20Hospital%20Waste%20Mgmt%20Rules%202014.pdf'),
        home_url('/downloaddocs/NationalLawsOfPK/FACTORIES%20ACT%201934.pdf'),
        home_url('/downloaddocs/NationalLawsOfPK/National%20Environmental%20Policy%202005.pdf'),
        home_url('/downloaddocs/NationalLawsOfPK/Building%20Code%20of%20Pakistan-Fire%20Safety%20Provisions-2016.pdf'),
    ];

    $attachments = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 24,
        'post_mime_type' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    $media_links = array_values(array_filter(array_map(function ($attachment) {
        return wp_get_attachment_url($attachment->ID);
    }, $attachments)));

    return array_values(array_unique(array_merge($media_links, $legacy_public_docs)));
}

function eta_modern_contact_form_shortcode($content)
{
    if (preg_match('/\[contact-form-7[^\]]+\]/', (string) $content, $match)) {
        return $match[0];
    }

    return '';
}

function eta_modern_render_contact_page()
{
    ?>
    <section class="eta-contact-hero" aria-labelledby="eta-contact-title">
        <div class="eta-shell eta-contact-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Contact Envi Tech AL', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-contact-title"><?php esc_html_e('Send the requirement. Get the right environmental testing path.', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('For EPA-related testing, water and wastewater analysis, calibration, monitoring, ballast water testing, and consultancy, share the scope with the lab team so the applicable method, location, and reporting route can be confirmed.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="#eta-contact-form"><?php esc_html_e('Start a request', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-contact-direct-card" aria-label="<?php esc_attr_e('Direct contact options', 'envi-tech-al-modern'); ?>">
                <span><?php esc_html_e('Fast contact', 'envi-tech-al-modern'); ?></span>
                <a href="tel:+923102288801">0310-2288801</a>
                <a href="tel:+923152006074">0315-2006074</a>
                <a href="tel:+924232296099">+92 42 32296099</a>
                <a href="mailto:info@envitechal.com">info@envitechal.com</a>
            </aside>
        </div>
    </section>

    <section class="eta-contact-proof" aria-label="<?php esc_attr_e('Contact support strengths', 'envi-tech-al-modern'); ?>">
        <div class="eta-shell eta-contact-proof-grid">
            <span><?php esc_html_e('Reports matched to defined requirements', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Karachi and Lahore offices', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Field, lab, and advisory support', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('ISO-led quality discipline', 'envi-tech-al-modern'); ?></span>
        </div>
    </section>

    <section class="eta-band eta-contact-request-band">
        <div class="eta-shell eta-contact-request-grid">
            <div class="eta-contact-form-copy">
                <?php eta_modern_section_title('Request intake', 'Tell us what needs to be tested, monitored, verified, or submitted', 'A clear first message helps the team match your requirement to the right service, timeline, parameters, and reporting format.'); ?>
                <div class="eta-contact-intake-list">
                    <span><?php esc_html_e('Sample type or facility location', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Purpose: EPA, audit, buyer, export, safety, or internal review', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Deadline, site visit need, and preferred contact method', 'envi-tech-al-modern'); ?></span>
                </div>
            </div>
            <div id="eta-contact-form" class="eta-form-panel eta-contact-form-panel">
                <?php echo do_shortcode('[contact-form-7 id="21" title="Page 12-27"]'); ?>
            </div>
        </div>
    </section>

    <section class="eta-band eta-contact-offices">
        <div class="eta-shell">
            <?php eta_modern_section_title('Office network', 'Karachi and Lahore support for compliance-critical work', 'Use the closest office for testing coordination, sample discussion, consultancy requests, and follow-up on reports.'); ?>
            <div class="eta-contact-office-grid">
                <article class="eta-contact-office-card">
                    <div class="eta-contact-office-copy">
                        <p class="eta-mini-kicker"><?php esc_html_e('Head Office', 'envi-tech-al-modern'); ?></p>
                        <h2><?php esc_html_e('Karachi Office', 'envi-tech-al-modern'); ?></h2>
                        <p><?php esc_html_e('First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS, Karachi, Sindh 75900, Pakistan.', 'envi-tech-al-modern'); ?></p>
                        <a href="tel:+923102288801">+92 310 2288801</a>
                        <a href="tel:+923152006074">+92 315 2006074</a>
                        <a href="mailto:info@envitechal.com">info@envitechal.com</a>
                    </div>
                    <iframe class="no-lazy" data-no-lazy="1" title="<?php esc_attr_e('Karachi office map', 'envi-tech-al-modern'); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3619.4132236327655!2d67.06932881033588!3d24.883882777821796!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3eb3370ec3447301%3A0x39941bfe15f4a93f!2sEnvi%20Tech%20AL%20-%20Environmental%20Consultancy%20%26%20Water%20Testing%20Lab%20Services%20Karachi!5e0!3m2!1sen!2sae!4v1704387871286!5m2!1sen!2sae"></iframe>
                </article>

                <article class="eta-contact-office-card">
                    <div class="eta-contact-office-copy">
                        <p class="eta-mini-kicker"><?php esc_html_e('Regional Office', 'envi-tech-al-modern'); ?></p>
                        <h2><?php esc_html_e('Lahore Office', 'envi-tech-al-modern'); ?></h2>
                        <p><?php esc_html_e('87-E Madina Heights, Office A/30-31, 8th Floor, Maulana Shaukat Ali Road, Johar Town, Lahore, Punjab, Pakistan.', 'envi-tech-al-modern'); ?></p>
                        <a href="tel:+924232296099">+92 42 32296099</a>
                        <a href="mailto:info@envitechal.com">info@envitechal.com</a>
                    </div>
                    <iframe class="no-lazy" data-no-lazy="1" title="<?php esc_attr_e('Lahore office map', 'envi-tech-al-modern'); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3402.861179118045!2d74.29714724390335!3d31.473004708007117!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x391903fc9371aa23%3A0x93f56fed04917cce!2sMadina%20Heights%2C%20Maulana%20Shaukat%20Ali%20Rd%2C%20Block%20E%20Phase%201%20Johar%20Town%2C%20Lahore%2C%20Punjab%2C%20Pakistan!5e0!3m2!1sen!2sae!4v1704388322687!5m2!1sen!2sae"></iframe>
                </article>
            </div>
        </div>
    </section>

    <section class="eta-contact-final">
        <div class="eta-shell eta-contact-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Before you submit', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('The better the scope, the faster the right quote.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-contact-final-points">
                <span><?php esc_html_e('Attach or mention previous report references when available.', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('Mention city, facility type, sample category, and deadline.', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('For urgent field visits, call or WhatsApp the team directly.', 'envi-tech-al-modern'); ?></span>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_render_about_page()
{
    $about_image = 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Testing-Lab.png';
    ?>
    <section class="eta-about-hero" aria-labelledby="eta-about-title">
        <img class="eta-about-hero-img" src="<?php echo esc_url($about_image); ?>" alt="" aria-hidden="true" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-about-hero-grid">
            <div class="eta-about-hero-copy">
                <p class="eta-eyebrow"><?php esc_html_e('About Envi Tech AL', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-about-title"><?php esc_html_e('Environmental intelligence for industries that cannot afford uncertain compliance.', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Envi Tech AL brings environmental testing, technical advisory, social compliance, regulatory guidance, certification support, and analytical services into one practical partner for industry.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Discuss a requirement', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('Explore services', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-about-hero-card" aria-label="<?php esc_attr_e('Company focus', 'envi-tech-al-modern'); ?>">
                <span><?php esc_html_e('Built for', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('Textile, leather, footwear, pharmaceutical, food, commercial, maritime, healthcare, and industrial clients.', 'envi-tech-al-modern'); ?></strong>
            </aside>
        </div>
    </section>

    <section class="eta-about-proof" aria-label="<?php esc_attr_e('About page proof points', 'envi-tech-al-modern'); ?>">
        <div class="eta-shell eta-about-proof-grid">
            <span><?php esc_html_e('Environmental testing laboratory', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Regulatory compliance advisory', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Industrial monitoring and calibration', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Karachi and Lahore support', 'envi-tech-al-modern'); ?></span>
        </div>
    </section>

    <section class="eta-band eta-about-positioning">
        <div class="eta-shell">
            <?php
            eta_modern_render_ai_summary_block(
                'Envi Tech AL entity summary',
                'Envi Tech AL is a Pakistan-based environmental testing laboratory, environmental consultancy, monitoring, calibration support, and compliance support company serving clients through Karachi and Lahore office coverage.',
                ['Environmental testing and analytical laboratory services', 'Water, wastewater, air, noise, soil and waste testing', 'SEPA/EPA compliance, IEE, EIA, EMP and EMR support', 'Report verification and credential support']
            );
            ?>
            <div class="eta-about-capability-grid">
                <article class="eta-about-capability-card">
                    <h3><?php esc_html_e('What Envi Tech AL does', 'envi-tech-al-modern'); ?></h3>
                    <p><?php esc_html_e('Envi Tech AL provides environmental testing, water and wastewater analysis, stack emission testing, ambient air monitoring, noise monitoring, soil and hazardous waste testing, industrial hygiene monitoring, calibration support, report verification and environmental consultancy.', 'envi-tech-al-modern'); ?></p>
                    <a class="eta-text-link" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('Services', 'envi-tech-al-modern'); ?></a>
                </article>
                <article class="eta-about-capability-card">
                    <h3><?php esc_html_e('Who Envi Tech AL serves', 'envi-tech-al-modern'); ?></h3>
                    <p><?php esc_html_e('The company supports industries, hospitals, hotels, textile and leather units, exporters, commercial facilities, maritime operators, construction projects, consultants, EHS teams, auditors and procurement teams across Karachi, Lahore and Pakistan.', 'envi-tech-al-modern'); ?></p>
                    <a class="eta-text-link" href="<?php echo esc_url(home_url('/karachi-environmental-lab/')); ?>"><?php esc_html_e('Karachi and Lahore coverage', 'envi-tech-al-modern'); ?></a>
                </article>
                <article class="eta-about-capability-card">
                    <h3><?php esc_html_e('Why clients trust Envi Tech AL', 'envi-tech-al-modern'); ?></h3>
                    <p><?php esc_html_e('Clients use Envi Tech AL for controlled laboratory discipline, EPA-related credential categories, quality and environmental management system credentials, report verification support and practical technical guidance for audit and regulatory decisions.', 'envi-tech-al-modern'); ?></p>
                    <a class="eta-text-link" href="<?php echo esc_url(home_url('/accreditations-certifications/')); ?>"><?php esc_html_e('Certifications and approvals', 'envi-tech-al-modern'); ?></a>
                </article>
                <article class="eta-about-capability-card">
                    <h3><?php esc_html_e('Official links and profiles', 'envi-tech-al-modern'); ?></h3>
                    <p><?php esc_html_e('Use the report verification portal for report checks, the contact page for quotation and credential support, and official social profiles where current company updates are needed.', 'envi-tech-al-modern'); ?></p>
                    <a class="eta-text-link" href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Report verification portal', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-text-link" href="<?php echo esc_url('https://www.facebook.com/envitechal'); ?>" target="_blank" rel="noopener"><?php esc_html_e('Facebook', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-text-link" href="<?php echo esc_url('https://www.linkedin.com/company/envitech-al'); ?>" target="_blank" rel="noopener"><?php esc_html_e('LinkedIn', 'envi-tech-al-modern'); ?></a>
                </article>
            </div>
        </div>
    </section>

    <section class="eta-band eta-about-positioning">
        <div class="eta-shell eta-about-positioning-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Who we are', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('A technical partner for sustainable business, audit confidence, and regulatory clarity.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-about-positioning-copy">
                <p><?php esc_html_e('We support industrial partners that need to transform compliance obligations into competitive advantage. Our work spans environmental, technical, social, and regulatory requirements, supported by advisory discipline and analytical laboratory services.', 'envi-tech-al-modern'); ?></p>
                <p><?php esc_html_e('Clients are treated as long-term partners. When a requirement is not the right fit today, the relationship still matters because compliance maturity is built through follow-up, communication, and practical guidance over time.', 'envi-tech-al-modern'); ?></p>
            </div>
        </div>
    </section>

    <section class="eta-band eta-about-capabilities">
        <div class="eta-shell">
            <?php eta_modern_section_title('Capability platform', 'One company, multiple technical pathways', 'A premium environmental partner should help clients define the requirement, execute the technical work, and communicate the result clearly.'); ?>
            <div class="eta-about-capability-grid">
                <?php
                $capabilities = [
                    ['Laboratory analysis', 'Water, wastewater, air, emission, and environmental testing support for operational and regulatory decisions.'],
                    ['Consultancy and approvals', 'IEE, EIA, EMP, EMR, audit support, monitoring plans, and environmental regulatory coordination.'],
                    ['Calibration and inspection', 'Equipment calibration and thermal imaging support for quality, safety, and preventive maintenance teams.'],
                    ['Certification advisory', 'ISO and regulatory certification guidance, gap assessment, documentation, and audit-readiness planning.'],
                ];
                foreach ($capabilities as $index => $capability) :
                    ?>
                    <article class="eta-about-capability-card">
                        <span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <h3><?php echo esc_html($capability[0]); ?></h3>
                        <p><?php echo esc_html($capability[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-about-vision">
        <div class="eta-shell eta-about-vision-grid">
            <article>
                <p class="eta-mini-kicker"><?php esc_html_e('Vision', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('To be a sagacious, candid, and pragmatic advisory firm with multifaceted analytical strength.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Our vision is to serve industrial partners through intellectual assets, technical clarity, and high-tech analytical services that support sustainable business in a knowledge economy.', 'envi-tech-al-modern'); ?></p>
            </article>
            <article>
                <p class="eta-mini-kicker"><?php esc_html_e('Mission', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('To help clients achieve environmental, technical, social, regulatory, and certification requirements.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Our professional advisory team shares explicit and tacit industrial knowledge so partners receive guidance that is technically sound, usable, and aligned with real operational conditions.', 'envi-tech-al-modern'); ?></p>
            </article>
        </div>
    </section>

    <section class="eta-band eta-about-service-links">
        <div class="eta-shell">
            <?php eta_modern_section_title('Business portfolio', 'Core services connected to the company story', 'Move from company confidence into the exact service path your project needs.'); ?>
            <div class="eta-grid eta-grid-3">
                <?php foreach (eta_modern_service_posts(6) as $service) : ?>
                    <?php eta_modern_card_link($service, 'eta-post-card'); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-about-final">
        <div class="eta-shell eta-about-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Partnership mindset', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('The right technical partner protects decisions before they become expensive.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-about-final-points">
                <span><?php esc_html_e('Define the requirement clearly.', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('Execute with laboratory and field discipline.', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('Deliver reporting that teams can use.', 'envi-tech-al-modern'); ?></span>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_download_groups()
{
    return [
        'Certifications and accreditations' => [
            ['ISO 9001:2015 Certificate', 'https://envitechal.com/wp-content/uploads/2026/01/ISO-9001-Certificate.pdf'],
            ['ISO 14001:2015 Certificate', 'https://envitechal.com/wp-content/uploads/2026/01/ISO-14001-Certificate.pdf'],
            ['Sindh EPA published document — confirm current status', 'https://envitechal.com/wp-content/uploads/2026/01/SEPA-NOC.pdf'],
            ['Punjab EPA Certificate 2025–2028 (official source)', 'https://epd.punjab.gov.pk/system/files/EnviTech_%202025-2028_merged.pdf'],
            ['PNAC LAB-347 Lahore scope (official source)', 'https://www.pnac.gov.pk/index.php/pdfFiles/LAB-347'],
        ],
        'Sindh environmental laws' => [
            ['SEQS compliance guide', home_url('/sindh-environmental-quality-standards-seqs/')],
            ['EIA / IEE Regulations 2021', 'https://envitechal.com/wp-content/uploads/2022/08/Final-Review-of-EC-IEE-EIA-Regulation-2021.pdf'],
            ['Environmental Samples Rules 2014', 'https://envitechal.com/downloaddocs/SindhLaws/Environmental%20Samples%20Rules%202014.pdf'],
            ['Hazardous Substance Rules 2014', 'https://envitechal.com/downloaddocs/SindhLaws/Hazardous%20Substance%20Rules%202014.pdf'],
            ['SEQS 2016', 'https://envitechal.com/downloaddocs/SindhLaws/SEQS%202016.pdf'],
            ['Sindh Environmental Protection Act 2014', 'https://envitechal.com/wp-content/uploads/2025/10/Sindh-Environmental-Protection-Act-2014.pdf'],
            ['Sindh Environmental Protection Tribunal Rules 2014', 'https://envitechal.com/downloaddocs/SindhLaws/Sindh%20Environmental%20Protection%20Tribunal%20Rules%202014.pdf'],
            ['SMART Rules 2014', 'https://envitechal.com/downloaddocs/SindhLaws/SMART%20Rules%202014.pdf'],
            ['Sindh Hospital Waste Management Rules 2014', 'https://envitechal.com/downloaddocs/SindhLaws/Sindh%20Hospital%20Waste%20Mgmt%20Rules%202014.pdf'],
        ],
        'National laws of Pakistan' => [
            ['Factories Act 1934', 'https://envitechal.com/downloaddocs/NationalLawsOfPK/FACTORIES%20ACT%201934.pdf'],
            ['National Environmental Policy 2005', 'https://envitechal.com/downloaddocs/NationalLawsOfPK/National%20Environmental%20Policy%202005.pdf'],
            ['Building Code of Pakistan - Fire Safety Provisions 2016', 'https://envitechal.com/downloaddocs/NationalLawsOfPK/Building%20Code%20of%20Pakistan-Fire%20Safety%20Provisions-2016.pdf'],
            ['Sindh Factories Act 2015', 'https://envitechal.com/wp-content/uploads/2022/08/Sindh-Factories-Act-2015.pdf'],
        ],
        'Pakistan Accord resources' => [
            ['Pakistan Accord Building Standard Version 1.1', 'https://envitechal.com/wp-content/uploads/2024/11/Pakistan-Accord-Building-Standard-Version-1-1.pdf'],
            ['Briefing for Manufacturers', 'https://envitechal.com/wp-content/uploads/2024/11/Briefing-for-Manufacturers_-Jan-2024.pdf'],
            ['Pakistan Accord FAQs', 'https://envitechal.com/wp-content/uploads/2024/11/Pakistan-Accord-FAQs-Dec-2022.pdf'],
        ],
    ];
}

function eta_modern_render_downloads_page()
{
    $groups = eta_modern_download_groups();
    $resource_count = array_sum(array_map('count', $groups));
    $download_image = 'https://envitechal.com/wp-content/uploads/2026/06/Regulatory-compliance-Advisory.png';
    ?>
    <section class="eta-download-hero" aria-labelledby="eta-download-title">
        <img class="eta-download-hero-img" src="<?php echo esc_url($download_image); ?>" alt="" aria-hidden="true" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-download-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Downloads', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-download-title"><?php esc_html_e('Compliance documents, certificates, and regulatory references in one clean resource center.', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Access Envi Tech AL certificates, environmental laws, Sindh EPA resources, national compliance documents, and Pakistan Accord references for audit planning and regulatory review.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="#eta-download-library"><?php esc_html_e('Browse resources', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Ask for guidance', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-download-hero-panel">
                <span><?php esc_html_e('Resource library', 'envi-tech-al-modern'); ?></span>
                <strong><?php echo esc_html($resource_count); ?></strong>
                <p><?php esc_html_e('curated certificates and compliance references', 'envi-tech-al-modern'); ?></p>
            </aside>
        </div>
    </section>

    <section class="eta-download-proof" aria-label="<?php esc_attr_e('Download library categories', 'envi-tech-al-modern'); ?>">
        <div class="eta-shell eta-download-proof-grid">
            <?php foreach (array_keys($groups) as $group_name) : ?>
                <a href="#<?php echo esc_attr(sanitize_title($group_name)); ?>"><?php echo esc_html($group_name); ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="eta-download-library" class="eta-band eta-download-library">
        <div class="eta-shell">
            <?php eta_modern_section_title('Document library', 'Audit-ready resources, grouped for fast selection', 'Each document opens in a new tab so teams can keep this library available while preparing submissions, audits, and internal reviews.'); ?>
            <div class="eta-download-group-grid">
                <?php foreach ($groups as $group_name => $links) : ?>
                    <article id="<?php echo esc_attr(sanitize_title($group_name)); ?>" class="eta-download-group-card">
                        <div class="eta-download-group-head">
                            <span><?php echo esc_html(count($links)); ?></span>
                            <h2><?php echo esc_html($group_name); ?></h2>
                        </div>
                        <div class="eta-download-item-list">
                            <?php foreach ($links as $link) : ?>
                                <a href="<?php echo esc_url($link[1]); ?>" target="_blank" rel="noopener">
                                    <span><?php echo esc_html($link[0]); ?></span>
                                    <em><?php esc_html_e('PDF', 'envi-tech-al-modern'); ?></em>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-download-guidance">
        <div class="eta-shell eta-download-guidance-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Use with confidence', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Not sure which standard or certificate applies?', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-download-guidance-copy">
                <p><?php esc_html_e('If a document is needed for a buyer audit, EPA submission, factory review, or certification exercise, our team can help align the reference with the right testing, monitoring, or advisory scope.', 'envi-tech-al-modern'); ?></p>
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request document guidance', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_post_image_url($post = null)
{
    $post = get_post($post);
    if (eta_modern_is_emr_emp_post($post)) {
        return eta_modern_emr_emp_share_image();
    }

    if ($post && has_post_thumbnail($post)) {
        $image = get_the_post_thumbnail_url($post, 'large');
        if ($image) {
            return $image;
        }
    }

    return 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Consulting-Services.png';
}

function eta_modern_post_hero_image_url($post = null)
{
    $topic = eta_modern_post_topic_label($post);
    if ($topic === 'Water testing') {
        return 'https://envitechal.com/wp-content/uploads/2026/05/water-testing-services-karachi-lahore.png';
    }

    if ($topic === 'Calibration') {
        return 'https://envitechal.com/wp-content/uploads/2026/06/Calibration-Services.png';
    }

    if ($topic === 'Compliance') {
        return 'https://envitechal.com/wp-content/uploads/2026/06/Regulatory-compliance-Advisory-Services.png';
    }

    if ($topic === 'Environmental advisory') {
        return 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Consulting-Services.png';
    }

    return 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Testing-Lab.png';
}


function eta_modern_post_topic_label($post = null)
{
    $post = get_post($post);
    if (!$post) {
        return 'Knowledge';
    }

    $title = strtolower((string) $post->post_title);
    if (strpos($title, 'water') !== false) {
        return 'Water testing';
    }
    if (strpos($title, 'environmental testing lab') !== false || (strpos($title, 'environmental') !== false && strpos($title, 'lab') !== false)) {
        return 'Environmental testing lab';
    }
    if (strpos($title, 'calibration') !== false) {
        return 'Calibration';
    }
    if (strpos($title, 'epa') !== false || strpos($title, 'noc') !== false || strpos($title, 'compliance') !== false) {
        return 'Compliance';
    }
    if (strpos($title, 'environmental') !== false || strpos($title, 'consult') !== false) {
        return 'Environmental advisory';
    }

    return 'Technical insight';
}

function eta_modern_post_meta_description($post = null)
{
    $post = get_post($post);
    if (!$post) {
        return 'Read Envi Tech AL technical insights on environmental testing, water testing, calibration, compliance, and consultancy decisions in Pakistan.';
    }

    if (eta_modern_is_emr_emp_post($post)) {
        return eta_modern_emr_emp_meta_description();
    }

    $title = eta_modern_display_title($post);
    $topic = eta_modern_post_topic_label($post);

    if ($topic === 'Water testing') {
        return 'Read Envi Tech AL guidance on water testing, lab reports, compliance decisions, and safe water quality planning for Karachi, Lahore, and Pakistan.';
    }

    if ($topic === 'Calibration') {
        return 'Read Envi Tech AL guidance on equipment calibration, measurement reliability, audit readiness, and industrial quality decisions in Pakistan.';
    }

    if ($topic === 'Compliance') {
        return 'Read Envi Tech AL guidance on EPA NOC, environmental compliance, regulatory documentation, audit preparation, and practical next steps for businesses in Pakistan.';
    }

    if ($topic === 'Environmental testing lab') {
        return 'Read Envi Tech AL guidance on environmental testing lab services, monitoring scope, report use, and compliance evidence for Karachi, Lahore, and Pakistan.';
    }

    if ($topic === 'Environmental advisory') {
        return 'Read Envi Tech AL guidance on environmental consultancy, monitoring, regulatory submissions, and compliance planning for industrial and commercial projects.';
    }

    $description = sprintf(
        'Read Envi Tech AL insight: %s. Practical environmental testing, compliance, calibration, and consultancy guidance for business decisions in Pakistan.',
        $title
    );

    if (function_exists('mb_strlen') && mb_strlen($description) > 172) {
        return wp_trim_words($description, 23, '.');
    }

    if (strlen($description) > 172) {
        return wp_trim_words($description, 23, '.');
    }

    return $description;
}

function eta_modern_post_card_excerpt($post = null)
{
    $post = get_post($post);
    if (!$post) {
        return 'Practical Envi Tech AL guidance for environmental testing, compliance, calibration, and advisory decisions.';
    }

    $topic = eta_modern_post_topic_label($post);
    if ($topic === 'Water testing') {
        return 'Understand water testing requirements, report use, safety expectations, and the next step before choosing a lab scope.';
    }

    if ($topic === 'Calibration') {
        return 'A practical look at calibration reliability, traceability, audit readiness, and measurement confidence for industry.';
    }

    if ($topic === 'Compliance') {
        return 'Guidance for EPA NOC, compliance documents, audit preparation, and regulatory decisions that need defensible evidence.';
    }

    if ($topic === 'Environmental testing lab') {
        return 'Guidance on environmental testing lab services, sampling scope, compliance evidence, and practical reporting for Karachi, Lahore, and Pakistan.';
    }

    if ($topic === 'Environmental advisory') {
        return 'Insight for environmental consultancy, monitoring, approvals, and compliance planning for business-critical projects.';
    }

    return 'Technical guidance from Envi Tech AL for laboratory, compliance, calibration, and consultancy decisions in Pakistan.';
}

function eta_modern_post_seo_title($post = null)
{
    $post = get_post($post);
    if (!$post) {
        return 'Environmental Testing Insights | Envi Tech AL';
    }

    if (eta_modern_is_emr_emp_post($post)) {
        return eta_modern_emr_emp_seo_title();
    }

    $title = eta_modern_display_title($post);
    $title_lower = strtolower($title);

    if (strpos($title_lower, 'sindh epa noc') !== false || strpos($title_lower, 'epa noc') !== false) {
        return 'Sindh EPA NOC Guide 2026 | Envi Tech AL';
    }

    if (strpos($title_lower, 'water testing') !== false && strpos($title_lower, 'karachi') !== false && strpos($title_lower, 'lahore') !== false) {
        return 'Water Testing Lab Karachi & Lahore | Envi Tech AL';
    }

    if (strpos($title_lower, 'calibration') !== false) {
        return 'Calibration Services in Karachi | Envi Tech AL';
    }

    if (strpos($title_lower, 'environmental testing lab') !== false) {
        return 'Environmental Testing Lab Karachi & Lahore | Envi Tech AL';
    }

    if (strpos($title_lower, 'environmental consulting') !== false || strpos($title_lower, 'environmental consultancy') !== false) {
        return 'Environmental Consultancy in Pakistan | Envi Tech AL';
    }

    $suffix = ' | Envi Tech AL';
    $max_title_length = 68 - strlen($suffix);
    if (strlen($title) > $max_title_length) {
        $title = rtrim(substr($title, 0, $max_title_length), " \t\n\r\0\x0B,.-");
        $title = eta_modern_preg_replace('/\s+\S*$/', '', $title);
        $title = trim($title) ?: eta_modern_display_title($post);
    }

    return $title . $suffix;
}

function eta_modern_render_report_verification_page()
{
    $hero_image = 'https://envitechal.com/wp-content/uploads/2026/06/Regulatory-compliance-Advisory-Services.png';
    ?>
    <section class="eta-verify-hero" aria-labelledby="eta-verify-title">
        <img class="eta-verify-hero-img" src="<?php echo esc_url($hero_image); ?>" alt="" aria-hidden="true" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-verify-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Report Verification Portal', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-verify-title"><?php esc_html_e('Verify the authenticity of an Envi Tech AL test report with confidence.', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Use the QR code printed on your report or submit a manual verification request with report number, reporting date, and company name.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="#eta-verification-form"><?php esc_html_e('Submit verification request', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="mailto:info@envitechal.com"><?php esc_html_e('Email support', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-verify-card">
                <span><?php esc_html_e('Required details', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('Report number, report date, and company name.', 'envi-tech-al-modern'); ?></strong>
            </aside>
        </div>
    </section>

    <section class="eta-verify-proof" aria-label="<?php esc_attr_e('Verification trust points', 'envi-tech-al-modern'); ?>">
        <div class="eta-shell eta-verify-proof-grid">
            <span><?php esc_html_e('QR-code verification path', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Manual authenticity request', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Lab-team review', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Working-day response support', 'envi-tech-al-modern'); ?></span>
        </div>
    </section>

    <section class="eta-band eta-verify-workflow">
        <div class="eta-shell">
            <?php eta_modern_section_title('Verification workflow', 'A clear path for clients, auditors, and buyers', 'Report verification protects decisions by confirming that the document was issued by Envi Tech AL and matches the identifying details provided.'); ?>
            <div class="eta-verify-step-grid">
                <?php
                $steps = [
                    ['Scan the QR code', 'Each test report includes a unique QR code where available. Scan it first for direct authenticity confirmation.'],
                    ['Submit manual details', 'If QR verification is unavailable, submit report number, report date, company name, and contact information.'],
                    ['Receive team confirmation', 'The verification team checks the provided details and responds with verified information during working hours.'],
                ];
                foreach ($steps as $index => $step) :
                    ?>
                    <article class="eta-verify-step-card">
                        <span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <h2><?php echo esc_html($step[0]); ?></h2>
                        <p><?php echo esc_html($step[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-band eta-verify-request">
        <div class="eta-shell eta-verify-request-grid">
            <div class="eta-verify-request-copy">
                <p class="eta-eyebrow"><?php esc_html_e('Manual verification', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Send the exact report details so our team can verify quickly.', 'envi-tech-al-modern'); ?></h2>
                <div class="eta-verify-detail-list">
                    <span><?php esc_html_e('Report number printed on the document', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Reporting date', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Company or client name', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Requester name, phone, and email', 'envi-tech-al-modern'); ?></span>
                </div>
            </div>
            <div id="eta-verification-form" class="eta-form-panel eta-verify-form-panel">
                <?php echo do_shortcode('[contact-form-7 id="22994" title="Report Verification Request"]'); ?>
            </div>
        </div>
    </section>

    <section class="eta-band eta-verify-depth">
        <div class="eta-shell">
            <?php eta_modern_section_title('Trust and traceability', 'Verification supports audits, buyers, regulators, and internal decision-makers', 'A report is only useful when its origin, details, and intended record can be trusted. These guidance sections preserve the information clients need before relying on a report.'); ?>
            <div class="eta-verify-depth-grid">
                <article>
                    <h2><?php esc_html_e('Who can use this portal?', 'envi-tech-al-modern'); ?></h2>
                    <ul>
                        <li><?php esc_html_e('Clients confirming issued reports before internal use or onward sharing.', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Buyer representatives and brand compliance teams reviewing supplier evidence.', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Third-party auditors validating records during assessments or site visits.', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Regulatory reviewers, consultants, project teams, and factory compliance teams.', 'envi-tech-al-modern'); ?></li>
                    </ul>
                </article>
                <article>
                    <h2><?php esc_html_e('Reports commonly verified', 'envi-tech-al-modern'); ?></h2>
                    <ul>
                        <li><?php esc_html_e('Drinking water, wastewater, and effluent testing reports.', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Stack emission, ambient air, noise, lux, and environmental monitoring reports.', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Calibration reports and selected consultancy or compliance deliverables.', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Project documentation where authenticity and traceability are business-critical.', 'envi-tech-al-modern'); ?></li>
                    </ul>
                </article>
                <article>
                    <h2><?php esc_html_e('Authenticity checklist', 'envi-tech-al-modern'); ?></h2>
                    <ul>
                        <li><?php esc_html_e('Check that the report number is readable and matches the issued copy.', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Confirm company name, reporting date, and report type before sharing further.', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Scan the QR code from the original report where possible.', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Keep the full report copy available for manual support if requested.', 'envi-tech-al-modern'); ?></li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="eta-band eta-verify-faq">
        <div class="eta-shell">
            <?php eta_modern_section_title('Report verification FAQ', 'Common questions before relying on a report', 'Short answers for clients, auditors, buyers, and internal reviewers who need a verified record.'); ?>
            <div class="eta-verify-faq-grid">
                <?php
                $faqs = [
                    ['How can I verify an Envi Tech AL report?', 'Scan the QR code printed on the report where available, or submit a manual request with report number, report date, and company name.'],
                    ['What if the QR code is not working?', 'Email the required report details to info@envitechal.com or use the portal form so the support team can review the record manually.'],
                    ['Can buyers or auditors verify directly?', 'Yes. Buyers, auditors, consultants, and client representatives may use the portal to support authenticity and traceability checks.'],
                    ['How long does manual verification take?', 'Response timing depends on working hours, report type, and record availability. Complete details help the team respond faster.'],
                ];
                foreach ($faqs as $faq) :
                    ?>
                    <article class="eta-verify-faq-card">
                        <h2><?php echo esc_html($faq[0]); ?></h2>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-verify-support">
        <div class="eta-shell eta-verify-support-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Need assistance?', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('For urgent verification support, contact the team directly during business hours.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-verify-support-list">
                <a href="mailto:info@envitechal.com">info@envitechal.com</a>
                <a href="tel:+923102288801">0310-2288801</a>
                <span><?php esc_html_e('Monday to Saturday, 9:00 AM to 6:00 PM', 'envi-tech-al-modern'); ?></span>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_render_knowledge_hub_page()
{
    $posts = eta_modern_latest_posts(12);
    $featured = $posts ? array_shift($posts) : null;
    $hero_image = 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Consulting-Services.png';
    ?>
    <section class="eta-knowledge-hero" aria-labelledby="eta-knowledge-title">
        <img class="eta-knowledge-hero-img" src="<?php echo esc_url($hero_image); ?>" alt="" aria-hidden="true" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-knowledge-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Knowledge Hub', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-knowledge-title"><?php esc_html_e('Environmental testing and compliance insight for better technical decisions.', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Guides, lab updates, and practical explainers for water testing, EPA approvals, calibration, environmental consultancy, and industrial compliance in Pakistan.', 'envi-tech-al-modern'); ?></p>
            </div>
            <?php if ($featured) : ?>
                <article class="eta-knowledge-featured-card">
                    <span><?php echo esc_html(eta_modern_post_topic_label($featured)); ?></span>
                    <h2><a href="<?php echo esc_url(get_permalink($featured)); ?>"><?php echo esc_html(eta_modern_display_title($featured)); ?></a></h2>
                    <p><?php echo esc_html(eta_modern_post_card_excerpt($featured)); ?></p>
                    <a class="eta-text-link" href="<?php echo esc_url(get_permalink($featured)); ?>"><?php esc_html_e('Read featured insight', 'envi-tech-al-modern'); ?></a>
                </article>
            <?php endif; ?>
        </div>
    </section>

    <section class="eta-knowledge-proof">
        <div class="eta-shell eta-knowledge-proof-grid">
            <span><?php esc_html_e('Water testing guides', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('EPA and NOC guidance', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Calibration and reliability', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Environmental consultancy', 'envi-tech-al-modern'); ?></span>
        </div>
    </section>

    <section class="eta-band eta-knowledge-topics">
        <div class="eta-shell eta-knowledge-topics-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Browse by topic', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Move from search intent to the right technical page.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-knowledge-topic-links">
                <a href="<?php echo esc_url(home_url('/services/water-testing-lab-services/')); ?>"><?php esc_html_e('Water Testing', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/services/analytical-lab-services/')); ?>"><?php esc_html_e('Environmental Testing', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/services/equipment-calibration-services/')); ?>"><?php esc_html_e('Calibration Services', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/services/environmental-consultancy/')); ?>"><?php esc_html_e('Environmental Consultancy', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/services/certification-advisory/')); ?>"><?php esc_html_e('Compliance & Audits', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/services/ballast-water-testing-services/')); ?>"><?php esc_html_e('Maritime Compliance', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>

    <section class="eta-band eta-knowledge-library">
        <div class="eta-shell">
            <?php eta_modern_section_title('Latest insights', 'Practical reading for compliance teams', 'Use the hub to understand the requirement before choosing a testing, advisory, or documentation path.'); ?>
            <?php if ($posts) : ?>
                <div class="eta-knowledge-grid">
                    <?php foreach ($posts as $post_item) : ?>
                        <?php eta_modern_render_post_card($post_item); ?>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <article class="eta-content"><p><?php esc_html_e('No updates were found.', 'envi-tech-al-modern'); ?></p></article>
            <?php endif; ?>
        </div>
    </section>

    <section class="eta-knowledge-cta">
        <div class="eta-shell eta-knowledge-cta-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Need a service, not just an article?', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Move from reading to a clear technical scope.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('Explore services', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Ask the lab team', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_render_post_card($post)
{
    $post = get_post($post);
    if (!$post) {
        return;
    }
    ?>
    <article class="eta-knowledge-card">
        <a class="eta-knowledge-card-media" href="<?php echo esc_url(get_permalink($post)); ?>" aria-label="<?php echo esc_attr(eta_modern_display_title($post)); ?>">
            <img src="<?php echo esc_url(eta_modern_post_image_url($post)); ?>" alt="<?php echo esc_attr(eta_modern_display_title($post)); ?>" loading="lazy">
        </a>
        <div class="eta-knowledge-card-body">
            <p class="eta-mini-kicker"><?php echo esc_html(eta_modern_post_topic_label($post)); ?></p>
            <h2><a href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html(eta_modern_display_title($post)); ?></a></h2>
            <p><?php echo esc_html(eta_modern_post_card_excerpt($post)); ?></p>
            <div class="eta-knowledge-card-meta">
                <time datetime="<?php echo esc_attr(get_the_date('c', $post)); ?>"><?php echo esc_html(get_the_date('', $post)); ?></time>
                <a href="<?php echo esc_url(get_permalink($post)); ?>" aria-label="<?php echo esc_attr(sprintf(__('Read article: %s', 'envi-tech-al-modern'), eta_modern_display_title($post))); ?>"><?php esc_html_e('Read article', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </article>
    <?php
}

function eta_modern_render_single_post_page()
{
    while (have_posts()) :
        the_post();
        $post_id = get_the_ID();
        $image = eta_modern_post_hero_image_url($post_id);
        $raw_post_content = (string) get_post_field('post_content', $post_id);
        $rendered_post_content = has_blocks($raw_post_content) ? do_blocks($raw_post_content) : get_the_content(null, false, $post_id);
        $post_content = eta_modern_clean_content($rendered_post_content);
        if (trim(wp_strip_all_tags($post_content)) === '') {
            $post_content = eta_modern_fallback_post_content($post_id);
        }
        $is_premium_legacy = eta_modern_is_premium_legacy_html($post_content);
        ?>
        <?php if (!$is_premium_legacy) : ?>
            <section class="eta-post-hero" aria-labelledby="eta-post-title">
                <img class="eta-post-hero-img" src="<?php echo esc_url($image); ?>" alt="" aria-hidden="true" loading="eager" decoding="async" fetchpriority="high">
                <div class="eta-shell eta-post-hero-grid">
                    <div>
                        <p class="eta-eyebrow"><?php echo esc_html(eta_modern_post_topic_label($post_id)); ?></p>
                        <h1 id="eta-post-title"><?php echo esc_html(eta_modern_display_title($post_id)); ?></h1>
                        <div class="eta-post-meta">
                            <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>"><?php echo esc_html(get_the_date('', $post_id)); ?></time>
                            <span><?php esc_html_e('Envi Tech AL Knowledge Hub', 'envi-tech-al-modern'); ?></span>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="eta-band eta-post-body-band">
            <?php if ($is_premium_legacy) : ?>
                <div class="eta-shell eta-post-layout eta-post-layout-wide">
                    <article class="eta-content eta-post-content eta-post-content-premium">
                        <?php if (!preg_match('#<\s*h1\b#i', $post_content)) : ?>
                            <h1 class="eta-sr-only"><?php echo esc_html(eta_modern_display_title($post_id)); ?></h1>
                        <?php endif; ?>
                        <?php echo $post_content; ?>
                    </article>
                </div>
            <?php else : ?>
                <div class="eta-shell eta-post-layout eta-post-layout-wide">
                    <article class="eta-content eta-post-content">
                        <?php echo $post_content; ?>
                    </article>
                </div>
            <?php endif; ?>
        </section>
        <?php
    endwhile;
}

function eta_modern_post_service_pathways($post_id)
{
    $title = strtolower(eta_modern_display_title($post_id));
    $topic = eta_modern_post_topic_label($post_id);

    if (strpos($title, 'water') !== false || $topic === 'Water testing') {
        return [
            '/services/water-testing-lab-services/' => 'Water testing lab services',
            '/drinking-water-testing-lab/' => 'Drinking water testing',
            '/wastewater-testing-services/' => 'Wastewater testing',
            '/report-verification-portal/' => 'Report verification',
        ];
    }

    if (strpos($title, 'calibration') !== false || $topic === 'Calibration') {
        return [
            '/services/equipment-calibration-services/' => 'Equipment calibration',
            '/industrial-hygiene-monitoring/' => 'Industrial hygiene monitoring',
            '/services/technical-advisory-2/' => 'Technical advisory',
            '/contact-us-envi-tech-al/' => 'Request calibration support',
        ];
    }

    if (strpos($title, 'consult') !== false || strpos($title, 'epa') !== false || strpos($title, 'noc') !== false || $topic === 'Compliance' || $topic === 'Environmental advisory') {
        return [
            '/services/environmental-consultancy/' => 'Environmental consultancy',
            '/emp-emr-iee-eia-compliance/' => 'EMP / EMR / IEE / EIA support',
            '/karachi-environmental-lab/' => 'Karachi environmental lab',
            '/lahore-environmental-lab/' => 'Lahore environmental lab',
        ];
    }

    if (strpos($title, 'air') !== false || strpos($title, 'emission') !== false || strpos($title, 'monitoring') !== false || strpos($title, 'noise') !== false) {
        return [
            '/gaseous-air-emission-testing-lab-near-me/' => 'Stack emission testing',
            '/ambient-air-monitoring-services/' => 'Ambient air monitoring',
            '/noise-monitoring-dosimetry/' => 'Noise monitoring',
            '/industrial-hygiene-monitoring/' => 'Industrial hygiene monitoring',
        ];
    }

    if (strpos($title, 'ballast') !== false || strpos($title, 'vessel') !== false || strpos($title, 'maritime') !== false) {
        return [
            '/services/ballast-water-testing-services/' => 'Ballast water testing',
            '/maritime-environmental-testing/' => 'Maritime environmental testing',
            '/services/analytical-lab-services/' => 'Analytical lab services',
            '/contact-us-envi-tech-al/' => 'Request maritime support',
        ];
    }

    return [
        '/services/analytical-lab-services/' => 'Analytical lab services',
        '/karachi-environmental-lab/' => 'Karachi environmental lab',
        '/lahore-environmental-lab/' => 'Lahore environmental lab',
        '/services/environmental-consultancy/' => 'Environmental consultancy',
    ];
}

function eta_modern_career_roles()
{
    return [
        [
            'title' => 'Accounts Officer',
            'location' => 'Karachi Office',
            'fit' => 'B.Com or accounts background with 2-3 years of relevant experience in invoicing, records, receivables, payables, and tax-support work.',
            'work' => ['Fast Accounts software support', 'Quotations and invoices', 'Receivables and payables', 'FBR and SRB compliance support'],
        ],
        [
            'title' => 'Assistant Manager Accounts',
            'location' => 'Karachi Office',
            'fit' => 'Bachelor-level finance or accounting profile with 3-5 years of relevant experience in financial reporting, tax compliance, and client billing coordination.',
            'work' => ['Financial reporting', 'Tax and compliance activity', 'Budget and audit support', 'Client billing follow-up'],
        ],
        [
            'title' => 'Lab, field, and business development profiles',
            'location' => 'Karachi and Lahore',
            'fit' => 'Environmental science, chemistry, field monitoring, laboratory, sales coordination, and client-facing technical-service profiles.',
            'work' => ['Lab and field support', 'Sampling and monitoring', 'Proposal support', 'Client coordination'],
        ],
    ];
}

function eta_modern_render_careers_page()
{
    $hero_image = 'https://envitechal.com/wp-content/uploads/2026/06/Industrial-compliance-Monitoring.png';
    ?>
    <section class="eta-career-hero" aria-labelledby="eta-career-title">
        <img class="eta-career-hero-img" src="<?php echo esc_url($hero_image); ?>" alt="" aria-hidden="true" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-career-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Careers at Envi Tech AL', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-career-title"><?php esc_html_e('Build a technical career where lab discipline meets real environmental decisions.', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Join a team supporting environmental testing, field monitoring, calibration, consultancy, and compliance reporting for industries across Karachi, Lahore, and Pakistan.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="#eta-career-apply"><?php esc_html_e('Submit your profile', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="mailto:hr@envitechal.com"><?php esc_html_e('Email HR', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-career-hero-card">
                <span><?php esc_html_e('Talent pool', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('Laboratory, field, consultancy, calibration, and business development pathways.', 'envi-tech-al-modern'); ?></strong>
            </aside>
        </div>
    </section>

    <section class="eta-career-proof">
        <div class="eta-shell eta-career-proof-grid">
            <span><?php esc_html_e('Karachi and Lahore teams', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Field and laboratory exposure', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Quality and compliance discipline', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Industrial client work', 'envi-tech-al-modern'); ?></span>
        </div>
    </section>

    <section class="eta-band eta-career-pathways">
        <div class="eta-shell">
            <?php eta_modern_section_title('Career pathways', 'Where strong profiles usually fit', 'The previous dated openings have been converted into evergreen hiring streams so the live site does not advertise expired deadlines.'); ?>
            <div class="eta-career-role-grid">
                <?php foreach (eta_modern_career_roles() as $role) : ?>
                    <article class="eta-career-role-card">
                        <p class="eta-mini-kicker"><?php echo esc_html($role['location']); ?></p>
                        <h2><?php echo esc_html($role['title']); ?></h2>
                        <p><?php echo esc_html($role['fit']); ?></p>
                        <ul>
                            <?php foreach ($role['work'] as $item) : ?>
                                <li><?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="eta-career-apply" class="eta-band eta-career-apply">
        <div class="eta-shell eta-career-apply-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Apply with context', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Send a focused profile, not a generic CV drop.', 'envi-tech-al-modern'); ?></h2>
                <div class="eta-career-apply-list">
                    <span><?php esc_html_e('Mention city and preferred work stream.', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Attach updated CV and relevant certificates.', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Highlight lab, field, compliance, or client-facing experience.', 'envi-tech-al-modern'); ?></span>
                </div>
            </div>
            <div class="eta-form-panel eta-career-form-panel">
                <?php echo do_shortcode('[contact-form-7 id="23" title="Page 22"]'); ?>
            </div>
        </div>
    </section>

    <section class="eta-career-final">
        <div class="eta-shell eta-career-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Hiring note', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Shortlisted candidates are contacted when an active opening matches their profile.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-career-contact-list">
                <a href="mailto:hr@envitechal.com">hr@envitechal.com</a>
                <a href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Contact offices', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_faq_page_questions()
{
    return [
        [
            'group' => 'Water testing',
            'question' => 'What water testing services does Envi Tech AL provide?',
            'answer' => 'Envi Tech AL supports drinking water, wastewater, RO plant water, bore water, process water, industrial discharge, groundwater, and facility water quality testing based on the report purpose and required parameters.',
        ],
        [
            'group' => 'Water testing',
            'question' => 'Which details are needed for a water testing quotation?',
            'answer' => 'Share the city, sample type, intended use, report purpose, deadline, required standard or buyer requirement, and whether field sampling is required. This helps the team select the right parameters and sampling plan.',
        ],
        [
            'group' => 'Water testing',
            'question' => 'Can you test drinking water for homes, offices, factories, hospitals, and hotels?',
            'answer' => 'Yes. Drinking water testing can be scoped for residential, commercial, healthcare, hospitality, educational, industrial, and institutional facilities, with parameters selected according to the final use of the report.',
        ],
        [
            'group' => 'Environmental monitoring',
            'question' => 'What environmental monitoring services are commonly requested?',
            'answer' => 'Common monitoring includes ambient air, stack emissions, noise level, workplace conditions, wastewater discharge, industrial hygiene, and facility-specific environmental checks for compliance, audit, or internal control.',
        ],
        [
            'group' => 'Environmental monitoring',
            'question' => 'Do you support stack emission and air quality testing?',
            'answer' => 'Yes. Stack emission, gaseous air emission, and ambient air monitoring can be planned around the fuel source, process, regulatory requirement, and reporting deadline.',
        ],
        [
            'group' => 'Environmental monitoring',
            'question' => 'When is noise monitoring required?',
            'answer' => 'Noise monitoring is commonly requested for workplace safety review, community impact concerns, construction activity, industrial operations, hotels, generators, regulatory submissions, and buyer audit evidence.',
        ],
        [
            'group' => 'Environmental monitoring',
            'question' => 'Can wastewater discharge testing support compliance decisions?',
            'answer' => 'Yes. Wastewater discharge testing helps facilities understand effluent quality, treatment performance, regulatory risk, operational control, and corrective actions before audit or authority review.',
        ],
        [
            'group' => 'Consultancy and approvals',
            'question' => 'Do you provide environmental consultancy for IEE, EIA, EMP, EMR, and audits?',
            'answer' => 'Yes. Envi Tech AL supports environmental consultancy, IEE, EIA, EMP, EMR, audits, documentation, monitoring coordination, and compliance guidance for industrial and commercial projects.',
        ],
        [
            'group' => 'Consultancy and approvals',
            'question' => 'Can Envi Tech AL support EPA-related requirements?',
            'answer' => 'Yes. The team can help clients understand technical scope, testing, monitoring, documentation, and reporting pathways for Sindh EPA, Punjab EPA, buyer, audit, and internal compliance requirements.',
        ],
        [
            'group' => 'Consultancy and approvals',
            'question' => 'When should I request consultancy instead of only lab testing?',
            'answer' => 'Consultancy is useful when the requirement includes approvals, submissions, corrective action planning, audit response, project documentation, environmental management plans, or interpretation beyond laboratory results.',
        ],
        [
            'group' => 'Consultancy and approvals',
            'question' => 'Can you help identify the right parameters for an audit?',
            'answer' => 'Yes. If a buyer, regulator, consultant, or auditor has shared a requirement, Envi Tech AL can review the context and help translate it into a practical testing or monitoring scope.',
        ],
        [
            'group' => 'Calibration and reports',
            'question' => 'Do you provide equipment calibration support?',
            'answer' => 'Yes. Calibration support helps laboratories, field teams, and industrial facilities maintain measurement confidence, traceability, and audit-ready records for critical instruments.',
        ],
        [
            'group' => 'Calibration and reports',
            'question' => 'How can I verify an Envi Tech AL report?',
            'answer' => 'Use the Report Verification Portal or contact the team with report number, report date, company name, and relevant details. Verification protects customers from altered or misused report copies.',
        ],
        [
            'group' => 'Calibration and reports',
            'question' => 'What makes a laboratory report useful for management decisions?',
            'answer' => 'A useful report connects the right sample, method, parameter list, date, location, result, and review context so management can decide whether action, monitoring, treatment, or documentation is needed.',
        ],
        [
            'group' => 'Calibration and reports',
            'question' => 'Can reports be used for buyer audits and internal compliance files?',
            'answer' => 'Yes, when the scope is correctly defined before sampling or monitoring. Share the buyer requirement, audit checklist, or internal standard early so the report is prepared for the intended file.',
        ],
        [
            'group' => 'Sampling and logistics',
            'question' => 'Do you provide services in Karachi and Lahore?',
            'answer' => 'Yes. Envi Tech AL supports clients through Karachi and Lahore coordination for laboratory testing, field monitoring, consultancy, calibration support, and report-related guidance.',
        ],
        [
            'group' => 'Sampling and logistics',
            'question' => 'How quickly can testing or monitoring be arranged?',
            'answer' => 'Timing depends on city, sample type, test parameters, field requirements, lab workload, and report purpose. Share the deadline early so the team can recommend a realistic pathway.',
        ],
        [
            'group' => 'Sampling and logistics',
            'question' => 'What should I do before sending a sample?',
            'answer' => 'Confirm the sample type, required parameters, container needs, holding time, preservation requirement, and whether collection should be performed by trained field staff.',
        ],
        [
            'group' => 'Sampling and logistics',
            'question' => 'Can your team collect samples from the site?',
            'answer' => 'Field sampling can be coordinated where required. Site collection is often better for compliance-sensitive work because location, timing, container, preservation, and chain-of-custody details matter.',
        ],
        [
            'group' => 'Sampling and logistics',
            'question' => 'Why should I avoid choosing tests only by price?',
            'answer' => 'The cheapest scope can miss required parameters, wrong containers, holding-time constraints, sampling conditions, or report wording needed for the final decision. Correct scoping protects both cost and credibility.',
        ],
    ];
}

function eta_modern_ai_faq_center_groups()
{
    return [
        'Environmental testing' => [
            ['What does environmental testing include?', 'Environmental testing can include water, wastewater, air emissions, ambient air, noise, soil, hazardous waste, industrial hygiene and other checks needed for compliance, audits or operational decisions.'],
            ['Who needs environmental testing in Pakistan?', 'Industries, hospitals, hotels, housing societies, exporters, maritime operators, construction projects, consultants and EHS teams may need environmental testing for compliance, buyer audits or internal risk control.'],
            ['How should a company choose the right environmental test scope?', 'Start with the report purpose, location, sample type, required standard, deadline and whether field sampling is needed. The technical team can then match parameters to the decision the report must support.'],
            ['Can environmental testing support regulatory submissions?', 'Yes, when the scope, sampling plan, parameters and documentation are aligned with the relevant authority or approval condition before work begins.'],
        ],
        'Water testing' => [
            ['What water testing services does Envi Tech AL provide?', 'Envi Tech AL supports drinking water, bore water, RO plant water, process water, groundwater, wastewater and industrial discharge testing based on the required parameters and report purpose.'],
            ['What details are needed for a water testing quotation?', 'Share the city, water source, sample type, intended use, report purpose, deadline, required standard and whether field sampling is required.'],
            ['Can drinking water be tested for homes and offices?', 'Yes. Drinking water testing can be scoped for homes, offices, buildings, schools, hospitals, hotels, factories and other facilities.'],
            ['Can water testing include microbiological parameters?', 'Microbiological indicators can be included when required by the scope, report purpose or applicable standard.'],
        ],
        'Wastewater testing' => [
            ['What is wastewater testing used for?', 'Wastewater testing helps assess industrial discharge quality, ETP performance, regulatory compliance, buyer audit evidence and corrective action needs.'],
            ['Which wastewater parameters are commonly requested?', 'Common parameters include pH, BOD, COD, TSS, TDS, oil and grease, ammonia, sulfide, chloride, sulfate and metals, depending on the standard or report purpose.'],
            ['Can Envi Tech AL test ETP inlet and outlet samples?', 'Yes. ETP inlet and outlet sampling can be scoped to assess treatment performance and discharge compliance.'],
            ['Can wastewater reports support SEPA or Punjab EPA submissions?', 'Reports can support EPA-related pathways when sampling, parameters and documentation are scoped correctly before testing.'],
        ],
        'Stack emission testing' => [
            ['What is stack emission testing?', 'Stack emission testing measures gases, particulates or combustion-related parameters from boilers, generators, chimneys or process stacks.'],
            ['Which facilities commonly need stack emission testing?', 'Factories, power systems, boilers, generators, process plants, hotels, hospitals and industrial facilities may need stack emission testing for compliance or audits.'],
            ['What details are needed before stack emission testing?', 'Share fuel type, source type, stack location, operating condition, required parameters, site city and reporting deadline.'],
            ['Can stack testing be linked with consultancy?', 'Yes. Results can be reviewed with environmental consultancy to support compliance documentation and corrective action planning.'],
        ],
        'Ambient air monitoring' => [
            ['What is ambient air monitoring?', 'Ambient air monitoring assesses surrounding air quality near facilities, construction sites, roads, sensitive receptors or operating areas.'],
            ['Which parameters are usually monitored in ambient air?', 'Parameters may include particulate matter, sulfur dioxide, nitrogen oxides, carbon monoxide and site-specific indicators depending on scope.'],
            ['Can ambient air monitoring support baseline studies?', 'Yes. Ambient air monitoring can support baseline environmental review, compliance monitoring, project documentation and audit evidence.'],
            ['Is field condition documentation important?', 'Yes. Location, timing, weather, activity and site conditions help make monitoring results easier to interpret.'],
        ],
        'Noise monitoring' => [
            ['What is noise monitoring used for?', 'Noise monitoring is used for workplace exposure review, boundary noise, construction activity, complaint investigation, audits and regulatory documentation.'],
            ['What is noise dosimetry?', 'Noise dosimetry measures personal noise exposure over a defined period, usually for occupational exposure assessment.'],
            ['Can noise monitoring support buyer audits?', 'Yes. Noise monitoring reports can support buyer audits when the measurement plan matches audit requirements.'],
            ['What information is needed before noise monitoring?', 'Share the site city, activity, work areas, shift timing, number of locations, audit requirement and deadline.'],
        ],
        'Soil testing' => [
            ['When is soil testing required?', 'Soil testing may be required for industrial site review, due diligence, contamination checks, construction projects or compliance documentation.'],
            ['Which soil parameters may be tested?', 'Soil scope may include pH, moisture, metals, oil and grease or site-specific indicators based on the reason for testing.'],
            ['Why does soil sampling location matter?', 'Soil results depend on location, depth, sample handling and site history, so sampling design is important.'],
            ['Can soil results support environmental consultancy?', 'Yes. Soil results can support due diligence, corrective action planning and environmental documentation.'],
        ],
        'Hazardous waste testing' => [
            ['What is hazardous waste testing used for?', 'Hazardous waste testing helps evaluate waste, sludge or industrial residues before handling, disposal, compliance review or documentation.'],
            ['Can sludge samples be tested?', 'Yes. Sludge or waste samples can be reviewed against requested parameters and the intended use of the report.'],
            ['What should be shared before hazardous waste testing?', 'Share waste type, process source, location, required parameters, report purpose and deadline.'],
            ['Does testing decide disposal method by itself?', 'Testing provides technical evidence; disposal decisions should follow applicable regulatory and facility requirements.'],
        ],
        'SEPA/EPA compliance' => [
            ['Can Envi Tech AL support SEPA compliance work?', 'Yes. Envi Tech AL can support testing, monitoring, documentation and advisory pathways relevant to Sindh EPA requirements.'],
            ['Can Envi Tech AL support Punjab EPA requirements?', 'Yes. Lahore and Punjab clients can request testing, monitoring and consultancy support for Punjab EPA-related needs.'],
            ['What is the difference between testing and compliance consultancy?', 'Testing produces technical data. Consultancy helps connect requirements, documentation, monitoring plans and corrective actions around that data.'],
            ['When should a facility start compliance planning?', 'Start before submission, inspection or audit deadlines so sampling, testing and documentation can be sequenced correctly.'],
        ],
        'IEE/EIA/EMP/EMR' => [
            ['What is an IEE?', 'An Initial Environmental Examination is a project environmental review required in certain approval pathways, depending on project type and authority requirements.'],
            ['What is an EIA?', 'An Environmental Impact Assessment is a more detailed environmental study used for projects with potentially significant environmental impacts.'],
            ['What is an EMP?', 'An Environmental Management Plan defines controls, monitoring and mitigation actions for a project or operating facility.'],
            ['What is an EMR?', 'An Environmental Monitoring Report documents monitoring and testing results against required conditions over a reporting period.'],
        ],
        'Industrial hygiene' => [
            ['What is industrial hygiene monitoring?', 'Industrial hygiene monitoring assesses workplace environmental conditions such as air, dust, noise, heat stress and exposure-related indicators.'],
            ['Which workplaces request industrial hygiene monitoring?', 'Factories, warehouses, textile units, chemical facilities, hospitals, laboratories and EHS teams commonly request industrial hygiene monitoring.'],
            ['Can industrial hygiene reports support internal safety decisions?', 'Yes. Reports can help teams prioritize corrective action, exposure control, training and follow-up monitoring.'],
            ['What details are needed for industrial hygiene scope?', 'Share process type, work areas, shift timing, employee groups, audit requirement and deadline.'],
        ],
        'Calibration' => [
            ['Why is equipment calibration important?', 'Calibration supports measurement confidence, traceability, audit readiness and controlled documentation for laboratory and industrial instruments.'],
            ['Which instrument details are needed for calibration support?', 'Share instrument type, range, location, certificate requirement, deadline and whether onsite coordination is needed.'],
            ['Can calibration support audit readiness?', 'Yes. Calibration records can support quality, laboratory, buyer and compliance audits when records are current and traceable.'],
            ['Does calibration replace maintenance?', 'No. Calibration checks measurement performance; repair or maintenance may be needed if equipment is not performing correctly.'],
        ],
        'Sampling' => [
            ['Why is proper sampling important?', 'Results depend on correct sample location, container, preservation, holding time, labeling and chain-of-custody discipline.'],
            ['Can Envi Tech AL guide sample collection?', 'Yes. The team can guide sample type, containers, preservation and field coordination based on the requested scope.'],
            ['Can a customer deliver a sample directly?', 'This depends on sample type, holding time and preservation requirements. Contact the lab before dispatching critical samples.'],
            ['What should be written on a sample label?', 'Typical labels include sample ID, location, date, time, sample type and any required project or client reference.'],
        ],
        'Report verification' => [
            ['How can I verify an Envi Tech AL report?', 'Use the report verification portal or contact Envi Tech AL with report number, report date, company name and relevant details.'],
            ['Why is report verification important?', 'Verification helps customers, auditors and regulators confirm that a report copy has not been altered or misused.'],
            ['Can a procurement team request certificate or report confirmation?', 'Yes. Procurement and audit teams can contact Envi Tech AL for document clarification or verified copies where appropriate.'],
            ['What should I do if report details do not match?', 'Contact Envi Tech AL directly with the report copy and identifying details so the team can review the issue.'],
        ],
        'Quotations and coordination' => [
            ['What information helps Envi Tech AL quote accurately?', 'Share the city, site or sample type, report purpose, required parameters, deadline, buyer or regulatory requirement and whether field support is needed.'],
            ['Can urgent work be requested?', 'Urgent work may be possible depending on scope, location, sample condition, method requirements and lab workload. Share the deadline early.'],
            ['Why are prices not fixed on the website?', 'Testing and consultancy costs depend on scope, parameters, field work, location, urgency, report purpose and documentation needs, so quotations are prepared case by case.'],
            ['Who should contact Envi Tech AL before submitting samples?', 'Customers with time-sensitive, regulated, microbiological, wastewater, hazardous waste or audit-related samples should contact the team before dispatch.'],
        ],
    ];
}

function eta_modern_ai_faq_center_questions()
{
    $questions = [];
    foreach (eta_modern_ai_faq_center_groups() as $group => $items) {
        foreach ($items as $item) {
            $questions[] = [
                'group' => $group,
                'question' => $item[0],
                'answer' => $item[1],
            ];
        }
    }

    return $questions;
}

function eta_modern_render_ai_faq_center_page()
{
    $groups = eta_modern_ai_faq_center_groups();
    ?>
    <section class="eta-utility-hero" aria-labelledby="eta-ai-faq-title">
        <div class="eta-shell eta-utility-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('AI FAQ Center', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-ai-faq-title"><?php esc_html_e('Environmental Testing FAQs Pakistan', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Practical answers about environmental testing, water and wastewater testing, emissions monitoring, EPA compliance, calibration, sampling and report verification for Pakistan-based customers.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('View services', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Ask a question', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
        </div>
    </section>
    <section class="eta-band eta-utility-faq">
        <div class="eta-shell">
            <?php foreach ($groups as $group => $items) : ?>
                <div class="eta-utility-faq-group">
                    <h2><?php echo esc_html($group); ?></h2>
                    <div class="eta-utility-faq-grid">
                        <?php foreach ($items as $item) : ?>
                            <article>
                                <h3><?php echo esc_html($item[0]); ?></h3>
                                <p><?php echo esc_html($item[1]); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="eta-utility-final">
        <div class="eta-shell eta-utility-final-grid">
            <h2><?php esc_html_e('Need a testing, monitoring, calibration, or compliance scope?', 'envi-tech-al-modern'); ?></h2>
            <div class="eta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Contact Envi Tech AL', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Verify a report', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_render_faq_page()
{
    $hero_image = 'https://envitechal.com/wp-content/uploads/2026/05/environmental-testing-services-hero-v2.png';
    $questions = eta_modern_faq_page_questions();
    $groups = [];
    foreach ($questions as $faq) {
        $groups[$faq['group']][] = $faq;
    }

    $decision_cards = [
        [
            'title' => 'I need a report for compliance',
            'text' => 'Start with the authority, buyer, audit, or internal requirement. The team can then align the test scope, sampling plan, and reporting format.',
            'link' => home_url('/contact-us-envi-tech-al/'),
            'label' => 'Ask for compliance scope',
        ],
        [
            'title' => 'I am unsure which test to choose',
            'text' => 'Share the sample type, industry, concern, city, and report purpose. Envi Tech AL can help convert the issue into the right technical pathway.',
            'link' => home_url('/services/water-testing-lab-services/'),
            'label' => 'water testing laboratory',
        ],
        [
            'title' => 'I need to verify a report',
            'text' => 'Use report number, report date, company details, and QR/manual verification guidance before relying on a submitted report copy.',
            'link' => home_url('/report-verification-portal/'),
            'label' => 'Verify a report',
        ],
    ];

    $popular = [
        'Water testing lab in Karachi and Lahore',
        'EPA-related environmental monitoring',
        'Stack emission and ambient air testing',
        'IEE, EIA, EMP, EMR consultancy',
        'Calibration and audit-ready records',
        'Report verification and documentation',
    ];
    ?>
    <section class="eta-faq-hero" aria-labelledby="eta-faq-title">
        <img class="eta-faq-hero-img" src="<?php echo esc_url($hero_image); ?>" alt="<?php esc_attr_e('Environmental testing answers and laboratory guidance', 'envi-tech-al-modern'); ?>" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-faq-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Frequently Asked Questions', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-faq-title"><?php esc_html_e('Clear answers before you choose a testing, monitoring, or compliance path.', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('A practical FAQ hub for customers comparing water testing, environmental monitoring, calibration, consultancy, report verification, and EPA-related support in Karachi, Lahore, and wider Pakistan.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="#eta-faq-answers"><?php esc_html_e('Browse answers', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Ask the team', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-faq-hero-panel">
                <span><?php esc_html_e('Fastest way to get the right answer', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('Tell us the sample, city, report purpose, deadline, and required standard.', 'envi-tech-al-modern'); ?></strong>
                <a href="tel:+923102288801">0310-2288801</a>
                <a href="mailto:info@envitechal.com">info@envitechal.com</a>
            </aside>
        </div>
    </section>

    <section class="eta-faq-proof">
        <div class="eta-shell eta-faq-proof-grid">
            <span><?php esc_html_e('Water and wastewater testing', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('EPA and buyer audit support', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Karachi and Lahore coordination', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Report verification guidance', 'envi-tech-al-modern'); ?></span>
        </div>
    </section>

    <section class="eta-band eta-faq-start">
        <div class="eta-shell">
            <?php eta_modern_section_title('Start with the decision', 'The right answer depends on why you need the report', 'Most confusion disappears when the report purpose is clear: compliance, audit, buyer requirement, internal control, troubleshooting, approval, or verification.'); ?>
            <div class="eta-faq-decision-grid">
                <?php foreach ($decision_cards as $card) : ?>
                    <article class="eta-faq-decision-card">
                        <h2><?php echo esc_html($card['title']); ?></h2>
                        <p><?php echo esc_html($card['text']); ?></p>
                        <a class="eta-text-link" href="<?php echo esc_url($card['link']); ?>"><?php echo esc_html($card['label']); ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-faq-command">
        <div class="eta-shell eta-faq-command-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('High-intent search topics', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Questions organized around real customer decisions, not filler content.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('This page supports both human buyers and modern crawlers by grouping questions around service intent, regulatory context, report use, and logistics.', 'envi-tech-al-modern'); ?></p>
            </div>
            <div class="eta-faq-topic-grid">
                <?php foreach ($popular as $topic) : ?>
                    <span><?php echo esc_html($topic); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section id="eta-faq-answers" class="eta-band eta-faq-answers">
        <div class="eta-shell">
            <?php eta_modern_section_title('Expert answers', 'FAQ library for testing, compliance, sampling, calibration, and report trust', 'Open the group that matches your requirement. Each answer is written to help you take the next practical step.'); ?>
            <div class="eta-faq-group-stack">
                <?php foreach ($groups as $group => $items) : ?>
                    <section class="eta-faq-answer-group" aria-labelledby="<?php echo esc_attr(sanitize_title($group)); ?>">
                        <h2 id="<?php echo esc_attr(sanitize_title($group)); ?>"><?php echo esc_html($group); ?></h2>
                        <div class="eta-faq-answer-grid">
                            <?php foreach ($items as $faq) : ?>
                                <details class="eta-faq-item">
                                    <summary><?php echo esc_html($faq['question']); ?></summary>
                                    <p><?php echo esc_html($faq['answer']); ?></p>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-band eta-faq-prepare">
        <div class="eta-shell eta-faq-prepare-grid">
            <div>
                <?php eta_modern_section_title('Before you contact the lab', 'Four details that prevent delays and wrong assumptions', 'Good scoping protects cost, turnaround time, sample validity, and report usefulness.'); ?>
            </div>
            <ol class="eta-faq-prep-list">
                <li><span>01</span><strong><?php esc_html_e('City and site location', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Karachi, Lahore, or another city changes field coordination and turnaround planning.', 'envi-tech-al-modern'); ?></p></li>
                <li><span>02</span><strong><?php esc_html_e('Sample or monitoring type', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Water, wastewater, air, emissions, noise, calibration, consultancy, or report verification.', 'envi-tech-al-modern'); ?></p></li>
                <li><span>03</span><strong><?php esc_html_e('Report purpose', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('EPA, buyer audit, internal control, approval, tender, troubleshooting, export, or certification.', 'envi-tech-al-modern'); ?></p></li>
                <li><span>04</span><strong><?php esc_html_e('Deadline and standard', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Share required parameters, buyer standard, legal reference, or submission date if available.', 'envi-tech-al-modern'); ?></p></li>
            </ol>
        </div>
    </section>

    <section class="eta-faq-final">
        <div class="eta-shell eta-faq-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Still unsure?', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Send the requirement and let the technical team guide the right next step.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('A short enquiry with the right context is faster than guessing the test list. Envi Tech AL can help you scope water testing, environmental monitoring, calibration, consultancy, or report verification correctly.', 'envi-tech-al-modern'); ?></p>
            </div>
            <div class="eta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Send FAQ enquiry', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('View services', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_utility_page_data($slug)
{
    $data = [
        'frequently-asked-questions-water-testing-in-karachi' => [
            'eyebrow' => 'Frequently asked questions',
            'title' => 'Clear answers before you choose a testing or compliance path.',
            'lead' => 'Practical answers about environmental testing, water testing, calibration, consultancy, EPA compliance, and laboratory reporting in Karachi and Lahore.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Testing-Lab.png',
            'sections' => [
                'Environmental testing' => [
                    ['What environmental tests are commonly done?', 'Air quality monitoring, stack emissions, water and wastewater testing, noise monitoring, soil or sludge analysis, and industrial hygiene checks.'],
                    ['Do you support Karachi and Lahore?', 'Yes. Envi Tech AL supports clients through Karachi and Lahore offices with lab, field, and advisory coordination.'],
                    ['How do I choose the right test scope?', 'Start with the report purpose: EPA, buyer audit, internal safety, export, construction, or troubleshooting. The lab team can align parameters accordingly.'],
                ],
                'Water and calibration' => [
                    ['What water testing services are available?', 'Drinking water, wastewater, process water, RO performance, groundwater, and industrial discharge testing can be scoped by intended use.'],
                    ['Do you provide calibration services?', 'Yes. Calibration support helps laboratories and industrial teams maintain measurement confidence, traceability, and audit readiness.'],
                    ['Can I request a quotation first?', 'Yes. Share sample type, city, deadline, report purpose, and any required standard or buyer requirement.'],
                ],
            ],
        ],
        'ourclients' => [
            'eyebrow' => 'Client portfolio',
            'title' => 'Trusted by industrial, healthcare, hospitality, development, and export-focused teams.',
            'lead' => 'Envi Tech AL supports organizations that need dependable environmental testing, monitoring, consultancy, calibration, and compliance reporting.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Consulting-Services.png',
            'clients' => ['Agha Khan University Hospital', 'WWF', 'Soorty Enterprises', 'Greaves Pakistan', 'Pearl Continental Hotel Karachi', 'National Medical Center', 'B.H.Y Hospital', 'United Towel Exporters', 'Rainbow Hosiery Pvt Ltd', 'Artistic Milliners', 'Velosi Pakistan', 'Hamdard University Group', 'Crown Textile', 'Movenpick Hotel Karachi', 'Patrind O&M Private Limited', 'Power China Gansu Energy', 'Vee Chem Industries', 'Fabritex Enterprises'],
        ],
        'lahore-environmental-lab' => [
            'eyebrow' => 'Lahore Environmental Lab',
            'title' => 'Environmental testing and consultancy support for Lahore and Punjab clients.',
            'lead' => 'The Lahore office supports environmental consultancy, water testing, field monitoring, calibration coordination, and compliance-ready reporting for industrial and commercial teams.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/IEE-EMP-Consulting.png',
            'points' => ['Water and wastewater testing coordination', 'Environmental consultancy and approvals support', 'Monitoring for air, emissions, noise, and workplace conditions', 'Calibration and documentation support'],
        ],
        'tdap-registered-lab-in-karachi-pakistan' => [
            'eyebrow' => 'Export testing support',
            'title' => 'Testing support for exporters that need credible documentation.',
            'lead' => 'Envi Tech AL supports leather and export-focused businesses with testing awareness, documentation guidance, and laboratory services in Karachi. Current TDAP or PTA eligibility and registration must be confirmed with the relevant authority.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Industrial-compliance-Monitoring.png',
            'points' => ['TDAP and PTA scheme research', 'Leather exporter testing support', 'Documentation for subsidy and quality review', 'Lab reporting for buyer and export requirements'],
        ],
    ];

    return $data[$slug] ?? null;
}

function eta_modern_location_faqs($location)
{
    if ($location === 'karachi') {
        return [
            ['Does Envi Tech AL provide environmental testing in Karachi?', 'Yes. Envi Tech AL provides environmental testing, water and wastewater testing, stack emission testing, air monitoring, noise monitoring, soil testing, industrial hygiene monitoring, consultancy, and report support for Karachi and Sindh clients.'],
            ['Are reports suitable for Sindh EPA or SEPA compliance work?', 'Reports and monitoring scopes can be prepared for SEPA-related compliance needs when the requested parameters, sampling plan, and report purpose are defined before work starts.'],
            ['Which Karachi industrial areas do you support?', 'Envi Tech AL supports facilities in SITE, Korangi, Landhi, North Karachi, Federal B Area, Port Qasim, Bin Qasim, DHA and Clifton commercial areas, hospitals, hotels, construction sites, and industrial estates where services are applicable.'],
            ['What details are needed for a Karachi testing quotation?', 'Share the facility location, sample type, required parameters, report purpose, deadline, buyer or regulatory requirement, and whether field sampling or consultancy support is needed.'],
            ['Can customers verify Envi Tech AL reports?', 'Yes. Customers can use the report verification portal or contact the team with report details for verification support.'],
        ];
    }

    return [
        ['Do you provide environmental lab services in Lahore?', 'Yes. Envi Tech AL supports Lahore and wider Punjab clients through environmental testing, consultancy, monitoring, calibration coordination, and compliance-focused reporting.'],
        ['Can Lahore clients request water testing?', 'Yes. Drinking water, wastewater, RO plant water, bore water, process water, and industrial discharge testing can be scoped according to the intended use and reporting requirement.'],
        ['Do you support Punjab EPA related work?', 'Yes. The team can support documentation, monitoring, consultancy, and reporting pathways for projects and facilities preparing for environmental compliance review.'],
        ['Which details should I share for a quotation?', 'Share location, sample type, industry, report purpose, deadline, required standard or buyer requirement, and whether field sampling or consultancy support is needed.'],
    ];
}

function eta_modern_render_karachi_page()
{
    $hero_image = 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Testing-Lab.png';
    $services = [
        ['water testing laboratory', 'Drinking water, groundwater, RO plant water, process water, and industrial water testing for safety, buyer, facility, and compliance decisions.', home_url('/services/water-testing-lab-services/')],
        ['Wastewater and discharge testing', 'Industrial wastewater, effluent, ETP performance, and discharge monitoring aligned with the report purpose and applicable compliance pathway.', home_url('/services/water-testing-lab-services/')],
        ['Stack and gaseous emission testing', 'Boiler, generator, chimney, and process exhaust testing for facilities preparing SEPA, audit, or internal monitoring evidence.', home_url('/gaseous-air-emission-testing-lab-near-me/')],
        ['Air, noise, soil, and workplace monitoring', 'Ambient air, noise, soil, hazardous waste, and industrial hygiene support for operational control, buyer requirements, and project documentation.', home_url('/services/analytical-lab-services/')],
        ['Environmental consultancy', 'IEE, EIA, EMP, EMR, SEPA coordination, compliance documentation, corrective action planning, and audit response support.', home_url('/services/environmental-consultancy/')],
        ['Report verification and compliance records', 'Clear report identification, verification support, and document pathways for customers who need defensible evidence after testing.', home_url('/report-verification-portal/')],
    ];
    $areas = ['SITE', 'Korangi', 'Landhi', 'North Karachi', 'Federal B Area', 'Port Qasim', 'Bin Qasim', 'DHA and Clifton commercial areas', 'Hospitals and healthcare facilities', 'Hotels and hospitality sites', 'Construction sites', 'Industrial estates'];
    $use_cases = ['SEPA compliance submissions', 'Buyer audit environmental monitoring', 'Internal EHS monitoring', 'Annual and quarterly reporting', 'Due diligence and project approval', 'Hotel and hospital compliance', 'Textile and export compliance', 'Corrective action follow-up'];
    $workflow = [
        ['01', 'Clarify the Karachi requirement', 'Confirm whether the report is needed for SEPA, buyer audit, internal monitoring, project approval, due diligence, export compliance, or troubleshooting.'],
        ['02', 'Select parameters and sampling plan', 'Match the sample type, operating condition, location, parameter list, and field plan to the final report use.'],
        ['03', 'Complete field or lab execution', 'Coordinate sampling, monitoring, chain of custody, analysis, review, and report preparation through the relevant technical team.'],
        ['04', 'Use the report with confidence', 'Support customers with report verification, interpretation, corrective actions, and next-step compliance documentation when required.'],
    ];
    $faqs = eta_modern_location_faqs('karachi');
    ?>
    <section class="eta-lahore-hero eta-karachi-hero" aria-labelledby="eta-karachi-title">
        <img class="eta-lahore-hero-img" src="<?php echo esc_url($hero_image); ?>" alt="<?php esc_attr_e('Environmental testing laboratory support for Karachi and Sindh industries', 'envi-tech-al-modern'); ?>" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-lahore-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Karachi Environmental Lab', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-karachi-title"><?php esc_html_e('Environmental Testing Lab in Karachi, Sindh', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Envi Tech AL provides environmental testing, analytical laboratory services, water and wastewater testing, stack emission testing, air monitoring, noise monitoring, soil testing, industrial hygiene monitoring, and compliance support for Karachi and Sindh clients.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request Testing Quote', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Verify Report', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-lahore-hero-card">
                <span><?php esc_html_e('Karachi Head Office', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS, Karachi, Sindh 75900.', 'envi-tech-al-modern'); ?></strong>
                <a href="tel:+923102288801">+92 310 2288801</a>
                <a href="tel:+923152006074">+92 315 2006074</a>
                <a href="mailto:info@envitechal.com">info@envitechal.com</a>
            </aside>
        </div>
    </section>

    <section class="eta-lahore-proof">
        <div class="eta-shell eta-lahore-proof-grid">
            <span><?php esc_html_e('Sindh EPA / SEPA relevance', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Location- and method-specific scope checks', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Karachi field coordination', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Report verification support', 'envi-tech-al-modern'); ?></span>
        </div>
    </section>

    <section class="eta-band eta-lahore-intro">
        <div class="eta-shell eta-lahore-intro-grid">
            <div>
                <?php eta_modern_section_title('Local capability', 'Karachi testing support for regulatory, buyer, and operational decisions', 'Environmental reports are strongest when the scope, parameters, sampling plan, and documentation path match the final use of the result.'); ?>
            </div>
            <div class="eta-lahore-rich-copy">
                <p><?php esc_html_e('Envi Tech AL provides environmental laboratory coordination, monitoring, compliance documentation, and reporting support for industries, commercial facilities, institutions, hotels, hospitals, exporters, shipping and maritime operations, construction projects, and regulatory submissions in Karachi and Sindh. Current credential status and scope must be confirmed for the specific location, matrix, parameter, and method before work begins.', 'envi-tech-al-modern'); ?></p>
                <p><?php esc_html_e('Customers use Karachi testing reports for SEPA compliance, buyer audits, internal monitoring, annual or quarterly reporting, due diligence, project approval, hotel and hospital compliance, textile and export compliance, and corrective action planning.', 'envi-tech-al-modern'); ?></p>
            </div>
        </div>
    </section>

    <section class="eta-band eta-lahore-services">
        <div class="eta-shell">
            <?php eta_modern_section_title('Karachi service pathways', 'Testing, monitoring, consultancy, and verification connected in one route', 'Choose the service path by the decision the report must support: regulatory, buyer, operational, safety, project, or verification.'); ?>
            <div class="eta-lahore-service-grid">
                <?php foreach ($services as $service) : ?>
                    <article class="eta-lahore-service-card">
                        <h2><?php echo esc_html($service[0]); ?></h2>
                        <p><?php echo esc_html($service[1]); ?></p>
                        <a class="eta-text-link" href="<?php echo esc_url($service[2]); ?>"><?php echo esc_html($service[2] === home_url('/services/water-testing-lab-services/') ? 'water testing laboratory' : __('View related service', 'envi-tech-al-modern')); ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-lahore-command">
        <div class="eta-shell eta-lahore-command-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Controlled workflow', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('From Karachi enquiry to compliance-ready evidence.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('A clear workflow protects the result from scope gaps, missing parameters, sampling uncertainty, and weak documentation.', 'envi-tech-al-modern'); ?></p>
            </div>
            <ol class="eta-lahore-timeline">
                <?php foreach ($workflow as $step) : ?>
                    <li>
                        <span><?php echo esc_html($step[0]); ?></span>
                        <strong><?php echo esc_html($step[1]); ?></strong>
                        <p><?php echo esc_html($step[2]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="eta-band eta-lahore-industries">
        <div class="eta-shell eta-lahore-industries-grid">
            <div>
                <?php eta_modern_section_title('Karachi areas and industries served', 'Support for facilities where environmental evidence matters commercially', 'Coverage depends on scope, site access, sample type, operating condition, and reporting timeline.'); ?>
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Discuss Karachi sampling', 'envi-tech-al-modern'); ?></a>
            </div>
            <div class="eta-lahore-chip-grid" aria-label="<?php esc_attr_e('Karachi areas served', 'envi-tech-al-modern'); ?>">
                <?php foreach ($areas as $area) : ?>
                    <span><?php echo esc_html($area); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-band eta-lahore-office">
        <div class="eta-shell eta-lahore-office-grid">
            <div class="eta-lahore-office-card">
                <p class="eta-eyebrow"><?php esc_html_e('Report use cases', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Karachi reports are often needed before audits, submissions, approvals, or corrective actions.', 'envi-tech-al-modern'); ?></h2>
                <div class="eta-lahore-office-links">
                    <a href="<?php echo esc_url(home_url('/services/analytical-lab-services/')); ?>"><?php esc_html_e('Analytical lab services', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/services/environmental-consultancy/')); ?>"><?php esc_html_e('Environmental consultancy', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/lahore-environmental-lab/')); ?>"><?php esc_html_e('Lahore environmental lab', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <div class="eta-lahore-map-card">
                <h2><?php esc_html_e('Common Karachi report purposes', 'envi-tech-al-modern'); ?></h2>
                <ul>
                    <?php foreach ($use_cases as $case) : ?>
                        <li><?php echo esc_html($case); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <section class="eta-band eta-lahore-faq">
        <div class="eta-shell">
            <?php eta_modern_section_title('Karachi environmental lab FAQ', 'Questions before requesting a testing quotation', 'Direct answers for Karachi and Sindh teams comparing environmental testing, monitoring, consultancy, and report verification support.'); ?>
            <div class="eta-lahore-faq-grid">
                <?php foreach ($faqs as $faq) : ?>
                    <article>
                        <h2><?php echo esc_html($faq[0]); ?></h2>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-lahore-final">
        <div class="eta-shell eta-lahore-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Need environmental testing in Karachi?', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Send the requirement and get the right testing route before the deadline tightens.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Share the sample type, facility location, report purpose, deadline, and required standard or parameter list so the team can prepare a focused quotation.', 'envi-tech-al-modern'); ?></p>
            </div>
            <div class="eta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request Testing Quote', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Verify Report', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_render_certificates_page()
{
    $credentials = [
        [
            'name' => 'Sindh EPA laboratory credential — current confirmation required',
            'issuer' => 'Sindh Environmental Protection Agency',
            'scope' => 'Environmental testing, monitoring, reporting and compliance support relevant to Sindh clients and regulatory submissions.',
            'status' => 'Do not rely on this category alone; confirm the latest issuer document, validity, conditions, and applicable scope.',
            'verification' => 'A published copy is available for review, but current status must be confirmed before audit, procurement, or regulatory use.',
            'link' => 'https://envitechal.com/wp-content/uploads/2026/01/SEPA-NOC.pdf',
            'link_label' => 'Review published Sindh EPA document',
        ],
        [
            'name' => 'Punjab EPA environmental laboratory certification (2025–2028)',
            'issuer' => 'Punjab Environmental Protection Agency',
            'scope' => 'Environmental testing, monitoring and compliance support relevant to Lahore and Punjab clients.',
            'status' => 'The official Punjab EPA record publishes Envi Tech AL and its 2025–2028 certificate document.',
            'verification' => 'Review the official document and its conditions for the requested work.',
            'link' => 'https://epd.punjab.gov.pk/system/files/EnviTech_%202025-2028_merged.pdf',
            'link_label' => 'Open official Punjab EPA certificate',
        ],
        [
            'name' => 'ISO 9001:2015',
            'issuer' => 'TÜV AUSTRIA Bureau of Inspection & Certification (Pvt) Ltd',
            'scope' => 'Quality management system discipline for consistent service delivery and documented operational controls.',
            'status' => 'Registration TPAK-080177324-QMS; issued 28 August 2024; certificate states validity through 27 August 2027.',
            'verification' => 'Review the certificate and confirm current issuer status for audit or procurement use.',
            'link' => 'https://envitechal.com/wp-content/uploads/2026/01/ISO-9001-Certificate.pdf',
            'link_label' => 'Open ISO 9001 certificate',
        ],
        [
            'name' => 'ISO 14001:2015',
            'issuer' => 'TÜV AUSTRIA Bureau of Inspection & Certification (Pvt) Ltd',
            'scope' => 'Environmental management system discipline relevant to environmental responsibility and management practice.',
            'status' => 'Registration TPAK-080177424-EMS; issued 28 August 2024; certificate states validity through 27 August 2027.',
            'verification' => 'Review the certificate and confirm current issuer status for audit or procurement use.',
            'link' => 'https://envitechal.com/wp-content/uploads/2026/01/ISO-14001-Certificate.pdf',
            'link_label' => 'Open ISO 14001 certificate',
        ],
        [
            'name' => 'ISO/IEC 17025:2017 accreditation — Lahore laboratory (LAB-347)',
            'issuer' => 'Pakistan National Accreditation Council (PNAC)',
            'scope' => 'Lahore only, and only the water/wastewater parameters and methods listed in the published LAB-347 scope.',
            'status' => 'First granted 22 September 2025; PNAC document states validity through 21 September 2028, subject to surveillance and current status.',
            'verification' => 'Do not infer accreditation for Karachi or for any method not listed in LAB-347. Confirm location, matrix, parameter, and method before booking.',
            'link' => 'https://www.pnac.gov.pk/index.php/pdfFiles/LAB-347',
            'link_label' => 'Open official PNAC LAB-347 scope',
        ],
    ];
    ?>
    <section class="eta-lahore-hero eta-credentials-hero" aria-labelledby="eta-credentials-title">
        <img class="eta-lahore-hero-img" src="https://envitechal.com/wp-content/uploads/2026/06/Regulatory-compliance-Advisory.png" alt="<?php esc_attr_e('Laboratory credentials and compliance document review', 'envi-tech-al-modern'); ?>" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-lahore-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Credentials and verification', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-credentials-title"><?php esc_html_e('Certifications, Approvals & Laboratory Quality Credentials', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Visible proof points for clients, auditors, regulators, and procurement teams.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Verify Report', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request certificate copy', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-lahore-hero-card">
                <span><?php esc_html_e('Customer note', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('Certificate copies or verification support can be requested through the Envi Tech AL team when required for audit, procurement, or regulatory review.', 'envi-tech-al-modern'); ?></strong>
                <a href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request certificate support', 'envi-tech-al-modern'); ?></a>
            </aside>
        </div>
    </section>

    <section class="eta-band eta-lahore-services">
        <div class="eta-shell">
            <?php
            eta_modern_render_ai_summary_block(
                'Certification and approval summary',
                'Envi Tech AL publishes evidence relating to laboratory, quality-management, environmental-management, and EPA-related credentials. The records below separate verified issuer evidence from documents whose current status still requires confirmation; scope, validity, conditions, location, matrix, parameter, and method must be checked before reliance.',
                ['Sindh EPA document with current confirmation required', 'Punjab EPA 2025–2028 official record', 'PNAC LAB-347 for the Lahore location and listed methods', 'ISO 9001:2015 and ISO 14001:2015 certificates']
            );
            eta_modern_section_title('Credential cards', 'Laboratory and compliance trust signals customers commonly verify', 'The information below is intentionally careful: it explains credential categories without inventing unsupported certificate numbers, ratings, or claims.');
            ?>
            <div class="eta-lahore-service-grid">
                <?php foreach ($credentials as $credential) : ?>
                    <article class="eta-lahore-service-card">
                        <h2><?php echo esc_html($credential['name']); ?></h2>
                        <p><strong><?php esc_html_e('Issuing body:', 'envi-tech-al-modern'); ?></strong> <?php echo esc_html($credential['issuer']); ?></p>
                        <p><strong><?php esc_html_e('Scope summary:', 'envi-tech-al-modern'); ?></strong> <?php echo esc_html($credential['scope']); ?></p>
                        <p><strong><?php esc_html_e('Validity/status:', 'envi-tech-al-modern'); ?></strong> <?php echo esc_html($credential['status']); ?></p>
                        <p><strong><?php esc_html_e('Verification note:', 'envi-tech-al-modern'); ?></strong> <?php echo esc_html($credential['verification']); ?></p>
                        <a class="eta-text-link" href="<?php echo esc_url($credential['link']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($credential['link_label']); ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-band eta-lahore-office">
        <div class="eta-shell eta-lahore-office-grid">
            <div class="eta-lahore-office-card">
                <p class="eta-eyebrow"><?php esc_html_e('Verification pathways', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Need a verified certificate copy for audit or procurement? Contact Envi Tech AL.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Use the report verification portal for report checks, or contact Envi Tech AL for certificate copy requests and credential clarification where public display is not appropriate.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-lahore-office-links">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Homepage', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/aboutus/')); ?>"><?php esc_html_e('About Envi Tech AL', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/services/analytical-lab-services/')); ?>"><?php esc_html_e('Analytical Lab Services', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/services/water-testing-lab-services/')); ?>"><?php esc_html_e('Water Testing', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/services/environmental-consultancy/')); ?>"><?php esc_html_e('Environmental Consultancy', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Report Verification Portal', 'envi-tech-al-modern'); ?></a>
                    <a href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Contact credential support', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <div class="eta-lahore-map-card">
                <h2><?php esc_html_e('How customers use this page', 'envi-tech-al-modern'); ?></h2>
                <ul>
                    <li><?php esc_html_e('Shortlist Envi Tech AL for environmental laboratory or consultancy work.', 'envi-tech-al-modern'); ?></li>
                    <li><?php esc_html_e('Confirm which credential categories may be relevant before requesting documents.', 'envi-tech-al-modern'); ?></li>
                    <li><?php esc_html_e('Connect certificates and approvals with report verification, contact, and downloads resources.', 'envi-tech-al-modern'); ?></li>
                    <li><?php esc_html_e('Avoid relying on outdated third-party descriptions or unsupported claims.', 'envi-tech-al-modern'); ?></li>
                </ul>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_cluster_page_data($slug)
{
    $data = [
        'wastewater-testing-services' => [
            'eyebrow' => 'Wastewater testing',
            'title' => 'Wastewater Testing Services in Karachi & Lahore',
            'seo_title' => 'Wastewater Testing Services in Karachi & Lahore | Envi Tech AL',
            'meta' => 'Wastewater testing for industrial discharge, ETP performance, compliance reporting, buyer audits and internal monitoring in Karachi and Lahore.',
            'summary' => 'Envi Tech AL provides wastewater testing for industrial discharge, ETP performance checks, compliance reporting, buyer audits, and internal monitoring. Scope can include BOD, COD, TSS, TDS, pH, oil and grease, heavy metals, and other parameters based on the report purpose.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/05/water-testing-services-karachi-lahore.png',
            'who' => ['Factories and industrial estates', 'Textile and dyeing units', 'Food and beverage plants', 'Hospitals and hotels', 'ETP operators', 'Compliance and EHS teams'],
            'covered' => ['Industrial wastewater', 'ETP inlet and outlet samples', 'Process discharge', 'Composite or grab sampling guidance', 'Discharge parameter selection', 'Compliance-ready reporting'],
            'parameters' => ['Temperature', 'pH', 'BOD', 'COD', 'TSS', 'TDS', 'Oil and grease', 'Phenolic compounds', 'Sulfide', 'Ammonia', 'Chloride', 'Sulfate', 'Chromium', 'Copper', 'Zinc', 'Nickel', 'Lead', 'Cadmium', 'Mercury'],
            'uses' => ['SEPA or Punjab EPA compliance support', 'Quarterly or annual monitoring', 'Buyer audit evidence', 'ETP troubleshooting', 'Internal EHS reporting', 'Corrective action follow-up'],
            'related' => ['/sindh-environmental-quality-standards-seqs/' => 'SEQS effluent standards', '/services/water-testing-lab-services/' => 'water testing laboratory services', '/services/analytical-lab-services/' => 'Analytical lab services', '/services/environmental-consultancy/' => 'Environmental consultancy'],
            'faqs' => [
                ['Can Envi Tech AL test industrial wastewater?', 'Yes. Industrial wastewater can be tested for discharge, ETP performance, buyer audit, and internal monitoring needs in Karachi and Lahore.'],
                ['Which parameters are usually required?', 'Common parameters include pH, BOD, COD, TSS, TDS, oil and grease, sulfide, ammonia, chloride, sulfate, and metals. The final list depends on the standard or report purpose.'],
                ['Can reports support EPA submissions?', 'Reports can support EPA-related requirements when sampling, parameters, and documentation are scoped correctly before testing.'],
            ],
        ],
        'drinking-water-testing-lab' => [
            'eyebrow' => 'Drinking water testing',
            'title' => 'Drinking Water Testing Lab in Karachi & Lahore',
            'seo_title' => 'Drinking Water Testing Lab in Karachi & Lahore | Envi Tech AL',
            'meta' => 'Drinking water testing for homes, buildings, hospitals, hotels, schools, RO plants and industrial facilities in Karachi and Lahore.',
            'summary' => 'Envi Tech AL provides drinking water testing for homes, buildings, hospitals, hotels, schools, RO plants, groundwater, and facilities that need clear safety and quality evidence. Parameters can be selected for health, operations, audit, or compliance use.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/05/water-testing-services-karachi-lahore.png',
            'who' => ['Residential buildings', 'Hospitals and clinics', 'Hotels and restaurants', 'Schools and institutions', 'Factories and warehouses', 'RO plant operators'],
            'covered' => ['Drinking water safety checks', 'Bore water and groundwater', 'RO plant output', 'Storage tank checks', 'Microbiological testing', 'Metal and chemistry screening'],
            'parameters' => ['pH', 'Color', 'Turbidity', 'TDS', 'Hardness', 'Chloride', 'Sulfate', 'Nitrate', 'Fluoride', 'Iron', 'Manganese', 'Copper', 'Lead', 'Arsenic', 'Residual chlorine', 'Total coliform', 'E. coli'],
            'uses' => ['Health and safety review', 'Hotel and hospital compliance', 'Buyer or tenant requirement', 'RO performance verification', 'Internal facility monitoring', 'Report verification support'],
            'related' => ['/services/water-testing-lab-services/' => 'Water testing services', '/wastewater-testing-services/' => 'Wastewater testing', '/report-verification-portal/' => 'Report verification'],
            'faqs' => [
                ['Do you provide drinking water testing in Karachi?', 'Yes. Envi Tech AL provides drinking water testing in Karachi for homes, buildings, hospitals, hotels, schools, and industrial facilities.'],
                ['Do you provide drinking water testing in Lahore?', 'Yes. Lahore clients can request drinking water testing with parameter selection based on the intended use of the report.'],
                ['Can you test for bacteria?', 'Microbiological indicators such as total coliform and E. coli can be included when required by the scope.'],
            ],
        ],
        'ambient-air-monitoring-services' => [
            'eyebrow' => 'Ambient air monitoring',
            'title' => 'Ambient Air Monitoring Services in Karachi & Lahore',
            'seo_title' => 'Ambient Air Monitoring Services in Karachi & Lahore | Envi Tech AL',
            'meta' => 'Ambient air monitoring for industrial, construction, hospital, hotel and compliance reporting needs in Karachi and Lahore.',
            'summary' => 'Ambient air monitoring helps facilities understand air quality around operations, construction activity, industrial processes, traffic influence, or compliance-sensitive sites. Envi Tech AL supports monitoring plans, field coordination, reporting, and consultancy follow-up.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Industrial-compliance-Monitoring.png',
            'who' => ['Industrial plants', 'Construction projects', 'Hospitals and hotels', 'Commercial facilities', 'Project consultants', 'EHS teams'],
            'covered' => ['Ambient air monitoring plan', 'Field measurement coordination', 'Particulate matter indicators', 'Gaseous indicators', 'Site observations', 'Compliance reporting support'],
            'parameters' => ['PM10', 'PM2.5', 'SO2', 'NOx', 'CO', 'CO2', 'Ozone where required', 'Meteorological notes', 'Site condition notes'],
            'uses' => ['Baseline environmental monitoring', 'SEPA or Punjab EPA documentation', 'Construction project monitoring', 'Buyer audit evidence', 'Internal EHS review', 'Corrective action tracking'],
            'related' => ['/sindh-environmental-quality-standards-seqs/' => 'SEQS ambient air standards', '/gaseous-air-emission-testing-lab-near-me/' => 'Stack emission testing', '/noise-monitoring-dosimetry/' => 'Noise monitoring', '/services/environmental-consultancy/' => 'Environmental consultancy'],
            'faqs' => [
                ['What is ambient air monitoring used for?', 'It is used to understand surrounding air quality conditions for compliance, construction, industrial operation, buyer audit, and internal environmental review.'],
                ['Is ambient air monitoring available in Karachi and Lahore?', 'Yes. Envi Tech AL supports Karachi/Sindh and Lahore/Punjab monitoring needs depending on scope and site conditions.'],
                ['Can monitoring be linked with consultancy?', 'Yes. Results can be connected with environmental consultancy, corrective action planning, and compliance documentation.'],
            ],
        ],
        'noise-monitoring-dosimetry' => [
            'eyebrow' => 'Noise monitoring',
            'title' => 'Noise Monitoring and Dosimetry Services',
            'seo_title' => 'Noise Monitoring and Dosimetry Services | Envi Tech AL',
            'meta' => 'Noise monitoring and dosimetry support for workplaces, factories, construction sites, hotels, hospitals and compliance reporting.',
            'summary' => 'Noise monitoring and dosimetry help facilities understand workplace exposure, boundary noise, construction noise, and environmental impact. Envi Tech AL supports measurement planning, field monitoring, reporting, and follow-up guidance.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Industrial-compliance-Monitoring.png',
            'who' => ['Factories and workshops', 'Construction projects', 'Hospitals and hotels', 'Power and utility sites', 'EHS teams', 'Buyer audit teams'],
            'covered' => ['Boundary noise', 'Workplace noise', 'Personal exposure dosimetry', 'Area monitoring', 'Shift or activity-based monitoring', 'Report interpretation'],
            'parameters' => ['Leq', 'Lmax', 'Lmin', 'Dose where applicable', 'Time-weighted exposure', 'Monitoring location notes', 'Activity condition notes'],
            'uses' => ['Workplace safety review', 'EPA or project monitoring', 'Buyer audit evidence', 'Construction site control', 'Complaint investigation', 'Corrective action planning'],
            'related' => ['/ambient-air-monitoring-services/' => 'Ambient air monitoring', '/industrial-hygiene-monitoring/' => 'Industrial hygiene monitoring', '/services/environmental-consultancy/' => 'Environmental consultancy'],
            'faqs' => [
                ['Do you provide noise monitoring for factories?', 'Yes. Factory and workplace noise monitoring can be planned around activity, shift, area, or compliance reporting needs.'],
                ['What is dosimetry?', 'Dosimetry measures personal noise exposure over a defined period, usually for occupational exposure assessment.'],
                ['Can noise results support buyer audits?', 'Yes. Noise monitoring reports can support buyer audits when scope and monitoring conditions match audit expectations.'],
            ],
        ],
        'industrial-hygiene-monitoring' => [
            'eyebrow' => 'Industrial hygiene',
            'title' => 'Industrial Hygiene Monitoring Services',
            'seo_title' => 'Industrial Hygiene Monitoring Services | Envi Tech AL',
            'meta' => 'Industrial hygiene monitoring for workplace exposure, noise, air, dust, heat stress and occupational environment review.',
            'summary' => 'Industrial hygiene monitoring helps organizations assess workplace environmental conditions that may affect worker health, audit readiness, or operational risk. Envi Tech AL supports practical monitoring scopes for factories, warehouses, hospitals, and industrial teams.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Industrial-compliance-Monitoring.png',
            'who' => ['Factories and warehouses', 'Textile and garment units', 'Chemical and process teams', 'Hospitals and labs', 'EHS departments', 'Buyer audit teams'],
            'covered' => ['Workplace air indicators', 'Noise exposure', 'Dust or particulate monitoring', 'Heat stress screening', 'Ventilation-related observations', 'Corrective action support'],
            'parameters' => ['Respirable or inhalable dust where scoped', 'Noise exposure', 'Temperature and humidity', 'Illumination where applicable', 'Work area observations', 'Exposure group notes'],
            'uses' => ['Occupational health review', 'Buyer audit evidence', 'Internal EHS monitoring', 'Corrective action planning', 'Training and awareness', 'Compliance documentation support'],
            'related' => ['/noise-monitoring-dosimetry/' => 'Noise monitoring', '/ambient-air-monitoring-services/' => 'Ambient air monitoring', '/services/equipment-calibration-services/' => 'Calibration support'],
            'faqs' => [
                ['What is industrial hygiene monitoring?', 'It is workplace environmental monitoring focused on exposure conditions such as noise, air, dust, heat, and other occupational factors.'],
                ['Can monitoring support buyer audits?', 'Yes. Industrial hygiene reports can support audit evidence when the monitoring plan matches audit criteria.'],
                ['What should be shared before quotation?', 'Share process type, work areas, number of workers or locations, shift timing, audit requirement, and deadline.'],
            ],
        ],
        'soil-hazardous-waste-testing' => [
            'eyebrow' => 'Soil and hazardous waste',
            'title' => 'Soil and Hazardous Waste Testing Services',
            'seo_title' => 'Soil and Hazardous Waste Testing Services | Envi Tech AL',
            'meta' => 'Soil and hazardous waste testing support for industrial sites, due diligence, contamination checks and compliance documentation.',
            'summary' => 'Soil and hazardous waste testing helps project owners, industries, consultants, and compliance teams understand contamination indicators, waste characteristics, and documentation needs before decisions or submissions.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Testing-Lab.png',
            'who' => ['Industrial sites', 'Construction projects', 'Consultants and developers', 'Waste handlers', 'EHS teams', 'Due diligence teams'],
            'covered' => ['Soil screening', 'Sludge and waste samples', 'Metals and chemistry indicators', 'Site-specific parameters', 'Sampling guidance', 'Report support'],
            'parameters' => ['pH', 'Moisture', 'Heavy metals', 'Oil and grease where scoped', 'Organic indicators where required', 'Waste characterization indicators', 'Site-specific analytes'],
            'uses' => ['Due diligence', 'Industrial site review', 'Waste handling decisions', 'EPA documentation support', 'Project approval support', 'Corrective action planning'],
            'related' => ['/services/analytical-lab-services/' => 'Analytical lab services', '/services/environmental-consultancy/' => 'Environmental consultancy', '/wastewater-testing-services/' => 'Wastewater testing'],
            'faqs' => [
                ['Can Envi Tech AL test soil samples?', 'Yes. Soil testing can be scoped for industrial, project, due diligence, or contamination-related review.'],
                ['Can hazardous waste samples be tested?', 'Waste or sludge samples can be reviewed against the requested parameters and intended use of the report.'],
                ['Is sampling guidance important?', 'Yes. Soil and waste results depend heavily on location, depth, container, preservation, and the reason for testing.'],
            ],
        ],
        'emp-emr-iee-eia-compliance' => [
            'eyebrow' => 'Compliance documentation',
            'title' => 'EMP, EMR, IEE, EIA, SEPA & Punjab EPA Compliance Support',
            'seo_title' => 'EMP, EMR, IEE, EIA & EPA Compliance Support | Envi Tech AL',
            'meta' => 'Environmental consultancy support for EMP, EMR, IEE, EIA, SEPA and Punjab EPA compliance documentation in Karachi and Lahore.',
            'summary' => 'Envi Tech AL supports environmental documentation and consultancy pathways for EMP, EMR, IEE, EIA, SEPA, Punjab EPA, project approvals, audits, monitoring plans, and corrective action follow-up.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Environmental-Consulting-Services.png',
            'who' => ['New projects', 'Operating facilities', 'Construction and real estate teams', 'Factories and exporters', 'Hospitals and hotels', 'Consultants and contractors'],
            'covered' => ['IEE and EIA support', 'EMP and EMR planning', 'Monitoring plan alignment', 'SEPA coordination support', 'Punjab EPA support', 'Corrective action documentation'],
            'parameters' => ['Project description inputs', 'Site and process details', 'Monitoring evidence', 'Mitigation measures', 'Legal or authority requirement', 'Submission timeline'],
            'uses' => ['Project approvals', 'Regulatory submissions', 'Operational compliance', 'Buyer audit response', 'Annual or quarterly reporting', 'Corrective action closure'],
            'related' => ['/services/environmental-consultancy/' => 'Environmental consultancy', '/services/analytical-lab-services/' => 'Analytical lab services', '/karachi-environmental-lab/' => 'Karachi environmental lab'],
            'faqs' => [
                ['Can Envi Tech AL support IEE or EIA work?', 'Yes. The consultancy team can support documentation, monitoring evidence, and advisory pathways for IEE and EIA related needs.'],
                ['Do you support SEPA and Punjab EPA requirements?', 'Yes. Envi Tech AL supports Karachi/Sindh and Lahore/Punjab clients with environmental compliance documentation and reporting support.'],
                ['When should we start?', 'Start before the submission or audit deadline so monitoring evidence, scope, and documents can be prepared in the right order.'],
            ],
        ],
        'maritime-environmental-testing' => [
            'eyebrow' => 'Maritime environmental testing',
            'title' => 'Ballast Water and Maritime Environmental Testing',
            'seo_title' => 'Ballast Water & Maritime Environmental Testing | Envi Tech AL',
            'meta' => 'Ballast water and maritime environmental testing support for vessels, shipping agents and port-related compliance needs in Karachi.',
            'summary' => 'Envi Tech AL supports ballast water and maritime environmental testing needs for vessels, shipping agents, operators, and port-related teams that require coordinated sampling, reporting, and compliance-aware documentation.',
            'image' => 'https://envitechal.com/wp-content/uploads/2026/06/Ballast-Water-Testing-Services.png',
            'who' => ['Vessel operators', 'Shipping agents', 'Port stakeholders', 'Marine consultants', 'Compliance teams', 'Inspection coordinators'],
            'covered' => ['Ballast water testing', 'Deballast water support', 'Sampling coordination', 'Port-call planning', 'Report preparation', 'Compliance documentation support'],
            'parameters' => ['Sampling location', 'Vessel schedule', 'Reporting deadline', 'Inspection context', 'Pathogen or water quality scope where required'],
            'uses' => ['Port-call compliance support', 'Inspection preparation', 'Vessel documentation', 'Operator records', 'Corrective action follow-up', 'Report verification support'],
            'related' => ['/services/ballast-water-testing-services/' => 'Ballast water testing services', '/services/analytical-lab-services/' => 'Analytical lab services', '/contact-us-envi-tech-al/' => 'Contact Envi Tech AL'],
            'faqs' => [
                ['Does Envi Tech AL support maritime testing in Karachi?', 'Yes. Envi Tech AL supports ballast water and maritime environmental testing needs around Karachi and port-related operations.'],
                ['What should vessel agents share?', 'Share vessel schedule, port timing, sample requirement, report deadline, and inspection or compliance context.'],
                ['Can reports be verified later?', 'Yes. Customers can use the report verification portal or contact Envi Tech AL for verification support.'],
            ],
        ],
    ];

    return $data[$slug] ?? null;
}

function eta_modern_render_cluster_service_page($slug)
{
    $data = eta_modern_cluster_page_data($slug);
    if (!$data) {
        return;
    }
    ?>
    <section class="eta-lahore-hero eta-cluster-hero" aria-labelledby="eta-cluster-title">
        <img class="eta-lahore-hero-img" src="<?php echo esc_url($data['image']); ?>" alt="" aria-hidden="true" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-lahore-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php echo esc_html($data['eyebrow']); ?></p>
                <h1 id="eta-cluster-title"><?php echo esc_html($data['title']); ?></h1>
                <p><?php echo esc_html($data['summary']); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request Testing Quote', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Verify Report', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-lahore-hero-card">
                <span><?php esc_html_e('Available support', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('Karachi/Sindh and Lahore/Punjab service coordination depending on scope, location, timeline, and reporting requirement.', 'envi-tech-al-modern'); ?></strong>
                <a href="<?php echo esc_url(home_url('/karachi-environmental-lab/')); ?>"><?php esc_html_e('Karachi environmental lab', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/lahore-environmental-lab/')); ?>"><?php esc_html_e('Lahore environmental lab', 'envi-tech-al-modern'); ?></a>
            </aside>
        </div>
    </section>

    <section class="eta-band eta-lahore-services">
        <div class="eta-shell">
            <?php eta_modern_render_ai_summary_block($data['title'], $data['summary'], array_merge($data['covered'], $data['uses'])); ?>
            <?php eta_modern_section_title('Service architecture', 'Scope the report around the decision it must support', 'The right page should tell customers who needs the service, what is covered, why it matters, and how to start.'); ?>
            <div class="eta-lahore-service-grid">
                <article class="eta-lahore-service-card">
                    <h2><?php esc_html_e('Who needs this', 'envi-tech-al-modern'); ?></h2>
                    <ul><?php foreach ($data['who'] as $item) : ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul>
                </article>
                <article class="eta-lahore-service-card">
                    <h2><?php esc_html_e('What is covered', 'envi-tech-al-modern'); ?></h2>
                    <ul><?php foreach ($data['covered'] as $item) : ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul>
                </article>
                <article class="eta-lahore-service-card">
                    <h2><?php esc_html_e('Report use cases', 'envi-tech-al-modern'); ?></h2>
                    <ul><?php foreach ($data['uses'] as $item) : ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul>
                </article>
                <article class="eta-lahore-service-card">
                    <h2><?php esc_html_e('Related pages', 'envi-tech-al-modern'); ?></h2>
                    <ul><?php foreach ($data['related'] as $url => $label) : ?><li><a class="eta-text-link" href="<?php echo esc_url(home_url($url)); ?>"><?php echo esc_html($label); ?></a></li><?php endforeach; ?></ul>
                </article>
            </div>
        </div>
    </section>

    <section class="eta-band eta-lahore-faq">
        <div class="eta-shell">
            <?php eta_modern_section_title('Parameters and scope areas', 'Clean review format for quotation and mobile users', 'Final parameter selection depends on the standard, buyer requirement, regulatory use, sample type, and site condition.'); ?>
            <div class="eta-parameter-grid">
                <article class="eta-parameter-card">
                    <h3><?php esc_html_e('Typical scope items', 'envi-tech-al-modern'); ?></h3>
                    <ul><?php foreach ($data['parameters'] as $item) : ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul>
                </article>
                <article class="eta-parameter-card">
                    <h3><?php esc_html_e('Process flow', 'envi-tech-al-modern'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Inquiry and report purpose', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Scope and parameter selection', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Sampling or field coordination', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Analysis, review, and report', 'envi-tech-al-modern'); ?></li>
                        <li><?php esc_html_e('Compliance or corrective action support', 'envi-tech-al-modern'); ?></li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <section class="eta-band eta-lahore-faq">
        <div class="eta-shell">
            <?php eta_modern_section_title('FAQ', 'Questions customers ask before requesting this service'); ?>
            <div class="eta-lahore-faq-grid">
                <?php foreach ($data['faqs'] as $faq) : ?>
                    <article>
                        <h2><?php echo esc_html($faq[0]); ?></h2>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-lahore-final">
        <div class="eta-shell eta-lahore-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Ready to scope this service?', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Send the requirement, city, sample or site details, and deadline.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('The team can then guide the right testing, monitoring, consultancy, or documentation route.', 'envi-tech-al-modern'); ?></p>
            </div>
            <div class="eta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request Testing Quote', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('View Services Hub', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_render_lahore_page()
{
    $hero_image = 'https://envitechal.com/wp-content/uploads/2026/05/iee-emp-emr-consulting-clean-1536x961.png';
    $services = [
        [
            'title' => 'Water and wastewater testing',
            'text' => 'Drinking water, bore water, RO plant water, process water, wastewater, and industrial discharge testing for facilities that need practical reporting and compliance-ready interpretation.',
            'link' => home_url('/services/water-testing-lab-services/'),
        ],
        [
            'title' => 'Environmental consultancy',
            'text' => 'IEE, EIA, EMP, EMR, environmental audits, Punjab EPA coordination, and documentation support for projects, factories, institutions, and commercial facilities.',
            'link' => home_url('/services/environmental-consultancy/'),
        ],
        [
            'title' => 'Monitoring and emissions',
            'text' => 'Ambient air, stack emissions, noise, workplace conditions, and field monitoring programs planned around the reporting need, operating process, and inspection deadline.',
            'link' => home_url('/services/analytical-lab-services/'),
        ],
        [
            'title' => 'Calibration and technical support',
            'text' => 'Calibration coordination and documentation support for teams that need measurement confidence, audit readiness, and traceable records for critical instruments.',
            'link' => home_url('/services/equipment-calibration-services/'),
        ],
    ];
    $industries = ['Textile and dyeing units', 'Leather and footwear', 'Food and beverage', 'Pharmaceutical and healthcare', 'Hotels and commercial buildings', 'Construction and real estate', 'Packaging and plastics', 'Educational institutions', 'Industrial estates', 'Export-oriented factories'];
    $workflow = [
        ['01', 'Clarify the Lahore requirement', 'Confirm whether the work is for Punjab EPA, buyer audit, internal control, tender documentation, factory approval, or troubleshooting.'],
        ['02', 'Match the test and advisory scope', 'Select the right parameters, sample plan, field monitoring approach, reporting format, and any supporting consultancy documents.'],
        ['03', 'Coordinate sampling and reporting', 'Align field visits, sample handling, lab analysis, technical review, and final report delivery with the customer timeline.'],
        ['04', 'Support the next compliance step', 'Help teams understand results, corrective actions, submission requirements, and follow-up monitoring where needed.'],
    ];
    $faqs = [
        ['Do you provide environmental lab services in Lahore?', 'Yes. Envi Tech AL supports Lahore and wider Punjab clients through environmental testing, consultancy, monitoring, calibration coordination, and compliance-focused reporting.'],
        ['Can Lahore clients request water testing?', 'Yes. Drinking water, wastewater, RO plant water, bore water, process water, and industrial discharge testing can be scoped according to the intended use and reporting requirement.'],
        ['Do you support Punjab EPA related work?', 'Yes. The team can support documentation, monitoring, consultancy, and reporting pathways for projects and facilities preparing for environmental compliance review.'],
        ['Which details should I share for a quotation?', 'Share location, sample type, industry, report purpose, deadline, required standard or buyer requirement, and whether field sampling or consultancy support is needed.'],
    ];
    ?>
    <section class="eta-lahore-hero" aria-labelledby="eta-lahore-title">
        <img class="eta-lahore-hero-img" src="<?php echo esc_url($hero_image); ?>" alt="<?php esc_attr_e('Environmental consultancy and testing support for Lahore industrial clients', 'envi-tech-al-modern'); ?>" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-lahore-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Lahore Environmental Lab', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-lahore-title"><?php esc_html_e('Environmental testing, consultancy, and compliance support for Lahore and Punjab.', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Envi Tech AL supports Lahore industries, commercial facilities, healthcare institutions, hotels, exporters, and project teams with water testing, environmental monitoring, calibration coordination, and consultancy guidance for Punjab EPA-related requirements.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request Lahore support', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('Explore services', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-lahore-hero-card">
                <span><?php esc_html_e('Lahore Office', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('Johar Town coordination for environmental testing, consultancy, monitoring, and report guidance.', 'envi-tech-al-modern'); ?></strong>
                <a href="tel:+924232296099">+92 42 32296099</a>
                <a href="mailto:info@envitechal.com">info@envitechal.com</a>
            </aside>
        </div>
    </section>

    <section class="eta-lahore-proof">
        <div class="eta-shell eta-lahore-proof-grid">
            <span><?php esc_html_e('Punjab EPA-related support', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Water and wastewater testing', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Field monitoring coordination', 'envi-tech-al-modern'); ?></span>
            <span><?php esc_html_e('Consultancy and documentation', 'envi-tech-al-modern'); ?></span>
        </div>
    </section>

    <section class="eta-band eta-lahore-intro">
        <div class="eta-shell eta-lahore-intro-grid">
            <div>
                <?php eta_modern_section_title('Local capability', 'A Lahore environmental partner for decisions that need credible evidence', 'The right lab and consultancy partner should help you define the requirement, collect the right data, and prepare documentation that can support inspections, approvals, audits, and internal decisions.'); ?>
            </div>
            <div class="eta-lahore-rich-copy">
                <p><?php esc_html_e('Lahore businesses often need environmental support for water quality, wastewater discharge, air and stack emissions, noise, workplace conditions, project approvals, certification audits, buyer requirements, and operational risk control. Envi Tech AL connects laboratory discipline with field coordination and advisory thinking so clients do not receive isolated test numbers without context.', 'envi-tech-al-modern'); ?></p>
                <p><?php esc_html_e('The Lahore page is designed for customers who are searching for an environmental lab in Lahore, water testing lab in Lahore, environmental consultancy in Lahore, or Punjab EPA compliance support and need a clear route to the right scope.', 'envi-tech-al-modern'); ?></p>
            </div>
        </div>
    </section>

    <section class="eta-band eta-lahore-services">
        <div class="eta-shell">
            <?php eta_modern_section_title('Lahore service pathways', 'Testing, monitoring, calibration, and consultancy built around the final use of the report', 'Start with the decision you need to support. The team can then match the service path, sample plan, parameters, and reporting format.'); ?>
            <div class="eta-lahore-service-grid">
                <?php foreach ($services as $service) : ?>
                    <article class="eta-lahore-service-card">
                        <h2><?php echo esc_html($service['title']); ?></h2>
                        <p><?php echo esc_html($service['text']); ?></p>
                        <a class="eta-text-link" href="<?php echo esc_url($service['link']); ?>"><?php echo esc_html($service['link'] === home_url('/services/water-testing-lab-services/') ? 'water testing laboratory' : __('View related service', 'envi-tech-al-modern')); ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-lahore-command">
        <div class="eta-shell eta-lahore-command-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Operational workflow', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('From Lahore enquiry to usable environmental evidence.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('A strong environmental report starts before sampling. Scope, purpose, timing, and standards matter. This workflow helps Lahore clients move from uncertainty to a controlled technical plan.', 'envi-tech-al-modern'); ?></p>
            </div>
            <ol class="eta-lahore-timeline">
                <?php foreach ($workflow as $step) : ?>
                    <li>
                        <span><?php echo esc_html($step[0]); ?></span>
                        <strong><?php echo esc_html($step[1]); ?></strong>
                        <p><?php echo esc_html($step[2]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="eta-band eta-lahore-industries">
        <div class="eta-shell eta-lahore-industries-grid">
            <div>
                <?php eta_modern_section_title('Industries served in Lahore and Punjab', 'Support for compliance-critical operations, not generic laboratory traffic', 'Different industries need different parameters, reporting formats, timelines, and supporting evidence.'); ?>
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Discuss your facility', 'envi-tech-al-modern'); ?></a>
            </div>
            <div class="eta-lahore-chip-grid" aria-label="<?php esc_attr_e('Lahore industries served', 'envi-tech-al-modern'); ?>">
                <?php foreach ($industries as $industry) : ?>
                    <span><?php echo esc_html($industry); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-band eta-lahore-office">
        <div class="eta-shell eta-lahore-office-grid">
            <div class="eta-lahore-office-card">
                <p class="eta-eyebrow"><?php esc_html_e('Lahore coordination office', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Johar Town access for sample discussion, consultancy requests, and follow-up.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('87-E Madina Heights, Office A/30-31, 8th Floor, Maulana Shaukat Ali Road, Johar Town, Lahore.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-lahore-office-links">
                    <a href="tel:+924232296099">+92 42 32296099</a>
                    <a href="mailto:info@envitechal.com">info@envitechal.com</a>
                    <a href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Send enquiry', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <div class="eta-lahore-map-card">
                <h2><?php esc_html_e('What to prepare before contacting Lahore office', 'envi-tech-al-modern'); ?></h2>
                <ul>
                    <li><?php esc_html_e('Facility location and industry type', 'envi-tech-al-modern'); ?></li>
                    <li><?php esc_html_e('Sample or monitoring requirement', 'envi-tech-al-modern'); ?></li>
                    <li><?php esc_html_e('Report purpose: EPA, buyer, audit, internal control, or approval', 'envi-tech-al-modern'); ?></li>
                    <li><?php esc_html_e('Deadline, standard, or parameter list if already available', 'envi-tech-al-modern'); ?></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="eta-band eta-lahore-faq">
        <div class="eta-shell">
            <?php eta_modern_section_title('Lahore environmental lab FAQ', 'Questions clients ask before selecting a testing or consultancy path', 'Short answers for Lahore and Punjab teams comparing environmental lab, water testing, monitoring, and compliance support.'); ?>
            <div class="eta-lahore-faq-grid">
                <?php foreach ($faqs as $faq) : ?>
                    <article>
                        <h2><?php echo esc_html($faq[0]); ?></h2>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-lahore-final">
        <div class="eta-shell eta-lahore-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Need environmental testing in Lahore?', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Send the requirement before the compliance timeline becomes urgent.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('The Envi Tech AL team can help convert your Lahore requirement into the right technical scope, sample plan, monitoring path, or consultancy support.', 'envi-tech-al-modern'); ?></p>
            </div>
            <div class="eta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request Lahore quotation', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/downloads/')); ?>"><?php esc_html_e('View certificates', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_render_indexed_utility_page($slug)
{
    if ($slug === 'frequently-asked-questions-water-testing-in-karachi') {
        eta_modern_render_faq_page();
        return;
    }

    if ($slug === 'lahore-environmental-lab') {
        eta_modern_render_lahore_page();
        return;
    }

    if ($slug === 'karachi-environmental-lab') {
        eta_modern_render_karachi_page();
        return;
    }

    if (in_array($slug, ['certificates-approvals', 'accreditations-certifications'], true)) {
        eta_modern_render_certificates_page();
        return;
    }

    if ($slug === 'environmental-testing-faqs-pakistan') {
        eta_modern_render_ai_faq_center_page();
        return;
    }

    if (eta_modern_cluster_page_data($slug)) {
        eta_modern_render_cluster_service_page($slug);
        return;
    }

    $data = eta_modern_utility_page_data($slug);
    if (!$data) {
        eta_modern_page_hero(eta_modern_display_title(get_the_ID()), eta_modern_plain_excerpt(get_the_ID(), 28));
        return;
    }
    ?>
    <section class="eta-utility-hero" aria-labelledby="eta-utility-title">
        <img class="eta-utility-hero-img" src="<?php echo esc_url($data['image']); ?>" alt="" aria-hidden="true" loading="eager" decoding="async" fetchpriority="high">
        <div class="eta-shell eta-utility-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php echo esc_html($data['eyebrow']); ?></p>
                <h1 id="eta-utility-title"><?php echo esc_html($data['title']); ?></h1>
                <p><?php echo esc_html($data['lead']); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request guidance', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('View services', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($data['sections'])) : ?>
        <section class="eta-band eta-utility-faq">
            <div class="eta-shell">
                <?php foreach ($data['sections'] as $group => $items) : ?>
                    <div class="eta-utility-faq-group">
                        <h2><?php echo esc_html($group); ?></h2>
                        <div class="eta-utility-faq-grid">
                            <?php foreach ($items as $item) : ?>
                                <article>
                                    <h3><?php echo esc_html($item[0]); ?></h3>
                                    <p><?php echo esc_html($item[1]); ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php elseif (!empty($data['clients'])) : ?>
        <section class="eta-band eta-utility-clients">
            <div class="eta-shell">
                <?php eta_modern_section_title('Client portfolio', 'Organizations and sectors supported by Envi Tech AL'); ?>
                <div class="eta-utility-client-grid">
                    <?php foreach ($data['clients'] as $client) : ?>
                        <span><?php echo esc_html($client); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php elseif (!empty($data['points'])) : ?>
        <section class="eta-band eta-utility-points">
            <div class="eta-shell eta-utility-points-grid">
                <?php foreach ($data['points'] as $point) : ?>
                    <article>
                        <h2><?php echo esc_html($point); ?></h2>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="eta-utility-final">
        <div class="eta-shell eta-utility-final-grid">
            <h2><?php esc_html_e('Need the right technical scope for your requirement?', 'envi-tech-al-modern'); ?></h2>
            <div class="eta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Contact Envi Tech AL', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/downloads/')); ?>"><?php esc_html_e('View resources', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_seqs_title()
{
    return 'SEQS — Sindh Environmental Quality Standards: Limits & Compliance Guide';
}

function eta_modern_seqs_meta_description()
{
    return 'What SEQS means, the Sindh Environmental Quality Standards 2016 limits for effluent, air and noise, who must comply, and how to test against them. Includes SEQS 2016 PDF.';
}

function eta_modern_seqs_pdf_url()
{
    return home_url('/downloaddocs/SindhLaws/SEQS%202016.pdf');
}

function eta_modern_seqs_effluent_limits()
{
    return [
        ['pH (range)', '6-9', '6-9', '6-9'],
        ['Temperature increase', '<=3 deg C', '<=3 deg C', '<=3 deg C'],
        ['BOD5', '80', '250', '80'],
        ['COD', '150', '400', '400'],
        ['Total suspended solids', '200', '400', '200'],
        ['Total dissolved solids', '3,500', '3,500', '3,500'],
        ['Oil & grease', '10', '10', '10'],
        ['Phenolic compounds', '0.1', '0.3', '0.3'],
        ['Sulphide (S2-)', '1.0', '1.0', '1.0'],
        ['Ammonia (NH3)', '40', '40', '40'],
        ['Cadmium', '0.1', '0.1', '0.1'],
        ['Chromium (total)', '1.0', '1.0', '1.0'],
        ['Copper', '1.0', '1.0', '1.0'],
        ['Lead', '0.5', '0.5', '0.5'],
        ['Mercury', '0.01', '0.01', '0.01'],
        ['Nickel', '1.0', '1.0', '1.0'],
        ['Zinc', '5.0', '5.0', '5.0'],
        ['Arsenic', '1.0', '1.0', '1.0'],
    ];
}

function eta_modern_seqs_faqs()
{
    return [
        ['What does SEQS stand for?', 'SEQS stands for Sindh Environmental Quality Standards - the province\'s legally enforceable limits for effluent discharge, air emissions, ambient air quality, noise and drinking water, notified under the Sindh Environmental Protection Act 2014 and enforced by SEPA.'],
        ['What is the difference between SEQS, NEQS and PEQS?', 'NEQS are the original national standards. After environmental regulation devolved to the provinces, Sindh notified SEQS (2016) and Punjab notified PEQS - each provincially enforceable. A facility in Karachi answers to SEQS and SEPA; a facility in Lahore answers to PEQS and the Punjab EPA. The frameworks are closely aligned but not identical, so always test against the standard your approval cites.'],
        ['What are the SEQS limits for wastewater discharge?', 'Key effluent limits include pH 6-9, BOD5 of 80 mg/L into inland waters (250 mg/L into sewage treatment), COD of 150 mg/L into inland waters, oil and grease of 10 mg/L, and parameter-specific limits for heavy metals such as lead (0.5 mg/L), cadmium (0.1 mg/L) and mercury (0.01 mg/L). The full tables, including discharge-destination variations, are in the complete document above.'],
        ['Where can I download the SEQS 2016 PDF?', 'The complete official SEQS 2016 notification is available from this page - see the download section above.'],
        ['How do I test my facility\'s effluent against SEQS?', 'Sampling must follow correct preservation and chain-of-custody practice. Send the discharge type, location, approval conditions, required parameters, and reporting purpose so Envi Tech AL can confirm the laboratory, methods, credential status, and exact SEQS panel before accepting the work.'],
    ];
}

function eta_modern_render_seqs_hub_page()
{
    $pdf_url = eta_modern_seqs_pdf_url();
    ?>
    <section class="eta-seqs-hero" aria-labelledby="eta-seqs-title">
        <div class="eta-shell eta-seqs-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Regulatory Compliance Guide', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-seqs-title"><?php esc_html_e('Sindh Environmental Quality Standards (SEQS): Limits, Parameters and Compliance', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('SEQS stands for the Sindh Environmental Quality Standards - the legally binding limits, notified under the Sindh Environmental Protection Act 2014, that govern what industries and facilities in Sindh may discharge to water, emit to air, and generate as noise. This guide explains what the standards require, presents the key limit values, and shows how facilities demonstrate compliance to the Sindh Environmental Protection Agency (SEPA).', 'envi-tech-al-modern'); ?></p>
                <p class="eta-seqs-reviewed"><?php esc_html_e('Last reviewed: June 2026', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="#seqs-download"><?php esc_html_e('Download SEQS 2016 (official PDF)', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Need SEQS testing? Request a quotation', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-seqs-hero-panel">
                <span><?php esc_html_e('Compliance scope', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('Effluent', 'envi-tech-al-modern'); ?></strong>
                <strong><?php esc_html_e('Air emissions', 'envi-tech-al-modern'); ?></strong>
                <strong><?php esc_html_e('Noise', 'envi-tech-al-modern'); ?></strong>
                <strong><?php esc_html_e('Drinking water', 'envi-tech-al-modern'); ?></strong>
            </aside>
        </div>
    </section>

    <section class="eta-band eta-band-light">
        <div class="eta-shell eta-seqs-copy-grid">
            <article>
                <p class="eta-eyebrow"><?php esc_html_e('What is SEQS', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('What SEQS means - and why it binds your facility', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('The Sindh Environmental Quality Standards 2016 were notified after the 18th Constitutional Amendment devolved environmental regulation to the provinces. They adapt the former National Environmental Quality Standards (NEQS) framework for Sindh and are enforced by SEPA through licensing conditions, Environmental Management Plan (EMP) approvals, and periodic Environmental Monitoring Reports (EMRs). If your facility holds - or needs - a SEPA approval, SEQS limits are the numbers your monitoring results are judged against. Exceeding them exposes the facility to penalties under the Sindh Environmental Protection Act 2014 and jeopardises NOC renewals.', 'envi-tech-al-modern'); ?></p>
                <a class="eta-text-link" href="<?php echo esc_url(home_url('/services/environmental-consultancy/')); ?>"><?php esc_html_e('environmental consultancy for SEPA compliance', 'envi-tech-al-modern'); ?></a>
            </article>
            <article>
                <p class="eta-eyebrow"><?php esc_html_e('Who must comply', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Who must comply', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('SEQS obligations apply across textile and garment units, leather and tanneries, food and beverage plants, pharmaceutical manufacturers, chemical and cement works, oil and gas operations, hospitals, hotels, construction projects and commercial facilities - in practice, any operation in Sindh that discharges effluent, runs generators or boilers, emits to air, or operates under an EMP/EMR condition. Punjab facilities operate under the parallel Punjab Environmental Quality Standards (PEQS) enforced by the Punjab EPA. Confirm the applicable authority, laboratory location, parameter, method, and current credential for each assignment.', 'envi-tech-al-modern'); ?></p>
            </article>
        </div>
    </section>

    <section class="eta-band eta-seqs-limits" aria-label="<?php esc_attr_e('SEQS liquid effluent limits', 'envi-tech-al-modern'); ?>">
        <div class="eta-shell">
            <?php eta_modern_section_title('Effluent limits table', 'SEQS liquid effluent limits - key parameters', 'The table below presents the most frequently tested parameters from the SEQS 2016 liquid effluent standards. Limits vary by discharge destination: inland waters, sewage treatment, or the sea. All values in mg/L unless stated.'); ?>
            <div class="eta-responsive-table-wrap">
                <table class="eta-responsive-table eta-seqs-table">
                    <caption class="screen-reader-text"><?php esc_html_e('SEQS liquid effluent limits by discharge destination', 'envi-tech-al-modern'); ?></caption>
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Parameter', 'envi-tech-al-modern'); ?></th>
                            <th scope="col"><?php esc_html_e('Into inland waters', 'envi-tech-al-modern'); ?></th>
                            <th scope="col"><?php esc_html_e('Into sewage treatment', 'envi-tech-al-modern'); ?></th>
                            <th scope="col"><?php esc_html_e('Into the sea', 'envi-tech-al-modern'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (eta_modern_seqs_effluent_limits() as $row) : ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($row[0]); ?></th>
                                <td><?php echo esc_html($row[1]); ?></td>
                                <td><?php echo esc_html($row[2]); ?></td>
                                <td><?php echo esc_html($row[3]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="eta-seqs-note"><?php esc_html_e('This table is a working summary for orientation. The authoritative text, including all parameters, annexes and notes, is the official notification - download the complete SEQS 2016 document below. For ambient air quality, stack emissions, motor vehicle exhaust, noise and drinking water, SEQS notifies separate annexes; the full document contains every table.', 'envi-tech-al-modern'); ?></p>
        </div>
    </section>

    <section class="eta-band eta-band-light">
        <div class="eta-shell eta-seqs-copy-grid">
            <article>
                <p class="eta-eyebrow"><?php esc_html_e('Beyond effluent', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Air, noise and drinking water standards under SEQS', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Ambient air quality: limits for SO2, NOx, particulate matter (PM10 and PM2.5), CO, ozone and lead, measured at facility boundaries and sensitive receptors. Stack and gaseous emissions: source-specific limits for boilers, generators and process stacks. Noise: day and night limits by zone - residential, commercial, industrial and silence zones. Drinking water: quality parameters aligned with WHO guideline values. Envi Tech AL confirms the requested annex, parameter set, field plan, laboratory method, and reporting scope before work begins.', 'envi-tech-al-modern'); ?></p>
                <p class="eta-seqs-link-row">
                    <a class="eta-text-link" href="<?php echo esc_url(home_url('/ambient-air-monitoring-services/')); ?>"><?php esc_html_e('ambient air monitoring', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-text-link" href="<?php echo esc_url(home_url('/services/water-testing-lab-services/')); ?>"><?php esc_html_e('water testing laboratory', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-text-link" href="<?php echo esc_url(home_url('/wastewater-testing-services/')); ?>"><?php esc_html_e('wastewater testing', 'envi-tech-al-modern'); ?></a>
                </p>
            </article>
            <article>
                <p class="eta-eyebrow"><?php esc_html_e('How compliance works', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('How facilities demonstrate SEQS compliance', 'envi-tech-al-modern'); ?></h2>
                <ol class="eta-seqs-process-list">
                    <li><strong><?php esc_html_e('Step 1 - Know your conditions.', 'envi-tech-al-modern'); ?></strong> <?php esc_html_e('Your EMP approval or NOC specifies which SEQS annexes apply, which points are sampled, and how often.', 'envi-tech-al-modern'); ?></li>
                    <li><strong><?php esc_html_e('Step 2 - Monitor and test.', 'envi-tech-al-modern'); ?></strong> <?php esc_html_e('Samples and field measurements are taken at the specified frequency. The laboratory, parameter set, methods, and current credential status are confirmed against the applicable limits before testing.', 'envi-tech-al-modern'); ?></li>
                    <li><strong><?php esc_html_e('Step 3 - Report.', 'envi-tech-al-modern'); ?></strong> <?php esc_html_e('Results are compiled into the Environmental Monitoring Report (EMR) submitted to SEPA, stating compliance status parameter by parameter.', 'envi-tech-al-modern'); ?></li>
                    <li><strong><?php esc_html_e('Step 4 - Act on exceedances.', 'envi-tech-al-modern'); ?></strong> <?php esc_html_e('Where a result exceeds a limit, corrective action may include process adjustment, treatment plant optimisation, or retesting, documented for follow-up review. Envi Tech AL can coordinate scope-confirmed testing, field monitoring, EMR preparation and corrective-action advisory.', 'envi-tech-al-modern'); ?></li>
                </ol>
            </article>
        </div>
    </section>

    <section id="seqs-download" class="eta-band eta-seqs-download" aria-labelledby="eta-seqs-download-title">
        <div class="eta-shell eta-seqs-download-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Download block - Variant B', 'envi-tech-al-modern'); ?></p>
                <h2 id="eta-seqs-download-title"><?php esc_html_e('Download the complete SEQS 2016 document', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Get the full official notification - every annex, every parameter, every note. The existing PDF remains directly accessible at its original URL.', 'envi-tech-al-modern'); ?></p>
                <a class="eta-button" href="<?php echo esc_url($pdf_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Download SEQS 2016 (PDF)', 'envi-tech-al-modern'); ?></a>
            </div>
            <form class="eta-seqs-checklist-form" action="#" method="post">
                <h3><?php esc_html_e('Want the one-page SEQS compliance checklist?', 'envi-tech-al-modern'); ?></h3>
                <p><?php esc_html_e('Enter your work email and we will send it across when the checklist is available.', 'envi-tech-al-modern'); ?></p>
                <label for="eta-seqs-email"><?php esc_html_e('Work email', 'envi-tech-al-modern'); ?></label>
                <div class="eta-seqs-form-row">
                    <input id="eta-seqs-email" name="email" type="email" placeholder="<?php esc_attr_e('name@company.com', 'envi-tech-al-modern'); ?>" autocomplete="email">
                    <button type="submit"><?php esc_html_e('Send checklist', 'envi-tech-al-modern'); ?></button>
                </div>
                <p class="eta-seqs-consent"><?php esc_html_e('Envi Tech AL will occasionally send compliance updates relevant to your role. Unsubscribe at any time.', 'envi-tech-al-modern'); ?></p>
            </form>
        </div>
    </section>

    <section class="eta-band eta-band-light eta-seqs-faq" aria-label="<?php esc_attr_e('SEQS frequently asked questions', 'envi-tech-al-modern'); ?>">
        <div class="eta-shell">
            <?php eta_modern_section_title('Frequently asked questions', 'SEQS answers for compliance teams'); ?>
            <div class="eta-faq-answer-grid">
                <?php foreach (eta_modern_seqs_faqs() as $faq) : ?>
                    <details class="eta-faq-item">
                        <summary><?php echo esc_html($faq[0]); ?></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-seqs-final">
        <div class="eta-shell eta-seqs-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Testing against SEQS limits and reporting against defined approval conditions', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('From a single effluent sample to a complete EMR programme - one coordinated team for sampling, scope-confirmed analysis, monitoring and reporting support.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request a quotation', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923152006074'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}

function eta_modern_page_hero($title = '', $lead = '')
{
    if ($title) {
        $charset = get_bloginfo('charset') ?: 'UTF-8';
        $title = (string) $title;
        for ($i = 0; $i < 3; $i++) {
            $decoded = html_entity_decode($title, ENT_QUOTES, $charset);
            if ($decoded === $title) {
                break;
            }
            $title = $decoded;
        }
        $title = trim(wp_strip_all_tags($title));
    } else {
        $title = eta_modern_display_title();
    }
    ?>
    <section class="eta-hero eta-page-hero">
        <div class="eta-shell">
            <p class="eta-eyebrow"><?php esc_html_e('Envi Tech AL', 'envi-tech-al-modern'); ?></p>
            <h1><?php echo esc_html($title); ?></h1>
            <?php if ($lead) : ?>
                <p><?php echo esc_html($lead); ?></p>
            <?php endif; ?>
        </div>
    </section>
    <?php
}
