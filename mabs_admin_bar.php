<?php

// Make sure admin bar class is loaded so we can extend it
require_once ABSPATH.WPINC.'/class-wp-admin-bar.php';


class MABS_Admin_Bar extends WP_Admin_Bar
{
    /**
     * This is the same as WP_Admin_Bar's initialize method but
     * $this->user->blogs serves our cached copy of user blogs for
     * better performance.
     */
    public function initialize()
    {
        $this->user = new stdClass;

        if ( is_user_logged_in() ) {
            /* Populate settings we need for the menu based on the current user. */
            $this->user->blogs = mabs_get_blogs_of_user( get_current_user_id() );
            if ( is_multisite() ) {
                $this->user->active_blog = $this->mabs_get_active_blog_for_user( get_current_user_id() );
                $this->user->domain = empty( $this->user->active_blog ) ? user_admin_url() : trailingslashit( get_home_url( $this->user->active_blog->blog_id ) );
                $this->user->account_domain = $this->user->domain;
            } else {
                $this->user->active_blog = $this->user->blogs[get_current_blog_id()];
                $this->user->domain = trailingslashit( home_url() );
                $this->user->account_domain = $this->user->domain;
            }
        }

        add_action( 'wp_head', 'wp_admin_bar_header' );

        add_action( 'admin_head', 'wp_admin_bar_header' );

        if ( current_theme_supports( 'admin-bar' ) ) {
            /**
             * To remove the default padding styles from WordPress for the Toolbar, use the following code:
             * add_theme_support( 'admin-bar', array( 'callback' => '__return_false' ) );
             */
            $admin_bar_args = get_theme_support( 'admin-bar' );
            $header_callback = $admin_bar_args[0]['callback'];
        }

        if ( empty($header_callback) )
            $header_callback = '_admin_bar_bump_cb';

        add_action('wp_head', $header_callback);

        wp_enqueue_script( 'admin-bar' );
        wp_enqueue_style( 'admin-bar' );

        /**
         * Fires after WP_Admin_Bar is initialized.
         *
         * @since 3.1.0
         */
        do_action( 'admin_bar_init' );
    }

    /**
     * This is the same as WordPress's get_active_blog_for_user() but with
     * cache support for increased performance.
     *
     * Get one of a user's active blogs
     *
     * Returns the user's primary blog, if they have one and
     * it is active. If it's inactive, function returns another
     * active blog of the user. If none are found, the user
     * is added as a Subscriber to the Dashboard Blog and that blog
     * is returned.
     *
     * @since MU 1.0
     *
     * @global wpdb $wpdb
     *
     * @param int $user_id The unique ID of the user
     * @return object|void The blog object
     */
    function mabs_get_active_blog_for_user( $user_id ) {
        $cache = get_site_transient('mabs_activeblog_'.$user_id);
        if ($cache)
            return $cache;

        global $wpdb;
        $blogs = mabs_get_blogs_of_user( $user_id );
        if ( empty( $blogs ) )
            return;

        if ( !is_multisite() )
        {
            $cache = $blogs[$wpdb->blogid];
            set_site_transient('mabs_activeblog_'.$user_id, $cache, apply_filters('mabs_cache_duration', 60*60*30));
            return $cache;
        }

        $primary_blog = get_user_meta( $user_id, 'primary_blog', true );
        $first_blog = current($blogs);
        if ( false !== $primary_blog ) {
            if ( ! isset( $blogs[ $primary_blog ] ) ) {
                update_user_meta( $user_id, 'primary_blog', $first_blog->userblog_id );
                $primary = get_blog_details( $first_blog->userblog_id );
            } else {
                $primary = get_blog_details( $primary_blog );
            }
        } else {
            //TODO Review this call to add_user_to_blog too - to get here the user must have a role on this blog?
            add_user_to_blog( $first_blog->userblog_id, $user_id, 'subscriber' );
            update_user_meta( $user_id, 'primary_blog', $first_blog->userblog_id );
            $primary = $first_blog;
        }

        if ( ( ! is_object( $primary ) ) || ( $primary->archived == 1 || $primary->spam == 1 || $primary->deleted == 1 ) ) {
            $blogs = mabs_get_blogs_of_user( $user_id ); // if a user's primary blog is shut down, check their other blogs.
            $ret = false;
            if ( is_array( $blogs ) && count( $blogs ) > 0 ) {
                foreach ( (array) $blogs as $blog_id => $blog ) {
                    if ( $blog->site_id != $wpdb->siteid )
                        continue;
                    $details = get_blog_details( $blog_id );
                    if ( is_object( $details ) && $details->archived == 0 && $details->spam == 0 && $details->deleted == 0 ) {
                        $ret = $blog;
                        if ( get_user_meta( $user_id , 'primary_blog', true ) != $blog_id )
                            update_user_meta( $user_id, 'primary_blog', $blog_id );
                        if ( !get_user_meta($user_id , 'source_domain', true) )
                            update_user_meta( $user_id, 'source_domain', $blog->domain );
                        break;
                    }
                }
            } else {
                return;
            }
            $cache = $ret;
            set_site_transient('mabs_activeblog_'.$user_id, $cache, apply_filters('mabs_cache_duration', 60*60*30));
            return $cache;
        } else {
            $cache = $primary;
            set_site_transient('mabs_activeblog_'.$user_id, $cache, apply_filters('mabs_cache_duration', 60*60*30));
            return $cache;
        }
    }
}