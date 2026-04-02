<?php

namespace Recipepress\Inc\Admin\Metaboxes;

use Recipepress\Inc\Core\Options;
use Recipepress\Inc\Common\Abstracts\Metadata;
use Recipepress\Inc\Common\Traits\Utilities;

/**
 * Saving the instructions meta information.
 *
 * @package    Recipepress
 *
 * @author     Kemory Grubb <kemory@wzymedia.com>
 */
class Ingredients extends Metadata {

	use Utilities;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 *
	 * @param  string  $plugin_name  The ID of this plugin.
	 * @param  string  $version      The current version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version, 'rpr_ingredients_metabox', 'rpr_recipe_ingredients', __DIR__, false, true );
	}

	/**
	 * Add a metabox for the recipe ingredients.
	 *
	 * We are removing the default ingredients taxonomy metabox
	 * and replacing it with our own.
	 *
	 * @since 1.0.0
	 *
	 * @uses  add_meta_box
	 * @return void
	 */
	public function add_metabox() {
		add_meta_box(
			$this->metabox_id,
			__( 'Ingredients', 'recipepress-reloaded' ),
			array( $this, 'render_metabox' ),
			$this->post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Register this meta information
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
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'grouptitle'    => array(
									'type' => 'string',
								),
								'sort'          => array(
									'type' => 'string',
								),
								'amount'        => array(
									'type' => 'string',
								),
								'unit'          => array(
									'type' => 'string',
								),
								'ingredient'    => array(
									'type' => 'string',
								),
								'notes'         => array(
									'type' => 'string',
								),
								'link'          => array(
									'type' => 'string',
								),
								'target'        => array(
									'type' => 'string',
								),
								'ingredient_id' => array(
									'type' => 'string',
								),
								'key'           => array(
									'type' => 'string',
								),
								'line'          => array(
									'type' => 'string',
								),
							),
						),
					),
				),
				'description'   => 'The recipe ingredient metadata',
				'auth_callback' => function ( $allowed, $meta_key, $object_id, $user_id, $cap, $caps ) {
					return current_user_can( 'edit_posts', $object_id );
				},
			)
		);
	}

	/**
	 * Should we display this metabox?
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	protected function display_metabox() {
		return true;
	}

	/**
	 * Get the list of pre-configured ingredient unit list entered
	 * in our setting page.
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $selected  The current selected ingredient unit.
	 *
	 * @return string
	 */
	public function get_the_ingredient_unit_selection( $selected = null ) {
		$units = explode( ',', Options::get_option( 'rpr_ingredient_unit_list', array() ) );
		$out   = '';

		foreach ( $units as $key => $unit ) {
			$out .= '<option value="' . $unit . '"';
			if ( $unit === $selected ) {
				$out .= ' selected="selected" ';
			}
			$out .= '>' . $unit . '</option>' . "\n";
		}
		if ( ! in_array( $selected, $units, true ) ) {
			$out .= '<option value="' . esc_attr( $selected ) . '"  selected="selected" >' . esc_attr( $selected ) . '</option>';
		}

		return $out;
	}

	/**
	 * Check the presence of, sanitizes then saves the ingredients.
	 *
	 * @since 1.0.0
	 *
	 * @uses  update_post_meta()
	 * @uses  wp_set_post_terms()
	 * @uses  sanitize_text_field()
	 *
	 * @param  int       $recipe_id  The post ID of the recipe post.
	 * @param  array     $data       The data passed from the $_POST request.
	 * @param  \WP_Post  $recipe     The recipe object this data is being saved to.
	 *
	 * @return int|\WP_Error
	 */
	public function save_metabox_metadata( $recipe_id, $data, $recipe ) {
		if ( ! $this->check_nonce( $data ) ) {
			return new \WP_Error( 'nonce_failed', 'Failed nonce check' );
		}

		$ingredients = isset( $data[ $this->meta_key ] ) ? $data[ $this->meta_key ] : array();

		if ( ! empty( $ingredients['bulk_import'] ) ) {
			$ingredients = $this->parse_bulk_import( $ingredients['bulk_import'] );
		}

		// A new array to contain all non-empty ingredient lines from the form.
		$non_empty = array();

		// An array of all ingredient term_ids to create a relation to the recipe.
		$ing_taxonomies = array();

		// Unit list for the ingredient unit selection.
		$unit_list = array();

		foreach ( $ingredients as $ingredient ) {

			$ingredient = (array) $ingredient;

			// Adding a key for future work with Gutenberg/React
			if ( empty( $ingredient['key'] ) && ( ( ! empty( $ingredient['ingredient'] ) || ! empty( $ingredient['notes'] ) ) || ! empty( $ingredient['grouptitle'] ) ) ) {
				$key = $ingredient['ingredient'] ?: $ingredient['grouptitle'];
				$ingredient['key'] = substr( md5( $key . $ingredient['sort'] ), 0, 9 );
			}

			// Check if we have an ingredients group or an ingredient.
			if ( ! empty( $ingredient['grouptitle'] ) ) {
				// we have an ingredient group title line
				$non_empty[] = $ingredient;
			} else {
				// we have a single ingredient line.
				if ( ! empty( $ingredient['ingredient'] ) ) {
					// We need to find the term_id of the ingredient and add a taxonomy relation to the recipe.
					$term = term_exists( $ingredient['ingredient'], 'rpr_ingredient' );

					if ( 0 === $term || null === $term ) {
						// Ingredient is not an existing term, create it.
						$term = wp_insert_term( sanitize_text_field( $ingredient['ingredient'] ), 'rpr_ingredient' );
					}

					if ( is_wp_error( $term ) ) {
						return new \WP_Error( 'invalid_term', 'Invalid ingredient term: ' . $ingredient['ingredient'] );
					}

					// Now we have a valid term id!
					$term_id = (int) $term['term_id'];

					// This means it's a new ingredient being created.
					if ( '' === get_term_meta( $term_id, 'ingredient_custom_meta', true ) ) {
						update_term_meta( $term_id, 'ingredient_custom_meta', array( 'use_in_listings' => '1' ) );
					}

					// Set it to the ingredient array.
					$ingredient['ingredient_id'] = $term_id;
					$ing_taxonomies[]            = $term_id;
				}

				if ( ! empty( $ingredient['ingredient'] ) || ! empty( $ingredient['line'] ) ) {
					$ing = array();
					foreach( $ingredient as $k => $v ) {
						if ( 'link' === $k ) {
							$ing[ 'link' ] = remove_accents( urldecode( $v ) );
						} else {
							$ing[ $k ] = sanitize_text_field( $v );
						}
					}

					if ( ! empty( $ing['unit'] ) ) {
						$unit_list[] = $ing['unit'];
					}

					$non_empty[] = $ing;
				}

			}
		}

		// Save the ingredient unit list.
		if ( $unit_list && Options::get_option( 'rpr_use_ingredient_unit_list' ) ) {
			$saved_unit_list = explode( ',', Options::get_option( 'rpr_ingredient_unit_list' ) );
			$new_unit_list = implode( ',', array_unique( array_merge( $saved_unit_list, $unit_list ) ) );

			Options::update_option( 'rpr_ingredient_unit_list', $new_unit_list );
		}

		// Save the recipe <-> ingredient taxonomy relationship.
		if ( $ing_taxonomies ) {
			wp_set_post_terms( $recipe_id, $ing_taxonomies, 'rpr_ingredient' );
		}
		// Save the new metadata array.
		update_post_meta( $recipe_id, $this->meta_key, $non_empty );

		return $recipe_id;
	}

	/**
	 * Return an array of all our ingredients
	 *
	 * @since 2.0.0
	 *
	 * @return string[]
	 */
	public function ingredients_list() {
		$ingredients_list = array();
		$ingredients      = get_terms( 'rpr_ingredient', array( 'orderby' => 'name',	'order'   => 'ASC' ) );

		if ( is_wp_error( $ingredients ) ) {
			return $ingredients_list;
		}

		foreach ( $ingredients as $ingredient ) {
			$ingredients_list[] = $ingredient->name;
		}

		return $ingredients_list;
	}

	/**
	 * Parses the contents of the ingredients bulk import textarea input.
	 *
	 * @since 2.7.0
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
				$group_title = sanitize_text_field( $line );
				$parsed[] = array(
					'sort'       => $i + 1,
					'grouptitle' => $group_title,
					'key'        => substr( md5( $group_title ), 0, 9 ),
				);
			} else {
				// We have a single ingredient line.
				$parsed_markdown = $this->parse_link_markdown( $line );
				$ingredient      = $parsed_markdown ? $parsed_markdown[ 'text' ] : null;
				$parsed[]        = array(
					'sort'       => $i + 1,
					'line'       => sanitize_text_field( $line ),
					'ingredient' => $ingredient,
					'key'        => substr( md5( $ingredient ?: ( $i + 1 ) ), 0, 9 ),
					'link'       => $parsed_markdown ? $parsed_markdown[ 'url' ] : null,
					'target'     => $parsed_markdown ? ( $this->internal_url( $parsed_markdown['url' ] ) ? 'same' : 'new' ) : null,
				);
			}
		}

		return $parsed;
	}

	/**
	 * @param  string  $markdown
	 *
	 * @return array
	 */
	protected function parse_link_markdown( $markdown ) {
		if ( empty( $markdown ) ) {
			return [];
		}

		// Match the link syntax using a regular expression
		preg_match_all( '/\[(.*?)\](?:\((.*?)\))?/', $markdown, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return [];
		}

		$link_text = '';
		$link_url  = '';

		// Loop through each matched link and output it
		foreach ( $matches as $match ) {
			$link_text = ! empty( $match[1] ) ? $match[1] : null;
			$link_url = ! empty( $match[2] ) ? $match[2] : null;
		}

		return [
			'text' => $link_text,
			'url'  => $link_url
		];
	}

	/**
	 * Prints the ingredients bulk import textarea.
	 *
	 * @since 2.7.0
	 *
	 * @param  array  $ingredients
	 *
	 * @return void
	 */
	public function print_parsed_bulk_import( array $ingredients ) {
		$out = '';

		foreach ( $ingredients as $ingredient ) {
			if ( ! empty( $ingredient['grouptitle'] ) ) {
				$out .= sanitize_text_field( $ingredient['grouptitle'] ) . "\n";
			}

			if ( ! empty( $ingredient['line'] ) ) {
				$out .= sanitize_text_field( $ingredient['line'] ) . "\n";
			}
		}

		echo trim( $out );
	}

}
