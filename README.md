# Lish — 自社共通 WordPress 親テーマ

株式会社 Lish の全案件で共有する WordPress 親テーマ。
**制作方法に依存しない、自社 WordPress サイト制作・保守の共通基盤**。
案件のデザインは**子テーマ**（`lish-child` をコピーしたもの）で実装する。

> **Lish は「Figma から WordPress を作るテーマ」ではない。**
> Figma / MCP は Lish の機能ではなく、Lish を利用した**案件制作方法の1つ**。
> 子テーマ側は Figma Mode / AI Design Mode / Existing Design Mode の3つの制作方法を想定しており、
> **親テーマはどの制作方法にも依存しない**（Figma 固有のコードを親に持たない）。

```
wp-content/themes/
├── lish/          ← このリポジトリ（親テーマ）
└── <案件名>/      ← 子テーマ（lish-child のコピー）
```

---

## 何を提供するか

| 領域 | 内容 |
|---|---|
| テーマ初期設定 | `add_theme_support` / `register_nav_menus` / 画像サイズ / i18n |
| アセット基盤 | enqueue 基盤、`defer` 自動付与、Webフォント、jQuery deregister |
| 共通ライブラリ | Splide（スライダー既定） / ajaxzip3（郵便番号→住所） |
| 共通CSS | destyle / ルートフォント（rem = 10px 基準） / 汎用リセット / 記事本文 |
| 共通JS | ドロワー開閉 / ヘッダーのスクロール状態 |
| テンプレートタグ | パンくず / ページネーション / 抜粋 / アイキャッチ |
| `<head>` | OGP / Twitter Card / favicon / Webフォント |
| セキュリティ | 情報露出の除去（generator / RSD / shortlink / X-Pingback / XML-RPC / ログインエラー） |
| 互換レイヤ | PHP / WP バージョン互換シム、deprecated 管理 |
| 更新基盤 | 更新通知の受け口（配信サーバは未実装。フック配線のみ） |

**提供しないもの**: 案件のデザイン、カラー、フォント、セクションレイアウト、CPT、ACF。
これらは子テーマの領分。

---

## 動作要件

| | バージョン |
|---|---|
| WordPress | 6.0 以上 |
| PHP | 8.0 以上 |

要件を満たさない環境では **Fatal を起こさず**、管理画面に通知を出して機能を縮退させる。

---

## 導入

1. このリポジトリを `wp-content/themes/lish/` に配置する
   - **フォルダ名は必ず小文字の `lish`**。子テーマの `Template: lish` と一致させる必要があり、本番（Linux）は大文字小文字を区別する
2. 有効化するのは**子テーマ**。親テーマ単体を有効化しても動くが、案件では使わない

---

## 開発（親テーマを変更するとき）

> 通常の案件制作では親テーマを変更しない。この手順は親テーマ担当者向け。

```bash
npm install
npm run build     # assets/scss/ → assets/css/lish-foundation.css
npm run watch     # 開発中
```

**ビルド成果物（`assets/css/*.css`）はコミットする**。案件側で親テーマをビルドさせないため。

### 変更前に必ず読む

- `CLAUDE.md` — 親テーマ開発の絶対ルール（後方互換 / プレフィックス / SemVer 判断）
- `.claude/rules/absolute.md` — 同上（ファイル種別ルール）
- `docs/API.md` — 公開API（ここに載っているものは勝手に変えられない）

---

## リリース手順

1. `npm run build` で `assets/css/lish-foundation.css` を更新
2. `style.css` の `Version:` と `functions.php` の `LISH_VERSION` を**同時に**更新
3. `CHANGELOG.md` に記載（Added / Changed / Deprecated / Removed / Fixed / Security）
4. 破壊的変更があれば `docs/UPGRADING.md` に移行手順
5. 新しい公開関数・フックがあれば `docs/API.md` に追記
6. `assets/scss/_mixin.scss` を変更した場合は、子テーマ側の `assets/scss/common/_mixin.scss` を差し替える手順を `docs/UPGRADING.md` に明記（コピー配布方式のため自動では届かない）
7. コミット → タグ `v1.2.0`

### SemVer の判断

| 種別 | 内容 |
|---|---|
| **PATCH** | バグ修正 / セキュリティ / 内部改善。API・HTML構造・CSSクラス名は変えない |
| **MINOR** | 機能追加 / 新フック追加。既存の動作は変えない |
| **MAJOR** | 破壊的変更（deprecated削除 / HTML構造変更 / CSSクラス名変更 / 最低PHP・WP引き上げ） |

判断に迷うものは **MAJOR に倒す**。既存案件が壊れないことが最優先。

---

## 更新配信について

v1.0 時点では配信サーバを実装していない。`inc/updater/updater.php` にフック配線と
差し替えポイントだけがあり、既定では**何もしない**（`lish/update_source` が空）。

将来、配信サーバを用意したら次のように有効化する:

```php
add_filter('lish/update_source', function () {
    return 'https://updates.lishinc.com/lish/info.json';
});
```

`style.css` に `Update URI:` を入れてあるため、wordpress.org に同名テーマが現れても
誤って上書き更新されることはない（WP 6.1+）。

---

## ドキュメント

| ファイル | 内容 |
|---|---|
| `docs/API.md` | 公開API（関数 / フック / クラス契約）— **子テーマ側にも `docs/lish-api.md` として配布** |
| `docs/UPGRADING.md` | バージョン間の移行手順 |
| `docs/CHILD-THEME.md` | 子テーマの作り方・上書きの注意 |
| `CHANGELOG.md` | 変更履歴（SemVer） |
| `CLAUDE.md` | 親テーマ開発の絶対ルール |
