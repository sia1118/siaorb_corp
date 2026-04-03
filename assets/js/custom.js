/**
 * SIAORB Corporate - カスタム JavaScript
 */
(function () {
  'use strict';

  /* ============================================================
     スムーススクロール
     ============================================================ */
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var id = this.getAttribute('href');
        if (id === '#') return;
        var target = document.querySelector(id);
        if (!target) return;
        e.preventDefault();
        var header = document.querySelector('#header, .l-header, header');
        var offset = header ? header.offsetHeight : 0;
        window.scrollTo({
          top:      target.getBoundingClientRect().top + window.pageYOffset - offset,
          behavior: 'smooth',
        });
      });
    });
  });

  /* ============================================================
     スクロールアニメーション
     ============================================================ */
  document.addEventListener('DOMContentLoaded', function () {
    if (!('IntersectionObserver' in window)) return;
    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('is-visible'); obs.unobserve(e.target); }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll(
      '.siaorb-news__item, .siaorb-services__row, .siaorb-philosophy__vision, .siaorb-philosophy__greeting'
    ).forEach(function (el) { el.classList.add('js-animate'); obs.observe(el); });
  });

  /* ============================================================
     セクション背景色 + Services/Philosophy カラーテーマ切り替え
     ============================================================ */
  document.addEventListener('DOMContentLoaded', function () {
    // 背景色マップ（philosophy は company に統合）
    var BG_MAP = {
      'fv':       '#080e1a',
      'news':     '#f0f3f8',
      'services': '#0d1b2a',
      'company':  '#0d1b2a',  // デフォルトはネイビー、スクロールで白に
      'contact':  '#ffffff',
    };

    var sections = Object.keys(BG_MAP).map(function (id) {
      return { el: document.getElementById(id), color: BG_MAP[id] };
    }).filter(function (s) { return s.el; });

    if (!sections.length) return;
    document.body.style.backgroundColor = BG_MAP['fv'];

    // Services: スクロールでネイビー（is-dark）
    // Company:  デフォルトネイビー、スクロールで白（is-light）
    var bgObs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        var id = entry.target.id;
        if (entry.isIntersecting) {
          if (BG_MAP[id]) document.body.style.backgroundColor = BG_MAP[id];
          if (id === 'services') entry.target.classList.add('is-dark');
          if (id === 'company')  entry.target.classList.add('is-light');
        } else {
          if (id === 'services') entry.target.classList.remove('is-dark');
          if (id === 'company')  entry.target.classList.remove('is-light');
        }
      });
    }, { rootMargin: '0px 0px -40% 0px', threshold: 0 });

    sections.forEach(function (s) { bgObs.observe(s.el); });
  });

  /* ============================================================
     マウストレイル（ベジェ曲線 + グロー）
     ============================================================ */
  (function initMouseTrail() {
    if (window.matchMedia('(hover: none)').matches) return;

    var canvas = document.createElement('canvas');
    canvas.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9999;';
    document.body.appendChild(canvas);
    var ctx = canvas.getContext('2d');

    var points  = [];
    var LIFE    = 700;
    var COLOR   = '104,212,224';
    var MAX_PTS = 100;
    var smoothX = null, smoothY = null;
    var SMOOTH  = 0.25; // 強めにスムーズにして手ぶれを減らす

    function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
    resize();
    window.addEventListener('resize', resize);

    document.addEventListener('mousemove', function (e) {
      if (smoothX === null) { smoothX = e.clientX; smoothY = e.clientY; }
      smoothX += SMOOTH * (e.clientX - smoothX);
      smoothY += SMOOTH * (e.clientY - smoothY);
      points.push({ x: smoothX, y: smoothY, t: Date.now() });
      if (points.length > MAX_PTS) points.shift();
    });

    // なめらかな quadratic bezier パスを建構するヘルパー
    function buildPath(pts, from, to) {
      if (from >= pts.length || to < from + 1) return;
      ctx.moveTo(pts[from].x, pts[from].y);
      for (var i = from + 1; i <= to; i++) {
        if (i < pts.length - 1) {
          var mx = (pts[i].x + pts[i + 1].x) / 2;
          var my = (pts[i].y + pts[i + 1].y) / 2;
          ctx.quadraticCurveTo(pts[i].x, pts[i].y, mx, my);
        } else {
          ctx.lineTo(pts[i].x, pts[i].y);
        }
      }
    }

    function draw() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      var now = Date.now();
      while (points.length > 0 && now - points[0].t > LIFE) points.shift();
      if (points.length < 2) { requestAnimationFrame(draw); return; }

      ctx.save();
      ctx.lineCap  = 'round';
      ctx.lineJoin = 'round';
      ctx.lineWidth = 3.5; // 均一な太さ（マーカー感）

      var total = points.length;
      var CHUNKS = 7; // 末尾→先頭の段階的フェード

      for (var c = 0; c < CHUNKS; c++) {
        var i0 = Math.floor(c / CHUNKS * total);
        var i1 = Math.min(Math.floor((c + 1) / CHUNKS * total), total - 1);
        if (i0 >= i1) continue;

        var alpha = 0.06 + (c / (CHUNKS - 1)) * 0.64; // 0.06 → 0.70

        // 先頭チャンクにだけグロー
        if (c === CHUNKS - 1) {
          ctx.shadowBlur  = 10;
          ctx.shadowColor = 'rgba(' + COLOR + ', 0.45)';
        } else {
          ctx.shadowBlur = 0;
        }
        ctx.strokeStyle = 'rgba(' + COLOR + ',' + alpha.toFixed(3) + ')';

        ctx.beginPath();
        buildPath(points, i0, i1);
        ctx.stroke();
      }

      ctx.restore();
      requestAnimationFrame(draw);
    }
    draw();
  })();

  /* ============================================================
     FV: 球体（輪郭明確 + 内部波型曲線）+ パーティクル
     ============================================================ */
  document.addEventListener('DOMContentLoaded', function () {
    var fvBg = document.querySelector('.siaorb-fv__bg');
    if (!fvBg) return;

    var canvas = document.createElement('canvas');
    canvas.style.cssText = 'position:absolute;top:0;left:0;width:100%;height:100%;';
    fvBg.appendChild(canvas);
    var ctx = canvas.getContext('2d');

    var W, H, cx, cy, R;
    var mouse = { x: -9999, y: -9999 };
    var CYAN  = '104,212,224'; // #68d4e0
    var time  = 0;

    function resize() {
      W  = canvas.width  = fvBg.offsetWidth  || window.innerWidth;
      H  = canvas.height = fvBg.offsetHeight || window.innerHeight;
      // 常に中央
      cx = W * 0.5;
      cy = H * 0.5;
      // PC では大きめ、SP ではコンパクト
      R  = W > 768 ? Math.min(W, H) * 0.34 : Math.min(W, H) * 0.36;
      initParticles();
    }
    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', function (e) { mouse.x = e.clientX; mouse.y = e.clientY; });
    // DOMContentLoaded 時点では FV の高さが未確定な場合があるため load でも再初期化
    window.addEventListener('load', resize);
    resize();

    /* ----------------------------------------------------------
       マウスによる微小傾き（穏やかに）
       ---------------------------------------------------------- */
    function getInf() {
      var dx = (mouse.x - cx) / (W || 1);
      var dy = (mouse.y - cy) / (H || 1);
      return { rx: dy * 0.25, ry: -dx * 0.25 };
    }

    /* ----------------------------------------------------------
       球面座標 → 2D 正射影
       ---------------------------------------------------------- */
    function proj(lat, lon, inf) {
      var cl = Math.cos(lat);
      var x  = cl * Math.cos(lon);
      var y  = Math.sin(lat);
      var z  = cl * Math.sin(lon);
      // X 軸回転
      var y2 = y * Math.cos(inf.rx) - z * Math.sin(inf.rx);
      var z2 = y * Math.sin(inf.rx) + z * Math.cos(inf.rx);
      // Y 軸回転
      var x3 = x * Math.cos(inf.ry) + z2 * Math.sin(inf.ry);
      var z3 =-x * Math.sin(inf.ry) + z2 * Math.cos(inf.ry);
      return { x: cx + x3 * R, y: cy - y2 * R, z: z3 };
    }

    /* ----------------------------------------------------------
       1. 球体輪郭（くっきりした円）
       ---------------------------------------------------------- */
    function drawOutline() {
      ctx.beginPath();
      ctx.arc(cx, cy, R, 0, Math.PI * 2);
      // 外側グロー
      ctx.shadowBlur  = 28;
      ctx.shadowColor = 'rgba(' + CYAN + ', 0.55)';
      ctx.strokeStyle = 'rgba(' + CYAN + ', 0.85)';
      ctx.lineWidth   = 1.8;
      ctx.stroke();
      // 2重目（細く薄く）
      ctx.beginPath();
      ctx.arc(cx, cy, R + 3, 0, Math.PI * 2);
      ctx.shadowBlur  = 0;
      ctx.strokeStyle = 'rgba(' + CYAN + ', 0.18)';
      ctx.lineWidth   = 0.8;
      ctx.stroke();
    }

    /* ----------------------------------------------------------
       2. クリッピングパス（輪郭内にだけ描画）
       ---------------------------------------------------------- */
    function clipToSphere() {
      ctx.beginPath();
      ctx.arc(cx, cy, R - 0.5, 0, Math.PI * 2);
      ctx.clip();
    }

    /* ----------------------------------------------------------
       3. 内部波型曲線（ランダム性を抑えた規則的な流れ）
          - 曲線本数を減らして整理感を出す
          - 全て左→右上方向に揃える
          - 振幅を小さめに抑制
       ---------------------------------------------------------- */
    var WAVE_COUNT = 14;
    var WAVES = [];

    for (var i = 0; i < WAVE_COUNT; i++) {
      var t = i / WAVE_COUNT;
      // 疑似ランダム（シード的に i を使って再現性あるバリエーション）
      var r1 = Math.sin(i * 127.1) * 0.5 + 0.5;
      var r2 = Math.sin(i * 311.7) * 0.5 + 0.5;
      var r3 = Math.sin(i * 74.3)  * 0.5 + 0.5;
      WAVES.push({
        latBase: -0.75 * Math.PI / 2 + t * 1.5 * Math.PI / 2,
        amp:     0.08 + r1 * 0.22,          // 0.08〜0.30（大幅に拡大）
        freq:    1.0 + r2 * 2.8,            // 1.0〜3.8（さらに多様な波数）
        phase:   r3 * Math.PI * 2,          // 完全ランダムな初期位相
        speed:   0.005 + r1 * 0.012,        // 0.005〜0.017（速い/遅いが混在）
        phaseShift: r2 * 0.8,               // 時間とともにフェーズがずれる量
        alpha:   0.22 + t * 0.28,
        width:   0.6 + r3 * 1.0,
      });
    }

    function drawWaves(inf) {
      var STEPS = 80;

      WAVES.forEach(function (w) {
        ctx.beginPath();

        for (var s = 0; s <= STEPS; s++) {
          var frac = s / STEPS;
          // 経度: 左端(-π)から右端(π) まで一定速度で進む
          var lon = -Math.PI + frac * 2 * Math.PI;
          // 緯度: baseに穏やかな正弦波を乗せる（振幅は球半径比）
          // 2つの正弦波を重ねることで複雑な動きを生成
          var lat = w.latBase
                  + Math.sin(frac * Math.PI * w.freq + time * w.speed + w.phase) * w.amp
                  + Math.sin(frac * Math.PI * (w.freq * 0.5) + time * (w.speed * 0.7) + w.phase + w.phaseShift * time * 0.001) * (w.amp * 0.4);

          var p = proj(lat, lon, inf);

          // 裏側は薄く
          var depthA = Math.max(0, (p.z + 1) / 2);
          // 端は自然に細く
          var edgeA  = Math.sin(frac * Math.PI);
          var alpha  = w.alpha * depthA * (0.3 + 0.7 * edgeA);

          if (s === 0) {
            ctx.moveTo(p.x, p.y);
          } else {
            ctx.lineTo(p.x, p.y);
          }
        }

        ctx.shadowBlur  = 6;
        ctx.shadowColor = 'rgba(' + CYAN + ',0.25)';
        ctx.strokeStyle = 'rgba(' + CYAN + ',' + w.alpha.toFixed(3) + ')';
        ctx.lineWidth   = w.width;
        ctx.stroke();
      });
    }

    /* ----------------------------------------------------------
       4. 波形リング（球体から外に広がるパルス）
       ---------------------------------------------------------- */
    var rings    = [];
    var lastRing = 0;
    var RING_INT = 2400;
    var RING_DUR = 5000;
    for (var wi = 0; wi < 3; wi++) rings.push({ start: Date.now() - wi * (RING_INT * 0.85) });

    function drawRings() {
      var now = Date.now();
      if (now - lastRing > RING_INT) { rings.push({ start: now }); lastRing = now; }
      rings = rings.filter(function (r) { return now - r.start < RING_DUR; });

      rings.forEach(function (r) {
        var t    = Math.min((now - r.start) / RING_DUR, 1);
        var ease = 1 - Math.pow(1 - t, 2);
        var wR   = R + (Math.max(W, H) * 0.62 - R) * ease;
        var al   = (1 - t) * (1 - t) * 0.4;

        ctx.shadowBlur = 0;
        [{ lw: 1.2, a: al }, { lw: 5, a: al * 0.14 }, { lw: 14, a: al * 0.05 }]
          .forEach(function (ring) {
            ctx.beginPath();
            ctx.arc(cx, cy, wR, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(' + CYAN + ',' + ring.a.toFixed(3) + ')';
            ctx.lineWidth   = ring.lw;
            ctx.stroke();
          });
      });
    }

    /* ----------------------------------------------------------
       5. パーティクル（数増 + 視認性向上）
       ---------------------------------------------------------- */
    var particles      = [];
    var PARTICLE_COUNT = 80; // 40 → 80 に増量

    function initParticles() {
      particles = [];
      for (var i = 0; i < PARTICLE_COUNT; i++) particles.push(makeParticle(true));
    }

    function makeParticle(random) {
      var isPC = W > 768;
      var tier  = Math.random();
      var size, alpha, speed;
      if (tier < 0.55) {         // 小（多め）
        size  = isPC ? 1.2 + Math.random() * 1.4 : 0.8 + Math.random() * 1.0;
        alpha = isPC ? 0.35 + Math.random() * 0.3 : 0.15 + Math.random() * 0.25;
        speed = 0.25 + Math.random() * 0.5;
      } else if (tier < 0.85) {  // 中
        size  = isPC ? 2.5 + Math.random() * 2.0 : 1.8 + Math.random() * 1.4;
        alpha = isPC ? 0.5  + Math.random() * 0.35 : 0.25 + Math.random() * 0.3;
        speed = 0.15 + Math.random() * 0.35;
      } else {                   // 大（少ない）
        size  = isPC ? 4.5 + Math.random() * 3.0 : 3.0 + Math.random() * 2.0;
        alpha = isPC ? 0.4  + Math.random() * 0.3 : 0.1 + Math.random() * 0.15;
        speed = 0.1 + Math.random() * 0.2;
      }
      return {
        x:     Math.random() * W,
        y:     random ? Math.random() * H : H + size + 2,
        size:  size,
        speed: speed,
        alpha: alpha,
        drift: (Math.random() - 0.5) * 0.5,
        glow:  tier > 0.55,      // 中・大はグロー付き
      };
    }

    function drawParticles() {
      particles.forEach(function (p, i) {
        p.y -= p.speed;
        p.x += p.drift;
        if (p.y < -p.size - 2) particles[i] = makeParticle(false);

        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
        if (p.glow) {
          var glowMult = W > 768 ? 8 : 5; // PCはグローを強める
          ctx.shadowBlur  = p.size * glowMult;
          ctx.shadowColor = 'rgba(' + CYAN + ',' + Math.min(p.alpha * 0.9, 1).toFixed(3) + ')';
        } else {
          ctx.shadowBlur = 0;
        }
        ctx.fillStyle = 'rgba(' + CYAN + ',' + p.alpha.toFixed(3) + ')';
        ctx.fill();
      });
    }

    /* ----------------------------------------------------------
       メインループ
       ---------------------------------------------------------- */
    function loop() {
      ctx.clearRect(0, 0, W, H);

      var inf = getInf();

      // パーティクルは sphere clip の外側も含む → clip前に描画
      ctx.save();
      drawParticles();
      ctx.restore();

      // リングも clip 外（球体より外に広がる）
      ctx.save();
      drawRings();
      ctx.restore();

      // 輪郭（clip 前に描いてクリッピングに巻き込まれないようにする）
      ctx.save();
      drawOutline();
      ctx.restore();

      // 内部波型曲線のみ球体内にクリップ
      ctx.save();
      clipToSphere();
      ctx.shadowBlur = 0;
      drawWaves(inf);
      ctx.restore();

      time++;
      requestAnimationFrame(loop);
    }

    loop();
  });

})();
