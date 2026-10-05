# Changelog

Lish 親テーマの変更履歴。[Keep a Changelog](https://keepachangelog.com/ja/1.1.0/) 形式、
バージョニングは [Semantic Versioning](https://semver.org/lang/ja/) に従う。

- **PATCH** — バグ修正 / セキュリティ / 内部改善。API・HTML構造・CSSクラス名は変えない
- **MINOR** — 機能追加 / 新フック追加。既存の動作は変えない
- **MAJOR** — 破壊的変更（deprecated 削除 / HTML構造変更 / CSSクラス名変更 / 最低PHP・WP引き上げ）

---

## [Unreleased]

### Changed

- `docs/RELEASE.md`: Release label の説明を現在の GitHub 画面（None / Pre-release / Latest）に合わせた。本番配布は **Latest** を選ぶ（None だと最新版にならず配られない）

---

## [1.3.0] - 2026-10-05

SEO プラグインとの OGP 重複の防止と、`.git` で自動更新が止まったときの通知を追加。

### Changed

- **SEO プラグインが有効なら description / OGP / Twitter Card を出さない**（`inc/head.php`）
  - 対象: All in One SEO / Yoast SEO / Rank Math / SEOPress。出力元が2つになり値が食い違うのを防ぐ
  - `lish/enable_ogp` の既定値が「SEO プラグインが無ければ `true`」になった。両方出したい案件は子で `true` を返す（従来どおり最優先）
  - SEO プラグインを入れていない案件の出力は変わらない

### Added

- `lish_seo_plugin_active()` と `lish/seo_plugin_active` フィルタ（判定の上書き）
- **`.git` で自動更新が止まっているときの管理画面通知**（`inc/updater/updater.php`）
  - `lish/` に `.git` があるサーバーで、ダッシュボードとテーマ画面に警告を出す（`update_themes` 権限のユーザーのみ）
  - 開発用ホスト（localhost / `*.local` / `*.test` / `*.localhost`）では出さない。`lish/vcs_notice` で止められる
- `docs/RELEASE.md`: リリース作成時に「Set as the latest release」を確認する手順

---

## [1.2.1] - 2026-10-01

ドキュメントのみの更新（自動更新の初回動作確認を兼ねる）。テーマの動作・出力は変更していない。

### Changed

- `docs/RELEASE.md`: リリース作成画面の説明を現在の GitHub（Release label: None / Pre-release）に合わせた
- `docs/RELEASE.md`: サイトへ入れるときの注意（リリースの zip から入れる・手元のフォルダや移行プラグインで上げない）を追加。1.1.0 以前からの入れ直しの説明を削除（該当サイトなし）

---

## [1.2.0] - 2026-10-01

親テーマの自動更新（GitHub Releases 配信）を追加。既存の関数・フック・出力・CSS クラスは変更していない。

### Added

- **自動更新**（`inc/updater/updater.php`）
  - WP の更新チェックで GitHub（`lishweb/lish`）の最新リリースを確認し、添付の `lish-x.y.z.zip` で親テーマだけを自動更新
  - 新しい版の `style.css` から `Requires at least` / `Requires PHP` を読み、要件を満たさないサイトには入れない
  - メジャー版（2.0.0 等）は自動更新しない（管理画面から手動で更新）
  - `lish/` に `.git` がある環境（開発環境）では更新を出さない（WP 本体の判定は `themes/` より上しか見ないため）
  - 成功時のメールは送らない。失敗時のみ `web@lishinc.com` へ通知
  - 管理画面の「再確認」ではキャッシュを使わずに GitHub を見に行く
- フィルタ `lish/auto_update` `lish/update_notify_email`
- 配布の手順書 `docs/RELEASE.md`
- 配布 zip から開発用ファイルを外す `export-ignore`（`.gitattributes`）

### Changed

- `Theme URI` / `Update URI` を `https://github.com/lishweb/lish` に変更（リポジトリ移転）
- `lish/update_source` は旧方式として残す（値があれば従来どおりその JSON を使う）

### 導入時の注意

- 1.1.0 以前には更新の仕組みが無いため、**1.2.0 だけは各サイトへ手動で入れる**。以降は自動。

---

## [1.1.0] - 2026-09-25

ブログ記事で使うデザインパターン（ブロックスタイル・パターン・文字装飾・独自ブロック）を追加。
既存の関数・フック・出力・CSS クラスは変更していない（追加のみ）。

### Added

- **ブログ用ブロック**（`inc/blocks/` / `assets/css/lish-blocks.css` / `assets/js/lish-blocks*.js`）
  - パターンカテゴリ「Lish ブログ」と 24 パターン（ボックス・チェックリスト・番号リスト・見出し・表・比較表・著者・ボタン・X 共有・URL コピー・記事カード・あわせて読みたい・記事一覧・目次 4 種）
  - ブロックスタイル（core/button・group・list・heading・table）
  - 文字装飾（ツールバー「Lish 装飾」）: 傍点・丸ラベル・点滅・メリット／デメリット・評価マーク ◎○△×・星評価・小ボタン
  - 独自ブロック `lish/post-card`（記事カード。URL から記事のアイキャッチ・タイトル・日付を描画時に取得）
  - 独自ブロック `lish/toc`（目次。本文の見出しから生成し、目次がある記事だけ見出しに `lish-toc-{n}` の id を付与）
  - X 共有ボタンのリンク先を記事の共有 URL に自動差し替え
  - 同じ CSS を編集画面にも読み込み、書いている時点で完成形と同じ見た目にする
  - 色は CSS 変数 `--lish-blocks-*` の既定値のみ。案件は子テーマの `app.css` で上書きする
- ハンドル `lish-blocks`（style / script）、`lish-blocks-editor`（script）
- フィルタ `lish/blocks/enable` `lish/blocks/patterns` `lish/blocks/formats` `lish/blocks/enqueue_front`
  `lish/toc/levels` `lish/toc/min_headings` `lish/share/x_url` `lish/post_card/data`
- `npm run build:blocks`（`lish-blocks.scss` のみをビルドする）

### Notes

- 著者ボックスのパターンは `core/details` を使うため WordPress 6.3 以降でのみ登録される。
  X 共有 / URL コピーのリンク差し替えと目次の見出し id 付与は `WP_HTML_Tag_Processor`（6.2〜）が無い環境では行わない。

### Changed

- `package.json` の browserslist を `last 2 versions, not dead, not kaios > 0` に変更。
  従来の `last 2 versions` では IE 10/11・KaiOS 向けのプレフィックスが付き、`npm run build` で `lish-foundation.css` が
  1.0.0 の成果物と変わってしまっていた。変更後は `lish-foundation.css` の出力が 1.0.0 と完全に一致する。

---

## [1.0.0] - 2026-09-08

初回リリース。従来の `base-theme`（シングルテーマ）を親テーマ + 子テーマ構成へ分割したもの。

### Added

- **親子テーマ基盤**
  - `LISH_VERSION` / `LISH_DIR` / `LISH_URI` / `LISH_MIN_PHP` / `LISH_MIN_WP` 定数
  - 動作要件チェック（PHP 8.0 / WP 6.0）。要件不足時は Fatal を起こさず管理画面通知＋機能縮退
  - 子テーマの `Lish Requires:` ヘッダーを読み、親が古い場合に管理画面通知
  - `style.css` に `Update URI:` を追加（wordpress.org の同名テーマによる誤更新を防止）
- **テンプレートの差し替え点**
  - 親 `header.php` が `<head>` / OGP / `wp_head()` / `body` 開始を持ち、見た目は `inc/part-header.php` に委譲
  - 親 `footer.php` も同様に `inc/part-footer.php` に委譲
  - `404.php` は子の `inc/part-404.php` があればそれを使う
  - アクション `lish/head` `lish/body_open` `lish/main_open` `lish/main_close` `lish/body_close`
- **アセット基盤**
  - ハンドル `lish-foundation` / `lish-entry-content` / `lish-ui` / `lish-splide` / `lish-ajaxzip`
  - `lish/defer_handles` フィルタ（子テーマの JS に `defer` を付けられる）
  - `lish/webfonts` フィルタ（`display=swap` と `preconnect` は自動）
  - `lish_enqueue_splide()` / `lish_enqueue_ajaxzip()`（必要な案件だけ読み込む）
- **テンプレートタグ**
  - `lish_breadcrumb()` / `lish_get_breadcrumb_items()`
  - `lish_pagination()`
  - `lish_excerpt()`
  - `lish_post_thumbnail()`（LCP 候補の `fetchpriority` 対応）
  - `lish_uri()` / `lish_project_uri()`
- **セキュリティ（最低限・すべてフィルタで無効化可）**
  - `<meta name="generator">` / RSD / wlwmanifest / 短縮URL / `X-Pingback` の除去
  - XML-RPC の無効化
  - ログインエラーメッセージの汎用化
- **互換レイヤ**
  - `inc/compat/{php,wp,deprecated}.php`
  - `lish_deprecated_function()`（`WP_DEBUG` 時のみ通知）
- **更新配信の受け口**
  - `inc/updater/updater.php`（v1.0 は no-op。`lish/update_source` が空なら何もしない）
  - 通信は失敗許容・12時間キャッシュ
- **共通CSS/JS**
  - `assets/css/lish-foundation.css`（destyle + ルートフォント + 汎用リセット）
  - `assets/css/lish-entry-content.css`
  - `assets/js/lish-ui.js`（ドロワー開閉 / ヘッダーのスクロール状態）
  - 同梱ライブラリ: Splide / ajaxzip3
- **ドキュメント**
  - `docs/API.md` / `docs/UPGRADING.md` / `docs/CHILD-THEME.md` / `CLAUDE.md`

### Changed（base-theme からの移行）

- 全関数に `lish_` プレフィックスを付与（グローバル名前空間の衝突回避）
  - `wpautop_filter()` → `lish_disable_wpautop()`
  - `get_the_custom_excerpt()` → `lish_excerpt()`
  - `my_theme_breadcrumbs()` → `lish_breadcrumb()`
- ハードコードされていた設定値をフィルタ化（フック 39 個）
  - `$defer_handles` 配列 → `lish/defer_handles`
  - 固定ページの `wpautop` 抑止 → `lish/wpautop_disabled_post_types`
  - ブロックエディタ無効化のコメントアウト → `lish/disable_block_editor_slugs`
- 記事本文のセレクタを `.p-article_content` → `.c-entry-content` に統一
  （テンプレートが出力するクラスと一致しておらず、スタイルが適用されていなかった）
- パンくずの CSS クラスを `p-breadcrumb` → `c-breadcrumb` に変更（親提供 = `c-` が妥当なため）
- パンくずの schema.org URL を `http` → `https` に修正
- Splide / ajaxzip3 を常時 enqueue → register のみに変更（必要な案件だけ読み込む）
- SCSS のビルド出力先を `style.css` → 親 `assets/css/lish-foundation.css` / 子 `assets/css/app.css` に分離
  （`style.css` はテーマ認識用ヘッダー専用になった）
- `main { overflow: hidden }` を親から子テーマへ移動
  （横スクロール防止はデザイン判断であり、かつ子孫の `position: sticky` を無効化する副作用があるため。
  親に残したのは `main { display: block }` のみ）

### Fixed

- `header.php` の `<head>` 内で `setup_postdata()` を呼んでいた問題を修正
  （グローバル状態を汚染していた。メインクエリの singular では不要）

### Removed

- 案件固有だったコード
  - `js-history-track`（沿革 横スクロール年表）の JS
  - 前案件のデザインを含む `c-page-header` / `c-heading` / `c-btn-contact` / `c-btn-more` の SCSS
- 未参照だった `assets/css/ajax-loader.gif`

### Deprecated

- `get_the_custom_excerpt()` → `lish_excerpt()`（2.0.0 で削除予定）
- `my_theme_breadcrumbs()` → `lish_breadcrumb()`（2.0.0 で削除予定）

[1.3.0]: https://github.com/lishweb/lish/releases/tag/v1.3.0
[1.2.1]: https://github.com/lishweb/lish/releases/tag/v1.2.1
[1.2.0]: https://github.com/lishweb/lish/releases/tag/v1.2.0
[1.1.0]: https://github.com/lishweb/lish/releases/tag/v1.1.0
[1.0.0]: https://github.com/lishweb/lish/releases/tag/v1.0.0
