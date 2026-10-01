<?php
/**
 * アーカイブの汎用テンプレート（親テーマの安全網）。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="l-content">
  <?php lish_breadcrumb(); ?>

  <header class="c-archive__header">
    <h1 class="c-archive__title"><?php echo esc_html(get_the_archive_title()); ?></h1>
  </header>

  <?php if (have_posts()) : ?>
    <ul class="c-post-list">
      <?php while (have_posts()) : the_post(); ?>
        <li class="c-post-list__item">
          <a class="c-post-list__link" href="<?php the_permalink(); ?>">
            <time class="c-post-list__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time>
            <span class="c-post-list__title"><?php the_title(); ?></span>
          </a>
        </li>
      <?php endwhile; ?>
    </ul>

    <?php lish_pagination(); ?>
  <?php else : ?>
    <p><?php esc_html_e('記事がありません。', 'lish'); ?></p>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
