# Lish 親テーマ 開発ルール

> **このリポジトリの位置づけ**: 全案件へ配布する自社共通の **WordPress 親テーマ**。
> **制作方法に依存しない、自社 WordPress サイト制作・保守の共通基盤**。
> 案件のデザインはここには入らない。デザインは子テーマ（`lish-child` のコピー）で実装する。
>
> 子テーマ側の制作方法は Figma Mode / AI Design Mode / Existing Design Mode の3つを想定するが、
> **親テーマはそのどれにも依存しない**。Figma / MCP 固有のコード・設定を親に持ち込まない。
>
> **最優先事項は「後方互換を壊さないこと」**。このテーマは既に稼働中の案件サイトが使っている。
> 機能が多いことより、**安定している / 壊れにくい / 子テーマへ影響を与えにくい / アップデートしやすい / APIが明確** であることを優先する。

---

## 🛑 絶対ルール（例外なし）

1. **案件固有のものを親に入れない**。特定案件のデザイン値・文言・Figma node 参照・ロゴ・カラーは絶対に入れない。
   **特定の制作方法（Figma/MCP 等）に依存するコード・設定も入れない**（親は制作方法に非依存）。「1案件で必要になった」は理由にならない。**2案件以上で必要になり、かつ内容を分離できる**ものだけが親の候補。
2. **公開関数・フック・CSSクラス名の削除／改名は MAJOR でのみ**。まず1メジャー分 deprecated を挟む（`inc/compat/deprecated.php`）。
3. **公開APIは `docs/API.md` に必ず記載する**。記載のないものは private 扱いで、予告なく変更してよい。
   - 純粋な内部ヘルパーは `_lish_` プレフィックスを付ける
   - **例外**: `add_action()` / `add_filter()` に**文字列で登録するコールバック関数**は `lish_` のままにする（子テーマが `remove_action()` で外せるようにするため）。ただしこれらは公開 API ではないので、**`docs/API.md` §10「内部関数（呼ばないこと）」に必ず列挙する**。名前は MAJOR で変わりうる。
4. **プレフィックスを守る**。関数 `lish_` / private 関数 `_lish_` / 定数 `LISH_` / フック `lish/` / オプション `lish_` / スクリプトハンドル `lish-` / CSSクラス `c-` `l-`。
   - **`lish_child_*` は子テーマ側の予約プレフィックス**。親テーマの関数名に使わない。
5. **子テーマから制御できるようにする**。新機能を足すときは必ずフィルタ／アクションの差し替えポイントをセットで用意する。「親を編集しないと変えられない」実装は作らない。
6. **`function_exists()` ガードを検討する**。子で上書きさせたい関数は pluggable にし、その旨を `docs/API.md` に明記する。
7. **変更は必ず `CHANGELOG.md` に SemVer で記録する**。
8. **`LISH_MIN_PHP` / `LISH_MIN_WP` を下回る API を使わない**。引き上げは MAJOR のみ。
9. **要件不足の環境で Fatal を起こさない**。管理画面通知＋機能縮退で動かす（`inc/bootstrap.php` / `inc/admin/notices.php`）。
10. **親が出す HTML のクラス名と階層は「契約」**。変更は MAJOR。変更時は `docs/UPGRADING.md` に旧→新の対応表を書く。子テーマの CSS セレクタが依存しているため。
11. **JS のクラス契約も同様に契約**（`js-hamburger` / `js-drawer` / `js-drawer-overlay` / `body.js-drawer-open` / `body.js-header-scrolled`）。
12. **`style.css` の `Version:` と `LISH_VERSION` 定数を必ず同期する**。
13. **迷ったら「新しいフックを足す」で解決する**。既存の挙動を変えない。既存フックの引数追加は**末尾に追加のみ**（順序を変えない）。
14. **CPT / タクソノミー / ACF フィールドを親に登録しない**。コンテンツの所有権は案件（子テーマ）側に置く。
15. **ユーザーの明示指示なしに `git commit` / `git push` / タグ付けをしない**。

---

## SemVer の判断基準

| 種別 | 内容 | 例 |
|---|---|---|
| **PATCH** (1.0.x) | バグ修正 / セキュリティ / 内部実装の改善。**API・HTML構造・CSSクラス名は変えない** | エスケープ漏れの修正、`filemtime` の Warning 対策 |
| **MINOR** (1.x.0) | 機能追加 / 新しいフック・関数の追加。**既存の動作は変えない** | `lish/ogp/locale` フィルタを追加、JSON-LD 基盤を追加 |
| **MAJOR** (x.0.0) | 破壊的変更 | deprecated の削除、HTML構造の変更、CSSクラス名の変更、`Requires PHP` の引き上げ |

**「既存案件が壊れないか」で判断する**。判断に迷うものは MAJOR に倒す。

---

## ディレクトリ構成

```
lish/
├── style.css                       # テーマヘッダーのみ（CSS は書かない）
├── index.php                       # WP必須のフォールバック
├── functions.php                   # 定数 + bootstrap 読み込みのみ（太らせない）
│
├── header.php                      # <head> + OGP + body開始 → get_template_part('inc/part-header')
├── footer.php                      # get_template_part('inc/part-footer') + wp_footer
├── page.php single.php archive.php search.php 404.php searchform.php
│
├── inc/
│   ├── bootstrap.php               # 要件チェック / モジュール読み込み
│   ├── setup.php                   # add_theme_support / register_nav_menus / image sizes / i18n
│   ├── assets.php                  # enqueue基盤 / defer / Webフォント / vendor register
│   ├── head.php                    # lish_head_meta()（OGP / favicon / Webフォント）
│   ├── content.php                 # wpautop制御 / ブロックエディタ制御
│   ├── security.php                # 情報露出の除去（最低限）
│   ├── template-tags.php           # lish_breadcrumb / lish_pagination / lish_excerpt / lish_post_thumbnail
│   ├── part-header.php             # ★ 既定ヘッダー（子が同名を持てば子が優先）
│   ├── part-footer.php             # ★ 既定フッター（同上）
│   ├── template-parts/             # ★ 親専用パーツ（子の inc/part-*.php と衝突させない）
│   ├── compat/{php,wp,deprecated}.php
│   ├── updater/updater.php         # 更新通知の受け口（v1.0 は no-op）
│   └── admin/notices.php
│
├── assets/
│   ├── css/lish-foundation.css     # ビルド成果物（コミットする）
│   ├── css/lish-entry-content.css
│   ├── css/vendor/splide-core.min.css
│   ├── js/lish-ui.js
│   ├── js/vendor/{splide.min.js,ajaxzip3.js}
│   └── scss/                       # 親CSSのソース（_mixin.scss は子へのコピー配布の正本）
│
├── languages/
├── docs/{API.md,UPGRADING.md,CHILD-THEME.md}
├── CHANGELOG.md
├── package.json                    # 親CSSのビルド
└── CLAUDE.md                       # 本ファイル
```

---

## 「親に入れる / 入れない」の判定

```
Q1. 特定案件のデザイン・文言・Figma参照が含まれるか？
   Yes → 親に入れない（子テーマへ）
   No  → Q2

Q2. 2案件以上で実際に必要になったか？（「使えそう」の予測は不可）
   No  → 親に入れない（実発生するまで待つ）
   Yes → Q3

Q3. 内容（文言 / 画像 / リンク）を子テーマ側から注入できるか？
   No  → 親に入れない（内容がハードコードされるものは共通化しない）
   Yes → Q4

Q4. 子テーマから差し替えられるフック／フィルタを用意できるか？
   No  → 設計を見直す（差し替え不能な実装は入れない）
   Yes → 親に入れてよい
```

---

## 実装ルール

### PHP

- 全ファイル冒頭に `if (!defined('ABSPATH')) { exit; }`
- 出力は必ずエスケープ（`esc_html` / `esc_attr` / `esc_url` / `wp_kses_post`）
- 翻訳可能文字列は `__()` / `esc_html__()` にテキストドメイン `'lish'` を付ける
- `filemtime()` の前に必ず `file_exists()`（Warning を出さない）
- 外部通信（更新チェック等）は**必ず失敗許容**にする。落ちてもサイトは動く
- `functions.php` に処理を書かない（`inc/` に分割する）

### CSS / SCSS

- 親の CSS は **全案件で常に同じであるべきもの**だけ（destyle / ルートフォント / 汎用リセット）
- 色・フォント・セクションレイアウトは入れない（子テーマの領分）
- **「子の実装を制約する CSS」を親に入れない**。特に `overflow` / `position` / `z-index` は、
  子孫要素の `position: sticky` やスタッキングコンテキストを壊すため、デザイン判断が入るものは子テーマへ。
  （例: `main { overflow: hidden }` は横スクロール防止という判断なので子テーマ側に置く）
- ビルドは `npm run build` → `assets/css/lish-foundation.css`。**成果物をコミットする**（案件側で親をビルドさせない）

### JavaScript

- バニラJSのみ。`var` 禁止、`console.log` 残存禁止
- クラス契約（`js-*` / `body.js-*`）は公開 API。変更は MAJOR
- 見た目に関わる CSS は書かない（クラスの付け外しだけ）

---

## リリース手順

1. `npm run build` で `assets/css/lish-foundation.css` を更新
2. `style.css` の `Version:` と `functions.php` の `LISH_VERSION` を**同時に**更新
3. `CHANGELOG.md` に Added / Changed / Deprecated / Removed / Fixed / Security で記載
4. 破壊的変更があれば `docs/UPGRADING.md` に移行手順を記載
5. 新しい公開関数・フックがあれば `docs/API.md` に追記
6. `_mixin.scss` を変更した場合は `docs/UPGRADING.md` に「子テーマの `common/_mixin.scss` を差し替える」と明記
7. ユーザーの指示のもとコミット → タグ `v1.2.0` を打つ

---

## 参考

- 公開API: `docs/API.md`
- 移行手順: `docs/UPGRADING.md`
- 子テーマの作り方: `docs/CHILD-THEME.md`
- 変更履歴: `CHANGELOG.md`
