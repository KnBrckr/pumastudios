<?php
/**
 * Copyright (C) 2019 Kenneth J. Brucker <ken@pumastudios.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package PumaStudios\SiteTweaks
 */

namespace AAD\SiteTweaks;

/**
 * Class Genesis
 *
 * Alter behavior of Genesis Theme
 */
class Genesis {

	/**
	 * Run code for plugin startup
	 */
	public function run() {
		if ( $this->is_genesis() ) {
			add_action( 'genesis_footer_creds_text', [ $this, 'filter_footer_credits_text' ] );
		}
	}

	/**
	 * Replace credits text in the footer
	 *
	 * Ignores incoming text parameter sent by filter
	 *
	 * @return string
	 */
	public function filter_footer_credits_text() {
		return sprintf( '[footer_copyright before="%s "]', __( 'Copyright', 'ps-SiteTweaks' ) );
	}

	/**
	 * Check if active theme is based on Genesis Framework
	 *
	 * @return bool true if active theme is a genesis theme
	 */
	private function is_genesis() {
		$theme = wp_get_theme();

		if ( 'Genesis' === $theme->get( 'Name' ) || 'genesis' === $theme->get( 'Template' ) ) {
			return true;
		}

		return false;
	}
}
