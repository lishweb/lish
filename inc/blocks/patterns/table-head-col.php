<?php
/**
 * パターン: 表（左列見出し・角丸）
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:table {"hasFixedLayout":false,"className":"is-style-lish-head-col"} -->
<figure class="wp-block-table is-style-lish-head-col"><table><tbody><tr><td><?php esc_html_e('項目', 'lish'); ?></td><td><?php esc_html_e('ここに内容が入ります', 'lish'); ?></td></tr><tr><td><?php esc_html_e('項目', 'lish'); ?></td><td><?php esc_html_e('ここに内容が入ります', 'lish'); ?></td></tr><tr><td><?php esc_html_e('項目', 'lish'); ?></td><td><?php esc_html_e('ここに内容が入ります', 'lish'); ?></td></tr><tr><td><?php esc_html_e('項目', 'lish'); ?></td><td><?php esc_html_e('ここに内容が入ります', 'lish'); ?></td></tr></tbody></table></figure>
<!-- /wp:table -->
