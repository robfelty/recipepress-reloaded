<?php
/**
 * Handles saving the recipe source meta information.
 *
 * @package    Recipepress
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */

namespace Recipepress\Inc\Admin\Metaboxes;

use Recipepress as NS;
use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Common\Abstracts\Metadata;

/**
 * Saving the recipe source meta information.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Source extends Metadata {

	use NS\Inc\Common\Traits\Utilities;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $plugin_name The ID of this plugin.
	 * @param   string $version     The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version, 'rpr_source_metabox', 'rpr_recipe_source', __DIR__ );
	}

	/**
	 * Add a metabox to the WP post edit screen
	 *
	 * @since 1.0.0
	 *
	 * @uses  add_meta_box
	 * @return bool
	 */
	public function add_metabox() {

		if ( ! $this->display_metabox() ) {
			return false;
		}

		add_meta_box(
			$this->metabox_id,
			__( 'Recipe source', 'recipepress-reloaded' ),
			array( $this, 'render_metabox' ),
			$this->post_type,
			'side',
			'high'
		);

		return true;
	}

	/**
	 * Should we display this metabox.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function display_metabox() {

		return (bool) Options::get_option( 'rpr_use_source_meta' );
	}

	/**
	 * Check the presence of, sanitizes then saves the metabox's data.
	 *
	 * @since 1.0.0
	 *
	 * @uses  update_post_meta()
	 * @uses  wp_verify_nonce()
	 * @uses  sanitize_text_field()
	 *
	 * @param int      $recipe_id  The post ID of the recipe post.
	 * @param array    $data       The data passed from the post custom metabox.
	 * @param \WP_Post $recipe     The recipe object this data is being saved to.
	 *
	 * @return bool|int
	 */
	public function save_metabox_metadata( $recipe_id, $data, $recipe ) {

		if ( ! $this->check_nonce( $data ) ) {
			return false;
		}

		$old = get_post_meta( $recipe_id, $this->meta_key, true );
		$new = array();

		if ( ! empty( $data[ $this->meta_key ] ) ) {
			foreach( $data[ $this->meta_key ] as $key => $value ) {
				if ( 'link' === $key ) {
					$new[ 'link' ] = remove_accents( urldecode( $value ) );
				} else {
					$new[ $key ] = sanitize_text_field( $value );
				}
			}
		}

		if ( '' === $new['name'] && '' === $new['link'] && ! empty( $old['name'] ) ) {
			delete_post_meta( $recipe_id, $this->meta_key );
		} elseif ( $new && $new !== $old ) {
			update_post_meta( $recipe_id, $this->meta_key, $new );
		}

		return $recipe_id;
	}

	/**
	 * Register this meta information
	 *
	 * This is used in the WP REST API's `meta` field
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function register_meta() {
		// TODO: Implement register_meta() method.
	}
}
