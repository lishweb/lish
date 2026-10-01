<?php
/**
 * 固定ページの汎用テンプレート（親テーマの安全網）。
 *
 * ★ 子テーマにコピーすると、このファイルへの親の更新が届かなくなる。
 *   案件で固定ページのデザインを作る場合は、まず pages/page-{slug}.php の
 *   カスタムテンプレートで対応できないか検討すること。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="l-content">
  <?php lish_breadcrumb(); ?>

  <?php while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <header class="c-entry__header">
        <?php the_title('<h1 class="c-entry__title">', '</h1>'); ?>
      </header>
      <div class="c-entry-content">
        <?php the_content(); ?>
      </div>
    </article>
  <?php endwhile; ?>
</div>

<?php get_footer(); ?>
