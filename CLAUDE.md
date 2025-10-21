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
└── wordpress/             # wp-envによるローカル環境（Git管理外）
```

## 技術スタック

### PHP
- **最小バージョン**: PHP 7.4以上
- **オートロード**: PSR-0（Composerで管理）
- **名前空間**: `WCTokyo\WpCheckin`
- **コーディング規約**: WordPress Coding Standards (WPCS)

### JavaScript/Node.js
- **Node.js**: 22.21.0以上（Voltaで管理）
- **ビルドツール**: grab-deps
- **コーディング規約**: @wordpress/eslint-plugin

### CSS
- **プリプロセッサ**: Sass (SCSS記法)
- **ポストプロセッサ**: PostCSS (Autoprefixer)
- **コーディング規約**: @wordpress/stylelint-config

### 開発環境
- **WordPress環境**: @wordpress/env (wp-env)
- **ローカルURL**: 起動後に表示される

## 開発フロー

### 初回セットアップ

```bash
composer install
npm install
npm start  # WordPress環境を起動
```

### 日常的な開発

```bash
# WordPress環境の起動/停止
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
npm test           # PHPUnitテスト
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

