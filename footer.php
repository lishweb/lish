<?php
/**
 * サイト共通フッター（親テーマ）。
 *
 * ★ このファイルを子テーマにコピーしないこと。
 *    案件ごとのフッターの見た目は、子テーマの inc/part-footer.php に書く。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<?php do_action('lish/main_close'); ?>
</main>

<?php
// フッターの見た目。子テーマの inc/part-footer.php が優先される。
get_template_part('inc/part-footer');
?>

<?php do_action('lish/body_close'); ?>
<?php wp_footer(); ?>
</body>

</html>
