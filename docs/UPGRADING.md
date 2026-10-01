# Lish アップグレードガイド

親テーマ Lish を新しいバージョンに上げるときの手順と注意点。

---

## 基本の手順

1. `CHANGELOG.md` で、現在のバージョン → 新バージョンの間の変更を確認する
2. **破壊的変更（MAJOR）が含まれるか**を確認する。含まれる場合は本書の該当セクションを読む
3. ステージング環境で `wp-content/themes/lish/` を差し替えて確認する
4. 本番へ反映する

> `wp-content/themes/lish/` を**フォルダごと差し替える**。子テーマ（案件フォルダ）は触らない。

---

## アップグレード前に必ず確認すること

### 1. 子テーマが親テンプレートを上書きしていないか

子テーマに次のファイルがあると、**そのファイルへの親の更新は届かない**。

```bash
# 案件フォルダで実行
ls header.php footer.php page.php single.php archive.php search.php 404.php index.php searchform.php 2>/dev/null
```

該当ファイルが出てきた場合:

- `CHANGELOG.md` にそのテンプレートへの変更が含まれていないか確認する
- 含まれていれば、**手動で差分を取り込む**か、`inc/part-*.php` 方式へ移行する

### 2. 子テーマが親の内部関数に依存していないか

```bash
# 案件フォルダで実行。docs/lish-api.md の §10 にある関数を呼んでいたら要注意
grep -rn "lish_setup\|lish_register_assets\|lish_render_\|lish_get_ogp_data\|lish_check_for_update" --include=*.php .
```

内部関数（フックコールバック）は公開 API ではないため、MAJOR で名前が変わりうる。

### 3. 子テーマが親のファイルを直接 require していないか

```bash
grep -rn "get_template_directory().*require\|require.*get_template_directory" --include=*.php .
```

親のファイル構成が変わると Fatal になる。フック経由に書き換える。

### 4. 子テーマが親の CSS を `!important` で打ち消していないか

```bash
grep -n "!important" assets/scss/**/*.scss
```

親の a11y 改善・バグ修正 CSS が効かなくなっている可能性がある。詳細度で解決するよう直す。

---

## `_mixin.scss` の同期（コピー配布方式）

子テーマの `assets/scss/common/_mixin.scss` は、親の `assets/scss/_mixin.scss` の**コピー**。
自動では更新されないため、`CHANGELOG.md` に mixin の変更が記載されているときだけ手動で差し替える。

```bash
cp ../lish/assets/scss/_mixin.scss assets/scss/common/_mixin.scss
npm run build
```

差し替えたら `assets/css/app.css` を再ビルドして、レイアウト崩れがないか確認する。

---

## `docs/lish-api.md` の同期

親を更新したら、子テーマ側の API 早見表も更新する（Claude Code が参照するため）。

```bash
cp ../lish/docs/API.md docs/lish-api.md
```

---

## バージョン別の移行手順

### → 1.0.0（base-theme からの移行）

`base-theme` から派生した既存案件は、**移行せずそのまま据え置く**のが方針
（`base-theme` 派生テーマはシングルテーマとして完結しており、親子化には全面的な作り替えが必要なため）。

新規案件から `lish` + 子テーマスターターを使う。

どうしても既存案件を移行する場合の作業:

1. `wp-content/themes/lish/` に親テーマを配置
2. 案件テーマの `style.css` をヘッダーのみに作り替え、`Template: lish` を追加
   - CSS 本体は `assets/css/app.css` へ移す
   - `package.json` / `prepros.config` のビルド先を `assets/css/app.css` に変更
3. `functions.php` から共通処理を削除し、案件固有処理だけを残す
   - enqueue は `inc/enqueue.php` に移し、`deps` を `lish-foundation` / `lish-ui` に
4. **`get_template_directory_uri()` を `get_stylesheet_directory_uri()` に全置換**
   ```bash
   grep -rn "get_template_directory_uri" --include=*.php .
   ```
   （親テーマ同梱ライブラリを指しているものだけ残す）
5. `header.php` の見た目部分を `inc/part-header.php` に切り出し、`header.php` を削除
6. `footer.php` も同様に `inc/part-footer.php` へ
7. `assets/scss/` から destyle / ルートフォント / 汎用リセットを削除（親が持つため）
8. `my_theme_breadcrumbs()` → `lish_breadcrumb()`、`get_the_custom_excerpt()` → `lish_excerpt()`
   （1.x の間は deprecated シムが動くので急がなくてよい）

---

## トラブルシューティング

| 症状 | 原因 | 対処 |
|---|---|---|
| 「このテーマは壊れています: 親テーマがありません」 | `wp-content/themes/lish/` が無い / フォルダ名が違う | フォルダ名を小文字 `lish` にする |
| 画像が 404 | `get_template_directory_uri()` を案件アセットに使っている | `get_stylesheet_directory_uri()` に直す |
| サイトが無スタイル | `assets/css/app.css` が無い / enqueue されていない | `npm run build` を実行、`inc/enqueue.php` を確認 |
| `Call to undefined function lish_...` | 子の `functions.php` トップレベルで親の関数を呼んでいる | フック内に移し `function_exists()` を付ける |
| `Cannot redeclare lish_...` | 子で親の関数を再定義している | 子の関数は `lish_child_*` に改名 |
| ヘッダーのデザインが反映されない | `inc/part-header.php` ではなく `header.php` を作っている | `inc/part-header.php` に移す |
| 親を更新したのに変わらない | 子が同名テンプレートで上書きしている | 上記「アップグレード前に必ず確認すること」を参照 |
| テーマヘッダーが消えた | `style.css` をビルド出力先にしている | 出力先を `assets/css/app.css` に直し、`style.css` を手書きで復元 |
