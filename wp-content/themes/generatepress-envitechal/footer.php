<?php
/**
 * Branded site footer.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

    </div><!-- #content -->

<footer class="eta-site-footer" role="contentinfo">
    <div class="eta-footer-cta">
        <div class="eta-shell eta-footer-cta-inner">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Need reliable environmental testing or compliance support?', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Send the requirement today. The lab team will guide the next step.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-footer-cta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request a quotation', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </div>

    <div class="eta-shell eta-footer-main">
        <section class="eta-footer-brand" aria-label="<?php esc_attr_e('Company summary', 'envi-tech-al-modern'); ?>">
            <a class="eta-footer-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Envi Tech AL homepage', 'envi-tech-al-modern'); ?>">
                <?php
                $custom_logo_id = get_theme_mod('custom_logo');
                if ($custom_logo_id) {
                    echo wp_get_attachment_image($custom_logo_id, 'medium', false, ['class' => 'eta-footer-logo-img']);
                } else {
                    esc_html_e('Envi Tech AL', 'envi-tech-al-modern');
                }
                ?>
            </a>
            <p><?php esc_html_e('Environmental testing laboratory and consultancy support for industrial, commercial, maritime, healthcare, hospitality, and compliance teams in Pakistan.', 'envi-tech-al-modern'); ?></p>
            <div class="eta-footer-trust" aria-label="<?php esc_attr_e('Accreditations and service strengths', 'envi-tech-al-modern'); ?>">
                <span><?php esc_html_e('ISO/IEC 17025', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('ISO 9001', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('ISO 14001', 'envi-tech-al-modern'); ?></span>
            </div>
        </section>

        <section class="eta-footer-card eta-footer-contact">
            <h3><?php esc_html_e('Contact', 'envi-tech-al-modern'); ?></h3>
            <a href="tel:+923102288801">+92 310 2288801</a>
            <a href="tel:+923152006074">+92 315 2006074</a>
            <a href="tel:+924232296099">+92 42 32296099</a>
            <a href="mailto:info@envitechal.com">info@envitechal.com</a>
        </section>

        <section class="eta-footer-card">
            <h3><?php esc_html_e('Karachi Office', 'envi-tech-al-modern'); ?></h3>
            <p><?php esc_html_e('First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS, Karachi, Sindh 75900, Pakistan', 'envi-tech-al-modern'); ?></p>
        </section>

        <section class="eta-footer-card">
            <h3><?php esc_html_e('Lahore Office', 'envi-tech-al-modern'); ?></h3>
            <p><?php esc_html_e('A-30 & 31, 8th Floor, Madina Heights, Maulana Shaukat Ali Khan Road, Johar Town, Lahore, Punjab, Pakistan', 'envi-tech-al-modern'); ?></p>
        </section>
    </div>

    <div class="eta-shell eta-footer-nav-grid">
        <nav class="eta-footer-links" aria-label="<?php esc_attr_e('Footer service links', 'envi-tech-al-modern'); ?>">
            <h3><?php esc_html_e('Services', 'envi-tech-al-modern'); ?></h3>
            <a href="<?php echo esc_url(home_url('/services/analytical-lab-services/')); ?>"><?php esc_html_e('Analytical Lab Services', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/services/water-testing-lab-services/')); ?>"><?php esc_html_e('Water Testing Services', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/wastewater-testing-services/')); ?>"><?php esc_html_e('Wastewater Testing', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/ambient-air-monitoring-services/')); ?>"><?php esc_html_e('Air Monitoring', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/services/equipment-calibration-services/')); ?>"><?php esc_html_e('Equipment Calibration', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/services/environmental-consultancy/')); ?>"><?php esc_html_e('Environmental Consultancy', 'envi-tech-al-modern'); ?></a>
        </nav>

        <nav class="eta-footer-links" aria-label="<?php esc_attr_e('Footer company links', 'envi-tech-al-modern'); ?>">
            <h3><?php esc_html_e('Company', 'envi-tech-al-modern'); ?></h3>
            <a href="<?php echo esc_url(home_url('/aboutus/')); ?>"><?php esc_html_e('About Envi Tech AL', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/blognewsupdates/')); ?>"><?php esc_html_e('Knowledge Hub', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/downloads/')); ?>"><?php esc_html_e('Downloads', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/careers-at-envi-tech-al/')); ?>"><?php esc_html_e('Careers', 'envi-tech-al-modern'); ?></a>
        </nav>

        <nav class="eta-footer-links" aria-label="<?php esc_attr_e('Footer support links', 'envi-tech-al-modern'); ?>">
            <h3><?php esc_html_e('Support', 'envi-tech-al-modern'); ?></h3>
            <a href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Contact Us', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Verify Report', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/accreditations-certifications/')); ?>"><?php esc_html_e('Certifications & Approvals', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/karachi-environmental-lab/')); ?>"><?php esc_html_e('Karachi Lab', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/lahore-environmental-lab/')); ?>"><?php esc_html_e('Lahore Lab', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url(home_url('/environmental-testing-faqs-pakistan/')); ?>"><?php esc_html_e('Environmental Testing FAQ', 'envi-tech-al-modern'); ?></a>
            <a href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp', 'envi-tech-al-modern'); ?></a>
        </nav>
    </div>

    <div class="eta-footer-bottom">
        <div class="eta-shell">
            <span><?php echo esc_html('Copyright ' . date('Y') . ' Envi Tech AL. All rights reserved.'); ?></span>
            <span><?php esc_html_e('Environmental testing, consultancy, calibration, and compliance support.', 'envi-tech-al-modern'); ?></span>
        </div>
    </div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
