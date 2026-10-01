<?php
/**
 * パターン: この記事でわかること
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:group {"className":"is-style-lish-box-summary"} -->
<div class="wp-block-group is-style-lish-box-summary"><!-- wp:paragraph {"className":"lish-box-label"} -->
<p class="lish-box-label"><?php esc_html_e('この記事でわかること', 'lish'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><?php esc_html_e('ここに内容', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('ここに内容', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('ここに内容', 'lish'); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->
