<?php
/**
 * 共通テンプレートタグ。
 *
 * 移行元:
 *   - base-theme inc/part-breadcrumb.php（my_theme_breadcrumbs）→ lish_breadcrumb()
 *   - base-theme functions.php L192-206（get_the_custom_excerpt）→ lish_excerpt()
 *   - base-theme archive.php L20（the_posts_pagination 直書き）→ lish_pagination()
 *
 * 変更点:
 *   - `lish_` プレフィックスへ統一（グローバル名前空間の衝突回避）
 *   - パンくずを「データ取得」と「描画」に分離。
 *     データは `lish/breadcrumb/items` フィルタ、描画は inc/template-parts/breadcrumb.php で差し替え可能。
 *   - CSS クラスを `p-breadcrumb` → `c-breadcrumb` に変更（親提供 = c- が妥当なため）
 *   - schema.org の URL を http → https に修正
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

/* -------------------------------------------------------------------------
 * 抜粋
 * ---------------------------------------------------------------------- */

/**
 * 本文から指定文字数の抜粋を生成する。
 *
 * @param string $content 元の本文。
 * @param int    $length  文字数。
 * @param string $more    末尾に付ける文字列。
 * @return string
 */
function lish_excerpt($content, $length = 70, $more = '...')
{
    $content = preg_replace('/<!--more-->.+/is', '', (string) $content);
    $content = strip_shortcodes($content);
    $content = wp_strip_all_tags($content);
    $content = str_replace('&nbsp;', '', $content);
    $content = trim($content);

    $content_length = function_exists('grapheme_strlen') ? grapheme_strlen($content) : mb_strlen($content);
    $trimmed        = mb_substr($content, 0, $length);

    return ($content_length > $length) ? $trimmed . $more : $trimmed;
}

/* -------------------------------------------------------------------------
 * ページネーション
 * ---------------------------------------------------------------------- */

/**
 * ページネーションを出力する。
 *
 * @param array $args the_posts_pagination() の引数を上書きする配列。
 */
function lish_pagination($args = array())
{
    $defaults = array(
        'mid_size'           => 2,
        'prev_text'          => __('前へ', 'lish'),
        'next_text'          => __('次へ', 'lish'),
        'screen_reader_text' => __('ページ送り', 'lish'),
        'class'              => 'c-pagination',
    );

    $args = apply_filters('lish/pagination/args', wp_parse_args($args, $defaults));

    the_posts_pagination($args);
}

/* -------------------------------------------------------------------------
 * パンくず
 * ---------------------------------------------------------------------- */

/**
 * パンくずの項目リストを返す。
 *
 * 各項目は array('label' => string, 'url' => string|'')。
 * 最後の項目（現在地）は url が空文字。
 *
 * @return array<int, array{label:string, url:string}>
 */
function lish_get_breadcrumb_items()
{
    $items = array();

    if (apply_filters('lish/breadcrumb/show_home', true)) {
        $items[] = array(
            'label' => apply_filters('lish/breadcrumb/home_label', __('ホーム', 'lish')),
            'url'   => home_url('/'),
        );
    }

    if (is_front_page()) {
        return apply_filters('lish/breadcrumb/items', $items);
    }

    if (is_singular() && !is_front_page()) {
        $post_type = get_post_type();

        if (is_page()) {
            // 固定ページ: 祖先ページを辿る
            $post = get_post();
            if ($post && $post->post_parent) {
                foreach (array_reverse(get_post_ancestors($post->ID)) as $ancestor) {
                    $items[] = array('label' => get_the_title($ancestor), 'url' => get_permalink($ancestor));
                }
            }
        } elseif ($post_type === 'post') {
            // 投稿: 主カテゴリの祖先を辿る
            $categories = get_the_category();
            if (!empty($categories)) {
                $cat = $categories[0];
                foreach (array_reverse(get_ancestors($cat->term_id, 'category')) as $ancestor) {
                    $items[] = array('label' => get_cat_name($ancestor), 'url' => get_category_link($ancestor));
                }
                $items[] = array('label' => $cat->name, 'url' => get_category_link($cat->term_id));
            }
        } else {
            // カスタム投稿タイプ: アーカイブがあれば挟む
            $post_type_obj = get_post_type_object($post_type);
            if ($post_type_obj && $post_type_obj->has_archive) {
                $items[] = array(
                    'label' => $post_type_obj->labels->singular_name,
                    'url'   => get_post_type_archive_link($post_type),
                );
            }
        }

        $items[] = array('label' => get_the_title(), 'url' => '');
    } elseif (is_category()) {
        $category = get_queried_object();
        if ($category instanceof WP_Term) {
            foreach (array_reverse(get_ancestors($category->term_id, 'category')) as $ancestor) {
                $items[] = array('label' => get_cat_name($ancestor), 'url' => get_category_link($ancestor));
            }
            $items[] = array('label' => $category->name, 'url' => '');
        }
    } elseif (is_tag()) {
        $items[] = array('label' => single_tag_title('', false), 'url' => '');
    } elseif (is_tax()) {
        $items[] = array('label' => single_term_title('', false), 'url' => '');
    } elseif (is_year()) {
        $items[] = array('label' => get_the_time('Y年'), 'url' => '');
    } elseif (is_month()) {
        $items[] = array('label' => get_the_time('Y年'), 'url' => get_year_link(get_the_time('Y')));
        $items[] = array('label' => get_the_time('n月'), 'url' => '');
    } elseif (is_day()) {
        $items[] = array('label' => get_the_time('Y年'), 'url' => get_year_link(get_the_time('Y')));
        $items[] = array('label' => get_the_time('n月'), 'url' => get_month_link(get_the_time('Y'), get_the_time('m')));
        $items[] = array('label' => get_the_time('j日'), 'url' => '');
    } elseif (is_author()) {
        $items[] = array('label' => get_the_author(), 'url' => '');
    } elseif (is_search()) {
        /* translators: %s: search query */
        $items[] = array('label' => sprintf(__('「%s」の検索結果', 'lish'), get_search_query()), 'url' => '');
    } elseif (is_404()) {
        $items[] = array('label' => __('404 Not Found', 'lish'), 'url' => '');
    } elseif (is_post_type_archive()) {
        $items[] = array('label' => post_type_archive_title('', false), 'url' => '');
    } elseif (is_archive()) {
        $items[] = array('label' => get_the_archive_title(), 'url' => '');
    }

    return apply_filters('lish/breadcrumb/items', $items);
}

/**
 * パンくずを出力する。
 *
 * 描画テンプレートは inc/template-parts/breadcrumb.php。
 * 子テーマで見た目の構造ごと差し替えたい場合は、子に
 * inc/template-parts/breadcrumb.php を置けばそちらが使われる
 * （ただしその時点で親の更新はこのファイルに届かなくなる点に注意）。
 */
function lish_breadcrumb()
{
    if (is_front_page() && !apply_filters('lish/breadcrumb/show_on_front', false)) {
        return;
    }

    $items = lish_get_breadcrumb_items();
    if (count($items) < 1) {
        return;
    }

    get_template_part('inc/template-parts/breadcrumb', null, array('items' => $items));
}

/* -------------------------------------------------------------------------
 * サムネイル
 * ---------------------------------------------------------------------- */

/**
 * アイキャッチ画像を出力する（LCP 候補には fetchpriority="high" を付ける）。
 *
 * @param bool   $is_lcp LCP 候補（ファーストビュー1枚目）かどうか。
 * @param string $size   画像サイズ。
 * @param array  $attr   追加属性。
 */
function lish_post_thumbnail($is_lcp = false, $size = 'large', $attr = array())
{
    if (!has_post_thumbnail()) {
        return;
    }

    if ($is_lcp) {
        // LCP 候補は lazy を外し優先度を上げる
        $attr = array_merge(array('fetchpriority' => 'high', 'loading' => false, 'decoding' => 'async'), $attr);
    } else {
        $attr = array_merge(array('loading' => 'lazy', 'decoding' => 'async'), $attr);
    }

    the_post_thumbnail($size, $attr);
}
