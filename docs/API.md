# Lish 公開 API リファレンス

対象バージョン: **Lish 1.2.1**

子テーマ（案件）から使える関数・フック・クラス契約の一覧。
**ここに載っているものだけが「公開 API」**で、後方互換が保証される（変更は MAJOR バージョンのみ）。

> このファイルは子テーマ側に `docs/lish-api.md` としてコピー配布される。
> 親テーマを更新したら、子テーマ側のコピーも同期すること。

---

## 0. 大原則

- **親テーマ `lish` のファイルは編集しない**。案件固有の変更は、下記のフック経由で子テーマから注入する。
- 子の `functions.php` は**親より先に読み込まれる**。親の関数を呼ぶときは必ず**フック内**で、`function_exists()` を確認してから。
- 親の関数を**子で再定義しない**（Fatal）。子の関数は `lish_child_*` プレフィックスを使う。

```php
// ❌ Fatal: 子の functions.php トップレベルで親の関数を呼ぶ
lish_enqueue_splide();

// ✅ フック内で、存在確認してから
add_action('wp_enqueue_scripts', function () {
    if (function_exists('lish_enqueue_splide')) {
        lish_enqueue_splide();
    }
}, 20);
```

---

## 1. 定数

| 定数 | 内容 |
|---|---|
| `LISH_VERSION` | 親テーマのバージョン（`style.css` の `Version:` と同期） |
| `LISH_DIR` | 親テーマのサーバパス（`get_template_directory()`） |
| `LISH_URI` | 親テーマの URI（`get_template_directory_uri()`） |
| `LISH_MIN_PHP` | 最低 PHP バージョン（`8.0`） |
| `LISH_MIN_WP` | 最低 WordPress バージョン（`6.0`） |

案件アセットのパスにこれらを使わないこと（親テーマを指す）。案件側は `get_stylesheet_directory_uri()`。

---

## 2. テンプレートタグ（テンプレートから呼ぶ関数）

### `lish_breadcrumb()`

パンくずを出力する。HTML 構造は親、見た目（`c-breadcrumb`）は子の SCSS で作る。

```php
<?php if (function_exists('lish_breadcrumb')) { lish_breadcrumb(); } ?>
```

フロントページでは既定で出力しない（`lish/breadcrumb/show_on_front` で変更可）。

### `lish_get_breadcrumb_items(): array`

パンくずの項目配列を返す（描画しない）。独自のマークアップを組みたい場合に使う。

```php
// array( array('label' => 'ホーム', 'url' => 'https://...'), ..., array('label' => '現在地', 'url' => '') )
$items = lish_get_breadcrumb_items();
```

### `lish_pagination( array $args = array() )`

ページネーションを出力する（`the_posts_pagination` のラッパー、クラスは `c-pagination`）。

```php
<?php if (function_exists('lish_pagination')) { lish_pagination(); } ?>
```

### `lish_excerpt( string $content, int $length = 70, string $more = '...' ): string`

本文から抜粋を生成する。ショートコード・タグ・`&nbsp;` を除去し、書記素単位で長さを判定する。

```php
<?php echo esc_html(lish_excerpt(get_the_content(), 60)); ?>
```

### `lish_post_thumbnail( bool $is_lcp = false, string $size = 'large', array $attr = array() )`

アイキャッチを出力する。`$is_lcp = true` で `fetchpriority="high"` を付与し `loading="lazy"` を外す。

```php
<?php lish_post_thumbnail(true); ?>   // ファーストビューの1枚目
<?php lish_post_thumbnail(); ?>       // それ以外（lazy + async）
```

### `lish_head_meta()`

`<head>` の共通メタ（charset / viewport / OGP / favicon / Webフォント）を出力する。
**親の `header.php` が呼ぶ**ので、子テーマから呼ぶ必要はない。

### `lish_uri( string $path = '' ): string` / `lish_project_uri( string $path = '' ): string`

| 関数 | 指す先 |
|---|---|
| `lish_uri('assets/js/vendor/splide.min.js')` | **親テーマ** `lish` |
| `lish_project_uri('assets/img/logo.webp')` | **子テーマ（案件）** |

案件アセットは `lish_project_uri()` または `get_stylesheet_directory_uri()` を使う。

---

## 3. アセット読み込み

### スクリプト・スタイルのハンドル

| ハンドル | 種別 | 内容 | 既定 |
|---|---|---|---|
| `lish-foundation` | style | destyle + ルートフォント + 汎用リセット | **自動 enqueue** |
| `lish-entry-content` | style | 記事本文の基本装飾（`.c-entry-content`） | **自動 enqueue** |
| `lish-ui` | script | ドロワー開閉 / ヘッダーのスクロール状態 | **自動 enqueue** |
| `lish-splide` | script + style | Splide 本体 + 構造CSS | register のみ（下記関数で enqueue） |
| `lish-ajaxzip` | script | 郵便番号→住所変換 | register のみ（下記関数で enqueue） |
| `lish-blocks` | style | ブログ用ブロックの見た目（§4「ブログ用ブロック」） | 投稿・固定ページの詳細と編集画面で**自動 enqueue**（1.1.0〜） |
| `lish-blocks` | script | URL コピー / 目次の「もっと見る」 | **使うブロックがあるページだけ**自動 enqueue・`defer` 付き（1.1.0〜） |
| `lish-blocks-editor` | script | 編集画面：文字装飾・記事カード・目次 | 編集画面で自動 enqueue（1.1.0〜） |

子テーマの CSS/JS は `deps` にこれらを指定して順序を保証する。

```php
wp_enqueue_style('child-app', LISH_CHILD_URI . '/assets/css/app.css', array('lish-foundation'), filemtime($app_css));
wp_enqueue_script('child-app', LISH_CHILD_URI . '/assets/js/app.js', array('lish-ui'), filemtime($app_js), true);
```

親の register は優先度 **5**。子テーマの enqueue は優先度 **20** で登録すること。

### `lish_enqueue_splide()` / `lish_enqueue_ajaxzip()`

同梱ライブラリを読み込む。**使う案件だけ**呼ぶ（常時読み込みしないことでパフォーマンスを保つ）。

```php
add_action('wp_enqueue_scripts', function () {
    if (function_exists('lish_enqueue_splide')) { lish_enqueue_splide(); }
}, 20);
```

### `lish_asset_version( string $relative_path )`

親テーマ内ファイルの `filemtime`（無ければ `LISH_VERSION`）を返す。親テーマ内部用。

---

## 4. フィルタ

### アセット

| フィルタ | 既定値 | 用途 |
|---|---|---|
| `lish/defer_handles` | `['lish-ui','lish-splide','lish-ajaxzip']` | **`defer` を付けるハンドル。子の JS を追加する** |
| `lish/deregister_jquery` | `true` | フロントで jQuery を読み込まない設定の on/off |
| `lish/enqueue_entry_content_css` | `true` | 記事本文CSSを読むかどうか |
| `lish/webfonts` | `''` | **Google Fonts の URL。`display=swap` は自動付与** |
| `lish/webfonts_preconnect` | `true` | `preconnect` を出力するか |

```php
// 子の JS に defer を付ける（必須）
add_filter('lish/defer_handles', function ($handles) {
    $handles[] = 'child-app';
    return $handles;
});

// Webフォント
add_filter('lish/webfonts', function () {
    return 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700';
});
```

### テーマ設定

| フィルタ | 既定値 | 用途 |
|---|---|---|
| `lish/theme_supports` | `post-thumbnails` / `title-tag` / `automatic-feed-links` / `responsive-embeds` / `html5` | テーマサポートの増減 |
| `lish/nav_menus` | `['primary' => …, 'footer' => …]` | ナビメニューの登録位置 |
| `lish/image_sizes` | `[]` | **`add_image_size` の追加** |

```php
add_filter('lish/image_sizes', function ($sizes) {
    $sizes['card'] = array('width' => 600, 'height' => 400, 'crop' => true);
    return $sizes;
});
```

### `<head>` / OGP

| フィルタ | 既定値 | 用途 |
|---|---|---|
| `lish/ogp` | 算出済みの配列 | **OGP 情報を丸ごと差し替え**（`title` / `description` / `type` / `url` / `image` / `site_name`） |
| `lish/ogp/default_description` | キャッチフレーズ | サイト既定の紹介文 |
| `lish/ogp/default_image` | `{子テーマ}/assets/img/common/og-image.jpg` | OGP 既定画像 |
| `lish/ogp/twitter_card` | `summary_large_image` | Twitter Card 種別 |
| `lish/ogp/twitter_site` | `''` | X アカウント（`@` 不要） |
| `lish/ogp/fb_admins` / `lish/ogp/fb_app_id` | `''` | Facebook 連携 |
| `lish/enable_ogp` | `true` | OGP 出力の on/off（SEOプラグインを使う場合は `false`） |
| `lish/favicon` | `/assets/img/icon/favicon.ico` | 子テーマ内の favicon パス。ファイルが無ければ出力しない |
| `lish/touch_icon` | `/assets/img/icon/touch.png` | 同上 |
| `lish/disable_telephone_detection` | `true` | `format-detection` メタの出力 |

### 本文 / エディタ

| フィルタ | 既定値 | 用途 |
|---|---|---|
| `lish/wpautop_disabled_post_types` | `['page']` | 自動整形（`<p>` 付与）を止める投稿タイプ |
| `lish/disable_block_editor_slugs` | `[]` | ブロックエディタを切る固定ページのスラッグ |
| `lish/disable_block_editor_post_types` | `[]` | ブロックエディタを切る投稿タイプ |

### ブログ用ブロック（1.1.0〜）

記事本文で使うブロックスタイル・パターン（編集画面の「パターン」→「Lish ブログ」）・文字装飾（ツールバー「Lish 装飾」）・独自ブロック（記事カード `lish/post-card` / 目次 `lish/toc`）。
クラス名は §7「ブログ用ブロック」を参照。

| フィルタ | 既定値 | 用途 |
|---|---|---|
| `lish/blocks/enable` | `true` | 機能全体の on/off（`false` でスタイル・パターン・独自ブロック・CSS/JS をすべて止める） |
| `lish/blocks/patterns` | 全パターンのスラッグ | 登録するパターンを減らす（例 `'toc-band'`。登録名は `lish/{スラッグ}`） |
| `lish/blocks/formats` | 全装飾の名前 | 使う文字装飾を減らす（例 `'lish/blink'`） |
| `lish/blocks/enqueue_front` | `is_singular()` | フロントで `lish-blocks` CSS を読む条件（一覧で本文を出す案件は広げる） |
| `lish/toc/levels` | `[2, 3]` | 目次の既定の見出しレベル |
| `lish/toc/min_headings` | `3` | 目次を出す最小の見出し数 |
| `lish/share/x_url` | 生成した共有 URL | X 共有ボタンのリンク先（引数: `$url`, `$post`） |
| `lish/post_card/data` | 記事から作った配列 | 記事カードの表示内容（`url` / `title` / `date` / `date_iso` / `image` / `post_id`。引数: `$data`, `$url`） |

```php
// 点滅の装飾を使わない
add_filter('lish/blocks/formats', function ($names) {
    return array_diff($names, array('lish/blink'));
});

// 目次に H4 も入れる
add_filter('lish/toc/levels', function () {
    return array(2, 3, 4);
});
```

個別のブロックスタイルを外す場合は、`init`（優先度 11 以降）で `unregister_block_style('core/group', 'lish-box-stripe')` のように呼ぶ。

### パンくず / ページネーション

| フィルタ | 既定値 | 用途 |
|---|---|---|
| `lish/breadcrumb/items` | 算出済みの配列 | **パンくずの項目を追加・削除・並べ替え** |
| `lish/breadcrumb/home_label` | `ホーム` | 先頭のラベル |
| `lish/breadcrumb/show_home` | `true` | 先頭に「ホーム」を出すか |
| `lish/breadcrumb/show_on_front` | `false` | フロントページでも出すか |
| `lish/pagination/args` | `mid_size:2` ほか | `the_posts_pagination` の引数 |

```php
add_filter('lish/breadcrumb/items', function ($items) {
    if (is_singular('works')) {
        array_splice($items, 1, 0, array(array('label' => '実績', 'url' => home_url('/works/'))));
    }
    return $items;
});
```

### セキュリティ（すべて `true` が既定 = 有効）

| フィルタ | 用途 |
|---|---|
| `lish/security/remove_generator` | `<meta name="generator">` の除去 |
| `lish/security/remove_legacy_links` | RSD / wlwmanifest リンクの除去 |
| `lish/security/remove_shortlink` | 短縮URL（`wp-shortlink`）の除去 |
| `lish/security/remove_pingback_header` | `X-Pingback` ヘッダーの除去 |
| `lish/security/disable_xmlrpc` | XML-RPC の無効化 |
| `lish/security/generic_login_error` | ログインエラーの汎用化 |

```php
// Jetpack など XML-RPC が必要な案件
add_filter('lish/security/disable_xmlrpc', '__return_false');
```

### 更新配信（1.2.0〜 GitHub Releases から自動更新）

親テーマは GitHub（`lishweb/lish`）の最新リリースを WP の更新チェック（1日2回）で調べ、**親テーマだけを自動で差し替える**。
子テーマ・`wp-config.php` の設定は不要。メジャー版（2.0.0 等）は自動更新しない。
`lish/` に `.git` がある環境（開発環境）では更新を出さない。配布の手順は `docs/RELEASE.md`。

| フィルタ | 既定値 | 用途 |
|---|---|---|
| `lish/auto_update` | `true` | `false` でそのサイトだけ自動更新を止める（更新は管理画面から手動で可能） |
| `lish/update_notify_email` | `'web@lishinc.com'` | 自動更新が**失敗したとき**の通知先。空文字で通知しない。成功時は誰にも送らない |
| `lish/update_source` | `''` | 更新情報 JSON の URL（旧方式）。値があれば GitHub ではなくこの JSON を使う |

```php
// 検証環境で自動更新を止める
add_filter('lish/auto_update', '__return_false');
```

---

## 5. アクション

| アクション | 発火位置 |
|---|---|
| `lish/head` | `<head>` 内、`wp_head()` の直前 |
| `lish/body_open` | `<body>` 直後（`wp_body_open()` の直後） |
| `lish/main_open` | `<main class="l-main">` の直後 |
| `lish/main_close` | `</main>` の直前 |
| `lish/body_close` | `wp_footer()` の直前 |

```php
add_action('lish/body_open', function () {
    get_template_part('inc/part-gtm-noscript');
});
```

---

## 6. テンプレートパーツの差し替え

親テーマのテンプレートは、次のパーツを `get_template_part()` で呼ぶ。
**子テーマに同名ファイルを置くと差し替わる**（親のテンプレート本体は上書きせずに済む）。

| パーツ | 呼び出し元 | 用途 |
|---|---|---|
| `inc/part-header.php` | 親 `header.php` | **ヘッダーの見た目** |
| `inc/part-footer.php` | 親 `footer.php` | **フッターの見た目** |
| `inc/part-404.php` | 親 `404.php` | 404 のデザイン（あれば使われる） |

これが「デザインの自由度」と「親テーマの更新」を両立させる仕組み。
`header.php` / `footer.php` そのものを子にコピーすると、`<head>` や `wp_head()` 周りの
親の改善が届かなくなるので**コピーしないこと**。

> 親テーマ専用のパーツは `inc/template-parts/` に置いてある。
> 子テーマのパーシャルは `inc/part-*.php` を使い、名前を衝突させないこと。

---

## 7. HTML / CSS クラス契約

親テーマが出力する CSS クラスは**公開 API**。子テーマの SCSS がこれに依存するため、変更は MAJOR。

| クラス | 出力元 | 子テーマがやること |
|---|---|---|
| `c-breadcrumb` `__list` `__item` `__link` `__current` | `lish_breadcrumb()` | 見た目（SCSS） |
| `c-pagination`（内部は `the_posts_pagination` の `.nav-links` / `.page-numbers`） | `lish_pagination()` | 見た目（SCSS） |
| `c-entry-content` | 親テンプレート | 親が基本装飾を持つ。差分のみ追記 |
| `c-entry__title` `c-entry__meta` `c-entry__date` `c-entry__thumbnail` `c-entry__header` | 親 `single.php` / `page.php` | 見た目（上書きする場合） |
| `c-post-list` `__item` `__link` `__date` `__title` | 親 `archive.php` / `search.php` | 見た目 |
| `c-archive__header` `c-archive__title` | 親 `archive.php` / `search.php` | 見た目 |
| `c-notfound` `__title` `__text` `__button` | 親 `404.php` | 見た目 |
| `c-searchform` `__label` `__input` `__submit` | 親 `searchform.php` | 見た目 |
| `l-main` | 親 `header.php` | 見た目 |

> これらを子の CSS で `!important` で打ち消さないこと。親のバグ修正・a11y 改善が効かなくなる。

### ブログ用ブロック（1.1.0〜 / `lish-blocks.css`）

**色は CSS 変数で案件ごとに上書きする**（親は既定値だけを持つ）。子の `app.css`（`lish-blocks` より後に読まれる）で `:root` を上書きすればよい。

```scss
:root {
  --lish-blocks-main: #{$color-main};     // ボタン・見出しの線・ラベル・目次・表の見出し列
  --lish-blocks-accent: #{$color-accent}; // 丸ラベル・アクセント枠・△
}
```

| 変数 | 既定値 | 使う場所 |
|---|---|---|
| `--lish-blocks-main` | `#009ef3` | ボタン・見出しの線・ラベル・目次・記事カード・表の見出し列 |
| `--lish-blocks-accent` | `#ffb36b` | 丸ラベル・アクセント枠・評価マーク △ |
| `--lish-blocks-sub` | `#8bb2da` | 角タイトル付きボックス・著者ボックス |
| `--lish-blocks-text` | `#333333` | ボックス内の文字・番号・要点ボックスのラベル |
| `--lish-blocks-bg` | `#f5f5f5` | 目次・要点ボックス・比較表の見出しの背景 |
| `--lish-blocks-bg-soft` | `#fffded` | アクセント枠・斜線背景 |
| `--lish-blocks-border` | `#cdcdcd` | 点線・区切り線・記事カードの枠 |
| `--lish-blocks-merit` / `--lish-blocks-demerit` | `#07a825` / `#e66b6a` | メリット・◎○ / デメリット・× |
| `--lish-blocks-link` | `#4f96f6` | 点滅 |
| `--lish-blocks-star` | `#f5b301` | 星評価 |
| `--lish-blocks-share-x` | `#000000` | X 共有ボタン |
| `--lish-blocks-radius` | `6px` | ボックス・表・目次・記事カードの角丸 |

| 種類 | クラス |
|---|---|
| ボタン（core/button） | `is-style-lish-triangle` / `is-style-lish-share-x` / `is-style-lish-copy-url` |
| ボックス（core/group） | `is-style-lish-box-title` / `-box-corner` / `-box-summary` / `-box-check` / `-box-accent` / `-box-stripe` / `-box-message`、ラベル段落 `lish-box-label`、修飾 `lish-author` `lish-related` |
| リスト（core/list） | `is-style-lish-check` / `is-style-lish-number-line` |
| 見出し（core/heading） | `is-style-lish-line-left` / `is-style-lish-check-icon` |
| 表（core/table） | `is-style-lish-head-col` / `is-style-lish-compare` |
| 文字装飾（`<span>`） | `lish-emphasis` `lish-label` `lish-blink` `lish-merit` `lish-demerit` `lish-mark-double-circle` `lish-mark-circle` `lish-mark-triangle` `lish-mark-cross` `lish-rate` `lish-inline-btn` |
| 記事カード（lish/post-card） | `lish-post-card` `__link` `__label` `__thumb` `__body` `__title` `__date` `__more`、スタイル `is-style-lish-card-b` |
| 目次（lish/toc） | `lish-toc` `__title` `__details` `__body` `__list` `__sub` `__item` `__more`、状態 `lish-toc--collapsible` `is-collapsed`、スタイル `is-style-lish-toc-band` / `-circle` / `-minimal`、見出しの id `lish-toc-{n}` |
| 記事一覧（パターン） | `lish-post-list` `__large` `__small` `__body` |

> 本文に保存されるクラス名なので、変更すると**既存の記事の見た目が崩れる**。変更は MAJOR。

---

## 8. JavaScript クラス契約

親の `lish-ui.js` が担当する挙動。**子テーマで再実装しない**。

| 役割 | クラス / 属性 |
|---|---|
| ハンバーガーボタン | `.js-hamburger`（`aria-expanded` / `aria-label` を自動更新） |
| ドロワー本体 | `.js-drawer`（`aria-hidden` を自動更新） |
| 暗幕オーバーレイ | `.js-drawer-overlay`（クリックで閉じる） |
| 開いている状態 | `body.js-drawer-open` |
| スクロール後の状態 | `body.js-header-scrolled`（`scrollY > 10` で付与） |

**挙動**: クリックでトグル / ドロワー内リンククリックで閉じる / オーバーレイクリックで閉じる /
Esc キーで閉じる / 769px 以上へリサイズしたら閉じる。

**カスタムイベント**: 開閉時に `document` へ `lish:drawer` が発火する。

```js
document.addEventListener('lish:drawer', (e) => {
  // e.detail.open === true / false
});
```

---

## 9. 子テーマ側の予約プレフィックス

| プレフィックス | 所有者 |
|---|---|
| `lish_` `_lish_` `LISH_` `lish/` `lish-` | **親テーマ**（子で再定義・再利用しない） |
| `lish_child_` `LISH_CHILD_` | **子テーマ**（親テーマは使わない） |

---

## 10. 内部関数（呼ばないこと）

以下は WordPress のフックコールバックとして登録されている関数。
**子テーマから直接呼ばない**。`remove_action()` / `remove_filter()` で外す用途にのみ名前を使ってよく、
その場合も名前は MAJOR バージョンで変わりうる（公開 API ではない）。

```
lish_setup / lish_load_child_textdomain / lish_register_assets / lish_add_defer_attribute
lish_render_meta_charset / lish_render_ogp / lish_render_icons / lish_render_webfonts / lish_get_ogp_data
lish_disable_wpautop / lish_maybe_disable_block_editor
lish_security_cleanup / lish_disable_xmlrpc / lish_remove_pingback_header / lish_generic_login_error
lish_meets_requirements / lish_admin_notice_requirements / lish_admin_notice_child_requirement
lish_blocks_register_assets / lish_blocks_enqueue_front / lish_blocks_enqueue_editor / lish_blocks_defer_handles
lish_blocks_register_styles / lish_blocks_register_patterns / lish_blocks_enqueue_editor_script
lish_blocks_render_share_button / lish_blocks_register_post_card / lish_blocks_render_post_card
lish_blocks_register_toc / lish_blocks_render_toc / lish_toc_add_heading_ids
lish_check_for_update / lish_fetch_update_info / lish_update_source / lish_deprecated_function
lish_auto_update_theme / lish_auto_update_send_email / lish_auto_update_notify_failure
```

挙動を変えたい場合は、まず**フィルタで解決できないか**を確認する（§4）。

---

## 11. 非推奨（deprecated）

| 旧名 | 代替 | 廃止予定 |
|---|---|---|
| `get_the_custom_excerpt()` | `lish_excerpt()` | 2.0.0 |
| `my_theme_breadcrumbs()` | `lish_breadcrumb()` | 2.0.0 |

`WP_DEBUG` が有効なときのみ `E_USER_DEPRECATED` で通知される（本番では静かに動く）。

---

## 12. 「親を触りたくなったら」

| やりたいこと | 正しい方法 |
|---|---|
| 案件JSに defer を付ける | `lish/defer_handles` |
| Webフォントを変える | `lish/webfonts` |
| OGP画像・紹介文を変える | `lish/ogp/default_image` / `lish/ogp/default_description` |
| 画像サイズを追加する | `lish/image_sizes` |
| パンくずの項目を変える | `lish/breadcrumb/items` |
| ヘッダー/フッターの見た目を作る | `inc/part-header.php` / `inc/part-footer.php` |
| 404 のデザインを当てる | `inc/part-404.php` |
| ブロックエディタを切る | `lish/disable_block_editor_slugs` |
| Splide を読み込む | `lish_enqueue_splide()` |
| GTM の noscript を body 直後に入れる | `lish/body_open` |

一覧に無いことをやりたくなったら、**実装せず停止して報告する**。
親テーマへのフック追加は、バージョンを上げる別作業になる。
