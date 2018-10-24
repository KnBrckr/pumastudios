<?php

/*
 * Copyright (C) 2018 Kenneth J. Brucker <ken.brucker@action-a-day.com>
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
 * Appointlet requires use of data-appointlet-organization attribute on img tags.
 * WP Editor filters this tag
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

class AppointletTweaks {

	/**
	 * Plug into WP
	 * 
	 * @return void
	 */
	public function run() {
		/**
		 * Tweak TinyMCE on load
		 */
		add_filter('tiny_mce_before_init', [AppointletTweaks::class, 'change_mce_options']);
	}

	public static function change_mce_options($initArray) {
	    $ext = 'img[data-appointlet-organization]';
	    if ( isset( $initArray['extended_valid_elements'] ) ) {
	        $initArray['extended_valid_elements'] .= ',' . $ext;
	    } else {
	        $initArray['extended_valid_elements'] = $ext;
	    }
	    return $initArray;
	}
	
}