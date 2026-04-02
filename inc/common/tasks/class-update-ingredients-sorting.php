<?php
/**
 * Run through the task of updates our recipe ingredients
 *
 * @package Recencio
 */

namespace Recipepress\Inc\Common\Tasks;

use Recipepress as NS;
use Recipepress\Inc\Libraries\WP_Background_Processing\WP_Background_Process;


/**
 * Creates a background process that updates our recipe ingredients
 *
 * The old version of the plugin was not correctly setting the `sort` value
 * on the group heading of recipe ingredients.
 *
 * @since 1.0.0
 *
 * @package Recencio
 */
class Update_Ingredients_Sorting extends WP_Background_Process {

	/**
	 * A tag to identify our process
	 *
	 * @var $action
	 */
	protected $action = 'rpr_update_ingredients';

	/**
	 * The update message display for this task
	 *
	 * @return array
	 */
	public function update_message() {

		return array(
			$this->action => __( "Your recipe's ingredients list needs to be updated.", 'recipepress-reloaded' ),
		);
	}

	/**
	 * The update message display for this task
	 *
	 * @return bool
	 */
	public function is_update_needed() {

		return get_option( $this->action ) ? get_option( $this->action )['needs_update'] : false;
	}

	/**
	 * Returns an array of items to process
	 *
	 * @return array
	 */
	public function items_to_process() {

		global $wpdb;

		// phpcs:ignore
		return $wpdb->get_results(
			'SELECT p.ID from ' . $wpdb->posts . ' as p WHERE post_type = "rpr_recipe" AND post_status = "publish"',
			ARRAY_N
		);
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param  array $recipe The recipe ID to iterate over.
	 *
	 * @return bool
	 */
	protected function task( $recipe ) {

		$ID = (int) $recipe[0];
		$ingredients = get_post_meta( $ID, 'rpr_recipe_ingredients', true );

		foreach ( $ingredients as $key => $ingredient ) {
			if ( '' === $ingredient['sort'] ) {
				$ingredients[ $key ]['sort'] = (string) ( $key + 1 );
			}
		}

		update_post_meta( $ID, 'rpr_recipe_ingredients', $ingredients );

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {

		update_option( $this->action, array( 'needs_update' => false ) );

		parent::complete();
	}
}
