<?php
/**
 * ブログ用ブロック（ブロックスタイル / パターン / 独自ブロック）の基盤。
 *
 * 記事本文で使う装飾を、WordPress 標準ブロック + ブロックスタイル + パターンで提供する。
 * 見た目は assets/css/lish-blocks.css（ソース: assets/scss/lish-blocks.scss）に集約し、
 * フロントと編集画面の両方に読み込んで「書いている時点で完成形と同じ見た目」にする。
 *
 * 色は CSS 変数 --lish-blocks-* の既定値だけを親が持つ。
 * 案件の色は子テーマの app.css で :root の変数を上書きする。
 *
 * 子テーマからの制御:
 *   add_filter('lish/blocks/enable', '__return_false');           // 機能ごと止める
 *   add_filter('lish/blocks/patterns', function ($slugs) { ... }); // パターンを減らす
 *   add_filter('lish/blocks/formats', function ($names) { ... });  // 文字装飾を減らす
 *   add_filter('lish/blocks/enqueue_front', '__return_true');      // フロントで常に読む
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ブログ用ブロック機能が有効か。
 *
 * 子の functions.php は親より先に読まれるため、トップレベルの add_filter も init 時点では効いている。
 *
 * @return bool
 */
function _lish_blocks_enabled()
{
    return (bool) apply_filters('lish/blocks/enable', true);
}

foreach (array('styles.php', 'patterns.php', 'formats.php', 'share.php', 'post-card.php', 'toc.php') as $lish_blocks_module) {
    $lish_blocks_module_path = LISH_DIR . '/inc/blocks/' . $lish_blocks_module;
    if (file_exists($lish_blocks_module_path)) {
        require_once $lish_blocks_module_path;
    }
}
unset($lish_blocks_module, $lish_blocks_module_path);

add_action('init', 'lish_blocks_register_assets', 5);
/**
 * CSS を register する（enqueue はフロント / 編集画面それぞれのフックで行う）。
 */
function lish_blocks_register_assets()
{
    if (!_lish_blocks_enabled() || !file_exists(LISH_DIR . '/assets/css/lish-blocks.css')) {
        return;
    }
    wp_register_style(
        'lish-blocks',
        lish_uri('assets/css/lish-blocks.css'),
        array(),
        lish_asset_version('assets/css/lish-blocks.css')
    );

    // フロント用 JS（URL コピーなど）。使うブロックが描画されたときだけ enqueue する。
    if (file_exists(LISH_DIR . '/assets/js/lish-blocks.js')) {
        wp_register_script(
            'lish-blocks',
            lish_uri('assets/js/lish-blocks.js'),
            array(),
            lish_asset_version('assets/js/lish-blocks.js'),
            true
        );
        wp_localize_script('lish-blocks', 'lishBlocksFront', array(
            'copied'     => __('コピーしました！', 'lish'),
            'copyFailed' => __('コピーできませんでした', 'lish'),
        ));
    }
}

/**
 * フロント用 JS を読み込む（ブロックの描画時に呼ぶ）。
 *
 * 本文の描画は wp_footer より前なので、ここで enqueue してもフッターに出力される。
 */
function _lish_blocks_enqueue_front_script()
{
    if (!is_admin() && wp_script_is('lish-blocks', 'registered')) {
        wp_enqueue_script('lish-blocks');
    }
}

add_filter('lish/defer_handles', 'lish_blocks_defer_handles');
/**
 * フロント用 JS に defer を付ける。
 *
 * @param string[] $handles defer 対象のハンドル。
 * @return string[]
 */
function lish_blocks_defer_handles($handles)
{
    $handles[] = 'lish-blocks';
    return $handles;
}

add_action('wp_enqueue_scripts', 'lish_blocks_enqueue_front', 6);
/**
 * フロントで CSS を読み込む。
 *
 * 優先度 6: 親の lish-entry-content（優先度 5）の後に出力し、同じ詳細度なら lish-blocks が勝つようにする。
 * 既定は投稿・固定ページの詳細のみ。一覧で本文を出す案件は lish/blocks/enqueue_front で広げる。
 */
function lish_blocks_enqueue_front()
{
    if (!wp_style_is('lish-blocks', 'registered')) {
        return;
    }
    if (apply_filters('lish/blocks/enqueue_front', is_singular())) {
        wp_enqueue_style('lish-blocks');
    }
}

add_action('enqueue_block_assets', 'lish_blocks_enqueue_editor');
/**
 * 編集画面で CSS を読み込む。
 *
 * enqueue_block_assets は WP 6.3 以降、編集画面の iframe の中にも出力される。
 * フロントは lish_blocks_enqueue_front() が担当するので、ここでは管理画面だけに限定する。
 */
function lish_blocks_enqueue_editor()
{
    if (is_admin() && wp_style_is('lish-blocks', 'registered')) {
        wp_enqueue_style('lish-blocks');
    }
}
