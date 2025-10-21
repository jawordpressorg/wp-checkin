# wp-checkin

Tags: wordcamp
Contributors: Takahashi_Fumiki
Tested up to: 6.8  
Stable Tag: nightly  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

A check-in helper for WordCamp.

## Description

WordCampサイトには「通知」という機能があり、チケット購入者にメールを送信することができます。
このプラグインから提供される画像タグを通知の本文内に入れると、QRコードとして表示され、スタッフはその画像を読み取ることで、チケットの内容を表示できます。
[WordCamp Tokyo 2019](https://2019.tokyo.wordcamp.org) における受付フロー改善のための仕組みとして導入されました。

[![WP Checkin](https://img.youtube.com/vi/-R4gbTd8EFI/maxresdefault.jpg)](https://www.youtube.com/watch?v=-R4gbTd8EFI)

かつてはPHP+Slim Frameworkで動作していましたが、WordPressプラグインとして書き直しました。

## Installation

GitHubの[release]()にビルド済みのzipファイルがあります。このファイルをダウンロードし、プラグイン > 新規追加からアップロードしてください。

## Development

このリポジトリをクローンし、 `composer` および `npm` をインストールしてください。
必要なバージョンは `package.json` および `composer.json` に記載されています。

```
git clone git@github.com:wct2019/wp-checkin.git
cd wp-checkin
composer install
npm install
```

- `npm start` を実行するとローカルのWordPressが起動します。
- `npm run watch` で静的ファイルの監視がスタートします。
- `composer lint` でPHPの構文チェックを行います。

変更はGitHubリポジトリにプルリクエストとして送ってください。

## License

GPL 3.0 or later.
