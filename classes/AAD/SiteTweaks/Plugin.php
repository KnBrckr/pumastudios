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


/**
 * Plugin Container
 * 
 * @package SiteTweaks
 * @author Kenneth J. Brucker <ken.brucker@action-a-day.com>
 */

namespace AAD\SiteTweaks;

/*
 *  Protect from direct execution
 */
if ( !defined( 'WP_PLUGIN_DIR' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die( 'I don\'t think you should be here.' );
}

class Plugin extends Container {

	public function run() {
		foreach ( $this->values as $key => $content ) { // Loop on contents
			if ( is_callable( $content ) ) {
				$content = $this[$key];
			}
			if ( is_object( $content ) ) {
				$reflection = new \ReflectionClass( $content );
				if ( $reflection->hasMethod( 'run' ) ) {
					$content->run(); // Call run method on object
				}
			}
		}
	}
	
	/**
	 * Performs meta box save validation
	 *
	 * @param $nonce_name
	 * @param $nonce_action
	 * @param $post_id
	 *
	 * @return bool
	 */
	public static function verify_save( $nonce_name, $nonce_action, $post_id ) {
		// Validate provided nonce
		$nonce = filter_input( INPUT_POST, $nonce_name);
		if ( ! $nonce ) {
			return false;
		}
		if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			return false;
		}
		
		// Ignore request if part of an auto-save
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		
		// Validate permissions
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return false;
		}
		
		// check if there was a multisite switch before
		if ( is_multisite() && ms_is_switched() ) {
			return false;
		}

		// A-OK!
		return true;
	}

}
