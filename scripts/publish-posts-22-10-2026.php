<?php
/**
 * Publish the three keyword-support articles (one rewrite in place) of 22-10-2026.
 *
 * Run on the server with WP-CLI (staging first, then production):
 *   wp --path="$HOME/staging.envitechal.com" eval-file scripts/publish-posts-22-10-2026.php dry
 *   wp --path="$HOME/staging.envitechal.com" eval-file scripts/publish-posts-22-10-2026.php
 *   wp --path="$HOME/public_html"            eval-file scripts/publish-posts-22-10-2026.php
 *
 * Idempotent: a post is matched by slug and updated in place, so the script
 * can be re-run after an edit to the HTML or manifest. "dry" reports what it
 * would do without writing.
 *
 * Sources: scripts/posts-22-10-2026/manifest.json, the HTML body per slug,
 * and the hero image per slug in scripts/posts-22-10-2026/images/.
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "Run through wp eval-file.\n");
    exit(1);
}

$dry = isset($args) && in_array('dry', (array) $args, true);
$dir = __DIR__ . '/posts-22-10-2026';
$manifest = json_decode((string) file_get_contents($dir . '/manifest.json'), true);
if (!is_array($manifest) || empty($manifest['posts'])) {
    WP_CLI::error('manifest.json missing or invalid');
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$author_user = get_user_by('email', 'info@envitechal.com');
if (!$author_user) {
    $admins = get_users(['role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC']);
    $author_user = $admins ? $admins[0] : null;
}
$author_id = $author_user ? (int) $author_user->ID : 1;

function eta_publish_term_ids($slugs, $taxonomy)
{
    $ids = [];
    foreach ((array) $slugs as $slug) {
        $term = get_term_by('slug', sanitize_title($slug), $taxonomy);
        if (!$term && $taxonomy === 'post_tag') {
            $created = wp_insert_term($slug, 'post_tag');
            if (!is_wp_error($created)) {
                $term = get_term($created['term_id'], 'post_tag');
            }
        }
        if ($term && !is_wp_error($term)) {
            $ids[] = (int) $term->term_id;
        } else {
            WP_CLI::warning("Term not found in {$taxonomy}: {$slug}");
        }
    }
    return $ids;
}

function eta_publish_sideload_image($path, $post_id, $alt, $dry)
{
    if (!file_exists($path)) {
        WP_CLI::warning("Image missing: {$path}");
        return 0;
    }
    $existing = get_posts([
        'post_type' => 'attachment',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_eta_source_image',
        'meta_value' => basename($path),
    ]);
    if ($existing) {
        return (int) $existing[0];
    }
    if ($dry) {
        return -1;
    }
    $tmp = wp_tempnam(basename($path));
    copy($path, $tmp);
    $file = ['name' => basename($path), 'tmp_name' => $tmp, 'type' => 'image/webp'];
    $attachment_id = media_handle_sideload($file, $post_id, $alt);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        WP_CLI::warning('Sideload failed: ' . $attachment_id->get_error_message());
        return 0;
    }
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    update_post_meta($attachment_id, '_eta_source_image', basename($path));
    return (int) $attachment_id;
}

$done = [];
foreach ($manifest['posts'] as $post) {
    $slug = $post['slug'];
    $body_file = $dir . '/' . $slug . '.html';
    if (!file_exists($body_file)) {
        WP_CLI::warning("Body missing for {$slug}");
        continue;
    }
    $content = trim((string) file_get_contents($body_file));
    $existing = get_page_by_path($slug, OBJECT, 'post');

    $data = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_name' => $slug,
        'post_title' => $post['title'],
        'post_content' => $content,
        'post_excerpt' => $post['excerpt'],
        'post_author' => $author_id,
        'comment_status' => 'closed',
    ];
    if ($existing) {
        $data['ID'] = (int) $existing->ID;
        if (!empty($post['republish'])) {
            // Full rewrite of an old post: bring the publish date forward so the
            // article is treated as new content while the URL keeps its history.
            $data['post_date'] = current_time('mysql');
            $data['post_date_gmt'] = current_time('mysql', true);
        }
    }

    if ($dry) {
        WP_CLI::log(($existing ? 'UPDATE ' : 'CREATE ') . $slug . ' (' . str_word_count(wp_strip_all_tags($content)) . ' words, ' . count($post['faqs']) . ' FAQs)');
        continue;
    }

    $post_id = wp_insert_post($data, true);
    if (is_wp_error($post_id)) {
        WP_CLI::warning("Insert failed for {$slug}: " . $post_id->get_error_message());
        continue;
    }

    wp_set_post_terms($post_id, eta_publish_term_ids($post['categories'], 'category'), 'category', false);
    wp_set_post_terms($post_id, eta_publish_term_ids($post['tags'], 'post_tag'), 'post_tag', false);

    update_post_meta($post_id, 'rank_math_title', $post['seo_title']);
    update_post_meta($post_id, 'rank_math_description', $post['description']);
    update_post_meta($post_id, 'rank_math_focus_keyword', $post['focus_keyword']);
    update_post_meta($post_id, 'eta_author', $post['author']);
    update_post_meta($post_id, 'eta_post_faqs', wp_json_encode($post['faqs'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    $image_id = eta_publish_sideload_image($dir . '/images/' . $post['image'], $post_id, $post['image_alt'], false);
    if ($image_id > 0) {
        set_post_thumbnail($post_id, $image_id);
    }

    $done[] = $post_id;
    WP_CLI::success(($existing ? 'Updated ' : 'Created ') . $slug . ' as post ' . $post_id . ' ' . get_permalink($post_id));
}

if (!$dry && $done) {
    if (function_exists('eta_ai_visibility_build_full_corpus')) {
        $sections = eta_ai_visibility_build_full_corpus();
        WP_CLI::log('llms-full corpus rebuilt: ' . var_export($sections, true) . ' sections');
    }
    if (function_exists('rank_math')) {
        WP_CLI::log('Rank Math present; sitemap will refresh on next request.');
    }
    WP_CLI::log('Purge LiteSpeed and Cloudflare caches after this run.');
}
