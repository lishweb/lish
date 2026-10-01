<?php
/**
 * WordPress バージョン互換シム。
 *
 * LISH_MIN_WP を引き上げたら、不要になったシムをここから削除する（MAJOR リリース時）。
 * 現在の最低要件: WordPress 6.0
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('wp_body_open')) {
    /**
     * WP 5.2 未満向け。実際には LISH_MIN_WP=6.0 なので到達しないが、
     * 万一の環境差異で Fatal にしないための保険。
     */
    function wp_body_open()
    {
        do_action('wp_body_open');
    }
}
