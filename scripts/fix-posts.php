<?php
/**
 * Editorial corrections for three posts (14-09-2026): remove em-dash clauses,
 * anglicise spellings. Run on the server with:
 *   wp --path=<root> eval-file scripts/fix-posts.php
 * Proper nouns (World Health Organization, International Organization for
 * Standardization, NELAP Program) are left untouched.
 */
$jobs = [
    22198 => [ // water-testing-lab-lahore
        ['Start with the purpose — not a generic', 'Start with the purpose, not a generic'],
        ['how it was analysed and—where applicable—what comparison basis was used', 'how it was analysed and, where applicable, what comparison basis was used'],
        ['Pakistan National Accreditation Council — Envi Tech AL LAB-347', 'Pakistan National Accreditation Council: Envi Tech AL LAB-347'],
        ['Pakistan National Accreditation Council — Active Testing', 'Pakistan National Accreditation Council: Active Testing'],
        ['Punjab Environmental Protection Agency — Envi Tech AL', 'Punjab Environmental Protection Agency: Envi Tech AL'],
        ['World Health Organization — Guidelines', 'World Health Organization: Guidelines'],
        ['International Organization for Standardization — ISO/IEC', 'International Organization for Standardization: ISO/IEC'],
        ['Organizations requiring wider', 'Organisations requiring wider'],
        ['organizations', 'organisations'],
        ['organization’s', 'organisation’s'],
        ["organization's", "organisation's"],
        ['an organization', 'an organisation'],
        ['the organization', 'the organisation'],
        ['recognized', 'recognised'],
        ['emphasizes', 'emphasises'],
    ],
    22706 => [ // environmental-consultancy-karachi-guide
        ['environmental issues — air pollution, water contamination, flooding, heatwaves — affect everyday life', 'environmental issues such as air pollution, water contamination, flooding and heatwaves affect everyday life'],
        ['frequent reports — higher cost', 'frequent reports, so a higher cost'],
        ['analyzed', 'analysed'],
        ['HS Code license', 'HS Code licence'],
        ['Social license', 'Social licence'],
        ['need license of existing business', 'need a licence for an existing business'],
    ],
    4642 => [ // how-to-choose-the-suitable-environmental-lab
        ['analyze', 'analyse'],
        ['utilize', 'utilise'],
    ],
];

foreach ($jobs as $id => $pairs) {
    $post = get_post($id);
    if (!$post) { WP_CLI::warning("Post $id not found"); continue; }
    $c = $post->post_content; $n = 0;
    foreach ($pairs as [$a, $b]) {
        $k = substr_count($c, $a);
        if ($k === 0) { WP_CLI::log("  $id: not found: $a"); continue; }
        $c = str_replace($a, $b, $c); $n += $k;
    }
    // Any remaining lowercase "organization" (not part of a proper noun) → organisation.
    $c = preg_replace('/\borganization(s?)\b/', 'organisation$1', $c, -1, $k); $n += $k;
    if ($c !== $post->post_content) {
        $r = wp_update_post(['ID' => $id, 'post_content' => $c], true);
        if (is_wp_error($r)) WP_CLI::error($r->get_error_message());
        WP_CLI::success("Post $id ({$post->post_name}): $n replacements, remaining em dashes: " . substr_count($c, '—'));
    } else {
        WP_CLI::log("Post $id: no change");
    }
}
