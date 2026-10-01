<?php
/**
 * パターン: チェックリストボックス（アクセント枠）
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:group {"className":"is-style-lish-box-accent"} -->
<div class="wp-block-group is-style-lish-box-accent"><!-- wp:list {"className":"is-style-lish-check"} -->
<ul class="wp-block-list is-style-lish-check"><!-- wp:list-item -->
<li><?php esc_html_e('ここに項目が入ります', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('ここに項目が入ります', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('ここに項目が入ります', 'lish'); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->
