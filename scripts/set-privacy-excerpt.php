<?php
/** Shorten the Privacy Policy meta description (page excerpt) to under 160 characters. */
$p = get_page_by_path('privacy-policy', OBJECT, 'page');
if (!$p) { echo "privacy-policy not found\n"; return; }
$t = 'How Envi Tech AL collects, uses and protects information from enquiries, job applications, report verification and the site assistant.';
$r = wp_update_post(['ID' => $p->ID, 'post_excerpt' => $t], true);
echo is_wp_error($r) ? 'ERROR ' . $r->get_error_message() . "\n" : "privacy excerpt set (" . strlen($t) . " chars)\n";
