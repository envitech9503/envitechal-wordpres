<?php
/**
 * PEQS and NEQS reference pages: data fidelity and page contract.
 *
 * The limit tables were transcribed from the official notifications (Punjab
 * Gazette 15-08-2016; S.R.O. 549(I)/2000; Gazette of Pakistan 26-11-2010).
 * This test pins the values most likely to be misread or edited by mistake,
 * checks the structural contract both pages share with the SEQS page, and
 * renders each page through a WordPress stub to make sure the markup is
 * complete and the house rules hold.
 *
 * Run: php tests/standards-pages-test.php
 */

define('ABSPATH', '/');
$GLOBALS['eta_slug'] = 'punjab-environmental-quality-standards-peqs';
function is_page($x = null) { if ($x === null) return true; return in_array($GLOBALS['eta_slug'], (array) $x, true); }
function esc_html($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s) { return esc_html($s); }
function esc_url($s) { return esc_html($s); }
function esc_html_e($s, $d = '') { echo esc_html($s); }
function esc_attr_e($s, $d = '') { echo esc_attr($s); }
function __($s, $d = '') { return $s; }
function home_url($p = '') { return 'https://envitechal.com' . $p; }
function sanitize_title($s) { return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $s)); }
function add_action() {}
function get_permalink() { return 'https://envitechal.com/' . $GLOBALS['eta_slug'] . '/'; }
function get_the_date($f) { return '2026-10-21'; }
function get_the_modified_date($f) { return '2026-10-21'; }
function get_the_ID() { return 1; }
function wp_strip_all_tags($s) { return strip_tags($s); }
function eta_modern_default_share_image() { return 'https://envitechal.com/share.png'; }
function eta_modern_display_title($id) { return 'x'; }

$root = dirname(__DIR__);
$theme = $root . '/wp-content/themes/generatepress-envitechal';
$functions = file_get_contents($theme . '/functions.php');
foreach (['function eta_modern_section_title', 'function eta_modern_schema_faq_page'] as $sig) {
    $start = strpos($functions, $sig);
    $end = strpos($functions, "\n}\n", $start) + 3;
    eval(substr($functions, $start, $end - $start));
}
require $theme . '/inc/standards-pages.php';

$failures = 0;
function check($label, $condition, &$failures)
{
    echo ($condition ? 'PASS ' : 'FAIL ') . $label . "\n";
    if (!$condition) {
        $failures++;
    }
}
function row($rows, $name)
{
    foreach ($rows as $r) {
        if ($r[0] === $name) {
            return $r;
        }
    }
    return null;
}

// Effluent: 32 parameters, PEQS has two destination columns, NEQS three.
$neqs = eta_modern_standards_effluent_rows(true);
$peqs = eta_modern_standards_effluent_rows(false);
check('effluent table has 32 parameters', count($neqs) === 32 && count($peqs) === 32, $failures);
check('PEQS effluent has 3 columns, NEQS 4', count($peqs[0]) === 3 && count($neqs[0]) === 4, $failures);
check('BOD5 80 / 250 / 80', row($neqs, 'BOD5 at 20 °C (1)') === ['BOD5 at 20 °C (1)', '80', '250', '80 (**)'], $failures);
check('COD 150 / 400 / 400', array_slice(row($neqs, 'COD (1)'), 1) === ['150', '400', '400'], $failures);
check('TSS 200 / 400 / 200', array_slice(row($neqs, 'Total suspended solids (TSS)'), 1) === ['200', '400', '200'], $failures);
check('phenol 0.1 / 0.3 / 0.3', array_slice(row($neqs, 'Phenolic compounds (as phenol)'), 1) === ['0.1', '0.3', '0.3'], $failures);
check('sulphate 600 / 1,000 / SC', array_slice(row($neqs, 'Sulphate (SO₄²⁻)'), 1) === ['600', '1,000', 'SC (***)'], $failures);
check('mercury 0.01', row($neqs, 'Mercury (4)')[1] === '0.01', $failures);
check('iron 8.0 (revised, not the old 2.0)', row($neqs, 'Iron')[1] === '8.0', $failures);
check('total toxic metals 2.0', row($neqs, 'Total toxic metals')[1] === '2.0', $failures);

// Drinking water: the values Pakistan sets above WHO are the ones to pin.
$dw = eta_modern_standards_drinking_water_rows();
check('arsenic 0.05 (P) vs WHO 0.01', row($dw, 'Arsenic') === ['Arsenic', '0.05 mg/L or less (P)', '0.01 mg/L'], $failures);
check('lead 0.05 vs WHO 0.01', row($dw, 'Lead') === ['Lead', '0.05 mg/L or less', '0.01 mg/L'], $failures);
check('cadmium 0.01 vs WHO 0.003', row($dw, 'Cadmium') === ['Cadmium', '0.01 mg/L', '0.003 mg/L'], $failures);
check('zinc 5.0 vs WHO 3', row($dw, 'Zinc') === ['Zinc', '5.0 mg/L', '3 mg/L'], $failures);
check('nitrate 50', row($dw, 'Nitrate as NO₃ (*)')[1] === '50 mg/L or less', $failures);
check('residual chlorine 0.2 to 0.5 at consumer end', strpos(row($dw, 'Residual chlorine')[1], '0.2 to 0.5') === 0, $failures);
check('E. coli not detectable in 100 mL', strpos(row($dw, 'E. coli or thermotolerant coliform')[1], 'Not detectable in any 100 mL') === 0, $failures);

// Ambient air (2013 values) and noise (second stage).
$air = eta_modern_standards_ambient_air_rows();
check('PM2.5 annual 15, 24 h 35', $air[11][2] === '15 µg/m³' && $air[12][2] === '35 µg/m³', $failures);
check('PM10 annual 120, 24 h 150', $air[9][2] === '120 µg/m³' && $air[10][2] === '150 µg/m³', $failures);
check('SO2 annual 80, 24 h 120', $air[0][2] === '80 µg/m³' && $air[1][2] === '120 µg/m³', $failures);
check('CO 8 h 5 mg, 1 h 10 mg', $air[16][2] === '5 mg/m³' && $air[17][2] === '10 mg/m³', $failures);
$noise = eta_modern_standards_noise_rows();
check('noise residential 55/45, industrial 75/65', $noise[0] === ['Residential area (A)', '55', '45'] && $noise[2] === ['Industrial area (C)', '75', '65'], $failures);

// Gaseous emissions.
$em = eta_modern_standards_emissions_rows();
check('PM oil 300, coal 500, cement 300', $em[1][2] === '300' && $em[2][2] === '500' && $em[3][2] === '300', $failures);
check('SOx other plants 1,700', row($em, 'Sulphur oxides (2)(3)') !== null && $em[10][2] === '1,700', $failures);
check('NOx gas 400, oil 600, coal 1,200', $em[20][2] === '400' && $em[21][2] === '600' && $em[22][2] === '1,200', $failures);
check('CO 800, H2S 10', row($em, 'Carbon monoxide')[2] === '800' && row($em, 'Hydrogen sulphide')[2] === '10', $failures);

// Page contract for both slugs.
$american = '/\b(organiz|analyz|recogniz|optimiz|utiliz|color\b|center\b|license\b|program\b)/i';
foreach (eta_modern_standards_pages() as $slug => $c) {
    $GLOBALS['eta_slug'] = $slug;
    check("$slug: title within 60", strlen($c['title']) <= 60, $failures);
    check("$slug: meta within 160", strlen($c['meta']) <= 160, $failures);
    check("$slug: definition answers 'what does X stand for'", strpos($c['definition'], $c['short'] . ' stands for') === 0, $failures);
    check("$slug: five tables", count($c['tables']) === 5, $failures);
    check("$slug: eight FAQs", count($c['faqs']) === 8, $failures);
    check("$slug: official download links present", count($c['downloads']) >= 3, $failures);
    ob_start();
    eta_modern_render_standards_page();
    $html = ob_get_clean();
    check("$slug: renders", strlen($html) > 50000, $failures);
    check("$slug: one h1", substr_count($html, '<h1') === 1, $failures);
    check("$slug: five tables rendered", substr_count($html, '<table') === 5, $failures);
    check("$slug: no em or en dash", strpos($html, "\u{2014}") === false && strpos($html, "\u{2013}") === false, $failures);
    check("$slug: British spelling", !preg_match($american, strip_tags($html)), $failures);
    check("$slug: links to both laboratories", strpos($html, '/karachi-environmental-lab/') !== false || strpos($html, '/lahore-environmental-lab/') !== false, $failures);
    check("$slug: links to the sister standards pages", strpos($html, '/sindh-environmental-quality-standards-seqs/') !== false, $failures);
    check("$slug: no compliance guarantee wording", !preg_match('/guarantee|ensure you pass|100% approval/i', $html), $failures);
    $nodes = eta_modern_standards_schema_nodes($c['meta']);
    check("$slug: Article and FAQPage schema", isset($nodes['eta-standards-article'], $nodes['eta-standards-faq']) && count($nodes['eta-standards-faq']['mainEntity']) === 8, $failures);
}

// Wiring in functions.php and page.php.
$page = file_get_contents($theme . '/page.php');
check('functions.php requires the module', strpos($functions, "/inc/standards-pages.php'") !== false, $failures);
check('page.php dispatches the renderer', strpos($page, 'eta_modern_render_standards_page()') !== false, $failures);
check('title hook present', substr_count($functions, 'return eta_modern_standards_title();') === 2, $failures);
check('meta hook present', strpos($functions, 'return eta_modern_standards_meta_description();') !== false, $failures);
check('schema hook present', strpos($functions, 'eta_modern_standards_schema_nodes($description)') !== false, $failures);
check('custom page lists include both slugs', substr_count($functions, "'punjab-environmental-quality-standards-peqs',") >= 2 && substr_count($functions, "'national-environmental-quality-standards-neqs',") >= 2, $failures);

if ($failures) {
    fwrite(STDERR, "$failures failure(s).\n");
    exit(1);
}
echo "All checks passed.\n";
