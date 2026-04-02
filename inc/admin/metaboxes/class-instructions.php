<?php

namespace Recipepress\Inc\Admin\Metaboxes;

use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Common\Abstracts\MetaData;
use Recipepress\Inc\Common\Traits\Utilities;
use Recipepress\Inc\Frontend\Template;

/**
 * Saving the instructions meta information.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Instructions extends Metadata {

	use Utilities;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $plugin_name The ID of this plugin.
	 * @param   string $version The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version, 'rpr_instructions_metabox', 'rpr_recipe_instructions', __DIR__, false, true );
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
	 *
	 * @return void
	 */
	public function add_metabox() {

		add_meta_box(
			$this->metabox_id,
			__( 'Instructions', 'recipepress-reloaded' ),
			array( $this, 'render_metabox' ),
			$this->post_type,
			'normal',
			'high'
		);
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
				'type'          => 'array',
				'show_in_rest'  => array(
					'schema' => array(
						'type' => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'grouptitle'    => array(
									'type' => 'string',
								),
								'sort'    => array(
									'type' => 'string',
								),
								'image'    => array(
									'type' => 'string',
								),
								'description'    => array(
									'type' => 'string',
								),
								'key'    => array(
									'type' => 'string',
								),
								'line'    => array(
									'type' => 'string',
								),
							),
						)
					)
				),
				'description'   => 'The recipe\'s instructions',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
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
	 * Check the presence of, sanitizes then saves book's ISBN.
	 *
	 * @since 1.0.0
	 *
	 * @uses  update_post_meta()
	 * @uses  wp_verify_nonce()
	 * @uses  sanitize_text_field()
	 *
	 * @param int      $recipe_id The post ID of the recipe post.
	 * @param array    $data      The data passed from the post custom metabox.
	 * @param \WP_Post $recipe    The review object this data is being saved to.
	 *
	 * @return int|\WP_Error
	 */
	public function save_metabox_metadata( $recipe_id, $data, $recipe ) {

		if ( ! $this->check_nonce( $data ) ) {
			return new \WP_Error( 'nonce_failed', 'Failed nonce check' );
		}

		// A new array to contain all non-empty line from the form.
		$non_empty    = array();
		$instructions = isset( $data[ $this->meta_key ] ) ? $data[ $this->meta_key ] : array();

		if ( ! empty( $instructions['bulk_import'] ) ) {
			$instructions = $this->parse_bulk_import( $instructions['bulk_import'] );
		}

		foreach ( $instructions as $instruction ) {
			// Make sure we have an array, as it may be an empty string.
			$instruction = (array) $instruction;

            // Adding a key for future work with Gutenberg/React
            $instruction['key'] = empty( $instruction['key'] ) ? substr( md5( mt_rand() ), 0, 9 ) : $instruction['key'];

			// Check if we have an instruction group, or an instruction line.
			if ( ! empty( $instruction['grouptitle'] ) ) {
				// We have an ingredient group title line,
				$non_empty[] = $instruction;
			} elseif ( ! empty( $instruction['description'] ) || ! empty( $instruction['image'] ) || ! empty( $instruction['line'] ) ) {
				$non_empty[] = array_map( 'sanitize_text_field', $instruction );
			}
		}

		// Save the new metadata array.
		update_post_meta( $recipe_id, $this->meta_key, $non_empty );

		return $recipe_id;
	}

	/**
	 * Parses the contents of the ingredients bulk import textarea.
	 *
	 * @since 2.6.0
	 *
	 * @param  string  $items
	 *
	 * @return array
	 */
	private function parse_bulk_import( $items ) {
		// Split the items into lines and remove empty lines and trim whitespace and reset array keys to 0.
		$lines  = array_values( array_filter( array_map( 'trim', explode( "\n", $items ) ) ) );
		$parsed = array();

		foreach ( $lines as $i => $line ) {
			// Check if we have a group title.
			if ( 0 === strpos( $line, '#' ) ) {
				$parsed[] = array(
					'sort'       => $i + 1,
					'grouptitle' => sanitize_text_field( $line ),
					'key'        => substr( md5( mt_rand() ), 0, 9 ),
				);
			} else {
				$parsed[] = array(
					'sort'       => $i + 1,
					'line'       => sanitize_text_field( $line ),
					'image'      => '',
					'key'        => substr( md5( mt_rand() ), 0, 9 ),
				);
			}
		}

		return $parsed;
	}

	/**
	 * Prints the instructions bulk import textarea.
	 *
	 * @since 2.6.0
	 *
	 * @param  array  $instructions
	 *
	 * @return void
	 */
	public function print_parsed_bulk_import( array $instructions ) {
		$out = '';

		foreach ( $instructions as $instruction ) {
			if ( ! empty( $instruction['grouptitle'] ) ) {
				$out .= sanitize_text_field( $instruction['grouptitle'] ) . "\n\n";
			}

			if ( ! empty( $instruction['line'] ) ) {
				$out .= sanitize_text_field( $instruction['line'] ) . "\n\n";
			}
		}

		echo trim( $out );
	}

}
