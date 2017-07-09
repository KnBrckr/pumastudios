<?php

/*
 * Copyright (C) 2017 Kenneth J. Brucker <ken.brucker@action-a-day.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace AAD\SiteTweaks;

/**
 * Description of pageChildren
 * 
 * @package AAD\SiteTweaks
 * @author Kenneth J. Brucker <ken.brucker@action-a-day.com>
 */
/*
 *  Protect from direct execution
 */
if ( !defined( 'WP_PLUGIN_DIR' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die( 'I don\'t think you should be here.' );
}

class pageChildren {

	/**
	 * Instantiate
	 * 
	 * @return void
	 */
	public function __construct( ) {
	}

	/**
	 * Plug into WP
	 * 
	 * @return void
	 */
	public function run() {
		/**
		 * Define Short Codes
		 */
		add_shortcode( "page-children", array( $this, "sc_page_children" ) );
	}

	/**
	 * sc_page_children
	 *
	 * Implements shortcode [page-children]
	 *
	 * [page-children class=<class> page_id=<id> order_by=<order>]
	 *
	 * @return text HTML list containing entries for each child of the current page.
	 * */
	function sc_page_children( $atts, $content = null ) {
		global $id;

		/**
		 * Retrieve shortcode attributes
		 */
		extract( shortcode_atts( array(
			"page_id"	 => $id,
			"class"		 => 'page-children',
			"order_by"	 => 'title'
		), $atts ) );

		/**
		 * Sanitize fields
		 */
		$page_id	 = (int) $page_id;
		$order_by	 = in_array( $order_by, array( 'title', 'order', 'date' ) ) ? $order_by : 'title';
		if ( 'order' == $order_by )
			$order_by	 = 'menu_order';

		/**
		 * Collect children of target page
		 */
		$children_of_page = get_children( array( "post_parent" => $page_id, "post_type" => "page", "orderby" => $order_by, "order" => "ASC", "post_status" => "publish" ) );
		if ( empty( $children_of_page ) ) {
			return "";
		}

		$text = "<ul class=" . esc_attr( $class ) . ">";
		foreach ( $children_of_page as $child_post ) {
			$text .= "<li><a href='" . get_bloginfo( 'wpurl' ) . "/" . get_page_uri( $child_post->ID ) . "'> $child_post->post_title </a></li>";
		}
		$text .= "</ul>";
		return $text;
	}
}
