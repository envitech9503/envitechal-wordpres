<?php
/**
 * Premium services hub template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$services = eta_modern_service_posts(-1);
$clusters = [
    '/drinking-water-testing-lab/' => ['Drinking Water Testing', 'Drinking water, RO plant, bore water, building, hospital, hotel, and facility water safety checks.'],
    '/wastewater-testing-services/' => ['Wastewater Testing', 'Industrial discharge, ETP performance, compliance reporting, and buyer-audit evidence.'],
    '/ambient-air-monitoring-services/' => ['Ambient Air Monitoring', 'Air monitoring for industrial sites, construction projects, and compliance-sensitive facilities.'],
    '/noise-monitoring-dosimetry/' => ['Noise Monitoring & Dosimetry', 'Boundary noise, workplace noise, personal exposure, and construction noise monitoring support.'],
    '/industrial-hygiene-monitoring/' => ['Industrial Hygiene Monitoring', 'Workplace exposure, air, dust, heat stress, and occupational environment review.'],
    '/soil-hazardous-waste-testing/' => ['Soil & Hazardous Waste Testing', 'Soil, sludge, waste, site review, due diligence, and contamination-related testing support.'],
    '/emp-emr-iee-eia-compliance/' => ['EMP / EMR / IEE / EIA Support', 'Environmental documentation and SEPA/Punjab EPA compliance support for projects and facilities.'],
    '/maritime-environmental-testing/' => ['Maritime Environmental Testing', 'Ballast water and port-related environmental testing support for vessels and agents.'],
];
?>

<main id="primary" class="site-main eta-main eta-services-master">
    <section class="eta-services-hero" aria-labelledby="eta-services-title">
        <div class="eta-shell eta-services-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Environmental testing services', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-services-title"><?php esc_html_e('Choose the right technical path before the deadline gets expensive.', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Envi Tech AL brings laboratory testing, field monitoring, calibration, regulatory consultancy, certification advisory, and report-ready documentation into one practical service ecosystem.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request service guidance', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Verify a report', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <aside class="eta-services-hero-panel">
                <span><?php esc_html_e('Built for', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('EPA submissions, buyer compliance, audits, plant diagnostics, and safety decisions.', 'envi-tech-al-modern'); ?></strong>
            </aside>
        </div>
    </section>

    <section class="eta-services-proof" aria-label="<?php esc_attr_e('Service strengths', 'envi-tech-al-modern'); ?>">
        <div class="eta-shell eta-services-proof-grid">
            <div>
                <strong><?php esc_html_e('Testing', 'envi-tech-al-modern'); ?></strong>
                <span><?php esc_html_e('Water, wastewater, air, emissions, and environmental samples.', 'envi-tech-al-modern'); ?></span>
            </div>
            <div>
                <strong><?php esc_html_e('Consultancy', 'envi-tech-al-modern'); ?></strong>
                <span><?php esc_html_e('IEE, EIA, EMP, EMR, SEPA support, and corrective actions.', 'envi-tech-al-modern'); ?></span>
            </div>
            <div>
                <strong><?php esc_html_e('Calibration', 'envi-tech-al-modern'); ?></strong>
                <span><?php esc_html_e('Traceability and measurement confidence for audit-ready operations.', 'envi-tech-al-modern'); ?></span>
            </div>
            <div>
                <strong><?php esc_html_e('Reporting', 'envi-tech-al-modern'); ?></strong>
                <span><?php esc_html_e('Clear documentation for customers, regulators, buyers, and internal teams.', 'envi-tech-al-modern'); ?></span>
            </div>
        </div>
    </section>

    <section class="eta-band eta-services-directory">
        <div class="eta-shell">
            <?php eta_modern_section_title('Service directory', 'A premium service portfolio organized by business outcome', 'Each service page is designed to help you understand the scope, the decision it supports, and the fastest path to a useful report or advisory outcome.'); ?>
            <div class="eta-services-card-grid">
                <?php foreach ($services as $service) : ?>
                    <?php
                    $slug = get_post_field('post_name', $service);
                    $profile = eta_modern_service_profile($slug);
                    ?>
                    <article class="eta-services-card">
                        <a class="eta-services-card-media" href="<?php echo esc_url(get_permalink($service)); ?>" aria-label="<?php echo esc_attr(eta_modern_display_title($service)); ?>">
                            <img src="<?php echo esc_url($profile['image']); ?>" alt="<?php echo esc_attr(eta_modern_display_title($service)); ?>" loading="lazy">
                        </a>
                        <div class="eta-services-card-body">
                            <p class="eta-mini-kicker"><?php echo esc_html($profile['category']); ?></p>
                            <h2><a href="<?php echo esc_url(get_permalink($service)); ?>"><?php echo esc_html(eta_modern_display_title($service)); ?></a></h2>
                            <p><?php echo esc_html($profile['lead']); ?></p>
                            <div class="eta-services-outcome-row">
                                <?php foreach (array_slice($profile['outcomes'], 0, 3) as $outcome) : ?>
                                    <span><?php echo esc_html($outcome); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <a class="eta-text-link" href="<?php echo esc_url(get_permalink($service)); ?>"><?php esc_html_e('Explore service', 'envi-tech-al-modern'); ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-band eta-services-directory eta-services-clusters">
        <div class="eta-shell">
            <?php eta_modern_section_title('High-intent service paths', 'Focused pages for the exact testing or compliance outcome you need', 'These commercial landing pages connect customers and search engines to the most common environmental testing, monitoring, and documentation requests.'); ?>
            <div class="eta-services-card-grid">
                <?php foreach ($clusters as $url => $cluster) : ?>
                    <article class="eta-services-card eta-services-cluster-card">
                        <div class="eta-services-card-body">
                            <p class="eta-mini-kicker"><?php esc_html_e('Focused service page', 'envi-tech-al-modern'); ?></p>
                            <h2><a href="<?php echo esc_url(home_url($url)); ?>"><?php echo esc_html($cluster[0]); ?></a></h2>
                            <p><?php echo esc_html($cluster[1]); ?></p>
                            <a class="eta-text-link" href="<?php echo esc_url(home_url($url)); ?>"><?php esc_html_e('Open service path', 'envi-tech-al-modern'); ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-services-workflow">
        <div class="eta-shell eta-services-workflow-grid">
            <div>
                <?php eta_modern_section_title('Decision workflow', 'Not sure which service you need?', 'Start with the business decision. The lab team can help translate your situation into the right test parameters, monitoring plan, advisory scope, or report format.'); ?>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Ask for scope guidance', 'envi-tech-al-modern'); ?></a>
            </div>
            <ol class="eta-services-workflow-list">
                <li>
                    <span><?php esc_html_e('01', 'envi-tech-al-modern'); ?></span>
                    <strong><?php esc_html_e('Define the purpose', 'envi-tech-al-modern'); ?></strong>
                    <p><?php esc_html_e('EPA submission, buyer requirement, internal audit, safety issue, vessel compliance, plant troubleshooting, or certification preparation.', 'envi-tech-al-modern'); ?></p>
                </li>
                <li>
                    <span><?php esc_html_e('02', 'envi-tech-al-modern'); ?></span>
                    <strong><?php esc_html_e('Match the scope', 'envi-tech-al-modern'); ?></strong>
                    <p><?php esc_html_e('Confirm sample category, site location, deadlines, parameters, field visit needs, and report expectations.', 'envi-tech-al-modern'); ?></p>
                </li>
                <li>
                    <span><?php esc_html_e('03', 'envi-tech-al-modern'); ?></span>
                    <strong><?php esc_html_e('Execute and document', 'envi-tech-al-modern'); ?></strong>
                    <p><?php esc_html_e('Testing, monitoring, calibration, or advisory work is completed with controlled documentation and technical review.', 'envi-tech-al-modern'); ?></p>
                </li>
            </ol>
        </div>
    </section>

    <section class="eta-services-final-cta">
        <div class="eta-shell eta-services-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Need a quote or compliance direction?', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Share your sample, site, deadline, and report purpose. We will guide the next step.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-services-final-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Start a service request', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
