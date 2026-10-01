<?php
/**
 * 廃止された関数の後方互換シム。
 *
 * 運用ルール:
 *   - 公開関数の改名・削除は MAJOR でのみ行う
 *   - 削除する前に必ず 1 メジャー分、ここにシムを置く
 *   - シムは WP_DEBUG 時のみ通知を出す（本番では静かに動く）
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 非推奨関数の使用を通知する。
 *
 * @param string $function    呼ばれた関数名。
 * @param string $version     非推奨になったバージョン。
 * @param string $replacement 代替関数名。
 */
function lish_deprecated_function($function, $version, $replacement = '')
{
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    $message = sprintf('[Lish] %s() は %s 以降非推奨です。', $function, $version);
    if ($replacement) {
        $message .= sprintf(' 代わりに %s() を使ってください。', $replacement);
    }
    trigger_error(esc_html($message), E_USER_DEPRECATED);
}

/* -------------------------------------------------------------------------
 * base-theme (1.0.0 以前) からの移行シム
 * ---------------------------------------------------------------------- */

if (!function_exists('get_the_custom_excerpt')) {
    /**
     * @deprecated 1.0.0 lish_excerpt() を使うこと。
     *
     * @param string $content 本文。
     * @param int    $length  文字数。
     * @return string
     */
    function get_the_custom_excerpt($content, $length = 70)
    {
        lish_deprecated_function(__FUNCTION__, '1.0.0', 'lish_excerpt');
        return lish_excerpt($content, $length);
    }
}

if (!function_exists('my_theme_breadcrumbs')) {
    /**
     * @deprecated 1.0.0 lish_breadcrumb() を使うこと。
     *
     * @param array $args 旧引数（無視される）。
     */
    function my_theme_breadcrumbs($args = array())
    {
        lish_deprecated_function(__FUNCTION__, '1.0.0', 'lish_breadcrumb');
        lish_breadcrumb();
    }
}
