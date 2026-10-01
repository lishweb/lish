<?php
/**
 * ブロックパターンの登録。
 *
 * パターン本体は inc/blocks/patterns/{スラッグ}.php（1パターン1ファイル）。
 * 編集画面の「パターン」→「Lish ブログ」から挿入できる。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 親が提供するパターンの定義。
 *
 * キーはパターンのスラッグ（登録名は lish/{スラッグ}）。
 * requires: そのブロックが無い WordPress（古い版）では登録しない。
 *
 * @return array<string, array{title: string, requires?: string}>
 */
function _lish_blocks_pattern_definitions()
{
    return array(
        'button-triangle'   => array('title' => __('ボタン（右下三角）', 'lish')),
        'share-x'           => array('title' => __('X で共有ボタン', 'lish')),
        'copy-url'          => array('title' => __('記事の URL をコピー', 'lish')),
        'box-title'         => array('title' => __('タイトル付きボックス', 'lish')),
        'summary'           => array('title' => __('この記事でわかること', 'lish')),
        'message'           => array('title' => __('メッセージ（アイコン付き）', 'lish')),
        'check-columns'     => array('title' => __('気になる内容をチェック（2列）', 'lish')),
        'box-accent-check'  => array('title' => __('チェックリストボックス', 'lish')),
        'box-good-points'   => array('title' => __('よかったところ', 'lish')),
        'stripe-check'      => array('title' => __('斜線背景チェックリスト', 'lish')),
        'number-list'       => array('title' => __('番号リスト（縦線）', 'lish')),
        'heading-line-left' => array('title' => __('見出し：左丸線', 'lish')),
        'heading-check'     => array('title' => __('見出し：チェックアイコン', 'lish')),
        'table-head-col'    => array('title' => __('表：左列見出し', 'lish')),
        'table-compare'     => array('title' => __('比較表', 'lish')),
        'author'            => array('title' => __('著者ボックス', 'lish'), 'requires' => 'core/details'),
        'post-card'         => array('title' => __('記事カード（ラベル＋続きを読む）', 'lish'), 'requires' => 'lish/post-card'),
        'post-card-b'       => array('title' => __('記事カード（あわせて読みたい）', 'lish'), 'requires' => 'lish/post-card'),
        'related-box'       => array('title' => __('あわせて読みたいボックス', 'lish'), 'requires' => 'lish/post-card'),
        'post-list'         => array('title' => __('記事一覧（大1件＋小3件）', 'lish'), 'requires' => 'core/query'),
        'toc-simple'        => array('title' => __('目次（シンプル）', 'lish'), 'requires' => 'lish/toc'),
        'toc-band'          => array('title' => __('目次（帯タイトル＋番号・もっと見る）', 'lish'), 'requires' => 'lish/toc'),
        'toc-circle'        => array('title' => __('目次（丸番号＋縦線・開閉）', 'lish'), 'requires' => 'lish/toc'),
        'toc-minimal'       => array('title' => __('目次（ミニマル・開閉）', 'lish'), 'requires' => 'lish/toc'),
    );
}

add_action('init', 'lish_blocks_register_patterns', 20); // requires 判定のため、ブロック登録（優先度 10）の後に走らせる
/**
 * パターンカテゴリとパターンを登録する。
 */
function lish_blocks_register_patterns()
{
    if (!_lish_blocks_enabled() || !function_exists('register_block_pattern')) {
        return;
    }

    register_block_pattern_category('lish-blog', array(
        'label' => __('Lish ブログ', 'lish'),
    ));

    $definitions = _lish_blocks_pattern_definitions();
    $slugs       = (array) apply_filters('lish/blocks/patterns', array_keys($definitions));
    $registry    = WP_Block_Type_Registry::get_instance();

    foreach ($slugs as $slug) {
        if (!isset($definitions[$slug])) {
            continue;
        }
        $definition = $definitions[$slug];
        if (!empty($definition['requires']) && !$registry->is_registered($definition['requires'])) {
            continue;
        }

        $file = LISH_DIR . '/inc/blocks/patterns/' . $slug . '.php';
        if (!file_exists($file)) {
            continue;
        }
        ob_start();
        include $file;
        $content = trim((string) ob_get_clean());
        if ($content === '') {
            continue;
        }

        register_block_pattern('lish/' . $slug, array(
            'title'      => $definition['title'],
            'categories' => array('lish-blog'),
            'content'    => $content,
        ));
    }
}
