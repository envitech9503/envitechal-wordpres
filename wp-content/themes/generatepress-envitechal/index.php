<?php
/**
 * Blog and archive fallback template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main eta-main">
    <?php eta_modern_render_knowledge_hub_page(); ?>
</main>

<?php
get_footer();
