<?php
/**
 * Flagship template: Thermal Imaging Inspection.
 *
 * Scroll-driven page on the fixed motion stack. The hero is a Three.js
 * thermogram: a switchboard in visible light that an infrared sweep resolves
 * into an ironbow heat map with blooming hotspots as the visitor scrolls.
 * GSAP + ScrollTrigger drive the pinned inspection sequence, the target
 * cards, the drawn workflow line and the reveals; Lenis smooths the scroll.
 * Without WebGL, with prefers-reduced-motion, or before the module runs, the
 * page is a complete static document.
 *
 * Variables available from single-services.php: $slug, $profile, $faqs,
 * $parameters, $process.
 */

if (!defined('ABSPATH')) {
    exit;
}

$ti_theme_uri = get_stylesheet_directory_uri();
$ti_contact   = home_url('/contact-us-envi-tech-al/');
$ti_verify    = home_url('/report-verification-portal/');
$ti_faqs      = eta_modern_service_faqs('thermal-imaging-inspection');

$ti_sequence = [
    ['Scope and access', 'What is inspected, and when', 'Panels, switchgear, motors, transformers, bearings and building envelope agreed with the maintenance team, with the survey timed for normal load so real thermal signatures show.', 'Under load'],
    ['Reference and conditions', 'Ambient, load and emissivity', 'Ambient temperature, load at the time of the scan, emissivity and reflected temperature recorded for each target, so the readings mean something.', 'Ambient · Load · Emissivity'],
    ['Thermal survey', 'Non-invasive, energised', 'Infrared imaging of energised equipment without shutdown or contact, each target captured with a visible-light reference image.', 'IR + visible'],
    ['Classification', 'Delta-T, not guesswork', 'Anomalies graded by temperature rise over a reference or ambient, against recognised severity bands, with the likely cause noted.', 'Severity bands'],
    ['Report', 'Image, reading, priority, action', 'Every anomaly reported with its thermogram, reference image, temperatures, severity and a maintenance recommendation the team can act on.', 'Actionable'],
    ['Follow-up', 'Closed and re-scanned', 'Re-inspection after repair to confirm the fault is cleared, and a baseline kept for the next survey.', 'Verified repair'],
];

$ti_targets = [
    ['LV', 'Low-voltage panels and distribution boards', 'Loose or corroded terminations, overloaded breakers, unbalanced phases and failing contacts at the busbar and outgoing ways.', ['Terminations', 'Breakers', 'Busbars'], 'Electrical'],
    ['HV', 'Switchgear and transformers', 'Connection heating, overloaded windings, cooling problems and bushing faults on medium-voltage switchgear and transformers.', ['Connections', 'Windings', 'Bushings'], 'Electrical'],
    ['MCC', 'Motor control centres and drives', 'Contactor and overload relay heating, cable lug faults and drive cooling issues on the panels that run the plant.', ['Contactors', 'Lugs', 'Drives'], 'Electrical'],
    ['MECH', 'Motors, bearings and couplings', 'Bearing wear, misalignment and lubrication problems showing as heat before they show as vibration or failure.', ['Bearings', 'Alignment', 'Lubrication'], 'Mechanical'],
    ['PROC', 'Process and steam systems', 'Steam trap performance, insulation loss, refractory wear and blocked lines on pipework, vessels and boilers.', ['Steam traps', 'Insulation', 'Refractory'], 'Process'],
    ['BLDG', 'Buildings and cold chain', 'Roof and envelope heat loss, moisture ingress, HVAC performance and cold-room and refrigerated storage integrity.', ['Envelope', 'HVAC', 'Cold rooms'], 'Facility'],
];

$ti_regs = [
    ['Delta-T severity bands', 'Anomalies graded by temperature rise over reference or ambient, following recognised infrared inspection practice'],
    ['NFPA 70B context', 'Electrical equipment maintenance guidance that calls for periodic infrared inspection'],
    ['Insurer requirements', 'Many property insurers ask for annual thermographic surveys of electrical installations'],
    ['ISO 45001 · ISO 14001', 'Inspection records that feed safety and environmental management systems'],
    ['Calibrated instruments', 'Thermal cameras with documented calibration; see our equipment calibration service'],
    ['Advisory findings', 'We report what the image shows and recommend; the facility decides and repairs'],
];

$ti_serves = [
    ['Maintenance and reliability teams', 'Faults found under load, ranked by severity, before the unplanned stop.'],
    ['Facility and building managers', 'Electrical rooms, HVAC and envelope surveyed without interrupting occupancy.'],
    ['Electrical departments', 'Panels, switchgear and MCCs checked energised, with images the team can act on.'],
    ['Industrial plants', 'Motors, bearings, steam and process systems in one survey programme.'],
    ['Insurers and auditors', 'Thermographic survey reports in the format a property insurer or safety auditor expects.'],
    ['Cold chain and pharma', 'Cold rooms, refrigerated storage and process cooling verified for integrity.'],
];

$ti_journey = [
    ['Scope', 'Confirm the equipment list, access, load conditions and the deadline; agree the survey window.'],
    ['Survey', 'Infrared and visible-light imaging of every target under load, with conditions recorded.'],
    ['Report', 'Anomalies classified by severity with temperatures, images and a maintenance recommendation for each.'],
    ['Follow up', 'Re-inspect after repair, confirm closure and keep the baseline for the next survey.'],
];

$ti_why = [
    ['Non-invasive, no shutdown', 'Energised equipment is surveyed in service, so the plant keeps running while the faults are found.'],
    ['Findings you can act on', 'Each anomaly comes with the image, the reading, the severity and the recommended action.'],
    ['Calibrated, documented', 'Instruments with documented calibration and conditions recorded for every target.'],
    ['Karachi and Lahore', 'Survey teams in both cities, scheduled around production.'],
];

$ti_related = [
    ['Equipment calibration', 'Calibrated instruments and records.', '/services/equipment-calibration-services/'],
    ['Environmental advisory', 'Improvement planning and audit response.', '/services/environmental-advisory/'],
    ['Certification advisory', 'ISO 45001 and ISO 14001 readiness.', '/services/certification-advisory/'],
    ['Analytical laboratory services', 'The environmental testing laboratory behind Envi Tech AL.', '/services/analytical-lab-services/'],
    ['Karachi environmental lab', 'LAB-285 and the field team behind it.', '/karachi-environmental-lab/'],
    ['Contact the team', 'Scope, schedule and quotation.', '/contact-us-envi-tech-al/'],
];
?>

<div id="eta-thermal" class="eta-thermal">

    <!-- ===== HERO: unsurveyed to mapped ===== -->
    <section class="ti-hero" aria-labelledby="ti-title">
        <div class="ti-stage">
            <canvas class="ti-gl" aria-hidden="true"></canvas>
            <div class="ti-veil" aria-hidden="true"></div>

            <div class="eta-shell ti-hero-grid">
                <div class="ti-hero-copy">
                    <p class="ti-eyebrow"><span>Thermal imaging inspection</span><i>·</i><span>Electrical · Mechanical · Facility</span><i>·</i><span>Karachi · Lahore</span></p>
                    <h1 id="ti-title" class="ti-title">
                        <span class="ti-title-line">See the fault</span>
                        <span class="ti-title-line"><em>before</em> it fails.</span>
                    </h1>
                    <p class="ti-lead">Infrared inspection of energised electrical, mechanical, process and building systems, carried out under load without shutdown, with every anomaly reported by image, temperature, severity and the maintenance action to take.</p>
                    <div class="ti-actions">
                        <a class="ti-btn ti-btn-solid" href="<?php echo esc_url($ti_contact); ?>">Book a thermal survey <span aria-hidden="true">&rarr;</span></a>
                        <a class="ti-btn ti-btn-ghost" href="#ti-deliver">What we inspect</a>
                    </div>
                </div>

                <aside class="ti-hero-panel" aria-label="Inspection targets">
                    <p class="ti-panel-kicker">Inspection targets</p>
                    <ul class="ti-panel-list">
                        <li><b>Panels</b><span>Switchgear, distribution boards, MCCs</span></li>
                        <li><b>Motors</b><span>Bearings, couplings, drives</span></li>
                        <li><b>Process</b><span>Steam, insulation, refractory</span></li>
                        <li><b>Buildings</b><span>Envelope, HVAC, cold rooms</span></li>
                    </ul>
                    <a class="ti-panel-link" href="<?php echo esc_url($ti_verify); ?>">Verify a laboratory report <span aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>

            <div class="ti-gauge" aria-hidden="true">
                <span class="ti-gauge-k">Hotspots found</span>
                <span class="ti-gauge-bar"><i></i></span>
                <span class="ti-gauge-v">0 of 4</span>
            </div>
            <p class="ti-cue" aria-hidden="true"><span></span>Scroll to run the infrared sweep</p>
        </div>
    </section>

    <!-- ===== LEDGER ===== -->
    <section class="ti-ledger" aria-label="Credentials">
        <div class="eta-shell ti-ledger-row">
            <div class="ti-ledger-item"><span class="ti-ledger-k">Method</span><strong>Non-invasive, under load</strong><span class="ti-ledger-s">Energised equipment surveyed in service, no shutdown</span></div>
            <div class="ti-ledger-item"><span class="ti-ledger-k">Grading</span><strong>Delta-T severity</strong><span class="ti-ledger-s">Anomalies ranked by temperature rise, with the likely cause</span></div>
            <div class="ti-ledger-item"><span class="ti-ledger-k">Instruments</span><strong>Calibrated cameras</strong><span class="ti-ledger-s">Documented calibration, conditions recorded per target</span></div>
            <div class="ti-ledger-item"><span class="ti-ledger-k">Coverage</span><strong>Karachi and Lahore</strong><span class="ti-ledger-s">Survey teams scheduled around production</span></div>
        </div>
    </section>

    <!-- ===== PATHWAY: pinned track ===== -->
    <section class="ti-types" aria-labelledby="ti-types-title">
        <div class="ti-types-pin">
            <div class="eta-shell ti-types-head">
                <div>
                    <p class="ti-kicker">The inspection sequence</p>
                    <h2 id="ti-types-title">Six steps from the equipment list to a fault found, fixed and re-checked.</h2>
                </div>
                <p class="ti-types-progress" aria-hidden="true"><span class="ti-types-bar"><i></i></span><span class="ti-types-count">01 / 06</span></p>
            </div>
            <div class="ti-track" data-ti-track>
                <?php foreach ($ti_sequence as $i => $t) : ?>
                    <article class="ti-type-card" data-ti-card>
                        <div class="ti-type-top"><span class="ti-type-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span><span class="ti-type-std"><?php echo esc_html($t[3]); ?></span></div>
                        <h3><?php echo esc_html($t[0]); ?></h3>
                        <p class="ti-type-sub"><?php echo esc_html($t[1]); ?></p>
                        <p class="ti-type-body"><?php echo esc_html($t[2]); ?></p>
                        <span class="ti-type-wave" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== DELIVERABLES ===== -->
    <section id="ti-deliver" class="ti-deliver" aria-labelledby="ti-deliver-title">
        <div class="eta-shell">
            <header class="ti-head">
                <p class="ti-kicker">What we inspect</p>
                <h2 id="ti-deliver-title">Six families of equipment, each with its own thermal signature of trouble.</h2>
                <p>Heat is the earliest symptom most faults give. The survey captures it under normal load, with a visible-light reference image for every thermogram.</p>
            </header>
            <div class="ti-deliver-grid">
                <?php foreach ($ti_targets as $d) : ?>
                    <article class="ti-deliver-card" data-ti-tilt>
                        <div class="ti-deliver-top"><span class="ti-deliver-code"><?php echo esc_html($d[0]); ?></span><span class="ti-deliver-std"><?php echo esc_html($d[4]); ?></span></div>
                        <h3><?php echo esc_html($d[1]); ?></h3>
                        <p><?php echo esc_html($d[2]); ?></p>
                        <ul class="ti-chips">
                            <?php foreach ($d[3] as $chip) : ?><li><?php echo esc_html($chip); ?></li><?php endforeach; ?>
                        </ul>
                        <span class="ti-deliver-glow" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- ===== REGULATORS ===== -->
    <section class="ti-regs" aria-labelledby="ti-regs-title">
        <div class="eta-shell ti-regs-grid">
            <div class="ti-regs-copy">
                <p class="ti-kicker">Practice and references</p>
                <h2 id="ti-regs-title">Graded to recognised practice, documented for the people who ask for it.</h2>
                <p>Severity is judged on temperature rise, not on colour alone. The report is written for the maintenance team first, and for the insurer or auditor who may ask for it next.</p>
            </div>
            <ul class="ti-regs-list">
                <?php foreach ($ti_regs as $r) : ?>
                    <li><b><?php echo esc_html($r[0]); ?></b><span><?php echo esc_html($r[1]); ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ===== WHO IT SERVES ===== -->
    <section class="ti-serves" aria-labelledby="ti-serves-title">
        <div class="eta-shell">
            <header class="ti-head ti-head-light">
                <p class="ti-kicker">Who relies on it</p>
                <h2 id="ti-serves-title">Anyone who would rather find a hot connection on a Tuesday than a failed one on a Sunday.</h2>
            </header>
            <div class="ti-serves-grid">
                <?php foreach ($ti_serves as $s) : ?>
                    <article class="ti-serve-card">
                        <span class="ti-serve-drop" aria-hidden="true"></span>
                        <h3><?php echo esc_html($s[0]); ?></h3>
                        <p><?php echo esc_html($s[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== JOURNEY ===== -->
    <section class="ti-journey" aria-labelledby="ti-journey-title">
        <div class="eta-shell ti-journey-grid">
            <div class="ti-journey-copy">
                <p class="ti-kicker">How a survey runs</p>
                <h2 id="ti-journey-title">Four steps from the equipment list to a closed finding.</h2>
                <p>Share the equipment list, the site, access and load conditions and the date you need the report by. The rest follows.</p>
                <a class="ti-btn ti-btn-solid" href="<?php echo esc_url($ti_contact); ?>">Book a thermal survey <span aria-hidden="true">&rarr;</span></a>
            </div>
            <ol class="ti-journey-list">
                <svg class="ti-journey-line" viewBox="0 0 2 100" preserveAspectRatio="none" aria-hidden="true"><path d="M1 0 V100" pathLength="1"></path></svg>
                <?php foreach ($ti_journey as $i => $step) : ?>
                    <li data-ti-step>
                        <span class="ti-journey-dot"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <strong><?php echo esc_html($step[0]); ?></strong>
                        <p><?php echo esc_html($step[1]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ===== WHY ===== -->
    <section class="ti-why" aria-labelledby="ti-why-title">
        <div class="eta-shell">
            <header class="ti-head ti-head-light">
                <p class="ti-kicker">Why plants choose this team</p>
                <h2 id="ti-why-title">Inspection that finds the fault, explains it and comes back to confirm it is gone.</h2>
            </header>
            <div class="ti-why-grid">
                <?php foreach ($ti_why as $w) : ?>
                    <article class="ti-why-card">
                        <h3><?php echo esc_html($w[0]); ?></h3>
                        <p><?php echo esc_html($w[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="ti-faq" aria-labelledby="ti-faq-title">
        <div class="eta-shell ti-faq-grid">
            <header class="ti-head ti-head-light">
                <p class="ti-kicker">Before you book a survey</p>
                <h2 id="ti-faq-title">Questions we are asked most.</h2>
            </header>
            <div class="ti-faq-list">
                <?php foreach ($ti_faqs as $i => $faq) : ?>
                    <details class="ti-faq-item"<?php echo $i === 0 ? ' open' : ''; ?>>
                        <summary><?php echo esc_html($faq[0]); ?><span class="ti-faq-icon" aria-hidden="true"></span></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== RELATED ===== -->
    <section class="ti-related" aria-labelledby="ti-related-title">
        <div class="eta-shell">
            <header class="ti-head ti-head-light">
                <p class="ti-kicker">Go deeper</p>
                <h2 id="ti-related-title">Related services and the team behind the camera.</h2>
            </header>
            <div class="ti-related-grid">
                <?php foreach ($ti_related as $r) : ?>
                    <a class="ti-related-card" href="<?php echo esc_url(home_url($r[2])); ?>">
                        <strong><?php echo esc_html($r[0]); ?></strong>
                        <p><?php echo esc_html($r[1]); ?></p>
                        <span class="ti-inline-link">Open <i aria-hidden="true">&rarr;</i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FINAL ===== -->
    <section class="ti-final" aria-labelledby="ti-final-title">
        <div class="eta-shell ti-final-grid">
            <div>
                <p class="ti-kicker">A shutdown coming, an insurer asking, or a panel running warm?</p>
                <h2 id="ti-final-title">Send the equipment list, the site and the date. We confirm scope, the survey window and the report format.</h2>
            </div>
            <div class="ti-final-actions">
                <a class="ti-btn ti-btn-solid" href="<?php echo esc_url($ti_contact); ?>">Book a thermal survey <span aria-hidden="true">&rarr;</span></a>
                <a class="ti-btn ti-btn-ghost" href="https://wa.me/923102288801" target="_blank" rel="noopener">WhatsApp consultation</a>
            </div>
        </div>
    </section>

</div>

<script data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1">
(function () {
    var d = document.documentElement;
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'noModule' in HTMLScriptElement.prototype) {
        d.classList.add('ti-gsap');
    }
    function f() { var h = document.getElementById('masthead'); d.style.setProperty('--eta-hh', (h ? h.offsetHeight : 0) + 'px'); }
    f(); addEventListener('resize', f); addEventListener('load', f);
})();
</script>
<script type="module" src="<?php echo esc_url($ti_theme_uri . '/eta-thermal-scene.js?v=' . (string) filemtime(get_stylesheet_directory() . '/eta-thermal-scene.js')); ?>" data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>
