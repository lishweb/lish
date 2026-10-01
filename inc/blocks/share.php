<?php
/**
 * 共有系ボタン（core/button のブロックスタイル）。
 *
 * - is-style-lish-share-x : リンク先を「この記事を X で共有する URL」に差し替える（JS 不要）
 * - is-style-lish-copy-url: リンク先をこの記事の URL にし、フロント JS がクリック時に URL をコピーする
 *
 * リンク先は描画時に PHP で決めるので、書き手はボタンの URL を入力しなくてよい。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('render_block_core/button', 'lish_blocks_render_share_button', 10, 2);
/**
 * 共有系ボタンのリンク先を差し替える。
 *
 * @param string $content 描画済みの HTML。
 * @param array  $block   ブロック。
 * @return string
 */
function lish_blocks_render_share_button($content, $block)
{
    $class = isset($block['attrs']['className']) ? (string) $block['attrs']['className'] : '';
    $is_x    = strpos($class, 'is-style-lish-share-x') !== false;
    $is_copy = strpos($class, 'is-style-lish-copy-url') !== false;
    if ((!$is_x && !$is_copy) || !_lish_blocks_enabled()) {
        return $content;
    }

    // WP_HTML_Tag_Processor は WP 6.2〜。無い環境では書き手が入れたリンクのまま出す
    $post = get_post();
    if (!$post instanceof WP_Post || !class_exists('WP_HTML_Tag_Processor')) {
        return $content;
    }

    $permalink = get_permalink($post);
    $tags      = new WP_HTML_Tag_Processor($content);
    if (!$tags->next_tag(array('tag_name' => 'a'))) {
        return $content;
    }

    if ($is_x) {
        $share_url = add_query_arg(
            array(
                'url'  => rawurlencode($permalink),
                'text' => rawurlencode(wp_strip_all_tags(get_the_title($post))),
            ),
            'https://x.com/intent/post'
        );
        /**
         * X 共有 URL を差し替える。
         *
         * @param string  $share_url 生成した共有 URL。
         * @param WP_Post $post      対象の記事。
         */
        $share_url = (string) apply_filters('lish/share/x_url', $share_url, $post);

        $tags->set_attribute('href', esc_url($share_url));
        $tags->set_attribute('target', '_blank');
        $tags->set_attribute('rel', 'noopener noreferrer');
        return $tags->get_updated_html();
    }

    // URL コピー: JS が無くても記事へのリンクとして成立させる
    $tags->set_attribute('href', esc_url($permalink));
    $tags->set_attribute('role', 'button');
    _lish_blocks_enqueue_front_script();
    return $tags->get_updated_html();
}
