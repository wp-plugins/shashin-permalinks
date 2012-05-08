=== Shashin permalinks ===
Contributors: SLiX
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JX2QCU67GZLD4
Tags: picasa, image, images, picture, pictures, photo, photos, permalinks, shashin
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: 1.21

This plugin adds permalinks support for Shashin 2 plugin galleries (default keywords: "album" and "page")

== Description ==

This plugin will replace all parameters for Shashin 2 plugin galleries by permalinks,

e.g.: /gallery/?shashin_album_key=1&page=2 will be replaced by /gallery/album/1/page/2/

== Installation ==

1. Upload the `shashin-permalinks/` folder to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Shashin permalinks admin page
1. Choose your keywords (or use the defaults) and enable Shashin permalinks
1. And voilà !

== Frequently Asked Questions ==

= How it works ? =

The plugin does 2 major things:

* It replaces "?shashin_album_key=x(&page=y)" links in content by "/album/x/(page/y/)" links
* it adds a rewrite rule to handle parameters for Shashin plugin

= What it is useful for ? =

This plugin can be used to:

* Hide Shashin plugin usage in URLs
* Shorten URLs (with proper configuration you can have "/a/3/2" for page 2 of album 3)
* Get better SEO (maybe ?!)

== Screenshots ==

1. Shashin permalinks admin page

== Upgrade Notice ==

Nothing, for the moment...

== Changelog ==

= 1.21 =
* Better handling of titles replacements

= 1.2 =
* Can fix Sociable plugin links to point them to current album
* Can add current album name to post/page title [Experimental]

= 1.11 =
* Little fix on matching pattern (remove "a" matching before "href")

= 1.1 =
* Only replace real links (a href=...)

= 1.0 =
* Initial Release 
