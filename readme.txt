=== alt自動挿入 ===
Contributors: taniyanr
Tags: alt, image, accessibility, seo, media
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPressの記事に画像を挿入した時、空のalt属性へ記事タイトルを自動で設定する軽量プラグインです。

== Description ==

「alt自動挿入」は、投稿や固定ページへ画像を挿入した時、alt属性が未設定または空の場合だけ記事タイトルを自動設定します。

主な特徴:

* Gutenbergでは画像ブロックへ画像を挿入した時点でaltを設定します。
* クラシックエディターでも画像を本文へ挿入する直前にaltを設定します。
* 既存の空でないaltは上書きしません。
* 保存時にも本文画像の空altを補完するため、取りこぼしを防ぎます。
* アイキャッチ画像の空altを表示時に記事タイトルで補完できます。
* 同じ添付画像を複数記事で再利用しても、添付ファイル自体のaltメタデータは変更しません。
* 投稿・固定ページ・公開カスタム投稿タイプを対象に選択できます。
* WordPress標準APIを利用し、独自DBテーブルや外部ライブラリは不要です。

== Installation ==

1. `alt-auto-insert` フォルダを `/wp-content/plugins/` にアップロードします。
2. WordPress管理画面の「プラグイン」から「alt自動挿入」を有効化します。
3. 「設定」→「alt自動挿入」で対象投稿タイプと動作を設定します。

初期設定では「投稿」「固定ページ」の本文画像とアイキャッチ画像が対象です。

== Frequently Asked Questions ==

= 画像を挿入した瞬間にaltが入りますか？ =

はい。Gutenbergでは画像ブロックに画像が設定された時点、クラシックエディターでは画像HTMLを本文へ挿入する直前に、記事タイトルをaltへ設定します。記事タイトルが空の場合は何もしません。

= すでにaltが設定されている画像は上書きされますか？ =

いいえ。空でないalt属性は一切上書きしません。

= 同じ画像を複数の記事で使った場合はどうなりますか？ =

本文画像は各記事の本文HTMLにaltを保存します。アイキャッチ画像は表示時に補完し、添付ファイル自体のaltメタデータは書き換えません。そのため別の記事へ影響しません。

= 過去記事にも自動で反映されますか？ =

本文画像は、その記事を次回保存・更新したときに反映されます。アイキャッチ画像は有効化後すぐに表示時補完の対象になります。

= 装飾画像にも記事タイトルが入りますか？ =

対象記事の本文内でaltが空の画像には記事タイトルが設定されます。意図的に `alt=""` としたい純粋な装飾画像については、自動補完の対象になる点に注意してください。

== Changelog ==

= 1.1.0 =
* Gutenbergで画像挿入時に空altへ記事タイトルを即時設定。
* クラシックエディターで画像挿入時に空altへ記事タイトルを即時設定。
* 保存時補完をフォールバックとして継続。

= 1.0.0 =
* Initial release.
* Empty/missing alt attributes in post content are filled with the post title on save.
* Featured-image alt fallback added without modifying attachment metadata.
* Settings screen for post types, content images and featured images added.
