<?php
/**
 * Create the theme-rendered legal pages if they are missing (the theme does
 * this on admin_init, but promotion should not wait for a dashboard visit).
 * Run: wp --path=<root> eval-file scripts/promote-legal-pages.php
 */
if (!function_exists('eta_modern_legal_pages')) { echo "theme legal-pages module not loaded\n"; return; }
foreach (eta_modern_legal_pages() as $slug => $page) {
    $existing = get_page_by_path($slug, OBJECT, 'page');
    if ($existing) { echo "exists: $slug (ID {$existing->ID}, status {$existing->post_status})\n"; continue; }
    $id = wp_insert_post([
        'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug,
        'post_title' => $page['title'], 'post_excerpt' => $page['excerpt'],
        'post_content' => '<!-- Rendered by the theme: inc/legal-pages.php -->',
    ], true);
    echo is_wp_error($id) ? "ERROR $slug: " . $id->get_error_message() . "\n" : "created: $slug (ID $id)\n";
}
if (defined('ETA_LEGAL_PAGES_VERSION')) { update_option('eta_legal_pages_version', ETA_LEGAL_PAGES_VERSION); }
