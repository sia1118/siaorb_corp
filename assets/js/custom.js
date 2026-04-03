/**
 * SIAORB Corporate - カスタム JavaScript
 */
(function () {
  'use strict';

  /* ------------------------------------------------------------------
     スムーススクロール（ページ内アンカーリンク）
     ------------------------------------------------------------------ */
  document.addEventListener('DOMContentLoaded', function () {
    var anchors = document.querySelectorAll('a[href^="#"]');

    anchors.forEach(function (anchor) {
      anchor.addEventListener('click', function (e) {
        var targetId = this.getAttribute('href');
        if (targetId === '#') return;

        var target = document.querySelector(targetId);
        if (!target) return;

        e.preventDefault();

        // ヘッダー高さを考慮したオフセット（SWELLの固定ヘッダー対応）
        var headerEl = document.querySelector('#header, .l-header, header');
        var offset   = headerEl ? headerEl.offsetHeight : 0;
        var targetY  = target.getBoundingClientRect().top + window.pageYOffset - offset;

        window.scrollTo({
          top:      targetY,
          behavior: 'smooth',
        });
      });
    });
  });

  /* ------------------------------------------------------------------
     スクロールアニメーション（IntersectionObserver）
     ------------------------------------------------------------------ */
  document.addEventListener('DOMContentLoaded', function () {
    if (!('IntersectionObserver' in window)) return;

    var targets = document.querySelectorAll(
      '.siaorb-news__item, .siaorb-services__item, .siaorb-philosophy__vision, .siaorb-philosophy__greeting'
    );

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 }
    );

    targets.forEach(function (el) {
      el.classList.add('js-animate');
      observer.observe(el);
    });
  });

})();
