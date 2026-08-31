=== alt自動挿入 ===
Contributors: taniyanr
Tags: alt, image, accessibility, seo, media
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPressの記事に挿入した画像の空のalt属性へ、記事タイトルを自動で設定する軽量プラグインです。

== Description ==

「alt自動挿入」は、投稿や固定ページに含まれる画像の alt 属性が未設定または空の場合だけ、記事タイトルを自動設定します。

主な特徴:

* 既存の空でない alt は上書きしません。
* 投稿保存時に本文画像へ記事タイトルを保存します。
* アイキャッチ画像の空 alt を表示時に記事タイトルで補完できます。
* 同じ添付画像を複数記事で再利用しても、添付ファイル自体の alt メタデータは変更しません。
* 投稿・固定ページ・公開カスタム投稿タイプを対象に選択できます。
* Gutenberg とクラシックエディターの投稿本文に対応します。
* WordPress 標準の WP_HTML_Tag_Processor を使用します。
* 外部ライブラリ、JavaScript、CSS、独自DBテーブルは不要です。

== Installation ==

1. `alt-auto-insert` フォルダを `/wp-content/plugins/` にアップロードします。
2. WordPress管理画面の「プラグイン」から「alt自動挿入」を有効化します。
3. 「設定」→「alt自動挿入」で対象投稿タイプと動作を設定します。

初期設定では「投稿」「固定ページ」の本文画像とアイキャッチ画像が対象です。

== Frequently Asked Questions ==

= すでにaltが設定されている画像は上書きされますか？ =

いいえ。空でないalt属性は一切上書きしません。

= 同じ画像を複数の記事で使った場合はどうなりますか？ =

本文画像は各記事の本文HTMLにaltを保存します。アイキャッチ画像は表示時に補完し、添付ファイル自体のaltメタデータは書き換えません。そのため別の記事へ影響しません。

= 過去記事にも自動で反映されますか？ =

本文画像は、その記事を次回保存・更新したときに反映されます。アイキャッチ画像は有効化後すぐに表示時補完の対象になります。

= 装飾画像にも記事タイトルが入りますか？ =

対象記事の本文内でaltが空の画像には記事タイトルが設定されます。アクセシビリティ上、意図的に `alt=""` としたい純粋な装飾画像がある場合は、このプラグインの対象外にする運用が必要です。

== Changelog ==

= 1.0.0 =
* Initial release.
* Empty/missing alt attributes in post content are filled with the post title on save.
* Featured-image alt fallback added without modifying attachment metadata.
* Settings screen for post types, content images and featured images added.
