<?php
/**
 * <head> 内の共通メタ出力。
 *
 * 移行元: base-theme header.php L9-71（OGP / Twitter Card / favicon / Webフォント）
 *
 * 変更点:
 *   - header.php 直書き → 関数化（子が header.php を持たなくても更新が届くようにする）
 *   - 画像パスを get_template_directory_uri()（親） → get_stylesheet_directory_uri()（子）へ変更
 *   - 全ての値を `lish/ogp/*` フィルタで子から差し替え可能にした
 *   - is_singular 時の setup_postdata() 呼び出しを削除（グローバル汚染の元。メインクエリでは不要）
 *
 * ※ v1.0 では base-theme にあった範囲のみを整理している。
 *    JSON-LD 構造化データ / canonical / robots は 1.1 以降の追加候補。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * <head> 内に Lish が出力する共通メタ一式。
 *
 * 親テーマの header.php から呼ばれる。子が header.php を持たない限り、
 * ここへの改善は親テーマの更新だけで全案件に届く。
 */
function lish_head_meta()
{
    lish_render_meta_charset();
    lish_render_ogp();
    lish_render_icons();
    lish_render_webfonts();
}

/**
 * charset / viewport / format-detection。
 */
function lish_render_meta_charset()
{
    echo '<meta charset="' . esc_attr(get_bloginfo('charset')) . '">' . "\n";
    echo '<meta name="viewport" content="width=device-width,initial-scale=1.0">' . "\n";
    if (apply_filters('lish/disable_telephone_detection', true)) {
        echo '<meta name="format-detection" content="telephone=no">' . "\n";
    }
}

/**
 * 現在のページの OGP 情報を配列で返す。
 *
 * 子テーマはこの配列を `lish/ogp` フィルタで丸ごと差し替えられる。
 *
 * @return array{title:string,description:string,type:string,url:string,image:string,site_name:string}
 */
function lish_get_ogp_data()
{
    $site_name = get_bloginfo('name');

    // description は管理画面「設定 > 一般 > キャッチフレーズ」を優先。
    $default_description = apply_filters('lish/ogp/default_description', get_bloginfo('description'));

    // OGP デフォルト画像は子テーマ（案件）側に置く。
    $default_image = apply_filters(
        'lish/ogp/default_image',
        get_stylesheet_directory_uri() . '/assets/img/common/og-image.jpg'
    );

    if (is_singular() && !is_front_page()) {
        $description = get_the_excerpt();
        if (empty($description)) {
            $description = $default_description;
        }
        $data = array(
            'title'       => get_the_title(),
            'description' => $description,
            'type'        => 'article',
            'url'         => get_permalink(),
            'image'       => has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : $default_image,
            'site_name'   => $site_name,
        );
    } else {
        $data = array(
            'title'       => $site_name,
            'description' => $default_description,
            'type'        => 'website',
            'url'         => home_url('/'),
            'image'       => $default_image,
            'site_name'   => $site_name,
        );
    }

    return apply_filters('lish/ogp', $data);
}

/**
 * description / OGP / Twitter Card を出力する。
 */
function lish_render_ogp()
{
    if (!apply_filters('lish/enable_ogp', true)) {
        return;
    }

    $ogp = lish_get_ogp_data();

    if (!empty($ogp['description'])) {
        echo '<meta name="description" content="' . esc_attr($ogp['description']) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($ogp['description']) . '">' . "\n";
    }
    echo '<meta property="og:title" content="' . esc_attr($ogp['title']) . '">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr($ogp['type']) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($ogp['url']) . '">' . "\n";
    if (!empty($ogp['image'])) {
        echo '<meta property="og:image" content="' . esc_url($ogp['image']) . '">' . "\n";
    }
    echo '<meta property="og:site_name" content="' . esc_attr($ogp['site_name']) . '">' . "\n";

    // Facebook（案件で必要なときだけフィルタで設定する）
    $fb_admins = apply_filters('lish/ogp/fb_admins', '');
    if (!empty($fb_admins)) {
        echo '<meta property="fb:admins" content="' . esc_attr($fb_admins) . '">' . "\n";
    }
    $fb_app_id = apply_filters('lish/ogp/fb_app_id', '');
    if (!empty($fb_app_id)) {
        echo '<meta property="fb:app_id" content="' . esc_attr($fb_app_id) . '">' . "\n";
    }

    // Twitter Card
    echo '<meta name="twitter:card" content="' . esc_attr(apply_filters('lish/ogp/twitter_card', 'summary_large_image')) . '">' . "\n";
    $twitter_site = apply_filters('lish/ogp/twitter_site', ''); // '@' は不要
    if (!empty($twitter_site)) {
        echo '<meta name="twitter:site" content="@' . esc_attr(ltrim($twitter_site, '@')) . '">' . "\n";
    }
}

/**
 * favicon / apple-touch-icon を出力する。
 *
 * ファイルは子テーマ（案件）側に置く。存在しなければ出力しない。
 */
function lish_render_icons()
{
    $child_dir = get_stylesheet_directory();

    $favicon = apply_filters('lish/favicon', '/assets/img/icon/favicon.ico');
    if ($favicon && file_exists($child_dir . $favicon)) {
        echo '<link rel="shortcut icon" href="' . esc_url(get_stylesheet_directory_uri() . $favicon) . '">' . "\n";
    }

    $touch_icon = apply_filters('lish/touch_icon', '/assets/img/icon/touch.png');
    if ($touch_icon && file_exists($child_dir . $touch_icon)) {
        echo '<link rel="apple-touch-icon-precomposed" href="' . esc_url(get_stylesheet_directory_uri() . $touch_icon) . '">' . "\n";
    }
}
