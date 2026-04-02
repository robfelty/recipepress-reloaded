<?php

namespace Recipepress\Inc\Importers;
/**
 * Handles importing recipes from the WP Ultimate Recipe plugin
 *
 * @since      2.2.0
 *
 * @package    Recipepress
 * @author     wzyMedia <wzy@outlook.com>
 */

use Recipepress\Inc\Libraries\WP_Background_Processing\WP_Background_Process;

/**
 * Handles importing recipes from the WP Ultimate Recipe plugin
 *
 * @since      2.2.0
 *
 * @package    Recipepress
 * @author     wzyMedia <wzy@outlook.com>
 */
class WPUR extends WP_Background_Process {

	/**
	 * A tag to identify our process
	 *
	 * @var $action
	 */
	public $action = 'rpr_wpur_to_rpr';

	/**
	 * The update message display for this task
	 *
	 * @return array
	 */
	public function plugin_information() {

		return array(
			$this->action => 'WP Ultimate Recipes (3.13.0) by Bootstrapped Ventures',
		);
	}

	/**
	 * The update message display for this task
	 *
	 * @return bool
	 */
	public function is_plugin_active() {

		return function_exists( 'is_plugin_active' ) && is_plugin_active( 'wp-ultimate-recipe/wp-ultimate-recipe.php' );
	}

	/**
	 * Returns an array of items to process
	 *
	 * @return array
	 */
	public function items_to_process() {
		global $wpdb;

		// phpcs:ignore
		return $wpdb->get_results( "SELECT ID from $wpdb->posts WHERE post_type = 'recipe'", ARRAY_N );
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param  mixed  $item  Queue item to iterate over.
	 *
	 * @return false
	 */
	protected function task( $item ) {
		global $wpdb;

		$ID = (int) $item[0];

		set_post_type( $ID, 'rpr_recipe' );

		// strip shortcodes
		$post_content = $wpdb->get_var( "SELECT post_content FROM  $wpdb->posts  WHERE ID = $ID");
		if ( $post_content ) {
			$post_content = str_ireplace( '[recipe]', '', $post_content );
			$post_content = str_ireplace( '[recipe-video]', '', $post_content );
			$post_content = str_ireplace( '/\[wpurp-searchable-recipe\][^\[]*\[\/wpurp-searchable-recipe\]/', '', $post_content );

			$wpdb->update( $wpdb->posts, [ 'post_content' => $post_content ], [ 'ID' => $ID ] );
		}

		// phpcs:ignore
		$stored_data = $wpdb->get_results(
			"SELECT * from $wpdb->postmeta WHERE post_id = $ID AND meta_key LIKE 'recipe_%'",
			OBJECT_K
		);


		foreach ( $stored_data as $datum ) {

			// recipe_rating
			if ( 'recipe_rating' === $datum->meta_key && '' !== $datum->meta_value ) {
				$wpdb->update( $wpdb->postmeta, [ 'meta_key' => 'rpr_rating_average' ], [ 'meta_key' => 'recipe_rating', 'post_id' => $datum->post_id ] );
				$wpdb->insert( $wpdb->postmeta, [ 'meta_key' => 'rpr_rating_count', 'meta_value' => NULL, 'post_id' => $datum->post_id ] );

				continue;
			}

			// recipe_title
			if ( 'recipe_title' === $datum->meta_key ) {
				$wpdb->delete( $wpdb->postmeta, ['meta_key' => 'recipe_title', 'post_id'  => $datum->post_id] );

				continue;
			}

			// recipe_description
			if ( 'recipe_description' === $datum->meta_key && '' !== $datum->meta_value ) {
				$wpdb->update( $wpdb->posts, [ 'post_excerpt' => $datum->meta_value ], [ 'ID' => (int) $datum->post_id ] );
				$wpdb->delete( $wpdb->postmeta, ['meta_key' => 'recipe_description', 'post_id'  => $datum->post_id] );

				continue;
			}

			// recipe_servings
			if ( 'recipe_servings' === $datum->meta_key && '' !== $datum->meta_value ) {
				$wpdb->update( $wpdb->postmeta, [ 'meta_key' => 'rpr_recipe_servings' ], [ 'meta_key' => 'recipe_servings', 'post_id' => $datum->post_id ] );
				$wpdb->delete( $wpdb->postmeta, ['meta_key' => 'recipe_servings_normalized', 'post_id'  => $datum->post_id] );

				continue;
			}

			// recipe_servings_type
			if ( 'recipe_servings_type' === $datum->meta_key && '' !== $datum->meta_value ) {
				$wpdb->update( $wpdb->postmeta, [ 'meta_key' => 'rpr_recipe_servings_type' ], [ 'meta_key' => 'recipe_servings_type', 'post_id' => $datum->post_id ] );

				continue;
			}

			// recipe_prep_time
			if ( 'recipe_prep_time' === $datum->meta_key && '' !== $datum->meta_value ) {
				$unit = $wpdb->get_var( "SELECT meta_value FROM  $wpdb->postmeta  WHERE post_id = $datum->post_id  AND meta_key = 'recipe_prep_time_text'");
				$wpdb->update( $wpdb->postmeta, [
					'meta_key'   => 'rpr_recipe_prep_time',
					'meta_value' => $this->convert_time( (int) $datum->meta_value, $unit )
				], [
					'meta_key' => 'recipe_prep_time',
					'post_id'  => $datum->post_id
				] );
				$wpdb->delete( $wpdb->postmeta, ['meta_key' => 'recipe_prep_time_text', 'post_id'  => $datum->post_id] );

				continue;
			}

			// recipe_cook_time
			if ( 'recipe_cook_time' === $datum->meta_key && '' !== $datum->meta_value ) {
				$unit = $wpdb->get_var( "SELECT meta_value FROM  $wpdb->postmeta  WHERE post_id = $datum->post_id  AND meta_key = 'recipe_cook_time_text'");
				$wpdb->update( $wpdb->postmeta, [
					'meta_key'   => 'rpr_recipe_cook_time',
					'meta_value' => $this->convert_time( (int) $datum->meta_value, $unit )
				], [
					'meta_key' => 'recipe_cook_time',
					'post_id'  => $datum->post_id
				] );
				$wpdb->delete( $wpdb->postmeta, ['meta_key' => 'recipe_cook_time_text', 'post_id'  => $datum->post_id] );

				continue;
			}

			// recipe_passive_time
			if ( 'recipe_passive_time' === $datum->meta_key && '' !== $datum->meta_value ) {
				$unit = $wpdb->get_var( "SELECT meta_value FROM  $wpdb->postmeta  WHERE post_id = $datum->post_id  AND meta_key = 'recipe_passive_time_text'");
				$wpdb->update( $wpdb->postmeta, [
					'meta_key'   => 'rpr_recipe_passive_time',
					'meta_value' => $this->convert_time( (int) $datum->meta_value, $unit )
				], [
					'meta_key' => 'recipe_passive_time',
					'post_id'  => $datum->post_id
				] );
				$wpdb->delete( $wpdb->postmeta, ['meta_key' => 'recipe_passive_time_text', 'post_id'  => $datum->post_id] );

				continue;
			}

			// recipe_ingredients
			if ( 'recipe_ingredients' === $datum->meta_key && '' !== $datum->meta_value ) {
				$saved_ingredients = maybe_unserialize( $wpdb->get_var( "SELECT meta_value FROM  $wpdb->postmeta  WHERE post_id = $datum->post_id  AND meta_key = 'recipe_ingredients'") );
				$converted_ingredients = [];

				foreach ( $saved_ingredients as $key => $ingredient ) {
					$ins = [];
					if ( $ingredient['group'] && ! $this->key_exists( $converted_ingredients, $ingredient['group'], 'grouptitle' ) ) {
						$ins['sort'] = (string) ($key + 1);
						$ins['grouptitle'] = $ingredient['group'];
						$ins['key'] = substr( md5( mt_rand() ), 0, 9 );

						$converted_ingredients[] = $ins;

						continue;
					}

					$ins['sort'] = (string) ($key + 1);
					$ins['amount'] = (string) $ingredient['amount'];
					$ins['unit'] = (string) $ingredient['unit'];
					$ins['ingredient'] = (string) $ingredient['ingredient'];
					$ins['notes'] = (string) $ingredient['notes'];
					$ins['link'] = '';
					$ins['target'] = '';
					$ins['ingredient_id'] = (string) $ingredient['ingredient_id'];
					$ins['key'] = substr( md5( mt_rand() ), 0, 9 );

					$converted_ingredients[] = $ins;

					$this->create_ingredient_taxonomy( $datum->post_id, $ins );
				}

				$wpdb->update( $wpdb->postmeta, [
					'meta_key'   => 'rpr_recipe_ingredients',
					'meta_value' => maybe_serialize( $converted_ingredients )
				], [
					'meta_key' => 'recipe_ingredients',
					'post_id'  => $datum->post_id
				] );

				continue;
			}

			// recipe_instructions
			if ( 'recipe_instructions' === $datum->meta_key && '' !== $datum->meta_value ) {
				$saved_instructions = maybe_unserialize( $wpdb->get_var( "SELECT meta_value FROM  $wpdb->postmeta  WHERE post_id = $datum->post_id  AND meta_key = 'recipe_instructions'") );
				$converted_instructions = [];

				foreach ( $saved_instructions as $key => $instruction ) {
					$ins = [];
					if ( $instruction['group'] && ! $this->key_exists( $converted_instructions, $instruction['group'], 'grouptitle' ) ) {
						$ins['sort'] = (string) ($key + 1);
						$ins['grouptitle'] = $instruction['group'];
						$ins['key'] = substr( md5( mt_rand() ), 0, 9 );

						$converted_instructions[] = $ins;

						continue;
					}

					$ins['sort'] = (string) ($key + 1);
					$ins['description'] = (string) $instruction['description'];
					$ins['image'] = (string) $instruction['image'];
					$ins['key'] = substr( md5( mt_rand() ), 0, 9 );

					$converted_instructions[] = $ins;
				}

				$wpdb->update( $wpdb->postmeta, [
					'meta_key'   => 'rpr_recipe_instructions',
					'meta_value' => maybe_serialize( $converted_instructions )
				], [
					'meta_key' => 'recipe_instructions',
					'post_id'  => $datum->post_id
				] );

				continue;
			}

			// recipe_notes
			if ( 'recipe_notes' === $datum->meta_key && '' !== $datum->meta_value ) {
				$wpdb->update( $wpdb->postmeta, [
					'meta_key'   => 'rpr_recipe_notes',
				], [
					'meta_key' => 'recipe_notes',
					'post_id'  => $datum->post_id
				] );

				continue;
			}

			// recipe_terms
			if ( 'recipe_terms' === $datum->meta_key && '' !== $datum->meta_value ) {
				$taxonomies = [];
				$saved_terms = maybe_unserialize( $wpdb->get_var( "SELECT meta_value FROM  $wpdb->postmeta  WHERE post_id = $datum->post_id  AND meta_key = 'recipe_terms'") );

				foreach ( $saved_terms as $key => $saved_term ) {
					if ( ! in_array( $key, [ 'ingredient', 'category', 'post_tag', 'wpurp_keyword' ], true ) ) {
						foreach ( $saved_term as $term ) {
							if ( $term !== 0 ) {
								$wpur_term = get_term_by( 'id', $term, $key );
								$rpr_term  = get_term_by( 'slug', $wpur_term->slug, 'rpr_' . $key );

								if ( false === $rpr_term ) {
									$rpr_term = wp_insert_term( sanitize_text_field( $wpur_term->name ), 'rpr_' . $key );
								}

								$taxonomies[] = $rpr_term->term_id;
							}
						}
					}

					wp_set_post_terms( $datum->post_id, $taxonomies, 'rpr_' . $key );
				}

				$wpdb->delete( $wpdb->postmeta, ['meta_key' => 'recipe_terms', 'post_id'  => $datum->post_id] );

				continue;
			}

			// recipe_terms_with_parents
			if ( 'recipe_terms_with_parents' === $datum->meta_key && '' !== $datum->meta_value ) {
				$wpdb->delete( $wpdb->postmeta, ['meta_key' => 'recipe_terms_with_parents', 'post_id'  => $datum->post_id] );

				continue;
			}

			// recipe_nutritional
			if ( 'recipe_nutritional' === $datum->meta_key && '' !== $datum->meta_value ) {
				$saved_nutrition = maybe_unserialize( $wpdb->get_var( "SELECT meta_value FROM  $wpdb->postmeta  WHERE post_id = $datum->post_id  AND meta_key = 'recipe_nutritional'") );

				foreach ( $saved_nutrition as $k => $v ) {
					if ( 'calories' === $k ) {
						$wpdb->insert( $wpdb->postmeta, [ 'meta_key' => 'rpr_recipe_calorific_value', 'meta_value' => $v, 'post_id' => $datum->post_id ] );
					}

					if ( 'carbohydrate' === $k ) {
						$wpdb->insert( $wpdb->postmeta, [ 'meta_key' => 'rpr_recipe_carbohydrate', 'meta_value' => $v, 'post_id' => $datum->post_id ] );
					}

					if ( 'protein' === $k ) {
						$wpdb->insert( $wpdb->postmeta, [ 'meta_key' => 'rpr_recipe_protein', 'meta_value' => $v, 'post_id' => $datum->post_id ] );
					}

					if ( 'fat' === $k ) {
						$wpdb->insert( $wpdb->postmeta, [ 'meta_key' => 'rpr_recipe_fat', 'meta_value' => $v, 'post_id' => $datum->post_id ] );
					}
				}

				$wpdb->insert( $wpdb->postmeta, [ 'meta_key' => 'rpr_recipe_nutrition', 'meta_value' => maybe_serialize( $saved_nutrition ), 'post_id' => $datum->post_id ] );
				$wpdb->insert( $wpdb->postmeta, [ 'meta_key' => 'rpr_recipe_nutrition_per', 'meta_value' => 'per_serving', 'post_id' => $datum->post_id ] );
				$wpdb->delete( $wpdb->postmeta, ['meta_key' => 'recipe_nutritional', 'post_id'  => $datum->post_id] );

				continue;
			}

		}

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {

		// update_option( $this->action, array( 'needs_update' => false ) ); // TODO

		parent::complete();
	}

	/**
	 * Convert time
	 *
	 * @since 2.2.0
	 *
	 * @param $measurement
	 * @param $unit
	 *
	 * @return int
	 */
	private function convert_time( $measurement, $unit ) {
		$time = 0;

		if ( 'hour' === $unit || 'hours' === $unit ) {
			$time = $measurement * 60;
		}

		if ( 'minute' === $unit || 'minutes' === $unit ) {
			$time = $measurement;
		}

		return (int) $time;
	}

	/**
	 * Check if a value already exist in a nested array
	 *
	 * @since 2.2.0
	 *
	 * @param array  $array
	 * @param string $value
	 * @param string $key
	 *
	 * @return bool
	 */
	private function key_exists( $array, $value, $key ) {
		foreach( $array as $k => $v ) {
			if ( $v[$key] === $value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Create an ingredient taxonomy
	 *
	 * If the taxonomy does not exist create a new one and attach
	 * it to our recipe post
	 *
	 * @since 2.2.0
	 *
	 * @param int   $recipe_id
	 * @param array $ingredient
	 *
	 * @return void
	 */
	private function create_ingredient_taxonomy( $recipe_id, $ingredient ) {
		$taxonomy = [];
		$term     = term_exists( $ingredient['ingredient'], 'rpr_ingredient' );

		if ( 0 === $term || null === $term ) {
			$term = wp_insert_term( sanitize_text_field( $ingredient['ingredient'] ), 'rpr_ingredient' );
		}

		if ( is_wp_error( $term ) ) {
			return;
		}

		$term_id = (int) $term['term_id'];

		if ( '' === get_term_meta( $term_id, 'ingredient_custom_meta', true ) ) {
			update_term_meta( $term_id, 'ingredient_custom_meta', array( 'use_in_listings' => '1' ) );
		}

		$taxonomy[] = $term_id;

		wp_set_post_terms( $recipe_id, $taxonomy, 'rpr_ingredient' );
	}
}