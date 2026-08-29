<?php
/**
 * Legal pages: privacy policy and terms of use.
 *
 * Renders the two legal documents from code (matching the theme's
 * slug-dispatch pattern in page.php) and creates the backing pages
 * once so the routes exist on every environment.
 */

if (!defined('ABSPATH')) {
    exit;
}

const ETA_LEGAL_PAGES_VERSION = 1;
const ETA_LEGAL_EFFECTIVE_DATE = '29 August 2026';

function eta_modern_legal_pages()
{
    return [
        'privacy-policy' => [
            'title' => 'Privacy Policy',
            'excerpt' => 'How Envi Tech AL collects, uses, and protects personal information submitted through this website, including enquiry forms, report verification, and the site assistant.',
        ],
        'terms-of-service' => [
            'title' => 'Terms of Use',
            'excerpt' => 'The terms that apply to using the Envi Tech AL website, its downloads, and the report verification portal.',
        ],
    ];
}

add_action('admin_init', function () {
    if ((int) get_option('eta_legal_pages_version') >= ETA_LEGAL_PAGES_VERSION) {
        return;
    }

    if (!current_user_can('edit_pages')) {
        return;
    }

    foreach (eta_modern_legal_pages() as $slug => $page) {
        if (get_page_by_path($slug, OBJECT, 'page')) {
            continue;
        }

        wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_name' => $slug,
            'post_title' => $page['title'],
            'post_excerpt' => $page['excerpt'],
            'post_content' => '<!-- Rendered by the theme: inc/legal-pages.php -->',
        ]);
    }

    update_option('eta_legal_pages_version', ETA_LEGAL_PAGES_VERSION);
});

function eta_modern_legal_section($title, $paragraphs = [], $items = [])
{
    ?>
    <section class="eta-legal-section">
        <h2><?php echo esc_html($title); ?></h2>
        <?php foreach ($paragraphs as $paragraph) : ?>
            <p><?php echo wp_kses($paragraph, ['a' => ['href' => []], 'strong' => [], 'em' => []]); ?></p>
        <?php endforeach; ?>
        <?php if ($items) : ?>
            <ul>
                <?php foreach ($items as $item) : ?>
                    <li><?php echo wp_kses($item, ['a' => ['href' => []], 'strong' => [], 'em' => []]); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php
}

function eta_modern_render_privacy_page()
{
    eta_modern_page_hero(
        'Privacy Policy',
        'How Envi Tech AL collects, uses, and protects information submitted through this website.'
    );
    ?>
    <section class="eta-band">
        <div class="eta-shell eta-legal-prose">
            <p class="eta-legal-updated"><?php echo esc_html('Effective date: ' . ETA_LEGAL_EFFECTIVE_DATE); ?></p>

            <?php
            eta_modern_legal_section('Who we are', [
                'Envi Tech AL operates environmental testing laboratories and consultancy services in Karachi and Lahore, Pakistan. This policy describes how information submitted through this website is handled. For questions about this policy, contact <a href="mailto:info@envitechal.com">info@envitechal.com</a>.',
            ]);

            eta_modern_legal_section('Information we collect', [
                'We collect information you choose to send us through this website:',
            ], [
                '<strong>Enquiry and quotation forms</strong> — name, company, email address, phone or WhatsApp number, city, service of interest, and the details of your requirement.',
                '<strong>Report verification requests</strong> — report number, report date, company name, and the requester\'s name and contact details.',
                '<strong>Site assistant</strong> — questions you type into the on-site assistant, used to answer your query and improve the assistance we provide.',
                '<strong>Technical data</strong> — standard web log and analytics information such as IP address, browser type, pages visited, and referring pages.',
            ]);

            eta_modern_legal_section('How we use information', [], [
                'To respond to enquiries, prepare quotations, and coordinate sampling, testing, monitoring, calibration, or consultancy work.',
                'To verify the authenticity of Envi Tech AL test reports on request.',
                'To maintain business records required for laboratory quality systems and legal compliance.',
                'To understand how the website is used so we can improve it.',
            ]);

            eta_modern_legal_section('Third-party services', [
                'This website uses a small number of third-party services that may process technical data under their own privacy policies:',
            ], [
                '<strong>Google reCAPTCHA</strong> — protects our forms from automated abuse. Use of reCAPTCHA is subject to Google\'s privacy policy and terms.',
                '<strong>Google Maps</strong> — displays our office locations on the contact page.',
                '<strong>Web analytics</strong> — measures page visits in aggregate.',
                '<strong>WhatsApp</strong> — if you choose a WhatsApp link, your conversation is handled by WhatsApp (Meta) under its own terms.',
            ]);

            eta_modern_legal_section('Cookies', [
                'The website uses a limited set of cookies for security (such as spam protection) and analytics. You can control or delete cookies through your browser settings; the site remains usable with non-essential cookies disabled.',
            ]);

            eta_modern_legal_section('Sharing', [
                'We do not sell personal information. Information may be shared with service providers who support the website\'s operation (such as hosting and email), with regulators or authorities where the law requires it, and within Envi Tech AL for the purposes described above.',
            ]);

            eta_modern_legal_section('Retention', [
                'Enquiry and verification records are kept for as long as needed to handle the request and to meet business, quality-system, and legal record-keeping obligations, after which they are deleted or anonymised.',
            ]);

            eta_modern_legal_section('Your rights', [
                'You may ask us to access, correct, or delete personal information you have submitted through this website by writing to <a href="mailto:info@envitechal.com">info@envitechal.com</a>. We will respond within a reasonable working period.',
            ]);

            eta_modern_legal_section('Security', [
                'The website is served over HTTPS and we apply reasonable technical and organisational measures to protect submitted information. No method of transmission or storage is completely secure, and we cannot guarantee absolute security.',
            ]);

            eta_modern_legal_section('Changes to this policy', [
                'We may update this policy from time to time. The effective date above reflects the latest revision, and material changes will be published on this page.',
            ]);
            ?>
        </div>
    </section>
    <?php
}

function eta_modern_render_terms_page()
{
    eta_modern_page_hero(
        'Terms of Use',
        'The terms that apply to using the Envi Tech AL website, downloads, and report verification portal.'
    );
    ?>
    <section class="eta-band">
        <div class="eta-shell eta-legal-prose">
            <p class="eta-legal-updated"><?php echo esc_html('Effective date: ' . ETA_LEGAL_EFFECTIVE_DATE); ?></p>

            <?php
            eta_modern_legal_section('Acceptance of these terms', [
                'By using this website you accept these terms of use. If you do not agree with them, please do not use the website.',
            ]);

            eta_modern_legal_section('About this website', [
                'This website describes the environmental testing, monitoring, calibration, and consultancy services of Envi Tech AL. Content is provided for general information. Service scope, methods, turnaround, and pricing are confirmed only in a written quotation or agreement for a specific requirement.',
            ]);

            eta_modern_legal_section('Reports and verification', [], [
                'The report verification portal confirms whether a document was issued by Envi Tech AL and matches the identifying details provided. It is not a re-issue of results.',
                'The authoritative record of any test is the signed laboratory report issued to the client.',
                'Accreditation and regulatory approval apply to the methods and parameters included within the respective approved scope at the time of testing.',
            ]);

            eta_modern_legal_section('Acceptable use', [], [
                'Do not use the website in a way that is unlawful, or that misrepresents your identity or association with any organisation.',
                'Do not attempt to interfere with the operation or security of the website.',
                'Do not use automated tools to overload the website, its forms, or its verification portal.',
            ]);

            eta_modern_legal_section('Intellectual property', [
                'Website content, including text, layout, and imagery, belongs to Envi Tech AL unless otherwise noted. Certificates, marks, and logos of accreditation bodies and regulators belong to their respective owners. Copies of laws and regulations offered in the downloads section are published government documents provided for reference; confirm current versions with the issuing authority.',
            ]);

            eta_modern_legal_section('Third-party links', [
                'The website links to third-party resources such as regulator and accreditation-body websites. Envi Tech AL is not responsible for the content or availability of external sites.',
            ]);

            eta_modern_legal_section('Disclaimer and limitation of liability', [
                'Website content is provided "as is" without warranties of any kind. General information on this website is not a substitute for advice on a specific facility, sample, or compliance requirement, which is provided only under a formal engagement. To the maximum extent permitted by law, Envi Tech AL is not liable for loss arising from reliance on website content, from interruption of the website, or from third-party services linked from it.',
            ]);

            eta_modern_legal_section('Governing law', [
                'These terms are governed by the laws of Pakistan, and any dispute relating to this website is subject to the jurisdiction of the courts of Pakistan.',
            ]);

            eta_modern_legal_section('Contact', [
                'Questions about these terms can be sent to <a href="mailto:info@envitechal.com">info@envitechal.com</a>.',
            ]);
            ?>
        </div>
    </section>
    <?php
}
