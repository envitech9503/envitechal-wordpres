<?php
/**
 * Reusable GenerateBlocks patterns for premium Envi Tech AL SEO/service posts.
 */

if (!defined('ABSPATH')) {
    exit;
}

function eta_premium_pattern_text($text)
{
    return trim(preg_replace('/\s+/', ' ', (string) $text));
}

function eta_premium_register_pattern($slug, $title, $description, $content)
{
    register_block_pattern('envitechal/' . $slug, [
        'title' => $title,
        'description' => $description,
        'categories' => ['envitechal-premium-posts'],
        'content' => trim($content),
    ]);
}

add_action('init', function () {
    if (!function_exists('register_block_pattern')) {
        return;
    }

    register_block_pattern_category('envitechal-premium-posts', [
        'label' => __('Envi Tech AL Premium Posts', 'envi-tech-al-modern'),
    ]);

    eta_premium_register_pattern(
        'premium-water-testing-hero',
        __('ETA Premium Hero - Water Testing', 'envi-tech-al-modern'),
        __('Hero section for premium water testing service posts.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/container {"className":"eta-wq-hero"} -->
<div class="gb-container eta-wq-hero"><!-- wp:generateblocks/text {"tagName":"span","className":"eta-wq-kicker"} -->
<span class="gb-text eta-wq-kicker">Water Testing Laboratory • Karachi • Lahore • Pakistan</span>
<!-- /wp:generateblocks/text -->

<!-- wp:generateblocks/headline {"element":"div","className":"eta-wq-title"} -->
<div class="gb-headline eta-wq-title">Water Testing Laboratory <span>for Safer Drinking Water Decisions</span></div>
<!-- /wp:generateblocks/headline -->

<!-- wp:generateblocks/text {"tagName":"p","className":"eta-wq-lead"} -->
<p class="gb-text eta-wq-lead"><strong>Clear water is not always safe water.</strong> A professional <strong>water testing laboratory</strong> helps homes, factories, hospitals, hotels, schools, offices, exporters and commercial buildings confirm whether their drinking water, borewell water, RO water, storage tank water, process water or utility water is fit for its intended use. Envi Tech AL supports <strong>water quality testing</strong>, <strong>drinking water testing lab</strong> services, sampling guidance, laboratory analysis and compliance-ready reporting for customers who need evidence, not assumptions.</p>
<!-- /wp:generateblocks/text -->

<!-- wp:generateblocks/container {"className":"eta-wq-hero-actions"} -->
<div class="gb-container eta-wq-hero-actions"><!-- wp:generateblocks/button {"url":"/contact-us-envi-tech-al/","className":"eta-wq-btn eta-wq-btn-primary"} -->
<a class="gb-button eta-wq-btn eta-wq-btn-primary" href="/contact-us-envi-tech-al/">Request Water Testing</a>
<!-- /wp:generateblocks/button -->

<!-- wp:generateblocks/button {"url":"https://wa.me/923152006074","className":"eta-wq-btn eta-wq-btn-secondary"} -->
<a class="gb-button eta-wq-btn eta-wq-btn-secondary" href="https://wa.me/923152006074">WhatsApp Lab Team</a>
<!-- /wp:generateblocks/button -->

<!-- wp:generateblocks/button {"url":"/services/water-testing-lab-services/","className":"eta-wq-btn eta-wq-btn-secondary"} -->
<a class="gb-button eta-wq-btn eta-wq-btn-secondary" href="/services/water-testing-lab-services/">View Water Testing Services</a>
<!-- /wp:generateblocks/button --></div>
<!-- /wp:generateblocks/container --></div>
<!-- /wp:generateblocks/container -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-stats',
        __('ETA Premium Stats Cards - Water Testing', 'envi-tech-al-modern'),
        __('Four statistic cards for premium service posts.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/grid {"className":"eta-wq-quick"} -->
<div class="gb-grid-wrapper eta-wq-quick"><!-- wp:generateblocks/container {"className":"eta-wq-quick-card"} -->
<div class="gb-container eta-wq-quick-card"><!-- wp:generateblocks/headline {"element":"strong"} --><strong class="gb-headline">2.1B</strong><!-- /wp:generateblocks/headline --><!-- wp:generateblocks/text {"tagName":"span"} --><span class="gb-text">people globally still lack safely managed drinking water services according to WHO/UNICEF JMP 2025.</span><!-- /wp:generateblocks/text --></div>
<!-- /wp:generateblocks/container -->
<!-- wp:generateblocks/container {"className":"eta-wq-quick-card"} -->
<div class="gb-container eta-wq-quick-card"><!-- wp:generateblocks/headline {"element":"strong"} --><strong class="gb-headline">106M</strong><!-- /wp:generateblocks/headline --><!-- wp:generateblocks/text {"tagName":"span"} --><span class="gb-text">people globally still drink directly from untreated surface water sources.</span><!-- /wp:generateblocks/text --></div>
<!-- /wp:generateblocks/container -->
<!-- wp:generateblocks/container {"className":"eta-wq-quick-card"} -->
<div class="gb-container eta-wq-quick-card"><!-- wp:generateblocks/headline {"element":"strong"} --><strong class="gb-headline">0</strong><!-- /wp:generateblocks/headline --><!-- wp:generateblocks/text {"tagName":"span"} --><span class="gb-text">E. coli should be detectable in any 100 mL drinking-water sample.</span><!-- /wp:generateblocks/text --></div>
<!-- /wp:generateblocks/container -->
<!-- wp:generateblocks/container {"className":"eta-wq-quick-card"} -->
<div class="gb-container eta-wq-quick-card"><!-- wp:generateblocks/headline {"element":"strong"} --><strong class="gb-headline">Data</strong><!-- /wp:generateblocks/headline --><!-- wp:generateblocks/text {"tagName":"span"} --><span class="gb-text">turns water complaints into traceable safety and treatment decisions.</span><!-- /wp:generateblocks/text --></div>
<!-- /wp:generateblocks/container --></div>
<!-- /wp:generateblocks/grid -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-trust-bar',
        __('ETA Premium Trust Bar - Water Testing', 'envi-tech-al-modern'),
        __('Compact trust bar for technical capability signals.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/grid {"className":"eta-wq-trustbar"} -->
<div class="gb-grid-wrapper eta-wq-trustbar"><!-- wp:generateblocks/container {"className":"eta-wq-trust"} --><div class="gb-container eta-wq-trust"><i>Lab</i> Microbiology</div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-trust"} --><div class="gb-container eta-wq-trust"><i>Chem</i> Chemistry</div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-trust"} --><div class="gb-container eta-wq-trust"><i>Water</i> Drinking Water</div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-trust"} --><div class="gb-container eta-wq-trust"><i>Files</i> Audit Records</div><!-- /wp:generateblocks/container --></div>
<!-- /wp:generateblocks/grid -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-risk-alert',
        __('ETA Premium Risk Alert - Water Testing', 'envi-tech-al-modern'),
        __('Risk alert panel for premium advisory posts.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/headline {"element":"h2"} --><h2 class="gb-headline">Why Water Testing Laboratory Near Me Should Mean More Than Location</h2><!-- /wp:generateblocks/headline -->
<!-- wp:generateblocks/text {"tagName":"p","className":"eta-wq-intro"} --><p class="gb-text eta-wq-intro">When someone searches for a <strong>water testing laboratory near me</strong>, the real requirement is usually urgent and practical: Is this water safe to drink, why does our RO water taste different, can this report support an audit, is our borewell suitable, or why are employees complaining about smell, scaling or stomach illness? A nearby <strong>water testing lab</strong> is useful only when it can provide correct sampling guidance, relevant parameter selection, reliable analysis and a report that management can actually use.</p><!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/container {"className":"eta-wq-warning"} --><div class="gb-container eta-wq-warning"><strong>Business and health risk:</strong> Water can look clean while still carrying microbiological contamination, high dissolved solids, nitrate, arsenic, lead, hardness, corrosion indicators or treatment failure signs. Taste, smell and colour are warning signals, but they are not a substitute for laboratory water quality testing.</div><!-- /wp:generateblocks/container -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-two-column',
        __('ETA Premium Two Column Explanation - Water Testing', 'envi-tech-al-modern'),
        __('Two-column explanation panel with risk bars.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/grid {"className":"eta-wq-split"} -->
<div class="gb-grid-wrapper eta-wq-split"><!-- wp:generateblocks/container {"className":"eta-wq-panel eta-wq-panel-blue"} -->
<div class="gb-container eta-wq-panel eta-wq-panel-blue"><!-- wp:generateblocks/headline {"element":"h3"} --><h3 class="gb-headline">What the user sees</h3><!-- /wp:generateblocks/headline --><!-- wp:generateblocks/text {"tagName":"p"} --><p class="gb-text">Clear water in a glass, normal taste, no visible particles, and a working filter or RO plant.</p><!-- /wp:generateblocks/text --><!-- wp:generateblocks/headline {"element":"h3"} --><h3 class="gb-headline">What the lab verifies</h3><!-- /wp:generateblocks/headline --><!-- wp:generateblocks/text {"tagName":"p"} --><p class="gb-text">Microbiology, pH, TDS, turbidity, hardness, chloride, sulphate, nitrate, fluoride, metals where required, and source-specific contamination indicators.</p><!-- /wp:generateblocks/text --></div>
<!-- /wp:generateblocks/container -->
<!-- wp:generateblocks/container {"className":"eta-wq-panel"} -->
<div class="gb-container eta-wq-panel"><!-- wp:generateblocks/headline {"element":"h3"} --><h3 class="gb-headline">Invisible Water Quality Risks</h3><!-- /wp:generateblocks/headline --><!-- wp:list {"className":"eta-gb-risk-list"} --><ul class="eta-gb-risk-list"><li><strong>Microbiology:</strong> tank, handling and source contamination</li><li><strong>Storage tanks:</strong> sediment, leakage and biofilm</li><li><strong>Groundwater:</strong> metals, nitrate, salinity and hardness</li><li><strong>RO failure:</strong> membrane or post-treatment contamination</li><li><strong>Plumbing:</strong> corrosion and cross-connection indicators</li></ul><!-- /wp:list --></div>
<!-- /wp:generateblocks/container --></div>
<!-- /wp:generateblocks/grid -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-parameter-cards',
        __('ETA Premium Testing Parameter Cards', 'envi-tech-al-modern'),
        __('Grid of testing parameter cards.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/headline {"element":"h2"} --><h2 class="gb-headline">What a Professional Water Testing Laboratory Checks</h2><!-- /wp:generateblocks/headline -->
<!-- wp:paragraph --><p>The right testing scope depends on the source and use of water. Drinking water, borewell water, tanker water, municipal water, RO-treated water, process water, boiler feed water and cooling tower water should not be treated as one generic sample type.</p><!-- /wp:paragraph -->
<!-- wp:generateblocks/grid {"className":"eta-wq-grid"} -->
<div class="gb-grid-wrapper eta-wq-grid"><!-- wp:generateblocks/container {"className":"eta-wq-card"} --><div class="gb-container eta-wq-card"><div class="eta-wq-icon">Micro</div><h3>Microbiological Testing</h3><p>Checks indicators such as total coliforms and E. coli where applicable.</p></div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-card"} --><div class="gb-container eta-wq-card"><div class="eta-wq-icon">Chem</div><h3>Chemical Testing</h3><p>Reviews pH, TDS, hardness, chloride, sulphate, alkalinity, nitrate, fluoride and related parameters.</p></div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-card"} --><div class="gb-container eta-wq-card"><div class="eta-wq-icon">Metal</div><h3>Heavy Metals</h3><p>Tests selected metals such as arsenic, lead, iron, manganese, chromium, cadmium or nickel.</p></div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-card"} --><div class="gb-container eta-wq-card"><div class="eta-wq-icon">Phys</div><h3>Physical Parameters</h3><p>Checks colour, turbidity, odour, appearance and acceptability indicators.</p></div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-card"} --><div class="gb-container eta-wq-card"><div class="eta-wq-icon">Proc</div><h3>Process Water</h3><p>Supports industries where water affects production quality, boilers, cooling systems or equipment life.</p></div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-card"} --><div class="gb-container eta-wq-card"><div class="eta-wq-icon">Audit</div><h3>Compliance Reporting</h3><p>Provides documented results for audits, compliance files and corrective-action records.</p></div><!-- /wp:generateblocks/container --></div>
<!-- /wp:generateblocks/grid -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-comparison-table',
        __('ETA Premium Source vs Risk Table', 'envi-tech-al-modern'),
        __('Responsive comparison table for source, risk, testing focus and business reason.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/headline {"element":"h2"} --><h2 class="gb-headline">Recommended Water Quality Testing Scope by Source</h2><!-- /wp:generateblocks/headline -->
<!-- wp:table {"className":"eta-wq-table-wrap"} -->
<figure class="wp-block-table eta-wq-table-wrap"><table class="eta-wq-table"><thead><tr><th>Water Source / Use</th><th>Typical Risk</th><th>Recommended Testing Focus</th><th>Business Reason</th></tr></thead><tbody><tr><td>Drinking water cooler</td><td>Post-treatment or storage contamination</td><td>Total coliforms, E. coli, pH, TDS, turbidity and chlorine where applicable</td><td>Confirms water reaching employees, students, guests or patients is acceptable.</td></tr><tr><td>Borewell / groundwater</td><td>High TDS, hardness, salinity, arsenic, nitrate, fluoride or local contamination</td><td>Full chemical profile, selected metals and microbiology if used for drinking</td><td>Groundwater quality varies by location, depth and nearby contamination sources.</td></tr><tr><td>RO treated water</td><td>Membrane failure, post-RO contamination or storage hygiene issue</td><td>TDS, conductivity, pH, microbiology, hardness, chloride and treatment indicators</td><td>Verifies treatment performance and safe storage after treatment.</td></tr><tr><td>Tank water</td><td>Biofilm, sediment, leakage, insects or sewage intrusion</td><td>Microbiology, turbidity, appearance and selected chemical parameters</td><td>Storage tanks are a common point where otherwise acceptable water becomes contaminated.</td></tr></tbody></table></figure>
<!-- /wp:table -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-workflow',
        __('ETA Premium Workflow Timeline', 'envi-tech-al-modern'),
        __('Step-by-step workflow timeline for technical service posts.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/headline {"element":"h2"} --><h2 class="gb-headline">Envi Tech AL Water Testing Workflow</h2><!-- /wp:generateblocks/headline -->
<!-- wp:generateblocks/container {"className":"eta-wq-process"} -->
<div class="gb-container eta-wq-process"><!-- wp:generateblocks/container {"className":"eta-wq-step"} --><div class="gb-container eta-wq-step"><div><h3>Define the source and purpose</h3><p>Confirm whether the requirement is drinking water, RO water, borewell water, process water, tank water, boiler water or cooling water.</p></div></div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-step"} --><div class="gb-container eta-wq-step"><div><h3>Select the right parameters</h3><p>Plan the test scope according to use, risk profile, audit requirement or internal standard.</p></div></div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-step"} --><div class="gb-container eta-wq-step"><div><h3>Collect and preserve the sample</h3><p>Use correct bottle selection, sampling point, preservation, chain of custody and holding time.</p></div></div><!-- /wp:generateblocks/container --><!-- wp:generateblocks/container {"className":"eta-wq-step"} --><div class="gb-container eta-wq-step"><div><h3>Analyze and report</h3><p>Perform agreed analysis and issue results that support corrective-action decisions.</p></div></div><!-- /wp:generateblocks/container --></div>
<!-- /wp:generateblocks/container -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-reference-cards',
        __('ETA Premium Reference Cards', 'envi-tech-al-modern'),
        __('Internal and external reference card grid.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/headline {"element":"h2"} --><h2 class="gb-headline">Authoritative References and Useful Internal Links</h2><!-- /wp:generateblocks/headline -->
<!-- wp:generateblocks/grid {"className":"eta-wq-linkbox"} -->
<div class="gb-grid-wrapper eta-wq-linkbox"><a class="eta-wq-link-card" href="/services/water-testing-lab-services/"><strong>Internal: Water Testing Lab Services</strong><span>Service path for drinking water, wastewater, groundwater, process water and RO testing.</span></a><a class="eta-wq-link-card" href="/services/"><strong>Internal: Environmental Testing Services</strong><span>Explore wider environmental testing, monitoring, consultancy and compliance services.</span></a><a class="eta-wq-link-card" href="/contact-us-envi-tech-al/"><strong>Internal: Request a Quotation</strong><span>Send the water source, location, testing purpose and reporting requirement.</span></a><a class="eta-wq-link-card" href="https://www.who.int/news-room/fact-sheets/detail/drinking-water" target="_blank" rel="noopener"><strong>External: WHO Drinking Water</strong><span>Public-health reference on safe drinking water and health protection.</span></a></div>
<!-- /wp:generateblocks/grid -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-faq',
        __('ETA Premium FAQ Accordion', 'envi-tech-al-modern'),
        __('FAQ accordion layout for Rank Math FAQ schema workflow.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/container {"className":"eta-wq-faq"} -->
<section class="gb-container eta-wq-faq" aria-label="Water testing laboratory FAQs"><h2>Water Testing Laboratory FAQs</h2><!-- wp:details --><details><summary>What is a water testing laboratory?</summary><p>A water testing laboratory analyzes water samples for physical, chemical, microbiological and selected metal parameters to determine whether the water is suitable for drinking, processing, utility use, audit records or compliance reporting.</p></details><!-- /wp:details --><!-- wp:details --><details><summary>What is the difference between water testing and drinking water testing?</summary><p>Water testing is a broad term covering drinking water, wastewater, groundwater, process water, boiler water, cooling water and RO water. Drinking water testing focuses on water used for human consumption.</p></details><!-- /wp:details --><!-- wp:details --><details><summary>Can clear water still be unsafe?</summary><p>Yes. Clear water may still contain bacteria, E. coli, arsenic, nitrate, lead, high dissolved solids or other contaminants that cannot be identified by appearance, taste or smell alone.</p></details><!-- /wp:details --></section>
<!-- /wp:generateblocks/container -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-final-cta',
        __('ETA Premium Final CTA', 'envi-tech-al-modern'),
        __('Final conversion section for premium service posts.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/container {"className":"eta-wq-final"} -->
<section class="gb-container eta-wq-final"><h2>Need a Water Testing Laboratory You Can Rely On?</h2><p>Share your water source, site location, intended use, concern and reporting purpose. Envi Tech AL can guide the right scope for water testing laboratory services, water quality testing, drinking water testing lab support, audit records and corrective-action decisions.</p><div class="eta-wq-hero-actions"><a class="eta-wq-btn eta-wq-btn-primary" href="/contact-us-envi-tech-al/">Request a Quotation</a><a class="eta-wq-btn eta-wq-btn-secondary" href="https://wa.me/923152006074">WhatsApp: 0315-2006074</a><a class="eta-wq-btn eta-wq-btn-secondary" href="/services/water-testing-lab-services/">Open Water Testing Service</a></div></section>
<!-- /wp:generateblocks/container -->'
    );

    eta_premium_register_pattern(
        'premium-water-testing-full-post',
        __('ETA Premium Full Post - Water Testing', 'envi-tech-al-modern'),
        __('Complete GenerateBlocks premium post shell assembled from reusable sections.', 'envi-tech-al-modern'),
        '<!-- wp:generateblocks/container {"className":"eta-wq-premium"} -->
<div class="gb-container eta-wq-premium"><!-- wp:pattern {"slug":"envitechal/premium-water-testing-hero"} /-->
<!-- wp:pattern {"slug":"envitechal/premium-water-testing-stats"} /-->
<!-- wp:pattern {"slug":"envitechal/premium-water-testing-trust-bar"} /-->
<!-- wp:pattern {"slug":"envitechal/premium-water-testing-risk-alert"} /-->
<!-- wp:pattern {"slug":"envitechal/premium-water-testing-two-column"} /-->
<!-- wp:pattern {"slug":"envitechal/premium-water-testing-parameter-cards"} /-->
<!-- wp:pattern {"slug":"envitechal/premium-water-testing-comparison-table"} /-->
<!-- wp:pattern {"slug":"envitechal/premium-water-testing-workflow"} /-->
<!-- wp:pattern {"slug":"envitechal/premium-water-testing-reference-cards"} /-->
<!-- wp:pattern {"slug":"envitechal/premium-water-testing-faq"} /-->
<!-- wp:pattern {"slug":"envitechal/premium-water-testing-final-cta"} /--></div>
<!-- /wp:generateblocks/container -->'
    );
});
