<?php
/**
 * Lish 親テーマ — ブートストラップ
 *
 * このファイルには「定数定義」と「bootstrap.php の読み込み」以外を書かない。
 * 実処理は inc/ 配下に分割する（更新時の差分レビューを容易にするため）。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

// ===== バージョン =====
// style.css の Version: と必ず同期させる（リリース時のチェック項目）。
define('LISH_VERSION', '1.3.0');

// ===== 最低動作要件 =====
define('LISH_MIN_PHP', '8.0');
define('LISH_MIN_WP', '6.0');

// ===== パス / URI =====
// 親テーマ（= このテーマ）を指す。子テーマのパスは get_stylesheet_directory() 側。
define('LISH_DIR', get_template_directory());
define('LISH_URI', get_template_directory_uri());

require_once LISH_DIR . '/inc/bootstrap.php';
