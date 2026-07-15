<?php
/**
 * Modern page template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main eta-main">
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $slug = get_post_field('post_name', get_the_ID());
        if ($slug === 'contact-us-envi-tech-al') {
            eta_modern_render_contact_page();
            continue;
        }

        if ($slug === 'aboutus') {
            eta_modern_render_about_page();
            continue;
        }

        if ($slug === 'downloads') {
            eta_modern_render_downloads_page();
            continue;
        }

        if ($slug === 'sindh-environmental-quality-standards-seqs') {
            eta_modern_render_seqs_hub_page();
            continue;
        }

        if ($slug === 'report-verification-portal') {
            eta_modern_render_report_verification_page();
            continue;
        }

        if ($slug === 'blognewsupdates' || $slug === 'newsupdates') {
            eta_modern_render_knowledge_hub_page();
            continue;
        }

        if ($slug === 'careers-at-envi-tech-al') {
            eta_modern_render_careers_page();
            continue;
        }

        if (in_array($slug, ['frequently-asked-questions-water-testing-in-karachi', 'environmental-testing-faqs-pakistan', 'ourclients', 'lahore-environmental-lab', 'karachi-environmental-lab', 'certificates-approvals', 'accreditations-certifications', 'wastewater-testing-services', 'drinking-water-testing-lab', 'ambient-air-monitoring-services', 'noise-monitoring-dosimetry', 'industrial-hygiene-monitoring', 'soil-hazardous-waste-testing', 'emp-emr-iee-eia-compliance', 'maritime-environmental-testing', 'tdap-registered-lab-in-karachi-pakistan'], true)) {
            eta_modern_render_indexed_utility_page($slug);
            continue;
        }

        $lead = eta_modern_plain_excerpt(get_the_ID(), 28);
        eta_modern_page_hero(eta_modern_display_title(get_the_ID()), $lead);
        ?>

        <section class="eta-band">
            <div class="eta-shell eta-page-layout">
                <article class="eta-content">
                    <?php
                    $form = eta_modern_contact_form_shortcode(get_the_content(null, false, get_the_ID()));

                    if ($slug === 'careers-at-envi-tech-al' && $form) {
                        echo eta_modern_clean_content(get_the_content(null, false, get_the_ID()));
                        echo '<div class="eta-form-panel">' . do_shortcode($form) . '</div>';
                    } else {
                        echo eta_modern_clean_content(get_the_content(null, false, get_the_ID()));
                    }
                    ?>
                </article>

                <?php if (!in_array($slug, ['blognewsupdates', 'newsupdates', 'downloads'], true)) : ?>
                    <aside class="eta-side-panel">
                        <h2><?php esc_html_e('Services', 'envi-tech-al-modern'); ?></h2>
                        <ul>
                            <?php foreach (eta_modern_service_posts(8) as $service) : ?>
                                <li><a href="<?php echo esc_url(get_permalink($service)); ?>"><?php echo esc_html(eta_modern_display_title($service)); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </aside>
                <?php endif; ?>
            </div>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
