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
 * Description of wooTweaks
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

class wooTweaks {

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
		 * Remove annoy message to install wootheme updater
		 */
		remove_action( 'admin_notices', 'woothemes_updater_notice' );

		/**
		 * Virtual Subscription product types do not need to be processed
		 */
		add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'filter_woo_item_needs_processing' ), 10, 3 );

		/**
		 * Change Backorder text
		 */
		// add_filter( 'woocommerce_get_availability', array( $this, 'woo_change_backorder_text' ), 100, 2 );

		/**
		 * Add a woocommerce filter to allow content to exist in an alternate content directory
		 */
		add_filter( 'woocommerce_downloadable_file_exists', array( $this, 'filter_woo_downloadable_file_exists' ), 10, 2 );

		/**
		 * Add hooks for handling downloads that are included as a part of a subscription
		 */
		if ( class_exists( 'WC_Subscription_Downloads', false ) ) {
			add_filter( 'woocommerce_get_price_html', array( $this, 'filter_woo_price_free_with_subscription' ), 10, 2 );
			add_filter( 'woocommerce_is_purchasable', array( $this, 'filter_woo_not_purchasable' ), 10, 2 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'action_woo_available_downloads' ), 30 );
		}
	}

	/**
	 * Allow WooCommerce to understand content in alternate content directory
	 *
	 * Uses filter defined in class-wc-product-download.php:file_exists()
	 *
	 * @param boolean $file_exists Earlier filters may have already decided if file exists
	 * @param string $file_url path to the downloadable file
	 */
	function filter_woo_downloadable_file_exists( $file_exists, $file_url ) {
		/**
		 * Hard-coded string based on setting of WP_CONTENT_URL defined on server
		 */
		if ( '/content' === substr( $file_url, 0, 8 ) ) {
			$filepath = realpath( WP_CONTENT_DIR . substr( $file_url, 8 ) );
			return file_exists( $filepath );
		}

		return $file_exists;
	}

	/**
	 * Virtual Subscription products do not need to be processed after payment is received. They can move
	 * directly to the 'completed' status.
	 * 
	 * Uses filter defined in class-wc-order.php:needs_processing()
	 * 
	 * @param boolean $needs_processing default return value to pass along
	 * @param WC_product $product Product item to check
	 * @param string $order_ID ID for the containing order
	 * @return boolean
	 */
	function filter_woo_item_needs_processing( $needs_processing, $product, $order_ID ) {
		$product_type = $product->get_type();
		if ( $product->is_virtual() && ( 'subscription' == $product_type || 'subscription_variation' == $product_type || 'variable-subscription' == $product_type ) ) {
			return false;
		}

		return $needs_processing;
	}

	/**
	 * Change "backorder" text
	 *
	 * @param array $availability
	 * @param WC_Product $product
	 * @return array
	 */
	function woo_change_backorder_text( $availability, $product ) {
		if ( 'available-on-backorder' == $availability['class'] ) {
			$availability['availability'] = 'Made to Order';
		}

		return $availability;
	}

	/**
	 * If product is included via subscription, indicate so
	 * 
	 * @param string $_price HTML text to display for price
	 * @param WC_Product $product
	 * @return string HTML text to display for price
	 */
	public function filter_woo_price_free_with_subscription( $price, $product ) {
		if ( self::is_free_with_sub( $product ) ) {
			$price = "Free with Membership!";
		}

		return $price;
	}

	/**
	 * When user has active subscription to download product, display links to download list on the product page
	 * 
	 * @param void
	 * @return string HTML
	 */
	public function action_woo_available_downloads() {
		global $product;

		$product_id = $product instanceof \WC_Product ? $product->get_id() : 0;

		if ( !is_user_logged_in() || !$product->is_downloadable() || !self::is_free_with_sub( $product ) || !current_user_can( 'wc_memberships_purchase_restricted_product', $product_id ) ) {
			return;
		}

		/**
		 * Get endpoints for my-account page
		 */
		$endpoints = wc_get_account_menu_items();
		if ( !array_key_exists( 'downloads', $endpoints ) ) {
			return;
		}

		$url = wc_get_account_endpoint_url( 'downloads' );
		?>
		<p>This download is included with your active Membership.</p>
		<p>View your <a href="<?php echo esc_url( $url ); ?>">available downloads</a> on your account page.</p>
		<?php
		return;
	}

	/**
	 * Is Product free and provided via a subscription?
	 * 
	 * @param WC_product $product
	 * @return boolean
	 */
	private function is_free_with_sub( $product ) {
		$product_id	 = $product instanceof \WC_Product ? $product->get_id() : 0;
		$price		 = $product instanceof \WC_Product ? $product->get_price() : 0;

		$subscriptions = class_exists( '\WC_Subscription_Downloads', false ) ? \WC_Subscription_Downloads::get_subscriptions( $product_id ) : array();

		return ( 0 == $price && count( $subscriptions ) > 0 );
	}

	/**
	 * Products that are included in a subscription are not available for direct purchase
	 * 
	 * @param boolean $purchaseable
	 * @param WC_product $product
	 * @return boolean
	 */
	public function filter_woo_not_purchasable( $purchaseable, $product ) {
		if ( self::is_free_with_sub( $product ) ) {
			return false;
		}

		return $purchaseable;
	}

}
