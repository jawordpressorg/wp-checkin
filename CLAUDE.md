# wp-checkinの開発方針

README.mdに記載のとおり、これはWordPressプラグインです。WordCamp用のチェックインシステムで、QRコードを使った受付フローを提供します。

## プロジェクト構造

```
wp-checkin/
├── lib/                    # PHPクラスファイル (PSR-0)
│   └── WCTokyo/WpCheckin/ # 名前空間: WCTokyo\WpCheckin
├── src/                    # ソースファイル（ビルド前）
│   ├── js/                # JavaScript
│   ├── scss/              # Sass/SCSS
│   └── img/               # 画像（最適化前）
├── build/                  # ビルド成果物（Git管理対象）
│   ├── js/                # コンパイル済みJS
│   ├── css/               # コンパイル済みCSS
│   └── img/               # 最適化済み画像
├── template-parts/         # テンプレートファイル
├── wp-checkin.php         # プラグインエントリーポイント
└── wordpress/             # IDEのための参照用（Dcokerの中にあるファイルではない）
```

## 技術スタック

### PHP
- **最小バージョン**: composer.jsonを参照のこと
- **オートロード**: PSR-0（Composerで管理）
- **名前空間**: `WCTokyo\WpCheckin`
- **コーディング規約**: WordPress Coding Standards (WPCS)

### JavaScript/Node.js
- **Node.js**: package.jsonを参照（Voltaで管理）
- **ビルドツール**: grab-deps
- **コーディング規約**: @wordpress/eslint-plugin

### CSS
- **プリプロセッサ**: Sass (SCSS記法)
- **ポストプロセッサ**: PostCSS (Autoprefixer)
- **コーディング規約**: @wordpress/stylelint-config

### 開発環境
- **WordPress環境**: @wordpress/env (wp-env)
- **ローカルURL**: 起動後に表示される http://localhost:8888

## 開発フロー

### 初回セットアップ

```bash
composer install
npm install
npm start  # WordPress環境を起動
```

### 日常的な開発

```bash
# WordPress Docker環境の起動/停止
npm start           # 環境を起動
npm stop            # 環境を停止
npm run update      # 環境を更新して起動

# ファイル監視（開発中は常時実行推奨）
npm run watch       # src/配下を監視し、変更時に自動ビルド

# WP-CLIコマンドの実行
npm run cli -- [command]        # 開発環境
npm run cli:test -- [command]   # テスト環境
```

### ビルド

```bash
# 完全ビルド
npm run build       # JS + CSS + 画像最適化 + dump

# 個別ビルド
npm run build:js    # JavaScriptのみ
npm run build:css   # CSSのみ
npm run imagemin    # 画像最適化のみ
npm run dump        # 依存関係のダンプ
```

### リント/テスト

```bash
# JavaScript
npm run lint:js     # JSリント
npm run fix:js      # JS自動修正

# CSS
npm run lint:css    # CSSリント

# PHP
composer lint       # PHPリント
composer fix        # PHP自動修正

# テスト
npm test           # Docker環境におけるPHPUnitテスト（未実装）
```

## コーディング規約

### PHP
- WordPress Coding Standards（WPCS）に準拠
- `composer lint` でチェック可能
- `composer fix` で自動修正可能

### JavaScript
- @wordpress/eslint-plugin に準拠
- `npm run lint:js` でチェック可能
- `npm run fix:js` で自動修正可能

### CSS
- @wordpress/stylelint-config に準拠
- `npm run lint:css` でチェック可能

### ファイルサイズ
- 1ファイル500行を超える場合は分割を検討
- 「関心の分離」を意識したディレクトリ構成

## wp-env環境とデバッグ

### wp-env環境の構成

このプロジェクトは `.wp-env.json` でDocker環境を構成しています:

- **PHP**: 8.2
- **WordPress**: 最新版（自動ダウンロード）
- **テーマ**: Twenty Twenty Four
- **追加プラグイン**: Query Monitor（デバッグ用）
- **デバッグ設定**:
  - `WP_DEBUG`: true
  - `WP_DEBUG_LOG`: true（ログファイルに記録）
  - `WP_DEBUG_DISPLAY`: false（画面には表示しない）

### Docker環境へのアクセス

```bash
# WordPress環境URL
http://localhost:8888

# 管理画面
http://localhost:8888/wp-admin
# デフォルト認証情報: admin / password

# テスト環境（存在する場合）
http://localhost:8889
```

### チケット一覧ページのBasic認証

このプラグインは、チケット一覧ページに**Basic認証**をかけることができます（Setting.php:88-90）。

認証情報はWordPressのオプションとして保存されており、以下のコマンドで取得できます:

```bash
# Basic認証のユーザー名を取得
npm run cli -- option get wordcamp_auth_user

# Basic認証のパスワードを取得
npm run cli -- option get wordcamp_auth_pass

# 両方を一度に確認
npm run cli -- eval "echo 'User: ' . get_option('wordcamp_auth_user') . '\nPass: ' . get_option('wordcamp_auth_pass');"
```

**Chromeブラウザでテストする際の注意:**

チケット一覧ページにアクセスする前に、上記コマンドで認証情報を取得しておく必要があります。
Chromeブラウザでアクセスすると Basic認証ダイアログが表示されるため、取得した認証情報を入力してください。

```bash
# 1. 認証情報を取得
npm run cli -- eval "echo get_option('wordcamp_auth_user') . ':' . get_option('wordcamp_auth_pass');"

# 2. 出力例: staff:password123

# 3. Chromeブラウザでチケット一覧ページを開く際、
#    Basic認証ダイアログに上記のユーザー名とパスワードを入力
```

### デバッグログの確認

WordPressのデバッグログは `wp-content/debug.log` に記録されます。

```bash
# 最新30行を表示
npm run env run cli tail -n30 wp-content/debug.log

# リアルタイムで監視（tail -f）
npm run env run cli tail -f wp-content/debug.log

# ログファイル全体を表示
npm run env run cli cat wp-content/debug.log

# ログファイルをクリア
npm run cli -- eval "file_put_contents('wp-content/debug.log', '');"
```

### Query Monitorの活用

環境には[Query Monitor](https://wordpress.org/plugins/query-monitor/)がインストールされています。

**Query Monitorでできること:**
- データベースクエリの分析
- PHPエラーの表示
- フックとアクションの追跡
- HTTP APIリクエストの監視
- 実行時間とメモリ使用量の確認

管理画面にログインすると、画面上部のツールバーにQuery Monitorのメニューが表示されます。

### Chromeブラウザを使った高度なデバッグ

Claudeは Chrome DevTools MCP サーバーを使って、ブラウザを操作しながらデバッグできます。

**デバッグフロー例:**

1. **ブラウザでサイトを開く**
   ```
   ChromeのMCPサーバーでhttp://localhost:8888を開く
   ```

2. **操作を実行**
   ```
   ページ遷移、フォーム送信、ボタンクリックなど
   ```

3. **エラーログを確認**
   ```bash
   npm run env run cli tail -n30 wp-content/debug.log
   ```

4. **ブラウザのコンソールを確認**
   ```
   ChromeのMCPサーバーでコンソールログを取得
   ```

5. **修正とリロード**
   ```
   コードを修正 → ブラウザをリロード → 再テスト
   ```

**具体的な使用例:**

```bash
# 1. Claudeに依頼する例
「ブラウザでhttp://localhost:8888を開いて、
QRコードスキャンの動作をテストしてください。
エラーが出たらdebug.logも確認してください」

# 2. Claudeは以下を自動実行:
# - Chromeでサイトを開く
# - 操作を実行
# - エラーが発生したらdebug.logを確認
# - コンソールエラーも確認
# - 問題の原因を特定
```

### WP-CLIコマンド実用例

```bash
# プラグイン管理
npm run cli -- plugin list
npm run cli -- plugin activate wp-checkin
npm run cli -- plugin deactivate wp-checkin

# データベース操作
npm run cli -- db query "SELECT * FROM wp_posts LIMIT 5"
npm run cli -- db export
npm run cli -- db reset --yes

# ユーザー管理
npm run cli -- user list
npm run cli -- user create testuser test@example.com --role=administrator

# オプション確認
npm run cli -- option get siteurl
npm run cli -- option update blogdescription "WordCamp Checkin System"

# キャッシュクリア
npm run cli -- cache flush
npm run cli -- rewrite flush

# PHPコードの実行
npm run cli -- eval "var_dump(get_option('admin_email'));"
```

## Git運用

### コミット

**必須**: `git cc-commit` コマンドを使用してください。

```bash
# 正しい方法
git add .
git cc-commit "コミットメッセージ"

# 間違った方法（使用禁止）
git commit -m "メッセージ"
```

`git cc-commit` は自動的に `Co-authored-by: Claude` を追加します。

### プルリクエスト作成

`gh` コマンドが利用可能です。

```bash
gh pr create --title "タイトル" --body "説明"
```

### デプロイとリリース

このプロジェクトは **Release Drafter** を使った自動リリース管理を採用しています。

#### リリースフロー

1. **PRがmainにマージされる**
   - Release Drafterが自動的にリリースドラフトを作成/更新
   - PRのラベルに基づいてバージョン番号を自動計算（semver）
   - 変更内容がカテゴリ別に整理される

2. **リリースドラフトを確認・編集**
   - GitHubのReleasesページでドラフトを確認
   - 必要に応じて説明を追加・修正
   - バージョン番号を調整（major/minor/patchラベルで制御）

3. **リリースを公開**
   - ドラフトを「Publish release」で公開
   - 自動的にデプロイワークフローが起動
   - プラグインのビルドと本番環境へのデプロイが実行
   - zipファイルがリリースアセットとして追加

#### PRラベルとバージョニング

- `major`: メジャーバージョンアップ（例: 1.0.0 → 2.0.0）
- `minor`: マイナーバージョンアップ（例: 1.0.0 → 1.1.0）
- `patch`: パッチバージョンアップ（例: 1.0.0 → 1.0.1、デフォルト）

#### カテゴリラベル

- `feature`, `enhancement`: 🚀 Features
- `fix`, `bugfix`, `bug`: 🐛 Bug Fixes
- `chore`, `dependencies`: 🧰 Maintenance
- `documentation`, `docs`: 📝 Documentation

#### ワークフロー

**`.github/workflows/release-drafter.yml`**
- PRがmainにマージされるたびに実行
- リリースドラフトを自動作成/更新

**`.github/workflows/wordpress.yml`**
- リリースが公開されたときに実行
- プラグインのビルド
- 本番サーバーへのデプロイ（rsync）
- zipファイルの作成とリリースへの添付

**`.github/workflows/test.yml`**
- PRが作成されたときに実行
- PHPCSチェック
- npmビルドテスト

## 開発における注意点

### UI変更時のコミット

以下の変更は**許可なくコミット禁止**です:

- UIの変更（視覚的な確認が必要）
- ログインが必要な処理
- その他、コードだけで確認できない変更

**コミット可能なケース**:
- ユニットテストで確認できる変更
- リント/フォーマットの修正
- リファクタリング（動作が変わらない）

### 禁止事項

グローバルのCLAUDE.mdで指定されている禁止事項を遵守してください:

1. ワーキングディレクトリ外への副作用を持つコマンドの実行
2. グローバルへのパッケージインストール（`npm install -g`）
3. 他プロジェクトに影響を与える操作（`docker system prune -a` など）

### Volta環境について

- Node.jsはVoltaで管理されています
- package.jsonでNode 22.21.0にピン留め
- Claudeが実行する際、Voltaへのパスが通っていない場合があるので注意

## よくある操作

### プラグインの有効化

```bash
npm run cli -- plugin activate wp-checkin
```

### データベースのリセット

```bash
npm run cli -- db reset --yes
```

### WordPressの日本語化

環境は日本語（ja）でセットアップされています。

## トラブルシューティング

### ビルドが失敗する

```bash
# 依存関係の再インストール
rm -rf node_modules
npm install
```

### wp-envが起動しない

```bash
# 環境を完全に削除して再構築
npm run stop
npm run clean  # もしあれば
npm start
```

### PHPのオートロードが効かない

```bash
composer dump-autoload
```

