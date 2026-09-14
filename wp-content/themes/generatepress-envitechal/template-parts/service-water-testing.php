<?php
/**
 * Flagship template: Water Testing Lab Services.
 *
 * Scroll-driven page on the fixed motion stack. The hero is a Three.js water
 * surface that settles from turbid chop to a clear, ordered sheet as the
 * visitor scrolls, with rising sample droplets and a deep-sea to aqua lighting
 * arc. GSAP + ScrollTrigger drive the pinned water-types track, the parameter
 * explorer, the drawn journey line and the reveals; Lenis smooths the scroll.
 * Without WebGL, with prefers-reduced-motion, or before the module runs, the
 * page is a complete static document.
 *
 * Variables available from single-services.php: $slug, $profile, $faqs,
 * $parameters, $process.
 */

if (!defined('ABSPATH')) {
    exit;
}

$wt_theme_uri = get_stylesheet_directory_uri();
$wt_contact   = home_url('/contact-us-envi-tech-al/');
$wt_verify    = home_url('/report-verification-portal/');
$wt_groups    = eta_modern_water_parameter_groups();
$wt_faqs      = eta_modern_water_testing_faqs();

$wt_types = [
    ['Drinking water', 'Safe to drink, and provable', 'Municipal, borehole, bottled and filtered supplies analysed against WHO guideline values and the Pakistan drinking-water standard: physico-chemical panel, trace metals by atomic absorption, and total coliform and E. coli as the decisive indicators of faecal contamination.', 'WHO · PEQS', '/drinking-water-testing-lab/'],
    ['Wastewater and effluent', 'Discharge that meets the limit', 'Sewer, sea and inland discharges scoped for pH, BOD5, COD, suspended solids, oil and grease, sulphide, phenols, temperature and the metals the standard names, read against SEQS, NEQS or PEQS as the permit requires.', 'SEQS · NEQS · PEQS', '/wastewater-testing-services/'],
    ['Process and utility water', 'Protecting product and plant', 'Product water for food, beverage, pharmaceutical and textile lines; boiler feed and cooling water for hardness, conductivity, silica and scaling indices; the checks that stop a water problem becoming a production problem.', 'Industry specification', null],
    ['RO and filtration performance', 'Membranes measured, not guessed', 'Feed, permeate and reject analysed together for TDS, conductivity and recovery indicators, so membrane deterioration shows in the numbers before it shows in the product or the power bill.', 'Plant specification', null],
    ['Bore, ground and recreational water', 'Everything else that holds water', 'Groundwater and bore water screening, swimming pool and recreational water, and industrial intake checks, each scoped to the question being asked of it.', 'Standard set by use', null],
];

$wt_serves = [
    ['Homes and residential buildings', 'Is the supply safe to drink, and what changed since the last test.'],
    ['Hospitals and hotels', 'Guest and patient safety with a report an inspector can read.'],
    ['Textile, food and industrial units', 'Effluent within the limit and process water within specification.'],
    ['Exporters and contractors', 'The water evidence a buyer audit or a tender asks for.'],
    ['Housing societies and utilities', 'Distribution checks across a network, on a schedule.'],
    ['Maritime operators', 'Ballast and port-call water alongside the vessel sampling team.'],
];

$wt_journey = [
    ['Define the requirement', 'Tell us the purpose: EPA submission, buyer audit, internal QA or troubleshooting. We match parameters, methods and report format to it.'],
    ['Sampling', 'Field teams collect across Karachi and Lahore with the correct containers, preservation and chain-of-custody records. For other cities, contact the laboratory before dispatch so acceptance and transit conditions are confirmed.'],
    ['Scope-confirmed analysis', 'The laboratory confirms location, matrix, parameter, method, range and credential status before work begins. LAB-285 and LAB-347 each cover only the combinations in their published scopes.'],
    ['Verifiable reporting', 'Results are issued in the agreed format, each stated against its applicable limit. Reports carrying verification details can be checked online by number and issue date.'],
];

$wt_why = [
    ['Evidence that can be checked', 'PNAC LAB-285 for the Karachi scope, PNAC LAB-347 for the Lahore scope, and the applicable EPA records.'],
    ['One coordinated workflow', 'Sampling, laboratory analysis, monitoring and consultancy delivered by one team.'],
    ['Verifiable reports', 'Each report carries verification details your regulator or buyer can check independently.'],
    ['Two laboratories', 'Karachi and Lahore, giving responsive coverage to the country\'s main industrial corridors.'],
];

$wt_related = [
    ['Drinking water testing', 'Panels, limits and what a result means.', '/drinking-water-testing-lab/'],
    ['Wastewater testing services', 'Effluent scoping against SEQS, NEQS and PEQS.', '/wastewater-testing-services/'],
    ['SEQS compliance guide', 'The Sindh limits, parameter by parameter.', '/sindh-environmental-quality-standards-seqs/'],
    ['Karachi environmental lab', 'LAB-285 and the field team behind it.', '/karachi-environmental-lab/'],
    ['Lahore environmental lab', 'LAB-347 and Punjab coverage.', '/lahore-environmental-lab/'],
    ['Equipment calibration', 'Traceable instruments for your own monitoring.', '/services/equipment-calibration-services/'],
];
?>

<div id="eta-water" class="eta-water">

    <!-- ===== HERO: turbid to clear ===== -->
    <section class="wt-hero" aria-labelledby="wt-title">
        <div class="wt-stage">
            <canvas class="wt-gl" aria-hidden="true"></canvas>
            <div class="wt-veil" aria-hidden="true"></div>

            <div class="eta-shell wt-hero-grid">
                <div class="wt-hero-copy">
                    <p class="wt-eyebrow"><span>Water and wastewater analysis</span><i>·</i><span>LAB-285 Karachi</span><i>·</i><span>LAB-347 Lahore</span></p>
                    <h1 id="wt-title" class="wt-title">
                        <span class="wt-title-line">Every drop,</span>
                        <span class="wt-title-line"><em>measured</em> against the limit.</span>
                    </h1>
                    <p class="wt-lead">Water quality testing for drinking water, wastewater, process water, RO performance and industrial discharge, for the people accountable for the result: a clear report, each value stated against its applicable limit, verifiable online.</p>
                    <div class="wt-actions">
                        <a class="wt-btn wt-btn-solid" href="<?php echo esc_url($wt_contact); ?>">Request a quotation <span aria-hidden="true">&rarr;</span></a>
                        <a class="wt-btn wt-btn-ghost" href="#wt-params">Explore the parameters</a>
                    </div>
                </div>

                <aside class="wt-hero-panel" aria-label="Read against">
                    <p class="wt-panel-kicker">Results read against</p>
                    <ul class="wt-panel-list">
                        <li><b>WHO</b><span>Drinking-water guideline values</span></li>
                        <li><b>PEQS</b><span>Pakistan drinking water and effluent</span></li>
                        <li><b>SEQS</b><span>Sindh effluent and discharge limits</span></li>
                        <li><b>NEQS</b><span>National environmental quality standards</span></li>
                    </ul>
                    <a class="wt-panel-link" href="<?php echo esc_url($wt_verify); ?>">Verify a report online <span aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>

            <div class="wt-gauge" aria-hidden="true">
                <span class="wt-gauge-k">Clarity</span>
                <span class="wt-gauge-bar"><i></i></span>
                <span class="wt-gauge-v">Turbid</span>
            </div>
            <p class="wt-cue" aria-hidden="true"><span></span>Scroll to clear the water</p>
        </div>
    </section>

    <!-- ===== LEDGER ===== -->
    <section class="wt-ledger" aria-label="Laboratory credentials">
        <div class="eta-shell wt-ledger-row">
            <div class="wt-ledger-item"><span class="wt-ledger-k">Karachi</span><strong>PNAC LAB-285</strong><span class="wt-ledger-s">ISO/IEC 17025, methods within the published scope</span></div>
            <div class="wt-ledger-item"><span class="wt-ledger-k">Lahore</span><strong>PNAC LAB-347</strong><span class="wt-ledger-s">ISO/IEC 17025, methods within the published scope</span></div>
            <div class="wt-ledger-item"><span class="wt-ledger-k">Punjab</span><strong>EPA listed laboratory</strong><span class="wt-ledger-s">For the monitoring the province requires</span></div>
            <div class="wt-ledger-item"><span class="wt-ledger-k">Every report</span><strong>Verifiable online</strong><span class="wt-ledger-s">By report number and issue date</span></div>
        </div>
    </section>

    <!-- ===== WATER TYPES: pinned track ===== -->
    <section class="wt-types" aria-labelledby="wt-types-title">
        <div class="wt-types-pin">
            <div class="eta-shell wt-types-head">
                <div>
                    <p class="wt-kicker">Five kinds of water</p>
                    <h2 id="wt-types-title">Each one asks a different question. The laboratory answers all five.</h2>
                </div>
                <p class="wt-types-progress" aria-hidden="true"><span class="wt-types-bar"><i></i></span><span class="wt-types-count">01 / 05</span></p>
            </div>
            <div class="wt-track" data-wt-track>
                <?php foreach ($wt_types as $i => $t) : ?>
                    <article class="wt-type-card" data-wt-card>
                        <div class="wt-type-top"><span class="wt-type-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span><span class="wt-type-std"><?php echo esc_html($t[3]); ?></span></div>
                        <h3><?php echo esc_html($t[0]); ?></h3>
                        <p class="wt-type-sub"><?php echo esc_html($t[1]); ?></p>
                        <p class="wt-type-body"><?php echo esc_html($t[2]); ?></p>
                        <?php if ($t[4]) : ?><a class="wt-inline-link" href="<?php echo esc_url(home_url($t[4])); ?>">Open the guide <i aria-hidden="true">&rarr;</i></a><?php endif; ?>
                        <span class="wt-type-wave" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== PARAMETER EXPLORER ===== -->
    <section id="wt-params" class="wt-params" aria-labelledby="wt-params-title">
        <div class="eta-shell">
            <header class="wt-head">
                <p class="wt-kicker">Parameters we test</p>
                <h2 id="wt-params-title">Water quality testing parameters, grouped the way a report reads.</h2>
                <p>Lists are indicative. The final set is confirmed against the standard your report must satisfy, and accreditation applies to methods within the relevant approved scope.</p>
            </header>
            <div class="wt-params-grid">
                <div class="wt-tabs" role="tablist" aria-label="Parameter groups">
                    <?php $k = 0; foreach ($wt_groups as $group => $items) : $id = 'wt-tab-' . $k; ?>
                        <button class="wt-tab<?php echo $k === 0 ? ' is-on' : ''; ?>" role="tab" id="<?php echo esc_attr($id); ?>" aria-selected="<?php echo $k === 0 ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($id . '-panel'); ?>" data-wt-tab="<?php echo esc_attr((string) $k); ?>">
                            <span><?php echo esc_html($group); ?></span><b><?php echo count($items); ?></b>
                        </button>
                    <?php $k++; endforeach; ?>
                    <div class="wt-report" aria-hidden="true">
                        <p class="wt-report-k">How a result reads on the report</p>
                        <div class="wt-report-row wt-report-head"><span>Parameter</span><span>Method</span><span>Result</span><span>Limit</span><span>Status</span></div>
                        <div class="wt-report-row"><span>Arsenic</span><span>AAS</span><span>▪▪▪ mg/L</span><span>0.05 mg/L</span><span class="ok">Within</span></div>
                        <div class="wt-report-row"><span>E. coli</span><span>MF</span><span>▪▪▪ /100 mL</span><span>0 /100 mL</span><span class="ok">Within</span></div>
                        <div class="wt-report-row"><span>Turbidity</span><span>Nephelometric</span><span>▪▪▪ NTU</span><span>5 NTU</span><span class="ok">Within</span></div>
                        <p class="wt-report-note">Illustrative layout. Limits shown are the WHO and PEQS drinking-water values for those parameters; the standard applied is the one your report specifies.</p>
                    </div>
                </div>
                <div class="wt-panels">
                    <?php $k = 0; foreach ($wt_groups as $group => $items) : $id = 'wt-tab-' . $k; ?>
                        <div class="wt-panel<?php echo $k === 0 ? ' is-on' : ''; ?>" role="tabpanel" id="<?php echo esc_attr($id . '-panel'); ?>" aria-labelledby="<?php echo esc_attr($id); ?>" data-wt-panel="<?php echo esc_attr((string) $k); ?>"<?php echo $k === 0 ? '' : ' hidden'; ?>>
                            <h3><?php echo esc_html($group); ?></h3>
                            <ul class="wt-chips">
                                <?php foreach ($items as $item) : ?>
                                    <li><?php echo esc_html($item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php $k++; endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== WHO IT SERVES ===== -->
    <section class="wt-serves" aria-labelledby="wt-serves-title">
        <div class="eta-shell">
            <header class="wt-head wt-head-light">
                <p class="wt-kicker">Who relies on it</p>
                <h2 id="wt-serves-title">Water quality decisions carry regulatory, commercial and public-health consequences. These are the people who make them.</h2>
            </header>
            <div class="wt-serves-grid">
                <?php foreach ($wt_serves as $s) : ?>
                    <article class="wt-serve-card">
                        <span class="wt-serve-drop" aria-hidden="true"></span>
                        <h3><?php echo esc_html($s[0]); ?></h3>
                        <p><?php echo esc_html($s[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== JOURNEY: drawn line ===== -->
    <section class="wt-journey" aria-labelledby="wt-journey-title">
        <div class="eta-shell wt-journey-grid">
            <div class="wt-journey-copy">
                <p class="wt-kicker">From sample to verified report</p>
                <h2 id="wt-journey-title">A controlled path from requirement to usable laboratory evidence.</h2>
                <p>Good scoping protects cost, turnaround planning, sample validity and the usefulness of the report.</p>
                <a class="wt-btn wt-btn-solid" href="<?php echo esc_url($wt_contact); ?>">Start a request <span aria-hidden="true">&rarr;</span></a>
            </div>
            <ol class="wt-journey-list">
                <svg class="wt-journey-line" viewBox="0 0 2 100" preserveAspectRatio="none" aria-hidden="true"><path d="M1 0 V100" pathLength="1"></path></svg>
                <?php foreach ($wt_journey as $i => $step) : ?>
                    <li data-wt-step>
                        <span class="wt-journey-dot"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <strong><?php echo esc_html($step[0]); ?></strong>
                        <p><?php echo esc_html($step[1]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ===== WHY ===== -->
    <section class="wt-why" aria-labelledby="wt-why-title">
        <div class="eta-shell">
            <header class="wt-head wt-head-light">
                <p class="wt-kicker">Why facilities choose this laboratory</p>
                <h2 id="wt-why-title">Scope-confirmed analysis, coordinated sampling and verifiable reporting.</h2>
            </header>
            <div class="wt-why-grid">
                <?php foreach ($wt_why as $w) : ?>
                    <article class="wt-why-card">
                        <h3><?php echo esc_html($w[0]); ?></h3>
                        <p><?php echo esc_html($w[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FAQ ===== -->
    <section class="wt-faq" aria-labelledby="wt-faq-title">
        <div class="eta-shell wt-faq-grid">
            <header class="wt-head wt-head-light">
                <p class="wt-kicker">Before you request a quotation</p>
                <h2 id="wt-faq-title">Questions we are asked most.</h2>
            </header>
            <div class="wt-faq-list">
                <?php foreach ($wt_faqs as $i => $faq) : ?>
                    <details class="wt-faq-item"<?php echo $i === 0 ? ' open' : ''; ?>>
                        <summary><?php echo esc_html($faq[0]); ?><span class="wt-faq-icon" aria-hidden="true"></span></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== RELATED ===== -->
    <section class="wt-related" aria-labelledby="wt-related-title">
        <div class="eta-shell">
            <header class="wt-head wt-head-light">
                <p class="wt-kicker">Go deeper</p>
                <h2 id="wt-related-title">Guides, limits and the laboratories behind the work.</h2>
            </header>
            <div class="wt-related-grid">
                <?php foreach ($wt_related as $r) : ?>
                    <a class="wt-related-card" href="<?php echo esc_url(home_url($r[2])); ?>">
                        <strong><?php echo esc_html($r[0]); ?></strong>
                        <p><?php echo esc_html($r[1]); ?></p>
                        <span class="wt-inline-link">Open <i aria-hidden="true">&rarr;</i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FINAL ===== -->
    <section class="wt-final" aria-labelledby="wt-final-title">
        <div class="eta-shell wt-final-grid">
            <div>
                <p class="wt-kicker">Water testing with a deadline attached?</p>
                <h2 id="wt-final-title">Send the parameters, the purpose and the timeline. The laboratory confirms scope, quotation and sampling schedule.</h2>
            </div>
            <div class="wt-final-actions">
                <a class="wt-btn wt-btn-solid" href="<?php echo esc_url($wt_contact); ?>">Request a quotation <span aria-hidden="true">&rarr;</span></a>
                <a class="wt-btn wt-btn-ghost" href="https://wa.me/923102288801" target="_blank" rel="noopener">WhatsApp consultation</a>
            </div>
        </div>
    </section>

</div>

<script data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1">
(function () {
    var d = document.documentElement;
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'noModule' in HTMLScriptElement.prototype) {
        d.classList.add('wt-gsap');
    }
    function f() { var h = document.getElementById('masthead'); d.style.setProperty('--eta-hh', (h ? h.offsetHeight : 0) + 'px'); }
    f(); addEventListener('resize', f); addEventListener('load', f);
})();
</script>
<script type="module" src="<?php echo esc_url($wt_theme_uri . '/eta-water-scene.js?v=' . (string) filemtime(get_stylesheet_directory() . '/eta-water-scene.js')); ?>" data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>
