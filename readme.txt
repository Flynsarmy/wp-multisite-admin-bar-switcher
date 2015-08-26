=== Multisite Admin Bar Switcher ===
Contributors: flynsarmy
Tags: multisite, toolbar, switcher, switch, network, admin, wpmu
Requires at least: 3.2.1
Tested up to: 4.3
Stable tag: 1.2.2

== Description ==

Require PHP 5.3

The Multisite Admin Bar Switcher is a plugin written for WordPress Multi-Site
that makes switching between sites easier with large numbers of sites.

This plugin replaces the built in 'sites' drop down with one which breaks the
sites up by letter.

== Installation ==

1. Upload `multisite-admin-bar-switcher` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Network/Plugins' menu in WordPress
The switcher will now appear on the Admin Menu

== Screenshots ==

1. Multisite Admin bar Switcher in action

== Frequently Asked Questions ==

Multisite Admin Bar Switcher supports filters to change the way the site lists look.

**mabs_blog_name** - used to customise the way the blog names look in the site list
`
/**
 * Sets the blog name to show in the sites drop down
 *
 * @param  string   $name    Blog name
 * @param  stdClass $blog    Blog details
 *
 * @return string            Blog name
 */
add_filter('mabs_blog_name', function($name, $blog) {
	return sprintf("(%s) %s", $blog->userblog_id, $name);
}, 10, 2);
`

**mabs_blog_pages** - used to add or remove subitems from blogs
`
/**
 * Sets the blog items to show under a site
 *
 * @param  [type]  $pages   List of blog subitems
 * @param  int     $site_id Blog ID
 * @param  WP_User $user    User we're showing the list to
 *
 * @return array            List of blog subitems
 */
add_filter('mabs_blog_pages', function($pages, $site_id, $user) {
	return array_merge($pages, array(
		'products' => array('title' => 'Products', 'url' => 'edit.php?post_type=product', 'permission' => 'edit_products'),
	));
}, 10, 3);
`

**mabs_cache_duration** - alter cache time (default 30 minutes)
`
/**
 * Various DB-intensive lookups are cached. Use this filter to alter the cache time.
 *
 * @param  int     $cachetime Cache Time (default 30 minutes)
 *
 * @return int                Cache time
 */
add_filter('mabs_cache_duration', function($cachetime) {
	return 60*60*30;
});
`

== Changelog ==

= 1.2 =
* Performance improvement: More admin bar methods cached. WP_Admin_Bar has been replaced with MABS_Admin_Bar

= 1.1.3 =
* Performance improvement: Admin bar menus that we're removing shouldn't ever be loaded in the first place

= 1.1.2 =
* Switch from wp_cache API to site_transient API so caching happens on both backend and frontend

= 1.1.1 =
* Cache mabs_get_blogs_of_network function

= 1.1 =
* Add cache support to speed up admin bar generation
* 'Network Settings' page for clearing cache

= 1.0.12 =

* 'My Sites' translation fix

= 1.0.11 =

* Capitalization fix

= 1.0.10 =

* Added mabs_blog_pages, mabs_blog_name filters - See documentation for usage instructions

= 1.0.9 =

* Disable autocomplete on filter field

= 1.0.8 =

* Show all blogs to super admins

= 1.0.7 =

* Minor fixes

= 1.0.6 =

* Minor fixes

= 1.0.5 =

September 25, 2014

* Added site filter if 10 or more blogs

= 1.0.4 =

April 30, 2014

* Confirmed compatibility with WP 3.9

= 1.0.3 =
* MABS now shows for all logged in users.
* Performance improvements
* Only applicable blogs show for each user

= 1.0.2 =
* 'My Sites' admin button now toggles between admin and frontend

= 1.0.1 =
* Minor readme updates

= 1.0 =

February 14, 2013

* First version released