/**
 * Lish 共通 UI 挙動（親テーマ）。
 *
 * 移行元: base-theme assets/js/script.js
 *   - ハンバーガー / ドロワー開閉
 *   - ヘッダーのスクロール状態クラス
 *   （案件固有だった「沿革 横スクロール年表」は削除した）
 *
 * ★ マークアップ契約（Lish 標準）
 *   ハンバーガー : <button class="js-hamburger" aria-expanded aria-controls="js-drawer">
 *   ドロワー     : <div class="js-drawer" id="js-drawer" aria-hidden>
 *   オーバーレイ : <div class="js-drawer-overlay">
 *   開いた状態   : <body class="js-drawer-open">
 *   スクロール後 : <body class="js-header-scrolled">
 *
 *   見た目（SCSS）は子テーマ側で自由に作る。JS はクラスの付け外しだけを担当する。
 *   この契約は親テーマの公開 API。変更は MAJOR バージョンでのみ行う。
 *
 * 案件固有の JS は子テーマの assets/js/app.js に書く（deps: ['lish-ui']）。
 */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  ready(function () {
    const body = document.body;

    // ===== ハンバーガー / ドロワー =====
    const hamburger = document.querySelector('.js-hamburger');
    const drawer = document.querySelector('.js-drawer');
    const drawerOverlay = document.querySelector('.js-drawer-overlay');
    const openClass = 'js-drawer-open';

    function setDrawerState(isOpen) {
      body.classList.toggle(openClass, isOpen);
      if (hamburger) {
        hamburger.setAttribute('aria-expanded', String(isOpen));
        hamburger.setAttribute('aria-label', isOpen ? 'メニューを閉じる' : 'メニューを開く');
      }
      if (drawer) {
        drawer.setAttribute('aria-hidden', String(!isOpen));
      }
      document.dispatchEvent(new CustomEvent('lish:drawer', { detail: { open: isOpen } }));
    }

    if (hamburger) {
      hamburger.addEventListener('click', function () {
        setDrawerState(!body.classList.contains(openClass));
      });
    }

    // ドロワー内リンクをクリックしたら閉じる
    if (drawer) {
      drawer.addEventListener('click', function (e) {
        if (e.target.closest('a')) {
          setDrawerState(false);
        }
      });
    }

    // オーバーレイ（暗幕）クリックで閉じる
    if (drawerOverlay) {
      drawerOverlay.addEventListener('click', function () {
        setDrawerState(false);
      });
    }

    // Esc キーで閉じる
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && body.classList.contains(openClass)) {
        setDrawerState(false);
      }
    });

    // リサイズでPC幅に戻ったら強制で閉じる
    let resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        if (window.matchMedia('(min-width: 769px)').matches && body.classList.contains(openClass)) {
          setDrawerState(false);
        }
      }, 150);
    });

    // ===== ヘッダーのスクロール状態 =====
    // body.js-header-scrolled が一定スクロール後に付与される。
    // SCSS 側で `body.js-header-scrolled .l-header { ... }` のように差分指定する。
    const scrolledClass = 'js-header-scrolled';
    const threshold = 10;

    function updateScrollState() {
      body.classList.toggle(scrolledClass, window.scrollY > threshold);
    }

    updateScrollState();
    window.addEventListener('scroll', updateScrollState, { passive: true });
  });
})();
