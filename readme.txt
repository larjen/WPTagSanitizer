=== WPTagSanitizer ===
Contributors: larjen
Donate link: http://exenova.dk/
Tags: Twitter
Requires at least: 4.3.1
Tested up to: 4.3.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin that sanitizes tags in posts.

== Description ==

A WordPress plugin that sanitizes tags in posts.

If you add the following tags to your posts they will be transformed like this:

    "joe" -> "Joe" (capitalized first letter)
    "iMac" -> "iMac" (no change)
    "googletranslate" -> "Google Translate" (Transformed tag)
    "Wordpress" -> "WordPress" (small p changed to capital P)

It is also possible to alter the tag mappings from within the plugin controlpanel.

== Installation ==

1. Download and unzip to your Wordpress plugin folder.
2. Activate plugin.
3. Whenever you add a tag to your post it will be sanitized so as to look prettier.

== Frequently Asked Questions ==

= Do I use this at my own risk? =

Yes.

== Screenshots ==

== Changelog ==

= 1.0.1 =
* Refactoring plugin for better performance.

= 1.0.0 =
* Uploaded plugin.

== Upgrade Notice ==
