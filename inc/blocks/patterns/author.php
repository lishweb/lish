<?php
/**
 * パターン: 著者ボックス
 *
 * 「詳しいプロフィール」の開閉に core/details（WordPress 6.3 以降）を使う。
 * 6.3 未満では patterns.php の requires により登録されない。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:group {"className":"is-style-lish-box-title lish-author"} -->
<div class="wp-block-group is-style-lish-box-title lish-author"><!-- wp:paragraph {"className":"lish-box-label"} -->
<p class="lish-box-label"><?php esc_html_e('この記事の著者', 'lish'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:columns {"isStackedOnMobile":false,"className":"lish-author__body"} -->
<div class="wp-block-columns is-not-stacked-on-mobile lish-author__body"><!-- wp:column {"width":"5em","className":"lish-author__photo"} -->
<div class="wp-block-column lish-author__photo" style="flex-basis:5em"><!-- wp:image {"sizeSlug":"thumbnail","className":"is-style-rounded"} -->
<figure class="wp-block-image size-thumbnail is-style-rounded"><img src="<?php echo esc_url(lish_uri('assets/img/blocks/avatar.svg')); ?>" alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"lish-author__info"} -->
<div class="wp-block-column lish-author__info"><!-- wp:paragraph {"className":"lish-author__role"} -->
<p class="lish-author__role"><?php esc_html_e('肩書き', 'lish'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lish-author__name"} -->
<p class="lish-author__name"><?php esc_html_e('名前', 'lish'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lish-author__kana"} -->
<p class="lish-author__kana"><?php esc_html_e('よみがな', 'lish'); ?></p>
<!-- /wp:paragraph -->

<!-- wp:details {"summary":"<?php esc_attr_e('詳しいプロフィール', 'lish'); ?>","className":"lish-author__profile"} -->
<details class="wp-block-details lish-author__profile"><summary><?php esc_html_e('詳しいプロフィール', 'lish'); ?></summary><!-- wp:paragraph -->
<p><?php esc_html_e('ここにプロフィールの詳細を書きます。', 'lish'); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
