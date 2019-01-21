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

// Protect from direct execution.
if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die( 'I don\'t think you should be here.' );
}

/**
 * RssHandler Class
 *
 * Control which categories show up in feeds and default post lists
 */
class RssHandler {

	/**
	 * Instantiate
	 *
	 * @param array $urls Array of asset URLs.
	 *
	 * @return void
	 */
	public function __construct( $urls ) {
		$this->urls = $urls;
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

		/**
		 * Connect to category add/edit screen to setup category filtering
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_css' ) );

		add_action( 'category_edit_form_fields', array( $this, 'category_edit_form_fields' ) );
		add_action( 'category_add_form_fields', array( $this, 'category_add_form_fields' ) );

		add_action( 'edited_category', array( $this, 'category_save_form_fields' ), 10, 1 );
		add_action( 'create_category', array( $this, 'category_save_form_fields' ), 10, 1 );
		add_action( 'delete_category', [ $this, 'delete_category' ], 10, 1 );

		add_filter( 'manage_edit-category_columns', [ $this, 'filter_add_category_column' ], 20, 1 );
		add_filter( 'manage_category_custom_column', [ $this, 'filter_manage_custom_column' ], 10, 3 );

		/**
		 * Filter categories available in feeds
		 */
		add_filter( 'pre_get_posts', array( $this, 'filter_feed_categories' ) );
	}

	/**
	 * Add post thumbnail (featured image) to RSS feed
	 *
	 * @global \WP_Post $post
	 *
	 * @param string    $content Post content to display.
	 *
	 * @return string Content
	 */
	public function filter_include_featured_image( $content ) {
		global $post;

		if ( has_post_thumbnail( $post->ID ) ) {
			/**
			 * Add thumbnail to beginning of the content so it's included before excerpt or post content.
			 */
			$content = get_the_post_thumbnail( $post->ID, 'medium', array( 'style' => 'margin-bottom: 15px;' ) ) . $content;
		}

		return $content;
	}

	/**
	 * For non-category feed, remove any categories that should not be displayed
	 *
	 * @param \WP_Query $query WordPress query to modify.
	 *
	 * @return \WP_Query
	 */
	public function filter_feed_categories( $query ) {
		/**
		 * Do not filter feeds for specific categories
		 */
		if ( $query->is_feed() && ! $query->is_category() ) {
			$categories_in_feed = get_option( 'aad_categories_in_feed' );
			if ( false !== $categories_in_feed ) {
				$exclude_cat_ids = array();
				foreach ( $categories_in_feed as $term_id => $in_feed ) {
					if ( $in_feed ) {
						continue;
					} // Skip categories that are included in feed

					array_push( $exclude_cat_ids, $term_id );
				}
				$query->set( 'category__not_in', $exclude_cat_ids );
			}
		}

		return $query;
	}

	/**
	 * Add column to display list for taxonomy "category"
	 *
	 * @param array $columns default columns to display.
	 *
	 * @return array of columns to display in wp-admin/edit-tags.php?taxonomy=category
	 */
	public function filter_add_category_column( $columns ) {
		$columns['in-feed'] = 'Include In Feed';

		return $columns;
	}

	/**
	 * Provide cell content (HTML) for custom columns
	 *
	 * @param string $content default content for cell.
	 * @param string $column_name e.g. 'in-feed'.
	 * @param string $term_id taxonomy term id.
	 *
	 * @return string
	 */
	public function filter_manage_custom_column( $content, $column_name, $term_id ) {
		if ( 'in-feed' === $column_name ) {
			$categories_in_feed = get_option( 'aad_categories_in_feed' );

			$in_feed = $categories_in_feed ? ( $categories_in_feed[ $term_id ] ?? true ) : true;

			return $in_feed ? 'Yes' : 'No';
		}

		return $content;
	}

	/**
	 * Enqueue admin CSS to format edit fields
	 */
	public function admin_enqueue_css() {
		wp_enqueue_style( 'aad-pumastudios-admin-css', $this->urls['css'] . 'admin.css' );
	}

	/**
	 * Display row to select if category should be included/excluded in RSS feed
	 *
	 * @param \WP_Term $term taxonomy term object.
	 */
	public function category_edit_form_fields( $term ) {
		$term_id            = $term->term_id;
		$categories_in_feed = get_option( 'aad_categories_in_feed' );

		$in_feed = $categories_in_feed ? ( $categories_in_feed[ $term_id ] ?? true ) : true;

		$checked_yes = $in_feed ? 'checked' : '';
		$checked_no  = $in_feed ? '' : 'checked';
		?>
		<tr class="form-field term-sitefeed-wrap">
			<th>Include in Site Feed?</th>
			<td>
				<label><input type="radio" name="aad_category_in_feed"
				              value="yes" <?php echo $checked_yes; ?>>Yes</label>
				<label><input type="radio" name="aad_category_in_feed" value="no" <?php echo $checked_no; ?>>No</label>
				<p class="description">Set to 'No' to exclude this category from the main site RSS Feed. It will still
					be
					possible to see the feed for this category specifically. </p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Display form field in category section to include/exclude category from RSS feed
	 */
	public function category_add_form_fields() {
		?>
		<div class="form-field term-sitefeed-wrap">
			<fieldset>
				<legend>Include in Site Feed?</legend>
				<label><input type="radio" name="aad_category_in_feed" value="yes" checked>Yes</label>
				<label><input type="radio" name="aad_category_in_feed" value="no">No</label>
			</fieldset>
			<p class="description">Set to 'No' to exclude this category from the main site RSS Feed. It will still be
				possible to see the feed for this category specifically. </p>
		</div>
		<?php
	}

	/**
	 * Save setting to include/exclude category from RSS feed
	 *
	 * @param string $term_id taxonomy term ID.
	 */
	public function category_save_form_fields( $term_id ) {
		if ( filter_has_var( INPUT_POST, 'aad_category_in_feed' ) ) {
			$categories_in_feed             = get_option( 'aad_categories_in_feed', array() );
			$categories_in_feed[ $term_id ] = filter_input( INPUT_POST, 'aad_category_in_feed', FILTER_VALIDATE_BOOLEAN );
			update_option( 'aad_categories_in_feed', $categories_in_feed );
		}
	}

	/**
	 * Remove deleted category from list of tracked categories
	 *
	 * @param string $term_id taxonomy term id.
	 */
	public function delete_category( $term_id ) {
		$categories_in_feed = get_option( 'aad_categories_in_feed', array() );
		if ( array_key_exists( $term_id, $categories_in_feed ) ) {
			unset( $categories_in_feed[ $term_id ] );
			update_option( 'aad_categories_in_feed', $categories_in_feed );
		}
	}

}
