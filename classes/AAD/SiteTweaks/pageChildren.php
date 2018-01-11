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
	public function __construct() {
		
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
	 * [page-children class=<class> page_id=<id> order_by=<order> children_of=<id> parent=<id>]
	 * 
	 *   class			string, class to include on list
	 *   parent			integer, list pages that have the provided single page as their parent.
	 *   page_id		integer, deprecated form of parent
	 *   children_of	integer, list all children of provided single page, including grand-children
	 * 	 order_by		string, field to order list by
	 * 
	 *   Only one of parent, children_of or page_id should be specified. They will be used in that order.
	 *
	 * @return text HTML list containing entries for each child of the current page.
	 * */
	function sc_page_children( $atts, $content = null ) {
		global $id;

		/**
		 * Retrieve shortcode attributes
		 */
		extract( shortcode_atts( array(
			"page_id"		 => $id,
			"parent"		 => -1,
			"children_of"	 => -1,
			"class"			 => 'page-children',
			"order_by"		 => 'post_title'
		), $atts ) );

		/**
		 * Sanitize fields
		 */
		$page_id	 = (int) $page_id;
		$parent		 = (int) $parent;
		$children_of = (int) $children_of;

		switch ( $order_by ) {
			case 'title':
				$order_by	 = 'post_title';
			// fall thru
			case 'post_title':
				break;
			case 'order':
				$order_by	 = 'menu_order';
			// fall thru
			case 'menu_order':
				break;
			case 'date':
				$order_by	 = 'post_date';
			case 'post_date':
				break;
			default:
				$order_by	 = 'post_title';
		}

		/**
		 * What to search for ...
		 */
		$get_children_of = array(
			'post_type'		 => 'page',
			'sort_column'	 => $order_by,
			'sort_order'	 => 'asc',
			'post_status'	 => 'publish' );

		/**
		 * Only one of page_id, parent or children_of should be specified.
		 */
		if ( $parent == -1 && $children_of == -1 ) {
			$get_children_of['parent'] = $page_id;
		} elseif ( $parent != -1 ) {
			$get_children_of['parent'] = $parent;
		} else {
			$get_children_of['child_of']	 = $children_of;
			$get_children_of['hierarchical'] = false;
		}

		/**
		 * Collect children of target page
		 */
		$children_of_page = get_pages( $get_children_of );
		if ( empty( $children_of_page ) ) {
			return "";
		}

		/**
		 * Natural sort by post_title, omit leading articles and non-word characters
		 */
		if ( $order_by == "post_title" ) {
			usort( $children_of_page, function($a, $b) {
				$ignore_prefix = '/^\W*(?:(?:a|an|the) )?\W*/i';
				$title_a = preg_replace( $ignore_prefix, '', $a->post_title );
				$title_b = preg_replace( $ignore_prefix, '', $b->post_title );
				return strnatcasecmp( $title_a, $title_b );
			} );
		}

		$text = "<ul class=" . esc_attr( $class ) . ">";
		foreach ( $children_of_page as $child_post ) {
			$text .= "<li><a href='" . get_bloginfo( 'wpurl' ) . "/" . get_page_uri( $child_post->ID ) . "'> $child_post->post_title </a></li>";
		}
		$text .= "</ul>";

		return $text;
	}

}
