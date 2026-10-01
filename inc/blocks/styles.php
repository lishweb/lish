<?php
/**
 * ブロックスタイルの登録。
 *
 * 編集画面のサイドバー「スタイル」から選べる見た目。出力クラスは is-style-{name}。
 * CSS は assets/scss/lish-blocks.scss（クラス名は公開 API。変更は MAJOR）。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'lish_blocks_register_styles');
/**
 * ブロックスタイルを登録する。
 *
 * 子テーマで個別に外す場合は unregister_block_style() を init（優先度 11 以降）で呼ぶ。
 */
function lish_blocks_register_styles()
{
    if (!_lish_blocks_enabled()) {
        return;
    }

    $styles = array(
        'core/button' => array(
            'lish-triangle' => __('三角付き', 'lish'),
            'lish-share-x'  => __('X で共有', 'lish'),
            'lish-copy-url' => __('URL コピー', 'lish'),
        ),
        'core/group' => array(
            'lish-box-title'   => __('タイトル付きボックス', 'lish'),
            'lish-box-corner'  => __('角タイトル付きボックス', 'lish'),
            'lish-box-summary' => __('要点ボックス', 'lish'),
            'lish-box-check'   => __('チェックボックス（斜線・枠）', 'lish'),
            'lish-box-accent'  => __('アクセント枠', 'lish'),
            'lish-box-stripe'  => __('斜線背景', 'lish'),
            'lish-box-message' => __('メッセージ', 'lish'),
        ),
        'core/list' => array(
            'lish-check'       => __('チェック', 'lish'),
            'lish-number-line' => __('番号＋縦線', 'lish'),
        ),
        'core/heading' => array(
            'lish-line-left'  => __('左丸線', 'lish'),
            'lish-check-icon' => __('チェックアイコン', 'lish'),
        ),
        'core/table' => array(
            'lish-head-col' => __('左列見出し', 'lish'),
            'lish-compare'  => __('比較表', 'lish'),
        ),
    );

    foreach ($styles as $block => $items) {
        foreach ($items as $name => $label) {
            register_block_style($block, array(
                'name'  => $name,
                'label' => $label,
            ));
        }
    }
}
