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
 * Description of thriveTweaks
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

class thriveTweaks {

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
		 * Remove Thrive Themes 'clone' option from WooCommerce products
		 */
		if ( is_admin() ) {
			add_action( 'load-edit.php', array( $this, 'remove_thrive_duplicate_link_row' ) );
		}
	}

	/**
	 * For Product pages, remove the duplicate link that Thrive would add
	 */
	function remove_thrive_duplicate_link_row() {
		$screen = get_current_screen();

		if ( !$screen )
			return;

		if ( 'product' == $screen->post_type ) {
			remove_filter( 'post_row_actions', 'thrive_make_duplicate_link_row', 10 );
			remove_filter( 'page_row_actions', 'thrive_make_duplicate_link_row', 10 );
		}
	}

}
