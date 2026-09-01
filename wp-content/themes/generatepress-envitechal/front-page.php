<?php
/**
 * Modern front page template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (!function_exists('eta_modern_home_image')) {
    function eta_modern_home_image($url, $class, $alt, $size = 'large', $loading = 'lazy', $sizes = '100vw', $extra = [])
    {
        $attrs = array_merge([
            'class' => $class,
            'alt' => $alt,
            'loading' => $loading,
            'decoding' => 'async',
            'sizes' => $sizes,
        ], $extra);

        if ($loading === 'eager') {
            $attrs['fetchpriority'] = 'high';
        }

        $attachment_id = attachment_url_to_postid($url);
        if ($attachment_id) {
            echo wp_get_attachment_image($attachment_id, $size, false, $attrs);
            return;
        }

        printf(
            '<img class="%1$s" src="%2$s" alt="%3$s" loading="%4$s" decoding="async"%5$s>',
            esc_attr($class),
            esc_url($url),
            esc_attr($alt),
            esc_attr($loading),
            $loading === 'eager' ? ' fetchpriority="high"' : ''
        );
    }
}

$service_tiles = [
    [
        'kicker' => 'Compliance Advisory',
        'title' => 'Environmental Consultancy',
        'text' => 'IEE, EIA, EMP, EMR, audits, SEPA submissions, and regulator-facing environmental documentation.',
        'url' => home_url('/services/environmental-consultancy/'),
        'image' => home_url('/wp-content/uploads/2026/06/Environmental-Consultancy.png'),
    ],
    [
        'kicker' => 'Environmental Laboratory',
        'title' => 'Environmental Lab & Analytical Services',
        'text' => 'Defensible laboratory analysis for environmental samples, industrial compliance, and buyer-facing reports.',
        'url' => home_url('/services/analytical-lab-services/'),
        'image' => home_url('/wp-content/uploads/2026/06/Water-Testing-Laboratory.png'),
    ],
    [
        'kicker' => 'Water & Wastewater',
        'title' => 'Water Testing Lab Services',
        'text' => 'Drinking water, wastewater, process water, RO performance, and discharge compliance testing.',
        'url' => home_url('/services/water-testing-lab-services/'),
        'image' => home_url('/wp-content/uploads/2026/08/featured.webp'),
    ],
    [
        'kicker' => 'Instrument Accuracy',
        'title' => 'Equipment Calibration',
        'text' => 'Calibration support for field instruments, monitoring equipment, and laboratory measurement confidence.',
        'url' => home_url('/services/equipment-calibration-services/'),
        'image' => home_url('/wp-content/uploads/2026/06/Calibration-Services.png'),
    ],
];

$industries = [
    'Textile',
    'Leather',
    'Food & Beverage',
    'Pharma',
    'Hotels',
    'Hospitals',
    'Construction',
    'Oil & Gas',
    'Cement',
    'Commercial Facilities',
];

$credentials = [
    [
        'title' => 'Sindh EPA credential',
        'subtitle' => 'Confirm current document and scope',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/sepa-sindh-logo.png',
    ],
    [
        'title' => 'ISO 9001:2015',
        'subtitle' => 'Quality management systems',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/iso-9001-2015-logo.png',
    ],
    [
        'title' => 'ISO 14001:2015',
        'subtitle' => 'Environmental management systems',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/iso-14001-2015-logo.png',
    ],
    [
        'title' => 'PNAC LAB-285 / LAB-347',
        'subtitle' => 'Karachi and Lahore; verify listed methods',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/iso-iec-170252017-accreditation-logo.png',
    ],
    [
        'title' => 'Punjab EPA certification',
        'subtitle' => 'Lahore laboratory, 2025–2028 document',
        'image' => 'https://envitechal.com/wp-content/uploads/2026/04/epa-new-logo-1.jpg',
    ],
];
?>

<main id="primary" class="site-main eta-main eta-home-master">
    <!-- ETS Living Environmental Digital Twin hero v6 — Three.js + GSAP — 28-08-2026 (backups: front-page.php.claude-backup-28082026, -v4-28082026, -v5-28082026) -->
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;500&family=Archivo:wght@300;400;500;600&display=swap">
<style>
html { overflow-x: clip; }
body.home .site-main.eta-home-master { margin-top: 0; margin-left: 0; margin-right: 0; }

.ets-twin { position: relative; background: #06090b; color: #EAF3EE; font-family: 'Archivo', 'Helvetica Neue', Arial, sans-serif;
  width: 100vw; width: var(--eta-vw, 100vw); margin-left: calc(50% - 50vw); margin-left: calc(50% - var(--eta-vw, 100vw)/2); }
html.ets-gsap .ets-twin { height: 265vh; height: 265svh; }
.ets-stage { position: relative; overflow: hidden; height: calc(100vh - var(--eta-hh, 0px)); height: calc(100svh - var(--eta-hh, 0px)); }
html.ets-gsap .ets-stage { position: sticky; top: var(--eta-hh, 0px); }

.ets-atmos { position: absolute; inset: 0; z-index: 0; background: #06090b; }
.ets-atmos::before { content: ""; position: absolute; inset: 0; opacity: .5;
  background: radial-gradient(ellipse 75% 60% at 50% 42%, rgba(31,107,84,.28), transparent 70%); }
html.ets-nogl .ets-atmos::after { content: ""; position: absolute; inset: 0; opacity: .35;
  background: linear-gradient(rgba(120,200,165,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(120,200,165,.08) 1px, transparent 1px);
  background-size: 72px 72px; transform: perspective(900px) rotateX(55deg) translateY(18%); transform-origin: 50% 100%; }
.ets-gl { position: absolute; inset: 0; z-index: 1; width: 100%; height: 100%; display: block; pointer-events: none; }
html:not(.ets-gsap) .ets-gl { display: none; }

.ets-mask { display: block; overflow: hidden; }
.ets-line { display: block; will-change: transform; }

/* ---------- Scene 1 — establishing ---------- */
.ets-l1 { position: absolute; inset: 0; z-index: 5; display: flex; flex-direction: column; align-items: center; justify-content: center;
  text-align: center; padding: 0 6vw 9vh; }
.ets-kicker { font-size: 11.5px; letter-spacing: .38em; text-transform: uppercase; color: #8FB8A4; font-weight: 500; margin: 0 0 26px; }
.ets-h1 { font-family: 'Outfit', 'Archivo', sans-serif; font-weight: 100; line-height: 1.02; letter-spacing: .1em; margin: 0;
  font-size: clamp(40px, 6.4vw, 96px); font-size: clamp(36px, min(6.2vw, 10svh), 96px);
  color: #F2FAF5; text-shadow: 0 0 34px rgba(140,255,205,.16); }
.ets-h1 .ets-em { font-weight: 300; }
.ets-sub { font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: clamp(15px, 1.35vw, 19px); color: #A3C4B4;
  max-width: 640px; line-height: 1.55; margin: 24px 0 0; }
.ets-ctas { display: flex; gap: 16px; margin-top: 34px; flex-wrap: wrap; justify-content: center; }
.ets-btn { display: inline-flex; align-items: center; gap: 9px; font-family: 'Archivo', sans-serif; font-size: 13px; font-weight: 600;
  letter-spacing: .12em; text-transform: uppercase; text-decoration: none; padding: 15px 26px; border-radius: 2px;
  transition: background .25s ease, color .25s ease, border-color .25s ease; }
.ets-btn .ets-arrow { display: inline-block; transition: transform .25s cubic-bezier(.2,.6,.2,1); }
.ets-btn:hover .ets-arrow { transform: translateX(4px); }
.ets-btn-primary { background: #1E6B54; color: #F2FAF5; border: 1px solid #1E6B54; }
.ets-btn-primary:hover { background: #27866A; border-color: #27866A; color: #fff; }
.ets-btn-ghost { background: transparent; color: #CDE8DB; border: 1px solid rgba(205,232,219,.34); position: relative; }
.ets-btn-ghost::after { content: ""; position: absolute; left: 26px; right: 100%; bottom: 9px; height: 1px; background: #CDE8DB; transition: right .3s cubic-bezier(.2,.6,.2,1); }
.ets-btn-ghost:hover::after { right: 26px; }
.ets-btn-ghost:hover { border-color: rgba(205,232,219,.7); color: #fff; }

.ets-cue { position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%); z-index: 5; display: flex; flex-direction: column;
  align-items: center; gap: 9px; font-size: 10.5px; letter-spacing: .34em; text-transform: uppercase; color: #7FA391; }
html:not(.ets-gsap) .ets-cue { display: none; }
.ets-cueline { width: 1px; height: 40px; background: linear-gradient(#7FA391, transparent); animation: etsCueDrop 2.2s ease-in-out infinite; }
.ets-skip { position: absolute; bottom: 24px; right: 104px; z-index: 6; appearance: none; cursor: pointer; font-family: 'Archivo', sans-serif;
  background: rgba(6,9,11,.4); color: #7FA391; border: 1px solid rgba(127,163,145,.4); border-radius: 999px;
  padding: 8px 18px; font-size: 10.5px; letter-spacing: .28em; text-transform: uppercase;
  transition: color .25s ease, border-color .25s ease; }
.ets-skip:hover { color: #CDE8DB; border-color: rgba(205,232,219,.7); }
.ets-skip:focus-visible { outline: 2px solid #7DE8CD; outline-offset: 3px; color: #CDE8DB; }
html:not(.ets-gsap) .ets-skip { display: none; }
@media (max-width: 640px) { .ets-skip { bottom: 20px; right: 80px; } }
@keyframes etsCueDrop { 0% { transform: scaleY(0); transform-origin: top; } 55% { transform: scaleY(1); transform-origin: top; } 100% { transform: scaleY(1) translateY(8px); opacity: 0; } }

/* intro entrance — editorial masked reveal (CSS so LCP is immediate) */
@media (prefers-reduced-motion: no-preference) {
  .ets-l1 .ets-line { animation: etsRise .9s cubic-bezier(.16,.6,.16,1) both; }
  .ets-l1 .ets-mask:nth-child(1) .ets-line { animation-delay: .08s; }
  .ets-h1 .ets-mask:nth-child(1) .ets-line { animation-delay: .2s; }
  .ets-h1 .ets-mask:nth-child(2) .ets-line { animation-delay: .32s; }
  .ets-h1 .ets-mask:nth-child(3) .ets-line { animation-delay: .44s; }
  .ets-subwrap .ets-line { animation-delay: .62s; }
  .ets-ctas { animation: etsFade 1s ease .85s backwards; }
  .ets-cue { animation: etsFade 1s ease 1.2s backwards; }
  .ets-kicker { animation: etsTrack 1.4s cubic-bezier(.16,.6,.16,1) both; }
}
@keyframes etsRise { from { transform: translateY(112%); } to { transform: translateY(0); } }
@keyframes etsFade { from { opacity: 0; } to { opacity: 1; } }
@keyframes etsTrack { from { letter-spacing: .58em; opacity: 0; } to { letter-spacing: .38em; opacity: 1; } }

/* ---------- editorial notes (scenes 2–5) ---------- */
.ets-note { position: absolute; z-index: 3; right: 7vw; top: 50%; transform: translateY(-50%); width: min(440px, 38vw); text-align: left; }
html:not(.ets-gsap) .ets-note { display: none; }
.ets-nnum { font-family: 'Outfit', sans-serif; font-weight: 100; font-size: 60px; color: rgba(141,230,201,.34); line-height: 1; display: block; margin-bottom: 0; }
.ets-note > .ets-mask { margin-bottom: 12px; }
html.ets-gsap .ets-note { opacity: 0; }
.ets-nkick { font-size: 10.5px; letter-spacing: .34em; text-transform: uppercase; color: #8FB8A4; font-weight: 600; display: block; margin-bottom: 0; }
.ets-ntext { font-family: 'Outfit', sans-serif; font-weight: 200; font-size: clamp(19px, 1.85vw, 27px); line-height: 1.3; color: #EAF3EE; margin: 0; }
.ets-nsmall { font-size: 12.5px; letter-spacing: .06em; color: #9CC3B2; margin-top: 12px; display: block; }

/* ---------- monitoring tags (scene 3) ---------- */
.ets-tags { position: absolute; inset: 0; z-index: 2; pointer-events: none; }
html:not(.ets-gsap) .ets-tags { display: none; }
.ets-tag { position: absolute; top: 0; left: 0; opacity: 0; transform: translate(-200px,-200px); padding-left: 12px; border-left: 1px solid rgba(141,230,201,.45); }
.ets-tag-name { display: block; font-size: 10.5px; font-weight: 500; letter-spacing: .3em; color: #A9DCC9; }
.ets-tag-par { display: block; font-size: 10px; letter-spacing: .14em; color: #7FA391; margin-top: 3px; }
.ets-tag-flip { border-left: none; border-right: 1px solid rgba(141,230,201,.6); padding-left: 0; padding-right: 12px; text-align: right; }
html.ets-nogl .ets-tag:nth-child(1) { transform: translate(26vw,20vh); }
html.ets-nogl .ets-tag:nth-child(2) { transform: translate(60vw,62vh); }
html.ets-nogl .ets-tag:nth-child(3) { transform: translate(56vw,30vh); }
html.ets-nogl .ets-tag:nth-child(4) { transform: translate(14vw,58vh); }
html.ets-nogl .ets-tag:nth-child(5) { transform: translate(44vw,70vh); }
html.ets-nogl .ets-tag:nth-child(6) { transform: translate(22vw,42vh); }

/* ---------- compliance words (scene 6) ---------- */
.ets-words { position: absolute; inset: 0; z-index: 4; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; }
html:not(.ets-gsap) .ets-words { display: none; }
.ets-wrow { display: flex; gap: 3.6vw; flex-wrap: wrap; justify-content: center; }
.ets-word { display: inline-block; overflow: hidden; }
.ets-word .ets-line { font-family: 'Outfit', sans-serif; font-weight: 300; font-size: clamp(17px, 2.7vw, 38px); letter-spacing: .26em; color: #E7F6EE; }
.ets-wmicro { font-size: 12.5px; letter-spacing: .1em; color: #BFE0D2; opacity: 0; max-width: 640px; text-align: center; padding: 0 6vw; margin-top: 6px; }

/* ---------- resolution (scene 7) ---------- */
.ets-final { position: absolute; inset: 0; z-index: 4; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 0 6vw; }
html:not(.ets-gsap) .ets-final { display: none; }
.ets-final .ets-line { font-family: 'Outfit', sans-serif; font-weight: 200; font-size: clamp(34px, 5.4vw, 80px); letter-spacing: .18em; line-height: 1.14; color: #0F1D18; }
.ets-fcta { opacity: 0; margin-top: 36px; display: flex; flex-direction: column; align-items: center; gap: 18px; }
.ets-fcta .ets-btn-ghost { color: #1E4436; border-color: rgba(20,60,47,.35); }
.ets-fcta .ets-btn-ghost::after { background: #1E4436; }
.ets-fcta .ets-btn-ghost:hover { color: #0F1B17; border-color: rgba(20,60,47,.7); }
.ets-ftrust { font-size: 11px; letter-spacing: .22em; text-transform: uppercase; color: #7C9C8C; }

/* ---------- mobile ---------- */
@media (max-width: 820px) {
  html.ets-gsap .ets-twin { height: 235vh; height: 235svh; }
  .ets-note { right: auto; left: 7vw; top: auto; bottom: 9svh; transform: none; width: 86vw; }
  .ets-nnum { font-size: 44px; }
  .ets-ntext { font-size: 20px; }
  .ets-sub { font-size: 15px; }
  .ets-wrow { gap: 14px; }
  .ets-tag-par { display: none; }
}
@media (prefers-reduced-motion: reduce) {
  html.ets-gsap .ets-twin { height: auto; }
  html.ets-gsap .ets-stage { position: relative; }
}
</style>
<script data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1">(function(){var d=document.documentElement;/* Claim the hero runway here rather than waiting for the module. The module is type=module, so it only runs after both vendor bundles have downloaded; until then .ets-twin has no height, the page is short, and the first section below it sits in the opening viewport and is scored as the LCP element. */if(!matchMedia("(prefers-reduced-motion: reduce)").matches&&"noModule" in HTMLScriptElement.prototype){d.classList.add("ets-gsap");}function f(){var h=document.getElementById("masthead");d.style.setProperty("--eta-vw",d.clientWidth+"px");d.style.setProperty("--eta-hh",(h?h.offsetHeight:0)+"px");}f();addEventListener("resize",f);addEventListener("orientationchange",f);addEventListener("load",f);})();</script>
<section class="ets-twin" id="ets-twin" aria-label="Envi Tech AL — environmental intelligence for industry">
  <div class="ets-stage">
    <div class="ets-atmos" aria-hidden="true"></div>
    <canvas class="ets-gl" aria-hidden="true"></canvas>

    <div class="ets-tags" aria-hidden="true">
      <div class="ets-tag"><span class="ets-tag-name">EMISSIONS</span><span class="ets-tag-par">SO&#8322; &middot; NO&#8339; &middot; CO</span></div>
      <div class="ets-tag"><span class="ets-tag-name">WATER</span><span class="ets-tag-par">pH &middot; TSS &middot; COD &middot; BOD</span></div>
      <div class="ets-tag"><span class="ets-tag-name">AIR</span><span class="ets-tag-par">PM&#8322;.&#8325; &middot; PM&#8321;&#8320;</span></div>
      <div class="ets-tag"><span class="ets-tag-name">NOISE</span><span class="ets-tag-par">dB(A)</span></div>
      <div class="ets-tag"><span class="ets-tag-name">WASTE</span><span class="ets-tag-par">HAZARDOUS &middot; SOLID</span></div>
      <div class="ets-tag"><span class="ets-tag-name">WORKPLACE</span><span class="ets-tag-par">LUX &middot; &deg;C &middot; RH</span></div>
    </div>

    <div class="ets-l1">
      <span class="ets-mask"><span class="ets-line ets-kicker">Envi Tech AL &middot; Environmental Laboratory &amp; Consultancy </span></span>
      <h1 class="ets-h1">
        <span class="ets-mask"><span class="ets-line">ENVIRONMENTAL </span></span>
        <span class="ets-mask"><span class="ets-line ets-em">INTELLIGENCE </span></span>
        <span class="ets-mask"><span class="ets-line">FOR INDUSTRY.</span></span>
      </h1>
      <span class="ets-mask ets-subwrap"><span class="ets-line ets-sub">Accredited environmental testing, monitoring and consultancy. Clear, defensible compliance intelligence for industry across Pakistan.</span></span>
      <div class="ets-ctas">
        <a class="ets-btn ets-btn-primary" href="/contact-us-envi-tech-al/">Request a quote <span class="ets-arrow">&rarr;</span></a>
        <a class="ets-btn ets-btn-ghost" href="/services/">Explore services</a>
      </div>
    </div>

    <div class="ets-note ets-n1">
      <span class="ets-mask"><span class="ets-line ets-nnum">01</span></span>
      <span class="ets-mask"><span class="ets-line ets-nkick">The Site</span></span>
      <p class="ets-ntext"><span class="ets-mask"><span class="ets-line">Every industrial operation </span></span><span class="ets-mask"><span class="ets-line">lives inside an environment </span></span><span class="ets-mask"><span class="ets-line">that can be measured.</span></span></p>
    </div>

    <div class="ets-note ets-n2">
      <span class="ets-mask"><span class="ets-line ets-nnum">02</span></span>
      <span class="ets-mask"><span class="ets-line ets-nkick">The Monitoring</span></span>
      <p class="ets-ntext"><span class="ets-mask"><span class="ets-line">Air, water, emissions, noise </span></span><span class="ets-mask"><span class="ets-line">and workplace conditions, </span></span><span class="ets-mask"><span class="ets-line">captured where they happen.</span></span></p>
    </div>

    <div class="ets-note ets-n3">
      <span class="ets-mask"><span class="ets-line ets-nnum">03</span></span>
      <span class="ets-mask"><span class="ets-line ets-nkick">The Measurement</span></span>
      <p class="ets-ntext"><span class="ets-mask"><span class="ets-line">The physical world becomes </span></span><span class="ets-mask"><span class="ets-line">traceable, defensible data.</span></span></p>
    </div>

    <div class="ets-note ets-n4">
      <span class="ets-mask"><span class="ets-line ets-nnum">04</span></span>
      <span class="ets-mask"><span class="ets-line ets-nkick">The Laboratory</span></span>
      <p class="ets-ntext"><span class="ets-mask"><span class="ets-line">PNAC ISO/IEC 17025 accredited </span></span><span class="ets-mask"><span class="ets-line">analysis, in Karachi and Lahore.</span></span></p>
      <span class="ets-nsmall">LAB-285 Karachi &middot; LAB-347 Lahore</span>
    </div>

    <div class="ets-words" aria-hidden="false">
      <div class="ets-wrow">
        <span class="ets-word"><span class="ets-line">MONITOR</span></span>
        <span class="ets-word"><span class="ets-line">VERIFY</span></span>
        <span class="ets-word"><span class="ets-line">COMPLY</span></span>
        <span class="ets-word"><span class="ets-line">IMPROVE</span></span>
      </div>
      <p class="ets-wmicro">Reporting aligned with SEQS, PEQS and NEQS. Ready for Sindh EPA and Punjab EPA.</p>
    </div>

    <div class="ets-final">
      <span class="ets-mask"><span class="ets-line">MEASURE. </span></span>
      <span class="ets-mask"><span class="ets-line">UNDERSTAND. </span></span>
      <span class="ets-mask"><span class="ets-line">COMPLY. </span></span>
      <span class="ets-mask"><span class="ets-line">IMPROVE.</span></span>
      <div class="ets-fcta">
        <div class="ets-ctas" style="margin-top:0">
          <a class="ets-btn ets-btn-primary" href="/contact-us-envi-tech-al/">Request a quote <span class="ets-arrow">&rarr;</span></a>
          <a class="ets-btn ets-btn-ghost" href="/services/">Explore services</a>
        </div>
        <span class="ets-ftrust">PNAC ISO/IEC 17025 &middot; LAB-285 Karachi &middot; LAB-347 Lahore &middot; Sindh EPA &middot; Punjab EPA</span>
      </div>
    </div>

    <div class="ets-cue"><span>Scroll to enter the site</span><span class="ets-cueline"></span></div>
    <button type="button" class="ets-skip" data-ets-skip aria-label="Skip the introduction and go to the services overview">Skip intro</button>
  </div>
</section>
<script type="module" src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/eta-hero-twin.js?v=19" data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>
<style>
/* ================= Homepage body v7 — act-based premium system — 28-08-2026 ================= */
:root { --etb-ink:#0F1D18; --etb-char:#081310; --etb-char2:#0A1512; --etb-tealdeep:#0A1E19; --etb-emerald:#1E6B54; --etb-emerald2:#27866A; --etb-aqua:#7DE8CD; --etb-mist:#9CC3B2; --etb-sage:#53705F; --etb-paper:#F4F7F3; --etb-line:rgba(15,29,24,.12); --etb-dline:rgba(125,232,205,.16); }

/* max-width alone left no gutter below 1200px, so on a phone every act ran
   its headings and body copy hard against both screen edges. Constrain to
   the viewport as .eta-shell already does, giving a 22px gutter on mobile
   while desktop still caps at 1200px. */
.etb-shell { width: min(1200px, calc(100% - 44px)); margin-inline: auto; }
.etb-dark { width: 100vw; width: var(--eta-vw,100vw); margin-left: calc(50% - 50vw); margin-left: calc(50% - var(--eta-vw,100vw)/2); padding-inline: max(22px, calc((var(--eta-vw,100vw) - 1200px)/2)); color: #DFEDE6; }

/* reveals (JS-gated; static without JS or with reduced motion) */
html.etb-js .etb-r { opacity: 0; transform: translateY(14px); transition: opacity .55s cubic-bezier(.16,.6,.16,1), transform .55s cubic-bezier(.16,.6,.16,1); }
html.etb-js .etb-r.in { opacity: 1; transform: none; }
@media (prefers-reduced-motion: reduce) { html.etb-js .etb-r { opacity: 1 !important; transform: none !important; transition: none !important; } }

/* section heads */
.etb-head { position: relative; max-width: 720px; margin: 0 0 36px; }
/* Act numerals sat at 1.07-1.40:1 contrast — below the 3:1 WCAG minimum for
   large text, so they read as smudges rather than structure. Raised to clear
   3.4:1 while staying secondary to the heading beneath. */
.etb-num { display: block; font-family: 'Outfit', sans-serif; font-weight: 200; font-size: 46px; line-height: 1; color: rgba(30,107,84,.75); margin-bottom: 4px; }
.etb-dark .etb-num { color: rgba(125,232,205,.46); }
.etb-eyebrow { font-family: 'Archivo', sans-serif; font-size: 11.5px; font-weight: 600; letter-spacing: .32em; text-transform: uppercase; color: var(--etb-emerald); margin: 0 0 14px; }
.etb-dark .etb-eyebrow { color: #8FB8A4; }
.etb-head h2, .etb-maritime h2 { font-family: 'Outfit', sans-serif; font-weight: 200; font-size: clamp(30px, 3.4vw, 48px); line-height: 1.12; letter-spacing: .01em; color: var(--etb-ink); margin: 0 0 18px; }
.etb-dark .etb-head h2, .etb-maritime h2 { color: #F2FAF5; }
.etb-lead { font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: 17.5px; line-height: 1.6; color: var(--etb-sage); margin: 0; }
.etb-dark .etb-lead { color: #A3C4B4; }
.etb-microlabel { font-family: 'Archivo', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: .28em; text-transform: uppercase; color: #8FB8A4; margin: 0 0 12px; }
.etb-matrix .etb-microlabel, .etb-why .etb-microlabel, .etb-vault .etb-microlabel { color: var(--etb-emerald); }

/* buttons + links */
.etb-btn { display: inline-flex; align-items: center; gap: 9px; font-family: 'Archivo', sans-serif; font-size: 12.5px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; text-decoration: none; padding: 14px 24px; border-radius: 2px; transition: background .25s ease, color .25s ease, border-color .25s ease; }
.etb-btn .etb-arrow, .etb-link .etb-arrow, .etb-obs-link .etb-arrow { display: inline-block; transition: transform .25s cubic-bezier(.2,.6,.2,1); }
.etb-btn:hover .etb-arrow, .etb-link:hover .etb-arrow { transform: translateX(4px); }
.etb-btn-solid { background: var(--etb-emerald); border: 1px solid var(--etb-emerald); color: #F2FAF5; }
.etb-btn-solid:hover { background: var(--etb-emerald2); border-color: var(--etb-emerald2); color: #fff; }
.etb-btn-ghost { background: transparent; border: 1px solid rgba(205,232,219,.34); color: #CDE8DB; }
.etb-btn-ghost:hover { border-color: rgba(205,232,219,.75); color: #fff; }
.etb-btn-line { background: transparent; border: 1px solid rgba(20,60,47,.3); color: #1E4436; }
.etb-btn-line:hover { border-color: rgba(20,60,47,.7); color: var(--etb-ink); }
/* inline-flex with a 24px minimum keeps these standalone calls to action at
   the WCAG 2.2 AA target size (2.5.8); as text links they rendered 17px tall. */
.etb-link { display: inline-flex; align-items: center; min-height: 24px; font-family: 'Archivo', sans-serif; font-size: 12.5px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; text-decoration: none; color: var(--etb-emerald); background: linear-gradient(currentColor, currentColor) left bottom / 0 1px no-repeat; padding-bottom: 3px; transition: background-size .3s cubic-bezier(.2,.6,.2,1), color .25s ease; }
.etb-link:hover { background-size: 100% 1px; color: #14503D; }
.etb-link-light { color: var(--etb-aqua); } .etb-link-light:hover { color: #fff; }
/* Any link sitting on a dark act inherits the aqua, not the emerald that is
   tuned for paper. The emerald measured 2.96:1 against #081310. */
.etb-dark .etb-link { color: var(--etb-aqua); }
.etb-dark .etb-link:hover, .etb-dark .etb-link:focus-visible { color: #FFFFFF; }
.etb-actions { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; margin-top: 28px; }
.etb-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: var(--etb-aqua); box-shadow: 0 0 8px rgba(125,232,205,.7); margin-left: 8px; vertical-align: 2px; }

/* ---------- 02 trust ledger ---------- */
.etb-ledger { background: var(--etb-char); position: relative; padding-top: 84px; padding-bottom: 0; }
.etb-ledger::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 84px; background: var(--etb-paper); }
.etb-ledger::after { content: ""; position: absolute; top: 22px; left: 50%; width: 1px; height: 40px; background: linear-gradient(rgba(30,107,84,0), rgba(30,107,84,.6)); }
.etb-ledger-row { display: grid; grid-template-columns: repeat(4, 1fr); border-top: 1px solid var(--etb-dline); }
.etb-ledger-item { padding: 24px 24px 26px; border-right: 1px solid rgba(125,232,205,.09); }
.etb-ledger-item:last-child { border-right: 0; }
.etb-ledger-auth { display: block; font-size: 10.5px; font-weight: 600; letter-spacing: .3em; text-transform: uppercase; color: #7FA391; margin-bottom: 10px; }
.etb-ledger-item strong { display: block; font-family: 'Outfit', sans-serif; font-weight: 300; font-size: 19px; letter-spacing: .02em; color: #EAF3EE; margin-bottom: 5px; }
.etb-ledger-sub { display: block; font-size: 12.5px; letter-spacing: .08em; color: var(--etb-mist); }

/* ---------- 03 service observatory ---------- */
.etb-obs { background: var(--etb-char); padding: 76px 0 84px; }
.etb-obs .etb-shell, .etb-journey .etb-shell { padding: 0; }
.etb-obs-grid { display: grid; grid-template-columns: minmax(0,5fr) minmax(0,7fr); gap: 56px; align-items: start; }
.etb-obs-index { display: flex; flex-direction: column; border-top: 1px solid var(--etb-dline); position: sticky; top: calc(var(--eta-hh, 90px) + 32px); }
.etb-obs-link { display: grid; grid-template-columns: 44px 1fr 24px; align-items: center; gap: 10px; padding: 22px 6px; border-bottom: 1px solid var(--etb-dline); text-decoration: none; transition: padding-left .3s cubic-bezier(.2,.6,.2,1); }
.etb-obs-i { font-family: 'Outfit', sans-serif; font-weight: 100; font-size: 22px; color: rgba(125,232,205,.35); }
.etb-obs-t { font-family: 'Outfit', sans-serif; font-weight: 300; font-size: clamp(17px, 1.5vw, 21px); color: #C8DDD2; transition: color .25s ease; }
.etb-obs-link .etb-arrow { color: var(--etb-aqua); opacity: 0; transform: translateX(-6px); transition: opacity .25s ease, transform .25s ease; }
.etb-obs-link:hover, .etb-obs-link.is-on { padding-left: 14px; }
.etb-obs-link:hover .etb-obs-t, .etb-obs-link.is-on .etb-obs-t { color: #fff; }
.etb-obs-link.is-on .etb-arrow, .etb-obs-link:hover .etb-arrow { opacity: 1; transform: none; }
.etb-obs-link:focus-visible { outline: 1px solid var(--etb-aqua); outline-offset: 2px; }
.etb-obs-planes { position: relative; min-height: 520px; }
.etb-obs-plane { position: absolute; inset: 0; opacity: 0; transform: translateY(14px); transition: opacity .5s ease, transform .5s cubic-bezier(.16,.6,.16,1); pointer-events: none; }
.etb-obs-plane.is-on { opacity: 1; transform: none; pointer-events: auto; }
.etb-obs-media { border-radius: 3px; overflow: hidden; border: 1px solid var(--etb-dline); aspect-ratio: 16/8.2; }
.etb-obs-media img { width: 100%; height: 100%; object-fit: cover; display: block; transform: scale(1.001); transition: transform 6s ease; }
.etb-obs-plane.is-on .etb-obs-media img { transform: scale(1.05); }
.etb-obs-body { padding-top: 22px; }
.etb-obs-h { font-family: 'Outfit', sans-serif; font-weight: 300; font-size: 24px; color: #F2FAF5; margin: 0 0 10px; }
.etb-obs-p { font-size: 15px; line-height: 1.65; color: var(--etb-mist); margin: 0 0 12px; max-width: 560px; }
.etb-params { font-family: 'Archivo', sans-serif; font-size: 11.5px; letter-spacing: .18em; text-transform: uppercase; color: var(--etb-aqua); border-top: 1px solid var(--etb-dline); padding-top: 12px; margin: 0 0 18px; }

/* ---------- 04 compliance journey ---------- */
.etb-journey { background: var(--etb-tealdeep); border-top: 1px solid rgba(125,232,205,.08); padding: 76px 0 88px; }
.etb-journey-grid { display: grid; grid-template-columns: minmax(0,5fr) minmax(0,7fr); gap: 64px; align-items: start; }
.etb-journey-tags { display: flex; flex-direction: column; gap: 10px; margin-top: 30px; }
.etb-journey-tags span { font-size: 13px; letter-spacing: .04em; color: var(--etb-mist); border-left: 1px solid rgba(125,232,205,.4); padding-left: 14px; }
.etb-steps { list-style: none; margin: 0; padding: 0 0 0 34px; position: relative; counter-reset: none; }
.etb-steps::before { content: ""; position: absolute; left: 8px; top: 8px; bottom: 8px; width: 1px; background: rgba(125,232,205,.14); }
.etb-steps::after { content: ""; position: absolute; left: 8px; top: 8px; bottom: 8px; width: 1px; background: var(--etb-aqua); transform-origin: top; transform: scaleY(var(--tp, 0)); }
.etb-steps li { display: grid; grid-template-columns: 62px 1fr; gap: 14px; padding: 16px 0; border-bottom: 1px solid rgba(125,232,205,.08); }
.etb-steps li:last-child { border-bottom: 0; }
.etb-step-n { font-family: 'Outfit', sans-serif; font-weight: 100; font-size: 40px; line-height: 1; color: rgba(125,232,205,.35); }
.etb-steps strong { display: block; font-family: 'Outfit', sans-serif; font-weight: 300; font-size: 19px; letter-spacing: .1em; text-transform: uppercase; color: #F2FAF5; margin-bottom: 6px; }
.etb-steps p { font-size: 14.5px; line-height: 1.6; color: var(--etb-mist); margin: 0; max-width: 520px; }

/* ---------- 05 sector matrix ---------- */
.etb-matrix { padding: 84px 0 88px; }
.etb-matrix-grid { display: grid; grid-template-columns: minmax(0,7fr) minmax(0,5fr); gap: 64px; align-items: start; }
.etb-matrix-list { list-style: none; margin: 0; padding: 0; border-top: 1px solid var(--etb-line); }
.etb-matrix-row { display: flex; flex-direction: column; gap: 4px; padding: 13px 8px; border-bottom: 1px solid var(--etb-line); cursor: default; transition: padding-left .3s cubic-bezier(.2,.6,.2,1), background .3s ease; }
.etb-matrix-name { font-family: 'Outfit', sans-serif; font-weight: 200; font-size: clamp(22px, 2.3vw, 34px); line-height: 1.1; color: #24352E; transition: color .25s ease; }
.etb-matrix-svc { font-family: 'Archivo', sans-serif; font-size: 11.5px; letter-spacing: .16em; text-transform: uppercase; color: var(--etb-sage); }
.etb-matrix-row:hover, .etb-matrix-row:focus-visible, .etb-matrix-row.is-on { padding-left: 20px; }
.etb-matrix-row:hover .etb-matrix-name, .etb-matrix-row:focus-visible .etb-matrix-name, .etb-matrix-row.is-on .etb-matrix-name { color: var(--etb-emerald); }
.etb-matrix-row:focus-visible { outline: 1px solid var(--etb-emerald); outline-offset: 2px; }
.etb-matrix-detail { position: sticky; top: calc(var(--eta-hh, 90px) + 40px); border: 1px solid var(--etb-line); border-radius: 3px; padding: 34px 32px 36px; background: #fff; }
.etb-matrix-big { font-family: 'Outfit', sans-serif; font-weight: 200; font-size: 40px; line-height: 1.05; color: var(--etb-ink); margin: 0 0 12px; }
.etb-matrix-lines { font-family: 'Archivo', sans-serif; font-size: 12.5px; letter-spacing: .16em; text-transform: uppercase; color: var(--etb-emerald); line-height: 2; margin: 0 0 8px; border-top: 1px solid var(--etb-line); padding-top: 14px; }
.etb-matrix-actions { margin: 14px 0 22px; }
@media (min-width: 821px) { .etb-matrix-row .etb-matrix-svc { display: none; } }

/* ---------- 06 maritime spotlight ---------- */
.etb-maritime { position: relative; overflow: hidden; background: linear-gradient(180deg, #07141A 0%, #0A2430 62%, #0C2B38 100%); padding: 88px 0 100px; }
.etb-maritime .etb-num { color: rgba(125,214,232,.49); }
.etb-maritime::before { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(7,20,26,.93) 0%, rgba(7,20,26,.62) 40%, rgba(9,26,34,.45) 68%, rgba(7,20,26,.78) 100%), url('/wp-content/uploads/2026/07/ballast-water-testing-imo-karachi-port.webp') center 62%/cover no-repeat; opacity: .9; }
.etb-sea { position: absolute; inset: auto 0 0 0; height: 46%; pointer-events: none; }
.etb-wave { position: absolute; left: -4%; width: 108%; height: 120px; }
.etb-wave-1 { bottom: 18%; } .etb-wave-2 { bottom: 9%; } .etb-wave-3 { bottom: 0; }
.etb-maritime-grid { position: relative; display: grid; grid-template-columns: minmax(0,7fr) minmax(0,5fr); gap: 64px; align-items: center; }
.etb-maritime .etb-lead { max-width: 560px; margin-bottom: 30px; color: #9EC2CE; }
.etb-maritime .etb-microlabel { color: #7DB6C6; }
.etb-maritime .etb-btn-ghost { border-color: rgba(158,206,220,.35); color: #CDE4EC; }
.etb-maritime .etb-btn-ghost:hover { border-color: rgba(158,206,220,.8); color: #fff; }
.etb-ticks { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 18px; }
.etb-ticks li { font-size: 14px; letter-spacing: .03em; color: #B9D6DF; border-left: 1px solid rgba(125,214,232,.45); padding-left: 16px; }

/* ---------- 07 credential vault ---------- */
.etb-vault { padding: 84px 0 80px; }
.etb-vault-table { border-top: 1px solid var(--etb-line); }
.etb-vault-row { display: grid; grid-template-columns: 64px 170px minmax(0,1fr) 230px; gap: 18px; align-items: center; padding: 18px 8px; border-bottom: 1px solid var(--etb-line); transition: background .3s ease; }
.etb-vault-row:hover { background: rgba(30,107,84,.04); }
.etb-vault-logo img { width: 44px; height: 44px; object-fit: contain; filter: grayscale(1); opacity: .75; transition: filter .3s ease, opacity .3s ease; }
.etb-vault-row:hover .etb-vault-logo img { filter: none; opacity: 1; }
.etb-vault-auth { font-family: 'Archivo', sans-serif; font-size: 12px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; color: var(--etb-ink); }
.etb-vault-cred { font-family: 'Outfit', sans-serif; font-weight: 300; font-size: 18px; color: #24352E; }
.etb-vault-loc { font-family: 'Archivo', sans-serif; font-size: 12px; letter-spacing: .14em; text-transform: uppercase; color: var(--etb-sage); text-align: right; }
.etb-footnote { font-size: 12.5px; color: var(--etb-sage); margin: 16px 0 0; font-style: italic; font-family: Georgia, serif; }

/* ---------- 08 why the system works ---------- */
.etb-why { padding: 80px 0 84px; }
.etb-why-grid { display: grid; grid-template-columns: minmax(0,7fr) minmax(0,5fr); gap: 56px; align-items: start; }
.etb-proofs { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border-top: 1px solid var(--etb-line); border-left: 1px solid var(--etb-line); }
.etb-proof { padding: 28px 26px 30px; border-right: 1px solid var(--etb-line); border-bottom: 1px solid var(--etb-line); }
/* The longest label ("KARACHI + LAHORE") measured 283px against a 280px
   content box, so it wrapped to a second line and left its row taller and
   its body copy out of line with its neighbour. Tightening the tracking and
   the top of the size ramp fits every label on one line, which keeps the
   rows equal without reserving blank space under the shorter ones. */
/* Named clients replaced three anonymous 'feedback themes'. Real organisations
   the buyer recognises do more for trust than described sentiment, and the
   names are the ones already published on the clients page. */
.etb-clients-lead { margin: 0 0 20px; font-size: 14px; line-height: 1.6; color: var(--etb-sage); }
.etb-clients { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 22px; margin: 0 0 28px; padding: 0; list-style: none; border-top: 1px solid var(--etb-line); }
.etb-clients li { padding: 9px 0; font-size: 13.5px; line-height: 1.35; color: var(--etb-ink); border-bottom: 1px solid var(--etb-line); }
@media (max-width: 560px) { .etb-clients { grid-template-columns: 1fr; } }

.etb-proof strong { display: block; font-family: 'Outfit', sans-serif; font-weight: 300; font-size: clamp(19px, 1.7vw, 23px); line-height: 1.5; letter-spacing: .07em; color: var(--etb-ink); margin-bottom: 8px; }
.etb-proof p { font-size: 14px; line-height: 1.6; color: var(--etb-sage); margin: 0; }
.etb-fb { display: grid; grid-template-columns: 52px 1fr; gap: 12px; padding: 18px 0; border-bottom: 1px solid var(--etb-line); }
.etb-fb > span { font-family: 'Outfit', sans-serif; font-weight: 100; font-size: 36px; line-height: 1; color: rgba(30,107,84,.3); }
.etb-fb strong { display: block; font-family: 'Outfit', sans-serif; font-weight: 300; font-size: 19px; color: var(--etb-ink); margin-bottom: 5px; }
.etb-fb p { font-size: 14px; line-height: 1.6; color: var(--etb-sage); margin: 0; }

/* ---------- 09 knowledge hub ---------- */
.etb-hub { padding: 80px 0 88px; }
.etb-hub-grid { display: grid; grid-template-columns: minmax(0,7fr) minmax(0,5fr); gap: 44px; align-items: start; }
.etb-hub-feature { position: relative; border-radius: 3px; overflow: hidden; background: var(--etb-char) radial-gradient(ellipse 90% 70% at 42% 18%, rgba(31,107,84,.3), transparent 70%); border: 1px solid var(--etb-dline); padding: 40px 38px; display: flex; flex-direction: column; justify-content: flex-end; min-height: 480px; }
.etb-hub-feature::before { content: ""; position: absolute; inset: 0; opacity: .3; background: linear-gradient(rgba(120,200,165,.07) 1px, transparent 1px), linear-gradient(90deg, rgba(120,200,165,.07) 1px, transparent 1px); background-size: 64px 64px; transform: perspective(800px) rotateX(48deg) translateY(22%); transform-origin: 50% 100%; }
.etb-hub-feature > * { position: relative; }
/* The feature card carried ~240px of bare texture above bottom-aligned text
   while every card beside it had a thumbnail. The photograph now fills the
   card and the copy sits over a scrim, so the upper area reads as the
   feature image rather than an empty panel. */
.etb-hub-feature-img { position: absolute; inset: 0; z-index: 0; width: 100%; height: 100%; object-fit: cover; opacity: .52; }
.etb-hub-feature::after { content: ""; position: absolute; inset: 0; z-index: 0; background: linear-gradient(to top, rgba(8,19,16,.96) 26%, rgba(8,19,16,.62) 58%, rgba(8,19,16,.28) 100%); }
.etb-hub-feature > *:not(.etb-hub-feature-img) { position: relative; z-index: 1; }
.etb-hub-feature h3 { font-family: 'Outfit', sans-serif; font-weight: 200; font-size: clamp(26px, 2.6vw, 40px); line-height: 1.14; margin: 0 0 14px; }
.etb-hub-feature h3 a { color: #F2FAF5; text-decoration: none; }
.etb-hub-feature h3 a:hover { color: var(--etb-aqua); }
.etb-hub-p { font-size: 15px; line-height: 1.65; color: var(--etb-mist); margin: 0 0 22px; max-width: 480px; }
.etb-hub-list { display: flex; flex-direction: column; }
.etb-hub-list .eta-card.eta-post-card { background: transparent; border: 0; border-bottom: 1px solid var(--etb-line); border-radius: 0; box-shadow: none; padding: 0 0 18px; margin: 0 0 18px; transform: none !important; display: grid; grid-template-columns: 148px minmax(0,1fr); gap: 16px; align-items: start; }
.etb-hub-list .eta-post-card img { border-radius: 3px; aspect-ratio: 4/3; width: 100%; height: auto; object-fit: cover; margin: 0; }
.etb-hub-list .eta-post-card h3 { font-family: 'Outfit', sans-serif; font-weight: 300; font-size: 19px; line-height: 1.3; margin: 0 0 6px; }
.etb-hub-list .eta-post-card h3 a { color: var(--etb-ink); text-decoration: none; }
.etb-hub-list .eta-post-card h3 a:hover { color: var(--etb-emerald); }
.etb-hub-list .eta-post-card p { font-size: 13px; line-height: 1.5; color: var(--etb-sage); margin: 0 0 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.etb-hub-list .eta-text-link { font-family: 'Archivo', sans-serif; font-size: 11.5px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: var(--etb-emerald); }
.etb-hub-list .etb-actions { margin-top: 6px; }

/* ---------- 10 final conversion scene ---------- */
.etb-final { position: relative; overflow: hidden; background: var(--etb-char); padding: 108px 0 118px; text-align: center; }
.etb-final::before { content: ""; position: absolute; inset: 0; background: radial-gradient(ellipse 70% 55% at 50% 30%, rgba(31,107,84,.32), transparent 70%); }
.etb-final::after { content: ""; position: absolute; inset: 0; opacity: .28; background: linear-gradient(rgba(120,200,165,.07) 1px, transparent 1px), linear-gradient(90deg, rgba(120,200,165,.07) 1px, transparent 1px); background-size: 72px 72px; transform: perspective(900px) rotateX(55deg) translateY(30%); transform-origin: 50% 100%; }
.etb-final-inner { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; }
.etb-final .etb-num { margin-bottom: 14px; }
.etb-final-h { font-family: 'Outfit', sans-serif; font-weight: 200; font-size: clamp(38px, 5vw, 78px); line-height: 1.1; letter-spacing: .03em; color: #F2FAF5; margin: 0 0 20px; }
.etb-final .etb-lead { max-width: 640px; margin-bottom: 22px; }
.etb-final .etb-microlabel { margin-bottom: 8px; }
.etb-final-actions { justify-content: center; }

/* ---------- responsive ---------- */
@media (min-width: 1021px) {
  .etb-journey-head { position: sticky; top: calc(var(--eta-hh, 90px) + 48px); }
}
@media (max-width: 1020px) {
  .etb-obs-grid, .etb-journey-grid, .etb-matrix-grid, .etb-maritime-grid, .etb-why-grid, .etb-hub-grid { grid-template-columns: 1fr; gap: 44px; }
  .etb-matrix-detail { display: none; }
  .etb-matrix-row .etb-matrix-svc { display: block; }
  .etb-obs-index { position: static; }
}
@media (max-width: 820px) {
  .etb-ledger-row { grid-template-columns: 1fr 1fr; }
  .etb-ledger-item { border-right: 0; border-bottom: 1px solid rgba(125,232,205,.09); }
  .etb-obs-index { display: none; }
  .etb-obs-planes { min-height: 0; display: flex; flex-direction: column; gap: 40px; }
  .etb-obs-plane { position: static; opacity: 1; transform: none; pointer-events: auto; }
  .etb-vault-row { grid-template-columns: 44px 1fr; grid-template-rows: auto auto; gap: 4px 14px; }
  .etb-vault-logo { grid-row: 1 / span 2; }
  .etb-vault-loc { grid-column: 2; text-align: left; }
  .etb-proofs { grid-template-columns: 1fr; }
  .etb-obs, .etb-journey, .etb-matrix, .etb-maritime, .etb-vault, .etb-why, .etb-hub { padding-top: 72px; padding-bottom: 76px; }
  .etb-final { padding: 96px 0 104px; }
  .etb-steps { padding-left: 24px; }
}

/* ============================================================
   VERTICAL RHYTHM
   The parent theme applies `.site-main > * { margin-bottom: 20px }`,
   which inserted a stray 20px between every full-bleed act on top of
   padding that ranged 76-118px with no shared scale — so the seam
   between sections read as an accident rather than a decision.
   One fluid scale governs every act; the closing act gets one step
   more air to signal arrival. Declared last so it also supersedes
   the per-section and breakpoint padding set above.
   ============================================================ */
.site-main > #ets-twin,
.site-main > section[class*="etb-"] { margin-bottom: 0; }

.etb-obs, .etb-journey, .etb-matrix, .etb-maritime,
.etb-vault, .etb-why, .etb-hub {
  padding-top: clamp(64px, 5.6vw, 96px);
  padding-bottom: clamp(64px, 5.6vw, 96px);
}

.etb-final {
  padding-top: clamp(80px, 7vw, 120px);
  padding-bottom: clamp(80px, 7vw, 120px);
}

/* The ledger strip is a rule between acts, not an act of its own. */
.etb-ledger { padding-top: clamp(48px, 4vw, 68px); padding-bottom: clamp(48px, 4vw, 68px); }
</style>
<?php
/* ======== Homepage body v7 — act-based premium architecture — 28-08-2026 ======== */
$etb_params = [
    'IEE · EIA · EMP · EMR · Audits · SEPA submissions',
    'Chemistry · Microbiology · Metals · QA/QC · Verification',
    'pH · TDS · TSS · COD · BOD · Metals · Microbiology',
    'Field instruments · Monitoring equipment · Laboratory measurement',
];
$etb_sectors = [
    ['Textile', 'Effluent · ETP monitoring · Air emissions · Compliance reporting'],
    ['Leather', 'Effluent · Process chemicals · Workplace monitoring'],
    ['Food & Beverage', 'Water quality · Wastewater · Hygiene monitoring'],
    ['Pharma', 'Process water · Ambient environment · Industrial hygiene'],
    ['Hotels', 'Water quality · Noise · Indoor environment'],
    ['Hospitals', 'Water testing · Waste · Indoor air'],
    ['Construction', 'Noise · Dust · IEE / EIA support'],
    ['Oil & Gas', 'Stack emissions · Noise · Waste · Monitoring'],
    ['Cement', 'Stack emissions · Ambient air · Noise'],
    ['Commercial Facilities', 'Water · Indoor environment · Compliance'],
];
$etb_vault = [
    ['PNAC', 'ISO/IEC 17025 accreditation', 'LAB-285 · Karachi', 'https://envitechal.com/wp-content/uploads/2026/04/iso-iec-170252017-accreditation-logo.png'],
    ['PNAC', 'ISO/IEC 17025 accreditation', 'LAB-347 · Lahore', 'https://envitechal.com/wp-content/uploads/2026/04/iso-iec-170252017-accreditation-logo.png'],
    ['Sindh EPA', 'Environmental laboratory credential', 'Karachi', 'https://envitechal.com/wp-content/uploads/2026/04/sepa-sindh-logo.png'],
    ['Punjab EPA', 'Certified laboratory', 'Lahore · 2025–2028', 'https://envitechal.com/wp-content/uploads/2026/04/epa-new-logo-1.jpg'],
    ['ISO 9001:2015', 'Quality management system', 'Organisation-wide', 'https://envitechal.com/wp-content/uploads/2026/04/iso-9001-2015-logo.png'],
    ['ISO 14001:2015', 'Environmental management system', 'Organisation-wide', 'https://envitechal.com/wp-content/uploads/2026/04/iso-14001-2015-logo.png'],
];
?>

    <!-- ACT I · 02 — TRUST LEDGER -->
    <section class="etb-ledger etb-dark" aria-label="<?php esc_attr_e('Accreditations and coverage', 'envi-tech-al-modern'); ?>">
        <div class="etb-shell etb-ledger-row">
            <div class="etb-ledger-item etb-r">
                <span class="etb-ledger-auth">PNAC <i class="etb-dot" aria-hidden="true"></i></span>
                <strong>ISO/IEC 17025</strong>
                <span class="etb-ledger-sub">LAB-285 · Karachi</span>
            </div>
            <div class="etb-ledger-item etb-r">
                <span class="etb-ledger-auth">PNAC <i class="etb-dot" aria-hidden="true"></i></span>
                <strong>ISO/IEC 17025</strong>
                <span class="etb-ledger-sub">LAB-347 · Lahore</span>
            </div>
            <div class="etb-ledger-item etb-r">
                <span class="etb-ledger-auth"><?php esc_html_e('Field + Lab', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('One controlled workflow', 'envi-tech-al-modern'); ?></strong>
                <span class="etb-ledger-sub"><?php esc_html_e('Sampling to defensible report', 'envi-tech-al-modern'); ?></span>
            </div>
            <div class="etb-ledger-item etb-r">
                <span class="etb-ledger-auth"><?php esc_html_e('Karachi + Lahore', 'envi-tech-al-modern'); ?></span>
                <strong><?php esc_html_e('Regional coverage', 'envi-tech-al-modern'); ?></strong>
                <span class="etb-ledger-sub"><?php esc_html_e('Responsive industrial support', 'envi-tech-al-modern'); ?></span>
            </div>
        </div>
    </section>

    <!-- ACT I · 03 — SERVICE OBSERVATORY -->
    <section class="etb-obs etb-dark" id="etb-obs">
        <div class="etb-shell">
            <header class="etb-head etb-r"><meta charset="utf-8">
                <span class="etb-num" aria-hidden="true">01</span>
                <p class="etb-eyebrow"><?php esc_html_e('Core services', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('What we measure. What we solve.', 'envi-tech-al-modern'); ?></h2>
                <p class="etb-lead"><?php esc_html_e('Environmental testing and compliance services built for serious operations. Accurate lab results, practical advisory, and documentation that stands up in front of regulators, buyers, and auditors.', 'envi-tech-al-modern'); ?></p>
            </header>
            <div class="etb-obs-grid">
                <nav class="etb-obs-index" aria-label="<?php esc_attr_e('Service index', 'envi-tech-al-modern'); ?>">
                    <?php foreach ($service_tiles as $i => $tile) : ?>
                    <a href="<?php echo esc_url($tile['url']); ?>" class="etb-obs-link<?php echo $i === 0 ? ' is-on' : ''; ?>" data-obs="<?php echo (int) $i; ?>">
                        <span class="etb-obs-i"><?php echo esc_html(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></span>
                        <span class="etb-obs-t"><?php echo esc_html($tile['title']); ?></span>
                        <span class="etb-arrow" aria-hidden="true">&rarr;</span>
                    </a>
                    <?php endforeach; ?>
                </nav>
                <div class="etb-obs-planes">
                    <?php foreach ($service_tiles as $i => $tile) : ?>
                    <article class="etb-obs-plane<?php echo $i === 0 ? ' is-on' : ''; ?>" data-plane="<?php echo (int) $i; ?>">
                        <div class="etb-obs-media"><?php eta_modern_home_image($tile['image'], '', $tile['title'], 'medium_large', 'lazy', '(max-width: 820px) 92vw, 46vw'); ?></div>
                        <div class="etb-obs-body">
                            <p class="etb-microlabel"><?php echo esc_html($tile['kicker']); ?></p>
                            <h3 class="etb-obs-h"><?php echo esc_html($tile['title']); ?></h3>
                            <p class="etb-obs-p"><?php echo esc_html($tile['text']); ?></p>
                            <p class="etb-params"><?php echo esc_html($etb_params[$i]); ?></p>
                            <a class="etb-link" href="<?php echo esc_url($tile['url']); ?>"><?php esc_html_e('Explore service', 'envi-tech-al-modern'); ?> <span class="etb-arrow" aria-hidden="true">&rarr;</span></a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ACT I · 04 — COMPLIANCE JOURNEY -->
    <section class="etb-journey etb-dark" id="etb-journey">
        <div class="etb-shell etb-journey-grid">
            <div class="etb-journey-head">
                <header class="etb-head etb-r">
                    <span class="etb-num" aria-hidden="true">02</span>
                    <p class="etb-eyebrow"><?php esc_html_e('Compliance workflow', 'envi-tech-al-modern'); ?></p>
                    <h2><?php esc_html_e('From requirement to defensible decision.', 'envi-tech-al-modern'); ?></h2>
                    <p class="etb-lead"><?php esc_html_e('From sample to submission without losing the thread. A controlled path from scope selection to sampling, analysis, reporting, and compliance support.', 'envi-tech-al-modern'); ?></p>
                </header>
                <div class="etb-journey-tags etb-r">
                    <span><?php esc_html_e('Scope matched to sample, permit, buyer, or audit need', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Field monitoring, lab testing, and technical review', 'envi-tech-al-modern'); ?></span>
                    <span><?php esc_html_e('Reports, advisory context, and verification support', 'envi-tech-al-modern'); ?></span>
                </div>
            </div>
            <ol class="etb-steps" data-trace>
                <li class="etb-r"><span class="etb-step-n">01</span><div><strong><?php esc_html_e('Requirement', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Clarify whether the report is for EPA submission, buyer compliance, plant troubleshooting, audit readiness, or internal risk control.', 'envi-tech-al-modern'); ?></p></div></li>
                <li class="etb-r"><span class="etb-step-n">02</span><div><strong><?php esc_html_e('Field', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Coordinate sampling, environmental monitoring, and inspection with documented custody.', 'envi-tech-al-modern'); ?></p></div></li>
                <li class="etb-r"><span class="etb-step-n">03</span><div><strong><?php esc_html_e('Lab', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Analytical testing, calibration, and quality documentation under accredited methods.', 'envi-tech-al-modern'); ?></p></div></li>
                <li class="etb-r"><span class="etb-step-n">04</span><div><strong><?php esc_html_e('Intelligence', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Interpretation against the applicable limits and the compliance status that matters.', 'envi-tech-al-modern'); ?></p></div></li>
                <li class="etb-r"><span class="etb-step-n">05</span><div><strong><?php esc_html_e('Report', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Usable findings, practical next steps, and verification pathways for stakeholders.', 'envi-tech-al-modern'); ?></p></div></li>
                <li class="etb-r"><span class="etb-step-n">06</span><div><strong><?php esc_html_e('Action', 'envi-tech-al-modern'); ?></strong><p><?php esc_html_e('Submission, corrective action, and measurable environmental improvement.', 'envi-tech-al-modern'); ?></p></div></li>
            </ol>
        </div>
    </section>

    <!-- ACT II · 03 — SECTOR MATRIX -->
    <section class="etb-matrix" id="etb-matrix">
        <div class="etb-shell">
            <header class="etb-head etb-r">
                <span class="etb-num" aria-hidden="true">03</span>
                <p class="etb-eyebrow"><?php esc_html_e('Industry coverage', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Trusted across compliance-critical sectors', 'envi-tech-al-modern'); ?></h2>
                <p class="etb-lead"><?php esc_html_e('Environmental support for operations where testing quality, response time, and documentation clarity directly affect approvals, shipments, safety, and reputation.', 'envi-tech-al-modern'); ?></p>
            </header>
            <div class="etb-matrix-grid">
                <ul class="etb-matrix-list">
                    <?php foreach ($etb_sectors as $i => $s) : ?>
                    <li class="etb-matrix-row etb-r" data-sector="<?php echo (int) $i; ?>" tabindex="0">
                        <span class="etb-matrix-name"><?php echo esc_html($s[0]); ?></span>
                        <span class="etb-matrix-svc"><?php echo esc_html($s[1]); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <aside class="etb-matrix-detail" aria-hidden="true">
                    <p class="etb-microlabel"><?php esc_html_e('Sector focus', 'envi-tech-al-modern'); ?></p>
                    <p class="etb-matrix-big" data-matrix-name><?php echo esc_html($etb_sectors[0][0]); ?></p>
                    <p class="etb-matrix-lines" data-matrix-svc><?php echo esc_html($etb_sectors[0][1]); ?></p>
                    <p class="etb-matrix-actions">
                        <a class="etb-link" href="<?php echo esc_url(home_url('/karachi-environmental-lab/')); ?>"><?php esc_html_e('Environmental laboratory support in Karachi', 'envi-tech-al-modern'); ?> <span class="etb-arrow" aria-hidden="true">&rarr;</span></a>
                    </p>
                    <a class="etb-btn etb-btn-solid" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Discuss your facility', 'envi-tech-al-modern'); ?> <span class="etb-arrow" aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>
        </div>
    </section>

    <!-- ACT II · 04 — MARITIME SPOTLIGHT -->
    <section class="etb-maritime etb-dark" id="etb-maritime">
        <div class="etb-sea" aria-hidden="true">
            <svg class="etb-wave etb-wave-1" viewBox="0 0 1440 120" preserveAspectRatio="none"><path d="M0 60 Q 180 20 360 60 T 720 60 T 1080 60 T 1440 60" fill="none" stroke="rgba(125,214,232,.20)" stroke-width="1.2"/></svg>
            <svg class="etb-wave etb-wave-2" viewBox="0 0 1440 120" preserveAspectRatio="none"><path d="M0 70 Q 240 30 480 70 T 960 70 T 1440 70" fill="none" stroke="rgba(125,214,232,.12)" stroke-width="1"/></svg>
            <svg class="etb-wave etb-wave-3" viewBox="0 0 1440 120" preserveAspectRatio="none"><path d="M0 50 Q 120 90 240 50 T 480 50 T 720 50 T 960 50 T 1200 50 T 1440 50" fill="none" stroke="rgba(125,214,232,.07)" stroke-width="1"/></svg>
        </div>
        <div class="etb-shell etb-maritime-grid">
            <div>
                <span class="etb-num" aria-hidden="true">04</span>
                <p class="etb-microlabel etb-r"><?php esc_html_e('Ballast water / vessel sampling / Karachi port / lab analysis', 'envi-tech-al-modern'); ?></p>
                <h2 class="etb-r"><?php esc_html_e('Open water. Controlled data.', 'envi-tech-al-modern'); ?></h2>
                <p class="etb-lead etb-r"><?php esc_html_e('Ballast water testing support for vessels that need fast, defensible results. Sampling coordination at Karachi port, scope-confirmed laboratory methods, and compliance-focused reporting for audit and inspection readiness.', 'envi-tech-al-modern'); ?></p>
                <a class="etb-btn etb-btn-ghost etb-r" href="<?php echo esc_url(home_url('/services/ballast-water-testing-services/')); ?>"><?php esc_html_e('Explore ballast water testing', 'envi-tech-al-modern'); ?> <span class="etb-arrow" aria-hidden="true">&rarr;</span></a>
            </div>
            <ul class="etb-ticks etb-r">
                <li><?php esc_html_e('Pathogen detection and invasive species screening', 'envi-tech-al-modern'); ?></li>
                <li><?php esc_html_e('Port-call planning support in Karachi', 'envi-tech-al-modern'); ?></li>
                <li><?php esc_html_e('Fast reporting for marine compliance teams', 'envi-tech-al-modern'); ?></li>
            </ul>
        </div>
    </section>

    <!-- ACT III · 05 — CREDENTIAL VAULT -->
    <section class="etb-vault" id="etb-vault">
        <div class="etb-shell">
            <header class="etb-head etb-r">
                <span class="etb-num" aria-hidden="true">05</span>
                <p class="etb-eyebrow"><?php esc_html_e('Trusted credentials', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Credentials behind the data.', 'envi-tech-al-modern'); ?></h2>
                <p class="etb-lead"><?php esc_html_e('Certifications, approvals, and quality systems. Visible proof points for customers who need confidence before they hand over a compliance-critical requirement.', 'envi-tech-al-modern'); ?></p>
            </header>
            <div class="etb-vault-table" role="list">
                <?php foreach ($etb_vault as $row) : ?>
                <div class="etb-vault-row etb-r" role="listitem">
                    <span class="etb-vault-logo"><img src="<?php echo esc_url($row[3]); ?>" alt="<?php echo esc_attr($row[0]); ?>" loading="lazy" width="44" height="44"></span>
                    <span class="etb-vault-auth"><?php echo esc_html($row[0]); ?></span>
                    <span class="etb-vault-cred"><?php echo esc_html($row[1]); ?></span>
                    <span class="etb-vault-loc"><?php echo esc_html($row[2]); ?><i class="etb-dot" aria-hidden="true"></i></span>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="etb-footnote etb-r"><?php esc_html_e('Accreditation applies to methods included within the respective approved scope.', 'envi-tech-al-modern'); ?></p>
            <div class="etb-actions etb-r">
                <a class="etb-btn etb-btn-line" href="<?php echo esc_url(home_url('/accreditations-certifications/')); ?>"><?php esc_html_e('Review certifications and approvals', 'envi-tech-al-modern'); ?></a>
                <a class="etb-btn etb-btn-line" href="<?php echo esc_url(home_url('/environmental-testing-faqs-pakistan/')); ?>"><?php esc_html_e('Read environmental testing FAQs', 'envi-tech-al-modern'); ?></a>
                <a class="etb-btn etb-btn-line" href="<?php echo esc_url(home_url('/report-verification-portal/')); ?>"><?php esc_html_e('Verify a report', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>

    <!-- ACT III · 06 — WHY THE SYSTEM WORKS -->
    <section class="etb-why" id="etb-why">
        <div class="etb-shell etb-why-grid">
            <div>
                <header class="etb-head etb-r">
                    <span class="etb-num" aria-hidden="true">06</span>
                    <p class="etb-eyebrow"><?php esc_html_e('Why Envi Tech AL', 'envi-tech-al-modern'); ?></p>
                    <h2><?php esc_html_e('Why the system works.', 'envi-tech-al-modern'); ?></h2>
                    <p class="etb-lead"><?php esc_html_e('Laboratory capability, field execution, and regulatory thinking in one team, helping customers move faster on approvals, corrective actions, buyer requirements, and compliance-critical reporting.', 'envi-tech-al-modern'); ?></p>
                </header>
                <div class="etb-proofs">
                    <div class="etb-proof etb-r"><strong>FIELD + LAB</strong><p><?php esc_html_e('One controlled chain from sampling to analysis.', 'envi-tech-al-modern'); ?></p></div>
                    <div class="etb-proof etb-r"><strong>METHOD + SCOPE</strong><p><?php esc_html_e('Testing aligned with the appropriate approved or required method.', 'envi-tech-al-modern'); ?></p></div>
                    <div class="etb-proof etb-r"><strong>DATA + CONTEXT</strong><p><?php esc_html_e('Results interpreted for the decision the client needs to make.', 'envi-tech-al-modern'); ?></p></div>
                    <div class="etb-proof etb-r"><strong>KARACHI + LAHORE</strong><p><?php esc_html_e('Regional support for major industrial markets.', 'envi-tech-al-modern'); ?></p></div>
                </div>
            </div>
            <div class="etb-feedback">
                <p class="etb-microlabel etb-r"><?php esc_html_e('Selected clients', 'envi-tech-al-modern'); ?></p>
                <p class="etb-clients-lead etb-r"><?php esc_html_e('Organisations that rely on Envi Tech AL for testing, monitoring, calibration and compliance reporting.', 'envi-tech-al-modern'); ?></p>
                <ul class="etb-clients etb-r">
                    <?php
                    $eta_clients = [
                        'Agha Khan University Hospital',
                        'WWF',
                        'Soorty Enterprises',
                        'Artistic Milliners',
                        'Greaves Pakistan',
                        'Pearl Continental Hotel Karachi',
                        'Movenpick Hotel Karachi',
                        'National Medical Center',
                        'Hamdard University Group',
                        'United Towel Exporters',
                        'Crown Textile',
                        'Rainbow Hosiery Pvt Ltd',
                        'Velosi Pakistan',
                        'Power China Gansu Energy',
                        'Patrind O&amp;M Private Limited',
                        'Fabritex Enterprises',
                        'Vee Chem Industries',
                        'B.H.Y Hospital',
                    ];
                    foreach ($eta_clients as $eta_client) {
                        echo '<li>' . $eta_client . '</li>';
                    }
                    ?>
                </ul>
                <div class="etb-actions etb-r">
                    <a class="etb-btn etb-btn-line" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Discuss your requirement', 'envi-tech-al-modern'); ?></a>
                    <a class="etb-link" href="<?php echo esc_url(home_url('/ourclients/')); ?>"><?php esc_html_e('Full client portfolio', 'envi-tech-al-modern'); ?> <span class="etb-arrow" aria-hidden="true">&rarr;</span></a>
                </div>
            </div>
        </div>
    </section>

    <!-- ACT III · 07 — KNOWLEDGE HUB -->
    <section class="etb-hub" id="etb-hub">
        <div class="etb-shell">
            <header class="etb-head etb-r">
                <span class="etb-num" aria-hidden="true">07</span>
                <p class="etb-eyebrow"><?php esc_html_e('Knowledge hub', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Environmental intelligence.', 'envi-tech-al-modern'); ?></h2>
                <p class="etb-lead"><?php esc_html_e('Recent compliance, testing, and environmental guidance for regulated operations in Pakistan.', 'envi-tech-al-modern'); ?></p>
            </header>
            <div class="etb-hub-grid">
                <article class="etb-hub-feature etb-r">
                    <img class="etb-hub-feature-img"
                         src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/eta-hub-seqs-1320.webp'); ?>"
                         srcset="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/eta-hub-seqs-760.webp'); ?> 760w, <?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/eta-hub-seqs-1320.webp'); ?> 1320w"
                         sizes="(max-width: 1020px) 100vw, 56vw"
                         width="1320" height="737" alt="" aria-hidden="true" loading="lazy" decoding="async"
                         data-spai-excluded="true">
                    <p class="etb-microlabel"><?php esc_html_e('Regulation / Sindh', 'envi-tech-al-modern'); ?></p>
                    <h3><a href="<?php echo esc_url(home_url('/sindh-environmental-quality-standards-seqs/')); ?>"><?php esc_html_e('Sindh Environmental Quality Standards guide', 'envi-tech-al-modern'); ?></a></h3>
                    <p class="etb-hub-p"><?php esc_html_e('A practical SEQS limits and compliance guide for Sindh facilities preparing testing, monitoring, and EMR submissions.', 'envi-tech-al-modern'); ?></p>
                    <a class="etb-link etb-link-light" href="<?php echo esc_url(home_url('/sindh-environmental-quality-standards-seqs/')); ?>"><?php esc_html_e('Read the guide', 'envi-tech-al-modern'); ?> <span class="etb-arrow" aria-hidden="true">&rarr;</span></a>
                </article>
                <div class="etb-hub-list etb-r">
                    <?php foreach (eta_modern_latest_posts(3) as $post_item) : ?>
                        <?php eta_modern_card_link($post_item, 'eta-post-card'); ?>
                    <?php endforeach; ?>
                    <div class="etb-actions">
                        <a class="etb-btn etb-btn-line" href="<?php echo esc_url(home_url('/blognewsupdates/')); ?>"><?php esc_html_e('View all updates', 'envi-tech-al-modern'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ACT III · 08 — FINAL CONVERSION SCENE -->
    <section class="etb-final etb-dark" id="etb-final">
        <div class="etb-shell etb-final-inner">
            <span class="etb-num" aria-hidden="true">08</span>
            <p class="etb-eyebrow etb-r"><?php esc_html_e('Need EPA / buyer / audit-ready environmental testing?', 'envi-tech-al-modern'); ?></p>
            <h2 class="etb-final-h etb-r"><?php esc_html_e('Have a requirement?', 'envi-tech-al-modern'); ?> <br><?php esc_html_e('Send us the scope.', 'envi-tech-al-modern'); ?></h2>
            <p class="etb-lead etb-r"><?php esc_html_e('Send the requirement before the deadline becomes urgent. We confirm scope for testing, monitoring, reporting, calibration, and consultancy to match your operational timelines.', 'envi-tech-al-modern'); ?></p>
            <p class="etb-microlabel etb-r"><?php esc_html_e('Testing · Monitoring · Consultancy · Calibration', 'envi-tech-al-modern'); ?></p>
            <div class="etb-actions etb-final-actions etb-r">
                <a class="etb-btn etb-btn-solid" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request a quotation', 'envi-tech-al-modern'); ?> <span class="etb-arrow" aria-hidden="true">&rarr;</span></a>
                <a class="etb-btn etb-btn-ghost" href="<?php echo esc_url('https://wa.me/923102288801'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
</main>

<script src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/eta-home-body.js?v=3" defer data-no-optimize="1" data-no-defer="1" data-litespeed-noopt="1"></script>

<?php
get_footer();
