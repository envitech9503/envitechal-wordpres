<?php
/**
 * Flagship template: Technical Advisory.
 *
 * Scroll-driven page on the fixed motion stack. The hero is a Three.js
 * root-cause tree: audit findings that trace up through corrective actions
 * and root causes to a closed audit as the visitor scrolls. GSAP +
 * ScrollTrigger drive the pinned review sequence, the support cards, the
 * drawn engagement line and the reveals; Lenis smooths the scroll.
 * Without WebGL, with prefers-reduced-motion, or before the module runs, the
 * page is a complete static document.
 *
 * Variables available from single-services.php: $slug, $profile, $faqs,
 * $parameters, $process.
 */

if (!defined('ABSPATH')) {
    exit;
}

$ta_theme_uri = get_stylesheet_directory_uri();
$ta_contact   = home_url('/contact-us-envi-tech-al/');
$ta_verify    = home_url('/report-verification-portal/');
$ta_faqs      = eta_modern_service_faqs('technical-advisory-2');

$ta_sequence = [
    ['Read the finding', 'What was actually observed', 'Audit findings, regulator notices, buyer non-conformances and laboratory exceedances read back to what was observed, measured and against which requirement.', 'Finding · Requirement'],
    ['Interpret the data', 'What the number means', 'Laboratory results, monitoring records and calibration certificates interpreted in the context of the process, the method and the limit that applies.', 'Result · Method · Limit'],
    ['Root cause', 'Why it happened', 'Structured root-cause analysis with the operations team, so the action addresses the cause rather than the symptom.', 'Cause, not symptom'],
    ['Corrective action', 'Owners, dates, evidence', 'A corrective and preventive action plan with the responsible person, the deadline and the evidence that will show closure.', 'CAPA'],
    ['Evidence mapping', 'Every requirement, one record', 'Existing records mapped to the requirements they satisfy, gaps listed, and the missing evidence planned.', 'Requirement → record'],
    ['Closure and follow-up', 'Ready for the re-audit', 'Findings closed with the evidence filed, verification monitoring scheduled and the next review date set.', 'Closed · Verified'],
];

$ta_support = [
    ['AUD', 'Technical audits', 'Independent technical review of a facility, process or programme against the regulatory, buyer or internal requirement that applies.', ['Site audit', 'Process review', 'Programme review'], 'Assessment'],
    ['GAP', 'Gap assessment', 'Requirement-by-requirement comparison of what is required with what is in place, ranked by risk and effort.', ['Legal', 'Buyer', 'Internal'], 'Assessment'],
    ['RPT', 'Report interpretation', 'Laboratory and monitoring reports explained: method, uncertainty, limit and what the result means for the site.', ['Results', 'Uncertainty', 'Limits'], 'Interpretation'],
    ['CAPA', 'Corrective action planning', 'Root cause, action, owner, date and closure evidence for every finding, in a plan the site can run.', ['Root cause', 'Owner and date', 'Closure'], 'Action'],
    ['EVD', 'Evidence preparation', 'Records, registers and reports assembled and mapped to the requirements an auditor or regulator will check.', ['Evidence file', 'Mapping', 'Gaps'], 'Documentation'],
    ['MON', 'Monitoring recommendations', 'Parameters, frequencies and sampling points recommended for the finding, delivered by our laboratories within their published scopes.', ['Parameters', 'Frequency', 'Points'], 'Follow-up'],
];

$ta_regs = [
    ['Sindh EPA · Punjab EPA', 'Notices, conditions and monitoring obligations that findings are read against'],
    ['SEQS · PEQS · NEQS', 'The limits every laboratory result is interpreted against'],
    ['Buyer and brand codes', 'Non-conformances raised in supplier audits and their closure evidence'],
    ['ISO 9001 · 14001 · 45001', 'Management system findings and the corrective action cycle they require'],
    ['PNAC ISO/IEC 17025', 'LAB-285 and LAB-347: the laboratories behind the results we interpret'],
    ['Advisory, not assurance', 'We review, explain and plan; the regulator, buyer or auditor decides on closure'],
];

$ta_serves = [
    ['Factories and export units', 'Audit findings turned into a plan with owners and dates before the follow-up visit.'],
    ['Project teams', 'Technical requirements at each project stage explained and evidenced.'],
    ['Compliance leaders', 'A clear reading of what a notice, a result or a finding actually requires.'],
    ['Technical managers', 'Laboratory data connected to process decisions and monitoring plans.'],
    ['Internal auditors', 'An external technical view on findings, causes and closure evidence.'],
    ['Hospitals, hotels and institutions', 'Findings from inspections and audits closed with documented evidence.'],
];

$ta_journey = [
    ['Share the finding', 'Send the audit finding, report, regulatory requirement, project stage, deadline and any existing evidence or corrective action record.'],
    ['Review', 'We read the finding against the requirement and the data, and identify the cause and the gaps.'],
    ['Plan', 'Corrective actions, evidence mapping and monitoring recommendations with owners and dates.'],
    ['Close', 'Evidence assembled, findings closed, verification monitoring scheduled and the next review set.'],
];

$ta_why = [
    ['We produce the data we interpret', 'Laboratory results from LAB-285 and LAB-347, within their published scopes, explained by the people who generated them.'],
    ['Action lists, not essays', 'Every finding leaves with a cause, an action, an owner, a date and the evidence that closes it.'],
    ['Operational context', 'Recommendations written for the process, the shift and the budget the site actually has.'],
    ['Karachi and Lahore', 'On-site support in both provinces, with the regulator context each one needs.'],
];

$ta_related = [
    ['Environmental advisory', 'Improvement planning, training and monitoring programmes.', '/services/environmental-advisory/'],
    ['Environmental consultancy', 'IEE, EIA, EMP and EMR pathways.', '/services/environmental-consultancy/'],
    ['Certification advisory', 'ISO and buyer-code readiness.', '/services/certification-advisory/'],
    ['Analytical laboratory services', 'The environmental testing laboratory behind the data.', '/services/analytical-lab-services/'],
    ['How to read a water test report', 'PEQS and WHO limits explained.', '/how-to-read-water-test-report-peqs-who/'],
    ['Verify a report', 'Check a laboratory report by number and date.', '/report-verification-portal/'],
];
?>

<div id="eta-techadv" class="eta-techadv">

    <!-- ===== HERO: unsurveyed to mapped ===== -->
    <section class="ta-hero" aria-labelledby="ta-title">
        <div class="ta-stage">
            <canvas class="ta-gl" aria-hidden="true"></canvas>
            <div class="ta-veil" aria-hidden="true"></div>

            <div class="eta-shell ta-hero-grid">
                <div class="ta-hero-copy">
                    <p class="ta-eyebrow"><span>Technical advisory</span><i>·</i><span>Audits · Evidence · Corrective action</span><i>·</i><span>Karachi · Lahore</span></p>
                    <h1 id="ta-title" class="ta-title">
                        <span class="ta-title-line">Every finding,</span>
                        <span class="ta-title-line">traced to its <em>cause</em>.</span>
                    </h1>
                    <p class="ta-lead">Technical guidance for facilities that need clear findings, practical actions and documentation support before or after an audit: reports interpreted, root causes found, corrective actions planned and the evidence assembled to close them.</p>
                    <div class="ta-actions">
                        <a class="ta-btn ta-btn-solid" href="<?php echo esc_url($ta_contact); ?>">Send a finding <span aria-hidden="true">&rarr;</span></a>
                        <a class="ta-btn ta-btn-ghost" href="#ta-deliver">How we support</a>
                    </div>
                </div>

                <aside class="ta-hero-panel" aria-label="What to share">
                    <p class="ta-panel-kicker">What to share with us</p>
                    <ul class="ta-panel-list">
                        <li><b>Finding</b><span>The audit finding, notice or report</span></li>
                        <li><b>Requirement</b><span>The regulation, code or clause cited</span></li>
                        <li><b>Stage</b><span>Project stage and the deadline</span></li>
                        <li><b>Evidence</b><span>Existing records and corrective actions</span></li>
                    </ul>
                    <a class="ta-panel-link" href="<?php echo esc_url($ta_verify); ?>">Verify a laboratory report <span aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>

            <div class="ta-gauge" aria-hidden="true">
                <span class="ta-gauge-k">Findings traced</span>
                <span class="ta-gauge-bar"><i></i></span>
                <span class="ta-gauge-v">0%</span>
            </div>
            <p class="ta-cue" aria-hidden="true"><span></span>Scroll to trace the findings</p>
        </div>
    </section>

    <!-- ===== LEDGER ===== -->
    <section class="ta-ledger" aria-label="Credentials">
        <div class="eta-shell ta-ledger-row">
            <div class="ta-ledger-item"><span class="ta-ledger-k">Output</span><strong>Clear action lists</strong><span class="ta-ledger-s">Cause, action, owner, date and closure evidence per finding</span></div>
            <div class="ta-ledger-item"><span class="ta-ledger-k">Data</span><strong>PNAC ISO/IEC 17025</strong><span class="ta-ledger-s">Results from LAB-285 and LAB-347 interpreted by the laboratory that produced them</span></div>
            <div class="ta-ledger-item"><span class="ta-ledger-k">Role</span><strong>Advisory, not assurance</strong><span class="ta-ledger-s">The regulator, buyer or auditor decides on closure</span></div>
            <div class="ta-ledger-item"><span class="ta-ledger-k">Coverage</span><strong>Karachi and Lahore</strong><span class="ta-ledger-s">On-site in Sindh and Punjab</span></div>
        </div>
    </section>

    <!-- ===== PATHWAY: pinned track ===== -->
    <section class="ta-types" aria-labelledby="ta-types-title">
        <div class="ta-types-pin">
            <div class="eta-shell ta-types-head">
                <div>
                    <p class="ta-kicker">The review sequence</p>
                    <h2 id="ta-types-title">Six steps from a finding on paper to a finding closed with evidence.</h2>
                </div>
                <p class="ta-types-progress" aria-hidden="true"><span class="ta-types-bar"><i></i></span><span class="ta-types-count">01 / 06</span></p>
            </div>
            <div class="ta-track" data-ta-track>
                <?php foreach ($ta_sequence as $i => $t) : ?>
                    <article class="ta-type-card" data-ta-card>
                        <div class="ta-type-top"><span class="ta-type-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span><span class="ta-type-std"><?php echo esc_html($t[3]); ?></span></div>
                        <h3><?php echo esc_html($t[0]); ?></h3>
                        <p class="ta-type-sub"><?php echo esc_html($t[1]); ?></p>
                        <p class="ta-type-body"><?php echo esc_html($t[2]); ?></p>
                        <span class="ta-type-wave" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== DELIVERABLES ===== -->
    <section id="ta-deliver" class="ta-deliver" aria-labelledby="ta-deliver-title">
        <div class="eta-shell">
            <header class="ta-head">
                <p class="ta-kicker">How we support</p>
                <h2 id="ta-deliver-title">Six kinds of technical support, before the audit and after it.</h2>
                <p>Testing produces the data. Technical advisory reads the finding against the requirement, explains the result, finds the cause and prepares the evidence that closes it.</p>
            </header>
            <div class="ta-deliver-grid">
                <?php foreach ($ta_support as $d) : ?>
                    <article class="ta-deliver-card" data-ta-tilt>
                        <div class="ta-deliver-top"><span class="ta-deliver-code"><?php echo esc_html($d[0]); ?></span><span class="ta-deliver-std"><?php echo esc_html($d[4]); ?></span></div>
                        <h3><?php echo esc_html($d[1]); ?></h3>
                        <p><?php echo esc_html($d[2]); ?></p>
                        <ul class="ta-chips">
                            <?php foreach ($d[3] as $chip) : ?><li><?php echo esc_html($chip); ?></li><?php endforeach; ?>
                        </ul>
                        <span class="ta-deliver-glow" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- ===== REGULATORS ===== -->
    <section class="ta-regs" aria-labelledby="ta-regs-title">
        <div class="eta-shell ta-regs-grid">
            <div class="ta-regs-copy">
                <p class="ta-kicker">Requirements and references</p>
                <h2 id="ta-regs-title">Findings read against the regulator, the standard and the buyer that raised them.</h2>
                <p>We name them exactly and work to them. Closure decisions rest with whoever raised the finding; our work is the interpretation, the plan and the evidence.</p>
            </div>
            <ul class="ta-regs-list">
                <?php foreach ($ta_regs as $r) : ?>
                    <li><b><?php echo esc_html($r[0]); ?></b><span><?php echo esc_html($r[1]); ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ===== WHO IT SERVES ===== -->
    <section class="ta-serves" aria-labelledby="ta-serves-title">
        <div class="eta-shell">
            <header class="ta-head ta-head-light">
                <p class="ta-kicker">Who relies on it</p>
                <h2 id="ta-serves-title">Anyone holding a finding, a notice or a report that needs turning into action.</h2>
            </header>
            <div class="ta-serves-grid">
                <?php foreach ($ta_serves as $s) : ?>
                    <article class="ta-serve-card">
                        <span class="ta-serve-drop" aria-hidden="true"></span>
                        <h3><?php echo esc_html($s[0]); ?></h3>
                        <p><?php echo esc_html($s[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== JOURNEY ===== -->
    <section class="ta-journey" aria-labelledby="ta-journey-title">
        <div class="eta-shell ta-journey-grid">
            <div class="ta-journey-copy">
                <p class="ta-kicker">How an engagement runs</p>
                <h2 id="ta-journey-title">Four steps from the finding you send us to the finding closed.</h2>
                <p>Share the audit finding, report, regulatory requirement, project stage, deadline and any existing evidence or corrective action record. The rest follows.</p>
                <a class="ta-btn ta-btn-solid" href="<?php echo esc_url($ta_contact); ?>">Send a finding <span aria-hidden="true">&rarr;</span></a>
            </div>
            <ol class="ta-journey-list">
                <svg class="ta-journey-line" viewBox="0 0 2 100" preserveAspectRatio="none" aria-hidden="true"><path d="M1 0 V100" pathLength="1"></path></svg>
                <?php foreach ($ta_journey as $i => $step) : ?>
                    <li data-ta-step>
                        <span class="ta-journey-dot"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <strong><?php echo esc_html($step[0]); ?></strong>
                        <p><?php echo esc_html($step[1]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ===== WHY ===== -->
    <section class="ta-why" aria-labelledby="ta-why-title">
        <div class="eta-shell">
            <header class="ta-head ta-head-light">
                <p class="ta-kicker">Why facilities choose this team</p>
                <h2 id="ta-why-title">Technical advice from the laboratory that produced the data, written as an action list.</h2>
            </header>
            <div class="ta-why-grid">
                <?php foreach ($ta_why as $w) : ?>
                    <article class="ta-why-card">
                        <h3><?php echo esc_html($w[0]); ?></h3>
                        <p><?php echo esc_html($w[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="ta-faq" aria-labelledby="ta-faq-title">
        <div class="eta-shell ta-faq-grid">
            <header class="ta-head ta-head-light">
                <p class="ta-kicker">Before you get in touch</p>
                <h2 id="ta-faq-title">Questions we are asked most.</h2>
            </header>
            <div class="ta-faq-list">
                <?php foreach ($ta_faqs as $i => $faq) : ?>
                    <details class="ta-faq-item"<?php echo $i === 0 ? ' open' : ''; ?>>
                        <summary><?php echo esc_html($faq[0]); ?><span class="ta-faq-icon" aria-hidden="true"></span></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== RELATED ===== -->
    <section class="ta-related" aria-labelledby="ta-related-title">
        <div class="eta-shell">
            <header class="ta-head ta-head-light">
                <p class="ta-kicker">Go deeper</p>
                <h2 id="ta-related-title">Related services, guides and the laboratory behind the data.</h2>
            </header>
            <div class="ta-related-grid">
                <?php foreach ($ta_related as $r) : ?>
                    <a class="ta-related-card" href="<?php echo esc_url(home_url($r[2])); ?>">
                        <strong><?php echo esc_html($r[0]); ?></strong>
                        <p><?php echo esc_html($r[1]); ?></p>
                        <span class="ta-inline-link">Open <i aria-hidden="true">&rarr;</i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FINAL ===== -->
    <section class="ta-final" aria-labelledby="ta-final-title">
        <div class="eta-shell ta-final-grid">
            <div>
                <p class="ta-kicker">A finding open, a notice received, or a report you cannot read?</p>
                <h2 id="ta-final-title">Send the finding, the requirement and the deadline. We confirm the review, the scope and a schedule.</h2>
            </div>
            <div class="ta-final-actions">
                <a class="ta-btn ta-btn-solid" href="<?php echo esc_url($ta_contact); ?>">Send a finding <span aria-hidden="true">&rarr;</span></a>
                <a class="ta-btn ta-btn-ghost" href="https://wa.me/923102288801" target="_blank" rel="noopener">WhatsApp consultation</a>
            </div>
        </div>
    </section>

</div>

<script data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1">
(function () {
    var d = document.documentElement;
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'noModule' in HTMLScriptElement.prototype) {
        d.classList.add('ta-gsap');
    }
    function f() { var h = document.getElementById('masthead'); d.style.setProperty('--eta-hh', (h ? h.offsetHeight : 0) + 'px'); }
    f(); addEventListener('resize', f); addEventListener('load', f);
})();
</script>
<script type="module" src="<?php echo esc_url($ta_theme_uri . '/eta-techadv-scene.js?v=' . (string) filemtime(get_stylesheet_directory() . '/eta-techadv-scene.js')); ?>" data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>
