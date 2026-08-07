<?php

define('ABSPATH', __DIR__);

function add_action()
{
    // Hook registration is intentionally inert in this pure content test.
}

function add_filter()
{
    // Hook registration is intentionally inert in this pure content test.
}

function home_url($path = '')
{
    return 'https://envitechal.com' . $path;
}

function get_stylesheet_directory()
{
    return dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal';
}

require dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal/functions.php';

function eta_credential_claim_test_true($condition, $label)
{
    if ($condition) {
        return;
    }
    file_put_contents('php://stderr', "FAILED: {$label}\n", FILE_APPEND);
    exit(1);
}

$generic = eta_modern_normalize_legacy_copy(
    '<p>Envi Tech AL is a PNAC-accredited environmental laboratory in Pakistan.</p>'
);
eta_credential_claim_test_true(strpos($generic, 'LAB-285') !== false, 'generic legacy claim names Karachi LAB-285');
eta_credential_claim_test_true(strpos($generic, 'LAB-347') !== false, 'generic legacy claim names Lahore LAB-347');
eta_credential_claim_test_true(strpos($generic, 'PNAC-accredited environmental laboratory in Pakistan') === false, 'unbounded generic PNAC claim is removed');

$combined = eta_modern_normalize_legacy_copy(
    '<p>Envi Tech AL is Sindh EPA approved lab having Green Lab certification (Gold), ISO 9001:2015, ISO 14001:2015 certification and ISO 17025:2017 accreditation from PNAC .</p>'
);
eta_credential_claim_test_true(strpos($combined, 'separate regulatory, management-system, and laboratory credentials') !== false, 'credential categories are separated');
eta_credential_claim_test_true(strpos($combined, 'applicable current published scope') !== false, 'scope boundary is explicit');
eta_credential_claim_test_true(strpos($combined, 'Green Lab certification (Gold)') === false, 'unsupported bundled rating is removed');

$precise = 'Karachi PNAC LAB-285 covers only its current published scope.';
eta_credential_claim_test_true(eta_modern_normalize_legacy_copy($precise) === $precise, 'already precise claims remain unchanged');

echo "Credential-claim normalization tests passed.\n";
