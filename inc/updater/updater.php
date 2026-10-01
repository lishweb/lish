<?php
/**
 * 親テーマ更新通知の受け口（v1.0 では no-op）。
 *
 * 配信サーバは未実装。ここには「後からサーバ実装を差し込むだけ」で済むように
 * WordPress 側のフック配線と、差し替えポイント（フィルタ）だけを用意しておく。
 *
 * 有効化の方法（将来）:
 *   add_filter('lish/update_source', function () {
 *       return 'https://updates.lishinc.com/lish/info.json';
 *   });
 *
 * 設計上の注意:
 *   - 更新チェックの HTTP 通信は必ず失敗許容にする（配信サーバが落ちてもサイトは動く）
 *   - レスポンスは必ずトランジェントにキャッシュする（毎回叩かない）
 *   - style.css の `Update URI:` ヘッダーにより、wordpress.org の同名テーマによる
 *     誤更新は WP 6.1+ が自動的に防いでくれる
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 更新情報の取得元 URL。空文字なら更新チェックを行わない。
 *
 * @return string
 */
function lish_update_source()
{
    return (string) apply_filters('lish/update_source', '');
}

add_filter('pre_set_site_transient_update_themes', 'lish_check_for_update');
/**
 * テーマ更新チェックに Lish の情報を差し込む。
 *
 * @param mixed $transient 更新トランジェント。
 * @return mixed
 */
function lish_check_for_update($transient)
{
    $source = lish_update_source();
    if ($source === '' || !is_object($transient)) {
        return $transient;
    }

    $remote = lish_fetch_update_info($source);
    if (!$remote || empty($remote['version'])) {
        return $transient;
    }

    $slug = get_template(); // 親テーマのディレクトリ名（= 'lish'）

    if (version_compare(LISH_VERSION, $remote['version'], '<')) {
        $transient->response[$slug] = array(
            'theme'       => $slug,
            'new_version' => $remote['version'],
            'url'         => isset($remote['url']) ? $remote['url'] : '',
            'package'     => isset($remote['package']) ? $remote['package'] : '',
        );
    } else {
        $transient->no_update[$slug] = array(
            'theme'       => $slug,
            'new_version' => LISH_VERSION,
            'url'         => isset($remote['url']) ? $remote['url'] : '',
            'package'     => '',
        );
    }

    return $transient;
}

/**
 * 更新情報 JSON を取得する（12時間キャッシュ・失敗許容）。
 *
 * @param string $source 取得元 URL。
 * @return array|false
 */
function lish_fetch_update_info($source)
{
    $cache_key = 'lish_update_info_' . md5($source);
    $cached    = get_site_transient($cache_key);
    if ($cached !== false) {
        return is_array($cached) ? $cached : false;
    }

    $response = wp_remote_get($source, array(
        'timeout' => 5,
        'headers' => array('Accept' => 'application/json'),
    ));

    // 通信失敗時はサイトを壊さない。短めのキャッシュを置いて再試行を抑える。
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        set_site_transient($cache_key, 'error', HOUR_IN_SECONDS);
        return false;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($data)) {
        set_site_transient($cache_key, 'error', HOUR_IN_SECONDS);
        return false;
    }

    set_site_transient($cache_key, $data, 12 * HOUR_IN_SECONDS);
    return $data;
}
