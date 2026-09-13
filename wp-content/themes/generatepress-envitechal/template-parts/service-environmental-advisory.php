<?php
/**
 * Flagship template: Environmental Advisory.
 *
 * Scroll-driven page on the fixed motion stack. The hero is a Three.js
 * performance chart: four erratic monitoring traces that settle inside their
 * limit lines as the visitor scrolls. GSAP + ScrollTrigger drive the pinned
 * maturity sequence, the workstream cards, the drawn engagement line and the
 * reveals; Lenis smooths the scroll.
 * Without WebGL, with prefers-reduced-motion, or before the module runs, the
 * page is a complete static document.
 *
 * Variables available from single-services.php: $slug, $profile, $faqs,
 * $parameters, $process.
 */

if (!defined('ABSPATH')) {
    exit;
}

$ea_theme_uri = get_stylesheet_directory_uri();
$ea_contact   = home_url('/contact-us-envi-tech-al/');
$ea_verify    = home_url('/report-verification-portal/');
$ea_faqs      = eta_modern_service_faqs('environmental-advisory');

$ea_sequence = [
    ['Performance review', 'Where the numbers stand', 'Effluent, emissions, noise, waste and workplace results from the last cycles read against the limits that apply, so the conversation starts from evidence rather than impressions.', 'SEQS · PEQS · NEQS'],
    ['Gap assessment', 'Requirement by requirement', 'Regulatory conditions, buyer codes and internal commitments compared with what the facility actually does, ranked by risk and effort.', 'Regulatory · Buyer · Internal'],
    ['Improvement plan', 'Actions with owners and dates', 'A plan the site can run: the control, the person, the deadline and the record that will show it happened.', 'Owners · Dates · Records'],
    ['Training', 'The people who run it', 'Awareness and role-specific training for operators, supervisors and EHS staff: what the limit means, what to do when a reading moves, what to record.', 'Operators · Supervisors · EHS'],
    ['Monitoring plan', 'Measured on a schedule', 'Parameters, frequencies, sampling points and methods agreed, with the laboratory work placed within the published scopes of LAB-285 and LAB-347.', 'Frequency · Points · Methods'],
    ['Audit response', 'Findings closed, evidence filed', 'Corrective and preventive actions for regulator, buyer or internal audit findings, followed through to closure and the next review.', 'CAPA · Closure'],
];

$ea_workstreams = [
    ['GAP', 'Gap assessment', 'Regulatory, buyer and internal requirements compared with practice on site, with findings ranked by risk and effort.', ['Legal register', 'Buyer codes', 'Risk ranking'], 'Assessment'],
    ['PLAN', 'Improvement planning', 'A costed, dated action plan with owners, records and milestones, reviewed at agreed intervals.', ['Owners', 'Milestones', 'Review cycle'], 'Planning'],
    ['TRAIN', 'Training support', 'Role-specific environmental training for operators, supervisors, internal auditors and management.', ['Awareness', 'Role-specific', 'Internal auditor'], 'People'],
    ['MON', 'Monitoring programme', 'Parameters, frequencies and sampling points designed around the permit, the buyer and the process, delivered by our laboratories.', ['Effluent', 'Emissions', 'Noise', 'Workplace'], 'Evidence'],
    ['AUD', 'Audit response', 'Corrective and preventive action for regulator, buyer and internal audit findings, followed to closure.', ['Root cause', 'CAPA', 'Closure evidence'], 'Assurance'],
    ['MAT', 'Compliance maturity', 'A periodic review of how the environmental programme is running, what has improved and what the next cycle should target.', ['Scorecard', 'Trend review', 'Next targets'], 'Continuity'],
];

$ea_programmes = [
    ['Inditex Green to Wear', 'Preparation for Inditex GTW audits: wastewater, chemical management, energy and environmental management criteria at wet-processing and manufacturing sites.', 'Brand programme'],
    ['Higg FEM', 'Higg Facility Environmental Module self-assessment and verification readiness: EMS, energy and GHG, water, wastewater, air, waste and chemicals, with the data to back each answer.', 'Cascale (SAC)'],
    ['amfori BEPI', 'amfori Business Environmental Performance Initiative: environmental self-assessment and audit readiness for buyer-facing supply chains.', 'Buyer initiative'],
    ['SBTi', 'Science Based Targets: GHG inventory (Scope 1, 2 and 3 screening), target setting aligned to SBTi criteria and the reduction plan behind the commitment.', 'Climate targets'],
    ['Chemical management', 'Chemical inventory, MRSL and RSL conformance, storage and handling controls, training and the records a brand audit expects.', 'CMS'],
    ['ZDHC', 'ZDHC MRSL conformance, wastewater testing to the ZDHC Wastewater Guidelines and Gateway reporting through ClearStream.', 'Roadmap to Zero'],
    ['BHive / InCheck', 'Chemical inventory management on The BHive platform and InCheck reports for ZDHC MRSL conformance levels.', 'Inventory and reporting'],
    ['Other environmental initiatives', 'Brand and industry programmes as they emerge: renewable energy transitions, water stewardship, circularity and supplier ESG reporting, prepared with laboratory data.', 'Emerging programmes'],
];

$ea_regs = [
    ['Sindh EPA · Punjab EPA', 'The approval conditions and reporting obligations an operating facility must keep'],
    ['SEQS · PEQS · NEQS', 'The limits every monitoring result is read against'],
    ['Brand environmental programmes', 'Inditex GTW, Higg FEM, amfori BEPI, SBTi, ZDHC and BHive / InCheck readiness for exporters'],
    ['ISO 14001 · ISO 45001', 'Management system frameworks the improvement plan can be aligned to'],
    ['PNAC ISO/IEC 17025', 'LAB-285 and LAB-347: the laboratories behind the monitoring evidence'],
    ['Advisory, not assurance', 'We advise and prepare; the regulator, buyer or auditor decides'],
];

$ea_serves = [
    ['EHS managers', 'A second pair of experienced hands for the plan, the training and the audit response.'],
    ['Facility and plant teams', 'Practical controls and records that fit the shift, not a binder that fits the shelf.'],
    ['Internal auditors', 'Independent review of findings, root causes and the evidence that closes them.'],
    ['Leadership teams', 'A clear view of environmental risk, cost and progress, in one periodic review.'],
    ['Exporters and brand suppliers', 'Buyer audit readiness with monitoring data the auditor can verify.'],
    ['Hospitals, hotels and institutions', 'Effluent, generator emissions, waste and noise obligations handled as one programme.'],
];

$ea_journey = [
    ['Review', 'Read the last cycles of monitoring data, the approval conditions and any audit findings, and agree what good looks like.'],
    ['Assess and plan', 'Gap assessment against the requirements that apply, then an improvement plan with owners, dates and records.'],
    ['Train and monitor', 'Train the people who run the controls, and put the monitoring programme on schedule with our laboratories.'],
    ['Review again', 'Periodic compliance maturity review: what improved, what moved, what the next cycle targets.'],
];

$ea_why = [
    ['Advice built on our own data', 'The monitoring behind the plan comes from LAB-285 and LAB-347, within their published scopes.'],
    ['Facility-aware, not textbook', 'Recommendations written for the shift pattern, the process and the budget the site actually has.'],
    ['Before the pressure arrives', 'Improvement planned on a schedule the facility controls, rather than under an inspection deadline.'],
    ['Karachi and Lahore', 'On-site advisory in both provinces, with the regulator context each one needs.'],
];

$ea_related = [
    ['Environmental consultancy', 'IEE, EIA, EMP and EMR pathways for approvals.', '/services/environmental-consultancy/'],
    ['Certification advisory', 'ISO and buyer-code readiness.', '/services/certification-advisory/'],
    ['Analytical laboratory services', 'The environmental testing laboratory behind the data.', '/services/analytical-lab-services/'],
    ['Ambient air monitoring', 'Baseline and periodic air quality monitoring.', '/ambient-air-monitoring-services/'],
    ['SEQS compliance guide', 'The Sindh limits, parameter by parameter.', '/sindh-environmental-quality-standards-seqs/'],
    ['Benefits of environmental consultancy', 'What advisory support changes on site.', '/what-are-the-benefits-of-environmental-lab-consultancy/'],
];
?>

<div id="eta-advisory" class="eta-advisory">

    <!-- ===== HERO: unsurveyed to mapped ===== -->
    <section class="ea-hero" aria-labelledby="ea-title">
        <div class="ea-stage">
            <canvas class="ea-gl" aria-hidden="true"></canvas>
            <div class="ea-veil" aria-hidden="true"></div>

            <div class="eta-shell ea-hero-grid">
                <div class="ea-hero-copy">
                    <p class="ea-eyebrow"><span>Environmental advisory</span><i>·</i><span>Higg FEM · ZDHC · SBTi · GTW</span><i>·</i><span>Karachi · Lahore</span></p>
                    <h1 id="ea-title" class="ea-title">
                        <span class="ea-title-line">Better numbers,</span>
                        <span class="ea-title-line"><em>before</em> the pressure arrives.</span>
                    </h1>
                    <p class="ea-lead">Gap assessment, improvement planning, training, monitoring programmes and audit response for facilities that want their environmental performance under control on their own schedule, advised by the laboratory that measures it.</p>
                    <div class="ea-actions">
                        <a class="ea-btn ea-btn-solid" href="<?php echo esc_url($ea_contact); ?>">Start a performance review <span aria-hidden="true">&rarr;</span></a>
                        <a class="ea-btn ea-btn-ghost" href="#ea-deliver">Advisory workstreams</a>
                    </div>
                </div>

                <aside class="ea-hero-panel" aria-label="Operational focus">
                    <p class="ea-panel-kicker">Operational focus</p>
                    <ul class="ea-panel-list">
                        <li><b>Water</b><span>Effluent and wastewater performance</span></li>
                        <li><b>Air</b><span>Stack emissions and ambient air</span></li>
                        <li><b>Noise</b><span>Boundary and workplace noise</span></li>
                        <li><b>Waste</b><span>Handling, storage and disposal records</span></li>
                    </ul>
                    <a class="ea-panel-link" href="<?php echo esc_url($ea_verify); ?>">Verify a laboratory report <span aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>

            <div class="ea-gauge" aria-hidden="true">
                <span class="ea-gauge-k">Within limits</span>
                <span class="ea-gauge-bar"><i></i></span>
                <span class="ea-gauge-v">0%</span>
            </div>
            <p class="ea-cue" aria-hidden="true"><span></span>Scroll to bring the numbers in</p>
        </div>
    </section>

    <!-- ===== LEDGER ===== -->
    <section class="ea-ledger" aria-label="Credentials">
        <div class="eta-shell ea-ledger-row">
            <div class="ea-ledger-item"><span class="ea-ledger-k">Evidence</span><strong>Laboratory-backed</strong><span class="ea-ledger-s">Monitoring data from LAB-285 and LAB-347, within published scopes</span></div>
            <div class="ea-ledger-item"><span class="ea-ledger-k">Read against</span><strong>SEQS · PEQS · NEQS</strong><span class="ea-ledger-s">And the approval conditions that apply to the site</span></div>
            <div class="ea-ledger-item"><span class="ea-ledger-k">Role</span><strong>Advisory, not assurance</strong><span class="ea-ledger-s">We advise and prepare; the regulator, buyer or auditor decides</span></div>
            <div class="ea-ledger-item"><span class="ea-ledger-k">Coverage</span><strong>Karachi and Lahore</strong><span class="ea-ledger-s">On-site in Sindh and Punjab</span></div>
        </div>
    </section>

    <!-- ===== PATHWAY: pinned track ===== -->
    <section class="ea-types" aria-labelledby="ea-types-title">
        <div class="ea-types-pin">
            <div class="eta-shell ea-types-head">
                <div>
                    <p class="ea-kicker">The maturity sequence</p>
                    <h2 id="ea-types-title">Six steps from the last set of results to a programme the site runs itself.</h2>
                </div>
                <p class="ea-types-progress" aria-hidden="true"><span class="ea-types-bar"><i></i></span><span class="ea-types-count">01 / 06</span></p>
            </div>
            <div class="ea-track" data-ea-track>
                <?php foreach ($ea_sequence as $i => $t) : ?>
                    <article class="ea-type-card" data-ea-card>
                        <div class="ea-type-top"><span class="ea-type-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span><span class="ea-type-std"><?php echo esc_html($t[3]); ?></span></div>
                        <h3><?php echo esc_html($t[0]); ?></h3>
                        <p class="ea-type-sub"><?php echo esc_html($t[1]); ?></p>
                        <p class="ea-type-body"><?php echo esc_html($t[2]); ?></p>
                        <span class="ea-type-wave" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== DELIVERABLES ===== -->
    <section id="ea-deliver" class="ea-deliver" aria-labelledby="ea-deliver-title">
        <div class="eta-shell">
            <header class="ea-head">
                <p class="ea-kicker">Advisory workstreams</p>
                <h2 id="ea-deliver-title">Six workstreams, each one built on the facility’s own monitoring data.</h2>
                <p>Testing produces the numbers. Advisory interprets the requirement, finds the gap, plans the action, trains the people and prepares the documentation around those numbers.</p>
            </header>
            <div class="ea-deliver-grid">
                <?php foreach ($ea_workstreams as $d) : ?>
                    <article class="ea-deliver-card" data-ea-tilt>
                        <div class="ea-deliver-top"><span class="ea-deliver-code"><?php echo esc_html($d[0]); ?></span><span class="ea-deliver-std"><?php echo esc_html($d[4]); ?></span></div>
                        <h3><?php echo esc_html($d[1]); ?></h3>
                        <p><?php echo esc_html($d[2]); ?></p>
                        <ul class="ea-chips">
                            <?php foreach ($d[3] as $chip) : ?><li><?php echo esc_html($chip); ?></li><?php endforeach; ?>
                        </ul>
                        <span class="ea-deliver-glow" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>

            <header class="ea-head ea-head-schemes">
                <p class="ea-kicker">Brand and industry environmental programmes</p>
                <h2 id="ea-programmes-title">The programmes buyers now ask for, prepared with the facility’s own data.</h2>
                <p>Readiness for the programme owner’s assessment, verification or audit: gap review against the criteria, data collection, documentation and facility preparation. Scores, verifications and approvals are issued by the programme owner or its approved verifier.</p>
            </header>
            <div class="ea-schemes-grid">
                <?php foreach ($ea_programmes as $pg) : ?>
                    <article class="ea-scheme-card" data-ea-tilt>
                        <span class="ea-scheme-std"><?php echo esc_html($pg[2]); ?></span>
                        <h3><?php echo esc_html($pg[0]); ?></h3>
                        <p><?php echo esc_html($pg[1]); ?></p>
                        <span class="ea-deliver-glow" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== REGULATORS ===== -->
    <section class="ea-regs" aria-labelledby="ea-regs-title">
        <div class="eta-shell ea-regs-grid">
            <div class="ea-regs-copy">
                <p class="ea-kicker">Requirements and references</p>
                <h2 id="ea-regs-title">Advice measured against the regulator, the standard and the buyer that apply to your site.</h2>
                <p>We name them exactly and work to them. Decisions on compliance rest with the regulator, buyer or auditor; our work is the evidence, the plan and the people ready to run it.</p>
            </div>
            <ul class="ea-regs-list">
                <?php foreach ($ea_regs as $r) : ?>
                    <li><b><?php echo esc_html($r[0]); ?></b><span><?php echo esc_html($r[1]); ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ===== WHO IT SERVES ===== -->
    <section class="ea-serves" aria-labelledby="ea-serves-title">
        <div class="eta-shell">
            <header class="ea-head ea-head-light">
                <p class="ea-kicker">Who relies on it</p>
                <h2 id="ea-serves-title">The people responsible for environmental performance, and the people who answer for it.</h2>
            </header>
            <div class="ea-serves-grid">
                <?php foreach ($ea_serves as $s) : ?>
                    <article class="ea-serve-card">
                        <span class="ea-serve-drop" aria-hidden="true"></span>
                        <h3><?php echo esc_html($s[0]); ?></h3>
                        <p><?php echo esc_html($s[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== JOURNEY ===== -->
    <section class="ea-journey" aria-labelledby="ea-journey-title">
        <div class="eta-shell ea-journey-grid">
            <div class="ea-journey-copy">
                <p class="ea-kicker">How an engagement runs</p>
                <h2 id="ea-journey-title">Four steps from the last results to a programme that keeps improving.</h2>
                <p>Share the site, the approval conditions, the last monitoring reports and any audit findings. The rest follows.</p>
                <a class="ea-btn ea-btn-solid" href="<?php echo esc_url($ea_contact); ?>">Request a performance review <span aria-hidden="true">&rarr;</span></a>
            </div>
            <ol class="ea-journey-list">
                <svg class="ea-journey-line" viewBox="0 0 2 100" preserveAspectRatio="none" aria-hidden="true"><path d="M1 0 V100" pathLength="1"></path></svg>
                <?php foreach ($ea_journey as $i => $step) : ?>
                    <li data-ea-step>
                        <span class="ea-journey-dot"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <strong><?php echo esc_html($step[0]); ?></strong>
                        <p><?php echo esc_html($step[1]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ===== WHY ===== -->
    <section class="ea-why" aria-labelledby="ea-why-title">
        <div class="eta-shell">
            <header class="ea-head ea-head-light">
                <p class="ea-kicker">Why facilities choose this team</p>
                <h2 id="ea-why-title">Advice from the laboratory that measures the site, written for the people who run it.</h2>
            </header>
            <div class="ea-why-grid">
                <?php foreach ($ea_why as $w) : ?>
                    <article class="ea-why-card">
                        <h3><?php echo esc_html($w[0]); ?></h3>
                        <p><?php echo esc_html($w[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="ea-faq" aria-labelledby="ea-faq-title">
        <div class="eta-shell ea-faq-grid">
            <header class="ea-head ea-head-light">
                <p class="ea-kicker">Before you get in touch</p>
                <h2 id="ea-faq-title">Questions we are asked most.</h2>
            </header>
            <div class="ea-faq-list">
                <?php foreach ($ea_faqs as $i => $faq) : ?>
                    <details class="ea-faq-item"<?php echo $i === 0 ? ' open' : ''; ?>>
                        <summary><?php echo esc_html($faq[0]); ?><span class="ea-faq-icon" aria-hidden="true"></span></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== RELATED ===== -->
    <section class="ea-related" aria-labelledby="ea-related-title">
        <div class="eta-shell">
            <header class="ea-head ea-head-light">
                <p class="ea-kicker">Go deeper</p>
                <h2 id="ea-related-title">Related services, standards and the laboratory behind the data.</h2>
            </header>
            <div class="ea-related-grid">
                <?php foreach ($ea_related as $r) : ?>
                    <a class="ea-related-card" href="<?php echo esc_url(home_url($r[2])); ?>">
                        <strong><?php echo esc_html($r[0]); ?></strong>
                        <p><?php echo esc_html($r[1]); ?></p>
                        <span class="ea-inline-link">Open <i aria-hidden="true">&rarr;</i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FINAL ===== -->
    <section class="ea-final" aria-labelledby="ea-final-title">
        <div class="eta-shell ea-final-grid">
            <div>
                <p class="ea-kicker">Results drifting, an audit ahead, or a programme to build?</p>
                <h2 id="ea-final-title">Send the site, the approval conditions and the last monitoring reports. We confirm the review, the scope and a schedule.</h2>
            </div>
            <div class="ea-final-actions">
                <a class="ea-btn ea-btn-solid" href="<?php echo esc_url($ea_contact); ?>">Start a performance review <span aria-hidden="true">&rarr;</span></a>
                <a class="ea-btn ea-btn-ghost" href="https://wa.me/923102288801" target="_blank" rel="noopener">WhatsApp consultation</a>
            </div>
        </div>
    </section>

</div>

<script data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1">
(function () {
    var d = document.documentElement;
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'noModule' in HTMLScriptElement.prototype) {
        d.classList.add('ea-gsap');
    }
    function f() { var h = document.getElementById('masthead'); d.style.setProperty('--eta-hh', (h ? h.offsetHeight : 0) + 'px'); }
    f(); addEventListener('resize', f); addEventListener('load', f);
})();
</script>
<script type="module" src="<?php echo esc_url($ea_theme_uri . '/eta-advisory-scene.js?v=' . (string) filemtime(get_stylesheet_directory() . '/eta-advisory-scene.js')); ?>" data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>
