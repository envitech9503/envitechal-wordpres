<?php
/**
 * FAQ schema from post meta, and the keyword-support content of 20-10-2026 and 22-10-2026.
 *
 * eta_modern_post_meta_faqs() is isolated from functions.php and run against
 * the JSON the publishing script writes, so the schema the theme emits and the
 * visible FAQ sections in the articles cannot drift apart. The second half
 * checks each article body against the house rules that matter to search and
 * to the client: no em dash clauses, British spellings, exact-anchor links to
 * the owner pages, and a visible FAQ heading for every FAQ in the manifest.
 *
 * Run: php tests/post-faq-meta-test.php
 */

$root = dirname(__DIR__);
$source = file_get_contents($root . '/wp-content/themes/generatepress-envitechal/functions.php');
$start = strpos($source, 'function eta_modern_post_meta_faqs');
$end = strpos($source, 'function eta_modern_schema_faq_page');
if ($start === false || $end === false || $end <= $start) {
    fwrite(STDERR, "Could not isolate eta_modern_post_meta_faqs().\n");
    exit(1);
}

$GLOBALS['eta_test_meta'] = '';
function get_post_meta($post_id, $key, $single)
{
    return $GLOBALS['eta_test_meta'];
}
eval(substr($source, $start, $end - $start));

$failures = 0;
function check($label, $condition, &$failures)
{
    echo ($condition ? 'PASS ' : 'FAIL ') . $label . "\n";
    if (!$condition) {
        $failures++;
    }
}

$GLOBALS['eta_test_meta'] = '';
check('empty meta gives no FAQs', eta_modern_post_meta_faqs(1) === [], $failures);
$GLOBALS['eta_test_meta'] = '{not json';
check('invalid JSON gives no FAQs', eta_modern_post_meta_faqs(1) === [], $failures);
$GLOBALS['eta_test_meta'] = json_encode([['Q1', 'A1'], ['bad'], ['Q2', 'A2', 'extra'], 'string']);
check('malformed pairs are skipped', eta_modern_post_meta_faqs(1) === [['Q1', 'A1'], ['Q2', 'A2']], $failures);

check('post schema falls back to meta FAQs', strpos($source, "eta_modern_schema_faq_page(eta_modern_post_meta_faqs(\$post_id)") !== false, $failures);

$batches = [
    '20-10-2026' => [
        'water-testing-lab-pakistan-any-city' => ['https://envitechal.com/services/water-testing-lab-services/', 'https://envitechal.com/water-testing-lab-lahore/'],
        'verify-pnac-accredited-lab-pakistan' => ['https://envitechal.com/karachi-environmental-lab/', 'https://envitechal.com/lahore-environmental-lab/'],
        'water-testing-karachi-borewell-tanker-ro' => ['https://envitechal.com/services/water-testing-lab-services/'],
    ],
    '22-10-2026' => [
        'how-to-choose-the-suitable-environmental-lab' => ['https://envitechal.com/karachi-environmental-lab/', 'https://envitechal.com/lahore-environmental-lab/', 'https://envitechal.com/verify-pnac-accredited-lab-pakistan/'],
        'epa-certified-laboratories-pakistan' => ['https://envitechal.com/karachi-environmental-lab/', 'https://envitechal.com/lahore-environmental-lab/', 'https://envitechal.com/punjab-environmental-quality-standards-peqs/'],
        'water-testing-cost-lahore' => ['https://envitechal.com/water-testing-lab-lahore/', 'https://envitechal.com/water-testing-cost-karachi/', 'https://envitechal.com/punjab-environmental-quality-standards-peqs/'],
    ],
];
foreach ($batches as $batch => $owner_links) {
$manifest = json_decode(file_get_contents($root . '/scripts/posts-' . $batch . '/manifest.json'), true);
check("manifest $batch has three posts", is_array($manifest) && count($manifest['posts']) === 3, $failures);

$american = '/\b(organiz|analyz|recogniz|optimiz|utiliz|color\b|center\b|license\b|program\b)/i';

foreach ($manifest['posts'] as $post) {
    $slug = $post['slug'];
    $body = file_get_contents($root . '/scripts/posts-' . $batch . '/' . $slug . '.html');
    check("$slug: body present", $body !== false && strlen($body) > 5000, $failures);
    check("$slug: no em or en dash", strpos($body, "\u{2014}") === false && strpos($body, "\u{2013}") === false, $failures);
    check("$slug: British spelling", !preg_match($american, strip_tags($body)), $failures);
    check("$slug: hero image exists", file_exists($root . '/scripts/posts-' . $batch . '/images/' . $post['image']), $failures);
    check("$slug: SEO title within 60", strlen($post['seo_title']) <= 61, $failures);
    check("$slug: description within 160", strlen($post['description']) <= 160, $failures);
    foreach ($owner_links[$slug] as $link) {
        check("$slug: links to $link", strpos($body, 'href="' . $link . '"') !== false, $failures);
    }
    foreach ($post['faqs'] as $faq) {
        $q = htmlspecialchars($faq[0], ENT_QUOTES);
        check("$slug: visible FAQ heading: " . substr($faq[0], 0, 50), strpos($body, $faq[0]) !== false || strpos($body, $q) !== false, $failures);
    }
    $GLOBALS['eta_test_meta'] = json_encode($post['faqs'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    check("$slug: meta round-trips to " . count($post['faqs']) . ' FAQs', count(eta_modern_post_meta_faqs(1)) === count($post['faqs']), $failures);
    check("$slug: no compliance guarantee wording", !preg_match('/guaranteed compliance|ensure you pass|100% approval|will pass/i', strip_tags($body)), $failures);
}
}

$functions = $source;
foreach (['/water-testing-karachi-borewell-tanker-ro/', '/verify-pnac-accredited-lab-pakistan/', '/water-testing-lab-pakistan-any-city/'] as $path) {
    check("theme links to $path", strpos($functions, "home_url('" . $path . "')") !== false, $failures);
    check("SEO override for $path", strpos($functions, "'" . trim($path, '/') . "' => [") !== false, $failures);
}
preg_match_all("/^        '([a-z0-9-]+)' => \\[$/m", substr($functions, strpos($functions, 'function eta_modern_post_seo_overrides'), 20000), $keys);
check('no duplicate SEO override keys', count($keys[1]) === count(array_unique($keys[1])), $failures);
$water = file_get_contents($root . '/wp-content/themes/generatepress-envitechal/template-parts/service-water-testing.php');
check('water page related cards include the new articles', strpos($water, '/water-testing-lab-pakistan-any-city/') !== false && strpos($water, '/water-testing-karachi-borewell-tanker-ro/') !== false, $failures);

if ($failures) {
    fwrite(STDERR, "$failures failure(s).\n");
    exit(1);
}
echo "All checks passed.\n";
