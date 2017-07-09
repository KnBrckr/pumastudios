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
 * Description of adminTweaks
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

class adminTweaks {

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
	function fix_admin_ajax_url( $url, $path, $blog_id ) {
		/**
		 * Scheme for admin-ajax.php should match scheme for current page
		 */
		if ( $path == 'admin-ajax.php' ) {
			return set_url_scheme( $url, is_ssl() ? 'https' : 'http'  );
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
	function filter_attachment_slug( $data, $postarr ) {
		/**
		 * Only work on attachment types
		 */
		if ( !array_key_exists( 'post_type', $data ) || 'attachment' != $data['post_type'] )
			return $data;

		/**
		 * Add mime type to the post title to build post-name
		 */
		$post_title		 = array_key_exists( 'post_title', $data ) ? $data['post_title'] : $postarr['post_title'];
		$post_mime_type	 = array_key_exists( 'post_mime_type', $data ) ? $data['post_mime_type'] : $postarr['post_mime_type'];
		$post_mime_type	 = str_replace( '/', '-', $post_mime_type );
		$post_name		 = sanitize_title( $post_title . '-' . $post_mime_type );

		/**
		 * Generate unique slug for post name
		 */
		$post_ID	 = array_key_exists( 'ID', $data ) ? $data['ID'] : $postarr['ID'];
		$post_status = array_key_exists( 'post_status', $data ) ? $data['post_status'] : $postarr['post_status'];
		$post_type	 = array_key_exists( 'post_type', $data ) ? $data['post_type'] : $postarr['post_type'];
		$post_parent = array_key_exists( 'post_parent', $data ) ? $data['post_parent'] : $postarr['post_parent'];

		$post_name			 = wp_unique_post_slug( $post_name, $post_ID, $post_status, $post_type, $post_parent );
		$data['post_name']	 = $post_name;

		return $data;
	}

}
