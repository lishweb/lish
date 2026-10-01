<?php
/**
 * パターン: 記事の URL をコピー
 *
 * リンク先は描画時に inc/blocks/share.php が記事の URL へ差し替え、
 * クリック時のコピーは assets/js/lish-blocks.js が行う。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-lish-copy-url"} -->
<div class="wp-block-button is-style-lish-copy-url"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e('記事の URL をコピー', 'lish'); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
