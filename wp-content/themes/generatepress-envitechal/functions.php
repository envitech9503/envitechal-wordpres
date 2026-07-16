<?php
/**
 * Envi Tech AL Modern child theme bootstrap.
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_stylesheet_directory() . '/inc/premium-post-patterns.php';
require_once get_stylesheet_directory() . '/inc/legacy-redirects.php';
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
            '<link rel="preload" as="image" href="%1$s" imagesrcset="%2$s 520w, %3$s 900w, %4$s 1500w" imagesizes="100vw">' . "\n",
            esc_url($hero_base . 'eta-home-hero-1500.webp'),
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
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash((string) $_SERVER['REQUEST_URI']) : '';
    $request_path = $request_uri !== '' ? parse_url($request_uri, PHP_URL_PATH) : '';
    if ($request_path === '/robots.txt') {
        header('Content-Type: text/plain; charset=utf-8', true);
    }
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
    $request_method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper((string) $_SERVER['REQUEST_METHOD']) : 'GET';
    if (!in_array($request_method, ['GET', 'HEAD'], true)) {
        return;
    }

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

    $legacy_target = eta_modern_legacy_redirect_target($path);
    if ($legacy_target !== null) {
        wp_safe_redirect(home_url($legacy_target), 301);
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
