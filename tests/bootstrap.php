<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Pumastudios
 */

global $wp_plugin_paths;

$pumastudios_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $pumastudios_tests_dir ) {
	$pumastudios_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $pumastudios_tests_dir . '/includes/functions.php' ) ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo "Could not find $pumastudios_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $pumastudios_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	global $wp_plugin_paths;

	$dir                     = getenv( 'WP_CORE_DIR' ) . '/wp-content/plugins';
	$real_dir                = dirname( dirname( __DIR__ ) );
	$wp_plugin_paths[ $dir ] = $real_dir; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	require dirname( dirname( __FILE__ ) ) . '/pumastudios.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $pumastudios_tests_dir . '/includes/bootstrap.php';
