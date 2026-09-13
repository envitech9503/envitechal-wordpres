<?php
/**
 * Flagship template: Ballast Water Testing Services.
 *
 * Scroll-driven page on the fixed motion stack. The hero is a Three.js
 * ballast tank: a swarm of organisms that is counted into the D-2 size bands
 * and indicator microbes as the visitor scrolls. GSAP + ScrollTrigger drive
 * the pinned port-call sequence, the analysis cards, the drawn workflow line
 * and the reveals; Lenis smooths the scroll.
 * Without WebGL, with prefers-reduced-motion, or before the module runs, the
 * page is a complete static document.
 *
 * Variables available from single-services.php: $slug, $profile, $faqs,
 * $parameters, $process.
 */

if (!defined('ABSPATH')) {
    exit;
}

$bw_theme_uri = get_stylesheet_directory_uri();
$bw_contact   = home_url('/contact-us-envi-tech-al/');
$bw_verify    = home_url('/report-verification-portal/');
$bw_faqs      = eta_modern_service_faqs('ballast-water-testing-services');

$bw_sequence = [
    ['Port-call notice', 'The clock starts here', 'Vessel name, ETA, berth, agent and the reason for testing: commissioning, compliance check, Port State Control request or an operator programme. We confirm scope and timing the same day.', 'Agent · Operator'],
    ['Sampling point access', 'In-line or tank', 'Confirm the sampling point (in-line during discharge, or direct tank sampling), safe access, the BWMS status and the volumes needed for representative samples.', 'BWMS · Sampling point'],
    ['Representative sampling', 'Collected to the guidance', 'Samples taken by our team with the correct containers, preservation, timing across the discharge and chain-of-custody records, ready for indicative or detailed analysis.', 'Chain of custody'],
    ['Indicative analysis', 'Fast first answer', 'Rapid screening of the organism size bands so a result is available while the vessel is still in port, where the purpose calls for it.', 'Size bands'],
    ['Detailed analysis', 'The laboratory count', 'Enumeration of viable organisms in the 50 µm and above and 10 to 50 µm bands, and the indicator microbes, at the Karachi laboratory within the confirmed scope.', 'LAB-285 Karachi'],
    ['Report and evidence', 'Read against D-2', 'Results stated against the IMO D-2 limits in a report the operator, class, flag or inspecting authority can read, and verify online by number and date.', 'D-2 · Verifiable'],
];

$bw_analysis = [
    ['≥50', 'Organisms of 50 µm and above', 'Viable organisms per cubic metre, counted against the D-2 limit of fewer than 10 per m³.', ['Viable count', 'per m³', 'D-2 limit 10'], 'Size band 1'],
    ['10–50', 'Organisms of 10 to 50 µm', 'Viable organisms per millilitre, counted against the D-2 limit of fewer than 10 per mL.', ['Viable count', 'per mL', 'D-2 limit 10'], 'Size band 2'],
    ['Vc', 'Toxicogenic Vibrio cholerae', 'Serotypes O1 and O139, reported against the D-2 limit of fewer than 1 cfu per 100 mL.', ['O1 / O139', 'cfu per 100 mL'], 'Indicator microbe'],
    ['Ec', 'Escherichia coli', 'Reported against the D-2 limit of fewer than 250 cfu per 100 mL.', ['cfu per 100 mL', 'D-2 limit 250'], 'Indicator microbe'],
    ['IE', 'Intestinal enterococci', 'Reported against the D-2 limit of fewer than 100 cfu per 100 mL.', ['cfu per 100 mL', 'D-2 limit 100'], 'Indicator microbe'],
    ['PC', 'Physico-chemical context', 'Salinity, temperature, turbidity and residual oxidant where the treatment system or the purpose requires them.', ['Salinity', 'Temperature', 'TRO'], 'Supporting parameters'],
];

$bw_regs = [
    ['IMO BWM Convention', 'The Ballast Water Management Convention that sets the obligation to manage ballast water'],
    ['Regulation D-2', 'The performance standard the results are read against: organism and indicator microbe limits'],
    ['BWM.2/Circ.70', 'IMO guidance for commissioning testing of ballast water management systems'],
    ['Port State Control', 'Sampling and analysis in support of an inspection or a compliance request'],
    ['Flag and class', 'Reporting in the format the vessel\'s flag administration or classification society expects'],
    ['PNAC ISO/IEC 17025', 'LAB-285 Karachi; methods applied within the confirmed scope for the sample'],
];

$bw_serves = [
    ['Vessel operators and managers', 'Commissioning tests and periodic checks planned around the schedule, not against it.'],
    ['Shipping agents', 'One call to arrange sampling, analysis and the report before the vessel sails.'],
    ['BWMS suppliers and installers', 'Commissioning test sampling and analysis coordinated with the installation team.'],
    ['Port authorities and terminals', 'Analysis support for compliance requests at Karachi and Port Qasim.'],
    ['Classification and flag surveyors', 'Independent laboratory results to accompany the survey.'],
    ['Marine compliance teams', 'A repeatable programme with verifiable reports for the fleet file.'],
];

$bw_journey = [
    ['Notify and scope', 'Share vessel schedule, port-call timing, the reason for testing, the sampling point and the reporting deadline.'],
    ['Sample', 'Our team attends the vessel with the containers, preservation and records the guidance requires.'],
    ['Analyse', 'Indicative screening where the purpose calls for it, and detailed analysis at the Karachi laboratory within the confirmed scope.'],
    ['Report', 'Results stated against the D-2 limits, issued to the operator, agent or authority, verifiable online.'],
];

$bw_why = [
    ['Port-side response', 'A Karachi laboratory and field team within reach of Karachi Port and Port Qasim, so port calls are not held for sampling.'],
    ['Read against the standard', 'Each result stated against its D-2 limit, in a report a surveyor or inspector can read at a glance.'],
    ['Scope confirmed first', 'Sampling method, analysis and credential status are confirmed before the team attends. No surprises in the report.'],
    ['Verifiable', 'Reports carrying verification details can be checked online by number and issue date.'],
];

$bw_related = [
    ['Ballast water testing in Karachi', 'Port-call planning and what to send before arrival.', '/ballast-water-testing-services-karachi/'],
    ['Water testing laboratory', 'Drinking water, wastewater and process water analysis.', '/services/water-testing-lab-services/'],
    ['Analytical laboratory services', 'The environmental testing laboratory behind the count.', '/services/analytical-lab-services/'],
    ['Karachi environmental lab', 'LAB-285 and the field team behind it.', '/karachi-environmental-lab/'],
    ['Verify a report', 'Check a laboratory report by number and date.', '/report-verification-portal/'],
    ['Environmental consultancy', 'Regulatory pathways for shore-side facilities.', '/services/environmental-consultancy/'],
];
?>

<div id="eta-ballast" class="eta-ballast">

    <!-- ===== HERO: unsurveyed to mapped ===== -->
    <section class="bw-hero" aria-labelledby="bw-title">
        <div class="bw-stage">
            <canvas class="bw-gl" aria-hidden="true"></canvas>
            <div class="bw-veil" aria-hidden="true"></div>

            <div class="eta-shell bw-hero-grid">
                <div class="bw-hero-copy">
                    <p class="bw-eyebrow"><span>Ballast water testing</span><i>·</i><span>Karachi Port</span><i>·</i><span>Port Qasim</span></p>
                    <h1 id="bw-title" class="bw-title">
                        <span class="bw-title-line">Sampled at the berth,</span>
                        <span class="bw-title-line"><em>counted</em> against D-2.</span>
                    </h1>
                    <p class="bw-lead">Ballast and deballast water sampling and analysis for vessels, agents and operators calling at Karachi: organism size bands and indicator microbes read against the IMO D-2 standard, in a report that keeps pace with the port call.</p>
                    <div class="bw-actions">
                        <a class="bw-btn bw-btn-solid" href="<?php echo esc_url($bw_contact); ?>">Arrange a sampling <span aria-hidden="true">&rarr;</span></a>
                        <a class="bw-btn bw-btn-ghost" href="#bw-deliver">What we analyse</a>
                    </div>
                </div>

                <aside class="bw-hero-panel" aria-label="D-2 limits">
                    <p class="bw-panel-kicker">Read against the D-2 standard</p>
                    <ul class="bw-panel-list">
                        <li><b>&ge;50 µm</b><span>Fewer than 10 viable organisms per m³</span></li>
                        <li><b>10–50 µm</b><span>Fewer than 10 viable organisms per mL</span></li>
                        <li><b>Microbes</b><span>V. cholerae, E. coli, intestinal enterococci</span></li>
                        <li><b>Purpose</b><span>Commissioning, compliance, PSC request</span></li>
                    </ul>
                    <a class="bw-panel-link" href="<?php echo esc_url($bw_verify); ?>">Verify a laboratory report <span aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>

            <div class="bw-gauge" aria-hidden="true">
                <span class="bw-gauge-k">Sample counted</span>
                <span class="bw-gauge-bar"><i></i></span>
                <span class="bw-gauge-v">0%</span>
            </div>
            <p class="bw-cue" aria-hidden="true"><span></span>Scroll to count the sample</p>
        </div>
    </section>

    <!-- ===== LEDGER ===== -->
    <section class="bw-ledger" aria-label="Credentials">
        <div class="eta-shell bw-ledger-row">
            <div class="bw-ledger-item"><span class="bw-ledger-k">Standard</span><strong>IMO D-2</strong><span class="bw-ledger-s">Organism size bands and indicator microbes, stated against the limits</span></div>
            <div class="bw-ledger-item"><span class="bw-ledger-k">Laboratory</span><strong>PNAC LAB-285</strong><span class="bw-ledger-s">ISO/IEC 17025, Karachi; methods within the confirmed scope</span></div>
            <div class="bw-ledger-item"><span class="bw-ledger-k">Ports</span><strong>Karachi and Port Qasim</strong><span class="bw-ledger-s">Field sampling team within reach of the berth</span></div>
            <div class="bw-ledger-item"><span class="bw-ledger-k">Every report</span><strong>Verifiable online</strong><span class="bw-ledger-s">By report number and issue date</span></div>
        </div>
    </section>

    <!-- ===== PATHWAY: pinned track ===== -->
    <section class="bw-types" aria-labelledby="bw-types-title">
        <div class="bw-types-pin">
            <div class="eta-shell bw-types-head">
                <div>
                    <p class="bw-kicker">The port-call sequence</p>
                    <h2 id="bw-types-title">Six steps between the arrival notice and a report the inspector can read.</h2>
                </div>
                <p class="bw-types-progress" aria-hidden="true"><span class="bw-types-bar"><i></i></span><span class="bw-types-count">01 / 06</span></p>
            </div>
            <div class="bw-track" data-bw-track>
                <?php foreach ($bw_sequence as $i => $t) : ?>
                    <article class="bw-type-card" data-bw-card>
                        <div class="bw-type-top"><span class="bw-type-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span><span class="bw-type-std"><?php echo esc_html($t[3]); ?></span></div>
                        <h3><?php echo esc_html($t[0]); ?></h3>
                        <p class="bw-type-sub"><?php echo esc_html($t[1]); ?></p>
                        <p class="bw-type-body"><?php echo esc_html($t[2]); ?></p>
                        <span class="bw-type-wave" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== DELIVERABLES ===== -->
    <section id="bw-deliver" class="bw-deliver" aria-labelledby="bw-deliver-title">
        <div class="eta-shell">
            <header class="bw-head">
                <p class="bw-kicker">What we analyse</p>
                <h2 id="bw-deliver-title">The D-2 parameters, each stated against its limit.</h2>
                <p>The IMO D-2 performance standard sets limits for two organism size bands and three indicator microbes. The report states each result against the limit that applies; the analysis is carried out within the scope confirmed before sampling.</p>
            </header>
            <div class="bw-deliver-grid">
                <?php foreach ($bw_analysis as $d) : ?>
                    <article class="bw-deliver-card" data-bw-tilt>
                        <div class="bw-deliver-top"><span class="bw-deliver-code"><?php echo esc_html($d[0]); ?></span><span class="bw-deliver-std"><?php echo esc_html($d[4]); ?></span></div>
                        <h3><?php echo esc_html($d[1]); ?></h3>
                        <p><?php echo esc_html($d[2]); ?></p>
                        <ul class="bw-chips">
                            <?php foreach ($d[3] as $chip) : ?><li><?php echo esc_html($chip); ?></li><?php endforeach; ?>
                        </ul>
                        <span class="bw-deliver-glow" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== REGULATORS ===== -->
    <section class="bw-regs" aria-labelledby="bw-regs-title">
        <div class="eta-shell bw-regs-grid">
            <div class="bw-regs-copy">
                <p class="bw-kicker">Conventions and authorities</p>
                <h2 id="bw-regs-title">Sampling and analysis that follow the convention, the guidance and the people who inspect.</h2>
                <p>We name the instruments exactly and work to them. Compliance determinations rest with the inspecting authority, the flag and the class; our work is the sample, the count and the report.</p>
            </div>
            <ul class="bw-regs-list">
                <?php foreach ($bw_regs as $r) : ?>
                    <li><b><?php echo esc_html($r[0]); ?></b><span><?php echo esc_html($r[1]); ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ===== WHO IT SERVES ===== -->
    <section class="bw-serves" aria-labelledby="bw-serves-title">
        <div class="eta-shell">
            <header class="bw-head bw-head-light">
                <p class="bw-kicker">Who relies on it</p>
                <h2 id="bw-serves-title">Everyone with a vessel, a berth or a survey that depends on the result.</h2>
            </header>
            <div class="bw-serves-grid">
                <?php foreach ($bw_serves as $s) : ?>
                    <article class="bw-serve-card">
                        <span class="bw-serve-drop" aria-hidden="true"></span>
                        <h3><?php echo esc_html($s[0]); ?></h3>
                        <p><?php echo esc_html($s[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== JOURNEY ===== -->
    <section class="bw-journey" aria-labelledby="bw-journey-title">
        <div class="eta-shell bw-journey-grid">
            <div class="bw-journey-copy">
                <p class="bw-kicker">How a port call runs</p>
                <h2 id="bw-journey-title">Four steps from the arrival notice to a verifiable report.</h2>
                <p>Share the vessel schedule, port-call timing, sample requirement, reporting deadline and any inspection or compliance context. The rest follows.</p>
                <a class="bw-btn bw-btn-solid" href="<?php echo esc_url($bw_contact); ?>">Send the port-call details <span aria-hidden="true">&rarr;</span></a>
            </div>
            <ol class="bw-journey-list">
                <svg class="bw-journey-line" viewBox="0 0 2 100" preserveAspectRatio="none" aria-hidden="true"><path d="M1 0 V100" pathLength="1"></path></svg>
                <?php foreach ($bw_journey as $i => $step) : ?>
                    <li data-bw-step>
                        <span class="bw-journey-dot"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <strong><?php echo esc_html($step[0]); ?></strong>
                        <p><?php echo esc_html($step[1]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ===== WHY ===== -->
    <section class="bw-why" aria-labelledby="bw-why-title">
        <div class="eta-shell">
            <header class="bw-head bw-head-light">
                <p class="bw-kicker">Why operators and agents choose this laboratory</p>
                <h2 id="bw-why-title">A port-side laboratory that reads the result the way the inspector will.</h2>
            </header>
            <div class="bw-why-grid">
                <?php foreach ($bw_why as $w) : ?>
                    <article class="bw-why-card">
                        <h3><?php echo esc_html($w[0]); ?></h3>
                        <p><?php echo esc_html($w[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="bw-faq" aria-labelledby="bw-faq-title">
        <div class="eta-shell bw-faq-grid">
            <header class="bw-head bw-head-light">
                <p class="bw-kicker">Before the vessel arrives</p>
                <h2 id="bw-faq-title">Questions we are asked most.</h2>
            </header>
            <div class="bw-faq-list">
                <?php foreach ($bw_faqs as $i => $faq) : ?>
                    <details class="bw-faq-item"<?php echo $i === 0 ? ' open' : ''; ?>>
                        <summary><?php echo esc_html($faq[0]); ?><span class="bw-faq-icon" aria-hidden="true"></span></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== RELATED ===== -->
    <section class="bw-related" aria-labelledby="bw-related-title">
        <div class="eta-shell">
            <header class="bw-head bw-head-light">
                <p class="bw-kicker">Go deeper</p>
                <h2 id="bw-related-title">Guides, the laboratory and the services around the port call.</h2>
            </header>
            <div class="bw-related-grid">
                <?php foreach ($bw_related as $r) : ?>
                    <a class="bw-related-card" href="<?php echo esc_url(home_url($r[2])); ?>">
                        <strong><?php echo esc_html($r[0]); ?></strong>
                        <p><?php echo esc_html($r[1]); ?></p>
                        <span class="bw-inline-link">Open <i aria-hidden="true">&rarr;</i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FINAL ===== -->
    <section class="bw-final" aria-labelledby="bw-final-title">
        <div class="eta-shell bw-final-grid">
            <div>
                <p class="bw-kicker">A vessel arriving, or a commissioning test to schedule?</p>
                <h2 id="bw-final-title">Send the vessel name, ETA, berth and the reason for testing. We confirm scope, sampling time and the reporting deadline.</h2>
            </div>
            <div class="bw-final-actions">
                <a class="bw-btn bw-btn-solid" href="<?php echo esc_url($bw_contact); ?>">Arrange a sampling <span aria-hidden="true">&rarr;</span></a>
                <a class="bw-btn bw-btn-ghost" href="https://wa.me/923102288801" target="_blank" rel="noopener">WhatsApp consultation</a>
            </div>
        </div>
    </section>

</div>

<script data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1">
(function () {
    var d = document.documentElement;
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'noModule' in HTMLScriptElement.prototype) {
        d.classList.add('bw-gsap');
    }
    function f() { var h = document.getElementById('masthead'); d.style.setProperty('--eta-hh', (h ? h.offsetHeight : 0) + 'px'); }
    f(); addEventListener('resize', f); addEventListener('load', f);
})();
</script>
<script type="module" src="<?php echo esc_url($bw_theme_uri . '/eta-ballast-scene.js?v=' . (string) filemtime(get_stylesheet_directory() . '/eta-ballast-scene.js')); ?>" data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>
