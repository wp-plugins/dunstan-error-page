=== Dunstan-style Error Page ===

Contributors: fergbrain
Donate link: http://www.andrewferguson.net/2007/03/08/general-note/
Tags: countdown, timer, count, date, event
Requires at least: 1.5
Tested up to: 2.1
Stable tag: 1.3.1

A fuller featured 404 error page modeled from http://1976design.com/blog/error/

== Description ==

See http://www.andrewferguson.net/wordpress-plugins/dunstan-style-error-page/ for the latest updates.

== Installation ==

1. Downloaded the latest version of the plugin (below)
1. Rename the downloaded file to `afdn_error_page.php`
1. Upload to your plugins directory (typically /wp-content/plugins/)
1. Activate the plugin
1. Configure the plugin under Options > Error Page in the administration panel
1. Find the 404.php file for the theme you are using, usually located at `/wp-content/themes/*YOUR_THEME_NAME*/404.php`
1. Delete everything in the 404.php file and replace it with:`<?php afdn_error_page(); ?>`
1. Enjoy!

== Frequently Asked Questions ==

= Where is my Askimet key? =

http://faq.wordpress.com/2005/10/19/api-key/

= I get a standard 'The page cannot be found' error when using Internet Explorer. Any suggestions? =

This is known problem and a fix will be rolled into the next release. In the meantime, read on:

Apache makes a note below the ErrorDocument documentation:

    Microsoft Internet Explorer (MSIE) will by default ignore server-generated error messages when they are "too small" and substitute its own "friendly" error messages. The size threshold varies depending on the type of error, but in general, if you make your error document greater than 512 bytes, then MSIE will show the server-generated error rather than masking it. More information is available in Microsoft Knowledge Base article Q294807.

My best recommendation is to inflate the size of the error document. But some garbage text between two comment tags and see what that does.

= Recently spammers have been using this plugin to send me gobs of "Quick Error Report" emails. Ideas to stop this? =

This is also a known problem and I'm working on a way to combat it. In the mean time, your best bet would be to disable the quick error report part. There are several ways you could do it, but the easiest and cleanest is to just remove lines 395 to 403:
`<h2>Quick error report</h2>`
`...`
`</form>`