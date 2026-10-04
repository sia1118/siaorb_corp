/**
 * SIAORB Corporate — custom.js
 *
 * やること:
 *   0. ヒーローより上の余白を実測して潰す
 *   1. アンカーリンクのスクロール（固定ナビの高さ分を差し引く）
 *   2. 固定背景の粒子（canvas）
 *   3. 欧文をスクランブルさせてから確定させる（data-scramble）
 *   4. 画面に入った要素に .is-in を付ける（data-reveal / data-scramble）
 *      + 保険: 一定時間後、画面内にあるのに未表示の要素を必ず出す
 *   5. スクロールに合わせて動かす
 *      - --pp : ページ全体の進み具合（固定背景の環の回転）
 *      - --s  : ヒーローの進み具合（ロゴの環の広がりと地の沈み込み）
 *      - --p  : 各要素の位置（章番号などの流れ）
 *   6. ナビの罫と、追従の相談ボタンの出し入れ
 *   7. ヒーローの奥行き（カーソルに合わせた層のずれ --mx / --my と、PC 幅だけの粒子）
 *   8. 追従するロゴ（ヒーローからサービスへ運び、上昇曲線を章ごとの形へ変形させる）
 *
 * 演出の初期状態（透明・ずらし）は html.sia-js が付いたときだけ効くので、
 * JS が動かない環境では素のまま全部表示される。
 * 動きを減らす設定（Android は省電力モードでも有効になる）のときは、
 * 位置の移動・回転・視差を止め、現れ方だけ不透明度に置き換える。
 * ナビと追従ボタンは機能なので、どちらの設定でも動く。
 */
(function () {
  'use strict';

  /*
     実機でしか出ない不具合を切り分けるための記録。
     スクリプトが動き出した時点の readyState と、その後のエラーを控えておく。
     URL に ?siadebug=1 を付けたときだけ画面に出す。
  */
  var BOOT_STATE = document.readyState;
  var ERRORS = [];
  window.addEventListener('error', function (e) {
    ERRORS.push((e.message || 'error') + ' @ ' + (e.filename || '?') + ':' + (e.lineno || 0));
  });

  var root = document.documentElement;
  var mq = window.matchMedia('(prefers-reduced-motion: reduce)');
  var reduce = mq.matches;

  /*
     .sia-js  … 演出の初期状態を有効にする印。常に付ける。
     .sia-calm … 「動きを減らす」設定のときに付く印。
                 位置の移動・回転・視差は止め、現れ方だけ不透明度に置き換える。

     Android は「ユーザー補助 → アニメーションを削除」だけでなく
     省電力モードでも reduce になるため、全部止めると画面が死んで見える。
  */
  root.classList.add('sia-js');
  if (reduce) root.classList.add('sia-calm');

  // 省電力モードの入切などで設定が変わったら、印だけ付け替える
  if (mq.addEventListener) {
    mq.addEventListener('change', function (e) {
      root.classList.toggle('sia-calm', e.matches);
    });
  }

  /* ============================================================
     実機診断
     ?siadebug=1 を付けて開いたときだけ、画面下に状態を出す。
     boot() が落ちても出るよう、load でも呼ぶようにしてある。
     （パネルが出ない = スクリプト自体が読まれていない、と切り分けられる）
     ============================================================ */
  var panelDone = false;

  function debugPanel() {
    if (panelDone || location.search.indexOf('siadebug=1') < 0) return;
    if (!document.body) return;
    panelDone = true;

    var cv = document.getElementById('sia-bg-dots');
    var lit = 0;
    try {
      if (cv && cv.width) {
        var d = cv.getContext('2d').getImageData(0, 0, cv.width, cv.height).data;
        for (var i = 3; i < d.length; i += 4) { if (d[i] > 0) lit++; }
      }
    } catch (err) { lit = -1; }

    var rows = [
      ['script実行時のreadyState', BOOT_STATE],
      ['boot() 実行', bootRan ? 'した' : 'していない'],
      ['reduced-motion', String(reduce)],
      ['html class', root.className || '(なし)'],
      ['IntersectionObserver', ('IntersectionObserver' in window) ? 'あり' : 'なし'],
      ['reveal 表示済/全', document.querySelectorAll('[data-reveal].is-in').length +
        ' / ' + document.querySelectorAll('[data-reveal]').length],
      ['スクランブル分割', document.querySelector('.sia-scr') ? 'あり' : 'なし'],
      ['粒子canvas', cv ? (cv.width + 'x' + cv.height + ' / 点' + lit) : 'なし'],
      ['--pp', root.style.getPropertyValue('--pp') || '(未設定)'],
      ['JSエラー', ERRORS.length ? ERRORS.join(' | ') : 'なし'],
      ['UA', navigator.userAgent],
    ];

    function esc(v) {
      return String(v).replace(/&/g, '&amp;').replace(/</g, '&lt;');
    }

    var box = document.createElement('div');
    box.setAttribute('style',
      'position:fixed;left:0;right:0;bottom:0;z-index:99999;background:#05070f;' +
      'color:#8fe6f0;font:12px/1.65 monospace;padding:12px 14px;max-height:56vh;overflow:auto;');
    box.innerHTML = '<b style="color:#fff">SIAORB debug</b><br>' + rows.map(function (r) {
      return '<span style="color:#6f8">' + esc(r[0]) + '</span>: ' + esc(r[1]);
    }).join('<br>');
    document.body.appendChild(box);
  }

  window.addEventListener('load', function () { setTimeout(debugPanel, 800); });

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

  /* ============================================================
     ヒーローの奥行き
     カーソルの位置を --mx / --my（-0.5〜0.5）として .sia-lead に渡す。
     層ごとのずれ量は CSS 側で決める。
     粒子は PC 幅でだけ描く。動き続けはせず、スクロールとカーソルに
     応じて描き直すだけなので、止まっている間は何も走らない。
     ============================================================ */
  function initHero(lead) {
    if (!lead || reduce) return null;

    var canvas = document.getElementById('sia-hero-dots');
    var wide = window.matchMedia('(min-width: 901px)');
    var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var ctx = canvas && canvas.getContext ? canvas.getContext('2d') : null;
    var dots = [];
    var w = 0, h = 0, dpr = 1;
    var scroll = 0;
    var t = { x: 0, y: 0 }, c = { x: 0, y: 0 };
    var running = false;

    for (var i = 0; i < 90; i++) {
      dots.push({
        x: Math.random(),
        y: Math.random(),
        z: 0.2 + Math.random() * 0.8,   // 奥行き。大きいほど手前
        cyan: Math.random() < 0.35,
      });
    }

    function resize() {
      if (!ctx || !wide.matches) return;
      dpr = Math.min(window.devicePixelRatio || 1, 2);
      w = canvas.clientWidth;
      h = canvas.clientHeight;
      canvas.width  = Math.round(w * dpr);
      canvas.height = Math.round(h * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      draw();
    }

    function draw() {
      if (!ctx || !wide.matches || !w) return;
      ctx.clearRect(0, 0, w, h);
      for (var i = 0; i < dots.length; i++) {
        var d = dots[i];
        // 手前の粒ほど速く流れる
        var y = ((d.y * h - scroll * d.z * 0.5) % h + h) % h;
        var x = d.x * w + c.x * 30 * d.z;
        ctx.beginPath();
        ctx.arc(x, y, 0.8 + 2 * d.z, 0, Math.PI * 2);
        ctx.fillStyle = (d.cyan ? 'rgba(104, 212, 224,' : 'rgba(15, 47, 180,') + (0.1 + 0.3 * d.z).toFixed(3) + ')';
        ctx.fill();
      }
    }

    // カーソルには少し遅れて付いていく。追いついたら止まる。
    function frame() {
      c.x += (t.x - c.x) * 0.12;
      c.y += (t.y - c.y) * 0.12;
      var moving = Math.abs(t.x - c.x) > 0.002 || Math.abs(t.y - c.y) > 0.002;
      if (!moving) { c.x = t.x; c.y = t.y; }

      lead.style.setProperty('--mx', c.x.toFixed(4));
      lead.style.setProperty('--my', c.y.toFixed(4));
      draw();

      if (moving) {
        window.requestAnimationFrame(frame);
      } else {
        running = false;
      }
    }

    if (fine) {
      lead.addEventListener('pointermove', function (e) {
        if (e.pointerType === 'touch') return;
        t.x = e.clientX / (window.innerWidth || 1) - 0.5;
        t.y = e.clientY / (window.innerHeight || 1) - 0.5;
        if (!running) { running = true; window.requestAnimationFrame(frame); }
      }, { passive: true });
    }

    resize();
    window.addEventListener('resize', resize);

    return {
      setScroll: function (y) {
        if (y === scroll) return;
        scroll = y;
        // 粒子の面が画面から出たあとは描かない
        if (y < h + 40) draw();
      },
    };
  }

  /* ============================================================
     追従するロゴ
     ヒーローのロゴ（#sia-lead-logo）を画面に固定したまま、
     章ごとの置き場所へ運ぶ。運びながら、上昇曲線（#sia-stage の主線）を
       ロゴの曲線 → 1章の形 → 2章の形 → 3章の形
     と変形させる。形は data-shapes に path の d で並んでいる。

     変形は、それぞれの形を同じ数の点に取り直して点どうしを補間する。
     章の中央付近では補間せず、元の d をそのまま当てて角を立たせる。

     置き場所:
       出発点 … 動かないロゴ（.sia-logo）のある場所。測って合わせる。
       PC     … 章ごとに左・右・左。本文の面とは反対側。上下も少しずらし、
                 章が変わるたびに斜めに移る。
       スマホ … 画面の上半分の中央。本文の面は下半分に来る。

     動きを減らす設定のときは CSS がこの面を出さない。出ていない間は何もしない。
     ============================================================ */
  function initStory() {
    var svg = document.getElementById('sia-stage');
    var box = document.getElementById('sia-lead-logo');
    var lead = document.getElementById('sia-lead');
    var still = lead ? lead.querySelector('.sia-logo') : null;
    if (!svg || !box || !lead || !still || reduce) return null;

    var layer = box.parentNode;
    var line = svg.querySelector('.sia-stage__line');
    var chapters = Array.prototype.slice.call(document.querySelectorAll('[data-chapter]'));
    var narrow = window.matchMedia('(max-width: 900px)');
    var shapes;
    try { shapes = JSON.parse(svg.getAttribute('data-shapes')); } catch (err) { shapes = null; }

    if (!line || !line.getTotalLength || !shapes || shapes.length !== chapters.length + 1) return null;

    var N = 160;          // 1つの形を何点で表すか
    var W_LOGO = 56;      // 主線の太さ。ロゴのとき（viewBox の単位）
    var W_FIG = 6;        //            図版のとき（PC）
    var W_FIG_SP = 11;    //            図版のとき（スマホ。図版が小さいぶん太らせる）
    var samples = null;   // 形ごとの点列。面が表示されてから作る
    var layers = [];      // 章ごとの補助線・シアンの線・点
    var cur = 0, target = 0, running = false, lastD = '';

    chapters.forEach(function (ch, i) {
      var k = String(i + 1);
      var under = svg.querySelector('.sia-stage__under[data-k="' + k + '"]');
      var nodes = svg.querySelector('.sia-stage__nodes[data-k="' + k + '"]');
      layers.push({
        under: under,
        branches: under ? Array.prototype.slice.call(under.querySelectorAll('.sia-fig__branch')) : [],
        nodes: nodes ? Array.prototype.slice.call(nodes.children) : [],
      });
    });

    function clamp(v) { return Math.max(0, Math.min(1, v)); }
    function ease(v) { return v * v * (3 - 2 * v); }

    function visible() { return layer.getClientRects().length > 0; }

    function sample(d) {
      var p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      p.setAttribute('d', d);
      p.setAttribute('fill', 'none');
      svg.appendChild(p);
      var len = p.getTotalLength();
      var out = [];
      for (var i = 0; i <= N; i++) {
        var pt = p.getPointAtLength(len * i / N);
        out.push([pt.x, pt.y]);
      }
      svg.removeChild(p);
      return out;
    }

    /**
     * いまどこまで進んだか。
     *   0 = ページの先頭（ロゴ） / 1 = 1章が基準の高さに来た / 2 = 2章が来た …
     * 基準の高さは、PC では画面の中央。スマホでは図版が上半分にあるので、下寄りに取る。
     */
    function measure() {
      var vh = window.innerHeight || 1;
      var ref = vh * (narrow.matches ? 0.72 : 0.5);
      var c = chapters.map(function (ch) {
        var r = ch.getBoundingClientRect();
        return r.top + r.height / 2 - ref;
      });

      if (c[0] >= 0) {
        // 先頭から1章までの道のりのうち、どれだけ来たか
        var y = window.pageYOffset;
        return clamp(y / Math.max(1, c[0] + y));
      }
      for (var i = 0; i < c.length - 1; i++) {
        if (c[i + 1] >= 0) return i + 1 + (-c[i]) / (c[i + 1] - c[i]);
      }
      return c.length;
    }

    /** k 番目の置き場所（0 = ヒーロー、1〜 = 各章）。左上の座標と一辺の長さ。 */
    function pose(k) {
      var vw = layer.clientWidth;
      var vh = window.innerHeight || 1;

      if (k === 0) {
        // 動かないロゴと同じ場所。
        // 横は面の左端から、縦は .sia-lead の上端から測る（＝ページ先頭での画面上の位置）。
        var a = still.getBoundingClientRect();
        var b = lead.getBoundingClientRect();
        return { x: a.left - layer.getBoundingClientRect().left, y: a.top - b.top, w: a.width };
      }

      if (narrow.matches) {
        var s = Math.min(vw * 0.92, vh * 0.46);
        return { x: (vw - s) / 2, y: 68, w: s };
      }

      // 奇数の章は左下、偶数の章は右上。本文の面はその反対側にある。
      var odd = k % 2 === 1;
      var w = Math.min(vw * 0.7, vh * 1.08, 1040);
      return {
        x: vw * (odd ? 0.3 : 0.7) - w / 2,
        y: vh * (odd ? 0.57 : 0.45) - w / 2,
        w: w,
      };
    }

    /** ロゴの枠を、k 番目と k+1 番目の置き場所の間（u = 0〜1）に置く */
    function place(k, u, g) {
      var p0 = pose(0), a = pose(k), b = pose(k + 1);
      var w = a.w + (b.w - a.w) * u;

      box.style.width = box.style.height = p0.w.toFixed(1) + 'px';
      box.style.transform =
        'translate3d(' + (a.x + (b.x - a.x) * u).toFixed(1) + 'px,' + (a.y + (b.y - a.y) * u).toFixed(1) + 'px,0) ' +
        'scale(' + (w / Math.max(1, p0.w)).toFixed(4) + ')';
      box.style.setProperty('--g', clamp(g).toFixed(4));
    }

    function render(f) {
      var last = shapes.length - 1;
      var k = Math.min(last - 1, Math.floor(f));
      // 章の中央の前後では形を止め、その間だけ変形させる
      var u = ease(clamp((f - k - 0.2) / 0.6));
      var g = k + u;

      var d;
      if (u === 0) {
        d = shapes[k];
      } else if (u === 1) {
        d = shapes[k + 1];
      } else {
        var a = samples[k], b = samples[k + 1];
        d = '';
        for (var i = 0; i <= N; i++) {
          d += (i ? 'L' : 'M') +
            (a[i][0] + (b[i][0] - a[i][0]) * u).toFixed(1) + ' ' +
            (a[i][1] + (b[i][1] - a[i][1]) * u).toFixed(1);
        }
      }
      if (d !== lastD) { line.setAttribute('d', d); lastD = d; }

      // ロゴの太い線から、図版の細い線へ
      var thin = narrow.matches ? W_FIG_SP : W_FIG;
      line.style.strokeWidth = (W_LOGO + (thin - W_LOGO) * clamp(g)).toFixed(1);

      place(k, u, g);

      // 補助線・シアンの線・点は、その章の形に近づいたときだけ出す
      layers.forEach(function (l, i) {
        var wgt = clamp(1 - Math.abs(g - (i + 1)) * 2);
        if (l.under) l.under.style.opacity = wgt.toFixed(3);
        l.branches.forEach(function (b, j) {
          // 内側の線から順に描く
          b.style.strokeDashoffset = (100 * (1 - clamp(wgt * 1.8 - 0.3 - j * 0.25))).toFixed(1);
        });
        l.nodes.forEach(function (n, j) {
          n.style.opacity = clamp((wgt - 0.45 - j * 0.04) * 8).toFixed(3);
        });
      });
    }

    // スクロールに少し遅れて付いていく。追いついたら止まる。
    function frame() {
      cur += (target - cur) * 0.16;
      if (Math.abs(target - cur) < 0.002) cur = target;
      render(cur);
      if (cur !== target) {
        window.requestAnimationFrame(frame);
      } else {
        running = false;
      }
    }

    return {
      update: function () {
        if (!visible()) return;
        if (!samples) {
          samples = shapes.map(sample);
          cur = measure();
        }
        target = measure();
        if (running) return;
        if (cur === target) {
          // 進み具合が同じでも、画面の大きさが変われば置き場所は変わる
          render(cur);
        } else {
          running = true;
          window.requestAnimationFrame(frame);
        }
      },
    };
  }

  var bootRan = false;

  function boot() {
    bootRan = true;

    var nav     = document.getElementById('sia-nav');
    var badge   = document.getElementById('sia-badge');
    var hero    = document.getElementById('hero');
    var contact = document.getElementById('contact');

    // ヒーローとサービスをつなぐ面。無いページではヒーローを代わりに使う。
    var lead = document.getElementById('sia-lead') || hero;

    var dots = initDots();
    var heroFx = initHero(lead);
    var story = initStory();

    /* ----------------------------------------------------------
       0. ヒーローより上の余白を潰す

       フロントページの地は固定背景（黒）なので、SWELL 側のラッパーに
       上余白が 1px でも残ると、そこから黒が覗いてしまう。
       CSS では打ち消しきれない環境差があるため、実際の位置を測って
       そのぶんだけヒーローを引き上げる。
       ---------------------------------------------------------- */
    function killTopGap() {
      if (!lead) return;
      // いったん戻してから測る。そうしないと2回目以降の値がずれる。
      lead.style.marginTop = '0px';
      var top = lead.getBoundingClientRect().top + window.pageYOffset;
      if (top > 1) lead.style.marginTop = '-' + Math.round(top) + 'px';
    }

    killTopGap();
    window.addEventListener('load', killTopGap);
    window.addEventListener('resize', killTopGap);

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

    /* ----------------------------------------------------------
       3. 欧文をスクランブルさせる下ごしらえ
          動きを減らす設定のときは、文字を触らずそのまま出す。

       [data-scramble] の中身を
         <span class="sia-sr">本来の文字</span>          ← 読み上げ用。目には見えない
         <span class="sia-scr" aria-hidden="true">…</span> ← 動かす側
       の2つに分ける。動く側は読み上げから外れるので、
       途中のランダムな文字列が読み上げられることはない。

       幅は最初に測って固定する。文字が入れ替わっても行が揺れない。
       ---------------------------------------------------------- */
    var GLYPHS = 'ABCDEFGHIJKLNOPQRSTUVXYZ0123456789#$%&*+-/<>?@[]^_{}~';

    if (!reduce) document.querySelectorAll('[data-scramble]').forEach(function (el) {
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
      document.querySelectorAll(reduce ? '[data-reveal]' : '[data-reveal], [data-scramble]')
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

      /*
         スクロールに連動する「変形」は、動きを減らす設定のときだけ止める。
         ナビの罫と追従ボタンは見た目の演出ではなく機能なので、
         どちらの設定でも動かす。
      */
      if (!reduce) {
        // ページ全体の進み具合。固定背景の環がこれで回る。
        root.style.setProperty('--pp', (y / max).toFixed(4));
        if (dots) dots.setShift(y * 0.06);

        // ヒーロー: ロゴの環の広がりと地の沈み込み
        if (lead) lead.style.setProperty('--s', Math.min(1.4, y / vh).toFixed(4));
        if (heroFx) heroFx.setScroll(y);
        if (story) story.update();

        driftEls.forEach(function (el) {
          el.style.setProperty('--p', progress(el).toFixed(4));
        });
      }

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

    debugPanel();
  }

  /*
     起動。

     SWELL の高速化設定や最適化プラグインがスクリプトを遅延させると、
     この行に到達した時点で DOMContentLoaded が既に終わっていることがある。
     その場合 addEventListener だけでは二度と呼ばれないので、
     読み込み状態を見て直接呼ぶ。
  */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
