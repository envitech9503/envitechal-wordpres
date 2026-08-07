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

$ballast = eta_modern_normalize_legacy_copy(
    '<p>Our&nbsp;<strong>ISO/IEC 17025:2017 accredited laboratory</strong>&nbsp;is equipped with cutting-edge technology to deliver accurate and reliable ballast water analysis, helping marine vessels meet the stringent environmental regulations set by the&nbsp;<strong>International Maritime Organization (IMO)</strong>.</p>'
);
eta_credential_claim_test_true(strpos($ballast, 'Neither Karachi LAB-285 nor Lahore LAB-347 establishes PNAC accreditation for ballast-water testing') !== false, 'ballast-water capability is not presented as accredited');
eta_credential_claim_test_true(strpos($ballast, 'accredited laboratory</strong>') === false, 'legacy ballast accreditation wording is removed');

$company_facts = eta_modern_normalize_legacy_copy(
    'The company facts confirm ISO/IEC 17025:2017 accreditation, TUV certification, and Sindh EPA and Punjab EPA certification.'
);
eta_credential_claim_test_true(strpos($company_facts, 'Karachi LAB-285 and Lahore LAB-347') !== false, 'generic company credential sentence becomes branch-specific');
eta_credential_claim_test_true(strpos($company_facts, 'Management-system and EPA credentials are separate') !== false, 'credential categories remain separate');

$workflow = eta_modern_normalize_legacy_copy(
    'Envi Tech AL supports environmental monitoring, accredited laboratory testing, field sampling coordination, compliance reporting, water and wastewater testing, ambient air monitoring, noise monitoring, emissions testing, calibration coordination, and environmental consultancy for industrial and commercial facilities in Pakistan.'
);
eta_credential_claim_test_true(strpos($workflow, 'supports environmental monitoring, laboratory testing') !== false, 'capability list removes universal accredited adjective');
eta_credential_claim_test_true(strpos($workflow, 'exact work matches Karachi LAB-285 or Lahore LAB-347') !== false, 'workflow copy adds exact branch scope boundary');

echo "Credential-claim normalization tests passed.\n";
