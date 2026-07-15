<?php
/**
 * Premium single service template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main eta-main eta-service-single-master">
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $slug = get_post_field('post_name', get_the_ID());
        $profile = eta_modern_service_profile($slug);
        $faqs = eta_modern_service_faqs($slug);
        $parameters = eta_modern_service_parameter_groups($slug);
        $process = eta_modern_service_process_steps($slug);

        if ($slug === 'water-testing-lab-services') {
            eta_modern_render_water_testing_flagship_page();
            continue;
        }
        ?>
        <section class="eta-service-hero" aria-labelledby="eta-service-title">
            <img class="eta-service-hero-img" src="<?php echo esc_url($profile['image']); ?>" alt="" aria-hidden="true" loading="eager" decoding="async" fetchpriority="high">
            <div class="eta-shell eta-service-hero-grid">
                <div>
                    <p class="eta-eyebrow"><?php echo esc_html($profile['category']); ?></p>
                    <h1 id="eta-service-title"><?php echo esc_html($profile['hero']); ?></h1>
                    <p><?php echo esc_html($profile['lead']); ?></p>
                    <div class="eta-actions">
                        <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request Testing Quote', 'envi-tech-al-modern'); ?></a>
                        <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('Back to services', 'envi-tech-al-modern'); ?></a>
                    </div>
                </div>
                <aside class="eta-service-hero-panel">
                    <span><?php esc_html_e('Service fit', 'envi-tech-al-modern'); ?></span>
                    <?php foreach ($profile['best_for'] as $item) : ?>
                        <strong><?php echo esc_html($item); ?></strong>
                    <?php endforeach; ?>
                </aside>
            </div>
        </section>

        <section class="eta-service-proof" aria-label="<?php esc_attr_e('Service proof points', 'envi-tech-al-modern'); ?>">
            <div class="eta-shell eta-service-proof-grid">
                <?php foreach ($profile['proof'] as $proof) : ?>
                    <span><?php echo esc_html($proof); ?></span>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="eta-band eta-service-detail-band">
            <div class="eta-shell eta-service-detail-grid">
                <article class="eta-service-main-card">
                    <?php eta_modern_section_title('Service overview', 'How this service supports the final decision', 'A clear service page should tell you what the work supports, who it helps, and how to start with the right technical scope.'); ?>
                    <div class="eta-service-outcome-grid">
                        <?php foreach ($profile['outcomes'] as $outcome) : ?>
                            <span><?php echo esc_html($outcome); ?></span>
                        <?php endforeach; ?>
                    </div>

                    <?php
                    eta_modern_render_ai_summary_block(
                        'At a glance',
                        eta_modern_service_profile_value($slug, 'answer_summary', $profile['lead']),
                        array_merge($profile['outcomes'], $profile['best_for']),
                        'h3'
                    );
                    ?>

                    <section class="eta-service-opening eta-service-answer-summary">
                        <h3><?php esc_html_e('Short answer', 'envi-tech-al-modern'); ?></h3>
                        <p><?php echo esc_html(eta_modern_service_profile_value($slug, 'answer_summary', $profile['lead'])); ?></p>
                        <?php if ($slug === 'environmental-consultancy') : ?>
                            <p><a class="eta-text-link" href="<?php echo esc_url(home_url('/sindh-environmental-quality-standards-seqs/')); ?>"><?php esc_html_e('Sindh Environmental Quality Standards (SEQS)', 'envi-tech-al-modern'); ?></a></p>
                        <?php endif; ?>
                    </section>

                    <?php
                    $trust_note = eta_modern_service_profile_value($slug, 'trust_note', '');
                    $trust_links = eta_modern_service_profile_value($slug, 'trust_links', []);
                    ?>
                    <?php if ($trust_note || $trust_links) : ?>
                        <section class="eta-service-opening eta-service-opening-muted">
                            <h3><?php esc_html_e('Evidence and trust path', 'envi-tech-al-modern'); ?></h3>
                            <?php if ($trust_note) : ?>
                                <p><?php echo esc_html($trust_note); ?></p>
                            <?php endif; ?>
                            <?php if ($trust_links) : ?>
                                <p>
                                    <?php
                                    $trust_link_markup = [];
                                    foreach ($trust_links as $url => $label) {
                                        $trust_link_markup[] = '<a class="eta-text-link" href="' . esc_url(home_url($url)) . '">' . esc_html($label) . '</a>';
                                    }
                                    echo implode(' <span aria-hidden="true">|</span> ', $trust_link_markup); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    ?>
                                </p>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>

                    <?php if (eta_modern_service_profile_value($slug, 'suppress_legacy_content', false)) : ?>
                        <section class="eta-service-opening">
                            <h3><?php esc_html_e('Controlled service scope', 'envi-tech-al-modern'); ?></h3>
                            <p><?php echo esc_html($profile['lead']); ?></p>
                        </section>
                    <?php elseif ($slug === 'analytical-lab-services') : ?>
                        <?php eta_modern_render_analytical_lab_service_content(); ?>
                    <?php elseif ($slug === 'water-testing-lab-services') : ?>
                        <?php eta_modern_render_water_testing_page(); ?>
                    <?php else : ?>
                        <div class="eta-content eta-service-clean-content">
                            <?php echo eta_modern_clean_content(get_the_content(null, false, get_the_ID())); ?>
                        </div>
                    <?php endif; ?>

                    <section class="eta-service-opening eta-service-opening-muted">
                        <h3><?php esc_html_e('Who needs this service', 'envi-tech-al-modern'); ?></h3>
                        <div class="eta-check-grid">
                            <?php foreach (eta_modern_service_profile_value($slug, 'who_needs', $profile['best_for']) as $item) : ?>
                                <span><?php echo esc_html($item); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="eta-service-opening">
                        <h3><?php esc_html_e('What is covered', 'envi-tech-al-modern'); ?></h3>
                        <div class="eta-check-grid">
                            <?php foreach (eta_modern_service_profile_value($slug, 'covered', $profile['outcomes']) as $item) : ?>
                                <span><?php echo esc_html($item); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <?php if ($parameters) : ?>
                        <section class="eta-parameter-section">
                            <h3><?php esc_html_e('Typical parameters and scope areas', 'envi-tech-al-modern'); ?></h3>
                            <p><?php esc_html_e('Final scope depends on the sample type, regulatory or buyer requirement, site condition, and report purpose. These groups make the service easier to review on mobile and during quotation discussions.', 'envi-tech-al-modern'); ?></p>
                            <div class="eta-parameter-grid">
                                <?php foreach ($parameters as $group => $items) : ?>
                                    <article class="eta-parameter-card">
                                        <h3><?php echo esc_html($group); ?></h3>
                                        <ul>
                                            <?php foreach ($items as $item) : ?>
                                                <li><?php echo esc_html($item); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <section class="eta-service-opening eta-service-opening-muted">
                        <h3><?php esc_html_e('Why it matters', 'envi-tech-al-modern'); ?></h3>
                        <p><?php echo esc_html(eta_modern_service_profile_value($slug, 'why_matters', 'Correct scope, controlled testing, and clear reporting help customers make defensible decisions for compliance, audits, procurement, safety, operations, and follow-up actions.')); ?></p>
                    </section>

                    <section class="eta-service-opening">
                        <h3><?php esc_html_e('Report and documentation use cases', 'envi-tech-al-modern'); ?></h3>
                        <div class="eta-check-grid">
                            <?php foreach (eta_modern_service_profile_value($slug, 'documentation_uses', ['EPA compliance support', 'Buyer or customer audit', 'Internal monitoring', 'Corrective action follow-up']) as $item) : ?>
                                <span><?php echo esc_html($item); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </article>

                <aside class="eta-service-command-panel">
                    <h2><?php esc_html_e('Plan the request', 'envi-tech-al-modern'); ?></h2>
                    <p><?php esc_html_e('Send the sample type, site city, purpose of report, deadline, and any previous report reference so the team can guide the correct scope.', 'envi-tech-al-modern'); ?></p>
                    <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request Testing Quote', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp team', 'envi-tech-al-modern'); ?></a>
                    <div class="eta-service-mini-list">
                        <span><?php esc_html_e('Karachi', 'envi-tech-al-modern'); ?></span>
                        <span><?php esc_html_e('Lahore', 'envi-tech-al-modern'); ?></span>
                        <span><?php esc_html_e('Field + lab support', 'envi-tech-al-modern'); ?></span>
                    </div>
                    <div class="eta-service-mini-list">
                        <a href="<?php echo esc_url(home_url('/accreditations-certifications/')); ?>"><?php esc_html_e('Certifications & approvals', 'envi-tech-al-modern'); ?></a>
                        <a href="<?php echo esc_url(home_url('/downloads/')); ?>"><?php esc_html_e('Downloads library', 'envi-tech-al-modern'); ?></a>
                        <a href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Report verification', 'envi-tech-al-modern'); ?></a>
                        <a href="<?php echo esc_url(home_url('/environmental-testing-faqs-pakistan/')); ?>"><?php esc_html_e('Environmental testing FAQ', 'envi-tech-al-modern'); ?></a>
                        <a href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('Services hub', 'envi-tech-al-modern'); ?></a>
                    </div>
                </aside>
            </div>
        </section>

        <section class="eta-service-method">
            <div class="eta-shell eta-service-method-grid">
                <div>
                    <?php eta_modern_section_title('Method', 'A controlled path from question to usable report', 'Each service begins by clarifying the decision the customer needs to make, then matching technical work to that decision.'); ?>
                </div>
                <ol class="eta-service-method-list">
                    <li>
                        <span><?php esc_html_e('01', 'envi-tech-al-modern'); ?></span>
                        <strong><?php esc_html_e('Scope', 'envi-tech-al-modern'); ?></strong>
                        <p><?php echo esc_html($process[0][1]); ?></p>
                    </li>
                    <li>
                        <span><?php esc_html_e('02', 'envi-tech-al-modern'); ?></span>
                        <strong><?php esc_html_e('Execute', 'envi-tech-al-modern'); ?></strong>
                        <p><?php echo esc_html($process[1][1]); ?></p>
                    </li>
                    <li>
                        <span><?php esc_html_e('03', 'envi-tech-al-modern'); ?></span>
                        <strong><?php esc_html_e('Report', 'envi-tech-al-modern'); ?></strong>
                        <p><?php echo esc_html($process[2][1]); ?></p>
                    </li>
                </ol>
            </div>
        </section>

        <?php if ($faqs) : ?>
            <section class="eta-band eta-service-faq">
                <div class="eta-shell">
                    <?php eta_modern_section_title('Service FAQ', 'Answers before you request a quotation', 'Practical questions customers ask before selecting scope, sampling, reporting, or compliance support.'); ?>
                    <div class="eta-service-faq-grid">
                        <?php foreach ($faqs as $faq) : ?>
                            <article class="eta-service-faq-card">
                                <h3><?php echo esc_html($faq[0]); ?></h3>
                                <p><?php echo esc_html($faq[1]); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="eta-band eta-service-related">
            <div class="eta-shell">
                <?php eta_modern_section_title('Related services', 'Build a complete compliance path'); ?>
                <div class="eta-service-related-grid">
                    <?php
                    $related = eta_modern_related_service_items($slug, get_the_ID());
                    foreach ($related as $service) :
                        $related_profile = eta_modern_service_profile(get_post_field('post_name', $service));
                        ?>
                        <article class="eta-service-related-card">
                            <p class="eta-mini-kicker"><?php echo esc_html($related_profile['category']); ?></p>
                            <h3><a href="<?php echo esc_url(get_permalink($service)); ?>"><?php echo esc_html($slug === 'analytical-lab-services' && get_post_field('post_name', $service) === 'water-testing-lab-services' ? 'water testing lab' : eta_modern_display_title($service)); ?></a></h3>
                            <p><?php echo esc_html($related_profile['lead']); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="eta-services-final-cta">
            <div class="eta-shell eta-services-final-grid">
                <div>
                    <p class="eta-eyebrow"><?php esc_html_e('Ready to scope this service?', 'envi-tech-al-modern'); ?></p>
                    <h2><?php esc_html_e('Send your requirement and let the technical team guide the next step.', 'envi-tech-al-modern'); ?></h2>
                </div>
                <div class="eta-services-final-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request this service', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
