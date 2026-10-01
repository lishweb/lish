<?php
/**
 * 最低限のセキュリティ処理。
 *
 * v1.0 では「情報露出を減らす」範囲のみに留める。
 * ログイン試行制限 / REST API 制限 / セキュリティヘッダー等は 1.1 以降の追加候補。
 * 全項目をフィルタで無効化できるようにしてあるので、案件都合で戻せる。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'lish_security_cleanup');
/**
 * <head> / HTTP ヘッダーから不要な情報露出を取り除く。
 */
function lish_security_cleanup()
{
    // WordPress のバージョン露出（<meta name="generator">）
    if (apply_filters('lish/security/remove_generator', true)) {
        remove_action('wp_head', 'wp_generator');
        add_filter('the_generator', '__return_empty_string');
    }

    // RSD / wlwmanifest（旧ブログクライアント向け。現在は不要）
    if (apply_filters('lish/security/remove_legacy_links', true)) {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
    }

    // 短縮 URL（wp-shortlink）
    if (apply_filters('lish/security/remove_shortlink', true)) {
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('template_redirect', 'wp_shortlink_header', 11);
    }
}

add_filter('xmlrpc_enabled', 'lish_disable_xmlrpc');
/**
 * XML-RPC を無効化する。
 *
 * 外部投稿ツールや Jetpack を使う案件では以下で戻す:
 *   add_filter('lish/security/disable_xmlrpc', '__return_false');
 *
 * @param bool $enabled 現在の設定。
 * @return bool
 */
function lish_disable_xmlrpc($enabled)
{
    return apply_filters('lish/security/disable_xmlrpc', true) ? false : $enabled;
}

add_filter('wp_headers', 'lish_remove_pingback_header');
/**
 * X-Pingback ヘッダーを削除する。
 *
 * @param array $headers 送出予定のヘッダー。
 * @return array
 */
function lish_remove_pingback_header($headers)
{
    if (apply_filters('lish/security/remove_pingback_header', true)) {
        unset($headers['X-Pingback']);
    }
    return $headers;
}

add_filter('login_errors', 'lish_generic_login_error');
/**
 * ログインエラーメッセージを汎用化する（ユーザー名の存在有無を漏らさない）。
 *
 * @param string $error エラーメッセージ。
 * @return string
 */
function lish_generic_login_error($error)
{
    if (!apply_filters('lish/security/generic_login_error', true)) {
        return $error;
    }
    return __('ログイン情報が正しくありません。', 'lish');
}
