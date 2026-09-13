/* Envi Tech AL — Technical Advisory flagship
 * Hero: a Three.js root-cause tree. Audit findings begin as a loose cloud
 * and, as the visitor scrolls, trace up through corrective actions and root
 * causes to a single closed audit, links drawing in as each level settles.
 * The gauge reads the share of findings traced. Below the hero, GSAP +
 * ScrollTrigger drive the pinned review sequence, cursor-lit support cards,
 * a drawn engagement line and the reveals. Lenis smooths the scroll.
 * Progressive enhancement: html.ta-gsap is added by the inline bootstrap only
 * when motion is allowed; this module removes it again if WebGL is missing so
 * the static hero stands. 13-09-2026 */

import * as THREE from './assets/js/vendor/three-slim.js';
import { gsap, ScrollTrigger, Lenis } from './assets/js/vendor/motion.js';

(function () {
  'use strict';
  var doc = document.documentElement;
  var root = document.getElementById('eta-techadv');
  if (!root) return;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  if (reduced) { doc.classList.remove('ta-gsap'); return; }
  doc.classList.add('ta-gsap');

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
  var hero = root.querySelector('.ta-hero');
  var stage = root.querySelector('.ta-stage');
  var canvas = root.querySelector('.ta-gl');
  var gaugeBar = root.querySelector('.ta-gauge-bar i');
  var gaugeVal = root.querySelector('.ta-gauge-v');
  var state = { p: 0, tp: 0, px: 0, py: 0, spx: 0, spy: 0, running: false };

  var renderer, scene, camera, nodes, links, uniforms, linkUniforms;
  var seed = 13;
  function rnd() { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; }

  function initGL() {
    try {
      renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: false, powerPreference: 'high-performance' });
    } catch (e) { return false; }
    if (!renderer.getContext()) return false;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 1.75));

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0a1220);
    scene.fog = new THREE.Fog(0x0a1220, 30, 90);
    camera = new THREE.PerspectiveCamera(isMobile ? 55 : 42, 1, 0.1, 200);

    /* --- the tree: 1 closed audit, 4 root causes, 16 corrective actions, 48 findings --- */
    var levels = [1, 4, 16, 48], levelY = [9, 4, -1.5, -7];
    var tgt = [], start = [], lvl = [], parent = [];
    var idx = 0, prevStart = 0;
    for (var L = 0; L < levels.length; L++) {
      var n = levels[L], span = [0, 14, 26, 34][L];
      var thisStart = idx;
      for (var i = 0; i < n; i++) {
        var x = n === 1 ? 0 : (i / (n - 1) - 0.5) * span;
        var z = L === 3 ? (i % 2 ? 2.2 : -2.2) : 0;
        tgt.push(x, levelY[L], z);
        start.push((rnd() - 0.5) * 40, (rnd() - 0.5) * 24, (rnd() - 0.5) * 24);
        lvl.push(L);
        parent.push(L === 0 ? -1 : prevStart + Math.floor(i / (n / levels[L - 1])));
        idx++;
      }
      prevStart = thisStart;
    }
    var N = idx;
    var pos = new Float32Array(start), tg = new Float32Array(tgt), lv = new Float32Array(lvl), seeds = new Float32Array(N);
    for (var k = 0; k < N; k++) seeds[k] = rnd();
    var geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    geo.setAttribute('aTarget', new THREE.BufferAttribute(tg, 3));
    geo.setAttribute('aLevel', new THREE.BufferAttribute(lv, 1));
    geo.setAttribute('aSeed', new THREE.BufferAttribute(seeds, 1));
    var MORPH = [
      'float ease(float t){ return t*t*(3.0-2.0*t); }',
      'vec3 morph(vec3 s, vec3 t, float level, float seed, float k0, float time){',
      // upper levels settle first, so the structure appears top-down
      '  float k = ease(clamp(k0 * 1.6 - level * 0.15 - seed * 0.1, 0.0, 1.0));',
      '  vec3 drift = vec3(sin(time*0.6 + seed*15.0), cos(time*0.5 + seed*9.0), sin(time*0.7 + seed*5.0)) * (0.8 * (1.0 - k) + 0.05);',
      '  return mix(s, t, k) + drift; }'
    ].join('\n');
    uniforms = { uTime: { value: 0 }, uK: { value: 0 }, uSize: { value: isMobile ? 9 : 11 } };
    var mat = new THREE.ShaderMaterial({
      uniforms: uniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
      vertexShader: [
        'attribute vec3 aTarget; attribute float aLevel, aSeed;',
        'uniform float uTime, uK, uSize;',
        'varying float vL, vK;',
        MORPH,
        'void main(){',
        '  vec3 p = morph(position, aTarget, aLevel, aSeed, uK, uTime);',
        '  vec4 mv = modelViewMatrix * vec4(p, 1.0);',
        '  gl_Position = projectionMatrix * mv;',
        '  float sz = aLevel == 0.0 ? 2.4 : (aLevel == 1.0 ? 1.7 : (aLevel == 2.0 ? 1.2 : 0.85));',
        '  gl_PointSize = uSize * sz * (22.0 / -mv.z);',
        '  vL = aLevel; vK = ease(clamp(uK * 1.6 - aLevel * 0.15 - aSeed * 0.1, 0.0, 1.0));',
        '}'
      ].join('\n'),
      fragmentShader: [
        'varying float vL, vK;',
        'void main(){',
        '  vec2 c = gl_PointCoord - 0.5; float d = length(c);',
        '  if (d > 0.5) discard;',
        '  float ring = smoothstep(0.5, 0.4, d);',
        '  float core = smoothstep(0.32, 0.1, d);',
        '  vec3 loose = vec3(0.55, 0.62, 0.55);',
        '  vec3 set = vL <= 1.0 ? vec3(0.86, 0.92, 1.0) : vec3(0.58, 0.77, 0.99);',
        '  vec3 col = mix(loose, set, vK);',
        '  gl_FragColor = vec4(col, (ring * 0.35 + core) * (0.7 + 0.3*vK));',
        '}'
      ].join('\n')
    });
    nodes = new THREE.Points(geo, mat);
    scene.add(nodes);

    /* --- links: one segment per child, morphing with its endpoints --- */
    var ls = [], lt = [], ll = [], lsd = [];
    for (var c2 = 1; c2 < N; c2++) {
      var pI = parent[c2];
      ls.push(start[c2 * 3], start[c2 * 3 + 1], start[c2 * 3 + 2], start[pI * 3], start[pI * 3 + 1], start[pI * 3 + 2]);
      lt.push(tgt[c2 * 3], tgt[c2 * 3 + 1], tgt[c2 * 3 + 2], tgt[pI * 3], tgt[pI * 3 + 1], tgt[pI * 3 + 2]);
      ll.push(lvl[c2], lvl[pI]); lsd.push(seeds[c2], seeds[pI]);
    }
    var lg = new THREE.BufferGeometry();
    lg.setAttribute('position', new THREE.Float32BufferAttribute(ls, 3));
    lg.setAttribute('aTarget', new THREE.Float32BufferAttribute(lt, 3));
    lg.setAttribute('aLevel', new THREE.Float32BufferAttribute(ll, 1));
    lg.setAttribute('aSeed', new THREE.Float32BufferAttribute(lsd, 1));
    linkUniforms = { uTime: { value: 0 }, uK: { value: 0 } };
    var lm = new THREE.ShaderMaterial({
      uniforms: linkUniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
      vertexShader: [
        'attribute vec3 aTarget; attribute float aLevel, aSeed;',
        'uniform float uTime, uK;',
        'varying float vK;',
        MORPH,
        'void main(){',
        '  vec3 p = morph(position, aTarget, aLevel, aSeed, uK, uTime);',
        '  gl_Position = projectionMatrix * modelViewMatrix * vec4(p, 1.0);',
        '  vK = ease(clamp(uK * 1.6 - aLevel * 0.15 - aSeed * 0.1, 0.0, 1.0));',
        '}'
      ].join('\n'),
      fragmentShader: [
        'varying float vK;',
        'void main(){ gl_FragColor = vec4(0.58, 0.77, 0.99, 0.04 + vK * 0.42); }'
      ].join('\n')
    });
    links = new THREE.LineSegments(lg, lm);
    scene.add(links);

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
  var bgA = new THREE.Color(0x0a1220), bgB = new THREE.Color(0x12203a), bg = new THREE.Color();
  function frame() {
    if (!state.running) return;
    var t = (performance.now() - t0) / 1000;
    state.p += (state.tp - state.p) * 0.08;
    state.spx += (state.px - state.spx) * 0.06;
    state.spy += (state.py - state.spy) * 0.06;
    var p = state.p, e = p * p * (3 - 2 * p);

    uniforms.uTime.value = t; uniforms.uK.value = p;
    linkUniforms.uTime.value = t; linkUniforms.uK.value = p;
    bg.copy(bgA).lerp(bgB, e); scene.background.copy(bg); scene.fog.color.copy(bg);

    var ang = 0.5 - e * 0.5 + state.spx * 0.12;
    nodes.rotation.y = ang; links.rotation.y = ang;
    nodes.rotation.x = state.spy * 0.05; links.rotation.x = nodes.rotation.x;
    var off = isMobile ? 0 : 7;
    nodes.position.x = off; links.position.x = off;
    camera.position.set(off + state.spx * 1.0, 2 + state.spy * 0.6, 44 - e * 4);
    camera.lookAt(off, 0.5, 0);

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
    gsap.to(root.querySelector('.ta-hero-grid'), {
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
    doc.classList.remove('ta-gsap');
  }

  gsap.from(root.querySelectorAll('.ta-title-line'), { yPercent: 110, duration: 1.1, ease: 'power4.out', stagger: 0.09, delay: 0.1 });
  gsap.from(root.querySelectorAll('.ta-eyebrow, .ta-lead, .ta-actions, .ta-hero-panel'), { opacity: 0, y: 18, duration: 0.9, ease: 'power3.out', stagger: 0.08, delay: 0.35 });

  /* ================= REVEALS ================= */
  var revealSel = '.ta-ledger-item, .ta-head, .ta-deliver-card, .ta-scheme-card, .ta-regs-copy, .ta-regs-list li, .ta-serve-card, .ta-journey-copy, .ta-why-card, .ta-faq-item, .ta-related-card, .ta-final-grid > *';
  root.querySelectorAll(revealSel).forEach(function (el) { el.setAttribute('data-ta-reveal', ''); });
  var reveal = function (els) { gsap.to(els, { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out', stagger: 0.07, overwrite: true }); };
  ScrollTrigger.batch(root.querySelectorAll('[data-ta-reveal]'), { start: 'top 88%', onEnter: reveal, onLeave: reveal });
  var sweep = function () {
    var line = window.innerHeight * 0.88, due = [];
    root.querySelectorAll('[data-ta-reveal]').forEach(function (el) {
      if (el.getBoundingClientRect().top < line && getComputedStyle(el).opacity !== '1') due.push(el);
    });
    if (due.length) reveal(due);
  };
  ScrollTrigger.addEventListener('refresh', sweep);
  lenis.on('scroll', function () { if (sweep._t) return; sweep._t = setTimeout(function () { sweep._t = 0; sweep(); }, 120); });
  window.addEventListener('load', sweep);

  /* ================= PATHWAY: pinned horizontal ================= */
  var track = root.querySelector('[data-ta-track]');
  var types = root.querySelector('.ta-types');
  var cards = root.querySelectorAll('[data-ta-card]');
  var bar = root.querySelector('.ta-types-bar i');
  var count = root.querySelector('.ta-types-count');
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
    root.querySelectorAll('[data-ta-tilt]').forEach(function (card) {
      var glow = card.querySelector('.ta-deliver-glow');
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
  var path = root.querySelector('.ta-journey-line path');
  var steps = root.querySelectorAll('[data-ta-step]');
  if (path) {
    gsap.to(path, {
      strokeDashoffset: 0, ease: 'none',
      scrollTrigger: {
        trigger: root.querySelector('.ta-journey-list'), start: 'top 70%', end: 'bottom 60%', scrub: true,
        onUpdate: function (st) {
          var n = Math.round(st.progress * steps.length + 0.25);
          steps.forEach(function (li, k) { li.classList.toggle('is-on', k < n); });
        }
      }
    });
  }

  window.addEventListener('load', function () { ScrollTrigger.refresh(); });
})();
