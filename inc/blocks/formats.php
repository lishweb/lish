<?php
/**
 * 文字単位の装飾（ツールバーの「Lish 装飾」メニュー）。
 *
 * 装飾そのものは assets/js/lish-blocks-editor.js が Format API で登録し、
 * <span class="lish-*"> として本文に保存される。見た目は lish-blocks.css。
 * ここでは編集画面用 JS の読み込みと、有効にする装飾の一覧を渡すだけを行う。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 親が提供する文字装飾の名前一覧（JS 側の定義と対応する）。
 *
 * @return string[]
 */
function _lish_blocks_format_names()
{
    return array(
        'lish/emphasis',
        'lish/label',
        'lish/blink',
        'lish/merit',
        'lish/demerit',
        'lish/mark-double-circle',
        'lish/mark-circle',
        'lish/mark-triangle',
        'lish/mark-cross',
        'lish/rate',
        'lish/inline-btn',
    );
}

add_action('enqueue_block_editor_assets', 'lish_blocks_enqueue_editor_script');
/**
 * 編集画面用の JS（文字装飾・記事カード・目次）を読み込み、設定値を渡す。
 *
 * 子テーマで装飾を減らす:
 *   add_filter('lish/blocks/formats', function ($names) {
 *       return array_diff($names, array('lish/blink'));
 *   });
 */
function lish_blocks_enqueue_editor_script()
{
    if (!_lish_blocks_enabled() || !file_exists(LISH_DIR . '/assets/js/lish-blocks-editor.js')) {
        return;
    }

    wp_enqueue_script(
        'lish-blocks-editor',
        lish_uri('assets/js/lish-blocks-editor.js'),
        array('wp-rich-text', 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n', 'wp-server-side-render'),
        lish_asset_version('assets/js/lish-blocks-editor.js'),
        true
    );

    $names = (array) apply_filters('lish/blocks/formats', _lish_blocks_format_names());
    wp_localize_script('lish-blocks-editor', 'lishBlocks', array(
        'formats' => array_values(array_intersect(_lish_blocks_format_names(), $names)),
        // 目次ブロック（inc/blocks/toc.php）の既定値。編集画面のプレビューを PHP の出力と揃える
        'toc'     => array(
            'levels'      => function_exists('_lish_toc_default_levels') ? _lish_toc_default_levels() : array(2, 3),
            'minHeadings' => function_exists('_lish_toc_min_headings') ? _lish_toc_min_headings() : 3,
        ),
    ));
}
