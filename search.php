<?php
/**
 * 検索結果（親テーマの安全網）。
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
    <h1 class="c-archive__title">
      <?php
      /* translators: %s: search query */
      printf(esc_html__('「%s」の検索結果', 'lish'), esc_html(get_search_query()));
      ?>
    </h1>
  </header>

  <?php if (have_posts()) : ?>
    <ul class="c-post-list">
      <?php while (have_posts()) : the_post(); ?>
        <li class="c-post-list__item">
          <a class="c-post-list__link" href="<?php the_permalink(); ?>">
            <span class="c-post-list__title"><?php the_title(); ?></span>
          </a>
        </li>
      <?php endwhile; ?>
    </ul>

    <?php lish_pagination(); ?>
  <?php else : ?>
    <p><?php esc_html_e('該当する記事が見つかりませんでした。', 'lish'); ?></p>
    <?php get_search_form(); ?>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
