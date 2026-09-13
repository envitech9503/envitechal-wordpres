/* Envi Tech AL — Analytical Lab Services flagship
 * Hero: a Three.js particle field that resolves from a scattered sample cloud
 * into an ordered result lattice as the visitor scrolls, lit by a slow arc from
 * charcoal to teal. Below the hero, GSAP + ScrollTrigger drive a pinned
 * horizontal journey, card reveals, a drawn method line and a cursor-lit
 * matrix. Lenis smooths the scroll ScrollTrigger reads.
 * Progressive enhancement: html.lab-gsap is added by the inline bootstrap only
 * when motion is allowed; this module removes it again if WebGL is missing so
 * the static hero stands. 12-09-2026 */

import * as THREE from './assets/js/vendor/three-slim.js';
import { gsap, ScrollTrigger, Lenis } from './assets/js/vendor/motion.js';

(function () {
  'use strict';
  var doc = document.documentElement;
  var root = document.getElementById('eta-lab');
  if (!root) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { doc.classList.remove('lab-gsap'); return; }
  doc.classList.add('lab-gsap');

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

  /* In-page anchors go through Lenis so they stay smooth. */
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
  var hero = root.querySelector('.lab-hero');
  var stage = root.querySelector('.lab-stage');
  var canvas = root.querySelector('.lab-gl');
  var phases = root.querySelectorAll('.lab-phases li');
  var state = { p: 0, tp: 0, px: 0, py: 0, spx: 0, spy: 0, running: false };

  var renderer, scene, camera, points, rings, uniforms, N;
  var seed = 11;
  function rnd() { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; }

  function initGL() {
    try {
      renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: false, alpha: false, powerPreference: 'high-performance' });
    } catch (e) { return false; }
    if (!renderer.getContext()) return false;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 1.75));

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x06110e);
    scene.fog = new THREE.Fog(0x06110e, 18, 70);
    camera = new THREE.PerspectiveCamera(isMobile ? 55 : 44, 1, 0.1, 200);

    /* --- particles: two target layouts, morphed in the vertex shader --- */
    N = isMobile ? 1400 : 2600;
    var pA = new Float32Array(N * 3); // cloud
    var pB = new Float32Array(N * 3); // lattice
    var seeds = new Float32Array(N);
    var cols = Math.ceil(Math.sqrt(N * 1.6)), rows = Math.ceil(N / cols);
    var gapX = 0.9, gapY = 0.62;
    for (var i = 0; i < N; i++) {
      // cloud: a loose torus-like swirl so it reads as suspended sample matter
      var a = rnd() * Math.PI * 2, r = 6 + rnd() * 9, h = (rnd() - 0.5) * 9;
      pA[i * 3] = Math.cos(a) * r + (rnd() - 0.5) * 3;
      pA[i * 3 + 1] = h;
      pA[i * 3 + 2] = Math.sin(a) * r * 0.55 + (rnd() - 0.5) * 3 - 4;
      // lattice: an ordered plane of "results", tilted toward the camera
      var cx = i % cols, cy = Math.floor(i / cols);
      pB[i * 3] = (cx - cols / 2) * gapX;
      pB[i * 3 + 1] = (cy - rows / 2) * gapY - 1.5;
      pB[i * 3 + 2] = -6 - cy * 0.12;
      seeds[i] = rnd();
    }
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(pA, 3));
    geo.setAttribute('aTarget', new THREE.BufferAttribute(pB, 3));
    geo.setAttribute('aSeed', new THREE.BufferAttribute(seeds, 1));

    uniforms = {
      uTime: { value: 0 },
      uMorph: { value: 0 },
      uPx: { value: 0 },
      uPy: { value: 0 },
      uSize: { value: isMobile ? 9.0 : 11.0 },
      uColA: { value: new THREE.Color(0x2f8c74) },
      uColB: { value: new THREE.Color(0xa9f0dd) }
    };
    var mat = new THREE.ShaderMaterial({
      uniforms: uniforms,
      transparent: true,
      depthWrite: false,
      blending: THREE.AdditiveBlending,
      vertexShader: [
        'attribute vec3 aTarget;',
        'attribute float aSeed;',
        'uniform float uTime, uMorph, uPx, uPy, uSize;',
        'varying float vSeed, vMorph, vDepth;',
        'float ease(float t){ return t*t*(3.0-2.0*t); }',
        'void main(){',
        '  float m = ease(clamp(uMorph * 1.15 - aSeed * 0.15, 0.0, 1.0));',
        '  vec3 drift = vec3(sin(uTime*0.6+aSeed*12.0), cos(uTime*0.5+aSeed*9.0), sin(uTime*0.4+aSeed*7.0)) * (0.35*(1.0-m) + 0.04*m);',
        '  vec3 p = mix(position, aTarget, m) + drift;',
        '  p.x += uPx * (1.4 - m*0.9); p.y += uPy * (1.0 - m*0.7);',
        '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
        '  gl_Position = projectionMatrix * mv;',
        '  float twinkle = 0.75 + 0.25 * sin(uTime*2.0 + aSeed*40.0);',
        '  gl_PointSize = uSize * twinkle * (1.0 + 0.5*m) * (14.0 / -mv.z);',
        '  vSeed = aSeed; vMorph = m; vDepth = -mv.z;',
        '}'
      ].join('\n'),
      fragmentShader: [
        'uniform vec3 uColA, uColB;',
        'varying float vSeed, vMorph, vDepth;',
        'void main(){',
        '  vec2 c = gl_PointCoord - 0.5; float d = length(c);',
        '  if (d > 0.5) discard;',
        '  float core = smoothstep(0.5, 0.05, d);',
        '  float halo = smoothstep(0.5, 0.0, d) * 0.35;',
        '  vec3 col = mix(uColA, uColB, vMorph * 0.8 + vSeed * 0.2);',
        '  float fade = clamp(1.0 - (vDepth - 18.0) / 40.0, 0.15, 1.0);',
        '  gl_FragColor = vec4(col, (core * 0.9 + halo) * fade);',
        '}'
      ].join('\n')
    });
    points = new THREE.Points(geo, mat);
    scene.add(points);

    /* --- calibration rings: three thin circles that settle behind the lattice --- */
    rings = new THREE.Group();
    [9, 13, 17].forEach(function (R, k) {
      var seg = 160, v = [];
      for (var s = 0; s < seg; s++) {
        var t0 = (s / seg) * Math.PI * 2, t1 = ((s + 1) / seg) * Math.PI * 2;
        v.push(Math.cos(t0) * R, Math.sin(t0) * R, 0, Math.cos(t1) * R, Math.sin(t1) * R, 0);
      }
      var g = new THREE.BufferGeometry();
      g.setAttribute('position', new THREE.Float32BufferAttribute(v, 3));
      var m = new THREE.LineBasicMaterial({ color: 0x7de8cd, transparent: true, opacity: 0.08 + k * 0.03 });
      var line = new THREE.LineSegments(g, m);
      line.position.z = -12 - k * 2;
      rings.add(line);
    });
    scene.add(rings);

    resize();
    return true;
  }

  function resize() {
    if (!renderer) return;
    var w = stage.clientWidth, h = stage.clientHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h; camera.updateProjectionMatrix();
  }

  var ARC = [
    { p: 0.0, bg: 0x06110e, a: 0x2f8c74, b: 0xa9f0dd },
    { p: 0.55, bg: 0x0b2822, a: 0x3ea88b, b: 0xc6f6e8 },
    { p: 1.0, bg: 0x0f2a24, a: 0x7de8cd, b: 0xffffff }
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
    uniforms.uMorph.value = p;
    uniforms.uPx.value = state.spx * 0.9;
    uniforms.uPy.value = state.spy * 0.6;
    arc(p, 'bg', scene.background); scene.fog.color.copy(scene.background);
    arc(p, 'a', uniforms.uColA.value); arc(p, 'b', uniforms.uColB.value);

    camera.position.set(2.5 - p * 2.2 + state.spx * 0.6, 1.2 - p * 0.4 + state.spy * 0.4, 20 - p * 7);
    camera.lookAt(0, -0.6, -6);
    rings.rotation.z = t * 0.03;
    rings.rotation.x = 0.35 - p * 0.35;
    rings.children.forEach(function (r, k) { r.material.opacity = (0.04 + k * 0.03) + p * 0.14; });

    renderer.render(scene, camera);
    requestAnimationFrame(frame);
  }

  function setPhase(p) {
    var idx = p < 0.33 ? 0 : p < 0.7 ? 1 : 2;
    phases.forEach(function (li, i) { li.classList.toggle('is-on', i === idx); });
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
      onUpdate: function (st) { state.tp = st.progress; setPhase(st.progress); }
    });
    // Copy drifts up and thins as the lattice resolves, so the scene gets the stage.
    gsap.to(root.querySelector('.lab-hero-grid'), {
      y: -60, opacity: 0.1, ease: 'none',
      scrollTrigger: { trigger: hero, start: '35% top', end: 'bottom bottom', scrub: true }
    });
    // Pause rendering when the hero is off screen.
    ScrollTrigger.create({
      trigger: hero, start: 'top bottom', end: 'bottom top',
      onEnter: function () { if (!state.running) { state.running = true; frame(); } },
      onEnterBack: function () { if (!state.running) { state.running = true; frame(); } },
      onLeave: function () { state.running = false; },
      onLeaveBack: function () { state.running = false; }
    });
  } else {
    doc.classList.remove('lab-gsap');
  }

  /* Title lines rise in on load. */
  gsap.from(root.querySelectorAll('.lab-title-line'), { yPercent: 110, duration: 1.1, ease: 'power4.out', stagger: 0.09, delay: 0.1 });
  gsap.from(root.querySelectorAll('.lab-eyebrow, .lab-lead, .lab-actions, .lab-hero-panel'), { opacity: 0, y: 18, duration: 0.9, ease: 'power3.out', stagger: 0.08, delay: 0.35 });

  /* ================= REVEALS ================= */
  var revealSel = '.lab-ledger-item, .lab-head, .lab-matrix-card, .lab-scope-col, .lab-bento-card, .lab-method-copy, .lab-faq-item, .lab-related-card, .lab-final-grid > *';
  root.querySelectorAll(revealSel).forEach(function (el) { el.setAttribute('data-lab-reveal', ''); });
  var reveal = function (els) { gsap.to(els, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out', stagger: 0.07, overwrite: true }); };
  ScrollTrigger.batch(root.querySelectorAll('[data-lab-reveal]'), {
    start: 'top 88%',
    onEnter: reveal,
    // A jump (anchor link, restored scroll position) can carry an element
    // straight past its trigger; onLeave catches it so nothing stays hidden.
    onLeave: reveal
  });
  // Belt and braces: anything already above the trigger line after a layout
  // refresh or a jump is revealed at once.
  var sweep = function () {
    var line = window.innerHeight * 0.88, due = [];
    root.querySelectorAll('[data-lab-reveal]').forEach(function (el) {
      if (el.getBoundingClientRect().top < line && getComputedStyle(el).opacity !== '1') due.push(el);
    });
    if (due.length) reveal(due);
  };
  ScrollTrigger.addEventListener('refresh', sweep);
  lenis.on('scroll', function () { if (sweep._t) return; sweep._t = setTimeout(function () { sweep._t = 0; sweep(); }, 120); });
  window.addEventListener('load', sweep);

  /* ================= JOURNEY: pinned horizontal ================= */
  var track = root.querySelector('[data-lab-track]');
  var journey = root.querySelector('.lab-journey');
  var cards = root.querySelectorAll('[data-lab-card]');
  var bar = root.querySelector('.lab-journey-bar i');
  var count = root.querySelector('.lab-journey-count');
  if (track && !isMobile) {
    var distance = function () { return Math.max(0, track.scrollWidth - journey.clientWidth + 24); };
    gsap.to(track, {
      x: function () { return -distance(); },
      ease: 'none',
      scrollTrigger: {
        trigger: journey, start: 'top top', end: function () { return '+=' + (distance() + 200); },
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
  } else {
    cards.forEach(function (c) { c.classList.add('is-on'); });
  }

  /* ================= MATRIX: cursor tilt + glow ================= */
  if (!isMobile) {
    root.querySelectorAll('[data-lab-tilt]').forEach(function (card) {
      var glow = card.querySelector('.lab-matrix-glow');
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

  /* ================= METHOD: line draws, dots light ================= */
  var path = root.querySelector('.lab-method-line path');
  var steps = root.querySelectorAll('[data-lab-step]');
  if (path) {
    gsap.to(path, {
      strokeDashoffset: 0, ease: 'none',
      scrollTrigger: {
        trigger: root.querySelector('.lab-method-list'), start: 'top 70%', end: 'bottom 60%', scrub: true,
        onUpdate: function (st) {
          var n = Math.round(st.progress * steps.length + 0.25);
          steps.forEach(function (li, k) { li.classList.toggle('is-on', k < n); });
        }
      }
    });
  }

  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
