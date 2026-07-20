<?php

define('ABSPATH', __DIR__);

function get_stylesheet_directory()
{
    return dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal';
}

function add_action()
{
    // Hook registration is intentionally inert in this pure graph test.
}

function add_filter()
{
    // Hook registration is intentionally inert in this pure graph test.
}

function home_url($path = '')
{
    return 'https://envitechal.com' . $path;
}

require dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal/functions.php';

function eta_schema_test_true($condition, $label)
{
    if ($condition) {
        return;
    }
    file_put_contents('php://stderr', "FAILED: {$label}\n", FILE_APPEND);
    exit(1);
}

$organization = eta_modern_schema_organization();
$karachi = eta_modern_schema_local_business('karachi', 'Karachi description');
$lahore = eta_modern_schema_local_business('lahore', 'Lahore description');
$organization_json = json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$karachi_json = json_encode($karachi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$lahore_json = json_encode($lahore, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

eta_schema_test_true($organization['legalName'] === 'Envi Tech AL (Pvt.) Ltd.', 'legal name is present');
eta_schema_test_true(count($organization['hasCredential']) === 4, 'organization carries the four verified credentials');
eta_schema_test_true(strpos($organization_json, 'LAB-347') !== false, 'organization carries LAB-347');
eta_schema_test_true(strpos($karachi_json, 'LAB-347') === false, 'Karachi never carries LAB-347');
eta_schema_test_true(strpos($lahore_json, 'LAB-347') !== false, 'Lahore carries LAB-347');
eta_schema_test_true($karachi['geo']['latitude'] === 24.883882777821796, 'Karachi latitude matches the published map embed');
eta_schema_test_true($lahore['geo']['longitude'] === 74.29714724390335, 'Lahore longitude matches the published map embed');
eta_schema_test_true(!isset($karachi['priceRange']), 'non-conforming priceRange is absent');
eta_schema_test_true(isset($karachi['hasOfferCatalog']['itemListElement'][0]['itemOffered']['serviceType']), 'offers use OfferCatalog to Service nesting');
eta_schema_test_true(strpos($karachi_json, 'Environmental Lab & Analytical Services') !== false, 'offer ampersand is not entity encoded');
eta_schema_test_true(isset($organization['department'], $karachi['branchOf'], $karachi['parentOrganization']), 'department and reciprocal branch relationships are present');
eta_schema_test_true(!isset($karachi['openingHoursSpecification']), 'unconfirmed operating hours are not invented');
eta_schema_test_true($organization['contactPoint']['availableLanguage'] === ['en', 'ur'], 'customer service languages are present');

echo "Structured-data graph tests passed.\n";

