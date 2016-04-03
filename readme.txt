=== Slash Edit ===
Contributors: ronalfy, bigwing
Tags: admin, edit
Requires at least: 3.9.1
Tested up to: 4.5
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Edit your posts or pages with a simple "/edit" at the end

== Description ==

Edit posts, pages, or custom post types by adding a "/edit" to the end of the URL.  If you are not logged in, you will be prompted to log in in order to edit the item.

The "/edit" functionality also works on author and taxonomy archives, and if you have a page assigned as your front page of your site.

This is useful if:

* You are not logged in, and want an easy shortcut to edit an item.
* You hate the admin bar and have disabled it, but still want an easy shortcut to edit an item.

Plugin banner and icon by <a href="https://www.flickr.com/photos/matthewgriff/4112899269/">matthewgriff</a>.

== Installation ==

1. Just unzip and upload the "slash-edit" folder to your '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= How do I use the plugin? =
Just browse to the post, page, or custom post type, and add "/edit" to the end (e.g., http://domain.com/posts/edit).

You'll need <a href="http://codex.wordpress.org/Using_Permalinks#mod_rewrite:_.22Pretty_Permalinks.22">pretty permalinks enabled</a>, which pretty much everyone already does.

= Will you allow quick editing of categories and other items later? =
It's already in there :D

= What about attachment pages? =
That one I couldn't figure out.  Patches welcome.

= Where are the options? =
No options :)

= English is not my first language.  Can I change the "/edit" into something else? =

Yep, just throw <a href="https://gist.github.com/ronalfy/cbbc1599bda2811c9a86">this code</a> in a <a href="http://www.wpbeginner.com/beginners-guide/what-why-and-how-tos-of-creating-a-site-specific-wordpress-plugin/">Site-specific plugin</a>.

Just keep in mind that whatever you choose to override with must be alphanumeric characters.  Something like edición will be parsed as edicion.

If you choose to use this filter, you'll need to <a href="http://codex.wordpress.org/Settings_Permalinks_Screen">update your permalinks</a> or deactivate and reactivate the Slash Edit plugin.

== Changelog ==

= 1.1.1 =
* Updated 2015-08-20 - Ensuring WordPress 4.3 compatibility
* Updated 2015-04-19 - Ensuring WordPress 4.2 compatibility
* Updated 2014-12-11 - Ensuring WordPress 4.1 compatibility 
* Released 2014-11-13
* Fixing endpoint when page is created with same slug as the endpoint

= 1.1.0 =
* Released 2014-11-13
* Added "/edit" to the front of the site (e.g., www.domain.com/edit) if you have a page set as your front page.
* Added a `slash_edit_url` filter to determine where to redirect a user when "/edit" is present (props <a href="https://profiles.wordpress.org/bjornjohansen/">Bjørn J.</a>)

= 1.0.0 =
* Released 2014-10-19
* Initial Release

== Upgrade Notice ==

= 1.1.1 =
Slash Edit now works on the home page when a page is set as your front page.  New filter: `slash_edit_url`.  Fixing endpoint when page is created with same slug as the endpoint