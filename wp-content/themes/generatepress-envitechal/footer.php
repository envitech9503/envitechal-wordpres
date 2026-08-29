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
            <div class="eta-footer-trust" aria-label="<?php esc_attr_e('Verified credentials and service strengths', 'envi-tech-al-modern'); ?>">
                <span><?php esc_html_e('PNAC LAB-285 Karachi | LAB-347 Lahore', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('ISO 9001', 'envi-tech-al-modern'); ?></span>
                <span><?php esc_html_e('ISO 14001', 'envi-tech-al-modern'); ?></span>
            </div>
            <div class="eta-footer-social" aria-label="<?php esc_attr_e('Envi Tech AL on social media', 'envi-tech-al-modern'); ?>">
                <a href="https://www.linkedin.com/company/envitech-al" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Envi Tech AL on LinkedIn', 'envi-tech-al-modern'); ?>">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" focusable="false"><circle cx="4.6" cy="4.9" r="2.1"/><rect x="2.8" y="9.3" width="3.6" height="11.7"/><path d="M9.4 9.3h3.5v1.7c.6-1 1.9-2 3.8-2 3 0 4.6 1.9 4.6 5.4V21h-3.7v-5.9c0-1.6-.6-2.7-2-2.7-1.4 0-2.5 1-2.5 2.7V21H9.4z"/></svg>
                </a>
                <a href="https://www.facebook.com/envitechal" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Envi Tech AL on Facebook', 'envi-tech-al-modern'); ?>">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" focusable="false"><path d="M13.4 21v-7.4h2.5l.4-2.9h-2.9V8.8c0-.8.2-1.4 1.4-1.4h1.6V4.8c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2h-2.5v2.9h2.5V21z"/></svg>
                </a>
                <a href="https://www.instagram.com/envitech2026/" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Envi Tech AL on Instagram', 'envi-tech-al-modern'); ?>">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/></svg>
                </a>
                <a href="https://www.youtube.com/channel/UC4C6CEHceAOGuzmSX_t7CpQ" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Envi Tech AL on YouTube', 'envi-tech-al-modern'); ?>">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" focusable="false"><rect x="2" y="5.5" width="20" height="13" rx="3.5"/><path d="M10 9.2l5 2.8-5 2.8z" fill="#fff"/></svg>
                </a>
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
            <p><?php esc_html_e('87-E Madina Heights, Office A/30-31, 8th Floor, Maulana Shaukat Ali Road, Johar Town, Lahore, Punjab, Pakistan', 'envi-tech-al-modern'); ?></p>
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
            <nav class="eta-footer-legal" aria-label="<?php esc_attr_e('Legal pages', 'envi-tech-al-modern'); ?>">
                <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php esc_html_e('Privacy Policy', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>"><?php esc_html_e('Terms of Use', 'envi-tech-al-modern'); ?></a>
            </nav>
            <span><?php esc_html_e('Environmental testing, consultancy, calibration, and compliance support.', 'envi-tech-al-modern'); ?></span>
        </div>
    </div>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
