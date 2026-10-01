<?php
/**
 * テーマ初期設定。
 *
 * 移行元: base-theme functions.php L3-12（add_theme_support / register_nav_menu / image size）
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', 'lish_setup');
/**
 * テーマサポートとナビメニューの登録。
 */
function lish_setup()
{
    // 翻訳ファイル（親）
    load_theme_textdomain('lish', LISH_DIR . '/languages');

    // ===== テーマサポート =====
    // 子テーマから増減させたい場合は `lish/theme_supports` フィルタを使う。
    $supports = apply_filters('lish/theme_supports', array(
        'post-thumbnails'       => true,
        'title-tag'             => true,
        'automatic-feed-links'  => true,
        'responsive-embeds'     => true,
        'html5'                 => array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'),
    ));

    foreach ($supports as $feature => $args) {
        if ($args === false) {
            continue;
        }
        if ($args === true) {
            add_theme_support($feature);
        } else {
            add_theme_support($feature, $args);
        }
    }

    // ===== ナビゲーションメニュー =====
    // 本テーマの標準方針ではグローバルナビを直書きするため必須ではないが、
    // 管理画面からメニューを編集したい案件のために登録だけしておく。
    $menus = apply_filters('lish/nav_menus', array(
        'primary' => __('グローバルナビ', 'lish'),
        'footer'  => __('フッターナビ', 'lish'),
    ));
    if (!empty($menus)) {
        register_nav_menus($menus);
    }

    // ===== 追加画像サイズ =====
    // 子テーマは親を編集せず、このフィルタでサイズを追加する。
    //   add_filter('lish/image_sizes', function ($sizes) {
    //       $sizes['card'] = array('width' => 600, 'height' => 400, 'crop' => true);
    //       return $sizes;
    //   });
    $image_sizes = apply_filters('lish/image_sizes', array());
    foreach ($image_sizes as $name => $size) {
        add_image_size(
            $name,
            isset($size['width']) ? (int) $size['width'] : 0,
            isset($size['height']) ? (int) $size['height'] : 0,
            isset($size['crop']) ? $size['crop'] : false
        );
    }
}

add_action('after_setup_theme', 'lish_load_child_textdomain', 11);
/**
 * 子テーマの翻訳ファイル読み込み。
 *
 * 子テーマ側で languages/ を用意すれば自動で読まれる（子は何も書かなくてよい）。
 */
function lish_load_child_textdomain()
{
    if (!is_child_theme()) {
        return;
    }
    $domain = wp_get_theme()->get('TextDomain');
    if ($domain && $domain !== 'lish') {
        load_child_theme_textdomain($domain, get_stylesheet_directory() . '/languages');
    }
}

/**
 * 子テーマかどうかに関わらず「案件側テーマ」のディレクトリを返すヘルパー。
 *
 * 案件アセット（画像・フォント）のパスはこれ、または get_stylesheet_directory_uri() を使う。
 * get_template_directory_uri() は親テーマ（Lish）を指すので案件アセットには使わない。
 *
 * ※ `lish_child_*` は子テーマ側が使うプレフィックスとして予約しているため、
 *    親テーマの関数名には使わない（衝突すると Fatal になる）。
 *
 * @param string $path テーマルートからの相対パス（先頭スラッシュ有無どちらでも可）。
 * @return string
 */
function lish_project_uri($path = '')
{
    $path = ltrim((string) $path, '/');
    return $path === '' ? get_stylesheet_directory_uri() : get_stylesheet_directory_uri() . '/' . $path;
}

/**
 * 親テーマ（Lish）のアセット URI を返すヘルパー。
 *
 * @param string $path テーマルートからの相対パス。
 * @return string
 */
function lish_uri($path = '')
{
    $path = ltrim((string) $path, '/');
    return $path === '' ? LISH_URI : LISH_URI . '/' . $path;
}
