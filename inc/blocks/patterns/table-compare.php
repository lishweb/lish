<?php
/**
 * パターン: 比較表
 *
 * セルの評価マーク・星評価・小ボタンはツールバー「Lish 装飾」の文字装飾（lish-blocks-editor.js）。
 * SP では表が横スクロールする。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$lish_noimage = esc_url(lish_uri('assets/img/blocks/noimage.svg'));
?>
<!-- wp:table {"hasFixedLayout":false,"className":"is-style-lish-compare"} -->
<figure class="wp-block-table is-style-lish-compare"><table><thead><tr><th></th><th><?php esc_html_e('商品A', 'lish'); ?></th><th><?php esc_html_e('商品B', 'lish'); ?></th><th><?php esc_html_e('商品C', 'lish'); ?></th></tr></thead><tbody><tr><td></td><td><img src="<?php echo $lish_noimage; ?>" alt=""></td><td><img src="<?php echo $lish_noimage; ?>" alt=""></td><td><img src="<?php echo $lish_noimage; ?>" alt=""></td></tr><tr><td><?php esc_html_e('使いやすさ', 'lish'); ?></td><td><span class="lish-mark-triangle"><?php esc_html_e('普通', 'lish'); ?></span></td><td><span class="lish-mark-circle"><?php esc_html_e('使いやすい', 'lish'); ?></span></td><td><span class="lish-mark-triangle"><?php esc_html_e('いまいち', 'lish'); ?></span></td></tr><tr><td><?php esc_html_e('居心地の良さ', 'lish'); ?></td><td><span class="lish-mark-triangle"><?php esc_html_e('普通', 'lish'); ?></span></td><td><span class="lish-mark-cross"><?php esc_html_e('よくない', 'lish'); ?></span></td><td><span class="lish-mark-circle"><?php esc_html_e('よい', 'lish'); ?></span></td></tr><tr><td><?php esc_html_e('サポート', 'lish'); ?></td><td><span class="lish-mark-triangle"><?php esc_html_e('いまいち', 'lish'); ?></span></td><td><span class="lish-mark-double-circle"><?php esc_html_e('充実している', 'lish'); ?></span></td><td><span class="lish-mark-cross"><?php esc_html_e('なし', 'lish'); ?></span></td></tr><tr><td><?php esc_html_e('評価', 'lish'); ?></td><td><span class="lish-rate">★★★☆☆</span></td><td><span class="lish-rate">★★★★☆</span></td><td><span class="lish-rate">★★★★★</span></td></tr><tr><td><?php esc_html_e('詳細', 'lish'); ?></td><td><a href="#"><span class="lish-inline-btn"><?php esc_html_e('詳しく見る', 'lish'); ?></span></a></td><td><a href="#"><span class="lish-inline-btn"><?php esc_html_e('詳しく見る', 'lish'); ?></span></a></td><td><a href="#"><span class="lish-inline-btn"><?php esc_html_e('詳しく見る', 'lish'); ?></span></a></td></tr></tbody></table><figcaption class="wp-element-caption"><?php esc_html_e('比較表', 'lish'); ?></figcaption></figure>
<!-- /wp:table -->
