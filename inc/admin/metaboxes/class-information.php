<?php

namespace Recipepress\Inc\Admin\Metaboxes;

use Recipepress\Inc\Common\Abstracts\Metadata;
use Recipepress\Inc\Core\Options;

/**
 * Saving the instructions meta information.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Information extends Metadata {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $plugin_name The ID of this plugin.
	 * @param   string $version The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version, 'rpr_information_metabox', '', __DIR__, false, true );
	}

	/**
	 * Add a metabox to the WP post edit screen
	 *
	 * @since 1.0.0
	 *
	 * @uses  add_meta_box
	 *
	 * @return void
	 */
	public function add_metabox() {

		add_meta_box(
			$this->metabox_id,
			__( 'General Information', 'recipepress-reloaded' ),
			array( $this, 'render_metabox' ),
			'rpr_recipe',
			'side',
			'high'
		);
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
	 * @uses  update_post_meta()
	 * @uses  wp_verify_nonce()
	 * @uses  sanitize_text_field()
	 *
	 * @param int      $recipe_id The post ID of the recipe post.
	 * @param array    $data      The data passed from the post custom metabox.
	 * @param \WP_Post $recipe    The recipe object this data is being saved to.
	 *
	 * @return bool|int
	 */
	public function save_metabox_metadata( $recipe_id, $data, $recipe ) {

		if ( ! $this->check_nonce( $data ) ) {
			return false;
		}

		$fields = array(
			'rpr_recipe_servings',
			'rpr_recipe_servings_type',
			'rpr_recipe_prep_time',
			'rpr_recipe_cook_time',
			'rpr_recipe_passive_time',
		);

		foreach ( $fields as $key ) {
			if ( isset( $data[ $key ] ) ) {

				$old = get_post_meta( $recipe_id, $key, true );
				$new = sanitize_text_field( $data[ $key ] );

				if ( $new !== $old ) {
					update_post_meta( $recipe_id, $key, $new );
				} elseif ( '' === $new && $old ) {
					delete_post_meta( $recipe_id, $key, $old );
				}
			}
		}

		if ( ! empty( $data['rpr_recipe_servings_type'] ) && Options::get_option( 'rpr_use_serving_unit_list' ) ) {
			$saved = explode( ',', Options::get_option( 'rpr_serving_unit_list' ) );
			$new = implode( ',', array_unique( array_merge( $saved, array( $data['rpr_recipe_servings_type'] ) ) ) );

			Options::update_option( 'rpr_serving_unit_list', $new );
		}

		return $recipe_id;
	}

	/**
	 * Prints a select list of the servings unit stored in our settings.
	 *
	 * @since 1.0.0
	 *
	 * @param string $selected The current selected unit.
	 *
	 * @return void
	 */
	public function the_serving_unit_selection( $selected = null ) {

		$output = '';
		$units  = explode( ',', Options::get_option( 'rpr_serving_unit_list' ) );

		foreach ( $units as $key => $unit ) {
			$output .= '<option value="' . esc_attr( $unit ) . '"';
			if ( $unit === $selected ) {
				$output .= ' selected="selected" ';
			}
			$output .= '>' . esc_html( $unit ) . '</option>' . "\n";
		}
		if ( ! in_array( $selected, $units, true ) ) {
			$output .= '<option value="' . $selected . '"  selected="selected" >' . $selected . '</option>\n';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $output;
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
