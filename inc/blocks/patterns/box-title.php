<?php
/**
 * パターン: タイトル付きボックス
 *
 * 先頭の段落（.lish-box-label）が枠の上辺に重なるラベルになる。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:group {"className":"is-style-lish-box-title"} -->
<div class="wp-block-group is-style-lish-box-title"><!-- wp:paragraph {"className":"lish-box-label"} -->
<p class="lish-box-label"><?php esc_html_e('タイトル', 'lish'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e('ここに内容が入ります。', 'lish'); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
