<?php
/**
 * Branded site footer.
 *
 * Full-bleed closing statement rather than a link strip: an invitation and
 * direct contact first, then the full link index, the credential rail, an
 * oversized wordmark, and the colophon. Every link the previous footer
 * carried is preserved.
 */

if (!defined('ABSPATH')) {
    exit;
}

$eta_year = date('Y');
?>

    </div><!-- #content -->

<footer class="eta-fs-footer" role="contentinfo">

    <!-- instrument edge: a measured rule, not a border -->
    <div class="fsf-rule" aria-hidden="true"></div>
    <span class="fsf-mark fsf-mark-tl" aria-hidden="true"></span>
    <span class="fsf-mark fsf-mark-tr" aria-hidden="true"></span>

    <!-- ACT 1 — the invitation and the direct line -->
    <div class="fsf-top">
        <div class="eta-shell fsf-top-grid">

            <section class="fsf-invite" aria-labelledby="fsf-invite-title">
                <a class="fsf-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Envi Tech AL homepage', 'envi-tech-al-modern'); ?>">
                    <?php
                    $custom_logo_id = get_theme_mod('custom_logo');
                    if ($custom_logo_id) {
                        echo wp_get_attachment_image($custom_logo_id, 'medium', false, ['class' => 'fsf-logo-img', 'alt' => 'Envi Tech AL']);
                    } else {
                        echo '<span class="fsf-logo-text">Envi Tech AL</span>';
                    }
                    ?>
                </a>

                <p class="fsf-kicker"><?php esc_html_e('Have a requirement?', 'envi-tech-al-modern'); ?></p>
                <h2 class="fsf-headline" id="fsf-invite-title"><?php esc_html_e('Send the scope.', 'envi-tech-al-modern'); ?><br><?php esc_html_e('We confirm the method.', 'envi-tech-al-modern'); ?></h2>
                <p class="fsf-sub"><?php esc_html_e('Testing, monitoring, calibration and consultancy for teams working to a deadline — scope confirmed before work begins.', 'envi-tech-al-modern'); ?></p>

                <div class="fsf-actions">
                    <a class="fsf-btn fsf-btn-solid" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request a quotation', 'envi-tech-al-modern'); ?> <span class="fsf-arrow" aria-hidden="true">&rarr;</span></a>
                    <a class="fsf-btn fsf-btn-line" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
                </div>
            </section>

            <section class="fsf-reach" aria-labelledby="fsf-reach-title">
                <p class="fsf-label" id="fsf-reach-title"><?php esc_html_e('Speak to the lab', 'envi-tech-al-modern'); ?></p>

                <a class="fsf-mail" href="mailto:info@envitechal.com">info@envitechal.com</a>

                <ul class="fsf-tels">
                    <li><a href="tel:+923102288801">+92 310 2288801</a></li>
                    <li><a href="tel:+923152006074">+92 315 2006074</a></li>
                    <li><a href="tel:+924232296099">+92 42 32296099</a></li>
                </ul>

                <div class="fsf-offices">
                    <div class="fsf-office">
                        <h3><?php esc_html_e('Karachi', 'envi-tech-al-modern'); ?></h3>
                        <p><?php esc_html_e('First Floor, 345, Street 15, Bahadurabad Block 3, Bahadur Yar Jang CHS, Karachi, Sindh 75900', 'envi-tech-al-modern'); ?></p>
                    </div>
                    <div class="fsf-office">
                        <h3><?php esc_html_e('Lahore', 'envi-tech-al-modern'); ?></h3>
                        <p><?php esc_html_e('87-E Madina Heights, Office A/30-31, 8th Floor, Maulana Shaukat Ali Road, Johar Town, Lahore', 'envi-tech-al-modern'); ?></p>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <!-- ACT 2 — what the laboratory actually measures, running continuously -->
    <div class="fsf-marquee" aria-hidden="true">
        <div class="fsf-marquee-track">
            <?php
            $eta_params = ['SO₂', 'NOₓ', 'CO', 'pH', 'TDS', 'TSS', 'COD', 'BOD₅', 'PM₂.₅', 'PM₁₀', 'dB(A)', 'Cd', 'Cr', 'Pb', 'Hg', 'Ni', 'Zn', 'As', 'Oil &amp; Grease', 'Phenols', 'NH₃', 'Lux', '°C', 'RH'];
            for ($i = 0; $i < 2; $i++) {
                foreach ($eta_params as $p) {
                    echo '<span class="fsf-param">' . $p . '</span><i class="fsf-dot">•</i>';
                }
            }
            ?>
        </div>
    </div>

    <!-- ACT 3 — the full index -->
    <div class="fsf-index-band">
        <div class="eta-shell fsf-index-grid">

            <nav class="fsf-col" aria-label="<?php esc_attr_e('Footer service links', 'envi-tech-al-modern'); ?>">
                <p class="fsf-label"><span class="fsf-n">01</span> <?php esc_html_e('Services', 'envi-tech-al-modern'); ?></p>
                <a href="<?php echo esc_url(home_url('/services/analytical-lab-services/')); ?>"><?php esc_html_e('Analytical Lab Services', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/services/water-testing-lab-services/')); ?>"><?php esc_html_e('Water Testing Services', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/wastewater-testing-services/')); ?>"><?php esc_html_e('Wastewater Testing', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/ambient-air-monitoring-services/')); ?>"><?php esc_html_e('Air Monitoring', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/services/equipment-calibration-services/')); ?>"><?php esc_html_e('Equipment Calibration', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/services/environmental-consultancy/')); ?>"><?php esc_html_e('Environmental Consultancy', 'envi-tech-al-modern'); ?></a>
            </nav>

            <nav class="fsf-col" aria-label="<?php esc_attr_e('Footer company links', 'envi-tech-al-modern'); ?>">
                <p class="fsf-label"><span class="fsf-n">02</span> <?php esc_html_e('Company', 'envi-tech-al-modern'); ?></p>
                <a href="<?php echo esc_url(home_url('/aboutus/')); ?>"><?php esc_html_e('About Envi Tech AL', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/blognewsupdates/')); ?>"><?php esc_html_e('Knowledge Hub', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/ourclients/')); ?>"><?php esc_html_e('Clients', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/downloads/')); ?>"><?php esc_html_e('Downloads', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/careers-at-envi-tech-al/')); ?>"><?php esc_html_e('Careers', 'envi-tech-al-modern'); ?></a>
            </nav>

            <nav class="fsf-col" aria-label="<?php esc_attr_e('Footer support links', 'envi-tech-al-modern'); ?>">
                <p class="fsf-label"><span class="fsf-n">03</span> <?php esc_html_e('Support', 'envi-tech-al-modern'); ?></p>
                <a href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Contact Us', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Verify a Report', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/accreditations-certifications/')); ?>"><?php esc_html_e('Certifications &amp; Approvals', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/environmental-testing-faqs-pakistan/')); ?>"><?php esc_html_e('Environmental Testing FAQ', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp', 'envi-tech-al-modern'); ?></a>
            </nav>

            <nav class="fsf-col" aria-label="<?php esc_attr_e('Footer laboratory links', 'envi-tech-al-modern'); ?>">
                <p class="fsf-label"><span class="fsf-n">04</span> <?php esc_html_e('Laboratories', 'envi-tech-al-modern'); ?></p>
                <a href="<?php echo esc_url(home_url('/karachi-environmental-lab/')); ?>"><?php esc_html_e('Karachi Environmental Lab', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/lahore-environmental-lab/')); ?>"><?php esc_html_e('Lahore Environmental Lab', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/sindh-environmental-quality-standards-seqs/')); ?>"><?php esc_html_e('SEQS Compliance Guide', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/services/ballast-water-testing-services/')); ?>"><?php esc_html_e('Ballast Water Testing', 'envi-tech-al-modern'); ?></a>
            </nav>

        </div>
    </div>

    <!-- ACT 3 — the credentials, stated once, plainly -->
    <div class="fsf-rail">
        <div class="eta-shell fsf-rail-inner">
            <span class="fsf-cred"><i class="fsf-live" aria-hidden="true"></i><strong>PNAC</strong> ISO/IEC 17025 &middot; LAB-285 Karachi</span>
            <span class="fsf-cred"><i class="fsf-live" aria-hidden="true"></i><strong>PNAC</strong> ISO/IEC 17025 &middot; LAB-347 Lahore</span>
            <span class="fsf-cred"><i class="fsf-live" aria-hidden="true"></i><strong>ISO 9001:2015</strong> Quality</span>
            <span class="fsf-cred"><i class="fsf-live" aria-hidden="true"></i><strong>ISO 14001:2015</strong> Environment</span>
            <span class="fsf-cred"><i class="fsf-live" aria-hidden="true"></i><strong>Sindh EPA</strong> &middot; <strong>Punjab EPA</strong></span>
        </div>
    </div>

    <!-- ACT 4 — the closing statement -->
    <div class="fsf-statement">
        <div class="fsf-aurora" aria-hidden="true"></div>
        <div class="fsf-contours" aria-hidden="true"></div>
        <span class="fsf-wordmark" aria-hidden="true" data-text="Envi Tech AL">Envi Tech AL</span>
        <p class="fsf-statement-note"><span>Measure.</span><span>Understand.</span><span>Comply.</span><span>Improve.</span></p>
    </div>

    <!-- ACT 5 — colophon -->
    <div class="fsf-colophon">
        <div class="eta-shell fsf-colophon-inner">
            <span class="fsf-copy"><?php echo esc_html('© ' . $eta_year . ' Envi Tech AL'); ?></span>
            <span class="fsf-disciplines"><?php esc_html_e('Testing', 'envi-tech-al-modern'); ?> <i aria-hidden="true">✳</i> <?php esc_html_e('Monitoring', 'envi-tech-al-modern'); ?> <i aria-hidden="true">✳</i> <?php esc_html_e('Consultancy', 'envi-tech-al-modern'); ?> <i aria-hidden="true">✳</i> <?php esc_html_e('Calibration', 'envi-tech-al-modern'); ?></span>

            <nav class="fsf-legal" aria-label="<?php esc_attr_e('Legal pages', 'envi-tech-al-modern'); ?>">
                <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php esc_html_e('Privacy Policy', 'envi-tech-al-modern'); ?></a>
                <a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>"><?php esc_html_e('Terms of Use', 'envi-tech-al-modern'); ?></a>
            </nav>

            <a class="fsf-top-link" href="#page"><span class="fsf-top-arrow" aria-hidden="true">&uarr;</span> Back to top</a>

            <div class="fsf-social" aria-label="<?php esc_attr_e('Envi Tech AL on social media', 'envi-tech-al-modern'); ?>">
                <a href="https://www.linkedin.com/company/envitech-al" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Envi Tech AL on LinkedIn', 'envi-tech-al-modern'); ?>">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor" aria-hidden="true" focusable="false"><circle cx="4.6" cy="4.9" r="2.1"/><rect x="2.8" y="9.3" width="3.6" height="11.7"/><path d="M9.4 9.3h3.5v1.7c.6-1 1.9-2 3.8-2 3 0 4.6 1.9 4.6 5.4V21h-3.7v-5.9c0-1.6-.6-2.7-2-2.7-1.4 0-2.5 1-2.5 2.7V21H9.4z"/></svg>
                </a>
                <a href="https://www.facebook.com/envitechal" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Envi Tech AL on Facebook', 'envi-tech-al-modern'); ?>">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor" aria-hidden="true" focusable="false"><path d="M13.4 21v-7.4h2.5l.4-2.9h-2.9V8.8c0-.8.2-1.4 1.4-1.4h1.6V4.8c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2h-2.5v2.9h2.5V21z"/></svg>
                </a>
                <a href="https://www.instagram.com/envitech2026/" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Envi Tech AL on Instagram', 'envi-tech-al-modern'); ?>">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/></svg>
                </a>
                <a href="https://www.youtube.com/channel/UC4C6CEHceAOGuzmSX_t7CpQ" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Envi Tech AL on YouTube', 'envi-tech-al-modern'); ?>">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor" aria-hidden="true" focusable="false"><rect x="2" y="5.5" width="20" height="13" rx="3.5"/><path d="M10 9.2l5 2.8-5 2.8z" fill="#08130F"/></svg>
                </a>
            </div>
        </div>
    </div>
    <span class="fsf-mark fsf-mark-bl" aria-hidden="true"></span>
    <span class="fsf-mark fsf-mark-br" aria-hidden="true"></span>
</footer>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
