/* Envi Tech AL — Thermal Imaging Inspection flagship
 * Hero: a Three.js thermogram. A switchboard face rendered as a dense field
 * of pixels begins in flat visible light and, as the visitor scrolls, an
 * infrared sweep crosses it: the ironbow palette resolves, hotspots on the
 * connections bloom and the gauge counts the anomalies found. Below the
 * hero, GSAP + ScrollTrigger drive the pinned inspection sequence,
 * cursor-lit target cards, a drawn workflow line and the reveals. Lenis
 * smooths the scroll.
 * Progressive enhancement: html.ti-gsap is added by the inline bootstrap only
 * when motion is allowed; this module removes it again if WebGL is missing so
 * the static hero stands. 13-09-2026 */

import * as THREE from './assets/js/vendor/three-slim.js';
import { gsap, ScrollTrigger, Lenis } from './assets/js/vendor/motion.js';

(function () {
  'use strict';
  var doc = document.documentElement;
  var root = document.getElementById('eta-thermal');
  if (!root) return;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  if (reduced) { doc.classList.remove('ti-gsap'); return; }
  doc.classList.add('ti-gsap');

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
  var hero = root.querySelector('.ti-hero');
  var stage = root.querySelector('.ti-stage');
  var canvas = root.querySelector('.ti-gl');
  var gaugeBar = root.querySelector('.ti-gauge-bar i');
  var gaugeVal = root.querySelector('.ti-gauge-v');
  var state = { p: 0, tp: 0, px: 0, py: 0, spx: 0, spy: 0, running: false };

  var renderer, scene, camera, board, pixels, frame_, uniforms;
  var W = 40, H = 26;
  var HOT = [[-11, 6], [4, 9], [12, -3], [-3, -8]];   // hotspot centres on the board

  function initGL() {
    try {
      renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: false, powerPreference: 'high-performance' });
    } catch (e) { return false; }
    if (!renderer.getContext()) return false;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 1.75));

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0b0716);
    scene.fog = new THREE.Fog(0x0b0716, 40, 100);
    camera = new THREE.PerspectiveCamera(isMobile ? 55 : 42, 1, 0.1, 200);
    board = new THREE.Group();

    /* --- the thermogram: a dense pixel field, temperature computed in the shader --- */
    var cols = isMobile ? 120 : 200, rows = isMobile ? 78 : 130, n = cols * rows;
    var pos = new Float32Array(n * 3), seeds = new Float32Array(n);
    var k = 0; for (var r = 0; r < rows; r++) for (var c = 0; c < cols; c++) { pos[k * 3] = (c / (cols - 1) - 0.5) * W; pos[k * 3 + 1] = (r / (rows - 1) - 0.5) * H; pos[k * 3 + 2] = 0; seeds[k] = ((c * 7 + r * 13) % 17) / 17; k++; }
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    geo.setAttribute('aSeed', new THREE.BufferAttribute(seeds, 1));
    uniforms = { uTime: { value: 0 }, uScan: { value: 0 }, uSize: { value: isMobile ? 5.0 : 5.6 }, uW: { value: W },
      uHot: { value: HOT.map(function (h) { return new THREE.Vector3(h[0], h[1], 0); }) } };
    var mat = new THREE.ShaderMaterial({
      uniforms: uniforms, transparent: true, depthWrite: false,
      vertexShader: [
        'attribute float aSeed;',
        'uniform float uTime, uScan, uSize, uW; uniform vec3 uHot[4];',
        'varying float vT, vIR, vScan;',
        'float hash(vec2 p){ return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }',
        'float vnoise(vec2 p){ vec2 i = floor(p), f = fract(p); f = f*f*(3.0-2.0*f);',
        '  return mix(mix(hash(i), hash(i+vec2(1.0,0.0)), f.x), mix(hash(i+vec2(0.0,1.0)), hash(i+vec2(1.0,1.0)), f.x), f.y); }',
        'void main(){',
        '  vec3 p = position;',
        // temperature field: ambient gradient + busbar warmth + hotspots
        '  float t = 0.18 + vnoise(p.xy * 0.25) * 0.14 + smoothstep(9.0, 0.0, abs(p.y - 2.0)) * 0.08;',
        '  for (int i = 0; i < 4; i++) { float d = distance(p.xy, uHot[i].xy); float amp = (i == 2) ? 0.95 : (i == 0 ? 0.8 : 0.55); t += amp * exp(-d*d / (2.2 + float(i)*0.6)) * (0.9 + 0.1*sin(uTime*2.0 + float(i))); }',
        '  float scan = mix(-uW*0.55, uW*0.55, uScan);',
        '  float ir = 1.0 - smoothstep(scan - 1.5, scan + 1.5, p.x);',
        '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
        '  gl_Position = projectionMatrix * mv;',
        '  gl_PointSize = uSize * (26.0 / -mv.z);',
        '  vT = t; vIR = ir; vScan = 1.0 - smoothstep(0.0, 2.0, abs(p.x - scan));',
        '}'
      ].join('\n'),
      fragmentShader: [
        'varying float vT, vIR, vScan;',
        // ironbow: black -> purple -> red -> orange -> yellow -> white
        'vec3 ironbow(float t){',
        '  t = clamp(t, 0.0, 1.0);',
        '  vec3 c0 = vec3(0.02, 0.0, 0.08), c1 = vec3(0.35, 0.02, 0.55), c2 = vec3(0.85, 0.12, 0.25), c3 = vec3(1.0, 0.48, 0.05), c4 = vec3(1.0, 0.85, 0.2), c5 = vec3(1.0, 1.0, 0.92);',
        '  if (t < 0.2) return mix(c0, c1, t/0.2); if (t < 0.45) return mix(c1, c2, (t-0.2)/0.25); if (t < 0.65) return mix(c2, c3, (t-0.45)/0.2); if (t < 0.85) return mix(c3, c4, (t-0.65)/0.2); return mix(c4, c5, (t-0.85)/0.15); }',
        'void main(){',
        '  vec2 c = gl_PointCoord - 0.5; if (abs(c.x) > 0.42 || abs(c.y) > 0.42) discard;',
        '  vec3 vis = vec3(0.22, 0.2, 0.28) + vT * 0.08;',
        '  vec3 col = mix(vis, ironbow(vT), vIR) + vec3(1.0, 0.9, 0.7) * vScan * 0.5;',
        '  gl_FragColor = vec4(col, 0.95);',
        '}'
      ].join('\n')
    });
    pixels = new THREE.Points(geo, mat);
    board.add(pixels);

    /* --- switchboard frame and the connection points the hotspots sit on --- */
    var fm = new THREE.LineBasicMaterial({ color: 0xffb020, transparent: true, opacity: 0.35 });
    board.add(new THREE.LineSegments(new THREE.EdgesGeometry(new THREE.BoxGeometry(W + 2, H + 2, 0.6)), fm));
    var busV = [];
    [-6, 2, 10].forEach(function (y) { busV.push(-W / 2 + 2, y, 0.2, W / 2 - 2, y, 0.2); });
    for (var x = -W / 2 + 4; x < W / 2; x += 8) busV.push(x, -H / 2 + 2, 0.2, x, H / 2 - 2, 0.2);
    var bg = new THREE.BufferGeometry();
    bg.setAttribute('position', new THREE.Float32BufferAttribute(busV, 3));
    board.add(new THREE.LineSegments(bg, new THREE.LineBasicMaterial({ color: 0xd9cfe6, transparent: true, opacity: 0.12 })));
    scene.add(board);

    resize();
    return true;
  }

  function resize() {
    if (!renderer) return;
    var w = stage.clientWidth, h = stage.clientHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h; camera.updateProjectionMatrix();
  }

  var t0 = performance.now();
  var bgA = new THREE.Color(0x0b0716), bgB = new THREE.Color(0x120b22), bgc = new THREE.Color();
  function frame() {
    if (!state.running) return;
    var t = (performance.now() - t0) / 1000;
    state.p += (state.tp - state.p) * 0.08;
    state.spx += (state.px - state.spx) * 0.06;
    state.spy += (state.py - state.spy) * 0.06;
    var p = state.p, e = p * p * (3 - 2 * p);

    uniforms.uTime.value = t; uniforms.uScan.value = p;
    bgc.copy(bgA).lerp(bgB, e); scene.background.copy(bgc); scene.fog.color.copy(bgc);

    var off = isMobile ? 0 : 7;
    board.position.x = off;
    board.rotation.y = -0.5 + e * 0.3 + state.spx * 0.1;
    board.rotation.x = 0.12 + state.spy * 0.06;
    camera.position.set(off + 5 + state.spx * 1.0, 2 + state.spy * 0.6, 56 - e * 6);
    camera.lookAt(off, 0, 0);

    renderer.render(scene, camera);
    requestAnimationFrame(frame);
  }

  function setGauge(p) {
    if (gaugeBar) gaugeBar.style.transform = 'scaleX(' + (0.06 + p * 0.94) + ')';
    // hotspots, from left to right across the board, are found as the sweep passes them
    var found = 0; HOT.forEach(function (h) { if ((h[0] + W * 0.55) / (W * 1.1) <= p) found++; });
    if (gaugeVal) gaugeVal.textContent = found + ' of 4';
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
    gsap.to(root.querySelector('.ti-hero-grid'), {
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
    doc.classList.remove('ti-gsap');
  }

  gsap.from(root.querySelectorAll('.ti-title-line'), { yPercent: 110, duration: 1.1, ease: 'power4.out', stagger: 0.09, delay: 0.1 });
  gsap.from(root.querySelectorAll('.ti-eyebrow, .ti-lead, .ti-actions, .ti-hero-panel'), { opacity: 0, y: 18, duration: 0.9, ease: 'power3.out', stagger: 0.08, delay: 0.35 });

  /* ================= REVEALS ================= */
  var revealSel = '.ti-ledger-item, .ti-head, .ti-deliver-card, .ti-scheme-card, .ti-regs-copy, .ti-regs-list li, .ti-serve-card, .ti-journey-copy, .ti-why-card, .ti-faq-item, .ti-related-card, .ti-final-grid > *';
  root.querySelectorAll(revealSel).forEach(function (el) { el.setAttribute('data-ti-reveal', ''); });
  var reveal = function (els) { gsap.to(els, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out', stagger: 0.07, overwrite: true }); };
  ScrollTrigger.batch(root.querySelectorAll('[data-ti-reveal]'), { start: 'top 88%', onEnter: reveal, onLeave: reveal });
  var sweep = function () {
    var line = window.innerHeight * 0.88, due = [];
    root.querySelectorAll('[data-ti-reveal]').forEach(function (el) {
      if (el.getBoundingClientRect().top < line && getComputedStyle(el).opacity !== '1') due.push(el);
    });
    if (due.length) reveal(due);
  };
  ScrollTrigger.addEventListener('refresh', sweep);
  lenis.on('scroll', function () { if (sweep._t) return; sweep._t = setTimeout(function () { sweep._t = 0; sweep(); }, 120); });
  window.addEventListener('load', sweep);

  /* ================= PATHWAY: pinned horizontal ================= */
  var track = root.querySelector('[data-ti-track]');
  var types = root.querySelector('.ti-types');
  var cards = root.querySelectorAll('[data-ti-card]');
  var bar = root.querySelector('.ti-types-bar i');
  var count = root.querySelector('.ti-types-count');
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
    root.querySelectorAll('[data-ti-tilt]').forEach(function (card) {
      var glow = card.querySelector('.ti-deliver-glow');
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
  var path = root.querySelector('.ti-journey-line path');
  var steps = root.querySelectorAll('[data-ti-step]');
  if (path) {
    gsap.to(path, {
      strokeDashoffset: 0, ease: 'none',
      scrollTrigger: {
        trigger: root.querySelector('.ti-journey-list'), start: 'top 70%', end: 'bottom 60%', scrub: true,
        onUpdate: function (st) {
          var n = Math.round(st.progress * steps.length + 0.25);
          steps.forEach(function (li, k) { li.classList.toggle('is-on', k < n); });
        }
      }
    });
  }

  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
