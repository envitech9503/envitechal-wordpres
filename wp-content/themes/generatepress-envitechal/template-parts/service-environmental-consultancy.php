<?php
/**
 * Flagship template: Environmental Consultancy.
 *
 * Scroll-driven page on the fixed motion stack. The hero is a Three.js
 * terrain: a wireframe site that begins as an unsurveyed, jittering field and
 * is mapped, contour by contour, by a survey sweep as the visitor scrolls.
 * GSAP + ScrollTrigger drive the pinned regulatory pathway, the deliverable
 * cards, the drawn engagement line and the reveals; Lenis smooths the scroll.
 * Without WebGL, with prefers-reduced-motion, or before the module runs, the
 * page is a complete static document.
 *
 * Variables available from single-services.php: $slug, $profile, $faqs,
 * $parameters, $process.
 */

if (!defined('ABSPATH')) {
    exit;
}

$ec_theme_uri = get_stylesheet_directory_uri();
$ec_contact   = home_url('/contact-us-envi-tech-al/');
$ec_verify    = home_url('/report-verification-portal/');
$ec_faqs      = eta_modern_service_faqs('environmental-consultancy');

$ec_pathway = [
    ['Screening and scoping', 'Which route applies', 'Whether the project falls under IEE or EIA, what the authority expects at each stage, and what evidence the submission must carry. Scoping is where most delays are avoided.', 'Sindh EPA · Punjab EPA'],
    ['IEE / EIA preparation', 'The submission, built to be read', 'Baseline monitoring, impact assessment and mitigation planning prepared as the regulator reads them, with laboratory data from LAB-285 or LAB-347 where the scope requires it.', 'IEE · EIA'],
    ['Approval conditions', 'What the NOC actually asks for', 'Approval letters carry conditions: monitoring frequencies, reporting intervals, limits. We translate them into an operational schedule the facility can keep.', 'NOC conditions'],
    ['Environmental Management Plan', 'Controls, owners and records', 'An EMP that names the control, the person responsible and the record that proves it, aligned to the standard the site is measured against.', 'EMP'],
    ['Monitoring and EMR', 'The periodic evidence', 'Ambient air, stack emissions, effluent, noise and workplace monitoring on the agreed frequency, compiled into the Environmental Monitoring Report the authority expects.', 'EMR · SEQS · PEQS · NEQS'],
    ['Audits and corrective action', 'Closing the gaps', 'Compliance and buyer audits, gap assessments and corrective action plans with dates, so the next inspection or audit starts from a stronger position.', 'Audit · CAPA'],
];

$ec_deliverables = [
    ['IEE', 'Initial Environmental Examination', 'For projects below the EIA threshold: screening, baseline, impacts, mitigation and the submission package.', ['Screening', 'Baseline data', 'Mitigation plan'], 'Sindh EPA · Punjab EPA'],
    ['EIA', 'Environmental Impact Assessment', 'Full assessment for larger or sensitive projects, including baseline monitoring, alternatives and public consultation support.', ['Baseline monitoring', 'Impact matrix', 'Consultation support'], 'Sindh EPA · Punjab EPA'],
    ['EMP', 'Environmental Management Plan', 'The operating control document: responsibilities, monitoring schedule, records and emergency arrangements.', ['Control register', 'Monitoring schedule', 'Records'], 'Operating facilities'],
    ['EMR', 'Environmental Monitoring Report', 'Periodic reporting of air, effluent, noise and workplace results against the applicable limits, in the format the authority accepts.', ['Quarterly / biannual', 'Results vs limits', 'Verifiable data'], 'SEQS · PEQS · NEQS'],
    ['Audit', 'Environmental and compliance audits', 'Gap assessments against regulatory conditions, buyer codes or ISO 14001, with prioritised corrective actions.', ['Gap assessment', 'Buyer audit prep', 'ISO 14001'], 'Facilities and exporters'],
    ['Liaison', 'Regulatory coordination', 'Correspondence, submissions, clarifications and inspection support with Sindh EPA and Punjab EPA on the client\'s behalf.', ['Submissions', 'Clarifications', 'Inspection support'], 'Sindh EPA · Punjab EPA'],
];

$ec_regs = [
    ['Sindh EPA', 'IEE/EIA approvals, NOC conditions and EMR expectations for Sindh'],
    ['Punjab EPA', 'Approvals and monitoring requirements for Punjab; Envi Tech AL is an EPA-listed laboratory'],
    ['SEQS', 'Sindh Environmental Quality Standards for effluent, air and noise'],
    ['PEQS', 'Punjab Environmental Quality Standards'],
    ['NEQS', 'National Environmental Quality Standards where the province applies them'],
    ['ISO 14001', 'Environmental management system alignment for audits and certification'],
];

$ec_serves = [
    ['New projects and expansions', 'IEE or EIA before construction, and the conditions that follow approval.'],
    ['Operating industrial units', 'EMP, periodic monitoring and EMR submissions kept on schedule.'],
    ['Exporters and brand suppliers', 'Buyer and brand environmental audits with evidence a laboratory can stand behind.'],
    ['Hospitals, hotels and institutions', 'Effluent, generator emissions and noise obligations handled as one programme.'],
    ['Contractors and developers', 'Site environmental requirements written into a plan the site team can follow.'],
    ['Compliance and EHS managers', 'A single point of contact for the regulator, the laboratory and the paperwork.'],
];

$ec_journey = [
    ['Scope', 'Confirm project type, regulatory pathway, site context, authority requirement and the documentation the submission needs.'],
    ['Baseline and evidence', 'Field monitoring and laboratory analysis within the published scopes of LAB-285 and LAB-347, so the numbers in the submission are defensible.'],
    ['Prepare and submit', 'Build the IEE, EIA, EMP, EMR or audit route with controlled supporting evidence, and coordinate the submission with the authority.'],
    ['Support and follow-up', 'Help the client understand findings, conditions, next steps and corrective actions, through to the next monitoring cycle.'],
];

$ec_why = [
    ['Laboratory and consultancy in one team', 'The monitoring data behind a submission comes from our own PNAC-accredited laboratories, within their published scopes.'],
    ['Regulator-literate documents', 'Reports are prepared the way Sindh EPA and Punjab EPA read them, against the standard that applies.'],
    ['Karachi and Lahore', 'Field teams and offices in both provinces, so site work and authority coordination happen locally.'],
    ['No outcome promises', 'We describe what is measured, prepared and submitted. Approval decisions rest with the authority.'],
];

$ec_related = [
    ['EMR and EMP for Sindh EPA', 'What the monitoring report must contain and how often.', '/environmental-monitoring-report-emr-emp-sindh-epa/'],
    ['Sindh EPA vs Punjab EPA NOC', 'How the two approval routes differ.', '/sindh-epa-vs-punjab-epa-noc-lahore/'],
    ['SEQS compliance guide', 'The Sindh limits, parameter by parameter.', '/sindh-environmental-quality-standards-seqs/'],
    ['Ambient air monitoring', 'Baseline and periodic air quality monitoring.', '/ambient-air-monitoring-services/'],
    ['Analytical laboratory services', 'The environmental testing lab behind the data.', '/services/analytical-lab-services/'],
    ['Certification advisory', 'ISO 14001 and related system preparation.', '/services/certification-advisory/'],
];
?>

<div id="eta-consult" class="eta-consult">

    <!-- ===== HERO: unsurveyed to mapped ===== -->
    <section class="ec-hero" aria-labelledby="ec-title">
        <div class="ec-stage">
            <canvas class="ec-gl" aria-hidden="true"></canvas>
            <div class="ec-veil" aria-hidden="true"></div>

            <div class="eta-shell ec-hero-grid">
                <div class="ec-hero-copy">
                    <p class="ec-eyebrow"><span>Environmental consultancy</span><i>·</i><span>Sindh EPA</span><i>·</i><span>Punjab EPA</span></p>
                    <h1 id="ec-title" class="ec-title">
                        <span class="ec-title-line">From site survey</span>
                        <span class="ec-title-line">to <em>submission</em>.</span>
                    </h1>
                    <p class="ec-lead">IEE, EIA, EMP, EMR, audits and regulatory coordination for projects and facilities in Sindh and Punjab, prepared by a consultancy with its own accredited laboratories behind the data.</p>
                    <div class="ec-actions">
                        <a class="ec-btn ec-btn-solid" href="<?php echo esc_url($ec_contact); ?>">Discuss a project <span aria-hidden="true">&rarr;</span></a>
                        <a class="ec-btn ec-btn-ghost" href="#ec-deliver">See the deliverables</a>
                    </div>
                </div>

                <aside class="ec-hero-panel" aria-label="Regulatory routes">
                    <p class="ec-panel-kicker">Routes we prepare</p>
                    <ul class="ec-panel-list">
                        <li><b>IEE</b><span>Initial Environmental Examination</span></li>
                        <li><b>EIA</b><span>Environmental Impact Assessment</span></li>
                        <li><b>EMP</b><span>Environmental Management Plan</span></li>
                        <li><b>EMR</b><span>Environmental Monitoring Report</span></li>
                    </ul>
                    <a class="ec-panel-link" href="<?php echo esc_url($ec_verify); ?>">Verify a laboratory report <span aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>

            <div class="ec-gauge" aria-hidden="true">
                <span class="ec-gauge-k">Site mapped</span>
                <span class="ec-gauge-bar"><i></i></span>
                <span class="ec-gauge-v">0%</span>
            </div>
            <p class="ec-cue" aria-hidden="true"><span></span>Scroll to survey the site</p>
        </div>
    </section>

    <!-- ===== LEDGER ===== -->
    <section class="ec-ledger" aria-label="Credentials">
        <div class="eta-shell ec-ledger-row">
            <div class="ec-ledger-item"><span class="ec-ledger-k">Sindh</span><strong>Sindh EPA</strong><span class="ec-ledger-s">IEE, EIA, EMP and EMR pathways</span></div>
            <div class="ec-ledger-item"><span class="ec-ledger-k">Punjab</span><strong>Punjab EPA</strong><span class="ec-ledger-s">EPA-listed laboratory, LAB-347 Lahore</span></div>
            <div class="ec-ledger-item"><span class="ec-ledger-k">Laboratory data</span><strong>PNAC ISO/IEC 17025</strong><span class="ec-ledger-s">LAB-285 Karachi, LAB-347 Lahore, within published scopes</span></div>
            <div class="ec-ledger-item"><span class="ec-ledger-k">Systems</span><strong>ISO 14001 aligned</strong><span class="ec-ledger-s">Audit and certification preparation</span></div>
        </div>
    </section>

    <!-- ===== PATHWAY: pinned track ===== -->
    <section class="ec-types" aria-labelledby="ec-types-title">
        <div class="ec-types-pin">
            <div class="eta-shell ec-types-head">
                <div>
                    <p class="ec-kicker">The regulatory pathway</p>
                    <h2 id="ec-types-title">Six stages between a site and its standing with the authority.</h2>
                </div>
                <p class="ec-types-progress" aria-hidden="true"><span class="ec-types-bar"><i></i></span><span class="ec-types-count">01 / 06</span></p>
            </div>
            <div class="ec-track" data-ec-track>
                <?php foreach ($ec_pathway as $i => $t) : ?>
                    <article class="ec-type-card" data-ec-card>
                        <div class="ec-type-top"><span class="ec-type-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span><span class="ec-type-std"><?php echo esc_html($t[3]); ?></span></div>
                        <h3><?php echo esc_html($t[0]); ?></h3>
                        <p class="ec-type-sub"><?php echo esc_html($t[1]); ?></p>
                        <p class="ec-type-body"><?php echo esc_html($t[2]); ?></p>
                        <span class="ec-type-wave" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== DELIVERABLES ===== -->
    <section id="ec-deliver" class="ec-deliver" aria-labelledby="ec-deliver-title">
        <div class="eta-shell">
            <header class="ec-head">
                <p class="ec-kicker">What we prepare</p>
                <h2 id="ec-deliver-title">Every document the pathway needs, with the evidence behind it.</h2>
                <p>Each deliverable is scoped to the authority, the project and the standard that applies. Laboratory results are produced within the published scopes of LAB-285 and LAB-347.</p>
            </header>
            <div class="ec-deliver-grid">
                <?php foreach ($ec_deliverables as $d) : ?>
                    <article class="ec-deliver-card" data-ec-tilt>
                        <div class="ec-deliver-top"><span class="ec-deliver-code"><?php echo esc_html($d[0]); ?></span><span class="ec-deliver-std"><?php echo esc_html($d[4]); ?></span></div>
                        <h3><?php echo esc_html($d[1]); ?></h3>
                        <p><?php echo esc_html($d[2]); ?></p>
                        <ul class="ec-chips">
                            <?php foreach ($d[3] as $chip) : ?><li><?php echo esc_html($chip); ?></li><?php endforeach; ?>
                        </ul>
                        <span class="ec-deliver-glow" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== REGULATORS ===== -->
    <section class="ec-regs" aria-labelledby="ec-regs-title">
        <div class="eta-shell ec-regs-grid">
            <div class="ec-regs-copy">
                <p class="ec-kicker">Authorities and standards</p>
                <h2 id="ec-regs-title">Prepared against the body and the standard that actually apply to your site.</h2>
                <p>We name them exactly and work to them. Approval decisions rest with the authority; our work is the evidence and the documents that go in front of it.</p>
            </div>
            <ul class="ec-regs-list">
                <?php foreach ($ec_regs as $r) : ?>
                    <li><b><?php echo esc_html($r[0]); ?></b><span><?php echo esc_html($r[1]); ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ===== WHO IT SERVES ===== -->
    <section class="ec-serves" aria-labelledby="ec-serves-title">
        <div class="eta-shell">
            <header class="ec-head ec-head-light">
                <p class="ec-kicker">Who relies on it</p>
                <h2 id="ec-serves-title">Projects at the approval stage, and facilities that have to stay compliant afterwards.</h2>
            </header>
            <div class="ec-serves-grid">
                <?php foreach ($ec_serves as $s) : ?>
                    <article class="ec-serve-card">
                        <span class="ec-serve-drop" aria-hidden="true"></span>
                        <h3><?php echo esc_html($s[0]); ?></h3>
                        <p><?php echo esc_html($s[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== JOURNEY ===== -->
    <section class="ec-journey" aria-labelledby="ec-journey-title">
        <div class="eta-shell ec-journey-grid">
            <div class="ec-journey-copy">
                <p class="ec-kicker">How an engagement runs</p>
                <h2 id="ec-journey-title">A controlled route from scope to submission and the cycle after it.</h2>
                <p>Good scoping protects timelines, cost and the usefulness of everything submitted.</p>
                <a class="ec-btn ec-btn-solid" href="<?php echo esc_url($ec_contact); ?>">Start a conversation <span aria-hidden="true">&rarr;</span></a>
            </div>
            <ol class="ec-journey-list">
                <svg class="ec-journey-line" viewBox="0 0 2 100" preserveAspectRatio="none" aria-hidden="true"><path d="M1 0 V100" pathLength="1"></path></svg>
                <?php foreach ($ec_journey as $i => $step) : ?>
                    <li data-ec-step>
                        <span class="ec-journey-dot"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <strong><?php echo esc_html($step[0]); ?></strong>
                        <p><?php echo esc_html($step[1]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ===== WHY ===== -->
    <section class="ec-why" aria-labelledby="ec-why-title">
        <div class="eta-shell">
            <header class="ec-head ec-head-light">
                <p class="ec-kicker">Why clients choose this team</p>
                <h2 id="ec-why-title">Consultancy with the laboratory, the field team and the regulator experience in one place.</h2>
            </header>
            <div class="ec-why-grid">
                <?php foreach ($ec_why as $w) : ?>
                    <article class="ec-why-card">
                        <h3><?php echo esc_html($w[0]); ?></h3>
                        <p><?php echo esc_html($w[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="ec-faq" aria-labelledby="ec-faq-title">
        <div class="eta-shell ec-faq-grid">
            <header class="ec-head ec-head-light">
                <p class="ec-kicker">Before you get in touch</p>
                <h2 id="ec-faq-title">Questions we are asked most.</h2>
            </header>
            <div class="ec-faq-list">
                <?php foreach ($ec_faqs as $i => $faq) : ?>
                    <details class="ec-faq-item"<?php echo $i === 0 ? ' open' : ''; ?>>
                        <summary><?php echo esc_html($faq[0]); ?><span class="ec-faq-icon" aria-hidden="true"></span></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== RELATED ===== -->
    <section class="ec-related" aria-labelledby="ec-related-title">
        <div class="eta-shell">
            <header class="ec-head ec-head-light">
                <p class="ec-kicker">Go deeper</p>
                <h2 id="ec-related-title">Guides, standards and the services that feed the submission.</h2>
            </header>
            <div class="ec-related-grid">
                <?php foreach ($ec_related as $r) : ?>
                    <a class="ec-related-card" href="<?php echo esc_url(home_url($r[2])); ?>">
                        <strong><?php echo esc_html($r[0]); ?></strong>
                        <p><?php echo esc_html($r[1]); ?></p>
                        <span class="ec-inline-link">Open <i aria-hidden="true">&rarr;</i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FINAL ===== -->
    <section class="ec-final" aria-labelledby="ec-final-title">
        <div class="eta-shell ec-final-grid">
            <div>
                <p class="ec-kicker">A submission deadline, an inspection, or an audit ahead?</p>
                <h2 id="ec-final-title">Send the project outline, the authority and the date. We confirm the pathway, the scope and a schedule.</h2>
            </div>
            <div class="ec-final-actions">
                <a class="ec-btn ec-btn-solid" href="<?php echo esc_url($ec_contact); ?>">Discuss a project <span aria-hidden="true">&rarr;</span></a>
                <a class="ec-btn ec-btn-ghost" href="https://wa.me/923102288801" target="_blank" rel="noopener">WhatsApp consultation</a>
            </div>
        </div>
    </section>

</div>

<script data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1">
(function () {
    var d = document.documentElement;
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'noModule' in HTMLScriptElement.prototype) {
        d.classList.add('ec-gsap');
    }
    function f() { var h = document.getElementById('masthead'); d.style.setProperty('--eta-hh', (h ? h.offsetHeight : 0) + 'px'); }
    f(); addEventListener('resize', f); addEventListener('load', f);
})();
</script>
<script type="module" src="<?php echo esc_url($ec_theme_uri . '/eta-consult-scene.js?v=' . (string) filemtime(get_stylesheet_directory() . '/eta-consult-scene.js')); ?>" data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>
