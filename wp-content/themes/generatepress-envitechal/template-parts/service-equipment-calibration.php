<?php
/**
 * Flagship template: Equipment Calibration Services.
 *
 * Scroll-driven page on the fixed motion stack. The hero is a Three.js
 * instrument face: scattered, drifting readings converge on the reference,
 * the needle settles to zero and the tolerance band narrows as the visitor
 * scrolls. GSAP + ScrollTrigger drive the pinned instrument-family track, the
 * certificate cards, the drawn workflow line and the reveals; Lenis smooths.
 * Without WebGL, with prefers-reduced-motion, or before the module runs, the
 * page is a complete static document.
 *
 * Variables available from single-services.php: $slug, $profile, $faqs,
 * $parameters, $process.
 */

if (!defined('ABSPATH')) {
    exit;
}

$cal_theme_uri = get_stylesheet_directory_uri();
$cal_contact   = home_url('/contact-us-envi-tech-al/');
$cal_verify    = home_url('/report-verification-portal/');
$cal_faqs      = eta_modern_service_faqs('equipment-calibration-services');

$cal_families = [
    ['Balances and mass', 'Weighing you can defend', 'Analytical and precision balances, platform scales and weights checked across the working range against reference masses, with as-found and as-left results.', 'Analytical · Precision · Platform'],
    ['Temperature', 'Ovens, baths and probes', 'Thermometers, data loggers, incubators, ovens, water baths and refrigerators compared to a reference at the points you actually use.', 'Probes · Chambers · Loggers'],
    ['pH and conductivity', 'The electrochemistry bench', 'pH meters, conductivity and TDS meters and dissolved-oxygen meters verified with reference solutions, slope and offset recorded.', 'pH · EC · DO'],
    ['Pressure and flow', 'Gauges and meters in service', 'Pressure gauges, transmitters, rotameters and flow meters checked in range so process readings and sampling volumes hold up.', 'Gauges · Transmitters · Flow'],
    ['Volume and dispensing', 'Pipettes and glassware', 'Micropipettes, burettes and volumetric glassware verified gravimetrically, the quiet source of most analytical error.', 'Pipettes · Burettes · Flasks'],
    ['Environmental instruments', 'The monitoring kit', 'Sound level meters, air samplers, lux meters, gas detectors and particulate monitors checked before the field campaign that depends on them.', 'Noise · Air · Light · Gas'],
];

$cal_certificate = [
    ['ID', 'Instrument identification', 'Make, model, serial number, location and the range and resolution the check covers, so the certificate maps to one instrument.', ['Serial and tag', 'Range', 'Resolution'], 'On every certificate'],
    ['REF', 'Reference and traceability', 'The reference standard used and its own calibration status, so the chain back to national or international references is documented.', ['Reference ID', 'Traceability chain', 'Validity'], 'Documented'],
    ['A/F', 'As-found and as-left', 'Readings before and after any adjustment at each test point, so drift since the last calibration is visible.', ['Test points', 'Before', 'After'], 'Per test point'],
    ['U', 'Measurement uncertainty', 'The uncertainty attached to each result, stated so the reader can judge conformity against the acceptance criteria.', ['Expanded uncertainty', 'Coverage factor'], 'Where required'],
    ['ENV', 'Conditions and method', 'Ambient temperature and humidity at the time of the check, the procedure followed and who performed it.', ['Temperature', 'Humidity', 'Procedure'], 'Recorded'],
    ['DUE', 'Status and next due', 'Conformity statement where requested, the calibration date and the recommended next due date for the interval plan.', ['Conformity', 'Date', 'Next due'], 'Interval planning'],
];

$cal_regs = [
    ['ISO/IEC 17025', 'The laboratory standard our own testing operates under; calibration records are prepared to the same discipline'],
    ['ISO 9001:2015', 'Quality management system context for controlled calibration records and intervals'],
    ['Traceability', 'Reference standards with documented traceability to national or international references'],
    ['Manufacturer specification', 'Tolerances taken from the instrument specification unless you set tighter ones'],
    ['Client acceptance criteria', 'Your own limits applied and stated, where you provide them'],
    ['Audit readiness', 'Certificates and intervals kept so an auditor can follow the chain without asking'],
];

$cal_serves = [
    ['Testing laboratories', 'Balances, ovens, pH meters and pipettes kept within tolerance between accreditation visits.'],
    ['Manufacturing and process plants', 'Gauges, meters and scales in service, checked without stopping the line for long.'],
    ['Pharmaceutical and food units', 'Instrument records that satisfy GMP, HACCP and buyer audit expectations.'],
    ['EHS and monitoring teams', 'Field instruments verified before the campaign, not after the query.'],
    ['Quality departments', 'One calibration schedule, one set of certificates, one place to find them.'],
    ['Hospitals and institutions', 'Refrigerators, incubators and thermometers with a documented history.'],
];

$cal_journey = [
    ['Scope', 'Confirm instrument type, range, location, certificate need, acceptance criteria and timeline.'],
    ['Reference and method', 'Select the reference standard and procedure for the instrument and range, and confirm on-site or laboratory handling.'],
    ['Check and record', 'Carry out the calibration with controlled identification, as-found and as-left readings and the ambient conditions logged.'],
    ['Certificate and interval', 'Issue the documentation that supports measurement confidence and audit review, with the next due date for your schedule.'],
];

$cal_why = [
    ['A laboratory that lives by its own instruments', 'The same discipline that keeps LAB-285 and LAB-347 within their PNAC scopes is applied to your equipment.'],
    ['On-site where it matters', 'Field teams in Karachi and Lahore for instruments that cannot travel, laboratory handling for those that can.'],
    ['Records an auditor can follow', 'Identification, reference, readings, uncertainty and due date on one certificate.'],
    ['Honest scope', 'We state what was checked, against what, and to what uncertainty. Nothing more is implied.'],
];

$cal_related = [
    ['Karachi environmental lab', 'The laboratory behind the references and the field team.', '/karachi-environmental-lab/'],
    ['Analytical laboratory services', 'The environmental testing laboratory behind the references.', '/services/analytical-lab-services/'],
    ['Water testing laboratory', 'Drinking water, wastewater and process water analysis.', '/services/water-testing-lab-services/'],
    ['Certification advisory', 'ISO 9001, ISO 14001 and system preparation.', '/services/certification-advisory/'],
    ['Downloads', 'Forms, profiles and reference documents.', '/downloads/'],
    ['Verify a report', 'Check a laboratory report by number and date.', '/report-verification-portal/'],
];
?>

<div id="eta-cal" class="eta-cal">

    <!-- ===== HERO: unsurveyed to mapped ===== -->
    <section class="cal-hero" aria-labelledby="cal-title">
        <div class="cal-stage">
            <canvas class="cal-gl" aria-hidden="true"></canvas>
            <div class="cal-veil" aria-hidden="true"></div>

            <div class="eta-shell cal-hero-grid">
                <div class="cal-hero-copy">
                    <p class="cal-eyebrow"><span>Equipment calibration</span><i>·</i><span>Karachi</span><i>·</i><span>Lahore</span></p>
                    <h1 id="cal-title" class="cal-title">
                        <span class="cal-title-line">Every reading,</span>
                        <span class="cal-title-line"><em>traceable</em> to a reference.</span>
                    </h1>
                    <p class="cal-lead">Calibration and verification of laboratory and industrial instruments, with certificates that carry the reference, the readings and the uncertainty, so measurement systems stay reliable, traceable and audit-ready.</p>
                    <div class="cal-actions">
                        <a class="cal-btn cal-btn-solid" href="<?php echo esc_url($cal_contact); ?>">Request a calibration <span aria-hidden="true">&rarr;</span></a>
                        <a class="cal-btn cal-btn-ghost" href="#cal-deliver">What the certificate carries</a>
                    </div>
                </div>

                <aside class="cal-hero-panel" aria-label="Instrument families">
                    <p class="cal-panel-kicker">Instruments we calibrate</p>
                    <ul class="cal-panel-list">
                        <li><b>Mass</b><span>Balances, scales and weights</span></li>
                        <li><b>Temp</b><span>Thermometers, ovens, baths, loggers</span></li>
                        <li><b>pH/EC</b><span>pH, conductivity and DO meters</span></li>
                        <li><b>Field</b><span>Noise, air, light and gas instruments</span></li>
                    </ul>
                    <a class="cal-panel-link" href="<?php echo esc_url($cal_verify); ?>">Verify a laboratory report <span aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>

            <div class="cal-gauge" aria-hidden="true">
                <span class="cal-gauge-k">Deviation</span>
                <span class="cal-gauge-bar"><i></i></span>
                <span class="cal-gauge-v">&plusmn;2.40 %</span>
            </div>
            <p class="cal-cue" aria-hidden="true"><span></span>Scroll to bring it into calibration</p>
        </div>
    </section>

    <!-- ===== LEDGER ===== -->
    <section class="cal-ledger" aria-label="Credentials">
        <div class="eta-shell cal-ledger-row">
            <div class="cal-ledger-item"><span class="cal-ledger-k">Traceability</span><strong>Documented references</strong><span class="cal-ledger-s">Reference standards with their own calibration status recorded</span></div>
            <div class="cal-ledger-item"><span class="cal-ledger-k">Certificate</span><strong>As-found, as-left</strong><span class="cal-ledger-s">Readings, uncertainty, conditions and next due date</span></div>
            <div class="cal-ledger-item"><span class="cal-ledger-k">Coverage</span><strong>Karachi and Lahore</strong><span class="cal-ledger-s">On-site visits and laboratory handling</span></div>
            <div class="cal-ledger-item"><span class="cal-ledger-k">Discipline</span><strong>ISO/IEC 17025 laboratory</strong><span class="cal-ledger-s">The same controls that keep LAB-285 and LAB-347 in scope</span></div>
        </div>
    </section>

    <!-- ===== PATHWAY: pinned track ===== -->
    <section class="cal-types" aria-labelledby="cal-types-title">
        <div class="cal-types-pin">
            <div class="eta-shell cal-types-head">
                <div>
                    <p class="cal-kicker">Six instrument families</p>
                    <h2 id="cal-types-title">From the analytical balance to the sound level meter, checked where you use them.</h2>
                </div>
                <p class="cal-types-progress" aria-hidden="true"><span class="cal-types-bar"><i></i></span><span class="cal-types-count">01 / 06</span></p>
            </div>
            <div class="cal-track" data-cal-track>
                <?php foreach ($cal_families as $i => $t) : ?>
                    <article class="cal-type-card" data-cal-card>
                        <div class="cal-type-top"><span class="cal-type-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span><span class="cal-type-std"><?php echo esc_html($t[3]); ?></span></div>
                        <h3><?php echo esc_html($t[0]); ?></h3>
                        <p class="cal-type-sub"><?php echo esc_html($t[1]); ?></p>
                        <p class="cal-type-body"><?php echo esc_html($t[2]); ?></p>
                        <span class="cal-type-wave" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== DELIVERABLES ===== -->
    <section id="cal-deliver" class="cal-deliver" aria-labelledby="cal-deliver-title">
        <div class="eta-shell">
            <header class="cal-head">
                <p class="cal-kicker">What the certificate carries</p>
                <h2 id="cal-deliver-title">Six things an auditor looks for on a calibration certificate. All six are there.</h2>
                <p>A certificate is only useful if the reader can follow it back to a reference and forward to a decision. These are the fields that make that possible.</p>
            </header>
            <div class="cal-deliver-grid">
                <?php foreach ($cal_certificate as $d) : ?>
                    <article class="cal-deliver-card" data-cal-tilt>
                        <div class="cal-deliver-top"><span class="cal-deliver-code"><?php echo esc_html($d[0]); ?></span><span class="cal-deliver-std"><?php echo esc_html($d[4]); ?></span></div>
                        <h3><?php echo esc_html($d[1]); ?></h3>
                        <p><?php echo esc_html($d[2]); ?></p>
                        <ul class="cal-chips">
                            <?php foreach ($d[3] as $chip) : ?><li><?php echo esc_html($chip); ?></li><?php endforeach; ?>
                        </ul>
                        <span class="cal-deliver-glow" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== REGULATORS ===== -->
    <section class="cal-regs" aria-labelledby="cal-regs-title">
        <div class="eta-shell cal-regs-grid">
            <div class="cal-regs-copy">
                <p class="cal-kicker">References and criteria</p>
                <h2 id="cal-regs-title">Checked against a documented reference, judged against criteria you can see.</h2>
                <p>Calibration is only as good as the chain behind it. We state the reference used, the tolerance applied and the uncertainty attached, so the certificate can be relied on.</p>
            </div>
            <ul class="cal-regs-list">
                <?php foreach ($cal_regs as $r) : ?>
                    <li><b><?php echo esc_html($r[0]); ?></b><span><?php echo esc_html($r[1]); ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ===== WHO IT SERVES ===== -->
    <section class="cal-serves" aria-labelledby="cal-serves-title">
        <div class="eta-shell">
            <header class="cal-head cal-head-light">
                <p class="cal-kicker">Who relies on it</p>
                <h2 id="cal-serves-title">Anyone whose numbers are only as good as the instrument that produced them.</h2>
            </header>
            <div class="cal-serves-grid">
                <?php foreach ($cal_serves as $s) : ?>
                    <article class="cal-serve-card">
                        <span class="cal-serve-drop" aria-hidden="true"></span>
                        <h3><?php echo esc_html($s[0]); ?></h3>
                        <p><?php echo esc_html($s[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== JOURNEY ===== -->
    <section class="cal-journey" aria-labelledby="cal-journey-title">
        <div class="eta-shell cal-journey-grid">
            <div class="cal-journey-copy">
                <p class="cal-kicker">How a calibration runs</p>
                <h2 id="cal-journey-title">Four steps from the instrument list to a certificate and a due date.</h2>
                <p>Share the instrument type, range, location, certificate requirement and deadline, and whether on-site support is needed. The rest follows.</p>
                <a class="cal-btn cal-btn-solid" href="<?php echo esc_url($cal_contact); ?>">Send an instrument list <span aria-hidden="true">&rarr;</span></a>
            </div>
            <ol class="cal-journey-list">
                <svg class="cal-journey-line" viewBox="0 0 2 100" preserveAspectRatio="none" aria-hidden="true"><path d="M1 0 V100" pathLength="1"></path></svg>
                <?php foreach ($cal_journey as $i => $step) : ?>
                    <li data-cal-step>
                        <span class="cal-journey-dot"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <strong><?php echo esc_html($step[0]); ?></strong>
                        <p><?php echo esc_html($step[1]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ===== WHY ===== -->
    <section class="cal-why" aria-labelledby="cal-why-title">
        <div class="eta-shell">
            <header class="cal-head cal-head-light">
                <p class="cal-kicker">Why laboratories and plants choose us</p>
                <h2 id="cal-why-title">Calibration from a laboratory that is audited on its own instruments every year.</h2>
            </header>
            <div class="cal-why-grid">
                <?php foreach ($cal_why as $w) : ?>
                    <article class="cal-why-card">
                        <h3><?php echo esc_html($w[0]); ?></h3>
                        <p><?php echo esc_html($w[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="cal-faq" aria-labelledby="cal-faq-title">
        <div class="eta-shell cal-faq-grid">
            <header class="cal-head cal-head-light">
                <p class="cal-kicker">Before you request calibration</p>
                <h2 id="cal-faq-title">Questions we are asked most.</h2>
            </header>
            <div class="cal-faq-list">
                <?php foreach ($cal_faqs as $i => $faq) : ?>
                    <details class="cal-faq-item"<?php echo $i === 0 ? ' open' : ''; ?>>
                        <summary><?php echo esc_html($faq[0]); ?><span class="cal-faq-icon" aria-hidden="true"></span></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== RELATED ===== -->
    <section class="cal-related" aria-labelledby="cal-related-title">
        <div class="eta-shell">
            <header class="cal-head cal-head-light">
                <p class="cal-kicker">Go deeper</p>
                <h2 id="cal-related-title">Related services and the laboratory behind them.</h2>
            </header>
            <div class="cal-related-grid">
                <?php foreach ($cal_related as $r) : ?>
                    <a class="cal-related-card" href="<?php echo esc_url(home_url($r[2])); ?>">
                        <strong><?php echo esc_html($r[0]); ?></strong>
                        <p><?php echo esc_html($r[1]); ?></p>
                        <span class="cal-inline-link">Open <i aria-hidden="true">&rarr;</i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FINAL ===== -->
    <section class="cal-final" aria-labelledby="cal-final-title">
        <div class="eta-shell cal-final-grid">
            <div>
                <p class="cal-kicker">An audit coming, or a certificate about to expire?</p>
                <h2 id="cal-final-title">Send the instrument list, the ranges and the date you need it by. We confirm scope, method and a visit or drop-off schedule.</h2>
            </div>
            <div class="cal-final-actions">
                <a class="cal-btn cal-btn-solid" href="<?php echo esc_url($cal_contact); ?>">Request a calibration <span aria-hidden="true">&rarr;</span></a>
                <a class="cal-btn cal-btn-ghost" href="https://wa.me/923102288801" target="_blank" rel="noopener">WhatsApp consultation</a>
            </div>
        </div>
    </section>

</div>

<script data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1">
(function () {
    var d = document.documentElement;
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'noModule' in HTMLScriptElement.prototype) {
        d.classList.add('cal-gsap');
    }
    function f() { var h = document.getElementById('masthead'); d.style.setProperty('--eta-hh', (h ? h.offsetHeight : 0) + 'px'); }
    f(); addEventListener('resize', f); addEventListener('load', f);
})();
</script>
<script type="module" src="<?php echo esc_url($cal_theme_uri . '/eta-cal-scene.js?v=' . (string) filemtime(get_stylesheet_directory() . '/eta-cal-scene.js')); ?>" data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>
