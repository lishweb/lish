<?php
/**
 * 投稿詳細の汎用テンプレート（親テーマの安全網）。
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
        <p class="c-entry__meta">
          <time class="c-entry__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time>
        </p>
        <?php the_title('<h1 class="c-entry__title">', '</h1>'); ?>
      </header>

      <?php if (has_post_thumbnail()) : ?>
        <div class="c-entry__thumbnail"><?php lish_post_thumbnail(true); ?></div>
      <?php endif; ?>

      <div class="c-entry-content">
        <?php the_content(); ?>
      </div>
    </article>
  <?php endwhile; ?>
</div>

<?php get_footer(); ?>
