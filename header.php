<?php
/**
 * サイト共通ヘッダー（親テーマ）。
 *
 * ★ このファイルを子テーマにコピーしないこと。
 *    コピーすると <head> 内の改善（meta / OGP / wp_head 周り）が親の更新で届かなくなる。
 *    案件ごとのヘッダーの見た目は、子テーマの inc/part-header.php に書く。
 *    get_template_part() が「子にあれば子、無ければ親」で解決するため、
 *    子が inc/part-header.php を置くだけでヘッダーが差し替わる。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <?php
  // charset / viewport / OGP / favicon / Webフォント（inc/head.php）
  lish_head_meta();

  do_action('lish/head');

  wp_head();
  ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php do_action('lish/body_open'); ?>

<?php
// ヘッダーの見た目。子テーマの inc/part-header.php が優先される。
get_template_part('inc/part-header');
?>

<main class="l-main">
<?php do_action('lish/main_open'); ?>
