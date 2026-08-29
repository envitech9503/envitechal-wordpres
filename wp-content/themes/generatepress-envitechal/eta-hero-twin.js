/* Envi Tech AL — Living Environmental Digital Twin hero
 * Three.js scene + a GSAP timeline scrubbed by ScrollTrigger, with Lenis
 * smoothing the scroll it reads from.
 * Progressive enhancement: this module adds html.ets-gsap when safe to run;
 * without it the page shows the static composed fallback hero.
 * 30-08-2026 */

import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.160.1/build/three.module.js';
import { gsap } from 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/+esm';
import { ScrollTrigger } from 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/ScrollTrigger/+esm';
import Lenis from 'https://cdn.jsdelivr.net/npm/lenis@1.3.11/+esm';

(function () {
  'use strict';
  var doc = document.documentElement;
  var sec = document.getElementById('ets-twin');
  if (!sec) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return; // static hero stands
  doc.classList.add('ets-gsap');

  var stage = sec.querySelector('.ets-stage');
  var canvas = sec.querySelector('.ets-gl');
  var isMobile = window.matchMedia('(max-width: 820px), (pointer: coarse)').matches;

  /* ---------------- Lenis + ScrollTrigger ----------------
   * Lenis owns the scroll position and feeds ScrollTrigger; ScrollTrigger
   * scrubs the hero timeline. Touch scrolling stays native. */
  gsap.registerPlugin(ScrollTrigger);

  var lenis = new Lenis({
    duration: 0.9,
    smoothWheel: true,
    syncTouch: false,
    wheelMultiplier: 1,
    easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); }
  });
  window.__etaLenis = lenis;

  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add(function (t) { lenis.raf(t * 1000); });
  gsap.ticker.lagSmoothing(0);

  var heroST = null;

  /* ---------------- palette / lighting arc ---------------- */
  var ARC = [
    { p: 0.00, bg: 0x06090b, fog: 0x06090b, hemi: 0x24413a, gnd: 0x05070a, key: 0x2e5a50, ki: 0.55 },
    { p: 0.30, bg: 0x08201c, fog: 0x08201c, hemi: 0x2c584c, gnd: 0x04100d, key: 0x3d8a74, ki: 0.75 },
    { p: 0.62, bg: 0x0a3a36, fog: 0x0a3a36, hemi: 0x3f8f83, gnd: 0x062420, key: 0x62c4ae, ki: 0.95 },
    { p: 0.86, bg: 0x0e4b46, fog: 0x0e4b46, hemi: 0x63a99c, gnd: 0x0a332e, key: 0x9fdccd, ki: 1.10 },
    { p: 1.00, bg: 0xf4f7f3, fog: 0xf4f7f3, hemi: 0xffffff, gnd: 0xe7efec, key: 0xffffff, ki: 1.20 }
  ];
  var cA = new THREE.Color(), cB = new THREE.Color();
  function arcLerp(p, field, target) {
    var i = 0;
    while (i < ARC.length - 2 && ARC[i + 1].p < p) i++;
    var a = ARC[i], b = ARC[i + 1];
    var t = Math.min(1, Math.max(0, (p - a.p) / (b.p - a.p)));
    t = t * t * (3 - 2 * t);
    cA.setHex(a[field]); cB.setHex(b[field]);
    target.copy(cA).lerp(cB, t);
    return a.ki + (b.ki - a.ki) * t;
  }

  /* ---------------- deterministic pseudo-random ---------------- */
  var seed = 7;
  function rnd() { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; }

  /* ---------------- state ---------------- */
  var state = window.__ets = { p: 0, tp: 0, sp: 0, px: 0, py: 0, spx: 0, spy: 0, running: false, glOK: false };

  /* ================= WEBGL SCENE ================= */
  var renderer, scene, camera, gl = {};
  function initGL() {
    try {
      renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: !isMobile, alpha: false, powerPreference: 'high-performance' });
    } catch (e) { return false; }
    if (!renderer.getContext()) return false;
    var dpr = Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 1.75);
    renderer.setPixelRatio(dpr);
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x06090b);
    scene.fog = new THREE.Fog(0x06090b, 16, 84);
    camera = new THREE.PerspectiveCamera(isMobile ? 50 : 42, 1, 0.1, 200);

    gl.hemi = new THREE.HemisphereLight(0x24413a, 0x05070a, 0.9); scene.add(gl.hemi);
    gl.key = new THREE.DirectionalLight(0x2e5a50, 0.55); gl.key.position.set(18, 24, 14); scene.add(gl.key);
    gl.rim = new THREE.PointLight(0x2f8c74, 14, 60); gl.rim.position.set(-14, 6, -10); scene.add(gl.rim);

    /* ground grid — digital-twin survey plane */
    (function () {
      var size = 240, step = 4, half = size / 2, v = [];
      for (var i = -half; i <= half; i += step) {
        v.push(-half, 0, i, half, 0, i);
        v.push(i, 0, -half, i, 0, half);
      }
      var g = new THREE.BufferGeometry();
      g.setAttribute('position', new THREE.Float32BufferAttribute(v, 3));
      gl.gridMat = new THREE.LineBasicMaterial({ color: 0x1d4a3f, transparent: true, opacity: 0.38 });
      gl.grid = new THREE.LineSegments(g, gl.gridMat);
      gl.grid.position.y = -0.02;
      scene.add(gl.grid);
    })();

    /* facility — procedural industrial digital twin */
    gl.fac = new THREE.Group();
    gl.facMats = []; gl.edgeMats = [];
    function block(geo, x, y, z, ry) {
      var m = new THREE.MeshLambertMaterial({ color: 0x0c1412, transparent: true });
      var mesh = new THREE.Mesh(geo, m);
      mesh.position.set(x, y, z); if (ry) mesh.rotation.y = ry;
      var em = new THREE.LineBasicMaterial({ color: 0x2f8c74, transparent: true, opacity: 0.75 });
      var edges = new THREE.LineSegments(new THREE.EdgesGeometry(geo, 24), em);
      edges.position.copy(mesh.position); edges.rotation.copy(mesh.rotation);
      gl.fac.add(mesh); gl.fac.add(edges);
      gl.facMats.push(m); gl.edgeMats.push(em);
      return mesh;
    }
    block(new THREE.BoxGeometry(11, 4.2, 6.5), -6, 2.1, -1);           // main hall
    block(new THREE.BoxGeometry(5, 2.8, 4.2), -12.2, 1.4, 1.2, 0.12);  // annex
    block(new THREE.CylinderGeometry(0.5, 0.62, 8.5, 14), -5.4, 8.35, -2.4);  // stack 1
    block(new THREE.CylinderGeometry(0.4, 0.5, 6.6, 14), -3.4, 7.4, -2.9);    // stack 2
    block(new THREE.CylinderGeometry(0.44, 0.54, 5.4, 14), -8.1, 6.8, -2.6);  // stack 3
    block(new THREE.CylinderGeometry(1.7, 1.7, 3.4, 20), 0.6, 1.7, 2.6);      // tank A
    block(new THREE.CylinderGeometry(1.35, 1.35, 2.7, 20), 3.4, 1.35, 3.4);   // tank B
    var pipe = new THREE.CylinderGeometry(0.14, 0.14, 7.5, 8);
    block(pipe, -1.8, 2.9, 1.6, 0).rotation.z = Math.PI / 2;
    block(pipe, -1.2, 1.15, 3.1, 0).rotation.z = Math.PI / 2;
    /* pond — water body */
    var pondM = new THREE.MeshLambertMaterial({ color: 0x0e3a3f, transparent: true, opacity: 0.85 });
    var pond = new THREE.Mesh(new THREE.CircleGeometry(2.6, 26), pondM);
    pond.rotation.x = -Math.PI / 2; pond.position.set(6.5, 0.02, 5.2);
    gl.fac.add(pond); gl.facMats.push(pondM);
    /* monitoring mast */
    block(new THREE.CylinderGeometry(0.05, 0.05, 5.6, 6), 4.6, 2.8, -2.2);
    scene.add(gl.fac);

    /* glow sprite texture (generated) */
    var cv = document.createElement('canvas'); cv.width = cv.height = 64;
    var cx = cv.getContext('2d');
    var grad = cx.createRadialGradient(32, 32, 2, 32, 32, 30);
    grad.addColorStop(0, 'rgba(150,255,225,1)'); grad.addColorStop(0.35, 'rgba(90,220,190,.55)'); grad.addColorStop(1, 'rgba(40,140,120,0)');
    cx.fillStyle = grad; cx.fillRect(0, 0, 64, 64);
    var glowTex = new THREE.CanvasTexture(cv);

    /* monitoring nodes */
    gl.nodePos = [
      new THREE.Vector3(-5.4, 12.9, -2.4),  // EMISSIONS (stack top)
      new THREE.Vector3(6.5, 0.5, 5.2),     // WATER (pond)
      new THREE.Vector3(4.6, 6.0, -2.2),    // AIR (mast top)
      new THREE.Vector3(-13.5, 1.6, 4.6),   // NOISE (boundary)
      new THREE.Vector3(1.8, 0.7, 6.8),     // WASTE
      new THREE.Vector3(-9.5, 1.4, 2.9)     // WORKPLACE
    ];
    gl.nodes = new THREE.Group();
    gl.nodeSprites = [];
    gl.nodePos.forEach(function (pnt) {
      var grp = new THREE.Group();
      var sm = new THREE.SpriteMaterial({ map: glowTex, color: 0x9df2dd, transparent: true, opacity: 0.95, depthWrite: false, blending: THREE.AdditiveBlending });
      var sp = new THREE.Sprite(sm); sp.scale.set(1.3, 1.3, 1);
      grp.add(sp);
      var lg = new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(0, 0, 0), new THREE.Vector3(0, 1.35, 0)]);
      var lm = new THREE.LineBasicMaterial({ color: 0x6fd8bf, transparent: true, opacity: 0.8 });
      grp.add(new THREE.Line(lg, lm));
      grp.position.copy(pnt);
      grp.scale.set(0.001, 0.001, 0.001);
      gl.nodes.add(grp);
      gl.nodeSprites.push({ grp: grp, sm: sm, lm: lm });
    });
    scene.add(gl.nodes);

    /* emission plume — restrained drift from stack 1 */
    (function () {
      var n = isMobile ? 90 : 200, pos = new Float32Array(n * 3), off = new Float32Array(n);
      for (var i = 0; i < n; i++) { pos[i * 3] = 0; pos[i * 3 + 1] = 0; pos[i * 3 + 2] = 0; off[i] = rnd(); }
      var g = new THREE.BufferGeometry();
      g.setAttribute('position', new THREE.BufferAttribute(pos, 3));
      g.setAttribute('aOff', new THREE.BufferAttribute(off, 1));
      gl.plumeMat = new THREE.ShaderMaterial({
        transparent: true, depthWrite: false, blending: THREE.NormalBlending,
        uniforms: { uT: { value: 0 }, uO: { value: 0 } },
        vertexShader:
          'attribute float aOff; uniform float uT; varying float vA;\n' +
          'void main(){ float t = fract(aOff + uT*0.05); vec3 p = position;\n' +
          ' p.y = t*7.0; p.x = sin(aOff*40.0)*0.3 + t*t*2.2; p.z = cos(aOff*37.0)*0.3 + t*0.5;\n' +
          ' vA = (1.0-t)*0.35; vec4 mv = modelViewMatrix*vec4(p,1.0);\n' +
          ' gl_PointSize = (2.0+t*7.0)*(10.0/-mv.z); gl_Position = projectionMatrix*mv; }',
        fragmentShader:
          'varying float vA; uniform float uO;\n' +
          'void main(){ vec2 c = gl_PointCoord-0.5; float d = smoothstep(0.5,0.1,length(c));\n' +
          ' gl_FragColor = vec4(0.55,0.66,0.64, vA*d*uO); }'
      });
      gl.plume = new THREE.Points(g, gl.plumeMat);
      gl.plume.position.set(-5.4, 12.6, -2.4);
      scene.add(gl.plume);
    })();

    /* measurement particles: environment (A) -> analytical lattice (B) -> report (C) */
    (function () {
      var N = isMobile ? 1000 : 2600;
      var aA = new Float32Array(N * 3), aB = new Float32Array(N * 3), aC = new Float32Array(N * 3);
      var aOff = new Float32Array(N), aSize = new Float32Array(N), aArc = new Float32Array(N);
      var DX = 7.6, DY = 2.0; // data zone centre x/base y
      for (var i = 0; i < N; i++) {
        /* A: clustered readings around a node */
        var nd = gl.nodePos[i % gl.nodePos.length];
        var r = 0.25 + rnd() * 0.95, th = rnd() * 6.2832, ph = rnd() * 3.1416;
        aA[i * 3] = nd.x + r * Math.sin(ph) * Math.cos(th);
        aA[i * 3 + 1] = Math.max(0.15, nd.y + r * Math.cos(ph) * 0.7);
        aA[i * 3 + 2] = nd.z + r * Math.sin(ph) * Math.sin(th);
        /* B: analytical composition — bars, curve, calibration ring */
        var kind = rnd(), bx, by, bz;
        if (kind < 0.55) {            // bar columns
          var col = Math.floor(rnd() * 10);
          var h = 1.0 + ((col * 2654435761 % 97) / 97) * 4.2;
          bx = DX - 4.4 + col * 0.98 + (rnd() - 0.5) * 0.22;
          by = DY + rnd() * h;
          bz = (rnd() - 0.5) * 0.35;
        } else if (kind < 0.78) {     // analytical curve
          var t = rnd();
          bx = DX - 4.6 + t * 9.2;
          by = DY + 3.1 + Math.sin(t * 5.4) * 1.15 + (rnd() - 0.5) * 0.12;
          bz = (rnd() - 0.5) * 0.25;
        } else if (kind < 0.92) {     // calibration ring
          var a2 = rnd() * 6.2832;
          bx = DX + Math.cos(a2) * 2.1;
          by = DY + 5.6 + Math.sin(a2) * 2.1;
          bz = (rnd() - 0.5) * 0.2;
        } else {                      // baseline ticks
          bx = DX - 4.6 + rnd() * 9.2; by = DY - 0.15 + rnd() * 0.1; bz = (rnd() - 0.5) * 0.3;
        }
        aB[i * 3] = bx; aB[i * 3 + 1] = by; aB[i * 3 + 2] = bz;
        /* C: compliance report — five ordered lines */
        var ln = i % 5;
        aC[i * 3] = DX - 2.6 + rnd() * 5.2;
        aC[i * 3 + 1] = 5.4 - ln * 0.72 + (rnd() - 0.5) * 0.06;
        aC[i * 3 + 2] = (rnd() - 0.5) * 0.1;
        aOff[i] = rnd(); aSize[i] = 2.2 + rnd() * 3.0; aArc[i] = 1.5 + rnd() * 3.5;
      }
      var g = new THREE.BufferGeometry();
      g.setAttribute('position', new THREE.BufferAttribute(aA.slice(), 3));
      g.setAttribute('aA', new THREE.BufferAttribute(aA, 3));
      g.setAttribute('aB', new THREE.BufferAttribute(aB, 3));
      g.setAttribute('aC', new THREE.BufferAttribute(aC, 3));
      g.setAttribute('aOff', new THREE.BufferAttribute(aOff, 1));
      g.setAttribute('aSize', new THREE.BufferAttribute(aSize, 1));
      g.setAttribute('aArc', new THREE.BufferAttribute(aArc, 1));
      gl.ptsMat = new THREE.ShaderMaterial({
        transparent: true, depthWrite: false, blending: THREE.AdditiveBlending,
        uniforms: {
          uP1: { value: 0 }, uP2: { value: 0 }, uT: { value: 0 }, uO: { value: 0 }, uPx: { value: dpr },
          uC1: { value: new THREE.Color(0x2f8c74) }, uC2: { value: new THREE.Color(0x7de8cd) }
        },
        vertexShader:
          'attribute vec3 aA; attribute vec3 aB; attribute vec3 aC;\n' +
          'attribute float aOff; attribute float aSize; attribute float aArc;\n' +
          'uniform float uP1; uniform float uP2; uniform float uT; uniform float uPx;\n' +
          'varying float vMix; varying float vTw;\n' +
          'float ss(float a, float b, float x){ return smoothstep(a,b,x); }\n' +
          'void main(){\n' +
          ' float t1 = ss(aOff*0.35, 0.65+aOff*0.35, uP1);\n' +
          ' float t2 = ss(aOff*0.3, 0.7+aOff*0.3, uP2);\n' +
          ' vec3 p = mix(aA, aB, t1);\n' +
          ' p.y += sin(t1*3.14159)*aArc*0.55;\n' +
          ' p = mix(p, aC, t2);\n' +
          ' float wob = (1.0-t1)*0.12;\n' +
          ' p.x += sin(uT*0.7+aOff*40.0)*wob; p.y += cos(uT*0.6+aOff*33.0)*wob;\n' +
          ' vMix = t1; vTw = 0.75+0.25*sin(uT*1.4+aOff*50.0);\n' +
          ' vec4 mv = modelViewMatrix*vec4(p,1.0);\n' +
          ' gl_PointSize = aSize*uPx*(11.0/-mv.z);\n' +
          ' gl_Position = projectionMatrix*mv; }',
        fragmentShader:
          'uniform vec3 uC1; uniform vec3 uC2; uniform float uO;\n' +
          'varying float vMix; varying float vTw;\n' +
          'void main(){ vec2 c = gl_PointCoord-0.5; float d = smoothstep(0.5,0.12,length(c));\n' +
          ' vec3 col = mix(uC1, uC2, vMix);\n' +
          ' gl_FragColor = vec4(col, d*uO*vTw); }'
      });
      gl.pts = new THREE.Points(g, gl.ptsMat);
      gl.pts.frustumCulled = false;
      scene.add(gl.pts);
      gl.ptsGeom = g; gl.ptsN = N;
    })();

    /* analytical axes + frame (scene 5) */
    (function () {
      gl.axMat = new THREE.LineBasicMaterial({ color: 0x8fe6cf, transparent: true, opacity: 0 });
      var DX = 7.6, DY = 2.0;
      var axg = new THREE.BufferGeometry().setFromPoints([
        new THREE.Vector3(DX - 4.8, DY + 6.2, 0), new THREE.Vector3(DX - 4.8, DY - 0.2, 0),
        new THREE.Vector3(DX - 4.8, DY - 0.2, 0), new THREE.Vector3(DX + 4.8, DY - 0.2, 0)
      ]);
      gl.axes = new THREE.LineSegments(axg, gl.axMat);
      scene.add(gl.axes);
      var ringPts = [];
      for (var i = 0; i <= 60; i++) { var a = i / 60 * 6.2832; ringPts.push(new THREE.Vector3(DX + Math.cos(a) * 2.35, DY + 5.6 + Math.sin(a) * 2.35, 0)); }
      gl.ring = new THREE.Line(new THREE.BufferGeometry().setFromPoints(ringPts), gl.axMat);
      scene.add(gl.ring);
    })();

    state.glOK = true;
    return true;
  }

  /* camera keyframes */
  var CAM = [
    { p: 0.00, pos: [15, 7.8, 31], look: [-2.5, 3.6, 0] },
    { p: 0.16, pos: [12.5, 6.6, 26], look: [-3, 3.4, 0] },
    { p: 0.32, pos: [9.5, 5.6, 21.5], look: [-3.5, 3.2, 0] },
    { p: 0.50, pos: [8.2, 5.0, 18], look: [1.5, 3.4, 1] },
    { p: 0.68, pos: [7.6, 4.8, 15.5], look: [6.2, 4.0, 0.5] },
    { p: 0.86, pos: [7.2, 4.5, 13.8], look: [7.4, 4.2, 0] },
    { p: 1.00, pos: [7.2, 5.2, 13.2], look: [7.4, 4.6, 0] }
  ];
  var vP = new THREE.Vector3(), vL = new THREE.Vector3(), vPa = new THREE.Vector3(), vPb = new THREE.Vector3(), vLa = new THREE.Vector3(), vLb = new THREE.Vector3();
  function camAt(p) {
    var i = 0;
    while (i < CAM.length - 2 && CAM[i + 1].p < p) i++;
    var a = CAM[i], b = CAM[i + 1];
    var t = Math.min(1, Math.max(0, (p - a.p) / (b.p - a.p)));
    t = t * t * (3 - 2 * t);
    vPa.fromArray(a.pos); vPb.fromArray(b.pos); vP.copy(vPa).lerp(vPb, t);
    vLa.fromArray(a.look); vLb.fromArray(b.look); vL.copy(vLa).lerp(vLb, t);
  }

  function rangeN(p, a, b) { return Math.min(1, Math.max(0, (p - a) / (b - a))); }

  /* per-frame scene update from smoothed progress */
  var tmpC = new THREE.Color();
  function updateGL(time) {
    var p = state.sp;
    camAt(p);
    var par = isMobile ? 0 : 1;
    camera.position.set(vP.x + state.spx * 0.34 * par, vP.y + state.spy * 0.2 * par, vP.z);
    camera.lookAt(vL);

    var ki = arcLerp(p, 'bg', tmpC);
    scene.background.copy(tmpC); scene.fog.color.copy(tmpC);
    arcLerp(p, 'hemi', gl.hemi.color);
    arcLerp(p, 'gnd', gl.hemi.groundColor);
    arcLerp(p, 'key', gl.key.color);
    gl.key.intensity = ki * 0.7; gl.hemi.intensity = 0.7 + ki * 0.5;
    gl.rim.intensity = 14 * (1 - rangeN(p, 0.86, 0.98));

    /* grid + facility presence */
    var fade = 1 - rangeN(p, 0.62, 0.84);           // facility recedes as data leads
    var gone = 1 - rangeN(p, 0.86, 0.97);           // everything resolves
    gl.gridMat.opacity = 0.38 * gone;
    var fo = (0.35 + 0.65 * fade) * gone;
    gl.facMats.forEach(function (m) { m.opacity = fo; });
    gl.edgeMats.forEach(function (m) { m.opacity = 0.75 * fo; });

    /* plume */
    gl.plumeMat.uniforms.uT.value = time;
    gl.plumeMat.uniforms.uO.value = rangeN(p, 0.14, 0.2) * (1 - rangeN(p, 0.5, 0.6));

    /* nodes pop 0.29-0.4, resolve out late */
    gl.nodeSprites.forEach(function (n, i) {
      var s = rangeN(p, 0.28 + i * 0.018, 0.35 + i * 0.018);
      s = s * s * (3 - 2 * s);
      var out = 1 - rangeN(p, 0.6, 0.72);
      var sc = Math.max(0.001, s * (0.7 + 0.3 * out));
      n.grp.scale.set(sc, sc, sc);
      n.sm.opacity = 0.8 * s * out * gone;
      n.lm.opacity = 0.8 * s * out * gone;
    });

    /* measurement particles */
    gl.ptsMat.uniforms.uT.value = time;
    gl.ptsMat.uniforms.uO.value = rangeN(p, 0.3, 0.4) * gone;
    gl.ptsMat.uniforms.uP1.value = rangeN(p, 0.44, 0.62);
    gl.ptsMat.uniforms.uP2.value = rangeN(p, 0.76, 0.86);
    gl.axMat.opacity = rangeN(p, 0.6, 0.68) * (1 - rangeN(p, 0.78, 0.86));

    renderer.render(scene, camera);
  }

  /* ---------------- render loop (only while hero near viewport) ---------------- */
  var rafId = null, frames = 0, frameT0 = 0, slowChecked = false;
  function loop(ts) {
    rafId = requestAnimationFrame(loop);
    state.n = (state.n || 0) + 1;
    /* state.p is written by the ScrollTrigger onUpdate below; the scene keeps
     * its own slower easing so the 3D read stays calm under fast scrolling. */
    state.sp += (state.p - state.sp) * 0.075;
    state.spx += (state.px - state.spx) * 0.06;
    state.spy += (state.py - state.spy) * 0.06;
    if (state.glOK) {
      updateGL(ts * 0.001);
      if (!slowChecked) {
        if (!frameT0) { frameT0 = ts; frames = 0; }
        frames++;
        if (ts - frameT0 > 3000) {
          slowChecked = true;
          var fps = frames / ((ts - frameT0) / 1000);
          if (fps < 38) { renderer.setPixelRatio(1); gl.ptsGeom.setDrawRange(0, Math.floor(gl.ptsN * 0.55)); }
        }
      }
      projectTags();
    }
  }
  function startLoop() { if (rafId === null) { frameT0 = 0; rafId = requestAnimationFrame(loop); } }
  function stopLoop() { if (rafId !== null) { cancelAnimationFrame(rafId); rafId = null; } }

  /* ---------------- HTML annotation projection ---------------- */
  var tags = Array.prototype.slice.call(sec.querySelectorAll('.ets-tag'));
  var vT = new THREE.Vector3();
  function projectTags() {
    if (!tags.length) return;
    var w = stage.clientWidth, h = stage.clientHeight;
    tags.forEach(function (tag, i) {
      var np = gl.nodePos[i]; if (!np) return;
      vT.copy(np); vT.y += 1.6;
      vT.project(camera);
      if (vT.z > 1) { tag.style.opacity = 0; return; }
      var x = (vT.x * 0.5 + 0.5) * w, y = (-vT.y * 0.5 + 0.5) * h;
      x = Math.min(Math.max(x, 70), w - 90);
      var flip = x > w * 0.58;
      tag.classList.toggle('ets-tag-flip', flip);
      tag.style.transform = 'translate(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px)' + (flip ? ' translateX(-100%)' : '');
    });
  }

  /* ---------------- pointer parallax (desktop only) ---------------- */
  if (!isMobile) {
    window.addEventListener('pointermove', function (e) {
      state.px = (e.clientX / window.innerWidth - 0.5) * 2;
      state.py = (e.clientY / window.innerHeight - 0.5) * 2;
    }, { passive: true });
  }

  /* ================= SCROLL CHOREOGRAPHY (HTML) ================= */
  function q(s) { return sec.querySelector(s); }
  function qa(s) { return Array.prototype.slice.call(sec.querySelectorAll(s)); }

  var tl = gsap.timeline({ defaults: { ease: 'power2.out' }, paused: true });
  state.tl = tl;

  /* skip affordance — jump past the intro in one step */
  var skipBtn = sec.querySelector('[data-ets-skip]');
  if (skipBtn) {
    skipBtn.addEventListener('click', function () {
      if (heroST) {
        lenis.scrollTo(heroST.end + 2, { immediate: true, force: true });
        ScrollTrigger.update();
      }
      state.sp = 1;                 // land the scene resolved, no catch-up easing
      var next = sec.nextElementSibling;
      while (next && (next.tagName === 'SCRIPT' || next.tagName === 'STYLE')) next = next.nextElementSibling;
      if (next) {
        if (!next.hasAttribute('tabindex')) next.setAttribute('tabindex', '-1');
        next.focus({ preventScroll: true });
      }
    });
  }

  /* Scene 1 exit — editorial rise, no large travel */
  tl.fromTo('.ets-l1', { opacity: 1, y: 0 }, { opacity: 0, y: -26, duration: 0.05, ease: 'power2.in', immediateRender: false }, 0.085);
  tl.fromTo('.ets-cue', { opacity: 1 }, { opacity: 0, duration: 0.025, immediateRender: false }, 0.04);
  tl.fromTo('.ets-skip', { opacity: 1 }, { opacity: 0, duration: 0.04, ease: 'none', immediateRender: false }, 0.9);
  tl.set('.ets-skip', { pointerEvents: 'none' }, 0.94);
  tl.set('.ets-l1', { pointerEvents: 'none' }, 0.13);

  /* editorial notes: masked line reveals in/out */
  var NOTES = [
    { el: '.ets-n1', tin: 0.145, tout: 0.26 },
    { el: '.ets-n2', tin: 0.272, tout: 0.415 },
    { el: '.ets-n3', tin: 0.428, tout: 0.585 },
    { el: '.ets-n4', tin: 0.598, tout: 0.75 }
  ];
  NOTES.forEach(function (n) {
    var lines = qa(n.el + ' .ets-line');
    tl.fromTo(n.el, { opacity: 0 }, { opacity: 1, duration: 0.02, ease: 'none', immediateRender: true }, n.tin);
    tl.fromTo(lines, { yPercent: 112 }, { yPercent: 0, duration: 0.045, stagger: 0.012, ease: 'power3.out', immediateRender: true }, n.tin);
    tl.fromTo(n.el, { opacity: 1 }, { opacity: 0, duration: 0.035, ease: 'power2.in', immediateRender: false }, n.tout);
    tl.fromTo(lines, { }, { yPercent: -40, duration: 0.035, stagger: 0.008, ease: 'power2.in', immediateRender: false }, n.tout);
  });

  /* monitoring tags */
  tl.fromTo('.ets-tag', { opacity: 0 }, { opacity: 1, duration: 0.035, stagger: 0.021, immediateRender: true }, 0.298);
  tl.to('.ets-tag', { opacity: 0, duration: 0.04, stagger: 0.008 }, 0.54);

  /* compliance words */
  var words = qa('.ets-word .ets-line');
  tl.fromTo(words, { yPercent: 112 }, { yPercent: 0, duration: 0.05, stagger: 0.02, ease: 'power3.out', immediateRender: true }, 0.775);
  tl.fromTo('.ets-wmicro', { opacity: 0 }, { opacity: 1, duration: 0.04, immediateRender: true }, 0.83);
  tl.fromTo('.ets-words', { opacity: 1 }, { opacity: 0, y: -18, duration: 0.035, ease: 'power2.in', immediateRender: false }, 0.875);

  /* resolution — light rises, canvas resolves, final statement */
  tl.fromTo(canvas, { opacity: 1 }, { opacity: 0, duration: 0.09, ease: 'none', immediateRender: false }, 0.9);
  tl.fromTo('.ets-atmos', { backgroundColor: '#06090b' }, { backgroundColor: '#f4f7f3', duration: 0.1, ease: 'none', immediateRender: false }, 0.875);
  var fLines = qa('.ets-final .ets-line');
  tl.fromTo(fLines, { yPercent: 112 }, { yPercent: 0, duration: 0.05, stagger: 0.016, ease: 'power3.out', immediateRender: true }, 0.915);
  tl.fromTo('.ets-fcta', { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: 0.045, immediateRender: true }, 0.955);

  /* ---------------- scrub the timeline from scroll position ----------------
   * start/end reproduce the sticky stage's travel: the stage is pinned by CSS
   * while the section scrolls past, so progress runs from the section meeting
   * the viewport top to its bottom meeting the viewport bottom. */
  heroST = ScrollTrigger.create({
    trigger: sec,
    start: 'top top',
    end: 'bottom bottom',
    scrub: 0.25,
    animation: tl,
    invalidateOnRefresh: true,
    onUpdate: function (self) { state.p = self.progress; }
  });
  state.st = heroST;

  /* ================= boot GL ================= */
  if (!initGL()) {
    doc.classList.add('ets-nogl');
    canvas.style.display = 'none';
  } else {
    function sizeGL() {
      var w = stage.clientWidth, h = stage.clientHeight;
      renderer.setSize(w, h, false);
      camera.aspect = w / h; camera.updateProjectionMatrix();
    }
    sizeGL();
    var rto = null;
    window.addEventListener('resize', function () { clearTimeout(rto); rto = setTimeout(function () { sizeGL(); ScrollTrigger.refresh(); }, 150); }, { passive: true });
    /* render only when hero is on screen */
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (en) {
        en.forEach(function (e) { e.isIntersecting ? startLoop() : stopLoop(); });
      }, { rootMargin: '120px' }).observe(sec);
    } else startLoop();
    document.addEventListener('visibilitychange', function () { document.hidden ? stopLoop() : startLoop(); });
    startLoop();
  }
})();
