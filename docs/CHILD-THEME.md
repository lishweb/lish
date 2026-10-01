# 子テーマの作り方

案件用の子テーマを Lish の上に作る手順と、守るべき設計上のルール。

---

## 1. スターターをコピーする

```
wp-content/themes/
├── lish/          ← 親テーマ（配置済み・触らない）
└── abc-corp/      ← lish-child をコピーして案件名にリネーム
```

`style.css` を編集する:

```css
/*
Theme Name: ABC Corp          ← 案件名に変更
Template: lish                ← ★変更しない
Version: 1.0.0
Requires PHP: 8.0
Text Domain: abc-corp         ← テーマスラッグに変更
Lish Requires: 1.0.0          ← 必要な親テーマの最低バージョン
*/
```

**変更してよいのは `Theme Name` / `Text Domain` / `Description` / `Lish Requires` のみ。**
`Template: lish` は親テーマのフォルダ名を指すので変えない（本番 Linux は大文字小文字を区別する）。

そのあと `/project-init` を実行する。最初に**制作モード**（Figma Mode / AI Design Mode /
Existing Design Mode）を選び、モードに応じてトークン・ページ構成・共通パーツを流し込む。

---

## 2. 最小構成

子テーマとして成立する最小セットは次のとおり。

```
abc-corp/
├── style.css        # 必須（Template: lish を含むヘッダー）
├── functions.php    # 定数 + inc/ の読み込み
├── inc/
│   ├── enqueue.php      # app.css / app.js の読み込み
│   ├── part-header.php  # ヘッダーの見た目
│   └── part-footer.php  # フッターの見た目
└── assets/
    ├── scss/style.scss
    └── css/app.css      # ビルド出力
```

これだけで、親テーマの `<head>` / OGP / パンくず / セキュリティ / 共通JS を受け取りつつ、
デザインは自由に作れる。

---

## 3. 「上書きしてよいもの / いけないもの」

WordPress は、子テーマに同名ファイルがあれば**そちらを優先**する。
便利な仕組みだが、上書きした瞬間に**そのファイルへの親テーマの更新は永久に届かなくなる**。

### 上書きしてはいけない（親の更新を受け取り続けるべきもの）

```
header.php   ← <head> / OGP / wp_head() の改善が届かなくなる
footer.php   ← wp_footer() 周りの改善が届かなくなる
page.php / single.php / archive.php / search.php / 404.php
index.php / searchform.php
```

### 代わりに使う差し替え点

| やりたいこと | 置くファイル |
|---|---|
| ヘッダーの見た目 | `inc/part-header.php` |
| フッターの見た目 | `inc/part-footer.php` |
| 404 のデザイン | `inc/part-404.php` |
| TOPページ | `front-page.php`（親に無いので自由） |
| 固定ページのデザイン | `pages/page-{slug}.php`（`Template Name:` 付き） |
| CPT の一覧・詳細 | `single-{cpt}.php` / `archive-{cpt}.php`（親に無いので自由） |

### どうしても上書きが必要な場合

1. まず「親にフィルタを追加すれば解決しないか」を検討する（`docs/API.md` §12）
2. それでも必要なら、**上書きする理由を設計書に記録**する
3. 親テーマを更新するたびに、そのファイルの差分を手動で確認する（`docs/UPGRADING.md`）

---

## 4. PHP を書くときの注意

### 読み込み順

**子の `functions.php` は親より先に読み込まれる。**

```php
// ❌ Fatal: この時点で親はまだ読み込まれていない
lish_enqueue_splide();

// ✅ フック内で、存在確認してから
add_action('wp_enqueue_scripts', function () {
    if (function_exists('lish_enqueue_splide')) {
        lish_enqueue_splide();
    }
}, 20);
```

### 関数名

| プレフィックス | 所有者 |
|---|---|
| `lish_` `_lish_` `LISH_` `lish/` `lish-` | 親テーマ（**子で再定義しない**。Fatal になる） |
| `lish_child_` `LISH_CHILD_` | 子テーマ |

### パス関数

| 関数 | 指す先 |
|---|---|
| `get_stylesheet_directory_uri()` / `LISH_CHILD_URI` | **子テーマ（案件）— 原則こちら** |
| `get_template_directory_uri()` / `LISH_URI` | 親テーマ `lish` |

案件の画像・CSS・JS・フォントは全て前者。後者を使うと 404 になる。

---

## 5. CSS を書くときの注意

### 読み込み順

```
1. lish-foundation      (親)  destyle + ルートフォント + 汎用リセット
2. lish-entry-content   (親)  記事本文
3. lish-splide          (親・条件付き)
4. child-app            (子)  案件デザイン ← deps で順序を保証
```

子の enqueue で `deps` に `lish-foundation` を指定すること。

### 親のCSSを `!important` で打ち消さない

親のバグ修正・a11y 改善が効かなくなる。詳細度で解決する。

### 親が持っているものを子で書き直さない

- `html` の `font-size`（rem = 10px の基準）
- destyle のリセット
- `img { max-width: 100% }` などの汎用リセット

### ビルド

```
assets/scss/style.scss  →  assets/css/app.css
```

**`style.css` をビルド出力先にしない。** テーマヘッダーが消えてテーマが認識されなくなる。

---

## 6. JavaScript を書くときの注意

ドロワー開閉とヘッダーのスクロール状態は**親の `lish-ui.js` が担当**する。
子はマークアップを親のクラス契約に合わせ、**見た目だけ**を SCSS で作る。

| 役割 | クラス |
|---|---|
| ハンバーガー | `.js-hamburger` |
| ドロワー | `.js-drawer` |
| オーバーレイ | `.js-drawer-overlay` |
| 開いた状態 | `body.js-drawer-open` |
| スクロール後 | `body.js-header-scrolled` |

新しい JS を enqueue したら、`defer` を効かせるためフィルタにハンドルを追加する:

```php
add_filter('lish/defer_handles', function ($handles) {
    $handles[] = 'child-app';
    return $handles;
});
```

---

## 7. デプロイ

親テーマと子テーマの**両方**をアップロードする。

```
wp-content/themes/lish/       ← 初回 / 親テーマのバージョンアップ時
wp-content/themes/abc-corp/   ← 案件の更新のたび
```

転送不要（開発用）:

```
node_modules/  .tmp/  tools/  .claude/  .git/
package.json  package-lock.json  prepros.config  .mcp.json
CLAUDE.md  README.md  docs/
```

`assets/css/app.css` と `style.css` は**必ず転送する**。

---

## 8. 詰まったら

- 使えるフック・関数の一覧 → `docs/lish-api.md`（親の `docs/API.md` のコピー）
- 症状別のトラブルシューティング → 親テーマの `docs/UPGRADING.md`
- 親を変更したくなったら → **実装せず停止して相談する**
