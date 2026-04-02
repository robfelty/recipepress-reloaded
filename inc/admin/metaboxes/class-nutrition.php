<?php

namespace Recipepress\Inc\Admin\Metaboxes;

use Recipepress\Inc\Common\Abstracts\Metadata;
use Recipepress\Inc\Common\Traits\Utilities;
use Recipepress\Inc\Common\Traits\Values;
use Recipepress\Inc\Core\Options;

/**
 * Saving the recipe nutrition meta information.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Nutrition extends Metadata {

	use Utilities;
	use Values;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $plugin_name The ID of this plugin.
	 * @param   string $version     The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version, 'rpr_nutrition_metabox', '', __DIR__, false, true );
	}

	/**
	 * Add a metabox to the WP post edit screen
	 *
	 * @since 1.0.0
	 *
	 * @uses  add_meta_box
	 *
	 * @return bool
	 */
	public function add_metabox() {

		add_meta_box(
			$this->metabox_id,
			__( 'Nutrition', 'recipepress-reloaded' ),
			array( $this, 'render_metabox' ),
			'rpr_recipe',
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

		return true;
	}

	/**
	 * Check the presence of, sanitizes then saves the metabox's data.
	 *
	 * @since 1.0.0
	 *
	 * @uses  $this->check_nonce()
	 * @uses  update_post_meta()
	 * @uses  wp_verify_nonce()
	 * @uses  sanitize_text_field()
	 *
	 * @param int      $recipe_id The post ID of the recipe post.
	 * @param array    $data      The data passed from the post custom metabox.
	 * @param \WP_Post $recipe    The recipe object this data is being saved to.
	 *
	 * @return void
	 */
	public function save_metabox_metadata( $recipe_id, $data, $recipe ) {

		if ( ! $this->check_nonce( $data ) ) {
			return;
		}

		$default_fields = $this->get_nutrition_fields( 'default' );

		foreach ( $default_fields as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$old = get_post_meta( $recipe_id, $key, true );
				$new = $data[ $key ];

				if ( $new !== $old ) {
					update_post_meta( $recipe_id, $key, $new );
				} elseif ( '' === $new && $old ) {
					delete_post_meta( $recipe_id, $key, $old );
				}
			}
		}

		$additional_fields = $this->get_nutrition_fields( 'additional' );

		foreach( $additional_fields as $k => $v ) {
			$key = array_values( $v )[0];

			if ( isset( $data[ $key ] ) ) {
				$old = get_post_meta( $recipe_id, $key, true );
				$new = $data[ $key ];

				if ( $new !== $old ) {
					update_post_meta( $recipe_id, $key, $new );
				} elseif ( '' === $new && $old ) {
					delete_post_meta( $recipe_id, $key, $old );
				}
			}
		}


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
