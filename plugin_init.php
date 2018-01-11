<?php

/*
  Plugin Name: Puma Studios
  Plugin URI: https://github.com/KnBrckr/pumastudios
  Description: Site Specific Tweaks and Shortcodes
  Version: 0.9
  Author: Kenneth J. Brucker
  Author URI: http://action-a-day.com
  License:     GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html

  Copyright: 2017 Kenneth J. Brucker (email: ken.brucker@action-a-day.com)

  This file is part of pumastudios site modifications, a plugin for Wordpress.

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * @package AAD\SiteTweaks
 * 
 * Uses the Pimple framework defined at https://pimple.sensiolabs.org
 */
/**
 *  Protect from direct execution
 */
if ( !defined( 'WP_PLUGIN_DIR' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die( 'I don\'t think you should be here.' );
}

/*
 * Define classes that will be used
 */

use AAD\SiteTweaks\Plugin;

/**
 * Define autoloader for plugin
 */
spl_autoload_register( function ( $class_name ) {
	if ( false !== strpos( $class_name, 'AAD\SiteTweaks' ) ) {
		$classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
		$class_file	 = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name ) . '.php';
		require $classes_dir . $class_file;
	}
} );

/**
 * Hook plugin loaded to execute setup
 */
add_action( 'plugins_loaded', function () {
	$plugin = new Plugin();

	$plugin['version']	 = '0.9';
	$plugin['path']		 = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR;
	$plugin_dir_url		 = plugin_dir_url( __FILE__ );
	$plugin['urls']	 = array(
		'plugin' => $plugin_dir_url,
		'js'	 => $plugin_dir_url . 'assets/js/',
		'css'	 => $plugin_dir_url . 'assets/css/',
		'fonts'	 => $plugin_dir_url . 'assets/fonts/',
		'images' => $plugin_dir_url . 'assets/images/'
	);

	$plugin['sc_page_children']	 = new AAD\SiteTweaks\pageChildren();
	$plugin['thrive_tweaks']	 = new AAD\SiteTweaks\thriveTweaks();
	$plugin['rss']				 = new AAD\SiteTweaks\rssHandler( $plugin['urls'] );

	if ( class_exists( 'WooCommerce', false ) ) {
		$plugin ['woo_tweaks'] = new AAD\SiteTweaks\wooTweaks();
	}

	/*
	 * 
	 * Instantiate needed plugin classes

	  $variationTableService = function ($product) {
	  $varTable = new VariationTable( $product );
	  return $varTable;
	  };
	  $plugin['VariationScreen'] = function ($p) use ($variationTableService) {
	  $varScreen = new VariationScreen( $p['version'], $p['url'], $variationTableService );
	  return $varScreen;
	  };

	  $variationScreen = $plugin[ 'VariationScreen' ];

	 */

	$plugin->run();
} );

/**
 *  Rudimentary hooks
 */
add_action( 'init', function() {
	/**
	 * Add excerpt box to page edit screen
	 */
	add_post_type_support( 'page', 'excerpt' );
} );
