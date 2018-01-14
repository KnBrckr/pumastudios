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

/*
 * Some content based on https://codex.wordpress.org/Javascript_Reference/wp.media
 */

namespace AAD\SiteTweaks;

/**
 * Add a Page Icon to display of Page Title
 * 
 * @package pumastudios
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

class PageIcon {
	
	/**
	 * Plugin version
	 */
	private $version;
	
	/**
	 * Plugin name
	 */
	private $name;
		
	/**
	 * Hash of plugin URLs to locate assets
	 * 
	 * Hash indices: plugin, js, css, fonts, images
	 */
	private $urls;
	
	/**
	 * ID used to identify page-icon metabox in HTML content, defined based on plugin name
	 */
	private $metabox_id;
	
	/**
	 * Javascript Handle, defined based on plugin name
	 */
	private $js_handle;
	
	/**
	 * CSS Handle, defined based on plugin name
	 */
	private $css_handle;
	
	/**
	 * Post Meta Data slug for storing image id
	 */
	const META_DATA_SLUG = 'aad_page_icon_img_id';
	
	/**
	 * nonce used to protect metabox save from possible abuse
	 */
	private $nonce;
	
	/**
	 * nonce action associated with nonce
	 */
	private $nonce_action;
	
	/**
	 * Instantiate
	 * 
	 * @return void
	 */
	public function __construct( $version, $name, $urls ) {
		$this->version		 = $version;
		$this->name			 = $name;
		$this->urls			 = $urls;
		$this->metabox_id	 = $name . '-page-icon-metabox';
		$this->js_handle	 = $name . '-page-icon-js';
		$this->css_handle	 = $name . '-page-icon-css';
		$this->nonce		 = $name . 'page-icon-nonce';
		$this->nonce_action	 = $name . 'page-icon-meta-box';
	}

	/**
	 * Plug into WP
	 * 
	 * @return void
	 */
	public function run() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts') );
			add_action( 'add_meta_boxes', array($this, 'action_register_meta_boxes') );
			add_action( 'save_post', array($this, 'save_meta_box'), 10, 1 );
		} else { // Don't filter post titles on admin screens
			add_filter( 'the_title', array($this, 'filter_add_icon'), 10, 2);
			add_action( 'wp_enqueue_scripts', array($this, 'action_enqueue_style') );
		}
	}

	/**
	 * Enqueue required scripts
	 * 
	 * @return void
	 */
	function action_admin_enqueue_scripts() {
		/**
		 * Enqueue WP media to use the media picker
		 */
		wp_enqueue_media();
		
		/**
		 * Enqueue script for handling media window
		 */
		wp_register_script(
			$this->js_handle,						// Script Handle
			$this->urls['js'] . 'page-icon.js',		// Javascript source
			array('jquery-core'),					// Dependencies
			$this->version,							// Script version for cache busting
			false									// In footer?
		);
		
		wp_localize_script( $this->js_handle, 'aad_page_icon_data', array(
			'meta_box_id' => $this->metabox_id
		) );
		
		wp_enqueue_script( $this->js_handle );
	}
	
	/**
	 * Enqueue CSS Style
	 */
	function action_enqueue_style() {
		wp_enqueue_style( 
			$this->css_handle,						// CSS Handle
			$this->urls['css'] . 'page-icon.css',	// CSS Source
			array(),								// Dependencies
			$this->version							// Version for cache busting
		);
	}
	
	/**
	 * Register meta box for display on post/page edit screens
	 * 
	 * @return void
	 */
	public function action_register_meta_boxes() {
		add_meta_box(
			$this->metabox_id,						// ID
			__( 'Page Icon', $this->name ),			// Title
			array( $this, 'render_meta_box' ),		// Callback function
			array( 'page', 'post'),					// Screens
			'side'									// Context
		);
	}

	/**
	 * Render contents of metabox used to select a page icon
	 */
	public function render_meta_box() {
		global $post;
		
		wp_nonce_field( $this->nonce_action, $this->nonce);

		// Get WordPress' media upload URL
		$upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );

		// See if there's a media id already saved as post meta
		$icon_img_id = get_post_meta( $post->ID, self::META_DATA_SLUG, true );
		$you_have_img = $icon_img_id != "";

		// Get the image src, treat as an icon
		$icon_img_src = wp_get_attachment_image_src( $icon_img_id, 'thumbnail', true );
		?>
		<div class="page-icon-img-container">
		<?php if ( $you_have_img ) : ?>
			        <img src="<?php echo $icon_img_src[0] ?>" alt="" style="max-width:100%;" />
				<?php endif; ?>
		</div>

		<p class="hide-if-no-js">
		    <a class="upload-page-icon-img thickbox <?php if ( $you_have_img ) {
			echo 'hidden';
		} ?>" 
		       href="<?php echo $upload_link ?>">
			<?php _e( 'Set icon image', $this->name ) ?>
		    </a>
		    <a class="delete-page-icon-img <?php if ( !$you_have_img ) {
			echo 'hidden';
		} ?>" 
		      href="#">
		<?php _e( 'Remove this image', $this->name ) ?>
		    </a>
		</p>

		<input class="page-icon-img-id" name="page-icon-img-id" type="hidden" value="<?php echo esc_attr( $icon_img_id ); ?>" />
		<?php
	}

	/**
	 * Save page icon as post meta data
	 *
	 * @param int     $post_id  The Post id
	 * @return void
	 */
	public function save_meta_box($post_id) {
		if ( ! Plugin::verify_save( $this->nonce, $this->nonce_action, $post_id ) ) {
			return;
		}
		
		$img_id = filter_input( INPUT_POST, 'page-icon-img-id' );
		if ( $img_id && is_numeric($img_id) ) {
			update_post_meta( $post_id, self::META_DATA_SLUG, $img_id);
		} elseif ( $img_id == "" ) {
			delete_post_meta( $post_id, self::META_DATA_SLUG );
		}
	}
	
	/**
	 * Filter page title to add page icon when it's available
	 * 
	 * @param string $title Post Title
	 * @param integer $id Post ID
	 * @return string Filtered Post Title
	 */
	public function filter_add_icon( $title, $id ) {
		
		// Retrieve 
		$icon_img_id = get_post_meta( $id, self::META_DATA_SLUG, true );
		
		if ( ! $icon_img_id != "" ) {
			return $title;
		}
		
		// Get the image src, treat as an icon
		$icon_img_src = wp_get_attachment_image_src( $icon_img_id, 'thumbnail', true );

		return '<img class="page-icon" src="' . $icon_img_src[0] . '">' . $title;
	}

}
