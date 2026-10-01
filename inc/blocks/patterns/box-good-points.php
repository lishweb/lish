<?php
/**
 * パターン: よかったところ（角タイトル付きボックス）
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:group {"className":"is-style-lish-box-corner"} -->
<div class="wp-block-group is-style-lish-box-corner"><!-- wp:paragraph {"className":"lish-box-label"} -->
<p class="lish-box-label"><?php esc_html_e('よかったところ', 'lish'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"is-style-lish-check"} -->
<ul class="wp-block-list is-style-lish-check"><!-- wp:list-item -->
<li><?php esc_html_e('ここによかったところ', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('ここによかったところ', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('ここによかったところ', 'lish'); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->
