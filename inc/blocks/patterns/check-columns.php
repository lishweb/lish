<?php
/**
 * パターン: 気になる内容をチェック（2列）
 *
 * SP では列が縦に並ぶ（core/columns の既定）。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:group {"className":"is-style-lish-box-check"} -->
<div class="wp-block-group is-style-lish-box-check"><!-- wp:paragraph {"className":"lish-box-label"} -->
<p class="lish-box-label"><?php esc_html_e('気になる内容をチェック', 'lish'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:list {"className":"is-style-lish-check"} -->
<ul class="wp-block-list is-style-lish-check"><!-- wp:list-item -->
<li><?php esc_html_e('特に見せたい見出しなど', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('記入欄', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('記入欄', 'lish'); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:list {"className":"is-style-lish-check"} -->
<ul class="wp-block-list is-style-lish-check"><!-- wp:list-item -->
<li><?php esc_html_e('特に見せたい見出しなど', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('記入欄', 'lish'); ?></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><?php esc_html_e('記入欄', 'lish'); ?></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
