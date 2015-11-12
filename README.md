# WPTagSanitizer

A WordPress plugin that sanitizes tags in posts.

If you add the following tags to your posts they will be transformed like this:

    "joe" -> "Joe" (capitalized first letter)
    "iMac" -> "iMac" (no change)
    "googletranslate" -> "Google Translate" (Transformed tag)
    "Wordpress" -> "WordPress" (small p changed to capital P)

It is also possible to alter the tag mappings from within the plugin controlpanel.

## Installation

1. Download and unzip to your Wordpress plugin folder.
2. Activate plugin.
3. Whenever you add a tag to your post it will be sanitized so as to look prettier.

## Changelog

### 1.0.1
* Refactoring plugin for better performance.

### 1.0.0
* Uploaded plugin.

[//]: title (WPTagSanitizer)
[//]: category (work)
[//]: start_date (20151024)
[//]: end_date (#)
[//]: excerpt (A WordPress plugin that sanitizes tags in posts.)
[//]: tag (GitHub)
[//]: tag (WordPress)
[//]: tag (PHP)
[//]: url_github (https://github.com/larjen/WPTagSanitizer)
[//]: url_demo (#) 
[//]: url_wordpress (https://wordpress.org/plugins/wptagsanitizer/)
[//]: url_download (https://github.com/larjen/WPTagSanitizer/archive/master.zip)
