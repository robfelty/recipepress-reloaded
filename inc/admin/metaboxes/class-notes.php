<?php

namespace Recipepress\Inc\Admin\Metaboxes;

use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Common\Abstracts\MetaData;

/**
 * Saving the recipe notes meta information.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Notes extends Metadata {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $plugin_name The ID of this plugin.
	 * @param   string $version The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version, 'rpr_notes_metabox', 'rpr_recipe_notes', __DIR__ );
	}

	/**
	 * Add a metabox for the recipe instruction.
	 *
	 * If the option has been disabled on the plugin setting page, return early with a false
	 * and don't do anything.
	 *
	 * @since 1.0.0
	 *
	 * @uses  add_meta_box
	 * @return bool
	 */
	public function add_metabox() {

		add_meta_box(
			$this->metabox_id,
			__( 'Notes', 'recipepress-reloaded' ),
			array( $this, 'render_metabox' ),
			$this->post_type,
			'normal',
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
	 * Builds and display the metabox UI.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $recipe The current post/recipe object.
	 *
	 * @return void
	 */
	public function render_metabox( $recipe ) {
		$description              = get_post_meta( $recipe->ID, $this->meta_key, true );
		$options                  = array(
			'textarea_rows' => 16,
		);
		$options['media_buttons'] = true;

		wp_editor( $description, $this->meta_key, $options );
	}

	/**
	 * Check the presence of, sanitizes then saves book's ISBN.
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
	 * @return void
	 */
	public function save_metabox_metadata( $recipe_id, $data, $recipe ) {

		$new_notes = isset( $data[ $this->meta_key ] ) ? $data[ $this->meta_key ] : '';
		$old_notes = get_post_meta( $recipe_id, $this->meta_key, true );

		if ( $new_notes !== $old_notes ) {
			update_post_meta( $recipe_id, $this->meta_key, wp_kses_post( $new_notes ) );
		} elseif ( '' === $new_notes && $old_notes ) {
			delete_post_meta( $recipe_id, $this->meta_key, $old_notes );
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
		register_post_meta(
			$this->post_type,
			$this->meta_key,
			array(
				'single'        => true,
				'type'          => 'string',
				'show_in_rest'  => true,
				'description'   => 'The recipe notes metadata',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}

