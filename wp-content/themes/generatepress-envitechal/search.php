<?php
/**
 * Search template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main eta-main">
    <?php eta_modern_page_hero(sprintf(__('Search: %s', 'envi-tech-al-modern'), get_search_query()), 'Search Envi Tech AL services, pages, and articles.'); ?>
    <section class="eta-band">
        <div class="eta-shell">
            <?php get_search_form(); ?>
            <?php if (have_posts()) : ?>
                <div class="eta-grid eta-grid-3 eta-search-results">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php eta_modern_card_link(get_post(), 'eta-post-card'); ?>
                    <?php endwhile; ?>
                </div>
                <div class="eta-pagination"><?php the_posts_pagination(); ?></div>
            <?php else : ?>
                <article class="eta-content"><p><?php esc_html_e('No matching results found.', 'envi-tech-al-modern'); ?></p></article>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
