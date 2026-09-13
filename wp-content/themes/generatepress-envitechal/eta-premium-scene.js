/* Envi Tech AL — shared premium layer for the secondary pages
 * One module for About, Downloads, Careers, Clients, Credentials, the
 * laboratory pages, SEQS, FAQs, TDAP, the Knowledge Hub, the services
 * archive and the cluster service pages. It mounts a Three.js scene behind
 * the page hero (the scene is chosen per page: a particle field, a wireframe
 * terrain, a stack of document sheets or an instrument orbit), drives it with
 * scroll progress and the cursor, reveals every section and card as it enters
 * the viewport, and gives cards a light cursor tilt. Lenis smooths the scroll
 * and is shared with any flagship module on the same page.
 * Progressive enhancement: html.pr-gsap is added by the inline bootstrap only
 * when motion is allowed; this module removes it again if WebGL is missing so
 * the static page stands. 13-09-2026 */

import * as THREE from './assets/js/vendor/three-slim.js';
import { gsap, ScrollTrigger, Lenis } from './assets/js/vendor/motion.js';

(function () {
  'use strict';
  var doc = document.documentElement;
  var cfg = window.__etaPremium || {};
  var main = document.querySelector('main') || document.body;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { doc.classList.remove('pr-gsap'); return; }
  doc.classList.add('pr-gsap');

  var isMobile = window.matchMedia('(max-width: 860px), (pointer: coarse)').matches;
  gsap.registerPlugin(ScrollTrigger);

  var lenis = window.__etaLenis || new Lenis({ duration: 0.95, smoothWheel: true, syncTouch: false, easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); } });
  if (!window.__etaLenis) {
    window.__etaLenis = lenis;
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add(function (t) { lenis.raf(t * 1000); });
    gsap.ticker.lagSmoothing(0);
  }

  /* ================= HERO SCENE ================= */
  var hero = main.querySelector(cfg.heroSelector || '.eta-about-hero, .eta-download-hero, .eta-career-hero, .eta-utility-hero, .eta-lahore-hero, .eta-seqs-hero, .eta-knowledge-hero, .eta-services-hero');
  var accent = new THREE.Color(cfg.accent || '#7de8cd');
  var accent2 = new THREE.Color(cfg.accent2 || '#dffbf2');
  var bgHex = cfg.bg || '#0b1f26';
  var state = { p: 0, tp: 0, px: 0, py: 0, spx: 0, spy: 0, running: false };
  var seed = 17;
  function rnd() { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; }

  function mountScene() {
    if (!hero) return false;
    var canvas = document.createElement('canvas');
    canvas.className = 'pr-gl'; canvas.setAttribute('aria-hidden', 'true');
    var veil = document.createElement('div'); veil.className = 'pr-veil'; veil.setAttribute('aria-hidden', 'true');
    hero.insertBefore(veil, hero.firstChild); hero.insertBefore(canvas, hero.firstChild);
    var renderer;
    try { renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true, powerPreference: 'high-performance' }); } catch (e) { return false; }
    if (!renderer.getContext()) return false;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 1.75));
    renderer.setClearColor(0x000000, 0);
    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(isMobile ? 55 : 42, 1, 0.1, 200);
    var group = new THREE.Group();
    scene.add(group);
    var kind = cfg.scene || 'field';
    var uniforms = { uTime: { value: 0 }, uP: { value: 0 }, uA: { value: accent }, uB: { value: accent2 }, uSize: { value: isMobile ? 7 : 9 } };
    var NOISE = [
      'float hash(vec2 p){ return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }',
      'float vnoise(vec2 p){ vec2 i = floor(p), f = fract(p); f = f*f*(3.0-2.0*f);',
      '  return mix(mix(hash(i), hash(i+vec2(1.0,0.0)), f.x), mix(hash(i+vec2(0.0,1.0)), hash(i+vec2(1.0,1.0)), f.x), f.y); }'
    ].join('\n');
    var pointFrag = [
      'uniform vec3 uA, uB; varying float vS;',
      'void main(){ vec2 c = gl_PointCoord - 0.5; float d = length(c); if (d > 0.5) discard;',
      '  float core = smoothstep(0.5, 0.08, d); gl_FragColor = vec4(mix(uA, uB, vS), core * 0.85); }'
    ].join('\n');

    if (kind === 'field') {
      /* a drifting constellation of sample points with faint links */
      var N = isMobile ? 220 : 420, pos = [], sd = [];
      for (var i = 0; i < N; i++) { pos.push((rnd() - 0.5) * 70, (rnd() - 0.5) * 36, (rnd() - 0.5) * 30); sd.push(rnd()); }
      var g = new THREE.BufferGeometry();
      g.setAttribute('position', new THREE.Float32BufferAttribute(pos, 3));
      g.setAttribute('aSeed', new THREE.Float32BufferAttribute(sd, 1));
      group.add(new THREE.Points(g, new THREE.ShaderMaterial({ uniforms: uniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
        vertexShader: ['attribute float aSeed; uniform float uTime, uP, uSize; varying float vS;',
          'void main(){ vec3 p = position + vec3(sin(uTime*0.4 + aSeed*20.0), cos(uTime*0.35 + aSeed*13.0), sin(uTime*0.3 + aSeed*7.0)) * 0.9; p.y += uP * 6.0 * (aSeed - 0.5);',
          '  vec4 mv = modelViewMatrix * vec4(p, 1.0); gl_Position = projectionMatrix * mv; gl_PointSize = uSize * (0.6 + aSeed) * (18.0 / -mv.z); vS = aSeed; }'].join('\n'),
        fragmentShader: pointFrag })));
      var lv = [];
      for (var a = 0; a < N; a++) { var best = -1, bd = 1e9; for (var b = a + 1; b < N; b++) { var dx = pos[a * 3] - pos[b * 3], dy = pos[a * 3 + 1] - pos[b * 3 + 1], dz = pos[a * 3 + 2] - pos[b * 3 + 2]; var d2 = dx * dx + dy * dy + dz * dz; if (d2 < bd) { bd = d2; best = b; } } if (best >= 0 && bd < 60) lv.push(pos[a * 3], pos[a * 3 + 1], pos[a * 3 + 2], pos[best * 3], pos[best * 3 + 1], pos[best * 3 + 2]); }
      var lg = new THREE.BufferGeometry(); lg.setAttribute('position', new THREE.Float32BufferAttribute(lv, 3));
      group.add(new THREE.LineSegments(lg, new THREE.LineBasicMaterial({ color: accent, transparent: true, opacity: 0.16 })));
      camera.position.set(0, 0, 42);
    } else if (kind === 'grid') {
      /* a wireframe terrain, the site under survey */
      var cols = isMobile ? 60 : 100, rows = isMobile ? 40 : 66, W = 90, D = 60, v = [];
      function P(c, r) { return [(c / (cols - 1) - 0.5) * W, 0, -(r / (rows - 1)) * D + 10]; }
      for (var r = 0; r < rows; r++) for (var c = 0; c < cols - 1; c++) { v.push.apply(v, P(c, r)); v.push.apply(v, P(c + 1, r)); }
      for (var c2 = 0; c2 < cols; c2 += 2) for (var r2 = 0; r2 < rows - 1; r2++) { v.push.apply(v, P(c2, r2)); v.push.apply(v, P(c2, r2 + 1)); }
      var tg = new THREE.BufferGeometry(); tg.setAttribute('position', new THREE.Float32BufferAttribute(v, 3));
      group.add(new THREE.LineSegments(tg, new THREE.ShaderMaterial({ uniforms: uniforms, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
        vertexShader: ['uniform float uTime, uP; varying float vD;', NOISE,
          'void main(){ vec3 p = position; p.y = vnoise(p.xz*0.08 + uTime*0.05)*5.0 + vnoise(p.xz*0.25)*1.5 - 3.0 - uP*4.0;',
          '  vec4 mv = modelViewMatrix * vec4(p, 1.0); gl_Position = projectionMatrix * mv; vD = -mv.z; }'].join('\n'),
        fragmentShader: ['uniform vec3 uA; varying float vD; void main(){ float f = clamp(1.0 - (vD - 20.0) / 70.0, 0.05, 1.0); gl_FragColor = vec4(uA, 0.55 * f); }'].join('\n') })));
      camera.position.set(0, 12, 34);
    } else if (kind === 'sheets') {
      /* a stack of document sheets that fans out as the page scrolls */
      var sheets = [];
      for (var s = 0; s < 9; s++) {
        var e = new THREE.LineSegments(new THREE.EdgesGeometry(new THREE.BoxGeometry(22, 30, 0.2)), new THREE.LineBasicMaterial({ color: accent, transparent: true, opacity: 0.18 + s * 0.05 }));
        e.userData.i = s; group.add(e); sheets.push(e);
        var ln = [];
        for (var k = 0; k < 7; k++) ln.push(-8, 10 - k * 3, 0.12, 8 - (k % 3) * 3, 10 - k * 3, 0.12);
        var lgeo = new THREE.BufferGeometry(); lgeo.setAttribute('position', new THREE.Float32BufferAttribute(ln, 3));
        var lines = new THREE.LineSegments(lgeo, new THREE.LineBasicMaterial({ color: accent2, transparent: true, opacity: 0.06 + s * 0.03 }));
        e.add(lines);
      }
      group.userData.sheets = sheets;
      camera.position.set(0, 0, 48);
    } else {
      /* orbit: instrument rings and ticks */
      function ring(R, seg, dash) { var v2 = []; for (var q = 0; q < seg; q++) { if (dash && (q % dash) >= dash / 2) continue; var t0 = (q / seg) * Math.PI * 2, t1 = ((q + 1) / seg) * Math.PI * 2; v2.push(Math.cos(t0) * R, Math.sin(t0) * R, 0, Math.cos(t1) * R, Math.sin(t1) * R, 0); } var gg = new THREE.BufferGeometry(); gg.setAttribute('position', new THREE.Float32BufferAttribute(v2, 3)); return gg; }
      [[26, 0], [21, 12], [16, 0], [9, 8]].forEach(function (rr, k) { var m = new THREE.LineSegments(ring(rr[0], 220, rr[1]), new THREE.LineBasicMaterial({ color: accent, transparent: true, opacity: 0.14 + k * 0.08 })); m.rotation.x = 0.9; m.position.z = -k * 3; group.add(m); });
      var tv = []; for (var t = 0; t < 90; t++) { var an = (t / 90) * Math.PI * 2, L = t % 10 === 0 ? 1.6 : 0.8; tv.push(Math.cos(an) * 26, Math.sin(an) * 26, 0, Math.cos(an) * (26 - L), Math.sin(an) * (26 - L), 0); }
      var tgg = new THREE.BufferGeometry(); tgg.setAttribute('position', new THREE.Float32BufferAttribute(tv, 3));
      var ticks = new THREE.LineSegments(tgg, new THREE.LineBasicMaterial({ color: accent2, transparent: true, opacity: 0.35 })); ticks.rotation.x = 0.9; group.add(ticks);
      camera.position.set(0, 4, 46);
    }

    function resize() { var w = hero.clientWidth, h = hero.clientHeight; renderer.setSize(w, h, false); camera.aspect = w / h; camera.updateProjectionMatrix(); }
    resize();
    var t0 = performance.now();
    function frame() {
      if (!state.running) return;
      var t = (performance.now() - t0) / 1000;
      state.p += (state.tp - state.p) * 0.08; state.spx += (state.px - state.spx) * 0.06; state.spy += (state.py - state.spy) * 0.06;
      uniforms.uTime.value = t; uniforms.uP.value = state.p;
      var off = isMobile ? 0 : 14;
      group.position.x = off;
      if (kind === 'field') { group.rotation.y = t * 0.03 + state.spx * 0.12; group.rotation.x = state.spy * 0.06 + state.p * 0.25; }
      else if (kind === 'grid') { group.rotation.y = -0.25 + state.spx * 0.08; group.rotation.x = 0.05 + state.spy * 0.04; camera.position.y = 12 + state.p * 6; }
      else if (kind === 'sheets') { group.userData.sheets.forEach(function (sh) { var i = sh.userData.i, f = 0.15 + state.p; sh.position.set((i - 4) * 4.2 * f, Math.sin(t * 0.6 + i) * 0.6 + (i - 4) * 1.2 * state.p, -i * 2.4 + state.p * 6); sh.rotation.y = -0.4 + (i - 4) * 0.08 * f + state.spx * 0.08; sh.rotation.x = state.spy * 0.05; }); }
      else { group.rotation.z = t * 0.04; group.rotation.y = state.spx * 0.12; group.rotation.x = state.spy * 0.06 + state.p * 0.4; }
      camera.lookAt(off, 0, 0);
      renderer.render(scene, camera);
      requestAnimationFrame(frame);
    }
    state.running = true; frame();
    window.addEventListener('resize', resize);
    if (!isMobile) window.addEventListener('pointermove', function (e) { state.px = (e.clientX / window.innerWidth - 0.5) * 2; state.py = -(e.clientY / window.innerHeight - 0.5) * 2; }, { passive: true });
    ScrollTrigger.create({ trigger: hero, start: 'top top', end: 'bottom top', scrub: true, onUpdate: function (st) { state.tp = st.progress; } });
    ScrollTrigger.create({ trigger: hero, start: 'top bottom', end: 'bottom top',
      onEnter: function () { if (!state.running) { state.running = true; frame(); } }, onEnterBack: function () { if (!state.running) { state.running = true; frame(); } },
      onLeave: function () { state.running = false; }, onLeaveBack: function () { state.running = false; } });
    hero.classList.add('pr-scene-on');
    return true;
  }
  if (!mountScene()) { doc.classList.remove('pr-gsap'); return; }

  /* Hero copy rises in. */
  if (hero) {
    var copy = hero.querySelectorAll('.eta-eyebrow, h1, h1 + p, p:not(.eta-eyebrow), .eta-actions, [class*="-hero-panel"], [class*="-hero-card"], aside');
    gsap.from(copy, { opacity: 0, y: 22, duration: 0.9, ease: 'power3.out', stagger: 0.07, delay: 0.1, clearProps: 'transform,opacity' });
  }

  /* ================= REVEALS ================= */
  var targets = [];
  main.querySelectorAll('section, .eta-band').forEach(function (sec) {
    if (hero && (sec === hero || hero.contains(sec))) return;
    var shell = sec.querySelector(':scope > .eta-shell') || sec;
    Array.prototype.forEach.call(shell.children, function (child) {
      var cards = child.querySelectorAll(':scope > article, :scope > li, :scope > a, :scope > div');
      if (cards.length >= 2 && cards.length <= 40 && child.children.length === cards.length) { Array.prototype.forEach.call(cards, function (c) { targets.push(c); }); }
      else targets.push(child);
    });
  });
  targets = targets.filter(function (el) { return el.getBoundingClientRect().height > 0; });
  targets.forEach(function (el) { el.setAttribute('data-pr-reveal', ''); });
  var reveal = function (els) { gsap.to(els, { opacity: 1, y: 0, duration: 0.85, ease: 'power3.out', stagger: 0.06, overwrite: true, clearProps: 'transform' }); };
  ScrollTrigger.batch(targets, { start: 'top 90%', onEnter: reveal, onLeave: reveal });
  var sweep = function () {
    var line = window.innerHeight * 0.9, due = [];
    targets.forEach(function (el) { if (el.getBoundingClientRect().top < line && getComputedStyle(el).opacity !== '1') due.push(el); });
    if (due.length) reveal(due);
  };
  ScrollTrigger.addEventListener('refresh', sweep);
  lenis.on('scroll', function () { if (sweep._t) return; sweep._t = setTimeout(function () { sweep._t = 0; sweep(); }, 120); });
  window.addEventListener('load', function () { sweep(); ScrollTrigger.refresh(); });
  setTimeout(sweep, 400);

  /* ================= CARD TILT ================= */
  if (!isMobile) {
    main.querySelectorAll('[class*="-card"], .eta-card').forEach(function (card) {
      if (hero && hero.contains(card)) return;
      var r0 = card.getBoundingClientRect(); if (r0.width < 160 || r0.width > 720 || r0.height < 80) return;
      card.classList.add('pr-tilt');
      var setRX = gsap.quickTo(card, 'rotationX', { duration: 0.5, ease: 'power3.out' });
      var setRY = gsap.quickTo(card, 'rotationY', { duration: 0.5, ease: 'power3.out' });
      card.addEventListener('pointermove', function (e) { var r = card.getBoundingClientRect(); setRY(((e.clientX - r.left) / r.width - 0.5) * 6); setRX((0.5 - (e.clientY - r.top) / r.height) * 6); });
      card.addEventListener('pointerleave', function () { setRY(0); setRX(0); });
    });
  }
})();
