<?php
/**
 * パターン: 記事一覧（最新1件を大きく＋次の3件を横長カード）
 *
 * 標準の「クエリーループ」ブロックで作っている。件数・並び順・カテゴリはブロックの設定で変えられる。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- wp:columns {"className":"lish-post-list"} -->
<div class="wp-block-columns lish-post-list"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:query {"queryId":1,"query":{"perPage":1,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"className":"lish-post-list__large"} -->
<div class="wp-block-query lish-post-list__large"><!-- wp:post-template -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->

<!-- wp:post-title {"level":3,"isLink":true} /-->

<!-- wp:post-date {"format":"Y.m.d"} /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:query {"queryId":2,"query":{"perPage":3,"pages":0,"offset":1,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"className":"lish-post-list__small"} -->
<div class="wp-block-query lish-post-list__small"><!-- wp:post-template -->
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->

<!-- wp:group {"className":"lish-post-list__body"} -->
<div class="wp-block-group lish-post-list__body"><!-- wp:post-title {"level":3,"isLink":true} /-->

<!-- wp:post-date {"format":"Y.m.d"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
