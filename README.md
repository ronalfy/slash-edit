Slash Edit for WordPress
======================

Slash Edit for WordPress 3.9.1+

## Description

Edit posts, pages, or custom post types by adding a "/edit" to the end of the URL.  If you are not logged in, you will be prompted to log in in order to edit the item.

The "/edit" functionality also works on author and taxonomy archives.

This is the awesome place where the developmental (growing?) version of the plugin lives.  For more stable releases, check out: https://wordpress.org/plugins/slash-edit/

This is useful if:

<ul>
<li>You are not logged in, and want an easy shortcut to edit an item.</li>
<li>You hate the admin bar and have disabled it, but still want an easy shortcut to edit an item.</li>
</ul>

A filter is available if you want to change "/edit" to "/anything".

## Installation

1. Just unzip and upload the "slash-edit" folder to your '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions
###How do I use the plugin?
Just browse to the post, page, or custom post type, and add "/edit" to the end (e.g., http://domain.com/posts/edit).

You'll need <a href="http://codex.wordpress.org/Using_Permalinks#mod_rewrite:_.22Pretty_Permalinks.22">pretty permalinks enabled</a>, which pretty much everyone already does.

###How do I change the "/edit" to something else?
Just throw <a href="https://gist.github.com/ronalfy/cbbc1599bda2811c9a86">this code</a> in a <a href="http://www.wpbeginner.com/beginners-guide/what-why-and-how-tos-of-creating-a-site-specific-wordpress-plugin/">Site-specific plugin</a>.

Just keep in mind that whatever you choose to override with must be alphanumeric characters.  Something like edición will be parsed as edicion.

If you choose to use this filter, you'll need to <a href="http://codex.wordpress.org/Settings_Permalinks_Screen">update your permalinks</a> or deactivate and reactivate the Slash Edit plugin.

### Where are the options?
No options :) 