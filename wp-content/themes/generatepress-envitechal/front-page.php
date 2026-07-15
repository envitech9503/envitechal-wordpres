<?php
/**
 * Modern front page template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (!function_exists('eta_modern_home_image')) {
    function eta_modern_home_image($url, $class, $alt, $size = 'large', $loading = 'lazy', $sizes = '100vw', $extra = [])
    {
        $attrs = array_merge([
            'class' => $class,
            'alt' => $alt,
            'loading' => $loading,
            'decoding' => 'async',
            'sizes' => $sizes,
        ], $extra);

        if ($loading === 'eager') {
            $attrs['fetchpriority'] = 'high';
        }

        $attachment_id = attachment_url_to_postid($url);
        if ($attachment_id) {
            echo wp_get_attachment_image($attachment_id, $size, false, $attrs);
            return;
        }

        printf(
            '<img class="%1$s" src="%2$s" alt="%3$s" loading="%4$s" decoding="async"%5$s>',
            esc_attr($class),
            esc_url($url),
            esc_attr($alt),
            esc_attr($loading),
            $loading === 'eager' ? ' fetchpriority="high"' : ''
        );
    }
}

$service_tiles = [
    [
        'kicker' => 'Compliance Advisory',
        'title' => 'Environmental Consultancy',
        'text' => 'IEE, EIA, EMP, EMR, audits, SEPA submissions, and regulator-facing environmental documentation.',
        'url' => home_url('/services/environmental-consultancy/'),
        'image' => 'https://envitechal.com/wp-content/uploads/2026/05/environmental-consultancy-1.png',
    ],
    [
        'kicker' => 'Accredited Laboratory',
        'title' => 'Environmental Lab & Analytical Services',
        'text' => 'Defensible laboratory analysis for environmental samples, industrial compliance, and buyer-facing reports.',
        'url' => home_url('/services/analytical-lab-services/'),
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/environmental-testing-lab.png',
    ],
    [
        'kicker' => 'Water & Wastewater',
        'title' => 'Water Testing Lab Services',
        'text' => 'Drinking water, wastewater, process water, RO performance, and discharge compliance testing.',
        'url' => home_url('/services/water-testing-lab-services/'),
        'image' => 'https://envitechal.com/wp-content/uploads/2026/05/water-testing-services-karachi-lahore.png',
    ],
    [
        'kicker' => 'Instrument Accuracy',
        'title' => 'Equipment Calibration',
        'text' => 'Calibration support for field instruments, monitoring equipment, and laboratory measurement confidence.',
        'url' => home_url('/services/equipment-calibration-services/'),
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/calibration-services.png',
    ],
];

$industries = [
    'Textile',
    'Leather',
    'Food & Beverage',
    'Pharma',
    'Hotels',
    'Hospitals',
    'Construction',
    'Oil & Gas',
    'Cement',
    'Commercial Facilities',
];

$credentials = [
    [
        'title' => 'Sindh EPA Certified',
        'subtitle' => 'Environmental testing lab',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/sepa-sindh-logo.png',
    ],
    [
        'title' => 'ISO 9001:2015',
        'subtitle' => 'Quality management systems',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/iso-9001-2015-logo.png',
    ],
    [
        'title' => 'ISO 14001:2015',
        'subtitle' => 'Environmental management systems',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/iso-14001-2015-logo.png',
    ],
    [
        'title' => 'ISO/IEC 17025:2017',
        'subtitle' => 'Laboratory accreditation',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/iso-iec-170252017-accreditation-logo.png',
    ],
    [
        'title' => 'Punjab EPA Certified',
        'subtitle' => 'Environmental testing lab',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/epa-new-logo-1.jpg',
    ],
];
?>

<main id="primary" class="site-main eta-main eta-home-master">
    <section class="eta-home-stage" aria-labelledby="eta-home-title">
        <img
            class="eta-home-bg-img"
            src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/eta-home-hero-900.webp'); ?>"
            srcset="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/eta-home-hero-520.webp'); ?> 520w, <?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/eta-home-hero-900.webp'); ?> 900w, <?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/eta-home-hero-1500.webp'); ?> 1500w"
            sizes="(max-width: 640px) 520px, 1180px"
            alt="<?php esc_attr_e('Environmental laboratory team performing testing and compliance analysis', 'envi-tech-al-modern'); ?>"
            loading="eager"
            fetchpriority="high"
            decoding="async">
        <div class="eta-shell eta-home-stage-grid">
            <div class="eta-home-copy">
                <p class="eta-eyebrow"><?php esc_html_e('Accredited laboratory & environmental consultancy', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-home-title"><?php esc_html_e('EPA-ready environmental testing for teams that need clear, defensible reports.', 'envi-tech-al-modern'); ?></h1>
                <p><?php esc_html_e('Envi Tech AL supports industries, hospitals, hotels, textile units, exporters, commercial facilities, and maritime operators with testing, monitoring, reporting, consultancy, calibration, and compliance guidance across Pakistan.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request a quote', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('View services', 'envi-tech-al-modern'); ?></a>
                </div>
                <div class="eta-home-signal-row" aria-label="<?php esc_attr_e('Priority service signals', 'envi-tech-al-modern'); ?>">
                    <span><?php esc_html_e('Water testing', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Wastewater monitoring', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Air & stack emissions', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('EMP / EMR', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Calibration', 'envi-tech-al-modern'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="eta-home-proof" aria-label="<?php esc_attr_e('Company strengths', 'envi-tech-al-modern'); ?>">
        <div class="eta-shell eta-home-proof-grid">
            <div>
                <strong><?php esc_html_e('ISO/IEC 17025', 'envi-tech-al-modern'); ?></strong>
                <span><?php esc_html_e('Accredited laboratory discipline', 'envi-tech-al-modern'); ?></span>
            </div>
            <div>
                <strong><?php esc_html_e('EPA-ready', 'envi-tech-al-modern'); ?></strong>
                <span><?php esc_html_e('Monitoring and reporting deliverables', 'envi-tech-al-modern'); ?></span>
            </div>
            <div>
                <strong><?php esc_html_e('Field + Lab', 'envi-tech-al-modern'); ?></strong>
                <span><?php esc_html_e('One coordinated compliance workflow', 'envi-tech-al-modern'); ?></span>
            </div>
            <div>
                <strong><?php esc_html_e('Karachi / Lahore', 'envi-tech-al-modern'); ?></strong>
                <span><?php esc_html_e('Responsive industrial coverage', 'envi-tech-al-modern'); ?></span>
            </div>
        </div>
    </section>

    <section class="eta-band eta-home-services">
        <div class="eta-shell">
            <?php eta_modern_section_title('Core services', 'Environmental testing and compliance services built for serious operations', 'A focused portfolio for facilities that need accurate lab results, practical advisory, and documentation that can stand up in front of regulators, buyers, and auditors.'); ?>
            <div class="eta-home-service-grid">
                <?php foreach ($service_tiles as $tile) : ?>
                    <article class="eta-home-service-card">
                        <a href="<?php echo esc_url($tile['url']); ?>" class="eta-home-service-media" aria-label="<?php echo esc_attr($tile['title']); ?>">
                            <?php eta_modern_home_image($tile['image'], '', $tile['title'], 'medium_large', 'lazy', '(max-width: 640px) 336px, 272px'); ?>
                        </a>
                        <div class="eta-home-service-body">
                            <p class="eta-mini-kicker"><?php echo esc_html($tile['kicker']); ?></p>
                            <h3><a href="<?php echo esc_url($tile['url']); ?>"><?php echo esc_html($tile['title']); ?></a></h3>
                            <p><?php echo esc_html($tile['text']); ?></p>
                            <a class="eta-text-link" href="<?php echo esc_url($tile['url']); ?>"><?php esc_html_e('Explore service', 'envi-tech-al-modern'); ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-home-command">
        <div class="eta-shell eta-home-command-grid">
            <div>
                <?php eta_modern_section_title('Compliance workflow', 'From sample to submission without losing the thread', 'From scope selection to sampling, analysis, reporting, and compliance support, Envi Tech AL gives clients a controlled path from requirement to usable report.'); ?>
                <div class="eta-home-outcome-list">
                    <span><?php esc_html_e('Scope matched to sample, permit, buyer, or audit need', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Field monitoring, lab testing, and technical review', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Reports, advisory context, and verification support', 'envi-tech-al-modern'); ?></span>
                </div>
            </div>
            <ol class="eta-home-timeline">
                <li>
                    <span><?php esc_html_e('01', 'envi-tech-al-modern'); ?></span>
                    <strong><?php esc_html_e('Define the compliance objective', 'envi-tech-al-modern'); ?></strong>
                    <p><?php esc_html_e('Clarify whether the report is for EPA submission, buyer compliance, plant troubleshooting, audit readiness, or internal risk control.', 'envi-tech-al-modern'); ?></p>
                </li>
                <li>
                    <span><?php esc_html_e('02', 'envi-tech-al-modern'); ?></span>
                    <strong><?php esc_html_e('Collect, test, monitor, and document', 'envi-tech-al-modern'); ?></strong>
                    <p><?php esc_html_e('Coordinate sampling, environmental monitoring, analytical testing, calibration, and quality documentation.', 'envi-tech-al-modern'); ?></p>
                </li>
                <li>
                    <span><?php esc_html_e('03', 'envi-tech-al-modern'); ?></span>
                    <strong><?php esc_html_e('Deliver report-ready clarity', 'envi-tech-al-modern'); ?></strong>
                    <p><?php esc_html_e('Provide usable findings, practical next steps, and verification pathways for customers and stakeholders.', 'envi-tech-al-modern'); ?></p>
                </li>
            </ol>
        </div>
    </section>

    <section class="eta-band eta-home-industries">
        <div class="eta-shell eta-home-industries-grid">
            <div>
                <?php eta_modern_section_title('Industry coverage', 'Trusted across compliance-critical sectors', 'Environmental support for operations where testing quality, response time, and documentation clarity directly affect approvals, shipments, safety, and reputation.'); ?>
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Discuss your facility', 'envi-tech-al-modern'); ?></a>
            </div>
            <div class="eta-home-sector-grid" aria-label="<?php esc_attr_e('Industries served', 'envi-tech-al-modern'); ?>">
                <?php foreach ($industries as $industry) : ?>
                    <span><?php echo esc_html($industry); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-home-maritime">
        <div class="eta-shell eta-home-maritime-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Maritime & vessel compliance', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Ballast water testing support for vessels that need fast, defensible results.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Support for vessels and ships calling at Karachi port, with ISO/IEC 17025:2017 laboratory discipline, sampling coordination, and compliance-focused reporting for audit and inspection readiness.', 'envi-tech-al-modern'); ?></p>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/services/ballast-water-testing-services/')); ?>"><?php esc_html_e('Explore ballast water testing', 'envi-tech-al-modern'); ?></a>
            </div>
            <div class="eta-home-maritime-points">
                <span><?php esc_html_e('Pathogen detection and invasive species screening', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('Port-call planning support in Karachi', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('Fast reporting for marine compliance teams', 'envi-tech-al-modern'); ?></span>
            </div>
        </div>
    </section>

    <section class="eta-band eta-home-credentials">
        <div class="eta-shell">
            <?php eta_modern_section_title('Trusted credentials', 'Certifications, approvals, and quality systems', 'Visible proof points for customers who need confidence before they hand over a compliance-critical requirement.'); ?>
            <div class="eta-home-credential-grid">
                <?php foreach ($credentials as $credential) : ?>
                    <article class="eta-home-credential">
                        <?php eta_modern_home_image($credential['image'], '', $credential['title'], 'medium', 'lazy', '(max-width: 640px) 300px, 180px'); ?>
                        <h3><?php echo esc_html($credential['title']); ?></h3>
                        <p><?php echo esc_html($credential['subtitle']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="eta-home-center-action">
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/accreditations-certifications/')); ?>"><?php esc_html_e('Review certifications and approvals', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/environmental-testing-faqs-pakistan/')); ?>"><?php esc_html_e('Read environmental testing FAQs', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Verify a report', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>

    <section class="eta-band eta-band-light">
        <div class="eta-shell eta-home-why-grid">
            <div>
                <?php eta_modern_section_title('Why Envi Tech AL', 'Laboratory capability, field execution, and regulatory thinking in one team', 'That integration helps customers move faster on approvals, corrective actions, buyer requirements, and compliance-critical reporting.'); ?>
            </div>
            <div class="eta-home-why-list">
                <span><?php esc_html_e('ISO/IEC 17025 aligned laboratory discipline', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('EPA-ready reports and monitoring deliverables', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('Field, lab, and advisory support in one workflow', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('Responsive coverage in Karachi and Lahore', 'envi-tech-al-modern'); ?></span>
            </div>
        </div>
    </section>

    <section class="eta-band eta-home-client-feedback" aria-labelledby="eta-home-client-feedback-title">
        <div class="eta-shell eta-home-feedback-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Client feedback themes', 'envi-tech-al-modern'); ?></p>
                <h2 id="eta-home-client-feedback-title"><?php esc_html_e('Why clients continue to choose Envi Tech AL', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Recent client feedback points to the same practical strengths: responsive coordination, clear environmental testing reports, and compliance-focused guidance when the result matters.', 'envi-tech-al-modern'); ?></p>
                <div class="eta-home-feedback-actions">
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Discuss your requirement', 'envi-tech-al-modern'); ?></a>
                    <a class="eta-text-link" href="<?php echo esc_url('https://maps.app.goo.gl/kbcjQDdRXYXadgve7'); ?>" target="_blank" rel="noopener"><?php esc_html_e('Read Google reviews', 'envi-tech-al-modern'); ?></a>
                </div>
            </div>
            <div class="eta-home-feedback-card-grid" aria-label="<?php esc_attr_e('Client feedback themes', 'envi-tech-al-modern'); ?>">
                <article class="eta-home-feedback-card">
                    <span><?php esc_html_e('01', 'envi-tech-al-modern'); ?></span>
                    <h3><?php esc_html_e('Responsive support', 'envi-tech-al-modern'); ?></h3>
                    <p><?php esc_html_e('Clients value quick coordination for sampling, quotations, report follow-up, and urgent compliance timelines.', 'envi-tech-al-modern'); ?></p>
                </article>
                <article class="eta-home-feedback-card">
                    <span><?php esc_html_e('02', 'envi-tech-al-modern'); ?></span>
                    <h3><?php esc_html_e('Clear reporting', 'envi-tech-al-modern'); ?></h3>
                    <p><?php esc_html_e('Testing work is strongest when reports are understandable, traceable, and useful for management, auditors, buyers, and regulators.', 'envi-tech-al-modern'); ?></p>
                </article>
                <article class="eta-home-feedback-card">
                    <span><?php esc_html_e('03', 'envi-tech-al-modern'); ?></span>
                    <h3><?php esc_html_e('Compliance guidance', 'envi-tech-al-modern'); ?></h3>
                    <p><?php esc_html_e('The team helps connect lab results, monitoring, consultancy, and documentation paths to the real decision a client needs to support.', 'envi-tech-al-modern'); ?></p>
                </article>
            </div>
        </div>
    </section>

    <section class="eta-band eta-home-insights">
        <div class="eta-shell">
            <?php eta_modern_section_title('Latest insights', 'Recent compliance, testing, and environmental intelligence', 'Fresh guidance from Envi Tech AL covering environmental testing, calibration, consultancy, and regulated operations in Pakistan.'); ?>
            <div class="eta-grid eta-grid-3">
                <article class="eta-card eta-post-card">
                    <div class="eta-card-body">
                        <h3><a href="<?php echo esc_url(home_url('/sindh-environmental-quality-standards-seqs/')); ?>"><?php esc_html_e('Sindh Environmental Quality Standards guide', 'envi-tech-al-modern'); ?></a></h3>
                        <p><?php esc_html_e('A practical SEQS limits and compliance guide for Sindh facilities preparing testing, monitoring, and EMR submissions.', 'envi-tech-al-modern'); ?></p>
                        <a class="eta-text-link" href="<?php echo esc_url(home_url('/sindh-environmental-quality-standards-seqs/')); ?>"><?php esc_html_e('Sindh Environmental Quality Standards guide', 'envi-tech-al-modern'); ?></a>
                    </div>
                </article>
                <?php foreach (eta_modern_latest_posts(3) as $post_item) : ?>
                    <?php eta_modern_card_link($post_item, 'eta-post-card'); ?>
                <?php endforeach; ?>
            </div>
            <div class="eta-home-center-action">
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/blognewsupdates/')); ?>"><?php esc_html_e('View all updates', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>

    <section class="eta-home-final-cta">
        <div class="eta-shell eta-home-final-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Need EPA / buyer / audit-ready environmental testing?', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Send the requirement before the deadline becomes urgent.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Talk to Envi Tech AL for accredited testing, monitoring, reporting, calibration, and consultancy support aligned with your operational timelines.', 'envi-tech-al-modern'); ?></p>
            </div>
            <div class="eta-home-final-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request a quotation', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
