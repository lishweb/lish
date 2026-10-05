# Lish リリース手順

親テーマ Lish の新しい版を各サイトへ配る手順。
GitHub（https://github.com/lishweb/lish）に**リリースを作って zip を添付すると、各サイトが12時間以内に自動で更新する**。

> 仕組みは `inc/updater/updater.php`、子テーマ向けの説明は `docs/API.md` §4「更新配信」。

---

## 0. 配る前に

- 版の上げ方は `CHANGELOG.md` 冒頭のルール（PATCH / MINOR / MAJOR）に従う。
- **MAJOR（2.0.0 等）は自動更新されない**。各サイトで管理画面から手動で更新する（`docs/UPGRADING.md`）。
- 公開したリリースは**すぐ全サイトに配られる**。試すときは §4 の「Pre-release」を使う。

## 1. 版を上げてコミットする

次の 3 箇所を同じ版にそろえる。

| ファイル | 箇所 |
|---|---|
| `style.css` | `Version: 1.2.1` |
| `functions.php` | `define('LISH_VERSION', '1.2.1');` |
| `CHANGELOG.md` | `## [1.2.1] - YYYY-MM-DD` の項目と、末尾のリンク `[1.2.1]: https://github.com/lishweb/lish/releases/tag/v1.2.1` |

CSS を変えた場合は `npm run build` で `assets/css/` を作り直してからコミットする（配布物はビルド済みの CSS を含める）。

```bash
git add -A
git commit -m "Lish 1.2.1: 〇〇を修正"
```

## 2. タグを付けて push する

```bash
git tag v1.2.1
git push origin main --tags
```

タグ名は必ず `v` + 版（`v1.2.1`）。サイト側はタグ名から版を読む。

## 3. 配布用 zip を作る

```bash
git archive --prefix=lish/ -o lish-1.2.1.zip v1.2.1
```

- 中身が **`lish/` フォルダ1つ**になっていること（違う名前だと WP が別テーマとして入れてしまう）。
- 開発用ファイル（`.claude/` `CLAUDE.md` `package*.json` 等）は `.gitattributes` の `export-ignore` で自動的に外れる。
- 中身の確認: `git archive --prefix=lish/ v1.2.1 | tar -t | head`
- zip はリポジトリにコミットしない（作業後に削除してよい）。

## 4. GitHub でリリースを作る

1. https://github.com/lishweb/lish/releases/new を開く
2. **Choose a tag** で `v1.2.1` を選ぶ
3. **Release title**: `Lish 1.2.1`
4. 説明欄（Describe this release）に `CHANGELOG.md` の該当項目を貼る（空でも配布には影響しない）
5. **Attach binaries** に `lish-1.2.1.zip` をドラッグ（ファイル名は `lish-` で始まり `.zip` で終わること）
6. **Release label** を選ぶ
   - 本番配布: **Latest**（最新版として各サイトに配られる）
   - 試験配布: **Pre-release**（各サイトには配られない）
   - **None は選ばない**。None だと「最新版」にならず、各サイトに配られない（1.2.1・1.3.0 で発生）
7. **Publish release**
8. 公開後、https://github.com/lishweb/lish/releases で今回の版に **Latest** の印が付いていることを確認する。
   付いていなければ、そのリリースの編集（鉛筆アイコン）→ Release label を **Latest** → **Update release**

> zip を添付し忘れると、サイト側は「更新なし」と判断する（壊れはしない）。後から添付すれば次回のチェックで配られる。

## 5. 配られたか確かめる

- 任意のサイトの管理画面「ダッシュボード → 更新」で **再確認** を押すと、すぐに GitHub を見に行く。
- 自動更新は WP の定期処理（1日2回）で実行される。急ぐときはその画面から手動で更新してよい。
- 失敗したサイトがあると `web@lishinc.com` にメールが届く（成功時は届かない）。

## 6. 取り消したいとき

- 配った版に問題があったら、**直した版（例 1.2.2）を新しく出す**のが基本。
- 版を下げる配布はできない（サイト側は新しい版しか入れない）。問題の版のリリースを削除しても、更新済みのサイトは戻らない。

---

## 新しいサイトに Lish を入れるとき

- GitHub の最新リリースから `lish-x.y.z.zip` をダウンロードし、管理画面の「外観 → テーマ → 新規追加 → テーマのアップロード」で入れる。
- 以降は自動で更新される。設定は不要。
- 同じ名前のテーマが既にある場合は「アップロードしたもので現在のものを置き換える」を押す。
- **手元の `lish/` フォルダをそのまま上げない**（FTP / All-in-One WP Migration 等）。`.git` が入ると開発環境と判断されて自動更新が止まり、`.claude` や `node_modules` も公開されてしまう。必ずリリースの zip から入れる。
- 子テーマも、サーバに置くのは動作に要るファイルだけ（`.git` `.claude` `.tmp` `node_modules` `tools` `CLAUDE.md` `package*.json` 等は上げない）。

## 自動更新されない環境

| 環境 | 理由 |
|---|---|
| `lish/` の中に `.git` がある（開発環境） | 更新で `.git` が消えないよう、Lish が更新を出さない |
| `add_filter('lish/auto_update', '__return_false')` を書いたサイト | そのサイトだけ自動更新を止めている（手動更新は可能） |
| `wp-config.php` で `AUTOMATIC_UPDATER_DISABLED` が true | WP の自動更新そのものが止まっている |
| WP がファイルを直接書き込めないサーバ（FTP 方式） | WP の自動更新が動かない。サーバ設定を確認する |
| メジャー版の更新 | 互換を壊す変更のため自動にしない |
