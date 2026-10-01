---
name: lish-parent-absolute
description: 親テーマ lish の開発時に厳守する後方互換・API設計ルール
paths:
  - "**/*"
---

# 親テーマ `lish` 絶対ルール

このテーマは**既に稼働中の案件サイトが使っている共通基盤**。
1つの変更が全案件に波及する。**後方互換を壊さないことが最優先**。

## 1. 案件固有のもの・特定の制作方法に依存するものを入れない

- 特定案件のデザイン値・文言・Figma node 参照・ロゴ・カラーを入れない
- **特定の制作方法（Figma/MCP 等）に依存するコード・設定を入れない**
  - 子テーマ側は Figma Mode / AI Design Mode / Existing Design Mode の3つを想定するが、
    **親テーマはそのどれにも依存しない**
  - Figma MCP の設定（.mcp.json）・Figma キャッシュフック・視覚差分ツールは
    すべて**子テーマ側**の資産。親には置かない
- 「1案件で必要になった」は理由にならない
- 親に入れてよいのは「2案件以上で実発生」かつ「内容を分離できる」もののみ
- 判定フローは `CLAUDE.md` の「親に入れる / 入れない」を参照

## 2. プレフィックス

| 種別 | プレフィックス |
|---|---|
| 公開関数 | `lish_`（`docs/API.md` §2〜3 に記載） |
| フックコールバック | `lish_`（`remove_action` で外せるように。ただし公開APIではない → `docs/API.md` §10 に列挙） |
| private ヘルパー | `_lish_`（`docs/API.md` に載せない = 予告なく変更可） |
| 定数 | `LISH_` |
| フック / フィルタ | `lish/` |
| オプション / トランジェント | `lish_` |
| スクリプト・スタイルのハンドル | `lish-` |
| **子テーマ用（親では使わない）** | **`lish_child_`** |

## 3. 破壊的変更の扱い

- 公開関数・フック・CSSクラス名の**削除／改名は MAJOR でのみ**
- 削除前に必ず1メジャー分 `inc/compat/deprecated.php` にシムを置く
- シムは `lish_deprecated_function()` で `WP_DEBUG` 時のみ通知（本番では静かに動く）
- 既存フックの引数は**末尾追加のみ**（順序を変えない）

## 4. 子テーマから制御できるようにする

新機能には必ずフィルタ／アクションの差し替えポイントをセットで用意する。
「親を編集しないと変えられない」実装は作らない。

```php
// ❌ 悪い: 案件ごとに親を編集させる
$handles = array('lish-ui', 'lish-splide');

// ✅ 良い: 子テーマがフィルタで追加できる
$handles = apply_filters('lish/defer_handles', array('lish-ui', 'lish-splide'));
```

## 5. 壊れない実装

- 全ファイル冒頭に `if (!defined('ABSPATH')) { exit; }`
- `filemtime()` の前に必ず `file_exists()`
- 外部通信は**必ず失敗許容**（配信サーバが落ちてもサイトは動く）
- 要件不足の環境で **Fatal を起こさない**（管理通知＋機能縮退）
- 子テーマにファイルが無い前提で動く（`assets/css/app.css` が無くてもエラーにしない）

## 6. HTML / JS のクラスは「契約」

親が出力する CSS クラスと DOM 階層、JS のクラス契約は**公開 API**。
子テーマの SCSS がこれに依存している。変更は MAJOR で、`docs/UPGRADING.md` に対応表を書く。

## 7. バージョン管理

- `style.css` の `Version:` と `LISH_VERSION` 定数を**必ず同期**
- 変更は `CHANGELOG.md` に SemVer で記録
- 新しい公開関数・フックは `docs/API.md` に追記

## 8. 出力のエスケープ

- `esc_html()` / `esc_attr()` / `esc_url()` / `wp_kses_post()` を必ず通す
- 翻訳文字列はテキストドメイン `'lish'`

## 9. 禁止

- 案件デザインの持ち込み
- CPT / タクソノミー / ACF フィールドの登録（コンテンツ所有権は案件側）
- `functions.php` に処理を書く（`inc/` に分割する）
- 公開APIの無断改名・削除
- ユーザーの明示指示なしの `git commit` / `git push` / タグ付け
