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
 * Description of rssHandler
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

class rssHandler {

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
		 * Include Featured Image in RSS Feeds
		 */
		add_filter( 'the_excerpt_rss', array( $this, 'filter_include_featured_image' ), 10, 1 );
		add_filter( 'the_content_feed', array( $this, 'filter_include_featured_image' ), 10, 1 );
	}

	/**
	 * Add post thumbnail (featured image) to RSS feed
	 * 
	 * @global WP_post $post
	 * @param string $content
	 * @return string Content
	 */
	function filter_include_featured_image( $content ) {
		global $post;

		if ( has_post_thumbnail( $post->ID ) ) {
			/**
			 * Add thumbnail to beginning of the content so it's included before excerpt or post content.
			 */
			$content = get_the_post_thumbnail( $post->ID, 'medium', array( 'style' => 'margin-bottom: 15px;' ) ) . $content;
		}

		return $content;
	}

}
