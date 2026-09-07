/**
 * SIAORB Corporate — custom.js
 *
 * やること:
 *   1. アンカーリンクのスクロール（固定ナビの高さ分を差し引く）
 *   2. 固定背景の粒子（canvas）
 *   3. 欧文をスクランブルさせてから確定させる（data-scramble）
 *   4. 画面に入った要素に .is-in を付ける（data-reveal / data-scramble）
 *      + 保険: 一定時間後、画面内にあるのに未表示の要素を必ず出す
 *   5. スクロールに合わせて動かす
 *      - --pp : ページ全体の進み具合（固定背景の環の回転）
 *      - --s  : ヒーローの進み具合（ロゴの傾きと地の沈み込み）
 *      - --p  : 各要素の位置（写真とテキストの左右の流れ）
 *   6. ナビの罫と、追従の相談ボタンの出し入れ
 *
 * 演出の初期状態（透明・ずらし）は html.sia-js が付いたときだけ効くので、
 * JS が動かない環境では素のまま全部表示される。
 * reduced-motion の環境では粒子も動かさず、演出も付けない。
 */
(function () {
  'use strict';

  var root = document.documentElement;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!reduce) root.classList.add('sia-js');

  /* ============================================================
     固定背景の粒子
     セクションの隙間からしか見えないので、数も速度も控えめにする。
     光らせない（shadowBlur を使わない）ことで、SF ではなく
     データの散布図に近い見え方にしている。
     ============================================================ */
  function initDots() {
    var canvas = document.getElementById('sia-bg-dots');
    if (!canvas || !canvas.getContext) return;

    var ctx = canvas.getContext('2d');
    var dots = [];
    var w = 0, h = 0, dpr = 1;
    var running = false;
    var scrollShift = 0;

    var TONES = [
      '104, 212, 224', // ロゴのシアン
      '120, 150, 255', // 明るい青
      '255, 255, 255', // 白
    ];

    function resize() {
      dpr = Math.min(window.devicePixelRatio || 1, 2);
      w = canvas.clientWidth;
      h = canvas.clientHeight;
      canvas.width  = Math.round(w * dpr);
      canvas.height = Math.round(h * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      build();
    }

    function build() {
      var count = Math.max(60, Math.min(190, Math.round((w * h) / 8600)));
      dots = [];
      for (var i = 0; i < count; i++) {
        var big = Math.random() > 0.88;
        dots.push({
          x: Math.random() * w,
          y: Math.random() * h,
          r: big ? 1.7 + Math.random() * 1.1 : 0.6 + Math.random() * 0.9,
          a: big ? 0.55 + Math.random() * 0.35 : 0.18 + Math.random() * 0.4,
          vx: (Math.random() - 0.5) * 0.11,
          vy: -0.05 - Math.random() * 0.13,
          tone: TONES[Math.random() < 0.5 ? 0 : (Math.random() < 0.6 ? 1 : 2)],
        });
      }
    }

    function draw() {
      if (!running) return;
      ctx.clearRect(0, 0, w, h);

      for (var i = 0; i < dots.length; i++) {
        var d = dots[i];
        d.x += d.vx;
        d.y += d.vy;

        if (d.y < -4) { d.y = h + 4; d.x = Math.random() * w; }
        if (d.x < -4) d.x = w + 4;
        if (d.x > w + 4) d.x = -4;

        // 固定背景なので、スクロール量をわずかに足して奥行きを出す
        var y = d.y + scrollShift;
        if (y > h + 4) y -= h + 8;
        if (y < -4) y += h + 8;

        ctx.beginPath();
        ctx.arc(d.x, y, d.r, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(' + d.tone + ',' + d.a.toFixed(3) + ')';
        ctx.fill();
      }

      window.requestAnimationFrame(draw);
    }

    function start() {
      if (running || reduce) return;
      running = true;
      window.requestAnimationFrame(draw);
    }

    function stop() { running = false; }

    resize();
    window.addEventListener('resize', resize);

    if (reduce) {
      // 動かさずに一度だけ描く
      running = true;
      draw();
      running = false;
    } else {
      start();
      document.addEventListener('visibilitychange', function () {
        if (document.hidden) stop(); else start();
      });
    }

    return { setShift: function (v) { scrollShift = v; } };
  }

  document.addEventListener('DOMContentLoaded', function () {
    var nav     = document.getElementById('sia-nav');
    var badge   = document.getElementById('sia-badge');
    var hero    = document.getElementById('hero');
    var contact = document.getElementById('contact');

    var dots = initDots();

    /* ----------------------------------------------------------
       1. アンカーリンクのスクロール
       ---------------------------------------------------------- */
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
      var id = a.getAttribute('href');
      if (!id || id === '#') return;

      a.addEventListener('click', function (e) {
        var target = document.querySelector(id);
        if (!target) return;
        e.preventDefault();

        var offset = nav ? nav.offsetHeight : 0;
        window.scrollTo({
          top: target.getBoundingClientRect().top + window.pageYOffset - offset,
          behavior: reduce ? 'auto' : 'smooth',
        });
      });
    });

    if (reduce) return;

    /* ----------------------------------------------------------
       3. 欧文をスクランブルさせる下ごしらえ

       [data-scramble] の中身を
         <span class="sia-sr">本来の文字</span>          ← 読み上げ用。目には見えない
         <span class="sia-scr" aria-hidden="true">…</span> ← 動かす側
       の2つに分ける。動く側は読み上げから外れるので、
       途中のランダムな文字列が読み上げられることはない。

       幅は最初に測って固定する。文字が入れ替わっても行が揺れない。
       ---------------------------------------------------------- */
    var GLYPHS = 'ABCDEFGHIJKLNOPQRSTUVXYZ0123456789#$%&*+-/<>?@[]^_{}~';

    document.querySelectorAll('[data-scramble]').forEach(function (el) {
      var text = (el.textContent || '').trim();
      if (!text) return;

      var sr = document.createElement('span');
      sr.className = 'sia-sr';
      sr.textContent = text;

      var scr = document.createElement('span');
      scr.className = 'sia-scr';
      scr.setAttribute('aria-hidden', 'true');
      scr.textContent = text;

      el.textContent = '';
      el.appendChild(sr);
      el.appendChild(scr);

      // 幅は span を組んでから測る。要素そのものを測ると
      // ブロック要素では行の幅（＝親の幅）になってしまう。
      var w = scr.getBoundingClientRect().width;
      if (w) scr.style.width = Math.ceil(w) + 'px';

      el.__siaScr = scr;
      el.__siaText = text;
    });

    /** 左から順に確定していく。1文字あたり PER ミリ秒ずつ遅らせる。 */
    function runScramble(el) {
      var scr = el.__siaScr;
      var text = el.__siaText;
      if (!scr || !text || el.__siaDone) return;
      el.__siaDone = true;

      var len = text.length;
      var PER = 46;      // 1文字ぶんの遅れ
      var HOLD = 340;    // 各文字が乱れている時間

      // data-scramble に数値が入っていれば、その ms ぶん長く見せる。
      // 全体が同じ比率で伸びるので、途中で急に速くなったりしない。
      var base = (len - 1) * PER + HOLD;
      var extra = parseInt(el.getAttribute('data-scramble'), 10);
      if (extra > 0) {
        var k = (base + extra) / base;
        PER *= k;
        HOLD *= k;
      }

      var total = (len - 1) * PER + HOLD + 60;
      var start = null;

      function frame(now) {
        if (start === null) start = now;
        var t = now - start;
        var out = '';

        for (var i = 0; i < len; i++) {
          var ch = text.charAt(i);
          if (ch === ' ') { out += ' '; continue; }
          if (t >= i * PER + HOLD) {
            out += ch;
          } else {
            out += GLYPHS.charAt((Math.random() * GLYPHS.length) | 0);
          }
        }

        scr.textContent = out;
        if (t < total) {
          window.requestAnimationFrame(frame);
        } else {
          scr.textContent = text;
        }
      }
      window.requestAnimationFrame(frame);
    }

    /* ----------------------------------------------------------
       4. 画面に入ったら .is-in
       ---------------------------------------------------------- */
    var revealEls = Array.prototype.slice.call(
      document.querySelectorAll('[data-reveal], [data-scramble]')
    );

    function show(el) {
      el.classList.add('is-in');
      if (el.hasAttribute('data-scramble')) runScramble(el);
    }

    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          show(entry.target);
          io.unobserve(entry.target);
        });
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.04 });

      revealEls.forEach(function (el) { io.observe(el); });
    } else {
      revealEls.forEach(show);
    }

    /* 保険。観測が働かなくても、画面内の要素は必ず出す。 */
    function rescue() {
      var vh = window.innerHeight || 1;
      revealEls.forEach(function (el) {
        if (el.classList.contains('is-in')) return;
        var r = el.getBoundingClientRect();
        if (r.top < vh && r.bottom > 0) show(el);
      });
    }
    setTimeout(rescue, 1600);
    window.addEventListener('load', function () { setTimeout(rescue, 400); });

    /* ----------------------------------------------------------
       5〜6. スクロールに合わせた処理をひとつの rAF にまとめる
       ---------------------------------------------------------- */
    var driftEls = Array.prototype.slice.call(document.querySelectorAll('[data-drift]'));
    driftEls.forEach(function (el) {
      el.style.setProperty('--drift', el.getAttribute('data-drift') || 0);
    });

    var ticking = false;
    var state = { solid: null, badge: null };

    /** 要素の中心が画面のどこにいるかを -1.5〜1.5 で返す */
    function progress(el) {
      var vh = window.innerHeight || 1;
      var r = el.getBoundingClientRect();
      var p = (r.top + r.height / 2 - vh / 2) / vh;
      return Math.max(-1.5, Math.min(1.5, p));
    }

    function update() {
      ticking = false;

      var y  = window.pageYOffset;
      var vh = window.innerHeight || 1;
      var max = Math.max(1, root.scrollHeight - vh);

      // ページ全体の進み具合。固定背景の環がこれで回る。
      root.style.setProperty('--pp', (y / max).toFixed(4));
      if (dots) dots.setShift(y * 0.06);

      // ヒーロー: ロゴの傾きと地の沈み込み
      if (hero) hero.style.setProperty('--s', Math.min(1.4, y / vh).toFixed(4));

      driftEls.forEach(function (el) {
        el.style.setProperty('--p', progress(el).toFixed(4));
      });

      if (nav && hero) {
        var solid = hero.getBoundingClientRect().bottom <= nav.offsetHeight;
        if (solid !== state.solid) {
          state.solid = solid;
          nav.classList.toggle('is-solid', solid);
        }
      }

      if (badge && hero) {
        var passed = hero.getBoundingClientRect().bottom <= 0;
        var atContact = contact
          ? contact.getBoundingClientRect().top < vh * 0.75
          : false;
        var next = passed && !atContact ? 'show' : 'hide';
        if (next !== state.badge) {
          state.badge = next;
          badge.classList.toggle('is-shown', next === 'show');
        }
      }
    }

    function onScroll() {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(update);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
  });
})();
