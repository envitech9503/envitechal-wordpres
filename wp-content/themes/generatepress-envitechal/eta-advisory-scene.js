/* Envi Tech AL — Environmental Advisory flagship
 * Hero: a Three.js performance chart drawn in space. Four monitoring signals
 * (effluent, emissions, noise, waste) start as erratic traces that stray over
 * their limit lines and, as the visitor scrolls, settle into controlled
 * curves inside the limits, while the gauge reads the share of the period
 * within limits. Below the hero, GSAP + ScrollTrigger drive the pinned
 * maturity sequence, cursor-lit workstream cards, a drawn engagement line and
 * the reveals. Lenis smooths the scroll.
 * Progressive enhancement: html.ea-gsap is added by the inline bootstrap only
 * when motion is allowed; this module removes it again if WebGL is missing so
 * the static hero stands. 13-09-2026 */

import * as THREE from './assets/js/vendor/three-slim.js';
import { gsap, ScrollTrigger, Lenis } from './assets/js/vendor/motion.js';

(function () {
  'use strict';
  var doc = document.documentElement;
  var root = document.getElementById('eta-advisory');
  if (!root) return;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  if (reduced) { doc.classList.remove('ea-gsap'); return; }
  doc.classList.add('ea-gsap');

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
  var hero = root.querySelector('.ea-hero');
  var stage = root.querySelector('.ea-stage');
  var canvas = root.querySelector('.ea-gl');
  var gaugeBar = root.querySelector('.ea-gauge-bar i');
  var gaugeVal = root.querySelector('.ea-gauge-v');
  var state = { p: 0, tp: 0, px: 0, py: 0, spx: 0, spy: 0, running: false };

  var renderer, scene, camera, chart, traces, marks, limits, uniforms, markUniforms;
  var seed = 21;
  function rnd() { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; }

  var LINES = 4, W = 40, LANE = 6.2;      // four lanes stacked in y
  function laneY(i) { return (1.5 - i) * LANE; }

  var NOISE = [
    'float hash(float n){ return fract(sin(n) * 43758.5453); }',
    'float n1(float x){ float i = floor(x), f = fract(x); f = f*f*(3.0-2.0*f); return mix(hash(i), hash(i+1.0), f); }',
    'float ease(float t){ return t*t*(3.0-2.0*t); }',
    // the unmanaged trace: a wandering signal with excursions over the limit
    'float wild(float t, float line, float time){ return (n1(t*9.0 + line*31.0 + time*0.15) - 0.5) * 5.2 + (n1(t*2.5 + line*7.0) - 0.5) * 3.0; }',
    // the managed trace: a smooth settle below the limit
    'float calm(float t, float line){ return -1.4 + sin(t*6.0 + line) * 0.35 - t * 0.6; }',
    'float traceY(float t, float line, float k, float time){ float kk = ease(clamp(k*1.5 - t*0.5 - line*0.06, 0.0, 1.0)); return mix(wild(t, line, time), calm(t, line), kk); }'
  ].join('\n');

  function initGL() {
    try {
      renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: false, powerPreference: 'high-performance' });
    } catch (e) { return false; }
    if (!renderer.getContext()) return false;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 1.75));

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x100c12);
    scene.fog = new THREE.Fog(0x100c12, 40, 110);
    camera = new THREE.PerspectiveCamera(isMobile ? 55 : 42, 1, 0.1, 200);
    chart = new THREE.Group();

    /* --- limit lines and lane baselines --- */
    var lv = [];
    for (var i = 0; i < LINES; i++) {
      var y = laneY(i);
      lv.push(-W / 2, y + 1.6, 0, W / 2, y + 1.6, 0);   // limit
      lv.push(-W / 2, y - 3.2, 0, W / 2, y - 3.2, 0);   // baseline
    }
    var lg = new THREE.BufferGeometry();
    lg.setAttribute('position', new THREE.Float32BufferAttribute(lv, 3));
    limits = new THREE.LineSegments(lg, new THREE.LineBasicMaterial({ color: 0xff7b72, transparent: true, opacity: 0.35 }));
    chart.add(limits);
    // dashed limit ticks
    var tv = [];
    for (var i2 = 0; i2 < LINES; i2++) for (var x = -W / 2; x <= W / 2; x += 2) tv.push(x, laneY(i2) + 1.6, 0, x, laneY(i2) + 2.0, 0);
    var tg = new THREE.BufferGeometry();
    tg.setAttribute('position', new THREE.Float32BufferAttribute(tv, 3));
    chart.add(new THREE.LineSegments(tg, new THREE.LineBasicMaterial({ color: 0xffb8b0, transparent: true, opacity: 0.25 })));

    /* --- the traces: line strips, y computed in the shader --- */
    var SEG = isMobile ? 140 : 260, pv = [], pt = [], pl = [];
    for (var L = 0; L < LINES; L++) {
      for (var s2 = 0; s2 < SEG; s2++) {
        var t0 = s2 / SEG, t1 = (s2 + 1) / SEG;
        pv.push(-W / 2 + t0 * W, laneY(L), 0, -W / 2 + t1 * W, laneY(L), 0);
        pt.push(t0, t1); pl.push(L, L);
      }
    }
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.Float32BufferAttribute(pv, 3));
    geo.setAttribute('aT', new THREE.Float32BufferAttribute(pt, 1));
    geo.setAttribute('aLine', new THREE.Float32BufferAttribute(pl, 1));
    uniforms = { uTime: { value: 0 }, uK: { value: 0 } };
    var mat = new THREE.ShaderMaterial({
      uniforms: uniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
      vertexShader: [
        'attribute float aT, aLine;',
        'uniform float uTime, uK;',
        'varying float vOver, vK;',
        NOISE,
        'void main(){',
        '  vec3 p = position;',
        '  float y = traceY(aT, aLine, uK, uTime);',
        '  p.y += y;',
        '  gl_Position = projectionMatrix * modelViewMatrix * vec4(p, 1.0);',
        '  vOver = smoothstep(1.2, 2.2, y);',
        '  vK = ease(clamp(uK*1.5 - aT*0.5 - aLine*0.06, 0.0, 1.0));',
        '}'
      ].join('\n'),
      fragmentShader: [
        'varying float vOver, vK;',
        'void main(){',
        '  vec3 ok = vec3(1.0, 0.72, 0.69);',
        '  vec3 over = vec3(1.0, 0.35, 0.32);',
        '  vec3 col = mix(ok, over, vOver);',
        '  gl_FragColor = vec4(col, 0.55 + 0.4*vK);',
        '}'
      ].join('\n')
    });
    traces = new THREE.LineSegments(geo, mat);
    chart.add(traces);

    /* --- sample markers: monthly points on each trace --- */
    var M = 14, mv = [], mt = [], ml = [], ms = [];
    for (var L2 = 0; L2 < LINES; L2++) for (var m = 0; m < M; m++) { var tt = (m + 0.5) / M; mv.push(-W / 2 + tt * W, laneY(L2), 0.05); mt.push(tt); ml.push(L2); ms.push(rnd()); }
    var mg = new THREE.BufferGeometry();
    mg.setAttribute('position', new THREE.Float32BufferAttribute(mv, 3));
    mg.setAttribute('aT', new THREE.Float32BufferAttribute(mt, 1));
    mg.setAttribute('aLine', new THREE.Float32BufferAttribute(ml, 1));
    mg.setAttribute('aSeed', new THREE.Float32BufferAttribute(ms, 1));
    markUniforms = { uTime: { value: 0 }, uK: { value: 0 }, uSize: { value: isMobile ? 9 : 11 } };
    var mm = new THREE.ShaderMaterial({
      uniforms: markUniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
      vertexShader: [
        'attribute float aT, aLine, aSeed;',
        'uniform float uTime, uK, uSize;',
        'varying float vOver;',
        NOISE,
        'void main(){',
        '  vec3 p = position; float y = traceY(aT, aLine, uK, uTime); p.y += y;',
        '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
        '  gl_Position = projectionMatrix * mv;',
        '  gl_PointSize = uSize * (0.8 + 0.4*aSeed) * (24.0 / -mv.z);',
        '  vOver = smoothstep(1.2, 2.2, y);',
        '}'
      ].join('\n'),
      fragmentShader: [
        'varying float vOver;',
        'void main(){',
        '  vec2 c = gl_PointCoord - 0.5; float d = length(c);',
        '  if (d > 0.5) discard;',
        '  float ring = smoothstep(0.5, 0.38, d) - smoothstep(0.3, 0.16, d) * 0.6;',
        '  vec3 col = mix(vec3(1.0, 0.85, 0.82), vec3(1.0, 0.3, 0.28), vOver);',
        '  gl_FragColor = vec4(col, ring * 0.95);',
        '}'
      ].join('\n')
    });
    marks = new THREE.Points(mg, mm);
    chart.add(marks);
    scene.add(chart);

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
  var bgA = new THREE.Color(0x100c12), bgB = new THREE.Color(0x1a1420), bg = new THREE.Color();
  function frame() {
    if (!state.running) return;
    var t = (performance.now() - t0) / 1000;
    state.p += (state.tp - state.p) * 0.08;
    state.spx += (state.px - state.spx) * 0.06;
    state.spy += (state.py - state.spy) * 0.06;
    var p = state.p, e = p * p * (3 - 2 * p);

    uniforms.uTime.value = t; uniforms.uK.value = p;
    markUniforms.uTime.value = t; markUniforms.uK.value = p;
    bg.copy(bgA).lerp(bgB, e); scene.background.copy(bg); scene.fog.color.copy(bg);
    limits.material.opacity = 0.3 + e * 0.35;

    var off = isMobile ? 0 : 6;
    chart.position.x = off;
    chart.rotation.y = -0.4 + e * 0.32 + state.spx * 0.1;
    chart.rotation.x = 0.1 + state.spy * 0.06;
    camera.position.set(off + 3 + state.spx * 1.0, 2 + state.spy * 0.6, 60 - e * 6);
    camera.lookAt(off, -0.5, 0);

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
    gsap.to(root.querySelector('.ea-hero-grid'), {
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
    doc.classList.remove('ea-gsap');
  }

  gsap.from(root.querySelectorAll('.ea-title-line'), { yPercent: 110, duration: 1.1, ease: 'power4.out', stagger: 0.09, delay: 0.1 });
  gsap.from(root.querySelectorAll('.ea-eyebrow, .ea-lead, .ea-actions, .ea-hero-panel'), { opacity: 0, y: 18, duration: 0.9, ease: 'power3.out', stagger: 0.08, delay: 0.35 });

  /* ================= REVEALS ================= */
  var revealSel = '.ea-ledger-item, .ea-head, .ea-deliver-card, .ea-scheme-card, .ea-regs-copy, .ea-regs-list li, .ea-serve-card, .ea-journey-copy, .ea-why-card, .ea-faq-item, .ea-related-card, .ea-final-grid > *';
  root.querySelectorAll(revealSel).forEach(function (el) { el.setAttribute('data-ea-reveal', ''); });
  var reveal = function (els) { gsap.to(els, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out', stagger: 0.07, overwrite: true }); };
  ScrollTrigger.batch(root.querySelectorAll('[data-ea-reveal]'), { start: 'top 88%', onEnter: reveal, onLeave: reveal });
  var sweep = function () {
    var line = window.innerHeight * 0.88, due = [];
    root.querySelectorAll('[data-ea-reveal]').forEach(function (el) {
      if (el.getBoundingClientRect().top < line && getComputedStyle(el).opacity !== '1') due.push(el);
    });
    if (due.length) reveal(due);
  };
  ScrollTrigger.addEventListener('refresh', sweep);
  lenis.on('scroll', function () { if (sweep._t) return; sweep._t = setTimeout(function () { sweep._t = 0; sweep(); }, 120); });
  window.addEventListener('load', sweep);

  /* ================= PATHWAY: pinned horizontal ================= */
  var track = root.querySelector('[data-ea-track]');
  var types = root.querySelector('.ea-types');
  var cards = root.querySelectorAll('[data-ea-card]');
  var bar = root.querySelector('.ea-types-bar i');
  var count = root.querySelector('.ea-types-count');
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
    root.querySelectorAll('[data-ea-tilt]').forEach(function (card) {
      var glow = card.querySelector('.ea-deliver-glow');
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
  var path = root.querySelector('.ea-journey-line path');
  var steps = root.querySelectorAll('[data-ea-step]');
  if (path) {
    gsap.to(path, {
      strokeDashoffset: 0, ease: 'none',
      scrollTrigger: {
        trigger: root.querySelector('.ea-journey-list'), start: 'top 70%', end: 'bottom 60%', scrub: true,
        onUpdate: function (st) {
          var n = Math.round(st.progress * steps.length + 0.25);
          steps.forEach(function (li, k) { li.classList.toggle('is-on', k < n); });
        }
      }
    });
  }

  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
