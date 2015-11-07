# WPTagSanitizer

A WordPress plugin that sanitizes tags in posts.

If you add the following tags to your posts they will be transformed like this:

```
"joe" -> "Joe" (capitalized first letter)
"iMac" -> "iMac" (no change)
"googletranslate" -> "Google Translate" (Transformed tag)
"Wordpress" -> "WordPress" (small p changed to capital P)
```

## Installation

1. Download to your Wordpress plugin folder.
2. Activate plugin.
3. Whenever you add a tag to your post it will be sanitized so as to look prettier.

## Changelog

### 1.0.1
* Refactoring plugin for better performance.

### 1.0.0
* Uploaded plugin.