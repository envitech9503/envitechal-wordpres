/* Envi Tech AL — Ballast Water Testing Services flagship
 * Hero: a Three.js ballast tank. A swarm of organisms drifts through the
 * tank volume and, as the visitor scrolls, is counted: each particle sorts
 * into one of three columns, the two size bands and the indicator microbes
 * of the IMO D-2 standard, while the gauge reads the share of the sample
 * counted. Below the hero, GSAP + ScrollTrigger drive the pinned port-call
 * sequence, cursor-lit analysis cards, a drawn workflow line and the reveals.
 * Lenis smooths the scroll.
 * Progressive enhancement: html.bw-gsap is added by the inline bootstrap only
 * when motion is allowed; this module removes it again if WebGL is missing so
 * the static hero stands. 13-09-2026 */

import * as THREE from './assets/js/vendor/three-slim.js';
import { gsap, ScrollTrigger, Lenis } from './assets/js/vendor/motion.js';

(function () {
  'use strict';
  var doc = document.documentElement;
  var root = document.getElementById('eta-ballast');
  if (!root) return;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  if (reduced) { doc.classList.remove('bw-gsap'); return; }
  doc.classList.add('bw-gsap');

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
  var hero = root.querySelector('.bw-hero');
  var stage = root.querySelector('.bw-stage');
  var canvas = root.querySelector('.bw-gl');
  var gaugeBar = root.querySelector('.bw-gauge-bar i');
  var gaugeVal = root.querySelector('.bw-gauge-v');
  var state = { p: 0, tp: 0, px: 0, py: 0, spx: 0, spy: 0, running: false };

  var renderer, scene, camera, tank, swarm, uniforms, tankMat, colMats = [];
  var seed = 9;
  function rnd() { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; }

  var TW = 30, TH = 14, TD = 16;          // tank size
  var COLS = [-9, 0, 9];                   // x of the three counting columns

  function initGL() {
    try {
      renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: false, powerPreference: 'high-performance' });
    } catch (e) { return false; }
    if (!renderer.getContext()) return false;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 1.75));

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x061019);
    scene.fog = new THREE.Fog(0x061019, 30, 80);
    camera = new THREE.PerspectiveCamera(isMobile ? 55 : 42, 1, 0.1, 200);

    /* --- the tank: a wireframe box with a waterline --- */
    tank = new THREE.Group();
    tankMat = new THREE.LineBasicMaterial({ color: 0xff8a4c, transparent: true, opacity: 0.35 });
    tank.add(new THREE.LineSegments(new THREE.EdgesGeometry(new THREE.BoxGeometry(TW, TH, TD)), tankMat));
    var wl = new THREE.BufferGeometry();
    wl.setAttribute('position', new THREE.Float32BufferAttribute([-TW / 2, TH * 0.3, TD / 2, TW / 2, TH * 0.3, TD / 2, -TW / 2, TH * 0.3, -TD / 2, TW / 2, TH * 0.3, -TD / 2, -TW / 2, TH * 0.3, TD / 2, -TW / 2, TH * 0.3, -TD / 2, TW / 2, TH * 0.3, TD / 2, TW / 2, TH * 0.3, -TD / 2], 3));
    tank.add(new THREE.LineSegments(wl, new THREE.LineBasicMaterial({ color: 0xffc19a, transparent: true, opacity: 0.25 })));
    /* three counting columns, drawn as thin cylinders that brighten as they fill */
    COLS.forEach(function (x) {
      var m = new THREE.LineBasicMaterial({ color: 0xffc19a, transparent: true, opacity: 0.0 });
      colMats.push(m);
      var cyl = new THREE.LineSegments(new THREE.EdgesGeometry(new THREE.CylinderGeometry(2.6, 2.6, TH - 2, 24, 1, true)), m);
      cyl.position.set(x, 0, 0);
      tank.add(cyl);
    });
    scene.add(tank);

    /* --- the swarm --- */
    var N = isMobile ? 1200 : 2400;
    var pos = new Float32Array(N * 3), seeds = new Float32Array(N), tgt = new Float32Array(N * 3), band = new Float32Array(N);
    for (var i = 0; i < N; i++) {
      pos[i * 3] = (rnd() - 0.5) * (TW - 2); pos[i * 3 + 1] = (rnd() - 0.5) * (TH - 2); pos[i * 3 + 2] = (rnd() - 0.5) * (TD - 2);
      var b = i % 3; band[i] = b;
      var a = rnd() * Math.PI * 2, r = rnd() * 2.2;
      tgt[i * 3] = COLS[b] + Math.cos(a) * r; tgt[i * 3 + 1] = -(TH - 2) / 2 + rnd() * (TH - 2) * (b === 0 ? 0.35 : b === 1 ? 0.7 : 0.5); tgt[i * 3 + 2] = Math.sin(a) * r;
      seeds[i] = rnd();
    }
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    geo.setAttribute('aTarget', new THREE.BufferAttribute(tgt, 3));
    geo.setAttribute('aSeed', new THREE.BufferAttribute(seeds, 1));
    geo.setAttribute('aBand', new THREE.BufferAttribute(band, 1));
    uniforms = { uTime: { value: 0 }, uCount: { value: 0 }, uSize: { value: isMobile ? 7 : 9 } };
    var mat = new THREE.ShaderMaterial({
      uniforms: uniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
      vertexShader: [
        'attribute vec3 aTarget; attribute float aSeed, aBand;',
        'uniform float uTime, uCount, uSize;',
        'varying float vK, vBand, vSeed;',
        'float ease(float t){ return t*t*(3.0-2.0*t); }',
        'void main(){',
        '  float k = ease(clamp(uCount * 1.25 - aSeed * 0.25, 0.0, 1.0));',
        '  vec3 drift = vec3(sin(uTime*0.5 + aSeed*19.0), cos(uTime*0.7 + aSeed*13.0), sin(uTime*0.6 + aSeed*7.0)) * (0.9 * (1.0 - k) + 0.08);',
        '  vec3 p = mix(position, aTarget, k) + drift;',
        '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
        '  gl_Position = projectionMatrix * mv;',
        '  float sz = aBand == 0.0 ? 1.5 : (aBand == 1.0 ? 1.0 : 0.65);',
        '  gl_PointSize = uSize * sz * (0.7 + 0.5*aSeed) * (20.0 / -mv.z);',
        '  vK = k; vBand = aBand; vSeed = aSeed;',
        '}'
      ].join('\n'),
      fragmentShader: [
        'varying float vK, vBand, vSeed;',
        'void main(){',
        '  vec2 c = gl_PointCoord - 0.5; float d = length(c);',
        '  if (d > 0.5) discard;',
        '  float core = smoothstep(0.5, 0.1, d);',
        '  vec3 wild = vec3(0.55, 0.72, 0.80);',
        '  vec3 counted = vBand == 0.0 ? vec3(1.0, 0.54, 0.30) : (vBand == 1.0 ? vec3(1.0, 0.76, 0.60) : vec3(1.0, 0.92, 0.80));',
        '  vec3 col = mix(wild, counted, vK);',
        '  gl_FragColor = vec4(col, core * (0.6 + 0.4*vK));',
        '}'
      ].join('\n')
    });
    swarm = new THREE.Points(geo, mat);
    scene.add(swarm);

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
  var bgA = new THREE.Color(0x061019), bgB = new THREE.Color(0x0e2233), bg = new THREE.Color();
  function frame() {
    if (!state.running) return;
    var t = (performance.now() - t0) / 1000;
    state.p += (state.tp - state.p) * 0.08;
    state.spx += (state.px - state.spx) * 0.06;
    state.spy += (state.py - state.spy) * 0.06;
    var p = state.p, e = p * p * (3 - 2 * p);

    uniforms.uTime.value = t; uniforms.uCount.value = p;
    bg.copy(bgA).lerp(bgB, e); scene.background.copy(bg); scene.fog.color.copy(bg);
    tankMat.opacity = 0.3 + e * 0.3;
    colMats.forEach(function (m, i) { m.opacity = Math.max(0, Math.min(1, (e * 1.3 - i * 0.15))) * 0.55; });

    var ang = -0.45 + e * 0.35 + state.spx * 0.1;
    tank.rotation.y = ang; swarm.rotation.y = ang;
    tank.rotation.x = 0.22 + state.spy * 0.06; swarm.rotation.x = tank.rotation.x;
    var off = isMobile ? 0 : 6;
    tank.position.x = off; swarm.position.x = off;
    camera.position.set(off + state.spx * 1.0, 6 - e * 2, 42 - e * 2);
    camera.lookAt(off, -1, 0);

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
    gsap.to(root.querySelector('.bw-hero-grid'), {
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
    doc.classList.remove('bw-gsap');
  }

  gsap.from(root.querySelectorAll('.bw-title-line'), { yPercent: 110, duration: 1.1, ease: 'power4.out', stagger: 0.09, delay: 0.1 });
  gsap.from(root.querySelectorAll('.bw-eyebrow, .bw-lead, .bw-actions, .bw-hero-panel'), { opacity: 0, y: 18, duration: 0.9, ease: 'power3.out', stagger: 0.08, delay: 0.35 });

  /* ================= REVEALS ================= */
  var revealSel = '.bw-ledger-item, .bw-head, .bw-deliver-card, .bw-regs-copy, .bw-regs-list li, .bw-serve-card, .bw-journey-copy, .bw-why-card, .bw-faq-item, .bw-related-card, .bw-final-grid > *';
  root.querySelectorAll(revealSel).forEach(function (el) { el.setAttribute('data-bw-reveal', ''); });
  var reveal = function (els) { gsap.to(els, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out', stagger: 0.07, overwrite: true }); };
  ScrollTrigger.batch(root.querySelectorAll('[data-bw-reveal]'), { start: 'top 88%', onEnter: reveal, onLeave: reveal });
  var sweep = function () {
    var line = window.innerHeight * 0.88, due = [];
    root.querySelectorAll('[data-bw-reveal]').forEach(function (el) {
      if (el.getBoundingClientRect().top < line && getComputedStyle(el).opacity !== '1') due.push(el);
    });
    if (due.length) reveal(due);
  };
  ScrollTrigger.addEventListener('refresh', sweep);
  lenis.on('scroll', function () { if (sweep._t) return; sweep._t = setTimeout(function () { sweep._t = 0; sweep(); }, 120); });
  window.addEventListener('load', sweep);

  /* ================= PATHWAY: pinned horizontal ================= */
  var track = root.querySelector('[data-bw-track]');
  var types = root.querySelector('.bw-types');
  var cards = root.querySelectorAll('[data-bw-card]');
  var bar = root.querySelector('.bw-types-bar i');
  var count = root.querySelector('.bw-types-count');
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
    root.querySelectorAll('[data-bw-tilt]').forEach(function (card) {
      var glow = card.querySelector('.bw-deliver-glow');
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
  var path = root.querySelector('.bw-journey-line path');
  var steps = root.querySelectorAll('[data-bw-step]');
  if (path) {
    gsap.to(path, {
      strokeDashoffset: 0, ease: 'none',
      scrollTrigger: {
        trigger: root.querySelector('.bw-journey-list'), start: 'top 70%', end: 'bottom 60%', scrub: true,
        onUpdate: function (st) {
          var n = Math.round(st.progress * steps.length + 0.25);
          steps.forEach(function (li, k) { li.classList.toggle('is-on', k < n); });
        }
      }
    });
  }

  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
