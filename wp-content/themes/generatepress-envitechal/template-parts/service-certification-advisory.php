<?php
/**
 * Flagship template: Certification Advisory.
 *
 * Scroll-driven page on the fixed motion stack. The hero is a Three.js
 * management system assembling itself: a cloud of requirements, procedures
 * and records that takes its place in a four-level hierarchy as the visitor
 * scrolls. GSAP + ScrollTrigger drive the pinned readiness sequence, the
 * standard cards, the drawn engagement line and the reveals; Lenis smooths.
 * Without WebGL, with prefers-reduced-motion, or before the module runs, the
 * page is a complete static document.
 *
 * Variables available from single-services.php: $slug, $profile, $faqs,
 * $parameters, $process.
 */

if (!defined('ABSPATH')) {
    exit;
}

$ca_theme_uri = get_stylesheet_directory_uri();
$ca_contact   = home_url('/contact-us-envi-tech-al/');
$ca_verify    = home_url('/report-verification-portal/');
$ca_faqs      = eta_modern_service_faqs('certification-advisory');

$ca_sequence = [
    ['Gap assessment', 'Where the system stands today', 'A clause-by-clause review of the standard against what the organisation actually does, with findings ranked by effort and audit risk.', 'Clause by clause'],
    ['Scope and context', 'Defining what is certified', 'Scope statement, interested parties, legal and other requirements, and the risks and opportunities register the standard expects to see first.', 'Context · Scope'],
    ['Documentation', 'Written the way it is worked', 'Policy, objectives, procedures and forms built around existing practice rather than imposed on it, so they survive the first month after the audit.', 'Policy · Procedures · Records'],
    ['Evidence and records', 'Proof that it runs', 'Monitoring results, calibration records, training evidence, legal registers and corrective actions collected and filed against the clause they satisfy.', 'Lab and monitoring data'],
    ['Internal audit and review', 'The dress rehearsal', 'Internal audit, management review and a corrective action cycle completed before the certification body arrives, with the records to show for it.', 'Internal audit · MR'],
    ['Certification audit support', 'On the day, and after', 'Preparation of the team, presence during Stage 1 and Stage 2 where wanted, and closure of any nonconformities raised. The decision rests with the certification body.', 'Stage 1 · Stage 2 · CAPA'],
];

$ca_standards = [
    ['9001', 'ISO 9001:2015 Quality', 'Quality management system readiness: process approach, risk-based thinking, customer focus and controlled records.', ['Process map', 'Objectives', 'Internal audit'], 'Management system'],
    ['14001', 'ISO 14001:2015 Environment', 'Environmental management: aspects and impacts, legal register, operational controls and monitoring, backed by our own laboratory data.', ['Aspects register', 'Legal register', 'Monitoring'], 'Management system'],
    ['45001', 'ISO 45001:2018 Health and safety', 'Occupational health and safety: hazard identification, workplace monitoring, worker participation and incident control.', ['Hazard register', 'Workplace monitoring', 'Participation'], 'Management system'],
    ['17025', 'ISO/IEC 17025 laboratories', 'For in-house or third-party laboratories preparing for accreditation: the standard our own laboratories are accredited to.', ['Method validation', 'Uncertainty', 'Proficiency testing'], 'Laboratory competence'],
    ['Buyer', 'Buyer and brand codes', 'Environmental and social requirements set by international buyers and brand programmes, prepared with the evidence they ask for.', ['Audit prep', 'Evidence file', 'Corrective actions'], 'Supply chain'],
    ['Evidence', 'Testing and monitoring evidence', 'Effluent, emissions, noise, workplace and calibration records from LAB-285 and LAB-347, produced within the published scopes, to sit behind the system.', ['Verifiable reports', 'Scheduled monitoring'], 'From our laboratories'],
];

$ca_schemes = [
    ['GOTS / OCS', 'Global Organic Textile Standard and Organic Content Standard: organic fibre content, chain of custody and, for GOTS, environmental and social criteria.', 'Organic textiles'],
    ['GRS / RCS', 'Global Recycled Standard and Recycled Claim Standard: recycled content verification and chain of custody through the supply chain.', 'Recycled content'],
    ['Regenagri', 'Regenerative agriculture programme: farm and supply-chain criteria for soil health, biodiversity and chain of custody.', 'Regenerative agriculture'],
    ['STeP by OEKO-TEX', 'Sustainable Textile and Leather Production: chemicals management, environmental performance, social responsibility and safety at the facility.', 'Sustainable production'],
    ['WRAP', 'Worldwide Responsible Accredited Production: social compliance and safety certification for apparel and footwear facilities.', 'Social compliance'],
    ['SEDEX / SMETA', 'Sedex membership and SMETA audits: labour, health and safety, environment and business ethics for buyer-facing supply chains.', 'Ethical trade audit'],
    ['CTPAT', 'Customs Trade Partnership Against Terrorism: supply-chain security criteria for exporters shipping to the United States.', 'Supply-chain security'],
    ['amfori BSCI', 'Business Social Compliance Initiative: social performance audits against the amfori BSCI Code of Conduct.', 'Social compliance'],
    ['Pakistan Accord', 'Pakistan Accord assessment readiness: structural, electrical and fire safety evidence, corrective action plans and follow-up for garment and textile factories.', 'Building and fire safety'],
];

$ca_regs = [
    ['Certification bodies', 'Independent bodies conduct the audit and make the certification decision; we prepare the organisation for it'],
    ['ISO 9001 · 14001 · 45001', 'The management system standards most of our clients certify to'],
    ['ISO/IEC 17025', 'Laboratory competence, the standard behind LAB-285 and LAB-347'],
    ['Textile and social schemes', 'GOTS, OCS, GRS, RCS, Regenagri, STeP, WRAP, SEDEX, CTPAT and amfori BSCI readiness for exporters'],
    ['Sindh EPA · Punjab EPA', 'The legal requirements an environmental management system must register and meet'],
    ['SEQS · PEQS · NEQS', 'The limits the monitoring evidence is read against'],
    ['No certificates issued by us', 'Advisory only: we do not issue, sell or guarantee certificates'],
];

$ca_serves = [
    ['Factories and export units', 'Buyer audits and ISO certification with an evidence file that stands up.'],
    ['Management system teams', 'A second pair of hands for the gap assessment, the documentation and the internal audit.'],
    ['Compliance and EHS departments', 'The environmental and safety evidence gathered in one place, on one schedule.'],
    ['Laboratories', 'ISO/IEC 17025 readiness from a team that has been through the accreditation itself.'],
    ['Hospitals, hotels and institutions', 'Quality and environmental systems scaled to the organisation, not to a template.'],
    ['Contractors and developers', 'Systems and records that satisfy client prequalification and tender requirements.'],
];

$ca_journey = [
    ['Assess', 'Gap assessment against the chosen standard, with findings ranked and a realistic timeline to the audit.'],
    ['Build', 'Documentation, registers and controls written around how the organisation works, with training for the people who will run them.'],
    ['Evidence', 'Monitoring, calibration and testing records from our laboratories and your operations filed against the clauses they satisfy.'],
    ['Audit', 'Internal audit, management review and corrective actions completed; support through the certification audit and the closure of findings.'],
];

$ca_why = [
    ['Evidence-led, not template-led', 'Systems built on real monitoring, calibration and testing records, many of them produced in our own laboratories.'],
    ['We know the audit from both sides', 'Our laboratories are assessed by PNAC every cycle. We prepare clients the way we prepare ourselves.'],
    ['Honest about the outcome', 'We prepare the organisation; the certification body decides. We do not issue or guarantee certificates.'],
    ['Karachi and Lahore', 'On-site support in both provinces, with the regulator context that an ISO 14001 system needs.'],
];

$ca_related = [
    ['Accreditations and certifications', 'The credentials behind our own laboratories.', '/accreditations-certifications/'],
    ['Environmental consultancy', 'IEE, EIA, EMP and EMR pathways.', '/services/environmental-consultancy/'],
    ['Equipment calibration', 'Calibration records for the evidence file.', '/services/equipment-calibration-services/'],
    ['Analytical laboratory services', 'Testing and monitoring data behind the system.', '/services/analytical-lab-services/'],
    ['Textile effluent testing', 'Effluent evidence behind textile certifications.', '/textile-effluent-testing-compliance-pakistan/'],
    ['Verify a report', 'Check a laboratory report by number and date.', '/report-verification-portal/'],
];
?>

<div id="eta-cert" class="eta-cert">

    <!-- ===== HERO: unsurveyed to mapped ===== -->
    <section class="ca-hero" aria-labelledby="ca-title">
        <div class="ca-stage">
            <canvas class="ca-gl" aria-hidden="true"></canvas>
            <div class="ca-veil" aria-hidden="true"></div>

            <div class="eta-shell ca-hero-grid">
                <div class="ca-hero-copy">
                    <p class="ca-eyebrow"><span>Certification advisory</span><i>·</i><span>ISO 9001 · 14001 · 45001</span><i>·</i><span>GOTS · GRS · STeP · WRAP · BSCI</span></p>
                    <h1 id="ca-title" class="ca-title">
                        <span class="ca-title-line">A system that is</span>
                        <span class="ca-title-line"><em>ready</em> for the audit.</span>
                    </h1>
                    <p class="ca-lead">ISO and buyer-code readiness for factories, exporters, laboratories and institutions: gap assessment, documentation, evidence and internal audit, prepared by a team whose own laboratories are assessed every cycle. The certification body decides; we get you ready.</p>
                    <div class="ca-actions">
                        <a class="ca-btn ca-btn-solid" href="<?php echo esc_url($ca_contact); ?>">Start a gap assessment <span aria-hidden="true">&rarr;</span></a>
                        <a class="ca-btn ca-btn-ghost" href="#ca-deliver">Standards we prepare for</a>
                    </div>
                </div>

                <aside class="ca-hero-panel" aria-label="Standards">
                    <p class="ca-panel-kicker">Readiness we prepare</p>
                    <ul class="ca-panel-list">
                        <li><b>ISO 9001</b><span>Quality management</span></li>
                        <li><b>ISO 14001</b><span>Environmental management</span></li>
                        <li><b>ISO 45001</b><span>Health and safety</span></li>
                        <li><b>ISO 17025</b><span>Laboratory competence</span></li>
                    </ul>
                    <a class="ca-panel-link" href="<?php echo esc_url($ca_verify); ?>">Verify a laboratory report <span aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>

            <div class="ca-gauge" aria-hidden="true">
                <span class="ca-gauge-k">System assembled</span>
                <span class="ca-gauge-bar"><i></i></span>
                <span class="ca-gauge-v">0%</span>
            </div>
            <p class="ca-cue" aria-hidden="true"><span></span>Scroll to assemble the system</p>
        </div>
    </section>

    <!-- ===== LEDGER ===== -->
    <section class="ca-ledger" aria-label="Credentials">
        <div class="eta-shell ca-ledger-row">
            <div class="ca-ledger-item"><span class="ca-ledger-k">Role</span><strong>Advisory only</strong><span class="ca-ledger-s">We prepare the organisation; the certification body decides</span></div>
            <div class="ca-ledger-item"><span class="ca-ledger-k">Our own credentials</span><strong>PNAC ISO/IEC 17025</strong><span class="ca-ledger-s">LAB-285 Karachi and LAB-347 Lahore, assessed every cycle</span></div>
            <div class="ca-ledger-item"><span class="ca-ledger-k">Evidence</span><strong>Laboratory-backed</strong><span class="ca-ledger-s">Monitoring, testing and calibration records from our laboratories</span></div>
            <div class="ca-ledger-item"><span class="ca-ledger-k">Coverage</span><strong>Karachi and Lahore</strong><span class="ca-ledger-s">On-site in Sindh and Punjab</span></div>
        </div>
    </section>

    <!-- ===== PATHWAY: pinned track ===== -->
    <section class="ca-types" aria-labelledby="ca-types-title">
        <div class="ca-types-pin">
            <div class="eta-shell ca-types-head">
                <div>
                    <p class="ca-kicker">The readiness sequence</p>
                    <h2 id="ca-types-title">Six steps between the first gap assessment and the certification audit.</h2>
                </div>
                <p class="ca-types-progress" aria-hidden="true"><span class="ca-types-bar"><i></i></span><span class="ca-types-count">01 / 06</span></p>
            </div>
            <div class="ca-track" data-ca-track>
                <?php foreach ($ca_sequence as $i => $t) : ?>
                    <article class="ca-type-card" data-ca-card>
                        <div class="ca-type-top"><span class="ca-type-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span><span class="ca-type-std"><?php echo esc_html($t[3]); ?></span></div>
                        <h3><?php echo esc_html($t[0]); ?></h3>
                        <p class="ca-type-sub"><?php echo esc_html($t[1]); ?></p>
                        <p class="ca-type-body"><?php echo esc_html($t[2]); ?></p>
                        <span class="ca-type-wave" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== DELIVERABLES ===== -->
    <section id="ca-deliver" class="ca-deliver" aria-labelledby="ca-deliver-title">
        <div class="eta-shell">
            <header class="ca-head">
                <p class="ca-kicker">Standards we prepare for</p>
                <h2 id="ca-deliver-title">The standards our clients certify to, and the evidence that sits behind each one.</h2>
                <p>Advisory covers gap assessment, documentation, evidence, internal audit and audit-day support. Certificates are issued by independent certification bodies on their own assessment.</p>
            </header>
            <div class="ca-deliver-grid">
                <?php foreach ($ca_standards as $d) : ?>
                    <article class="ca-deliver-card" data-ca-tilt>
                        <div class="ca-deliver-top"><span class="ca-deliver-code"><?php echo esc_html($d[0]); ?></span><span class="ca-deliver-std"><?php echo esc_html($d[4]); ?></span></div>
                        <h3><?php echo esc_html($d[1]); ?></h3>
                        <p><?php echo esc_html($d[2]); ?></p>
                        <ul class="ca-chips">
                            <?php foreach ($d[3] as $chip) : ?><li><?php echo esc_html($chip); ?></li><?php endforeach; ?>
                        </ul>
                        <span class="ca-deliver-glow" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>

            <header class="ca-head ca-head-schemes">
                <p class="ca-kicker">Textile, social and supply-chain schemes</p>
                <h2 id="ca-schemes-title">GOTS, GRS, RCS, Regenagri and Pakistan Accord readiness for exporters in Karachi and Lahore.</h2>
                <p>Readiness for the scheme owner’s audit or certification body: gap assessment against the standard, documentation, evidence and facility preparation. The scheme owner or its approved body makes the decision.</p>
            </header>
            <div class="ca-schemes-grid">
                <?php foreach ($ca_schemes as $sc) : ?>
                    <article class="ca-scheme-card" data-ca-tilt>
                        <span class="ca-scheme-std"><?php echo esc_html($sc[2]); ?></span>
                        <h3><?php echo esc_html($sc[0]); ?></h3>
                        <p><?php echo esc_html($sc[1]); ?></p>
                        <span class="ca-deliver-glow" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== REGULATORS ===== -->
    <section class="ca-regs" aria-labelledby="ca-regs-title">
        <div class="eta-shell ca-regs-grid">
            <div class="ca-regs-copy">
                <p class="ca-kicker">Standards, bodies and regulators</p>
                <h2 id="ca-regs-title">Prepared against the standard, the certification body and the regulator that apply.</h2>
                <p>We name them exactly and work to them. The certification decision rests with the body that audits you; our work is the readiness, the documents and the evidence.</p>
            </div>
            <ul class="ca-regs-list">
                <?php foreach ($ca_regs as $r) : ?>
                    <li><b><?php echo esc_html($r[0]); ?></b><span><?php echo esc_html($r[1]); ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ===== WHO IT SERVES ===== -->
    <section class="ca-serves" aria-labelledby="ca-serves-title">
        <div class="eta-shell">
            <header class="ca-head ca-head-light">
                <p class="ca-kicker">Who relies on it</p>
                <h2 id="ca-serves-title">Organisations with an audit date, a buyer requirement or an accreditation ahead.</h2>
            </header>
            <div class="ca-serves-grid">
                <?php foreach ($ca_serves as $s) : ?>
                    <article class="ca-serve-card">
                        <span class="ca-serve-drop" aria-hidden="true"></span>
                        <h3><?php echo esc_html($s[0]); ?></h3>
                        <p><?php echo esc_html($s[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== JOURNEY ===== -->
    <section class="ca-journey" aria-labelledby="ca-journey-title">
        <div class="eta-shell ca-journey-grid">
            <div class="ca-journey-copy">
                <p class="ca-kicker">How an engagement runs</p>
                <h2 id="ca-journey-title">Four steps from the gap assessment to the audit and the closure of findings.</h2>
                <p>Share the standard, the scope of the organisation, the target audit date and any buyer or regulator context. The rest follows.</p>
                <a class="ca-btn ca-btn-solid" href="<?php echo esc_url($ca_contact); ?>">Request a gap assessment <span aria-hidden="true">&rarr;</span></a>
            </div>
            <ol class="ca-journey-list">
                <svg class="ca-journey-line" viewBox="0 0 2 100" preserveAspectRatio="none" aria-hidden="true"><path d="M1 0 V100" pathLength="1"></path></svg>
                <?php foreach ($ca_journey as $i => $step) : ?>
                    <li data-ca-step>
                        <span class="ca-journey-dot"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <strong><?php echo esc_html($step[0]); ?></strong>
                        <p><?php echo esc_html($step[1]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ===== WHY ===== -->
    <section class="ca-why" aria-labelledby="ca-why-title">
        <div class="eta-shell">
            <header class="ca-head ca-head-light">
                <p class="ca-kicker">Why organisations choose this team</p>
                <h2 id="ca-why-title">Readiness from a team that is audited itself, with the evidence to show for it.</h2>
            </header>
            <div class="ca-why-grid">
                <?php foreach ($ca_why as $w) : ?>
                    <article class="ca-why-card">
                        <h3><?php echo esc_html($w[0]); ?></h3>
                        <p><?php echo esc_html($w[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="ca-faq" aria-labelledby="ca-faq-title">
        <div class="eta-shell ca-faq-grid">
            <header class="ca-head ca-head-light">
                <p class="ca-kicker">Before you get in touch</p>
                <h2 id="ca-faq-title">Questions we are asked most.</h2>
            </header>
            <div class="ca-faq-list">
                <?php foreach ($ca_faqs as $i => $faq) : ?>
                    <details class="ca-faq-item"<?php echo $i === 0 ? ' open' : ''; ?>>
                        <summary><?php echo esc_html($faq[0]); ?><span class="ca-faq-icon" aria-hidden="true"></span></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== RELATED ===== -->
    <section class="ca-related" aria-labelledby="ca-related-title">
        <div class="eta-shell">
            <header class="ca-head ca-head-light">
                <p class="ca-kicker">Go deeper</p>
                <h2 id="ca-related-title">Credentials, guides and the services that feed the evidence file.</h2>
            </header>
            <div class="ca-related-grid">
                <?php foreach ($ca_related as $r) : ?>
                    <a class="ca-related-card" href="<?php echo esc_url(home_url($r[2])); ?>">
                        <strong><?php echo esc_html($r[0]); ?></strong>
                        <p><?php echo esc_html($r[1]); ?></p>
                        <span class="ca-inline-link">Open <i aria-hidden="true">&rarr;</i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FINAL ===== -->
    <section class="ca-final" aria-labelledby="ca-final-title">
        <div class="eta-shell ca-final-grid">
            <div>
                <p class="ca-kicker">An audit date set, or a buyer asking for a certificate?</p>
                <h2 id="ca-final-title">Send the standard, the size of the organisation and the target date. We confirm the gap assessment, the timeline and what evidence is needed.</h2>
            </div>
            <div class="ca-final-actions">
                <a class="ca-btn ca-btn-solid" href="<?php echo esc_url($ca_contact); ?>">Start a gap assessment <span aria-hidden="true">&rarr;</span></a>
                <a class="ca-btn ca-btn-ghost" href="https://wa.me/923102288801" target="_blank" rel="noopener">WhatsApp consultation</a>
            </div>
        </div>
    </section>

</div>

<script data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1">
(function () {
    var d = document.documentElement;
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'noModule' in HTMLScriptElement.prototype) {
        d.classList.add('ca-gsap');
    }
    function f() { var h = document.getElementById('masthead'); d.style.setProperty('--eta-hh', (h ? h.offsetHeight : 0) + 'px'); }
    f(); addEventListener('resize', f); addEventListener('load', f);
})();
</script>
<script type="module" src="<?php echo esc_url($ca_theme_uri . '/eta-cert-scene.js?v=' . (string) filemtime(get_stylesheet_directory() . '/eta-cert-scene.js')); ?>" data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>
