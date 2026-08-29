/* Envi Tech AL — homepage body v7: restrained editorial motion.
 * No dependencies. Reveals, service observatory, sector matrix,
 * compliance-journey trace, maritime wave drift. 28-08-2026 */
(function () {
  'use strict';
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var doc = document.documentElement;
  doc.classList.add('etb-js');

  /* ---- reveals: reveal once, pre-trigger before entry, failsafe below ---- */
  var pending = Array.prototype.slice.call(document.querySelectorAll('.etb-r'));
  function unpend(el) {
    el.classList.add('in');
    var k = pending.indexOf(el);
    if (k > -1) pending.splice(k, 1);
  }
  if ('IntersectionObserver' in window && pending.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { io.unobserve(e.target); unpend(e.target); } });
    }, { threshold: 0, rootMargin: '0px 0px 14% 0px' });
    pending.slice().forEach(function (el) { io.observe(el); });
  } else {
    pending.slice().forEach(unpend);
  }

  /* ---- service observatory ---- */
  var obsLinks = Array.prototype.slice.call(document.querySelectorAll('.etb-obs-link'));
  var obsPlanes = Array.prototype.slice.call(document.querySelectorAll('.etb-obs-plane'));
  function obsSet(i) {
    obsLinks.forEach(function (l, k) { l.classList.toggle('is-on', k === i); });
    obsPlanes.forEach(function (p, k) { p.classList.toggle('is-on', k === i); });
  }
  obsLinks.forEach(function (l, i) {
    l.addEventListener('mouseenter', function () { obsSet(i); }, { passive: true });
    l.addEventListener('focus', function () { obsSet(i); }, { passive: true });
  });

  /* ---- sector matrix ---- */
  var mRows = Array.prototype.slice.call(document.querySelectorAll('.etb-matrix-row'));
  var mName = document.querySelector('[data-matrix-name]');
  var mSvc = document.querySelector('[data-matrix-svc]');
  var wide = window.matchMedia('(min-width: 1021px)');
  mRows.forEach(function (row) {
    function on() {
      if (!wide.matches || !mName) return;
      mRows.forEach(function (r) { r.classList.toggle('is-on', r === row); });
      mName.textContent = row.querySelector('.etb-matrix-name').textContent;
      mSvc.textContent = row.querySelector('.etb-matrix-svc').textContent;
    }
    row.addEventListener('mouseenter', on, { passive: true });
    row.addEventListener('focus', on, { passive: true });
  });

  /* ---- journey trace + maritime drift (one rAF scroll consumer) ---- */
  var steps = document.querySelector('.etb-steps');
  var waves = Array.prototype.slice.call(document.querySelectorAll('.etb-wave'));
  var maritime = document.querySelector('.etb-maritime');
  var ticking = false;
  function frame() {
    ticking = false;
    var vh = window.innerHeight;
    /* failsafe: anything at or above the fold reveals no matter what */
    for (var pi = pending.length - 1; pi >= 0; pi--) {
      if (pending[pi].getBoundingClientRect().top < vh * 0.99) {
        pending[pi].classList.add('in');
        pending.splice(pi, 1);
      }
    }
    if (steps) {
      var r = steps.getBoundingClientRect();
      var p = (vh * 0.78 - r.top) / r.height;
      p = Math.min(1, Math.max(0, p));
      steps.style.setProperty('--tp', p.toFixed(4));
    }
    if (maritime && waves.length) {
      var mr = maritime.getBoundingClientRect();
      if (mr.bottom > 0 && mr.top < vh) {
        var mp = (vh - mr.top) / (vh + mr.height);
        waves.forEach(function (w, i) {
          var f = (i + 1) * 9;
          w.style.transform = 'translateX(' + ((mp - 0.5) * f * 2).toFixed(2) + 'px) translateY(' + ((0.5 - mp) * f).toFixed(2) + 'px)';
        });
      }
    }
  }
  function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(frame); } }
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll, { passive: true });
  frame();
  setTimeout(frame, 700);
  setTimeout(frame, 2000);
  window.addEventListener('load', frame, { passive: true });
})();
