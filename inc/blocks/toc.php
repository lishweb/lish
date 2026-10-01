<?php
/**
 * 独自ブロック lish/toc（目次）。
 *
 * 記事本文の見出しブロック（core/heading）を読み取り、目次を PHP で出力する。
 * 見出しに HTML アンカーが無ければ、本文の描画前に id="lish-toc-{n}" を付ける
 * （n は本文中の見出しブロックの通し番号。目次ブロックがある記事だけで行う）。
 *
 * スタイル（ブロックスタイル）:
 *   lish-toc-simple  … シンプル（背景・矢印）※既定
 *   lish-toc-band    … 帯タイトル＋丸番号（「もっと見る」向き）
 *   lish-toc-circle  … 丸番号＋子見出しに縦線
 *   lish-toc-minimal … ミニマル（番号のみ・開閉向き）
 *
 * 編集画面の UI とプレビューは assets/js/lish-blocks-editor.js。
 * 「もっと見る」は assets/js/lish-blocks.js。見た目は lish-blocks.css。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 目次の既定の見出しレベル。
 *
 * @return int[]
 */
function _lish_toc_default_levels()
{
    $levels = array_map('intval', (array) apply_filters('lish/toc/levels', array(2, 3)));
    return array_values(array_filter($levels, function ($level) {
        return $level >= 2 && $level <= 6;
    }));
}

/**
 * 目次を出す最小の見出し数（これ未満なら目次を出さない）。
 *
 * @return int
 */
function _lish_toc_min_headings()
{
    return max(1, (int) apply_filters('lish/toc/min_headings', 3));
}

add_action('init', 'lish_blocks_register_toc');
/**
 * lish/toc を登録する。
 */
function lish_blocks_register_toc()
{
    if (!_lish_blocks_enabled()) {
        return;
    }

    register_block_type('lish/toc', array(
        'api_version'     => 2,
        'title'           => __('目次', 'lish'),
        'category'        => 'widgets',
        'attributes'      => array(
            'title'     => array('type' => 'string', 'default' => __('目次', 'lish')),
            'levels'    => array('type' => 'array', 'default' => _lish_toc_default_levels(), 'items' => array('type' => 'integer')),
            'toggle'    => array('type' => 'boolean', 'default' => false),
            'collapse'  => array('type' => 'boolean', 'default' => false),
            'className' => array('type' => 'string'),
        ),
        'supports'        => array('html' => false, 'multiple' => false),
        'render_callback' => 'lish_blocks_render_toc',
    ));

    $styles = array(
        'lish-toc-simple'  => __('シンプル', 'lish'),
        'lish-toc-band'    => __('帯タイトル＋番号', 'lish'),
        'lish-toc-circle'  => __('丸番号＋縦線', 'lish'),
        'lish-toc-minimal' => __('ミニマル', 'lish'),
    );
    foreach ($styles as $name => $label) {
        register_block_style('lish/toc', array(
            'name'       => $name,
            'label'      => $label,
            'is_default' => $name === 'lish-toc-simple',
        ));
    }
}

/**
 * 本文の見出しブロックを、出現順に集める（入れ子のブロックの中も探す）。
 *
 * @param array $blocks parse_blocks() の結果。
 * @param array $found  集めた見出し（参照渡し）。
 * @return void
 */
function _lish_toc_collect_headings($blocks, &$found)
{
    foreach ($blocks as $block) {
        if ($block['blockName'] === 'core/heading') {
            $index = count($found) + 1;
            $found[] = array(
                'level' => isset($block['attrs']['level']) ? (int) $block['attrs']['level'] : 2,
                'text'  => trim(wp_strip_all_tags((string) $block['innerHTML'])),
                'id'    => !empty($block['attrs']['anchor']) ? (string) $block['attrs']['anchor'] : 'lish-toc-' . $index,
            );
        }
        if (!empty($block['innerBlocks'])) {
            _lish_toc_collect_headings($block['innerBlocks'], $found);
        }
    }
}

add_filter('the_content', 'lish_toc_add_heading_ids', 8);
/**
 * 目次ブロックがある本文の見出しに id を付ける（do_blocks より前に動かす）。
 *
 * @param string $content 本文（ブロックのマークアップ）。
 * @return string
 */
function lish_toc_add_heading_ids($content)
{
    if (!_lish_blocks_enabled() || !class_exists('WP_HTML_Tag_Processor') || strpos($content, '<!-- wp:lish/toc') === false) {
        return $content;
    }

    $counter = 0;
    $blocks  = _lish_toc_add_ids_to_blocks(parse_blocks($content), $counter);
    return serialize_blocks($blocks);
}

/**
 * 見出しブロックに id を付ける（_lish_toc_collect_headings と同じ通し番号を使う）。
 *
 * @param array $blocks  ブロック。
 * @param int   $counter 見出しの通し番号（参照渡し）。
 * @return array
 */
function _lish_toc_add_ids_to_blocks($blocks, &$counter)
{
    foreach ($blocks as $i => $block) {
        if ($block['blockName'] === 'core/heading') {
            $counter++;
            if (empty($block['attrs']['anchor']) && !empty($block['innerContent'][0])) {
                $tags = new WP_HTML_Tag_Processor($block['innerContent'][0]);
                if ($tags->next_tag() && $tags->get_attribute('id') === null) {
                    $tags->set_attribute('id', 'lish-toc-' . $counter);
                    $blocks[$i]['innerContent'][0] = $tags->get_updated_html();
                    $blocks[$i]['innerHTML']       = $blocks[$i]['innerContent'][0];
                }
            }
        }
        if (!empty($block['innerBlocks'])) {
            $blocks[$i]['innerBlocks'] = _lish_toc_add_ids_to_blocks($block['innerBlocks'], $counter);
        }
    }
    return $blocks;
}

/**
 * 見出しの平らな一覧を、レベルに応じた木構造にする。
 *
 * 直前の見出しよりレベルが深ければその子にする（h2 → h4 のような飛び級もそのまま子にする）。
 * 編集画面のプレビュー（lish-blocks-editor.js の tocTree）と同じ規則。
 *
 * @param array $headings 見出し（level / text / id）。
 * @return array
 */
function _lish_toc_build_tree($headings)
{
    $root  = array('level' => 0, 'children' => array());
    $stack = array(&$root);

    foreach ($headings as $heading) {
        $heading['children'] = array();
        while (count($stack) > 1 && $heading['level'] <= $stack[count($stack) - 1]['level']) {
            array_pop($stack);
        }
        $parent = &$stack[count($stack) - 1];
        $parent['children'][] = $heading;
        $stack[] = &$parent['children'][count($parent['children']) - 1];
        unset($parent);
    }
    return $root['children'];
}

/**
 * 木構造の見出しを入れ子の <ol> にする。
 *
 * @param array $nodes  見出し（children を持つ）。
 * @param bool  $is_sub 子の一覧か。
 * @return string
 */
function _lish_toc_render_list($nodes, $is_sub = false)
{
    $html = '<ol class="' . ($is_sub ? 'lish-toc__sub' : 'lish-toc__list') . '">';
    foreach ($nodes as $node) {
        $html .= '<li class="lish-toc__item"><a href="#' . esc_attr($node['id']) . '">' . esc_html($node['text']) . '</a>';
        if (!empty($node['children'])) {
            $html .= _lish_toc_render_list($node['children'], true);
        }
        $html .= '</li>';
    }
    return $html . '</ol>';
}

/**
 * lish/toc の描画。
 *
 * @param array $attributes ブロック属性。
 * @return string
 */
function lish_blocks_render_toc($attributes)
{
    $post = get_post();
    if (!$post instanceof WP_Post) {
        return '';
    }

    $levels = !empty($attributes['levels']) ? array_map('intval', (array) $attributes['levels']) : _lish_toc_default_levels();
    $found  = array();
    _lish_toc_collect_headings(parse_blocks($post->post_content), $found);

    $headings = array_values(array_filter($found, function ($heading) use ($levels) {
        return in_array($heading['level'], $levels, true) && $heading['text'] !== '';
    }));
    if (count($headings) < _lish_toc_min_headings()) {
        return '';
    }

    $title    = isset($attributes['title']) ? (string) $attributes['title'] : __('目次', 'lish');
    $toggle   = !empty($attributes['toggle']);
    $collapse = !empty($attributes['collapse']);
    $list     = _lish_toc_render_list(_lish_toc_build_tree($headings));

    $classes = 'lish-toc';
    if ($collapse) {
        $classes .= ' lish-toc--collapsible js-lish-toc';
        _lish_blocks_enqueue_front_script();
    }

    ob_start();
    ?>
<nav <?php echo get_block_wrapper_attributes(array('class' => $classes, 'aria-label' => $title !== '' ? $title : __('目次', 'lish'))); ?>>
  <?php if ($toggle) : ?>
    <details class="lish-toc__details" open>
      <summary class="lish-toc__title"><?php echo esc_html($title); ?></summary>
      <div class="lish-toc__body"><?php echo $list; // _lish_toc_render_list() 内でエスケープ済み ?></div>
    </details>
  <?php else : ?>
    <?php if ($title !== '') : ?>
      <p class="lish-toc__title"><?php echo esc_html($title); ?></p>
    <?php endif; ?>
    <div class="lish-toc__body"><?php echo $list; // _lish_toc_render_list() 内でエスケープ済み ?></div>
  <?php endif; ?>
  <?php if ($collapse) : ?>
    <button type="button" class="lish-toc__more js-lish-toc-more" hidden><?php esc_html_e('もっと見る', 'lish'); ?></button>
  <?php endif; ?>
</nav>
    <?php
    return (string) ob_get_clean();
}
