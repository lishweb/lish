/**
 * Lish ブログ用ブロック — 編集画面（1.1.0〜）
 *
 * - 文字単位の装飾をツールバーの「Lish 装飾」メニューにまとめて登録する
 *   保存形式は <span class="lish-*">（クラス名は公開 API。変更は MAJOR）
 * - 独自ブロック lish/post-card（記事カード）の編集 UI（描画は PHP: inc/blocks/post-card.php）
 *
 * 見た目は lish-blocks.css。ビルド不要で動くよう、wp.* のグローバルを直接使う。
 */
(function (wp) {
  if (!wp || !wp.richText || !wp.blocks || !wp.blockEditor || !wp.components || !wp.element) {
    return;
  }

  const el = wp.element.createElement;
  const __ = wp.i18n.__;

  // ===================================================================
  //  文字装飾
  // ===================================================================
  const registerFormats = () => {
    const { registerFormatType, toggleFormat, getActiveFormat } = wp.richText;
    const { BlockControls } = wp.blockEditor;
    const { ToolbarGroup, ToolbarDropdownMenu } = wp.components;

    const definitions = [
      { name: 'lish/emphasis', className: 'lish-emphasis', title: __('傍点', 'lish') },
      { name: 'lish/label', className: 'lish-label', title: __('丸ラベル', 'lish') },
      { name: 'lish/blink', className: 'lish-blink', title: __('点滅', 'lish') },
      { name: 'lish/merit', className: 'lish-merit', title: __('メリット（○）', 'lish') },
      { name: 'lish/demerit', className: 'lish-demerit', title: __('デメリット（×）', 'lish') },
      { name: 'lish/mark-double-circle', className: 'lish-mark-double-circle', title: __('評価マーク ◎', 'lish') },
      { name: 'lish/mark-circle', className: 'lish-mark-circle', title: __('評価マーク ○', 'lish') },
      { name: 'lish/mark-triangle', className: 'lish-mark-triangle', title: __('評価マーク △', 'lish') },
      { name: 'lish/mark-cross', className: 'lish-mark-cross', title: __('評価マーク ×', 'lish') },
      { name: 'lish/rate', className: 'lish-rate', title: __('星評価（★☆ の文字に使う）', 'lish') },
      { name: 'lish/inline-btn', className: 'lish-inline-btn', title: __('小ボタン', 'lish') },
    ];

    const enabled = (window.lishBlocks && window.lishBlocks.formats) || [];
    const formats = definitions.filter((format) => enabled.indexOf(format.name) !== -1);
    if (!formats.length) {
      return;
    }

    // メニューは1つだけ出したいので、先頭の装飾の edit にまとめて描画させる
    const LishFormatMenu = ({ value, onChange }) =>
      el(
        BlockControls,
        { group: 'other' },
        el(
          ToolbarGroup,
          null,
          el(ToolbarDropdownMenu, {
            icon: 'art',
            label: __('Lish 装飾', 'lish'),
            controls: formats.map((format) => ({
              title: format.title,
              isActive: !!getActiveFormat(value, format.name),
              onClick: () => onChange(toggleFormat(value, { type: format.name })),
            })),
          })
        )
      );

    formats.forEach((format, index) => {
      registerFormatType(format.name, {
        title: format.title,
        tagName: 'span',
        className: format.className,
        edit: index === 0 ? LishFormatMenu : () => null,
      });
    });
  };

  // ===================================================================
  //  記事カード lish/post-card
  // ===================================================================
  const registerPostCard = () => {
    if (!wp.serverSideRender) {
      return;
    }
    const { registerBlockType } = wp.blocks;
    const { useBlockProps, InspectorControls, URLInput } = wp.blockEditor;
    const { PanelBody, TextControl, Placeholder } = wp.components;
    const ServerSideRender = wp.serverSideRender;

    registerBlockType('lish/post-card', {
      apiVersion: 2,
      title: __('記事カード', 'lish'),
      description: __('記事の URL を入れると、アイキャッチ・タイトル・日付をカードで表示します。', 'lish'),
      icon: 'admin-links',
      category: 'widgets',
      attributes: {
        url: { type: 'string', default: '' },
        label: { type: 'string' },
        className: { type: 'string' },
      },
      supports: { html: false },
      edit: ({ attributes, setAttributes, isSelected, name }) => {
        const blockProps = useBlockProps();
        const isB = (attributes.className || '').indexOf('is-style-lish-card-b') !== -1;
        const defaultLabel = isB ? __('あわせて読みたい', 'lish') : __('関連', 'lish');

        // 記事名で検索している途中の文字はプレビューせず、URL の形になってから描画する
        const url = attributes.url || '';
        const isUrl = /^(https?:)?\/\//.test(url) || url.charAt(0) === '/';

        // 入力欄は常に先頭に置く（表示の切り替えで作り直されると入力中のフォーカスが外れるため）
        const urlInput =
          isSelected || !isUrl
            ? el(
                'div',
                { className: 'lish-post-card-editor__input' },
                el(URLInput, {
                  label: __('記事の URL（記事名で検索できます）', 'lish'),
                  value: url,
                  onChange: (value) => setAttributes({ url: value }),
                  __nextHasNoMarginBottom: true,
                })
              )
            : null;

        return el(
          'div',
          blockProps,
          el(
            InspectorControls,
            null,
            el(
              PanelBody,
              { title: __('記事カード', 'lish') },
              el(TextControl, {
                label: __('ラベル', 'lish'),
                help: __('空にするとラベルを出しません。', 'lish'),
                value: attributes.label === undefined ? defaultLabel : attributes.label,
                onChange: (label) => setAttributes({ label }),
                __nextHasNoMarginBottom: true,
              })
            )
          ),
          urlInput,
          isUrl
            ? el(
                'div',
                { className: 'lish-post-card-editor__preview' },
                el(ServerSideRender, { block: name, attributes })
              )
            : el(Placeholder, {
                icon: 'admin-links',
                label: __('記事カード', 'lish'),
                instructions: __('上の欄に記事名を入れて、候補から記事を選んでください。', 'lish'),
              })
        );
      },
      save: () => null,
    });
  };

  // ===================================================================
  //  目次 lish/toc（フロントの描画は PHP: inc/blocks/toc.php）
  //  編集画面では、編集中の見出しブロックからその場でプレビューを作る
  // ===================================================================
  const registerToc = () => {
    if (!wp.data) {
      return;
    }
    const { registerBlockType } = wp.blocks;
    const { useBlockProps, InspectorControls } = wp.blockEditor;
    const { PanelBody, TextControl, ToggleControl, CheckboxControl } = wp.components;
    const { useSelect } = wp.data;

    const settings = (window.lishBlocks && window.lishBlocks.toc) || {};
    const defaultLevels = (settings.levels || [2, 3]).map(Number);
    const minHeadings = parseInt(settings.minHeadings, 10) || 3;

    // 見出しの HTML から文字だけを取り出す（DOMParser はスクリプトや画像を実行・読み込みしない）
    const toText = (html) => new DOMParser().parseFromString(String(html), 'text/html').body.textContent.trim();

    const collectHeadings = (blocks, found) => {
      blocks.forEach((block) => {
        if (block.name === 'core/heading') {
          found.push({ level: block.attributes.level || 2, text: toText(block.attributes.content || '') });
        }
        if (block.innerBlocks && block.innerBlocks.length) {
          collectHeadings(block.innerBlocks, found);
        }
      });
      return found;
    };

    // PHP の _lish_toc_build_tree() と同じ規則で入れ子にする
    const tocTree = (headings) => {
      const root = { level: 0, children: [] };
      const stack = [root];
      headings.forEach((heading) => {
        const node = { level: heading.level, text: heading.text, children: [] };
        while (stack.length > 1 && heading.level <= stack[stack.length - 1].level) {
          stack.pop();
        }
        stack[stack.length - 1].children.push(node);
        stack.push(node);
      });
      return root.children;
    };

    const renderList = (nodes, isSub) =>
      el(
        'ol',
        { className: isSub ? 'lish-toc__sub' : 'lish-toc__list' },
        nodes.map((node, index) =>
          el(
            'li',
            { key: index, className: 'lish-toc__item' },
            el('a', { href: '#' }, node.text),
            node.children.length ? renderList(node.children, true) : null
          )
        )
      );

    registerBlockType('lish/toc', {
      apiVersion: 2,
      title: __('目次', 'lish'),
      description: __('記事の見出しから目次を自動で作ります。', 'lish'),
      icon: 'list-view',
      category: 'widgets',
      attributes: {
        title: { type: 'string', default: __('目次', 'lish') },
        levels: { type: 'array', default: defaultLevels },
        toggle: { type: 'boolean', default: false },
        collapse: { type: 'boolean', default: false },
        className: { type: 'string' },
      },
      supports: { html: false, multiple: false },
      edit: ({ attributes, setAttributes }) => {
        const blocks = useSelect((select) => select('core/block-editor').getBlocks(), []);
        const levels = attributes.levels && attributes.levels.length ? attributes.levels : defaultLevels;
        const headings = collectHeadings(blocks, []).filter(
          (heading) => levels.indexOf(heading.level) !== -1 && heading.text !== ''
        );
        const blockProps = useBlockProps({
          className: 'lish-toc' + (attributes.collapse ? ' lish-toc--collapsible' : ''),
        });

        const toggleLevel = (level, checked) => {
          const next = checked ? levels.concat(level) : levels.filter((value) => value !== level);
          setAttributes({ levels: next.sort((a, b) => a - b) });
        };

        const body =
          headings.length >= minHeadings
            ? el('div', { className: 'lish-toc__body' }, renderList(tocTree(headings), false))
            : el(
                'p',
                { className: 'lish-toc__notice' },
                wp.i18n.sprintf(
                  // translators: 1: 目次を出す最小の見出し数 2: 現在の見出し数
                  __('見出しが %1$d つ以上になると目次が表示されます（現在 %2$d 件）', 'lish'),
                  minHeadings,
                  headings.length
                )
              );

        const content = attributes.toggle
          ? el(
              'details',
              { className: 'lish-toc__details', open: true },
              el('summary', { className: 'lish-toc__title' }, attributes.title),
              body
            )
          : [attributes.title ? el('p', { key: 'title', className: 'lish-toc__title' }, attributes.title) : null, el(wp.element.Fragment, { key: 'body' }, body)];

        return el(
          wp.element.Fragment,
          null,
          el(
            InspectorControls,
            null,
            el(
              PanelBody,
              { title: __('目次', 'lish') },
              el(TextControl, {
                label: __('タイトル', 'lish'),
                value: attributes.title,
                onChange: (title) => setAttributes({ title }),
                __nextHasNoMarginBottom: true,
              }),
              el('p', { className: 'components-base-control__label' }, __('目次に入れる見出し', 'lish')),
              [2, 3, 4].map((level) =>
                el(CheckboxControl, {
                  key: level,
                  label: 'H' + level,
                  checked: levels.indexOf(level) !== -1,
                  onChange: (checked) => toggleLevel(level, checked),
                  __nextHasNoMarginBottom: true,
                })
              ),
              el(ToggleControl, {
                label: __('開閉ボタンを付ける', 'lish'),
                checked: attributes.toggle,
                onChange: (toggle) => setAttributes({ toggle }),
                __nextHasNoMarginBottom: true,
              }),
              el(ToggleControl, {
                label: __('4項目以上は「もっと見る」で折りたたむ', 'lish'),
                checked: attributes.collapse,
                onChange: (collapse) => setAttributes({ collapse }),
                __nextHasNoMarginBottom: true,
              })
            )
          ),
          el('nav', blockProps, content)
        );
      },
      save: () => null,
    });
  };

  registerFormats();
  registerPostCard();
  registerToc();
})(window.wp);
