<?php
/**
 * アセット読み込み基盤。
 *
 * 移行元: base-theme functions.php L51-113
 *   - jQuery の deregister
 *   - ajaxzip3 / Splide / script.js / destyle.css / entry-content.css / splide-core.css の enqueue
 *   - script_loader_tag による defer 付与
 *
 * 変更点:
 *   - ハンドル名を `lish-*` に統一
 *   - Splide / ajaxzip3 は「register だけして、必要な案件が enqueue 関数を呼ぶ」方式に変更
 *   - defer 対象ハンドルを `lish/defer_handles` フィルタで子から追加できるようにした
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 親テーマ内ファイルの更新時刻をバージョン文字列として返す。
 *
 * ファイルが無い場合は LISH_VERSION にフォールバックする（filemtime の Warning を避ける）。
 *
 * @param string $relative_path 親テーマルートからの相対パス。
 * @return string|int
 */
function lish_asset_version($relative_path)
{
    $path = LISH_DIR . '/' . ltrim($relative_path, '/');
    return file_exists($path) ? filemtime($path) : LISH_VERSION;
}

add_action('wp_enqueue_scripts', 'lish_register_assets', 5);
/**
 * 親テーマのアセットを register / enqueue する。
 *
 * 優先度 5 で走らせ、子テーマ（優先度 20 想定）が deps に指定できる状態を先に作る。
 */
function lish_register_assets()
{
    // ===== jQuery 非依存構成 =====
    // フロント訪問者には WordPress 同梱の jquery / jquery-migrate を読み込まない。
    // ログイン中は管理バー (admin-bar.js) が jquery 依存のため除外しない。
    // 案件でどうしても必要なら add_filter('lish/deregister_jquery', '__return_false');
    if (apply_filters('lish/deregister_jquery', true) && !is_user_logged_in()) {
        wp_deregister_script('jquery');
    }

    // ===== CSS: 共通基盤 =====
    if (file_exists(LISH_DIR . '/assets/css/lish-foundation.css')) {
        wp_enqueue_style(
            'lish-foundation',
            lish_uri('assets/css/lish-foundation.css'),
            array(),
            lish_asset_version('assets/css/lish-foundation.css')
        );
    }

    // ===== CSS: 記事本文 =====
    // 投稿・固定ページ以外では不要な案件も多いので、フィルタで切れるようにする。
    if (apply_filters('lish/enqueue_entry_content_css', true) && file_exists(LISH_DIR . '/assets/css/lish-entry-content.css')) {
        wp_enqueue_style(
            'lish-entry-content',
            lish_uri('assets/css/lish-entry-content.css'),
            array('lish-foundation'),
            lish_asset_version('assets/css/lish-entry-content.css')
        );
    }

    // ===== JS: 共通挙動（ドロワー / スクロール状態） =====
    if (file_exists(LISH_DIR . '/assets/js/lish-ui.js')) {
        wp_enqueue_script(
            'lish-ui',
            lish_uri('assets/js/lish-ui.js'),
            array(),
            lish_asset_version('assets/js/lish-ui.js'),
            true
        );
    }

    // ===== 同梱ライブラリ（register のみ・enqueue はしない） =====
    // 使う案件だけが lish_enqueue_splide() / lish_enqueue_ajaxzip() を呼ぶ。
    wp_register_style(
        'lish-splide',
        lish_uri('assets/css/vendor/splide-core.min.css'),
        array(),
        lish_asset_version('assets/css/vendor/splide-core.min.css')
    );
    wp_register_script(
        'lish-splide',
        lish_uri('assets/js/vendor/splide.min.js'),
        array(),
        lish_asset_version('assets/js/vendor/splide.min.js'),
        true
    );
    wp_register_script(
        'lish-ajaxzip',
        lish_uri('assets/js/vendor/ajaxzip3.js'),
        array(),
        lish_asset_version('assets/js/vendor/ajaxzip3.js'),
        true
    );
}

/**
 * Splide（スライダー既定ライブラリ）を読み込む。
 *
 * 子テーマの wp_enqueue_scripts 内から呼ぶ:
 *   if (function_exists('lish_enqueue_splide')) { lish_enqueue_splide(); }
 */
function lish_enqueue_splide()
{
    wp_enqueue_style('lish-splide');
    wp_enqueue_script('lish-splide');
}

/**
 * ajaxzip3（郵便番号→住所変換 / jQuery 非依存版）を読み込む。
 *
 * 問い合わせフォームがあるページでのみ呼ぶ想定。
 */
function lish_enqueue_ajaxzip()
{
    wp_enqueue_script('lish-ajaxzip');
}

add_filter('script_loader_tag', 'lish_add_defer_attribute', 10, 3);
/**
 * 自前 JS に defer 属性を付与してレンダリングをブロックさせない。
 *
 * 子テーマは親を編集せず、フィルタでハンドルを追加する:
 *   add_filter('lish/defer_handles', function ($h) { $h[] = 'child-app'; return $h; });
 *
 * @param string $tag    スクリプトタグ。
 * @param string $handle ハンドル名。
 * @param string $src    ソース URL。
 * @return string
 */
function lish_add_defer_attribute($tag, $handle, $src)
{
    $defer_handles = apply_filters('lish/defer_handles', array(
        'lish-ui',
        'lish-splide',
        'lish-ajaxzip',
    ));

    if (!in_array($handle, (array) $defer_handles, true)) {
        return $tag;
    }
    if (strpos($tag, ' defer') !== false || strpos($tag, ' async') !== false) {
        return $tag;
    }
    return str_replace(' src=', ' defer src=', $tag);
}

/**
 * Webフォントの <link> を出力する。
 *
 * 子テーマは親を編集せず、フィルタで URL を宣言する:
 *   add_filter('lish/webfonts', function () {
 *       return 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap';
 *   });
 *
 * self-host する案件はフィルタで空文字を返し、子の SCSS に @font-face を書く。
 * 本関数は header.php から lish_head_meta() 経由で呼ばれる。
 */
function lish_render_webfonts()
{
    $url = apply_filters('lish/webfonts', '');
    if (empty($url)) {
        return;
    }

    // Google Fonts は FOUT 対策のため display=swap を必須にする。
    if (strpos($url, 'fonts.googleapis.com') !== false && strpos($url, 'display=') === false) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . 'display=swap';
    }

    if (apply_filters('lish/webfonts_preconnect', true)) {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    }
    echo '<link rel="stylesheet" href="' . esc_url($url) . '">' . "\n";
}
