<?php
/**
 * ヘッダーの既定表示（親テーマのフォールバック）。
 *
 * ★ 子テーマが inc/part-header.php を持つと、そちらが優先されてこのファイルは使われない。
 *   案件では子テーマ側に案件のヘッダーを直書きする。これは
 *   「親テーマ単体でも最低限表示される」ための無地フォールバック。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<header class="l-header">
  <div class="l-header__inner">
    <?php $tag = is_front_page() ? 'h1' : 'p'; ?>
    <<?php echo esc_attr($tag); ?> class="l-header__logo">
      <a class="l-header__logo-link" href="<?php echo esc_url(home_url('/')); ?>">
        <?php bloginfo('name'); ?>
      </a>
    </<?php echo esc_attr($tag); ?>>

    <?php if (has_nav_menu('primary')) : ?>
      <nav class="l-header__nav l-global-nav" aria-label="<?php esc_attr_e('グローバルナビ', 'lish'); ?>">
        <?php
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'container'      => false,
            'menu_class'     => 'l-global-nav__list',
            'depth'          => 1,
        ));
        ?>
      </nav>
    <?php endif; ?>
  </div>
</header>
