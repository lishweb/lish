<?php
/**
 * フッターの既定表示（親テーマのフォールバック）。
 *
 * ★ 子テーマが inc/part-footer.php を持つと、そちらが優先されてこのファイルは使われない。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<footer class="l-footer">
  <div class="l-footer__inner">
    <?php if (has_nav_menu('footer')) : ?>
      <nav class="l-footer__nav" aria-label="<?php esc_attr_e('フッターナビ', 'lish'); ?>">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'footer',
            'container'      => false,
            'menu_class'     => 'l-footer__list',
            'depth'          => 1,
        ));
        ?>
      </nav>
    <?php endif; ?>

    <p class="l-footer__copyright">
      &copy; <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?>
    </p>
  </div>
</footer>
