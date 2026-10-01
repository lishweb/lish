<?php
/**
 * パンくずの描画。
 *
 * lish_breadcrumb() から get_template_part() 経由で呼ばれる。
 * $args['items'] に array('label' => string, 'url' => string) の配列が入る。
 *
 * ※ このディレクトリ（inc/template-parts/）は親テーマ専用。
 *    子テーマのパーシャルは inc/part-*.php に置き、名前を衝突させないこと。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

$items = isset($args['items']) ? $args['items'] : array();
if (empty($items)) {
    return;
}

$last = count($items) - 1;
?>
<nav class="c-breadcrumb" aria-label="<?php esc_attr_e('パンくずリスト', 'lish'); ?>">
  <ol class="c-breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">
    <?php foreach ($items as $i => $item) : ?>
      <li class="c-breadcrumb__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
        <?php if (!empty($item['url']) && $i !== $last) : ?>
          <a class="c-breadcrumb__link" itemprop="item" href="<?php echo esc_url($item['url']); ?>">
            <span itemprop="name"><?php echo esc_html($item['label']); ?></span>
          </a>
        <?php else : ?>
          <span class="c-breadcrumb__current" itemprop="name" aria-current="page"><?php echo esc_html($item['label']); ?></span>
        <?php endif; ?>
        <meta itemprop="position" content="<?php echo esc_attr($i + 1); ?>">
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
