<?php
/**
 * 管理画面通知。
 *
 * - 動作要件（PHP / WP バージョン）を満たさない場合
 * - 子テーマが要求する Lish バージョンを親が満たさない場合
 *
 * いずれも「通知を出すだけ」で、サイトの表示は止めない。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_notices', 'lish_admin_notice_requirements');
/**
 * 動作要件不足の通知。
 */
function lish_admin_notice_requirements()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $messages = array();

    if (version_compare(PHP_VERSION, LISH_MIN_PHP, '<')) {
        $messages[] = sprintf(
            /* translators: 1: required PHP version, 2: current PHP version */
            __('Lish は PHP %1$s 以上を必要とします（現在: %2$s）。', 'lish'),
            LISH_MIN_PHP,
            PHP_VERSION
        );
    }

    if (version_compare(get_bloginfo('version'), LISH_MIN_WP, '<')) {
        $messages[] = sprintf(
            /* translators: 1: required WP version, 2: current WP version */
            __('Lish は WordPress %1$s 以上を必要とします（現在: %2$s）。', 'lish'),
            LISH_MIN_WP,
            get_bloginfo('version')
        );
    }

    if (empty($messages)) {
        return;
    }

    echo '<div class="notice notice-error"><p><strong>Lish</strong><br>';
    echo esc_html(implode(' / ', $messages));
    echo '</p></div>';
}

add_action('admin_notices', 'lish_admin_notice_child_requirement');
/**
 * 子テーマの `Lish Requires:` ヘッダーを読み、親が古い場合に通知する。
 *
 * 子テーマの style.css に以下のような独自ヘッダーを書いておくと機能する:
 *   Lish Requires: 1.1.0
 */
function lish_admin_notice_child_requirement()
{
    if (!current_user_can('manage_options') || !is_child_theme()) {
        return;
    }

    $required = wp_get_theme()->get('Lish Requires');
    if (!$required || !is_string($required)) {
        return;
    }

    if (version_compare(LISH_VERSION, $required, '>=')) {
        return;
    }

    echo '<div class="notice notice-warning"><p><strong>Lish</strong><br>';
    echo esc_html(sprintf(
        /* translators: 1: required Lish version, 2: installed Lish version */
        __('この子テーマは親テーマ Lish %1$s 以上を必要としますが、現在 %2$s が有効です。親テーマを更新してください。', 'lish'),
        $required,
        LISH_VERSION
    ));
    echo '</p></div>';
}
