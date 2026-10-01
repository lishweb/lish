<?php
/**
 * 独自ブロック lish/post-card（記事カード）。
 *
 * 編集画面で URL を入れると、その記事のアイキャッチ・タイトル・日付を描画時に PHP で出力する。
 * 記事のタイトルやアイキャッチを後から変えても、カードは自動で追従する。
 * サイト外の URL や見つからない記事は、アイキャッチ無しのリンクとして出す（エラーにしない）。
 *
 * スタイル（ブロックスタイル）:
 *   既定          … 左上ラベル（既定「関連」）＋右下「続きを読む」
 *   lish-card-b   … 上に「あわせて読みたい」タブ
 *
 * 編集画面の UI は assets/js/lish-blocks-editor.js。見た目は lish-blocks.css。
 *
 * @package Lish
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'lish_blocks_register_post_card');
/**
 * lish/post-card を登録する。
 */
function lish_blocks_register_post_card()
{
    if (!_lish_blocks_enabled()) {
        return;
    }

    register_block_type('lish/post-card', array(
        'api_version'     => 2,
        'title'           => __('記事カード', 'lish'),
        'category'        => 'widgets',
        'attributes'      => array(
            'url'   => array('type' => 'string', 'default' => ''),
            'label' => array('type' => 'string'),
            // 編集画面のプレビュー（ServerSideRender）がスタイルを渡せるよう明示する
            'className' => array('type' => 'string'),
        ),
        'supports'        => array('html' => false),
        'render_callback' => 'lish_blocks_render_post_card',
    ));

    register_block_style('lish/post-card', array(
        'name'       => 'lish-card-a',
        'label'      => __('ラベル＋続きを読む', 'lish'),
        'is_default' => true,
    ));
    register_block_style('lish/post-card', array(
        'name'  => 'lish-card-b',
        'label' => __('あわせて読みたい', 'lish'),
    ));
}

/**
 * 記事カードに出す内容を URL から組み立てる。
 *
 * @param string $url 入力された URL。
 * @return array{url: string, title: string, date: string, date_iso: string, image: string, post_id: int}
 */
function _lish_blocks_post_card_data($url)
{
    $data = array(
        'url'      => $url,
        'title'    => $url,
        'date'     => '',
        'date_iso' => '',
        'image'    => '',
        'post_id'  => 0,
    );

    $post_id = url_to_postid($url);
    $post    = $post_id ? get_post($post_id) : null;
    if ($post instanceof WP_Post && $post->post_status === 'publish') {
        $data['url']      = get_permalink($post);
        $data['title']    = get_the_title($post);
        $data['date']     = get_the_date('Y.m.d', $post);
        $data['date_iso'] = get_the_date('c', $post);
        $data['post_id']  = $post->ID;
        $data['image']    = has_post_thumbnail($post)
            ? get_the_post_thumbnail($post, 'medium', array('loading' => 'lazy', 'decoding' => 'async', 'alt' => ''))
            : '';
    }

    /**
     * 記事カードの表示内容を差し替える。
     *
     * @param array  $data 表示内容（url / title / date / date_iso / image(HTML) / post_id）。
     * @param string $url  入力された URL。
     */
    return (array) apply_filters('lish/post_card/data', $data, $url);
}

/**
 * lish/post-card の描画。
 *
 * @param array $attributes ブロック属性。
 * @return string
 */
function lish_blocks_render_post_card($attributes)
{
    $url = isset($attributes['url']) ? trim((string) $attributes['url']) : '';
    if ($url === '') {
        return '';
    }

    $data  = _lish_blocks_post_card_data($url);
    $class = isset($attributes['className']) ? (string) $attributes['className'] : '';
    $is_b  = strpos($class, 'is-style-lish-card-b') !== false;

    $default_label = $is_b ? __('あわせて読みたい', 'lish') : __('関連', 'lish');
    $label         = isset($attributes['label']) ? (string) $attributes['label'] : $default_label;

    $image = $data['image'] !== ''
        ? $data['image']
        : '<img src="' . esc_url(lish_uri('assets/img/blocks/noimage.svg')) . '" alt="" width="160" height="120" loading="lazy" decoding="async">';

    ob_start();
    ?>
<div <?php echo get_block_wrapper_attributes(array('class' => 'lish-post-card')); ?>>
  <a class="lish-post-card__link" href="<?php echo esc_url($data['url']); ?>">
    <?php if ($label !== '') : ?>
      <span class="lish-post-card__label"><?php echo esc_html($label); ?></span>
    <?php endif; ?>
    <span class="lish-post-card__thumb"><?php echo wp_kses_post($image); ?></span>
    <span class="lish-post-card__body">
      <span class="lish-post-card__title"><?php echo esc_html($data['title']); ?></span>
      <?php if ($data['date'] !== '') : ?>
        <time class="lish-post-card__date" datetime="<?php echo esc_attr($data['date_iso']); ?>"><?php echo esc_html($data['date']); ?></time>
      <?php endif; ?>
    </span>
    <span class="lish-post-card__more"><?php esc_html_e('続きを読む', 'lish'); ?></span>
  </a>
</div>
    <?php
    return (string) ob_get_clean();
}
