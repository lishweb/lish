<?php
/**
 * 最終フォールバック（WordPress がテーマに必須とするファイル）。
 *
 * front-page.php / page.php / single.php / archive.php に該当しない場合の保険。
 * 通常はここまで来ない想定。
 *
 * @package Lish
 */

get_header(); ?>

<div class="l-content">
  <?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <h1 class="c-entry__title"><?php the_title(); ?></h1>
        <div class="c-entry-content"><?php the_content(); ?></div>
      </article>
    <?php endwhile; ?>
    <?php lish_pagination(); ?>
  <?php else : ?>
    <p><?php esc_html_e('記事がありません。', 'lish'); ?></p>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
