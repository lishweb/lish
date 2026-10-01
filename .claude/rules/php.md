---
name: lish-parent-php
description: 親テーマ lish の PHP 実装ルール
paths:
  - "**/*.php"
---

# 親テーマ PHP 実装ルール

## パス取得関数

親テーマのコードでは「これは親のファイルか、子（案件）のファイルか」を常に意識する。

| 対象 | 使う関数 |
|---|---|
| 親テーマ自身のアセット（vendor / lish-*.css / lish-ui.js） | `LISH_URI` / `lish_uri()` / `LISH_DIR` |
| 案件のアセット（ロゴ / OGP画像 / favicon / app.css） | `get_stylesheet_directory_uri()` / `lish_project_uri()` |
| 子で差し替え可能なテンプレートパーツ | `get_template_part()`（子優先で解決される） |

```php
// ✅ 親が同梱するライブラリ
wp_register_script('lish-splide', lish_uri('assets/js/vendor/splide.min.js'), ...);

// ✅ 案件が持つ OGP 画像
$default_image = get_stylesheet_directory_uri() . '/assets/img/common/og-image.jpg';
```

## テンプレートの構造

- `header.php` / `footer.php` は「`<head>` / `wp_head()` / `body` 開始・終了」など**更新し続けたい部分**だけを持つ
- 見た目は `get_template_part('inc/part-header')` に委譲する（子が同名ファイルを置けば差し替わる）
- **子に上書きされたくない親専用パーツは `inc/template-parts/` に置く**（子の `inc/part-*.php` と名前が衝突しないようにするため）

## 関数定義

- 公開関数は `lish_` プレフィックス、`docs/API.md` に記載
- 子で上書きさせたい関数は `function_exists()` ガードを付け、pluggable であることを `docs/API.md` に明記
- private は `_lish_` プレフィックス

## 差し替えポイント

新しい値・挙動を書くときは、必ず `apply_filters()` / `do_action()` を通す。

```php
$post_types = apply_filters('lish/wpautop_disabled_post_types', array('page'));
```

フック名は `lish/{領域}/{項目}` の形（例: `lish/ogp/default_image`）。

## 安全策

- 全ファイル冒頭に `if (!defined('ABSPATH')) { exit; }`
- `filemtime()` の前に `file_exists()`
- 子テーマにファイルが無くてもエラーにしない
- 外部通信は `is_wp_error()` を必ずチェックし、失敗時もサイトを壊さない

## エスケープ

出力は必ず `esc_html()` / `esc_attr()` / `esc_url()` / `wp_kses_post()` を通す。
翻訳文字列はテキストドメイン `'lish'`。

## 禁止

- 案件固有のデザイン・文言・Figma参照、特定の制作方法に依存するコード
- CPT / タクソノミー / ACF フィールドの登録
- `functions.php` への処理の追記（`inc/` に分割）
- 公開関数・フック・CSSクラス名の無断改名・削除（MAJOR + deprecated シム必須）
