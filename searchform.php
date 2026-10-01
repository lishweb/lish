<?php
/**
 * 検索フォーム（親テーマの安全網）。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<form class="c-searchform" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
  <label class="c-searchform__label" for="lish-search-field"><?php esc_html_e('検索', 'lish'); ?></label>
  <input class="c-searchform__input" type="search" id="lish-search-field" name="s" value="<?php echo esc_attr(get_search_query()); ?>">
  <button class="c-searchform__submit" type="submit"><?php esc_html_e('検索', 'lish'); ?></button>
</form>
