<?php
/**
 * Single post template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main eta-main">
    <?php eta_modern_render_single_post_page(); ?>
</main>

<?php
get_footer();
