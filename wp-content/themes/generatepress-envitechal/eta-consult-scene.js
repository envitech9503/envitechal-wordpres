/* Envi Tech AL — Environmental Consultancy flagship
 * Hero: a Three.js wireframe terrain, the site itself. It starts as an
 * unsurveyed field, dim and jittering, and a survey sweep maps it contour by
 * contour as the visitor scrolls: lines settle, turn amber and survey markers
 * rise where the sweep has passed. Below the hero, GSAP + ScrollTrigger drive
 * the pinned regulatory pathway, the cursor-lit deliverable cards, a drawn
 * engagement line and the reveals. Lenis smooths the scroll.
 * Progressive enhancement: html.ec-gsap is added by the inline bootstrap only
 * when motion is allowed; this module removes it again if WebGL is missing so
 * the static hero stands. 13-09-2026 */

import * as THREE from './assets/js/vendor/three-slim.js';
import { gsap, ScrollTrigger, Lenis } from './assets/js/vendor/motion.js';

(function () {
  'use strict';
  var doc = document.documentElement;
  var root = document.getElementById('eta-consult');
  if (!root) return;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  if (reduced) { doc.classList.remove('ec-gsap'); return; }
  doc.classList.add('ec-gsap');

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
  var hero = root.querySelector('.ec-hero');
  var stage = root.querySelector('.ec-stage');
  var canvas = root.querySelector('.ec-gl');
  var gaugeBar = root.querySelector('.ec-gauge-bar i');
  var gaugeVal = root.querySelector('.ec-gauge-v');
  var state = { p: 0, tp: 0, px: 0, py: 0, spx: 0, spy: 0, running: false };

  var renderer, scene, camera, terrain, markers, uniforms, markUniforms;
  var seed = 3;
  function rnd() { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; }

  var NOISE = [
    'float hash(vec2 p){ return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }',
    'float vnoise(vec2 p){ vec2 i = floor(p), f = fract(p); f = f*f*(3.0-2.0*f);',
    '  return mix(mix(hash(i), hash(i+vec2(1.0,0.0)), f.x), mix(hash(i+vec2(0.0,1.0)), hash(i+vec2(1.0,1.0)), f.x), f.y); }',
    'float terrainH(vec2 p){ return vnoise(p*0.09)*6.0 + vnoise(p*0.22)*2.2 + vnoise(p*0.6)*0.5 - 4.2; }'
  ].join('\n');

  function initGL() {
    try {
      renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: false, powerPreference: 'high-performance' });
    } catch (e) { return false; }
    if (!renderer.getContext()) return false;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 1.75));

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0d0f12);
    scene.fog = new THREE.Fog(0x0d0f12, 30, 95);
    camera = new THREE.PerspectiveCamera(isMobile ? 58 : 46, 1, 0.1, 200);

    /* --- terrain: a wireframe grid, heights computed in the shader --- */
    var cols = isMobile ? 70 : 120, rows = isMobile ? 50 : 84;
    var W = 96, D = 68;
    var v = [];
    function P(c, r) { return [(c / (cols - 1) - 0.5) * W, 0, -(r / (rows - 1)) * D + 10]; }
    for (var r = 0; r < rows; r++) for (var c = 0; c < cols - 1; c++) { v.push.apply(v, P(c, r)); v.push.apply(v, P(c + 1, r)); }
    for (var c2 = 0; c2 < cols; c2++) for (var r2 = 0; r2 < rows - 1; r2++) { v.push.apply(v, P(c2, r2)); v.push.apply(v, P(c2, r2 + 1)); }
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.Float32BufferAttribute(v, 3));

    uniforms = {
      uTime: { value: 0 },
      uMap: { value: 0 },
      uPx: { value: 0 },
      uPy: { value: 0 },
      uW: { value: W },
      uColDim: { value: new THREE.Color(0x66707f) },
      uColMap: { value: new THREE.Color(0xf2b544) }
    };
    var mat = new THREE.ShaderMaterial({
      uniforms: uniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
      vertexShader: [
        'uniform float uTime, uMap, uPx, uPy, uW;',
        'varying float vMapped, vScan, vDepth, vH;',
        NOISE,
        'void main(){',
        '  vec3 p = position;',
        '  float h = terrainH(p.xz);',
        '  float scan = mix(-uW*0.55, uW*0.55, uMap);',
        '  float mapped = 1.0 - smoothstep(scan - 4.0, scan + 4.0, p.x);',
        '  float jit = (vnoise(p.xz*1.7 + uTime*0.9) - 0.5) * 1.6;',
        '  p.y = h * (0.55 + 0.45*mapped) + jit * (1.0 - mapped);',
        '  p.x += uPx * 1.0; p.y += uPy * 0.4;',
        '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
        '  gl_Position = projectionMatrix * mv;',
        '  vMapped = mapped; vScan = 1.0 - smoothstep(0.0, 3.5, abs(p.x - scan)); vDepth = -mv.z; vH = h;',
        '}'
      ].join('\n'),
      fragmentShader: [
        'uniform vec3 uColDim, uColMap;',
        'varying float vMapped, vScan, vDepth, vH;',
        'void main(){',
        '  vec3 col = mix(uColDim, uColMap, vMapped);',
        '  col += vec3(1.0, 0.9, 0.6) * vScan * 0.9;',
        '  float fade = clamp(1.0 - (vDepth - 20.0) / 70.0, 0.08, 1.0);',
        '  float a = (0.55 + 0.4*vMapped + 0.5*vScan) * fade;',
        '  gl_FragColor = vec4(col, a);',
        '}'
      ].join('\n')
    });
    terrain = new THREE.LineSegments(geo, mat);
    scene.add(terrain);

    /* --- survey markers: points that rise where the sweep has passed --- */
    var M = isMobile ? 40 : 90;
    var mp = new Float32Array(M * 3), ms = new Float32Array(M);
    for (var j = 0; j < M; j++) {
      mp[j * 3] = (rnd() - 0.5) * W * 0.95; mp[j * 3 + 1] = 0; mp[j * 3 + 2] = -(rnd()) * D + 8; ms[j] = rnd();
    }
    var mg = new THREE.BufferGeometry();
    mg.setAttribute('position', new THREE.BufferAttribute(mp, 3));
    mg.setAttribute('aSeed', new THREE.BufferAttribute(ms, 1));
    markUniforms = { uTime: { value: 0 }, uMap: { value: 0 }, uW: { value: W }, uCol: { value: new THREE.Color(0xffd98a) } };
    var mm = new THREE.ShaderMaterial({
      uniforms: markUniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
      vertexShader: [
        'attribute float aSeed;',
        'uniform float uTime, uMap, uW;',
        'varying float vA;',
        NOISE,
        'void main(){',
        '  vec3 p = position;',
        '  float scan = mix(-uW*0.55, uW*0.55, uMap);',
        '  float on = 1.0 - smoothstep(scan - 6.0, scan, p.x);',
        '  p.y = terrainH(p.xz) + 1.2 + on * 1.4 + sin(uTime*1.5 + aSeed*20.0) * 0.25;',
        '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
        '  gl_Position = projectionMatrix * mv;',
        '  gl_PointSize = (7.0 + aSeed*6.0) * on * (18.0 / -mv.z);',
        '  vA = on;',
        '}'
      ].join('\n'),
      fragmentShader: [
        'uniform vec3 uCol;',
        'varying float vA;',
        'void main(){',
        '  vec2 c = gl_PointCoord - 0.5; float d = length(c);',
        '  if (d > 0.5) discard;',
        '  float ring = smoothstep(0.5, 0.36, d) - smoothstep(0.3, 0.16, d) * 0.7;',
        '  gl_FragColor = vec4(uCol, ring * vA);',
        '}'
      ].join('\n')
    });
    markers = new THREE.Points(mg, mm);
    scene.add(markers);

    resize();
    return true;
  }

  function resize() {
    if (!renderer) return;
    var w = stage.clientWidth, h = stage.clientHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h; camera.updateProjectionMatrix();
  }

  /* Colour arc: cold, unsurveyed charcoal to a warm, mapped amber dusk. */
  var ARC = [
    { p: 0.0, bg: 0x0d0f12, dim: 0x66707f, map: 0xf2b544 },
    { p: 0.5, bg: 0x13161b, dim: 0x6f7987, map: 0xf6c15b },
    { p: 1.0, bg: 0x1a1a1c, dim: 0x7a8290, map: 0xffd98a }
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

    uniforms.uTime.value = t; uniforms.uMap.value = p;
    uniforms.uPx.value = state.spx * 0.6; uniforms.uPy.value = state.spy * 0.5;
    markUniforms.uTime.value = t; markUniforms.uMap.value = p;
    arc(p, 'bg', scene.background); scene.fog.color.copy(scene.background);
    arc(p, 'dim', uniforms.uColDim.value); arc(p, 'map', uniforms.uColMap.value);

    // Camera arcs from a high, distant survey view to a lower, closer approach.
    var ang = -0.25 + p * 0.5 + state.spx * 0.08;
    var rad = 34 - p * 8;
    camera.position.set(Math.sin(ang) * rad, 15 - p * 6 + state.spy * 0.8, Math.cos(ang) * rad + 4);
    camera.lookAt(0, -2.5, -18);

    renderer.render(scene, camera);
    requestAnimationFrame(frame);
  }

  function setGauge(p) {
    if (gaugeBar) gaugeBar.style.transform = 'scaleX(' + (0.06 + p * 0.94) + ')';
    if (gaugeVal) gaugeVal.textContent = Math.round(p * 100) + '%';
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
    gsap.to(root.querySelector('.ec-hero-grid'), {
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
    doc.classList.remove('ec-gsap');
  }

  gsap.from(root.querySelectorAll('.ec-title-line'), { yPercent: 110, duration: 1.1, ease: 'power4.out', stagger: 0.09, delay: 0.1 });
  gsap.from(root.querySelectorAll('.ec-eyebrow, .ec-lead, .ec-actions, .ec-hero-panel'), { opacity: 0, y: 18, duration: 0.9, ease: 'power3.out', stagger: 0.08, delay: 0.35 });

  /* ================= REVEALS ================= */
  var revealSel = '.ec-ledger-item, .ec-head, .ec-deliver-card, .ec-regs-copy, .ec-regs-list li, .ec-serve-card, .ec-journey-copy, .ec-why-card, .ec-faq-item, .ec-related-card, .ec-final-grid > *';
  root.querySelectorAll(revealSel).forEach(function (el) { el.setAttribute('data-ec-reveal', ''); });
  var reveal = function (els) { gsap.to(els, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out', stagger: 0.07, overwrite: true }); };
  ScrollTrigger.batch(root.querySelectorAll('[data-ec-reveal]'), { start: 'top 88%', onEnter: reveal, onLeave: reveal });
  var sweep = function () {
    var line = window.innerHeight * 0.88, due = [];
    root.querySelectorAll('[data-ec-reveal]').forEach(function (el) {
      if (el.getBoundingClientRect().top < line && getComputedStyle(el).opacity !== '1') due.push(el);
    });
    if (due.length) reveal(due);
  };
  ScrollTrigger.addEventListener('refresh', sweep);
  lenis.on('scroll', function () { if (sweep._t) return; sweep._t = setTimeout(function () { sweep._t = 0; sweep(); }, 120); });
  window.addEventListener('load', sweep);

  /* ================= PATHWAY: pinned horizontal ================= */
  var track = root.querySelector('[data-ec-track]');
  var types = root.querySelector('.ec-types');
  var cards = root.querySelectorAll('[data-ec-card]');
  var bar = root.querySelector('.ec-types-bar i');
  var count = root.querySelector('.ec-types-count');
  if (track && !isMobile) {
    var distance = function () { return Math.max(0, track.scrollWidth - types.clientWidth + 24); };
    gsap.to(track, {
      x: function () { return -distance(); },
      ease: 'none',
      scrollTrigger: {
        trigger: types, start: function () { return 'top ' + (parseInt(getComputedStyle(doc).getPropertyValue('--eta-hh')) || 0); }, end: function () { return '+=' + (distance() + 200); },
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

  /* ================= DELIVERABLES: cursor tilt + glow ================= */
  if (!isMobile) {
    root.querySelectorAll('[data-ec-tilt]').forEach(function (card) {
      var glow = card.querySelector('.ec-deliver-glow');
      var setRX = gsap.quickTo(card, 'rotationX', { duration: 0.5, ease: 'power3.out' });
      var setRY = gsap.quickTo(card, 'rotationY', { duration: 0.5, ease: 'power3.out' });
      card.addEventListener('pointermove', function (e) {
        var r = card.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
        setRY((x - 0.5) * 8); setRX((0.5 - y) * 8);
        if (glow) { glow.style.setProperty('--gx', (x * 100) + '%'); glow.style.setProperty('--gy', (y * 100) + '%'); }
      });
      card.addEventListener('pointerleave', function () { setRY(0); setRX(0); });
    });
  }

  /* ================= JOURNEY: line draws, dots light ================= */
  var path = root.querySelector('.ec-journey-line path');
  var steps = root.querySelectorAll('[data-ec-step]');
  if (path) {
    gsap.to(path, {
      strokeDashoffset: 0, ease: 'none',
      scrollTrigger: {
        trigger: root.querySelector('.ec-journey-list'), start: 'top 70%', end: 'bottom 60%', scrub: true,
        onUpdate: function (st) {
          var n = Math.round(st.progress * steps.length + 0.25);
          steps.forEach(function (li, k) { li.classList.toggle('is-on', k < n); });
        }
      }
    });
  }

  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
