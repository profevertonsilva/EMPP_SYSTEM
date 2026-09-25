/**
 * Public site: draws the sample record's synthetic micrograph and plays the
 * record's "study run" once when it scrolls into view.
 */
(function () {
  'use strict';

  // Seeded PRNG: the same micrograph on every visit
  function rng(seed) {
    return function () {
      seed |= 0;
      seed = (seed + 0x6d2b79f5) | 0;
      var t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
      t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
      return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
  }

  /*
   * A SEM-like mat of electrospun fibres: long, gently curving strands in
   * depth layers (far ones darker and thinner), each shaded as a cylinder,
   * a few beads, then detector grain.
   */
  function drawMicrograph(canvas) {
    var rect = canvas.getBoundingClientRect();
    if (!rect.width) return;
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var W = Math.round(rect.width * dpr);
    var H = Math.round(rect.height * dpr);
    canvas.width = W;
    canvas.height = H;
    var ctx = canvas.getContext('2d', { willReadFrequently: true });
    var rand = rng(20260924);
    var unit = W / 480;

    ctx.fillStyle = '#16181c';
    ctx.fillRect(0, 0, W, H);

    function strand(depth) {
      // Enter from one edge, wander across with slowly changing heading
      var x, y, a;
      if (rand() < 0.5) {
        x = -20 * unit; y = rand() * H; a = (rand() - 0.5) * 1.4;
      } else {
        x = rand() * W; y = -20 * unit; a = Math.PI / 2 + (rand() - 0.5) * 1.4;
      }
      if (rand() < 0.5) { a += Math.PI; x = W - x; y = H - y; }
      var pts = [[x, y]];
      var turn = (rand() - 0.5) * 0.02;
      var step = 6 * unit;
      for (var i = 0; i < 400; i++) {
        turn += (rand() - 0.5) * 0.012;
        turn *= 0.96;
        a += turn;
        x += Math.cos(a) * step;
        y += Math.sin(a) * step;
        pts.push([x, y]);
        if (x < -40 * unit || x > W + 40 * unit || y < -40 * unit || y > H + 40 * unit) break;
      }
      var w = (1.4 + rand() * 2.6 + depth * 2.2) * unit;
      var grey = 70 + depth * 110 + rand() * 25;

      function path() {
        ctx.beginPath();
        ctx.moveTo(pts[0][0], pts[0][1]);
        for (var j = 1; j < pts.length; j++) ctx.lineTo(pts[j][0], pts[j][1]);
      }
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      // Cast shadow on the fibres below
      path();
      ctx.strokeStyle = 'rgba(0,0,0,' + (0.35 + depth * 0.3) + ')';
      ctx.lineWidth = w + 3 * unit;
      ctx.stroke();
      // Body
      path();
      ctx.strokeStyle = 'rgb(' + (grey | 0) + ',' + (grey | 0) + ',' + ((grey + 4) | 0) + ')';
      ctx.lineWidth = w;
      ctx.stroke();
      // Edge brightening down the axis, as secondary electrons do on a cylinder
      path();
      var hi = Math.min(250, grey + 60);
      ctx.strokeStyle = 'rgba(' + hi + ',' + hi + ',' + (hi + 3) + ',0.55)';
      ctx.lineWidth = Math.max(0.8 * unit, w * 0.35);
      ctx.stroke();

      // The occasional bead, a common electrospinning defect
      if (depth > 0.4 && rand() < 0.12) {
        var p = pts[(pts.length * (0.2 + rand() * 0.6)) | 0];
        var r = w * (1.6 + rand());
        var g = ctx.createRadialGradient(p[0] - r * 0.3, p[1] - r * 0.3, r * 0.1, p[0], p[1], r);
        g.addColorStop(0, 'rgb(' + hi + ',' + hi + ',' + hi + ')');
        g.addColorStop(1, 'rgb(' + (grey | 0) + ',' + (grey | 0) + ',' + (grey | 0) + ')');
        ctx.beginPath();
        ctx.ellipse(p[0], p[1], r * 1.4, r, rand() * Math.PI, 0, Math.PI * 2);
        ctx.fillStyle = g;
        ctx.fill();
      }
    }

    var layers = [[0, 55], [0.35, 45], [0.7, 32], [1, 18]];
    layers.forEach(function (l) {
      for (var k = 0; k < l[1]; k++) strand(l[0] + rand() * 0.2);
    });

    // Detector grain
    var img = ctx.getImageData(0, 0, W, H);
    var d = img.data;
    for (var i = 0; i < d.length; i += 4) {
      var n = (rand() - 0.5) * 26;
      d[i] += n; d[i + 1] += n; d[i + 2] += n;
    }
    ctx.putImageData(img, 0, 0);
  }

  var canvas = document.getElementById('micrograph');
  if (canvas && canvas.getContext) {
    drawMicrograph(canvas);
    // Redraw only when the width really changes: phones fire resize when the
    // address bar shows or hides, and the grain pass is not free
    var drawnWidth = canvas.getBoundingClientRect().width;
    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        var w = canvas.getBoundingClientRect().width;
        if (Math.abs(w - drawnWidth) > 1) {
          drawnWidth = w;
          drawMicrograph(canvas);
        }
      }, 150);
    });
  }

  // The study run plays once per part, as each part comes on screen. The head
  // script holds their start state until then.
  //  - wide: steps 1-3 sit side by side and run in sequence from the sheet's arrival
  //  - stacked (phones): each step runs when it scrolls into view
  //  - the filtration row always has its own trigger
  var rec = document.getElementById('rec');
  if (rec && document.documentElement.classList.contains('js-run')) {
    window.emppSiteReady = true;
    var stacked = window.matchMedia('(max-width: 720px)').matches;
    var steps = rec.querySelectorAll('.rec-data .rec-block');

    function once(el, onEnter) {
      var io = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
          onEnter();
          io.disconnect();
        }
      }, { threshold: 0, rootMargin: '0px 0px -15% 0px' });
      io.observe(el);
    }

    if (stacked) rec.classList.add('is-staged');
    once(rec, function () {
      rec.classList.add('is-running');
      if (!stacked) {
        Array.prototype.forEach.call(steps, function (s) { s.classList.add('is-running'); });
      }
    });
    if (stacked) {
      Array.prototype.forEach.call(steps, function (s) {
        once(s, function () { s.classList.add('is-running'); });
      });
    }
    var filt = rec.querySelector('.rec-filtration');
    if (filt) once(filt, function () { filt.classList.add('is-running'); });
  }
})();
