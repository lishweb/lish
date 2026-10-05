<?php
/**
 * 親テーマの自動更新（GitHub Releases 配信）。
 *
 * 流れ:
 *   1. WP の更新チェック（1日2回）で GitHub の最新リリースを調べる
 *   2. 新しい版があれば WP の更新情報に載せる（zip はリリースに添付した lish-x.y.z.zip）
 *   3. WP 標準の自動更新が親テーマだけを差し替える（子テーマには触れない）
 *   4. 失敗したときだけ Lish（既定 web@lishinc.com）へメールする。成功メールは送らない
 *
 * 設計上の注意:
 *   - 更新チェックの HTTP 通信は必ず失敗許容にする（GitHub が落ちてもサイトは動く）
 *   - レスポンスは必ずトランジェントにキャッシュする（毎回叩かない）
 *   - メジャー版（1.x → 2.0）は自動更新しない。互換を壊す変更が入るため手動で更新する
 *   - lish/ に .git がある環境（開発環境）では更新を出さない（lish_check_for_update() で判定。
 *     WP 本体の VCS 判定は themes/ より上の階層しか見ないため、自前で止める）
 *   - style.css の `Update URI:` ヘッダーにより、wordpress.org の同名テーマによる
 *     誤更新は WP 6.1+ が自動的に防いでくれる
 *
 * 配布の手順は docs/RELEASE.md。
 *
 * @package Lish
 */

if (!defined('ABSPATH')) {
    exit;
}

/** 配布元の GitHub リポジトリ（owner/repo）。 */
const LISH_UPDATE_REPO = 'lishweb/lish';

/**
 * 更新情報の取得元 URL（旧方式）。空文字なら GitHub Releases を見る。
 *
 * 値を返すと、従来どおりその URL の JSON（version / url / package）を使う。
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
    if (!is_object($transient)) {
        return $transient;
    }

    // 開発環境（lish/ が Git 管理）では更新を出さない。WP 本体の VCS 判定は themes/ より上しか見ないため、
    // ここで止めないと更新時に lish/ が丸ごと差し替わり .git が消える。
    if (is_dir(get_template_directory() . '/.git')) {
        return $transient;
    }

    $source = lish_update_source();
    $remote = $source !== '' ? lish_fetch_update_info($source) : _lish_fetch_github_release();
    if (!$remote || empty($remote['version'])) {
        return $transient;
    }

    $slug = get_template(); // 親テーマのディレクトリ名（= 'lish'）
    $item = array(
        'theme'        => $slug,
        'new_version'  => $remote['version'],
        'url'          => isset($remote['url']) ? $remote['url'] : '',
        'package'      => isset($remote['package']) ? $remote['package'] : '',
        'requires'     => isset($remote['requires']) ? $remote['requires'] : '',
        'requires_php' => isset($remote['requires_php']) ? $remote['requires_php'] : '',
    );

    if ($item['package'] !== '' && version_compare(LISH_VERSION, $remote['version'], '<')) {
        $transient->response[$slug] = $item;
        unset($transient->no_update[$slug]);
    } else {
        $item['new_version'] = LISH_VERSION;
        $item['package']     = '';
        $transient->no_update[$slug] = $item;
        unset($transient->response[$slug]);
    }

    return $transient;
}

/**
 * 更新情報 JSON を取得する（旧方式。12時間キャッシュ・失敗許容）。
 *
 * @param string $source 取得元 URL。
 * @return array|false
 */
function lish_fetch_update_info($source)
{
    $cache_key = 'lish_update_info_' . md5($source);
    $cached    = _lish_update_cache_get($cache_key);
    if ($cached !== null) {
        return $cached;
    }

    $data = _lish_remote_json($source);
    _lish_update_cache_set($cache_key, $data);
    return $data;
}

/**
 * GitHub の最新リリースから更新情報を作る（12時間キャッシュ・失敗許容）。
 *
 * Pre-release / Draft は `releases/latest` に出ないため、試験配布には Pre-release を使う。
 * リリースに `lish-x.y.z.zip`（中身は lish/ フォルダ1つ）が添付されていなければ配らない。
 *
 * @return array|false version / url / package / requires / requires_php
 */
function _lish_fetch_github_release()
{
    $cache_key = 'lish_update_info_github';
    $cached    = _lish_update_cache_get($cache_key);
    if ($cached !== null) {
        return $cached;
    }

    $release = _lish_remote_json('https://api.github.com/repos/' . LISH_UPDATE_REPO . '/releases/latest');
    $info    = false;

    if (is_array($release) && !empty($release['tag_name'])) {
        $version = ltrim((string) $release['tag_name'], 'vV');
        $package = '';
        if (!empty($release['assets']) && is_array($release['assets'])) {
            foreach ($release['assets'] as $asset) {
                if (isset($asset['name'], $asset['browser_download_url'])
                    && preg_match('/^lish-[0-9][0-9A-Za-z.\-]*\.zip$/', $asset['name'])) {
                    $package = $asset['browser_download_url'];
                    break;
                }
            }
        }

        $info = array(
            'version' => $version,
            'url'     => isset($release['html_url']) ? $release['html_url'] : '',
            'package' => $package,
        );

        // 新しい版のときだけ、その版の style.css から動作要件を読む（PHP/WP 要件の引き上げに備える）
        if ($package !== '' && version_compare(LISH_VERSION, $version, '<')) {
            $info = array_merge($info, _lish_fetch_release_requirements((string) $release['tag_name']));
        }
    }

    _lish_update_cache_set($cache_key, $info);
    return $info;
}

/**
 * 指定タグの style.css から `Requires at least` / `Requires PHP` を読む。
 *
 * @param string $tag リリースのタグ名。
 * @return array
 */
function _lish_fetch_release_requirements($tag)
{
    $response = wp_remote_get(
        'https://raw.githubusercontent.com/' . LISH_UPDATE_REPO . '/' . rawurlencode($tag) . '/style.css',
        array('timeout' => 5)
    );
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return array();
    }

    $body     = wp_remote_retrieve_body($response);
    $requires = array();
    if (preg_match('/^[ \t\/*#@]*Requires at least:(.*)$/mi', $body, $m)) {
        $requires['requires'] = trim($m[1]);
    }
    if (preg_match('/^[ \t\/*#@]*Requires PHP:(.*)$/mi', $body, $m)) {
        $requires['requires_php'] = trim($m[1]);
    }
    return $requires;
}

/**
 * JSON を取得する。失敗したら false（サイトは壊さない）。
 *
 * @param string $url 取得先。
 * @return array|false
 */
function _lish_remote_json($url)
{
    $response = wp_remote_get($url, array(
        'timeout' => 5,
        'headers' => array('Accept' => 'application/json'),
    ));
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    return is_array($data) ? $data : false;
}

/**
 * キャッシュを読む。未取得なら null。
 * 管理画面の「再確認」（update-core.php?force-check=1）ではキャッシュを使わない。
 *
 * @param string $key トランジェント名。
 * @return array|false|null
 */
function _lish_update_cache_get($key)
{
    if (is_admin() && !empty($_GET['force-check'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return null;
    }

    $cached = get_site_transient($key);
    if ($cached === false) {
        return null;
    }
    return is_array($cached) ? $cached : false;
}

/**
 * キャッシュを書く。成功は12時間、失敗は1時間（再試行を抑える）。
 *
 * @param string      $key  トランジェント名。
 * @param array|false $data 取得結果。
 */
function _lish_update_cache_set($key, $data)
{
    if (is_array($data)) {
        set_site_transient($key, $data, 12 * HOUR_IN_SECONDS);
    } else {
        set_site_transient($key, 'error', HOUR_IN_SECONDS);
    }
}

add_filter('auto_update_theme', 'lish_auto_update_theme', 10, 2);
/**
 * Lish だけ自動更新を ON にする。メジャー版の更新は自動にしない。
 *
 * @param bool|null $update 自動更新するか（WP / 他フィルタの判定）。
 * @param object    $item   更新対象（theme / new_version）。
 * @return bool|null
 */
function lish_auto_update_theme($update, $item)
{
    if (!is_object($item) || !isset($item->theme) || $item->theme !== get_template()) {
        return $update;
    }

    // 管理画面のテーマ一覧（自動更新の表示）では new_version が無い
    if (empty($item->new_version)) {
        return (bool) apply_filters('lish/auto_update', true);
    }

    $current_major = (int) explode('.', LISH_VERSION)[0];
    $new_major     = (int) explode('.', (string) $item->new_version)[0];
    if ($new_major !== $current_major) {
        return false;
    }

    return (bool) apply_filters('lish/auto_update', true);
}

add_filter('auto_theme_update_send_email', 'lish_auto_update_send_email', 10, 2);
/**
 * Lish だけの自動更新ではクライアント宛の WP 標準メールを送らない。
 * （失敗時は lish_auto_update_notify_failure() が Lish 宛に送る）
 *
 * @param bool  $enabled        送るか。
 * @param array $update_results テーマの更新結果。
 * @return bool
 */
function lish_auto_update_send_email($enabled, $update_results)
{
    if (!$enabled || empty($update_results)) {
        return $enabled;
    }

    foreach ($update_results as $result) {
        if (!isset($result->item->theme) || $result->item->theme !== get_template()) {
            return $enabled; // 他テーマも更新された場合は WP 標準どおり
        }
    }
    return false;
}

add_action('automatic_updates_complete', 'lish_auto_update_notify_failure');
/**
 * Lish の自動更新が失敗したときだけ、Lish へメールする。
 *
 * @param array $update_results 種別ごとの自動更新結果。
 */
function lish_auto_update_notify_failure($update_results)
{
    if (empty($update_results['theme']) || !is_array($update_results['theme'])) {
        return;
    }

    foreach ($update_results['theme'] as $result) {
        if (!isset($result->item->theme) || $result->item->theme !== get_template()) {
            continue;
        }
        if ($result->result === true) {
            return;
        }

        $to = (string) apply_filters('lish/update_notify_email', 'web@lishinc.com');
        if ($to === '') {
            return;
        }

        $error = is_wp_error($result->result) ? $result->result->get_error_message() : '不明なエラー';
        $site  = home_url('/');
        $body  = implode("\n", array(
            'Lish 親テーマの自動更新に失敗しました。サイトは更新前の版で動いています。',
            '',
            'サイト: ' . $site,
            '現在の版: ' . LISH_VERSION,
            '更新しようとした版: ' . (isset($result->item->new_version) ? $result->item->new_version : '-'),
            'エラー: ' . $error,
            '',
            '管理画面の「ダッシュボード → 更新」から手動で更新するか、サーバの書き込み権限を確認してください。',
        ));

        wp_mail($to, '[Lish] 親テーマの自動更新に失敗: ' . wp_parse_url($site, PHP_URL_HOST), $body);
        return;
    }
}

add_action('admin_notices', 'lish_admin_notice_vcs');
/**
 * lish/ に .git があって自動更新が止まっているサーバーで、管理画面に知らせる（1.3.0〜）。
 *
 * 手元のフォルダを移行プラグイン等でそのまま上げると .git が入り、lish_check_for_update() が
 * 開発環境と判断して更新を出さなくなる。何も表示されないまま止まるのを防ぐ。
 * 開発用のホスト（localhost / *.local / *.test 等）では出さない。
 * ※ Local の wp_get_environment_type() は production を返すため、環境タイプではなくホストで判定する。
 */
function lish_admin_notice_vcs()
{
    if (!is_dir(get_template_directory() . '/.git') || !current_user_can('update_themes')) {
        return;
    }
    if (!apply_filters('lish/vcs_notice', !_lish_is_dev_host())) {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || !in_array($screen->id, array('dashboard', 'themes'), true)) {
        return;
    }

    echo '<div class="notice notice-warning"><p>'
        . esc_html__('親テーマ Lish のフォルダに .git があるため、自動更新が止まっています。GitHub Releases の lish-x.y.z.zip でテーマを置き換えてください。', 'lish')
        . '</p></div>';
}

/**
 * 開発用のホストか（localhost / 127.0.0.1 / *.local / *.test / *.localhost）。
 *
 * @return bool
 */
function _lish_is_dev_host()
{
    $host = (string) wp_parse_url(home_url(), PHP_URL_HOST);
    if (in_array($host, array('localhost', '127.0.0.1', '[::1]', '::1'), true)) {
        return true;
    }
    return (bool) preg_match('/\.(local|test|localhost)$/', $host);
}
