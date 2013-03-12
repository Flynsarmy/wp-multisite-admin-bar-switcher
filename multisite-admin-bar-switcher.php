<?php
/*
	Plugin Name: Multisite Admin bar Switcher
	Plugin URI: http://www.flynsarmy.com
	Description: Replaces the built in 'My Sites' drop down with a better layed out one
	Version: 1
	Author: Flyn San
	Author URI: http://www.flynsarmy.com/
*/
?><?php
/*
	Copyright 2013  Flyn San  (email : flynsarmy@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?><?php
add_action('admin_bar_menu', 'mabs', 40);

/**
 * Adds a blogs submenu items to the admin drop down menu.
 *
 * @param  string $blog_type 'site' or 'network'
 * @param  integer $id   site ID
 * @param  string $url  '<url>/wp-admin/'
 *
 * @return void
 */
function mabs_add_blog_pages( $blog_type, $id, $url )
{
	global $wp_admin_bar;
	if ( $blog_type == "site" )
		$pages = array(
			'dashboard' => 'index.php',
			'visit' => '',
			'posts' => 'edit.php',
			'media' => 'media.php',
			'links' => 'link-manager.php',
			'pages' => 'edit.php?post_type=page',
			'comments' => 'edit-comments.php',
			'appearance' => 'themes.php',
			'plugins' => 'plugins.php',
			'users' => 'users.php',
			'tools' => 'tools.php',
			'settings' => 'options-general.php'
		);
	elseif ( $blog_type == "network" )
		$pages = array(
			'dashboard' => 'index.php',
			'sites' => 'sites.php',
			'users' => 'users.php',
			'themes' => 'themes.php',
			'plugins' => 'plugins.php',
			'settings' => 'settings.php',
			'updates' => 'update-core.php'
		);
	else
		return false;

	foreach ( $pages as $key => $value )
	{
		if ( $key == "visit" )
			$wp_admin_bar->add_menu(array(
				'parent' => 'mabs_'.$id,
				'id' =>'mabs_'.$id.'_visit',
				'title'=>__('Visit Site'),
				'href'=>str_replace('wp-admin/','',$url)
			));
		else
			$wp_admin_bar->add_menu(array(
				'parent' => 'mabs_'.$id,
				'id' =>'mabs_'.$id.'_'.$key,
				'title'=>__(ucfirst($key)),
				'href' => $url.$value
			));
	}
}

/**
 * Adds the blog list under their respective letters
 *
 * @return void
 */
function mabs_add_blogs()
{
	global $wp_admin_bar,$wpdb;

	$blogs = mabs_get_blog_list();

	//Add letter submenus
	mabs_add_letters( $blogs );

	// add menu item for each blog
	$i = 1;
	foreach ( $blogs as $b )
	{
		$letter = substr($b['title'], 0, 1);
		$site_parent = "mabs_".$letter."_letter";
		$url = get_admin_url( $b['blog_id'] );

		//Add the site
		$wp_admin_bar->add_menu(array(
			'parent' => $site_parent,
			'id' => 'mabs_'.$letter.$i,
			//'title' => $b_title,
			'title' => $b['title'],
			'href' => $url
		));

		//Add site submenu options
		mabs_add_blog_pages('site', $letter.$i, $url);

		$i++;
	}
}

/**
 * Adds a letter submenu for each blog to sit in
 *
 * @param  array  $blogs List of blogs
 *
 * @return void
 */
function mabs_add_letters( array $blogs )
{
	global $wp_admin_bar;

	$letters = array();
	foreach ( $blogs as $blog )
		$letters[ strtoupper(substr($blog['title'], 0, 1)) ] = '';

	foreach ( array_keys($letters) as $letter )
		$wp_admin_bar->add_menu(array(
			'parent' => 'mabs',
			'id' => 'mabs_'.$letter.'_letter',
			'title'=>__($letter)
		));
}

/**
 * Returns an alphabetically sorted array of blogs
 *
 * @return [type]
 */
function mabs_get_blog_list()
{
	global $wpdb;

	//Get list of blogs
	$blogs_unsorted = $wpdb->get_results("
		SELECT blog_id, domain, path
		FROM $wpdb->blogs
	", ARRAY_A);

	//Get blog names and sort list
	$blogs = array();
	foreach ( $blogs_unsorted as $key=>$b )
	{
		$b['title'] = get_blog_option($b['blog_id'], "blogname");
		$blogs[ $b['title'].$key ] = $b;
	}
	//Sort alphabetically
	ksort($blogs);

	return $blogs;
}

function mabs() {
	if ( !is_multisite() || !is_super_admin() || !is_admin_bar_showing() )
		return;

	global $wp_admin_bar, $wpdb, $current_blog;

	$wp_admin_bar->remove_node('my-sites');
	$wp_admin_bar->remove_node('site-name');

	// current site path
	if ( is_network_admin() )
	{
		$blogname = __('Network');
		$url = network_admin_url();
	}
	else
	{
		$blogname = get_blog_option($current_blog->blog_id, "blogname");
		$url = get_admin_url( $current_blog->blog_id );
	}


	// add top menu
	$wp_admin_bar->add_menu(array(
		'parent' => false,
		'id' => 'mabs',
		'title' => __('My Sites') . ': ' . $blogname,
		'href' => $url,
	));

	$url = get_admin_url( $current_blog->blog_id );
	$wp_admin_bar->add_menu(array(
		'parent' => 'mabs',
		'id' => 'mabs_yoursite',
		'title' =>__('Your Site'),
		'href' => str_replace('/wp-admin/', '', $url)
	));
	mabs_add_blog_pages('site', 'yoursite', $url);

	// add network menu
	$url = network_admin_url();
	$wp_admin_bar->add_menu(array(
		'parent' => 'mabs',
		'id' => 'mabs_network',
		'title' =>__('Network'),
		'href' => $url,
	));
	mabs_add_blog_pages('network', 'network', $url);

	mabs_add_blogs();
}

?>