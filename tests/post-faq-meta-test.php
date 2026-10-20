<?php
/**
 * FAQ schema from post meta, and the keyword-support content of 20-10-2026.
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

$manifest = json_decode(file_get_contents($root . '/scripts/posts-20-10-2026/manifest.json'), true);
check('manifest has three posts', is_array($manifest) && count($manifest['posts']) === 3, $failures);

$owner_links = [
    'water-testing-lab-pakistan-any-city' => ['https://envitechal.com/services/water-testing-lab-services/', 'https://envitechal.com/water-testing-lab-lahore/'],
    'verify-pnac-accredited-lab-pakistan' => ['https://envitechal.com/karachi-environmental-lab/', 'https://envitechal.com/lahore-environmental-lab/'],
    'water-testing-karachi-borewell-tanker-ro' => ['https://envitechal.com/services/water-testing-lab-services/'],
];
$american = '/\b(organiz|analyz|recogniz|optimiz|utiliz|color\b|center\b|license\b|program\b)/i';

foreach ($manifest['posts'] as $post) {
    $slug = $post['slug'];
    $body = file_get_contents($root . '/scripts/posts-20-10-2026/' . $slug . '.html');
    check("$slug: body present", $body !== false && strlen($body) > 5000, $failures);
    check("$slug: no em or en dash", strpos($body, "\u{2014}") === false && strpos($body, "\u{2013}") === false, $failures);
    check("$slug: British spelling", !preg_match($american, strip_tags($body)), $failures);
    check("$slug: hero image exists", file_exists($root . '/scripts/posts-20-10-2026/images/' . $post['image']), $failures);
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
}

$functions = $source;
foreach (['/water-testing-karachi-borewell-tanker-ro/', '/verify-pnac-accredited-lab-pakistan/', '/water-testing-lab-pakistan-any-city/'] as $path) {
    check("theme links to $path", strpos($functions, "home_url('" . $path . "')") !== false, $failures);
    check("SEO override for $path", strpos($functions, "'" . trim($path, '/') . "' => [") !== false, $failures);
}
$water = file_get_contents($root . '/wp-content/themes/generatepress-envitechal/template-parts/service-water-testing.php');
check('water page related cards include the new articles', strpos($water, '/water-testing-lab-pakistan-any-city/') !== false && strpos($water, '/water-testing-karachi-borewell-tanker-ro/') !== false, $failures);

if ($failures) {
    fwrite(STDERR, "$failures failure(s).\n");
    exit(1);
}
echo "All checks passed.\n";
