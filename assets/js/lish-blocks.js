/**
 * Lish ブログ用ブロック — フロント（1.1.0〜）
 *
 * - URL コピーボタン（core/button の is-style-lish-copy-url）
 * - 目次の「もっと見る」（lish/toc の lish-toc--collapsible）
 *
 * 使うブロックがあるページだけ読み込まれる（inc/blocks/*.php の render_block から enqueue）。
 * 文言は lishBlocksFront（wp_localize_script）で受け取る。
 */
(() => {
  const texts = window.lishBlocksFront || {};

  // ===== URL コピー =====
  // http 環境など Clipboard API が使えない / 応答が無い場合の代替
  const copyTextLegacy = (text) =>
    new Promise((resolve, reject) => {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.setAttribute('readonly', '');
      textarea.className = 'lish-copy-buffer'; // 画面外に置く（lish-blocks.css）
      document.body.appendChild(textarea);
      textarea.select();
      const ok = document.execCommand('copy');
      textarea.remove();
      ok ? resolve() : reject(new Error('copy failed'));
    });

  const copyText = (text) => {
    if (!navigator.clipboard || !window.isSecureContext) {
      return copyTextLegacy(text);
    }
    // 権限待ちなどで writeText が返ってこないブラウザがあるため、一定時間で代替に切り替える
    const timeout = new Promise((resolve, reject) => {
      window.setTimeout(() => reject(new Error('clipboard timeout')), 1500);
    });
    return Promise.race([navigator.clipboard.writeText(text), timeout]).catch(() => copyTextLegacy(text));
  };

  document.querySelectorAll('.is-style-lish-copy-url .wp-block-button__link').forEach((link) => {
    const label = link.textContent;
    let timer = 0;
    link.setAttribute('aria-live', 'polite');

    const showResult = (message, copied) => {
      window.clearTimeout(timer);
      link.textContent = message;
      link.classList.toggle('is-copied', copied);
      timer = window.setTimeout(() => {
        link.textContent = label;
        link.classList.remove('is-copied');
      }, 3000);
    };

    link.addEventListener('click', (event) => {
      event.preventDefault();
      copyText(link.href || window.location.href)
        .then(() => showResult(texts.copied || 'Copied', true))
        .catch(() => showResult(texts.copyFailed || 'Copy failed', false));
    });
  });

  // ===== 目次の「もっと見る」 =====
  // 項目が 4 件以上のときだけ高さを制限し、ボタンで全部表示する
  document.querySelectorAll('.js-lish-toc').forEach((toc) => {
    const button = toc.querySelector('.js-lish-toc-more');
    if (!button || toc.querySelectorAll('.lish-toc__item').length <= 3) {
      return;
    }

    const onFocusIn = (event) => {
      if (event.target !== button) {
        expand();
      }
    };
    const expand = () => {
      toc.classList.remove('is-collapsed');
      button.remove();
      toc.removeEventListener('focusin', onFocusIn);
    };

    toc.classList.add('is-collapsed');
    button.hidden = false;
    button.addEventListener('click', expand);
    // キーボードで隠れた項目にフォーカスが移ったら開く
    toc.addEventListener('focusin', onFocusIn);
  });
})();
