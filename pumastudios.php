<?php
/*
Plugin Name: Puma Studios
Plugin URI: https://github.com/KnBrckr/pumastudios
Description: Site Specific Tweaks and Shortcodes
Version: 0.2.3
Author: Kenneth J. Brucker
Author URI: http://action-a-day.com
Text Domain: pumastudios

Copyright: 2016 Kenneth J. Brucker (email: ken.brucker@action-a-day.com)

This file is part of shortcodes, a plugin for Wordpress.

Shortcodes is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Shortcodes is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Shortcodes.  If not, see <http://www.gnu.org/licenses/>.
*/

// Protect from direct execution
if (!defined('WP_PLUGIN_DIR')) {
	header('Status: 403 Forbidden');
  header('HTTP/1.1 403 Forbidden');
  exit();
}

global $pumastudios;

// ===============================
// = Define the shortcodes class =
// ===============================
if ( ! class_exists('pumastudios')) {
	class pumastudios {
		
		/**
		 * Constructor function
		 *
		 * @return void
		 */
		function __construct()
		{
			// Run the init during WP init processing
			// FIXME Don't do init in __construct, makes it difficult to unit test outside WP
			add_action('init', array($this, 'wp_init'));
		}

		/**
		 * Run during WordPress wp_init
		 *
		 * @return void
		 */
		function wp_init() 
		{
			/**
			 * Define Short Codes
			 */
			add_shortcode("page-children",array($this, "sc_page_children"));
						
			/**
			 * Take care of woocommerce customizations
			 */
			add_action('wp_loaded', array($this, 'woocommerce_customize'));
			
			/**
			 * Filter admin_url scheme when SSL is not being used.
			 *
			 * Only required if FORCE_SSL_ADMIN is enabled
			 */
			if ( force_ssl_admin() ) {
				add_filter( 'admin_url', array( $this, 'fix_admin_ajax_url' ), 10, 3 );
			}
			
			/**
			 * Adjust slug for uploaded files to include mime type
			 */
			add_filter( 'wp_insert_attachment_data', array( $this, 'filter_attachment_slug' ), 10, 2 );
		}
				
		/**
		 * sc_page_children
		 *
		 * Implements shortcode [page-children]
		 * 
		 * [page-children class=<class> page_id=<id> order_by=<order>]
		 *
		 * @return text HTML list containing entries for each child of the current page.
		 **/
		function sc_page_children($atts, $content = null) 
		{
			global $id;

			/**
			 * Retrieve shortcode attributes
			 */
			extract(shortcode_atts(array(
				"page_id" => $id,
				"class" => 'page-children',
				"order_by" => 'title'
			), $atts));
			
			/**
			 * Sanitize fields
			 */
			$page_id = ( int ) $page_id;
			$order_by = in_array( $order_by, array( 'title', 'order', 'date' ) ) ? $order_by : 'title';
			if ( 'order' == $order_by ) $order_by = 'menu_order';

			/**
			 * Collect children of target page
			 */
			$children_of_page = get_children(array("post_parent"=>$page_id, "post_type"=>"page", "orderby" => $order_by, "order" => "ASC", "post_status" => "publish"));
			if (empty($children_of_page)) {
				return "";
			}

			$text = "<ul class=" . esc_attr( $class ) . ">";
			foreach ($children_of_page as $child_post) {
				$text .= "<li><a href='".get_bloginfo('wpurl')."/".get_page_uri($child_post->ID)."'> $child_post->post_title </a></li>";
			}
			$text .= "</ul>";
			return $text;
		}
		
		/**
		 * Take care of some WooCommerce customizations
		 *
		 * @return void
		 */
		function woocommerce_customize()
		{
			/**
			 * Remove annoy message to install wootheme updater
			 */
			remove_action( 'admin_notices', 'woothemes_updater_notice' );
		}
		
		/**
		 * Fix scheme (http/https) used for admin-ajax.php
		 *
		 * If FORCE_SSL_ADMIN is set, admin_url() will return a URL with https scheme, even if
		 * the front-end is using http.
		 *
		 * Cookies sent via https are secure by default and not available to http: content.
		 * This can break some site features served by AJAX.
		 *
		 * @param string   $url     The complete admin area URL including scheme and path.
		 * @param string   $path    Path relative to the admin area URL. Blank string if no path is specified.
		 * @param int|null $blog_id Site ID, or null for the current site.
		 * @return string  Repaired Admin URL
		 */
		function fix_admin_ajax_url($url, $path, $blog_id)
		{
			/**
			 * Scheme for admin-ajax.php should match scheme for current page
			 */
		    if ( $path == 'admin-ajax.php' ) {
				return set_url_scheme( $url, is_ssl() ? 'https' : 'http' );
		    }
			
		    return $url;
		}
		
		/**
		 * Filter attachment post data before it is added to the database
		 *  - Add mime type to post_name to reduce slug collisions
		 *
		 * @param array $data    Array of santized attachment post data
		 * @param array $postarr Array of unsanitized attachment post data
 		 * @return $data, array of post data
		 */
		function filter_attachment_slug($data, $postarr)
		{
			/**
			 * Only work on attachment types
			 */
			if ( ! array_key_exists( 'post_type', $data ) || 'attachment' != $data['post_type'] )
				return $data;
			
			/**
			 * Add mime type to the post title to build post-name
			 */
			$post_title = array_key_exists( 'post_title', $data ) ? $data['post_title'] : $postarr['post_title'];
			$post_mime_type = array_key_exists( 'post_mime_type', $data ) ? $data['post_mime_type'] : $postarr['post_mime_type'];
			$post_mime_type = str_replace( '/', '-', $post_mime_type );
			$post_name = sanitize_title( $post_title . '-' . $post_mime_type );
			
			/**
			 * Generate unique slug for post name
			 */
			$post_ID = array_key_exists( 'ID', $data ) ? $data['ID'] : $postarr['ID'];
			$post_status = array_key_exists( 'post_status', $data ) ? $data['post_status'] : $postarr['post_status'];
			$post_type = array_key_exists( 'post_type', $data ) ? $data['post_type'] : $postarr['post_type'];
			$post_parent = array_key_exists( 'post_parent', $data ) ? $data['post_parent'] : $postarr['post_parent'];
			
			$post_name = wp_unique_post_slug( $post_name, $post_ID, $post_status, $post_type, $post_parent );
			$data['post_name'] = $post_name;
			
			return $data;
		}
	}
}

// =========================
// = Plugin initialization =
// =========================

$pumastudios = new pumastudios();

?>