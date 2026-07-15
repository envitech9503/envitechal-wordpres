<?php
/**
 * 404 template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main eta-main">
    <?php eta_modern_page_hero('Page not found', 'The page may have moved, or the address may be mistyped.'); ?>
    <section class="eta-band">
        <div class="eta-shell eta-content">
            <?php get_search_form(); ?>
            <div class="eta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Go home', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('View services', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
