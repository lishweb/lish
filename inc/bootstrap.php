<?php
/**
 * 読み込み制御と動作要件チェック。
 *
 * 要件を満たさない環境では「Fatal を起こさず」機能を縮退させ、管理画面に通知を出す。
 * サイトが白画面になるより、警告付きで表示され続ける方が事故が小さいため。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 動作要件を満たしているか。
 *
 * @return bool
 */
function lish_meets_requirements()
{
    if (version_compare(PHP_VERSION, LISH_MIN_PHP, '<')) {
        return false;
    }
    if (version_compare(get_bloginfo('version'), LISH_MIN_WP, '<')) {
        return false;
    }
    return true;
}

// 要件チェック — 満たさない場合は管理通知だけ読み込んで終了する。
if (!lish_meets_requirements()) {
    require_once LISH_DIR . '/inc/admin/notices.php';
    return;
}

/**
 * 読み込むモジュール。
 *
 * 順序に意味がある（compat → 基盤 → 機能）。
 * ファイルが存在しない場合はスキップする（部分的な配置ミスで白画面にしない）。
 */
$lish_modules = array(
    // 互換レイヤ（最初に読む）
    'inc/compat/php.php',
    'inc/compat/wp.php',
    'inc/compat/deprecated.php',

    // 基盤
    'inc/setup.php',
    'inc/assets.php',
    'inc/head.php',
    'inc/content.php',
    'inc/security.php',
    'inc/template-tags.php',

    // ブログ用ブロック（1.1.0〜）
    'inc/blocks/setup.php',

    // 管理・更新
    'inc/admin/notices.php',
    'inc/updater/updater.php',
);

foreach ($lish_modules as $lish_module) {
    $lish_module_path = LISH_DIR . '/' . $lish_module;
    if (file_exists($lish_module_path)) {
        require_once $lish_module_path;
    }
}
unset($lish_modules, $lish_module, $lish_module_path);
