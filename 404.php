<?php
/**
 * 404（親テーマの安全網）。
 *
 * 案件のデザインを当てる場合、まず子テーマの inc/part-404.php で対応できないか検討する
 * （このファイル自体を子にコピーすると親の更新が届かなくなる）。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="l-content">
  <?php
  // 子テーマが inc/part-404.php を持っていればそれを使う。
  if (locate_template('inc/part-404.php')) {
      get_template_part('inc/part-404');
  } else {
  ?>
    <section class="c-notfound">
      <h1 class="c-notfound__title">404 NOT FOUND</h1>
      <p class="c-notfound__text"><?php esc_html_e('お探しのページが見つかりません。', 'lish'); ?></p>
      <p class="c-notfound__button">
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('トップページに戻る', 'lish'); ?></a>
      </p>
    </section>
  <?php } ?>
</div>

<?php get_footer(); ?>
