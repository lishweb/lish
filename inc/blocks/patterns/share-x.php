<?php
/**
 * パターン: X で共有ボタン
 *
 * リンク先は描画時に inc/blocks/share.php が記事の共有 URL へ差し替える（書き手は URL 不要）。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-lish-share-x"} -->
<div class="wp-block-button is-style-lish-share-x"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('記事を共有する', 'lish'); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
