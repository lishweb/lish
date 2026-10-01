<?php
/**
 * パターン: 番号リスト（縦線）
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:list {"ordered":true,"className":"is-style-lish-number-line"} -->
<ol class="wp-block-list is-style-lish-number-line"><!-- wp:list-item -->
<li><?php esc_html_e('ここに項目が入ります', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('ここに項目が入ります', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('ここに項目が入ります', 'lish'); ?></li>
<!-- /wp:list-item --></ol>
<!-- /wp:list -->
