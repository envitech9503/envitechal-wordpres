<?php
/**
 * Flagship template: Analytical Lab Services (the environmental testing
 * laboratory page).
 *
 * A scroll-driven page on the site's fixed motion stack: a Three.js particle
 * scene in the hero (sample cloud resolving into an ordered result lattice),
 * GSAP + ScrollTrigger choreography for the journey, matrix and method
 * sections, Lenis for smoothing. Everything degrades: without WebGL, with
 * prefers-reduced-motion, or before the module loads, the page is a complete
 * static document.
 *
 * Variables available from single-services.php: $slug, $profile, $faqs,
 * $parameters, $process.
 */

if (!defined('ABSPATH')) {
    exit;
}

$lab_theme_uri = get_stylesheet_directory_uri();
$lab_contact   = home_url('/contact-us-envi-tech-al/');
$lab_verify    = home_url('/report-verification-portal/');
$lab_related   = eta_modern_related_service_items($slug, get_the_ID());

$lab_stages = [
    ['Scope', 'Purpose first', 'We confirm what the report must support: an EPA submission, a buyer audit, an internal check. That decides parameters, sampling and the standard the results are read against.'],
    ['Sample', 'Chain of custody', 'Field sampling by our team or receipt of your samples, with preservation, labelling and custody records kept from the first container to the bench.'],
    ['Analyse', 'Accredited methods', 'Analysis on methods within the relevant PNAC ISO/IEC 17025 approved scope, with calibration, blanks and duplicates run as the method requires.'],
    ['Review', 'Technical sign-off', 'Results are reviewed against the method and the specified standard before anything is released.'],
    ['Report', 'Defensible document', 'A numbered report with the method, the limit, the result and the reviewer, verifiable online by number and issue date.'],
];

$lab_matrix = [
    [
        'code'  => 'W',
        'title' => 'Water and wastewater',
        'note'  => 'Drinking, process, bore, effluent and discharge samples.',
        'std'   => 'SEQS · PEQS · NEQS · WHO',
        'items' => $parameters['Water and wastewater'] ?? [],
    ],
    [
        'code'  => 'A',
        'title' => 'Air and emissions',
        'note'  => 'Stack, ambient and workplace air, with field instruments where the scope needs them.',
        'std'   => 'SEQS · PEQS · NEQS',
        'items' => $parameters['Air and emissions'] ?? [],
    ],
    [
        'code'  => 'S',
        'title' => 'Soil, waste and industrial samples',
        'note'  => 'Soil, sludge, hazardous waste screening and process-specific matrices.',
        'std'   => 'Method and client specification',
        'items' => $parameters['Soil, waste, and industrial samples'] ?? [],
    ],
];
?>

<div id="eta-lab" class="eta-lab" data-lab-root>

    <!-- ===== HERO: sample cloud to result lattice ===== -->
    <section class="lab-hero" aria-labelledby="lab-title">
        <div class="lab-stage">
            <canvas class="lab-gl" aria-hidden="true"></canvas>
            <div class="lab-veil" aria-hidden="true"></div>

            <div class="eta-shell lab-hero-grid">
                <div class="lab-hero-copy">
                    <p class="lab-eyebrow">
                        <span>PNAC ISO/IEC 17025</span><i>·</i><span>LAB-285 Karachi</span><i>·</i><span>LAB-347 Lahore</span>
                    </p>
                    <h1 id="lab-title" class="lab-title">
                        <span class="lab-title-line">Environmental analysis</span>
                        <span class="lab-title-line">you can <em>defend</em>.</span>
                    </h1>
                    <p class="lab-lead"><?php echo esc_html($profile['lead']); ?></p>
                    <div class="lab-actions">
                        <a class="lab-btn lab-btn-solid" href="<?php echo esc_url($lab_contact); ?>">Request a quotation <span aria-hidden="true">&rarr;</span></a>
                        <a class="lab-btn lab-btn-ghost" href="#lab-matrix">See what we measure</a>
                    </div>
                </div>

                <aside class="lab-hero-panel" aria-label="Service fit">
                    <p class="lab-panel-kicker">Built for</p>
                    <ul class="lab-panel-list">
                        <?php foreach ($profile['best_for'] as $fit) : ?>
                            <li><?php echo esc_html($fit); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a class="lab-panel-link" href="<?php echo esc_url($lab_verify); ?>">Verify a report online <span aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>

            <ol class="lab-phases" aria-hidden="true">
                <li data-phase="0"><b>01</b> Sample</li>
                <li data-phase="1"><b>02</b> Analyse</li>
                <li data-phase="2"><b>03</b> Report</li>
            </ol>

            <p class="lab-cue" aria-hidden="true"><span></span>Scroll</p>
        </div>
    </section>

    <!-- ===== LEDGER ===== -->
    <section class="lab-ledger" aria-label="Laboratory credentials">
        <div class="eta-shell lab-ledger-row">
            <div class="lab-ledger-item">
                <span class="lab-ledger-k">Accreditation</span>
                <strong>ISO/IEC 17025</strong>
                <span class="lab-ledger-s">PNAC, for methods within the approved scope</span>
            </div>
            <div class="lab-ledger-item">
                <span class="lab-ledger-k">Karachi</span>
                <strong>LAB-285</strong>
                <span class="lab-ledger-s">Bahadurabad laboratory and field team</span>
            </div>
            <div class="lab-ledger-item">
                <span class="lab-ledger-k">Lahore</span>
                <strong>LAB-347</strong>
                <span class="lab-ledger-s">Johar Town laboratory and field team</span>
            </div>
            <div class="lab-ledger-item">
                <span class="lab-ledger-k">Every report</span>
                <strong>Verifiable online</strong>
                <span class="lab-ledger-s">By report number and issue date</span>
            </div>
        </div>
    </section>

    <!-- ===== JOURNEY: pinned horizontal track ===== -->
    <section class="lab-journey" aria-labelledby="lab-journey-title">
        <div class="lab-journey-pin">
            <div class="eta-shell lab-journey-head">
                <div>
                    <p class="lab-kicker">Sample to report</p>
                    <h2 id="lab-journey-title">Five controlled steps between a sample and a signature.</h2>
                </div>
                <p class="lab-journey-progress" aria-hidden="true"><span class="lab-journey-bar"><i></i></span><span class="lab-journey-count">01 / 05</span></p>
            </div>
            <div class="lab-track" data-lab-track>
                <?php foreach ($lab_stages as $i => $stage) : ?>
                    <article class="lab-stage-card" data-lab-card>
                        <span class="lab-stage-n"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <h3><?php echo esc_html($stage[0]); ?></h3>
                        <p class="lab-stage-sub"><?php echo esc_html($stage[1]); ?></p>
                        <p><?php echo esc_html($stage[2]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== MATRIX ===== -->
    <section id="lab-matrix" class="lab-matrix" aria-labelledby="lab-matrix-title">
        <div class="eta-shell">
            <header class="lab-head">
                <p class="lab-kicker">What we measure</p>
                <h2 id="lab-matrix-title">Three sample streams, one reporting standard.</h2>
                <p>Parameters below are indicative. The final list is confirmed against the standard your report must satisfy, and accreditation applies to methods within the relevant approved scope.</p>
            </header>
            <div class="lab-matrix-grid">
                <?php foreach ($lab_matrix as $m) : ?>
                    <article class="lab-matrix-card" data-lab-tilt>
                        <div class="lab-matrix-top">
                            <span class="lab-matrix-code"><?php echo esc_html($m['code']); ?></span>
                            <span class="lab-matrix-std"><?php echo esc_html($m['std']); ?></span>
                        </div>
                        <h3><?php echo esc_html($m['title']); ?></h3>
                        <p><?php echo esc_html($m['note']); ?></p>
                        <ul class="lab-chips">
                            <?php foreach ($m['items'] as $item) : ?>
                                <li><?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <span class="lab-matrix-glow" aria-hidden="true"></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== OUTCOMES BENTO ===== -->
    <section class="lab-bento" aria-labelledby="lab-bento-title">
        <div class="eta-shell">
            <header class="lab-head lab-head-dark">
                <p class="lab-kicker">What the report does for you</p>
                <h2 id="lab-bento-title">Numbers that hold up in the room where the decision is made.</h2>
            </header>
            <div class="lab-bento-grid">
                <article class="lab-bento-card lab-bento-wide">
                    <p class="lab-kicker">Regulatory</p>
                    <h3>Report inputs for Sindh EPA and Punjab EPA pathways</h3>
                    <p>Results referenced to SEQS, PEQS or NEQS as the submission requires, with the method and limit stated beside every value, so the document can be read by a regulator without a covering explanation.</p>
                </article>
                <article class="lab-bento-card">
                    <p class="lab-kicker">Commercial</p>
                    <h3>Buyer and export audits</h3>
                    <p>Effluent, discharge and workplace results in the form brand compliance teams ask for.</p>
                </article>
                <article class="lab-bento-card">
                    <p class="lab-kicker">Operational</p>
                    <h3>Process and utility checks</h3>
                    <p>RO performance, boiler and cooling water, and plant troubleshooting on a repeat schedule.</p>
                </article>
                <article class="lab-bento-card">
                    <p class="lab-kicker">Assurance</p>
                    <h3>Reviewed before release</h3>
                    <p>Results are checked against the method and the specified limit before the report is issued.</p>
                </article>
                <article class="lab-bento-card lab-bento-accent">
                    <p class="lab-kicker">Verification</p>
                    <h3>Check any report in seconds</h3>
                    <p>Enter the report number and issue date on the verification portal and the record is confirmed from the laboratory system.</p>
                    <a class="lab-inline-link" href="<?php echo esc_url($lab_verify); ?>">Open the portal <span aria-hidden="true">&rarr;</span></a>
                </article>
            </div>
        </div>
    </section>

    <!-- ===== METHOD: drawn timeline ===== -->
    <section class="lab-method" aria-labelledby="lab-method-title">
        <div class="eta-shell lab-method-grid">
            <div class="lab-method-copy">
                <p class="lab-kicker">How a request runs</p>
                <h2 id="lab-method-title">A controlled path from question to usable report.</h2>
                <p>Each request begins by clarifying the decision you need to make. The technical work is then matched to that decision, not the other way round.</p>
                <a class="lab-btn lab-btn-solid" href="<?php echo esc_url($lab_contact); ?>">Start a request <span aria-hidden="true">&rarr;</span></a>
            </div>
            <ol class="lab-method-list">
                <svg class="lab-method-line" viewBox="0 0 2 100" preserveAspectRatio="none" aria-hidden="true"><path d="M1 0 V100" pathLength="1"></path></svg>
                <?php foreach ($process as $i => $step) : ?>
                    <li data-lab-step>
                        <span class="lab-method-dot"><?php echo esc_html(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <strong><?php echo esc_html($step[0]); ?></strong>
                        <p><?php echo esc_html($step[1]); ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ===== FAQ ===== -->
    <?php if ($faqs) : ?>
        <section class="lab-faq" aria-labelledby="lab-faq-title">
            <div class="eta-shell lab-faq-grid">
                <header class="lab-head">
                    <p class="lab-kicker">Before you request a quotation</p>
                    <h2 id="lab-faq-title">Questions we are asked most.</h2>
                </header>
                <div class="lab-faq-list">
                    <?php foreach ($faqs as $i => $faq) : ?>
                        <details class="lab-faq-item"<?php echo $i === 0 ? ' open' : ''; ?>>
                            <summary><?php echo esc_html($faq[0]); ?><span class="lab-faq-icon" aria-hidden="true"></span></summary>
                            <p><?php echo esc_html($faq[1]); ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ===== RELATED ===== -->
    <section class="lab-related" aria-labelledby="lab-related-title">
        <div class="eta-shell">
            <header class="lab-head">
                <p class="lab-kicker">Related services</p>
                <h2 id="lab-related-title">Build a complete compliance path.</h2>
            </header>
            <div class="lab-related-grid">
                <?php foreach ($lab_related as $service) :
                    $rp = eta_modern_service_profile(get_post_field('post_name', $service));
                    $rt = ($slug === 'analytical-lab-services' && get_post_field('post_name', $service) === 'water-testing-lab-services') ? 'water testing lab' : eta_modern_display_title($service);
                    ?>
                    <a class="lab-related-card" href="<?php echo esc_url(get_permalink($service)); ?>">
                        <span class="lab-kicker"><?php echo esc_html($rp['category']); ?></span>
                        <strong><?php echo esc_html($rt); ?></strong>
                        <p><?php echo esc_html($rp['lead']); ?></p>
                        <span class="lab-inline-link">Explore <i aria-hidden="true">&rarr;</i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== FINAL CTA ===== -->
    <section class="lab-final" aria-labelledby="lab-final-title">
        <div class="eta-shell lab-final-grid">
            <div>
                <p class="lab-kicker">Ready to scope the work?</p>
                <h2 id="lab-final-title">Send the sample type, the site and the purpose of the report. We confirm the method before any work begins.</h2>
            </div>
            <div class="lab-final-actions">
                <a class="lab-btn lab-btn-solid" href="<?php echo esc_url($lab_contact); ?>">Request a quotation <span aria-hidden="true">&rarr;</span></a>
                <a class="lab-btn lab-btn-ghost" href="https://wa.me/923102288801" target="_blank" rel="noopener">WhatsApp the laboratory</a>
            </div>
        </div>
    </section>

</div>

<script data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1">
(function () {
    var d = document.documentElement;
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches && 'noModule' in HTMLScriptElement.prototype) {
        d.classList.add('lab-gsap');
    }
    function f() { var h = document.getElementById('masthead'); d.style.setProperty('--eta-hh', (h ? h.offsetHeight : 0) + 'px'); }
    f(); addEventListener('resize', f); addEventListener('load', f);
})();
</script>
<script type="module" src="<?php echo esc_url($lab_theme_uri . '/eta-lab-scene.js?v=1'); ?>" data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>
