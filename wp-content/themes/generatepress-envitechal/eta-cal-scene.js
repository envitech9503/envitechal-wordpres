/* Envi Tech AL — Equipment Calibration Services flagship
 * Hero: a Three.js instrument face. Concentric scale rings, a needle and a
 * cloud of readings that begin scattered and drifting and, as the visitor
 * scrolls, converge on the reference value while the needle settles to zero
 * and the tolerance band narrows. The gauge reads the residual deviation.
 * Below the hero, GSAP + ScrollTrigger drive the pinned instrument-family
 * track, cursor-lit certificate cards, a drawn workflow line and the reveals.
 * Lenis smooths the scroll.
 * Progressive enhancement: html.cal-gsap is added by the inline bootstrap only
 * when motion is allowed; this module removes it again if WebGL is missing so
 * the static hero stands. 13-09-2026 */

import * as THREE from './assets/js/vendor/three-slim.js';
import { gsap, ScrollTrigger, Lenis } from './assets/js/vendor/motion.js';

(function () {
  'use strict';
  var doc = document.documentElement;
  var root = document.getElementById('eta-cal');
  if (!root) return;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  if (reduced) { doc.classList.remove('cal-gsap'); return; }
  doc.classList.add('cal-gsap');

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
  var hero = root.querySelector('.cal-hero');
  var stage = root.querySelector('.cal-stage');
  var canvas = root.querySelector('.cal-gl');
  var gaugeBar = root.querySelector('.cal-gauge-bar i');
  var gaugeVal = root.querySelector('.cal-gauge-v');
  var state = { p: 0, tp: 0, px: 0, py: 0, spx: 0, spy: 0, running: false };

  var renderer, scene, camera, dial, needle, readings, band, uniforms, ringMats = [];
  var seed = 5;
  function rnd() { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; }

  function ringGeo(R, seg, dashEvery) {
    var v = [];
    for (var s = 0; s < seg; s++) {
      if (dashEvery && (s % dashEvery) >= dashEvery / 2) continue;
      var t0 = (s / seg) * Math.PI * 2, t1 = ((s + 1) / seg) * Math.PI * 2;
      v.push(Math.cos(t0) * R, Math.sin(t0) * R, 0, Math.cos(t1) * R, Math.sin(t1) * R, 0);
    }
    var g = new THREE.BufferGeometry();
    g.setAttribute('position', new THREE.Float32BufferAttribute(v, 3));
    return g;
  }
  function tickGeo(R, n, len, every) {
    var v = [];
    for (var i = 0; i < n; i++) {
      var a = (i / n) * Math.PI * 2, L = (i % every === 0) ? len * 1.9 : len;
      v.push(Math.cos(a) * R, Math.sin(a) * R, 0, Math.cos(a) * (R - L), Math.sin(a) * (R - L), 0);
    }
    var g = new THREE.BufferGeometry();
    g.setAttribute('position', new THREE.Float32BufferAttribute(v, 3));
    return g;
  }

  function initGL() {
    try {
      renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: false, powerPreference: 'high-performance' });
    } catch (e) { return false; }
    if (!renderer.getContext()) return false;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 1.75));

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0b0d14);
    scene.fog = new THREE.Fog(0x0b0d14, 20, 60);
    camera = new THREE.PerspectiveCamera(isMobile ? 55 : 42, 1, 0.1, 200);

    /* --- the instrument face: rings and ticks --- */
    dial = new THREE.Group();
    [[14, 0.10, 0], [11.5, 0.16, 12], [9, 0.22, 0], [4, 0.3, 8]].forEach(function (r) {
      var m = new THREE.LineBasicMaterial({ color: 0xa78bfa, transparent: true, opacity: r[1] });
      ringMats.push(m);
      dial.add(new THREE.LineSegments(ringGeo(r[0], 240, r[2]), m));
    });
    var tickMat = new THREE.LineBasicMaterial({ color: 0xd6c7ff, transparent: true, opacity: 0.55 });
    ringMats.push(tickMat);
    dial.add(new THREE.LineSegments(tickGeo(14, 120, 0.5, 10), tickMat));
    dial.add(new THREE.LineSegments(tickGeo(9, 60, 0.35, 5), tickMat));
    dial.position.x = isMobile ? 0 : 7;
    scene.add(dial);

    /* --- tolerance band: a thin wedge that narrows as the readings settle --- */
    var bandGeo = new THREE.BufferGeometry();
    bandGeo.setAttribute('position', new THREE.Float32BufferAttribute(new Float32Array(3 * 4), 3));
    band = new THREE.LineSegments(bandGeo, new THREE.LineBasicMaterial({ color: 0xa78bfa, transparent: true, opacity: 0.5 }));
    dial.add(band);

    /* --- the needle --- */
    var ng = new THREE.BufferGeometry();
    ng.setAttribute('position', new THREE.Float32BufferAttribute([0, -1.6, 0.2, 0, 13.4, 0.2], 3));
    needle = new THREE.Line(ng, new THREE.LineBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.95 }));
    dial.add(needle);

    /* --- readings: points that converge on the reference --- */
    var N = isMobile ? 700 : 1400;
    var pos = new Float32Array(N * 3), seeds = new Float32Array(N), err = new Float32Array(N * 2);
    for (var i = 0; i < N; i++) {
      var a = rnd() * Math.PI * 2, r = 3 + rnd() * 12;
      pos[i * 3] = Math.cos(a) * r; pos[i * 3 + 1] = Math.sin(a) * r; pos[i * 3 + 2] = (rnd() - 0.5) * 6;
      seeds[i] = rnd();
      // target: on the reference line (the needle at zero), spread along its length
      err[i * 2] = 0; err[i * 2 + 1] = 1.5 + rnd() * 11.5;
    }
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    geo.setAttribute('aSeed', new THREE.BufferAttribute(seeds, 1));
    geo.setAttribute('aTarget', new THREE.BufferAttribute(err, 2));
    uniforms = { uTime: { value: 0 }, uCal: { value: 0 }, uSize: { value: isMobile ? 8 : 10 }, uColA: { value: new THREE.Color(0xb9bce0) }, uColB: { value: new THREE.Color(0xd6c7ff) } };
    var mat = new THREE.ShaderMaterial({
      uniforms: uniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
      vertexShader: [
        'attribute float aSeed; attribute vec2 aTarget;',
        'uniform float uTime, uCal, uSize;',
        'varying float vK, vSeed;',
        'float ease(float t){ return t*t*(3.0-2.0*t); }',
        'void main(){',
        '  float k = ease(clamp(uCal * 1.2 - aSeed * 0.2, 0.0, 1.0));',
        '  vec3 drift = vec3(sin(uTime*0.7 + aSeed*17.0), cos(uTime*0.6 + aSeed*11.0), sin(uTime*0.5 + aSeed*7.0)) * (0.6 * (1.0 - k) + 0.05);',
        '  vec3 tgt = vec3(aTarget.x + sin(uTime*1.3 + aSeed*30.0) * 0.12, aTarget.y, 0.3);',
        '  vec3 p = mix(position, tgt, k) + drift;',
        '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
        '  gl_Position = projectionMatrix * mv;',
        '  gl_PointSize = uSize * (0.7 + 0.5*aSeed) * (0.7 + 0.5*k) * (18.0 / -mv.z);',
        '  vK = k; vSeed = aSeed;',
        '}'
      ].join('\n'),
      fragmentShader: [
        'uniform vec3 uColA, uColB;',
        'varying float vK, vSeed;',
        'void main(){',
        '  vec2 c = gl_PointCoord - 0.5; float d = length(c);',
        '  if (d > 0.5) discard;',
        '  float core = smoothstep(0.5, 0.08, d);',
        '  vec3 col = mix(uColA, uColB, vK);',
        '  gl_FragColor = vec4(col, core * (0.85 + 0.15*vK));',
        '}'
      ].join('\n')
    });
    readings = new THREE.Points(geo, mat);
    readings.position.copy(dial.position);
    scene.add(readings);

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
  var bgA = new THREE.Color(0x0b0d14), bgB = new THREE.Color(0x151a2b), bg = new THREE.Color();
  function frame() {
    if (!state.running) return;
    var t = (performance.now() - t0) / 1000;
    state.p += (state.tp - state.p) * 0.08;
    state.spx += (state.px - state.spx) * 0.06;
    state.spy += (state.py - state.spy) * 0.06;
    var p = state.p, e = p * p * (3 - 2 * p);

    uniforms.uTime.value = t; uniforms.uCal.value = p;
    bg.copy(bgA).lerp(bgB, e); scene.background.copy(bg); scene.fog.color.copy(bg);

    // Needle: drifting and wandering, settling to the reference (straight up).
    var wander = Math.sin(t * 1.7) * 0.35 + Math.sin(t * 0.9) * 0.2;
    needle.rotation.z = (0.55 + wander) * (1 - e);
    // Tolerance band narrows around the reference.
    var half = 0.5 * (1 - e) + 0.03, R = 13.6, arr = band.geometry.attributes.position.array;
    var a0 = Math.PI / 2 - half, a1 = Math.PI / 2 + half;
    arr[0] = 0; arr[1] = 0; arr[2] = 0.05; arr[3] = Math.cos(a0) * R; arr[4] = Math.sin(a0) * R; arr[5] = 0.05;
    arr[6] = 0; arr[7] = 0; arr[8] = 0.05; arr[9] = Math.cos(a1) * R; arr[10] = Math.sin(a1) * R; arr[11] = 0.05;
    band.geometry.attributes.position.needsUpdate = true;
    band.material.opacity = 0.35 + e * 0.4;
    ringMats.forEach(function (m, i) { m.opacity = (i === 4 ? 0.75 : 0.34 + i * 0.08) + e * 0.2; });

    dial.rotation.z = t * 0.01;
    dial.rotation.x = -0.25 + state.spy * 0.08;
    dial.rotation.y = state.spx * 0.12;
    readings.rotation.copy(dial.rotation);
    camera.position.set(state.spx * 1.2 + 4 - e * 4, -6 + e * 2 + state.spy * 0.6, 36 - e * 6);
    camera.lookAt(isMobile ? 0 : 3, 1.5, 0);

    renderer.render(scene, camera);
    requestAnimationFrame(frame);
  }

  function setGauge(p) {
    if (gaugeBar) gaugeBar.style.transform = 'scaleX(' + (0.06 + p * 0.94) + ')';
    if (gaugeVal) gaugeVal.textContent = '\u00b1' + (2.4 * (1 - p)).toFixed(2) + ' %';
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
    gsap.to(root.querySelector('.cal-hero-grid'), {
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
    doc.classList.remove('cal-gsap');
  }

  gsap.from(root.querySelectorAll('.cal-title-line'), { yPercent: 110, duration: 1.1, ease: 'power4.out', stagger: 0.09, delay: 0.1 });
  gsap.from(root.querySelectorAll('.cal-eyebrow, .cal-lead, .cal-actions, .cal-hero-panel'), { opacity: 0, y: 18, duration: 0.9, ease: 'power3.out', stagger: 0.08, delay: 0.35 });

  /* ================= REVEALS ================= */
  var revealSel = '.cal-ledger-item, .cal-head, .cal-deliver-card, .cal-regs-copy, .cal-regs-list li, .cal-serve-card, .cal-journey-copy, .cal-why-card, .cal-faq-item, .cal-related-card, .cal-final-grid > *';
  root.querySelectorAll(revealSel).forEach(function (el) { el.setAttribute('data-cal-reveal', ''); });
  var reveal = function (els) { gsap.to(els, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out', stagger: 0.07, overwrite: true }); };
  ScrollTrigger.batch(root.querySelectorAll('[data-cal-reveal]'), { start: 'top 88%', onEnter: reveal, onLeave: reveal });
  var sweep = function () {
    var line = window.innerHeight * 0.88, due = [];
    root.querySelectorAll('[data-cal-reveal]').forEach(function (el) {
      if (el.getBoundingClientRect().top < line && getComputedStyle(el).opacity !== '1') due.push(el);
    });
    if (due.length) reveal(due);
  };
  ScrollTrigger.addEventListener('refresh', sweep);
  lenis.on('scroll', function () { if (sweep._t) return; sweep._t = setTimeout(function () { sweep._t = 0; sweep(); }, 120); });
  window.addEventListener('load', sweep);

  /* ================= PATHWAY: pinned horizontal ================= */
  var track = root.querySelector('[data-cal-track]');
  var types = root.querySelector('.cal-types');
  var cards = root.querySelectorAll('[data-cal-card]');
  var bar = root.querySelector('.cal-types-bar i');
  var count = root.querySelector('.cal-types-count');
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
    root.querySelectorAll('[data-cal-tilt]').forEach(function (card) {
      var glow = card.querySelector('.cal-deliver-glow');
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
  var path = root.querySelector('.cal-journey-line path');
  var steps = root.querySelectorAll('[data-cal-step]');
  if (path) {
    gsap.to(path, {
      strokeDashoffset: 0, ease: 'none',
      scrollTrigger: {
        trigger: root.querySelector('.cal-journey-list'), start: 'top 70%', end: 'bottom 60%', scrub: true,
        onUpdate: function (st) {
          var n = Math.round(st.progress * steps.length + 0.25);
          steps.forEach(function (li, k) { li.classList.toggle('is-on', k < n); });
        }
      }
    });
  }

  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
