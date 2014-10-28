<?php
/*
Plugin Name: Puma Studios Shortcodes
Plugin URI: http://pumastudios.com/software/
Description: My Shortcodes
Version: 0.1
Author: Kenneth J. Brucker
Author URI: http://pumastudios.com/
Text Domain: pumastudios-shortcodes

Copyright: 2014 Kenneth J. Brucker (email: ken@pumastudios.com)

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
			add_action('init', array($this, 'wp_init'));
		}

		/**
		 * Run during WordPress wp_init
		 *
		 * @return void
		 */
		function wp_init() 
		{
			// Define Short Codes
			add_shortcode("page-children",array($this, "sc_page_children"));
			
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
	}
}

// =========================
// = Plugin initialization =
// =========================

$pumastudios_shortcodes = new pumastudios_shortcodes();

?>