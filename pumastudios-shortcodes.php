<?php
/*
Plugin Name: Puma Studios Shortcodes
Plugin URI: http://pumastudios.com/software/
Description: My Shortcodes
Version: 0.2
Author: Kenneth J. Brucker
Author URI: http://pumastudios.com/
Text Domain: pumastudios-shortcodes

Copyright: 2016 Kenneth J. Brucker (email: ken@pumastudios.com)

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

global $pumastudios_shortcodes;

// ===============================
// = Define the shortcodes class =
// ===============================
if ( ! class_exists('pumastudios_shortcodes')) {
	class pumastudios_shortcodes {
		
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
			if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) {
				add_filter( 'admin_url', array($this, 'fix_admin_ajax_url' ), 10, 3 );
			}
		}
				
		/**
		 * sc_page_children
		 *
		 * Implements shortcode [page-children]
		 * 
		 * [page-children class=<class>]
		 *
		 * @return text HTML list containing entries for each child of the current page.
		 **/
		function sc_page_children($atts, $content = null) 
		{
			global $id;

			extract(shortcode_atts(array(
				"page_id" => $id,
				"class" => 'page-children'
			), $atts));

			$children_of_page = get_children(array("post_parent"=>$page_id, "post_type"=>"page", "orderby" => 'title', "order" => "ASC", "post_status" => "publish"));
			if (empty($children_of_page)) {
				return "";
			}

			$text = "<ul class=$class>";
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
		 * Send correct scheme (http/https) for admin-ajax.php
		 *
		 * If FORCE_SSL_ADMIN is set, admin_url() will return a URL with https scheme, even if
		 * the remainder of a front-end is using http.
		 *
		 * Cookies sent via https are secure and not available to http: content which can break some AJAX features.
		 *
		 * @param string   $url     The complete admin area URL including scheme and path.
		 * @param string   $path    Path relative to the admin area URL. Blank string if no path is specified.
		 * @param int|null $blog_id Site ID, or null for the current site.
		 * @return string  Repaired Admin URL
		 * @author Kenneth J. Brucker <ken.brucker@action-a-day.com>
		 */
		function fix_admin_ajax_url($url, $path, $blog_id)
		{
			/**
			 * Replace https with http if current request not using SSL
			 */
		    if ( $path == 'admin-ajax.php' && is_ssl() == FALSE ) {
		        return str_replace( 'https:', 'http:', $url );
		    }
		    return $url;
		}
	}
}

// =========================
// = Plugin initialization =
// =========================

$pumastudios_shortcodes = new pumastudios_shortcodes();

?>