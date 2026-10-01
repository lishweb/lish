<?php
/**
 * 本文まわりの制御。
 *
 * 移行元: base-theme functions.php L14-37
 *   - wpautop_filter()（固定ページの自動整形抑止）
 *   - ブロックエディタ無効化（コメントアウトのサンプル）
 *
 * 変更点:
 *   - `wpautop_filter()` → `lish_disable_wpautop()` に改名（WP コア関数と紛らわしい名前を解消）
 *   - 対象投稿タイプを `lish/wpautop_disabled_post_types` フィルタ化
 *   - ブロックエディタ無効化を `lish/disable_block_editor_slugs` フィルタで宣言する方式に変更
 *     （コメントを手で外す運用をやめた）
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('the_content', 'lish_disable_wpautop', 9);
/**
 * 指定した投稿タイプで自動整形（<p> / <br> 付与）を抑止する。
 *
 * 既定は 'page'（固定ページはテンプレートで直書きするため）。
 * 案件で対象を変えたい場合:
 *   add_filter('lish/wpautop_disabled_post_types', function () { return array('page', 'news'); });
 * 抑止自体を無効にしたい場合は空配列を返す。
 *
 * @param string $content 本文。
 * @return string 変更せずそのまま返す（副作用としてフィルタを外すだけ）。
 */
function lish_disable_wpautop($content)
{
    $post_types = apply_filters('lish/wpautop_disabled_post_types', array('page'));

    if (empty($post_types)) {
        return $content;
    }

    $post = get_post();
    if (!$post instanceof WP_Post) {
        return $content;
    }

    if (in_array($post->post_type, (array) $post_types, true)) {
        remove_filter('the_content', 'wpautop');
        remove_filter('the_excerpt', 'wpautop');
    }

    return $content;
}

add_filter('use_block_editor_for_post', 'lish_maybe_disable_block_editor', 10, 2);
/**
 * 特定スラッグの固定ページでブロックエディタを無効化する。
 *
 * 子テーマ側で対象スラッグを宣言する:
 *   add_filter('lish/disable_block_editor_slugs', function () { return array('top', 'company'); });
 *
 * 全固定ページで無効にしたい場合:
 *   add_filter('lish/disable_block_editor_post_types', function () { return array('page'); });
 *
 * @param bool    $use_block_editor 現在の判定。
 * @param WP_Post $post             対象投稿。
 * @return bool
 */
function lish_maybe_disable_block_editor($use_block_editor, $post)
{
    if (!$post instanceof WP_Post) {
        return $use_block_editor;
    }

    $post_types = apply_filters('lish/disable_block_editor_post_types', array());
    if (!empty($post_types) && in_array($post->post_type, (array) $post_types, true)) {
        return false;
    }

    $slugs = apply_filters('lish/disable_block_editor_slugs', array());
    if (!empty($slugs) && in_array($post->post_name, (array) $slugs, true)) {
        return false;
    }

    return $use_block_editor;
}
