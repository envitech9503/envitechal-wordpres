/* Envi Tech AL — Water Testing Lab Services flagship
 * Hero: a Three.js water surface, built as a dense point sheet displaced by
 * layered sine waves in the vertex shader. As the visitor scrolls the chop
 * settles into a calm, ordered plane and the colour arc lifts from a turbid
 * deep-sea murk to a clear aqua, while sample droplets rise through the
 * column. Below the hero, GSAP + ScrollTrigger drive the pinned water-types
 * track, the parameter explorer, a drawn journey line and the reveals.
 * Lenis smooths the scroll ScrollTrigger reads.
 * Progressive enhancement: html.wt-gsap is added by the inline bootstrap only
 * when motion is allowed; this module removes it again if WebGL is missing so
 * the static hero stands. 13-09-2026 */

import * as THREE from './assets/js/vendor/three-slim.js';
import { gsap, ScrollTrigger, Lenis } from './assets/js/vendor/motion.js';

(function () {
  'use strict';
  var doc = document.documentElement;
  var root = document.getElementById('eta-water');
  if (!root) return;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  doc.classList.add('wt-js');

  /* ================= PARAMETER EXPLORER: tabs =================
     Wired before the motion gate so the explorer works with reduced motion. */
  var tabs = root.querySelectorAll('[data-wt-tab]');
  var panels = root.querySelectorAll('[data-wt-panel]');
  function showPanel(k) {
    tabs.forEach(function (b) { var on = b.getAttribute('data-wt-tab') === k; b.classList.toggle('is-on', on); b.setAttribute('aria-selected', on ? 'true' : 'false'); });
    panels.forEach(function (pnl) {
      var on = pnl.getAttribute('data-wt-panel') === k;
      pnl.classList.toggle('is-on', on);
      pnl.hidden = !on;
      if (on && !reduced) {
        gsap.fromTo(pnl, { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.45, ease: 'power3.out', overwrite: true });
        gsap.fromTo(pnl.querySelectorAll('.wt-chips li'), { opacity: 0, y: 8 }, { opacity: 1, y: 0, duration: 0.4, stagger: 0.02, ease: 'power2.out', overwrite: true });
      }
    });
    if (!reduced) ScrollTrigger.refresh();
  }
  tabs.forEach(function (b) {
    b.addEventListener('click', function () { showPanel(b.getAttribute('data-wt-tab')); });
    b.addEventListener('keydown', function (e) {
      var i = Array.prototype.indexOf.call(tabs, b), j = -1;
      if (e.key === 'ArrowDown' || e.key === 'ArrowRight') j = (i + 1) % tabs.length;
      if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') j = (i - 1 + tabs.length) % tabs.length;
      if (j < 0) return;
      e.preventDefault(); tabs[j].focus(); tabs[j].click();
    });
  });

  if (reduced) { doc.classList.remove('wt-gsap'); return; }
  doc.classList.add('wt-gsap');

  var isMobile = window.matchMedia('(max-width: 860px), (pointer: coarse)').matches;
  gsap.registerPlugin(ScrollTrigger);

  /* ---------------- Lenis ---------------- */
  var lenis = window.__etaLenis || new Lenis({
    duration: 0.95,
    smoothWheel: true,
    syncTouch: false,
    easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); }
  });
  window.__etaLenis = lenis;
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add(function (t) { lenis.raf(t * 1000); });
  gsap.ticker.lagSmoothing(0);

  root.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = a.getAttribute('href').slice(1);
      var target = document.getElementById(id);
      if (!target) return;
      e.preventDefault();
      lenis.scrollTo(target, { offset: -(parseInt(getComputedStyle(doc).getPropertyValue('--eta-hh')) || 0) - 12 });
    });
  });

  /* ================= HERO SCENE ================= */
  var hero = root.querySelector('.wt-hero');
  var stage = root.querySelector('.wt-stage');
  var canvas = root.querySelector('.wt-gl');
  var gaugeBar = root.querySelector('.wt-gauge-bar i');
  var gaugeVal = root.querySelector('.wt-gauge-v');
  var state = { p: 0, tp: 0, px: 0, py: 0, spx: 0, spy: 0, running: false };

  var renderer, scene, camera, surface, drops, uniforms, dropUniforms;
  var seed = 7;
  function rnd() { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; }

  function initGL() {
    try {
      renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: false, alpha: false, powerPreference: 'high-performance' });
    } catch (e) { return false; }
    if (!renderer.getContext()) return false;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 1.75));

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x03111f);
    scene.fog = new THREE.Fog(0x03111f, 18, 70);
    camera = new THREE.PerspectiveCamera(isMobile ? 58 : 46, 1, 0.1, 200);

    /* --- water surface: a point sheet displaced by waves in the shader --- */
    var cols = isMobile ? 110 : 200, rows = isMobile ? 70 : 130;
    var W = 80, D = 60, n = cols * rows;
    var pos = new Float32Array(n * 3), seeds = new Float32Array(n);
    for (var r = 0; r < rows; r++) {
      for (var c = 0; c < cols; c++) {
        var i = r * cols + c;
        pos[i * 3] = (c / (cols - 1) - 0.5) * W;
        pos[i * 3 + 1] = 0;
        pos[i * 3 + 2] = -(r / (rows - 1)) * D + 5;
        seeds[i] = rnd();
      }
    }
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    geo.setAttribute('aSeed', new THREE.BufferAttribute(seeds, 1));

    uniforms = {
      uTime: { value: 0 },
      uClear: { value: 0 },
      uPx: { value: 0 },
      uPy: { value: 0 },
      uSize: { value: isMobile ? 5.5 : 6.5 },
      uColA: { value: new THREE.Color(0x7fa08c) },
      uColB: { value: new THREE.Color(0x35d6ff) }
    };
    var mat = new THREE.ShaderMaterial({
      uniforms: uniforms,
      transparent: true,
      depthWrite: false,
      blending: THREE.AdditiveBlending,
      vertexShader: [
        'attribute float aSeed;',
        'uniform float uTime, uClear, uPx, uPy, uSize;',
        'varying float vSeed, vH, vDepth;',
        'float ease(float t){ return t*t*(3.0-2.0*t); }',
        'void main(){',
        '  float k = ease(uClear);',
        '  float chop = 1.0 - k;',
        '  vec3 p = position;',
        // turbid: three crossing wave trains plus per-point jitter; clear: one long, slow swell
        '  float w1 = sin(p.x*0.55 + uTime*1.6) * 0.9;',
        '  float w2 = sin(p.z*0.8 - uTime*1.3 + p.x*0.2) * 0.7;',
        '  float w3 = sin((p.x+p.z)*1.4 + uTime*2.4) * 0.35;',
        '  float jit = (aSeed - 0.5) * 0.8 * sin(uTime*3.0 + aSeed*30.0);',
        '  float swell = sin(p.x*0.12 + uTime*0.5) * cos(p.z*0.15 + uTime*0.35) * 0.35;',
        '  p.y = (w1 + w2 + w3 + jit) * chop + swell * (0.3 + 0.7*k);',
        '  p.x += uPx * 1.2; p.y += uPy * 0.5;',
        '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
        '  gl_Position = projectionMatrix * mv;',
        '  gl_PointSize = min(uSize * (0.85 + 0.3*aSeed) * (16.0 / -mv.z), 14.0);',
        '  vSeed = aSeed; vH = p.y; vDepth = -mv.z;',
        '}'
      ].join('\n'),
      fragmentShader: [
        'uniform vec3 uColA, uColB;',
        'uniform float uClear;',
        'varying float vSeed, vH, vDepth;',
        'void main(){',
        '  vec2 c = gl_PointCoord - 0.5; float d = length(c);',
        '  if (d > 0.5) discard;',
        '  float core = smoothstep(0.5, 0.08, d);',
        '  float crest = clamp(vH * 0.5 + 0.5, 0.0, 1.0);',
        '  vec3 col = mix(uColA, uColB, crest * 0.6 + uClear * 0.4);',
        '  float fade = clamp(1.0 - (vDepth - 16.0) / 60.0, 0.15, 1.0);',
        '  gl_FragColor = vec4(col, core * (0.8 + 0.2*uClear) * fade);',
        '}'
      ].join('\n')
    });
    surface = new THREE.Points(geo, mat);
    scene.add(surface);

    /* --- sample droplets rising through the column --- */
    var M = isMobile ? 160 : 320;
    var dp = new Float32Array(M * 3), ds = new Float32Array(M);
    for (var j = 0; j < M; j++) {
      dp[j * 3] = (rnd() - 0.5) * 44;
      dp[j * 3 + 1] = -6 + rnd() * 16;
      dp[j * 3 + 2] = -(rnd()) * 30 + 2;
      ds[j] = rnd();
    }
    var dg = new THREE.BufferGeometry();
    dg.setAttribute('position', new THREE.BufferAttribute(dp, 3));
    dg.setAttribute('aSeed', new THREE.BufferAttribute(ds, 1));
    dropUniforms = { uTime: { value: 0 }, uClear: { value: 0 }, uCol: { value: new THREE.Color(0x9df0ff) } };
    var dm = new THREE.ShaderMaterial({
      uniforms: dropUniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
      vertexShader: [
        'attribute float aSeed;',
        'uniform float uTime, uClear;',
        'varying float vA;',
        'void main(){',
        '  vec3 p = position;',
        '  float life = fract(uTime * (0.05 + aSeed*0.06) + aSeed);',
        '  p.y = -6.0 + life * 18.0;',
        '  p.x += sin(uTime*0.8 + aSeed*20.0) * 0.6;',
        '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
        '  gl_Position = projectionMatrix * mv;',
        '  gl_PointSize = (3.0 + aSeed*6.0) * (14.0 / -mv.z);',
        '  vA = (1.0 - abs(life*2.0-1.0)) * (0.35 + 0.65*uClear);',
        '}'
      ].join('\n'),
      fragmentShader: [
        'uniform vec3 uCol;',
        'varying float vA;',
        'void main(){',
        '  vec2 c = gl_PointCoord - 0.5; float d = length(c);',
        '  if (d > 0.5) discard;',
        '  float ring = smoothstep(0.5, 0.3, d) - smoothstep(0.3, 0.1, d) * 0.6;',
        '  gl_FragColor = vec4(uCol, ring * vA);',
        '}'
      ].join('\n')
    });
    drops = new THREE.Points(dg, dm);
    scene.add(drops);

    resize();
    return true;
  }

  function resize() {
    if (!renderer) return;
    var w = stage.clientWidth, h = stage.clientHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h; camera.updateProjectionMatrix();
  }

  /* Colour arc: turbid murk -> mid-water blue -> clear aqua. */
  var ARC = [
    { p: 0.0, bg: 0x041524, a: 0x7fa08c, b: 0x6fd0dc },
    { p: 0.5, bg: 0x06203a, a: 0x1673c9, b: 0x35d6ff },
    { p: 1.0, bg: 0x0a3556, a: 0x35d6ff, b: 0xffffff }
  ];
  var cA = new THREE.Color(), cB = new THREE.Color();
  function arc(p, field, target) {
    var i = p < ARC[1].p ? 0 : 1, a = ARC[i], b = ARC[i + 1];
    var t = (p - a.p) / (b.p - a.p); t = Math.max(0, Math.min(1, t)); t = t * t * (3 - 2 * t);
    cA.setHex(a[field]); cB.setHex(b[field]); target.copy(cA).lerp(cB, t);
  }

  var t0 = performance.now();
  function frame() {
    if (!state.running) return;
    var t = (performance.now() - t0) / 1000;
    state.p += (state.tp - state.p) * 0.08;
    state.spx += (state.px - state.spx) * 0.06;
    state.spy += (state.py - state.spy) * 0.06;
    var p = state.p;

    uniforms.uTime.value = t;
    uniforms.uClear.value = p;
    uniforms.uPx.value = state.spx * 0.9;
    uniforms.uPy.value = state.spy * 0.6;
    dropUniforms.uTime.value = t;
    dropUniforms.uClear.value = p;
    arc(p, 'bg', scene.background); scene.fog.color.copy(scene.background);
    arc(p, 'a', uniforms.uColA.value); arc(p, 'b', uniforms.uColB.value);

    // Camera drops from a high, oblique view of the chop to a low glide over the calm sheet.
    camera.position.set(1.5 - p * 1.0 + state.spx * 0.8, 9.0 - p * 4.0 + state.spy * 0.5, 18 - p * 4);
    camera.lookAt(0, -2.0 - p * 0.5, -20);
    surface.rotation.y = state.spx * 0.04;

    renderer.render(scene, camera);
    requestAnimationFrame(frame);
  }

  function setGauge(p) {
    if (gaugeBar) gaugeBar.style.transform = 'scaleX(' + (0.06 + p * 0.94) + ')';
    if (gaugeVal) gaugeVal.textContent = p < 0.3 ? 'Turbid' : p < 0.7 ? 'Settling' : 'Clear';
  }

  if (initGL()) {
    state.running = true;
    frame();
    window.addEventListener('resize', resize);
    if (!isMobile) {
      window.addEventListener('pointermove', function (e) {
        state.px = (e.clientX / window.innerWidth - 0.5) * 2;
        state.py = -(e.clientY / window.innerHeight - 0.5) * 2;
      }, { passive: true });
    }
    ScrollTrigger.create({
      trigger: hero, start: 'top top', end: 'bottom bottom', scrub: true,
      onUpdate: function (st) { state.tp = st.progress; setGauge(st.progress); }
    });
    gsap.to(root.querySelector('.wt-hero-grid'), {
      y: -60, opacity: 0.1, ease: 'none',
      scrollTrigger: { trigger: hero, start: '35% top', end: 'bottom bottom', scrub: true }
    });
    ScrollTrigger.create({
      trigger: hero, start: 'top bottom', end: 'bottom top',
      onEnter: function () { if (!state.running) { state.running = true; frame(); } },
      onEnterBack: function () { if (!state.running) { state.running = true; frame(); } },
      onLeave: function () { state.running = false; },
      onLeaveBack: function () { state.running = false; }
    });
  } else {
    doc.classList.remove('wt-gsap');
  }

  gsap.from(root.querySelectorAll('.wt-title-line'), { yPercent: 110, duration: 1.1, ease: 'power4.out', stagger: 0.09, delay: 0.1 });
  gsap.from(root.querySelectorAll('.wt-eyebrow, .wt-lead, .wt-actions, .wt-hero-panel'), { opacity: 0, y: 18, duration: 0.9, ease: 'power3.out', stagger: 0.08, delay: 0.35 });

  /* ================= REVEALS ================= */
  var revealSel = '.wt-ledger-item, .wt-head, .wt-tabs, .wt-panels, .wt-serve-card, .wt-journey-copy, .wt-why-card, .wt-faq-item, .wt-related-card, .wt-final-grid > *';
  root.querySelectorAll(revealSel).forEach(function (el) { el.setAttribute('data-wt-reveal', ''); });
  var reveal = function (els) { gsap.to(els, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out', stagger: 0.07, overwrite: true }); };
  ScrollTrigger.batch(root.querySelectorAll('[data-wt-reveal]'), { start: 'top 88%', onEnter: reveal, onLeave: reveal });
  var sweep = function () {
    var line = window.innerHeight * 0.88, due = [];
    root.querySelectorAll('[data-wt-reveal]').forEach(function (el) {
      if (el.getBoundingClientRect().top < line && getComputedStyle(el).opacity !== '1') due.push(el);
    });
    if (due.length) reveal(due);
  };
  ScrollTrigger.addEventListener('refresh', sweep);
  lenis.on('scroll', function () { if (sweep._t) return; sweep._t = setTimeout(function () { sweep._t = 0; sweep(); }, 120); });
  window.addEventListener('load', sweep);

  /* ================= WATER TYPES: pinned horizontal ================= */
  var track = root.querySelector('[data-wt-track]');
  var types = root.querySelector('.wt-types');
  var cards = root.querySelectorAll('[data-wt-card]');
  var bar = root.querySelector('.wt-types-bar i');
  var count = root.querySelector('.wt-types-count');
  if (track && !isMobile) {
    var distance = function () { return Math.max(0, track.scrollWidth - types.clientWidth + 24); };
    gsap.to(track, {
      x: function () { return -distance(); },
      ease: 'none',
      scrollTrigger: {
        trigger: types, start: 'top top', end: function () { return '+=' + (distance() + 200); },
        pin: true, scrub: 0.6, invalidateOnRefresh: true, anticipatePin: 1,
        onUpdate: function (st) {
          var i = Math.min(cards.length - 1, Math.floor(st.progress * cards.length + 0.001));
          cards.forEach(function (c, k) { c.classList.toggle('is-on', k === i); });
          if (bar) bar.style.transform = 'scaleX(' + (1 + st.progress * (cards.length - 1)) + ')';
          if (count) count.textContent = ('0' + (i + 1)).slice(-2) + ' / 0' + cards.length;
        }
      }
    });
    if (bar) bar.style.width = (100 / cards.length) + '%';
    cards[0].classList.add('is-on');
  } else {
    cards.forEach(function (c) { c.classList.add('is-on'); });
  }

  /* ================= JOURNEY: line draws, dots light ================= */
  var path = root.querySelector('.wt-journey-line path');
  var steps = root.querySelectorAll('[data-wt-step]');
  if (path) {
    gsap.to(path, {
      strokeDashoffset: 0, ease: 'none',
      scrollTrigger: {
        trigger: root.querySelector('.wt-journey-list'), start: 'top 70%', end: 'bottom 60%', scrub: true,
        onUpdate: function (st) {
          var n = Math.round(st.progress * steps.length + 0.25);
          steps.forEach(function (li, k) { li.classList.toggle('is-on', k < n); });
        }
      }
    });
  }

  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
