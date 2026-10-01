<?php
/**
 * パターン: あわせて読みたいボックス（タイトル付きボックス＋記事カード2件）
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:group {"className":"is-style-lish-box-title lish-related"} -->
<div class="wp-block-group is-style-lish-box-title lish-related"><!-- wp:paragraph {"className":"lish-box-label"} -->
<p class="lish-box-label"><?php esc_html_e('あわせて読みたい', 'lish'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:lish/post-card {"label":""} /-->

<!-- wp:lish/post-card {"label":""} /--></div>
<!-- /wp:group -->
